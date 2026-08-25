<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cacheFile = sys_get_temp_dir() . '/bm_news_cache.json';
$cacheTTL  = 900; // 15 minutes

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
  echo file_get_contents($cacheFile);
  exit;
}

$ctx = stream_context_create([
  'http' => ['timeout' => 8, 'header' => "User-Agent: Mozilla/5.0\r\n"],
  'ssl'  => ['verify_peer' => true],
]);

$sources = [
  'https://nfs.faireconomy.media/ff_calendar_thisweek.json',
];

foreach ($sources as $url) {
  $body = @file_get_contents($url, false, $ctx);
  if ($body === false) continue;

  $d = json_decode($body, true);
  if (!$d || !is_array($d)) continue;

  file_put_contents($cacheFile, json_encode($d));
  echo json_encode($d);
  exit;
}

echo json_encode([]);
