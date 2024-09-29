<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsManager
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next, string $role = null): Response
	{
		$user = $request->user();

		if(!($user && $user->isManager() && (empty($role) || array_search($user->is, explode(',', $role)) !== false))) return redirect('/');

		return $next($request);
	}
}
