{{-- Mark All Complete for Today Modal --}}
<div class="modal fade" id="completeAllTasbeehsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid #162a45; border-radius: 18px; box-shadow: 0 20px 45px rgba(0,0,0,0.85);">
            <div class="modal-body text-center p-4">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #10b981;">
                    <i class="bi bi-check2-all fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2 text-white">Complete All for Today</h5>
                <p class="text-muted-custom small mb-4" style="line-height: 1.6;">
                    Kia aap aaj ke tamam tasbeeh targets ko complete mark karna chahte hain? Tamam remaining counts 100% complete ho jayengi.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-theme btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm px-4 fw-bold" id="btnConfirmCompleteAll" style="background: #10b981; border-color: #10b981;">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                        Yes, Complete All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reset All Tasbeehs Step 1 (Confirmation Alert) --}}
<div class="modal fade" id="resetAllTasbeehsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 390px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.95);">
            <div class="modal-body text-center p-4">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #ef4444;">
                    <i class="bi bi-arrow-counterclockwise fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2 text-white">Reset All Tasbeehs?</h5>
                <p class="text-muted-custom small mb-4" style="line-height: 1.6; font-size: 0.82rem;">
                    Kia aap waqai tamam tasbeehs ko <strong>0</strong> par reset karna chahte hain? Tamam counts zero ho jayengi aur Tracking Start Date aaj set ho jayegi.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-theme btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#verifyResetAllPasswordModal" style="background: #ef4444; border-color: #ef4444;">
                        Yes, Continue <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reset All Tasbeehs Step 2 (Dedicated Password Verification Modal) --}}
<div class="modal fade" id="verifyResetAllPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 390px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.95);">
            <form id="verifyResetAllPasswordForm" method="POST" action="javascript:void(0);">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444;">
                        <i class="bi bi-shield-lock fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white">Security Verification</h5>
                    <p class="text-muted-custom small mb-3" style="line-height: 1.5; font-size: 0.78rem;">
                        Tamam Tasbeehs reset karne ke liye apna <strong>Login Password</strong> enter karein:
                    </p>

                    {{-- Password Input Box --}}
                    <div class="mb-3 text-start">
                        <label for="resetAllPasswordInput" class="form-label small fw-semibold text-muted-custom mb-1" style="font-size: 0.75rem;">
                            Login Password:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary border-opacity-25" style="background: #0f172a; color: #94a3b8;">
                                <i class="bi bi-key"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control border-secondary border-opacity-25 text-white" 
                                id="resetAllPasswordInput" 
                                placeholder="Enter login password..."
                                style="background: #0f172a; font-size: 0.85rem;"
                                autocomplete="current-password"
                                required
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleResetAllPassword" style="border-color: rgba(255,255,255,0.15); background: #0f172a; color: #94a3b8;">
                                <i class="bi bi-eye" id="toggleResetAllPasswordIcon"></i>
                            </button>
                        </div>
                        <div class="text-danger small mt-2 d-none fw-semibold" id="resetAllPasswordError" style="font-size: 0.78rem; line-height: 1.4;"></div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center pt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#resetAllTasbeehsModal">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold" id="btnConfirmResetAll" style="background: #ef4444; border-color: #ef4444;">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                            <span id="btnConfirmResetAllText">Verify & Reset All</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 1: Reset Lifetime Total Confirmation Alert --}}
<div class="modal fade" id="resetLifetimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 390px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.95);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); color: #f97316;">
                    <i class="bi bi-trash3 fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2 text-white">Reset Lifetime Total?</h5>
                <p class="text-muted-custom small mb-4" style="line-height: 1.6; font-size: 0.82rem;">
                    Kia aap waqai apna <strong>Lifetime Permanent Total Zikr</strong> counter aur <strong>Tracking Duration</strong> zero (0) par reset karna chahte hain?
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-theme btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-accent btn-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#verifyLifetimePasswordModal" style="background: #f97316; border-color: #f97316;">
                        Yes, Continue <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Dedicated Password Verification Modal --}}
<div class="modal fade" id="verifyLifetimePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 390px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.95);">
            <form id="verifyLifetimePasswordForm" method="POST" action="javascript:void(0);">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); color: #f97316;">
                        <i class="bi bi-shield-lock fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white">Security Verification</h5>
                    <p class="text-muted-custom small mb-3" style="line-height: 1.5; font-size: 0.78rem;">
                        Tasdeeq ke liye apna <strong>Login Password</strong> enter karein:
                    </p>

                    {{-- Password Input Box --}}
                    <div class="mb-3 text-start">
                        <label for="resetLifetimePasswordInput" class="form-label small fw-semibold text-muted-custom mb-1" style="font-size: 0.75rem;">
                            Login Password:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary border-opacity-25" style="background: #0f172a; color: #94a3b8;">
                                <i class="bi bi-key"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control border-secondary border-opacity-25 text-white" 
                                id="resetLifetimePasswordInput" 
                                placeholder="Enter login password..."
                                style="background: #0f172a; font-size: 0.85rem;"
                                autocomplete="current-password"
                                required
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleResetLifetimePassword" style="border-color: rgba(255,255,255,0.15); background: #0f172a; color: #94a3b8;">
                                <i class="bi bi-eye" id="toggleResetLifetimePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="text-danger small mt-2 d-none fw-semibold" id="resetLifetimePasswordError" style="font-size: 0.78rem; line-height: 1.4;"></div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center pt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#resetLifetimeModal">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                        <button type="submit" class="btn btn-accent btn-sm px-3 fw-bold" id="btnConfirmResetLifetime" style="background: #f97316; border-color: #f97316;">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                            <span id="btnConfirmResetLifetimeText">Verify & Reset</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const completeAllBtn = document.getElementById('btnConfirmCompleteAll');
    const resetBtn = document.getElementById('btnConfirmResetAll');
    const resetLifetimeBtn = document.getElementById('btnConfirmResetLifetime');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);
    const defaultUserId = urlParams.get('user_id') || '{{ $selectedUser->id ?? auth()->id() }}';



    // Direct instant DOM updater across all tasbeeh cards and top stats (0ms, no page reload)
    function updateAllCardsAsCompletedToday(summary) {
        const tasbeehsMap = {};
        if (summary && Array.isArray(summary.tasbeehs)) {
            summary.tasbeehs.forEach(t => {
                tasbeehsMap[String(t.tasbeeh_id || t.id)] = t;
            });
        }

        const cards = document.querySelectorAll('[id^="tasbeeh-card-"]');
        cards.forEach(card => {
            const tId = card.id.replace('tasbeeh-card-', '');
            const sItem = tasbeehsMap[tId];

            let dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10);
            if (sItem && sItem.daily_target) dailyTarget = parseInt(sItem.daily_target, 10);

            let newToday = dailyTarget;
            let newTotal = parseInt(card.dataset.totalCompleted || card.dataset.baseTotalCompleted || '0', 10);

            if (sItem) {
                newToday = parseInt(sItem.today_completed, 10);
                newTotal = parseInt(sItem.total_completed, 10);
            } else {
                const oldToday = parseInt(card.dataset.todayCompleted || '0', 10);
                const addAmount = Math.max(0, dailyTarget - oldToday);
                newTotal += addAmount;
            }

            let totalReq = parseInt(card.dataset.totalRequired || '0', 10);
            if (sItem && sItem.total_required) totalReq = parseInt(sItem.total_required, 10);

            // Update card dataset attributes
            card.dataset.todayCompleted = String(newToday);
            card.dataset.totalCompleted = String(newTotal);
            card.dataset.baseTodayCompleted = String(newToday);
            card.dataset.baseTotalCompleted = String(newTotal);
            card.dataset.baseToday = String(newToday);
            card.dataset.baseTotal = String(newTotal);
            card.dataset.complete = 'true';

            // 1. Today status badge (Green with checkmark)
            const todayBadge = card.querySelector('.today-status-badge');
            if (todayBadge) {
                todayBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                todayBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                todayBadge.style.color = '#34d399';
                todayBadge.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">${newToday.toLocaleString()}</strong>`;
            }

            // 2. Complete check button
            const completeBtn = card.querySelector('.btn-complete-icon');
            if (completeBtn) {
                completeBtn.classList.add('is-completed', 'active');
            }

            // 3. Completed total badge
            const completedBadge = card.querySelector('.badge-completed');
            if (completedBadge) {
                completedBadge.dataset.rawCompleted = newTotal.toLocaleString();
                const strong = completedBadge.querySelector('strong');
                if (strong) strong.textContent = newTotal.toLocaleString();
            }

            // 4. Remaining / Extra badge
            const remainingBadge = card.querySelector('.badge-remaining');
            if (remainingBadge) {
                const diff = newTotal - totalReq;
                if (diff > 0) {
                    remainingBadge.textContent = '+' + diff.toLocaleString() + ' Extra';
                    remainingBadge.className = 'badge-remaining extra';
                } else if (diff === 0) {
                    remainingBadge.textContent = 'Completed';
                    remainingBadge.className = 'badge-remaining completed-badge';
                } else {
                    remainingBadge.textContent = 'Remaining ' + Math.abs(diff).toLocaleString();
                    remainingBadge.className = 'badge-remaining';
                }
            }

            // 5. Progress bar & percentage
            const progressBar = card.querySelector('.progress-bar-custom');
            const percentTextEl = card.querySelector('.progress-percent-text') || card.querySelector('.progress-container .font-monospace');
            if (progressBar && totalReq > 0) {
                const pct = Math.min(100, Math.round((newTotal / totalReq) * 100));
                progressBar.style.width = pct + '%';
                if (percentTextEl) percentTextEl.textContent = pct + '%';

                const diff = newTotal - totalReq;
                let barClass = 'amber';
                if (diff > 0) barClass = 'cyan';
                else if (diff === 0) barClass = 'emerald';
                progressBar.className = `progress-bar-custom ${barClass}`;
            }

            // 6. Period badge if filtered
            if (sItem && sItem.range_completed !== undefined) {
                const periodBadge = card.querySelector('.zikr-period-count-badge');
                if (periodBadge) {
                    const valEl = periodBadge.querySelector('.zikr-period-val');
                    if (valEl) valEl.textContent = Number(sItem.range_completed).toLocaleString();
                }
            }
        });

        // Update Top Overall Statistics Cards if summary provided
        if (summary) {
            const lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl && summary.lifetime_total !== undefined) {
                lifetimeEl.dataset.baseLifetime = String(summary.lifetime_total);
                lifetimeEl.dataset.rawVal = Number(summary.lifetime_total).toLocaleString();
                if (lifetimeEl.textContent !== '••••') {
                    lifetimeEl.textContent = Number(summary.lifetime_total).toLocaleString();
                }
            }

            const todayCompletedEl = document.getElementById('top-stat-today-completed');
            if (todayCompletedEl && summary.overall_today_completed !== undefined) {
                todayCompletedEl.dataset.baseVal = String(summary.overall_today_completed);
                todayCompletedEl.dataset.rawVal = Number(summary.overall_today_completed).toLocaleString();
                todayCompletedEl.textContent = Number(summary.overall_today_completed).toLocaleString();
            }

            const todayPctEl = document.getElementById('top-stat-today-percentage');
            if (todayPctEl && summary.overall_today_percentage !== undefined) {
                todayPctEl.dataset.rawSubtext = `${summary.overall_today_percentage}% of daily target`;
                todayPctEl.textContent = `${summary.overall_today_percentage}% of daily target`;
            }

            const totalCompEl = document.getElementById('top-stat-total-completed');
            if (totalCompEl && summary.overall_total_completed !== undefined) {
                totalCompEl.dataset.rawVal = Number(summary.overall_total_completed).toLocaleString();
                if (totalCompEl.textContent !== '••••') {
                    totalCompEl.textContent = Number(summary.overall_total_completed).toLocaleString();
                }
            }

            const overallPctEl = document.getElementById('top-stat-overall-percentage');
            if (overallPctEl && summary.overall_percentage !== undefined) {
                overallPctEl.dataset.rawSubtext = `${summary.overall_percentage}% Completed`;
                if (overallPctEl.textContent !== '•••% Completed') {
                    overallPctEl.textContent = `${summary.overall_percentage}% Completed`;
                }
            }

            // Backlog / Extra
            const backlogValEl = document.getElementById('top-stat-backlog-value');
            const backlogSubEl = document.getElementById('top-stat-backlog-subtext');
            const backlogTitleEl = document.getElementById('top-stat-backlog-title');
            if (backlogValEl && summary.overall_extra !== undefined && summary.overall_backlog !== undefined) {
                const isExtra = summary.overall_extra > 0;
                const isBehind = summary.overall_backlog > 0;
                const colorClass = isExtra ? 'text-info' : (isBehind ? 'text-warning' : 'text-success');
                const valText = isExtra ? ('+' + Number(summary.overall_extra).toLocaleString()) : Number(summary.overall_backlog).toLocaleString();
                const subText = isExtra ? 'Ahead of schedule' : (isBehind ? 'Behind schedule' : 'On track');

                if (backlogTitleEl) {
                    backlogTitleEl.textContent = isExtra ? 'Extra Zikr' : 'Remaining Backlog';
                }
                backlogValEl.className = `fs-3 fs-md-2 ${colorClass} d-block font-monospace my-0 zikr-stat-maskable`;
                backlogValEl.dataset.rawVal = valText;
                backlogValEl.textContent = valText;

                if (backlogSubEl) {
                    backlogSubEl.className = `${colorClass} d-block fw-semibold text-truncate zikr-stat-maskable`;
                    backlogSubEl.dataset.rawSubtext = subText;
                    backlogSubEl.textContent = subText;
                }
            }
        }

        // Re-calculate / sync top stats
        if (typeof window.recalculateZikrTopStats === 'function') {
            window.recalculateZikrTopStats();
        }

        // Re-run filter buttons count & visibility
        if (typeof window.refreshZikrFilter === 'function') {
            window.refreshZikrFilter();
        }

        // Re-apply stat card mask if needed
        if (typeof window.renderZikrStatCards === 'function') {
            window.renderZikrStatCards();
        }
    }

    if (completeAllBtn) {
        completeAllBtn.addEventListener('click', async function () {
            const spinner = completeAllBtn.querySelector('.spinner-border');
            completeAllBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');

            if (!navigator.onLine) {
                if (window.PwaSync && typeof window.PwaSync.completeAllTasbeehsToday === 'function') {
                    await window.PwaSync.completeAllTasbeehsToday();
                }
                updateAllCardsAsCompletedToday(null);
                if (typeof window.reconcileZikrOfflineCounts === 'function') {
                    window.reconcileZikrOfflineCounts();
                }
                const modalEl = document.getElementById('completeAllTasbeehsModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('info', 'Saved offline. All tasbeehs updated on screen!');
                }
                completeAllBtn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                return;
            }

            try {
                const fromDate = document.getElementById('filter_from_date')?.value || '';
                const toDate = document.getElementById('filter_to_date')?.value || '';

                const response = await fetch('{{ route("admin.zikr.complete-all-today") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: defaultUserId,
                        from_date: fromDate,
                        to_date: toDate
                    })
                });

                const data = await response.json();
                if (data.success) {
                    if (window.PwaSync && typeof window.PwaSync.broadcastEvent === 'function') {
                        window.PwaSync.broadcastEvent('ZIKR_COMPLETE_ALL', {});
                    }

                    // 1. Reconcile with fresh server summary data
                    if (data.summary && typeof window.reconcileZikrOfflineCounts === 'function') {
                        await window.reconcileZikrOfflineCounts({
                            zikr_summary: data.summary,
                            server_time: data.server_time || new Date().toISOString()
                        });
                    }

                    // 2. Direct Instant DOM Update across all cards & top stats (0ms, no page reload)
                    updateAllCardsAsCompletedToday(data.summary);

                    const modalEl = document.getElementById('completeAllTasbeehsModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();

                    if (window.App && typeof window.App.showToast === 'function') {
                        window.App.showToast('success', data.message || 'Today\'s quota completed across all tasbeehs!');
                    }
                } else {
                    alert(data.message || 'Failed to complete tasbeehs.');
                }
            } catch (err) {
                console.error(err);
                if (!navigator.onLine && window.PwaSync && typeof window.PwaSync.completeAllTasbeehsToday === 'function') {
                    await window.PwaSync.completeAllTasbeehsToday();
                    updateAllCardsAsCompletedToday(null);
                    if (typeof window.reconcileZikrOfflineCounts === 'function') {
                        window.reconcileZikrOfflineCounts();
                    }
                    const modalEl = document.getElementById('completeAllTasbeehsModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                    if (window.App && typeof window.App.showToast === 'function') {
                        window.App.showToast('info', 'Saved offline. Pending tasbeehs updated locally.');
                    }
                } else {
                    alert('An unexpected error occurred while completing tasbeehs.');
                }
            } finally {
                completeAllBtn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            }
        });
    }

    // Password toggle & handlers for Dedicated Reset All Password Verification Modal (#verifyResetAllPasswordModal)
    const verifyResetAllModalEl = document.getElementById('verifyResetAllPasswordModal');
    const verifyResetAllFormEl = document.getElementById('verifyResetAllPasswordForm');
    const toggleResetAllPasswordBtn = document.getElementById('toggleResetAllPassword');
    const resetAllPasswordInput = document.getElementById('resetAllPasswordInput');
    const resetAllPasswordError = document.getElementById('resetAllPasswordError');
    const resetAllPasswordIcon = document.getElementById('toggleResetAllPasswordIcon');
    const resetAllBtnText = document.getElementById('btnConfirmResetAllText');

    if (verifyResetAllModalEl && resetAllPasswordInput) {
        verifyResetAllModalEl.addEventListener('show.bs.modal', function () {
            resetAllPasswordInput.value = '';
            resetAllPasswordInput.type = 'password';
            resetAllPasswordInput.classList.remove('is-invalid');
            if (resetAllPasswordIcon) resetAllPasswordIcon.className = 'bi bi-eye';
            if (resetAllPasswordError) {
                resetAllPasswordError.textContent = '';
                resetAllPasswordError.classList.add('d-none');
            }
        });

        verifyResetAllModalEl.addEventListener('shown.bs.modal', function () {
            resetAllPasswordInput.focus();
        });
    }

    if (toggleResetAllPasswordBtn && resetAllPasswordInput && resetAllPasswordIcon) {
        toggleResetAllPasswordBtn.addEventListener('click', function () {
            if (resetAllPasswordInput.type === 'password') {
                resetAllPasswordInput.type = 'text';
                resetAllPasswordIcon.className = 'bi bi-eye-slash';
            } else {
                resetAllPasswordInput.type = 'password';
                resetAllPasswordIcon.className = 'bi bi-eye';
            }
        });
    }

    async function handleResetAllSubmit(e) {
        if (e) e.preventDefault();

        const passwordVal = resetAllPasswordInput ? resetAllPasswordInput.value.trim() : '';
        if (!passwordVal) {
            if (resetAllPasswordError) {
                resetAllPasswordError.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Baraye meharbani apna login password enter karein!';
                resetAllPasswordError.classList.remove('d-none');
            }
            if (resetAllPasswordInput) {
                resetAllPasswordInput.classList.add('is-invalid');
                resetAllPasswordInput.focus();
            }
            return;
        }

        if (resetAllPasswordError) resetAllPasswordError.classList.add('d-none');
        if (resetAllPasswordInput) resetAllPasswordInput.classList.remove('is-invalid');

        const spinner = resetBtn ? resetBtn.querySelector('.spinner-border') : null;
        if (resetBtn) resetBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (resetAllBtnText) resetAllBtnText.textContent = 'Verifying...';

        if (!navigator.onLine) {
            if (window.PwaSync && typeof window.PwaSync.resetAllTasbeehs === 'function') {
                await window.PwaSync.resetAllTasbeehs();
            }
            if (typeof window.updateZikrCardDom === 'function') {
                document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                    const tId = card.id.replace('tasbeeh-card-', '');
                    window.updateZikrCardDom(tId, 0, true);
                });
            }
            const modalInstance = bootstrap.Modal.getOrCreateInstance(verifyResetAllModalEl);
            if (modalInstance) modalInstance.hide();
            if (window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('info', 'All tasbeehs reset to 0 offline and updated on screen!');
            }
            if (resetBtn) resetBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resetAllBtnText) resetAllBtnText.textContent = 'Verify & Reset All';
            return;
        }

        try {
            const token = csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('{{ route("admin.zikr.reset-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    user_id: defaultUserId,
                    password: passwordVal
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                if (typeof window.updateZikrCardDom === 'function') {
                    document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                        const tId = card.id.replace('tasbeeh-card-', '');
                        window.updateZikrCardDom(tId, 0, true);
                    });
                }

                const modalInstance = bootstrap.Modal.getOrCreateInstance(verifyResetAllModalEl);
                if (modalInstance) modalInstance.hide();

                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('success', data.message || 'All tasbeehs have been reset to 0.');
                }
            } else {
                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Incorrect password! Please enter your correct login password.');
                if (resetAllPasswordError) {
                    resetAllPasswordError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${errMsg}`;
                    resetAllPasswordError.classList.remove('d-none');
                }
                if (resetAllPasswordInput) {
                    resetAllPasswordInput.classList.add('is-invalid');
                    resetAllPasswordInput.focus();
                    resetAllPasswordInput.select();
                }
            }
        } catch (err) {
            console.error(err);
            if (resetAllPasswordError) {
                resetAllPasswordError.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Incorrect password or network error. Please try again.';
                resetAllPasswordError.classList.remove('d-none');
            }
            if (resetAllPasswordInput) {
                resetAllPasswordInput.classList.add('is-invalid');
                resetAllPasswordInput.focus();
            }
        } finally {
            if (resetBtn) resetBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resetAllBtnText) resetAllBtnText.textContent = 'Verify & Reset All';
        }
    }

    if (verifyResetAllFormEl) {
        verifyResetAllFormEl.addEventListener('submit', handleResetAllSubmit);
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', handleResetAllSubmit);
    }

    // Password toggle & handlers for Dedicated Lifetime Password Verification Modal (#verifyLifetimePasswordModal)
    const verifyModalEl = document.getElementById('verifyLifetimePasswordModal');
    const verifyFormEl = document.getElementById('verifyLifetimePasswordForm');
    const togglePasswordBtn = document.getElementById('toggleResetLifetimePassword');
    const passwordInput = document.getElementById('resetLifetimePasswordInput');
    const passwordError = document.getElementById('resetLifetimePasswordError');
    const passwordIcon = document.getElementById('toggleResetLifetimePasswordIcon');
    const resetLifetimeBtnText = document.getElementById('btnConfirmResetLifetimeText');

    if (verifyModalEl && passwordInput) {
        verifyModalEl.addEventListener('show.bs.modal', function () {
            passwordInput.value = '';
            passwordInput.type = 'password';
            passwordInput.classList.remove('is-invalid');
            if (passwordIcon) passwordIcon.className = 'bi bi-eye';
            if (passwordError) {
                passwordError.textContent = '';
                passwordError.classList.add('d-none');
            }
        });

        verifyModalEl.addEventListener('shown.bs.modal', function () {
            passwordInput.focus();
        });
    }

    if (togglePasswordBtn && passwordInput && passwordIcon) {
        togglePasswordBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'bi bi-eye';
            }
        });
    }

    async function handleLifetimeResetSubmit(e) {
        if (e) e.preventDefault();

        const passwordVal = passwordInput ? passwordInput.value.trim() : '';
        if (!passwordVal) {
            if (passwordError) {
                passwordError.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Baraye meharbani apna login password enter karein!';
                passwordError.classList.remove('d-none');
            }
            if (passwordInput) {
                passwordInput.classList.add('is-invalid');
                passwordInput.focus();
            }
            return;
        }

        if (passwordError) passwordError.classList.add('d-none');
        if (passwordInput) passwordInput.classList.remove('is-invalid');

        const spinner = resetLifetimeBtn ? resetLifetimeBtn.querySelector('.spinner-border') : null;
        if (resetLifetimeBtn) resetLifetimeBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (resetLifetimeBtnText) resetLifetimeBtnText.textContent = 'Verifying...';

        if (!navigator.onLine) {
            if (window.PwaSync && typeof window.PwaSync.resetLifetime === 'function') {
                await window.PwaSync.resetLifetime();
            }
            const lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl) {
                lifetimeEl.dataset.baseLifetime = '0';
                lifetimeEl.dataset.rawVal = '0';
                lifetimeEl.textContent = '0';
            }
            const lifetimeDurEl = document.getElementById('top-stat-lifetime-duration');
            if (lifetimeDurEl) {
                lifetimeDurEl.dataset.rawSubtext = '<i class="bi bi-clock-history me-1"></i>1 Day';
                lifetimeDurEl.innerHTML = '<i class="bi bi-clock-history me-1"></i>1 Day';
            }
            if (typeof window.renderZikrStatCards === 'function') {
                window.renderZikrStatCards();
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(verifyModalEl);
            if (modalInstance) modalInstance.hide();
            if (window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('info', 'Lifetime reset queued offline. Will synchronize once online.');
            }
            if (resetLifetimeBtn) resetLifetimeBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resetLifetimeBtnText) resetLifetimeBtnText.textContent = 'Verify & Reset';
            return;
        }

        try {
            const token = csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('{{ route("admin.zikr.reset-lifetime") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    user_id: defaultUserId,
                    password: passwordVal
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                const durationText = data.lifetime_duration ? data.lifetime_duration.formatted_full : '1 Day';
                const startDateStr = data.lifetime_duration ? data.lifetime_duration.start_date_formatted : new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

                const lifetimeEl = document.getElementById('top-stat-lifetime-total');
                if (lifetimeEl) {
                    lifetimeEl.dataset.baseLifetime = '0';
                    lifetimeEl.dataset.rawVal = '0';
                    lifetimeEl.textContent = '0';
                }

                const lifetimeDurEl = document.getElementById('top-stat-lifetime-duration');
                if (lifetimeDurEl) {
                    lifetimeDurEl.dataset.rawSubtext = `<i class="bi bi-clock-history me-1"></i>${durationText}`;
                    lifetimeDurEl.innerHTML = `<i class="bi bi-clock-history me-1"></i>${durationText}`;
                    lifetimeDurEl.title = `Started: ${startDateStr}`;
                }

                if (typeof window.renderZikrStatCards === 'function') {
                    window.renderZikrStatCards();
                }

                const modalInstance = bootstrap.Modal.getOrCreateInstance(verifyModalEl);
                if (modalInstance) modalInstance.hide();

                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('success', data.message || 'Lifetime total counter and tracking duration have been reset to 0.');
                }
            } else {
                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Incorrect password! Please enter your correct login password.');
                if (passwordError) {
                    passwordError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${errMsg}`;
                    passwordError.classList.remove('d-none');
                }
                if (passwordInput) {
                    passwordInput.classList.add('is-invalid');
                    passwordInput.focus();
                    passwordInput.select();
                }
            }
        } catch (err) {
            console.error(err);
            if (passwordError) {
                passwordError.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Incorrect password or network error. Please try again.';
                passwordError.classList.remove('d-none');
            }
            if (passwordInput) {
                passwordInput.classList.add('is-invalid');
                passwordInput.focus();
            }
        } finally {
            if (resetLifetimeBtn) resetLifetimeBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resetLifetimeBtnText) resetLifetimeBtnText.textContent = 'Verify & Reset';
        }
    }

    if (verifyFormEl) {
        verifyFormEl.addEventListener('submit', handleLifetimeResetSubmit);
    }
    if (resetLifetimeBtn) {
        resetLifetimeBtn.addEventListener('click', handleLifetimeResetSubmit);
    }
});
</script>
@endpush

