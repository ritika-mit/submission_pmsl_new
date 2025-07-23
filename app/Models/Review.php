<?php

namespace App\Models;

use App\Enums\Manuscript\ReviewDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    const REVIEW_REPORT_PATH = 'review_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'comments_to_author',
        'decision',
        'comments_to_associate_editor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'revision_id',
        'reviewer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decision' => ReviewDecision::class,
    ];

    public function saveFileAndSetAttribute(UploadedFile $file, string $attribute)
    {
        return $this
            ->setAttribute(
                key: $attribute,
                value: $file->storeAs(
                    path: match ($attribute) {
                        'review_report' => self::REVIEW_REPORT_PATH,
                    },
                    name: implode('-', array_filter([
                        $this->revision->index > 0 ? $this->revision->code : null,
                        $this->revision->manuscript->getRawOriginal('code'),
                        str_replace('_', '-', $attribute),
                        sprintf('%04d', $this->revision->reviews()->count() + 1),
                        strtolower(Str::random()) . '.' . $file->clientExtension(),
                    ])),
                )
            );
    }

    protected function getFileAttributeRoute(string $attribute)
    {
        if (empty($path = $this->attributes[$attribute] ?? null)) return;

        $review = $this;
        $revision = $this->revision;
        $manuscript = $revision->manuscript;

        [$prefix, $path] = explode('/', $path, 2);

        $type = str_replace('_', '-', $attribute);

        return route(
            name: 'manuscripts.revisions.reviews.files',
            parameters: compact('manuscript', 'revision', 'review', 'type', 'path'),
            absolute: false
        );
    }

    public function getReviewReportAttribute(?string $path)
    {
        return $this->getFileAttributeRoute('review_report');
    }

    public function revision()
    {
        return $this->belongsTo(
            related: Revision::class,
        );
    }

    public function reviewer()
    {
        return $this->belongsTo(
            related: Author::class,
            foreignKey: 'reviewer_id',
        );
    }
}
