<?php
$cacheFile = sys_get_temp_dir() . '/bm_gold_cache.json';
if (file_exists($cacheFile)) {
    echo "CACHE: " . file_get_contents($cacheFile) . " (mtime: " . date('Y-m-d H:i:s', filemtime($cacheFile)) . ")\n";
} else {
    echo "No cache file found at: $cacheFile\n";
}

$ctx = stream_context_create([
    'http' => [
        'timeout' => 5,
        'header'  => "User-Agent: Mozilla/5.0\r\nAccept: application/json\r\n",
    ],
    'ssl' => ['verify_peer' => false],
]);

echo "Testing gold-api.com...\n";
$res1 = @file_get_contents('https://api.gold-api.com/price/XAU', false, $ctx);
echo "gold-api.com: " . ($res1 !== false ? $res1 : 'FAILED') . "\n";

echo "Testing goldprice.org...\n";
$res2 = @file_get_contents('https://data-asg.goldprice.org/dbXRates/USD', false, $ctx);
echo "goldprice.org: " . ($res2 !== false ? substr($res2, 0, 200) : 'FAILED') . "\n";

echo "Testing metals.dev...\n";
$res3 = @file_get_contents('https://api.metals.dev/v1/latest?api_key=demo&currency=USD&unit=toz', false, $ctx);
echo "metals.dev: " . ($res3 !== false ? substr($res3, 0, 200) : 'FAILED') . "\n";
