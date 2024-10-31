<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Traits\LinkResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SplFileInfo;

class ImageController extends Controller
{

	use LinkResponse;

	public function show(Request $request, $index){

		$check = (bool) preg_match('/^(\d+)[.]?([a-z]+)?$/i', $index, $m);

		$response = $check && isset($m[1])? $this->showBaseByOriginalNameAndExtension(
			value : $m[1], 
			ext : null,
			request : $request,
			call : function($query, $value){
				$tb = $query->selectRaw('ROW_NUMBER() OVER (ORDER BY files.id) as numbering, files.*')
					->join('types', 'files.type_id', '=', 'types.id')
					->where('types.name', 'image')
					->toBase()
				;
				return File::query()
					->withTrashed()
					->selectRaw('*')
					->from(DB::raw("({$tb->toSql()}) as subquery"))
					->mergeBindings($tb)
					->where('numbering', (int) $value)
				;
			}
		) : false;
		if(!$response){
			$response = $this->showBaseByOriginalNameAndExtension(
				value : $index, 
				ext : null,
				request : $request,
				call : function($query, $value){
					$query->select('files.*')
						->join('types', 'files.type_id', '=', 'types.id')
						->where('types.name', 'image')
						->where('files.path', 'like', '%' . $value)
					;
				}
			);
		}

		if($response !== false) return $response;

		abort(404);
	}

	public function showRandom(Request $request){

		$file = File::inRandomOrder()->where('type_id', 1)->take(1)->first();

		if($file)
			return $this->responseFile($file, $request);

			abort(404);

	}

	public function showQuality(Request $request, int $quality, $index){

		$quality = abs($quality);

		if($quality <= 0 || $quality >= 100) abort(401);

		$response = $this->show($request, $index);

		$spl_file_info = $response->getFile();

		if(array_search($spl_file_info->getExtension(), ['jpg', 'jpeg', 'png', 'webp']) !== false){

			// $new_file_name = 'image-'. $quality . '-' . uniqid() . random_int(0, 20000) . '.' . $spl_file_info->getExtension();
			$new_file_name = $request->has('unique-file')
				? $index . '-' . $quality . '-' . uniqid() . random_int(0, 20000) . '.' . $spl_file_info->getExtension()
				: sha1($index . '|' . $quality . '|' . $spl_file_info->getPathname() . ($request->has('resize') ? 'r' : '')) . '.' . $spl_file_info->getExtension()
			;

			if(!is_dir(storage_path('app/public/temp_images/')))
				mkdir(storage_path('app/public/temp_images/'), recursive : true);

			$new_file_path = storage_path('app/public/temp_images/'. $new_file_name);
			
			$already_exists = is_readable($new_file_path);

			$file = $already_exists ? new SplFileInfo($new_file_path) : $this->reduce_image_quality(
				$spl_file_info->getPathname(),
				$new_file_path,
				$quality,
				$request->has('resize')
			);

			if($file) {

				$path_name = $file->getPathname();

				if($already_exists === false){

					dispatch(function () use ($path_name){

						if(is_writeable($path_name)) unlink($path_name);

					})->delay(is_numeric($request->get('delay')) 
						? now()->addMinutes((float) $request->get('delay'))
						: now()->addHour()
					);
					// })->delay(now()->addSeconds(20));

				}

				return $response->setFile($file);

			}

		}

		abort(401);

	}

	/**
	 * [Description for reduce_image_quality]
	 *
	 * @param string  $source_file
	 * @param string  $destination_file
	 * @param int     $percentage_reduction
	 * @param bool    $resize
	 * 
	 * @return bool|\SplFileInfo
	 * 
	 */
	private function reduce_image_quality(string $source_file, string $destination_file, int $percentage_reduction, bool $resize = true){

		if (!file_exists($source_file)) return false;

		if(($size = (new SplFileInfo($source_file))->getSize()) > (6 * 1024000)){
			$size_mo = $size / 1024000;
			$memory_limit = 128 + ($size_mo + (($size_mo * 1221) / 100));
			ini_set('memory_limit', ceil($memory_limit) . 'M');
		}
	
		// Obtient les informations de l'image
		list($width, $height, $image_type) = getimagesize($source_file);

		switch ($image_type) {

			case IMAGETYPE_JPEG: // 2

				$image = imagecreatefromjpeg($source_file);

				break;

			case IMAGETYPE_PNG: // 3

				$image = imagecreatefrompng($source_file);

				break;

			case IMAGETYPE_WEBP: // 18

				$image = imagecreatefromwebp($source_file);

				break;

			default:
				return false; // Si le format n'est pas supporté
		}

		$resized_image = $image;

		if($resize){

			$reduction_factor = $percentage_reduction / 100;

			$new_width = floor($width * $reduction_factor);
			$new_height = floor($height * $reduction_factor);

			$resized_image = imagecreatetruecolor($new_width, $new_height);

			imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			imagedestroy($image);

		}
	
		$quality = $percentage_reduction;

		if ($image_type == IMAGETYPE_JPEG) {

			imagejpeg($resized_image, $destination_file, $quality);

		}
		elseif ($image_type == IMAGETYPE_PNG) {

			$png_quality = floor($quality / 10);

			imagepng($resized_image, $destination_file, $png_quality);

		}
		elseif ($image_type == IMAGETYPE_WEBP){

			imagewebp($resized_image, $destination_file, $quality);

		}

		imagedestroy($resized_image);
	
		return new SplFileInfo($destination_file);

	}

	// protected function 
}
