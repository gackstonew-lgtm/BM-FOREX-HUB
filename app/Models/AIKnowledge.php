<?php
/**
 * BM Forex Hub — AI Knowledge Base Model
 */

class AIKnowledge {
    private static function getPdo() {
        $dbDir = __DIR__ . '/../../storage/db';
        if (!file_exists($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        $sqliteFile = $dbDir . '/ai_system.sqlite';
        $pdo = new PDO('sqlite:' . $sqliteFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::initDbTables($pdo);
        return $pdo;
    }

    private static function initDbTables(PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_knowledge (
                id TEXT PRIMARY KEY,
                category TEXT NOT NULL DEFAULT 'general',
                question TEXT NOT NULL,
                answer TEXT NOT NULL,
                keywords TEXT DEFAULT '',
                published INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at INTEGER DEFAULT 0,
                updated_at INTEGER DEFAULT 0
            );
        ");

        // Seed default knowledge if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM ai_knowledge");
        if ($stmt->fetchColumn() == 0) {
            self::seedDefaults($pdo);
        }
    }

    private static function seedDefaults(PDO $pdo) {
        $now = time();
        $defaults = [
            // Company & Services
            [
                'category' => 'company',
                'question' => 'What is BM Forex Hub?',
                'answer' => "BM Forex Hub is an elite Forex trading academy and signal provider operating in Africa. We specialize in institutional trading education using Smart Money Concepts (SMC) and ICT techniques, market analysis, trading tools, and community support.",
                'keywords' => 'bm forex hub, about, company, services, academy, signals, who are you'
            ],
            [
                'category' => 'account',
                'question' => 'How do I register or create an account on BM Forex Hub?',
                'answer' => "To register an account on BM Forex Hub, click the 'Create Account' button on our website or navigate directly to register.php. Enter your preferred username, email address, password, and complete phone number to create your free member profile.",
                'keywords' => 'register, create account, signup, join, new user'
            ],
            [
                'category' => 'account',
                'question' => 'How do I verify my account or complete KYC?',
                'answer' => "Account verification is simple! Sign in to your dashboard at login.php, navigate to your Profile & Verification section, and submit your valid photo ID or documentation. You can also view our full verification policy on our AML & KYC Policy page (aml-kyc.php).",
                'keywords' => 'verify, verification, kyc, aml, document, id proof'
            ],
            [
                'category' => 'payments',
                'question' => 'How do I deposit funds or purchase a subscription plan?',
                'answer' => "To make a deposit or purchase a VIP membership plan: Log in to your member dashboard (index.php), navigate to the Payments / Subscription section, select your desired tier, and follow the instant payment prompts (supporting M-Pesa, Mobile Money, Cards, and Cryptocurrencies via Kora Pay).",
                'keywords' => 'deposit, pay, subscription, buy, purchase, mpesa, kora, crypto, pricing'
            ],
            [
                'category' => 'payments',
                'question' => 'How do I withdraw funds or request a payout?',
                'answer' => "Withdrawals can be requested directly from your user dashboard under the Wallet / Withdrawals panel. Select your preferred payout method (Mobile Money or Bank/Crypto), enter the amount, and submit your request. Processing is handled securely within standard verification SLA times.",
                'keywords' => 'withdraw, withdrawal, payout, cashout, money back'
            ],
            [
                'category' => 'plans',
                'question' => 'What membership plans are available at BM Forex Hub?',
                'answer' => "BM Forex Hub offers flexible tiers for traders: Free Community Tier (market updates & basic educational articles), VIP Signal & Mentorship Tier (daily high-probability SMC/ICT trade setups, live webinars, weekly market breakdowns), and Masterclass Mentorship Tier.",
                'keywords' => 'plans, pricing, vip, membership, tiers, cost, subscription'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'What is Smart Money Concepts (SMC)?',
                'answer' => "Smart Money Concepts (SMC) is a trading framework that tracks institutional money (central banks, hedge funds, major financial institutions) entering and exiting financial markets. Key elements include Liquidity Pools, Order Blocks, Fair Value Gaps (FVG), Break of Structure (BOS), and Change of Character (CHOCH).",
                'keywords' => 'smc, smart money concepts, smart money, institutional trading'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'What is an Order Block (OB)?',
                'answer' => "An Order Block (OB) is a specific price candle or cluster of candles where institutional market participants placed heavy buy or sell orders prior to a strong directional market movement. In bullish SMC, a bullish Order Block is the last down candle before an aggressive upward break of structure.",
                'keywords' => 'order block, ob, bullish order block, bearish order block'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'What is a Fair Value Gap (FVG)?',
                'answer' => "A Fair Value Gap (FVG) is a 3-candle price imbalance created when price moves violently in one direction, leaving a space between Candle 1's high/low and Candle 3's low/high. Smart money often returns to fill this imbalance before continuing the trend.",
                'keywords' => 'fvg, fair value gap, imbalance, inefficiency, 3 candle pattern'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'What is BOS and CHOCH in Forex trading?',
                'answer' => "BOS (Break of Structure) occurs when price breaks and closes beyond a previous swing high in an uptrend or swing low in a downtrend, confirming trend continuation. CHOCH (Change of Character) is an early signal of trend reversal when price breaks the key swing high/low that created the recent extreme.",
                'keywords' => 'bos, choch, break of structure, change of character, market structure'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'What is Risk Management and Position Sizing in Forex?',
                'answer' => "Risk management is the cornerstone of profitable trading. Never risk more than 1% to 2% of your capital per trade. Calculate your Lot Size based on: Lot Size = (Account Risk Amount $) / (Stop Loss in Pips × Pip Value). Always place a Stop Loss (SL) before placing any trade.",
                'keywords' => 'risk management, position size, lot size, stop loss, risk per trade, pips'
            ],
            [
                'category' => 'trading_concepts',
                'question' => 'How do I calculate Pips in Forex and Gold (XAUUSD)?',
                'answer' => "In Forex standard pairs (e.g. EURUSD, GBPUSD), 1 pip = 0.0001 movement ($10 per standard lot). In JPY pairs, 1 pip = 0.01 movement. In Gold (XAUUSD), a $1.00 move equals 100 pips (or 10 points), meaning a move from 2400.00 to 2401.00 is a 10-pip / 100-tick move.",
                'keywords' => 'pip, pips, calculation, xauusd, gold, pip value'
            ],
            [
                'category' => 'contact',
                'question' => 'How can I contact BM Forex Hub support?',
                'answer' => "You can reach our official team via WhatsApp Chat at https://wa.me/254785618608, visit our Telegram Channel at https://t.me/bmforexhubafrica, or send an inquiry from our Contact Us page (contact.php).",
                'keywords' => 'contact, support, help, whatsapp, telegram, phone, email'
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO ai_knowledge (id, category, question, answer, keywords, published, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)
        ");

        $i = 1;
        foreach ($defaults as $d) {
            $id = 'kn_' . bin2hex(random_bytes(8));
            $stmt->execute([
                $id,
                $d['category'],
                $d['question'],
                $d['answer'],
                $d['keywords'],
                $i++,
                $now,
                $now
            ]);
        }
    }

    public static function getAll($category = null, $publishedOnly = false) {
        $pdo = self::getPdo();
        $sql = "SELECT * FROM ai_knowledge WHERE 1=1";
        $params = [];
        if ($category && $category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        if ($publishedOnly) {
            $sql .= " AND published = 1";
        }
        $sql .= " ORDER BY sort_order ASC, created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM ai_knowledge WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create($category, $question, $answer, $keywords = '', $published = 1) {
        $pdo = self::getPdo();
        $id = 'kn_' . bin2hex(random_bytes(8));
        $now = time();
        $stmt = $pdo->prepare("
            INSERT INTO ai_knowledge (id, category, question, answer, keywords, published, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)
        ");
        $stmt->execute([
            $id,
            trim($category),
            trim($question),
            trim($answer),
            trim($keywords),
            (int)$published,
            $now,
            $now
        ]);
        return $id;
    }

    public static function update($id, $category, $question, $answer, $keywords = '', $published = 1) {
        $pdo = self::getPdo();
        $now = time();
        $stmt = $pdo->prepare("
            UPDATE ai_knowledge
            SET category = ?, question = ?, answer = ?, keywords = ?, published = ?, updated_at = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($category),
            trim($question),
            trim($answer),
            trim($keywords),
            (int)$published,
            $now,
            $id
        ]);
    }

    public static function delete($id) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("DELETE FROM ai_knowledge WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function togglePublished($id) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("UPDATE ai_knowledge SET published = CASE WHEN published = 1 THEN 0 ELSE 1 END, updated_at = ? WHERE id = ?");
        return $stmt->execute([time(), $id]);
    }
}
