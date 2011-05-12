<?php
if ((parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != 
	$_SERVER['SERVER_NAME']) || (empty($_SERVER['HTTP_REFERER'])))
{
	// this isn't a hotlink
	$filename = $_SERVER['DOCUMENT_ROOT'] . $_GET['f'];

	// we need to do some validation on the above filename, 
	// otherwise it's really dangerous, heh.
}
// otherwise, it was hotlinked. woo! give them goaste or something
else $filename = "hl.gif";

// first we need to go through and get the mime type of our $filename
// if we have the Finfo functions, use them
if (function_exists('finfo_open'))
{
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $filename);
	finfo_close($finfo);
}
// but if we don't, we'll have to use this deprecated function for compat
else if (function_exists('mime_content_type'))
	$mime = mime_content_type($filename);

// and if THAT doesn't exist, then try to guess based on the extension... :/
else
{
	switch (substr($filename,-4))
	{
		case 'jpeg':
		case '.jpg': $mime = 'image/jpeg'; break;
		case '.gif': $mime = 'image/gif'; break;
		case '.png': $mime = 'image/png'; break;
		case '.mp3': $mime = 'audio/mpeg'; break;
		default: $mime = 'application/octet-stream';
	}
}


// now send the header
header('Content-Type: ' . $mime);

// aaand NOW we go through and print the file out.
$fd = fopen($filename, 'r');
while ($data = fread($fd, 256))
	print $data;
