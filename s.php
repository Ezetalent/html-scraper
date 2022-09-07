<?php
require_once 'App.php';

$u = $_GET['u'] ?? false;
if (!$u) die('no url');
// $u = $_GET['u'] ?? 'https://bestwebcreator.com/cryptoking-landing-page/rtl/demo/index-blue.html';
// die($u);

// $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
// //Basically adding headers to the request
// $context = stream_context_create($opts);

// print_r(file_get_contents($u));
// $c = new Utils;
// $x = $c->CURL($u);
// print_r(htmlentities($x));

// die;
$s = new App($u);

$srt = '<script src="assets/js/vendors/jquery-3.6.0.min.js"></script>';
$jq = 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
$jqs = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';

// preg_match('#<script\s+src=[\'"]([\w\-.\/]+jquery[\w\-.\/]+)[\'"]#', $srt, $m);
// replace with capturing brackets
// $m = preg_replace('#(<script\s+src=[\'"])([\w\-.\/]+jquery[\w\-.\/]+)([\'"])#', '$1' . $jq . '$2', $srt);

// one time replace
// $m = preg_replace('/<script\s+src=[\'"]?[\w\-.\/]+jquery[\w\-.\/]+[\'"]?/', $jqs, $srt);

// print_r($m);

// die;

// $s->assets('', '');
$ut = new Utils;
// die(json_encode(parse_url('assets/images/logo.png')));

// print_r($str = @file_get_contents("https://bestwebcreator.com/cryptoking-landing-page/rtl/demo/assets/images/favicon.png"));

// $h = 'src="https://www.googletagmanager.com/gtag/js?id=UA-106310707-1"';
// $h = 'src=https://www.googletagmanager.com/gtag/js?id=UA-106310707-1>';

// $h = 'style="background-image:url(assets/images/banner_1.jpg)">';

// preg_match_all('/background-image:(\s)*url\(["\']?(\.)*([.\w\/\?\-])+["\']?\)/U', $h, $m);
// dj($m);
// $u = 'https://livedemo00.template-help.com/wt_60047_v4/starbis/index.html';
// $u = 'https://livedemo00.template-help.com/wt_60047_v4/starbis/css/style.css';



// $f = file_get_contents('https://livedemo00.template-help.com/wt_60047_v4/starbis/css/style.css', false, $s->context());
// $f = file_get_contents('https://livedemo00.template-help.com/wt_60047_v4/starbis/css/../fonts/fl-bigmug-line.eot', false, $s->context());
// $f = file_get_contents('https://livedemo00.template-help.com/wt_60047_v4/starbis/fonts/fl-bigmug-line.eot', false, $s->context());
// $f = $ut->CURL($u);

// print_r($f);
// $x = mkdir(__DIR__ . '/ext/ert/ee/../dev', 0777, true);
// dj([__DIR__ . 'ext/ert/ee/../dev']);

// $str = file_get_contents(__DIR__ . '/temps/livedemo00.template-help.com/wt_60047_v4/starbis-audit-attent/index.html');
// $s->addCssIm($str, 'xx', 1);
// $s->assets('', '');

$s->scrapeMain($u);

// $s->css = ['assets/css/style.css'];

// $s->parseCss();
// $s->ParseIcons();
// $s->parseCss('assets/css/style.css');
// $s->cssColors();
// dj($s->hex3_hex6('#fff'));
// $s->parseIcons();
