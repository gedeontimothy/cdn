<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as BaseRequest;
use App\Models\File;
use App\Models\Link;
use App\Models\Request;
use App\Traits\LinkResponse;

class ReadLinkController extends Controller
{
	use LinkResponse;

	public function show(BaseRequest $request, $key) {

		$resp = $this->showByKey($key, $request);

		if($resp != false) return $resp;

		abort(404);

	}
}
