<?php

namespace App\Enums\Manuscript;

use Illuminate\Contracts\Support\Arrayable;

enum Action: string implements Arrayable
{
    case CREATE = 'create';

    case EDIT = 'edit';

    case VIEW = 'view';

    case REVIEWERS = 'reviewers';

    case ADD_REVIEWER = 'add-reviewer';
    
    case ADD_REVIEWERS = 'add-reviewers';

    case ADD_REVIEWER_MANUALLY = 'add-reviewer-manually';

    case REMOVE_REVIEWER = 'remove-reviewer';

    case AUTHORS = 'authors';

    case ADD_AUTHOR = 'add-author';

    case ADD_AUTHOR_MANUALLY = 'add-author-manually';

    case REMOVE_AUTHOR = 'remove-author';

    case SUBMIT = 'submit';

    case WITHDRAW = 'withdraw';

    case DELETE = 'delete';

    case INVITE = 'invite';

    case CONDITIONALLY_ACCEPT = 'conditionally-accept';

    case ACCEPT = 'accept';

    case REJECT = 'reject';

    case PRODUCTION = 'production';

    case FORMATTER = 'formatter';

    case PROOFREADER = 'proofreader';
    // case READY_ARTICLE = 'ready-article';

    case PUBLICATION = 'publication';

    case PUBLISH = 'publish';

    case ASSIGN_ASSOCIATE_EDITOR = 'assign-associate-editor';

    case INVITE_REVIEWER = 'invite-reviewer';

    case INVITE_MORE_REVIEWER = 'invite-more-reviewer';

    case REINVITE_REVIEWER = 'reinvite-reviewer';

    case ACCEPT_REVIEW_INVITE = 'accept-review-invite';

    case DENY_REVIEW_INVITE = 'deny-review-invite';

    case REMIND_REVIEWER = 'remind-reviewer';

    case REVIEW = 'review';

    case SEND_TO_EIC = 'send-to-eic';

    case SEND_FOR_SIMILARITY_CHECK = 'send-for-similarity-check';

    case UPDATE_SIMILARITY = 'update-similarity';
    
    case UPDATE_FORMATTER = 'update-formatter';

    case UPDATE_PROOFREADER = 'update-proofreader';

    case SEND_FOR_PAGINATION = 'send-for-pagination';

    case UPDATE_PAGES = 'update-pages';

    case SEND_FOR_GRAMMAR_CHECK = 'send-for-grammar-check';

    case GRAMMAR_UPDATED = 'grammar-checked';

    case MINOR_REVISION = 'minor-revision-required';

    case MAJOR_REVISION = 'major-revision-required';

    case REVISE = 'revise';

    case UPDATE_COMMENT = 'update-comment';

    case UPDATE_COMMENT_REPLY = 'update-comment-reply';
    
    case REMIND_AUTHOR = 'remind-author';
    
    case GET_PROOFREADER = 'get-proofreader';

    public function label()
    {
        return __("manuscript.action.{$this->value}");
    }

    public function toArray()
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
