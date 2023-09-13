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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // for create forgein key user
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users")->onUpdate('cascade')->onDelete('cascade');
            // for create forgein key post
            $table->unsignedBigInteger("post_id");
            $table->foreign("post_id")->references("id")->on("tea_posts")->onUpdate('cascade')->onDelete('cascade');
            $table->string("comment_detail");
            $table->date('comment_date_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
