<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;

class EmailVerification extends VerifyEmail
{
	/**
	 * Get the verify email notification mail message for the given URL.
	 *
	 * @param  string  $url
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	protected function buildMailMessage($url)
	{
		$user = request()->user();

		$person = $user->person;

		$name = $person->name . ($person->firstname ? ' ' . $person->firstname : '') . ($person->lastname ? ' ' . $person->lastname : '');

		return (new MailMessage)
			->subject(Lang::get('mail.email-verification.subject'))
			->view('mails.user.auth.mail-verification', [
				'url' => $url,
				'name' => $name,
				'min' => Config::get('auth.verification.expire', 60),
			])
		;
	}
}
