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
        Schema::table('services', function (Blueprint $table) {

            $table->text('address')->nullable()->after('name');
            $table->string('whatsapp')->nullable()->after('facebook');
            $table->boolean('is_add')->default(0)->after('valid');

            $table->unsignedBigInteger('parent_id')->nullable()->after('name');
            $table->foreign('parent_id')->on('services')->references('id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            //
        });
    }
};
