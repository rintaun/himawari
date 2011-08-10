<?php

/**
 * General static methods around array sorting.
 * 
 * Note: this is code might be transformed or removed.
 * 
 */
class PurSort{
	
	/**
	 * Recursively sort an array by value while placing the value provided in 
	 * the second parameter as first.
	 * 
	 * @param array $array Array to sort
	 * @param array $sort 
	 * @return array Sorted array
	 */
	public static function byValue($array,$sort){
		foreach(array_reverse($sort) as $key=>$value){
			$realKey = (is_array($value)?$key:$value);
			if(array_key_exists($realKey,$array)){
				if(is_array($value)) $array[$key] = PurSort::byValue($array[$key],$value);
				$array = array($realKey=>$array[$realKey])+$array;
			}
		}
		return $array;
	}
	
	/**
	 * Exemple:
	 *     $users = array(
	 *         array('id'=>1,'username'=>'username 1','sort'=>'a'),
	 *         array('id'=>2,'username'=>'username 2','sort'=>'b'),
	 *         array('id'=>3,'username'=>'username 3','sort'=>'c'),
	 *     );
	 *     $sort = array('b','a','c');
	 *     $sortedUsers = PurArray::array_sort_by_key($users,$sort,'sort');
	 *   
	 * @return array
	 * @param $arrayToSort array
	 * @param $arrayKeysReference array
	 * @param $key string
	 */
	public static function byKey(array $arrayToSort,array $arrayKeysReference,$key){
			$arrayKeysReference = array_fill_keys($arrayKeysReference,null);
			foreach($arrayToSort as $el){
				if(array_key_exists(strval($el[$key]),$arrayKeysReference)){
					$arrayKeysReference[strval($el[$key])] = $el;
				}
			}
			return array_values($arrayKeysReference);
	}
	
}
