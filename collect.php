<?php
// Öğrenci tarayıcılarından anonim kayıt alır (herkese açık uç nokta).
require __DIR__ . '/ortak.php';
cors($C);
$p = db($C);
$cf = cfg($p);

if (isset($_GET['cfg'])) out(['ok' => true] + $cf);
if (!$cf['aktif']) out(['ok' => true, 'aktif' => false, 'dongu' => $cf['dongu']]);

$raw = file_get_contents('php://input', false, null, 0, 262144);
$j = json_decode($raw, true);
if (!is_array($j)) out(['ok' => false, 'hata' => 'gecersiz'], 400);

$tabs = ['oturumlar' => 'S', 'gorevler' => 'T', 'mini_etkinlikler' => 'M'];
$sel  = $p->prepare('SELECT d FROM rec WHERE t=? AND k=?');
$selS = $p->prepare("SELECT d FROM rec WHERE t='S' AND k=?");
$ins  = $p->prepare('INSERT OR REPLACE INTO rec(t,k,d,j,u) VALUES(?,?,?,?,?)');
$n = 0;
$p->beginTransaction();
foreach ($tabs as $name => $t) {
  $rows = (isset($j[$name]) && is_array($j[$name])) ? array_slice($j[$name], 0, 150) : [];
  foreach ($rows as $r) {
    if (!is_array($r)) continue;
    $sid = (string)($r['sid'] ?? '');
    if (!preg_match('/^[A-Za-z0-9]{6,24}$/', $sid)) continue;
    if ($t === 'S') {
      $k = $sid;
    } elseif ($t === 'T') {
      $kk = (string)($r['k'] ?? '');
      if (!preg_match('/^[a-z0-9_]{1,24}$/', $kk)) continue;
      $k = $sid . '|' . $kk;
    } else {
      $k = $sid . '|' . substr((string)($r['tur'] ?? ''), 0, 30) . '|' . substr((string)($r['i'] ?? ''), 0, 20);
    }
    // Dönem: oturumun ilk kaydedildiği andaki sunucu dönemi (istemci değeri yok sayılır)
    $sel->execute([$t, $k]);
    $d = $sel->fetchColumn();
    if ($d === false) {
      if ($t === 'S') { $d = $cf['dongu']; }
      else {
        $selS->execute([$sid]);
        $d = $selS->fetchColumn();
        if ($d === false) $d = $cf['dongu'];
      }
    }
    $r = clean($r);
    $r['dongu'] = (int)$d;
    $ins->execute([$t, $k, (int)$d, json_encode($r, JSON_UNESCAPED_UNICODE), time()]);
    $n++;
  }
}
$p->commit();
out(['ok' => true, 'n' => $n, 'dongu' => $cf['dongu'], 'aktif' => true]);
