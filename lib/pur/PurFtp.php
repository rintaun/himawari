<?php

/**
 * General static methods around the FTP protocol.
 */
class PurFtp{
	
	public static function connect($config){
		if(!isset($config['host'])) throw new Exception('Invalid Parameters: host is required');
		if(!isset($config['port'])) $config['port'] = 21;
		if(!isset($config['timeout'])) $config['timeout'] = 60;
		if(!isset($config['username'])) throw new Exception('Invalid Parameters: username is required');
		if(!isset($config['password'])) $config['password'] = '';
		if(!$con=ftp_connect($config['host'],$config['port'],$config['timeout'])){
			throw new Exception('FTP Connection Failed: '.$config['host'].':'.$config['port']);
		}
		if(!ftp_login($con,$config['username'],$config['password'])){
			throw new Exception('FTP Authentication Failed: '.$config['username']);
		}
		return $con;
	}
	
	/**
	 * Warning: Not tested, not flexible. Found on http://www.php.net/ftp_put as a 
	 * groundwork for later develepment
	 *   
	 * @return array
	 * @param $arrayToSort array
	 * @param $arrayKeysReference array
	 * @param $key string
	 */
	public static function copy($con,$source,$dest){
		$d = dir($source);
			while($file = $d->read()){
				if ($file != "." && $file != "..") {
					if (is_dir($source."/".$file)) {
						if (!@ftp_chdir($con, $dest."/".$file)) {
							ftp_mkdir($con, $dest."/".$file);
						}
						ftp_copy($source."/".$file, $dest."/".$file);
					}
					else{
						$upload = ftp_put($con, $dest."/".$file, $source."/".$file, FTP_BINARY);
					}
				}
			}
		$d->close();
	}
	
}
