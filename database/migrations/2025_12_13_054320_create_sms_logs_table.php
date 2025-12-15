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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('template_id');
            $table->text('template_message');
            $table->string('mobile_number');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])
                  ->default('pending');
            $table->longText('message_response')->nullable();
            $table->timestamp('sent_at')->nullable();
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
        Schema::dropIfExists('sms_logs');
    }
};
