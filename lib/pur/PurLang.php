<?php

/**
 * General static methods around PHP core.
 */
class PurLang{
	
	/**
	 * Determine if the current script is running as a web process.
	 * 
	 * @return boolean True if HTTP environment
	 */
	public static function isHttp(){
		// todo: use PHP_SAPI == 'cli'
		return !empty($_SERVER['SERVER_NAME']);
	}
	
	/**
	 * Determine if the current script is running as a cli process.
	 * 
	 * @return True if CLI environment
	 */
	public static function isCli(){
		// todo: use PHP_SAPI == 'cli'
		return empty($_SERVER['SERVER_NAME']);
	}
	
	/**
	 * Output a formated message as a readable text in all environments.
	 * 
	 * In web environment, format may vary depending on the "$_SERVER['HTTP_ACCEPT']"
	 * variable.
	 * 
	 * Internally, it uses the print_r method to output the message.
	 * 
	 * @return null
	 * @param mixed $source Value to debug
	 * @param mixed $options[optional] Options used to alter the method behavior
	 */
	public static function debug($source,array $options=array()){
		if(!isset($options['linebreak'])){
			$options['linebreak'] = 
				(isset($_SERVER['HTTP_ACCEPT'])&&strpos($_SERVER['HTTP_ACCEPT'],'text/html')!==false||isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))?
				'<br/>':
				PHP_EOL;
		}
		if(!isset($options['tab'])){
			$options['tab'] = 
				(isset($_SERVER['HTTP_ACCEPT'])&&strpos($_SERVER['HTTP_ACCEPT'],'text/html')!==false||isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))?
				'&nbsp;':
				'    ';
		}
		if (!is_array($source)){
			$source = array($source);
		}
		$works = array($source);
		$content = '['.$options['linebreak'];
		while(count($works)){
			$work = $works[0];
			while(list($k,$v) = each($work)){
				unset($work[$k]);
				switch(gettype($v)){
					case 'array':
						$works[0] = $work;
						$work = $v;
						array_unshift($works, $v);
						$content .= implode('',array_pad(array(),(count($works)-1),$options['tab'])).$k.': ['.$options['linebreak'];
						break;
					case 'string':
						$content .= implode('',array_pad(array(),count($works),$options['tab'])).$k.': "'.$v.'"'.$options['linebreak'];
						break;
					case 'float':
					case 'double':
					case 'int':
						$content .= implode('',array_pad(array(),count($works),$options['tab'])).$k.': '.$v.$options['linebreak'];
						break;
					case 'boolean':
						$content .= implode('',array_pad(array(),count($works),$options['tab'])).$k.': '.($v?'true':'fasle').$options['linebreak'];
						break;
					case 'NULL':
						$content .= implode('',array_pad(array(),count($works),$options['tab'])).$k.': NULL'.$options['linebreak'];
						break;
					case 'object':
						$content .= implode('',array_pad(array(),count($works),$options['tab'])).$k.': {'.get_class($v).'}'.$options['linebreak'];
						break;
				}
			}
			$content .= implode('',array_pad(array(),(count($works)-1),$options['tab'])).']'.$options['linebreak'];
			array_shift($works);
		}
		echo $content;
		if(isset($notArray)){
			// todo
		}
	}

	/**
	 * Load a PHP file from a file path or a directory path.
	 * 
	 * Internally, it uses the "PurFile::browse" method to find all
	 * files with a ".php" extension. It accept the same options as
	 * the "PurFile::browse" method.
	 * 
	 * @return 
	 * @param object $path
	 * @param object $options[optional]
	 */
	public static function load($path,$options=array()){
		if(!is_readable($path)) throw new Exception('Provided path not readable: '.$path);
		if(is_file($path)){
			require_once($path);
		}else if(is_dir($path)){
			$files = PurFile::browse($path,array_merge(array(
				'file_only'=>true,
				'absolute'=>true,
				'include'=>'**/*.php'),$options));
			foreach($files as $file){
				require_once($file);
			}
		}
	}
	
	/**
	 * Compute a mathematical function with provided context and without calling PHP exec method.
	 * 
	 * For now, the algorithm support the following operators: +,-,*,/,(,)
	 * 
	 * @return int Computation result of the provided function
	 * @param string $expression
	 * @param array $params[optional]
	 */
	public static function math($expression,$params=array(),$options=array()){
		// Scanner
		$length = strlen($expression);
		$count = 0;
		$scan = '';
		$scans = array();
		$symbols = array('+'=>true,'-'=>true,'*'=>true,'/'=>true,'('=>true,')'=>true);
		while($count<$length){
			$char = $expression[$count];
			if(isset($symbols[$char])){
				if(strlen($scan)>0){
					$scans[] = (double) $scan;
					$scan = '';
				}
				switch($char){
					case '+';
						if(strlen($scan)===0){
							switch($lastScan = end($scans)){
								case '+';
								case '-';
								case '*';
								case '/';
									$break = true;
									break;
							}
						}
						if (isset($break) && $break === true) break;
					case '-';
						if(strlen($scan)===0){
							switch($lastScan = end($scans)){
								case '+';
									array_pop($scans);
									break;
								case '-';
									array_pop($scans);
									$char = '+';
									break;
								case '/';
								case '*';
									$scan .= $char;
									$count++;
									$break = true;
									break;
								case false;
									if($lastScan===false){
										$scan .= $char;
										$count++;
										$break = true;
										break;
									}
									break;
							}
						}
						if (isset($break) && $break === true) break;
					case '*';
					case '/';
					case ')';
						$scans[] = $char;
						break;
					case '(';
						if(strlen($scan)===0){
							switch($lastScan = end($scans)){
								case ')';
									$scans[] = '*';
									break;
							}
						}
						$scans[] = $char;
				}
			}else{
				switch($char){
					case ' ':
						break;
					case '.';
					default:
						$scan .= $char;
				}
			}
			$count++;
		}
		if(strlen($scan)>0){
			$scans[] = (double) $scan;
		}
		unset($char);
		unset($scan);
		reset($scans);
		if(!empty($options['scans'])){
			return $scans;
		}
		// Tokenizer
		$tokens = array();
		$leftActions = array(array());	// * /
		$rightActions = array(array());	// + -
		$level = 0;
		while(($scan = current($scans))!==false){
			$leftFlush = false;
			$rightFlush = false;
			switch($scan){
				case '+':
				case '-':
					$leftFlush = $leftActions[$level];
					$leftActions[$level] = array();
					$rightFlush = $rightActions[$level];
					$rightActions[$level] = array();
					$rightActions[$level][] = $scan;
					break;
				case '*':
				case '/':
					$leftFlush = $leftActions[$level];
					$leftActions[$level] = array();
					$leftActions[$level][] = $scan;
					break;
				case '(':
					$level++;
					$leftActions[$level] = array();
					$rightActions[$level] = array();
					break;
				case ')':
					if($level===0){
						throw new Exception('Missing opening parenthesis: '.$expression);
					}
					$leftFlush = $leftActions[$level];
					unset($leftActions[$level]);
					$rightFlush = $rightActions[$level];
					unset($rightActions[$level]);
					$level--;
					break;
				default:
					$tokens[] = $scan;
			}
			if($leftFlush){
				$tokens = array_merge($tokens,$leftFlush);
			}
			if($rightFlush){
				$tokens = array_merge($tokens,array_reverse($rightFlush));
			}
			next($scans);
		}
		if($level!==0||count($level)!==1){
			throw new Exception('Missing closing parenthesis: '.$expression);
		}
		$tokens = array_merge($tokens,$leftActions[$level]);
		$tokens = array_merge($tokens,array_reverse($rightActions[$level]));
		if(!empty($options['tokens'])){
			return $tokens;
		}
		// Processor
		$stack = array();
		while($token = current($tokens)){
			switch($token){
				case '+':
					$stack[] = array_pop($stack) + array_pop($stack);
					break;
				case '-':
					$stack[] = - array_pop($stack) + array_pop($stack);
					break;
				case '*':
					$stack[] = array_pop($stack) * array_pop($stack);
					break;
				case '/':
					$stack[] = 1 / array_pop($stack) * array_pop($stack);
					break;
				default:
					$stack[] = $token;
			}
			next($tokens);
		}
		return $stack[0];
	}
	
	/**
	 * Retrieve functions arguments with complex and multiple combinations.
	 * 
	 * The first argument are the arguments to retrieve. For exemple, they 
	 * may be obtained with the "func_get_args" function or through the second 
	 * argument of the PHP magick "__call" method.
	 * 
	 * The second argument is a list of all the possible func_get_args. Each
	 * combination is structure as an associative array where keys represent 
	 * the PHP type of argument (using "" or "mixed" for all) and the value is the key name 
	 * used in the returned array to access the matching argument.
	 * 
	 * If no match are found, an exception of type "InvalidArgumentException" is thrown.
	 * 
	 *     PurLang::args(
	 *         func_get_args(),
	 *         array(
	 *             array("my_param_1"=>array("string","array")),
	 *             array("my_param_1"=>"mixed","my_param_array.my_param_key"=>"string",
	 *             array("my_param_1"=>"my_param_1","my_param_array"=>"array"));
	 * 
	 * @param array $args Arguments to retrieve
	 * @param array $structure List of combinations
	 * @return array Associative array of arguemnts
	 */
	public static function args(array $args,array $structure){
		$count = count($args);
		foreach($structure as $combination){
			if($count!==count($combination)){
				continue;
			}
			if($count===0){
				return array();
			}
			$combination = array_reverse($combination);
			$i = $count-1;
			foreach($combination as $key=>$type){
				if($type!=''&&$type!='mixed'&&
					((is_string($type)&&gettype($args[$i])!=$type)
					||
					((is_array($type)&&!in_array(gettype($args[$i]),$type))))){
					continue 2;
				}
				if(empty($key)){
					
				}
				$i--;
			}
			$keys = array();
			foreach($combination as $key=>$type){
				if(empty($key)){
					$keys[] = '_';
				}else{
					$keys[] = '_.'.$key;
				}
			}
			$result = PurProperties::propertiesToArray(array_combine($keys,array_reverse($args)));
			return array_reverse($result['_']);
		}
		throw new InvalidArgumentException('Unexpected arguments: "'.self::toString($args).'"');
	}
	
	/**
	 * Provide a readable string for any type of argument. 
	 * 
	 * The return value is formated with the type of the argument and 
	 * when possible a readable representation of the argument.
	 * 
	 * Exemples:
	 * 
	 * * PurLang::toString('my string'); // return string(my string);
	 * * PurLang::toString(null); // return NULL;
	 * * PurLang::toString(1); // return int(1);
	 * * PurLang::toString(array('my_key','my value')); // return array(my_key=>string(my value));
	 * 
	 * @return 
	 * @param object $mixed
	 */
	public static function toString($mixed){
		$type = gettype($mixed);
		switch($type){
			case 'NULL':
				return 'NULL';
			case 'object':
				$mixed = get_class($mixed);
				break;
			case 'array':
				$content = array();
				$count = count($mixed);
				foreach($mixed as $k=>$v){
					//if($count++>10) break;
					$content[] = $k.
						'=>'.
						(is_array($v)?
							'array('.(PurArray::indexed($mixed)?'indexed':'associated').')':
							self::toString($v));
				}
				$mixed = implode(',',$content);
				break;
			case 'boolean':
				$mixed = $mixed?'true':'false';
				break;
			default:
				$mixed = strval($mixed);
				break;
		}
		return $type.'('.strval($mixed).')'; 
	}
	
	/**
	 * Return a sanitized trace.
	 * 
	 * The optional "base" path parameter offer the ability to transform absolute 
	 * paths to relative path when trace paths are located inside the provided path.
	 * 
	 * Internally, it uses the PHP "debug_backtrace" function and they filter the trace
	 * for greater readability.
	 * 
	 * @param string $base[optional]
	 * 
	 * @return array Trace
	 */
	public static function trace(){
		$args = PurLang::args(
			func_get_args(),
			array(
				array(),
				array('base'=>'string'),
				array('trace'=>array('array','NULL')),
				array('trace'=>array('array','NULL'),''=>'array'),
				array('base'=>'string',''=>'array')));
		if(empty($args['trace'])){
			$args['trace'] = debug_backtrace();
		}
		if(empty($args['base'])){
			$args['base'] = '';
		}else{
			$args['base'] .= '/';
		}
		while(list($k,$v) = each($args['trace'])){
			if(!isset($v['file'])){
				unset($args['trace'][$k]);
			}else{
				unset($args['trace'][$k]['object']);
				$args['trace'][$k]['file'] = PurPath::relative($args['base'],$args['trace'][$k]['file']);
				// Some function like ereg don't return the 'args' key
				if(isset($v['args'])){
					while(list($kk,$vv) = each($v['args'])){
						$args['trace'][$k]['args'][$kk] = PurLang::toString($vv);
					}
				}
			}
		}
		reset($args['trace']);
		return array_values($args['trace']); // reindex the array
	}
	
	/**
	 * Print a readable stacktrace. If no strace is provided, will generate a new one.
	 * 
	 * Options may include:
	 * - prefix: string to insert a the beginning of each lines, default to empty
	 * - suffix: string to insert at the end of each lines, default to windows carriage return '\r\n'
	 * - base: transform absolute path to relative
	 * 
	 * @return 
	 * @param object $trace[optional]
	 * @param object $options[optional]
	 */
	public static function traceAsString($trace=null,$options=array()){
		if(is_null($trace)) $trace = PurLang::trace();
		elseif(!is_array($trace)) throw new InvalidArgumentException('Trace must be an array or null: "'.self::toString($trace).'" provided');
		$content = '';
		foreach($trace as $k=>$v){
			if(isset($v['file'])){
				if(isset($options['prefix'])) $content .= $options['prefix'];
				$file = $v['file'];
				if(!empty($options['base'])){
					$_file = PurPath::relative($options['base'],$file);
					if(strpos($_file,'../')!==0){
						$file = $_file;
					}
					unset($_file);
				}
				$content .= $file.':'.$v['line']." ";
				$content .= (array_key_exists('class',$v)?$v['class']:'').(array_key_exists('type',$v)?$v['type']:'').$v['function'].'()';
				$content .= (isset($options['suffix']))?$options['suffix']:"\r\n";
			}
		}
		return $content;
	}
	
	/**
	 * Return the associated value provinding a error code (int) or an error message (string)
	 * Imported from http://fr2.php.net/error_reporting
	 * 
	 * NOTE: THIS IS CODE IS STILL EXPERIMENTAL AND MIGHT BE MOVED
	 */
	public static function convertError($value){
		if(is_string($value)&&strval((int)$value)!=$value){
			$level_names = array(
				'E_ERROR',
				'E_WARNING',
				'E_PARSE',
				'E_NOTICE',
				'E_CORE_ERROR',
				'E_CORE_WARNING',
				'E_COMPILE_ERROR',
				'E_COMPILE_WARNING',
				'E_USER_ERROR',
				'E_USER_WARNING',
				'E_USER_NOTICE',
				'E_ALL',
				'E_STRICT' );
			$return = 0;
			$levels = explode('|',$value);
			foreach($levels as $level){
				$level = trim($level);
				if(defined($level)) $return |= (int)constant($level);
			}
			return $return;
		}else{
			$level_names = array(
				E_ERROR => 'E_ERROR',								// 1
				E_WARNING => 'E_WARNING',							// 2
				E_PARSE => 'E_PARSE',								// 4
				E_NOTICE => 'E_NOTICE',								// 8
				E_CORE_ERROR => 'E_CORE_ERROR',						// 16
				E_CORE_WARNING => 'E_CORE_WARNING',					// 32
				E_COMPILE_ERROR => 'E_COMPILE_ERROR',				// 64
				E_COMPILE_WARNING => 'E_COMPILE_WARNING',			// 128
				E_USER_ERROR => 'E_USER_ERROR',						// 256
				E_USER_WARNING => 'E_USER_WARNING',					// 512
				E_USER_NOTICE => 'E_USER_NOTICE',					// 1024
				E_STRICT => 'E_STRICT',								// 2048
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR' );		// 4096
			$levels=array();
			if(($value&E_ALL)==E_ALL){								// 8191
				$levels[]='E_ALL';
				$value&=~E_ALL;
			}
			foreach($level_names as $level=>$name)
				if(($value&$level)==$level) $levels[]=$name;
			return implode(' | ',$levels);
		}
	}
	
	/**
	 * Convert a PHP comment retrieve from PHP native reflection methods.
	 * 
	 * NOTE: THIS IS CODE IS STILL EXPERIMENTAL AND MIGHT BE MOVED OR DELETED
	 */
	public static function parseComment($comment,$extractTitle=false){
		$output = array('comments'=>array());
		$tok = strtok($comment,"\r\n");
		$inList = false;
		while($tok !== false){
			//comment because we need to check spaces in list
			//$tok = trim($tok);
			if(preg_match("/((\*\/)|(\/\*\*)|\*)?(.*)$/",trim($tok),$rest)){
				$tok = $rest[4];
			}
			// Line is empty, skip processing
			if(!$tok){
				$tok = strtok("\r\n");
				continue;
			}
			// If class comment, extract page title from 1st line
			if(!isset($output['title'])&&$extractTitle){
				$output['title'] = trim($tok);
			// Deal with anotation
			}else if(strpos(trim($tok),'@package')===0){
				$output['package'] = trim(mb_substr(trim($tok),0));
			// Look if a new list is asked
			}else if(substr(trim($tok),0,1)=='-'){
				if(!$inList){
					$output['comments'][] = array('type'=>'list','value'=>array());
				}
				$output['comments'][count($output['comments'])-1]['value'][] = trim(mb_substr(trim($tok),1));
			// Look if a new paragraphe is asked
			}else if(substr(trim($tok),0,1)=='*'){
				$value = trim(mb_substr(trim($tok),1));
				if(count($output['comments'])&& $output['comments'][count($output['comments'])-1]['type']=='code'){
					$output['comments'][count($output['comments'])-1]['value'][] = $value;
				}else{
					$output['comments'][] = array('type'=>'code','value'=>array($value));
				}
				unset($value);
			// Look if a new paragraphe is asked
			}else if(
				!$inList &&
				count($output['comments'])&&
				$output['comments'][count($output['comments'])-1]['type']=='text'&&
				substr($output['comments'][count($output['comments'])-1]['value'],-1)!='.'&&
				substr($output['comments'][count($output['comments'])-1]['value'],-1)!=':'&&
				strpos(trim($tok),'@')!==0)
			{
				$output['comments'][count($output['comments'])-1]['value'] .= ' '.trim($tok);
			}else if($inList&&substr($tok,0,1)==' '){
				$commentIndex = count($output['comments'])-1;
				$valueIndex = count($output['comments'][$commentIndex]['value'])-1;
				if(!is_array($output['comments'][$commentIndex]['value'][$valueIndex])){
					$output['comments'][$commentIndex]['value'][$valueIndex] = array($output['comments'][$commentIndex]['value'][$valueIndex]);
				}
				if(
					substr($output['comments'][$commentIndex]['value'][$valueIndex][count($output['comments'][$commentIndex]['value'][$valueIndex])-1],-1)!='.'&&
					substr($output['comments'][$commentIndex]['value'][$valueIndex][count($output['comments'][$commentIndex]['value'][$valueIndex])-1],-1)!=':'&&
					strpos(trim($tok),'@')!==0)
				{
					$output['comments'][$commentIndex]['value'][$valueIndex][count($output['comments'][$commentIndex]['value'][$valueIndex])-1] .= ' '.trim($tok);
				}else{
					$output['comments'][$commentIndex]['value'][$valueIndex][] = trim($tok);
				}
				unset($commentIndex);
				unset($valueIndex);
			}else{
				$output['comments'][] = array('type'=>'text','value'=>trim($tok));
			}
			$inList = count($output['comments'])&&$output['comments'][count($output['comments'])-1]['type']=='list';
			$tok = strtok("\r\n");
		}
		return $output;
	}

}
