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
        Schema::create('pizzas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('category_code', 50)->nullable();
            $table->string('pizza_name', 255)->nullable();
            $table->string('pizza_subtitle', 255)->nullable();
            $table->text('pizza_description')->nullable();
            $table->string('pizza_image', 255)->nullable();
            $table->text('pizza_prices')->nullable();
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
            $table->decimal('ratings', 10, 2)->default(4.0);
            $table->integer('reviews')->nullable();
            $table->tinyInteger('isDelete')->default(0);
            $table->string('addID', 50)->nullable();
            $table->string('addIP', 50)->nullable();
            $table->timestamp('addDate')->nullable();
            $table->string('editIP', 50)->nullable();
            $table->string('editID', 50)->nullable();
            $table->timestamp('editDate')->nullable();
            $table->string('deleteID', 50)->nullable();
            $table->string('deleteIP', 50)->nullable();
            $table->timestamp('deleteDate')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('pizzas');
    }
};
