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
		Schema::create('requests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('link_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->string('ip_address');
			$table->string('request_type')->nullable();
			$table->text('user_agent')->nullable();
			$table->enum('status', [0, 1, 2])->default(0); // 0:pending  1:completed  2:failed
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('requests', function (Blueprint $table) {
			$table->dropForeign('requests_link_id_foreign');
		});
		Schema::dropIfExists('requests');
	}
};
