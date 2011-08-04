<?php
if (!file_exists('../dat/.db'))
{
        header('Location: install.php');
        exit;
}
session_start();
if ((!isset($_SESSION['loggedin'])) || ($_SESSION['loggedin'] !== true))
{
	header('Location: login.php');
	exit;
}

$db = sqlite_open('../dat/.db');

$query = "SELECT * FROM config";
$result = sqlite_query($db, $query);
while ($row = sqlite_fetch_array($result))
{
	$config[$row['opt']] = $row['value'];
}

$query = "SELECT * FROM songs";
$result = sqlite_query($db, $query);
while ($row = sqlite_fetch_array($result))
{
	$songlist[$row['id']] = $row;
}

$query = "SELECT * FROM links";
$result = sqlite_query($db, $query);
while ($row = sqlite_fetch_array($result))
{
	$links[$row['id']] = $row;
}

require_once('../lib/Savant3.php');
$tpl = new Savant3();


$TEMPLATE = 'kyrie';
$tpl->addPath('template', '../tpl/' . $TEMPLATE . '/');

$tpl->maxUpload = min(mtob(ini_get('upload_max_filesize')), mtob(ini_get('post_max_size')));

$tpl->songlist = (isset($songlist)) ? $songlist : array();
$tpl->config = (isset($config)) ? $config : array();
$tpl->links = (isset($links)) ? $links : array();

$tpl->title = (isset($config['sitename'])) ? $config['sitename'] : '';
$tpl->introduction = (isset($config['introduction'])) ? $config['introduction'] : '';
$tpl->about = (isset($config['aboutme'])) ? $config['aboutme'] : '';

function mtob($m)
{
	$c = substr($m,-1);
	if (is_numeric($c)) return $c * 1024 * 1024;
	$m = substr($m,0,-1);
	switch ($c)
	{
		case 'K': return $m * 1024;
		case 'M': return $m * 1024 * 1024;
		case 'G': return $m * 1024 * 1024 * 1024;
		case 'T': return $m * 1024 * 1024 * 1024 * 1024;
	}
	return intval($m) * 1024 * 1024;
}

/* shamelessly stolen from the Audio-Player wordpress plugin! */
function encodeSource($string) {
	$source = utf8_decode($string);
	$ntexto = "";
	$codekey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
	for ($i = 0; $i < strlen($string); $i++) {
		$ntexto .= substr("0000".base_convert(ord($string{$i}), 10, 2), -8);
	}
	$ntexto .= substr("00000", 0, 6-strlen($ntexto)%6);
	$string = "";
	for ($i = 0; $i < strlen($ntexto)-1; $i = $i + 6) {
		$string .= $codekey{intval(substr($ntexto, $i, 6), 2)};
	}
	
	return $string;
}

foreach ($tpl->songlist AS $key => $entry)
{
	$tpl->songlist[$key]['url'] = encodeSource('../dat/' . $tpl->songlist[$key]['fname']);
}

$tpl->display('adm/index.tpl.php');
