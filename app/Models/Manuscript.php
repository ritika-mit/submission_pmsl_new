<?php

namespace App\Models;

use App\Enums\Manuscript\Filter;
use App\Enums\Manuscript\Status;
use App\Enums\Manuscript\Type;
use App\Enums\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Manuscript extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    const COPYRIGHT_FORM_PATH = 'copyright_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'copyright_form',
        'revision_id',
        'author_id',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'type' => Type::class,
    ];

    protected function getCodeAttribute(string $code)
    {
        if (!$this->exists)
            return;

        return $this->revision && $this->revision->index > 0
            ? implode('-', [$this->revision->code, $code])
            : $code;
    }

    protected function step(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $this->loadMissing('reviewers', 'authors', 'revision');

                return [
                    'basic' => !!($attributes['id'] ?? null),
                    'reviewer' => !!self::getRelation('reviewers')->count(),
                    'author' => !!self::getRelation('authors')->count(),
                    'status' => self::getRelation('revision')?->getAttribute('status') ?? Status::PENDING,
                ];
            }
        );
    }

    public function generateCodeAttribute()
    {
        $count = self::query()->whereYear('created_at', date('Y'))->count() + 1;
        $this->attributes['code'] = implode('-', ['PMSL', date('y'), sprintf('%04d', $count)]);
    }

    public function saveFileAndSetAttribute(UploadedFile $file, string $attribute)
    {
        return $this
            ->setAttribute(
                key: $attribute,
                value: $file->storeAs(
                    path: match ($attribute) {
                        'copyright_form' => self::COPYRIGHT_FORM_PATH,
                    },
                    name: implode('-', [
                        $this->attributes['code'],
                        str_replace('_', '-', $attribute),
                        strtolower(Str::random()) . '.' . $file->clientExtension(),
                    ]),
                )
            );
    }

    protected function getFileAttributeRoute(string $attribute)
    {
        if (empty($path = $this->attributes[$attribute] ?? null))
            return;

        $manuscript = $this;

        [$prefix, $path] = explode('/', $path, 2);

        $type = str_replace('_', '-', $attribute);

        return route(
            name: 'manuscripts.files',
            parameters: compact('manuscript', 'type', 'path'),
            absolute: false
        );
    }

    public function getCopyrightFormAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('copyright_form');
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereActive(true);
    }

    public function scopeStatus(Builder $query, Status $status)
    {
        return $query->whereHas(
            'revision',
            fn(Builder $query) => $query->whereStatus($status)
        );
    }

    public function scopeStatusIn(Builder $query, array $status)
    {
        return $query->whereHas(
            'revision',
            fn(Builder $query) => $query->whereIn('status', $status)
        );
    }

    public function scopeFilterForUserAndStatus(Builder $query, Author $user, Filter $filter)
    {
        return $query
            ->where(fn(Builder $query) => match ($user->section) {
                Section::AUTHOR => $query->whereAuthorId($user->getKey()),

                Section::ASSOCIATE_EDITOR => $query->whereHas(
                    'revision',
                    fn($query) => $query
                        ->whereAssociateEditorId($user->getKey())
                ),

                default => $query
            })
            ->where(fn(Builder $query) => match ($filter) {
                Filter::INVITE => $query->status(Status::SUBMITTED)
                    ->whereDoesntHave(
                        relation: 'revision.reviewers',
                        callback: fn($query) => $query
                            ->whereNotNull('invited_at')
                    ),

                Filter::INVITED => $query->status(Status::SUBMITTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query
                            ->whereNull('comments_to_eic')
                            ->whereHas(
                                relation: 'reviewers',
                                callback: fn($query) => $query
                                    ->whereNotNull('invited_at')
                            )
                    ),

                Filter::REVIEW => match ($user->section) {
                        Section::REVIEWER => $query
                            ->status(Status::SUBMITTED)
                            ->whereHas(
                                relation: 'revision',
                                callback: fn($query) => $query
                                    ->reviewPending($user)
                            ),

                        default => $query
                    },

                Filter::REVIEWED => match ($user->section) {
                        Section::REVIEWER => $query
                            ->whereHas(
                                relation: 'revisions',
                                callback: fn(Builder $query) => $query
                                    ->reviewCompleted($user)
                            ),

                        Section::ASSOCIATE_EDITOR => $query
                            ->status(Status::SUBMITTED)
                            ->whereHas(
                                relation: 'revision.reviews',
                                callback: fn($query) => $query->whereNull('comments_to_eic'),
                                operator: '>=',
                                count: DB::raw('`minimum_reviews`')
                            ),

                        default => $query
                    },

                Filter::UNDER_REVISION => $query->statusIn([Status::MINOR_REVISION, Status::MAJOR_REVISION]),

                Filter::WITH_AE => $query
                    ->status(Status::SUBMITTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query
                            ->whereNotNull('associate_editor_id')
                            ->whereNull('comments_to_eic')
                    ),

                Filter::WITH_EIC => $query
                    ->status(Status::SUBMITTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query->whereNotNull('comments_to_eic')
                    ),

                Filter::PENDING => $query->status(Status::PENDING),

                Filter::SUBMITTED => match ($user->section) {
                        Section::AUTHOR => $query
                            ->status(Status::SUBMITTED)
                            ->whereDoesntHave(
                                relation: 'revision.reviewers',
                                callback: fn($query) => $query
                                    ->whereNotNull('invited_at')
                            ),

                        Section::EDITOR_IN_CHIEF => $query
                            ->status(Status::SUBMITTED)
                            ->whereHas(
                                relation: 'revision',
                                callback: fn($query) => $query
                                    ->whereNull('associate_editor_id')
                            ),

                        default => $query->status(Status::SUBMITTED)
                    },

                Filter::UNDER_REVIEW => $query
                    ->status(Status::SUBMITTED)
                    ->whereHas(
                        relation: 'revision.reviewers',
                        callback: fn($query) => $query
                            ->whereNotNull('invited_at')
                    ),

                Filter::WITHDRAWN => $query->status(Status::WITHDRAWN),

                Filter::DELETED => $query->status(Status::DELETED),

                Filter::REJECTED => $query->status(Status::REJECTED),

                Filter::CONDITIONALLY_ACCEPTED => $query->status(Status::CONDITIONALLY_ACCEPTED)
                    ->whereDoesntHave(
                        relation: 'revision',
                        callback: fn($query) => $query->whereSimilarityCheckRequired(true)
                    ),

                Filter::UNDER_SIMILARITY_CHECK => $query->status(Status::CONDITIONALLY_ACCEPTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query->whereSimilarityCheckRequired(true)->whereNull('similarity')
                    ),

                Filter::UNDER_PAGINATION => $query->status(Status::CONDITIONALLY_ACCEPTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query->wherePaginationRequired(true)->whereNull('pages')
                    ),

                Filter::UNDER_GRAMMAR_CHECK => $query->status(Status::CONDITIONALLY_ACCEPTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query->whereGrammarCheckRequired(true)->whereNull('grammar_updated')
                    ),

                Filter::READY_FOR_ACCEPT => $query->status(Status::CONDITIONALLY_ACCEPTED)
                    ->whereHas(
                        relation: 'revision',
                        callback: fn($query) => $query->whereGrammarCheckRequired(true)->whereNotNull('grammar_updated')
                    ),

                Filter::ACCEPTED => $query->status(Status::ACCEPTED),

                Filter::PRODUCTION => $query->status(Status::PRODUCTION),

                Filter::PUBLICATION => $query->status(Status::PUBLICATION),

                Filter::PUBLISHED => $query->status(Status::PUBLISHED),
                Filter::FORMATTER => $query->status(Status::FORMATTER),

                Filter::PROOFREADER => $query->status(Status::PROOFREADER),
                Filter::READY_ARTICLE => $query->status(Status::READY_ARTICLE),


                default => $query
            });
    }

    public function author()
    {
        return $this
            ->belongsTo(
                related: Author::class,
                foreignKey: 'author_id'
            );
    }

    public function revision()
    {
        return $this
            ->belongsTo(
                related: Revision::class,
            );
    }

    public function revisions()
    {
        return $this
            ->hasMany(
                related: Revision::class,
            );
    }

    public function reviewers()
    {
        return $this
            ->hasManyThrough(
                related: RevisionReviewer::class,
                through: Revision::class,
            );
    }

    public function authors()
    {
        return $this
            ->hasManyThrough(
                related: RevisionAuthor::class,
                through: Revision::class,
            );
    }

    public function associateEditors()
    {
        return $this
            ->hasManyThrough(
                related: Author::class,
                through: Revision::class,
                secondKey: 'id',
                secondLocalKey: 'associate_editor_id',
            );
    }

    public function researchAreas()
    {
        return $this
            ->belongsToMany(
                related: ResearchArea::class,
            )
            ->using(new class extends Pivot {
            protected $hidden = [
                'manuscript_id',
                'research_area_id',
            ];
            });
    }

    public function termAndConditions()
    {
        return $this
            ->belongsToMany(
                related: TermAndCondition::class,
            )
            ->using(new class extends Pivot {
            protected $hidden = [
                'manuscript_id',
                'term_and_condition_id',
            ];
            });
    }

    public function reviews()
    {
        return $this
            ->hasManyThrough(
                related: Review::class,
                through: Revision::class,
            );
    }

    public function events()
    {
        return $this
            ->hasManyThrough(
                related: RevisionEvent::class,
                through: Revision::class,
            );
    }
}
