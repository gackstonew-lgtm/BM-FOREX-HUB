<?php
$cacheFile = sys_get_temp_dir() . '/bm_gold_cache.json';
file_put_contents($cacheFile, json_encode(['price' => 4360.00, 'source' => 'market_reference']));
echo "Gold cache updated to 4360.00 at $cacheFile\n";
