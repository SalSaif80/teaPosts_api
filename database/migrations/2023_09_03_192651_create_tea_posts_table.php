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
        Schema::create('tea_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users")->onUpdate('cascade')->onDelete('cascade');

            $table->string("tea_name");
            $table->string("tea_image_path");
            $table->string("tea_type");
            $table->string("how_to_prepare_tea");
            $table->string("tea_in_water_time")->nullable();
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tea_posts');
    }
};
