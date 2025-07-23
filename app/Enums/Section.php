<?php

namespace App\Enums;

use App\Enums\Manuscript\Action;
use App\Enums\Manuscript\Filter;
use App\Models\Permission;
use Illuminate\Contracts\Support\Arrayable;

enum Section: string implements Arrayable
{
    case ADMIN = 'admin';

    case AUTHOR = 'author';

    case REVIEWER = 'reviewer';

    case ASSOCIATE_EDITOR = 'associate-editor';

    case EDITOR_IN_CHIEF = 'editor-in-chief';

    case EPM = 'epm';

    case FORMATTER = 'formatter';
    
    case PROOFREADER = 'proofreader';
    case READY_ARTICLE = 'ready-article';

    public function label()
    {
        return __("section.{$this->value}");
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

    public function items()
    {
        return match ($this) {
            self::ADMIN => [
                self::item(
                    label: __('Authors'),
                    route: ['authors.index'],
                    permission: Permission::AUTHOR_LIST,
                ),
                self::item(
                    label: __('Roles'),
                    route: ['roles.index'],
                    permission: Permission::ROLE_LIST,
                ),
            ],

            self::AUTHOR => [
                self::item(
                    label: __('Submit New Manuscript'),
                    route: ['manuscripts.edit', ['action' => Action::CREATE]],
                    permission: Permission::MANUSCRIPT_CREATE,
                ),
                self::item(
                    label: __('Submissions Completed'),
                    route: ['manuscripts.index', ['filter' => Filter::SUBMITTED]],
                    permission: Permission::MANUSCRIPT_LIST_SUBMITTED,
                ),
                self::item(
                    label: __('Incomplete Submissions'),
                    route: ['manuscripts.index', ['filter' => Filter::PENDING]],
                    permission: Permission::MANUSCRIPT_LIST_PENDING,
                ),
                self::item(
                    label: __('Under Review'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_REVIEW]],
                    permission: Permission::MANUSCRIPT_LIST_UNDER_REVISION,
                ),
                self::item(
                    label: __('Under Revision'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_REVISION]],
                    permission: Permission::MANUSCRIPT_LIST_UNDER_REVISION,
                ),
                self::item(
                    label: __('Conditionally Accepted'),
                    route: ['manuscripts.index', ['filter' => Filter::CONDITIONALLY_ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_PENDING,
                ),
                self::item(
                    label: __('Accepted Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_ACCEPTED,
                ),
                self::item(
                    label: __('In Production'),
                    route: ['manuscripts.index', ['filter' => Filter::PRODUCTION]],
                    permission: Permission::MANUSCRIPT_LIST_PRODUCTION,
                ),
                self::item(
                    label: __('To Publication'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLICATION]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLICATION,
                ),
                self::item(
                    label: __('Published Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLISHED]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLISHED,
                ),
                self::item(
                    label: __('Rejected Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::REJECTED]],
                    permission: Permission::MANUSCRIPT_LIST_REJECTED,
                ),
                self::item(
                    label: __('Withdrawn Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::WITHDRAWN]],
                    permission: Permission::MANUSCRIPT_LIST_WITHDRAWN,
                ),
                self::item(
                    label: __('Deleted Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::DELETED]],
                    permission: Permission::MANUSCRIPT_LIST_DELETED,
                ),
            ],

            self::REVIEWER => [
                self::item(
                    label: __('Review Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::REVIEW]],
                    permission: Permission::MANUSCRIPT_LIST_REVIEW,
                ),
                self::item(
                    label: __('Review Completed'),
                    route: ['manuscripts.index', ['filter' => Filter::REVIEWED]],
                    permission: Permission::MANUSCRIPT_LIST_REVIEWED,
                ),
            ],

            self::ASSOCIATE_EDITOR => [
                self::item(
                    label: __('Invite Reviewers'),
                    route: ['manuscripts.index', ['filter' => Filter::INVITE]],
                    permission: Permission::MANUSCRIPT_LIST_INVITE,
                ),
                self::item(
                    label: __('Under Review Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::INVITED]],
                    permission: Permission::MANUSCRIPT_LIST_REVIEW,
                ),
                self::item(
                    label: __('Reviews Completed Send to EIC'),
                    route: ['manuscripts.index', ['filter' => Filter::REVIEWED]],
                    permission: Permission::MANUSCRIPT_LIST_REVIEWED,
                ),
                self::item(
                    label: __('With EIC'),
                    route: ['manuscripts.index', ['filter' => Filter::WITH_EIC]],
                    permission: Permission::MANUSCRIPT_LIST_WITH_EIC,
                ),
                self::item(
                    label: __('Under Revision'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_REVISION]],
                    permission: Permission::MANUSCRIPT_LIST_UNDER_REVISION,
                ),
                self::item(
                    label: __('Conditionally Accepted'),
                    route: ['manuscripts.index', ['filter' => Filter::CONDITIONALLY_ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_PENDING,
                ),
                self::item(
                    label: __('Accepted Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_ACCEPTED,
                ),
                self::item(
                    label: __('In Production'),
                    route: ['manuscripts.index', ['filter' => Filter::PRODUCTION]],
                    permission: Permission::MANUSCRIPT_LIST_PRODUCTION,
                ),
                self::item(
                    label: __('To Publication'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLICATION]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLICATION,
                ),
                self::item(
                    label: __('Published Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLISHED]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLISHED,
                ),
                self::item(
                    label: __('Rejected Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::REJECTED]],
                    permission: Permission::MANUSCRIPT_LIST_REJECTED,
                ),
                self::item(
                    label: __('Withdrawn Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::WITHDRAWN]],
                    permission: Permission::MANUSCRIPT_LIST_WITHDRAWN,
                ),
            ],

            self::EDITOR_IN_CHIEF => [
                self::item(
                    label: __('New Submission'),
                    route: ['manuscripts.index', ['filter' => Filter::SUBMITTED]],
                    permission: Permission::MANUSCRIPT_LIST_SUBMITTED,
                ),
                self::item(
                    label: __('With Associate Editor'),
                    route: ['manuscripts.index', ['filter' => Filter::WITH_AE]],
                    permission: Permission::MANUSCRIPT_LIST_WITH_AE,
                ),
                self::item(
                    label: __('Under Revision'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_REVISION]],
                    permission: Permission::MANUSCRIPT_LIST_UNDER_REVISION,
                ),
                self::item(
                    label: __('Ready for EIC Decision'),
                    route: ['manuscripts.index', ['filter' => Filter::WITH_EIC]],
                    permission: Permission::MANUSCRIPT_LIST_WITH_EIC,
                ),
                self::item(
                    label: __('Conditionally Accepted'),
                    route: ['manuscripts.index', ['filter' => Filter::CONDITIONALLY_ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_CONDITIONALLY_ACCEPTED,
                ),
                self::item(
                    label: __('Under Similarity Check'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_SIMILARITY_CHECK]],
                    permission: Permission::MANUSCRIPT_LIST_SIMILARITY_CHECK,
                ),
                self::item(
                    label: __('Under Pagination'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_PAGINATION]],
                    permission: Permission::MANUSCRIPT_LIST_PAGINATION,
                ),
                self::item(
                    label: __('Under Grammar Check'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_GRAMMAR_CHECK]],
                    permission: Permission::MANUSCRIPT_LIST_GRAMMAR_CHECK,
                ),
                self::item(
                    label: __('Ready For Accept'),
                    route: ['manuscripts.index', ['filter' => Filter::READY_FOR_ACCEPT]],
                    permission: Permission::MANUSCRIPT_LIST_READY_FOR_ACCEPT,
                ),
                self::item(
                    label: __('Accepted Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::ACCEPTED]],
                    permission: Permission::MANUSCRIPT_LIST_ACCEPTED,
                ),
                self::item(
                    label: __('In Production'),
                    route: ['manuscripts.index', ['filter' => Filter::PRODUCTION]],
                    permission: Permission::MANUSCRIPT_LIST_PRODUCTION,
                ),
                self::item(
                    label: __('Formatter'),
                    route: ['manuscripts.index', ['filter' => Filter::FORMATTER]],
                    permission: Permission::MANUSCRIPT_LIST_FORMATTER,
                ),
                self::item(
                    label: __('Proof Read'),
                    route: ['manuscripts.index', ['filter' => Filter::PROOFREADER]],
                    permission: Permission::MANUSCRIPT_LIST_PROOFREADER,
                ),
                self::item(
                    label: __('Ready Article'),
                    route: ['manuscripts.index', ['filter' => Filter::READY_ARTICLE]],
                    permission: Permission::MANUSCRIPT_LIST_READY_ARTICLE,
                ),

                self::item(
                    label: __('To Publication'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLICATION]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLICATION,
                ),
                self::item(
                    label: __('Published Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::PUBLISHED]],
                    permission: Permission::MANUSCRIPT_LIST_PUBLISHED,
                ),
                self::item(
                    label: __('Rejected Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::REJECTED]],
                    permission: Permission::MANUSCRIPT_LIST_REJECTED,
                ),
                self::item(
                    label: __('Withdrawn Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::WITHDRAWN]],
                    permission: Permission::MANUSCRIPT_LIST_WITHDRAWN,
                ),
                self::item(
                    label: __('Un-Submitted'),
                    route: ['manuscripts.index', ['filter' => Filter::PENDING]],
                    permission: Permission::MANUSCRIPT_LIST_PENDING,
                ),
                self::item(
                    label: __('Deleted Manuscripts'),
                    route: ['manuscripts.index', ['filter' => Filter::DELETED]],
                    permission: Permission::MANUSCRIPT_LIST_DELETED,
                ),
            ],

            self::EPM => [
                self::item(
                    label: __('Under Similarity Check'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_SIMILARITY_CHECK]],
                    permission: Permission::MANUSCRIPT_LIST_SIMILARITY_CHECK,
                ),
                self::item(
                    label: __('Under Pagination'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_PAGINATION]],
                    permission: Permission::MANUSCRIPT_LIST_PAGINATION,
                ),
                self::item(
                    label: __('Under Grammar Check'),
                    route: ['manuscripts.index', ['filter' => Filter::UNDER_GRAMMAR_CHECK]],
                    permission: Permission::MANUSCRIPT_LIST_GRAMMAR_CHECK,
                ),
            ],

            self::FORMATTER => [
                self::item(
                    label: __('Format Article'),
                    route: ['formatter.index', ['filter' => Filter::FORMATTER]],
                    permission: Permission::FORMATTER_LIST,
                )
            ],

            self::PROOFREADER => [
                self::item(
                    label: __('Proof Read Article'),
                    route: ['proofreader.index', ['filter' => Filter::PROOFREADER]],
                    permission: Permission::PROOFREADER_LIST,
                )
            ],

            default => []
        };
    }

    protected function item(string $label, string|array $route, string $permission)
    {
        return compact('label', 'route', 'permission');
    }
}
