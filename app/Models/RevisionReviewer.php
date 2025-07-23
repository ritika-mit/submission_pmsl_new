<?php

namespace App\Models;

use App\Enums\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class RevisionReviewer extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    protected $hidden = [
        'revision_id',
        'reviewer_id',
        'created_by',
        'invited_by',
    ];

    protected $casts = [
        'section' => Section::class,
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'denied_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $item) {
            $user = Auth::user();

            if (!$item->section) $item->section = $user->section;
            if (!$item->created_by) $item->created_by = $user->getAuthIdentifier();
        });
    }

    public function invite()
    {
        if ($user = Auth::user()) {
            $this->invited_by = $user->getAuthIdentifier();
            $this->invited_at = now();
        }

        $this->invite_count ??= 0;
        $this->invite_count++;

        $this->save();
    }

    public function remind()
    {
        $this->remind_count ??= 0;
        $this->remind_count++;

        $this->save();
    }

    public function accept()
    {
        $this->refresh();
        if (is_null($this->denied_at)) {
            $this->accepted_at = now();
            $this->save();
        }
    }

    public function deny()
    {
        $this->refresh();
        if (is_null($this->accepted_at)) {
            $this->denied_at = now();
            $this->save();
        }
    }

    public function revision(): BelongsTo
    {
        return $this
            ->belongsTo(
                related: Revision::class,
            );
    }

    public function reviewer(): MorphTo
    {
        return $this
            ->morphTo(
                name: 'reviewer',
            );
    }

    public function review(): HasOne
    {
        return $this
            ->hasOne(
                related: Review::class,
                foreignKey: 'reviewer_id',
                localKey: 'reviewer_id',
            );
    }
}
