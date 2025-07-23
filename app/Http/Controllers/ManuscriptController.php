<?php

namespace App\Http\Controllers;

use App\Enums\Manuscript\Action;
use App\Enums\Manuscript\Event;
use App\Enums\Manuscript\Filter;
use App\Enums\Manuscript\ReviewDecision;
use App\Enums\Manuscript\Status;
use App\Enums\Manuscript\Type;
use App\Enums\Section;
use App\Http\Requests\Manuscript\UpdateRequest;
use App\Models\Author;
use App\Models\Country;
use App\Models\GuestAuthor;
use App\Models\Manuscript;
use App\Models\ResearchArea;
use App\Models\Review;
use App\Models\Revision;
use App\Models\RevisionAuthor;
use App\Models\RevisionReviewer;
use App\Models\Role;
use App\Models\TermAndCondition;
use App\Notifications\Manuscript\ReviewerInvited;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ManuscriptController extends Controller
{
    private $author_columns = [
        'id',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'highest_qualification',
        'organization_institution',
        'country_id'
    ];

    public function index(Request $request, Filter $filter = Filter::PENDING)
    {
        $user = $request->user();
        $columns = [
            'index' => [
                'label' => __('S. No.'),
                'orderable' => false,
                'searchable' => false
            ],
            'code' => [
                'label' => __('Paper ID'),
                'orderable' => true,
                'searchable' => true
            ],
            'type' => [
                'label' => __('Manuscript Type'),
                'orderable' => true,
                'searchable' => true
            ],
            'title' => [
                'label' => __('Title'),
                'orderable' => false,
                'searchable' => false
            ],
            'keywords' => [
                'label' => __('Keywords'),
                'orderable' => false,
                'searchable' => false
            ],
            'author' => [
                'label' => __('Corresponding Author'),
                'orderable' => false,
                'searchable' => false
            ],
            'associate_editor' => [
                'label' => __('Associate Editor'),
                'orderable' => false,
                'searchable' => false
            ],
           /* 'proofreader_name' => [
                'label' => __('Proofreader Name'),
                'orderable' => false,
                'searchable' => false
            ],
            'proofreader_email' => [
                'label' => __('Proofreader Email'),
                'orderable' => false,
                'searchable' => false
            ],
            'formatter_name' => [
                'label' => __('Formatter Name'),
                'orderable' => false,
                'searchable' => false
            ],
            'formatter_email' => [
                'label' => __('Formatter Email'),
                'orderable' => false,
                'searchable' => false
            ],*/
            'other' => [
                'label' => __('Other'),
                'orderable' => false,
                'searchable' => false
            ],
            'events' => [
                'label' => __('Events'),
                'orderable' => false,
                'searchable' => false
            ],
            // 'created_at' =>   [
            //     'label' => __('Created on'),
            //     'orderable' => false,
            //     'searchable' => false
            // ],
            // 'updated_at' =>   [
            //     'label' => __('Updated on'),
            //     'orderable' => false,
            //     'searchable' => false
            // ],
            'action' => [
                'label' => __('Action'),
                'orderable' => false,
                'searchable' => false
            ]
        ];
        
        // if($filter === Filter::PROOFREADER) {
        //     unset($columns['formatter_email']);
        //     unset($columns['formatter_name']);
        // }else if($filter === Filter::FORMATTER) {
        //     unset($columns['proofreader_email']);
        //     unset($columns['proofreader_name']);
        // }else {
        //     unset($columns['proofreader_email']);
        //     unset($columns['proofreader_name']);
        //     unset($columns['formatter_email']);
        //     unset($columns['formatter_name']);
        // }
        if ($user->section === Section::REVIEWER) {
            unset($columns['author']);
        }

        if (!in_array($user->section, [Section::EDITOR_IN_CHIEF, Section::EPM])) {
            unset($columns['other']);
        }

        $data = Manuscript::query()
            ->select('manuscripts.id', 'manuscripts.code', 'manuscripts.type', 'manuscripts.copyright_form', 'manuscripts.revision_id', 'revisions.title', 'revisions.keywords', 'revisions.created_at', 'revisions.updated_at')
            // ->select('manuscripts.id', 'manuscripts.code', 'manuscripts.type', 'manuscripts.copyright_form', 'manuscripts.revision_id', 'revisions.title', 'revisions.keywords', 'revisions.created_at', 'revisions.updated_at', 'revision_auth.email as user_email')
            ->selectRaw("CONCAT_WS(' ', associate_editors.title, associate_editors.first_name, associate_editors.middle_name, associate_editors.last_name) as associate_editor")
            // ->selectRaw("CONCAT_WS(' ', revision_auth.title, revision_auth.first_name, revision_auth.middle_name, revision_auth.last_name) as user_name")
            ->leftJoin('revisions', 'revisions.id', '=', 'manuscripts.revision_id')
            // ->leftJoin('revision_authors', 'revision_authors.revision_id', '=', 'manuscripts.revision_id')
            // ->leftJoin('authors as revision_auth', 'revision_auth.id', '=', 'revision_authors.author_id')
            ->leftJoin('authors as associate_editors', 'associate_editors.id', '=', 'revisions.associate_editor_id')
            ->with('revision.manuscript:id,code')
            ->when(
                $user->section !== Section::REVIEWER,
                fn($query) => $query
                    ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as author")
                    ->leftJoin('authors', 'authors.id', '=', 'manuscripts.author_id')
                    ->with(
                        'revision:id,manuscript_id,index,anonymous_file,source_file,formatted_paper,correction_file,other_file,proofreader_paper,similarity,pages,grammar_updated,associate_editor_id,status',
                    )
            )
            ->when(
                $user->section === Section::EDITOR_IN_CHIEF && in_array($filter, [Filter::SUBMITTED, Filter::WITH_AE]),
                fn($query) => $query
                    ->with([
                        'revisions' => fn($query) => $query
                            ->select('id', 'manuscript_id', 'index', 'comment_reply', 'comment_reply_file', 'associate_editor_id')
                            ->with('associateEditor:id')
                    ])
            )
            ->when(
                $columns['events'] ?? null,
                fn($query) => $query
                    ->with([
                        'events' => fn($query) => $query
                            ->where('event', Event::STATUS_UPDATED)
                            ->when(
                                $user->section == Section::REVIEWER,
                                fn($query) => $query->where('value', Status::SUBMITTED),
                            )
                            ->latest()
                    ])
            )
            ->filterForUserAndStatus(user: $user, filter: $filter)
            ->search(
                $request->input('search'),
                array_keys(array_filter($columns, fn($item) => $item['searchable']))
            )
            ->when(
                $request->input('order'),
                function ($query, $order) {
                    foreach ($order as $column => $dir) {
                        $query->orderBy($column, $dir);
                    }
                    return $query;
                },
                fn(Builder $query) => $query->latest()
            )
            ->jsonPaginate(
                perPage: $request->input('perPage'),
                transform: function ($manuscript) use ($filter, $user) {
                    if (in_array($user->section, [Section::AUTHOR]) && !in_array($manuscript->revision->status, [Status::DELETED, Status::WITHDRAWN, Status::REJECTED])) {
                        $manuscript->revision->makeVisible('anonymous_file');
                    }

                    if (in_array($user->section, [Section::REVIEWER]) && $filter !== Filter::REVIEWED) {
                        $manuscript->revision->makeVisible('anonymous_file');
                    }

                    if (in_array($user->section, [Section::ASSOCIATE_EDITOR])) {
                        $manuscript->revision->makeVisible('anonymous_file');
                    }

                    if (in_array($user->section, [Section::EDITOR_IN_CHIEF, Section::EPM])) {
                        $manuscript->makeVisible('copyright_form');
                        $manuscript->revision->makeVisible('anonymous_file', 'source_file', 'formatted_paper', 'correction_file', 'other_file', 'proofreader_paper');
                    }

                    if (in_array($user->section, [Section::EDITOR_IN_CHIEF]) && $manuscript->revisions->count()) {
                        $manuscript->revisions->each->makeVisible('comment_reply_file');
                    }

                    $manuscript->actions = $this->manuscriptListAction($manuscript, $filter, $user);

                    return $manuscript;
                }
            );

        $associate_editors = Inertia::lazy(
            fn() => Author::query()
                ->associateEditor()
                ->select($this->author_columns)
                ->with('country:id,name')
                ->orderBy('first_name')
                ->get()
        );
        $formatterlist = Role::query()
        ->select('authors.id','authors.email','authors.first_name as name','authors.highest_qualification','authors.organization_institution', 'countries.name as country_name')
        ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as name")
        ->leftJoin('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->leftJoin('authors', 'authors.id', '=', 'model_has_roles.model_id')
        ->leftJoin('countries', 'countries.id', '=', 'authors.country_id')
        ->where('roles.section','formatter')->get();

        $proofreaderlist = Role::query()
        ->select('authors.id','authors.email','authors.first_name as name','authors.highest_qualification','authors.organization_institution', 'countries.name as country_name')
        ->leftJoin('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as name")
        ->leftJoin('authors', 'authors.id', '=', 'model_has_roles.model_id')
        ->leftJoin('countries', 'countries.id', '=', 'authors.country_id')
        ->where('roles.section','proofreader')->get();
// echo "<pre>";
// print_r($data);
// echo "</pre>";
// exit();
        return Inertia::render('manuscript/list', compact('columns', 'data', 'associate_editors', 'formatterlist', 'proofreaderlist'));
    }

    function getFormatter(UpdateRequest $request, Manuscript $manuscript){
        $proofreaderlist = Role::query()
        ->select('authors.id','authors.email','authors.first_name as name','authors.highest_qualification','authors.organization_institution', 'countries.name as country_name')
        ->leftJoin('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as name")
        ->leftJoin('authors', 'authors.id', '=', 'model_has_roles.model_id')
        ->leftJoin('countries', 'countries.id', '=', 'authors.country_id')
        ->where('roles.section','proofreader')->get();
        $arr = array();
        if(count($proofreaderlist)>0){
            foreach($proofreaderlist as $proofreader){
                array_push($arr, $proofreader->id);
            }
            
        }
        if(count($arr)>0){
            $manu = RevisionAuthor::where('revision_id',$manuscript->revision_id)->whereIn('author_id',$arr)->get();
            if(count($manu)>0){
                return 0;
            }else{
                return 1;
            }
        }

    }

    protected function manuscriptListAction(Manuscript $manuscript, Filter $filter, Author $user)
    {
        // print_r($filter);
        // exit();
        $actions = [
            Action::VIEW
        ];

        if ($filter === Filter::PENDING && $user->section === Section::AUTHOR) {
            $actions[] = Action::EDIT;
        }

        if ($user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::EDIT;
        }

        if ($user->section === Section::EDITOR_IN_CHIEF && $manuscript->revisions->count() > 1 && ($filter !== Filter::CONDITIONALLY_ACCEPTED) && ($filter !== Filter::UNDER_SIMILARITY_CHECK) && ($filter !== Filter::UNDER_PAGINATION) && ($filter !== Filter::UNDER_GRAMMAR_CHECK) && ($filter !== Filter::READY_FOR_ACCEPT) && ($filter !== Filter::FORMATTER) && ($filter !== Filter::PROOFREADER) && ($filter !== Filter::READY_ARTICLE) && ($filter !== Filter::PRODUCTION)) {
            $actions[] = Action::UPDATE_COMMENT_REPLY;
        }

        if ($filter === Filter::PENDING && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::DELETE;
        }

        if ($filter === Filter::WITH_AE && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::ASSIGN_ASSOCIATE_EDITOR;
            $actions[] = Action::WITHDRAW;
        }

        if ($filter === Filter::UNDER_REVISION && $user->section === Section::AUTHOR) {
            $actions[] = Action::REVISE;
        }

        if ($filter === Filter::UNDER_REVISION && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::REMIND_AUTHOR;
            $actions[] = Action::WITHDRAW;
        }

        if ($filter === Filter::REVIEW && $user->section === Section::REVIEWER) {
            $actions[] = Action::REVIEW;
        }

        if ($filter === Filter::INVITE && $user->section === Section::ASSOCIATE_EDITOR) {
            $actions[] = Action::INVITE_REVIEWER;
        }

        if ($filter === Filter::INVITED && $user->section === Section::ASSOCIATE_EDITOR) {
            $actions[] = Action::INVITE_MORE_REVIEWER;
        }

        if ($filter === Filter::REVIEWED && $user->section === Section::ASSOCIATE_EDITOR) {
            $actions[] = Action::SEND_TO_EIC;
        }

        if ($filter === Filter::SUBMITTED && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::ASSIGN_ASSOCIATE_EDITOR;
            $actions[] = Action::CONDITIONALLY_ACCEPT;
            $actions[] = Action::ACCEPT;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::WITH_EIC && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::MINOR_REVISION;
            $actions[] = Action::MAJOR_REVISION;
            $actions[] = Action::CONDITIONALLY_ACCEPT;
            $actions[] = Action::ACCEPT;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::CONDITIONALLY_ACCEPTED && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::SEND_FOR_SIMILARITY_CHECK;
            $actions[] = Action::ACCEPT;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::READY_FOR_ACCEPT && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::ACCEPT;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::ACCEPTED && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::FORMATTER;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }
        if ($filter === Filter::READY_ARTICLE && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::PUBLICATION;
        }

        if ($filter === Filter::PROOFREADER && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::PROOFREADER;
        }

        if ($filter === Filter::PRODUCTION && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::PUBLICATION;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::PUBLICATION && $user->section === Section::EDITOR_IN_CHIEF) {
            $actions[] = Action::PUBLISH;
            $actions[] = Action::WITHDRAW;
            $actions[] = Action::REJECT;
        }

        if ($filter === Filter::UNDER_SIMILARITY_CHECK && $user->section === Section::EPM) {
            $actions[] = Action::UPDATE_SIMILARITY;
        }

        if ($filter === Filter::UNDER_PAGINATION && $user->section === Section::EPM) {
            $actions[] = Action::UPDATE_PAGES;
        }

        if ($filter === Filter::UNDER_GRAMMAR_CHECK && $user->section === Section::EPM) {
            $actions[] = Action::GRAMMAR_UPDATED;
        }

        return $actions;
    }

    public function getAction(Request $request, ?Manuscript $manuscript, Action $action)
    {
        return match ($action) {
            Action::CREATE, Action::EDIT => $this->createEdit(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REVIEWERS => $this->reviewers(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::AUTHORS => $this->authors(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::VIEW => $this->view(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::INVITE => $this->invite(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REVIEW => $this->review(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REVISE => $this->revise(
                request: $request,
                manuscript: $manuscript,
            ),

            default => abort(404)
        };
    }

    public function postAction(UpdateRequest $request, ?Manuscript $manuscript, Action $action)
    {
        return match ($action) {
            Action::CREATE, Action::EDIT => $this->storeUpdate(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ADD_REVIEWER => $this->addRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ADD_REVIEWERS => $this->addRevisionReviewers(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ADD_REVIEWER_MANUALLY => $this->addRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
                reviewer: $this->createGuestAuthor(request: $request)
            ),

            Action::REMOVE_REVIEWER => $this->removeRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REVIEWERS => $this->updateReviewers(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ADD_AUTHOR => $this->addRevisionAuthor(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ADD_AUTHOR_MANUALLY => $this->addRevisionAuthor(
                request: $request,
                manuscript: $manuscript,
                author: $this->createGuestAuthor(request: $request)
            ),

            Action::REMOVE_AUTHOR => $this->removeRevisionAuthor(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::AUTHORS => $this->updateAuthors(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::SUBMIT => $this->submit(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::ASSIGN_ASSOCIATE_EDITOR => $this->assignAssociateEditor(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::INVITE => $this->updateInvite(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::INVITE_REVIEWER => $this->inviteRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REINVITE_REVIEWER => $this->reInviteRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REMIND_REVIEWER => $this->remindRevisionReviewer(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REVIEW => $this->addReview(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_COMMENT => $this->updateReview(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::SEND_TO_EIC => $this->sendToEIC(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::MINOR_REVISION, Action::MAJOR_REVISION => $this->revisionRequired(
                request: $request,
                manuscript: $manuscript,
                status: match ($action) {
                        Action::MINOR_REVISION => Status::MINOR_REVISION,
                        Action::MAJOR_REVISION => Status::MAJOR_REVISION,
                    }
            ),

            Action::REVISE => $this->updateRevise(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::WITHDRAW, Action::DELETE, Action::CONDITIONALLY_ACCEPT,Action::FORMATTER,Action::PROOFREADER,
            Action::ACCEPT, Action::REJECT, Action::PRODUCTION, Action::PUBLICATION,
            Action::PUBLISH => $this->updateStatus(
                request: $request,
                manuscript: $manuscript,
                status: match ($action) {
                        Action::WITHDRAW => Status::WITHDRAWN,
                        Action::DELETE => Status::DELETED,
                        Action::CONDITIONALLY_ACCEPT => Status::CONDITIONALLY_ACCEPTED,
                        Action::ACCEPT => Status::ACCEPTED,
                        Action::REJECT => Status::REJECTED,
                        Action::PRODUCTION => Status::PRODUCTION,
                        Action::PUBLICATION => Status::PUBLICATION,
                        Action::FORMATTER => Status::FORMATTER,
                        Action::PROOFREADER => Status::PROOFREADER,
                        Action::PUBLISH => Status::PUBLISHED,
                    }
            ),

            Action::SEND_FOR_SIMILARITY_CHECK => $this->sendForSimilarityCheck(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_SIMILARITY => $this->updateSimilarity(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_FORMATTER => $this->updateFormatter(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_PROOFREADER => $this->updateProofreader(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::SEND_FOR_PAGINATION => $this->sendForPagination(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_PAGES => $this->updatePages(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::SEND_FOR_GRAMMAR_CHECK => $this->sendForGrammarCheck(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::GRAMMAR_UPDATED => $this->grammarUpdated(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::UPDATE_COMMENT_REPLY => $this->updateCommentReply(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::REMIND_AUTHOR => $this->remindAuthor(
                request: $request,
                manuscript: $manuscript,
            ),

            Action::GET_PROOFREADER => $this->getFormatter(
                request: $request,
                manuscript: $manuscript,
            ),

            default => abort(404)
        };
    }

    protected function file(?string $file_path)
    {
        if (!$file_path || !Storage::exists($file_path))
            abort(404);

        return response()->file(Storage::path($file_path), [
            'Content-Type' => Storage::mimeType($file_path),
        ]);
    }

    public function manuscriptFile(Request $request, Manuscript $manuscript, string $type, string $file_name)
    {
        return $this->file(
            file_path: $manuscript->getRawOriginal(str_replace('-', '_', $type))
        );
    }

    public function revisionFile(Request $request, Manuscript $manuscript, Revision $revision, string $type, string $file_name)
    {
        return $this->file(
            file_path: $revision->getRawOriginal(str_replace('-', '_', $type))
        );
    }

    public function reviewFile(Request $request, Manuscript $manuscript, Revision $revision, Review $review, string $type, string $file_name)
    {
        return $this->file(
            file_path: $review->getRawOriginal(str_replace('-', '_', $type))
        );
    }

    protected function createEdit(Request $request, ?Manuscript $manuscript)
    {
        $manuscript ??= new Manuscript;

        $manuscript->load('revision', 'researchAreas');
        $manuscript->makeVisible('copyright_form');
        $manuscript->append('step');

        $manuscript->revision?->makeVisible('anonymous_file', 'source_file');

        $types = Type::cases();
        $research_areas = ResearchArea::query()->active()->get();

        $can_submit = !$manuscript->exists || $manuscript->author?->is($request->user());

        return Inertia::render('manuscript/edit', compact('manuscript', 'types', 'research_areas', 'can_submit'));
    }

    protected function storeUpdate(UpdateRequest $request, ?Manuscript $manuscript)
    {
        $user = $request->user();

        $manuscript ??= new Manuscript;

        if (!$manuscript->exists) {
            $manuscript->generateCodeAttribute();
            $manuscript->author()->associate($user);
        }

        $manuscript->fill(attributes: $request->only(['type']));

        if ($request->hasFile('copyright_form')) {
            $manuscript->saveFileAndSetAttribute(
                file: $request->file('copyright_form'),
                attribute: 'copyright_form',
            );
        }

        $manuscript->save();

        $manuscript->researchAreas()->sync(
            $request->input('research_areas', [])
        );

        $revision = $manuscript->revision;
        $revision ??= new Revision;

        $revision->manuscript()->associate($manuscript);

        $revision->fill(attributes: $request->safe()->only([
            'title',
            'abstract',
            'keywords',
            'novelty'
        ]));

        if ($request->hasFile('anonymous_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('anonymous_file'),
                attribute: 'anonymous_file',
            );
        }

        if ($request->hasFile('source_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('source_file'),
                attribute: 'source_file',
            );
        }

        $revision->save();

        $manuscript->revision()->associate($revision);
        $manuscript->save();

        if ($manuscript->wasRecentlyCreated) {
            $revision->createEvent(
                event: Event::STATUS_UPDATED,
                value: Status::PENDING
            );
        }

        return ($request->input('action') === 'submit&next'
        ? to_route('manuscripts.action', [$manuscript, 'reviewers'])
        : back())
            ->with('success', $manuscript->wasRecentlyCreated
                ? __('Manuscript created successfully.')
                : __('Manuscript updated successfully.'));
    }

    protected function searchAuthorOrGuestAuthor(?string $search, array $exclude_ids = [])
    {
        if (!$search) {
            return [];
        }

        return Author::query()
            ->select($this->author_columns)
            ->with('country')
            ->verified()
            ->whereEmail($search)
            ->whereKeyNot($exclude_ids[Author::class] ?? [])
            ->unionAll(
                GuestAuthor::query()
                    ->select($this->author_columns)
                    ->with('country')
                    ->whereEmail($search)
                    ->whereKeyNot($exclude_ids[GuestAuthor::class] ?? [])
            )
            ->limit(10)
            ->get();
    }

    public function getSimilarResearchAreaReviewers(Request $request)
    {
        $search = $request->input('search');

        $reviewers = Reviewer::query()
            ->when($search, function ($query, $search) {
                $query->where('email', 'like', "%$search%")
                    ->orWhere('first_name', 'like', "$search%")  // Search by first name (starts with input)
                    ->orWhere('last_name', 'like', "$search%");   // Search by last name (starts with input)
            })
            ->get();

        return Inertia::render('ManuscriptPage', [
            'similar_research_area_reviewers' => $reviewers,
        ]);
    }



    public function reviewers(Request $request, Manuscript $manuscript)
    {
        $manuscript->revision->load('reviewers.reviewer.country');
        $manuscript->append('step');

        $countries = fn() => Country::query()->active()->get();

        $reviewers = Inertia::lazy(fn() => $this->searchAuthorOrGuestAuthor(
            search: $request->input('search'),
        )
        );

        return Inertia::render('manuscript/edit-reviewers', compact('manuscript', 'countries', 'reviewers'));
    }

    public function authors(Request $request, Manuscript $manuscript)
    {
        $manuscript->revision->load('authors.author.country');
        $manuscript->append('step');

        $countries = fn() => Country::query()->active()->get();
        $authors = Inertia::lazy(fn() => $this->searchAuthorOrGuestAuthor(
            search: $request->input('search'),
        )
        );

        return Inertia::render('manuscript/edit-authors', compact('manuscript', 'countries', 'authors'));
    }

    protected function createGuestAuthor(UpdateRequest $request)
    {
        $author = $request->input('id')
            ? GuestAuthor::query()->find($request->input('id'))
            : new GuestAuthor;

        $author->fill($request->safe()->except(['id', 'country']));
        $author->country()->associate($request->input('country'));
        $author->save();

        return $author;
    }

    protected function addRevisionReviewer(UpdateRequest $request, Manuscript $manuscript, Author|GuestAuthor $reviewer = null)
    {
        if ($reviewer === null) {
            $reviewer = Author::query()->where($request->only('email'))->first();
        }

        if ($reviewer === null) {
            $reviewer = GuestAuthor::query()->where($request->only('email'))->first();
        }

        if ($reviewer == null) {
            return back()
                ->withErrors(['reviewers' => 'Reviewer not found.']);
        }

        if ($reviewer->is($manuscript->author)) {
            return back()
                ->withErrors(['reviewers' => 'Corresponding author can not added as reviewer.']);
        }

        if ($manuscript->authors?->firstWhere('author_id', $reviewer->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Co author can not added as reviewer.']);
        }

        if ($manuscript->revision->reviewers?->firstWhere('reviewer_id', $reviewer->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Reviewer can not added twice.']);
        }

        if ($manuscript->associateEditors?->firstWhere('id', $reviewer->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Associate editor can not added as reviewer.']);
        }

        $revision_reviewer = new RevisionReviewer;
        $revision_reviewer->revision()->associate($manuscript->revision);
        $revision_reviewer->reviewer()->associate($reviewer);
        $revision_reviewer->save();

        return back();
    }

    protected function removeRevisionReviewer(UpdateRequest $request, Manuscript $manuscript)
    {
        $revision_reviewer = $manuscript->revision
            ->reviewers()
            ->with('reviewer')
            ->find($request->input('revision_reviewer'));
        $reviewer = $revision_reviewer->getRelation('reviewer');

        //$revision_reviewer->delete(); Manoj
        //forceDelete will delete
        $revision_reviewer->forceDelete();

        if (true === $reviewer->can_edit && (0 === $reviewer->revisionAsReviewer()->count() && 0 === $reviewer->revisionAsAuthor()->count())) {
            //$reviewer->delete(); //Manoj
            $reviewer->forceDelete();
        }

        return back();
    }

    protected function updateReviewers(UpdateRequest $request, Manuscript $manuscript)
    {
        return ($request->input('action') === 'submit&next'
        ? to_route('manuscripts.action', [$manuscript, 'authors'])
        : back())
            ->with('success', __('Manuscript reviewers updated successfully.'));
    }

    protected function addRevisionAuthorProofreaderFormatter(UpdateRequest $request, Manuscript $manuscript, Author|GuestAuthor $author = null)
    {
        if ($author === null) {
            $author = Author::query()->where($request->only('email'))->first();
        }

        if ($author === null) {
            $author = GuestAuthor::query()->where($request->only('email'))->first();
        }

        if ($author === null) {
            return back()->withErrors(['authors' => 'Author not found.']);
        }

        // if ($author->is($manuscript->author)) {
        //     return back()
        //         ->withErrors(['reviewers' => 'Corresponding author can not added as co-author.']);
        // }

        // if ($manuscript->authors?->firstWhere('author_id', $author->getKey())) {
        //     return back()
        //         ->withErrors(['reviewers' => 'Co-author can not added twice.']);
        // }

        // if ($manuscript->reviewers?->firstWhere('reviewer_id', $author->getKey())) {
        //     return back()
        //         ->withErrors(['reviewers' => 'Reviewer can not added as co-author.']);
        // }

        // if ($manuscript->associateEditors?->firstWhere('id', $author->getKey())) {
        //     return back()
        //         ->withErrors(['reviewers' => 'Associate editor can not added as co-author.']);
        // }

        $revision_author = new RevisionAuthor;
        $revision_author->revision()->associate($manuscript->revision);
        $revision_author->author()->associate($author);
        $revision_author->save();

        return back();
    }
    protected function addRevisionAuthor(UpdateRequest $request, Manuscript $manuscript, Author|GuestAuthor $author = null)
    {
        
        if ($author === null) {
            $author = Author::query()->where($request->only('email'))->first();
        }

        if ($author === null) {
            $author = GuestAuthor::query()->where($request->only('email'))->first();
        }

        if ($author === null) {
            return back()->withErrors(['authors' => 'Author not found.']);
        }

        if ($author->is($manuscript->author)) {
            return back()
                ->withErrors(['reviewers' => 'Corresponding author can not added as co-author.']);
        }

        if ($manuscript->authors?->firstWhere('author_id', $author->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Co-author can not added twice.']);
        }

        if ($manuscript->reviewers?->firstWhere('reviewer_id', $author->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Reviewer can not added as co-author.']);
        }

        if ($manuscript->associateEditors?->firstWhere('id', $author->getKey())) {
            return back()
                ->withErrors(['reviewers' => 'Associate editor can not added as co-author.']);
        }

        $revision_author = new RevisionAuthor;
        $revision_author->revision()->associate($manuscript->revision);
        $revision_author->author()->associate($author);
        $revision_author->save();

        return back();
    }

    protected function removeRevisionAuthor(UpdateRequest $request, Manuscript $manuscript)
    {
        $revision_author = $manuscript->revision
            ->authors()
            ->with('author')
            ->find($request->input('revision_author'));

        $author = $revision_author->getRelation('author');

        $revision_author->delete();

        if (true === $author->can_edit && (0 === $author->revisionAsReviewer()->count() && 0 === $author->revisionAsAuthor()->count())) {
            $author->delete();
        }

        return back();
    }

    protected function updateAuthors(UpdateRequest $request, Manuscript $manuscript)
    {
        return ($request->input('action') === 'submit&next'
        ? to_route('manuscripts.action', [$manuscript, 'view'])
        : back())
            ->with('success', __('Manuscript authors updated successfully.'));
    }

    protected function view(Request $request, Manuscript $manuscript)
    {
        $user = $request->user();
        $manuscript->loadMissing('revision', 'researchAreas');

        if ($user->section === Section::AUTHOR && in_array($manuscript->revision->status, [Status::PENDING, Status::SUBMITTED])) {
            $manuscript->append('step');

            $manuscript->loadMissing(
                'author.country',
                'authors.author.country',
            );

            $manuscript->revision->makeVisible('anonymous_file');

            if ($manuscript->revision->status === Status::PENDING) {
                $manuscript->loadMissing('termAndConditions');

                $manuscript->makeVisible('copyright_form');
                $manuscript->revision->makeVisible('source_file');

                $manuscript->revision->loadMissing([
                    'reviewers' => fn($query) => $query
                        ->whereSection(Section::AUTHOR)
                        ->with('reviewer.country'),
                ]);

                $term_and_conditions = fn() => TermAndCondition::query()->active()->get();

                $can_submit = $manuscript->author->is($user);
            }
        } else {
            $manuscript
                ->load([
                    'revisions' => fn($query) => $query
                        ->select('id', 'index', 'manuscript_id', 'anonymous_file', 'source_file', 'comments_to_eic', 'comment_reply', 'comment_reply_file')
                        ->with([
                            'reviews' => fn($query) => $query
                                ->when(
                                    in_array($user->section, [Section::REVIEWER]),
                                    fn($query) => $query->whereReviewerId($user->getKey())
                                )
                                ->when(
                                    !in_array($user->section, [Section::AUTHOR, Section::REVIEWER]),
                                    fn($query) => $query->with('reviewer')
                                )
                                ->latest()
                        ])
                        ->latest('index'),
                ]);

            $reviewer_review_pending = false;

            if (in_array($user->section, [Section::AUTHOR])) {
                if (!in_array($manuscript->revision->status, [Status::DELETED, Status::WITHDRAWN, Status::REJECTED])) {
                    $manuscript->revision->makeVisible('anonymous_file');
                }
            } elseif (in_array($user->section, [Section::REVIEWER])) {
                if ($manuscript->revision()->reviewPending($user)->exists()) {
                    $reviewer_review_pending = true;
                    $manuscript->revision->makeVisible('anonymous_file');
                }
            } elseif (in_array($user->section, [Section::ASSOCIATE_EDITOR, Section::EDITOR_IN_CHIEF, Section::EPM])) {
                $manuscript->revision->makeVisible('anonymous_file');
            }

            $manuscript->revisions->each(function ($revision) use ($manuscript, $user, $reviewer_review_pending) {
                if (in_array($user->section, [Section::AUTHOR])) {
                    if (!in_array($manuscript->revision->status, [Status::DELETED, Status::WITHDRAWN, Status::REJECTED])) {
                        $revision->makeVisible('anonymous_file', 'comment_reply_file');
                    }
                } elseif (in_array($user->section, [Section::REVIEWER])) {
                    if ($reviewer_review_pending) {
                        $revision->makeVisible('anonymous_file', 'comment_reply_file');
                    }
                } elseif (in_array($user->section, [Section::ASSOCIATE_EDITOR, Section::EDITOR_IN_CHIEF, Section::EPM])) {
                    $revision->makeVisible('anonymous_file', 'comment_reply_file');
                }

                if (in_array($user->section, [Section::AUTHOR])) {
                    $revision->reviews->each(function ($review) {
                        $review->makeHidden('comments_to_associate_editor');
                    });
                }
            });

            if (in_array($user->section, [Section::ASSOCIATE_EDITOR, Section::EDITOR_IN_CHIEF, Section::EPM])) {

                $manuscript->loadMissing('author.country');
                $manuscript->loadMissing('authors.author.country');
                $manuscript->loadMissing('events.revision:id,index');

                $manuscript->revision->loadMissing('reviewers.reviewer.country');

                if ($user->section === Section::EDITOR_IN_CHIEF) {
                    $term_and_conditions = fn() => TermAndCondition::query()->active()->get();

                    $manuscript->loadMissing('termAndConditions');

                    $manuscript->makeVisible('copyright_form');
                    $manuscript->revision->makeVisible('source_file');

                    $can_edit_review = true;

                    $review_decisions = fn() => ReviewDecision::cases();
                }
            }
        }

        $can_submit ??= null;
        $term_and_conditions ??= null;

        $can_edit_review ??= null;
        $review_decisions ??= null;
        // echo "<pre>";
        // print_r($manuscript->authors);
        // echo "</pre>";
        // exit();
        return Inertia::render('manuscript/view', compact('manuscript', 'term_and_conditions', 'review_decisions', 'can_submit', 'can_edit_review'));
    }

    protected function submit(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->status = $status = Status::SUBMITTED;

        $manuscript->revision->save();

        $manuscript->revision->createEvent(
            event: Event::STATUS_UPDATED,
            value: $status
        );

        $manuscript->termAndConditions()->sync(
            array_column($request->input('term_and_conditions', []), 'id')
        );

        $manuscript->author
            ->sendManuscriptSubmittedNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Manuscript submitted successfully.')
            );
    }

    protected function assignAssociateEditor(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision
            ->associateEditor()
            ->associate($request->input('associate_editor'));

        $manuscript->revision->save();

        $manuscript->revision
            ->associateEditor
            ->sendManuscriptAssociateEditorAssignedNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Associate editor assigned.')
            );
    }

    protected function invite(Request $request, Manuscript $manuscript)
    {
        $reviewers = Inertia::lazy(fn() => $this->searchAuthorOrGuestAuthor(
            search: $request->input('search'),
        )
        );

        $manuscript->loadMissing([
            'revision',
            'researchAreas',
            'author.country',
            'authors.author.country',
            'events.revision:id,index',
            'revisions' => fn($query) => $query
                ->select('id', 'index', 'manuscript_id', 'anonymous_file', 'source_file', 'comments_to_eic', 'comment_reply', 'comment_reply_file')
                ->with('reviews.reviewer')
                ->latest('index'),

        ]);

        $manuscript->revision->loadMissing([
            'reviewers' => fn($query) => $query
                ->with([
                    'reviewer' => fn($query) => $query
                        ->with('country')
                        ->withCount([
                            'revisionAsReviewer as pending_review_count' => fn($query) => $query
                                ->whereNotNull('invited_at')
                                ->whereNotNull('accepted_at')
                                ->whereNull('denied_at')
                                ->whereDoesntHave(
                                    relation: 'review',
                                    callback: fn(Builder $query) => $query
                                        ->whereColumn('reviews.revision_id', 'revision_reviewers.revision_id')
                                )
                        ]),
                    'review' => fn($query) => $query
                        ->where('revision_id', $manuscript->revision->id),
                ]),
        ]);

        $manuscript->revision->makeVisible('anonymous_file', 'minimum_reviews');

        $manuscript->revisions->each(function ($revision) {
            $revision->makeVisible('anonymous_file', 'comment_reply_file');
        });

        $countries = fn() => Country::query()->active()->get();

        $similar_research_area_reviewers = Inertia::lazy(
            fn() => $this->similarResearchAreaReviewers($manuscript)
        );

        $reinvite_reviewer_email_content = Inertia::lazy(
            fn() => $this->previewReInviteRevisionReviewer($request, $manuscript)
        );

        return Inertia::render('manuscript/invite', compact('manuscript', 'countries', 'reviewers', 'similar_research_area_reviewers', 'reinvite_reviewer_email_content'));
    }

    protected function previewReInviteRevisionReviewer(Request $request, Manuscript $manuscript)
    {
        $revision_reviewer = $manuscript->revision
            ->reviewers()
            ->with('reviewer')
            ->findOrFail(hash_id_decode($request->input('revision_reviewer')));

        $mail = (new ReviewerInvited($manuscript))
            ->toMail($revision_reviewer->reviewer);

        return [
            'subject' => $mail->subject,
            'content' => (string) $mail->render(),
        ];
    }

    /*     protected function similarResearchAreaReviewers(Manuscript $manuscript)
        {
            return Author::query()
                ->whereKeyNot($manuscript->revision->reviewers->map->reviewer->map->getKey())
                ->whereKeyNot($manuscript->revision->authors->map->author->map->getKey())
                ->whereKeyNot($manuscript->revision->associateEditor->getKey())
                ->whereKeyNot($manuscript->author->getKey())
                ->whereHas(
                    relation: 'researchAreas',
                    callback: fn ($query) => $query->whereKey(
                        $manuscript->researchAreas->map->getKey()
                    )
                )
                ->reviewer()
                ->with('country')
                ->withCount([
                    'revisionAsReviewer as pending_review_count' => fn ($query) => $query
                        ->whereNotNull('invited_at')
                        ->whereNotNull('accepted_at')
                        ->whereNull('denied_at')
                        ->whereDoesntHave(
                            relation: 'review',
                            callback: fn (Builder $query) => $query
                                ->whereColumn('reviews.revision_id', 'revision_reviewers.revision_id')
                        )
                ])
                ->get();
        } */

    protected function similarResearchAreaReviewers(Manuscript $manuscript, $searchTerm = null)
    {
        $searchTerm = request()->query('search', null);  // Fetch 'search' from the request

        \Log::info("Received Search Term for invite reviewers from journal: " . json_encode($searchTerm));
        return Author::query()
            ->whereKeyNot($manuscript->revision->reviewers->map->reviewer->map->getKey())
            ->whereKeyNot($manuscript->revision->authors->map->author->map->getKey())
            ->whereKeyNot($manuscript->revision->associateEditor->getKey())
            ->whereKeyNot($manuscript->author->getKey())
            ->whereHas(
                relation: 'researchAreas',
                callback: fn($query) => $query->whereIn(
                    'research_areas.id',
                    $manuscript->researchAreas->map->getKey()
                )
            )
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%$searchTerm%")
                      ->orwhere('last_name', 'LIKE', "%$searchTerm%")
                      ->orwhere('organization_institution', 'LIKE', "%$searchTerm%")
                      ->orWhere('email', 'LIKE', "%$searchTerm%");
                });
            })
            ->reviewer()
            ->with('country')
            ->with(['researchAreas' => fn($query) => $query->select('research_areas.research_area')]) // Select relevant columns
            ->withCount([
                'revisionAsReviewer as pending_review_count' => fn($query) => $query
                    ->whereNotNull('invited_at')
                    ->whereNotNull('accepted_at')
                    ->whereNull('denied_at')
                    ->whereDoesntHave(
                        relation: 'review',
                        callback: fn(Builder $query) => $query
                            ->whereColumn('reviews.revision_id', 'revision_reviewers.revision_id')
                    )
            ])
            ->get();
    }



    protected function updateInvite(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->minimum_reviews = $request->input('minimum_reviews');
        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function inviteRevisionReviewer(UpdateRequest $request, Manuscript $manuscript)
    {
        $revision_reviewer = $manuscript->revision
            ->reviewers()
            ->with('reviewer')
            ->find($request->input('revision_reviewer'));

        $revision_reviewer->invite();

        $revision_reviewer->reviewer
            ->sendManuscriptReviewerInvitedNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Reviewer invited successfully.')
            );
    }

    protected function reInviteRevisionReviewer(UpdateRequest $request, Manuscript $manuscript)
    {
        $revision_reviewer = $manuscript->revision
            ->reviewers()
            ->with('reviewer')
            ->find($request->input('revision_reviewer'));

        $revision_reviewer->invite();

        $reviewer = $revision_reviewer->reviewer;
        $subject = $request->input('subject');

        Mail::html(
            html: $request->input('content'),
            callback: function ($message) use ($reviewer, $subject) {
                $message
                    ->to($reviewer->email, $reviewer->name)
                    ->subject($subject);
            }
        );

        return back()
            ->with(
                'success',
                __('Reviewer reinvited successfully.')
            );
    }

    protected function remindRevisionReviewer(UpdateRequest $request, Manuscript $manuscript)
    {
        $revision_reviewer = $manuscript->revision
            ->reviewers()
            ->with('reviewer')
            ->find($request->input('revision_reviewer'));

        $revision_reviewer->remind();

        $revision_reviewer->reviewer
            ->sendManuscriptReviewReminderNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Reviewer reminded for review successfully.')
            );
    }

    protected function remindAuthor(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->author
            ->sendManuscriptRevisionReminderNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Author reminded for revision successfully.')
            );
    }

    protected function respondReviewInvite(Manuscript $manuscript, Action $action, string $type, string $author)
    {
        $reviewer = (hash_equals($type, sha1(Author::class)) ? Author::query() : GuestAuthor::query()->withTrashed())
            ->findOrFail(hash_id_decode($author));

        if (get_class($reviewer) === GuestAuthor::class && $reviewer->trashed()) {
            $reviewer = Author::query()
                ->whereEmail($reviewer->email)
                ->firstOrFail();
        }

        $revision_reviewer = RevisionReviewer::query()
            ->whereRevisionId($manuscript->revision->getKey())
            ->whereReviewerType($reviewer::class)
            ->whereReviewerId($reviewer->getKey())
            ->whereNotNull('invited_at')
            ->whereNull('accepted_at')
            ->whereNull('denied_at')
            ->firstOrFail();

        if (!($accept = $action === Action::ACCEPT_REVIEW_INVITE)) {
            $reviewer->sendManuscriptReviewInviteDeniedNotification($manuscript);
        }

        $revision_reviewer->{$accept ? 'accept' : 'deny'}();

        $response = $accept ? 'accepted' : 'denied';

        return (Auth::user() ? to_route('index') : to_route('auth.index'))
            ->with(
                'success',
                __('Manuscript review request :response successfully.', compact('response'))
            );
    }

    protected function review(Request $request, Manuscript $manuscript)
    {
        $review = $manuscript->revision
            ->reviews()
            ->whereReviewerId($request->user()->getKey())
            ->first();

        $review_decisions = ReviewDecision::cases();

        return Inertia::render('manuscript/review', compact('manuscript', 'review', 'review_decisions'));
    }

    protected function addReview(UpdateRequest $request, Manuscript $manuscript)
    {
        $reviewer = $request->user();

        $review = $manuscript->revision->reviews()
            ->whereReviewerId($reviewer->getKey())
            ->firstOr(fn() => tap(
                new Review,
                function ($review) use ($reviewer, $manuscript) {
                    $review->revision()->associate($manuscript->revision);
                    $review->reviewer()->associate($reviewer);
                }
            )
            );

        $review->fill(
            attributes: $request->safe()->except('review_report')
        );

        if ($request->hasFile('review_report')) {
            $review->saveFileAndSetAttribute(
                file: $request->file('review_report'),
                attribute: 'review_report',
            );
        }

        $review->save();

        $reviewer->sendManuscriptReviewSubmittedNotification($manuscript);

        return to_route('index')
            ->with(
                'success',
                __('Manuscript review submitted successfully.')
            );
    }

    protected function updateReview(UpdateRequest $request, Manuscript $manuscript)
    {
        $review = $manuscript->reviews()
            ->whereRevisionId($request->input('revision'))
            ->findOrFail($request->input('review'));

        $review->fill(
            attributes: $request->safe()->except('review_report')
        );

        if ($request->hasFile('review_report')) {
            $review->saveFileAndSetAttribute(
                file: $request->file('review_report'),
                attribute: 'review_report',
            );
        }

        $review->save();

        return back()->with(
            'success',
            __('Manuscript review updated successfully.')
        );
    }

    protected function sendToEIC(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->fill($request->validated());
        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function revisionRequired(UpdateRequest $request, Manuscript $manuscript, Status $status)
    {
        $manuscript->revision->status = $status;
        $manuscript->revision->save();

        $manuscript->author
            ->sendManuscriptRevisionRequiredNotification($manuscript);

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function revise(Request $request, Manuscript $manuscript)
    {
        $manuscript->load('revision', 'researchAreas');

        return Inertia::render('manuscript/revise', compact('manuscript'));
    }

    protected function updateRevise(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->loadMissing('revision', 'author');

        $manuscript->revision->fill(
            attributes: $request->safe()->only(['comment_reply'])
        );

        if ($request->hasFile('comment_reply_file')) {
            $manuscript->revision->saveFileAndSetAttribute(
                file: $request->file('comment_reply_file'),
                attribute: 'comment_reply_file',
            );
        }

        $manuscript->revision->save();

        $revision = new Revision;

        $revision->manuscript()->associate($manuscript);
        $revision->unsetRelation('manuscript');

        $revision->index = $manuscript->revision->index + 1;

        $revision->fill(attributes: $request->safe()->only([
            'title',
            'abstract',
            'keywords',
            'novelty'
        ]));

        if ($request->hasFile('anonymous_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('anonymous_file'),
                attribute: 'anonymous_file',
            );
        }

        if ($request->hasFile('source_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('source_file'),
                attribute: 'source_file',
            );
        }

        $revision->status = Status::SUBMITTED;
        $revision->save();

        $manuscript->revision->reviewers()
            ->whereHas(
                'review',
                fn($query) => $query
                    ->where('revision_id', $manuscript->revision->id)
            )
            ->get()
            ->each(function ($item) use ($revision) {
                $revision_reviewer = new RevisionReviewer;
                $revision_reviewer->revision_id = $revision->id;
                $revision_reviewer->reviewer_type = $item->reviewer_type;
                $revision_reviewer->reviewer_id = $item->reviewer_id;
                $revision_reviewer->section = $item->section;
                $revision_reviewer->created_by = $item->created_by;

                $revision_reviewer->save();
            });

        $manuscript->revision()->associate($revision);
        $manuscript->save();
        $manuscript->unsetRelation('revision');

        $manuscript->revision->createEvent(
            event: Event::STATUS_UPDATED,
            value: $revision->status
        );

        $manuscript->author
            ->sendManuscriptRevisedNotification($manuscript);

        return to_route('index')
            ->with(
                'success',
                __('Manuscript submitted successfully.')
            );
    }

    protected function updateStatus(UpdateRequest $request, Manuscript $manuscript, Status $status)
    {
        $manuscript->revision->status = $status;
        $manuscript->revision->save();

        $manuscript->revision->createEvent(
            event: Event::STATUS_UPDATED,
            value: $status
        );

        match ($status) {
            Status::WITHDRAWN => $manuscript->author
                ->sendManuscriptWithdrawnNotification($manuscript),

            Status::REJECTED => $manuscript->author
                ->sendManuscriptRejectedNotification($manuscript),

            Status::CONDITIONALLY_ACCEPTED => $manuscript->author
                ->sendManuscriptConditionallyAcceptedNotification($manuscript),

            Status::ACCEPTED => $manuscript->author
                ->sendManuscriptAcceptedNotification($manuscript),

            Status::PUBLISHED => $manuscript->author
                ->sendManuscriptPublishedNotification($manuscript),

            default => null
        };

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function sendForSimilarityCheck(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->similarity_check_required = true;
        $manuscript->revision->similarity = null;

        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function updateSimilarity(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->similarity = $request->input('similarity');

        $manuscript->revision->pagination_required = true;
        $manuscript->revision->pages = null;

        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }
    protected function updateFormatter(UpdateRequest $request, Manuscript $manuscript)
    {
        $rules = [
            'email' => 'required|email'
        ];
        $customMessages = [
            'required' => 'please choose at least one email.'
        ];
        $this->validate($request, $rules, $customMessages);
        $manuscript->revision->status = 'formatter';

        $manuscript->revision->pagination_required = true;
        $manuscript->revision->pages = null;

        $manuscript->revision->save();
        $this->addRevisionAuthorProofreaderFormatter(
            request: $request,
            manuscript: $manuscript
        );
        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function updateProofreader(UpdateRequest $request, Manuscript $manuscript)
    {
        $rules = [
            'email' => 'required|email'
        ];
        $customMessages = [
            'required' => 'please choose at least one email.'
        ];
        $this->validate($request, $rules, $customMessages);
        $manuscript->revision->status = 'proofreader';

        $manuscript->revision->pagination_required = true;
        $manuscript->revision->pages = null;

        $manuscript->revision->save();
        $this->addRevisionAuthorProofreaderFormatter(
            request: $request,
            manuscript: $manuscript
        );
        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }
    protected function sendForPagination(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->pagination_required = true;
        $manuscript->revision->pages = null;

        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function updatePages(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->pages = $request->input('pages');

        if ($request->hasFile('source_file')) {
            $manuscript->revision->saveFileAndSetAttribute(
                file: $request->file('source_file'),
                attribute: 'source_file',
            );
        }

        $manuscript->revision->grammar_check_required = true;
        $manuscript->revision->grammar_updated = null;

        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function sendForGrammarCheck(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->grammar_check_required = true;
        $manuscript->revision->grammar_updated = false;

        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function grammarUpdated(UpdateRequest $request, Manuscript $manuscript)
    {
        $manuscript->revision->grammar_updated = true;

        if ($request->hasFile('source_file')) {
            $manuscript->revision->saveFileAndSetAttribute(
                file: $request->file('source_file'),
                attribute: 'source_file',
            );
        }
        $manuscript->revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function updateCommentReply(UpdateRequest $request, Manuscript $manuscript)
    {
        $previous_revision = $manuscript->revisions()
            ->where('index', $manuscript->revision->index - 1)
            ->firstOrFail();

        $previous_revision->fill(
            attributes: $request->safe()->only(['comment_reply'])
        );

        if ($request->hasFile('comment_reply_file')) {
            $previous_revision->saveFileAndSetAttribute(
                file: $request->file('comment_reply_file'),
                attribute: 'comment_reply_file',
            );
        }
        $previous_revision->save();

        return back()
            ->with(
                'success',
                __('Manuscript updated successfully.')
            );
    }

    protected function addRevisionReviewers(UpdateRequest $request, Manuscript $manuscript, Author|GuestAuthor $reviewer = null)
    {
        if (count($request->_rawValue) > 0) {
            foreach ($request->_rawValue as $req) {
                if ($reviewer === null) {
                    $reviewer = Author::query()->where('email', $req['email'])->first();
                }

                if ($reviewer === null) {
                    $reviewer = GuestAuthor::query()->where('email', $req['email'])->first();
                }

                if ($reviewer == null) {
                    return back()
                        ->withErrors(['reviewers' => 'Reviewer not found.']);
                }

                if ($reviewer->is($manuscript->author)) {
                    return back()
                        ->withErrors(['reviewers' => 'Corresponding author can not added as reviewer.']);
                }

                if ($manuscript->authors?->firstWhere('author_id', $reviewer->getKey())) {
                    return back()
                        ->withErrors(['reviewers' => 'Co author can not added as reviewer.']);
                }

                if ($manuscript->revision->reviewers?->firstWhere('reviewer_id', $reviewer->getKey())) {
                    return back()
                        ->withErrors(['reviewers' => 'Reviewer can not added twice.']);
                }

                if ($manuscript->associateEditors?->firstWhere('id', $reviewer->getKey())) {
                    return back()
                        ->withErrors(['reviewers' => 'Associate editor can not added as reviewer.']);
                }

                $revision_reviewer = new RevisionReviewer;
                $revision_reviewer->revision()->associate($manuscript->revision);
                $revision_reviewer->reviewer()->associate($reviewer);
                $revision_reviewer->save();
                $reviewer = null;
            }
        }
        return back();
    }
}
