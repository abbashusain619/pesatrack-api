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
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('currency'); // for M-Pesa
            $table->string('bank_account_number')->nullable()->after('phone_number');
            $table->string('bank_code')->nullable()->after('bank_account_number');
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_status')->default('pending'); // pending, verified, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
};
