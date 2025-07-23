<?php

namespace App\Http\Controllers;

use App\Enums\Manuscript\Filter;
use Illuminate\Http\Request;
use App\Enums\Manuscript\Event;
use App\Enums\Manuscript\Status;
use App\Enums\Section;
use App\Models\Manuscript;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;

class FormatterController extends Controller
{
    //
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
            'action' => [
                'label' => __('Action'),
                'orderable' => false,
                'searchable' => false
            ]
        ];

        $data = Manuscript::query()
            ->select('manuscripts.id', 'manuscripts.code', 'manuscripts.type', 'manuscripts.copyright_form', 'manuscripts.revision_id', 'revisions.title', 'revisions.keywords', 'revisions.created_at', 'revisions.updated_at')
            ->selectRaw("CONCAT_WS(' ', associate_editors.title, associate_editors.first_name, associate_editors.middle_name, associate_editors.last_name) as associate_editor")
            ->leftJoin('revisions', 'revisions.id', '=', 'manuscripts.revision_id')
            ->leftJoin('revision_authors', 'revision_authors.revision_id', '=', 'manuscripts.revision_id')
            ->leftJoin('authors as associate_editors', 'associate_editors.id', '=', 'revision_authors.author_id')
            ->with('revision.manuscript:id,code')
            ->where([['associate_editors.id',auth()->user()->id],['revisions.status','formatter']])
            ->when(
                $user->section !== Section::REVIEWER,
                fn($query) => $query
                    ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as author")
                    ->leftJoin('authors', 'authors.id', '=', 'manuscripts.author_id')
                    ->with(
                        'revision:id,manuscript_id,index,anonymous_file,source_file,similarity,pages,grammar_updated,associate_editor_id,status',
                    )
            )
            ->when(
                $user->section === Section::EDITOR_IN_CHIEF,
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
                                $user->section == Section::FORMATTER,
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
                transform: function ($manuscript) use ($user) {
                    if (in_array($user->section, [Section::FORMATTER])) {
                        $manuscript->makeVisible('copyright_form');
                        $manuscript->revision->makeVisible('anonymous_file', 'source_file','formatted_paper','correction_file','other_file');
                    }

                    return $manuscript;
                }
            );
            $actions = [
                ['value' => 'format_paper', 'label' => 'Format paper']
            ];

        return Inertia::render('formatter/list', compact('columns', 'data', 'actions'));
    }

    public function formatPaper(Request $request,  ?Manuscript $formatter) {

        $validated = $request->validate([
            'formatted_paper' => [ 'required', 'file', 'mimes:doc,docx', 'max:65535'],
            'correction_file' => ['required', 'file', 'mimes:doc,docx', 'max:65535'],
        ]);
       $revision= $formatter->revision;

        if ($request->hasFile('formatted_paper')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('formatted_paper'),
                attribute: 'formatted_paper',
            );
        }

        if ($request->hasFile('correction_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('correction_file'),
                attribute: 'correction_file',
            );
        }
        if ($request->hasFile('other_file')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('other_file'),
                attribute: 'other_file',
            );
        }
        $revision->status = 'proofreader';
        $revision->save();
        return back()->with('success', 'formatter updated successfully.');
    }
}
