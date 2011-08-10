<?php

/**
 * General static methods around the property format.
 */
class PurProperties{
	
	/**
	 * Convert a multidimensional array to a one level array with keys as properties.
	 * 
	 * Options may include:
	 * - key_before: String prefixing each key
	 * - key_after: String suffixing each key
	 * - value_before: String prefixing each value
	 * - value_after: String suffixing each value
	 * - separator: Character use to split properties keys (default to ".")
	 * - from_encoding: Source encoding, default to utf-8
	 * - to_encoding: Destination encoding, default to utf-8
	 * 
	 * Exemple:
	 * 
	 *     assert(
	 *         array('user.username'=>'my','user.password'=>'account')
	 *         ===
	 *         PurProperties::arrayToProperties(
	 *             array('user'=>array('username'=>'my','password'=>'account'))));
	 * 
	 * @return 
	 * @param $array Object
	 * @param $options Object[optional]
	 */
	public static function arrayToProperties(array $array,array $options=array()){
		if(!is_array($array)) throw new InvalidArgumentException('First parameter is not an array');
		$keyBefore = isset($options['key_before'])&&is_string($options['key_before'])?$options['key_before']:'';
		$keyAfter = isset($options['key_after'])&&is_string($options['key_after'])?$options['key_after']:'';
		$valueBefore = isset($options['value_before'])&&is_string($options['key_before'])?$options['value_before']:'';
		$valueAfter = isset($options['value_after'])&&is_string($options['key_after'])?$options['value_after']:'';
		$separator = isset($options['separator'])?$options['separator']:'.';
		$fromEncoding = isset($options['from_encoding'])?$options['from_encoding']:'utf-8';
		$toEncoding = isset($options['to_encoding'])?$options['to_encoding']:'utf-8';
		$properties = array();
		$jobs = array();
		$rootline = array();
		while(list($k,$v) = each($array)){
			unset($array[$k]);
			if(is_array($v)&&!empty($v)){
				if(key($array)!==null){
					$jobs[] = array($k,$array);
				}
				$rootline[] = $k;
				$array = $v;
			}else{
				switch(gettype($v)){
					case 'array':
						// empty array, bypass
						break;
					case 'string':
						if($fromEncoding!=$toEncoding){
							$v = mb_convert_encoding($v,$toEncoding,$fromEncoding);
						}
					default:
						$r = (empty($rootline))?'':implode($separator,$rootline).$separator;
						$properties[$keyBefore.$r.$k.$keyAfter] = $valueBefore.$v.$valueAfter;
				}
				if(current($array)===false){
					$a = array_pop($jobs);
					if($a!==null){
						list($key,$array) = $a;
						while($key!==array_pop($rootline)){}
					}
				}
			}
		}
		return $properties;
	}
	
	/**
	 * Convert a multidimensional array to a property formated string.
	 * 
	 * @return string Property formated string
	 * @param array $array Multidimensional array to convert
	 */
	public static function arrayToString(array $array){
		return self::propertiesToString(self::arrayToProperties($array));
	}
	
	/**
	 * Retrieve a value from an array correponding to a provided property.
	 * 
	 * Exemple:
	 * 
	 *     assert(
	 *         'my_username'
	 *         ===
	 *         PurProperties::get(
	 *             array('user'=>array('username'=>'my_username')),
	 *             'user.username'));
	 * 
	 * @return mixed Value pointing to the provided property or default if not found
	 * @param array $source
	 * @param string $property Searched property
	 * @param mixed $default[optional] Value to return if property is not found
	 */
	public static function get($source,$property,$default=null){
		$properties = explode('.',$property);
		foreach($properties as $property){
			if(array_key_exists($property,$source)){
				$source = $source[$property];
			}else{
				return $default;
			}
		}
		return $source;
	}
	
	/**
	 * Convert a one level array with property like keys to a multidimensional array.
	 * 
	 * Options may include:
	 * -   *source*
	 *     array
	 *     Source array to be enriched and returned.
	 * -   *separator*
	 *     string
	 *     Character use to split properties keys (default to ".").
	 * -   *from_encoding*
	 *     string, default to utf-8
	 *     Source encoding.
	 * -   *to_encoding*
	 *     string, default to utf-8
	 *     Destination encoding.
	 * 
	 * @return array Multidimensional array
	 * @param array $properties
	 * @param array $options[optional]
	 */
	public static function propertiesToArray(array $properties,array $options=array()){
		if(!is_array($properties)) throw new InvalidArgumentException('First parameter is not an array');
		$return = isset($options['source'])?$options['source']:array();
		$separator = isset($options['separator'])?$options['separator']:'.';
		$fromEncoding = isset($options['from_encoding'])?$options['from_encoding']:'utf-8';
		$toEncoding = isset($options['to_encoding'])?$options['to_encoding']:'utf-8';
		while(list($key,$value) = each($properties)){
			$steps = explode($separator,$key);
			$lastStep = array_pop($steps);
			$tempArray = &$return;
			while(list(,$step) = each($steps)){
				if(!array_key_exists($step,$tempArray)){
					$tempArray[$step] = array();
				}
				$tempArray = &$tempArray[$step];
			}
			switch(gettype($value)){
				case 'array':
					if(isset($tempArray[$lastStep])){
						//$tempArray[$lastStep] = $value;
//						print_r($tempArray[$lastStep]);
//						print_r($value);
//						print_r(PurArray::merge($tempArray[$lastStep],$value));
						if(is_string($tempArray[$lastStep])){
							//$tempArray[$lastStep] = array();
							//unset($tempArray[$lastStep]);
							echo $lastStep."\n";
							$tempArray[$lastStep] = $value;
						}else{
							$tempArray[$lastStep] = PurArray::merge($tempArray[$lastStep],$value);
						}
						//$tempArray[$lastStep] = PurArray::merge($tempArray[$lastStep],$value);
					}else{
						$tempArray[$lastStep] = $value;
					}
					break;
				case 'string':
					if($fromEncoding!=$toEncoding){
						$tempArray[$lastStep] = mb_convert_encoding($value,$toEncoding,$fromEncoding);
					}else{
						$tempArray[$lastStep] = $value;
					}
					break;
				default:
					$tempArray[$lastStep] = $value;
					break;
			}
				
		}
		return $return;
	}
	
	/**
	 * Convert a one level property array to a string.
	 * 
	 * @return string Property formated string
	 * @param array $properties Property array to convert
	 */
	public static function propertiesToString(array $properties){
		$string = array();
		while(list($key,$value) = each($properties)){
			$string[] = $key.' = '.$value;
		}
		return implode("\n",$string);
	}
	
	/**
	 * Create a multidimentional array from a property formated file.
	 * 
	 * @return array Multidimensional array
	 * @param string $path Path to the property file
	 */
	public static function read($path){
		return PurProperties::stringToArray(PurFile::read($path));
	}
	
	/**
	 * Assign a value to an array correponding to a provided key.
	 * 
	 * Exemple:
	 * 
	 *     $this->assertEquals(
	 *         array('user'=>array('username'=>'my_username')),
	 *         PurProperties::set(
	 *              null,
	 *              'user.username',
	 *              'my_username'));
	 * 
	 *     $this->assertEquals(
	 *         array('user'=>array('username'=>'my_username')),
	 *         PurProperties::set(
	 *              array('user'=>'Unkown User'),
	 *              'user.username',
	 *              'my_username'));
	 * 
	 * @return array Modified source array
	 * @param array $source Array to modify (if null, a new array is created)
	 * @param string $property Property to modify
	 * @param mixed $value Value to assign
	 */
	public static function set(array $source=null,$property,$value){
		$properties = explode('.',$property);
		$work = &$source;
		foreach($properties as $property){
			if(isset($work[$property])&&is_array($work[$property])&&array_key_exists($property,$work)){
				$work = &$work[$property];
			}else{
				$work[$property] = null;
				$work = &$work[$property];
			}
		}
		$work = $value;
		return $source;
	}
	
	/**
	 * Convert a string consisting of properties and convert it to a multidimensional array.
	 * 
	 * @return array Multidimensional array
	 * @param string $string String in property like format
	 * @param array $array[optional]
	 */
	public static function stringToArray($string,array &$array=array()){
		return self::propertiesToArray(self::stringToProperties($string),$array);
	}
	
	/**
	 * Convert a string consisting of properties and convert it to an array of properties.
	 * 
	 * @return array Property array
	 * @param string $string String to be converted
	 */
	public static function stringToProperties($string){
		$array = array();
		$split = "\r\n";
		$tok = strtok($string,$split);
		while($tok !== false) {
			if(substr($tok,0,1)=='#'){
				$tok = strtok($split);
				continue;
			}
			list($k,$v) = explode('=',$tok);
			$k = trim($k);
			$v = trim($v);
			if($k) $array[$k] = $v;
			$tok = strtok($split);
			
		}
		return $array;
	}
	
}
