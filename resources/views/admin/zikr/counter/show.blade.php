@extends('layouts.admin')
@section('title', $tasbeeh->title . ' — Live Counter')
@section('page_title', 'Live Zikr Counter')

@section('content')
<div class="live-counter-page-wrapper">
    {{-- Main Tasbeeh Card with Tap Anywhere Detection --}}
    <div
        class="tasbeeh-card"
        id="tasbeehContainer"
        data-increment-url="{{ route('admin.zikr.counter.increment', $tasbeeh) }}"
        data-user-id="{{ $user->id }}"
        data-total-required="{{ $stats['total_required'] }}"
        data-total-completed="{{ $stats['total_completed'] }}"
        onclick="handleLiveCardTap(event)"
    >
        {{-- Card Top Header Bar --}}
        <div class="card-top-bar">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <a href="{{ route('admin.zikr.index', ['user_id' => $user->id]) }}" class="btn-menu-dots text-decoration-none flex-shrink-0" onclick="event.stopPropagation()" title="Back to Dashboard">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <span class="fw-semibold text-white text-truncate" style="font-size: 0.9rem;">{{ $tasbeeh->title }}</span>
            </div>
            <button class="btn-menu-dots flex-shrink-0" type="button" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#controlsModal" title="Controls & Quick Add">
                <i class="bi bi-three-dots-vertical fs-5"></i>
            </button>
        </div>

        {{-- Dua Section - Arabic & Urdu --}}
        <div class="dua-content-box">
            <div class="arabic-text">
                {{ $tasbeeh->arabic_text }}
            </div>

            <div class="islamic-divider">
                <div class="divider-icon">✦ ✧ ✦</div>
            </div>

            @if($tasbeeh->urdu_meaning)
                <div class="urdu-text">
                    {{ $tasbeeh->urdu_meaning }}
                </div>
            @endif
        </div>

        {{-- Islamic Mehrab Arch with Dynamic Arc Beads & Main Counter --}}
        <div class="mehrab-arch" id="mehrabArchBox">
            <svg class="svg-arc-container" viewBox="0 0 300 150">
                <path d="M 30,135 A 120,120 0 0,1 270,135" fill="none" stroke="rgba(255, 255, 255, 0.12)" stroke-dasharray="3,3" stroke-width="1.5"/>
                <g id="beadsGroup"></g>
            </svg>

            {{-- Main Counter Display Number with #ff6b2c Color --}}
            <div class="counter-number" id="mainCountDisplay">{{ number_format($stats['total_completed']) }}</div>

            {{-- Bottom Stats Row Inside Arch --}}
            <div class="bottom-stats-row">
                <div class="stat-col">
                    <small>Target</small>
                    <strong class="text-white" id="reqVal">{{ number_format($stats['total_required']) }}</strong>
                </div>
                <div class="stat-col">
                    <small>Completed</small>
                    <strong class="text-success" id="completedVal">{{ number_format($stats['total_completed']) }}</strong>
                </div>
                <div class="stat-col">
                    <small id="remainingLabel">{{ $stats['extra'] > 0 ? 'Extra' : 'Remaining' }}</small>
                    <strong class="{{ $stats['extra'] > 0 ? 'text-info' : ($stats['remaining'] > 0 ? 'text-warning' : 'text-success') }}" id="remainingVal">
                        {{ $stats['extra'] > 0 ? '+' . number_format($stats['extra']) : number_format($stats['remaining']) }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Controls & Actions Modal --}}
<div class="modal fade finance-modal" id="controlsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content modal-content-custom p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2 text-warning"></i>
                    <span>Quick Controls</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Quick Presets --}}
            <label class="d-block text-secondary mb-2" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Quick Add Presets</label>
            <div class="preset-grid">
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(33)">+33</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(100)">+100</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(300)">+300</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(1000)">+1,000</button>
            </div>

            {{-- Custom Count Form --}}
            <form id="counterManualForm" method="POST" action="{{ route('admin.zikr.counter.manual', $tasbeeh) }}" class="d-flex gap-2 mb-3">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="number" name="count" id="customInputCount" class="form-control input-dark font-monospace fw-bold" placeholder="Custom (e.g. 50 or -33)" required>
                <button type="submit" class="btn btn-action-add text-nowrap d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </form>

            {{-- Extra Navigation & Management Links --}}
            <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-25">
                <button type="button" class="btn btn-outline-theme btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#zikrSettingsModal">
                    <i class="bi bi-gear-fill me-1 text-info"></i> Display Settings
                </button>
                <button type="button" class="btn btn-outline-theme btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#changeStartDateModal">
                    <i class="bi bi-calendar-event me-1 text-info"></i> Start Date
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#resetTasbeehModal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Cycle
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Change Start Date Modal --}}
<div class="modal fade finance-modal" id="changeStartDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="changeStartDateForm" method="POST" action="{{ route('admin.zikr.counter.start-date', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title mb-0 text-white">Change Tracking Start Date</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted-custom small mb-3">
                        Total required count will be calculated from this date to today inclusive.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-white">Tracking Start Date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="tracking_start_date" value="{{ $stats['tracking_start_date'] }}" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit">
                        <span data-submit-label>Save Start Date</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset Tracking Modal --}}
<div class="modal fade finance-modal" id="resetTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="resetDetailForm" method="POST" action="{{ route('admin.zikr.counter.reset', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header border-secondary border-opacity-25">
                    <div>
                        <h5 class="modal-title text-danger mb-0">Reset Tracking?</h5>
                        <p class="text-muted-custom small mb-0 mt-1">{{ $tasbeeh->title }}</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div class="small">
                            <strong>Note:</strong> Resetting will set your completed count for <strong>this Tasbeeh only</strong> to 0 and restart your tracking cycle from today. Other Tasbeehs will remain completely untouched.
                        </div>
                    </div>
                    <p class="text-muted-custom small mb-0">Are you sure you want to start a new tracking cycle?</p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="submit">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Yes, Reset Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.zikr.partials.settings-modal')

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('tasbeehContainer');
        if (!container) return;

        let totalCompleted = parseInt(container.dataset.totalCompleted, 10) || 0;
        const totalRequired = parseInt(container.dataset.totalRequired, 10) || 0;
        const maxBeads = 33;
        let pendingBatch = 0;
        let batchTimer = null;
        let isSyncing = false;

        const mainCountEl = document.getElementById('mainCountDisplay');
        const completedValEl = document.getElementById('completedVal');
        const reqValEl = document.getElementById('reqVal');
        const remainingValEl = document.getElementById('remainingVal');
        const remainingLabelEl = document.getElementById('remainingLabel');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Render 1 to 33 Dynamic Arc Beads
        function renderBeads(activeCount) {
            const beadsGroup = document.getElementById('beadsGroup');
            if (!beadsGroup) return;
            beadsGroup.innerHTML = '';

            const cx = 150, cy = 135, r = 120;

            for (let i = 1; i <= maxBeads; i++) {
                const angle = Math.PI - (i / maxBeads) * Math.PI;
                const x = cx + r * Math.cos(angle);
                const y = cy - r * Math.sin(angle);

                const isActive = i <= activeCount;

                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', x);
                circle.setAttribute('cy', y);
                circle.setAttribute('r', isActive ? '6' : '3');
                circle.setAttribute('fill', isActive ? '#00e5ff' : 'rgba(255, 255, 255, 0.15)');
                circle.setAttribute('stroke', isActive ? '#ffffff' : 'none');
                circle.setAttribute('stroke-width', '1.5');

                beadsGroup.appendChild(circle);
            }
        }

        // Update Screen Elements
        function updateDisplay() {
            if (totalCompleted < 0) totalCompleted = 0;

            let beadStep = totalCompleted % maxBeads;
            if (totalCompleted > 0 && beadStep === 0) beadStep = maxBeads;

            const formattedNum = totalCompleted.toLocaleString();

            if (mainCountEl) {
                mainCountEl.innerText = formattedNum;

                // Dynamic responsive font sizing based on digit length
                const len = formattedNum.length;
                if (len <= 4) {
                    mainCountEl.style.fontSize = 'clamp(1.85rem, 4.4vh, 2.35rem)';
                } else if (len <= 6) {
                    mainCountEl.style.fontSize = 'clamp(1.5rem, 3.6vh, 1.9rem)';
                } else if (len <= 9) {
                    mainCountEl.style.fontSize = 'clamp(1.2rem, 3.0vh, 1.55rem)';
                } else {
                    mainCountEl.style.fontSize = 'clamp(0.95rem, 2.4vh, 1.25rem)';
                }

                mainCountEl.classList.remove('number-bump');
                void mainCountEl.offsetWidth;
                mainCountEl.classList.add('number-bump');
            }

            if (completedValEl) completedValEl.innerText = totalCompleted.toLocaleString();
            if (reqValEl) reqValEl.innerText = totalRequired.toLocaleString();

            const diff = totalCompleted - totalRequired;
            if (remainingValEl && remainingLabelEl) {
                if (diff > 0) {
                    remainingLabelEl.innerText = 'Extra';
                    remainingValEl.innerText = '+' + diff.toLocaleString();
                    remainingValEl.className = 'text-info';
                } else {
                    const rem = Math.max(0, totalRequired - totalCompleted);
                    remainingLabelEl.innerText = 'Remaining';
                    remainingValEl.innerText = rem.toLocaleString();
                    remainingValEl.className = rem > 0 ? 'text-warning' : 'text-success';
                }
            }

            renderBeads(beadStep);
        }

        // Batch AJAX Sync to backend
        let inFlightBatch = 0;

        function flushBatch() {
            if (pendingBatch === 0 || isSyncing) return;

            isSyncing = true;
            inFlightBatch = pendingBatch;
            pendingBatch = 0;

            const formData = new FormData();
            formData.append('count', inFlightBatch);
            formData.append('user_id', container.dataset.userId);
            formData.append('_token', csrfToken);

            fetch(container.dataset.incrementUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Increment failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (payload.stats) {
                        // Reconcile totalCompleted with any new user taps during in-flight network request
                        totalCompleted = Number(payload.stats.total_completed) + pendingBatch;
                        updateDisplay();
                    }
                    inFlightBatch = 0;
                })
                .catch((err) => {
                    console.error('Increment sync error:', err);
                    pendingBatch += inFlightBatch;
                    inFlightBatch = 0;
                })
                .finally(() => {
                    isSyncing = false;
                    if (pendingBatch > 0) {
                        clearTimeout(batchTimer);
                        batchTimer = setTimeout(flushBatch, 350);
                    }
                });
        }

        // Tap Handler for Card
        window.handleLiveCardTap = function (e) {
            // Ignore clicks on buttons/links/inputs/modals
            if (e.target.closest('button, a, input, select, textarea, .modal')) return;

            totalCompleted += 1;
            pendingBatch += 1;
            updateDisplay();

            clearTimeout(batchTimer);
            batchTimer = setTimeout(flushBatch, 350);
        };

        // Quick Preset amount handler
        window.applyQuickAmount = function (amount) {
            const input = document.getElementById('customInputCount');
            if (input) input.value = amount;
            const form = document.getElementById('counterManualForm');
            if (form) form.requestSubmit();
        };

        // Manual form AJAX submission
        const manualForm = document.getElementById('counterManualForm');
        if (manualForm) {
            manualForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('customInputCount');
                const val = parseInt(input.value, 10);
                if (isNaN(val)) return;

                const submitBtn = manualForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(manualForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(manualForm),
                })
                    .then(async (res) => {
                        const payload = await res.json().catch(() => ({}));
                        if (!res.ok) throw Object.assign(new Error('Manual add failed'), { payload });
                        return payload;
                    })
                    .then((payload) => {
                        if (payload.stats) {
                            totalCompleted = Number(payload.stats.total_completed);
                            updateDisplay();
                        }
                        const modalEl = document.getElementById('controlsModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        input.value = '';
                        if (typeof showFlashToast === 'function') {
                            showFlashToast(payload.message || 'Zikr count updated successfully.');
                        }
                    })
                    .catch((err) => {
                        alert(err?.payload?.message || 'Could not update zikr count.');
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            });
        }

        // Beforeunload beacon fallback
        window.addEventListener('beforeunload', () => {
            if (pendingBatch > 0) {
                const formData = new FormData();
                formData.append('count', pendingBatch);
                formData.append('user_id', container.dataset.userId);
                formData.append('_token', csrfToken);
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(container.dataset.incrementUrl, formData);
                }
            }
        });

        // Initial bead render & display update
        updateDisplay();
    })();
</script>
@endpush
@endsection
