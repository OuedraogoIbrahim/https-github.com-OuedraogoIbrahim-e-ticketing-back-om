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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->date('date_achat');
            $table->string('token')->unique(); // Ajout pour le QR code
            $table->date('date_utilisation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
