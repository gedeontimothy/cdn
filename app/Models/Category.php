<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
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
	 * Get file_categories relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function file_categories() : HasMany
	{
		return $this->hasMany(FileCategory::class);
	}


}
