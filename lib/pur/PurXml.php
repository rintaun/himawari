<?php

/**
 * Read and convert XML into a PHP array
 */
class PurXml{
	
	/**
	 * Convert an XML string or document to an array.
	 * 
	 * Cdata type is traited as a regular text type but the returned array contains a key 
	 * "cdata" with a boolean value set to true next to the text value.
	 * 
	 * @param string $source XML to transform
	 * @param array $options[optional] Options used to alter the method behavior
	 * 
	 * @return array Array representing the XML source
	 */
	public static function toArray($source,array $options=array()){
		$result = array();
		if(is_string($source)){
			$dom = new DOMDocument();
//			$dom->strictErrorChecking = false;
			$dom->substituteEntities = false;
			if(!preg_match('/^<\?.*?\?>/',$source)){
				$source = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$source;
			}
			if(!empty($options['html'])){
				$dom->loadHTML($source);
//				$dom->loadXML($source,LIBXML_NOBLANKS);
			}else{
				$dom->loadXML($source,LIBXML_NOBLANKS);
			}
			$source = $dom;
			unset($dom);
		}
		if($source instanceof DOMDocument){
			$result['declaration'] = array(
				'version' => $source->xmlVersion,
				'standalone' => $source->xmlStandalone
			);
			if($source->xmlEncoding){
				$result['declaration']['encoding'] = $source->xmlEncoding;
			}
			if($source->doctype){
				$result['doctype'] = array();
				$result['doctype']['name'] = $source->doctype->name;
				if($source->doctype->publicId){
					$result['doctype']['public'] = $source->doctype->publicId;
				}
				if($source->doctype->systemId){
					$result['doctype']['system'] = $source->doctype->systemId;
					
					// If any elements, attributes, or entities are used in the XML document 
					// that are referenced or defined in an external DTD, standalone="no" 
					// must be included in the XML declaration
					$result['declaration']['standalone'] = false;
				}else{
					$result['declaration']['standalone'] = true;
					$internal = $source->doctype->internalSubset;
					if(preg_match('/(\[.*\])/s',$internal,$matches)){
						$result['doctype']['internal'] = $matches[1];
					}
				}
			}
			$source = $source->documentElement;
		}
		$result['name'] = $source->localName;
		if($source->prefix){
			$result['prefix'] = $source->prefix;
		}
		foreach($source->attributes as $attribute){
			$attributeResult = array();
			$attributeResult['name'] = $attribute->name;
			if($attribute->prefix){
				$attributeResult['prefix'] = $attribute->prefix;
			}
			$attributeResult['value'] = $attribute->value;
			$result['attributes'][] = $attributeResult;
		}
		$children = $source->childNodes;
		foreach($children as $child){
			switch(get_class($child)){
				case 'DOMElement':
					$result['children'][] = self::toArray($child,$options);
					break;
				case 'DOMText':
					$result['children'][] = array('value'=>$child->nodeValue);
					break;
				case 'DOMCdataSection':
					$result['cdata'] = true;
					$result['children'][] = array('value'=>$child->nodeValue);
					break;
				default:
					echo get_class($child)."\n";
			}
		}
		if(isset($result['children'])&&count($result['children'])===1&&!isset($result['children'][0]['name'])&&isset($result['children'][0]['value'])){
			$result['value'] = $result['children'][0]['value'];
			unset($result['children']);
		}
		return $result;
	}
	
	/**
	 * Converted an array to an XML string.
	 * 
	 * Options may include:
	 * - -*prefix* Prefix string which may be used for indentation-
	 * - *tab* Characteres used for tabulation
	 * - *return* Characteres used for line returns
	 * - *dtd* XML DTD string
	 * - *declaration* XML declaration string
	 * - *instructions* XML instructions array of strings
	 * - *from_encoding* Destination encoding, default to "UTF-8"
	 * - *to_encoding* Source encoding, default to "UTF-8"
	 * - *docktype* Will only return the XML doctype
	 * - *declaration* Will only return the XML declaration
	 * 
	 * Element options may include
	 * - *cdata* Wrap element value in XML CDATA construct
	 * - *no_entities* Disable entities encoding, notice that it may break your XML
	 * 
	 * @param object $source
	 * @param object $options [optional]
	 * @return 
	 */
	public static function toString($source,$options=array()){
		// Some note between htmlspecialchars and htmlentities
		// The 2 functions are identical except with htmlentities(), 
		// all characters which have HTML character entity equivalents 
		// are translated into these entities.
//		if(empty($options['prefix'])){
//			$options['prefix'] = '';
//		}
		if(!isset($options['elements'])){
			$options['elements'] = array();
		}
		if(empty($options['from_encoding'])){
			$options['from_encoding'] = 'UTF-8';
		}
		if(empty($options['return'])){
			$options['return'] = "\n";
		}
		if(empty($options['tab'])){
			$options['tab'] = "\t";
		}
		if(empty($options['to_encoding'])){
			$options['to_encoding'] = 'UTF-8';
		}
		$result = '';
		// Deal with XML declaration
		$declaration = null;
		if(!empty($source['declaration'])){
			switch(gettype($source['declaration'])){
				case 'string':
					if(!empty($options['to_encoding'])&&preg_match('/encoding="(.*)"/i',$source['declaration'])){
						$source['declaration'] = preg_replace('/encoding="(.*)"/i','encoding="'.$options['to_encoding'].'"',$source['declaration']);
					}
					$declaration .= $source['declaration'];
					break;
				case 'boolean':
					$declaration .= '<?xml version="1.0" encoding="'.$options['to_encoding'].'" ?>';
					break;
				case 'array':
					$declaration .= '<?xml';
					if(isset($source['declaration']['version'])){
						$declaration .= ' version="'.$source['declaration']['version'].'"';
					}
					if(isset($source['declaration']['encoding'])){
						$declaration .= ' encoding="'.(!empty($options['to_encoding'])?$options['to_encoding']:$source['declaration']['encoding']).'"';
					}
					if(isset($source['declaration']['standalone'])){
						$declaration .= ' standalone="'.($source['declaration']['standalone']?'yes':'no').'"';
					}
					$declaration .= ' ?>';
					break;
				default:
					throw new Exception('Invalid declaration "'.PurLang::toString($source['declaration']).'"');
			}
		}
		if(!empty($options['declaration'])){
			return $declaration;
		}
		if($declaration){
			$result .= $declaration.$options['return'];
		}
		// Deal with XML doctype
		$doctype = null;
		if(!empty($source['doctype'])){
			switch(gettype($source['doctype'])){
				case 'string':
					$doctype .= $source['doctype'];
					break;
				case 'array':
					$doctype .= '<!DOCTYPE';
					if(isset($source['doctype']['name'])&&is_string($source['doctype']['name'])){
						$doctype .= ' '.$source['doctype']['name'];
					}else{
						throw new Exception('Invalid doctype name "'.PurLang::toString($source['doctype']['name']).'"');
					}
					if(isset($source['doctype']['public'])){
						$doctype .= ' PUBLIC "'.$source['doctype']['public'].'"';
					}
					if(isset($source['doctype']['system'])){
						if(!isset($source['doctype']['public'])){
							$doctype .= ' SYSTEM';
						}
						$doctype .= ' "'.$source['doctype']['system'].'"';
					}
					if(isset($source['doctype']['internal'])){
						$doctype .= ' '.$source['doctype']['internal'].'';
					}
					$doctype .= '>';
					break;
				default:
					throw new Exception('Invalid doctype "'.PurLang::toString($source['doctype']).'"');
			}
		}
		if(!empty($options['doctype'])){
			return $doctype;
		}
		if($doctype){
			$result .= $doctype.$options['return'];
		}
		// Deal with nodes
		$works = array(array(null,array($source),null));
		while(count($works)){
			list(,$work) = $works[0];
			while(list($k,$v) = each($work)){
				unset($work[$k]);
				if(isset($v['name'])){
					if(isset($v['children'])){
						if(!empty($options['no_format'])||!empty($v['no_format'])||!empty($options['elements'][$v['name']]['no_format'])){
							foreach($v['children'] as &$child){
								$child['return'] = '';
								$child['tab'] = '';
							}
							reset($v['children']);
							$v['return_in'] = '';
							$v['tab_in'] = '';
						}else{
							$noFormat = false;
							foreach($v['children'] as $child){
								if(!isset($child['name'])){
									$noFormat = true;
									break;
								}
							}
							reset($v['children']);
							if($noFormat){
								foreach($v['children'] as &$child){
									$child['return'] = '';
									$child['tab'] = '';
								}
								reset($v['children']);
								$v['return_in'] = '';
								$v['tab_in'] = '';
							}
						}
					}
					if(!isset($v['return'])){
						$v['return'] = $options['return'];
					}
					if(!isset($v['return_in'])){
						$v['return_in'] = $options['return'];
					}
					if(!isset($v['tab'])){
						$v['tab'] = $options['tab'];
					}
					if(!isset($v['tab_in'])){
						$v['tab_in'] = $options['tab'];
					}
					$result .=
						implode('',array_pad(array(),count($works)-1,$v['tab']));
					
					$attributes = '';
					if(isset($v['attributes'])){
						foreach($v['attributes'] as $attribute){
							$value = mb_convert_encoding($attribute['value'],$options['to_encoding'],$options['from_encoding']);
							if(empty($attribute['no_entities'])){
								$value = htmlspecialchars($value,ENT_QUOTES,$options['to_encoding']);
							}
							$attributes .= 
								' '.
								(empty($attribute['prefix'])?'':$attribute['prefix'].':').
								$attribute['name'].
								'="'.$value.'"';
							unset($value);
						}
						$attributes = rtrim($attributes);
					}
					$result .= '<'.(empty($v['prefix'])?'':$v['prefix'].':').$v['name'].$attributes;
					if(isset($v['value'])){
						$result .= '>';
						$value = mb_convert_encoding($v['value'],$options['to_encoding'],$options['from_encoding']);
						if(empty($v['cdata'])){
							if(empty($v['no_entities'])){
								$value = htmlspecialchars($value,ENT_QUOTES,$options['to_encoding']);
							}
							$result .= $value;
						}else{
							$result .= '<![CDATA['.$value.']]>';
						}
						unset($value);
						$result .= '</'.(empty($v['prefix'])?'':$v['prefix'].':').$v['name'].'>'.$v['return'];
					}else if(isset($v['children'])){
						$result .= '>'.$v['return_in'];
						$works[0][1] = $work;
						$work = $v['children'];
						array_unshift($works, array($v,$v['children']));
					}else{
						$result .= ' />'.$v['return'];
					}
				}else{
					$value = mb_convert_encoding($v['value'],$options['to_encoding'],$options['from_encoding']);
					if(empty($v['no_entities'])){
						$value = htmlspecialchars($value,ENT_QUOTES,$options['to_encoding']);
					}
					$result .= $value;
				}
			}
			list($parent) = array_shift($works);
			if($parent){
				$result .=
					implode('',array_pad(array(),count($works)-1,$parent['tab_in'])).
					'</'.(empty($parent['prefix'])?'':($parent['prefix'].':')).$parent['name'].'>'.$parent['return'];
			}
		}
		return $result;
	}
	
}
