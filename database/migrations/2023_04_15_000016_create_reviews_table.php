<?php

use App\Enums\Manuscript\ReviewDecision;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained();
            $table->foreignId('reviewer_id')->constrained(table: 'authors');
            $table->text('comments_to_author')->nullable();
            $table->enum('decision', ReviewDecision::values())->nullable();
            $table->text('comments_to_associate_editor')->nullable();
            $table->string('review_report')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
