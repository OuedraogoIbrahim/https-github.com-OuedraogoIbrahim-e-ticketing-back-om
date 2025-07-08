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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('description')->nullable();
            $table->string('photo')->nullable();
            $table->string("date_debut");
            $table->string("date_fin");
            $table->string('ville');
            $table->integer('prix');
            $table->string('heure_debut');
            $table->string('heure_fin');
            $table->string('nombre_tickets');
            $table->foreignId('organizer_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('event_type_id')->constrained("event_types")->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
