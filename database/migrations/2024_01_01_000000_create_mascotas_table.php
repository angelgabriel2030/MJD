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

        Schema::create('nombre_reset_tokens', function (Blueprint $table) {
            $table->string('nombre')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('mascota_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('mascota_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
        Schema::dropIfExists('nombre_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
