<?php

/**
 * General static methods used with databases.
 */
class PurSql{
	
	/**
	 * Split a string of queries into an array of queries.
	 * 
	 * This method may be used to split the result of a MySQL dump into
	 * multiple queries.
	 * 
	 * @return array Queries extracted from provided string
	 * @param string $query String containing one or multiple queries
	 */
	public static function split($query){
		$query = trim($query);
		$length = strlen($query);
		$count = 0;
		$escape = null;
		$queries = array();
		$start = 0;
		while($count<$length){
			$char = $query[$count];
			// check commented line starting with "--"
			if($char=='-'&&$query[$count+1]=='-'&&($count==0||in_array($query[$count-1],array("\r","\n")))){
				while($count<$length&&!in_array($query[$count],array("\r","\n"))){
					$count++;
				}
				$start = $count;
				continue;
			}
			if($char=='#'&&($count==0||in_array($query[$count-1],array("\r","\n")))){
				while($count<$length&&!in_array($query[$count],array("\r","\n"))){
					$count++;
				}
				$start = $count;
				continue;
			}
			if($char=='`'||($char=='\''&&$count&&$query[$count-1]!='\\')){
				if(is_null($escape)){
					$escape = $char;
				}else if($escape==$char){
					$escape = null;
				}
				$count++;
				continue;
			}
			if(($char==';'&&is_null($escape))||$count+1==$length){
				$queries[] = trim(substr($query,$start,$count+1-$start));
				$start = $count+1;
			}
			$count++;
		}
		return $queries;
	}
	
	/**
	 * Convert a unix timestamp in second to a SQL datetime representation.
	 * 
	 * @return string timestamp
	 * @param string $date SQL date to convert
	 */
	public static function toDatetime($date){
		return gmstrftime("%Y-%m-%d %H:%M:%S",$date);
	}
	
	/**
	 * Convert a unix timestamp in second to a SQL date representation.
	 * 
	 * @return string timestamp
	 * @param string $date SQL date to convert
	 */
	public static function toDate($date){
		return gmstrftime("%Y-%m-%d",$date);
	}
	
	/**
	 * Convert an sql date to a unix timestamp in second.
	 * The provided date may be a year, a date, a time or a datetime.
	 * 
	 *     PurSql::toTimestamp('2007-12-45 16:15:00');
	 *     PurSql::toTimestamp('2007-12-45');
	 *     PurSql::toTimestamp('16:15:00');
	 *     PurSql::toTimestamp('2007');
	 * 
	 * @return null if date parameter is invalid or timestamp as int
	 * @param $date String
	 */
	public static function toTimestamp($date){
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/",$date,$regs)){
			return gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}else if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/",$date,$regs)){
			return gmmktime(0, 0, 0, $regs[2], $regs[3], $regs[1]);
		}else if(preg_match("/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/",$date,$regs)){
			return gmmktime($regs[1], $regs[2], $regs[3], 1, 1, 1970);
		}else if(preg_match("/^([0-9]{4})$/",$date,$regs)){
			return gmmktime(0, 0, 0, 1, 1, $regs[1]);
		}
		throw new InvalidArgumentException('Invalid Date: '.PurLang::toString($date));
	}
	
}
