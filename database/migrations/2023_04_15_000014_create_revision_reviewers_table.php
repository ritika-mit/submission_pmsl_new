<?php

use App\Enums\Section;
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
        Schema::create('revision_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->nullable()->constrained();
            $table->morphs('reviewer');
            $table->enum('section', Section::values())->default(Section::AUTHOR->value);
            $table->foreignId('created_by')->constrained(table: 'authors');
            $table->foreignId('invited_by')->nullable()->constrained(table: 'authors');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->tinyInteger('invite_count')->nullable();
            $table->tinyInteger('remind_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_reviewers');
    }
};
