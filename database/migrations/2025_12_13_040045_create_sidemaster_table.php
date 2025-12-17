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
        Schema::create('sidemaster', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('sidename')->nullable();

            $table->tinyInteger('isActive')->default(1);
            $table->decimal('ratings')->default(4.0);
            $table->integer('reviews')->nullable();
            $table->tinyInteger('isDelete')->default(0);

            $table->string('addID');
            $table->string('addIP');
            $table->timestamp('addDate')->nullable();

            $table->string('editIP');
            $table->string('editID');
            $table->timestamp('editDate')->nullable();

            $table->string('deleteID')->nullable();
            $table->string('deleteIP')->nullable();
            $table->timestamp('deleteDate')->nullable();

            $table->string('image')->nullable();
            $table->string('type')->nullable();

            $table->tinyInteger('hasToppings')->nullable();
            $table->$table->bigInteger('nooftoppings')->nullable();

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
        Schema::dropIfExists('sidemaster');
    }
};
