<?php

use App\Enums\Manuscript\Status;
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
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_id')->constrained();
            $table->integer('index')->unsigned()->default(0);
            $table->string('title');
            $table->text('abstract');
            $table->text('keywords');
            $table->text('novelty')->nullable();
            $table->text('comments_to_eic')->nullable();
            $table->text('comment_reply')->nullable();
            $table->string('anonymous_file');
            $table->string('source_file');
            $table->string('comment_reply_file')->nullable();
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::PENDING->value);
            $table->foreignId('associate_editor_id')->nullable()->constrained('authors');
            $table->unsignedInteger('minimum_reviews')->default(8);
            $table->boolean('similarity_check_required')->default(false);
            $table->unsignedInteger('similarity')->nullable();
            $table->boolean('pagination_required')->default(false);
            $table->unsignedInteger('pages')->nullable();
            $table->boolean('grammar_check_required')->default(false);
            $table->boolean('grammar_updated')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['manuscript_id', 'index']);
        });

        Schema::table('manuscripts', function (Blueprint $table) {
            $table->foreignId('revision_id')
                ->after('author_id')
                ->nullable()
                ->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manuscripts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revision_id');
        });

        Schema::dropIfExists('revisions');
    }
};
