<?php
require_once("Markdown.php");

# Savant3 Plugin by rintaun
# WOO ISN'T THIS AWESOME

class Savant3_Plugin_markdown extends Savant3_Plugin {
	public function markdown($text)
	{
		echo Markdown($text);
	}
}

?>