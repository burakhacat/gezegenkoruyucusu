<?php
$C = require __DIR__ . '/config.php';

function db($C) {
  $dir = dirname($C['db']);
  if (!is_dir($dir)) { mkdir($dir, 0750, true); }
  $p = new PDO('sqlite:' . $C['db']);
  $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $p->exec('CREATE TABLE IF NOT EXISTS rec(t TEXT NOT NULL, k TEXT NOT NULL, d INTEGER NOT NULL, j TEXT NOT NULL, u INTEGER NOT NULL, PRIMARY KEY(t,k))');
  $p->exec('CREATE TABLE IF NOT EXISTS cfg(k TEXT PRIMARY KEY, v TEXT)');
  return $p;
}
function cfg($p) {
  $r = ['dongu' => 1, 'aktif' => true];
  foreach ($p->query('SELECT k,v FROM cfg') as $x) {
    if ($x['k'] === 'dongu') $r['dongu'] = (int)$x['v'];
    if ($x['k'] === 'aktif') $r['aktif'] = ($x['v'] === '1');
  }
  return $r;
}
function setcfg($p, $k, $v) {
  $s = $p->prepare('INSERT OR REPLACE INTO cfg(k,v) VALUES(?,?)');
  $s->execute([$k, (string)$v]);
}
function clean($v, $d = 0) {
  if ($d > 6) return null;
  if (is_array($v)) {
    $o = []; $n = 0;
    foreach ($v as $k => $x) {
      if (++$n > 120) break;
      $o[is_int($k) ? $k : substr((string)$k, 0, 40)] = clean($x, $d + 1);
    }
    return $o;
  }
  if (is_string($v)) return mb_substr($v, 0, 600);
  if (is_int($v) || is_float($v) || is_bool($v) || $v === null) return $v;
  return null;
}
function out($a, $code = 200) {
  http_response_code($code);
  echo json_encode($a, JSON_UNESCAPED_UNICODE);
  exit;
}
function cors($C) {
  header('Access-Control-Allow-Origin: ' . $C['allow_origin']);
  header('Access-Control-Allow-Headers: Content-Type');
  header('Content-Type: application/json; charset=utf-8');
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
}
