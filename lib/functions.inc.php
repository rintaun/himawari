<?php
/**
 * Function to encode URLs for the Audio-Player plugin,
 * shamelessly stolen from the wordpress plugin!
 * @link http://wpaudioplayer.com/ Source
 * @param string the URL (relative or absolute) to the file
 * @return string the encoded URL
 */
function encodeSource($string) {
	$source = utf8_decode($string);
	$ntexto = "";
	$codekey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
	for ($i = 0; $i < strlen($string); $i++) {
		$ntexto .= substr("0000".base_convert(ord($string{$i}), 10, 2), -8);
	}
	$ntexto .= substr("00000", 0, 6-strlen($ntexto)%6);
	$string = "";
	for ($i = 0; $i < strlen($ntexto)-1; $i = $i + 6) {
		$string .= $codekey{intval(substr($ntexto, $i, 6), 2)};
	}
	
	return $string;
}

/**
 * Function to convert strings (e.g. 64M) to bytes (e.g. 67108864)
 * @param string the size with its suffix, K, M, G, or T. Anything else, including no suffix, assumes megabytes
 * @return integer the number of bytes
 */

function toBytes($m)
{
	$c = substr($m,-1);
	if (is_numeric($c)) return $c * 1024 * 1024;
	$m = substr($m,0,-1);
	switch ($c)
	{
		case 'K': return $m * 1024;
		case 'M': return $m * 1024 * 1024;
		case 'G': return $m * 1024 * 1024 * 1024;
		case 'T': return $m * 1024 * 1024 * 1024 * 1024;
	}
	return intval($m) * 1024 * 1024;
}

/**
 * Checks the existance of any number of keys in a given array
 * @param array an array of keys to be checked
 * @param array the array to search
 * @return boolean whether all specified keys exist in the search array  
 */

function array_keys_exist(array $keys, array $search) {
	foreach ($keys AS $key)
		if (!array_key_exists($key, $search)) return false;
	return true;
}