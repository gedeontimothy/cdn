<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Person;
use App\Models\File;
use App\Notifications\EmailVerification;
use App\Traits\GoogleOauth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class MainController extends \App\Http\Controllers\Controller
{
	
	use GoogleOauth;

	public $redirect_after_login = '/';

	public $redirect_after_logout = '/';

	public $redirect_after_register = '/';

	// public function signIn() {
	// 	return Inertia::render('pages/public/auth/Login');
	// }
	
	// public function signUp() {
	// 	return Inertia::render('pages/public/auth/Register');
	// }
	
	public function login(LoginRequest $request) {

		$request->authenticate();

		$request->session()->regenerate();

		return redirect()->intended(isset($_GET['next']) ? $_GET['next'] : (Auth::user()->isManager() ? '/dashboard' : $this->redirect_after_login));

	}

	public function register(RegisterRequest $request) : RedirectResponse {

		$valid_datas = $request->validationData();
		
		$user = User::create([
			'person_id' => Person::create([
				'name' => $valid_datas['name'],
				'firstname' => $valid_datas['firstname'],
				'lastname' => $valid_datas['lastname'],
				'sexe' => $valid_datas['sexe'],
			])->id,
			'username' => $valid_datas['username'],
			'email' => $valid_datas['email'],
			'password' => Hash::make($valid_datas['password']),
		]);

		Auth::login($user);

		$user->notify(new EmailVerification);

		return redirect()->intended(isset($_GET['next']) ? $_GET['next'] : $this->redirect_after_register);

	}

	/**
	 * Destroy an authenticated session.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy(Request $request)
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return redirect()->intended(isset($_GET['next']) ? $_GET['next'] : $this->redirect_after_logout);
	}

}
