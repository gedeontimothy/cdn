<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Request as ModelRequest;

class UpdateLinkQueryMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$response = $next($request);


		$datas = (function($str){

			try { return json_decode($str);
			} catch (\Throwable $th) { return null;
			} catch (\Error $th) { return null; }

			return null;

		})($response->headers->get('X-Link-Request'));

		if($datas){

			$request = ModelRequest::find($datas->id);

			$status = isset($datas->{'status'}) ? $datas->status : null;

			$request->status = $status ?? ($response->isSuccessful() ? '1' : '2');

			$request->save();

		}

		return $response;
	}
}
