<?php
/**
 * Legacy Adapter: Fingo Pay Configuration
 * Redirects and aliases to kora-config.php for backward compatibility.
 */
require_once __DIR__ . '/kora-config.php';

// Maintain constant alias if legacy scripts reference FINGO_API_BASE
if (!defined('FINGO_API_BASE')) {
    define('FINGO_API_BASE', KORA_API_BASE);
}
