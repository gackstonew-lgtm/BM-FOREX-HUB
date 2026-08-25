<?php
/**
 * BM Forex Hub — AI Support & Forex Education Engine Service
 */

require_once __DIR__ . '/KnowledgeBaseService.php';
require_once __DIR__ . '/ChatRouter.php';
require_once __DIR__ . '/../Models/AIConversation.php';

class AIService {

    /**
     * Process incoming user prompt and generate domain-restricted AI response
     */
    public static function processPrompt($userPrompt, $sessionId = '', $userId = 'visitor') {
        $prompt = trim($userPrompt);
        if (empty($prompt)) {
            return [
                'reply' => "Hello! How can I assist you today with BM Forex Hub or Forex trading?",
                'escalate' => false,
                'off_topic' => false
            ];
        }

        $settings = AIConversation::getAllSettings();
        $fallbackMsg = $settings['fallback_message'];
        $escalationMsg = $settings['escalation_message'];
        $waUrl = $settings['whatsapp_url'];

        // 1. Check if AI is enabled
        if (empty($settings['enabled'])) {
            return [
                'reply' => "Our AI Assistant is currently offline for maintenance. Please reach out to our team on WhatsApp: " . $waUrl,
                'escalate' => true,
                'whatsapp_url' => $waUrl
            ];
        }

        // 2. Off-Topic Domain Check (Feature 4 Guard)
        if (self::isOffTopic($prompt)) {
            $resp = [
                'reply' => $fallbackMsg,
                'escalate' => false,
                'off_topic' => true
            ];
            AIConversation::logConversation($sessionId, $userId, $prompt, $resp['reply'], 0);
            return $resp;
        }

        // 3. Human Escalation Request Check (Feature 7)
        if (self::isEscalationRequest($prompt)) {
            $reply = $escalationMsg . " Click the button below to connect directly with our official support representative on WhatsApp.";
            $resp = [
                'reply' => $reply,
                'escalate' => true,
                'whatsapp_url' => $waUrl
            ];
            AIConversation::logConversation($sessionId, $userId, $prompt, $reply, 1);
            return $resp;
        }

        // 4. Knowledge Base Lookup (Feature 3 & 8)
        $kbMatch = KnowledgeBaseService::searchKnowledge($prompt);
        if ($kbMatch) {
            $reply = $kbMatch['answer'];
            $routeInfo = self::detectNavigationRoute($prompt);
            
            $resp = [
                'reply' => $reply,
                'escalate' => false,
                'action_route' => $routeInfo
            ];
            AIConversation::logConversation($sessionId, $userId, $prompt, $reply, 0);
            return $resp;
        }

        // 5. Intelligent Fallback for Forex & BM Forex Hub topics
        $smartReply = self::generateSmartDomainResponse($prompt);
        $escalate = $smartReply['escalate'] ?? false;

        $resp = [
            'reply' => $smartReply['text'],
            'escalate' => $escalate,
            'whatsapp_url' => $escalate ? $waUrl : null,
            'action_route' => $smartReply['route'] ?? null
        ];

        AIConversation::logConversation($sessionId, $userId, $prompt, $smartReply['text'], $escalate ? 1 : 0);
        return $resp;
    }

    /**
     * Detect off-topic queries outside Forex & BM Forex Hub domain
     */
    private static function isOffTopic($prompt) {
        $p = strtolower($prompt);

        // Keywords that confirm domain relevance
        $allowedKeywords = [
            'bm', 'forex', 'hub', 'trade', 'trader', 'trading', 'smc', 'smart money', 'ict', 'pip', 'pips', 'lot', 'leverage', 'margin',
            'order block', 'order blocks', 'fvg', 'fair value', 'fair value gap', 'bos', 'choch', 'liquidity', 'xauusd', 'gold', 'pair', 'currency',
            'register', 'account', 'login', 'deposit', 'withdraw', 'payout', 'kyc', 'verify', 'verification',
            'signal', 'signals', 'vip', 'plan', 'plans', 'price', 'pricing', 'subscription', 'course', 'mentorship', 'academy', 'education',
            'stop loss', 'take profit', 'risk', 'management', 'psychology', 'support', 'help', 'contact', 'whatsapp',
            'telegram', 'challenge', 'prop firm', 'funded', 'session', 'london', 'new york', 'asian', 'spread', 'broker',
            'candlestick', 'trend', 'resistance', 'breakout', 'mitigation', 'breaker', 'market', 'analysis', 'chart', 'concepts', 'concept'
        ];

        foreach ($allowedKeywords as $kw) {
            if (strpos($p, $kw) !== false) {
                return false; // Valid domain topic!
            }
        }

        // Explicit off-topic indicator words
        $offTopicPatterns = [
            'recipe', 'cook', 'football', 'soccer', 'nba', 'movie', 'actor', 'celebrity', 'politics', 'election',
            'president', 'weather in', 'python code', 'javascript code', 'write a function', 'sing a song', 'poem',
            'joke', 'game', 'playstation', 'crypto price of bitcoin today', 'who won'
        ];

        foreach ($offTopicPatterns as $otp) {
            if (strpos($p, $otp) !== false) {
                return true; // Off-topic!
            }
        }

        // Short generic greetings are allowed
        if (in_array($p, ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'help'])) {
            return false;
        }

        // If no trading or BM Forex Hub keywords found at all, treat as off-topic
        return true;
    }

    /**
     * Check if user is requesting human support
     */
    private static function isEscalationRequest($prompt) {
        $p = strtolower($prompt);
        $phrases = [
            'human', 'agent', 'real person', 'talk to someone', 'customer care', 'representative',
            'whatsapp support', 'speak to a person', 'live chat', 'admin support', 'call me'
        ];
        foreach ($phrases as $phrase) {
            if (strpos($p, $phrase) !== false) return true;
        }
        return false;
    }

    /**
     * Detect navigation intent
     */
    private static function detectNavigationRoute($prompt) {
        $p = strtolower($prompt);
        if (strpos($p, 'register') !== false || strpos($p, 'create account') !== false || strpos($p, 'signup') !== false) {
            return ChatRouter::getRouteLink('register');
        }
        if (strpos($p, 'login') !== false || strpos($p, 'sign in') !== false) {
            return ChatRouter::getRouteLink('login');
        }
        if (strpos($p, 'deposit') !== false || strpos($p, 'pay') !== false || strpos($p, 'buy') !== false) {
            return ChatRouter::getRouteLink('deposit');
        }
        if (strpos($p, 'withdraw') !== false || strpos($p, 'payout') !== false) {
            return ChatRouter::getRouteLink('withdraw');
        }
        if (strpos($p, 'kyc') !== false || strpos($p, 'verify') !== false) {
            return ChatRouter::getRouteLink('kyc');
        }
        if (strpos($p, 'contact') !== false || strpos($p, 'support') !== false) {
            return ChatRouter::getRouteLink('contact');
        }
        return null;
    }

    /**
     * Generate fallback response for domain queries
     */
    private static function generateSmartDomainResponse($prompt) {
        $p = strtolower($prompt);

        if (in_array($p, ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
            return [
                'text' => "Hello! Welcome to BM Forex Hub. How can I assist your Forex trading journey or account navigation today?",
                'escalate' => false
            ];
        }

        if (strpos($p, 'smc') !== false || strpos($p, 'smart money') !== false) {
            return [
                'text' => "Smart Money Concepts (SMC) focuses on identifying institutional footprints in price action. Key SMC building blocks include Order Blocks (OB), Fair Value Gaps (FVG), Liquidity Sweeps, Break of Structure (BOS), and Change of Character (CHOCH). Would you like me to explain any of these specific concepts?",
                'escalate' => false
            ];
        }

        if (strpos($p, 'order block') !== false || strpos($p, 'ob') !== false) {
            return [
                'text' => "An Order Block (OB) represents institutional supply or demand zones created by central banks and large market participants. Bullish OB = last bearish candle before a strong upward expansion. Bearish OB = last bullish candle before a strong downward drop.",
                'escalate' => false
            ];
        }

        if (strpos($p, 'fvg') !== false || strpos($p, 'fair value') !== false) {
            return [
                'text' => "A Fair Value Gap (FVG) is a 3-candle imbalance zone where price moved rapidly, leaving an inefficiency between Candle 1 and Candle 3. Institutional traders expect price to retrace into the FVG to rebalance price before continuing the move.",
                'escalate' => false
            ];
        }

        if (strpos($p, 'gold') !== false || strpos($p, 'xauusd') !== false) {
            return [
                'text' => "Gold (XAUUSD) is one of our specialty instruments at BM Forex Hub. Gold respects SMC liquidity sweeps and Fair Value Gaps exceptionally well during London and New York market sessions.",
                'escalate' => false
            ];
        }

        if (strpos($p, 'deposit') !== false || strpos($p, 'payment') !== false || strpos($p, 'mpesa') !== false) {
            return [
                'text' => "Deposits and VIP tier purchases can be made directly in your Dashboard under Payments. We support instant M-Pesa, Mobile Money, Debit/Credit Cards, and Crypto via Kora Pay.",
                'route' => ChatRouter::getRouteLink('deposit'),
                'escalate' => false
            ];
        }

        if (strpos($p, 'register') !== false || strpos($p, 'sign up') !== false) {
            return [
                'text' => "Registering on BM Forex Hub takes less than 1 minute! Simply provide your username, email, and mobile phone number.",
                'route' => ChatRouter::getRouteLink('register'),
                'escalate' => false
            ];
        }

        // General fallback within Forex domain
        return [
            'text' => "For specific account inquiries or personalized trading setup guidance, our support team is available to assist you. Would you like to connect directly on WhatsApp?",
            'escalate' => true
        ];
    }
}
