{{-- Universal PWA Installation Modal (Android, iPhone & Desktop) --}}
<div class="modal fade finance-modal" id="pwaInstallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid #142845; border-radius: 24px; box-shadow: 0 25px 60px rgba(0,0,0,0.85);">
            <div class="modal-header border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $pwaSettings?->icon_192_url ?? asset('assets/pwa-icons/icon-192x192.png') }}" alt="App Icon" style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.4); padding: 2px;">
                    <div>
                        <h5 class="modal-title mb-0 text-white fs-6 fw-bold">{{ $pwaSettings?->app_name ?? 'Amanullah App' }}</h5>
                        <p class="text-secondary small mb-0" style="font-size: 0.76rem;">Install Progressive Web Application</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Platform Tabs --}}
                <ul class="nav nav-pills nav-fill mb-3 gap-2" id="pwaInstallTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-2 px-3 small rounded-3 fw-semibold text-white" id="pwa-android-tab" data-bs-toggle="pill" data-bs-target="#pwa-android-pane" type="button" role="tab" style="background: #0c1626; border: 1px solid #18283e;">
                            <i class="bi bi-android2 text-success me-1"></i>Android / PC
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2 px-3 small rounded-3 fw-semibold text-white" id="pwa-ios-tab" data-bs-toggle="pill" data-bs-target="#pwa-ios-pane" type="button" role="tab" style="background: #0c1626; border: 1px solid #18283e;">
                            <i class="bi bi-apple text-info me-1"></i>iPhone / iPad
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pwaInstallTabContent">
                    {{-- Android & PC Chrome/Edge Pane --}}
                    <div class="tab-pane fade show active" id="pwa-android-pane" role="tabpanel">
                        <div class="text-center p-3 rounded-3 mb-3" style="background: rgba(16, 185, 129, 0.08); border: 1px dashed rgba(16, 185, 129, 0.35);">
                            <button type="button" class="btn btn-accent btn-sm px-4 py-2 fw-bold" id="pwaModalDirectInstallBtn">
                                <i class="bi bi-download me-1"></i>Direct 1-Click Install
                            </button>
                            <small class="d-block text-secondary mt-2" style="font-size: 0.76rem;">Click above to install instantly on your home screen or desktop.</small>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: #0c1626; border: 1px solid #18283e;">
                                <span class="badge bg-secondary rounded-pill">1</span>
                                <small class="text-light">Chrome / Edge browser ke top right par <strong>3 Dots (⋮)</strong> menu dabayein.</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: #0c1626; border: 1px solid #18283e;">
                                <span class="badge bg-secondary rounded-pill">2</span>
                                <small class="text-light">Menu mein <strong>"Install app"</strong> ya <strong>"Add to Home screen"</strong> par tap karein.</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: #0c1626; border: 1px solid #18283e;">
                                <span class="badge bg-secondary rounded-pill">3</span>
                                <small class="text-light">Popup aane par <strong>Install</strong> confirm karein.</small>
                            </div>
                        </div>
                    </div>

                    {{-- iOS Safari Pane --}}
                    <div class="tab-pane fade" id="pwa-ios-pane" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                                <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 0.8rem;">1</div>
                                <div>
                                    <strong class="d-block text-white small mb-1">Tap the Share Button</strong>
                                    <p class="text-secondary small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                                        Safari browser ke bottom toolbar par mojood <strong>Share</strong> icon (<i class="bi bi-box-arrow-up text-info"></i>) par tap karein.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                                <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 0.8rem;">2</div>
                                <div>
                                    <strong class="d-block text-white small mb-1">Select "Add to Home Screen"</strong>
                                    <p class="text-secondary small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                                        List ko scroll karke <strong>Add to Home Screen</strong> (<i class="bi bi-plus-square text-info"></i>) option par tap karein.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #0c1626; border: 1px solid #18283e;">
                                <div class="badge bg-info text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 0.8rem;">3</div>
                                <div>
                                    <strong class="d-block text-white small mb-1">Tap "Add"</strong>
                                    <p class="text-secondary small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                                        Top right corner par <strong>Add</strong> button par tap karein. App Home Screen par save ho jayegi!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 pt-2">
                <button type="button" class="btn btn-secondary btn-sm w-100" data-bs-dismiss="modal">
                    <i class="bi bi-check2 me-1"></i>Got It
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alias id for backwards compatibility --}}
<div id="pwaIosInstallModal" style="display: none;"></div>
