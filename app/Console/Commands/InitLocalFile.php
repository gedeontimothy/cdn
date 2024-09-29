<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\Link;
use App\Models\MimeType;
use App\Models\Person;
use App\Models\Type;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File as BaseFile;
use Illuminate\Support\Facades\Hash;

class InitLocalFile extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:init-local-file
	                        {folder? : Folder path to recover files}
	                        {type? : File type}
	                        {--disable-permanent-link : Disable permanent linking}
	                        {--view : View the file}
	                        {--recursive-folder : Recursive folder}
	                        {--category : Add categories for each file}
	                        {--only-extension= : Have only files of specified types}
	                        {--categories= : Add more category for files (Separate by `|`)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Initialize local files from a specified folder, with options for filtering file types, assigning categories, recursive search and preview.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if($this->argument('folder') && $this->argument('type')){
			// dd($this->argument('folder'), $this->argument('type'));
			if(is_dir($this->argument('folder')) && is_readable($this->argument('folder'))){
				$this->make(
					$this->argument('folder'),
					$this->argument('type'),
					($this->option('categories') 
						? [] 
						: explode('|', $this->option('categories'))
					),
					($this->option('only-extension') 
						? [] 
						: explode('|', $this->option('only-extension'))
					),
					$this->argument('recursive-folder'),
				);
			}
			// else // Error, folder error 
		}
		else{
			foreach (self::getFolders() as $value) {
				$this->make(
					$value['folder'],
					isset($value['type']) 
						? $value['type'] 
						: self::class . '::autoDetectType'
					,
					isset($value['categories']) ? $value['categories'] : [],
					isset($value['only-extension']) ? $value['only-extension'] : [],
					isset($value['recursive-folder']) ? $value['recursive-folder'] : false
				);
			}
		}
	}

	protected function make(array|string $dir, string|callable $type, array $categories = [], array $only_extensions = [], bool $recursive_folder = false){

		$dirs = is_string($dir) ? [$dir] : $dir;

		if($recursive_folder){
			foreach ([...$dirs] as $folder_path) {
				$dirs = array_merge($dirs, BaseFile::directories($folder_path));
			}
		}

		$categories = array_map(fn ($arg) => Category::firstOrCreate(['name' => $arg]), $categories);

		foreach ($dirs as $kk =>  $folder) { // file_put_contents(__DIR__ . '/r/dirs.txt', $kk);
			// if($kk < 1) continue;

			if(is_dir($folder) && is_readable($folder)){

				$absolute_folder_path =  trim(trim($folder, '/'), '\\');

				$files = BaseFile::files($absolute_folder_path);

				$user = self::getUser();

				foreach ($files as $key => $file) { // file_put_contents(__DIR__ . '/r/dirs.txt', file_get_contents(__DIR__ . '/r/dirs.txt') . ', ' . $key);
					// if($key < 66) continue;
					// dump($key);

					$ext = $file->getExtension();

					if(count($only_extensions) > 0 && !is_numeric(array_search($ext, $only_extensions))) continue;

					$absolute_file_path = $file->getPathname();

					// $categories_ = array_map(fn ($arg) => Category::firstOrCreate(['name' => $arg]), $categories);
					$categories_ = $categories;

					if($this->option('view')){
						switch (strtolower(PHP_OS_FAMILY)) {
							case 'windows':
								exec('explorer /select,"' . str_replace('"', '\\"', $absolute_file_path) . '"');
								break;
							case 'linux':
								exec('nautilus --select "' . str_replace('"', '\\"', $absolute_file_path) . '" 2>/dev/null');
								break;
							case 'darwin':
								exec('open -R "' . str_replace('"', '\\"', $absolute_file_path) . '"');
								break;
						}
						$this->info("\n View this file : \"" . str_replace('"', '\\"', $absolute_file_path) . '"');

						if(!$this->option('category')) $this->ask(__('Click to Entre to continue...'));
					}

					if($this->option('category')){
						$input_categories = explode(',', $this->ask(__('Add categories')));

						foreach ($input_categories as $cat_){
							if(trim($cat_) != ''){
								$categories_[] = Category::firstOrCreate(['name' => trim($cat_)]);
							}
						}
					}

					$mime = MimeType::firstOrCreate(['name' => BaseFile::mimeType($file->getRealPath())]);


					$type = is_callable($type) ? $type(...['mime' => $mime, 'file' => $file, 'user' => $user]) : $type;

					$type_ = Type::firstOrCreate(['name' => $type]);

					$file_ = File::firstOrCreate([
						"user_id" => $user->id,
						"type_id" => $type_->id,
						"mime_type_id" => $mime->id,
						"path" => $file->getRealPath(),
						"size" => $file->getSize(),
						"name" => $type_->name . '-' . uniqid() . random_int(0, 20000),
						"original_name" => $file->getBasename($ext == '' ? '' : ('.' . $ext)),
						"extension" => $ext,
					]);
					if(!$this->option('disable-permanent-link')) {
						Link::create([
							'file_id' => $file_->id,
							'key' => uniqid() . random_int(0, 20000),
						]);
					}

					array_map(function($cat) use ($file_) { return FileCategory::create(['file_id' => $file_->id, 'category_id' => $cat->id]); }, $categories_);

					$this->info("\n " . (count($dirs) > 1 ? "Dirs : [" . ($kk + 1) . "/" . count($dirs) . "] - " : '') . $file->getRealPath() . " [" . ($key + 1) . "/" . count($files) . "] - " . round((($key + 1) * 100) / count($files), 2) . '%');
				}
			}
			else $this->error(" Unable to access $folder folder, please check if : \n    - This folder exists.                      \n    - You have read rights to this folder.     ");

		}
	}

	protected static function getUser() : User {
		return (User::where('email', env('COMMAND_LOCAL_FILE_USER_EMAIL', 'johndoe@mail.com'))->get()->first() ?? User::where('username', env('COMMAND_LOCAL_FILE_USER_USERNAME', 'johndoe'))->get()->first()) ?? User::create([
			'person_id' => Person::create([
				"name" => env('COMMAND_LOCAL_FILE_USER_NAME', 'Doe'),
				"firstname" => env('COMMAND_LOCAL_FILE_USER_FIRSTNAME', 'John'),
				"lastname" => env('COMMAND_LOCAL_FILE_USER_LASTNAME', 'X'),
				"sex" => env('COMMAND_LOCAL_FILE_USER_SEX', 'm'),
			])->id,
			'email' => env('COMMAND_LOCAL_FILE_USER_EMAIL', 'johndoe@mail.com'),
			'username' => env('COMMAND_LOCAL_FILE_USER_USERNAME', 'johndoe'),
			'password' => Hash::make(env('COMMAND_LOCAL_FILE_USER_PASSWORD', '12345678')),
			'is' => "0",
			'email_verified_at' => env('COMMAND_LOCAL_FILE_USER_EMAIL_VERIFIED', 1) == 1 ? now() : null,
		]);
	}

	public static function autoDetectType($mime, $file, $user){
		// $mime->name
		return preg_match('/^' . preg_quote('application/vnd.openxmlformats-officedocument', '/') . '.*/', $mime->name) || $mime->name == 'application/msword' || $mime->name == 'application/pdf'
			? 'document'
			: (preg_match('/^' . preg_quote('font/', '/') . '.*/i', $mime->name)
				? 'font'
				: (preg_match('/^' . preg_quote('audio/', '/') . '.*/i', $mime->name)
					? 'audio'
					: (preg_match('/^' . preg_quote('image/', '/') . '.*/i', $mime->name)
						? 'image'
						: (preg_match('/^' . preg_quote('video/', '/') . '.*/i', $mime->name)
							? 'video'
							: (preg_match('/^' . preg_quote('text/', '/') . '.*/i', $mime->name)
								? 'text'
								: null
							)
						)
					)
				)
			)
		;
	}

	protected static function getFolders(){
		return [
			[
				'folder' => 'C:\\Backup\\Pictures\\Templates Images\\Original',
				'categories' => ['Original', 'Full', 'Template'],
				'only-extension' => ['jpeg', 'jpg', 'png'],
			],
			[
				'folder' => 'C:\\Backup\\Videos',
				'categories' => ['Film'],
				'only-extension' => ['mp4', 'avi', 'mkv'],
			],
			[
				'folder' => ['C:\\Backup\\Music\\World', 'C:\\Backup\\Music\\Heaven'],
				'categories' => ['Music'],
				'only-extension' => ['mpga', 'mp2', 'mp2a', 'mp3', 'm2a', 'm3a', 'm4a', 'mp4a', 'oga', 'ogg', 'spx', 'weba', 'wav', 'flac', 'dts'],
				'recursive-folder' => true,
			],
			[
				'folder' => 'C:\\Backup\\Pictures\\Cover',
				'categories' => ['Music', 'Cover'],
			],
			[
				'folder' => 'C:\\xampp\\htdocs\\cdn-old\\storage\\files\\css',
				'categories' => ['Code', 'Css'],
			],
			[
				'folder' => 'C:\\xampp\\htdocs\\cdn-old\\storage\\files\\js',
				'categories' => ['Code', 'Js'],
			],
			[
				'folder' => 'C:\\xampp\\htdocs\\cdn-old\\storage\\files\\icons\\svg',
				'categories' => ['Code', 'Svg', 'Icon', 'Not Colored'],
			],
			[
				'folder' => 'C:\\xampp\\htdocs\\cdn-old\\storage\\files\\icons\\svg-color\\',
				'categories' => ['Code', 'Svg', 'Icon', 'Colored'],
			],
			[
				'folder' => 'C:\\xampp\\htdocs\\cdn-old\\storage\\files\\icons\\svg-modify\\',
				'categories' => ['Code', 'Svg', 'Icon', 'Modified'],
			],
		];
	}
}
