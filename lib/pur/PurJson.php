<?php

/**
 * General static methods around JSON manipulation.
 */
class PurJson{
	
	/**
	 * Decode a Json while providing encoding functionnalities.
	 * 
	 * Options may contain:
	 * -   *from_encoding*
	 *     /default to "UTF-8"/
	 *     Encoding of the source string (string is encoded in UTF-8 before being unserialized)
	 * -   *to_encoding*
	 *     /default to "UTF-8"/
	 *     Encoding of the returned array
	 * 
	 * @param string $string Encoded JSON string
	 * @param array $options[optional] Options used to alter the method behavior
	 * 
	 * @return array Decoded array
	 */
	public static function decode($string,array $options=array()){
		if(!isset($options['from_encoding'])) $options['from_encoding'] = 'UTF-8';
		if(!isset($options['to_encoding'])) $options['to_encoding'] = 'UTF-8';
		if(substr($string,0,1)=='('&&substr($string,-1)==')')
			$string = substr($string,1,-1);
		if(strtoupper($options['from_encoding'])!='UTF-8')
			$string = mb_convert_encoding($string,'UTF-8',$options['from_encoding']);
		// we found json_decode to return a string in some circonstances
		// when it start with a space, a tab or a new line
		// and when there's a missing comma in an array
		$string = json_decode(trim($string),1);
		if(is_null($string)) throw new Exception('Invalid JS/JSON');
		if(strtoupper($options['to_encoding'])!='UTF-8'){
			$string = PurArray::encode($string,$options['to_encoding'],'UTF-8');
		}
		return $string;
	}
	
	/**
	 * Encode a Json while providing encoding functionnalities.
	 * 
	 * Options may contain:
	 * -   *from_encoding*
	 *     /default to "UTF-8"/
	 *     Encoding of the source array
	 * -   *to_encoding*
	 *     /default to "UTF-8"/
	 *     Encoding of the returned string (JSON is serialize in UTF-8 and then encoded)
	 * -   *pretty*
	 *     /default to boolean "false"/
	 *     Format a JSON string with carriage returns and tabulations
	 * 
	 * @param array $array Array to encode
	 * @param array $options[optional] Options used to alter the method behavior
	 * 
	 * @return string Encoded JSON string
	 */
	public static function encode(array $array,array $options=array()){
		if(!isset($options['from_encoding'])) $options['from_encoding'] = 'UTF-8';
		if(!isset($options['to_encoding'])) $options['to_encoding'] = 'UTF-8';
		if(strtoupper($options['from_encoding'])!='UTF-8')
			$array = PurArray::encode($array,'UTF-8',$options['from_encoding']);
		$array = json_encode($array);
		if(isset($options['pretty'])){
			$array = PurJson::pretty($array);
		}
		if(strtoupper($options['to_encoding'])!='UTF-8')
			$array = mb_convert_encoding($array,$options['to_encoding'],'UTF-8');
		return $array;
	}
	
	/**
	 * Format a JSON string with carriage returns and tabulations.
	 * 
	 * Options may include:
	 * -   *tab*
	 *     /string/
	 *     Value replace the tab character
	 * -   *js*
	 *     /boolean/
	 *     True will surround the json string by parenthesis
	 * 
	 * @param mixed(string or array) $json Array or json string to format
	 * @param array $options[optional] Options used to alter the method behavior
	 * 
	 * @return string Encoded and formated JSON string
	 */
	public static function pretty($json,$options=array()){
		if(is_array($json)){
			$json = json_encode($json);
		}else if(is_string($json)){
			if(json_decode($json) === false) throw new Exception('Invalid Parameter: invalid json string');
		}else{
			Exception('Invalid Parameter: expect a json string or an array');
		}
		$tab = (!empty($options['tab'])?$options['tab']:"\t");
		$result = "";
		$ident = 0;
		$in_string = false;
		$len = strlen($json);
		for($c = 0; $c < $len; $c++){
			$char = $json[$c];
			switch($char){
				case '{':
				case '[':
					if(!$in_string){
						$result .= $char . "\n" . str_repeat($tab, $ident+1);
						$ident++;
					}
					else
					{
						$result .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string){
						$ident--;
						$result .= "\n" . str_repeat($tab, $ident) . $char;
					}
					else{
						$result .= $char;
					}
					break;
				case ',':
					if(!$in_string){
						$result .= ",\n" . str_repeat($tab, $ident);
					}
					else{
						$result .= $char;
					}
					break;
				case ':':
					if(!$in_string){
						$result .= ": ";
					}
					else{
						$result .= $char;
					}
					break;
				case '\\':
					$search = substr($json, $c, 6);
					if(preg_match('/\\\u[0-9A-F]{4}/i', $search)){
						$utf16 = chr(hexdec(substr($search,2, 2)))
								.chr(hexdec(substr($search,4, 2)));
						$utf8 = mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
						$result .= $utf8;
						$c += 5;
					}else{
						$result .= $char;
					}
					break;
				case '(':
					if($c==0) break;
				case ')':
					if($c==$len-1) break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\'){
						$in_string = !$in_string;
					}
				default:
					$result .= $char;
					break; 
			}
		}
		if(!empty($options['js'])){
			$result = '('.$result.')';
		}
		return $result;
	}
	
	/**
	 * Clean up json string recieved as an HTTP parameter (at least via dojo.xhr)
	 * 
	 * @return string Cleaned up string
	 * @param $json string String to clean up
	 */
	public static function unescape($json){
		$json = str_replace('\\\\','\\',$json);
		$json = str_replace('\"','"',$json);
		$json = str_replace('\\\'','\'',$json);
		return $json;
	}
	
}
