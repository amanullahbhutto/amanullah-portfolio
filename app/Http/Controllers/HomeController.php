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

class HomeController extends Controller
{
    public function index(): View
    {
        $profile = Profile::query()->first();
        $experiences = Experience::query()->orderByDesc('is_current')->orderByDesc('start_date')->get();
        $experienceStat = $profile?->use_experience_dates
            ? Experience::formattedTotalDuration($experiences)
            : ($profile?->years_experience ?? 2).'y';

        return view('public.home', [
            'profile' => $profile,
            'experienceStat' => $experienceStat,
            'services' => Service::query()->active()->orderBy('sort_order')->get(),
            'projects' => Project::query()->published()->latest()->limit(6)->get(),
            'posts' => Post::query()->published()->latest('published_at')->limit(3)->get(),
            'experiences' => $experiences,
            'educations' => Education::query()->orderByDesc('end_year')->get(),
            'skills' => Skill::query()->active()->orderBy('sort_order')->get(),
        ]);
    }
}
