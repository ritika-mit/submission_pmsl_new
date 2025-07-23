<?php

namespace App\Http\Requests\Manuscript;

use App\Enums\Manuscript\Action;
use App\Enums\Manuscript\ReviewDecision;
use App\Models\Author;
use App\Models\Country;
use App\Models\GuestAuthor;
use App\Models\ResearchArea;
use App\Models\Revision;
use App\Models\RevisionAuthor;
use App\Models\RevisionReviewer;
use App\Models\TermAndCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $manuscript = $this->route('manuscript');

        return match ($this->route('action')) {
            default => true
        };
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $manuscript = $this->route('manuscript');
        $revision = $manuscript?->revision;

        return match ($action = $this->route('action')) {
            Action::CREATE, Action::EDIT => [
                'type' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255', Rule::unique(Revision::class, 'title')->ignore($manuscript?->id, 'manuscript_id')],
                'abstract' => ['required', 'string', 'max:65535'],
                'keywords' => ['required', 'array', 'min:3'],
                'keywords.*' => ['required', 'string', 'max:255'],
                'novelty' => ['nullable', 'string', 'max:65535'],
                'research_areas' => ['required', 'array', 'min:3'],
                'research_areas.*' => ['required', Rule::exists(ResearchArea::class, 'id')],
                'anonymous_file' => [$revision?->anonymous_file ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:65535'],
                'source_file' => [$revision?->source_file ? 'nullable' : 'required', 'file', 'mimes:doc,docx', 'max:65535'],
                'copyright_form' => [$manuscript?->copyright_form ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,gif,pdf', 'max:65535'],
            ],

            Action::ADD_REVIEWER, Action::ADD_AUTHOR => [
                'email' => ['required', 'email', 'max:255']
            ],

            Action::ADD_REVIEWER_MANUALLY, Action::ADD_AUTHOR_MANUALLY => [
                'id' => ['nullable', Rule::exists(GuestAuthor::class, 'id')->where('created_by', '!=', $user->getAuthIdentifier())],
                'title' => ['required', 'string', 'max:255'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique(Author::class), Rule::unique(GuestAuthor::class)->ignore($this->input('id'))],
                'highest_qualification' => ['required', 'string', 'max:255'],
                'organization_institution' => ['required', 'string', 'max:255'],
                'country' => ['required', Rule::exists(Country::class, 'id')],
            ],

            Action::REMOVE_REVIEWER, Action::INVITE_REVIEWER, Action::REMIND_REVIEWER => [
                'revision_reviewer' => ['required', Rule::exists(RevisionReviewer::class, 'id')->where('revision_id', $revision->getKey())],
            ],

            Action::REMOVE_AUTHOR => [
                'revision_author' => ['required', Rule::exists(RevisionAuthor::class, 'id')->where('revision_id', $revision->getKey())],
            ],

            Action::REVIEWERS => [
                'reviewers' => ['required', 'array', "min:{$revision->minimum_reviews}", function (string $attribute, mixed $reviewers, \Closure $fail) {
                    if (count(array_filter(array_count_values(array_map(fn ($item) => $item['country_id'] ?: 0, $reviewers)), fn ($count) => $count > 3))) {
                        $fail(__("Only three (3) {$attribute} can be added from same country."));
                    }
                }],
                'reviewers.*.email' => ['required', 'email', 'distinct', 'max:255'],
            ],

            Action::SUBMIT => [
                'reviewers' => ['required', 'array', "min:{$revision->minimum_reviews}", function (string $attribute, mixed $reviewers, \Closure $fail) {
                    if (count(array_filter(array_count_values(array_map(fn ($item) => $item['country_id'] ?: 0, $reviewers)), fn ($count) => $count > 3))) {
                        $fail(__("Only three (3) {$attribute} can be added from same country."));
                    }
                }],
                'reviewers.*.email' => ['required', 'email', 'distinct', 'max:255'],
                'term_and_conditions' => ['required', 'array', 'min:' . TermAndCondition::count()],
                'term_and_conditions.*.id' => ['required', Rule::exists(TermAndCondition::class, 'id')],
                'term_and_conditions.*.accepted' => ['accepted'],
            ],

            Action::ASSIGN_ASSOCIATE_EDITOR => [
                'associate_editor' => ['required', Rule::exists(Author::class, 'id')],
            ],

            Action::INVITE => [
                'minimum_reviews' => ['required', 'numeric', 'min:1'],
                // 'reviewers' => ['required', 'array'],
                // 'reviewers.*.email' => ['required', 'email', 'distinct', 'max:255'],
            ],

            Action::REVIEW, Action::UPDATE_COMMENT => [
                'revision' => [$action === Action::UPDATE_COMMENT ? 'required' : 'nullable'],
                'review' => [$action === Action::UPDATE_COMMENT ? 'required' : 'nullable'],
                'comments_to_author' => ['required', 'string', 'max:65535'],
                'decision' => ['required', Rule::enum(ReviewDecision::class)],
                'comments_to_associate_editor' => ['nullable', 'string', 'max:65535'],
                'review_report' => ['nullable', 'file', 'mimes:doc,docx,jpg,jpeg,png,gif,pdf', 'max:65535'],
            ],

            Action::SEND_TO_EIC => [
                'comments_to_eic' => ['required', 'string', 'max:65535'],
            ],

            Action::REVISE => [
                'title' => ['required', 'string', 'max:255', Rule::unique(Revision::class, 'title')->ignore($manuscript?->id, 'manuscript_id')],
                'abstract' => ['required', 'string', 'max:65535'],
                'keywords' => ['required', 'array', 'min:3'],
                'keywords.*' => ['required', 'string', 'max:255'],
                'novelty' => ['nullable', 'string', 'max:65535'],
                'comment_reply' => ['required', 'string', 'max:65535'],
                'comment_reply_file' => ['required', 'file', 'mimes:pdf', 'max:65535'],
                'anonymous_file' => ['required', 'file', 'mimes:pdf', 'max:65535'],
                'source_file' => ['required', 'file', 'mimes:doc,docx', 'max:65535'],
            ],

            Action::UPDATE_SIMILARITY => [
                'similarity' => ['required', 'numeric', 'min:0', 'max:100'],
            ],

            Action::UPDATE_PAGES => [
                'pages' => ['required', 'numeric', 'min:0'],
                'source_file' => ['required', 'file', 'mimes:doc,docx', 'max:65535'],
            ],

            Action::GRAMMAR_UPDATED => [
                'source_file' => ['required', 'file', 'mimes:doc,docx', 'max:65535'],
            ],

            Action::UPDATE_COMMENT_REPLY => [
                'comment_reply' => ['required', 'string', 'max:65535'],
                'comment_reply_file' => [
                    $manuscript->revisions()->where('index', $revision->index - 1)->whereNull('comment_reply_file')->exists()
                        ? 'required'
                        : 'nullable',
                    'file',
                    'mimes:pdf',
                    'max:65535'
                ],
            ],

            default => []
        };
    }

    public function prepareForValidation()
    {
        $manuscript = $this->route('manuscript');
        $revision = $manuscript?->revision;

        switch ($this->route('action')) {
            case Action::CREATE:
            case Action::EDIT:
            case Action::REVISE:
                $this->decodeHashId('research_areas');
                $this->merge(['keywords' => array_map('trim', array_filter(explode(',', $this->input('keywords'))))]);
                break;

            case Action::ADD_REVIEWER_MANUALLY:
            case Action::ADD_AUTHOR_MANUALLY:
                $this->decodeHashId('id', 'country');
                break;

            case Action::REMOVE_REVIEWER:
                $this->decodeHashId('revision_reviewer');
                break;

            case Action::REVIEWERS:
                $revision->load('reviewers.reviewer');
                $reviewers = $revision->reviewers->map->reviewer
                    ->makeVisible('country_id')
                    ->toArray();

                $this->merge(compact('reviewers'));
                break;

            case Action::REMOVE_AUTHOR:
                $this->decodeHashId('revision_author');
                break;

            case Action::AUTHORS:
                $revision->load('authors.author');
                $authors = $revision->authors->map->author->toArray();

                $this->merge(compact('authors'));
                break;

            case Action::SUBMIT:
                $revision->load('reviewers.reviewer');
                $reviewers = $revision->reviewers->map->reviewer
                    ->makeVisible('country_id')
                    ->toArray();

                $this->decodeHashId('term_and_conditions.*.id');
                $this->merge(compact('reviewers'));
                break;

            case Action::ASSIGN_ASSOCIATE_EDITOR:
                $this->decodeHashId('associate_editor');
                break;

            case Action::INVITE_REVIEWER:
            case Action::REINVITE_REVIEWER:
            case Action::REMIND_REVIEWER:
                $this->decodeHashId('revision_reviewer');
                break;

            case Action::UPDATE_COMMENT:
                $this->decodeHashId('revision', 'review');
                break;
        }
    }

    public function attributes()
    {
        $keys = array_keys(Arr::dot($this->only('reviewers', 'authors', 'term_and_conditions')));

        return array_map(
            fn ($item) => str_replace(
                [' id', ' accepted', '_'],
                ['', '', ' '],
                preg_replace_callback(
                    '/.(\d)./',
                    fn ($value) => ' ' . ($value[1] + 1) . ' ',
                    $item
                )
            ),
            array_combine($keys, $keys)
        );
    }

    public function safe(array $keys = null)
    {
        switch ($this->route('action')) {
            case Action::CREATE:
            case Action::EDIT:
            case Action::REVISE:
                return parent::safe($keys)->merge([
                    'keywords' => implode(', ', $this->input('keywords'))
                ]);

            default:
                return parent::safe($keys);
        }
    }
}
