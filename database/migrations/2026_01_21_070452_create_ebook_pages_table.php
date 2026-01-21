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
       Schema::create('ebook_pages', function (Blueprint $table) {
    $table->engine = 'InnoDB'; // 🔥 MUST

    $table->id();
    $table->unsignedBigInteger('ebook_id');
    $table->integer('page_no');
    $table->string('image_path');
    $table->enum('orientation', ['portrait', 'landscape']);
    $table->integer('width');
    $table->integer('height');
    $table->timestamps();

    $table->foreign('ebook_id')
          ->references('id')
          ->on('ebooks')
          ->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebook_pages');
    }
};
