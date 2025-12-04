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
            $table->string('mobile_number', 20);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('message_response')->nullable(); // Twilio response JSON

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
