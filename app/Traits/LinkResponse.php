<?php

namespace App\Traits;

use Illuminate\Http\Request as BaseRequest;
use App\Models\File;
use App\Models\Link;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Builder;

trait LinkResponse
{
	public function showByKey($key, BaseRequest $request){

		$link = Link::where('key', $key)->get()->first();

		if($link){

			$file = $link->file;
			
			$req = Request::create([
				'link_id' => $link->id,
				'ip_address' => $request->ip(),
				'request_type' => $request->method(),
				'user_agent' => $request->userAgent(),
				'status' => '0', // 0:pending  1:completed  2:failed
			]);

			if($link->is_expired()) {
				$req->status = '2';
				$req->save();
				return response(status : 410, headers : ['Content-Length' => 0, 'Content-Type' => $file->mime_type->name .';charset=UTF-8']);
			}
			else{
				
				return $this->responseFile($file, $request, ['X-Link-Request' => '{"id" : ' . $req->id . '}']);
				
			}
		}
		return false;
	}

	public function responseFile($file, $request, array $headers = []){

		$content_type = $file->mime_type->name .';charset=UTF-8';

		set_time_limit(0);

		$chunkSizeLimit = 15 * 1024 * 1024; // 12 Mo

		// if($file->size > (15  * 1024 * 1024)){
		if($file->size > $chunkSizeLimit){
			// Limite de taille pour utiliser le chunk (12Mo)

			// Gérer la reprise du téléchargement en vérifiant l'en-tête 'Range'
			$range = $request->header('Range');

			$start = 0;
			$end = $file->size - 1;

			// Si un en-tête Range est présent, on calcule le début et la fin de la plage
			if ($range) {
				if (preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
					$start = intval($matches[1]);

					if (isset($matches[2])) {
						$end = intval($matches[2]);
					}
				}
			}

			// Utiliser StreamedResponse pour envoyer le fichier en morceaux
			$streamResponse = new StreamedResponse(function () use ($file, $start, $end) {
				$handle = fopen($file->path, 'rb');

				// Positionner le curseur du fichier à l'emplacement de départ
				fseek($handle, $start);

				// Lire et envoyer le chunk
				while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
					if ($pos + 8192 > $end) {
						// Si la prochaine lecture dépasse la fin, ajuster la taille de lecture
						echo fread($handle, $end - $pos + 1);
						file_put_contents(__DIR__ . '/uu.txt', file_get_contents(__DIR__ . '/uu.txt') . "$file->size | $start - $end\n\n");
					} else {
						echo fread($handle, 8192); // Lire en morceaux de 8 Ko
					}

					// Vider le tampon de sortie à chaque lecture pour ne pas surcharger la mémoire
					ob_flush();
					flush();
				}

				fclose($handle);
			});

			// Ajouter les en-têtes HTTP nécessaires pour indiquer que le fichier peut être repris (Range)
			$streamResponse->headers->set('Content-Type', $content_type);
			$streamResponse->headers->set('Content-Length', $end - $start + 1);
			$streamResponse->headers->set('Accept-Ranges', 'bytes');
			// $streamResponse->headers->set('Content-Disposition', "attachment; filename=\"". $file->original_name . '.' . $file->extension . "\"");

			if(count($headers) > 0)
				$streamResponse->headers->add($headers);


			// Si un Range est présent, répondre avec le code HTTP 206 (Partial Content)
			if ($range) {
				$streamResponse->setStatusCode(206); // HTTP 206 Partial Content
				$streamResponse->headers->set('Content-Range', "bytes $start-$end/" . $file->size);
			} else {
				// Sinon, renvoyer tout le fichier avec un code 200 (OK)
				$streamResponse->setStatusCode(200); // HTTP 200 OK
			}

			return $streamResponse;
		} 
		else {
			return response()->file($file->path, ['Content-Type' => $content_type, ...$headers]);
		}
	}

	public function showBaseByOriginalNameAndExtension(string $value, string|array $ext = null, BaseRequest $request, callable $call = null, array $more_categories = []){

		$ext = is_string($ext) ? [$ext] : $ext;

		$categories = array_unique(array_merge(is_array($request->get('category')) ? $request->get('category') : ($request->get('category') ? explode(',', $request->get('category')) : []), $more_categories));

		$query = File::query();

		if(is_array($ext) && count($ext) > 0)
			$query->whereIn('files.extension', $ext);

		if(count($categories) > 0){

			$query->join('file_categories', 'files.id', '=', 'file_categories.file_id');

			$query->join('categories', 'file_categories.category_id', '=', 'categories.id');

			$query->groupBy('files.id');

			$query->whereIn(DB::raw('LOWER(`categories`.`name`)'), $categories);

		}

		if($call) {
			$resp = $call($query, $value);
			if(is_object($resp)) $query = $resp;
		}
		else $query->where('files.path', 'like', '%' . $value);

		$file = $query->get()->first();

		if($file){

			return $this->responseFile($file, $request);

		}

		return false;

	}

}
