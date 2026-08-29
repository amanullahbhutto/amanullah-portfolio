<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());

        $cities = City::withCount('contributors')
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('state', 'like', "%{$q}%")->orWhere('country', 'like', "%{$q}%"))
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($cities);
        }

        return view('admin.cities.index', compact('cities', 'status'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);
        $city = City::create($validated);

        return response()->json([
            'message' => 'City added successfully.',
            'city' => [
                'id' => $city->id,
                'name' => $city->name,
                'status' => $city->status,
            ],
        ], 201);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $validated = $this->validated($request, $city->id);
        $city->update($validated);

        return response()->json([
            'message' => 'City updated successfully.',
            'city' => [
                'id' => $city->id,
                'name' => $city->name,
                'status' => $city->status,
            ],
        ]);
    }

    public function destroy(City $city): JsonResponse
    {
        if ($city->contributors()->exists()) {
            throw ValidationException::withMessages([
                'city' => 'This city is linked to existing contributors/income records and cannot be deleted. You can mark it inactive instead.',
            ]);
        }

        $city->delete();

        return response()->json(['message' => 'City deleted successfully.']);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('cities', 'name')->ignore($id)],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}

