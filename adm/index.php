<?php
if (!file_exists('../.config.php'))
{
        header('Location: install.php');
        exit;
}

require_once('../.config.php');

$conn = mysql_connect($db['host'] . (!empty($db['port']) ? ":" . $db['port'] : ""), $db['user'], $db['pass']);
mysql_select_db($db['name'], $conn);

$query = "SELECT * FROM config";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result))
{
	$config[$row['opt']] = $row['value'];
}

$query = "SELECT * FROM songs";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result))
{
	$songlist[$row['id']] = $row;
}

require_once('../lib/Savant3.php');
$tpl = new Savant3();


$TEMPLATE = 'kyrie';

$tpldir = '../tpl/' . $TEMPLATE . '/';

$tpl->addPath('template', $tpldir);

$tpl->tpldir = $tpldir;

$tpl->title = $config['sitename'];
$tpl->introduction = $config['introduction'];
$tpl->about = $config['aboutme'];

$tpl->songlist = $songlist;

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
