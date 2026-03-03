<?php

$key = 'base64keytest';
$reservation_id = 123;

$payload = base64_encode(json_encode([
    'reservation_id' => $reservation_id,
    'token' => hash_hmac('sha256', $reservation_id, $key),
]));

echo "Payload (base64): {$payload}\n";

$decoded = base64_decode($payload, true);
echo "Decoded raw: {$decoded}\n";

$arr = json_decode($decoded, true);
var_dump($arr);

// Raw JSON test
$raw = json_encode([
    'reservation_id' => $reservation_id,
    'token' => hash_hmac('sha256', $reservation_id, $key),
]);

echo "Raw JSON: {$raw}\n";
$try = json_decode($raw, true);
var_dump($try);

// Simulate controller detection logic
$payloads = [
    $payload,
    $raw,
    "not-base64-or-json",
];

foreach ($payloads as $p) {
    $d = @base64_decode($p, true);
    if ($d !== false && json_decode($d, true) !== null) {
        echo "Controller: detected base64\n";
        var_dump(json_decode($d, true));
    } else {
        echo "Controller: treating as raw JSON or invalid base64\n";
        var_dump(json_decode($p, true));
    }
}
