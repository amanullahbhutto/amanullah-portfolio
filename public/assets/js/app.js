(() => {
    'use strict';

    const root = document.documentElement;
    const storedTheme = localStorage.getItem('portfolio-theme');
    if (storedTheme === 'dark' || storedTheme === 'light') root.dataset.theme = storedTheme;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            localStorage.setItem('portfolio-theme', next);
        });
    });

    const header = document.getElementById('siteHeader');
    const backToTop = document.querySelector('[data-back-to-top]');
    const updateScrollUI = () => {
        header?.classList.toggle('scrolled', window.scrollY > 20);
        backToTop?.classList.toggle('visible', window.scrollY > 500);
    };
    updateScrollUI();
    window.addEventListener('scroll', updateScrollUI, { passive: true });
    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    const mainNav = document.getElementById('mainNav');
    const navbarToggler = document.querySelector('[data-bs-target="#mainNav"]');
    const hideMainNav = () => {
        if (!mainNav?.classList.contains('show')) return;

        const collapse = window.bootstrap?.Collapse?.getOrCreateInstance(mainNav, { toggle: false });
        if (collapse) {
            collapse.hide();
            return;
        }

        mainNav.classList.remove('show');
        navbarToggler?.setAttribute('aria-expanded', 'false');
    };

    document.addEventListener('click', (event) => {
        if (!mainNav?.classList.contains('show')) return;
        if (mainNav.contains(event.target) || navbarToggler?.contains(event.target)) return;

        hideMainNav();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') hideMainNav();
    });

    mainNav?.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', hideMainNav);
    });

    if (window.AOS) {
        window.AOS.init({ duration: 750, once: true, offset: 60, easing: 'ease-out-cubic' });
    }

    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });
    document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
        button.addEventListener('click', () => {
            sidebar?.classList.remove('open');
            overlay?.classList.remove('show');
        });
    });

    const ensureActiveSidebarVisible = () => {
        const sidebarNav = document.querySelector('.sidebar-nav');
        if (!sidebarNav) return;
        const activeLink = sidebarNav.querySelector('.sidebar-subnav a.active') || sidebarNav.querySelector('a.active') || sidebarNav.querySelector('summary.active');
        if (activeLink) {
            const navRect = sidebarNav.getBoundingClientRect();
            const activeRect = activeLink.getBoundingClientRect();
            if (activeRect.top < navRect.top + 30 || activeRect.bottom > navRect.bottom - 30) {
                const targetScroll = (activeLink.offsetTop - sidebarNav.offsetTop) - (sidebarNav.clientHeight / 2) + (activeLink.clientHeight / 2);
                sidebarNav.scrollTop = Math.max(0, targetScroll);
            }
        }
    };
    ensureActiveSidebarVisible();

    document.addEventListener('submit', (event) => {
        const form = event.target.closest?.('[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm || 'Are you sure?')) {
            event.preventDefault();
        }
    });

    document.querySelectorAll('[data-auto-submit]').forEach((control) => {
        control.addEventListener('change', () => {
            if (control.form?.requestSubmit) {
                control.form.requestSubmit();
                return;
            }

            control.form?.submit();
        });
    });

    document.querySelectorAll('.project-type-option input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.project-type-option').forEach((option) => {
                const input = option.querySelector('input[type="radio"]');
                option.classList.toggle('is-selected', Boolean(input?.checked));
            });
        });
    });

    const prepareFlashToast = (toast) => {
        const duration = Number(toast.dataset.flashDuration || 3000);
        toast.style.setProperty('--flash-duration', `${duration}ms`);

        const hideToast = () => {
            if (toast.classList.contains('is-hiding')) return;
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 260);
        };

        window.setTimeout(hideToast, duration);
        toast.querySelector('[data-flash-close]')?.addEventListener('click', hideToast);
    };

    const showFlashToast = (message, type = 'success') => {
        let viewport = document.querySelector('.flash-toast-viewport');
        if (!viewport) {
            viewport = document.createElement('div');
            viewport.className = 'flash-toast-viewport';
            document.body.appendChild(viewport);
        }

        const toast = document.createElement('div');
        const isSuccess = type !== 'danger';
        toast.className = `flash-toast ${isSuccess ? 'success' : 'danger'}`;
        toast.dataset.flashToast = '';
        toast.dataset.flashDuration = '3000';
        toast.innerHTML = `
            <div class="flash-toast-icon"><i class="bi ${isSuccess ? 'bi-check2-circle' : 'bi-exclamation-triangle'}"></i></div>
            <div class="flash-toast-body">
                <strong>${isSuccess ? 'Success' : 'Error'}</strong>
                <span></span>
            </div>
            <button class="flash-toast-close" type="button" data-flash-close aria-label="Close notification"><i class="bi bi-x-lg"></i></button>
            <div class="flash-toast-progress"></div>
        `;
        toast.querySelector('.flash-toast-body span').textContent = message;
        viewport.appendChild(toast);
        prepareFlashToast(toast);
    };

    document.querySelectorAll('[data-flash-toast]').forEach(prepareFlashToast);

    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-permission-toggle]');
        if (!button) return;

        const panel = button.closest('.permission-panel');
        const checkboxes = Array.from(panel?.querySelectorAll('input[type="checkbox"]') || []);
        const shouldCheck = checkboxes.some((checkbox) => !checkbox.checked);

        checkboxes.forEach((checkbox) => {
            checkbox.checked = shouldCheck;
        });
    });

    document.querySelectorAll('[data-live-search]').forEach((form) => {
        const input = form.querySelector('input[type="search"][name="q"]');
        const clearButton = form.querySelector('[data-live-search-clear]');
        const targetSelector = form.dataset.liveSearchTarget;
        const target = targetSelector ? document.querySelector(targetSelector) : null;
        let timer = null;
        let controller = null;
        let activeRequest = 0;

        if (!input) return;

        const buildUrl = () => {
            const url = new URL(form.action, window.location.href);
            const params = new URLSearchParams();

            new FormData(form).forEach((value, key) => {
                const stringValue = String(value).trim();
                if (stringValue !== '') params.set(key, stringValue);
            });

            params.delete('page');
            url.search = params.toString();

            return url;
        };

        const renderUrl = (url) => {
            if (!target) {
                window.location.href = url.toString();
                return;
            }

            if (controller) controller.abort();
            controller = new AbortController();
            const requestId = ++activeRequest;
            target.classList.add('is-loading');
            target.setAttribute('aria-busy', 'true');

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then((response) => {
                    if (!response.ok) throw new Error('Search request failed');
                    return response.text();
                })
                .then((html) => {
                    if (requestId !== activeRequest) return;

                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextTarget = doc.querySelector(targetSelector);
                    if (!nextTarget) throw new Error('Search target missing');

                    target.innerHTML = nextTarget.innerHTML;
                    window.history.replaceState({}, '', url);
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') window.location.href = url.toString();
                })
                .finally(() => {
                    if (requestId === activeRequest) {
                        target.classList.remove('is-loading');
                        target.setAttribute('aria-busy', 'false');
                    }
                });
        };

        input.addEventListener('input', () => {
            clearButton?.classList.toggle('d-none', input.value.trim() === '');
            window.clearTimeout(timer);
            timer = window.setTimeout(() => renderUrl(buildUrl()), 350);
        });

        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                window.clearTimeout(timer);
                renderUrl(buildUrl());
            });
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(timer);
            renderUrl(buildUrl());
        });

        clearButton?.addEventListener('click', () => {
            input.value = '';
            clearButton.classList.add('d-none');
            renderUrl(buildUrl());
            input.focus();
        });

        target?.addEventListener('click', (event) => {
            const link = event.target.closest('.pagination a');
            if (!link) return;

            event.preventDefault();
            renderUrl(new URL(link.href, window.location.href));
        });
    });

    document.querySelectorAll('[data-date-mask]').forEach((input) => {
        input.addEventListener('input', () => {
            const value = input.value.replace(/[^\d/]/g, '').replace(/\/{2,}/g, '/');

            if (value.includes('/')) {
                const parts = value.split('/').slice(0, 3);
                const day = (parts[0] || '').slice(0, 2);
                let month = parts[1] || '';
                let year = parts[2] || '';

                if (month.length > 2) {
                    year = `${month.slice(2)}${year}`;
                    month = month.slice(0, 2);
                }

                let nextValue = day;
                if (parts.length > 1) nextValue += `/${month.slice(0, 2)}`;
                if (parts.length > 2 || year !== '') nextValue += `/${year.slice(0, 4)}`;

                input.value = nextValue.slice(0, 10);
                return;
            }

            const digits = value.replace(/\D/g, '').slice(0, 8);
            if (digits.length > 4) {
                input.value = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
            } else if (digits.length > 2) {
                input.value = `${digits.slice(0, 2)}/${digits.slice(2)}`;
            } else {
                input.value = digits;
            }
        });
    });

    const dobCrud = document.querySelector('[data-dob-crud]');
    if (dobCrud) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const formModalElement = document.getElementById('dateOfBirthFormModal');
        const viewModalElement = document.getElementById('dateOfBirthViewModal');
        const formModal = formModalElement && window.bootstrap
            ? window.bootstrap.Modal.getOrCreateInstance(formModalElement)
            : null;
        const viewModal = viewModalElement && window.bootstrap
            ? window.bootstrap.Modal.getOrCreateInstance(viewModalElement)
            : null;
        const dobForm = formModalElement?.querySelector('[data-dob-form]');
        const methodInput = dobForm?.querySelector('[data-dob-method]');
        const submitButton = dobForm?.querySelector('[data-dob-submit]');
        const submitLabel = dobForm?.querySelector('[data-dob-submit-label]');
        const modalTitle = dobForm?.querySelector('[data-dob-modal-title]');

        const setField = (field, value = '') => {
            const input = dobForm?.querySelector(`[data-dob-field="${field}"]`);
            if (input) input.value = value || '';
        };

        const clearDobErrors = () => {
            dobForm?.querySelectorAll('.is-invalid').forEach((input) => input.classList.remove('is-invalid'));
            dobForm?.querySelectorAll('[data-dob-error-for]').forEach((feedback) => {
                feedback.textContent = '';
            });
        };

        const showDobErrors = (errors = {}) => {
            Object.entries(errors).forEach(([field, messages]) => {
                const input = dobForm?.querySelector(`[data-dob-field="${field}"]`);
                const feedback = dobForm?.querySelector(`[data-dob-error-for="${field}"]`);
                input?.classList.add('is-invalid');
                if (feedback) feedback.textContent = messages[0] || 'This field is invalid.';
            });
        };

        const setDobSubmitting = (isSubmitting) => {
            dobForm?.classList.toggle('is-submitting', isSubmitting);
            if (submitButton) submitButton.disabled = isSubmitting;
        };

        const refreshDobResults = () => {
            const target = document.getElementById('admin-list-results');
            if (!target) return Promise.resolve();

            target.classList.add('is-loading');
            target.setAttribute('aria-busy', 'true');

            return fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => {
                    if (!response.ok) throw new Error('List refresh failed');
                    return response.text();
                })
                .then((html) => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextTarget = doc.getElementById('admin-list-results');
                    if (!nextTarget) throw new Error('Updated list missing');
                    target.innerHTML = nextTarget.innerHTML;
                })
                .finally(() => {
                    target.classList.remove('is-loading');
                    target.setAttribute('aria-busy', 'false');
                });
        };

        const openCreateDobModal = () => {
            if (!dobForm || !formModal) return;
            clearDobErrors();
            dobForm.reset();
            dobForm.action = dobCrud.dataset.dobStoreUrl;
            if (methodInput) methodInput.disabled = true;
            if (modalTitle) modalTitle.textContent = 'Add Date of Birth';
            if (submitLabel) submitLabel.textContent = 'Save Record';
            formModal.show();
        };

        const openEditDobModal = (trigger) => {
            if (!dobForm || !formModal) return;
            clearDobErrors();
            dobForm.reset();
            dobForm.action = trigger.dataset.dobAction;
            if (methodInput) methodInput.disabled = false;
            setField('name', trigger.dataset.dobName);
            setField('father_name', trigger.dataset.dobFatherName);
            setField('start_date', trigger.dataset.dobStartDate);
            setField('end_date', trigger.dataset.dobEndDate);
            if (modalTitle) modalTitle.textContent = 'Edit Date of Birth';
            if (submitLabel) submitLabel.textContent = 'Update Record';
            formModal.show();
        };

        const setViewText = (selector, value) => {
            const target = viewModalElement?.querySelector(selector);
            if (target) target.textContent = value || 'Present';
        };

        const openViewDobModal = (trigger) => {
            if (!viewModal) return;
            setViewText('[data-dob-view-name]', trigger.dataset.dobName || 'Date of Birth');
            setViewText(
                '[data-dob-view-father]',
                trigger.dataset.dobFatherName ? `Father: ${trigger.dataset.dobFatherName}` : 'Father: Not added'
            );
            setViewText('[data-dob-view-start]', trigger.dataset.dobStartDate);
            setViewText('[data-dob-view-end]', trigger.dataset.dobEndDate || 'Present');
            setViewText('[data-dob-view-age]', trigger.dataset.dobAge);
            setViewText('[data-dob-view-next]', trigger.dataset.dobNextBirthday);
            setViewText('[data-dob-view-countdown]', trigger.dataset.dobNextCountdown);
            viewModal.show();
        };

        document.addEventListener('click', (event) => {
            const createTrigger = event.target.closest?.('[data-dob-open]');
            if (createTrigger) {
                event.preventDefault();
                openCreateDobModal();
                return;
            }

            const editTrigger = event.target.closest?.('[data-dob-edit]');
            if (editTrigger) {
                event.preventDefault();
                openEditDobModal(editTrigger);
                return;
            }

            const viewTrigger = event.target.closest?.('[data-dob-view]');
            if (viewTrigger) {
                event.preventDefault();
                openViewDobModal(viewTrigger);
            }
        });

        dobForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            clearDobErrors();
            setDobSubmitting(true);

            fetch(dobForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(dobForm),
            })
                .then((response) => {
                    if (response.status === 422) {
                        return response.json().then((payload) => {
                            showDobErrors(payload.errors);
                            throw new Error('Validation failed');
                        });
                    }

                    if (!response.ok) throw new Error('Save failed');
                    return response.json();
                })
                .then((payload) => {
                    formModal?.hide();
                    showFlashToast(payload.message || 'Date of birth record saved successfully.');
                    return refreshDobResults();
                })
                .catch((error) => {
                    if (error.message !== 'Validation failed') {
                        showFlashToast('Date of birth record could not be saved.', 'danger');
                    }
                })
                .finally(() => setDobSubmitting(false));
        });

        document.addEventListener('submit', (event) => {
            const deleteForm = event.target.closest?.('[data-dob-delete]');
            if (!deleteForm || event.defaultPrevented) return;

            event.preventDefault();

            fetch(deleteForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(deleteForm),
            })
                .then((response) => {
                    if (!response.ok) throw new Error('Delete failed');
                    return response.json();
                })
                .then((payload) => {
                    showFlashToast(payload.message || 'Date of birth record deleted successfully.');
                    return refreshDobResults();
                })
                .catch(() => showFlashToast('Date of birth record could not be deleted.', 'danger'));
        });
    }

    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.imageInput);
            const file = input.files?.[0];
            if (target && file) {
                target.src = URL.createObjectURL(file);
                target.closest('.preview-frame')?.classList.add('has-image');
            }
        });
    });

    document.querySelectorAll('[data-gallery-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.galleryInput);
            const files = Array.from(input.files || []);
            if (!target) return;

            target.innerHTML = '';
            target.classList.toggle('d-none', files.length === 0);

            files.forEach((file) => {
                const preview = document.createElement('span');
                preview.className = 'selected-gallery-thumb';

                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = file.name;

                preview.appendChild(image);
                target.appendChild(preview);
            });
        });
    });

    document.querySelectorAll('[data-gallery-delete]').forEach((input) => {
        const tile = input.closest('.gallery-delete-tile');
        const updateState = () => tile?.classList.toggle('is-marked', input.checked);

        input.addEventListener('change', updateState);
        updateState();
    });


    // Reusable AJAX CRUD for the finance/program modules. It intentionally reuses
    // the existing Bootstrap modal, toast and list-refresh behaviour.
    const financeCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const firstErrorMessage = (payload, fallback = 'Something went wrong. Please try again.') => {
        const errors = payload?.errors || {};
        const first = Object.values(errors).flat()[0];
        return first || payload?.message || fallback;
    };

    const clearAjaxErrors = (form) => {
        form?.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
        form?.querySelectorAll('[data-error-for]').forEach((node) => { node.textContent = ''; });
    };

    const showAjaxErrors = (form, errors = {}) => {
        Object.entries(errors).forEach(([field, messages]) => {
            const input = form?.querySelector(`[name="${CSS.escape(field)}"]`);
            const feedback = form?.querySelector(`[data-error-for="${CSS.escape(field)}"]`);
            input?.classList.add('is-invalid');
            if (feedback) feedback.textContent = messages?.[0] || 'This field is invalid.';
        });
    };

    const setAjaxSubmitting = (form, busy) => {
        form?.classList.toggle('is-submitting', busy);
        form?.querySelectorAll('[data-submit]').forEach((button) => {
            button.disabled = busy;
            const label = button.querySelector('[data-submit-label]');
            const labelTarget = label || button;
            if (!button.dataset.originalLabel) button.dataset.originalLabel = labelTarget.textContent.trim();
            labelTarget.textContent = busy ? 'Saving...' : button.dataset.originalLabel;
        });
    };

    const financeRoot = () => document.querySelector('[data-ajax-crud]');

    const refreshFinanceTarget = (root = financeRoot()) => {
        const selector = root?.dataset.refreshTarget;
        const target = selector ? document.querySelector(selector) : null;
        if (!target) return Promise.resolve();
        target.classList.add('is-loading');
        target.setAttribute('aria-busy', 'true');
        return fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((response) => {
                if (!response.ok) throw new Error('Refresh failed');
                return response.text();
            })
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.querySelector(selector);
                if (!next) throw new Error('Refresh target missing');
                target.innerHTML = next.innerHTML;

                const statsTarget = document.querySelector('[data-khata-stats]');
                const nextStats = doc.querySelector('[data-khata-stats]');
                if (statsTarget && nextStats) {
                    statsTarget.innerHTML = nextStats.innerHTML;
                }

                initTooltips(target);
            })
            .finally(() => {
                target.classList.remove('is-loading');
                target.setAttribute('aria-busy', 'false');
            });
    };

    const initTooltips = (context = document) => {
        if (!window.bootstrap?.Tooltip) return;
        context.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            window.bootstrap.Tooltip.getOrCreateInstance(el);
        });
    };
    initTooltips();

    document.addEventListener('click', (event) => {
        const create = event.target.closest?.('[data-crud-open]');
        if (create) {
            event.preventDefault();
            const modalElement = document.querySelector(create.dataset.modal || '');
            const form = modalElement?.querySelector('[data-ajax-form]');
            if (!modalElement || !form || !window.bootstrap) return;
            clearAjaxErrors(form);
            form.reset();
            form.action = create.dataset.storeUrl || form.dataset.storeUrl || '';
            const method = form.querySelector('[data-method]');
            if (method) method.disabled = true;
            const title = modalElement.querySelector('[data-modal-title]');
            if (title) {
                if (!title.dataset.createTitle) title.dataset.createTitle = title.textContent.trim();
                title.textContent = title.dataset.createTitle;
            }
            const submitLabel = form.querySelector('[data-submit-label]');
            if (submitLabel && submitLabel.textContent.trim().startsWith('Update')) {
                submitLabel.textContent = submitLabel.textContent.replace(/^Update/, 'Save');
            }
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        const khataQuickTrx = event.target.closest?.('[data-khata-trx-open]');
        if (khataQuickTrx) {
            event.preventDefault();
            const modalElement = document.getElementById('quickKhataTrxModal');
            const form = modalElement?.querySelector('[data-ajax-form]');
            if (!modalElement || !form || !window.bootstrap) return;
            clearAjaxErrors(form);
            form.reset();
            const customerIdInput = document.getElementById('quickTrxCustomerId');
            const customerNameEl = document.getElementById('quickTrxCustomerName');
            const nameLiyeEl = document.getElementById('quickNameLiye');
            const nameDiyeEl = document.getElementById('quickNameDiye');
            const custName = khataQuickTrx.dataset.customerName || 'Customer';
            if (customerIdInput) customerIdInput.value = khataQuickTrx.dataset.customerId || '';
            if (customerNameEl) customerNameEl.textContent = custName;
            if (nameLiyeEl) nameLiyeEl.textContent = custName;
            if (nameDiyeEl) nameDiyeEl.textContent = custName;
            const isDiye = khataQuickTrx.dataset.type === 'pese_diye';
            const radio = document.getElementById(isDiye ? 'quickTypeDiye' : 'quickTypeLiye');
            if (radio) radio.checked = true;
            const dateInput = form.querySelector('input[type="date"]');
            if (dateInput) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        const ledgerTrx = event.target.closest?.('[data-ledger-trx-open]');
        if (ledgerTrx) {
            event.preventDefault();
            const modalElement = document.getElementById('transactionModal');
            const form = modalElement?.querySelector('[data-ajax-form]');
            if (!modalElement || !form || !window.bootstrap) return;
            clearAjaxErrors(form);
            form.reset();
            const method = form.querySelector('[data-method]');
            if (method) method.disabled = true;
            const isDiye = ledgerTrx.dataset.type === 'pese_diye';
            const radio = document.getElementById(isDiye ? 'ledgerTypeDiye' : 'ledgerTypeLiye');
            if (radio) radio.checked = true;
            const title = modalElement.querySelector('[data-modal-title]');
            if (title) {
                if (!title.dataset.createTitle) title.dataset.createTitle = title.textContent.trim();
                title.textContent = title.dataset.createTitle;
            }
            const submitLabel = form.querySelector('[data-submit-label]');
            if (submitLabel && submitLabel.textContent.trim().startsWith('Update')) {
                submitLabel.textContent = submitLabel.textContent.replace(/^Update/, 'Save');
            }
            const dateInput = form.querySelector('input[type="date"]');
            if (dateInput) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        const edit = event.target.closest?.('[data-crud-edit]');
        if (edit) {
            event.preventDefault();
            const modalElement = document.querySelector(edit.dataset.modal || '');
            const form = modalElement?.querySelector('[data-ajax-form]');
            if (!modalElement || !form || !window.bootstrap) return;
            clearAjaxErrors(form);
            form.reset();
            form.action = edit.dataset.action || '';
            const method = form.querySelector('[data-method]');
            if (method) method.disabled = false;
            let record = {};
            try { record = JSON.parse(edit.dataset.record || '{}'); } catch (_) { record = {}; }
            Object.entries(record).forEach(([name, value]) => {
                const fields = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);
                if (fields.length > 1 && fields[0].type === 'radio') {
                    fields.forEach((radio) => {
                        radio.checked = (String(radio.value) === String(value));
                    });
                } else if (fields.length > 0) {
                    fields[0].value = value ?? '';
                }
            });
            const title = modalElement.querySelector('[data-modal-title]');
            if (title) {
                if (!title.dataset.createTitle) title.dataset.createTitle = title.textContent.trim();
                title.textContent = title.dataset.createTitle.replace(/^Add/, 'Edit');
            }
            const submitLabel = form.querySelector('[data-submit-label]');
            if (submitLabel) submitLabel.textContent = submitLabel.textContent.replace(/^Save/, 'Update');
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        const quickPrayerCell = event.target.closest?.('[data-bs-target="#quickPrayerModal"]');
        if (quickPrayerCell) {
            const { userId, userName, date, formattedDate, dayName, prayer, prayerLabel, currentStatus, isManual, hasArrived, statusUrl } = quickPrayerCell.dataset;
            const modalEl = document.getElementById('quickPrayerModal');
            if (modalEl) {
                modalEl.dataset.userId = userId || '';
                modalEl.dataset.date = date || '';
                modalEl.dataset.prayer = prayer || '';
                modalEl.dataset.statusUrl = statusUrl || document.querySelector('[data-status-url]')?.dataset.statusUrl || '/admin/namaz-attendance/status';

                const titleEl = document.getElementById('quickPrayerModalTitle');
                const subtitleEl = document.getElementById('quickPrayerSubtitle');
                const resetContainer = document.getElementById('quickPrayerResetContainer');
                const warningAlert = document.getElementById('quickPrayerTimeNotArrivedAlert');
                const promptText = document.getElementById('quickPrayerPromptText');

                if (titleEl) titleEl.textContent = `${prayerLabel || 'Prayer'} Attendance`;
                if (subtitleEl) subtitleEl.textContent = `${formattedDate || date} (${dayName || ''}) • ${userName || 'Person'}`;
                if (resetContainer) {
                    resetContainer.style.display = isManual === '1' ? 'block' : 'none';
                }

                const arrived = hasArrived === '1';
                if (warningAlert) {
                    warningAlert.style.display = arrived ? 'none' : 'flex';
                }
                if (promptText) {
                    promptText.textContent = arrived ? 'Choose attendance status for this prayer:' : 'Waqt aane ke baad hi status mark kiya ja sakega:';
                }

                modalEl.querySelectorAll('[data-namaz-status-btn]').forEach((btn) => {
                    const btnStatus = btn.dataset.status;
                    if (btnStatus) { // Jamat, Without Jamat, Kaza, Absent
                        btn.disabled = !arrived;
                        btn.style.opacity = arrived ? '1' : '0.45';
                        btn.style.pointerEvents = arrived ? 'auto' : 'none';
                    }
                    if (btnStatus && btnStatus === currentStatus) {
                        btn.classList.add('border-2', 'shadow-sm');
                    } else {
                        btn.classList.remove('border-2', 'shadow-sm');
                    }
                });
            }
        }

        const namazStatusBtn = event.target.closest?.('[data-namaz-status-btn]');
        if (namazStatusBtn) {
            event.preventDefault();
            const modalEl = document.getElementById('quickPrayerModal');
            const userId = modalEl?.dataset.userId;
            const date = modalEl?.dataset.date;
            const prayer = modalEl?.dataset.prayer;
            const status = namazStatusBtn.dataset.status;
            const statusUrl = modalEl?.dataset.statusUrl || document.querySelector('[data-status-url]')?.dataset.statusUrl || '/admin/namaz-attendance/status';

            if (!userId || !date || !prayer) return;

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('attendance_date', date);
            formData.append('prayer', prayer);
            formData.append('status', status || '');
            if (modalEl && window.bootstrap) {
                const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }

            fetch(statusUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: formData,
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw Object.assign(new Error('Status update failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    showFlashToast(payload.message || 'Prayer status updated.');
                    return refreshFinanceTarget(document.querySelector('[data-ajax-crud]'));
                })
                .catch((error) => {
                    showFlashToast(firstErrorMessage(error.payload, 'Could not update prayer status.'), 'danger');
                });
            return;
        }

        const editDayBtn = event.target.closest?.('[data-bs-target="#editDayModal"]');
        if (editDayBtn) {
            const dateInput = document.getElementById('modalDayDate');
            const dateFormatted = document.getElementById('modalDayFormatted');
            if (dateInput) dateInput.value = editDayBtn.dataset.date || '';
            if (dateFormatted) dateFormatted.textContent = editDayBtn.dataset.formattedDate || '';
            ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'].forEach((p) => {
                const select = document.getElementById(`modalStatus_${p}`);
                if (select) select.value = editDayBtn.dataset[p] || '';
            });
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest?.('[data-ajax-form]');
        if (!form) return;
        event.preventDefault();
        clearAjaxErrors(form);
        setAjaxSubmitting(form, true);
        const url = form.getAttribute('action') || form.dataset.storeUrl;
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': financeCsrf },
            body: new FormData(form),
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));
                if (response.status === 422) {
                    showAjaxErrors(form, payload.errors);
                    throw Object.assign(new Error('Validation failed'), { payload, validation: true });
                }
                if (!response.ok) throw Object.assign(new Error('Request failed'), { payload });
                return payload;
            })
            .then((payload) => {
                const modalElement = form.closest('.modal');
                if (form.hasAttribute('data-quick-category') && payload.category) {
                    document.querySelectorAll('[data-category-select]').forEach((select) => {
                        let option = Array.from(select.options).find((item) => String(item.value) === String(payload.category.id));
                        if (!option) {
                            option = new Option(payload.category.name, payload.category.id);
                            select.add(option);
                        }
                        select.value = String(payload.category.id);
                    });
                }
                if (modalElement && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                showFlashToast(payload.message || 'Saved successfully.');
                form.reset();
                if (form.hasAttribute('data-quick-category')) {
                    const expenseModalElement = document.getElementById('expenseModal');
                    window.setTimeout(() => {
                        if (expenseModalElement && !expenseModalElement.classList.contains('show')) {
                            window.bootstrap?.Modal.getOrCreateInstance(expenseModalElement).show();
                        }
                    }, 180);
                    return null;
                }
                return refreshFinanceTarget();
            })
            .catch((error) => {
                if (error.validation) return;
                showFlashToast(firstErrorMessage(error.payload), 'danger');
            })
            .finally(() => setAjaxSubmitting(form, false));
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest?.('[data-ajax-delete]');
        if (!form || event.defaultPrevented) return;
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"], button:not([type])');
        if (submit) submit.disabled = true;
        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': financeCsrf },
            body: new FormData(form),
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw Object.assign(new Error('Delete failed'), { payload });
                return payload;
            })
            .then((payload) => {
                showFlashToast(payload.message || 'Deleted successfully.');
                return refreshFinanceTarget();
            })
            .catch((error) => showFlashToast(firstErrorMessage(error.payload, 'Record could not be deleted.'), 'danger'))
            .finally(() => { if (submit) submit.disabled = false; });
    });

    document.querySelectorAll('.financial-filter[data-live-search]').forEach((form) => {
        form.querySelectorAll('input[type="date"]').forEach((input) => {
            input.addEventListener('change', () => form.requestSubmit());
        });
    });

    const profitSharing = document.querySelector('[data-profit-sharing]');
    if (profitSharing) {
        const form = profitSharing.querySelector('[data-profit-form]');
        const previewButton = profitSharing.querySelector('[data-profit-preview]');
        const confirmButton = profitSharing.querySelector('[data-profit-confirm]');
        const errorBox = profitSharing.querySelector('[data-profit-error]');
        let lastPreview = null;
        const money = (value) => `Rs. ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const render = (payload) => {
            profitSharing.querySelector('[data-net-profit]').textContent = money(payload.net_profit);
            profitSharing.querySelector('[data-investor-profit]').textContent = money(payload.total_investor_profit);
            profitSharing.querySelector('[data-owner-profit]').textContent = money(payload.owner_profit);
            const body = profitSharing.querySelector('[data-profit-allocations]');
            body.innerHTML = payload.allocations?.length
                ? payload.allocations.map((row) => `<tr><td><strong>${row.name}</strong></td><td>${Number(row.percentage).toFixed(2)}%</td><td class="text-end"><strong>${money(row.amount)}</strong></td></tr>`).join('')
                : '<tr><td colspan="3" class="text-center text-muted-custom py-4">No active investors.</td></tr>';
        };
        const sendProfit = (url, confirming = false) => {
            if (!form?.reportValidity()) return;
            errorBox.textContent = '';
            profitSharing.classList.add('is-busy');
            fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': financeCsrf }, body: new FormData(form) })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw Object.assign(new Error('Profit request failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (confirming) {
                        showFlashToast(payload.message || 'Profit sharing confirmed.');
                        lastPreview = null;
                        confirmButton.disabled = true;
                        return refreshFinanceTarget(document.querySelector('[data-ajax-crud]'));
                    }
                    lastPreview = payload;
                    render(payload);
                    confirmButton.disabled = false;
                })
                .catch((error) => { errorBox.textContent = firstErrorMessage(error.payload); })
                .finally(() => profitSharing.classList.remove('is-busy'));
        };
        previewButton?.addEventListener('click', () => sendProfit(profitSharing.dataset.previewUrl, false));
        confirmButton?.addEventListener('click', () => { if (lastPreview) sendProfit(profitSharing.dataset.storeUrl, true); });
        form?.addEventListener('input', () => { lastPreview = null; if (confirmButton) confirmButton.disabled = true; });
    }

    // -------------------------------------------------------------
    // Zikr / Tasbeeh Live Counter & AJAX Handlers
    // -------------------------------------------------------------
    const liveTapBtn = document.getElementById('liveTapButton');
    if (liveTapBtn) {
        let pendingBatchCount = 0;
        let batchTimer = null;
        let isSyncing = false;
        const numberEl = document.getElementById('liveCounterNumber');
        const statCompletedEl = document.getElementById('statTotalCompleted');
        const statBacklogEl = document.getElementById('statBacklog');
        const statBacklogLabel = document.getElementById('statBacklogLabel');
        const statPercentageEl = document.getElementById('statPercentage');
        const displayPercentageEl = document.getElementById('displayPercentage');
        const progressBar = document.getElementById('liveProgressBar');

        const flushBatch = () => {
            if (pendingBatchCount <= 0 || isSyncing) return;
            const countToSend = pendingBatchCount;
            pendingBatchCount = 0;
            isSyncing = true;

            const formData = new FormData();
            formData.append('count', countToSend);
            if (liveTapBtn.dataset.userId) {
                formData.append('user_id', liveTapBtn.dataset.userId);
            }

            fetch(liveTapBtn.dataset.incrementUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
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
                        updateLiveStats(payload.stats);
                    }
                })
                .catch((err) => {
                    console.error('Zikr count sync error:', err);
                    pendingBatchCount += countToSend;
                })
                .finally(() => {
                    isSyncing = false;
                    if (pendingBatchCount > 0) {
                        clearTimeout(batchTimer);
                        batchTimer = setTimeout(flushBatch, 400);
                    }
                });
        };

        const statBacklogBadge = document.getElementById('statBacklogBadge');
        const statBacklogPrefix = document.getElementById('statBacklogPrefix');

        const updateLiveStats = (stats) => {
            if (numberEl) numberEl.textContent = Number(stats.total_completed).toLocaleString();
            if (statCompletedEl) statCompletedEl.textContent = Number(stats.total_completed).toLocaleString();
            if (statPercentageEl) statPercentageEl.textContent = `${stats.percentage}%`;
            if (displayPercentageEl) displayPercentageEl.textContent = `${stats.percentage}%`;
            if (progressBar) {
                progressBar.style.width = `${stats.percentage}%`;
                progressBar.className = `progress-bar ${stats.extra > 0 ? 'bg-info' : (stats.remaining === 0 ? 'bg-success' : 'bg-warning')}`;
            }
            if (statBacklogEl) {
                statBacklogEl.textContent = stats.extra > 0 ? `+${Number(stats.extra).toLocaleString()}` : Number(stats.remaining).toLocaleString();
            }
            if (statBacklogPrefix) {
                statBacklogPrefix.textContent = stats.extra > 0 ? 'Extra' : 'Remaining';
            }
            if (statBacklogBadge) {
                statBacklogBadge.className = `zikr-pill ${stats.extra > 0 ? 'pill-extra' : (stats.remaining > 0 ? 'pill-remaining' : 'pill-done')}`;
            }
            if (statBacklogLabel) {
                statBacklogLabel.textContent = stats.extra > 0 ? 'Ahead of target' : (stats.remaining > 0 ? 'Pending' : 'Completed');
                statBacklogLabel.className = stats.extra > 0 ? 'text-info' : (stats.remaining > 0 ? 'text-warning' : 'text-success');
            }
        };

        liveTapBtn.addEventListener('click', (e) => {
            e.preventDefault();
            pendingBatchCount += 1;

            if (numberEl) {
                const current = parseInt(numberEl.textContent.replace(/,/g, ''), 10) || 0;
                numberEl.textContent = (current + 1).toLocaleString();
                numberEl.classList.remove('number-bump');
                void numberEl.offsetWidth;
                numberEl.classList.add('number-bump');
            }

            liveTapBtn.classList.remove('tap-pulse');
            void liveTapBtn.offsetWidth;
            liveTapBtn.classList.add('tap-pulse');

            clearTimeout(batchTimer);
            batchTimer = setTimeout(flushBatch, 350);
        });

        window.addEventListener('beforeunload', () => {
            if (pendingBatchCount > 0) {
                const formData = new FormData();
                formData.append('count', pendingBatchCount);
                if (liveTapBtn.dataset.userId) formData.append('user_id', liveTapBtn.dataset.userId);
                formData.append('_token', financeCsrf);
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(liveTapBtn.dataset.incrementUrl, formData);
                }
            }
        });
    }

    window.submitManualCount = function (amount) {
        const form = document.getElementById('manualCountForm');
        if (!form) return;
        const input = document.getElementById('manualCountInput');
        if (input) input.value = amount;
        form.requestSubmit();
    };

    const manualCountForm = document.getElementById('manualCountForm');
    if (manualCountForm) {
        manualCountForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = document.getElementById('manualCountSubmitBtn');
            if (btn) btn.disabled = true;

            fetch(manualCountForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: new FormData(manualCountForm),
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Manual add failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    showFlashToast(payload.message || 'Zikr added successfully.');
                    const input = document.getElementById('manualCountInput');
                    if (input) input.value = '';
                    if (payload.stats) {
                        const numberEl = document.getElementById('liveCounterNumber');
                        const statCompletedEl = document.getElementById('statTotalCompleted');
                        const statBacklogEl = document.getElementById('statBacklog');
                        const statBacklogLabel = document.getElementById('statBacklogLabel');
                        const statBacklogBadge = document.getElementById('statBacklogBadge');
                        const statBacklogPrefix = document.getElementById('statBacklogPrefix');
                        const statPercentageEl = document.getElementById('statPercentage');
                        const displayPercentageEl = document.getElementById('displayPercentage');
                        const progressBar = document.getElementById('liveProgressBar');

                        if (numberEl) numberEl.textContent = Number(payload.stats.total_completed).toLocaleString();
                        if (statCompletedEl) statCompletedEl.textContent = Number(payload.stats.total_completed).toLocaleString();
                        if (statPercentageEl) statPercentageEl.textContent = `${payload.stats.percentage}%`;
                        if (displayPercentageEl) displayPercentageEl.textContent = `${payload.stats.percentage}%`;
                        if (progressBar) {
                            progressBar.style.width = `${payload.stats.percentage}%`;
                            progressBar.className = `progress-bar ${payload.stats.extra > 0 ? 'bg-info' : (payload.stats.remaining === 0 ? 'bg-success' : 'bg-warning')}`;
                        }
                        if (statBacklogEl) {
                            statBacklogEl.textContent = payload.stats.extra > 0 ? `+${Number(payload.stats.extra).toLocaleString()}` : Number(payload.stats.remaining).toLocaleString();
                        }
                        if (statBacklogPrefix) {
                            statBacklogPrefix.textContent = payload.stats.extra > 0 ? 'Extra' : 'Remaining';
                        }
                        if (statBacklogBadge) {
                            statBacklogBadge.className = `zikr-pill ${payload.stats.extra > 0 ? 'pill-extra' : (payload.stats.remaining > 0 ? 'pill-remaining' : 'pill-done')}`;
                        }
                        if (statBacklogLabel) {
                            statBacklogLabel.textContent = payload.stats.extra > 0 ? 'Ahead of target' : (payload.stats.remaining > 0 ? 'Pending' : 'Completed');
                            statBacklogLabel.className = payload.stats.extra > 0 ? 'text-info' : (payload.stats.remaining > 0 ? 'text-warning' : 'text-success');
                        }
                    }
                })
                .catch((err) => {
                    showFlashToast(firstErrorMessage(err.payload, 'Could not add zikr count.'), 'danger');
                })
                .finally(() => {
                    if (btn) btn.disabled = false;
                });
        });
    }

    document.addEventListener('click', (e) => {
        const quickAddTrigger = e.target.closest?.('[data-bs-target="#quickAddModal"]');
        if (quickAddTrigger) {
            const { tasbeehId, tasbeehTitle, userId, postUrl } = quickAddTrigger.dataset;
            const form = document.getElementById('quickAddForm');
            const titleEl = document.getElementById('quickAddTasbeehTitle');
            const userInput = document.getElementById('quickAddUserId');
            if (form && postUrl) form.action = postUrl;
            if (titleEl) titleEl.textContent = tasbeehTitle || 'Tasbeeh';
            if (userInput) userInput.value = userId || '';
        }

        const resetTrigger = e.target.closest?.('[data-bs-target="#resetTasbeehModal"]');
        if (resetTrigger) {
            const { tasbeehId, tasbeehTitle, userId, resetUrl } = resetTrigger.dataset;
            const form = document.getElementById('resetTasbeehForm');
            const titleEl = document.getElementById('resetTasbeehTitle');
            const userInput = document.getElementById('resetTasbeehUserId');
            if (form && resetUrl) form.action = resetUrl;
            if (titleEl) titleEl.textContent = tasbeehTitle || 'Tasbeeh';
            if (userInput) userInput.value = userId || '';
        }

        const editTasbeehTrigger = e.target.closest?.('[data-bs-target="#editTasbeehModal"]');
        if (editTasbeehTrigger) {
            const { id, title, arabic, urdu, target, order, active, desc, ref, updateUrl } = editTasbeehTrigger.dataset;
            const form = document.getElementById('editTasbeehForm');
            if (form && updateUrl) form.action = updateUrl;
            const titleInput = document.getElementById('editTasbeehTitle');
            const arabicInput = document.getElementById('editTasbeehArabic');
            const urduInput = document.getElementById('editTasbeehUrdu');
            const targetInput = document.getElementById('editTasbeehTarget');
            const orderInput = document.getElementById('editTasbeehOrder');
            const activeInput = document.getElementById('editTasbeehIsActive');
            const refInput = document.getElementById('editTasbeehRef');

            if (titleInput) titleInput.value = title || '';
            if (arabicInput) arabicInput.value = arabic || '';
            if (urduInput) urduInput.value = urdu || '';
            if (targetInput) targetInput.value = target || 100;
            if (orderInput) orderInput.value = order || 0;
            if (activeInput) activeInput.checked = active === '1';
            if (refInput) refInput.value = ref || '';
        }
    });

    const resetFormHandler = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const modal = form.closest('.modal');
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: new FormData(form),
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Reset failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (modal && window.bootstrap) {
                        window.bootstrap.Modal.getInstance(modal)?.hide();
                    }
                    showFlashToast(payload.message || 'Tracking reset successfully.');
                    setTimeout(() => window.location.reload(), 300);
                })
                .catch((err) => {
                    showFlashToast(firstErrorMessage(err.payload, 'Could not reset tracking.'), 'danger');
                });
        });
    };
    resetFormHandler('resetTasbeehForm');
    resetFormHandler('resetDetailForm');

    const quickAddForm = document.getElementById('quickAddForm');
    if (quickAddForm) {
        quickAddForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const modal = quickAddForm.closest('.modal');
            const btn = document.getElementById('quickAddSubmitBtn');
            if (btn) btn.disabled = true;

            fetch(quickAddForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: new FormData(quickAddForm),
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Quick add failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (modal && window.bootstrap) {
                        window.bootstrap.Modal.getInstance(modal)?.hide();
                    }
                    showFlashToast(payload.message || 'Zikr added successfully.');
                    setTimeout(() => window.location.reload(), 300);
                })
                .catch((err) => {
                    showFlashToast(firstErrorMessage(err.payload, 'Could not add zikr.'), 'danger');
                })
                .finally(() => {
                    if (btn) btn.disabled = false;
                });
        });
    }

    const changeStartDateForm = document.getElementById('changeStartDateForm');
    if (changeStartDateForm) {
        changeStartDateForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const modal = changeStartDateForm.closest('.modal');
            fetch(changeStartDateForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: new FormData(changeStartDateForm),
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Date update failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (modal && window.bootstrap) {
                        window.bootstrap.Modal.getInstance(modal)?.hide();
                    }
                    showFlashToast(payload.message || 'Start date updated.');
                    setTimeout(() => window.location.reload(), 300);
                })
                .catch((err) => {
                    showFlashToast(firstErrorMessage(err.payload, 'Could not update start date.'), 'danger');
                });
        });
    }

})();
