<?php

use App\Modules\Subscriptions\Models\Feature;
use App\Modules\Subscriptions\Models\Plan;
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
            $table->string('currency_id');
            $table->integer('sort')->default(1);
            $table->integer('period')->default(30);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('type')->default('feature');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');
            $table->morphs('subscriber');
            $table->timestamp('starts_on')->nullable();
            $table->timestamp('expires_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();
            $table->timestamp('suppressed_at')->nullable();
            $table->boolean('was_switched')->default(false);
            $table->timestamps();
        });

        Schema::create('feature_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedDecimal('used')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->foreignIdFor(Feature::class)->constrained()->cascadeOnDelete();
            $table->morphs('subscriber');
            $table->timestamps();
        });

        Schema::create('feature_plan', function (Blueprint $table) {
            $table->id();
            $table->decimal('value')->nullable();
            $table->foreignIdFor(Feature::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Plan::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('features');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('feature_usages');
        Schema::dropIfExists('feature_plan');
    }
};
