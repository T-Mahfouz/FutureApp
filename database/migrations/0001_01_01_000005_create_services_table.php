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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('phone');

            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            
            $table->string('name');
            $table->text('brief_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('lat')->nullable();
            $table->string('lon')->nullable();

            $table->text('website')->nullable();
            $table->text('youtube')->nullable();
            $table->text('facebook')->nullable();
            $table->text('instagram')->nullable();
            $table->text('telegram')->nullable();
            $table->text('video_link')->nullable();

            $table->boolean('valid')->default(1);
            $table->integer('arrangement_order')->default(1);
            
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
        Schema::dropIfExists('services');
    }
};
