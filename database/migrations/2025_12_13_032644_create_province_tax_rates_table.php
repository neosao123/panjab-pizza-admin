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
        Schema::create('province_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('province_state')->nullable();
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('timezone')->nullable();
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
        Schema::dropIfExists('province_tax_rates');
    }
};
