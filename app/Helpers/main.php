<?php

if(!function_exists('check_console_admin_mode')){
	/**
	 * Check if the console is in administrator mode
	 *
	 * @return bool
	 * 
	 */
	function check_console_admin_mode() : bool{
		if(checkOS('windows')){
			exec('net session', result_code : $code);
			return $code === 0;
		}
		elseif(checkOS('linux') || checkOS('darwin')){
			exec('whoami', $output, $code);
			return $code === 0 && trim($output[0]) === 'root';
		}
		return false;
	}
}

if(!function_exists('checkOS')){
	/**
	 * Checks if the current operating system matches the specified operating system name.
	 *
	 * @param string $os
	 * @return bool
	 
	*/
	function checkOS(string $os) : bool {
		return stripos(strtolower(PHP_OS_FAMILY), strtolower($os)) !== false; 
	}
}
