<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DateOfBirth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ['only' => ['edit', 'update']]
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

        $dateOfBirth = DateOfBirth::query()->create([
            'name' => $validated['name'],
            'father_name' => $validated['father_name'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
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

        $dateOfBirth->update([
            'name' => $validated['name'],
            'father_name' => $validated['father_name'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
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

    private function validatedData(Request $request): array
    {
        $this->normalizeDateInputs($request);

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
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
        ];
    }
}
