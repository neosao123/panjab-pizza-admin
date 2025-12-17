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
        Schema::create('rolesmaster', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('role', 255);
            $table->tinyInteger('isActive')->default(1);
            $table->tinyInteger('isDelete')->default(0);
            $table->string('addID', 50)->nullable();
            $table->string('addIP', 50)->nullable();
            $table->string('deleteID', 50)->nullable();
            $table->string('deleteIP', 50)->nullable();
            $table->timestamp('deleteDate')->nullable();
            $table->timestamp('addDate')->nullable();
            $table->string('editIP', 50)->nullable();
            $table->string('editID', 50)->nullable();
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
        Schema::dropIfExists('rolesmaster');
    }
};
