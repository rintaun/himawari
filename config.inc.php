<?php
/** Global configuation setup
 * NOTE: This file *must* be in the base directory
 * @author Matthew Lanigan <rintaun@gmail.com>
 * @copyright Copyright (c) 2011 Matthew J. Lanigan
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

ini_set('memory_limit', '96M');
ini_set('short_open_tag', 1);
// we store everything we need in $_ENV, so first, empty it out.
$_ENV = array();

/** Configuration defaults
 * 
 * Valid entries:
 *  - BASE_MOD string Path to the base url root for the software
 *  - DB_POPULATE boolean Whether we should populate the database or not
 *  - DATA_DIRNAME string The name of the data directory
 *  - LIB_DIRNAME string The name of the library directory
 *  - TEMPLATE_DIRNAME string The name of the template directory
 *
 * Setting values in $_CONFIG prior to including config.inc.php
 * overrides these defaults.
 *
 * @global array$_ENV['CONFIG_DEFAULTS']
 */
$_ENV['CONFIG_DEFAULTS'] = array(
	'BASE_MOD'     => '',
	'DB_POPULATE'  => false,
	'DATA_DIRNAME' => 'dat',
	'LIB_DIRNAME' => 'lib',
	'TEMPLATE_DIRNAME'  => 'tpl',
);
$_CONFIG = array_merge($_ENV['CONFIG_DEFAULTS'], (isset($_CONFIG) && is_array($_CONFIG) ? $_CONFIG : array()));

/** Enables or disables debug mode.
 * @global boolean $_ENV['DEBUG']
 */
$_ENV['DEBUG'] = true;

/** Enables or disables error display. Overridden by $_ENV['DEBUG'].
 * @global boolean $_ENV['SHOW_ERRORS']
 */
$_ENV['SHOW_ERRORS'] = false;

/** Sets the default PHP error level. Overridden by $_ENV['DEBUG'].
 * @global integer $_ENV['ERROR_LEVEL']
 */
$_ENV['ERROR_LEVEL'] = E_ALL & ~E_NOTICE;

error_reporting($_ENV['ERROR_LEVEL']);
if ($_ENV['DEBUG'])
{
	error_reporting(E_STRICT | E_ALL);
	ini_set('display_errors', 1);
	ini_set('html_errors', 1);
	ini_set('docref_root', 'http://php.net/manual/');
	ini_set('docref_ext', '.php');
}
elseif ($_ENV['SHOW_ERRORS']) ini_set('display_errors', 'on');
else ini_set('display_errors', 0);

/** Whether PHP is running on windows or not
 * @global boolean $_ENV['WINDOWS']
 */
$_ENV['WINDOWS'] = strpos(PHP_OS, 'WIN') !== FALSE ? true : false;

/** The current PHP version identifier
 * @global integer $_ENV['PHP_VERSION']
 */
$version = explode('.', PHP_VERSION);
$_ENV['PHP_VERSION'] = (defined('PHP_VERSION_ID') ? PHP_VERSION_ID : ($version[0] * 10000 + $version[1] * 100 + $version[2]));

if ($_ENV['PHP_VERSION'] < 50000) trigger_error('PHP version is not PHP5 or greater', E_USER_ERROR);

if (!extension_loaded('sqlite') && (!function_exists('dl') || !get_ini('enable_dl') || !@dl((PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '') . 'sqlite.' . PHP_SHLIB_SUFFIX)))
	trigger_error('SQLite extension is not loaded', E_USER_ERROR);

/* FILE INCLUDES */

require_once('lib/functions.inc.php');
require_once('lib/pur/pur.inc.php');

/* END FILE INCLUDES */

// sometimes PATH_INFO doesn't exist, so set it.
/*
$re = '!^' . $_SERVER['SCRIPT_NAME'] . '(.*)' . (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '\?' . $_SERVER['QUERY_STRING'] : '') . '$!i';
$_SERVER['PATH_INFO'] = preg_replace($re, '\1', $_SERVER['REQUEST_URI']);
*/
$_SERVER['PATH_INFO'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
// then fill it with relevant stuff.

/** Alias of $_SERVER['REQUEST_METHOD'].
 * The request method used to access the page (e.g. GET, HEAD, POST).
 * @global string $_ENV['METHOD']
 */
$_ENV['METHOD'] = $_SERVER['REQUEST_METHOD'];

/** Boolean translation of $_SERVER['HTTPS'].
 * Whether the request was sent over HTTPS or not.
 * @global bool $_ENV['SECURE']
 */
$_ENV['SECURE'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? true : false;

/** Alias of $_SERVER['SERVER_NAME'].
 * The current domain name or virtual host.
 * @global string $_ENV['DOMAIN']
 */
$_ENV['DOMAIN'] = $_SERVER['SERVER_NAME'];

/** Alias of $_SERVER['SERVER_PORT'] sanitized to integer.
 * The port used to connect to the server. May vary based on server and protocol.
 * @global integer $_ENV['PORT']
 */
$_ENV['PORT'] = intval($_SERVER['SERVER_PORT']);

/** Alias of $_SERVER['REQUEST_URI'].
 * The entire path and query string used in the request.
 * @global string $_ENV['REQUEST']
 */
$_ENV['REQUEST'] = $_SERVER['REQUEST_URI'];

/** Alias of $_SERVER['SCRIPT_NAME'].
 * The full path of the executing script on the current domain.
 * @global string $_ENV['SCRIPT']
 */
$_ENV['SCRIPT'] = $_SERVER['SCRIPT_NAME'];

/** Himawari's base directory on HTTP.
 * @global string $_ENV['BASE_URL'];
 */
$_ENV['BASE_URL'] = PurPath::clean(substr($_ENV['SCRIPT'], 0, strrpos($_ENV['SCRIPT'], "/")+1) . (isset($_CONFIG['BASE_MOD']) ? $_CONFIG['BASE_MOD'] : ''));

/** Alias of $_SERVER['PATH_INFO'] sanitized for consistency.
 * Anything in the request after the script name and before the query string in the requested URL.
 * @global string $_ENV['PATH']
 */
$_ENV['PATH'] = $_SERVER['PATH_INFO'];

/** Alias of $_SERVER['QUERY_STRING'].
 * The query string portion of the requested url (everything after the first ?).
 * @global string $_ENV['QUERY_STRING']
 */
$_ENV['QUERY'] = $_SERVER['QUERY_STRING'];
	
if (!empty($_ENV['PATH']) && strlen($_ENV['PATH']) > 1)
{
	$path = $_ENV['PATH'];
	if (substr($path,0,1) == '/') $path = substr($path,1);
	if (substr($path,-1) == '/') $path = substr($path,0,-1);
	$chunks = explode('/', $path);
	$pathvars = array();

	foreach ($chunks AS $var)
	{
		if (empty($var)) continue;
		if (strpos($var, ':') === FALSE)
			$pathvars[$var] = $var;
		else
		{
			$key = strtok($var, ':');
			$value = strtok(':');
			$pathvars[$key] = $value;
		}
	}
}

/** Sanitization of $_ENV['PATH'] into $_GET-like array.
 * Example: /varname:varvalue/ would become Array('varname' => 'varvalue')
 * @global array $_ENV['PATHVARS']
 */
$_ENV['PATHVARS'] = isset($pathvars) ? $pathvars : array();

/** Default template when there is no database or no template entry.
 * @global string $_ENV['TEMPLATE']
 */
$_ENV['TEMPLATE'] = 'kyrie';

/** Himawari's base directory on the filesystem
 * @global string $_ENV['BASE_PATH']
 */
$_ENV['BASE_PATH'] = PurPath::clean(dirname(__FILE__));

/** The current script's directory
 * @global string $_ENV['CURRENT_PATH']
 */
$_ENV['CURRENT_PATH'] = getcwd();

/** The data directory, where the database and uploaded files are stored.
 * @global string $_ENV['DATA_DIR']
 */
$_ENV['DATA_DIR'] = $_ENV['BASE_PATH'] . '/' . $_CONFIG['DATA_DIRNAME'] . '/';

if ((!@is_dir($_ENV['DATA_DIR']) && !mkdir($_ENV['DATA_DIR'])) || !is_writable($_ENV['DATA_DIR']))
	trigger_error("DATA_DIR ({$_ENV['DATA_DIR']}) does not exist or is not writable.", E_USER_ERROR);

/** The URL to the data directory
 * @global string $_ENV['DATA_URL']
 */
$_ENV['DATA_URL'] = $_ENV['BASE_URL'] . $_CONFIG['DATA_DIRNAME'] . '/';

/** The library directory, where software libraries are stored.
 * @global string $_ENV['LIB_DIR']
 */
$_ENV['LIB_DIR'] = $_ENV['BASE_PATH'] . '/' . $_CONFIG['LIB_DIRNAME'] . '/';

/** The URL to the data directory
 * @global string $_ENV['DATA_URL']
 */
$_ENV['LIB_URL'] = $_ENV['BASE_URL'] . $_CONFIG['LIB_DIRNAME'] . '/';
	
/** The directory where templates are stored
 * @global string $_ENV['TEMPLATE_DIR']
 */
$_ENV['TEMPLATE_DIR'] = $_ENV['BASE_PATH'] . '/' . $_CONFIG['TEMPLATE_DIRNAME'] . '/';

/** The URL to the template directory
 * @global string $_ENV['TEMPLATE_URL']
 */ 
$_ENV['TEMPLATE_URL'] = $_ENV['BASE_URL'] . $_CONFIG['TEMPLATE_DIRNAME'] . '/';


/** The filename for the database. Will be stored in $_ENV['DATA_DIR'].
 * @global string $_ENV['DB_FILE']
 */
$_ENV['DB_FILE'] = $_ENV['DATA_DIR'] . '.db';

/** The resource handle for the database.
 * @global resource $_ENV['DB']
 * @name $db
 */
$_ENV['DB'] = sqlite_open($_ENV['DB_FILE']);
$db = &$_ENV['DB'];

/** Array of database data
 * @global array $_ENV['DB_DATA']
 */
$_ENV['DB_DATA'] = array();

/** Whether Himawari is installed or not
 * @global boolean $_ENV['INSTALLED']
 */
$_ENV['INSTALLED'] = @sqlite_query($_ENV['DB'], 'SELECT * FROM config LIMIT 1') !== FALSE ? true : false;

if ($_ENV['INSTALLED'] && $_CONFIG['DB_POPULATE'])
{
	
	$tables = array('config', 'songs', 'links');
	foreach ($tables AS $table)
	{
		$_ENV['DB_DATA'][$table] = array();
		$sql = "SELECT * FROM {$table}";
		$result = sqlite_query($db, $sql, SQLITE_ASSOC) or trigger_error('could not get data from table `'.$table.'`', E_USER_ERROR);
		while ($row = sqlite_fetch_array($result))
			switch ($table) {
				case 'config':
					$_ENV['DB_DATA'][$table][$row['opt']] = $row['value'];
					break;
				case 'songs':
					$row['url'] = encodeSource($_ENV['DATA_URL'] . $row['fname']);
					$_ENV['DB_DATA'][$table][$row['id']] = $row;
					
					break;
				case 'links':
					$_ENV['DB_DATA'][$table][$row['id']] = $row;
					break;
				default:
					break;
			}
			
	}
}

if (!session_start()) trigger_error('could not start session', E_USER_ERROR);

/** Whether the user is logged in or not
 * @global boolean $_ENV['LOGGED_IN']
 */
$_ENV['LOGGED_IN'] = ((isset($_SESSION['loggedin'])) && ($_SESSION['loggedin'] === true));

require_once($_ENV['LIB_DIR'] . 'Savant3.php');

/** The template engine
 * @global Savant3 $_ENV['TEMPLATE_ENGINE']
 * @name $tpl
 */
$_ENV['TEMPLATE_ENGINE'] = new Savant3();
$tpl = $_ENV['TEMPLATE_ENGINE'];

$tpl->addPath('template', $_ENV['TEMPLATE_DIR'] . $_ENV['TEMPLATE'] . '/');