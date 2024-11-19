<?php

use App\Http\Controllers\CssController;
use App\Http\Controllers\JsController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ReadLinkController;
use App\Http\Controllers\SvgController;
use App\Http\Controllers\FontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return view('welcome');
});

Route::get('/link/{key}', [ReadLinkController::class, 'show'])->name('show.link.file')->where('key', '.*')->middleware('update.link.query');

Route::get('/css/{original_name}', [CssController::class, 'show'])->name('show.css')->where('original_name', '.*');
Route::get('/js/{original_name}', [JsController::class, 'show'])->name('show.js')->where('original_name', '.*');

Route::get('/font/{name}/{weight}', [FontController::class, 'show'])->name('show.font')->where('weight', '.*');
Route::get('/font/{name}.{ext}', fn($name, $ext) => to_route('show.font', ['name' => $name, 'weight' => 'regular.' . $ext]));
Route::get('/font/{name}', fn($name) => to_route('show.font', ['name' => $name, 'weight' => 'regular.ttf']));

Route::middleware('cors.all')->group(function(){
	Route::get('/icon/svg-color/{p}', fn($p) => to_route('show.svg', ['original_name' => $p, 'category' => 'colored']))->where('p', '.*');
	Route::get('/icon/svg/{original_name}', [SvgController::class, 'show'])->name('show.svg')->where('original_name', '.*');
	Route::get('/icon/svg/{original_name}', [SvgController::class, 'iconShow'])->name('show.svg')->where('original_name', '.*');
});

Route::get('/random/image', [ImageController::class, 'showRandom'])->name('show.random.image')->where('index', '.*');

Route::get('/image/{quality}/{index}', [ImageController::class, 'showQuality'])->name('show.quality.image')->where(['quality' => '\d+', 'index' => '.*']);

Route::get('/image/{index}', [ImageController::class, 'show'])->name('show.image')->where('index', '.*');

