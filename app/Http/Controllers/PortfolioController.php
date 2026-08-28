<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function about(): View
    {
        return view('public.about', [
            'profile' => Profile::query()->first(),
            'experiences' => Experience::query()->orderByDesc('is_current')->orderByDesc('start_date')->get(),
            'educations' => Education::query()->orderByDesc('end_year')->get(),
            'skills' => Skill::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function projects(): View
    {
        return view('public.projects', ['projects' => Project::query()->published()->latest()->paginate(9)]);
    }

    public function project(Project $project): View
    {
        abort_unless($project->is_published, 404);

        return view('public.project-show', compact('project'));
    }

    public function posts(): View
    {
        return view('public.posts', ['posts' => Post::query()->published()->latest('published_at')->paginate(9)]);
    }

    public function post(Post $post): View
    {
        abort_unless($post->is_published && ($post->published_at === null || $post->published_at->isPast()), 404);

        return view('public.post-show', compact('post'));
    }

    public function services(): View
    {
        return view('public.services', ['services' => Service::query()->active()->orderBy('sort_order')->get()]);
    }
}
