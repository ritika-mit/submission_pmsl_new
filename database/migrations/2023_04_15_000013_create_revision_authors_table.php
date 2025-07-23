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
        Schema::create('revision_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->nullable()->constrained();
            $table->morphs('author');
            $table->foreignId('created_by')->constrained(table: 'authors');
            $table->enum('section', Section::values())->default(Section::AUTHOR->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_authors');
    }
};
