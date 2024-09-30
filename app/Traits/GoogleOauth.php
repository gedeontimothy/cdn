<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Person;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

trait GoogleOauth
{
	public $redirect_after_login = '/';

	public $redirect_after_register = '/';

	public function redirect()
	{
		return Socialite::driver('google')->redirect();
	}

	public function callbackGoogle()
	{
		try {
			$google_user = Socialite::driver('google')->user();

			$email = $google_user->getEmail();
			
			$user = User::where('id_google', $google_user->getId())->first();
			
			if(!$user) $user = User::where('email', $email)->first();

			$redirect = '/';

			if($user) $redirect = $this->redirect_after_login;
			else{
				$username = preg_replace('/@.*$/', '', $email);
				
				$file = $google_user->avatar ? File::firstOrCreate([
					'name' => 'google-avatar-' . $username,
					'url' => $google_user->avatar,
				]) : null;
				
				$user = User::create([
					'person_id' => Person::create([
						'file_id' => $file ? $file->id : null,
						'name' => $google_user->getName(),
						'firstname' => !empty($google_user->getNickname()) ? $google_user->getNickname() : $google_user->getName(),
					])->id,
					'email' => $email,
					'email_verified_at' => now(),
					'username' => $username,
					'id_google' => $google_user->getId(),
				]);

				$redirect = $this->redirect_after_register;
			}

			Auth::login($user);

			return redirect()->intended(isset($_GET['next']) ? $_GET['next'] : $redirect);

		} catch (\Throwable $e) {
			file_put_contents(__DIR__.'/er.txtr1', $e->getMessage());
			return redirect('/login')->withErrors(__('Something went wrong ! Retry.'));

		}
	}
}
