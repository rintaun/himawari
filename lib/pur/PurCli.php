<?php

/**
 * General static methods around the Command Line Interface (CLI) environmnet.
 */
class PurCli{
	
	/**
	 * Execute an external command and return its content.
	 * 
	 * Options may include
	 * -   *return_message*
	 *     Return message even if an error code is returned by the command 
	 *     execution, may be a boolean true or an int matching the error code.
	 * -   *no_exception*
	 *     Return the integer return code instead of throwing an exception.
	 * -   *cwd* 
	 *     Current working directory used to execute the command, 
	 *     previous directory will be restored once the command is executed.
	 * 
	 * @param string $command
	 * @param array $options [optional] Options used to alter the method behavior
	 * @return array Message
	 */
	public static function exec($command,$options=array()){
		$command .= ' 2>&1';
		if(isset($options['cwd'])){
			$cwd = getcwd();
			chdir($options['cwd']);
		}
		//echo $command;
		exec($command,$out, $err);
		if(isset($options['cwd'])){
			chdir($cwd);
		}
		if($err){
			if(isset($options['return_message'])&&($options['return_message']===true||$options['return_message']===$err)){
				return $out;
			}
			if(!empty($options['no_exception'])){
				return $err;
			}
			switch($err){
				case 127:
					throw new RuntimeException('Command Not Found: "'.substr($command,0,strpos($command,' ')).'"');
				default:
					throw new RuntimeException('Command "'.substr($command,0,-5).'" Failed with code "'.$err.'" : "'.implode(PHP_EOL,$out).'"');
			}
		}
		return $out;
	}
	
	/**
	 * Parse command line arguments.
	 * 
	 * Arguments may be provided as an array similar to the PHP global 
	 * variable "$_SERVER['argv']", as a string or as null in which case 
	 * it will default to "$_SERVER['argv']".
	 * 
	 * @param mixed (array, string or null) $arguments [optional] Arguments to extract
	 * @param array $options [optional] Optional configuration
	 * @return array Arguments array
	 */
	public static function toArray($arguments=null,$options=array()){
		switch(gettype($arguments)){
			case 'array':
				// ok
				break;
			case 'string':
				// Note about php parsing
				// - include script name
				//   eg: php pur.php will have "pur.php" as first element
				// - same behavior between single and double quotes
				//   eg: 'my test' is identical to "my test"
				// - two following quotes will be merge as a single element
				//   eg: "'my '' test'" convert to "my  test"
				// - quoted text following word will be merged as a single element
				//   eg: "'my 'test" convert to "my test"
				// - quoted text covering a section of the word is interpreted as well
				//   eg: "my'quoted test'" convert to "myquoted text"
				// Lexer implementation
//				$arguments = explode(' ',$arguments);
//				break;
				$a = $arguments.' ';
				$arguments = array();
				$w = '';
				$l = strlen($a);
				$i = 0;
				$q = false;
				for($i;$i<$l;$i++){
					$c = $a[$i];
					if($c===$q){
						$q = false;
					}else if($c==='"'||$c==="'"){
						$q = $c;
					}else if((!$q&&$c===' ')){
						if($w!==''){
							$arguments[] = $w;
							$w = '';
						}
					}else{
						$w .= $c;
					}
				}
				break;
			case 'NULL':
				// Note about php parsing
				// - equal sign is treated as a regular sign
				$arguments = $_SERVER['argv'];
				break;
			default:
				throw new InvalidArgumentException('First argument "arguments" is expected to be a string, an array or null');
		}
		$return = array();
		$previousKey = null;
		$currentKey = null;
		$index = 0;
		while(($argument = array_shift($arguments))!==null){
			//$quoted = is_int(strpbrk($argument,' "\''));
			$flush = false;
			$value = null;
			// Named param key
			if(substr($argument,0,2)=='--'){
				// boolean don't have value and are treated instantly
				$currentKey = substr($argument,2);
				if(substr($currentKey,-1)=='='){
					$currentKey = substr($currentKey,0,-1);
				}
				// Deal with alias
				if(isset($options['map'][$currentKey])&&is_string($options['map'][$currentKey])){
					$currentKey = $options['map'][$currentKey];
				}
				if(isset($options['map'][$currentKey]['type'])&&$options['map'][$currentKey]['type']=='boolean'){
					$previousKey = null;
					$value = true;
				}else{
					$previousKey = $currentKey;
					$currentKey = null;
				}
			// Shortcut param key
			}else if(substr($argument,0,1)=='-'){
				// Deal with shortcut not associated to named param
				$k = substr($argument,1);
				if(strlen($k)>1){
					if($k[1]=='='){
						
						$value = trim(substr($k,2));
						if(empty($value)&&!empty($arguments[0])&&substr($arguments[0],0,1)!='-'){
							$value = array_shift($arguments);
						}
					}else{
						array_unshift($arguments,'-'.substr($k,1));
					}
					$k = $k[0];
//					for($i=1;$i<strlen($k);$i++){
//						array_unshift($arguments,'-'.$k[$i]);
//					}
				}
				if(!isset($options['map'][$k])||!is_string($options['map'][$k])){
					switch(isset($options['strict'])){
						case true:
							throw new UnexpectedValueException('Shortcut "'.$k.'" not associated to a named parameter');
						case false:
							$currentKey = null;
					}
				}else{
					$currentKey = $options['map'][$k];
					// value may be set earlier like in -ab=c where b has the value c
					if($value===null){
						$value = true;
					}else if(is_string($value)){
						// Make sure their is no confict type is string value
						if(isset($options['map'][$currentKey]['type'])&&$options['map'][$currentKey]['type']!='string'){
							switch(isset($options['strict'])){
								case true:
									throw new UnexpectedValueException('Shortcut "'.$k.'" for "'.$currentKey.'" associated to a named parameter of type "'.$options['map'][$currentKey]['type'].'" instead of "string"');
								case false:
									$currentKey = null;
									$value = false;
							}
						}
					}
				}
			// Indexed param key
			}else if($previousKey===null){
				// Index is mapped to named param
				if(($pos = strpos($argument,'='))!==false){
					$currentKey = substr($argument,0,$pos);
					$value = substr($argument,$pos+1);
					if($value==''){
						$value = true;
					}
				}else{
					if(isset($options['map'][$index])){
						$currentKey = $options['map'][$index];
					}else{
						$currentKey = $index;
					}
					$index++;
					$value = $argument;
				}
			// A value
			}else{
				$currentKey = $previousKey;
				$previousKey = null;
				$value = $argument;
			}
			if(!is_null($currentKey)){
				if(isset($options['map'][$currentKey]['type'])){
					switch($options['map'][$currentKey]['type']){
						case 'boolean':
							$value = (boolean) $value;
							break;
						case 'double':
							$value = (double) $value;
							break;
						case 'int':
							$value = (int) $value;
							break;
						case 'float':
							$value = (float) $value;
							break;
						case 'string':
							break;
						default:
							throw new InvalidArgumentException('Invalid option type for mapped key "'.$currentKey.'"');
					}
				}
				$return[$currentKey] = $value;
				$previousKey = $currentKey = null;
			}else{
				//echo 'yo';
				//$previousKey = $currentKey;
			}
		}
		return empty($options['flatten'])?PurProperties::propertiesToArray($return):$return;
	}

}
