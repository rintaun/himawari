<?php

/**
 * General static methods around the HTTP protocol.
 */
class PurHttp{
	
	// 2xx Success
	const CODE_200 = 'OK';
	const CODE_201 = 'Created';
	const CODE_202 = 'Accepted';
	const CODE_203 = 'Non-Authoritative Information';
	const CODE_204 = 'No Content';
	const CODE_205 = 'Reset Content';
	const CODE_206 = 'Partial Content';
	const CODE_207 = 'Multi-Status (WebDAV)';
	// 3xx Redirection
	const CODE_300 = 'Multiple Choices';
	const CODE_301 = 'Moved Permanently';
	const CODE_302 = 'Found';
	const CODE_303 = 'See Other';
	const CODE_304 = 'Not Modified';
	const CODE_305 = 'Use Proxy';
	const CODE_306 = 'Switch Proxy';
	const CODE_307 = 'Temporary Redirect';
	// 4xx Client Error
	const CODE_400 = 'Bad Request';
	const CODE_401 = 'Unauthorized';
	const CODE_402 = 'Payment Required';
	const CODE_403 = 'Forbidden';
	const CODE_404 = 'Not Found';
	const CODE_405 = 'Method Not Allowed';
	const CODE_406 = 'Not Acceptable';
	const CODE_407 = 'Proxy Authentication Required';
	const CODE_408 = 'Request Timeout';
	const CODE_409 = 'Conflict';
	const CODE_410 = 'Gone';
	const CODE_411 = 'Length Required';
	const CODE_412 = 'Precondition Failed';
	const CODE_413 = 'Request Entity Too Large';
	const CODE_414 = 'Request-URI Too Long';
	const CODE_415 = 'Unsupported Media Type';
	const CODE_416 = 'Requested Range Not Satisfiable';
	const CODE_417 = 'Expectation Failed';
	const CODE_418 = 'I\'m a teapot';
	const CODE_422 = 'Unprocessable Entity (WebDAV)';
	const CODE_423 = 'Locked (WebDAV)';
	const CODE_424 = 'Failed Dependency (WebDAV)';
	const CODE_425 = 'Unordered Collection';
	const CODE_426 = 'Upgrade Required';
	const CODE_449 = 'Retry With';
	// 5xx Server Error
	const CODE_500 = 'Internal Server Error';
	const CODE_501 = 'Not Implemented';
	const CODE_502 = 'Bad Gateway';
	const CODE_503 = 'Service Unavailable';
	const CODE_504 = 'Gateway Timeout';
	const CODE_505 = 'HTTP Version Not Supported';
	const CODE_506 = 'Variant Also Negotiates';
	const CODE_507 = 'Insufficient Storage (WebDAV)';
	const CODE_509 = 'Bandwidth Limit Exceeded (Apache bw/limited extension)';
	const CODE_510 = 'Not Extended';
	
	// 2xx Success
	const SUCCESS_OK = 200;
	const SUCCESS_CREATED = 201;
	const SUCCESS_ACCEPTED = 202;
	const SUCCESS_NON_AUTHORITATIVE_INFORMATION = 203;
	const SUCCESS_NO_CONTENT = 204;
	const SUCCESS_RESET_CONTENT = 205;
	const SUCCESS_PARTIAL_CONTENT = 206;
	const SUCCESS_MULTI_STATUS_WEBDAV = 207;
	// 3xx Redirection
	const REDIRECTION_MULTIPLE_CHOICES = 300;
	const REDIRECTION_MOVED_PERMANENTLY = 301;
	const REDIRECTION_FOUND = 302;
	const REDIRECTION_SEE_OTHER = 303;
	const REDIRECTION_NOT_MODIFIED = 304;
	const REDIRECTION_USE_PROXY = 305;
	const REDIRECTION_SWITCH_PROXY = 306;
	const REDIRECTION_TEMPORARY_REDIRECT = 307;
	// 4xx Client Error
	const CLIENT_ERROR_BAD_REQUEST = 400;
	const CLIENT_ERROR_UNAUTHORIZED = 401;
	const CLIENT_ERROR_PAYMENT_REQUIRED = 402;
	const CLIENT_ERROR_FORBIDDEN = 403;
	const CLIENT_ERROR_NOT_FOUND = 404;
	const CLIENT_ERROR_METHOD_NOT_ALLOWED = 405;
	const CLIENT_ERROR_NOT_ACCEPTABLE = 406;
	const CLIENT_ERROR_PROXY_AUTHENTICATION_REQUIRED = 407;
	const CLIENT_ERROR_REQUEST_TIMEOUT = 408;
	const CLIENT_ERROR_CONFLICT = 409;
	const CLIENT_ERROR_GONE = 410;
	const CLIENT_ERROR_LENGTH_REQUIRED = 411;
	const CLIENT_ERROR_PRECONDITION_FAILED = 412;
	const CLIENT_ERROR_REQUEST_ENTITY_TOO_LARGE = 413;
	const CLIENT_ERROR_REQUEST_URI_TOO_LONG = 414;
	const CLIENT_ERROR_UNSUPPORTED_MEDIA_TYPE = 415;
	const CLIENT_ERROR_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const CLIENT_ERROR_EXPECTATION_FAILED = 417;
	const CLIENT_ERROR_I_M_A_TEAPOT = 418;
	const CLIENT_ERROR_UNPROCESSABLE_ENTITY_WEBDAV = 422;
	const CLIENT_ERROR_LOCKED_WEBDAV = 423;
	const CLIENT_ERROR_FAILED_DEPENDENCY_WEBDAV = 424;
	const CLIENT_ERROR_UNORDERED_COLLECTION = 425;
	const CLIENT_ERROR_UPGRADE_REQUIRED = 426;
	const CLIENT_ERROR_RETRY_WITH = 449;
	// 5xx Server Error
	const SERVER_ERROR_INTERNAL_SERVER_ERROR = 500;
	const SERVER_ERROR_NOT_IMPLEMENTED = 501;
	const SERVER_ERROR_BAD_GATEWAY = 502;
	const SERVER_ERROR_SERVICE_UNAVAILABLE = 503;
	const SERVER_ERROR_GATEWAY_TIMEOUT = 504;
	const SERVER_ERROR_HTTP_VERSION_NOT_SUPPORTED = 505;
	const SERVER_ERROR_VARIANT_ALSO_NEGOTIATES = 506;
	const SERVER_ERROR_INSUFFICIENT_STORAGE_WEBDAV = 507;
	const SERVER_ERROR_BANDWIDTH_LIMIT_EXCEEDED = 509;
	const SERVER_ERROR_NOT_EXTENDED = 510;
	
	/**
	 * Check if a URL exist online.
	 * 
	 * Note, this implementation require the PHP CURL extension installed.
	 * 
	 * @return boolean True if URL exists
	 * @param string $url URL to check
	 */
	public static function exists($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		preg_match_all("/HTTP\/1\.[1|0]\s(\d{3})/",$data,$matches);
		$code = end($matches[1]);
		if(!$data) {
			// Domain could not be found
			return false;
		} else {
			if($code==200) {
				// Page Found
				return true;
			} elseif($code==404) {
				// Page Not Found
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Extract all urls from a provided string.
	 * 
	 * @return array Extracted urls
	 * @param string $text Text to search
	 * @param object $options[optional]
	 */
	public static function extractUrls($text,$options=array()){
		list($protocol,$domain,$path,$page,$query) = array_values(self::urlPattern());
		$search = array();
		$search[] = "/(^| )(".$protocol.$domain.$path.$page.$query.")(&| )/i";
		$replace = array();
		$replace[] = '$1<a href="$2">$2</a>$15';
		if(!empty($options['optional_protocol'])){
			$search[] = "/(^| )(".$domain.$path.$page.$query.")(&| )/i";
			$replace[] = '$1<a href="http://$2">http://$2</a>$13';
		}
		return preg_replace($search,$replace,$text);
	}
	
	/**
	 * Detect if a provided string match a URL pattern.
	 * 
	 * @return boolean True if provided url is valid string, false otherwise
	 * @param string $url Url to validate
	 */
	public static function isUrl($url){
		if(!is_string($url)) return false;
		return (bool) preg_match("/^".implode(self::urlPattern())."$/i",$url);
	}
	
	/**
	 * Return a URL content or write its output to file if a destination is provided.
	 * 
	 * Note, this implementation require the PHP CURL extension installed.
	 * 
	 * @param string $url Content source
	 * @param string $destination [optional] File destination where output should be written
	 * @return mixed URL content or boolean true if a destination is provided
	 */
	public static function read($url,$destination=null){
		if(!function_exists('curl_init')){
			throw new Exception('PHP CURL Extension Required');
		}
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_HEADER,false);
		if($destination){
			PurFile::mkdir(dirname($destination));
			if(file_exists($destination)) PurFile::delete($destination);
			$fp = fopen($destination,'w');
			curl_setopt($ch,CURLOPT_FILE,$fp);
		}else{
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1) ;
		}
		if(($data = curl_exec($ch))===false){
			throw new Exception('Failed to download url: "'.$url.'"');
		}
		$header = curl_getinfo($ch);
		curl_close($ch);
		if($destination){
			fclose($fp);
		}
		if($header['http_code']!=self::SUCCESS_OK){
			if($destination){
				PurFile::delete($destination);
			}
			throw new Exception('Download Failed: "'.constant('PurHTTP::'.'CODE_'.$header['http_code']).'" ('.$header['http_code'].')');
		}
		return $data;
	}
	
	/**
	 * Return a URL or an extended array with URL information.
	 * 
	 * If URL is provided as an array, it may include the following options:
	 * - show_port: if true, the port will always be present in url even if equals 80
	 * 
	 * @param object $info
	 * @param object $extended [optional]
	 * @return 
	 */
	public static function url($url=null,$extended=false){
		if(is_string($url)){
			// Optimization if a url is provided as well as expected to be returned
			if(!$extended) return $url;
			$url = array('url'=>$url);
		}else if(is_null($url)){
			$url = array();
			$url['server'] = true;
		}else if(!is_array($url)){
			throw new InvalidArgumentException('If provided, first parameter "info" is expected to be a string, an array or null');
		}
		if(!empty($url['url'])){
			if(preg_match("/^(\w+)\:\/\/([\-_a-z0-9\.]*)(\:(\d+))?(\/?.*)?/i",$url['url'],$matches)){
				$url['protocol'] = $matches[1];
				$url['domain'] = $matches[2];
				$url['port'] = $matches[4]?intval($matches[4]):80;
				$url['path'] = $matches[5];
			}else{
				throw new InvalidArgumentException('Malformed URL: "'.$url.'"');
			}
		}
		// Provide default values from $_SERVER if url not provided or option "server" is provided
		if(!empty($url['server'])){
			if(empty($url['protocol'])){
				$url['protocol'] = strtolower(substr($_SERVER['SERVER_PROTOCOL'],0,strpos($_SERVER['SERVER_PROTOCOL'],'/')));
			}
			if(empty($url['domain'])){
				$url['domain'] = $_SERVER['HTTP_HOST'];
			}
			if(empty($url['port'])){
				$url['port'] = intval($_SERVER['SERVER_PORT']);
			}
			if(empty($url['path'])){
				$url['path'] = $_SERVER['REQUEST_URI'];
			}
		}
		if(!isset($url['parameters'])){
			$url['parameters'] = array();
		}
		if($question=strpos($url['path'],'?')){
			$url['query'] = substr($url['path'],$question+1);
			$url['path'] = substr($url['path'],0,$question);
			unset($question);
		}
		if(!empty($url['query'])){
			$queries = explode('&',$url['query']);
			$parameters = array();
			foreach($queries as $query){
				list($k,$v) = explode('=',$query);
				if(!isset($url['parameters'][$k])){
					$parameters[$k] = urldecode($v);
				}
				unset($k,$v);
			}
			$url['parameters'] = array_merge($parameters,$url['parameters']);
			unset($parameters);
		}
		if(!empty($url['parameters'])){
			$url['query'] = array();
			foreach($url['parameters'] as $k=>$v){
				$url['query'][] = $k.'='.urldecode($v);
				unset($k,$v);
			}
			$url['query'] = implode('&',$url['query']);
		}
		if(isset($url['server'])) unset($url['server']);
		$url['url'] = $url['protocol'].
				'://'.
				$url['domain'].
				($url['port']!=80||!empty($url['show_port'])?':'.$url['port']:'').
				$url['path'].
				(empty($url['query'])?'':'?'.$url['query']);
		return $extended?$url:$url['url'];
	}
	
	public static function urlPattern(){
		return array(
			'protocol'=>"((\w*)\:\/\/)",
			'domain'=>"([a-z][\-[a-z0-9]*[a-z0-9])(\.[a-z0-9][\-_[a-z0-9]*[a-z0-9])+",
			'path'=>"((\/)?[a-z0-9][\-_a-z0-9]*)*",
			'page'=>"(\/)?([a-z0-9][\-_a-z0-9]*\.[a-z]{3,5})?",
			'query'=>"(\?([a-z0-9][-_%a-z0-9]*=[\-_%a-z0-9]+)(&([a-z0-9][-_%[a-z0-9]]*=[\-_%[a-z0-9]]+))*)?",
		);
	}
	
}
