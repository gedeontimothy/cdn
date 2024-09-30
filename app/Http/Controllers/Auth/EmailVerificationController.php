<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Auth\Events\Verified;
// use Inertia\Inertia;
// use Inertia\Response as InertiaResponse;
use App\Notifications\EmailVerification;

class EmailVerificationController extends \App\Http\Controllers\Controller
{

	public $redirect_after = '/';

	public $redirect_after_verify = '/';

	public $redirect_already_verified = '/';

	// public function prompt(Request $request) : RedirectResponse|InertiaResponse {
	// 	return $request->user()->hasVerifiedEmail()
	// 		? redirect()->intended(isset($_GET['next']) ? $_GET['next'] : $this->redirect_after)
	// 		: Inertia::render('pages/user/auth/VerifyEmail')
	// 	;
	// }

	public function verify(Request $request) : RedirectResponse {

		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->intended($this->redirect_already_verified.'?verified=1');
		}

		if ($request->user()->markEmailAsVerified()) {
			event(new Verified($request->user()));
		}

		return redirect()->intended($this->redirect_after_verify.'?verified=1');
	}

	public function notification(Request $request) : RedirectResponse {
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->intended($this->redirect_already_verified);
		}

		$request->user()->notify(new EmailVerification);
		// $request->user()->sendEmailVerificationNotification();

		return back()->with('status', 'verification-link-sent');
	}

}
