<?php
/**
 * BM Forex Hub — AI Assistant Configuration
 */

return [
    'enabled' => true,
    'bot_name' => 'BM Forex Hub AI',
    'personality' => 'professional, educational, encouraging',
    'welcome_message' => "Welcome to BM Forex Hub! 👋 How can I assist you with Forex trading education, account setup, deposits, withdrawals, or platform features today?",
    'suggested_questions' => [
        "How do I register?",
        "How do I deposit?",
        "What is BM Forex Hub?",
        "Explain Forex Trading",
        "What is an Order Block?",
        "What is Smart Money Concepts?",
        "Explain Fair Value Gaps",
        "Membership Plans",
        "Contact Support"
    ],
    'fallback_message' => "I'm the BM Forex Hub AI Assistant. I specialize in BM Forex Hub services, Forex trading, and trading education. For questions outside these topics, please use a general AI assistant.",
    'escalation_message' => "It looks like this requires assistance from our support team.",
    'whatsapp_url' => "https://wa.me/message/K5RM7MSWXBNPC1",
    'max_history_length' => 20,
    'rate_limit_per_minute' => 30,
];
