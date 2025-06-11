<?php

$appUrl = env('APP_URL'); // أو استخدم getenv('APP_URL') لو خارج Laravel

$manifestPath = public_path('mix-manifest.json');
$manifest = json_decode(file_get_contents($manifestPath), true);

foreach ($manifest as $key => $value) {
    // تأكد من وجود "/" بين APP_URL والقيمة
    $manifest[$key] = rtrim($appUrl, '/') . '/' . ltrim($value, '/');
}

file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✅ mix-manifest.json updated successfully.";