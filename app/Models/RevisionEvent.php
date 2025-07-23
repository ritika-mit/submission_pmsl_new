<?php

namespace App\Models;

use App\Enums\Manuscript\Event;
use App\Enums\Manuscript\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class RevisionEvent extends Model
{
    use HasFactory, HasHashId;

    const UPDATED_AT = null;

    protected $guarded = [
        'created_by',
    ];

    protected $hidden = [
        'revision_id',
        'event',
        'value',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event' => Event::class,
    ];

    protected $appends = [
        'title',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $item) {
            $user = Auth::user();
            $item->created_by = $user->getAuthIdentifier();
        });
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            fn (mixed $value, array $attributes)  => match (Event::tryFrom($attributes['event'])) {
                Event::STATUS_UPDATED => Status::tryFrom($attributes['value'])->label(),
                default => null,
            }
        );
    }

    public function revision(): BelongsTo
    {
        return $this
            ->belongsTo(
                related: Revision::class
            );
    }
}
