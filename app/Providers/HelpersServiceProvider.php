<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap services.
	 */
	public function boot(): void
	{
		$this->loadHelpers();
	}

	/**
	 * Load custom helper files.
	 * 
	 * @return void
	 */
	protected function loadHelpers()
	{
		foreach (glob(app_path('Helpers/*.php')) as $filename)
			require_once $filename;
	}
}
