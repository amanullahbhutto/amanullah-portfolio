<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasbeeh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TasbeehAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! ($user->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $user->can('manage tasbeeh'))) {
                abort(403, 'Unauthorized. Admin access required to manage Tasbeeh master definitions.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        $tasbeehs = Tasbeeh::query()->ordered()->get();
        return view('admin.zikr.tasbeehs.index', compact('tasbeehs'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'arabic_text' => ['required', 'string'],
            'urdu_meaning' => ['nullable', 'string'],
            'daily_target' => ['required', 'integer', 'min:1', 'max:100000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'transliteration' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['created_by'] = $request->user()->id;

        $tasbeeh = Tasbeeh::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$tasbeeh->title}' created successfully.",
            'tasbeeh' => $tasbeeh,
        ]);
    }

    public function update(Request $request, Tasbeeh $tasbeeh): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'arabic_text' => ['required', 'string'],
            'urdu_meaning' => ['nullable', 'string'],
            'daily_target' => ['required', 'integer', 'min:1', 'max:100000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'transliteration' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $tasbeeh->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$tasbeeh->title}' updated successfully.",
            'tasbeeh' => $tasbeeh,
        ]);
    }

    public function toggle(Tasbeeh $tasbeeh): JsonResponse
    {
        $tasbeeh->update(['is_active' => ! $tasbeeh->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh status changed to " . ($tasbeeh->is_active ? 'Active' : 'Inactive') . ".",
            'is_active' => $tasbeeh->is_active,
        ]);
    }

    public function destroy(Tasbeeh $tasbeeh): JsonResponse
    {
        $title = $tasbeeh->title;
        $tasbeeh->delete();

        return response()->json([
            'success' => true,
            'message' => "Tasbeeh '{$title}' deleted successfully.",
        ]);
    }
}

