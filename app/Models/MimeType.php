<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MimeType extends Model
{
	use HasFactory;

	/**
	 * Configuration of `created_at` attribut
	 * 
	 * @var CREATED_AT
	 */
	const CREATED_AT = NULL;

	/**
	 * Configuration of `updated_at` attribut
	 * 
	 * @var UPDATED_AT
	 */
	const UPDATED_AT = NULL;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		"name"
	];

	/**
	 * Get files relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function files() : HasMany
	{
		return $this->hasMany(File::class);
	}


}
