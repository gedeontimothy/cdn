<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Traits\LinkResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SvgController extends Controller
{

	use LinkResponse;

	public function show(Request $request, $original_name){
	public function iconShow(Request $request, $original_name){

		$response = $this->showBaseByOriginalNameAndExtension($original_name, 'svg', $request);

		if($response !== false) return $response;

		abort(404);

	}
}
