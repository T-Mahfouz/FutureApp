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
            $table->boolean('is_request')->default(0)->after('valid');
            $table->unsignedBigInteger('user_id')->nullable()->after('is_request');
            $table->timestamp('requested_at')->nullable()->after('user_id');
            $table->timestamp('approved_at')->nullable()->after('requested_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_by');

            $table->foreign('user_id')->on('users')->references('id')->onDelete('set null');
            $table->foreign('approved_by')->on('admins')->references('id')->onDelete('set null');
            $table->foreign('rejected_by')->on('admins')->references('id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_request','user_id','requested_at','approved_at','rejected_at','rejection_reason','approved_by']);
        });
    }
};
