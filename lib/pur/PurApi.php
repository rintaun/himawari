<?php

/**
 * Class and method API discovery and source code analysis.
 */
class PurApi{
	
	/**
	 * Extract class information using PHP reflection.
	 * 
	 * Options may include:
	 * -   include: List of properties to return
	 * -   exclude: List of properties to not return
	 * 
	 * @return array Extracted class information
	 * @param string $class Name of the class to be analysed
	 */
	public static function clazz($class,array $options=array()){
		// Sanitize option "include"
		if(isset($options['include'])){
			if(is_string($options['include'])){
				$options['include'] = explode(',',$options['include']);
			}
			if(is_array($options['include'])){
				$includeKeys = array();
				foreach($options['include'] as $key=>$value){
					if(is_int($key)){
						$includeKeys[$value] = true;
					}else if(is_array($value)){
						$includeKeys[$key] = $value;
					}
				}
			}else{
				$includeKeys = false;
			}
		}else{
			$includeKeys = false;
		}
		// Sanitize option "exclude"
		if(!empty($options['exclude'])){
			if(is_string($options['exclude'])){
				$options['exclude'] = explode(',',$options['exclude']);
			}
			if(is_array($options['exclude'])){
				$excludeKeys = array();
				foreach($options['exclude'] as $key=>$value){
					if(is_int($key)){
						$excludeKeys[$value] = true;
					}else if(is_array($value)){
						$excludeKeys[$key] = $value;
					}
				}
			}else{
				$excludeKeys = false;
			}
		}else{
			$excludeKeys = false;
		}
		$keys = array(
			'name',
			'filename',
			'parents',
			'is_interface',
			'interfaces',
			'comment',
			'summary',
			'attributes',
			'methods');
		foreach($keys as $key){
			$options['return_'.$key] = !($excludeKeys && !empty($excludeKeys[$key]) && !is_array($excludeKeys[$key]))&&!($includeKeys && empty($includeKeys[$key]));
		}
		// Prepare returned array
		$return = array();
		// Start reflection
		$reflection = new ReflectionClass($class);
		if($options['return_name']){
			$return['name'] = $reflection->getName();
		}
		if($options['return_filename']){
			$return['filename'] = $reflection->getFileName();
		}
		if($options['return_parents']){
			$return['parents'] = array();
			$parent = $reflection;
			while(true){
				$parent = $parent->getParentClass();
				if($parent){
					$return['parents'][] = $parent->getName();
					continue;
				}
				break;
			}
			unset($parent);
		}
		if($options['return_is_interface']){
			$return['is_interface'] = $reflection->isInterface();
		}
		if($options['return_interfaces']){
			// $reflection->getInterfaceNames
			$return['interfaces'] = array();
			$interfaces = $reflection->getInterfaceNames();
			foreach($interfaces as $interface){
				$return['interfaces'][] = $interface;
			}
			unset($interfaces);
		}
		list($comments,$summary,$attributes) = self::comment($reflection);
		if($options['return_comment']){
			$return['comment'] = $comments;
		}
		if($options['return_summary']){
			$return['summary'] = $summary;
		}
		if($options['return_attributes']){
			$return['attributes'] = $attributes;
		}
		if($options['return_methods']){
			$return['methods'] = array();
			$methods = $reflection->getMethods();
			foreach($methods as $method){
				$return['methods'][] = self::method($method);
			}
		}
//		if($options['return_']){
//		}
//		if($options['return_']){
//		}
//		if($options['return_']){
//		}
		return $return;
	}
	
	/**
	 * Extract method information using PHP reflection.
	 * 
	 * Options may include:
	 * -   include: List of properties to return
	 * -   exclude: List of properties to not return
	 * 
	 * @param ReflectionMethod $method
	 * @return array
	 * 
	 * @param ReflectionMethod $method
	 * @param array $options [optional]
	 * @return array
	 * 
	 * @param string $class
	 * @param string $method
	 * @return array
	 * 
	 * @param string $class
	 * @param string $method
	 * @param array $options [optional]
	 * @return array
	 */
	public static function method(){
		$args = func_get_args();
		switch(count($args)){
			case 1:
				$reflection = $args[0];
				break;
			case 2:
				if($args[0] instanceof ReflectionMethod){
					$reflection = $args[0];
					$options = $args[1];
				}else{
					$reflection = new ReflectionMethod($args[0],$args[1]);
				}
				break;
			case 3:
				$reflection = new ReflectionMethod($args[0],$args[1]);
				$options = $args[2];
				break;
			default:
				throw new InvalidArgumentException('Invalid argument count');
		}
		// Sanitize option "include"
		if(isset($options['include'])){
			if(is_string($options['include'])){
				$options['include'] = explode(',',$options['include']);
			}
			if(is_array($options['include'])){
				$includeKeys = array();
				foreach($options['include'] as $key=>$value){
					if(is_int($key)){
						$includeKeys[$value] = true;
					}else if(is_array($value)){
						$includeKeys[$key] = $value;
					}
				}
			}else{
				$includeKeys = false;
			}
		}else{
			$includeKeys = false;
		}
		// Sanitize option "exclude"
		if(!empty($options['exclude'])){
			if(is_string($options['exclude'])){
				$options['exclude'] = explode(',',$options['exclude']);
			}
			if(is_array($options['exclude'])){
				$excludeKeys = array();
				foreach($options['exclude'] as $key=>$value){
					if(is_int($key)){
						$excludeKeys[$value] = true;
					}else if(is_array($value)){
						$excludeKeys[$key] = $value;
					}
				}
			}else{
				$excludeKeys = false;
			}
		}else{
			$excludeKeys = false;
		}
		$keys = array(
			'name',
			'filename',
			'is_abstract',
			'is_constructor',
			'is_destructor',
			'is_final',
			'is_private',
			'is_protected',
			'is_public',
			'is_static',
			'comment',
			'summary',
			'attributes');
		foreach($keys as $key){
			$options['return_'.$key] = !($excludeKeys && !empty($excludeKeys[$key]) && !is_array($excludeKeys[$key]))&&!($includeKeys && empty($includeKeys[$key]));
		}
		// Prepare returned array
		$return = array();
		// Start reflection
		if($options['return_name']){
			$return['name'] = $reflection->getName();
		}
		if($options['return_filename']){
			$return['filename'] = $reflection->getFileName();
		}
		if($options['return_is_abstract']){
			$return['is_abstract'] = $reflection->isAbstract();
		}
		if($options['return_is_constructor']){
			$return['is_constructor'] = $reflection->isConstructor();
		}
		if($options['return_is_destructor']){
			$return['is_destructor'] = $reflection->isDestructor();
		}
		if($options['return_is_final']){
			$return['is_final'] = $reflection->isFinal();
		}
		if($options['return_is_private']){
			$return['is_private'] = $reflection->isPrivate();
		}
		if($options['return_is_protected']){
			$return['is_protected'] = $reflection->isProtected();
		}
		if($options['return_is_public']){
			$return['is_public'] = $reflection->isPublic();
		}
		if($options['return_is_static']){
			$return['is_static'] = $reflection->isStatic();
		}
		list($comment,$summary,$attributes) = self::comment($reflection);
		if($options['return_comment']){
			$return['comment'] = $comment;
		}
		if($options['return_summary']){
			$return['summary'] = $summary;
		}
		if($options['return_attributes']){
			$return['attributes'] = $attributes;
		}
		return $return;
	}
	
	public static function comment($reflection){
		$comments = array();
		$summary = '';
		$attributes = array();
		$attributesTemp = array();
		$comment = $reflection->getDocComment();
		$tok = strtok($comment,"\r\n");
		while($tok !== false){
			if(preg_match('/((\*\/)|(\/\*\*)|\*)?(.*)$/',ltrim($tok),$matches)){
				$line = $matches[4];
				unset($matches);
				if(substr($line,0,1)===' '){
					$line = substr($line,1);
				}
				if(substr($line,0,1)==='@'){
					$attributesTemp[] = self::attribute($line);
					$tok = strtok("\r\n");
					continue;
				}
				if(!empty($attributesTemp)){
					$attributes[] = $attributesTemp;
					$attributesTemp = array();
				}
				if(empty($line)&&empty($summary)&&empty($attributes)){
					$summary = trim(implode(PHP_EOL,$comments));
				}
				$comments[] = $line;
			}
			$tok = strtok("\r\n");
		}
		unset($attributesTemp);
		$comments = trim(implode(PHP_EOL,$comments));
		return array($comments,$summary,$attributes);
	}
	
	public static function attribute($text){
		$attribute = array();
		if(preg_match('/^@([\w\d-_.]+)( (.*))?$/',$text,$matches)){
			$attribute['name'] = $matches[1];
			$value = isset($matches[3])?$matches[3]:'';
			$attribute['value'] = $value;
			switch($attribute['name']){
				case 'return':
					$valueLength = strlen($value);
					$valueCount = 0;
					$stack = array();
					$current = '';
					$flush = false;
					$escape = false;
					while($valueCount<$valueLength){
						$char = $value[$valueCount];
						if($escape||ctype_graph($char)||count($stack)>0){
							if(!$escape&&($char=='('||$char=='[')){
								$escape = $char;
								if($char=='('&&$flush){
									$current .= $flush;
									$flush = false;
								}
							}
							if($escape&&(($char==')'&&$escape=='(')||($char==']'&&$escape=='['))){
								$escape = false;
							}
							if($flush){
								$first = substr($current,0,1);
								if(($first=='['&&substr($current,-1)==']')||($first=='('&&substr($current,-1)==')')){
									$current = substr($current,1,-1);
								}
								unset($first);
								$stack[] = $current;
								$current = '';
							}
							$current .= $char;
							$flush = false;
						}else if(!empty($current)){
							$flush = $char;
						}
						$valueCount++;
					}
					$first = substr($current,0,1);
					if(($first=='['&&substr($current,-1)==']')||($first=='('&&substr($current,-1)==')')){
						$current = substr($current,1,-1);
					}
					unset($first);
					$stack[] = $current;
					unset($current);
					$stack = array_pad($stack,4,'');
					list($type,$description) = $stack;
					$attribute['type'] = $type;
					$attribute['description'] = $description;
					break;
				case 'param':
					$valueLength = strlen($value);
					$valueCount = 0;
					$stack = array();
					$current = '';
					$flush = false;
					$escape = false;
					while($valueCount<$valueLength){
						$char = $value[$valueCount];
						if($escape||ctype_graph($char)||count($stack)>2){
							if(!$escape&&($char=='('||$char=='[')){
								$escape = $char;
								if($char=='('&&$flush){
									$current .= $flush;
									$flush = false;
								}
							}
							if($escape&&(($char==')'&&$escape=='(')||($char==']'&&$escape=='['))){
								$escape = false;
							}
							if($flush){
								$first = substr($current,0,1);
								if(($first=='['&&substr($current,-1)==']')||($first=='('&&substr($current,-1)==')')){
									$current = substr($current,1,-1);
								}
								unset($first);
								$stack[] = $current;
								$current = '';
							}
							$current .= $char;
							$flush = false;
						}else if(!empty($current)){
							$flush = $char;
						}
						$valueCount++;
					}
					$first = substr($current,0,1);
					if(($first=='['&&substr($current,-1)==']')||($first=='('&&substr($current,-1)==')')){
						$current = substr($current,1,-1);
					}
					unset($first);
					$stack[] = $current;
					unset($current);
					$stack = array_pad($stack,4,'');
					list($type,$variable,$default,$description) = $stack;
					$attribute['type'] = $type;
					$attribute['variable'] = $variable;
					$attribute['default'] = $default;
					$attribute['description'] = $description;
					break;
				default:
					break;
			}
		}
		return $attribute;
	}
}
