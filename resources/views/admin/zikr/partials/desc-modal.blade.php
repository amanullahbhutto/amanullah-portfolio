{{-- Tasbeeh Description & Complete Details Modal --}}
<div class="modal fade finance-modal" id="tasbeehDescModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <div class="modal-header border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background: rgba(168, 85, 247, 0.15); color: #c084fc; font-size: 1.25rem; border: 1px solid rgba(168, 85, 247, 0.35);">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title text-white mb-0" id="descModalTitle">Tasbeeh Details</h5>
                            <span class="badge rounded-pill bg-secondary bg-opacity-25 text-white font-monospace px-2 py-0.5" id="descModalSeqBadge" style="font-size: 0.7rem;">#1</span>
                            <span class="badge rounded-pill px-2 py-0.5" id="descModalStatusBadge" style="font-size: 0.7rem;">Active</span>
                        </div>
                        <small class="text-muted-custom" style="font-size: 0.75rem;">Complete Information & Description</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                {{-- Key Stats Overview (4 Cards) --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-2 p-md-3 rounded-3 h-100 text-center" style="background: #060d18; border: 1px solid #142842;">
                            <small class="text-muted-custom d-block" style="font-size: 0.72rem;">Daily Target</small>
                            <strong class="text-white font-monospace fs-6" id="descModalTarget">100</strong>
                            <small class="text-muted-custom d-block" style="font-size: 0.68rem;">per day</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 p-md-3 rounded-3 h-100 text-center" style="background: #060d18; border: 1px solid #142842;">
                            <small class="text-muted-custom d-block" style="font-size: 0.72rem;">Today Done</small>
                            <strong class="font-monospace fs-6 text-info" id="descModalTodayCompleted">0</strong>
                            <small class="text-muted-custom d-block" id="descModalTodaySubtext" style="font-size: 0.68rem;">Today Count</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 p-md-3 rounded-3 h-100 text-center" style="background: #060d18; border: 1px solid #142842;">
                            <small class="text-muted-custom d-block" style="font-size: 0.72rem;">Cycle Completed</small>
                            <strong class="font-monospace fs-6 text-white" id="descModalTotalCompleted">0</strong>
                            <small class="text-muted-custom d-block" id="descModalTotalRequiredSubtext" style="font-size: 0.68rem;">of <span id="descModalTotalRequired">0</span></small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 p-md-3 rounded-3 h-100 text-center" style="background: #060d18; border: 1px solid #142842;">
                            <small class="text-muted-custom d-block" style="font-size: 0.72rem;">Cycle Progress</small>
                            <strong class="font-monospace fs-6 text-accent" id="descModalCyclePercentage">0%</strong>
                            <div class="progress mt-1 mx-auto" style="height: 5px; width: 85%; background: rgba(255,255,255,0.08); border-radius: 4px;">
                                <div class="progress-bar bg-info" id="descModalProgressBar" style="width: 0%; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tracking Journey & Source Info Strip --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 px-3 mb-3 rounded-3 small" style="background: rgba(6, 182, 212, 0.06); border: 1px solid rgba(6, 182, 212, 0.2);">
                    <div class="d-flex align-items-center gap-2 flex-wrap text-muted-custom" style="font-size: 0.78rem;">
                        <span><i class="bi bi-calendar2-check text-info me-1"></i>Started: <strong class="text-white" id="descModalStarted">—</strong> (<span id="descModalActiveDays">1</span> days)</span>
                        <span class="opacity-40">•</span>
                        <span><i class="bi bi-clock-history text-secondary me-1"></i>Last Zikr: <strong class="text-white" id="descModalLastZikr">—</strong></span>
                    </div>
                    <div id="descModalRefRow" class="text-muted-custom" style="font-size: 0.78rem;">
                        <i class="bi bi-bookmark-check text-warning me-1"></i>Reference: <strong class="text-white" id="descModalRef">—</strong>
                    </div>
                </div>

                {{-- Arabic & Urdu Preview Box --}}
                <div class="p-3 mb-3 rounded-3" style="background: #060c16; border: 1px solid #142842;">
                    <div class="arabic-text mb-2 text-center" id="descModalArabic" style="font-size: 1.45rem;"></div>
                    <div class="islamic-divider my-2"><div class="divider-icon">✦ ✧ ✦</div></div>
                    <div class="urdu-text text-center" id="descModalUrdu" style="font-size: 1.1rem;"></div>
                </div>

                {{-- Full Description Content Box --}}
                <div>
                    <label class="form-label small text-uppercase text-muted-custom fw-bold mb-2" style="letter-spacing: 0.5px; font-size: 0.74rem;">
                        <i class="bi bi-file-earmark-text text-info me-1"></i> Description / تفصيل و فضيلت
                    </label>
                    <div class="p-3 rounded-3" style="background: #0a1424; border: 1px solid rgba(168, 85, 247, 0.3); min-height: 85px;">
                        <p id="descModalBodyText" class="mb-0 text-white" style="line-height: 1.85; white-space: pre-wrap; font-size: 0.95rem;"></p>
                        <div id="descModalEmptyText" class="text-center py-3 text-muted-custom d-none">
                            <i class="bi bi-chat-square-text fs-3 opacity-50 d-block mb-1"></i>
                            <span class="fst-italic">No description added yet for this Tasbeeh.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 d-flex justify-content-between">
                <a href="#" class="btn btn-sm btn-outline-info" id="descModalCounterBtn">
                    <i class="bi bi-speedometer2 me-1"></i>Open Counter
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-theme btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-accent btn-sm d-none" id="descModalEditBtn" data-bs-toggle="modal" data-bs-target="#editTasbeehModal">
                        <i class="bi bi-pencil me-1"></i>Edit Tasbeeh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
