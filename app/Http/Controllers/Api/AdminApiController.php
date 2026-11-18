<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\PwaSetting;
use App\Models\Service;
use App\Models\Skill;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminApiController extends Controller
{
    /**
     * Get initial authenticated bootstrap data for Admin React App
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = Profile::query()->first();
        $unreadCount = ContactMessage::query()->unread()->count();
        $pwaSettings = PwaSetting::getSettings();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin', 'admin', 'super-admin']),
            ],
            'unread_messages_count' => $unreadCount,
            'profile' => $profile ? array_merge($profile->toArray(), [
                'profile_image_url' => $profile->profile_image_url,
                'cv_file_url' => $profile->cv_file_url,
                'whatsapp_url' => $profile->whatsapp_url,
                'gmail_compose_url' => $profile->gmail_compose_url,
            ]) : null,
            'pwa_settings' => $pwaSettings,
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Admin Dashboard statistics and recent activity
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            ['label' => 'Projects', 'value' => Project::query()->count(), 'icon' => 'bi-kanban', 'color' => 'orange', 'route' => 'projects'],
            ['label' => 'Blog Posts', 'value' => Post::query()->count(), 'icon' => 'bi-journal-richtext', 'color' => 'blue', 'route' => 'posts'],
            ['label' => 'Services', 'value' => Service::query()->count(), 'icon' => 'bi-grid', 'color' => 'green', 'route' => 'services'],
            ['label' => 'Unread Messages', 'value' => ContactMessage::query()->unread()->count(), 'icon' => 'bi-envelope-exclamation', 'color' => 'purple', 'route' => 'messages'],
            ['label' => 'Website Views', 'value' => VisitorLog::query()->count(), 'icon' => 'bi-eye', 'color' => 'orange', 'route' => 'visitors'],
            ['label' => 'Skills', 'value' => Skill::query()->count(), 'icon' => 'bi-bar-chart', 'color' => 'blue', 'route' => 'skills'],
        ];

        $latestMessages = ContactMessage::query()->latest()->limit(5)->get();
        $latestProjects = Project::query()->latest()->limit(5)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'project_type_label' => $p->project_type_label,
                'is_published' => (bool) $p->is_published,
                'created_at' => $p->created_at ? $p->created_at->format('M d, Y') : null,
            ];
        });

        $recentVisitors = VisitorLog::query()->latest('visited_at')->limit(5)->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'ip_address' => $v->ip_address,
                'path' => $v->path,
                'browser' => $v->browser,
                'platform' => $v->platform,
                'device_type' => $v->device_type,
                'visited_at' => $v->visited_at ? $v->visited_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'stats' => $stats,
            'latest_messages' => $latestMessages,
            'latest_projects' => $latestProjects,
            'recent_visitors' => $recentVisitors,
        ]);
    }

    /**
     * Get content list for projects, posts, services, skills, experiences, educations
     */
    public function getContent(string $type): JsonResponse
    {
        $modelMap = [
            'projects' => Project::class,
            'posts' => Post::class,
            'services' => Service::class,
            'skills' => Skill::class,
            'experiences' => Experience::class,
            'educations' => Education::class,
        ];

        if (!isset($modelMap[$type])) {
            return response()->json(['message' => 'Invalid content type.'], 404);
        }

        $query = $modelMap[$type]::query();

        if ($type === 'projects') {
            $query->withCount(['visitorLogs as project_views_count']);
        }

        if (in_array($type, ['services', 'skills'])) {
            $query->orderBy('sort_order');
        } elseif ($type === 'experiences') {
            $query->orderByDesc('is_current')->orderByDesc('start_date');
        } elseif ($type === 'educations') {
            $query->orderByDesc('end_year');
        } else {
            $query->latest();
        }

        $items = $query->get()->map(function ($item) use ($type) {
            $arr = $item->toArray();
            if ($type === 'projects' || $type === 'posts') {
                $arr['image_url'] = $item->image_url;
            }
            if ($type === 'projects') {
                $arr['image_urls'] = $item->image_urls;
                $arr['project_type_label'] = $item->project_type_label;
                $arr['project_type_badge_class'] = $item->project_type_badge_class;
                $arr['project_views_count'] = $item->project_views_count ?? 0;
            }
            if ($type === 'experiences') {
                $arr['start_date_formatted'] = $item->start_date ? $item->start_date->format('M Y') : null;
                $arr['end_date_formatted'] = $item->end_date ? $item->end_date->format('M Y') : 'Present';
            }
            return $arr;
        });

        return response()->json([
            'type' => $type,
            'items' => $items,
        ]);
    }

    /**
     * Store new content record
     */
    public function storeContent(Request $request, string $type): JsonResponse
    {
        $modelMap = [
            'projects' => Project::class,
            'posts' => Post::class,
            'services' => Service::class,
            'skills' => Skill::class,
            'experiences' => Experience::class,
            'educations' => Education::class,
        ];

        if (!isset($modelMap[$type])) {
            return response()->json(['message' => 'Invalid type.'], 400);
        }

        $data = $request->all();

        if (in_array($type, ['projects', 'posts', 'services']) && empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $item = $modelMap[$type]::create($data);

        return response()->json([
            'success' => true,
            'message' => ucfirst(rtrim($type, 's')) . ' created successfully.',
            'item' => $item,
        ]);
    }

    /**
     * Update content record
     */
    public function updateContent(Request $request, string $type, int $id): JsonResponse
    {
        $modelMap = [
            'projects' => Project::class,
            'posts' => Post::class,
            'services' => Service::class,
            'skills' => Skill::class,
            'experiences' => Experience::class,
            'educations' => Education::class,
        ];

        if (!isset($modelMap[$type])) {
            return response()->json(['message' => 'Invalid type.'], 400);
        }

        $item = $modelMap[$type]::findOrFail($id);
        $data = $request->all();

        if (in_array($type, ['projects', 'posts', 'services']) && empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => ucfirst(rtrim($type, 's')) . ' updated successfully.',
            'item' => $item,
        ]);
    }

    /**
     * Delete content record
     */
    public function deleteContent(string $type, int $id): JsonResponse
    {
        $modelMap = [
            'projects' => Project::class,
            'posts' => Post::class,
            'services' => Service::class,
            'skills' => Skill::class,
            'experiences' => Experience::class,
            'educations' => Education::class,
        ];

        if (!isset($modelMap[$type])) {
            return response()->json(['message' => 'Invalid type.'], 400);
        }

        $item = $modelMap[$type]::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully.',
        ]);
    }

    /**
     * Get messages with live filter/pagination
     */
    public function messages(): JsonResponse
    {
        $messages = ContactMessage::query()->latest()->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'name' => $msg->name,
                'email' => $msg->email,
                'phone' => $msg->phone,
                'subject' => $msg->subject,
                'message' => $msg->message,
                'is_read' => (bool) $msg->is_read,
                'ip_address' => $msg->ip_address,
                'created_at' => $msg->created_at ? $msg->created_at->format('M d, Y h:i A') : null,
                'created_at_human' => $msg->created_at ? $msg->created_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Mark message as read or mark all
     */
    public function markMessageRead(Request $request, string $id): JsonResponse
    {
        if ($id === 'all') {
            ContactMessage::query()->unread()->update(['is_read' => true]);
            return response()->json(['success' => true, 'message' => 'All messages marked as read.']);
        }

        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json(['success' => true, 'message' => 'Message marked as read.']);
    }

    /**
     * Delete message
     */
    public function deleteMessage(int $id): JsonResponse
    {
        ContactMessage::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Message deleted.']);
    }

    /**
     * Visitors log
     */
    public function visitors(): JsonResponse
    {
        $visitors = VisitorLog::query()->latest('visited_at')->limit(200)->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'ip_address' => $v->ip_address,
                'path' => $v->path,
                'route_name' => $v->route_name,
                'browser' => $v->browser,
                'platform' => $v->platform,
                'device_type' => $v->device_type,
                'referrer' => $v->referrer,
                'visited_at' => $v->visited_at ? $v->visited_at->format('M d, Y h:i A') : null,
                'visited_at_human' => $v->visited_at ? $v->visited_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'visitors' => $visitors,
        ]);
    }

    /**
     * Users list
     */
    public function users(): JsonResponse
    {
        $users = User::query()->latest()->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->getRoleNames(),
                'created_at' => $u->created_at ? $u->created_at->format('M d, Y') : null,
            ];
        });

        return response()->json(['users' => $users]);
    }

    /**
     * Roles & Permissions
     */
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Profile get & update
     */
    public function getProfile(): JsonResponse
    {
        $profile = Profile::first();
        return response()->json(['profile' => $profile]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $profile = Profile::firstOrCreate(['id' => 1]);
        $profile->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'profile' => $profile,
        ]);
    }
}
