<?php

namespace App\Modules\Subscriptions\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('subscription_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('subscription_id')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_usage_logs');
    }
};
