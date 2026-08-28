<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NamazSetting;
use App\Services\NamazAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NamazSettingController extends Controller
{
    public function __construct(protected NamazAttendanceService $namazService)
    {
        $this->middleware('permission:view namaz settings|namaz_settings.view', ['only' => ['index']]);
        $this->middleware('permission:update namaz settings|namaz_settings.update', ['only' => ['update']]);
    }

    public function index(): View
    {
        $settings = $this->namazService->getSettings();
        $timezone = $this->namazService->getTimezone();
        $currentTime = $this->namazService->now()->format('h:i A (d M Y)');

        return view('admin.namaz.settings.index', [
            'settings' => $settings,
            'timezone' => $timezone,
            'currentTime' => $currentTime,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fajr_time' => ['required', 'string', 'max:10'],
            'zuhr_time' => ['required', 'string', 'max:10'],
            'asr_time' => ['required', 'string', 'max:10'],
            'maghrib_time' => ['required', 'string', 'max:10'],
            'isha_time' => ['required', 'string', 'max:10'],
            'jummah_time' => ['required', 'string', 'max:10'],
        ]);

        $settings = $this->namazService->getSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Namaz prayer timings updated successfully.',
            'settings' => $settings,
        ]);
    }
}

