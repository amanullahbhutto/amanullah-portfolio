<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioContentController extends Controller
{
    private const TYPES = [
        'projects' => ['model' => Project::class, 'label' => 'Projects', 'resource' => 'project', 'search' => ['title', 'slug', 'excerpt', 'description', 'project_type']],
        'posts' => ['model' => Post::class, 'label' => 'Blog Posts', 'resource' => 'post', 'search' => ['title', 'slug', 'excerpt', 'content']],
        'services' => ['model' => Service::class, 'label' => 'Services', 'resource' => 'service', 'search' => ['title', 'slug', 'short_description', 'icon']],
        'experiences' => ['model' => Experience::class, 'label' => 'Experience', 'resource' => 'experience', 'search' => ['company', 'position', 'location', 'summary']],
        'educations' => ['model' => Education::class, 'label' => 'Education', 'resource' => 'education', 'search' => ['institution', 'degree', 'field', 'description']],
        'skills' => ['model' => Skill::class, 'label' => 'Skills', 'resource' => 'skill', 'search' => ['name', 'category']],
    ];

    public function __construct()
    {
        $this->middleware('permission:view project|view post|view service|view experience|view education|view skill', ['only' => ['index']]);
        $this->middleware('permission:create project|create post|create service|create experience|create education|create skill', ['only' => ['create', 'store']]);
        $this->middleware('permission:update project|update post|update service|update experience|update education|update skill', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete project|delete post|delete service|delete experience|delete education|delete skill', ['only' => ['destroy']]);
    }

    public function index(Request $request, string $type): View
    {
        $config = $this->config($request, $type, 'view');
        $search = trim($request->string('q')->toString());
        $query = $config['model']::query();

        if ($type === 'projects') {
            $query->withCount(['visitorLogs as project_views_count']);
        }

        $items = $query
            ->when($search !== '', function (Builder $query) use ($config, $search): void {
                $query->where(function (Builder $query) use ($config, $search): void {
                    foreach ($config['search'] as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();
        $experienceProfile = $type === 'experiences' ? Profile::query()->first() : null;

        return view('admin.content.index', compact('type', 'config', 'items', 'experienceProfile'));
    }

    public function create(Request $request, string $type): View
    {
        $config = $this->config($request, $type, 'create');
        $item = new $config['model'];

        return view('admin.content.form', compact('type', 'config', 'item'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $config = $this->config($request, $type, 'create');
        $data = $this->validatedData($request, $type);
        $data = $this->transform($request, $type, $data);
        $config['model']::query()->create($data);

        return redirect()->route('admin.content.index', $type)->with('success', $config['label'].' item created successfully.');
    }

    public function edit(Request $request, string $type, int $id): View
    {
        $config = $this->config($request, $type, 'update');
        $item = $config['model']::query()->findOrFail($id);

        return view('admin.content.form', compact('type', 'config', 'item'));
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $config = $this->config($request, $type, 'update');
        $item = $config['model']::query()->findOrFail($id);
        $data = $this->validatedData($request, $type, $item);
        $data = $this->transform($request, $type, $data, $item);
        $item->update($data);

        return redirect()->route('admin.content.index', $type)->with('success', $config['label'].' item updated successfully.');
    }

    public function destroy(Request $request, string $type, int $id): RedirectResponse
    {
        $config = $this->config($request, $type, 'delete');
        $item = $config['model']::query()->findOrFail($id);

        if ($type === 'projects') {
            foreach ($item->image_paths as $imagePath) {
                $this->deleteContentImage($imagePath);
            }
        } elseif ($type === 'posts' && $item->image) {
            $this->deleteContentImage($item->image);
        }

        $item->delete();

        return back()->with('success', $config['label'].' item deleted successfully.');
    }

    private function config(Request $request, string $type, string $action): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $config = self::TYPES[$type];
        abort_unless($request->user()->can($action.' '.$config['resource']), 403);

        return $config;
    }

    private function validatedData(Request $request, string $type, ?Model $item = null): array
    {
        $id = $item?->getKey();

        if (in_array($type, ['projects', 'posts', 'services'], true) && blank($request->input('slug'))) {
            $request->merge(['slug' => Str::slug((string) $request->input('title'))]);
        }

        $rules = match ($type) {
            'projects' => [
                'title' => ['required', 'string', 'max:160'],
                'slug' => ['nullable', 'alpha_dash', 'max:170', Rule::unique('projects', 'slug')->ignore($id)],
                'project_type' => ['required', 'string', Rule::in([Project::TYPE_FULL_DEVELOPMENT, Project::TYPE_MODIFICATION_ENHANCEMENT])],
                'excerpt' => ['required', 'string', 'max:500'],
                'description' => ['required', 'string'],
                'technologies' => ['nullable', 'string', 'max:500'],
                'project_url' => ['nullable', 'url', 'max:255'],
                'github_url' => ['nullable', 'url', 'max:255'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
                'project_images' => ['nullable', 'array', 'max:20'],
                'project_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
                'delete_images' => ['nullable', 'array'],
                'delete_images.*' => ['string', 'max:500'],
                'is_featured' => ['nullable', 'boolean'],
                'is_published' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            ],
            'posts' => [
                'title' => ['required', 'string', 'max:160'],
                'slug' => ['nullable', 'alpha_dash', 'max:170', Rule::unique('posts', 'slug')->ignore($id)],
                'excerpt' => ['required', 'string', 'max:500'],
                'content' => ['required', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
                'meta_title' => ['nullable', 'string', 'max:70'],
                'meta_description' => ['nullable', 'string', 'max:170'],
                'published_at' => ['nullable', 'date'],
                'is_published' => ['nullable', 'boolean'],
            ],
            'services' => [
                'title' => ['required', 'string', 'max:160'],
                'slug' => ['nullable', 'alpha_dash', 'max:170', Rule::unique('services', 'slug')->ignore($id)],
                'short_description' => ['required', 'string', 'max:800'],
                'icon' => ['required', 'regex:/^bi-[a-z0-9-]+$/', 'max:80'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
                'is_active' => ['nullable', 'boolean'],
            ],
            'experiences' => [
                'company' => ['required', 'string', 'max:160'],
                'position' => ['required', 'string', 'max:160'],
                'location' => ['nullable', 'string', 'max:160'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'is_current' => ['nullable', 'boolean'],
                'summary' => ['required', 'string', 'max:1500'],
                'bullets' => ['nullable', 'string', 'max:4000'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            ],
            'educations' => [
                'institution' => ['required', 'string', 'max:180'],
                'degree' => ['required', 'string', 'max:120'],
                'field' => ['nullable', 'string', 'max:120'],
                'start_year' => ['required', 'integer', 'min:1980', 'max:2100'],
                'end_year' => ['required', 'integer', 'gte:start_year', 'max:2100'],
                'description' => ['nullable', 'string', 'max:1200'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            ],
            'skills' => [
                'name' => ['required', 'string', 'max:100'],
                'category' => ['required', 'string', 'max:100'],
                'proficiency' => ['required', 'integer', 'min:1', 'max:100'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
                'is_active' => ['nullable', 'boolean'],
            ],
        };

        return $request->validate($rules);
    }

    private function transform(Request $request, string $type, array $data, ?Model $item = null): array
    {
        if (in_array($type, ['projects', 'posts', 'services'], true)) {
            $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        }

        $checkboxes = match ($type) {
            'projects' => ['is_featured', 'is_published'],
            'posts' => ['is_published'],
            'services', 'skills' => ['is_active'],
            'experiences' => ['is_current'],
            default => [],
        };

        foreach ($checkboxes as $boolean) {
            $data[$boolean] = $request->boolean($boolean);
        }

        if ($type === 'projects') {
            $data['project_type'] = $data['project_type'] ?? ($item?->project_type ?? Project::TYPE_FULL_DEVELOPMENT);
            $data['technologies'] = array_values(array_filter(array_map('trim', explode(',', $data['technologies'] ?? ''))));
        }

        if ($type === 'experiences') {
            $data['bullets'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['bullets'] ?? ''))));
            if ($request->boolean('is_current')) {
                $data['end_date'] = null;
            }
        }

        if ($type === 'projects') {
            $existingImages = $item instanceof Project ? $item->image_paths : [];
            $deletedImages = array_values(array_intersect($data['delete_images'] ?? [], $existingImages));

            foreach ($deletedImages as $deletedImage) {
                $this->deleteContentImage($deletedImage);
            }

            $remainingImages = array_values(array_diff($existingImages, $deletedImages));
            $newImages = [];

            if ($request->hasFile('image')) {
                $newImages[] = $this->storeContentImage($request, $type);
            }

            foreach ($request->file('project_images', []) as $file) {
                $newImages[] = $this->storeContentImageFile($file, $type);
            }

            $projectImages = array_values(array_unique(array_merge($remainingImages, $newImages)));
            $data['image'] = $projectImages[0] ?? null;
            $data['images'] = $projectImages;
            unset($data['delete_images'], $data['project_images']);

            return $data;
        }

        if ($request->hasFile('image')) {
            $this->deleteContentImage($item?->image);
            $data['image'] = in_array($type, ['projects', 'posts'], true)
                ? $this->storeContentImage($request, $type)
                : $request->file('image')->store($type, 'public');
        } else {
            unset($data['image']);
        }

        return $data;
    }

    private function storeContentImage(Request $request, string $type): string
    {
        return $this->storeContentImageFile($request->file('image'), $type);
    }

    private function storeContentImageFile(\Illuminate\Http\UploadedFile $file, string $type): string
    {
        $folder = $type === 'posts' ? 'blogs' : $type;
        $prefix = $type === 'posts' ? 'blog' : Str::singular($type);
        $directory = public_path('assets/images/'.$folder);
        File::ensureDirectoryExists($directory);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions, true)) {
            $extension = 'jpg';
        }

        $filename = $prefix.'-'.now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
        $file->move($directory, $filename);

        return 'assets/images/'.$folder.'/'.$filename;
    }

    private function deleteContentImage(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        if (Str::startsWith($path, 'assets/')) {
            $fullPath = public_path($path);

            if (File::isFile($fullPath)) {
                File::delete($fullPath);
            }

            return;
        }

        if (Str::startsWith($path, 'storage/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));

            return;
        }

        Storage::disk('public')->delete($path);
    }
}
