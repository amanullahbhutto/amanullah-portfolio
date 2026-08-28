<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitorLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view dashboard');
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $visits = VisitorLog::query()
            ->with(['user:id,name,email', 'project:id,title,slug'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('path', 'like', "%{$search}%")
                        ->orWhere('full_url', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('visitor_id', 'like', "%{$search}%")
                        ->orWhere('browser', 'like', "%{$search}%")
                        ->orWhere('platform', 'like', "%{$search}%")
                        ->orWhere('device_type', 'like', "%{$search}%")
                        ->orWhere('referrer', 'like', "%{$search}%");
                });
            })
            ->latest('visited_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            ['label' => 'Total Views', 'value' => VisitorLog::query()->count(), 'icon' => 'bi-eye', 'color' => 'orange'],
            ['label' => 'Unique Visitors', 'value' => VisitorLog::query()->distinct('visitor_id')->count('visitor_id'), 'icon' => 'bi-people', 'color' => 'blue'],
            ['label' => 'Today Views', 'value' => VisitorLog::query()->today()->count(), 'icon' => 'bi-calendar2-check', 'color' => 'green'],
            ['label' => 'Project Views', 'value' => VisitorLog::query()->whereNotNull('project_id')->count(), 'icon' => 'bi-kanban', 'color' => 'purple'],
        ];

        $topPages = VisitorLog::query()
            ->select('path', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT visitor_id) as visitors'), DB::raw('MAX(visited_at) as last_seen_at'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(8)
            ->get();

        $deviceBreakdown = VisitorLog::query()
            ->select('device_type', DB::raw('COUNT(*) as views'))
            ->groupBy('device_type')
            ->orderByDesc('views')
            ->get();

        return view('admin.visitors.index', compact('visits', 'stats', 'topPages', 'deviceBreakdown'));
    }
}
