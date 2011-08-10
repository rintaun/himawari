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

if (!$_ENV['installed']) die('{}');

if ((!isset($_SESSION['loggedin'])) || ($_SESSION['loggedin'] !== true)) die('{}');

require_once("../lib/Savant3/resources/Markdown.php");
if (empty($_REQUEST)) die('{}');

$db = sqlite_open('../dat/.db');

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

	case 'uploadsong':
		break;
	case 'editsongs':
		//not really sure what i'm doing with this yet...
		break;
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
		sqlite_exec($db, $query, $error) or die($query . " " . $error);
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
