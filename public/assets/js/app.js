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

    window.showFlashToast = showFlashToast;
    window.App = window.App || {};
    window.App.showToast = (type, message) => showFlashToast(message, type);

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

                    const financeBadge = document.querySelector('.finance-badge, [data-finance-total]');
                    const nextFinanceBadge = doc.querySelector('.finance-badge, [data-finance-total]');
                    if (financeBadge && nextFinanceBadge) {
                        financeBadge.innerHTML = nextFinanceBadge.innerHTML;
                    }

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

        const calculateDobStats = (startDateStr) => {
            if (!startDateStr) return { ageText: '—', countdownText: '—', nextBirthdayFormatted: '—', startFormattedShort: '—', startFormattedLong: '—' };
            const birth = new Date(startDateStr);
            const today = new Date();

            let years = today.getFullYear() - birth.getFullYear();
            let months = today.getMonth() - birth.getMonth();
            let days = today.getDate() - birth.getDate();
            if (days < 0) {
                months--;
                days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
            }
            if (months < 0) {
                years--;
                months += 12;
            }

            let nextBday = new Date(today.getFullYear(), birth.getMonth(), birth.getDate());
            if (nextBday < today) {
                nextBday.setFullYear(today.getFullYear() + 1);
            }
            const diffMs = nextBday - today;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            const countdownText = diffDays === 0 ? 'Today' : `In ${diffDays} Day${diffDays > 1 ? 's' : ''}`;
            const nextBirthdayFormatted = nextBday.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            const startFormattedShort = birth.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const startFormattedLong = birth.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

            return {
                ageText: `<strong>${years}</strong> Years, <strong>${months}</strong> Months, <strong>${days}</strong> Days`,
                countdownText,
                nextBirthdayFormatted,
                startFormattedShort,
                startFormattedLong,
            };
        };

        const addDobRowDom = (data) => {
            const tbody = document.querySelector('.table tbody') || document.querySelector('tbody');
            if (!tbody) return;

            const emptyRow = tbody.querySelector('td[colspan]');
            if (emptyRow) emptyRow.closest('tr')?.remove();

            const stats = calculateDobStats(data.start_date);
            const avatarLetter = (data.name || 'D').charAt(0).toUpperCase();

            const tr = document.createElement('tr');
            tr.id = `dob-row-${data.id}`;
            tr.dataset.dobRow = data.id;
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="user-avatar" style="width:34px;height:34px">${avatarLetter}</span>
                        <div>
                            <strong>${data.name || '—'}</strong>
                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"><i class="bi bi-cloud-arrow-up"></i> Offline</span>
                        </div>
                    </div>
                </td>
                <td>${data.father_name || '&mdash;'}</td>
                <td>
                    <strong>${stats.startFormattedShort}</strong>
                    <small class="duration-range d-block">${stats.startFormattedLong}</small>
                </td>
                <td>
                    <span class="duration-pill">${stats.countdownText}</span>
                    <small class="duration-range d-block">Next: ${stats.nextBirthdayFormatted}</small>
                </td>
                <td>
                    ${data.end_date ? data.end_date : '<span class="status-badge live">Present</span>'}
                </td>
                <td>${stats.ageText}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        <button class="btn-icon danger" type="button" onclick="this.closest('tr').remove(); if(window.PwaSync) window.PwaSync.deleteDateOfBirth('${data.id}')" title="Delete record">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.prepend(tr);
        };

        const updateDobRowDom = (id, data) => {
            const tr = document.getElementById(`dob-row-${id}`) || document.querySelector(`[data-dob-row="${id}"]`);
            if (!tr) return;

            const stats = calculateDobStats(data.start_date);
            const avatarLetter = (data.name || 'D').charAt(0).toUpperCase();

            if (tr.cells[0]) {
                tr.cells[0].innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <span class="user-avatar" style="width:34px;height:34px">${avatarLetter}</span>
                        <div>
                            <strong>${data.name || '—'}</strong>
                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"><i class="bi bi-cloud-arrow-up"></i> Offline</span>
                        </div>
                    </div>
                `;
            }
            if (tr.cells[1]) tr.cells[1].innerHTML = data.father_name || '&mdash;';
            if (tr.cells[2]) {
                tr.cells[2].innerHTML = `
                    <strong>${stats.startFormattedShort}</strong>
                    <small class="duration-range d-block">${stats.startFormattedLong}</small>
                `;
            }
            if (tr.cells[3]) {
                tr.cells[3].innerHTML = `
                    <span class="duration-pill">${stats.countdownText}</span>
                    <small class="duration-range d-block">Next: ${stats.nextBirthdayFormatted}</small>
                `;
            }
            if (tr.cells[4]) {
                tr.cells[4].innerHTML = data.end_date ? data.end_date : '<span class="status-badge live">Present</span>';
            }
            if (tr.cells[5]) {
                tr.cells[5].innerHTML = stats.ageText;
            }
        };

        dobForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            clearDobErrors();

            const formData = new FormData(dobForm);
            const name = formData.get('name');
            const fatherName = formData.get('father_name');
            const startDate = formData.get('start_date');
            const endDate = formData.get('end_date');
            const actionUrl = dobForm.action || '';
            const match = actionUrl.match(/date-of-births\/(\d+)/);
            const isUpdate = methodInput && !methodInput.disabled && match;
            const recordId = isUpdate ? parseInt(match[1], 10) : null;

            if (!navigator.onLine) {
                const tempId = recordId || `offline_${Date.now()}`;
                if (window.PwaSync) {
                    if (isUpdate && recordId) {
                        window.PwaSync.updateDateOfBirth(recordId, { name, father_name: fatherName, start_date: startDate, end_date: endDate });
                    } else {
                        window.PwaSync.saveDateOfBirth({ name, father_name: fatherName, start_date: startDate, end_date: endDate });
                    }
                }

                if (isUpdate && recordId) {
                    updateDobRowDom(recordId, { name, father_name: fatherName, start_date: startDate, end_date: endDate });
                } else {
                    addDobRowDom({ id: tempId, name, father_name: fatherName, start_date: startDate, end_date: endDate });
                }

                formModal?.hide();
                showFlashToast('Date of birth record saved offline and displayed!', 'info');
                return;
            }

            setDobSubmitting(true);

            fetch(dobForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
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
                    if (!navigator.onLine && window.PwaSync) {
                        const tempId = recordId || `offline_${Date.now()}`;
                        if (isUpdate && recordId) {
                            window.PwaSync.updateDateOfBirth(recordId, { name, father_name: fatherName, start_date: startDate, end_date: endDate });
                            updateDobRowDom(recordId, { name, father_name: fatherName, start_date: startDate, end_date: endDate });
                        } else {
                            window.PwaSync.saveDateOfBirth({ name, father_name: fatherName, start_date: startDate, end_date: endDate });
                            addDobRowDom({ id: tempId, name, father_name: fatherName, start_date: startDate, end_date: endDate });
                        }
                        formModal?.hide();
                        showFlashToast('Date of birth record saved offline and displayed!', 'info');
                    } else if (error.message !== 'Validation failed') {
                        showFlashToast('Date of birth record could not be saved.', 'danger');
                    }
                })
                .finally(() => setDobSubmitting(false));
        });

        document.addEventListener('submit', (event) => {
            const deleteForm = event.target.closest?.('[data-dob-delete]');
            if (!deleteForm || event.defaultPrevented) return;

            event.preventDefault();
            const actionUrl = deleteForm.action || '';
            const match = actionUrl.match(/date-of-births\/(\d+)/);
            const recordId = match ? parseInt(match[1], 10) : null;

            if (!navigator.onLine) {
                if (window.PwaSync && recordId) {
                    window.PwaSync.deleteDateOfBirth(recordId);
                }
                const row = deleteForm.closest('tr') || deleteForm.closest('.dob-card');
                if (row) row.remove();
                showFlashToast('Date of birth deletion saved offline.', 'info');
                return;
            }

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
                .catch(() => {
                    if (!navigator.onLine && window.PwaSync && recordId) {
                        window.PwaSync.deleteDateOfBirth(recordId);
                        const row = deleteForm.closest('tr') || deleteForm.closest('.dob-card');
                        if (row) row.remove();
                        showFlashToast('Date of birth deletion saved offline.', 'info');
                    } else {
                        showFlashToast('Date of birth record could not be deleted.', 'danger');
                    }
                });
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

                const financeBadge = document.querySelector('.finance-badge, [data-finance-total]');
                const nextFinanceBadge = doc.querySelector('.finance-badge, [data-finance-total]');
                if (financeBadge && nextFinanceBadge) {
                    financeBadge.innerHTML = nextFinanceBadge.innerHTML;
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

    function updateNamazCellDom(userId, date, prayer, status) {
        const selector = `[data-bs-target="#quickPrayerModal"][data-user-id="${userId}"][data-date="${date}"][data-prayer="${prayer}"]`;
        const btn = document.querySelector(selector);
        if (!btn) return;

        btn.dataset.currentStatus = status || '';
        btn.dataset.isManual = status ? '1' : '0';

        const pill = btn.querySelector('.namaz-status-pill');
        if (!pill) return;

        const metaMap = {
            'jamat': { label: 'Jamat', icon: 'bi bi-check-circle-fill' },
            'without_jamat': { label: 'Without Jamat', icon: 'bi bi-person-fill' },
            'kaza': { label: 'Kaza', icon: 'bi bi-clock-history' },
            'absent': { label: 'Absent', icon: 'bi bi-x-circle-fill' },
        };
        const meta = metaMap[status] || { label: 'Pending', icon: null };

        pill.className = `namaz-status-pill ${status || 'pending'}`;
        let html = '';
        if (meta.icon) {
            html += `<span class="status-icon"><i class="${meta.icon}"></i></span>`;
        }
        html += `<span class="status-label">${meta.label}</span>`;
        if (status) {
            html += `<span class="manual-dot" title="Recorded"><i class="bi bi-check2"></i></span>`;
        }
        pill.innerHTML = html;
    }

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

            // Immediately update visual table cell for instant feedback
            updateNamazCellDom(userId, date, prayer, status);

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('attendance_date', date);
            formData.append('prayer', prayer);
            formData.append('status', status || '');
            if (modalEl && window.bootstrap) {
                const modalInstance = window.bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }

            if (!navigator.onLine) {
                if (window.PwaSync && typeof window.PwaSync.updateNamazStatus === 'function') {
                    window.PwaSync.updateNamazStatus(userId, date, prayer, status);
                }
                showFlashToast('Prayer status saved offline. Will sync once online.', 'info');
                return;
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
                    if (!navigator.onLine && window.PwaSync && typeof window.PwaSync.updateNamazStatus === 'function') {
                        window.PwaSync.updateNamazStatus(userId, date, prayer, status);
                        showFlashToast('Prayer status saved offline. Will sync once online.', 'info');
                    } else {
                        showFlashToast(firstErrorMessage(error.payload, 'Could not update prayer status.'), 'danger');
                    }
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
        const url = form.getAttribute('action') || form.dataset.storeUrl || '';

        // Offline Day Attendance Interception
        if (!navigator.onLine && url.includes('/namaz-attendance/day')) {
            const formData = new FormData(form);
            const userId = formData.get('user_id');
            const date = formData.get('attendance_date');
            const statuses = {
                fajr: formData.get('fajr_status') || '',
                zuhr: formData.get('zuhr_status') || '',
                asr: formData.get('asr_status') || '',
                maghrib: formData.get('maghrib_status') || '',
                isha: formData.get('isha_status') || '',
            };

            if (window.PwaSync && typeof window.PwaSync.enqueueAction === 'function') {
                window.PwaSync.enqueueAction('namaz_attendance_day', 'update', {
                    user_id: parseInt(userId, 10),
                    attendance_date: date,
                    fajr_status: statuses.fajr,
                    zuhr_status: statuses.zuhr,
                    asr_status: statuses.asr,
                    maghrib_status: statuses.maghrib,
                    isha_status: statuses.isha,
                });
            }

            // Immediately update visual table cells
            ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'].forEach((p) => {
                updateNamazCellDom(userId, date, p, statuses[p]);
            });

            const modalElement = form.closest('.modal');
            if (modalElement && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            showFlashToast('Day attendance saved offline. Will sync once online.', 'info');
            setAjaxSubmitting(form, false);
            return;
        }
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
                if (form.hasAttribute('data-quick-city') && payload.city) {
                    document.querySelectorAll('[data-city-select]').forEach((select) => {
                        let option = Array.from(select.options).find((item) => String(item.value) === String(payload.city.id));
                        if (!option) {
                            option = new Option(payload.city.name, payload.city.id);
                            select.add(option);
                        }
                        select.value = String(payload.city.id);
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    const cityOptionsContainer = document.getElementById('citySelectOptions');
                    if (cityOptionsContainer) {
                        const existingOpt = Array.from(cityOptionsContainer.querySelectorAll('.searchable-option')).find((el) => String(el.dataset.value) === String(payload.city.id));
                        if (!existingOpt) {
                            const newOpt = document.createElement('div');
                            newOpt.className = 'searchable-option';
                            newOpt.dataset.value = payload.city.id;
                            newOpt.dataset.text = payload.city.name;
                            newOpt.innerHTML = `<span>${payload.city.name}</span>`;
                            cityOptionsContainer.appendChild(newOpt);
                        }
                    }
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
                if (form.hasAttribute('data-quick-city')) {
                    const contributionModalElement = document.getElementById('contributionModal');
                    window.setTimeout(() => {
                        if (contributionModalElement && !contributionModalElement.classList.contains('show')) {
                            window.bootstrap?.Modal.getOrCreateInstance(contributionModalElement).show();
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
                            if (payload.stats.extra > 0) {
                                progressBar.style.background = 'linear-gradient(90deg, #00bcd4 0%, #00e5ff 100%)';
                                progressBar.style.boxShadow = '0 0 12px rgba(0, 229, 255, 0.7)';
                            } else if (payload.stats.remaining === 0) {
                                progressBar.style.background = 'linear-gradient(90deg, #10b981 0%, #059669 100%)';
                                progressBar.style.boxShadow = '0 0 12px rgba(16, 185, 129, 0.7)';
                            } else {
                                progressBar.style.background = 'linear-gradient(90deg, #f59e0b 0%, #d97706 100%)';
                                progressBar.style.boxShadow = '0 0 12px rgba(245, 158, 11, 0.7)';
                            }
                        }
                        if (statBacklogEl) {
                            statBacklogEl.textContent = payload.stats.extra > 0 ? `+${Number(payload.stats.extra).toLocaleString()}` : Number(payload.stats.remaining).toLocaleString();
                        }
                        if (statBacklogPrefix) {
                            statBacklogPrefix.textContent = payload.stats.extra > 0 ? 'Extra:' : 'Remaining:';
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
            const countInput = document.getElementById('quickAddCountInput');
            if (form) {
                if (postUrl) form.action = postUrl;
                if (tasbeehId) form.dataset.tasbeehId = tasbeehId;
            }
            if (titleEl) titleEl.textContent = tasbeehTitle || 'Tasbeeh';
            if (userInput) userInput.value = userId || '';
            if (countInput) {
                countInput.value = '';
                setTimeout(() => countInput.focus(), 150);
            }
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

    window.recalculateZikrTopStats = function () {
        let overallTotalCompleted = 0;
        let overallTotalRequired = 0;
        let overallTodayCompleted = 0;
        let overallTodayRequired = 0;
        let cards = document.querySelectorAll('[id^="tasbeeh-card-"]');

        cards.forEach(card => {
            let completedEl = card.querySelector('.badge-completed strong');
            let badgeCompletedText = card.querySelector('.badge-completed')?.textContent || '';
            let matchReq = badgeCompletedText.match(/\/\s*([0-9,]+)/);

            let completed = completedEl ? (parseInt(completedEl.textContent.replace(/,/g, ''), 10) || 0) : 0;
            let required = matchReq ? (parseInt(matchReq[1].replace(/,/g, ''), 10) || 0) : 0;

            let dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10);
            let activeDays = parseInt(card.dataset.activeDays || '1', 10);
            let priorRequired = Math.max(activeDays - 1, 0) * dailyTarget;
            let todayCompleted = Math.max(completed - priorRequired, 0);

            overallTodayCompleted += todayCompleted;
            overallTodayRequired += dailyTarget;
            overallTotalCompleted += completed;
            overallTotalRequired += required;
        });

        let todayCompletedStatEl = document.getElementById('top-stat-today-completed');
        let todayPercentStatEl = document.getElementById('top-stat-today-percentage');
        let completedStatEl = document.getElementById('top-stat-total-completed');
        let percentStatEl = document.getElementById('top-stat-overall-percentage');
        let backlogContainerEl = document.getElementById('top-stat-backlog-container');

        if (todayCompletedStatEl) {
            todayCompletedStatEl.textContent = overallTodayCompleted.toLocaleString();
        }

        if (todayPercentStatEl) {
            let todayPercent = overallTodayRequired > 0 ? Math.min(100, Math.round((overallTodayCompleted / overallTodayRequired) * 100)) : 100;
            todayPercentStatEl.textContent = `${todayPercent}% of daily target`;
        }

        if (completedStatEl) {
            completedStatEl.textContent = overallTotalCompleted.toLocaleString();
        }

        let percentage = overallTotalRequired > 0 ? Math.min(100, Math.round((overallTotalCompleted / overallTotalRequired) * 100)) : 100;
        if (percentStatEl) {
            percentStatEl.textContent = `${percentage}% Completed`;
        }

        if (backlogContainerEl) {
            let diff = overallTotalCompleted - overallTotalRequired;
            if (diff > 0) {
                backlogContainerEl.innerHTML = `
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Extra Zikr</span>
                    <strong class="fs-3 fs-md-2 text-info d-block font-monospace my-0" style="line-height: 1.2;">+${diff.toLocaleString()}</strong>
                    <small class="text-info d-block fw-semibold text-truncate" style="font-size: 0.72rem;">Ahead of schedule</small>
                `;
            } else {
                let backlog = Math.abs(diff);
                let colorClass = backlog > 0 ? 'text-warning' : 'text-success';
                let label = backlog > 0 ? 'Behind schedule' : 'On track';
                backlogContainerEl.innerHTML = `
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Remaining Backlog</span>
                    <strong class="fs-3 fs-md-2 ${colorClass} d-block font-monospace my-0" style="line-height: 1.2;">${backlog.toLocaleString()}</strong>
                    <small class="${colorClass} d-block fw-semibold text-truncate" style="font-size: 0.72rem;">${label}</small>
                `;
            }
        }
    };

    window.updateZikrCardDom = function (tasbeehId, countDelta, isAbsolute = false) {
        const cardCol = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
        if (!cardCol) return;

        let completedEl = cardCol.querySelector('.badge-completed strong');
        let remainingEl = cardCol.querySelector('.badge-remaining');
        let progressEl = cardCol.querySelector('.progress-bar-custom');
        let percentTextEl = cardCol.querySelector('.progress-container')?.parentElement?.querySelector('.font-monospace');

        let rawCompletedText = completedEl ? completedEl.textContent.replace(/,/g, '') : '0';
        let currentCompleted = parseInt(rawCompletedText, 10) || 0;

        let totalRequired = 0;
        let badgeCompletedText = cardCol.querySelector('.badge-completed')?.textContent || '';
        let matchReq = badgeCompletedText.match(/\/\s*([0-9,]+)/);
        if (matchReq) {
            totalRequired = parseInt(matchReq[1].replace(/,/g, ''), 10) || 0;
        }

        let deltaAdded = isAbsolute ? (countDelta - currentCompleted) : countDelta;
        let newCompleted = isAbsolute ? countDelta : currentCompleted + countDelta;
        if (newCompleted < 0) newCompleted = 0;

        if (completedEl) {
            completedEl.textContent = newCompleted.toLocaleString();
        }

        // Live Real-Time Lifetime Total Counter Update
        if (deltaAdded > 0) {
            let lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl) {
                let currentLifetime = parseInt(lifetimeEl.textContent.replace(/,/g, ''), 10) || 0;
                lifetimeEl.textContent = (currentLifetime + deltaAdded).toLocaleString();
            }
        }

        if (totalRequired > 0) {
            let percentage = Math.min(100, Math.round((newCompleted / totalRequired) * 100));
            if (percentTextEl) percentTextEl.textContent = `${percentage}%`;

            let diff = newCompleted - totalRequired;
            if (remainingEl) {
                if (diff > 0) {
                    remainingEl.className = 'badge-remaining extra';
                    remainingEl.textContent = `+${diff.toLocaleString()} Extra`;
                } else if (diff === 0) {
                    remainingEl.className = 'badge-remaining completed-badge';
                    remainingEl.textContent = 'Completed';
                } else {
                    remainingEl.className = 'badge-remaining';
                    remainingEl.textContent = `Remaining ${(totalRequired - newCompleted).toLocaleString()}`;
                }
            }

            if (progressEl) {
                progressEl.style.width = `${percentage}%`;
                let barClass = 'amber';
                if (diff > 0) {
                    barClass = 'cyan';
                } else if (diff === 0) {
                    barClass = 'emerald';
                }
                progressEl.className = `progress-bar-custom ${barClass}`;
            }
        }

        if (typeof window.recalculateZikrTopStats === 'function') {
            window.recalculateZikrTopStats();
        }
    };

    // Reconcile pending offline counts on Zikr dashboard cards
    if (window.PwaDB && typeof window.PwaDB.getPendingOutbox === 'function') {
        window.PwaDB.getPendingOutbox().then(items => {
            const countByTasbeeh = {};
            (items || []).forEach(item => {
                if ((item.entity === 'tasbeeh_count' || item.entity === 'zikr_count') && item.payload?.tasbeeh_id) {
                    const tId = item.payload.tasbeeh_id;
                    countByTasbeeh[tId] = (countByTasbeeh[tId] || 0) + (parseInt(item.payload.count, 10) || 0);
                }
            });
            Object.entries(countByTasbeeh).forEach(([tId, count]) => {
                if (count > 0 && typeof window.updateZikrCardDom === 'function') {
                    window.updateZikrCardDom(tId, count);
                }
            });
        }).catch(() => {});
    }

    const resetFormHandler = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const modal = form.closest('.modal');
            const actionUrl = form.action || '';
            const match = actionUrl.match(/counter\/(\d+)/);
            const tasbeehId = match ? match[1] : null;

            if (!navigator.onLine) {
                if (window.PwaSync && tasbeehId) {
                    window.PwaSync.resetTasbeeh(tasbeehId);
                }
                if (tasbeehId) {
                    window.updateZikrCardDom(tasbeehId, 0, true);
                }
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getInstance(modal)?.hide();
                }
                showFlashToast('Tracking reset saved offline and updated on screen.', 'info');
                return;
            }

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
                    if (tasbeehId) {
                        window.updateZikrCardDom(tasbeehId, 0, true);
                    }
                    showFlashToast(payload.message || 'Tracking reset successfully.');
                })
                .catch((err) => {
                    if (!navigator.onLine && window.PwaSync && tasbeehId) {
                        window.PwaSync.resetTasbeeh(tasbeehId);
                        if (tasbeehId) {
                            window.updateZikrCardDom(tasbeehId, 0, true);
                        }
                        if (modal && window.bootstrap) {
                            window.bootstrap.Modal.getInstance(modal)?.hide();
                        }
                        showFlashToast('Tracking reset saved offline and updated on screen.', 'info');
                    } else {
                        showFlashToast(firstErrorMessage(err.payload, 'Could not reset tracking.'), 'danger');
                    }
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

            const formData = new FormData(quickAddForm);
            const countVal = parseInt(formData.get('count'), 10);
            const actionUrl = quickAddForm.action || '';
            const match = actionUrl.match(/counter\/(\d+)/);
            const tasbeehId = quickAddForm.dataset.tasbeehId || (match ? match[1] : null);

            if (!navigator.onLine) {
                if (window.PwaSync && tasbeehId) {
                    window.PwaSync.saveZikrCount(tasbeehId, countVal);
                }
                if (tasbeehId && !isNaN(countVal)) {
                    window.updateZikrCardDom(tasbeehId, countVal);
                }
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getInstance(modal)?.hide();
                }
                const countInput = document.getElementById('quickAddCountInput');
                if (countInput) countInput.value = '';
                if (btn) btn.disabled = false;
                showFlashToast('Zikr count saved offline and updated on screen!', 'info');
                return;
            }

            fetch(quickAddForm.action, {
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
                    if (!res.ok) throw Object.assign(new Error('Quick add failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (modal && window.bootstrap) {
                        window.bootstrap.Modal.getInstance(modal)?.hide();
                    }
                    if (tasbeehId && !isNaN(countVal)) {
                        window.updateZikrCardDom(tasbeehId, countVal);
                    }
                    const countInput = document.getElementById('quickAddCountInput');
                    if (countInput) countInput.value = '';
                    showFlashToast(payload.message || 'Zikr added successfully.', 'success');
                })
                .catch((err) => {
                    if (!navigator.onLine && window.PwaSync && tasbeehId) {
                        window.PwaSync.saveZikrCount(tasbeehId, countVal);
                        if (tasbeehId && !isNaN(countVal)) {
                            window.updateZikrCardDom(tasbeehId, countVal);
                        }
                        if (modal && window.bootstrap) {
                            window.bootstrap.Modal.getInstance(modal)?.hide();
                        }
                        showFlashToast('Zikr count saved offline and updated on screen!', 'info');
                    } else {
                        showFlashToast(firstErrorMessage(err.payload, 'Could not add zikr.'), 'danger');
                    }
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
                })
                .catch((err) => {
                    showFlashToast(firstErrorMessage(err.payload, 'Could not update start date.'), 'danger');
                });
        });
    }

    // -------------------------------------------------------------
    // Zikr & Tasbeeh Display Settings (Integer Numbers & DB Sync)
    // -------------------------------------------------------------
    const ARABIC_SIZE_KEY = 'zikr_pref_arabic_size_px';
    const URDU_SIZE_KEY = 'zikr_pref_urdu_size_px';
    const ARABIC_SHOW_KEY = 'zikr_pref_show_arabic';
    const URDU_SHOW_KEY = 'zikr_pref_show_urdu';

    const DEFAULT_ARABIC_SIZE = 24; // clean number 24
    const DEFAULT_URDU_SIZE = 16;   // clean number 16

    let zikrSyncTimeout = null;

    function getZikrCurrentSettings() {
        const modalEl = document.getElementById('zikrSettingsModal');
        const dbArabic = modalEl ? parseInt(modalEl.dataset.dbArabicSize, 10) : null;
        const dbUrdu = modalEl ? parseInt(modalEl.dataset.dbUrduSize, 10) : null;
        const dbShowArabic = modalEl ? modalEl.dataset.dbShowArabic === '1' : null;
        const dbShowUrdu = modalEl ? modalEl.dataset.dbShowUrdu === '1' : null;

        const rawArabic = localStorage.getItem(ARABIC_SIZE_KEY);
        const rawUrdu = localStorage.getItem(URDU_SIZE_KEY);

        const arabicSize = rawArabic !== null ? parseInt(rawArabic, 10) : (dbArabic || DEFAULT_ARABIC_SIZE);
        const urduSize = rawUrdu !== null ? parseInt(rawUrdu, 10) : (dbUrdu || DEFAULT_URDU_SIZE);

        const rawShowArabic = localStorage.getItem(ARABIC_SHOW_KEY);
        const rawShowUrdu = localStorage.getItem(URDU_SHOW_KEY);

        const showArabic = rawShowArabic !== null ? rawShowArabic !== 'false' : (dbShowArabic !== null ? dbShowArabic : true);
        const showUrdu = rawShowUrdu !== null ? rawShowUrdu !== 'false' : (dbShowUrdu !== null ? dbShowUrdu : true);

        return { arabicSize, urduSize, showArabic, showUrdu };
    }

    function applyZikrDisplaySettings() {
        const { arabicSize, urduSize, showArabic, showUrdu } = getZikrCurrentSettings();

        document.documentElement.style.setProperty('--zikr-arabic-size', `${arabicSize}px`);
        document.documentElement.style.setProperty('--zikr-urdu-size', `${urduSize}px`);

        if (showArabic) {
            document.body.classList.remove('hide-arabic-text');
        } else {
            document.body.classList.add('hide-arabic-text');
        }

        if (showUrdu) {
            document.body.classList.remove('hide-urdu-text');
        } else {
            document.body.classList.add('hide-urdu-text');
        }

        // Direct DOM updates for immediate guaranteed live rendering
        document.querySelectorAll('.arabic-text, .arabic-live-text').forEach((el) => {
            el.style.setProperty('font-size', `${arabicSize}px`, 'important');
            el.style.display = showArabic ? '' : 'none';
        });

        document.querySelectorAll('.urdu-text, .urdu-live-text').forEach((el) => {
            el.style.setProperty('font-size', `${urduSize}px`, 'important');
            el.style.display = showUrdu ? '' : 'none';
        });

        document.querySelectorAll('.islamic-divider, .divider-box').forEach((el) => {
            el.style.display = (showArabic && showUrdu) ? '' : 'none';
        });

        document.querySelectorAll('.text-container, .arabic-box').forEach((el) => {
            el.style.display = (!showArabic && !showUrdu) ? 'none' : '';
        });

        // Sync inputs in modal if present
        const arabicValEl = document.getElementById('settingArabicSizeVal');
        if (arabicValEl) arabicValEl.textContent = `${arabicSize}`;

        const urduValEl = document.getElementById('settingUrduSizeVal');
        if (urduValEl) urduValEl.textContent = `${urduSize}`;

        const arabicSwitch = document.getElementById('settingShowArabicSwitch');
        if (arabicSwitch) arabicSwitch.checked = showArabic;

        const urduSwitch = document.getElementById('settingShowUrduSwitch');
        if (urduSwitch) urduSwitch.checked = showUrdu;
    }

    function syncZikrSettingsToDatabase() {
        const modalEl = document.getElementById('zikrSettingsModal');
        if (!modalEl || !modalEl.dataset.settingsUrl) return;

        const { arabicSize, urduSize, showArabic, showUrdu } = getZikrCurrentSettings();

        clearTimeout(zikrSyncTimeout);
        zikrSyncTimeout = setTimeout(() => {
            fetch(modalEl.dataset.settingsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': financeCsrf,
                },
                body: JSON.stringify({
                    zikr_arabic_size: arabicSize,
                    zikr_urdu_size: urduSize,
                    zikr_show_arabic: showArabic ? 1 : 0,
                    zikr_show_urdu: showUrdu ? 1 : 0,
                }),
            }).catch(() => {});
        }, 300);
    }

    window.adjustZikrFontSize = function (type, delta) {
        const currentSettings = getZikrCurrentSettings();
        if (type === 'arabic') {
            let current = currentSettings.arabicSize + delta;
            current = Math.min(Math.max(current, 14), 48);
            localStorage.setItem(ARABIC_SIZE_KEY, current);
        } else if (type === 'urdu') {
            let current = currentSettings.urduSize + delta;
            current = Math.min(Math.max(current, 10), 32);
            localStorage.setItem(URDU_SIZE_KEY, current);
        }
        applyZikrDisplaySettings();
        syncZikrSettingsToDatabase();
    };

    window.resetZikrFontSize = function (type) {
        if (type === 'arabic') {
            localStorage.setItem(ARABIC_SIZE_KEY, DEFAULT_ARABIC_SIZE);
        } else if (type === 'urdu') {
            localStorage.setItem(URDU_SIZE_KEY, DEFAULT_URDU_SIZE);
        }
        applyZikrDisplaySettings();
        syncZikrSettingsToDatabase();
    };

    window.toggleZikrVisibility = function (type, isVisible) {
        if (type === 'arabic') {
            localStorage.setItem(ARABIC_SHOW_KEY, isVisible ? 'true' : 'false');
        } else if (type === 'urdu') {
            localStorage.setItem(URDU_SHOW_KEY, isVisible ? 'true' : 'false');
        }
        applyZikrDisplaySettings();
        syncZikrSettingsToDatabase();
    };

    // Apply immediately and on DOM ready
    applyZikrDisplaySettings();
    document.addEventListener('DOMContentLoaded', applyZikrDisplaySettings);

    const zikrSettingsModalEl = document.getElementById('zikrSettingsModal');
    if (zikrSettingsModalEl) {
        zikrSettingsModalEl.addEventListener('show.bs.modal', applyZikrDisplaySettings);
    }

})();
