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
        Schema::create('door_dash_steps', function (Blueprint $table) {
            $table->id();
            $table->longText('order_id');
            $table->longText('doordash_status')->nullable();
            $table->longText('doordash_response')->nullable();
            $table->longText('doordash_delivery_id')->nullable();
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
        Schema::dropIfExists('door_dash_steps');
    }
};
