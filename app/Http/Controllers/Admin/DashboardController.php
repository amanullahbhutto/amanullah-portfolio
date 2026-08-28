<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\VisitorLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view dashboard');
    }

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Projects', 'value' => Project::query()->count(), 'icon' => 'bi-kanban', 'color' => 'orange'],
                ['label' => 'Blog Posts', 'value' => Post::query()->count(), 'icon' => 'bi-journal-richtext', 'color' => 'blue'],
                ['label' => 'Services', 'value' => Service::query()->count(), 'icon' => 'bi-grid', 'color' => 'green'],
                ['label' => 'Unread Messages', 'value' => ContactMessage::query()->unread()->count(), 'icon' => 'bi-envelope-exclamation', 'color' => 'purple'],
                ['label' => 'Website Views', 'value' => VisitorLog::query()->count(), 'icon' => 'bi-eye', 'color' => 'orange'],
            ],
            'latestMessages' => ContactMessage::query()->latest()->limit(6)->get(),
            'latestProjects' => Project::query()->latest()->limit(5)->get(),
        ]);
    }
}
