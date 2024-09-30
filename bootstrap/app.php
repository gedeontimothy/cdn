<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureIsManager;
use App\Http\Middleware\UpdateLinkQueryMiddleware;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__.'/../routes/web.php',
		commands: __DIR__.'/../routes/console.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware
			->alias(aliases: [
				'update.link.query' => UpdateLinkQueryMiddleware::class,
				// 'manager' => EnsureIsManager::class,
			])
		;
	})
	->withCommands([
		__DIR__ . '/../app/Console/Commands',
	])
	->withExceptions(function (Exceptions $exceptions) {
		//
	})->create();
