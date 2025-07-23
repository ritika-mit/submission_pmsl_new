<?php

namespace App\Enums\Manuscript;

use Illuminate\Contracts\Support\Arrayable;

enum Type: string implements Arrayable
{
    case RESEARCH = 'research';

    case REVIEW = 'review';

    case CASE_STUDY = 'case-study';

    public function toArray()
    {
        return [
            'value' => $this->value,
            'label' => __("manuscript.type.{$this->value}"),
        ];
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
