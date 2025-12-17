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
        Schema::create('specialoffer', function (Blueprint $table) {
            $table->id();
             $table->string('code')->unique();
            $table->string('name')->nullable();

            $table->string('specialofferphoto')->nullable();
            $table->text('description')->nullable();

            $table->string('noofToppings')->nullable();
            $table->string('noofDips')->nullable();
            $table->string('noofSides')->nullable();

            $table->longText('type')->nullable();

            $table->tinyInteger('isActive')->default(1);
            $table->tinyInteger('isDelete')->default(0);

            $table->decimal('ratings')->default(4.0);
            $table->integer('reviews')->nullable();

            $table->string('addID')->nullable();
            $table->string('addIP')->nullable();
            $table->timestamp('addDate')->nullable();

            $table->string('editID')->nullable();
            $table->string('editIP')->nullable();
            $table->timestamp('editDate')->nullable();

            $table->string('deleteID')->nullable();
            $table->string('deleteIP')->nullable();
            $table->timestamp('deleteDate')->nullable();

            $table->decimal('price', 20, 2)->nullable();

            $table->string('noofPizza')->nullable();
            $table->string('pops')->nullable();
            $table->string('bottle')->nullable();
            $table->decimal('extraLargePrice', 20, 2)->nullable();

            $table->string('subtitle')->nullable();
            $table->string('dealType')->default('otherdeal');

            $table->tinyInteger('showOnClient')->default(1);

            $table->longText('pizza_prices')->nullable();

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->tinyInteger('limited_offer')->nullable();
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
        Schema::dropIfExists('specialoffer');
    }
};
