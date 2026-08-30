<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view profile', ['only' => ['edit']]);
        $this->middleware('permission:update profile', ['only' => ['update', 'updateExperienceSource']]);
    }

    public function edit(): View
    {
        return view('admin.profile.edit', ['profile' => Profile::query()->firstOrFail()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = Profile::query()->firstOrFail();
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'short_bio' => ['required', 'string', 'max:700'],
            'long_bio' => ['required', 'string', 'max:5000'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'availability' => ['required', 'string', 'max:120'],
            'use_experience_dates' => ['nullable', 'boolean'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:50'],
            'project_count' => ['required', 'integer', 'min:0', 'max:9999'],
            'happy_clients' => ['required', 'integer', 'min:0', 'max:9999'],
            'languages' => ['nullable', 'string', 'max:300'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $data['phone_secondary'] = null;
        $data['languages'] = array_values(array_filter(array_map('trim', explode(',', $data['languages'] ?? ''))));
        if ($request->has('use_experience_dates')) {
            $data['use_experience_dates'] = $request->boolean('use_experience_dates');
        } else {
            unset($data['use_experience_dates']);
        }

        foreach (['profile_image', 'cv_file'] as $field) {
            if ($request->hasFile($field)) {
                $this->deletePublicProfileFile($profile->{$field});
                $data[$field] = $this->storePublicProfileFile($request, $field);
            } else {
                unset($data[$field]);
            }
        }

        $profile->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateExperienceSource(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'use_experience_dates' => ['required', 'boolean'],
        ]);

        Profile::query()->firstOrFail()->update([
            'use_experience_dates' => (bool) $data['use_experience_dates'],
        ]);

        return back()->with('success', 'Experience homepage calculation turned '.((bool) $data['use_experience_dates'] ? 'on' : 'off').'.');
    }

    private function storePublicProfileFile(Request $request, string $field): string
    {
        $directory = public_path('assets/images');
        File::ensureDirectoryExists($directory);

        $file = $request->file($field);
        $allowedExtensions = $field === 'cv_file' ? ['pdf'] : ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions, true)) {
            $extension = $field === 'cv_file' ? 'pdf' : 'jpg';
        }

        $filename = $field.'-'.now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
        $file->move($directory, $filename);

        return 'assets/images/'.$filename;
    }

    private function deletePublicProfileFile(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if (Str::startsWith($path, 'assets/images/')) {
            $fullPath = public_path($path);

            if (File::isFile($fullPath)) {
                File::delete($fullPath);
            }

            return;
        }

        if (! Str::startsWith($path, ['/', 'storage/'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
