<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('files', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId('type_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
			$table->foreignId('mime_type_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
			$table->text('path');
			$table->bigInteger('size');
			$table->string('name')->nullable();
			$table->string('original_name')->nullable();
			$table->string('extension')->nullable();
			$table->softDeletes();
			$table->timestamps();
			$table->unique(['type_id', 'mime_type_id', 'path', 'size', 'original_name', 'extension']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('files', function (Blueprint $table) {
			$table->dropForeign('files_user_id_foreign');
			$table->dropForeign('files_type_id_foreign');
			$table->dropForeign('files_mime_type_id_foreign');
			$table->dropIndex('files_type_id_mime_type_id_path_size_original_name_extension_unique');
		});
		Schema::dropIfExists('files');
	}
};
