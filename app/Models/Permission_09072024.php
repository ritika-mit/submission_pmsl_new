<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Models\Permission as Base;

class Permission extends Base
{
    use HasHashId, HasSearchable;

    const ROLE_CREATE = 'role.create';
    const ROLE_EDIT = 'role.edit';
    const ROLE_LIST = 'role.list';

    const AUTHOR_CREATE = 'author.create';
    const AUTHOR_EDIT = 'author.edit';
    const AUTHOR_LIST = 'author.list';

    const MANUSCRIPT_CREATE = 'manuscript.create';
    const MANUSCRIPT_EDIT = 'manuscript.edit';

    const MANUSCRIPT_LIST_PENDING = 'manuscript.list.pending';
    const MANUSCRIPT_LIST_SUBMITTED = 'manuscript.list.submitted';
    const MANUSCRIPT_LIST_DELETED = 'manuscript.list.deleted';
    const MANUSCRIPT_LIST_WITHDRAWN = 'manuscript.list.withdrawn';
    const MANUSCRIPT_LIST_REJECTED = 'manuscript.list.rejected';
    const MANUSCRIPT_LIST_UNDER_REVISION = 'manuscript.list.under-revision';
    const MANUSCRIPT_LIST_CONDITIONALLY_ACCEPTED = 'manuscript.list.conditionally-accepted';
    const MANUSCRIPT_LIST_ACCEPTED = 'manuscript.list.accepted';
    const MANUSCRIPT_LIST_PRODUCTION = 'manuscript.list.production';
    const MANUSCRIPT_LIST_PUBLICATION = 'manuscript.list.publication';
    const MANUSCRIPT_LIST_PUBLISHED = 'manuscript.list.published';
    const MANUSCRIPT_LIST_INVITE = 'manuscript.list.invite';
    const MANUSCRIPT_LIST_REVIEW = 'manuscript.list.review';
    const MANUSCRIPT_LIST_REVIEWED = 'manuscript.list.reviewed';
    const MANUSCRIPT_LIST_WITH_EIC = 'manuscript.list.with-eic';
    const MANUSCRIPT_LIST_WITH_AE = 'manuscript.list.with-ae';
    const MANUSCRIPT_LIST_SIMILARITY_CHECK = 'manuscript.list.similarity-check';
    const MANUSCRIPT_LIST_PAGINATION = 'manuscript.list.pagination';
    const MANUSCRIPT_LIST_GRAMMAR_CHECK = 'manuscript.list.grammar-check';
    const MANUSCRIPT_LIST_READY_FOR_ACCEPT = 'manuscript.list.ready-for-accept';
    const MANUSCRIPT_LIST_FORMATTER = 'manuscript.list.formatter';
    const MANUSCRIPT_LIST_PROOFREADER = 'manuscript.list.proofreader';
    const FORMATTER_LIST = 'formatter.list';
    const PROOFREADER_LIST = 'proofreader.list';


    protected $hidden = [
        'guard_name',
    ];

    protected $appends = [
        'label',
    ];

    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => __("permission.{$attributes['name']}")
        );
    }

    public static function getConstants()
    {
        $constants = (new \ReflectionClass(self::class))->getConstants();
        unset($constants['CREATED_AT'], $constants['UPDATED_AT']);
        return $constants;
    }

    public function scopeActive(Builder $query)
    {
        return $query;
    }
}
