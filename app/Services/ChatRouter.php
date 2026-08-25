<?php
/**
 * BM Forex Hub — AI Chat Router for Website Navigation
 */

class ChatRouter {

    /**
     * Map intent to website page route & return action buttons if applicable
     */
    public static function getRouteLink($intent) {
        $routes = [
            'register' => [
                'url'   => 'register.php',
                'label' => 'Create Account / Register',
                'text'  => "You can create your account directly on our Registration page."
            ],
            'login' => [
                'url'   => 'login.php',
                'label' => 'Sign In to Account',
                'text'  => "Sign in to access your dashboard and VIP signals."
            ],
            'deposit' => [
                'url'   => 'index.php#payments',
                'label' => 'Go to Payments / Deposit',
                'text'  => "Navigate to your Dashboard Payments section to purchase a subscription or make a deposit."
            ],
            'withdraw' => [
                'url'   => 'index.php#withdrawals',
                'label' => 'Go to Withdrawals',
                'text'  => "Navigate to your Dashboard Wallet to submit a payout request."
            ],
            'contact' => [
                'url'   => 'contact.php',
                'label' => 'Contact Support Page',
                'text'  => "Visit our official Contact page or chat directly with our team on WhatsApp."
            ],
            'kyc' => [
                'url'   => 'aml-kyc.php',
                'label' => 'Read AML & KYC Policy',
                'text'  => "Read about our compliance and identity verification guidelines."
            ],
            'terms' => [
                'url'   => 'terms.php',
                'label' => 'Terms of Service',
                'text'  => "Review BM Forex Hub's official Terms of Service."
            ],
            'privacy' => [
                'url'   => 'privacy.php',
                'label' => 'Privacy Policy',
                'text'  => "Review our Privacy Policy and data protection standards."
            ],
        ];

        return $routes[$intent] ?? null;
    }
}
