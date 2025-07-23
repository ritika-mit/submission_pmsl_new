<?php

namespace App\Enums\Manuscript;

use Illuminate\Contracts\Support\Arrayable;

enum ReviewDecision: string implements Arrayable
{
    case ACCEPT = 'accept';

    case MINOR_REVISION_REQUIRED = 'minor-revision-required';

    case MAJOR_REVISION_REQUIRED = 'major-revision-required';

    case REJECT = 'reject';

    public function toArray()
    {
        return [
            'value' => $this->value,
            'label' => __("manuscript.review.decision.{$this->value}"),
        ];
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
