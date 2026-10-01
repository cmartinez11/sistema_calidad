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
        // creamos la tabla maquinas
        Schema::create('maquinas', function (BluePrint $table){
            $table->id();
            $table->string('codigo',50)->unique();
            $table->string('nombre',100);
            $table->enum('estado',['activo','mantenimiento','inactivo'])->default('activo');
            $table->timestamps();
        });

        //creamos la tabla productos
        Schema::create('productos', function (BluePrint $table){
           $table->id();
           $table->string('codigo',50)->unique();
           $table->string('nombre',150);
           $table->boolean('activo')->default(true);
           $table->timestamps(); 
        });

        //creamos la tabla turno
        Schema::create('turnos', function(Blueprint $table){
            $table->id();
            $table->string('nombre',10);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('maquinas');
    }
};
