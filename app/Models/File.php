<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
	use HasFactory, SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		"user_id",
		"type_id",
		"mime_type_id",
		"path",
		"size",
		"name",
		"original_name",
		"extension",
	];

	/**
	 * Get user relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() : BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get type relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function type() : BelongsTo
	{
		return $this->belongsTo(Type::class);
	}

	/**
	 * Get mime_type relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function mime_type() : BelongsTo
	{
		return $this->belongsTo(MimeType::class);
	}

	/**
	 * Get file_categories relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function file_categories() : HasMany
	{
		return $this->hasMany(FileCategory::class);
	}

	/**
	 * Get links relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function links() : HasMany
	{
		return $this->hasMany(Link::class);
	}

}
