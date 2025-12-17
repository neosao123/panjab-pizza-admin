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
        Schema::create('signaturepizza', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('category_code')->nullable();

            $table->string('pizza_name')->nullable();
            $table->string('pizza_subtitle')->nullable();
            $table->longText('pizza_description')->nullable();

            $table->string('pizza_image')->nullable();

            $table->longText('pizza_prices')->nullable();

            $table->text('cheese')->nullable();
            $table->text('crust')->nullable();
            $table->text('crust_type')->nullable();
            $table->text('special_base')->nullable();
            $table->text('spices')->nullable();
            $table->text('sauce')->nullable();
            $table->text('cook')->nullable();

            $table->text('topping_as_1')->nullable();
            $table->text('topping_as_2')->nullable();
            $table->text('topping_as_free')->nullable();

            $table->tinyInteger('isActive')->default(1);
            $table->decimal('ratings')->default(4.0);
            $table->integer('reviews')->nullable();
            $table->tinyInteger('isDelete')->default(0);

            $table->string('addID')->nullable();
            $table->string('addIP')->nullable();
            $table->timestamp('addDate')->nullable();

            $table->string('editIP')->nullable();
            $table->string('editID')->nullable();
            $table->timestamp('editDate')->nullable();

            $table->string('deleteID')->nullable();
            $table->string('deleteIP')->nullable();
            $table->timestamp('deleteDate')->nullable();

            $table->longText('description')->nullable();
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
        Schema::dropIfExists('signaturepizza');
    }
};
