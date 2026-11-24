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
        Schema::create('recurso_pesebres', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 60)->unique();
    $table->string('categoria');
    $table->unsignedInteger('cantidad');
    $table->unsignedInteger('stockMinimo');
    $table->string('estado');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurso_pesebres');
    }
};
