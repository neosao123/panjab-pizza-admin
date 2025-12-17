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
        Schema::create('crust_type', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('crustType')->nullable();

            $table->tinyInteger('isActive')->default(1);
            $table->decimal('price', 10, 2)->nullable();
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
        Schema::dropIfExists('crust_type');
    }
};
