<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Casts\UserIsCast;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
	use SoftDeletes, HasFactory, Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'person_id',
		'username',
		'email',
		'password',
		'id_google',
		'is', // 0 : super | 1 : admin | 2 : agent | null : simple user
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
			'is' => UserIsCast::class,
		];
	}

	/**
	 * Get the person that owns the user
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function person() : BelongsTo
	{
		return $this->belongsTo(Person::class);
	}

	/**
	 * Check if user is a manager (super, admin or agent)
	 * 
	 * @return bool
	 */
	public function isManager() : bool
	{
		return array_search($this->is, ['super', 'admin', 'agent']) !== false;
	}

}
