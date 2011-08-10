<?php

/**
 * General static methods around file path manipulation.
 */
class PurPath{
	
	/**
	 * Sanitize a path. Accept URLs, Unix paths and Windows paths.
	 * 
	 * Exemple:
	 * 
	 * * PurPath::clean('my/path'); // return my/path
	 * * PurPath::clean('my/./path'); // return my/path
	 * * PurPath::clean('/my/./../path'); // return /path
	 * 
	 * Observations about platform directory separator handling:
	 * - Windows don't allow "\" and "/" in filenames
	 * - Apple allow "/" in finder but display as ":" in command line,
	 *   using "\" is ok (must be escaped in command line)
	 * 
	 * @return string Sanitized path
	 * @param string $path Path to sanitize
	 */
	public static function clean($path,array $options=array()){
		if(empty($path)){
			return '';
		}else if($path[0]=='/'){
			$separator = '/';
			$protocol = '/';
		}else if(preg_match('/^([a-zA-Z]*\:\/\/)/',$path,$matches)){
			$separator = '/';
			$protocol = $matches[1];
		}else if(preg_match('/^([a-zA-Z]*\:)\\\/',$path,$matches)){
			if(isset($options['unix'])){
				$separator = '/';
				$protocol = $matches[1].$separator;
				$path = str_replace('\\','/',$path);
			}else{
				$separator = '\\';
				$protocol = $matches[1].$separator;
			}
		}else{
			$separator = '/';
			$protocol = '';
		}
		$path = substr($path,strlen($protocol));
		$length = strlen($path);
		$return = '';
		$count = 0;
		$slashed = false;
		while($count<$length){
			$char = $path[$count];
			switch($char){
				case '\\':
				case '/':
					if($slashed){ // encountered two following slahs
						break;
					}
					$slashed = true;
					$return .= $char;
					break;
				case '.':
					if($slashed){
						if($count+1==$length) break;
						switch($path[$count+1]){
							case '\\':
							case '/':
								$count++;
								break 2;
							case '.':
								$count++;
								$count++;
								$c = strlen($return)-2;
								if($c<0) throw new Exception('Invalid Path: "'.$protocol.$path.'"');
								while($c>=0&&$return[$c]!='/'&&$return[$c]!='\\'){
									$c--;
								}
								$return = substr($path,0,$c+1);
								break 2;
						}
					}
				default:
					$return .= $char;
					$slashed = false;
			}
			$count++;
		}
		return $protocol.$return;
	}
	
	/**
	 * Combine two paths together when appropriate.
	 * 
	 * The first path may be empty or absolute, an error will be thrown 
	 * if a relative path is provided.
	 * 
	 * The second path may be relative in which case it will be combined 
	 * with the first path if not empty or absolute in which case it will be
	 * return as is.
	 * 
	 * Note, all path will be cleaned up before being returned.
	 * 
	 * @param object $path1
	 * @param object $path2
	 * @return 
	 */
	public static function combine($path1,$path2){
		if(!empty($path1)&&!self::isAbsolute($path1)){
			throw new InvalidArgumentException('First argument must be empty or an asolute path');
		}
		if(self::isAbsolute($path2)){
			return PurPath::clean($path2);
		}
		return PurPath::clean(empty($path1)?$path2:$path1.'/'.$path2);
	}
	
	/**
	 * Compare two paths and, based on their matching rootline, 
	 * dissociate base path from relative paths.
	 * 
	 * Exemples:
	 * 
	 * * PurPath::compare(
	 * *   '/path/to/dir1/file1',
	 * *   '/path/to/dir2/file2');
	 * * // return array('/path/to/','dir1/file1','dir2/file2')
	 * 
	 * * list($base,$path1,$path2) = array_values(
	 * *   PurPath::compare(
	 * *     '/path/to/dir1/file1',
	 * *     '/path/to/dir2/file2'));
	 * * // return
	 * * // $base === '/path/to/'
	 * * // $path1 === 'dir1/file1'
	 * * // $path2 === 'dir2/file2'
	 */
	public static function compare($path1,$path2){
		$path1 = self::clean($path1);
		$path2 = self::clean($path2);
		$base = '';
		$current = '';
		$count = 0;
		$maxCount = min(strlen($path1),strlen($path2));
		while($count<$maxCount){
			$char = $path1[$count];
			switch($char){
				case '\\':
				case '/':
					if($current){
						$base .= $current;
						$current = '';
					}
					$base .= $char;
					break;
				default:
					if($char==$path2[$count]){
						$current .= $char;
					}else{
						break 2;
					}
			}
			$count++;
		}
		$count = $count-strlen($current);
		$path1 = substr($path1,$count);
		$path2 = substr($path2,$count);
		return array(
			$base,
			$path1,
			$path2,
		);
	}
	
	/**
	 * Extract the extension of a provided path.
	 * 
	 * @return string
	 * @param object $file
	 */
	public static function extension($path){
		if(preg_match('/^https?:\/\//i', $path)){
			if($q=strpos($path,'?')){
				$path = substr($path,0,$q);
			}
			//return substr($path,strrpos($path,'.')+1);
		}
		$path = PurPath::filename($path);
		if(($position=strrpos($path,'.'))!==false){
			return substr($path,$position+1);
		}else{
			return '';
		}
		
	}
	
	/**
	 * Extract the filename of a provided path.
	 * 
	 * Optionnaly, the extension  may be stripped out of the returned filename.
	 * 
	 * Exemples:
	 * 
	 * * PurPath::filename('path/to/file.txt'); // return "file.txt"
	 * * PurPath::filename('path/to/file.txt',false); // return "file"
	 * 
	 * @return string Filename portion
	 * @param string $path Path to work on
	 * @param boolean $extension[optional] False if extension should be removed from the return filename
	 */
	public static function filename($path,$extension=true){
		return ($extension||false===$dot=strrpos(basename($path),'.'))?
			basename($path):
			substr(basename($path),0,$dot);
	}
	
	/**
	 * Determine wether a provided path is absolute or not.
	 * 
	 * It is aware of the underlying operating system.
	 * 
	 * @return boolean True if path is absolute
	 * @param object $path
	 */
	public static function isAbsolute($path){
		if(empty($path)) return false;
		$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		return ((!$isWindows && $path{0} == '/') || ($isWindows && $path{1} == ':'));
	}
	
	/**
	 * Match an array of include and exclude Ant style path selectors against a provided path.
	 * 
	 * To validate, the provided path must match all the included patterns and must match 
	 * none of the excluded patterns.
	 * 
	 * Arguments order are aligned with the Ant API.
	 * 
	 * @return boolean True on success
	 * @param mixed $include Include string or array of Ant selectors
	 * @param mixed $exclude Exclude string or array of Ant selectors
	 * @param string $path File path to match
	 */
	public static function match($include,$exclude,$path,$isCaseSensitive=false){
		// Sanitize include
		if(is_string($include)) $include = array($include);
		else if(!is_array($include)) throw new InvalidArgumentException('Invalid Argument "included": string or array of strings expected');
		// Sanitize exclude
		if(is_string($exclude)) $exclude = array($exclude);
		else if(!is_array($exclude)) throw new InvalidArgumentException('Invalid Argument "included": string or array of strings expected');
		// Path must match all the included patterns
		$match = PurSelector::matchPath($include,$path,$isCaseSensitive);
		if(!$match) return false;
		// Path must match none of the excluded patterns
		foreach($exclude as $pattern){
			if(PurSelector::matchPath($pattern,$path,$isCaseSensitive)) return false;
		}
		return true;
	}
	
	/**
	 * Transform the target path as relative to the source path.
	 * 
	 * @return string Relative path
	 * @param string $source Source path
	 * @param string $target Target path
	 */
	public static function relative($source,$target){
		list(,$source,$target) = self::compare($source,$target);
		$count = substr_count($source,'/')+substr_count($source,'\\');
		for($i=0;$i<$count;$i++){
			$target = '../'.$target;
		}
		return $target;
	}
	
}
