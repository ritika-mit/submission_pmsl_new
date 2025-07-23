<?php

namespace App\Models;

use App\Enums\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class RevisionAuthor extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    protected $visible = [
        'section',
        'author',
    ];

    protected $casts = [
        'section' => Section::class,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $item) {
            $user = Auth::user();

            $item->section = $user->section;
            $item->created_by = $user->getAuthIdentifier();
        });
    }

    public function revision(): BelongsTo
    {
        return $this
            ->belongsTo(
                related: Revision::class
            );
    }

    public function author(): MorphTo
    {
        return $this
            ->morphTo(
                name: 'author'
            );
    }
}
