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
		Schema::create('links', function (Blueprint $table) {
			$table->id();
			$table->foreignId('file_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->text('key')->unique();
			$table->json('options')->default('{}');
			$table->bigInteger('access_count')->default(0);
			$table->timestamp('expired_at')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('links', function (Blueprint $table) {
			$table->dropForeign('links_file_id_foreign');
		});
		Schema::dropIfExists('links');
	}
};
