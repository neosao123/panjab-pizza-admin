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
        Schema::create('customeraddress', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('customerCode')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();

            $table->tinyInteger('isActive')->default(1);
            $table->tinyInteger('isDelete')->default(0);

            $table->string('addID')->nullable();
            $table->string('addIP')->nullable();

            $table->string('deleteID')->nullable();
            $table->string('deleteIP')->nullable();
            $table->timestamp('deleteDate')->nullable();

            $table->timestamp('addDate')->nullable();

            $table->string('editIP')->nullable();
            $table->string('editID')->nullable();
            $table->timestamp('editDate')->nullable();

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
        Schema::dropIfExists('customeraddress');
    }
};
