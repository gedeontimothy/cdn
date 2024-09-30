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

if(!function_exists('isIpV4Address')){
	/**
	 * Checks if a given string is a valid IP address.
	 *
	 * @param string $ip
	 * 
	 * @return bool
	 * 
	 */
	function isIpV4Address(string $ip) : bool{
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	}
}

if(!function_exists('isUrl')){
	/**
	 * Checks if a given string is a valid URL.
	 *
	 * @param string $url
	 * 
	 * @return bool
	 * 
	 */
	function isUrl(string $url) : bool{
		return filter_var($url, FILTER_VALIDATE_URL) !== false;
	}
}

if(!function_exists('isDomain')){
	/**
	 * Checks if a given string is a valid Domain.
	 *
	 * @param string $domain
	 * 
	 * @return bool
	 * 
	 */
	function isDomain(string $domain) : bool{
		return filter_var($domain, FILTER_VALIDATE_DOMAIN) !== false;
	}
}
