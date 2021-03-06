<?php
$_CONFIG['BASE_MOD'] = '..';
require_once('../config.inc.php');

header('Vary: Accept');
if (isset($_SERVER['HTTP_ACCEPT']) &&
	(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
	header('Content-type: application/json');
} else {
	header('Content-type: text/plain');
}

if (!$_ENV['INSTALLED'] || !$_ENV['LOGGED_IN']) die('{}');

require_once("../lib/Savant3/resources/Markdown.php");

if (empty($_REQUEST)) die('{}');

switch ($_REQUEST['action'])
{
	case 'editintro':
		$text = sqlite_escape_string($_GET['introtext']);
		$title = sqlite_escape_string($_GET['introtitle']);
		$queries[] = "UPDATE config SET value='{$text}' WHERE opt='introduction'";
		$queries[] = "UPDATE config SET value='{$title}' WHERE opt='lang_intro'";
		foreach ($queries AS $sql)
		{
			sqlite_exec($db, $sql) or die('{}');
		}
		die('{"title":"'.$_GET['introtitle'].'", "text":"'.addcslashes(Markdown($_GET['introtext']),"\"\r\n").'"}');
	case 'editabout':
		$text = sqlite_escape_string($_GET['abouttext']);
		$title = sqlite_escape_string($_GET['abouttitle']);
		$queries[] = "UPDATE config SET value='{$text}' WHERE opt='aboutme'";
		$queries[] = "UPDATE config SET value='{$title}' WHERE opt='lang_about'";
		foreach ($queries AS $sql)
		{
			sqlite_exec($db, $sql) or die('{}');
		}
		die('{"title":"'.$_GET['abouttitle'].'", "text":"'.addcslashes(Markdown($_GET['abouttext']),"\"\r\n").'"}');
		break;

	case 'editsong':
		if (!array_keys_exist(array('songartist','songtitle','songdesc','songid'), $_GET)) die('{}');
		$artist = sqlite_escape_string($_GET['songartist']);
		$title = sqlite_escape_string($_GET['songtitle']);
		$desc = sqlite_escape_string($_GET['songdesc']);
		$id = sqlite_escape_string($_GET['songid']);
		
		$query = "UPDATE songs SET artist='{$artist}', title='{$title}', descr='{$desc}' WHERE id='{$id}'";
		sqlite_exec($db, $query) or die('{}');
		
		$query = "SELECT * FROM songs WHERE id='{$id}'";
		$result = sqlite_query($db, $query) or die('{}');
		$data = sqlite_fetch_array($result) or die('{}');
		die('{"id":"'.$data['id'].'", "artist":"'.addslashes($data['artist']).'", "title":"'.addslashes($data['title']).'", "desc":"'.addcslashes(Markdown($data['descr']),"\"\r\n").'","url":"'.addslashes(encodeSource($_ENV['DATA_URL'] . $data['fname'])).'"}');
		break;
	case 'getsongdescr':
		if (!array_key_exists('songid', $_GET)) die('{}');
		$id = sqlite_escape_string($_GET['songid']);
		
		$query = "SELECT descr FROM songs WHERE id='{$id}'";
		$result = sqlite_query($db, $query) or die('{}');
		$data = sqlite_fetch_array($result) or die('{}');
		die('{"id":"'.$_GET['songid'].'","descr":"'.addslashes($data['descr']).'"}');
	case 'archivesong':
		if (!array_key_exists('songid', $_GET)) die('{}');
		$id = sqlite_escape_string($_GET['songid']);
		
		$query = "UPDATE songs SET active=0 WHERE id='{$id}'";
		sqlite_exec($db, $query) or die('{}');
		die('{"id":"'.$id.'"}');
	case 'removesong':
		break;
	
	case 'addlink':
		$name = sqlite_escape_string($_GET['linkname']);
		$title = sqlite_escape_string($_GET['linktitle']);
		$url = sqlite_escape_string($_GET['linkurl']);
		// this and editlinks are next, i think. should be relatively straightforward.
		$query = "INSERT INTO links (url, name, title) VALUES ('{$url}', '{$name}', '{$title}')";
		sqlite_exec($db, $query) or die('{}');
		
		$query = "SELECT last_insert_rowid()";
		$result = sqlite_query($db, $query) or die('{}');
		$id = sqlite_fetch_single($result) or die('{}');
		die('{"id":"'.$id.'", "name":"'.addslashes($_GET['linkname']).'", "title":"'.addslashes($_GET['linktitle']).'", "url":"'.addslashes($_GET['linkurl']).'"}');
		break;
	case 'editlink':
		$name = sqlite_escape_string($_GET['linkname']);
		$title = sqlite_escape_string($_GET['linktitle']);
		$url = sqlite_escape_string($_GET['linkurl']);
		$id = sqlite_escape_string($_GET['id']);
		
		$query = "UPDATE links SET url='{$url}', title='{$title}', name='{$name}' WHERE id='{$id}'";
		sqlite_exec($db, $query) or die('{}');
		die('{"id":"'.$id.'", "name":"'.addslashes($_GET['linkname']).'", "title":"'.addslashes($_GET['linktitle']).'", "url":"'.addslashes($_GET['linkurl']).'"}');
		break;
	case 'removelink':
		$id = sqlite_escape_string($_GET['id']);
		$query = "DELETE FROM links WHERE id='{$id}'";
		sqlite_exec($db, $query) or die('{}');
		die('{"id":"'.$_GET['id'].'"}');
		break;
	
	default: die('{}');
	
}
