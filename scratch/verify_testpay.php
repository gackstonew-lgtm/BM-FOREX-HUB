<?php
$_GET['amount'] = 10;
ob_start();
include __DIR__ . '/../testpay.php';
$html = ob_get_clean();

if (preg_match('/href="([^"]+checkout\.korapay\.com[^"]+)"/', $html, $m)) {
    echo "SUCCESS: Found Kora Checkout URL in testpay.php!\n";
    echo "Checkout URL: " . $m[1] . "\n";
} else {
    echo "ERROR: Checkout URL not found in HTML output\n";
    echo substr($html, 0, 500) . "\n";
}
