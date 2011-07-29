<?php

if (file_exists('../dat/.db'))
{
	header('Location: index.php');
	exit;
}

session_start();

require_once('../lib/Savant3.php');
$tpl = new Savant3();

$TEMPLATE = 'kyrie';
$tpl->addPath('template', '../tpl/' . $TEMPLATE . '/');

$tpl->manageruser = "";
$tpl->managerpass = "";
$tpl->managerpassrep = "";

$tpl->sitename = "";
$tpl->intro = "";
$tpl->aboutme = "";

if ((isset($_POST['action'])) && ($_POST['action'] == "execute"))
{
	
	$error = "";
	
	$tpl->manageruser = $_POST['manageruser'];
	$tpl->managerpass = $_POST['managerpass'];
	$tpl->managerpassrep = $_POST['managerpassrep'];
	
	$tpl->sitename = $_POST['sitename'];
	$tpl->intro = $_POST['intro'];
	$tpl->aboutme = $_POST['aboutme'];
	
	
	if ($_POST['managerpass'] != $_POST['managerpassrep'])
	{
		$tpl->managerpass = "";
		$tpl->managerpassrep = "";

		$error .= " - The Manager passwords do not match!\n";
	
	}
	else
	{
		$db = sqlite_open('../dat/.db');
		
		if ($db === FALSE)
		{
			$error .= " - Could not write /dat/!\n";
		}
		else
		{
			$queries[] = "CREATE TABLE config (opt TEXT NOT NULL PRIMARY KEY, value TEXT NOT NULL)";
			$queries[] = "CREATE TABLE songs (id INTEGER NOT NULL PRIMARY KEY, artist TEXT NOT NULL, title TEXT NOT NULL, descr TEXT NOT NULL, fname TEXT NOT NULL)";
			$queries[] = "CREATE TABLE links (id INTEGER NOT NULL PRIMARY KEY, url TEXT NOT NULL, name TEXT NOT NULL, alt TEXT)";

			$username = sqlite_escape_string($_POST['manageruser']);
			$password = sqlite_escape_string(sha1(md5($_POST['managerpass'])));
			$sitename = sqlite_escape_string($_POST['sitename']);
			$intro    = sqlite_escape_string($_POST['intro']);
			$aboutme  = sqlite_escape_string($_POST['aboutme']);
			$theme    = sqlite_escape_string('kyrie');
			
			$queries[] = "INSERT INTO config VALUES	('username', '{$username}')";
			$queries[] = "INSERT INTO config VALUES	('password', '{$password}')";
			$queries[] = "INSERT INTO config VALUES	('sitename', '{$sitename}')";
			$queries[] = "INSERT INTO config VALUES	('introduction', '{$intro}')";
			$queries[] = "INSERT INTO config VALUES	('aboutme', '{$aboutme}')";
			$queries[] = "INSERT INTO config VALUES	('theme', '{$theme}')";
			
			$queries[] = "INSERT INTO config VALUES ('lang_intro', 'Introduction')";
			$queries[] = "INSERT INTO config VALUES ('lang_about', 'About Me')";
			$queries[] = "INSERT INTO config VALUES ('lang_songs', 'Songs')";
			
			$queries[] = "INSERT INTO links (url, name) VALUES ('http://www.example.com', 'Example Link')";
			$queries[] = "INSERT INTO links (url, name) VALUES ('http://www.example.com', 'Another Example Link')";
			$queries[] = "INSERT INTO links (url, name) VALUES ('http://www.example.com', 'Another Example Link!')";
			$queries[] = "INSERT INTO links (url, name) VALUES ('http://www.example.com', 'This one has a much longer name.')";

			$break = FALSE;
			foreach ($queries AS $query)
			{
				$result = sqlite_exec($db, $query);
				if ($result === FALSE)
				{
					$error .= "Couldn't create tables! Uh oh.";
					$break = TRUE;
					break;
				}
			}
		}
	}

	if (empty($error))
	{
		$file = 'install-complete.tpl.php';
		$_SESSION['loggedin'] = true;
	}
	else { $tpl->installerror = $error; $file = 'install.tpl.php'; }
}
else $file = 'install.tpl.php';

$tpl->display('adm/' . $file);
