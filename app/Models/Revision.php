<?php

namespace App\Models;

use App\Enums\Manuscript\Event;
use App\Enums\Manuscript\Status;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Revision extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    const ANONYMOUS_FILE_PATH = 'anonymous_files';

    const SOURCE_FILE_PATH = 'source_files';
    const FORMATTED_PAPER_FILE_PATH = 'formatted_paper';
    const CORRECTION_FILE_PATH = 'correction_file';
    const OTHER_FILE_PATH = 'other_file';

    const PROOFREADER_PAPER_FILE_PATH = 'proofreader_paper';

    const COMMENT_REPLY_FILE_PATH = 'comment_reply_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'abstract',
        'keywords',
        'novelty',
        'comments_to_eic',
        'comment_reply',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'manuscript_id',
        'index',
        'anonymous_file',
        'source_file',
        'comment_reply_file',
        'formatted_paper',
        'correction_file',
        'proofreader_paper',
        'other_file',
        'associate_editor_id',
        'minimum_reviews',
        'similarity_check_required',
        'pagination_required',
        'grammar_check_required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'similarity_check_required' => 'boolean',
        'pagination_required' => 'boolean',
        'grammar_check_required' => 'boolean',
        'grammar_updated' => 'boolean',
        'status' => Status::class,
    ];

    protected $appends = [
        'code'
    ];

    protected function code(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $index = $attributes['index'] ?? 0;
                return "R{$index}";
            }
        );
    }

    public function saveFileAndSetAttribute(UploadedFile $file, string $attribute)
    {
        return $this
            ->setAttribute(
                key: $attribute,
                value: $file->storeAs(
                    path: match ($attribute) {
                        'source_file' => self::SOURCE_FILE_PATH,
                        'anonymous_file' => self::ANONYMOUS_FILE_PATH,
                        'comment_reply_file' => self::COMMENT_REPLY_FILE_PATH,
                        'formatted_paper' => self::FORMATTED_PAPER_FILE_PATH,
                        'proofreader_paper' => self::PROOFREADER_PAPER_FILE_PATH,
                        'correction_file' => self::CORRECTION_FILE_PATH,
                        'other_file' => self::OTHER_FILE_PATH
                    },
                    name: implode('-', array_filter([
                        $this->index > 0 ? $this->code : null,
                        $this->manuscript->getRawOriginal('code'),
                        str_replace('_', '-', $attribute),
                        strtolower(Str::random()) . '.' . $file->clientExtension(),
                    ])),
                )
            );
    }

    protected function getFileAttributeRoute(string $attribute)
    {
        if (empty($path = $this->attributes[$attribute] ?? null)) return;

        $revision = $this;
        $manuscript = $this->manuscript;

        [$prefix, $path] = explode('/', $path, 2);

        $type = str_replace('_', '-', $attribute);

        return route(
            name: 'manuscripts.revisions.files',
            parameters: compact('manuscript', 'revision', 'type', 'path'),
            absolute: false
        );
    }

    public function getAnonymousFileAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('anonymous_file');
    }

    public function getSourceFileAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('source_file');
    }

    public function getCommentReplyFileAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('comment_reply_file');
    }

    public function getFormattedPaperAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('formatted_paper');
    }
    public function getCorrectionFileAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('correction_file');
    }
    public function getOtherFileAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('other_file');
    }
    public function getProofreaderPaperAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('proofreader_paper');
    }
    public function scopeActive(Builder $query)
    {
        return $query->whereActive(true);
    }

    public function scopeStatus(Builder $query, Status $status)
    {
        return $query->where('status', $status);
    }

    public function scopeReviewPending(Builder $query, Authenticatable $reviewer)
    {
        return $query
            ->whereHas(
                relation: 'reviewers',
                callback: fn ($query) => $query
                    ->whereNotNull('invited_at')
                    ->whereNotNull('accepted_at')
                    ->whereNull('denied_at')
                    ->whereReviewerId($reviewer->getAuthIdentifier())
            )
            ->whereDoesntHave(
                relation: 'reviews',
                callback: fn (Builder $query) => $query
                    ->whereReviewerId($reviewer->getAuthIdentifier())
            );
    }

    public function scopeReviewCompleted(Builder $query, Authenticatable $reviewer)
    {
        return $query
            ->whereHas(
                relation: 'reviews',
                callback: fn (Builder $query) => $query
                    ->whereReviewerId($reviewer->getAuthIdentifier())
            );
    }

    public function manuscript()
    {
        return $this
            ->belongsTo(
                related: Manuscript::class,
            );
    }

    public function reviewers()
    {
        return $this
            ->hasMany(
                related: RevisionReviewer::class
            );
    }

    public function authors()
    {
        return $this
            ->hasMany(
                related: RevisionAuthor::class
            );
    }

    public function associateEditor()
    {
        return $this
            ->belongsTo(
                related: Author::class,
                foreignKey: 'associate_editor_id',
            );
    }

    public function reviews()
    {
        return $this
            ->hasMany(
                related: Review::class,
            );
    }

    public function events()
    {
        return $this
            ->hasMany(
                related: RevisionEvent::class,
            );
    }

    public function createEvent(Event $event, mixed $value)
    {
        return $this
            ->events()
            ->create(compact('event', 'value'));
    }
}
