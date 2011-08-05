<?php
/**
 * Global configuration setup
 * @author Matthew Lanigan <rintaun@gmail.com>
 * @copyright Copyright (c) 2011 Matthew J. Lanigan
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
// we store everything we need in $_ENV, so first, empty it out.
$_ENV = array();

// sometimes PATH_INFO doesn't exist, so set it.
$re = '!^' . $_SERVER['SCRIPT_NAME'] . '(.*)' . (isset($_SERVER['QUERY_STRING']) &&  !empty($_SERVER['QUERY_STRING']) ? '\?' . $_SERVER['QUERY_STRING'] : '') . '$!i';
$_SERVER['PATH_INFO'] = preg_replace($re, '\1', $_SERVER['REQUEST_URI']);

// then fill it with relevant stuff.

/** Alias of $_SERVER['REQUEST_METHOD'].
 * The request method used to access the page (e.g. GET, HEAD, POST).
 * @global string $_ENV['METHOD']
 */
$_ENV['METHOD']  = $_SERVER['REQUEST_METHOD'];

/** Boolean translation of $_SERVER['HTTPS'].
 * Whether the request was sent over HTTPS or not.
 * @global bool $_ENV['SECURE']
 */
$_ENV['SECURE']  = !empty($_SERVER['HTTPS']) ? true : false;

/** Alias of $_SERVER['SERVER_NAME'].
 * The current domain name or virtual host.
 * @global string $_ENV['DOMAIN']
 */
$_ENV['DOMAIN']  = $_SERVER['SERVER_NAME'];

/** Alias of $_SERVER['SERVER_PORT'] sanitized to integer.
 * The port used to connect to the server. May vary based on server and protocol.
 * @global integer $_ENV['PORT']
 */
$_ENV['PORT']    = intval($_SERVER['SERVER_PORT']);

/** Alias of $_SERVER['REQUEST_URI'].
 * The entire path and query string used in the request.
 * @global string $_ENV['REQUEST']
 */
$_ENV['REQUEST'] = $_SERVER['REQUEST_URI'];

/** Alias of $_SERVER['SCRIPT_NAME'].
 * The full path of the executing script on the current domain.
 * @global string $_ENV['SCRIPT']
 */
$_ENV['SCRIPT']  = $_SERVER['SCRIPT_NAME'];

/** Alias of $_SERVER['PATH_INFO'] sanitized for consistency.
 * Anything in the request after the script name and before the query string in the requested URL.
 * @global string $_ENV['PATH']
 */
$_ENV['PATH']    = $_SERVER['PATH_INFO'];

/** Alias of $_SERVER['QUERY_STRING'].
 * The query string portion of the requested url (everything after the first ?).
 * @global string $_ENV['QUERY_STRING']
 */
$_ENV['QUERY']   = $_SERVER['QUERY_STRING'];
	
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


/*
 *
 */
echo "<pre>" . print_r($_ENV,1);