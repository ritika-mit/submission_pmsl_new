<?php

namespace App\Models;

use App\Notifications\Manuscript\ReviewerInvited as ManuscriptReviewerInvitedNotification;
use App\Notifications\Manuscript\ReviewInviteDenied as ManuscriptReviewInviteDeniedNotification;
use App\Notifications\Manuscript\ReviewReminder as ManuscriptReviewReminderNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class GuestAuthor extends Model
{
    use HasFactory, Notifiable, HasHashId, HasSearchable, SoftDeletes;

    protected $guarded = [
        'country_id',
    ];

    protected $hidden = [
        'created_by',
        'country_id',
    ];

    protected $appends = [
        'name',
        'can_edit'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $item) {
            $user = Auth::user();
            $item->created_by = $user->getAuthIdentifier();
        });
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => collect($attributes ?? [])
                ->only(['first_name', 'middle_name', 'last_name'])
                ->filter()
                ->join(' ')
        );
    }

    protected function canEdit(): Attribute
    {
        return Attribute::make(
            get: fn () => Auth::user()?->getAuthIdentifier() === ($this->attributes['created_by'] ?? null)
        );
    }

    public function country()
    {
        return $this
            ->belongsTo(related: Country::class);
    }

    public function revisionAsReviewer()
    {
        return $this
            ->morphMany(
                related: RevisionReviewer::class,
                name: 'reviewer',
            );
    }

    public function revisionAsAuthor()
    {
        return $this
            ->morphMany(
                related: RevisionAuthor::class,
                name: 'author',
            );
    }

    public function sendManuscriptReviewerInvitedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptReviewerInvitedNotification($manuscript));
    }

    public function sendManuscriptReviewInviteDeniedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptReviewInviteDeniedNotification($manuscript));
    }
    
    public function sendManuscriptReviewReminderNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptReviewReminderNotification($manuscript));
    }
    
}
