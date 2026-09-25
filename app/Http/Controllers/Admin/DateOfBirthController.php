<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DateOfBirth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DateOfBirthController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            'permission:view date of birth',
            ['only' => ['index', 'show']]
        );

        $this->middleware(
            'permission:create date of birth',
            ['only' => ['create', 'store']]
        );

        $this->middleware(
            'permission:update date of birth',
            ['only' => ['edit', 'update', 'updatePhotos']]
        );

        $this->middleware(
            'permission:delete date of birth',
            ['only' => ['destroy']]
        );
    }

    /**
     * Display all records.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $fatherName = trim($request->string('father_name')->toString());
        $perPage = $this->perPage($request);
        $fatherNames = DateOfBirth::query()
            ->whereNotNull('father_name')
            ->where('father_name', '<>', '')
            ->distinct()
            ->orderBy('father_name')
            ->pluck('father_name');

        $records = DateOfBirth::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('start_date', 'like', "%{$search}%")
                        ->orWhere('end_date', 'like', "%{$search}%");
                });
            })
            ->when($fatherName !== '', function ($query) use ($fatherName): void {
                $query->where('father_name', $fatherName);
            })
            ->get()
            ->sortBy(fn (DateOfBirth $dateOfBirth): string => sprintf(
                '%04d-%s',
                $dateOfBirth->days_until_next_birthday,
                mb_strtolower($dateOfBirth->name)
            ))
            ->values();

        $dateOfBirths = $this->paginateRecords($request, $records, $perPage);

        return view('admin.date-of-births.index', [
            'dateOfBirths' => $dateOfBirths,
            'fatherNames' => $fatherNames,
            'selectedFatherName' => $fatherName,
            'selectedPerPage' => $perPage,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('admin.date-of-births.create');
    }

    /**
     * Save new record.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $this->validatedData($request);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $folder = $this->getPersonFolder($validated['name']);
            foreach ($request->file('images', []) as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadedImages[] = $this->storeDobImageFile($file, $folder);
                }
            }
        }

        $dateOfBirth = DateOfBirth::query()->create([
            'name' => $validated['name'],
            'father_name' => $validated['father_name'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'images' => $uploadedImages,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Date of birth record created successfully.',
                'record' => $this->recordPayload($dateOfBirth),
            ], 201);
        }

        return redirect()
            ->route('admin.date-of-births.index')
            ->with('success', 'Date of birth record created successfully.');
    }

    /**
     * Display single record.
     */
    public function show(DateOfBirth $dateOfBirth): View
    {
        return view('admin.date-of-births.show', [
            'dateOfBirth' => $dateOfBirth,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(DateOfBirth $dateOfBirth): View
    {
        return view('admin.date-of-births.edit', [
            'dateOfBirth' => $dateOfBirth,
        ]);
    }

    /**
     * Update record.
     */
    public function update(
        Request $request,
        DateOfBirth $dateOfBirth
    ): RedirectResponse|JsonResponse {
        $validated = $this->validatedData($request);

        $existingImages = $dateOfBirth->image_paths;
        $deleteImages = array_values(array_intersect($validated['delete_images'] ?? [], $existingImages));

        foreach ($deleteImages as $deletedImage) {
            $this->deleteDobImageFile($deletedImage);
        }

        $remainingImages = array_values(array_diff($existingImages, $deleteImages));

        $newImages = [];
        if ($request->hasFile('images')) {
            $folder = $this->getPersonFolder($validated['name'], $dateOfBirth->id);
            foreach ($request->file('images', []) as $file) {
                if ($file instanceof UploadedFile) {
                    $newImages[] = $this->storeDobImageFile($file, $folder);
                }
            }
        }

        $allImages = array_values(array_unique(array_merge($remainingImages, $newImages)));

        $dateOfBirth->update([
            'name' => $validated['name'],
            'father_name' => $validated['father_name'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'images' => $allImages,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Date of birth record updated successfully.',
                'record' => $this->recordPayload($dateOfBirth->fresh()),
            ]);
        }

        return redirect()
            ->route('admin.date-of-births.index')
            ->with('success', 'Date of birth record updated successfully.');
    }

    /**
     * Delete record.
     */
    public function destroy(Request $request, DateOfBirth $dateOfBirth): RedirectResponse|JsonResponse
    {
        foreach ($dateOfBirth->image_paths as $imagePath) {
            $this->deleteDobImageFile($imagePath);
        }

        $folder = $this->getPersonFolder($dateOfBirth->name, $dateOfBirth->id);
        $directory = public_path('DOB/'.$folder);
        if (File::isDirectory($directory) && count(File::files($directory)) === 0) {
            File::deleteDirectory($directory);
        }

        $dateOfBirth->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Date of birth record deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.date-of-births.index')
            ->with('success', 'Date of birth record deleted successfully.');
    }

    /**
     * Update photos for a specific date of birth record.
     */
    public function updatePhotos(
        Request $request,
        DateOfBirth $dateOfBirth
    ): RedirectResponse|JsonResponse {
        $validated = $request->validate([
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['string', 'max:500'],
        ]);

        $existingImages = $dateOfBirth->image_paths;
        $deletedImages = array_values(array_intersect($validated['delete_images'] ?? [], $existingImages));

        foreach ($deletedImages as $deletedImage) {
            $this->deleteDobImageFile($deletedImage);
        }

        $remainingImages = array_values(array_diff($existingImages, $deletedImages));

        $newImages = [];
        if ($request->hasFile('images')) {
            $folder = $this->getPersonFolder($dateOfBirth->name, $dateOfBirth->id);
            foreach ($request->file('images', []) as $file) {
                if ($file instanceof UploadedFile) {
                    $newImages[] = $this->storeDobImageFile($file, $folder);
                }
            }
        }

        $allImages = array_values(array_unique(array_merge($remainingImages, $newImages)));

        $dateOfBirth->update([
            'images' => $allImages,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Photos updated successfully.',
                'record' => $this->recordPayload($dateOfBirth->fresh()),
            ]);
        }

        return redirect()
            ->route('admin.date-of-births.index')
            ->with('success', 'Photos updated successfully.');
    }

    private function getPersonFolder(string $name, ?int $id = null): string
    {
        $folder = trim(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name), '_');
        if ($folder === '') {
            $folder = $id ? 'dob_'.$id : 'person';
        }

        return $folder;
    }

    private function storeDobImageFile(UploadedFile $file, string $folder): string
    {
        $directory = public_path('DOB/'.$folder);
        File::ensureDirectoryExists($directory);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            $extension = 'jpg';
        }

        $filename = 'img-'.now()->format('YmdHis').'-'.Str::random(8).'.'.$extension;
        $file->move($directory, $filename);

        return 'DOB/'.$folder.'/'.$filename;
    }

    private function deleteDobImageFile(?string $path): void
    {
        if (
            blank($path)
            || ! Str::startsWith($path, 'DOB/')
            || str_contains($path, '..')
            || str_contains($path, "\0")
        ) {
            return;
        }

        $baseDir = realpath(public_path('DOB'));
        $fullPath = public_path($path);

        if ($baseDir && File::exists($fullPath)) {
            $realPath = realpath($fullPath);
            if ($realPath && Str::startsWith($realPath, $baseDir)) {
                File::delete($realPath);
            }
        }
    }

    private function validatedData(Request $request): array
    {
        $this->normalizeDateInputs($request);

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['string', 'max:500'],
        ]);
    }

    private function normalizeDateInputs(Request $request): void
    {
        foreach (['start_date', 'end_date'] as $field) {
            $value = trim((string) $request->input($field));

            if ($value === '') {
                continue;
            }

            $normalizedDate = $this->normalizeDateValue($value);

            if ($normalizedDate !== null) {
                $request->merge([$field => $normalizedDate]);
            }
        }
    }

    private function normalizeDateValue(string $value): ?string
    {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            return checkdate($month, $day, $year)
                ? Carbon::create($year, $month, $day)->toDateString()
                : null;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            return checkdate($month, $day, $year)
                ? Carbon::create($year, $month, $day)->toDateString()
                : null;
        }

        return null;
    }

    private function paginateRecords(Request $request, $records, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 50);

        return in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 50;
    }

    private function recordPayload(DateOfBirth $dateOfBirth): array
    {
        return [
            'id' => $dateOfBirth->id,
            'name' => $dateOfBirth->name,
            'father_name' => $dateOfBirth->father_name,
            'start_date' => $dateOfBirth->start_date?->format('j/n/Y'),
            'end_date' => $dateOfBirth->end_date?->format('j/n/Y'),
            'age' => $dateOfBirth->formatted_age,
            'next_birthday' => $dateOfBirth->next_birthday->format('M d, Y'),
            'next_birthday_countdown' => $dateOfBirth->formatted_next_birthday_countdown,
            'primary_image_url' => $dateOfBirth->image_url,
            'image_urls' => $dateOfBirth->image_urls,
            'images' => $dateOfBirth->images_with_urls,
        ];
    }
}
