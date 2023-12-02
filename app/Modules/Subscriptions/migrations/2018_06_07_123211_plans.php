<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->float('price', 8, 2);
            $table->string('currency');

            $table->integer('sort_order')->default(1);
            $table->integer('duration')->default(30);
            $table->jsonb('metadata')->nullable();


            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');

            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();

            $table->string('type')->default('feature');
            $table->integer('limit')->default(0);
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');

            $table->morphs('subscriber');
//            $table->integer('model_id');
//            $table->string('model_type');

//            $table->string('payment_method')->nullable();
//            $table->boolean('is_paid')->default(false);

            $table->float('charging_price', 8, 2)->nullable();
            $table->string('charging_currency')->nullable();

            $table->boolean('is_recurring')->default(true);
            $table->integer('recurring_each_days')->default(30);

            $table->timestamp('starts_on')->nullable();
            $table->timestamp('expires_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();

            $table->timestamps();
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->id();
            $table->integer('subscription_id');

            $table->string('code');
            $table->float('used', 9, 2)->default(0);

            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('features');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_usages');
    }
};
