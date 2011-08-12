<?php

/**
 * General static methods around files manipulation.
 */
class PurFile{
	
	/**
	 * Return a list of file paths relative to a provided path with selector and recursivity.
	 * 
	 * Options may include:
	 * - include mixed String or array of file selectors
	 * - exclude mixed String or array of file selectors
	 * - file_only boolean Return files only
	 * - dir_only boolean Return directories only
	 * - absolute boolean Return relative path including the provided base path
	 * - depth int Browsing depth where 0 means no recursion and default is infinite
	 * - no_extension boolean Remove extension from file names
	 * 
	 * The include and exclude options may be a string pattern or an array of string patterns. Patterns
	 * follow the Ant style conventions.
	 * 
	 * The source directory can be a pattern as well. In this case, it is similar to an "include" option.
	 * 
	 *     assert(
	 *         PurFile::browse('/my/path/*\/*')
	 *         ===
	 *         PurFile::browse('/my/path/',array('include'=>'*\/*'))
	 *     );
	 * 
	 * Note: if an array of directories is provided, the returned path will be absolute.
	 * 
	 * @return array File and directory matching the provided arguments
	 * @param $directory mixed(string or array) One or more directories to be traversed
	 * @params $options (optional) Options used to alter the method execution
	 */
	public static function browse($directory,$options=array()){
		// Browse each directories if a list is provided
		if(is_array($directory)){
			$result = array();
			foreach($directory as $dir){
				$result = array_merge($result,self::browse($dir,array_merge(array('absolute'=>true),$options)));
			}
			return $result;
		}
		// Sanitize include option
		if(isset($options['include'])){
			if(is_string($options['include'])) $options['include'] = array($options['include']);
			else if(!is_array($options['include'])) throw new InvalidArgumentException('Invalid Option "include": string or array expected of file selectors expected');
		}else{
			$options['include'] = array();
		}
		// Sanitize exclude option
		if(isset($options['exclude'])){
			if(is_string($options['exclude'])) $options['exclude'] = array($options['exclude']);
			else if(!is_array($options['exclude'])) throw new InvalidArgumentException('Invalid Option "exclude": string or array expected of file selectors expected');
		}else{
			$options['exclude'] = array();
		}
		// Deal with source directory containing a pattern
		$patternStar = strpos($directory,'*');
		$patternQuestionMark = strpos($directory,'?');
		if($patternStar!==false&&$patternQuestionMark!==false){
			$pattern = $patternStar<$patternQuestionMark?$patternStar:$patternQuestionMark;
		}else if($patternStar!==false){
			$pattern = $patternStar;
		}else if($patternQuestionMark!==false){
			$pattern = $patternQuestionMark;
		}
		unset($patternStar);
		unset($patternQuestionMark);
		if(isset($pattern)){
			for($i=$pattern;$i>=0;$i--){
				switch($char = $directory[$i]){
					case '/':
					case '\\':
						$pattern = $i+1;
						$break = true;
						break;
				}
				if (isset($break) && $break === true) break;
			}
			$options['include'][] = substr($directory,$pattern);
			$directory = substr($directory,0,$pattern);
			unset($pattern);
		}
		// Ready to traverse the filesystem
		if(substr($directory,-1)!='/') $directory .= '/';
		$files = array();
		$stack = array(array(0,''));
		$depth = 0;
		while(null!==list($depth,$path)=array_shift($stack)){
			if(!is_readable($directory.$path)) throw new Exception('File Not Readable: '.$directory.$path);
			if($dir = opendir($directory.$path) ){
				while($file = readdir($dir)){
					if($file!='.'&&$file!='..'){
						if(is_file($directory.$path.$file)){
							if(!empty($options['dir_only'])) continue;
							if(!empty($options['no_extension'])&&preg_match("/([^\.].*)\..*/",$file,$matches)){
								$file = $matches[1];
								unset($matches);
							}
						}else if(is_dir($directory.$path.$file)){
							if(!isset($options['depth'])||$depth<$options['depth']){
								array_push($stack,array($depth+1,$path.$file.'/'));
							}
							if(!empty($options['file_only']))continue;
						}
						if(!PurPath::match($options['include'],$options['exclude'],$path.$file)) continue;
						if(!empty($options['absolute'])){
							$files[] = $directory.$path.$file;
						}else{
							$files[] = $path.$file;
						}
					}
				}
				closedir($dir);
			}
		}
		sort($files);
		return $files;
	}
	
	/**
	 * Compress a source file or directory into a destination archive.
	 * 
	 * Options may include:
	 * -   *format*
	 *     The method used to compress the source. If not provided as an option, 
	 *     format is derived from the destination extension name.
	 * 
	 */
	public static function compress($source,$destination,array $options=array()){
		if(empty($options['format'])){
			$options['format'] = PurPath::extension($destination);
		}
		$options['base'] = $source;
		switch($options['format']){
			case 'zip';
				PurFile::mkdir(dirname($destination));
				$pwd = getcwd();
				chdir($options['base']);
				PurCli::exec('zip -r '.escapeshellarg($destination).' .');
				chdir($pwd);
				break;
			default:
				throw new Exception('Unsupported format: "'.$options['format'].'"');
		}
	}
	
	/**
	 * Extract a source achive into a destination file or directory.
	 * 
	 * Options may include:
	 * -   *format*
	 *     The method used to compress the source. If not provided as an option, 
	 *     format is derived from the destination extension name.
	 * 
	 */
	public static function extract($source,$destination,array $options=array()){
		if(empty($options['format'])){
			$options['format'] = PurPath::extension($source);
		}
		switch($options['format']){
			case 'zip';
				PurFile::mkdir($destination);
				// u: update existing files and create new ones if needed.
				PurCli::exec('unzip -u '.escapeshellarg($source).' -d '.escapeshellarg($destination));
				break;
			default:
				throw new Exception('Unsupported format: "'.$options['format'].'"');
		}
	}
	
	/**
	 * Copy a file or directory into another file or directory with optional path selectors.
	 * 
	 * By default, hidden files are copied as well. You may alter 
	 * this behavior by provided the option "exclude" with the value "**\/.*\/**"
	 * 
	 * Options may include
	 * - include mixed String or array of file selectors
	 * - exclude mixed String or array of file selectors
	 * 
	 * Exemples:
	 * 
	 * - Copy a file 'file.txt' into a non existing dir
	 * * PurFile::mkdir('/path/file.txt','path/dir/');
	 * 	
	 * - Copy the directory '/source' inside a new directory '/destination':
	 * * PurFile::mkdir('/destination');
	 * * PurFile::copy('/source','/destination');
	 *  
	 * - Copy the files and directories inside '/source' inside the directory '/destination':
	 * * PurFile::delete('/destination');
	 * * PurFile::copy('/source','/destination');
	 *  
	 * - Copy the directory '/source' inside a new directory '/destination' (notice the trailing slash):
	 * * PurFile::delete('/destination');
	 * * PurFile::copy('/source/','/destination');
	 *  
	 * - Copy all non hidden files and directories which name does not start by 'dir' followed by 1 character:
	 * * PurFile::copy($src,$dest,array('include'=>'**\/dir?\/**','exclude'=>'**\/.*\/**'));
	 * 
	 * @param string $src Source file or directory
	 * @param string $dest Destination file or directory
	 * @return boolean True an success, false otherwise
	 */
	public static function copy($src,$dest,$options=array()){
		if(!file_exists($src)) return false;
		// Sanitize include option
		if(array_key_exists('include',$options)){
			if(is_string($options['include'])) $options['include'] = array($options['include']);
			else if(!is_array($options['include'])) throw new InvalidArgumentException('Invalid Option "include": string or array expected of file selectors expected');
		}else{
			$options['include'] = array();
		}
		// Sanitize exclude option
		if(array_key_exists('exclude',$options)){
			if(is_string($options['exclude'])) $options['exclude'] = array($options['exclude']);
			else if(!is_array($options['exclude'])) throw new InvalidArgumentException('Invalid Option "exclude": string or array expected of file selectors expected');
		}else{
			$options['exclude'] = array();
		}
		if(is_dir($dest)&&is_dir($src)&&substr($src,-1,1)!='/'){
			$dest .= '/'.basename($src);
		}
		if(is_dir($src)&&!file_exists($dest)){
			PurFile::mkdir($dest,0775,true);
		}else if((substr($dest,-1,1)=='/'&&file_exists($dest))||!file_exists(dirname($dest))){
			PurFile::mkdir(substr($dest,-1,1)=='/'?$dest:dirname($dest),0775,true);
		}
		
		if(is_dir($src)){
			if(substr($src,-1)!='/') $src .= '/';
			if(substr($dest,-1)!='/') $dest .= '/';
			$stack = array('');
			while(null!==$path=array_shift($stack)){
				if($dir = opendir($src.$path) ){
					while($file = readdir($dir)){
						if($file!='.'&&$file!='..'){
							$file = $path.$file;
							if(is_file($src.$file)){
								if(!PurPath::match($options['include'],$options['exclude'],$file)) continue;
								copy($src.$file,$dest.$file);
							}else if(is_dir($src.$file)){
								array_push($stack,$file.'/');
								if(!PurPath::match($options['include'],$options['exclude'],$file)) continue;
								if(!is_dir($dest.$file)) PurFile::mkdir($dest.$file,0755);
							}
						}
					}
					closedir($dir);
				}
			}
		}else if(is_file($src)){
			copy($src,$dest.(is_dir($dest)?basename($src):''));
		}else{
			return false;
		}
		return true;
	}
	
	/**
	 * Delete a file or a directory (including its sub-directories).
	 * 
	 * Options may include:
	 * - include mixed(array or string list)
	 * - exclude mixed(array or string list)
	 * 
	 * The source path to delete may be a pattern.
	 * 
	 * Exemple to remove all files in a directory while preserving the directory
	 *     PurFile::delete('/path/to/directory/**');
	 * 
	 * @return string Path being destroyed or null if path does not exist
	 * @param string $path Path to the file or directory
	 */
	public static function delete($path,$options=array()) {
		// Sanitize include option
		if(array_key_exists('include',$options)){
			if(is_string($options['include'])) $options['include'] = array($options['include']);
			else if(!is_array($options['include'])) throw new InvalidArgumentException('Invalid Option "include": string or array expected of file selectors expected');
		}else{
			$options['include'] = array();
		}
		// Sanitize exclude option
		if(array_key_exists('exclude',$options)){
			if(is_string($options['exclude'])) $options['exclude'] = array($options['exclude']);
			else if(!is_array($options['exclude'])) throw new InvalidArgumentException('Invalid Option "exclude": string or array expected of file selectors expected');
		}else{
			$options['exclude'] = array();
		}
		if(is_file($path)){
			if(!is_readable($path)) throw new Exception('Permission Denied: '.$path);
			// Windows does not allow removal of 0444 mask
			if(DIRECTORY_SEPARATOR=='\\'){
				if(!is_writable($path)){
					chmod($path,0666);;
				}
			}
			unlink($path);
			return $path;
		}
		$options['absolute'] = true;
		try{
			$files = PurFile::browse($path,$options);
		}catch(Exception $e){
			// there is nothing to remove, just return null
			return null;
		}
		
		$files = array_reverse($files);
		while(list(,$file) = each($files)){
			if(is_file($file)){
				if(!is_readable($file)) throw new Exception('Permission Denied: '.$file);
				// Windows does not allow removal of 0444 mask
				if(DIRECTORY_SEPARATOR=='\\'){
					if(!is_writable($file)){
						chmod($file,0666);;
					}
				}
				unlink($file);
			}else if(is_dir($file)){
				rmdir($file);
			}
		}
		if(is_dir($path)){
			$handler = opendir($path);
			$found = false;
			while(false!==($file=readdir($handler))){
				if($file == "." or $file == ".."){
					continue;
				}
				$found = true;
				break;
			}
			closedir($handler);
			if(!$found) rmdir($path);
		}
		return $path;
	}

	/**
	 * Create directories and sub-directories (targeting PHP 5.1). Note,
	 * it also enforces correct permission mask on created directories.
	 * 
	 * @param string $dir Path to the directory to be created
	 * @param int $mode [optional] Permission applied to newly created directories
	 * @return mixed(string,boolean) Path to the newly created directory on success, otherwise boolean false
	 */
	public static function mkdir($dir, $mode=0755){
		$dir = PurPath::clean($dir);
		$return = $dir;
		if(is_dir($dir)) return $dir;
		$stack = array(basename($dir));
		$path = null;
		while ( ($d = dirname($dir) ) ){
			if ( !is_dir($d) ){
				$stack[] = basename($d);
				$dir = $d;
			} else{
				$path = $d;
				break;
			}
		}
	
		if ( ( $path = realpath($path) ) === false ){
			// Return false if path doesn't exist
			throw new Exception('Invalid directory '.$dir);
		}
		
		$created = array();
		for ( $n = count($stack) - 1; $n >= 0; $n-- ){
			$s = $path . '/'. $stack[$n];
			if(!is_writable(dirname($s))) throw new Exception('Permission Denied: '.$s);
			if ( !mkdir($s, $mode) ){
				for ( $m = count($created) - 1; $m >= 0; $m-- )
					rmdir($created[$m]);
				throw new Exception('Failed to create directory :"'.$s.'"');
			}
			if(!chmod($s, $mode)){
				for ( $m = count($created) - 1; $m >= 0; $m-- )
					rmdir($created[$m]);
				throw new Exception('Failed to update directory permission : "'.$mode.'" in "'.$s.'"');
			}
			$created[] = $s;	  
			$path = $s;
		}
		return $return;
	}
	
	/**
	 * Move a file or a directory to a target destination.
	 * 
	 * @return boolean True on success
	 * @param string $source Source file or directory
	 * @param object $dest Target destination
	 */
	public static function move($source,$dest){
		if(empty($source)||empty($dest)||!file_exists($source))
			return false;
		if(self::copy($source,$dest)){
			if (self::delete($source))
				return true;
			self::delete($dest);
		}
		return false;
	}
	
	/**
	 * Read a file and return its content. The file is expected to exist and
	 * be readable otherwise an exception is thrown.
	 * 
	 * Unicode BOM will be removed from the return string.
	 * 
	 * @return mixed(string or resource) File content
	 * @param string $path File path
	 */
	public static function read($path){
		switch(gettype($path)){
			case 'string':
				if(!is_file($path)) throw new Exception('File Does Not Exists: '.$path);
				if(!is_readable($path)) throw new Exception('File Not Readable: '.$path);
				$resource = fopen($path,'r');
				break;
			case 'resource':
				$resource = $path;
				break;
			default:
				throw new InvalidArgumentException('Path is expected to be a string or a resource');
		}
		$content = '';
		while(!feof($resource)){
			$content .= fgets($resource);
		}
		fclose($resource);
		// Remove UTF-8 BOM if present
		if(substr($content,0,3)==pack("CCC",0xef,0xbb,0xbf)){
			$content = substr($content,3);
		}
		return $content;
		
	}
	
	/**
	 * Write a string to a file. If file does not exist, it will be created. If file exists,
	 * its current content will be overwritten unless the "append" option is provided. Parent
	 * directories will be created if they do not already exist.
	 * 
	 * Exemples:
	 * 
	 * * // Touching a file
	 * * PurFile::write('/path/to/file.txt');
	 * 
	 * * // (Over)Write content to a file
	 * * PurFile::write('/path/to/file.txt','my content');
	 * 
	 * * // Add or write (if new) content to a file
	 * * PurFile::write('/path/to/file.txt','my content',array('append'));
	 * 
	 * Options may include:
	 * - permissions: chmod mask, apply only when the file is created, default to php 0644
	 * - append: boolean If true, new content will be appended to the destination file if it exists
	 *  
	 * @return boolean true
	 * @param mixed(string or resource) $path Target file path
	 * @param string $content Content to write
	 * @param object $options[optional]
	 */
	public static function write($path,$content='',$options=array()){
		$options = PurArray::sanitize($options);
		switch(gettype($path)){
			case 'string':
				if(!file_exists($path)){
					$dir = dirname($path);
					PurFile::mkdir($dir);
					//if(!is_writable($dir)) throw new Exception('File Not Writable in Dir: '.$dir);
					$isNew = true;
				}else if(!is_writable($path)) throw new Exception('File Not Writable: '.$path);
				$resource = fopen($path,empty($options['append'])?'w':'a');
				if(isset($isNew)&&isset($options['permissions'])){
					chmod($path,$options['permissions']);
				}
				break;
			case 'resource':
				$resource = $path;
				break;
			default:
				throw new InvalidArgumentException('Path is expected to be a string or a resource');
		}
		flock($resource,LOCK_EX);
		fwrite($resource,$content);
		flock($resource,LOCK_UN);
		fclose($resource);
		return true;
	}
	
}
