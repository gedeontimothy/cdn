<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		"file_id",
		"key",
		"options",
		"access_count",
		"expire_at",
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'options' => 'array',
			'expired_at' => 'datetime',
		];
	}

	/**
	 * Get file relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function file() : BelongsTo
	{
		return $this->belongsTo(File::class);
	}

	/**
	 * Get requests relationship
	 * 
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function requests() : HasMany
	{
		return $this->hasMany(Request::class);
	}

	public function is_expired() : bool {
		return is_null($this->expired_at) ? false : $this->expired_at->timestamp <= time();
	}

	public function url() : string
	{
		return route('show.link.file', ['key' => $this->key]);
		// $this->file->mime_type->name
		// return route('name');
	}

}
