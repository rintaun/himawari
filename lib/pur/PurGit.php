<?php

/**
 * Wrapper method around the Git distributed version control system.
 */
class PurGit{
	
	public static $bin = 'git';
	
	/**
	 * Options may include:
	 * -   *cwd* Directory from where changes are to be commited
	 * -   *bin* Path to the Git command
	 * -   *message* Message associated to the commit
	 * 
	 * Exemples
	 * 
	 *     PurGit::commit('My message');
	 * 
	 *     PurGit::commit(array('message'=>'My message','cwd'=>$git));
	 * 
	 *     PurGit::commit('My message',array('cwd'=>$git));
	 * 
	 * @param array $options Options used to alter the method behavior
	 * @return boolean Return true on success or false if there was nothing to be commited
	 * 
	 * @param string $message required Message associated to the commit
	 * @param array $options optional All parameters are optional
	 * @return boolean Return true on success or false if there was nothing to be commited
	 */
	public static function commit(){
		$args = PurLang::args(
			func_get_args(),
			array(
				array('options.message'=>'string'),
				array('options'=>'array'),
				array('options.message'=>'string','options'=>'array')
			)
		);
		$args = $args['options'];
		// Note, at some point, we need to improve args
		// so it could handle empty keys
//		print_r(PurLang::args(
//			func_get_args(),
//			array(
//				array('message'=>'string'),
//				array(''=>'array'),
//				array('message'=>'string',''=>'array')
//			)
//		));
		if(empty($args['message'])){
			throw new InvalidArgumentException('No message provided');
		}
		if(empty($args['bin'])){
			$args['bin'] = self::$bin;
		}
		$status = self::status($args);
		if(empty($status['changed_to_be_committed'])){
			return false;
		}
		$command = $args['bin'];
		$command .= ' commit -m "'.str_replace('"','\\"',$args['message']).'"';
		PurCli::exec($command,$args['path']);
	}
	
	/**
	 * Options may include:
	 * -   *bin* Path to the Git command
	 * -   *cwd* Directory where changes are to be commited
	 * 
	 * @param array $options required Must include the directory path and the commit message
	 * @return boolean Return true on success or false if there was nothing to be commited
	 * 
	 * @param string $path required Path to the directory where changes are to be commited
	 * @param string $message required Message associated to the commit
	 * @param array $options optional All parameters are optional
	 * @return boolean Return true on success or false if there was nothing to be commited
	 */
	public static function remoteShow(){
		$args = func_get_args();
		$options = $args[0];
		if(empty($options['bin'])){
			$options['bin'] = self::$bin;
		}
		$return = array();
		$command = $options['bin'];
		$command .= ' config -l';
		$configs = PurCli::exec($command,$options);
		foreach($configs as $config){
			list($key,$value) = explode('=',$config);
			if(preg_match('/remote\.([\w]+)\.url/',$key,$matches)){
				$return[$matches[1]]['private'] = $value;
			}
		}
		/*
		foreach($remotes as $remote){
			$command = $options['bin'];
			$command .= ' remote show '.$remote;
			$out = PurCli::exec($command,$options);
			$return[$remote] = array();
			foreach($out as $line){
				// public git://github.com/pop/pop_website.git
				// private git@github.com:pop/pop_website.git
				if(preg_match('/Fetch URL\: ([\w]+@[\w]+.*\:.*)/',$line,$matches)){
					$return[$remote]['private'] = $matches[1];
				}
			}
		}
		*/
		return $return;
	}
	
	/**
	 * Options may include:
	 * -   *bare* Intialize a bare Git repository
	 * -   *bin* Path to the Git command
	 * -   *cwd* Directory or list of directories to initialize
	 * 
	 * @param array $options required Must include the "cwd" setting.
	 * @return true Return true on success, otherwise an exception is thrown
	 * 
	 * @param string $cwd required Path to the directory to initialize
	 * @param array $options optional All parameters are optional
	 * @return true Return true on success, otherwise an exception is thrown
	 */
	public static function init(){
		$args = func_get_args();
		switch(count($args)){
			case 1:
				if(is_string($args[0])){
					$options = array(
						'cwd' => array($args[0]=>true));
				}else if(is_array($args[0])){
					$options = PurArray::sanitize($args[0]);
					if(!isset($options['cwd'])){
						throw new InvalidArgumentException('Options must include the "cwd" key');
					}else if(is_string($options['cwd'])){
						$options['cwd'] = array($options['cwd']=>true);
					}else if(!is_array($options['cwd'])){
						throw new InvalidArgumentException('Invalid "cwd" option: "'.PurLang::toString($options['cwd']).'"');
					}
				}else{
					throw new InvalidArgumentException('Invalid Arguments: "'.PurLang::toString($args).'"');
				}
				break;
			case 2:
				if(is_string($args[0])&&is_array($args[1])){
					$options = PurArray::sanitize($args[1]);
					if(!isset($options[1]['cwd'])){
						$options['cwd'] = array();
					}
					$options['cwd'][$args[0]] = true;
				}else{
					throw new InvalidArgumentException('Invalid Arguments: "'.PurLang::toString($args).'"');
				}
				break;
			default:
				throw new InvalidArgumentException('Invalid Arguments Count: '.PurLang::toString($args));
		}
		unset($args);
		if(empty($options['bin'])){
			$options['bin'] = self::$bin;
		}
		while(list($cwd,) = each($options['cwd'])){
			if(!is_string($cwd)){
				throw new InvalidArgumentException('Invalid "cwd" option: "'.PurLang::toString($cwd).'"');
			}
			$command = $options['bin'];
			$command .= ' init';
			if(!empty($options['bare'])){
				$command .= ' --bare';
			}
			if(!is_dir($cwd)){
				PurFile::mkdir($cwd);
			}
			PurCli::exec($command,array_merge($options,array('cwd'=>$cwd)));
		}
		return true;
	}
	
	/**
	 * Options may include
	 * -   *cwd* Directory from from where the status must be run
	 * -   *bin* Path to the Git command
	 * -   *relative* Relative to the Git project root directory
	 * 
	 * @param array Options optional Options to alter the method behavior
	 * @return string Absolute Path of the project directory containing the .git directory
	 * 
	 * @param string $cwd optional Options to alter the method behavior
	 * @param array $options optional Options to alter the method behavior
	 * @return string Absolute Path of the project directory containing the .git directory
	 */
	public static function dir(){
		$args = func_get_args();
		switch(count($args)){
			case 0:
				$options = array();
				break;
			case 1:
				if(is_string($args[0])){
					$options = array(
						'cwd' => $args[0]);
				}else if(is_array($args[0])){
					$options = $args[0];
				}else{
					throw new InvalidArgumentException('Invalid Arguments: "'.PurLang::toString($args).'"');
				}
				break;
			default:
				throw new InvalidArgumentException('Invalid Arguments Count: '.PurLang::toString($args));
		}
		unset($args);
		if(empty($options['bin'])){
			$options['bin'] = self::$bin;
		}
		$command = $options['bin'];
		$command .= ' rev-parse --git-dir';
		$out = PurCli::exec($command,$options);
		return dirname($out[0]).'/';
	}
	
	/**
	 * Return an array representation of the git status command.
	 * 
	 * Options may include:
	 * -   *absolute* Return absolute paths
	 * -   *cwd* Directory from from where the status must be run
	 * -   *bin* Path to the Git command
	 * -   *relative* Relative to the Git project root directory
	 * 
	 * Structure of the returned array:
	 * -   changed_to_be_committed
	 * -   changed_but_not_updated
	 * -   untracked_files
	 * 
	 * @return array Decomposed status
	 * 
	 * @param array $options required Options used to alter the method behavior
	 * @return array Decomposed status
	 * 
	 * @param string $cwd required Path to the directory to initialize
	 * @return array Decomposed status
	 */
	public static function status(){
		$args = func_get_args();
		switch(count($args)){
			case 0:
				$options = array();
				break;
			case 1:
				if(is_string($args[0])){
					$options = array(
						'cwd' => $args[0]);
				}else if(is_array($args[0])){
					$options = $args[0];
				}else{
					throw new InvalidArgumentException('Invalid Arguments: "'.PurLang::toString($args).'"');
				}
				break;
			default:
				throw new InvalidArgumentException('Invalid Arguments Count: '.PurLang::toString($args));
		}
		unset($args);
		$options['return_message'] = 1;
		if(empty($options['bin'])){
			$options['bin'] = self::$bin;
		}
		$command = $options['bin'];
		$command .= ' status';
		$out = PurCli::exec($command,$options);
		$status = array(
			'changed_to_be_committed'=>array(),
			'changed_but_not_updated'=>array(),
			'untracked_files'=>array());
		$type = null;
		foreach($out as $line){
			if($line=='# Changes to be committed:'){
				$type = 'changed_to_be_committed';
			}else if($line=='# Changed but not updated:'){
				$type = 'changed_but_not_updated';
			}else if($line=='# Untracked files:'){
				$type = 'untracked_files';
			}else{
				if(preg_match('/^#\t((new file|modified|deleted): +)?(.*)/',$line,$matches)){
//					if(isset($options['cwd'])){
//						$matches[3] = PurPath::relative($options['cwd'],getcwd().'/'.$matches[3]);
//					}
					if(!empty($options['absolute'])){
						$matches[3] = PurPath::clean($options['cwd'].'/'.$matches[3]);
					}else if(!empty($options['relative'])){
						$matches[3] = PurPath::relative(self::dir($options),$options['cwd'].'/'.$matches[3]);
					}
					if(empty($matches[2])){
						$status[$type][] = $matches[3];
					}else{
						$status[$type][$matches[2]][] = $matches[3];
					}
				}
			} 
		}
		while(list($k,$v) = each($status)){
			if(empty($v)){
				unset($status[$k]);
			}
		}
		reset($status);
		return $status;
	}

}
