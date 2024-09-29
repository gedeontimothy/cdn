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
		Schema::create('file_categories', function (Blueprint $table) {
			$table->id();
			$table->foreignId('file_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId('category_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('file_categories', function (Blueprint $table) {
			$table->dropForeign('file_categories_user_id_foreign');
			$table->dropForeign('file_categories_category_id_foreign');
		});
		Schema::dropIfExists('file_categories');
	}
};
