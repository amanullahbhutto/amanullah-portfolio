{{-- Tasbeeh Description & Complete Details Modal --}}
<div class="modal fade finance-modal tasbeeh-desc-modal" id="tasbeehDescModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="desc-modal-icon-badge">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title mb-0" id="descModalTitle">Tasbeeh Details</h5>
                            <span class="desc-modal-seq-badge font-monospace" id="descModalSeqBadge">#1</span>
                            <span class="badge rounded-pill px-2 py-0.5" id="descModalStatusBadge" style="font-size: 0.72rem;">Active</span>
                        </div>
                        <small class="desc-modal-subtitle">Complete Information, Progress & Description</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                {{-- Key Stats Overview (4 Modern Stat Cards) --}}
                <div class="row g-2 g-md-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="desc-stat-card stat-target">
                            <div class="desc-stat-label">
                                <i class="bi bi-bullseye me-1"></i>Daily Target
                            </div>
                            <div class="desc-stat-value text-emerald font-monospace" id="descModalTarget">100</div>
                            <div class="desc-stat-subtext">Per Day Goal</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="desc-stat-card stat-today">
                            <div class="desc-stat-label">
                                <i class="bi bi-calendar-check me-1"></i>Today Done
                            </div>
                            <div class="desc-stat-value text-cyan font-monospace" id="descModalTodayCompleted">0</div>
                            <div class="desc-stat-subtext" id="descModalTodaySubtext">Today Count</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="desc-stat-card stat-total">
                            <div class="desc-stat-label">
                                <i class="bi bi-layers me-1"></i>Cycle Total
                            </div>
                            <div class="desc-stat-value text-violet font-monospace" id="descModalTotalCompleted">0</div>
                            <div class="desc-stat-subtext" id="descModalTotalRequiredSubtext">of <span id="descModalTotalRequired">0</span> required</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="desc-stat-card stat-progress">
                            <div class="desc-stat-label">
                                <i class="bi bi-graph-up-arrow me-1"></i>Progress
                            </div>
                            <div class="desc-stat-value text-amber font-monospace" id="descModalCyclePercentage">0%</div>
                            <div class="progress mt-1 mx-auto" style="height: 5px; width: 85%; background: rgba(148, 163, 184, 0.2); border-radius: 4px;">
                                <div class="progress-bar bg-info" id="descModalProgressBar" style="width: 0%; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tracking Journey & Source Info Strip --}}
                <div class="desc-journey-strip d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="bi bi-calendar2-check text-cyan me-1"></i>Started: <strong id="descModalStarted">—</strong> (<span id="descModalActiveDays">1</span> active days)</span>
                        <span class="opacity-30">•</span>
                        <span><i class="bi bi-clock-history text-violet me-1"></i>Last Zikr: <strong id="descModalLastZikr">—</strong></span>
                    </div>
                    <div id="descModalRefRow">
                        <i class="bi bi-bookmark-star-fill text-amber me-1"></i>Reference: <strong id="descModalRef">—</strong>
                    </div>
                </div>

                {{-- Arabic & Urdu Ornate Preview Box --}}
                <div class="desc-arabic-box mb-3">
                    <div class="desc-arabic-text mb-2 text-center" id="descModalArabic"></div>
                    <div class="islamic-divider my-2"><div class="divider-icon">✦ ✧ ✦</div></div>
                    <div class="desc-urdu-text text-center" id="descModalUrdu"></div>
                </div>

                {{-- Full Description / Fazilat Content Box --}}
                <div>
                    <div class="desc-section-badge">
                        <i class="bi bi-file-earmark-richtext me-1"></i> Description / تفصيل و فضيلت
                    </div>
                    <div class="desc-body-box">
                        <p id="descModalBodyText" class="desc-body-text"></p>
                        <div id="descModalEmptyText" class="text-center py-3 text-muted-custom d-none">
                            <i class="bi bi-chat-square-quote fs-3 opacity-40 d-block mb-1"></i>
                            <span class="fst-italic small">No detailed description added yet for this Tasbeeh.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="#" class="btn btn-sm btn-outline-info px-3" id="descModalCounterBtn">
                    <i class="bi bi-speedometer2 me-1"></i>Open Live Counter
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-theme btn-sm px-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-accent btn-sm px-3 d-none" id="descModalEditBtn" data-bs-toggle="modal" data-bs-target="#editTasbeehModal">
                        <i class="bi bi-pencil-square me-1"></i>Edit Tasbeeh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
