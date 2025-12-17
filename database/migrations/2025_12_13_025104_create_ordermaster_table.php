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
        Schema::create('ordermaster', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('customerCode')->nullable();
            $table->string('customerName')->nullable();
            $table->string('customerEmail')->nullable();
            $table->string('mobileNumber')->nullable();
            $table->text('address')->nullable();
            $table->string('deliveryType')->nullable();
            $table->string('storeLocation')->nullable();
            $table->timestamps();
            $table->string('addID')->nullable();
            $table->string('editID')->nullable();
            $table->string('deletID')->nullable();
            $table->string('clientType')->nullable();
            $table->decimal('subTotal', 20, 2)->default(0);
            $table->decimal('discountAmount', 20, 2)->default(0);
            $table->decimal('discountPer', 20, 2)->nullable();
            $table->decimal('taxAmount', 20, 2)->default(0);
            $table->decimal('taxPer', 20, 2)->default(0);
            $table->decimal('grandTotal', 20, 2)->default(0);
            $table->timestamp('orderDate')->nullable();
            $table->string('txnId')->nullable();
            $table->string('transactionDate')->nullable();
            $table->string('orderFrom')->nullable();
            $table->string('orderCode')->nullable();
            $table->string('storeCode')->nullable();
            $table->string('deliveryExecutiveCode')->nullable();
            $table->decimal('deliveryCharges', 20, 2)->default(0);
            $table->decimal('extraDeliveryCharges', 20, 2)->default(0);
            $table->string('orderStatus')->nullable();
            $table->string('zipCode')->nullable();
            $table->string('paymentStatus')->nullable();
            $table->string('orderTakenBy')->nullable();
            $table->tinyInteger('isDeliveryTypeChange')->nullable();
            $table->text('comments')->nullable();
            $table->longText('webHookResponse')->nullable();
            $table->longText('paymentOrderId')->nullable();
            $table->longText('stripesessionid')->nullable();
            $table->longText('payment_link')->nullable();
            $table->longText('payment_link_id')->nullable();
            $table->longText('doordash_quote_id')->nullable();
            $table->longText('doordash_fee')->nullable();
            $table->longText('doordash_response')->nullable();
            $table->longText('doordash_status')->nullable();
            $table->longText('doordash_accept_response')->nullable();
            $table->longText('doordash_delivery_id')->nullable();
            $table->string('deviceType')->nullable();
            $table->string('deviceType')->nullable();
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamp('doordash_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordermaster');
    }
};
