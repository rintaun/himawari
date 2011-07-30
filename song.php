<?php
$base = preg_replace('!/song.php$!i', '', preg_replace("!^{$_SERVER['DOCUMENT_ROOT']}!i", '', $_SERVER['SCRIPT_FILENAME']));
if (!$sid = preg_replace("!^{$base}/song/(.+)!i", "\\1", $_SERVER['REQUEST_URI']))
	die();
echo "Song ID: " . $sid;
