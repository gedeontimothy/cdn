<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LocalDomain extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:local-domain
	                        {--host=127.0.1.1 : The host address (ipv4)}
	                        {--domain=* : Local domain name (default : [\'cdn.net\', \'www.cdn.net\'])}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Generates a local domain name linked to an IP address.\n  This command must be executed as administrator mode.";

	/**
	 * Execute the console command.
	 */
	public function handle()
	{

		$host_address = $this->option('host');

		if(isIpV4Address($host_address)){

			$domains = count($this->option('domain')) > 0 ? $this->option('domain') : ['cdn.net', 'www.cdn.net'];
	
			$hosts_file_path = null;
			
			if(is_null($hosts_file_path) || !is_file($hosts_file_path)) {

				$this->error("\n The hosts file for your operating system was not found" . (!is_null($hosts_file_path) 
					? ' (on the path ' . $hosts_file_path . ')' 
					: ' (The path to your '. strtolower(PHP_OS_FAMILY) .' system\'s `hosts` file is not supported automatically, please provide your `hosts` file path in the `getHostsFilePath` method of the `App\Console\Commands\LocalDomain` command class)'
				) . ".");

				return;
			}

			$mode = check_console_admin_mode();

			if($mode === true || is_null($mode)){

				if(is_writeable($hosts_file_path) && is_readable($hosts_file_path)){

					$host_content = file_get_contents($hosts_file_path);

					$hosts = $this->parseHostsFile($host_content);

					$update = false;

					foreach ($domains as $domain) {

						$domain = strtolower($domain);

						if(isDomain($domain)){
							if(!array_key_exists($domain, $hosts)){

								$host_content .= "\n    ". $host_address . '      ' . $domain . '     # CDN Local domain';

								$update = true;

							}

							else $this->warn('The domain ' . $domain . ' already exists on IP "' . $hosts[$domain] . '"');

						}

						else{

							$this->error('Domain ' . $domain . ' is invalid !');

							return;

						}

					}

					if($update) {

						file_put_contents($hosts_file_path, $host_content . "\n\n");

						$this->info(" Local domain generated ");

					}

				}
				else $this->error("\n Unable to access $hosts_file_path file, please check if : \n\n    - This file exists. \n\n    - You have read and write rights to this file. ");

			}
			else $this->error("\n Please run the command in " . (checkOS('windows') ? 'administrator' : 'super user') . " mode. ");

		}

	}

	public function parseHostsFile($content) {

		$hosts = [];

		$lines = explode(PHP_EOL, $content);

		foreach ($lines as $line) {

			$line = trim($line);


			if (empty($line) || strpos($line, '#') === 0) {

				continue;

			}

			$parts = preg_split('/\s+/', $line, 2);

			if (count($parts) === 2) {

				$ip = $parts[0];

				$domain = trim($parts[1]);

				if(trim($domain) != ''){

					preg_match('/^(\s+)?([^\s]+).*/i', $domain, $m);

					$hosts[trim($m[2])] = $ip;

				}

			}

		}

		return $hosts;
	}

	/**
	 * Get hosts file path
	 *
	 * @return string|null
	 * 
	 */
	public function getHostsFilePath() : string|null {

		return checkOS('windows')
			? getenv('SystemRoot') . '\\System32\\drivers\\etc\\hosts'
			: (checkOS('linux') || checkOS('darwin')
				? '/etc/hosts'
				: null
			)
		;

	}
}
