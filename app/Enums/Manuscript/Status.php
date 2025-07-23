<?php

namespace App\Enums\Manuscript;

use Illuminate\Contracts\Support\Arrayable;

enum Status: string implements Arrayable
{
    case PENDING = 'pending';

    case SUBMITTED = 'submitted';

    case WITHDRAWN = 'withdrawn';

    case DELETED = 'deleted';

    case MINOR_REVISION = 'minor-revision-required';

    case MAJOR_REVISION = 'major-revision-required';

    case REJECTED = 'rejected';

    case CONDITIONALLY_ACCEPTED = 'conditionally-accepted';

    case ACCEPTED = 'accepted';

    case PRODUCTION = 'production';

    case PUBLICATION = 'publication';

    case PUBLISHED = 'published';

    case FORMATTER = 'formatter';

    case PROOFREADER = 'proofreader';
    case READY_ARTICLE = 'ready-article';
    public function label()
    {
        return __("manuscript.status.{$this->value}");
    }

    public function toArray()
    {
        return [
            'value' => $this->value,
            'label' => __("manuscript.status.{$this->value}"),
        ];
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
