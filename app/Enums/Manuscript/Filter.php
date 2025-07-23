<?php

namespace App\Enums\Manuscript;

enum Filter: string
{
    case PENDING = 'pending';

    case SUBMITTED = 'submitted';

    case WITHDRAWN = 'withdrawn';

    case DELETED = 'deleted';

    case REJECTED = 'rejected';

    case CONDITIONALLY_ACCEPTED = 'conditionally-accepted';

    case ACCEPTED = 'accepted';

    case PRODUCTION = 'production';

    case PUBLICATION = 'publication';

    case PUBLISHED = 'published';

    case INVITE = 'invite';

    case INVITED = 'invited';

    case REVIEW = 'review';

    case REVIEWED = 'reviewed';

    case UNDER_REVISION = 'under-revision';

    case UNDER_REVIEW = 'under-review';

    case WITH_AE = 'with-ae';

    case WITH_EIC = 'with-eic';

    case UNDER_SIMILARITY_CHECK = 'under-similarity-check';

    case UNDER_PAGINATION = 'under-pagination';

    case UNDER_GRAMMAR_CHECK = 'under-grammar-check';

    case READY_FOR_ACCEPT = 'ready-for-accept';

    case FORMATTER = 'formatter';
    case PROOFREADER = 'proofreader';
    case READY_ARTICLE = 'ready-article';

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
