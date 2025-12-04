<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create sections table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            
            $table->longText('title');
            $table->longText('subTitle')->nullable();
            $table->tinyInteger('isActive')->default(1);
            $table->timestamps();
        
        });

        // Create section_lineentries table
        Schema::create('section_lineentries', function (Blueprint $table) {
            $table->id();
            
            $table->integer('section_id');
            $table->longText('image')->nullable();
            $table->longText('title');
            $table->string('counter');
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_lineentries');
        Schema::dropIfExists('sections');
    }
};