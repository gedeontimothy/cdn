<?php

namespace App\Http\Controllers;

use App\Traits\LinkResponse;
use Illuminate\Http\Request;

class FontController extends Controller
{

	use LinkResponse;

	public function show(Request $request, $name, $weight){ // %5C = \

		$original_name = $name . '\\' . $weight;

		$response = $this->showBaseByOriginalNameAndExtension($original_name, ['ttf', 'otf', 'woff'], $request);

		if($response !== false) return $response;

		abort(404);

	}
}
