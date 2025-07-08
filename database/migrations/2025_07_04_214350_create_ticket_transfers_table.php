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
        Schema::create('ticket_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onUpdate('cascade')->onDelete('cascade');   
            $table->foreignId('from_client_id')->constrained('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('to_client_id')->constrained("clients")->onUpdate('cascade')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamp('transferred_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_transfers');
    }
};
