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
		Schema::dropIfExists('users');
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->foreignId('person_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->string('username')->unique();
			$table->string('email')->unique();
			$table->string('password')->nullable();
			$table->string('id_google')->unique()->nullable();
			$table->enum('is', ["0", "1", "2"])->nullable(); // 0 : super | 1 : admin | 2 : agent | null : simple user
			$table->timestamp('email_verified_at')->nullable();
			$table->rememberToken();
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropForeign('users_person_id_foreign');
		});
		Schema::dropIfExists('users');
	}
};
