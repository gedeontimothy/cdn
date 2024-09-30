<?php

namespace App\Http\Controllers;

use App\Traits\LinkResponse;
use Illuminate\Http\Request;

class CssController extends Controller
{

	use LinkResponse;

	public function show(Request $request, $original_name){

		$response = $this->showBaseByOriginalNameAndExtension($original_name, 'css', $request);

		if($response !== false) return $response;

		abort(404);

	}
}
