{{-- Modal: Unlock Protected Stats (Lifetime, Total Required, Total Completed) --}}
<div class="modal fade" id="unlockZikrStatsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 410px;">
        <div class="modal-content text-white" style="background: #08111e; border: 1px solid rgba(6, 182, 212, 0.45); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.95);">
            <form id="unlockZikrStatsForm" method="POST" action="javascript:void(0);">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #06b6d4;">
                        <i class="bi bi-shield-lock fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white">Security Verification</h5>
                    <p class="text-muted-custom small mb-3" style="line-height: 1.5; font-size: 0.8rem;">
                        <strong>Lifetime Total</strong>, <strong>Total Required</strong> aur <strong>Total Completed</strong> dekhne ke liye apna <strong>Login Password</strong> enter karein:
                    </p>

                    {{-- Password Input Box --}}
                    <div class="mb-3 text-start">
                        <label for="unlockStatsPasswordInput" class="form-label small fw-semibold text-muted-custom mb-1" style="font-size: 0.75rem;">
                            Login Password:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary border-opacity-25" style="background: #0f172a; color: #94a3b8;">
                                <i class="bi bi-key"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control border-secondary border-opacity-25 text-white" 
                                id="unlockStatsPasswordInput" 
                                placeholder="Enter login password..."
                                style="background: #0f172a; font-size: 0.85rem;"
                                autocomplete="current-password"
                                required
                            >
                            <button class="btn btn-outline-secondary" type="button" id="toggleUnlockStatsPassword" style="border-color: rgba(255,255,255,0.15); background: #0f172a; color: #94a3b8;" title="Show/Hide Password">
                                <i class="bi bi-eye" id="toggleUnlockStatsPasswordIcon"></i>
                            </button>
                        </div>
                        <div class="text-danger small mt-2 d-none fw-semibold" id="unlockStatsPasswordError" style="font-size: 0.78rem; line-height: 1.4;"></div>
                    </div>

                    {{-- 5 min note --}}
                    <div class="alert alert-info py-2 px-3 small text-start border-0 rounded-3 mb-3 d-flex align-items-center gap-2" style="background: rgba(6, 182, 212, 0.12); color: #38bdf8; font-size: 0.72rem; line-height: 1.4;">
                        <i class="bi bi-info-circle flex-shrink-0 fs-6"></i>
                        <span>Password verify hone par ye stats <strong>5 minute</strong> tak view rahenge. Us ke baad dobara password enter karna hoga.</span>
                    </div>

                    <div class="d-flex gap-2 justify-content-center pt-1">
                        <button type="button" class="btn btn-outline-theme btn-sm px-3" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-cyan btn-sm px-3 fw-bold" id="btnConfirmUnlockStats" style="background: #06b6d4; border-color: #06b6d4; color: #08111e;">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                            <span id="btnConfirmUnlockStatsText"><i class="bi bi-unlock-fill me-1"></i>Unlock Stats</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
