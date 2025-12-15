<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('payment_mode'); // 0 = Test, 1 = Live
            $table->text('test_secret_key');
            $table->text('test_client_id');
            $table->text('live_secret_key');
            $table->text('live_client_id');
            $table->text('webhook_secret_key');
            $table->text('webhook_secret_live_key');
            $table->text('payment_gateway')->default("stripe");
            $table->tinyInteger('isActive')->default(1);
            $table->tinyInteger('isDelete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_settings');
    }
};
