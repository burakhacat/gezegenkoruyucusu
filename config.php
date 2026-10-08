<?php
// ZORUNLU: admin_key değerini uzun ve tahmin edilemez bir metinle değiştirin.
// Değiştirilmediği sürece admin.php hiçbir isteği kabul etmez.
return [
  'admin_key'    => 'DEGISTIR_BENI',
  'db'           => __DIR__ . '/data/veri.sqlite',
  // Yalnızca kendi siteniz izin verilsin: örn. 'https://siteniz.com'  ('*' = herkes)
  'allow_origin' => '*',
];
