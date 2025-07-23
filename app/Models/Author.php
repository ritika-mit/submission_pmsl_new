<?php

namespace App\Models;

use App\Enums\Section;
use App\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmail as VerifyEmailNotification;
use App\Notifications\Manuscript\Accepted as ManuscriptAcceptedNotification;
use App\Notifications\Manuscript\AssociateEditorAssigned as ManuscriptAssociateEditorAssignedNotification;
use App\Notifications\Manuscript\ConditionallyAccepted as ManuscriptConditionallyAcceptedNotification;
use App\Notifications\Manuscript\Published as ManuscriptPublishedNotification;
use App\Notifications\Manuscript\Rejected as ManuscriptRejectedNotification;
use App\Notifications\Manuscript\ReviewerInvited as ManuscriptReviewerInvitedNotification;
use App\Notifications\Manuscript\ReviewInviteDenied as ManuscriptReviewInviteDeniedNotification;
use App\Notifications\Manuscript\ReviewReminder as ManuscriptReviewReminderNotification;
use App\Notifications\Manuscript\RevisionReminder as ManuscriptRevisionReminderNotification;
use App\Notifications\Manuscript\ReviewSubmitted as ManuscriptReviewSubmittedNotification;
use App\Notifications\Manuscript\Revised as ManuscriptRevisedNotification;
use App\Notifications\Manuscript\RevisionRequired as ManuscriptRevisionRequiredNotification;
use App\Notifications\Manuscript\Submitted as ManuscriptSubmittedNotification;
use App\Notifications\Manuscript\Withdrawn as ManuscriptWithdrawnNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;


class Author extends User implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasHashId, HasSearchable, HasRoles;

    protected $guarded = [
        'country_id',
        'remember_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'country_id',
        'active',
        'privacy_policy_accepted',
        'subscribed_for_notifications',
        'accept_review_request',
    ];

    protected $casts = [
        'active' => 'boolean',
        'section' => Section::class,
        'privacy_policy_accepted' => 'boolean',
        'subscribed_for_notifications' => 'boolean',
        'accept_review_request' => 'boolean',
        'email_verified_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    protected $appends = [
        'name',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => collect($attributes ?? [])
                ->only(['first_name', 'middle_name', 'last_name'])
                ->filter()
                ->join(' ')
        );
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereActive(true);
    }

    public function scopeVerified(Builder $query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeReviewer(Builder $query)
    {
        return $query->whereHas(
            relation: 'roles',
            callback: fn ($q) => $q->where('name', 'Reviewer')
        );
    }

    public function scopeAssociateEditor(Builder $query)
    {
        return $query->whereHas(
            relation: 'roles',
            callback: fn ($q) => $q->where('name', 'Associate Editor')
        );
    }

    public function country()
    {
        return $this->belongsTo(related: Country::class);
    }

    public function researchAreas()
    {
        return $this->belongsToMany(related: ResearchArea::class);
    }

    public function manuscripts()
    {
        return $this->hasMany(related: Manuscript::class);
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

    public function reviews()
    {
        return $this->hasMany(related: Review::class);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendManuscriptSubmittedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptSubmittedNotification($manuscript));
    }

    public function sendManuscriptAssociateEditorAssignedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptAssociateEditorAssignedNotification($manuscript));
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

    public function sendManuscriptRevisionReminderNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptRevisionReminderNotification($manuscript));
    }

    public function sendManuscriptReviewSubmittedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptReviewSubmittedNotification($manuscript));
    }

    public function sendManuscriptRevisionRequiredNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptRevisionRequiredNotification($manuscript));
    }

    public function sendManuscriptRevisedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptRevisedNotification($manuscript));
    }

    public function sendManuscriptWithdrawnNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptWithdrawnNotification($manuscript));
    }

    public function sendManuscriptRejectedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptRejectedNotification($manuscript));
    }

    public function sendManuscriptConditionallyAcceptedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptConditionallyAcceptedNotification($manuscript));
    }

    public function sendManuscriptAcceptedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptAcceptedNotification($manuscript));
    }

    public function sendManuscriptPublishedNotification(Manuscript $manuscript)
    {
        $this->notify(new ManuscriptPublishedNotification($manuscript));
    }

    /**
     * Determine if the author is marked as deleted.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted === true;
    }

    /**
     * Automatically exclude deleted authors in all queries.
     */
    protected static function booted(): void
    {
        Log::info("in booted Author");
        static::addGlobalScope('notDeleted', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }
}
