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
        Schema::create('storelocation', function (Blueprint $table) {
            $table->id();
              $table->string('code')->unique();
            $table->string('storeLocation')->nullable();
            $table->text('storeAddress')->nullable();
            $table->tinyInteger('isMain')->nullable();
            $table->tinyInteger('isActive')->default(1);
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
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->time('weekdays_start_time')->nullable();
            $table->time('weekdays_end_time')->nullable();
            $table->time('weekend_start_time')->nullable();
            $table->time('weekend_end_time')->nullable();
            $table->string('city')->nullable();
            $table->integer('tax_province_id')->default(1)->nullable();
            $table->string('timezone')->nullable();
            $table->longText('doordash_response')->nullable();
            $table->string('pickup_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storelocation');
    }
};
