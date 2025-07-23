<?php

namespace App\Http\Controllers;

use App\Enums\Manuscript\Status;
use App\Enums\Section;
use App\Http\Requests\ProfileRequest;
use App\Models\Author;
use App\Models\Country;
use App\Models\Manuscript;
use App\Models\ResearchArea;
use App\Models\Revision;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('dashboard/index', [
            'publishedManuscripts' => Manuscript::query()->status(Status::PUBLISHED)->count(),
            'totalManuscripts' => Manuscript::count(),
            'totalAuthor' => Author::query()->count(),
            'manuscriptCountByStatus' => $this->manuscriptCountByStatus($user),
            'authorsVsManuscripts' => $this->authorsVsManuscripts($user),
            'authors' => in_array($user->section, [Section::AUTHOR, Section::REVIEWER]) ? null : Author::query()->with('country')->latest()->limit(5)->get(),
            'manuscripts' => in_array($user->section, [Section::AUTHOR, Section::REVIEWER]) ? null :  Manuscript::query()->with('revision')->latest()->limit(5)->get(),
        ]);
    }

    public function manuscriptCountByStatus($user)
    {
        return Revision::query()
            ->selectRaw('count(*) as value')
            ->selectRaw("CASE WHEN status IN (?, ?) THEN 'revision-required' ELSE status END AS name", [Status::MAJOR_REVISION, Status::MINOR_REVISION])
            ->rightJoin('manuscripts', 'manuscripts.revision_id', '=', 'revisions.id')
            ->when(
                in_array($user->section, [Section::AUTHOR, Section::REVIEWER]),
                fn ($query) => $query->whereIn('status', [Status::PENDING, Status::SUBMITTED, Status::CONDITIONALLY_ACCEPTED, Status::ACCEPTED, Status::PRODUCTION, Status::PUBLICATION, Status::PUBLISHED, Status::FORMATTER, Status::PROOFREADER]),
                fn ($query) => $query->whereNotIn('status', [Status::DELETED, Status::WITHDRAWN])
            )
            ->groupBy('name')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->value,
                'name' => ucwords(str_replace('-', ' ', $item->name)),
            ]);
    }

    public function authorsVsManuscripts($user)
    {
        if (!in_array($user->section, [Section::AUTHOR, Section::REVIEWER])) {
            $authors = Author::query()
                ->selectRaw("COUNT(*) as value, DATE_FORMAT(created_at,'%Y-%m') as month")
                ->where('created_at', '>', Carbon::now()->subMonth(12))
                ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                ->get()
                ->pluck('value', 'month');
        }

        $manuscripts = Manuscript::query()
            ->selectRaw("COUNT(*) as value, DATE_FORMAT(created_at,'%Y-%m') as month")
            ->where('created_at', '>', Carbon::now()->subMonth(12))
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()
            ->pluck('value', 'month');

        $return = [
            'months' => [],
            'manuscripts' => [],
        ];

        if (!in_array($user->section, [Section::AUTHOR, Section::REVIEWER])) {
            $return['authors'] = [];
        }

        for ($i = 11; $i > -1; $i--) {
            $date = Carbon::now()->subMonths($i);
            $return['months'][] = $date->format('M, Y');
            if (!in_array($user->section, [Section::AUTHOR, Section::REVIEWER])) {
                $return['authors'][] = $authors[$date->format('Y-m')] ?? 0;
            }
            $return['manuscripts'][] = $manuscripts[$date->format('Y-m')] ?? 0;
        }

        return $return;
    }

    public function profile(Request $request)
    {
        $author = $request->user();
        $author->load('country', 'researchAreas');

        $author->makeVisible([
            'privacy_policy_accepted',
            'subscribed_for_notifications',
            'accept_review_request',
        ]);

        $countries = Country::query()->active()->get();
        $research_areas = ResearchArea::query()->active()->get();

        return Inertia::render('profile', compact('author', 'countries', 'research_areas'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $author = $request->user();
        $author->fill($request->validated());
        $author->country()->associate($request->input('country'));
        $author->save();

        $author->researchAreas()->sync(
            $request->input('research_areas', [])
        );

        return back()->with(
            'success',
            __('Profile updated successfully.')
        );
    }
}
