<?php
// Yalnızca araştırmacı: dışa aktarma, döngü ayarı, sıfırlama. Yönetici anahtarı gerekir.
require __DIR__ . '/ortak.php';
cors($C);
$j = json_decode(file_get_contents('php://input', false, null, 0, 4096), true);
if (!is_array($j) || !isset($j['key']) || $C['admin_key'] === 'DEGISTIR_BENI' || !hash_equals((string)$C['admin_key'], (string)$j['key'])) {
  usleep(800000);
  out(['ok' => false, 'hata' => 'yetkisiz'], 403);
}
$p = db($C);
$a = $j['action'] ?? '';

if ($a === 'cfg') out(['ok' => true] + cfg($p));

if ($a === 'set') {
  $d = max(1, min(20, (int)($j['dongu'] ?? 1)));
  setcfg($p, 'dongu', $d);
  setcfg($p, 'aktif', !empty($j['aktif']) ? '1' : '0');
  out(['ok' => true] + cfg($p));
}

if ($a === 'export') {
  $m = ['S' => 'oturumlar', 'T' => 'gorevler', 'M' => 'mini_etkinlikler'];
  $o = ['oturumlar' => [], 'gorevler' => [], 'mini_etkinlikler' => []];
  foreach ($p->query('SELECT t,j FROM rec ORDER BY u') as $r) {
    $o[$m[$r['t']]][] = json_decode($r['j'], true);
  }
  $res = [
    'ok' => true,
    'oturumlar' => $o['oturumlar'],
    'gorevler' => $o['gorevler'],
    'mini_etkinlikler' => $o['mini_etkinlikler'],
    'sayilar' => ['oturum' => count($o['oturumlar']), 'gorev' => count($o['gorevler']), 'mini' => count($o['mini_etkinlikler'])],
  ];
  out($res + cfg($p));
}

if ($a === 'reset') {
  $s = $j['scope'] ?? 'all';
  if ($s === 'all') {
    $p->exec('DELETE FROM rec');
    setcfg($p, 'dongu', 1);
  } else {
    $st = $p->prepare('DELETE FROM rec WHERE d=?');
    $st->execute([(int)$s]);
  }
  out(['ok' => true] + cfg($p));
}

out(['ok' => false, 'hata' => 'bilinmeyen'], 400);
