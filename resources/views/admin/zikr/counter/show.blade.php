@extends('layouts.admin')
@section('title', $tasbeeh->title . ' — Live Counter')
@section('page_title', 'Live Zikr Counter')

@section('content')
<div class="live-counter-page-wrapper" id="liveCounterPage" style="cursor: pointer; min-height: calc(100vh - 120px); width: 100%; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation;">
    {{-- Main Tasbeeh Card with Tap Anywhere Detection --}}
    <div
        class="tasbeeh-card"
        id="tasbeehContainer"
        data-increment-url="{{ route('admin.zikr.counter.increment', $tasbeeh) }}"
        data-user-id="{{ $user->id }}"
        data-total-required="{{ $stats['total_required'] }}"
        data-total-completed="{{ $stats['total_completed'] }}"
        data-today-completed="{{ $stats['today_completed'] }}"
        data-daily-target="{{ $stats['daily_target'] }}"
    >
        {{-- Card Top Header Bar --}}
        <div class="card-top-bar">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <a href="{{ route('admin.zikr.index', ['user_id' => $user->id]) }}" class="btn-menu-dots text-decoration-none flex-shrink-0" onclick="event.stopPropagation()" title="Back to Dashboard">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <span class="fw-semibold text-white text-truncate" style="font-size: 0.9rem;">{{ $tasbeeh->title }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($stats['today_completed'] >= $stats['daily_target'] && $stats['daily_target'] > 0)
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 0.72rem; font-weight: 600;">
                        <i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace" id="liveTodayVal">{{ $stats['today_completed'] }}</strong>
                    </span>
                @elseif($stats['today_completed'] > 0)
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #38bdf8; font-size: 0.72rem; font-weight: 600;">
                        Today: <strong class="ms-1 font-monospace" id="liveTodayVal">{{ $stats['today_completed'] }}</strong>
                    </span>
                @else
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; font-size: 0.72rem; font-weight: 600;">
                        Today: <strong class="ms-1 font-monospace" id="liveTodayVal">0</strong>
                    </span>
                @endif
                <button class="btn-menu-dots flex-shrink-0" type="button" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#controlsModal" title="Controls & Quick Add">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                </button>
            </div>
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

        {{-- Islamic Mehrab Arch with Main Counter --}}
        <div class="mehrab-arch" id="mehrabArchBox">
            {{-- Main Counter Display Number without comma --}}
            <div class="counter-number" id="mainCountDisplay">{{ $stats['total_completed'] }}</div>

            {{-- Bottom Stats Row Inside Arch --}}
            <div class="bottom-stats-row">
                <div class="stat-col">
                    <small>Target</small>
                    <strong class="text-white" id="reqVal">{{ $stats['total_required'] }}</strong>
                </div>
                <div class="stat-col">
                    <small>Completed</small>
                    <strong class="text-success" id="completedVal">{{ $stats['total_completed'] }}</strong>
                </div>
                <div class="stat-col">
                    <small id="remainingLabel">{{ $stats['extra'] > 0 ? 'Extra' : 'Remaining' }}</small>
                    <strong class="{{ $stats['extra'] > 0 ? 'text-info' : ($stats['remaining'] > 0 ? 'text-warning' : 'text-success') }}" id="remainingVal">
                        {{ $stats['extra'] > 0 ? ('+' . $stats['extra']) : $stats['remaining'] }}
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
                <input type="number" inputmode="numeric" name="count" id="customInputCount" class="form-control input-dark font-monospace fw-bold" placeholder="Custom (e.g. 50 or -33)" required>
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

        let baseTotalCompleted = parseInt(container.dataset.totalCompleted, 10) || 0;
        let baseTodayCompleted = parseInt(container.dataset.todayCompleted || '0', 10) || 0;
        let totalCompleted = baseTotalCompleted;
        let todayCompleted = baseTodayCompleted;
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

        const dailyTarget = parseInt(container.dataset.dailyTarget || '100', 10) || 100;
        const liveTodayValEl = document.getElementById('liveTodayVal');
        const liveTodayBadgeEl = document.getElementById('liveTodayBadge');

        function updateTodayDisplay() {
            if (todayCompleted < 0) todayCompleted = 0;

            if (liveTodayValEl) {
                liveTodayValEl.innerText = String(todayCompleted);
            }

            if (liveTodayBadgeEl) {
                if (todayCompleted >= dailyTarget && dailyTarget > 0) {
                    liveTodayBadgeEl.style.background = 'rgba(16, 185, 129, 0.15)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                    liveTodayBadgeEl.style.color = '#34d399';
                    liveTodayBadgeEl.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace" id="liveTodayVal">${todayCompleted}</strong>`;
                } else if (todayCompleted > 0) {
                    liveTodayBadgeEl.style.background = 'rgba(6, 182, 212, 0.15)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(6, 182, 212, 0.4)';
                    liveTodayBadgeEl.style.color = '#38bdf8';
                    liveTodayBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace" id="liveTodayVal">${todayCompleted}</strong>`;
                } else {
                    liveTodayBadgeEl.style.background = 'rgba(239, 68, 68, 0.12)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                    liveTodayBadgeEl.style.color = '#f87171';
                    liveTodayBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace" id="liveTodayVal">0</strong>`;
                }
            }
        }

        // Update Screen Elements
        function updateDisplay(shouldAnimate = false) {
            if (totalCompleted < 0) totalCompleted = 0;

            const formattedNum = String(totalCompleted);

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

                if (shouldAnimate) {
                    mainCountEl.classList.remove('number-bump');
                    void mainCountEl.offsetWidth;
                    mainCountEl.classList.add('number-bump');
                }
            }

            if (completedValEl) completedValEl.innerText = String(totalCompleted);
            if (reqValEl) reqValEl.innerText = String(totalRequired);

            const diff = totalCompleted - totalRequired;
            if (remainingValEl && remainingLabelEl) {
                if (diff > 0) {
                    remainingLabelEl.innerText = 'Extra';
                    remainingValEl.innerText = '+' + String(diff);
                    remainingValEl.className = 'text-info';
                } else {
                    const rem = Math.max(0, totalRequired - totalCompleted);
                    remainingLabelEl.innerText = 'Remaining';
                    remainingValEl.innerText = String(rem);
                    remainingValEl.className = rem > 0 ? 'text-warning' : 'text-success';
                }
            }

            updateTodayDisplay();
        }

        // Batch AJAX Sync to backend
        let inFlightBatch = 0;

        function flushBatch(forceOffline = false) {
            if (pendingBatch === 0 || isSyncing) return;

            isSyncing = true;
            inFlightBatch = pendingBatch;
            pendingBatch = 0;

            const tasbeehId = '{{ $tasbeeh->id }}';

            // If offline, save directly to IndexedDB outbox without duplicate broadcasting
            if (!navigator.onLine || forceOffline) {
                if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                    window.PwaSync.saveZikrCount(tasbeehId, inFlightBatch, null, false);
                }
                inFlightBatch = 0;
                isSyncing = false;
                return;
            }

            const formData = new FormData();
            formData.append('count', inFlightBatch);
            formData.append('user_id', container.dataset.userId);
            formData.append('_token', csrfToken);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3500);

            fetch(container.dataset.incrementUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                signal: controller.signal,
            })
                .then(async (res) => {
                    clearTimeout(timeoutId);
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Increment failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (payload.stats) {
                        baseTotalCompleted = Number(payload.stats.total_completed);
                        if (payload.stats.today_completed !== undefined) {
                            baseTodayCompleted = Number(payload.stats.today_completed);
                        }
                        totalCompleted = baseTotalCompleted + pendingBatch;
                        todayCompleted = baseTodayCompleted + pendingBatch;
                        updateDisplay(false);
                    }
                    inFlightBatch = 0;
                })
                .catch((err) => {
                    clearTimeout(timeoutId);
                    console.warn('Increment network sync failed, saving to offline outbox:', err);
                    if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                        window.PwaSync.saveZikrCount(tasbeehId, inFlightBatch, null, false);
                    }
                    inFlightBatch = 0;
                })
                .finally(() => {
                    isSyncing = false;
                    if (pendingBatch > 0) {
                        clearTimeout(batchTimer);
                        batchTimer = setTimeout(() => flushBatch(), 400);
                    }
                });
        }

        // Helper to spawn a gentle animated glowing ripple on mobile tap
        function createTouchRipple(e) {
            try {
                const ripple = document.createElement('div');
                ripple.className = 'zikr-touch-ripple';
                let x = e.clientX;
                let y = e.clientY;
                if ((x === undefined || y === undefined) && e.touches && e.touches[0]) {
                    x = e.touches[0].clientX;
                    y = e.touches[0].clientY;
                }
                if (x === undefined || y === undefined) {
                    const archBox = document.getElementById('mehrabArchBox') || container;
                    const rect = archBox.getBoundingClientRect();
                    x = rect.left + rect.width / 2;
                    y = rect.top + rect.height / 2;
                }
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                document.body.appendChild(ripple);
                setTimeout(() => {
                    if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
                }, 380);
            } catch (_) {}
        }

        // Ultra-responsive Tap Anywhere Handler for Mobile & Desktop with Haptics & Ripples
        let lastTapTimestamp = 0;
        function handleScreenTap(e) {
            // Ignore clicks on buttons/links/inputs/modals/controls/forms
            if (e && e.target && e.target.closest && e.target.closest('button, a, input, select, textarea, .modal, .modal-backdrop, [data-bs-toggle], .btn-menu-dots, .btn-close, .dropdown-menu, label, form, .preset-grid')) {
                return;
            }

            const now = Date.now();
            if (now - lastTapTimestamp < 75) {
                return;
            }
            lastTapTimestamp = now;

            // Gentle haptic feedback on mobile touch devices
            if (window.navigator && typeof window.navigator.vibrate === 'function') {
                try {
                    window.navigator.vibrate(15);
                } catch (_) {}
            }

            // Visual touch ripple
            if (e) createTouchRipple(e);

            totalCompleted += 1;
            todayCompleted += 1;
            pendingBatch += 1;
            updateDisplay(true);

            // Broadcast real-time tap to other tabs/pages immediately
            if (window.PwaSync && typeof window.PwaSync.broadcastZikrCountUpdate === 'function') {
                window.PwaSync.broadcastZikrCountUpdate('{{ $tasbeeh->id }}', 1);
            }

            clearTimeout(batchTimer);
            batchTimer = setTimeout(() => flushBatch(), 400);
        }

        // Fast pointerdown for touchscreens (0ms latency), click for desktop mouse
        let lastTouchHandled = 0;
        document.addEventListener('pointerdown', function (e) {
            if (e.pointerType === 'touch') {
                if (e.target && e.target.closest && e.target.closest('button, a, input, select, textarea, .modal, .modal-backdrop, [data-bs-toggle], .btn-menu-dots, .btn-close, .dropdown-menu, label, form, .preset-grid')) {
                    return;
                }
                lastTouchHandled = Date.now();
                handleScreenTap(e);
            }
        }, { passive: true });

        document.addEventListener('click', function (e) {
            if (Date.now() - lastTouchHandled < 350) {
                return; // Prevent duplicate execution from synthesized click
            }
            handleScreenTap(e);
        });
        window.handleLiveCardTap = handleScreenTap;

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

                if (!navigator.onLine) {
                    if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                        window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', val);
                    }
                    totalCompleted += val;
                    todayCompleted += val;
                    updateDisplay();
                    const modalEl = document.getElementById('controlsModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    input.value = '';
                    if (submitBtn) submitBtn.disabled = false;
                    if (window.App && typeof window.App.showToast === 'function') {
                        window.App.showToast('info', 'Zikr count saved offline. Will sync once reconnected.');
                    }
                    return;
                }

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
                            baseTotalCompleted = Number(payload.stats.total_completed);
                            if (payload.stats.today_completed !== undefined) {
                                baseTodayCompleted = Number(payload.stats.today_completed);
                            }
                            totalCompleted = baseTotalCompleted;
                            todayCompleted = baseTodayCompleted;
                            updateDisplay();
                        }
                        if (window.PwaSync && typeof window.PwaSync.broadcastZikrCountUpdate === 'function') {
                            window.PwaSync.broadcastZikrCountUpdate('{{ $tasbeeh->id }}', val);
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
                        if (!navigator.onLine && window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                            window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', val);
                            totalCompleted += val;
                            todayCompleted += val;
                            updateDisplay();
                        } else {
                            alert(err?.payload?.message || 'Could not update zikr count.');
                        }
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            });
        }

        // Immediate flush on mobile navigation/pagehide/visibilitychange
        const flushImmediate = () => {
            if (pendingBatch > 0) {
                const countToSave = pendingBatch;
                pendingBatch = 0;
                if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                    window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', countToSave, null, false);
                }
            }
        };

        window.addEventListener('pagehide', flushImmediate);
        window.addEventListener('beforeunload', () => {
            flushImmediate();
            if (navigator.sendBeacon && pendingBatch > 0) {
                const formData = new FormData();
                formData.append('count', pendingBatch);
                formData.append('user_id', container.dataset.userId);
                formData.append('_token', csrfToken);
                navigator.sendBeacon(container.dataset.incrementUrl, formData);
            }
        });
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') flushImmediate();
        });
        document.querySelectorAll('a, button[data-bs-dismiss], [data-bs-toggle]').forEach(el => {
            el.addEventListener('click', flushImmediate);
        });

        // Initial bead render & display update
        updateDisplay();

        // Absolute reconciliation of pending offline counts for this tasbeeh
        const reconcilePending = async (syncedData = null) => {
            try {
                const currentTasbeehId = '{{ $tasbeeh->id }}';

                // 1. If syncedData is provided, update baseline
                if (syncedData && syncedData.zikr_summary && Array.isArray(syncedData.zikr_summary.tasbeehs)) {
                    const match = syncedData.zikr_summary.tasbeehs.find(t => String(t.tasbeeh_id) === currentTasbeehId);
                    if (match) {
                        baseTotalCompleted = Number(match.total_completed || 0);
                        baseTodayCompleted = Number(match.today_completed || 0);
                    }
                } else if (window.PwaDB && typeof window.PwaDB.getMeta === 'function') {
                    // Check cached zikr_summary from IndexedDB if available
                    const cachedSummary = await window.PwaDB.getMeta('zikr_summary');
                    if (cachedSummary && Array.isArray(cachedSummary.tasbeehs)) {
                        const match = cachedSummary.tasbeehs.find(t => String(t.tasbeeh_id) === currentTasbeehId);
                        if (match) {
                            baseTotalCompleted = Number(match.total_completed || 0);
                            baseTodayCompleted = Number(match.today_completed || 0);
                        }
                    }
                }

                // 2. Read pending outbox items
                let items = [];
                if (window.PwaDB && typeof window.PwaDB.getPendingOutbox === 'function') {
                    items = await window.PwaDB.getPendingOutbox();
                }

                let pendingCountForThis = 0;
                let isCompletedToday = false;
                let isReset = false;

                (items || []).forEach(item => {
                    const entity = item.entity || '';
                    const p = item.payload || {};
                    const tId = p.tasbeeh_id ? String(p.tasbeeh_id) : null;

                    if ((entity === 'tasbeeh_count' || entity === 'zikr_count') && tId === currentTasbeehId) {
                        pendingCountForThis += (parseInt(p.count, 10) || 0);
                    } else if (entity === 'tasbeeh_complete_today' && tId === currentTasbeehId) {
                        isCompletedToday = true;
                    } else if (entity === 'zikr_complete_all') {
                        isCompletedToday = true;
                    } else if (entity === 'tasbeeh_reset_single' && tId === currentTasbeehId) {
                        isReset = true;
                    } else if (entity === 'zikr_reset_all') {
                        isReset = true;
                    }
                });

                if (isReset) {
                    totalCompleted = 0 + pendingBatch;
                    todayCompleted = 0 + pendingBatch;
                } else if (isCompletedToday) {
                    const neededForToday = Math.max(dailyTarget - baseTodayCompleted, 0);
                    todayCompleted = Math.max(baseTodayCompleted, dailyTarget) + pendingCountForThis + pendingBatch;
                    totalCompleted = baseTotalCompleted + neededForToday + pendingCountForThis + pendingBatch;
                } else {
                    totalCompleted = baseTotalCompleted + pendingCountForThis + pendingBatch;
                    todayCompleted = baseTodayCompleted + pendingCountForThis + pendingBatch;
                }

                if (todayCompleted < 0) todayCompleted = 0;
                if (totalCompleted < 0) totalCompleted = 0;

                updateDisplay();
            } catch (e) {
                console.warn('reconcilePending error:', e);
            }
        };

        reconcilePending();
        window.addEventListener('load', () => reconcilePending());
        window.addEventListener('pageshow', () => reconcilePending());
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') reconcilePending();
        });
        window.addEventListener('pwa:sync-completed', (e) => reconcilePending(e.detail?.data || null));

        function handleCounterBroadcast(data) {
            if (!data || !data.type) return;
            const currentTasbeehId = '{{ $tasbeeh->id }}';

            if (data.type === 'ZIKR_COUNT_INCREMENT' && String(data.tasbeehId) === currentTasbeehId) {
                const delta = parseInt(data.delta, 10) || 0;
                if (delta !== 0) {
                    totalCompleted += delta;
                    todayCompleted += delta;
                    updateDisplay(true);
                }
            } else if (data.type === 'ZIKR_COMPLETE_TODAY' && String(data.tasbeehId) === currentTasbeehId) {
                const countToAdd = dailyTarget > 0 ? dailyTarget : 100;
                totalCompleted += countToAdd;
                todayCompleted += countToAdd;
                updateDisplay(true);
            } else if (data.type === 'ZIKR_COMPLETE_ALL') {
                const countToAdd = dailyTarget > 0 ? dailyTarget : 100;
                totalCompleted += countToAdd;
                todayCompleted += countToAdd;
                updateDisplay(true);
            } else if ((data.type === 'ZIKR_RESET_SINGLE' && String(data.tasbeehId) === currentTasbeehId) || data.type === 'ZIKR_RESET_ALL') {
                totalCompleted = 0;
                todayCompleted = 0;
                updateDisplay();
            }
        }

        // Listen for BroadcastChannel & storage events
        if ('BroadcastChannel' in window) {
            try {
                const zikrChannel = new BroadcastChannel('portfolio_zikr_channel');
                zikrChannel.onmessage = (event) => {
                    if (event.data && event.data.type) {
                        handleCounterBroadcast(event.data);
                    }
                };
            } catch (e) {}
        }

        window.addEventListener('storage', (event) => {
            if (event.key === 'pwa_zikr_live_broadcast' && event.newValue) {
                try {
                    const parsed = JSON.parse(event.newValue);
                    handleCounterBroadcast(parsed);
                } catch (e) {}
            }
        });

        // Cache this counter page dynamically into Service Worker Cache
        if ('caches' in window) {
            caches.keys().then(names => {
                const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                if (pwaCacheName) {
                    caches.open(pwaCacheName).then(cache => {
                        cache.add(window.location.href).catch(() => {});
                        cache.add(window.location.pathname).catch(() => {});
                        cache.add('/admin/zikr').catch(() => {});
                        cache.add('/admin/tasbeehs').catch(() => {});
                    });
                }
            }).catch(() => {});
        }
    })();
</script>
@endpush
@endsection
