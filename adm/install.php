<?php
if (file_exists('../.config.php'))
{
	header('Location: index.php');
	exit;
}

require_once('../lib/Savant3.php');
$tpl = new Savant3();

$TEMPLATE = 'kyrie';

$tpldir = '../tpl/' . $TEMPLATE . '/';

$tpl->addPath('template', $tpldir);
$tpl->tpldir = $tpldir;

$tpl->mysqluser = "";
$tpl->mysqlpass = "";
$tpl->mysqlhost = "";
$tpl->mysqlport = "";
$tpl->mysqldb = "";

$tpl->manageruser = "";
$tpl->managerpass = "";
$tpl->managerpassrep = "";

$tpl->sitename = "";
$tpl->intro = "";
$tpl->aboutme = "";

if ((isset($_POST['action'])) && ($_POST['action'] == "execute"))
{
	$error = "";

	$tpl->mysqluser = $_POST['mysqluser'];
	$tpl->mysqlpass = $_POST['mysqlpass'];
	$tpl->mysqlhost = $_POST['mysqlhost'];
	$tpl->mysqlport = $_POST['mysqlport'];
	$tpl->mysqldb = $_POST['mysqldb'];
	
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
		$db = mysql_connect($_POST['mysqlhost'] . (!empty($_POST['mysqlport'])) ? ":" . $_POST['mysqlport'] : "",
					$_POST['mysqluser'], $_POST['mysqlpass']);
		if ($db === FALSE)
		{
			$error .= " - The specified database information is invalid. :(\n";
		}
		else
		{
			$query = mysql_query("CREATE DATABASE IF NOT EXISTS " . mysql_real_escape_string($_POST['mysqldb']));
			$dbselect = mysql_select_db(mysql_real_escape_string($_POST['mysqldb']));
	
			if (($query === FALSE) || ($dbselect === FALSE))
			{
				$error .= " - Couldn't connect to the specified database name. Do you have the right permissions?\n";
			}
			else
			{
				$queries[] = "
					CREATE TABLE IF NOT EXISTS config (
						opt	VARCHAR	(32)	NOT NULL	PRIMARY KEY			,
						value	TEXT		NOT NULL
					) CHARSET=UTF8
				";
			
				$queries[] = "
					CREATE TABLE IF NOT EXISTS songs (
						id	INT		NOT NULL	PRIMARY KEY	AUTO_INCREMENT	,
						artist	VARCHAR	(64)	NOT NULL					,
						title	VARCHAR	(128)	NOT NULL					,
						descr	TEXT		NOT NULL					,
						fname	VARCHAR	(128)	NOT NULL
					) CHARSET=UTF8
				";
	
				$username = mysql_real_escape_string($_POST['manageruser']);
				$password = mysql_real_escape_string(md5(sha1($_POST['managerpass'])));
				$sitename = mysql_real_escape_string($_POST['sitename']);
				$intro    = mysql_real_escape_string($_POST['intro']);
				$aboutme  = mysql_real_escape_string($_POST['aboutme']);
				$theme    = mysql_real_escape_string('kyrie');

				$queries[] = "
					INSERT INTO config VALUES
						('username', '{$username}'),
						('password', '{$password}'),
						('sitename', '{$sitename}'),
						('introduction', '{$intro}'),
						('aboutme', '{$aboutme}'),
						('theme', '{$theme}')
					ON DUPLICATE KEY UPDATE value=VALUES(value)
				";
				$break = FALSE;
				foreach ($queries AS $query)
				{
					$result = mysql_query($query);
					if ($result === FALSE)
					{
						$error .= "Couldn't create tables! Uh oh.";
						$break = TRUE;
						break;
					}
				}
				if ($break !== TRUE)
				{
$config = '<?php
$db = array(
	"user" => "' . $_POST['mysqluser'] . '",
	"pass" => "' . $_POST['mysqlpass'] . '",
	"host" => "' . $_POST['mysqlhost'] . '",
	"port" => "' . $_POST['mysqlport'] . '",
	"name" => "' . $_POST['mysqldb'] . '",
);
?>';
					if (($fd = @fopen('../.config.php', 'w')) !== FALSE)
					{
						fwrite($fd, $config);
					}
					else $tpl->configtext = htmlspecialchars($config);

				}
			}
		}
	}


	if (empty($error)) $file = 'install-complete.tpl.php';
	else { $tpl->installerror = $error; $file = 'install.tpl.php'; }
}
else $file = 'install.tpl.php';

$tpl->display('adm/' . $file);
