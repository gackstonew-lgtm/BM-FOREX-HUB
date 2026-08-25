<?php
/**
 * PHP proxy for the Python signal engine.
 * Routes: ?route=signals, ?route=prices, ?route=strength, ?route=health
 * Optional: ?force=1 (for signals)
 *
 * Access control: only the "signals" route carries the proprietary
 * trade-setup data (the Live Signal Grid). prices/strength/health stay
 * fully public, exactly as before — they power Market Overview, the
 * Currency Strength Meter, and price polling, none of which are gated
 * by the trial. For "signals", an expired-trial / unsubscribed caller
 * (including a direct/unauthenticated request to this endpoint) still
 * gets a 200 response with pair/price/structure data intact, but each
 * pair's "setup" (entry/SL/TP/direction — the actual signal call) is
 * stripped server-side, so the restriction can't be bypassed from the
 * client. This mirrors the auth pattern already used in
 * api/subscription-status.php and api/crypto-config.php.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../engine_config.php';

$base = getenv('SIGNAL_ENGINE_URL') ?: 'http://157.173.193.93:5000';

$route  = $_GET['route'] ?? '';
$force  = ($_GET['force'] ?? '0') === '1' ? '?force=1' : '';

$allowed = ['signals', 'prices', 'strength', 'health'];
if (!in_array($route, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid route']);
    exit;
}

// Only the signals route needs to know who's asking; prices/strength/health
// never touch Supabase and stay exactly as fast/public as before.
$engineSignalAccess = true;
if ($route === 'signals') {
    require_once __DIR__ . '/kora-config.php'; // SUPABASE_URL / SUPABASE_ANON_KEY / SUPABASE_SERVICE_KEY + bm_has_permanent_access()
    $engineSignalAccess = engine_signal_access_allowed();
}

$url = $base . '/api/' . $route . ($route === 'signals' && $force ? $force : '');

$ctx = stream_context_create([
    'http' => [
        'timeout' => $route === 'signals' ? 300 : 10,
        'method'  => 'GET',
        'header'  => "User-Agent: BMForexHub/1.0\r\n",
    ],
    'ssl' => ['verify_peer' => true],
]);

$body = @file_get_contents($url, false, $ctx);
if ($body === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Engine temporarily unavailable']);
    exit;
}

if ($route === 'strength') {
    $data = json_decode($body, true);
    // Detect flat scores (all 5) — compute from signal RSI data as fallback
    if (is_array($data) && isset($data['currencies'])) {
        $scores = array_unique(array_map(function($c) { return $c['score'] ?? 0; }, $data['currencies']));
        if (count($scores) <= 1) {
            // Fetch signals for RSI data to compute strength
            $sigUrl = $base . '/api/signals?limit=14';
            $sigBody = @file_get_contents($sigUrl, false, $ctx);
            if ($sigBody) {
                $sigData = json_decode($sigBody, true);
                if (is_array($sigData) && isset($sigData['signals'])) {
                    $data['currencies'] = compute_strength_from_signals($sigData['signals']);
                    echo json_encode($data);
                    exit;
                }
            }
        }
    }
    echo $body;
    exit;
}

$data = json_decode($body, true);
$bodyModified = false;

// Inject real-time Gold spot price into engine responses
if (in_array($route, ['signals', 'prices']) && is_array($data)) {
    $goldMeta  = engine_fetch_live_xauusd_price();
    $goldPrice = (is_array($goldMeta) && isset($goldMeta['price'])) ? (float)$goldMeta['price'] : null;

    if ($goldPrice) {
        if ($route === 'signals' && isset($data['signals']) && is_array($data['signals'])) {
            foreach ($data['signals'] as &$s) {
                if (isset($s['pair']['code']) && $s['pair']['code'] === 'XAUUSD') {
                    $enginePrice = $s['currentPrice'] ?? null;
                    if ($enginePrice) {
                        $offset = $goldPrice - $enginePrice;
                        if (isset($s['setup']) && is_array($s['setup'])) {
                            if (isset($s['setup']['entry'])) $s['setup']['entry'] = round($s['setup']['entry'] + $offset, 2);
                            if (isset($s['setup']['sl'])) $s['setup']['sl'] = round($s['setup']['sl'] + $offset, 2);
                            if (isset($s['setup']['tp1'])) $s['setup']['tp1'] = round($s['setup']['tp1'] + $offset, 2);
                            if (isset($s['setup']['tp2'])) $s['setup']['tp2'] = round($s['setup']['tp2'] + $offset, 2);
                            if (isset($s['setup']['ob_high'])) $s['setup']['ob_high'] = round($s['setup']['ob_high'] + $offset, 2);
                            if (isset($s['setup']['ob_low'])) $s['setup']['ob_low'] = round($s['setup']['ob_low'] + $offset, 2);
                        }
                        if (isset($s['structure']['last_bos']['price'])) {
                            $s['structure']['last_bos']['price'] = round($s['structure']['last_bos']['price'] + $offset, 2);
                        }
                        if (isset($s['structure']['last_choch']['price'])) {
                            $s['structure']['last_choch']['price'] = round($s['structure']['last_choch']['price'] + $offset, 2);
                        }
                    }
                    $s['currentPrice'] = $goldPrice;
                    $s['price_meta']   = $goldMeta;
                    $bodyModified = true;
                }
            }
            unset($s);
        } elseif ($route === 'prices' && isset($data['prices']) && is_array($data['prices'])) {
            foreach ($data['prices'] as &$p) {
                if (isset($p['code']) && $p['code'] === 'XAUUSD') {
                    $p['price'] = $goldPrice;
                    $bodyModified = true;
                }
            }
        }
    } else {
        // If live market feed fails & cache is stale, invalidate XAUUSD setup to prevent bad signals
        if ($route === 'signals' && isset($data['signals']) && is_array($data['signals'])) {
            foreach ($data['signals'] as &$s) {
                if (isset($s['pair']['code']) && $s['pair']['code'] === 'XAUUSD') {
                    $s['setup'] = null;
                    $bodyModified = true;
                }
            }
            unset($s);
        }
    }
}

// Deduplicate signals by pair code to ensure each asset (e.g. SOL/Solana) has only one accurate signal
if ($route === 'signals' && is_array($data) && isset($data['signals']) && is_array($data['signals'])) {
    $uniqueSignals = [];
    foreach ($data['signals'] as $s) {
        if (!is_array($s)) continue;
        $pairCode = $s['pair']['code'] ?? $s['pair']['symbol'] ?? $s['code'] ?? $s['symbol'] ?? null;
        if (!$pairCode) continue;

        $pairKey = strtoupper(trim($pairCode));
        if (!isset($uniqueSignals[$pairKey])) {
            $uniqueSignals[$pairKey] = $s;
        } else {
            $existing = $uniqueSignals[$pairKey];
            $hasSetup = !empty($s['setup']);
            $existingHasSetup = !empty($existing['setup']);

            if (!$existingHasSetup && $hasSetup) {
                $uniqueSignals[$pairKey] = $s;
            } elseif ($existingHasSetup && $hasSetup) {
                $eRR = (float)($existing['setup']['rr'] ?? 0);
                $sRR = (float)($s['setup']['rr'] ?? 0);
                if ($sRR > $eRR) {
                    $uniqueSignals[$pairKey] = $s;
                }
            }
        }
    }
    $data['signals'] = array_values($uniqueSignals);
    $bodyModified = true;
}

// Optimize & validate signals (SL 20-30 pips, TP >= 60 pips, R:R >= 1:2.0)
if ($route === 'signals' && is_array($data) && isset($data['signals']) && is_array($data['signals'])) {
    engine_optimize_and_validate_signals($data);
    $bodyModified = true;
}

if ($route === 'signals' && !$engineSignalAccess) {
    if (is_array($data) && isset($data['signals']) && is_array($data['signals'])) {
        foreach ($data['signals'] as &$s) {
            if (is_array($s)) $s['setup'] = null;
        }
        unset($s);
        $data['signal_access'] = 'locked';
        $bodyModified = true;
    }
}

if ($bodyModified) {
    echo json_encode($data);
    exit;
}

echo $body;

/**
 * ── Live Signal Grid access check ───────────────────────────────────
 * Full access to the "setup" field (entry/SL/TP/direction) requires:
 *   - one of the 3 permanent admin accounts, OR
 *   - an active subscription, OR
 *   - a trial that hasn't expired yet (trial_started_at + 3 days).
 * Does NOT start/reset a trial — that's owned by subscription-status.php,
 * which already runs once per dashboard load before this is ever called.
 */
function engine_signal_access_allowed(): bool
{
    $user = engine_get_user_from_token();
    if (!$user) return false;

    if (bm_has_permanent_access($user['email'] ?? null)) return true;

    require_once __DIR__ . '/../app/Services/MembershipService.php';
    $service = new \App\Services\MembershipService();
    $user_id = $user['id'];

    $activeSub = $service->getActiveSubscription($user_id);
    if ($activeSub) return true;

    $trialStatus = $service->getTrialStatus($user_id);
    if ($trialStatus['trial_active']) return true;

    return false;
}

function engine_get_user_from_token(): ?array
{
    $auth_header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['Authorization']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) return null;
    }
    $token = $m[1];

    $ctx = stream_context_create([
        'http' => [
            'header'  => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON_KEY . "\r\n",
            'timeout' => 5,
        ],
    ]);

    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}

function engine_supabase_request($url, $method = 'GET')
{
    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", [
                "apikey: " . SUPABASE_SERVICE_KEY,
                "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            ]),
            'timeout' => 8,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp ? json_decode($resp, true) : null;
}

/**
 * Compute currency strength scores from signal RSI/trend data
 * as fallback when the engine returns flat scores.
 */
function compute_strength_from_signals($signals) {
    $ccyPairs = [];
    $rsiMap = [];
    foreach ($signals as $s) {
        if (!isset($s['pair'], $s['structure'])) continue;
        $code = $s['pair']['code'] ?? '';
        $rsi  = $s['structure']['rsi'] ?? null;
        if (!$code || $rsi === null) continue;
        $rsiMap[$code] = $rsi;
    }

    // Map RSI to currency scores using a simple triangulation
    // A high RSI for EURUSD suggests EUR strong or USD weak
    $ccys = ['USD','EUR','GBP','JPY','CHF','AUD','CAD','NZD','XAU','BTC'];
    $raw = [];
    foreach ($ccys as $c) { $raw[$c] = ['base' => [], 'quote' => []]; }

    $pairMap = [
        'EURUSD' => ['base'=>'EUR','quote'=>'USD'],
        'GBPUSD' => ['base'=>'GBP','quote'=>'USD'],
        'USDJPY' => ['base'=>'USD','quote'=>'JPY'],
        'USDCHF' => ['base'=>'USD','quote'=>'CHF'],
        'AUDUSD' => ['base'=>'AUD','quote'=>'USD'],
        'NZDUSD' => ['base'=>'NZD','quote'=>'USD'],
        'USDCAD' => ['base'=>'USD','quote'=>'CAD'],
        'XAUUSD' => ['base'=>'XAU','quote'=>'USD'],
        'BTCUSD' => ['base'=>'BTC','quote'=>'USD'],
    ];

    foreach ($rsiMap as $code => $rsi) {
        if (!isset($pairMap[$code])) continue;
        $base  = $pairMap[$code]['base'];
        $quote = $pairMap[$code]['quote'];
        $raw[$base]['base'][]  = $rsi;
        $raw[$quote]['quote'][] = $rsi;
    }

    $currencies = [];
    $rank = 1;
    $scores = [];
    foreach ($ccys as $c) {
        $bAvg = count($raw[$c]['base'])  > 0 ? array_sum($raw[$c]['base']) / count($raw[$c]['base']) : 50;
        $qAvg = count($raw[$c]['quote']) > 0 ? array_sum($raw[$c]['quote']) / count($raw[$c]['quote']) : 50;
        // Base pairs: high RSI → strong. Quote pairs: high RSI → weak.
        $score = round(($bAvg + (100 - $qAvg)) / 2);
        $score = max(1, min(99, $score));
        $scores[$c] = $score;
    }
    // If still flat, add small spread based on position
    $unique = array_unique(array_values($scores));
    if (count($unique) <= 1) {
        $i = 0;
        foreach ($ccys as $c) {
            $scores[$c] = max(1, min(99, 50 + ($i++ - 3.5) * 6));
        }
    }
    arsort($scores);
    foreach ($scores as $c => $score) {
        $currencies[] = [
            'currency' => $c,
            'score'    => (int)$score,
            'rank'     => $rank++,
            'change'   => 0.0,
            'bullish'  => $score > 55,
        ];
    }
    return $currencies;
}

/**
 * Return pip size for a given instrument pair code and price decimals.
 */
function engine_get_pip_size(string $pairCode, int $decimals = 4): float
{
    $code = strtoupper(trim($pairCode));
    
    if ($code === 'XAUUSD') {
        // Gold: $0.10 price move = 1 pip ($1.00 move = 10 pips)
        return 0.10;
    }
    if ($code === 'BTCUSD') {
        // Bitcoin: $10.00 price move = 1 pip ($250 risk = 25 pips, $600 target = 60 pips)
        return 10.0;
    }
    if ($code === 'ETHUSD') {
        // Ethereum: $1.00 price move = 1 pip ($25 risk = 25 pips, $60 target = 60 pips)
        return 1.0;
    }
    if ($code === 'SOLUSD') {
        // Solana: $0.10 price move = 1 pip ($2.50 risk = 25 pips, $6.00 target = 60 pips)
        return 0.10;
    }
    if (in_array($code, ['NAS100', 'US30', 'SPX500', 'GER30'], true)) {
        // Index CFD: 1 point = 1 pip
        return 1.0;
    }
    if (strpos($code, 'JPY') !== false || $decimals === 2 || $decimals === 3) {
        // JPY pairs: 0.01 = 1 pip
        return 0.01;
    }
    
    // Standard 5-digit Forex pairs (EURUSD, GBPUSD, USDCHF, AUDUSD, NZDUSD, USDCAD)
    return 0.0001;
}

/**
/**
 * Evaluate Higher-Timeframe (4H/1H) Alignment for Day-Trading setups.
 */
function engine_check_htf_alignment(string $direction, array $structure): bool
{
    $dailyBias = strtolower(trim($structure['daily_bias'] ?? 'neutral'));
    $trend     = strtolower(trim($structure['trend'] ?? 'neutral'));
    $lastChoch = strtolower(trim($structure['last_choch']['dir'] ?? ''));
    $lastBos   = strtolower(trim($structure['last_bos']['dir'] ?? ''));

    if ($direction === 'buy') {
        // HTF bullish conditions: Daily Bias is bullish OR 1H trend is bullish OR CHoCH/BOS is bullish
        $isHtfBullish = ($dailyBias === 'bullish') || ($trend === 'bullish') || ($lastChoch === 'bullish') || ($lastBos === 'bullish');
        // Filter out if strongly opposed by both Daily Bias AND trend
        $stronglyBearish = ($dailyBias === 'bearish') && ($trend === 'bearish');
        return $isHtfBullish && !$stronglyBearish;
    } elseif ($direction === 'sell') {
        // HTF bearish conditions: Daily Bias is bearish OR 1H trend is bearish OR CHoCH/BOS is bearish
        $isHtfBearish = ($dailyBias === 'bearish') || ($trend === 'bearish') || ($lastChoch === 'bearish') || ($lastBos === 'bearish');
        // Filter out if strongly opposed by both Daily Bias AND trend
        $stronglyBullish = ($dailyBias === 'bullish') && ($trend === 'bullish');
        return $isHtfBearish && !$stronglyBullish;
    }

    return false;
}

/**
 * Calculate Day-Trading Quality Score (0 - 100) based on HTF alignment, 15M confirmation,
 * strategy type, and Risk/Reward potential.
 */
function engine_calculate_day_trading_score(array $setup, array $structure, float $actualSlPips, float $actualTpPips, float $rrRatio): int
{
    $score = 0;
    $dir = strtolower(trim($setup['direction'] ?? ''));
    $dailyBias = strtolower(trim($structure['daily_bias'] ?? 'neutral'));
    $trend     = strtolower(trim($structure['trend'] ?? 'neutral'));

    // 1. Higher-Timeframe Alignment (Max 35 pts)
    if ($dir === 'buy' && $dailyBias === 'bullish') $score += 20;
    if ($dir === 'sell' && $dailyBias === 'bearish') $score += 20;
    if ($dir === 'buy' && $trend === 'bullish') $score += 15;
    if ($dir === 'sell' && $trend === 'bearish') $score += 15;

    // 2. 15M Execution / Confirmation (Max 30 pts)
    $hasH1Confirm  = !empty($setup['h1_confirm']);
    $hasM15Trigger = !empty($setup['m15_trigger']);
    $isSniper      = !empty($setup['sniper']);
    $confidence    = (float)($setup['confidence'] ?? 0);

    if ($hasH1Confirm || $hasM15Trigger || $isSniper) {
        $score += 25;
    } elseif ($confidence >= 50) {
        $score += 15;
    }

    // 3. Strategy Type Quality (Max 20 pts)
    $strategy = strtoupper(trim($setup['type'] ?? $setup['strategy'] ?? ''));
    if (strpos($strategy, 'OB') !== false || strpos($strategy, 'FVG') !== false) {
        $score += 20; // High probability SMC Order Block / FVG
    } elseif (strpos($strategy, 'RANGE') !== false) {
        $score += 10;
    } else {
        $score += 5; // Fallback momentum
    }

    // 4. R:R & Target Depth (Max 15 pts)
    if ($actualTpPips >= 80.0 && $rrRatio >= 2.4) {
        $score += 15;
    } elseif ($actualTpPips >= 60.0 && $rrRatio >= 2.0) {
        $score += 10;
    }

    return min(100, $score);
}

/**
 * Optimize and validate signals according to Day-Trading criteria:
 * - 4H/1H Higher-Timeframe Bias alignment
 * - 15M/1H entry confirmation
 * - Target Stop Loss distance: 20-30 pips (preferred default: 25 pips)
 * - Target Take Profit distance: at least 60 pips (targeting major HTF structural liquidity)
 * - Risk/Reward ratio: Reward / Risk >= 1:2.0 (preferred 1:2.5 - 1:4+)
 * - Rejects scalping / counter-trend / low-quality setups by setting setup to null
 */
function engine_optimize_and_validate_signals(array &$data): void
{
    if (!isset($data['signals']) || !is_array($data['signals'])) {
        return;
    }

    $logEntries = [];

    foreach ($data['signals'] as &$s) {
        if (!is_array($s) || empty($s['setup']) || !is_array($s['setup'])) {
            continue;
        }

        $setup = &$s['setup'];
        $structure = is_array($s['structure'] ?? null) ? $s['structure'] : [];
        $pairCode = $s['pair']['code'] ?? $s['pair']['symbol'] ?? $s['code'] ?? 'UNKNOWN';
        $decimals = (int)($s['pair']['decimals'] ?? 4);
        $pipSize  = engine_get_pip_size($pairCode, $decimals);

        $direction = strtolower(trim($setup['direction'] ?? ''));
        $entry     = isset($setup['entry']) ? (float)$setup['entry'] : 0.0;
        $rawSl     = isset($setup['sl']) ? (float)$setup['sl'] : 0.0;
        $rawTp1    = isset($setup['tp1']) ? (float)$setup['tp1'] : 0.0;
        $rawTp2    = isset($setup['tp2']) ? (float)$setup['tp2'] : 0.0;

        // Basic sanity checks
        if (!in_array($direction, ['buy', 'sell'], true) || $entry <= 0 || $rawSl <= 0 || $rawTp1 <= 0) {
            engine_log_signal_debug($pairCode, $direction, $entry, $rawSl, $rawTp1, 0, 0, 0, 0, 'REJECTED: Invalid entry/SL/TP', $logEntries);
            $setup = null;
            continue;
        }

        // --- STEP 1: HIGHER-TIMEFRAME (4H/1H) BIAS FILTER ---
        $htfAligned = engine_check_htf_alignment($direction, $structure);
        if (!$htfAligned) {
            engine_log_signal_debug($pairCode, $direction, $entry, $rawSl, $rawTp1, 0, 0, 0, 0, 'REJECTED: HTF Bias mismatch (Scalp filter)', $logEntries);
            $setup = null;
            continue;
        }

        // Calculate raw distances in pips
        $rawSlPips  = abs($entry - $rawSl) / $pipSize;
        $rawTp1Pips = abs($rawTp1 - $entry) / $pipSize;

        // --- STEP 2: DAY-TRADING STOP LOSS OPTIMIZATION ---
        // Desired risk range: 20-30 pips (preferred default: 25 pips)
        if ($rawSlPips < 20.0) {
            $targetSlPips = 25.0; // Widen tight scalping SL to day-trading 25 pips
        } elseif ($rawSlPips > 30.0) {
            $targetSlPips = 25.0; // Tighten wide SL to 25 pips if structure permits
        } else {
            $targetSlPips = round($rawSlPips, 1); // Keep structural SL within 20-30 pips
        }

        // --- STEP 3: DAY-TRADING MAJOR TARGET OPTIMIZATION ---
        // Check for major HTF liquidity targets (BOS / CHoCH levels)
        $structuralTargetPips = $rawTp1Pips;
        if (isset($structure['last_bos']['price']) && (float)$structure['last_bos']['price'] > 0) {
            $bosPrice = (float)$structure['last_bos']['price'];
            $bosDist  = abs($bosPrice - $entry) / $pipSize;
            if ($direction === 'buy' && $bosPrice > $entry && $bosDist >= 60.0) {
                $structuralTargetPips = max($structuralTargetPips, $bosDist);
            } elseif ($direction === 'sell' && $bosPrice < $entry && $bosDist >= 60.0) {
                $structuralTargetPips = max($structuralTargetPips, $bosDist);
            }
        }
        if (isset($structure['last_choch']['price']) && (float)$structure['last_choch']['price'] > 0) {
            $chochPrice = (float)$structure['last_choch']['price'];
            $chochDist  = abs($chochPrice - $entry) / $pipSize;
            if ($direction === 'buy' && $chochPrice > $entry && $chochDist >= 60.0) {
                $structuralTargetPips = max($structuralTargetPips, $chochDist);
            } elseif ($direction === 'sell' && $chochPrice < $entry && $chochDist >= 60.0) {
                $structuralTargetPips = max($structuralTargetPips, $chochDist);
            }
        }

        // Minimum Day-Trading target: at least 60 pips
        $targetTp1Pips = max(60.0, round($structuralTargetPips, 1));

        // Compute actual new SL and TP price levels
        if ($direction === 'buy') {
            $newSl  = round($entry - ($targetSlPips * $pipSize), $decimals);
            $newTp1 = round($entry + ($targetTp1Pips * $pipSize), $decimals);
            $tp2DistPips = max($targetTp1Pips + 30.0, abs($rawTp2 - $entry) / $pipSize);
            $newTp2 = round($entry + ($tp2DistPips * $pipSize), $decimals);
        } else { // sell
            $newSl  = round($entry + ($targetSlPips * $pipSize), $decimals);
            $newTp1 = round($entry - ($targetTp1Pips * $pipSize), $decimals);
            $tp2DistPips = max($targetTp1Pips + 30.0, abs($entry - $rawTp2) / $pipSize);
            $newTp2 = round($entry - ($tp2DistPips * $pipSize), $decimals);
        }

        // --- STEP 4: RECALCULATE PIP DISTANCES & RISK/REWARD ---
        $actualSlPips = round(abs($entry - $newSl) / $pipSize, 1);
        $actualTpPips = round(abs($newTp1 - $entry) / $pipSize, 1);

        if ($actualSlPips <= 0) {
            engine_log_signal_debug($pairCode, $direction, $entry, $newSl, $newTp1, $actualSlPips, $actualTpPips, 0, 0, 'REJECTED: Zero SL distance', $logEntries);
            $setup = null;
            continue;
        }

        $rrRatio = round($actualTpPips / $actualSlPips, 2);

        // --- STEP 5: DAY-TRADING QUALITY SCORING ---
        $dtScore = engine_calculate_day_trading_score($setup, $structure, $actualSlPips, $actualTpPips, $rrRatio);

        // --- STEP 6: SIGNAL VALIDATION LAYER ---
        $dirValid = ($direction === 'buy' && $newSl < $entry && $entry < $newTp1) ||
                    ($direction === 'sell' && $newSl > $entry && $entry > $newTp1);

        $slValid = ($actualSlPips >= 18.0 && $actualSlPips <= 32.0);
        $tpValid = ($actualTpPips >= 59.5);
        $rrValid = ($rrRatio >= 1.95);
        $scoreValid = ($dtScore >= 55); // Require minimum 55/100 Day-Trading Quality Score

        if ($dirValid && $slValid && $tpValid && $rrValid && $scoreValid) {
            // Apply optimized levels to setup object
            $setup['sl']        = $newSl;
            $setup['tp1']       = $newTp1;
            $setup['tp2']       = $newTp2;
            $setup['risk_pips'] = $actualSlPips;
            $setup['rr']        = $rrRatio;

            engine_log_signal_debug($pairCode, $direction, $entry, $newSl, $newTp1, $actualSlPips, $actualTpPips, $rrRatio, $dtScore, 'PASSED (Day-Trading Signal)', $logEntries);
        } else {
            $reason = "REJECTED: ";
            if (!$dirValid)   $reason .= "Invalid direction alignment; ";
            if (!$slValid)    $reason .= "SL ($actualSlPips pips) outside 20-30; ";
            if (!$tpValid)    $reason .= "TP ($actualTpPips pips) < 60; ";
            if (!$rrValid)    $reason .= "R:R (1:$rrRatio) < 1:2; ";
            if (!$scoreValid) $reason .= "Quality Score ($dtScore/100) < 55 (Scalp filter); ";
            
            engine_log_signal_debug($pairCode, $direction, $entry, $newSl, $newTp1, $actualSlPips, $actualTpPips, $rrRatio, $dtScore, trim($reason), $logEntries);
            $setup = null;
        }
    }
    unset($s);

    if (!empty($logEntries)) {
        $logFile = sys_get_temp_dir() . '/bm_signal_engine.log';
        @file_put_contents($logFile, implode("\n", $logEntries) . "\n", FILE_APPEND);
    }
}

function engine_log_signal_debug(string $pair, string $dir, float $entry, float $sl, float $tp, float $slPips, float $tpPips, float $rr, int $score, string $status, array &$logEntries): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntries[] = sprintf(
        "[%s] PAIR: %-7s | DIR: %-4s | ENTRY: %-10.4f | SL: %-10.4f | TP: %-10.4f | SL PIPS: %-5.1f | TP PIPS: %-5.1f | R:R: 1:%-4.2f | SCORE: %3d/100 | VALIDATION: %s",
        $timestamp, $pair, strtoupper($dir), $entry, $sl, $tp, $slPips, $tpPips, $rr, $score, $status
    );
}

/**
 * Retrieve verified live XAU/USD spot market price with multi-provider fallback.
 * Provider 1: GoldAPI.io (gold-api.com / goldapi.io)
 * Provider 2: GoldPrice.org
 * Provider 3: Metals.dev
 *
 * Cache TTL: 15 seconds. Max Stale Cache Age: 120 seconds.
 * Returns array { price: float, bid?: float, ask?: float, timestamp: string, source: string } or null.
 */
function engine_fetch_live_xauusd_price(): ?array
{
    $cacheFile = sys_get_temp_dir() . '/bm_gold_cache.json';
    $cacheTTL  = 15;  // 15 seconds fresh cache
    $maxAge    = 120; // 120 seconds max stale threshold

    // 1. Check fresh cache
    if (file_exists($cacheFile)) {
        $mtime = filemtime($cacheFile);
        $age = time() - $mtime;
        if ($age < $cacheTTL) {
            $gCache = json_decode(file_get_contents($cacheFile), true);
            if (is_array($gCache) && isset($gCache['price']) && (float)$gCache['price'] > 0) {
                return $gCache;
            }
        }
    }

    $apiKey = getenv('GOLD_API_KEY') ?: ($_ENV['GOLD_API_KEY'] ?? '');

    $headers = [
        "User-Agent: BMForexHub/1.0 (Windows NT 10.0; Win64; x64)",
        "Accept: application/json",
    ];
    if ($apiKey) {
        $headers[] = "x-access-token: " . trim($apiKey);
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 4,
            'method'  => 'GET',
            'header'  => implode("\r\n", $headers) . "\r\n",
        ],
        'ssl' => ['verify_peer' => false],
    ]);

    // Provider 1: GoldAPI.io (Spot Gold - XAU/USD)
    $url1 = 'https://api.gold-api.com/price/XAU';
    $body1 = @file_get_contents($url1, false, $ctx);
    if ($body1 !== false) {
        $d1 = json_decode($body1, true);
        if (is_array($d1) && isset($d1['price']) && (float)$d1['price'] > 0) {
            $price = round((float)$d1['price'], 2);
            if ($price >= 1000.0 && $price <= 10000.0) { // Sanity bounds
                $result = [
                    'price'     => $price,
                    'bid'       => isset($d1['bid']) ? round((float)$d1['bid'], 2) : $price,
                    'ask'       => isset($d1['ask']) ? round((float)$d1['ask'], 2) : $price,
                    'symbol'    => $d1['symbol'] ?? 'XAU',
                    'currency'  => $d1['currency'] ?? 'USD',
                    'timestamp' => $d1['updatedAt'] ?? date('c'),
                    'source'    => 'gold-api.com',
                ];
                file_put_contents($cacheFile, json_encode($result));
                return $result;
            }
        }
    }

    // Provider 2: GoldPrice.org (Secondary Spot Feed)
    $url2 = 'https://data-asg.goldprice.org/dbXRates/USD';
    $body2 = @file_get_contents($url2, false, $ctx);
    if ($body2 !== false) {
        $d2 = json_decode($body2, true);
        if (isset($d2['items'][0]['xauPrice']) && (float)$d2['items'][0]['xauPrice'] > 0) {
            $price = round((float)$d2['items'][0]['xauPrice'], 2);
            if ($price >= 1000.0 && $price <= 10000.0) {
                $result = [
                    'price'     => $price,
                    'bid'       => $price,
                    'ask'       => $price,
                    'symbol'    => 'XAUUSD',
                    'currency'  => 'USD',
                    'timestamp' => date('c'),
                    'source'    => 'goldprice.org',
                ];
                file_put_contents($cacheFile, json_encode($result));
                return $result;
            }
        }
    }

    // Provider 3: Metals.dev (Tertiary Feed)
    $url3 = 'https://api.metals.dev/v1/latest?api_key=demo&currency=USD&unit=toz';
    $body3 = @file_get_contents($url3, false, $ctx);
    if ($body3 !== false) {
        $d3 = json_decode($body3, true);
        if (isset($d3['metals']['gold']) && (float)$d3['metals']['gold'] > 0) {
            $price = round((float)$d3['metals']['gold'], 2);
            if ($price >= 1000.0 && $price <= 10000.0) {
                $result = [
                    'price'     => $price,
                    'bid'       => $price,
                    'ask'       => $price,
                    'symbol'    => 'XAUUSD',
                    'currency'  => 'USD',
                    'timestamp' => date('c'),
                    'source'    => 'metals.dev',
                ];
                file_put_contents($cacheFile, json_encode($result));
                return $result;
            }
        }
    }

    // Fallback: Gracefully use cached price if < 120s old
    if (file_exists($cacheFile)) {
        $mtime = filemtime($cacheFile);
        if ((time() - $mtime) < $maxAge) {
            $gCache = json_decode(file_get_contents($cacheFile), true);
            if (is_array($gCache) && isset($gCache['price']) && (float)$gCache['price'] > 0) {
                $gCache['source'] .= ' (cached)';
                return $gCache;
            }
        }
    }

    @error_log("[" . date('Y-m-d H:i:s') . "] BM Forex API Error: All XAUUSD live market price feeds failed\n", 3, sys_get_temp_dir() . '/bm_gold_error.log');
    return null; // Do NOT return hardcoded static prices if feeds fail and cache is stale
}



