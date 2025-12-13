<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('door_dashes', function (Blueprint $table) {
            $table->id();

            $table->text('mode')->nullable();

            $table->text('test_developer_id')->nullable();
            $table->text('live_developer_id')->nullable();

            $table->text('test_key_id')->nullable();
            $table->text('live_key_id')->nullable();

            $table->text('test_signing_secret')->nullable();
            $table->text('live_signing_secret')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('door_dashes');
    }
};
