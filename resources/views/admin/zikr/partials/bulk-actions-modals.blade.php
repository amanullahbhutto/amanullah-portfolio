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
    const completeBtn = document.getElementById('btnConfirmCompleteAll');
    const resetBtn = document.getElementById('btnConfirmResetAll');
    const resetLifetimeBtn = document.getElementById('btnConfirmResetLifetime');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);
    const defaultUserId = urlParams.get('user_id') || '{{ $selectedUser->id ?? auth()->id() }}';

    // Direct 1-Click Complete for Individual Tasbeeh (No Modal / No Prompt)
    document.addEventListener('click', async function (e) {
        const completeBtn = e.target.closest('.btn-complete-icon');
        if (!completeBtn) return;
        e.preventDefault();

        const tasbeehId = completeBtn.getAttribute('data-tasbeeh-id');
        const completeUrl = completeBtn.getAttribute('data-complete-url');
        const userId = completeBtn.getAttribute('data-user-id') || defaultUserId;
        const title = completeBtn.getAttribute('data-tasbeeh-title') || 'Tasbeeh';

        if (!tasbeehId || !completeUrl) return;

        const card = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
        let currentCompleted = parseInt(card?.querySelector('.badge-completed strong')?.textContent.replace(/,/g, '') || '0', 10);
        let badgeText = card?.querySelector('.badge-completed')?.textContent || '';
        let match = badgeText.match(/\/\s*([0-9,]+)/);
        // Always add 1 full daily target count (e.g. +100 / +33)
        let countToAdd = parseInt(card?.dataset?.dailyTarget || '100', 10);
        if (countToAdd <= 0) countToAdd = 100;

        // Instant visual update on card (0ms delay)
        if (typeof window.updateZikrCardDom === 'function') {
            window.updateZikrCardDom(tasbeehId, countToAdd, false);
        }

        // Visual click feedback
        completeBtn.style.transform = 'scale(1.25)';
        completeBtn.style.transition = 'transform 0.2s ease';
        setTimeout(() => { completeBtn.style.transform = 'scale(1)'; }, 250);

        if (!navigator.onLine) {
            if (window.PwaSync && typeof window.PwaSync.completeTasbeehToday === 'function') {
                await window.PwaSync.completeTasbeehToday(tasbeehId);
            }
            if (typeof window.showFlashToast === 'function') {
                window.showFlashToast(`+${countToAdd} completed for '${title}' (saved offline)!`, 'info');
            }
            return;
        }

        try {
            const response = await fetch(completeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_id: userId })
            });

            const data = await response.json();
            if (data.success) {
                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(data.message || `+${countToAdd} completed for '${title}'!`, 'success');
                }
            } else {
                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(data.message || 'Failed to complete tasbeeh.', 'danger');
                }
            }
        } catch (err) {
            console.error(err);
            if (!navigator.onLine && window.PwaSync && typeof window.PwaSync.completeTasbeehToday === 'function') {
                await window.PwaSync.completeTasbeehToday(tasbeehId);
                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(`+${countToAdd} completed for '${title}' (saved offline)!`, 'info');
                }
            }
        }
    });

    if (completeBtn) {
        completeBtn.addEventListener('click', async function () {
            const spinner = completeBtn.querySelector('.spinner-border');
            completeBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');

            if (!navigator.onLine) {
                if (window.PwaSync && typeof window.PwaSync.completeAllTasbeehsToday === 'function') {
                    await window.PwaSync.completeAllTasbeehsToday();
                }
                if (typeof window.updateZikrCardDom === 'function') {
                    document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                        const tId = card.id.replace('tasbeeh-card-', '');
                        let dailyTarget = parseInt(card?.dataset?.dailyTarget || '100', 10);
                        if (dailyTarget <= 0) dailyTarget = 100;
                        window.updateZikrCardDom(tId, dailyTarget, false);
                    });
                }
                const modalEl = document.getElementById('completeAllTasbeehsModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('info', 'Saved offline. All tasbeehs updated on screen!');
                }
                completeBtn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                return;
            }

            try {
                const response = await fetch('{{ route("admin.zikr.complete-all-today") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ user_id: defaultUserId })
                });

                const data = await response.json();
                if (data.success) {
                    if (typeof window.updateZikrCardDom === 'function') {
                        document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                            const tId = card.id.replace('tasbeeh-card-', '');
                            let dailyTarget = parseInt(card?.dataset?.dailyTarget || '100', 10);
                            if (dailyTarget <= 0) dailyTarget = 100;
                            window.updateZikrCardDom(tId, dailyTarget, false);
                        });
                    }

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
                    if (typeof window.updateZikrCardDom === 'function') {
                        document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                            const tId = card.id.replace('tasbeeh-card-', '');
                            let currentCompleted = parseInt(card?.querySelector('.badge-completed strong')?.textContent.replace(/,/g, '') || '0', 10);
                            let badgeText = card?.querySelector('.badge-completed')?.textContent || '';
                            let match = badgeText.match(/\/\s*([0-9,]+)/);
                            let targetReq = match ? parseInt(match[1].replace(/,/g, ''), 10) || 100 : 100;
                            let remaining = Math.max(targetReq - currentCompleted, 0);
                            if (remaining > 0) {
                                let dailyTarget = parseInt(card?.dataset?.dailyTarget || '100', 10);
                                let countToAdd = Math.min(dailyTarget, remaining);
                                window.updateZikrCardDom(tId, countToAdd, false);
                            }
                        });
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
                completeBtn.disabled = false;
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
            if (lifetimeEl) lifetimeEl.textContent = '0';
            const lifetimeDurEl = document.getElementById('top-stat-lifetime-duration');
            if (lifetimeDurEl) lifetimeDurEl.innerHTML = '<i class="bi bi-clock-history me-1"></i>1 Day';

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
                const lifetimeEl = document.getElementById('top-stat-lifetime-total');
                if (lifetimeEl) lifetimeEl.textContent = '0';

                const durationText = data.lifetime_duration ? data.lifetime_duration.formatted_full : '1 Day';
                const startDateStr = data.lifetime_duration ? data.lifetime_duration.start_date_formatted : new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

                const lifetimeDurEl = document.getElementById('top-stat-lifetime-duration');
                if (lifetimeDurEl) {
                    lifetimeDurEl.innerHTML = `<i class="bi bi-clock-history me-1"></i>${durationText}`;
                    lifetimeDurEl.title = `Started: ${startDateStr}`;
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

