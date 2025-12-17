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
        Schema::create('orderlineentries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('pid')->nullable();
            $table->string('orderCode')->nullable();
            $table->string('productCode')->nullable();
            $table->string('productName')->nullable();
            $table->string('productType')->nullable();
            $table->longText('config')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('pizzaSize')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->decimal('pizzaPrice', 10, 2)->nullable();
            $table->tinyInteger('isActive')->default(1);
            $table->tinyInteger('isDelete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderlineentries');
    }
};
