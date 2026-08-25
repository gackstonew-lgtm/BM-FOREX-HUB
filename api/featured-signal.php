<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$supabaseUrl = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM5ODM1NjEsImV4cCI6MjA5OTU1OTU2MX0.cKpLKN-Azoj7UUUp3_uodYraYlJH4fQtKpyRitnbMgk';

$url = $supabaseUrl . '/rest/v1/featured_signals?select=*&status=eq.published&order=created_at.desc&limit=1';

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    'apikey: ' . $supabaseKey,
    'Authorization: Bearer ' . $supabaseKey,
    'Content-Type: application/json',
  ],
  CURLOPT_TIMEOUT => 10,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 200 && $code < 300) {
  $data = json_decode($resp, true);
  echo json_encode(['success' => true, 'data' => $data[0] ?? null]);
} else {
  echo json_encode(['success' => false, 'error' => 'Failed to fetch signal']);
}
