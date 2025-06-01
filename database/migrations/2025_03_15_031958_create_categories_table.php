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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->boolean('active')->default(1);
            $table->text('description')->nullable();
            $table->foreign('parent_id')->on('categories')->references('id')->onDelete('set null');
            $table->foreign('city_id')->on('cities')->references('id')->onDelete('set null');
            $table->foreign('image_id')->on('media')->references('id')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
