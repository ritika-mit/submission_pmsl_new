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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('highest_qualification')->nullable();
            $table->string('scopus_id')->nullable();
            $table->string('orcid_id')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('organization_institution')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->boolean('privacy_policy_accepted')->default(false);
            $table->boolean('subscribed_for_notifications')->default(false);
            $table->boolean('accept_review_request')->default(false);
            $table->enum('section', Section::values())->default(Section::AUTHOR->value);
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
