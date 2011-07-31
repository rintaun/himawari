<?php
header('Vary: Accept');
if (isset($_SERVER['HTTP_ACCEPT']) &&
	(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
	header('Content-type: application/json');
} else {
	header('Content-type: text/plain');
}

if (!file_exists('../dat/.db')) die('{}');
session_start();
if ((!isset($_SESSION['loggedin'])) || ($_SESSION['loggedin'] !== true)) die('{}');

require_once("../lib/Savant3/resources/Markdown.php");
if (empty($_GET)) die('{}');

$db = sqlite_open('../dat/.db');

switch ($_GET['action'])
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
		//at the end, we need to return JSON with information about the upload
		//[{"name":"picture1.jpg","size":902604,"url":"\/\/example.org\/files\/picture1.jpg","thumbnail_url":"\/\/example.org\/thumbnails\/picture1.jpg","delete_url":"\/\/example.org\/upload-handler?file=picture1.jpg","delete_type":"DELETE"}]
		//Note that the response should always be a JSON array even if only one file is uploaded.
		//see: https://github.com/blueimp/jQuery-File-Upload/wiki/Setup
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
