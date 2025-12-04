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
        Schema::create('twilio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('twilio_session_id');
            $table->string('twilio_auth_id');
            $table->string('twilio_number');
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
        Schema::dropIfExists('twilio_settings');
    }
};
