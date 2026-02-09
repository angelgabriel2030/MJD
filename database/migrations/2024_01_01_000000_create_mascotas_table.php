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
        Schema::create('mascotas', function (Blueprint $table) {
           $table->id();
            $table->string('nombre');       
            $table->string('animal');       
            $table->integer('edad');        
            $table->text('descripcion');    
            $table->string('raza');         
            $table->timestamps();
        });


        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    
    }
};
