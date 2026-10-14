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
        Schema::create('plugiit_twitch_live_streamers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login', 25)->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 10)->default('approved');
            $table->string('display_name', 50)->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugiit_twitch_live_streamers');
    }
};
