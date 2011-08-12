<?php
$_CONFIG['DB_POPULATE'] = true;
require_once('config.inc.php');

if (!$_ENV['INSTALLED']) die("You need to <a href='adm/install.php'>install himawari</a> first!");

$tpl->songlist = $_ENV['DB_DATA']['songs'];
$tpl->config = $_ENV['DB_DATA']['config'];
$tpl->links = $_ENV['DB_DATA']['links'];

$tpl->title = (isset($_ENV['DB_DATA']['config']['sitename'])) ? $_ENV['DB_DATA']['config']['sitename'] : '';
$tpl->introduction = (isset($_ENV['DB_DATA']['config']['introduction'])) ? $_ENV['DB_DATA']['config']['introduction'] : '';
$tpl->about = (isset($_ENV['DB_DATA']['config']['aboutme'])) ? $_ENV['DB_DATA']['config']['aboutme'] : '';

$tpl->display('index.tpl.php');