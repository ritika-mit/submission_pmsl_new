<?php

use App\Enums\Manuscript\Event;
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
        Schema::create('revision_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->nullable()->constrained();
            $table->enum('event', Event::values());
            $table->string('value');
            $table->foreignId('created_by')->constrained(table: 'authors');
            $table->datetime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_events');
    }
};
