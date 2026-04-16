<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::rename('webhooks_logs', 'webhook_logs');
    }

    public function down()
    {
        Schema::rename('webhook_logs', 'webhooks_logs');
    }
};
