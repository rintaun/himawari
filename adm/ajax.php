<?php
if (!file_exists('../dat/.db')) die('{}');
require_once("../lib/Savant3/resources/Markdown.php");
if (empty($_GET)) die('{}');

$db = sqlite_open('../dat/.db');

switch ($_GET['action'])
{
	case 'editintro':
		$queries[] = "UPDATE config SET value='".$_GET['introtext']."' WHERE opt='introduction'";
		$queries[] = "UPDATE config SET value='".$_GET['introtitle']."' WHERE opt='lang_intro'";
		foreach ($queries AS $sql)
		{
			//sqlite_exec($db, $sql);
		}
		die('{"title":"'.$_GET['introtitle'].'", "text":"'.addcslashes(Markdown($_GET['introtext']),"\"\r\n").'"}');
	case 'editabout':
		$queries[] = "UPDATE config SET value='".$_GET['introtext']."' WHERE opt='aboutme'";
		$queries[] = "UPDATE config SET value='".$_GET['introtitle']."' WHERE opt='lang_about'";
		foreach ($queries AS $sql)
		{
			//sqlite_exec($db, $sql);
		}
		die('{"title":"'.$_GET['abouttitle'].'", "text":"'.addcslashes(Markdown($_GET['abouttext']),"\"\r\n").'"}');
		break;

	case 'uploadsong':
		break;
	case 'editsongs':
		break;
	//case 'removesong':
	//	break;
	
	case 'addlink':
		break;
	case 'editlinks':
		break;
	//case 'removelink':
	//	break;
	
	default: die('{}');
	
}
