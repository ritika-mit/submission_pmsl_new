<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Enums\Manuscript\Event;
use App\Enums\Manuscript\Status;
use App\Enums\Section;
use App\Models\Manuscript;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
class ProofreaderController extends Controller
{
    //
    public function index(Request $request)
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
            ->where([['associate_editors.id',auth()->user()->id],['revisions.status','proofreader']])
            ->when(
                $user->section !== Section::REVIEWER,
                fn($query) => $query
                    ->selectRaw("CONCAT_WS(' ', authors.title, authors.first_name, authors.middle_name, authors.last_name) as author")
                    ->leftJoin('authors', 'authors.id', '=', 'manuscripts.author_id')
                    ->with(
                        'revision:id,manuscript_id,index,anonymous_file,source_file,formatted_paper,correction_file,other_file,similarity,pages,grammar_updated,associate_editor_id,status',
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
                                $user->section == Section::PROOFREADER,
                                fn($query) => $query->where('value', Status::PROOFREADER),
                            )
                            ->latest()
                    ])
            )
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
                    if (in_array($user->section, [Section::PROOFREADER])) {
                        $manuscript->makeVisible('copyright_form');
                        $manuscript->revision->makeVisible('anonymous_file', 'source_file','formatted_paper','correction_file','other_file');
                    }

                    return $manuscript;
                }
            );
            $actions = [
                ['value' => 'proofreader_paper', 'label' => 'Proofreader paper']
            ];

        return Inertia::render('proofreader/list', compact('columns', 'data', 'actions'));
    }

    
    public function proofreaderPaper(Request $request,  ?Manuscript $proofreader) {

        $validated = $request->validate([
            'proofreader_paper' => [ 'required', 'file', 'mimes:doc,docx', 'max:65535']
        ]);
       $revision= $proofreader->revision;

        if ($request->hasFile('proofreader_paper')) {
            $revision->saveFileAndSetAttribute(
                file: $request->file('proofreader_paper'),
                attribute: 'proofreader_paper',
            );
        }
        $revision->status = 'ready-article';
        $revision->save();
        return back()->with('success', 'proofreader updated successfully.');
    }
}
