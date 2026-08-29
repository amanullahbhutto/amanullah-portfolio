{{-- iOS Safari PWA Installation Instructions Modal --}}
<div class="modal fade finance-modal" id="pwaIosInstallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid #142845; border-radius: 24px; box-shadow: 0 25px 60px rgba(0,0,0,0.85);">
            <div class="modal-header border-secondary border-opacity-25 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(56, 189, 248, 0.15); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
                        <i class="bi bi-apple fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white fs-6 fw-bold">Install on iPhone / iPad</h5>
                        <p class="text-muted-custom small mb-0" style="font-size: 0.76rem;">Add to your Home Screen in 3 easy steps</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-3">
                    {{-- Step 1 --}}
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                        <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">1</div>
                        <div>
                            <strong class="d-block text-white small mb-1">Tap the Share Button</strong>
                            <p class="text-muted-custom small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                Safari browser ke bottom toolbar par mojood <strong>Share</strong> icon (<i class="bi bi-box-arrow-up text-info"></i>) par tap karein.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                        <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">2</div>
                        <div>
                            <strong class="d-block text-white small mb-1">Select "Add to Home Screen"</strong>
                            <p class="text-muted-custom small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                List ko scroll karke <strong>Add to Home Screen</strong> (<i class="bi bi-plus-square text-info"></i>) option par tap karein.
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                        <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">3</div>
                        <div>
                            <strong class="d-block text-white small mb-1">Tap "Add"</strong>
                            <p class="text-muted-custom small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                Top right corner par <strong>Add</strong> button par tap karein. App aapke Home Screen par save ho jayegi!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 pt-2">
                <button type="button" class="btn btn-accent btn-sm w-100" data-bs-dismiss="modal">
                    <i class="bi bi-check2 me-1"></i>Understood
                </button>
            </div>
        </div>
    </div>
</div>

