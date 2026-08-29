@php
    $user = auth()->user();
    $savedArabicSize = (int) ($user?->zikr_arabic_size ?? 24);
    $savedUrduSize = (int) ($user?->zikr_urdu_size ?? 16);
    $savedShowArabic = $user ? (bool)$user->zikr_show_arabic : true;
    $savedShowUrdu = $user ? (bool)$user->zikr_show_urdu : true;
@endphp

{{-- Compact Zikr Display Settings Modal --}}
<div 
    class="modal fade finance-modal zikr-compact-settings-modal" 
    id="zikrSettingsModal" 
    tabindex="-1" 
    aria-hidden="true"
    data-settings-url="{{ route('admin.zikr.settings.update') }}"
    data-db-arabic-size="{{ $savedArabicSize }}"
    data-db-urdu-size="{{ $savedUrduSize }}"
    data-db-show-arabic="{{ $savedShowArabic ? '1' : '0' }}"
    data-db-show-urdu="{{ $savedShowUrdu ? '1' : '0' }}"
>
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #182e4e; border-radius: 16px; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.8), 0 0 30px rgba(0, 229, 255, 0.12);">
            {{-- Compact Header --}}
            <div class="modal-header py-2 px-3 border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-info fs-6"></i>
                    <h6 class="modal-title mb-0 text-white fw-bold" style="font-size: 0.92rem;">Display Settings</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>

            {{-- Compact Body with 1-Line Controls --}}
            <div class="modal-body p-3">
                {{-- 1. Arabic 1-Line Row --}}
                <div class="d-flex align-items-center justify-content-between gap-2 p-2 px-3 rounded-3 mb-2" style="background: #0c1728; border: 1px solid #162a45;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch mb-0" style="min-height: auto;">
                            <input class="form-check-input mt-0" type="checkbox" role="switch" id="settingShowArabicSwitch" onchange="toggleZikrVisibility('arabic', this.checked)" {{ $savedShowArabic ? 'checked' : '' }} title="Show/Hide Arabic" style="cursor: pointer;">
                        </div>
                        <span class="text-white small fw-bold" style="font-size: 0.85rem;">Arabic</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 d-flex align-items-center justify-content-center" onclick="adjustZikrFontSize('arabic', -2)" title="Decrease Size (-2)" style="height: 28px; width: 28px; border-radius: 6px;">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <span class="badge font-monospace fw-bold text-center" id="settingArabicSizeVal" style="background: #08111e; border: 1px solid #1a2f4c; color: #f97316; font-size: 0.88rem; min-width: 36px; padding: 4px 6px;">{{ $savedArabicSize }}</span>
                        <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 d-flex align-items-center justify-content-center" onclick="adjustZikrFontSize('arabic', 2)" title="Increase Size (+2)" style="height: 28px; width: 28px; border-radius: 6px;">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 text-muted-custom d-flex align-items-center justify-content-center" onclick="resetZikrFontSize('arabic')" title="Reset (24)" style="height: 28px; width: 26px; border-radius: 6px;">
                            <i class="bi bi-arrow-counterclockwise" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>

                {{-- 2. Urdu 1-Line Row --}}
                <div class="d-flex align-items-center justify-content-between gap-2 p-2 px-3 rounded-3" style="background: #0c1728; border: 1px solid #162a45;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch mb-0" style="min-height: auto;">
                            <input class="form-check-input mt-0" type="checkbox" role="switch" id="settingShowUrduSwitch" onchange="toggleZikrVisibility('urdu', this.checked)" {{ $savedShowUrdu ? 'checked' : '' }} title="Show/Hide Urdu" style="cursor: pointer;">
                        </div>
                        <span class="text-white small fw-bold" style="font-size: 0.85rem;">Urdu</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 d-flex align-items-center justify-content-center" onclick="adjustZikrFontSize('urdu', -1)" title="Decrease Size (-1)" style="height: 28px; width: 28px; border-radius: 6px;">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <span class="badge font-monospace fw-bold text-center" id="settingUrduSizeVal" style="background: #08111e; border: 1px solid #1a2f4c; color: #94a3b8; font-size: 0.88rem; min-width: 36px; padding: 4px 6px;">{{ $savedUrduSize }}</span>
                        <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 d-flex align-items-center justify-content-center" onclick="adjustZikrFontSize('urdu', 1)" title="Increase Size (+1)" style="height: 28px; width: 28px; border-radius: 6px;">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 text-muted-custom d-flex align-items-center justify-content-center" onclick="resetZikrFontSize('urdu')" title="Reset (16)" style="height: 28px; width: 26px; border-radius: 6px;">
                            <i class="bi bi-arrow-counterclockwise" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Compact Footer --}}
            <div class="modal-footer py-2 px-3 border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                <span class="text-muted-custom" style="font-size: 0.75rem;"><i class="bi bi-check2-circle text-success me-1"></i>Auto-saved</span>
                <button type="button" class="btn btn-accent btn-sm py-1 px-3" data-bs-dismiss="modal" style="font-size: 0.8rem; border-radius: 8px;">Done</button>
            </div>
        </div>
    </div>
</div>
