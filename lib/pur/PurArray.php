<?php

/**
 * General static methods around array manipulation.
 */
class PurArray{
	
	/**
	 * Merge 2 arrays and return the resulting array. Behavior is similar with the
	 * PHP native function "array_merge_recursive" but with additionnal goodies.
	 * 
	 * Merging behavior may be altered with the "__OVERWRITE__" keyword in the second 
	 * array in which case the first array will be disregarded.
	 * 
	 * Exemple using the "__OVERWRITE__" keyword:
	 * 
	 * * $array = PurArray::merge(array('key_1'=>array('key_2')),array('key_1'=>array('key_3')));
	 * * $this->assertSame($array,array('key_1'=>array('key_3')));
	 * 
	 * @return array Merged array
	 * @param array $array1
	 * @param array $array2
	 * @param array $options[optional]
	 */
	public static function merge(array $array1,array $array2,array $options=array()){
		$stack = array(array(&$array1,$array2));
		while($count = count($stack)){
			$a1 = &$stack[$count-1][0];
			$a2 = $stack[$count-1][1];
			array_pop($stack);
			//unset($stack[$count-1]);
			if(isset($a2['__OVERWRITE__'])){
				unset($a2['__OVERWRITE__']);
				$a1 = array();
			}else{
				foreach($a2 as $k=>$v){
					if($v==='__OVERWRITE__'){
						unset($a2[$k]);
						$a1 = array();
						break;
					}
				}
				reset($a2);
			}
//			while(list($k,$v) = each($a2)){
			foreach($a2 as $k=>$v){
				if(is_int($k)){
					//echo $k."\n";
					$a1[] = $v;
				//}else if(isset($a1[$k])){
				}else if(array_key_exists($k, $a1)){
					if(is_array($v)){
						if(!is_array($a1[$k])){
							$a1[$k] = array($a1[$k]);
						}
						$stack[] = array(&$a1[$k],$v);
					}else{
						$a1[$k] = $v;
					}
				}else{
					$a1[$k] = $v;
				}
			}
		}
		return $array1;
	}
	
	/**
	 * Encode an array between two encodings.
	 * 
	 * @return array
	 * @param array $array Array to encode
	 * @param string $to_encoding Desired encoding
	 * @param string $from_encoding Source encoding
	 */
	public static function encode(array $array,$to_encoding,$from_encoding){
		if(strtoupper($to_encoding)==strtoupper($from_encoding)) return $array;
		$stack = array(&$array);
		while($count = count($stack)){
			$a = &$stack[$count-1];
			array_pop($stack);
			foreach($a as $k=>&$v){
				switch(gettype($v)){
					case 'array':
						$stack[] = &$v;
						break;
					case 'string':
						$a[$k] = mb_convert_encoding($v,$to_encoding,$from_encoding);
						break;
					default:
						$a[$k] = $v;
				}
			}
		}
		return $array;
	}
	
	/**
	 * Check if an array contains only indexed (numeric) keys.
	 * 
	 * @return boolean True on success
	 * @param array $array
	 */
	public static function indexed(array $array){
		while(list($k,) = each($array)){
			if(!is_int($k)) return false;
		}
		return true;
	}
	
	/**
	 * Filter an array according to include and exclude filters.
	 * 
	 * Possible options include:
	 * - include: multidimentional array of keys to be included
	 * - exclude: multidimentional array of keys to be excluded
	 * - preserve_indexes: Wether indexes should be preserve or recomputed (default behavior)
	 * 
	 * Include and exclude values may be a string (as a list of keys separated by commas) or
	 * a multidimentional array.
	 * 
	 * Exemple: multidimentional include
	 * 
	 *     assert(
	 *       array(
	 *           'param_b'=>array(
	 *               'param_b_2'=>2,
	 *               'param_b_3'=>3
	 *           )
	 *       ),
	 *       PurArray::filter(array(
	 *             'param_a'=>true,
	 *             'param_b'=>array(
	 *                 'param_b_1'=>1,
	 *                 'param_b_2'=>2,
	 *                 'param_b_3'=>3 ),
	 *             'param_c'=>'c',
	 *         ),array(
	 *             'param_b'=>array(
	 *                 'param_b_2','param_b_3'))));
	 * 
	 * Tip: Any value with a key different than "include", "exclude" or "preserve_indexes"
	 * and present in the filters array is considered an "include" value, so as an exemple:
	 * 
	 * * $options = array('my_param_1','include'=>'my_param_2','exclude'=>'my_param_3');
	 * * // is similar to
	 * * $options = array('include'=>array('my_param_1','my_param_2'),'exclude'=>'my_param_3');
	 * 
	 * Note: this method is not yet coded with a non-recursive implementation.
	 * 
	 * @return array Filtered array
	 * @param $array Source array
	 * @param $filters Filters and options array
	 */
	public static function filter(array $array,array $filters){
		// Prepare options for config mode
		foreach($filters as $k=>$v){
			if(is_int($k)){
				unset($filters[$k]);
				$filters['include'][] = $v;
			}else{
				switch($k){
					case 'include':
					case 'exclude':
					case 'preserve_indexes':
						continue;
					default:
						unset($filters[$k]);
						$filters['include'][$k] = $v;
				}
			}
		}
		// Sanitize option "include"
		if(!empty($filters['include'])){
			if(is_string($filters['include'])){
				$filters['include'] = explode(',',$filters['include']);
			}
			if(is_array($filters['include'])){
				$includeKeys = array();
				foreach($filters['include'] as $key=>$value){
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
		if(!empty($filters['exclude'])){
			if(is_string($filters['exclude'])){
				$filters['exclude'] = explode(',',$filters['exclude']);
			}
			if(is_array($filters['exclude'])){
				$excludeKeys = array();
				foreach($filters['exclude'] as $key=>$value){
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
		foreach($array as $key=>$value){
			if($excludeKeys && !empty($excludeKeys[$key]) && !is_array($excludeKeys[$key])){
				unset($array[$key]);
			}else if($includeKeys && empty($includeKeys[$key])){
				unset($array[$key]);
			}else if(
				($includeKeys && isset($includeKeys[$key])&&is_array($includeKeys[$key])) ||
				($excludeKeys && isset($excludeKeys[$key])&&is_array($excludeKeys[$key]))
			){
				$mergedOptions = array();
				if($includeKeys) $mergedOptions['include'] = $includeKeys[$key];
				if($excludeKeys) $mergedOptions['exclude'] = $excludeKeys[$key];
				$array[$key] = PurArray::filter($array[$key],array_merge($filters,$mergedOptions));
			}
		}
		if(empty($filters['preserve_indexes'])){
			$result = array();
			foreach($array as $key=>$value){
				if(is_int($key)){
					$result[] = $value;
				}else{
					$result[$key] = $value;
				}
			}
			return $result;
		}else{
			return $array;
		}
	}
	
	/**
	 * Read the content of a file and deserialize it as an array. Unless provided, 
	 * deserialization format is derived from the file path extension.
	 * 
	 * Options include:
	 * - format Deserialization method (js and json accepted);
	 * - from_encoding Encoding of the file being read
	 * - to_encoding Encoding of the returned array
	 * 
	 * @return boolean True on success
	 * @param object $path
	 * @param object $options[optional]
	 */
	public static function read($path,array $options=array()){
		if(!isset($options['format'])){
			$dot = strrpos(basename($path),'.');
			if($dot===false) throw new Exception('Format Undertermined From Path: "'.basename($path).'"');
			$format = substr(basename($path),$dot+1);
		}
		switch($format){
			case 'js':
			case 'json':
				$content = trim(PurFile::read($path));
				try{
					return PurJson::decode($content,$options);
				}catch(Exception $e){
					throw new Exception($e->getMessage().': '.basename($path));
				}
			default:
				throw new Exception('Unsupported Format: "'.$format.'"');
		}
	}
	
	/**
	 * Normalize an array where indexed value are converted to association 
	 * keys with a value set to boolean true.
	 * 
	 * Exemple:
	 * 
	 *     $this->assertSame(array(
	 *     	"a"=>true,
	 *     	"b"=>"value_a",
	 *     	"c"=>array(
	 *     		"c_1"=>true )),
	 *     	PurArray::sanitize(array(
	 *     		"a",
	 *     		"b"=>"value_a",
	 *     		"c"=>array(
	 *     			"c_1" ))));
	 * 
	 * @return array Normalized version of the provided array
	 * @param array $array
	 */
	public static function sanitize(array $array){
		$jobs = array(&$array);
		while($count = count($jobs)){
			$job = &$jobs[$count-1];
			array_pop($jobs);
			$newJob = array();
			foreach($job as $k=>&$v){
				if(is_array($v)){
					$jobs[] = &$v;
				}else if(is_string($v)&&is_int($k)){
					$k = $v;
					$v = true;
				}
				if(is_int($k)){
					$newJob[] = &$v;
				}else{
					$newJob[$k] = &$v;
				}
			}
			$job = $newJob;
		}
		return $array;
	}
	
	/**
	 * Serialize an array to a file. Unless provided, the serialization
	 * format is derived from the file path extension.
	 * 
	 * Options include:
	 * - format Serialization method (js and json accepted);
	 * - from_encoding Encoding of the provided array
	 * - to_encoding Encoding of the destination file
	 * 
	 * Javascript is meant to be understood as JSON surrounded by parenthesis, not
	 * as the full Javascript language. This form is usefull when text editors
	 * support the Javascript synthax and not the JSON one.
	 * 
	 * JSON imposes the UTF-8 encoding. When converting to/from it, it is not the 
	 * PHP array which is encoded/decoded but the JSON string. Also, always 
	 * provide the source encoding when reading from a file written in a 
	 * different encoding than UTF-8.
	 * 
	 * @return boolean True on success
	 * @param string $path Path to the destination file
	 * @param array $array Array to serialize
	 * @param array $options[optional]
	 */
	public static function write($path,array $array,array $options=array()){
		$options = self::sanitize($options);
		if(!isset($options['format'])){
			$dot = strrpos(basename($path),'.');
			if($dot===false) throw new Exception('Format Undertermined From Path: "'.basename($path).'"');
			$format = substr(basename($path),$dot+1);
		}
		switch($format){
			case 'json':
				$array = PurJson::encode($array,$options);
				break;
			case 'js':
				$array = PurJson::encode($array,$options);
				$array = '('.$array.')';
				break;
			default:
				throw new Exception('Unsupported Format: "'.$format.'"');
		}
		return PurFile::write($path,$array,$options);
	}
	
}
