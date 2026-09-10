(() => {
    'use strict';

    const root = document.documentElement;
    const storedTheme = localStorage.getItem('portfolio-theme');
    root.dataset.theme = storedTheme === 'light' ? 'light' : 'dark';

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

        let timer = null;
        let remaining = duration;
        let startTime = Date.now();

        const hideToast = () => {
            if (toast.classList.contains('is-hiding')) return;
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 260);
        };

        const startTimer = (ms) => {
            clearTimeout(timer);
            startTime = Date.now();
            timer = window.setTimeout(hideToast, ms);
        };

        startTimer(remaining);

        toast.addEventListener('mouseenter', () => {
            clearTimeout(timer);
            const elapsed = Date.now() - startTime;
            remaining = Math.max(remaining - elapsed, 400);
            const progress = toast.querySelector('.flash-toast-progress');
            if (progress) {
                progress.style.animationPlayState = 'paused';
            }
        });

        toast.addEventListener('mouseleave', () => {
            const progress = toast.querySelector('.flash-toast-progress');
            if (progress) {
                progress.style.animationPlayState = 'running';
            }
            startTimer(remaining);
        });

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

    document.querySelectorAll('[data-searchable-select]').forEach((wrapper) => {
        const toggle = wrapper.querySelector('.searchable-select-toggle');
        const selectedText = wrapper.querySelector('.selected-text');
        const searchInput = wrapper.querySelector('.searchable-select-input');
        const options = Array.from(wrapper.querySelectorAll('.searchable-option-item'));
        const noResults = wrapper.querySelector('.searchable-no-results');
        const clearBtn = wrapper.querySelector('[data-clear-selection]');
        const hiddenSelect = wrapper.querySelector('select');

        const filterOptions = (term) => {
            const query = (term || '').toLowerCase().trim();
            let visibleCount = 0;

            options.forEach((opt) => {
                const text = (opt.textContent || '').toLowerCase();
                const matches = query === '' || text.includes(query);
                opt.classList.toggle('d-none', !matches);
                if (matches) visibleCount++;
            });

            if (noResults) {
                noResults.classList.toggle('d-none', visibleCount > 0);
            }
        };

        wrapper.addEventListener('shown.bs.dropdown', () => {
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                searchInput.focus();
            }
        });

        searchInput?.addEventListener('input', (e) => {
            filterOptions(e.target.value);
        });

        const selectValue = (value, labelText) => {
            if (hiddenSelect) {
                hiddenSelect.value = value;
                hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            options.forEach((opt) => {
                const isActive = opt.dataset.value === value;
                opt.classList.toggle('active', isActive);
            });

            if (selectedText) {
                selectedText.textContent = value ? `Father: ${labelText || value}` : 'All Fathers';
            }

            if (clearBtn) {
                clearBtn.classList.toggle('d-none', !value);
            }

            const bsDropdown = window.bootstrap?.Dropdown?.getInstance(toggle);
            bsDropdown?.hide();
        };

        options.forEach((opt) => {
            opt.addEventListener('click', (e) => {
                e.preventDefault();
                const value = opt.dataset.value || '';
                const labelText = opt.dataset.label || opt.textContent.trim();
                selectValue(value, labelText);
            });
        });

        clearBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            selectValue('', 'All Fathers');
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

    // Universal Micro-Animation Handler for Action Icon Buttons (0ms Instant Tactile Feedback)
    document.addEventListener('pointerdown', (e) => {
        const btn = e.target.closest?.('.action-icon-btn');
        if (!btn) return;
        btn.classList.remove('btn-click-anim');
        void btn.offsetWidth; // Reflow to restart keyframe animation on rapid repeated clicks
        btn.classList.add('btn-click-anim');
    }, { passive: true });

    document.addEventListener('animationend', (e) => {
        if (e.target?.classList?.contains('btn-click-anim')) {
            e.target.classList.remove('btn-click-anim');
        }
    });

    document.addEventListener('click', (event) => {
        // Direct 1-Click Complete for Individual Tasbeeh (Instant 0ms Visual & Robust Offline/Online Sync)
        const completeIconBtn = event.target.closest?.('.btn-complete-icon');
        if (completeIconBtn) {
            event.preventDefault();
            const tasbeehId = completeIconBtn.getAttribute('data-tasbeeh-id');
            const completeUrl = completeIconBtn.getAttribute('data-complete-url');
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('user_id') || completeIconBtn.getAttribute('data-user-id') || '';
            const title = completeIconBtn.getAttribute('data-tasbeeh-title') || 'Tasbeeh';

            if (tasbeehId) {
                const card = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
                let dailyTarget = parseInt(card?.dataset?.dailyTarget || '100', 10);
                if (dailyTarget <= 0) dailyTarget = 100;

                let currentToday = parseInt(card?.dataset?.todayCompleted || '0', 10);
                const isAlreadyComplete = currentToday >= dailyTarget && dailyTarget > 0;

                let countDelta = 0;
                let toastMsg = '';

                if (isAlreadyComplete) {
                    // Toggle Off: Remove / Minus today's completed count
                    const removeCount = Math.min(currentToday, dailyTarget) || dailyTarget;
                    countDelta = -removeCount;
                    toastMsg = `-${removeCount.toLocaleString()} removed for '${title}'!`;
                    completeIconBtn.classList.remove('is-completed', 'active');
                } else {
                    // Toggle On: Add remaining count to complete today
                    const addCount = Math.max(1, dailyTarget - currentToday);
                    countDelta = addCount;
                    toastMsg = `+${addCount.toLocaleString()} completed for '${title}'!`;
                    completeIconBtn.classList.add('is-completed', 'active');
                }

                // Immediate 0ms local visual update on screen
                if (typeof window.updateZikrCardDom === 'function') {
                    window.updateZikrCardDom(tasbeehId, countDelta, false);
                }

                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(toastMsg, isAlreadyComplete ? 'info' : 'success');
                } else if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast(isAlreadyComplete ? 'info' : 'success', toastMsg);
                }

                if (!navigator.onLine) {
                    // Queue offline action & broadcast event across tabs
                    if (window.PwaSync && typeof window.PwaSync.completeTasbeehToday === 'function') {
                        window.PwaSync.completeTasbeehToday(tasbeehId);
                    }
                } else if (completeUrl) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch(completeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify({ user_id: userId })
                    }).then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw Object.assign(new Error('Complete failed'), { data });
                        return data;
                    }).then(data => {
                        if (data && data.success && card) {
                            if (data.summary && typeof window.reconcileZikrOfflineCounts === 'function') {
                                window.reconcileZikrOfflineCounts({ zikr_summary: data.summary, server_time: data.server_time });
                            } else if (data.stats) {
                                const sToday = data.stats.today_completed !== undefined ? data.stats.today_completed : (data.stats.total_completed ?? 0);
                                const sTotal = data.stats.total_completed !== undefined ? data.stats.total_completed : 0;
                                card.dataset.baseTodayCompleted = String(sToday);
                                card.dataset.baseTotalCompleted = String(sTotal);
                                card.dataset.todayCompleted = String(sToday);
                                card.dataset.totalCompleted = String(sTotal);
                                if (typeof window.updateZikrCardDom === 'function') {
                                    window.updateZikrCardDom(tasbeehId, sTotal, true);
                                }
                                if (typeof window.recalculateZikrTopStats === 'function') {
                                    window.recalculateZikrTopStats();
                                }
                            }
                            if (data.stats && data.stats.today_completed !== undefined) {
                                const isDone = data.stats.today_completed >= (data.stats.daily_target || dailyTarget);
                                completeIconBtn.classList.toggle('is-completed', isDone);
                                completeIconBtn.classList.toggle('active', isDone);
                            }
                        }
                        if (window.PwaSync && typeof window.PwaSync.broadcastEvent === 'function') {
                            window.PwaSync.broadcastEvent('ZIKR_COMPLETE_TODAY', { tasbeehId: String(tasbeehId) });
                        }
                    }).catch(err => {
                        console.warn('Online sync background request failed, saving offline:', err);
                        if (window.PwaSync && typeof window.PwaSync.completeTasbeehToday === 'function') {
                            window.PwaSync.completeTasbeehToday(tasbeehId);
                        }
                    });
                }
            }
            return;
        }

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

            if (window.PwaSync && typeof window.PwaSync.updateNamazDay === 'function') {
                window.PwaSync.updateNamazDay(userId, date, statuses);
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

        // Offline Namaz Start Date Interception
        if (!navigator.onLine && (url.includes('/namaz-attendance') && url.includes('start-date'))) {
            const formData = new FormData(form);
            const startDate = formData.get('namaz_start_date');
            const match = url.match(/users\/(\d+)\/start-date/);
            const userId = match ? match[1] : (document.querySelector('[data-user-id]')?.dataset.userId || '1');

            if (window.PwaSync && typeof window.PwaSync.updateNamazStartDate === 'function') {
                window.PwaSync.updateNamazStartDate(userId, startDate);
            }

            const modalElement = form.closest('.modal');
            if (modalElement && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            showFlashToast('Namaz start date saved offline. Will sync once online.', 'info');
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

        // Offline Namaz Attendance Reset / Delete
        if (!navigator.onLine && form.action && form.action.includes('/namaz-attendance/')) {
            const match = form.action.match(/\/namaz-attendance\/(\d+)/);
            const attendanceId = match ? match[1] : null;
            const tr = form.closest('tr');
            const firstCellBtn = tr?.querySelector('[data-bs-target="#quickPrayerModal"]');
            const userId = firstCellBtn?.dataset.userId || '1';
            const date = firstCellBtn?.dataset.date || '';

            if (window.PwaSync && typeof window.PwaSync.deleteNamazAttendance === 'function') {
                window.PwaSync.deleteNamazAttendance(attendanceId, userId, date);
            }

            if (userId && date) {
                ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'].forEach((p) => {
                    updateNamazCellDom(userId, date, p, '');
                });
            }

            showFlashToast('Attendance reset saved offline. Will sync once online.', 'info');
            if (submit) submit.disabled = false;
            return;
        }
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
            const descInput = document.getElementById('editTasbeehDesc');
            const targetInput = document.getElementById('editTasbeehTarget');
            const orderInput = document.getElementById('editTasbeehOrder');
            const activeInput = document.getElementById('editTasbeehIsActive');
            const refInput = document.getElementById('editTasbeehRef');

            if (titleInput) titleInput.value = title || '';
            if (arabicInput) arabicInput.value = arabic || '';
            if (urduInput) urduInput.value = urdu || '';
            if (descInput) descInput.value = desc || '';
            if (targetInput) targetInput.value = target || 100;
            if (orderInput) orderInput.value = order || 0;
            if (activeInput) activeInput.checked = active === '1';
            if (refInput) refInput.value = ref || '';
        }

        const descTasbeehTrigger = e.target.closest?.('[data-bs-target="#tasbeehDescModal"]');
        if (descTasbeehTrigger) {
            const {
                id,
                title,
                arabic,
                urdu,
                desc,
                ref,
                target,
                order,
                active,
                todayCompleted,
                totalCompleted,
                totalRequired,
                percentage,
                remaining,
                extra,
                activeDays,
                started,
                lastZikr,
                counterUrl,
                updateUrl
            } = descTasbeehTrigger.dataset;

            const titleEl = document.getElementById('descModalTitle');
            const seqEl = document.getElementById('descModalSeqBadge');
            const statusEl = document.getElementById('descModalStatusBadge');
            const targetEl = document.getElementById('descModalTarget');
            const todayCompEl = document.getElementById('descModalTodayCompleted');
            const todaySubtextEl = document.getElementById('descModalTodaySubtext');
            const totalCompEl = document.getElementById('descModalTotalCompleted');
            const totalReqEl = document.getElementById('descModalTotalRequired');
            const cyclePctEl = document.getElementById('descModalCyclePercentage');
            const progressBarEl = document.getElementById('descModalProgressBar');
            const startedEl = document.getElementById('descModalStarted');
            const activeDaysEl = document.getElementById('descModalActiveDays');
            const lastZikrEl = document.getElementById('descModalLastZikr');
            const refEl = document.getElementById('descModalRef');
            const refRowEl = document.getElementById('descModalRefRow');
            const arabicEl = document.getElementById('descModalArabic');
            const urduEl = document.getElementById('descModalUrdu');
            const descBodyEl = document.getElementById('descModalBodyText');
            const descEmptyEl = document.getElementById('descModalEmptyText');
            const counterBtn = document.getElementById('descModalCounterBtn');
            const editBtn = document.getElementById('descModalEditBtn');

            const numTarget = Number(target || 100);
            const numToday = Number(todayCompleted || 0);
            const numTotal = Number(totalCompleted || 0);
            const numReq = Number(totalRequired || numTarget);
            const numPct = Number(percentage || 0);

            if (titleEl) titleEl.textContent = title || 'Tasbeeh Details';
            if (seqEl) seqEl.textContent = `#${order || '1'}`;

            if (statusEl) {
                const isActive = active !== '0';
                statusEl.textContent = isActive ? 'Active' : 'Inactive';
                statusEl.className = `badge rounded-pill px-2 py-0.5 ${isActive ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger'}`;
            }

            if (targetEl) targetEl.textContent = numTarget.toLocaleString();
            if (todayCompEl) todayCompEl.textContent = numToday.toLocaleString();

            if (todaySubtextEl) {
                if (numToday >= numTarget && numTarget > 0) {
                    todaySubtextEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check2"></i> Target Done</span>';
                } else {
                    const rem = Math.max(numTarget - numToday, 0);
                    todaySubtextEl.innerHTML = `Rem: <strong class="text-white">${rem.toLocaleString()}</strong>`;
                }
            }

            if (totalCompEl) totalCompEl.textContent = numTotal.toLocaleString();
            if (totalReqEl) totalReqEl.textContent = numReq.toLocaleString();

            if (cyclePctEl) cyclePctEl.textContent = `${numPct}%`;
            if (progressBarEl) {
                progressBarEl.style.width = `${Math.min(numPct, 100)}%`;
                progressBarEl.className = 'progress-bar ' + (Number(extra || 0) > 0 ? 'bg-info' : (numPct >= 100 ? 'bg-success' : 'bg-warning'));
            }

            if (startedEl) startedEl.textContent = started || '—';
            if (activeDaysEl) activeDaysEl.textContent = activeDays || '1';
            if (lastZikrEl) lastZikrEl.textContent = lastZikr || 'Never';

            if (ref && ref.trim()) {
                if (refEl) refEl.textContent = ref;
                if (refRowEl) refRowEl.classList.remove('d-none');
            } else {
                if (refRowEl) refRowEl.classList.add('d-none');
            }

            if (arabicEl) arabicEl.textContent = arabic || '';
            if (urduEl) urduEl.textContent = urdu || '—';

            if (desc && desc.trim()) {
                if (descBodyEl) {
                    descBodyEl.textContent = desc;
                    descBodyEl.classList.remove('d-none');
                }
                if (descEmptyEl) descEmptyEl.classList.add('d-none');
            } else {
                if (descBodyEl) {
                    descBodyEl.textContent = '';
                    descBodyEl.classList.add('d-none');
                }
                if (descEmptyEl) descEmptyEl.classList.remove('d-none');
            }

            if (counterBtn) {
                if (counterUrl) {
                    counterBtn.href = counterUrl;
                    counterBtn.classList.remove('d-none');
                } else {
                    counterBtn.classList.add('d-none');
                }
            }

            if (editBtn) {
                if (updateUrl) {
                    editBtn.classList.remove('d-none');
                    editBtn.dataset.id = id || '';
                    editBtn.dataset.title = title || '';
                    editBtn.dataset.arabic = arabic || '';
                    editBtn.dataset.urdu = urdu || '';
                    editBtn.dataset.target = target || '';
                    editBtn.dataset.order = order || '';
                    editBtn.dataset.active = active || '1';
                    editBtn.dataset.desc = desc || '';
                    editBtn.dataset.ref = ref || '';
                    editBtn.dataset.updateUrl = updateUrl || '';
                } else {
                    editBtn.classList.add('d-none');
                }
            }
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

            let completed = completedEl ? (parseInt(completedEl.textContent.replace(/,/g, ''), 10) || 0) : (parseInt(card.dataset.totalCompleted || '0', 10) || 0);
            let required = matchReq ? (parseInt(matchReq[1].replace(/,/g, ''), 10) || 0) : (parseInt(card.dataset.totalRequired || '0', 10) || 0);
            let dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10);
            let todayCompletedFromCard = parseInt(card.dataset.todayCompleted || '0', 10) || 0;

            overallTodayCompleted += todayCompletedFromCard;
            overallTodayRequired += dailyTarget;
            overallTotalCompleted += completed;
            overallTotalRequired += required;
        });

        let dailyTargetStatEl = document.getElementById('top-stat-daily-target');
        let todayCompletedStatEl = document.getElementById('top-stat-today-completed');
        let todayPercentStatEl = document.getElementById('top-stat-today-percentage');
        let totalRequiredStatEl = document.getElementById('top-stat-total-required');
        let completedStatEl = document.getElementById('top-stat-total-completed');
        let percentStatEl = document.getElementById('top-stat-overall-percentage');
        let backlogContainerEl = document.getElementById('top-stat-backlog-container');

        if (dailyTargetStatEl) {
            dailyTargetStatEl.dataset.rawVal = overallTodayRequired.toLocaleString();
            dailyTargetStatEl.textContent = overallTodayRequired.toLocaleString();
        }

        if (todayCompletedStatEl) {
            todayCompletedStatEl.dataset.rawVal = overallTodayCompleted.toLocaleString();
            todayCompletedStatEl.textContent = overallTodayCompleted.toLocaleString();
        }

        if (todayPercentStatEl) {
            let todayPercent = overallTodayRequired > 0 ? Math.min(100, Math.round((overallTodayCompleted / overallTodayRequired) * 100)) : 100;
            todayPercentStatEl.dataset.rawSubtext = `${todayPercent}% of daily target`;
            todayPercentStatEl.dataset.maskedSubtext = '•••% of daily target';
            todayPercentStatEl.textContent = `${todayPercent}% of daily target`;
        }

        if (totalRequiredStatEl) {
            totalRequiredStatEl.dataset.rawVal = overallTotalRequired.toLocaleString();
            totalRequiredStatEl.textContent = overallTotalRequired.toLocaleString();
        }

        if (completedStatEl) {
            completedStatEl.dataset.rawVal = overallTotalCompleted.toLocaleString();
            completedStatEl.textContent = overallTotalCompleted.toLocaleString();
        }

        let percentage = overallTotalRequired > 0 ? Math.min(100, Math.round((overallTotalCompleted / overallTotalRequired) * 100)) : 100;
        if (percentStatEl) {
            percentStatEl.dataset.rawSubtext = `${percentage}% Completed`;
            percentStatEl.dataset.maskedSubtext = '•••% Completed';
            percentStatEl.textContent = `${percentage}% Completed`;
        }

        if (backlogContainerEl) {
            let diff = overallTotalCompleted - overallTotalRequired;
            let titleEl = document.getElementById('top-stat-backlog-title');
            let valEl = document.getElementById('top-stat-backlog-value');
            let subEl = document.getElementById('top-stat-backlog-subtext');

            if (diff > 0) {
                if (titleEl) titleEl.textContent = 'Extra Zikr';
                if (valEl) {
                    valEl.className = 'fs-3 fs-md-2 text-info d-block font-monospace my-0 zikr-stat-maskable';
                    valEl.dataset.rawVal = `+${diff.toLocaleString()}`;
                    valEl.textContent = `+${diff.toLocaleString()}`;
                }
                if (subEl) {
                    subEl.className = 'text-info d-block fw-semibold text-truncate zikr-stat-maskable';
                    subEl.dataset.rawSubtext = 'Ahead of schedule';
                    subEl.dataset.maskedSubtext = 'Ahead of schedule';
                    subEl.textContent = 'Ahead of schedule';
                }
            } else {
                let backlog = Math.abs(diff);
                let colorClass = backlog > 0 ? 'text-warning' : 'text-success';
                let label = backlog > 0 ? 'Behind schedule' : 'On track';
                if (titleEl) titleEl.textContent = 'Remaining Backlog';
                if (valEl) {
                    valEl.className = `fs-3 fs-md-2 ${colorClass} d-block font-monospace my-0 zikr-stat-maskable`;
                    valEl.dataset.rawVal = backlog.toLocaleString();
                    valEl.textContent = backlog.toLocaleString();
                }
                if (subEl) {
                    subEl.className = `${colorClass} d-block fw-semibold text-truncate zikr-stat-maskable`;
                    subEl.dataset.rawSubtext = label;
                    subEl.dataset.maskedSubtext = label;
                    subEl.textContent = label;
                }
            }
        }

        if (typeof window.renderZikrStatCards === 'function') {
            window.renderZikrStatCards();
        }
    };

    window.updateZikrCardDom = function (tasbeehId, countDelta, isAbsolute = false) {
        const cardCol = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
        if (!cardCol) return;

        let completedEl = cardCol.querySelector('.badge-completed strong');
        let remainingEl = cardCol.querySelector('.badge-remaining');
        let progressEl = cardCol.querySelector('.progress-bar-custom');
        let percentTextEl = cardCol.querySelector('.progress-container')?.parentElement?.querySelector('.font-monospace');

        let currentCompleted = parseInt(cardCol.dataset.totalCompleted || (completedEl ? completedEl.textContent.replace(/,/g, '') : '0'), 10) || 0;

        let totalRequired = parseInt(cardCol.dataset.totalRequired || '0', 10) || 0;
        let badgeCompletedText = cardCol.querySelector('.badge-completed')?.textContent || '';
        let matchReq = badgeCompletedText.match(/\/\s*([0-9,]+)/);
        if (matchReq && !totalRequired) {
            totalRequired = parseInt(matchReq[1].replace(/,/g, ''), 10) || 0;
        }

        let deltaAdded = isAbsolute ? (countDelta - currentCompleted) : countDelta;
        let newCompleted = isAbsolute ? countDelta : currentCompleted + countDelta;
        if (newCompleted < 0) newCompleted = 0;

        cardCol.dataset.totalCompleted = String(newCompleted);

        if (completedEl) {
            completedEl.textContent = newCompleted.toLocaleString();
        }

        let todayStatusBadgeEl = cardCol.querySelector('.today-status-badge');
        let todayMetaEl = cardCol.querySelector('.today-meta-count');
        let dailyTarget = parseInt(cardCol.dataset.dailyTarget || '100', 10);
        let nextTodayCompleted = 0;

        // Ensure date rollover is respected before modifying today's count
        const todayStr = (function () {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        })();
        const cardDate = cardCol.dataset.renderDate;
        let currentTodayCompleted = parseInt(cardCol.dataset.todayCompleted || '0', 10) || 0;
        if (cardDate && cardDate < todayStr) {
            currentTodayCompleted = 0;
            cardCol.dataset.baseTodayCompleted = '0';
            cardCol.dataset.renderDate = todayStr;
        }

        nextTodayCompleted = Math.max(isAbsolute ? (countDelta === 0 ? 0 : currentTodayCompleted) : currentTodayCompleted + deltaAdded, 0);
        cardCol.dataset.todayCompleted = String(nextTodayCompleted);
        cardCol.dataset.renderDate = todayStr;

        if (todayMetaEl) {
            todayMetaEl.textContent = nextTodayCompleted.toLocaleString();
            todayMetaEl.className = `today-meta-count font-monospace ${nextTodayCompleted >= dailyTarget ? 'text-success' : (nextTodayCompleted > 0 ? 'text-info' : 'text-danger')}`;
        }

        if (todayStatusBadgeEl) {
            if (nextTodayCompleted >= dailyTarget && dailyTarget > 0) {
                todayStatusBadgeEl.style.background = 'rgba(16, 185, 129, 0.15)';
                todayStatusBadgeEl.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                todayStatusBadgeEl.style.color = '#34d399';
                todayStatusBadgeEl.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">${nextTodayCompleted.toLocaleString()}</strong>`;
            } else if (nextTodayCompleted > 0) {
                todayStatusBadgeEl.style.background = 'rgba(6, 182, 212, 0.15)';
                todayStatusBadgeEl.style.borderColor = 'rgba(6, 182, 212, 0.4)';
                todayStatusBadgeEl.style.color = '#38bdf8';
                todayStatusBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace">${nextTodayCompleted.toLocaleString()}</strong>`;
            } else {
                todayStatusBadgeEl.style.background = 'rgba(239, 68, 68, 0.12)';
                todayStatusBadgeEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                todayStatusBadgeEl.style.color = '#f87171';
                todayStatusBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace">0</strong>`;
            }
        }

        let completeIconBtn = cardCol.querySelector('.btn-complete-icon');
        if (completeIconBtn) {
            const isDone = nextTodayCompleted >= dailyTarget && dailyTarget > 0;
            completeIconBtn.classList.toggle('is-completed', isDone);
            completeIconBtn.classList.toggle('active', isDone);
        }

        // Live Real-Time Lifetime Total Counter Update (Only increments/decrements on zikr additions/removals, NEVER on resets)
        if (!isAbsolute && deltaAdded !== 0) {
            let lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl) {
                let currentLifetime = parseInt((lifetimeEl.dataset.rawVal || lifetimeEl.textContent).replace(/,/g, ''), 10) || 0;
                let newLifetime = Math.max(currentLifetime + deltaAdded, 0);
                lifetimeEl.dataset.rawVal = newLifetime.toLocaleString();
                lifetimeEl.textContent = newLifetime.toLocaleString();
                if (typeof window.renderZikrStatCards === 'function') {
                    window.renderZikrStatCards();
                }
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

    // Absolute Offline Reconciliation for Dashboard & Counter Cards
    window.reconcileZikrOfflineCounts = async function (pulledServerData = null) {
        try {
            function getLocalDateStr(dateObj = new Date()) {
                const d = (dateObj instanceof Date && !isNaN(dateObj)) ? dateObj : new Date();
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }
            const clientToday = getLocalDateStr();
            const globalPageDate = document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
            let lastActiveDate = null;
            try {
                lastActiveDate = localStorage.getItem('pwa_zikr_active_date');
            } catch (_) {}

            // Robust check: Did a 24-hour day rollover occur since the user last interacted or since page was rendered?
            const isDateRolledOver = Boolean(
                (lastActiveDate && lastActiveDate < clientToday) ||
                (globalPageDate && globalPageDate < clientToday)
            );

            // 1. If fresh server data was passed (e.g. from pull sync), update baseline values
            if (pulledServerData && pulledServerData.zikr_summary) {
                const summary = pulledServerData.zikr_summary;
                const syncDate = (pulledServerData.server_time || '').substring(0, 10);
                const isSyncToday = !syncDate || syncDate === clientToday;

                if (Array.isArray(summary.tasbeehs)) {
                    summary.tasbeehs.forEach(t => {
                        const card = document.getElementById(`tasbeeh-card-${t.tasbeeh_id}`) || document.querySelector(`[data-tasbeeh-card="${t.tasbeeh_id}"]`);
                        if (card) {
                            const todayCount = isSyncToday ? (t.today_completed || 0) : 0;
                            card.dataset.baseTodayCompleted = String(todayCount);
                            card.dataset.baseTotalCompleted = String(t.total_completed || 0);
                            card.dataset.todayCompleted = String(todayCount);
                            card.dataset.totalCompleted = String(t.total_completed || 0);
                            card.dataset.dailyTarget = String(t.daily_target || card.dataset.dailyTarget || 100);
                            card.dataset.totalRequired = String(t.total_required || card.dataset.totalRequired || 100);
                            card.dataset.renderDate = clientToday;
                        }
                    });
                }
                const lifetimeEl = document.getElementById('top-stat-lifetime-total');
                if (lifetimeEl && summary.lifetime_total !== undefined) {
                    lifetimeEl.dataset.baseLifetime = String(summary.lifetime_total);
                    lifetimeEl.dataset.rawVal = Number(summary.lifetime_total).toLocaleString();
                    lifetimeEl.textContent = Number(summary.lifetime_total).toLocaleString();
                }
            }

            // 2. Read pending outbox items
            let items = [];
            if (window.PwaDB && typeof window.PwaDB.getPendingOutbox === 'function') {
                items = await window.PwaDB.getPendingOutbox();
            }

            function getItemLocalDate(item) {
                if (!item) return null;
                const p = item.payload || {};
                if (p.date && typeof p.date === 'string') {
                    return p.date.substring(0, 10);
                }
                if (item.created_at) {
                    try {
                        return getLocalDateStr(new Date(item.created_at));
                    } catch (_) {
                        return String(item.created_at).substring(0, 10);
                    }
                }
                return null;
            }

            const countTodayByTasbeeh = {};
            const countTotalByTasbeeh = {};
            const completedTodayByTasbeeh = {};
            const resetByTasbeeh = {};
            let hasLifetimeReset = false;
            let hasZikrResetAll = false;
            let hasZikrCompleteAllToday = false;

            (items || []).forEach(item => {
                const entity = item.entity || '';
                const p = item.payload || {};
                const tId = p.tasbeeh_id ? String(p.tasbeeh_id) : null;
                const itemDate = getItemLocalDate(item);
                const isTodayItem = Boolean(itemDate && itemDate === clientToday);

                if (entity === 'zikr_count' || entity === 'tasbeeh_count') {
                    if (tId) {
                        const cnt = (parseInt(p.count, 10) || 0);
                        countTotalByTasbeeh[tId] = (countTotalByTasbeeh[tId] || 0) + cnt;
                        if (isTodayItem) {
                            countTodayByTasbeeh[tId] = (countTodayByTasbeeh[tId] || 0) + cnt;
                        }
                    }
                } else if (entity === 'tasbeeh_complete_today') {
                    if (tId && isTodayItem) {
                        completedTodayByTasbeeh[tId] = (completedTodayByTasbeeh[tId] || 0) + 1;
                    }
                } else if (entity === 'zikr_complete_all') {
                    if (isTodayItem) {
                        hasZikrCompleteAllToday = true;
                    }
                } else if (entity === 'tasbeeh_reset_single') {
                    if (tId) resetByTasbeeh[tId] = true;
                } else if (entity === 'zikr_reset_all') {
                    hasZikrResetAll = true;
                } else if (entity === 'lifetime_reset') {
                    hasLifetimeReset = true;
                }
            });

            // 3. Update all Tasbeeh Cards using absolute baseline + pending mutations
            const cards = document.querySelectorAll('[id^="tasbeeh-card-"]');
            let allCompletesTotalCount = 0;

            cards.forEach(card => {
                const tId = card.id.replace('tasbeeh-card-', '');
                const cardRenderDate = card.dataset.renderDate || globalPageDate;
                const isCardPastDay = !pulledServerData && Boolean(
                    isDateRolledOver ||
                    (cardRenderDate && cardRenderDate < clientToday)
                );

                if (isCardPastDay) {
                    card.dataset.baseTodayCompleted = '0';
                    card.dataset.renderDate = clientToday;
                } else if (!card.dataset.baseTodayCompleted) {
                    card.dataset.baseTodayCompleted = card.dataset.todayCompleted || '0';
                }
                if (!card.dataset.baseTotalCompleted) {
                    const completedEl = card.querySelector('.badge-completed strong');
                    card.dataset.baseTotalCompleted = card.dataset.totalCompleted || (completedEl ? completedEl.textContent.replace(/,/g, '') : '0');
                }

                let baseToday = isCardPastDay ? 0 : (parseInt(card.dataset.baseTodayCompleted || '0', 10) || 0);
                let baseTotal = parseInt(card.dataset.baseTotalCompleted || '0', 10) || 0;
                let dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10) || 100;

                let totalRequired = parseInt(card.dataset.totalRequired || '0', 10);
                const startDateStr = card.dataset.trackingStartDate;
                if (startDateStr && isCardPastDay) {
                    const start = new Date(startDateStr + 'T00:00:00');
                    const cur = new Date(clientToday + 'T00:00:00');
                    const activeDays = Math.max(1, Math.floor((cur - start) / 86400000) + 1);
                    card.dataset.activeDays = String(activeDays);
                    totalRequired = activeDays * dailyTarget;
                    card.dataset.totalRequired = String(totalRequired);
                } else if (!totalRequired) {
                    let badgeCompletedText = card.querySelector('.badge-completed')?.textContent || '';
                    let matchReq = badgeCompletedText.match(/\/\s*([0-9,]+)/);
                    totalRequired = matchReq ? (parseInt(matchReq[1].replace(/,/g, ''), 10) || 0) : 0;
                }

                let finalToday = baseToday;
                let finalTotal = baseTotal;

                if (hasZikrResetAll || resetByTasbeeh[tId]) {
                    finalToday = 0;
                    finalTotal = 0;
                } else {
                    const addedTodayCount = countTodayByTasbeeh[tId] || 0;
                    const addedTotalCount = countTotalByTasbeeh[tId] || 0;
                    const completesCount = (completedTodayByTasbeeh[tId] || 0) + (hasZikrCompleteAllToday ? 1 : 0);
                    const completeTodayAdd = completesCount > 0 ? Math.max(0, dailyTarget - baseToday) : 0;

                    allCompletesTotalCount += completeTodayAdd;

                    finalToday = baseToday + completeTodayAdd + addedTodayCount;
                    finalTotal = baseTotal + completeTodayAdd + addedTotalCount;
                }

                if (finalToday < 0) finalToday = 0;
                if (finalTotal < 0) finalTotal = 0;

                // Apply to Card DOM
                card.dataset.todayCompleted = String(finalToday);
                card.dataset.totalCompleted = String(finalTotal);
                card.dataset.renderDate = clientToday;

                let completedEl = card.querySelector('.badge-completed strong');
                if (completedEl) completedEl.textContent = finalTotal.toLocaleString();

                let todayStatusBadgeEl = card.querySelector('.today-status-badge');
                let todayMetaEl = card.querySelector('.today-meta-count');

                if (todayMetaEl) {
                    todayMetaEl.textContent = finalToday.toLocaleString();
                    todayMetaEl.className = `today-meta-count font-monospace ${finalToday >= dailyTarget ? 'text-success' : (finalToday > 0 ? 'text-info' : 'text-danger')}`;
                }

                if (todayStatusBadgeEl) {
                    if (finalToday >= dailyTarget && dailyTarget > 0) {
                        todayStatusBadgeEl.style.background = 'rgba(16, 185, 129, 0.15)';
                        todayStatusBadgeEl.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                        todayStatusBadgeEl.style.color = '#34d399';
                        todayStatusBadgeEl.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">${finalToday.toLocaleString()}</strong>`;
                    } else if (finalToday > 0) {
                        todayStatusBadgeEl.style.background = 'rgba(6, 182, 212, 0.15)';
                        todayStatusBadgeEl.style.borderColor = 'rgba(6, 182, 212, 0.4)';
                        todayStatusBadgeEl.style.color = '#38bdf8';
                        todayStatusBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace">${finalToday.toLocaleString()}</strong>`;
                    } else {
                        todayStatusBadgeEl.style.background = 'rgba(239, 68, 68, 0.12)';
                        todayStatusBadgeEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                        todayStatusBadgeEl.style.color = '#f87171';
                        todayStatusBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace">0</strong>`;
                    }
                }

                let completeIconBtn = card.querySelector('.btn-complete-icon');
                if (completeIconBtn) {
                    const isDone = finalToday >= dailyTarget && dailyTarget > 0;
                    completeIconBtn.classList.toggle('is-completed', isDone);
                    completeIconBtn.classList.toggle('active', isDone);
                }

                let remainingEl = card.querySelector('.badge-remaining');
                let progressEl = card.querySelector('.progress-bar-custom');
                let percentTextEl = card.querySelector('.progress-container')?.parentElement?.querySelector('.font-monospace');

                if (totalRequired > 0) {
                    let percentage = Math.min(100, Math.round((finalTotal / totalRequired) * 100));
                    if (percentTextEl) percentTextEl.textContent = `${percentage}%`;

                    let diff = finalTotal - totalRequired;
                    if (remainingEl) {
                        if (diff > 0) {
                            remainingEl.className = 'badge-remaining extra';
                            remainingEl.textContent = `+${diff.toLocaleString()} Extra`;
                        } else if (diff === 0) {
                            remainingEl.className = 'badge-remaining completed-badge';
                            remainingEl.textContent = 'Completed';
                        } else {
                            remainingEl.className = 'badge-remaining';
                            remainingEl.textContent = `Remaining ${(totalRequired - finalTotal).toLocaleString()}`;
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
            });

            // 4. Update Lifetime Counter
            let lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl) {
                if (hasLifetimeReset) {
                    lifetimeEl.dataset.baseLifetime = '0';
                    lifetimeEl.dataset.rawVal = '0';
                    lifetimeEl.textContent = '0';
                } else {
                    let baseLifetime = parseInt(String(lifetimeEl.dataset.baseLifetime || lifetimeEl.dataset.rawVal || lifetimeEl.textContent || '0').replace(/,/g, ''), 10) || 0;
                    let totalAddedAcrossAll = Object.values(countTotalByTasbeeh).reduce((sum, v) => sum + (parseInt(v, 10) || 0), 0) + allCompletesTotalCount;
                    let finalLifetime = baseLifetime + totalAddedAcrossAll;
                    lifetimeEl.dataset.rawVal = finalLifetime.toLocaleString();
                    lifetimeEl.textContent = finalLifetime.toLocaleString();
                }
            }

            // 5. Recalculate Overall Top Statistics Cards
            if (typeof window.recalculateZikrTopStats === 'function') {
                window.recalculateZikrTopStats();
            }

            // Update page meta rendered date so future checks in this session know it's clientToday
            const metaRenderDateEl = document.querySelector('meta[name="page-rendered-date"]');
            if (metaRenderDateEl) metaRenderDateEl.setAttribute('content', clientToday);

            try {
                localStorage.setItem('pwa_zikr_active_date', clientToday);
            } catch (_) {}
        } catch (e) {
            console.warn('reconcileZikrOfflineCounts notice:', e);
        }
    };

    // Absolute Offline Reconciliation for Namaz Attendance & Dashboard
    window.reconcileNamazOfflineState = async function (pulledServerData = null) {
        try {
            let items = [];
            if (window.PwaDB && typeof window.PwaDB.getPendingOutbox === 'function') {
                items = await window.PwaDB.getPendingOutbox();
            }

            const namazOps = (items || [])
                .filter(item => item && (
                    item.entity === 'namaz_attendance_status' ||
                    item.entity === 'namaz_attendance_day' ||
                    item.entity === 'namaz_attendance_delete' ||
                    item.entity === 'namaz_start_date'
                ))
                .sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

            const latestPrayerStatus = {};
            const updatedStartDates = {};

            namazOps.forEach(op => {
                const p = op.payload || {};
                const uId = String(p.user_id || '');
                const d = p.attendance_date;

                if (op.entity === 'namaz_attendance_status') {
                    if (uId && d && p.prayer) {
                        const key = `${uId}_${d}_${p.prayer}`;
                        latestPrayerStatus[key] = p.status || '';
                    }
                } else if (op.entity === 'namaz_attendance_day') {
                    if (uId && d) {
                        ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'].forEach(prayer => {
                            if (p[`${prayer}_status`] !== undefined) {
                                const key = `${uId}_${d}_${prayer}`;
                                latestPrayerStatus[key] = p[`${prayer}_status`] || '';
                            }
                        });
                    }
                } else if (op.entity === 'namaz_attendance_delete') {
                    if (uId && d) {
                        ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'].forEach(prayer => {
                            const key = `${uId}_${d}_${prayer}`;
                            latestPrayerStatus[key] = '';
                        });
                    }
                } else if (op.entity === 'namaz_start_date') {
                    if (uId && p.namaz_start_date) {
                        updatedStartDates[uId] = p.namaz_start_date;
                    }
                }
            });

            // 1. Reconcile Attendance Table View if present
            const attendanceContainer = document.getElementById('namaz-attendance-results');
            if (attendanceContainer) {
                Object.entries(latestPrayerStatus).forEach(([key, status]) => {
                    const parts = key.split('_');
                    if (parts.length >= 3) {
                        const uId = parts[0];
                        const d = parts[1];
                        const prayer = parts.slice(2).join('_');
                        updateNamazCellDom(uId, d, prayer, status);
                    }
                });

                Object.entries(updatedStartDates).forEach(([uId, sDate]) => {
                    const startDateEl = document.querySelector(`[data-namaz-start-date-user="${uId}"]`);
                    if (startDateEl) startDateEl.textContent = sDate;
                });
            }

            // 2. Reconcile Dashboard View if present
            const dashboardContainer = document.getElementById('namaz-dashboard-results');
            if (dashboardContainer) {
                recalculateNamazDashboardStats(latestPrayerStatus);
            }
        } catch (e) {
            console.warn('reconcileNamazOfflineState notice:', e);
        }
    };

    function recalculateNamazDashboardStats(latestPrayerStatus = {}) {
        const totalEl = document.getElementById('namaz-stat-total');
        const jamatEl = document.getElementById('namaz-stat-jamat');
        const withoutJamatEl = document.getElementById('namaz-stat-without-jamat');
        const kazaEl = document.getElementById('namaz-stat-kaza');
        const absentEl = document.getElementById('namaz-stat-absent');
        const pendingEl = document.getElementById('namaz-stat-pending');
        const jamatLabel = document.getElementById('namaz-stat-jamat-label');

        if (!totalEl || !jamatEl || !withoutJamatEl || !kazaEl || !absentEl || !pendingEl) return;

        let baseTotal = parseInt(totalEl.dataset.baseVal || '0', 10);
        let baseJamat = parseInt(jamatEl.dataset.baseVal || '0', 10);
        let baseWithoutJamat = parseInt(withoutJamatEl.dataset.baseVal || '0', 10);
        let baseKaza = parseInt(kazaEl.dataset.baseVal || '0', 10);
        let baseAbsent = parseInt(absentEl.dataset.baseVal || '0', 10);
        let basePending = parseInt(pendingEl.dataset.baseVal || '0', 10);

        let deltaJamat = 0;
        let deltaWithoutJamat = 0;
        let deltaKaza = 0;
        let deltaAbsent = 0;
        let deltaPending = 0;

        Object.values(latestPrayerStatus).forEach(status => {
            if (status === 'jamat') {
                deltaJamat++;
                if (basePending > 0) deltaPending--;
            } else if (status === 'without_jamat') {
                deltaWithoutJamat++;
                if (basePending > 0) deltaPending--;
            } else if (status === 'kaza') {
                deltaKaza++;
                if (basePending > 0) deltaPending--;
            } else if (status === 'absent') {
                deltaAbsent++;
                if (basePending > 0) deltaPending--;
            }
        });

        const curJamat = Math.max(0, baseJamat + deltaJamat);
        const curWithoutJamat = Math.max(0, baseWithoutJamat + deltaWithoutJamat);
        const curKaza = Math.max(0, baseKaza + deltaKaza);
        const curAbsent = Math.max(0, baseAbsent + deltaAbsent);
        const curPending = Math.max(0, basePending + deltaPending);
        const curTotal = Math.max(0, baseTotal);

        jamatEl.textContent = curJamat.toLocaleString();
        withoutJamatEl.textContent = curWithoutJamat.toLocaleString();
        kazaEl.textContent = curKaza.toLocaleString();
        absentEl.textContent = curAbsent.toLocaleString();
        pendingEl.textContent = curPending.toLocaleString();
        totalEl.textContent = curTotal.toLocaleString();

        const prayedCount = curJamat + curWithoutJamat + curKaza + curAbsent;
        const jamatPct = prayedCount > 0 ? Math.round((curJamat / prayedCount) * 100) : 0;
        if (jamatLabel) {
            jamatLabel.textContent = `Total Jamat (${jamatPct}%)`;
        }
    }

    // Auto-trigger reconciliation on multiple key browser lifecycles
    const runAllOfflineReconciliations = (data = null) => {
        if (typeof window.reconcileZikrOfflineCounts === 'function') {
            window.reconcileZikrOfflineCounts(data);
        }
        if (typeof window.reconcileNamazOfflineState === 'function') {
            window.reconcileNamazOfflineState(data);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => runAllOfflineReconciliations());
    } else {
        runAllOfflineReconciliations();
    }
    window.addEventListener('load', () => runAllOfflineReconciliations());
    window.addEventListener('pageshow', () => runAllOfflineReconciliations());
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            runAllOfflineReconciliations();
        }
    });
    window.addEventListener('pwa:sync-completed', (e) => {
        runAllOfflineReconciliations(e.detail?.data || null);
    });

    // Periodic midnight / 24-hour rollover monitor for offline PWA
    let lastKnownDayStr = (function () {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    })();

    const checkDayRollover = () => {
        const d = new Date();
        const curDayStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        const pageRenderDate = document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
        if (curDayStr !== lastKnownDayStr || (pageRenderDate && pageRenderDate < curDayStr)) {
            lastKnownDayStr = curDayStr;
            runAllOfflineReconciliations();
        }
    };

    setInterval(checkDayRollover, 5000);
    window.addEventListener('focus', checkDayRollover);
    window.addEventListener('pageshow', checkDayRollover);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') checkDayRollover();
    });

    // Helper to process live broadcast messages across tabs
    function handleZikrLiveBroadcast(eventData) {
        if (!eventData || !eventData.type) return;
        const myClientId = window.PWA_CLIENT_ID || (window.PwaSync && window.PwaSync.clientId);
        if (eventData.clientId && myClientId && eventData.clientId === myClientId) {
            return; // Ignore broadcast generated by this same tab
        }

        if (eventData.type === 'ZIKR_COUNT_INCREMENT') {
            const tId = String(eventData.tasbeehId);
            const delta = parseInt(eventData.delta, 10) || 0;
            if (tId && delta !== 0 && typeof window.updateZikrCardDom === 'function') {
                window.updateZikrCardDom(tId, delta, false);
            }
        } else if (eventData.type === 'ZIKR_COMPLETE_TODAY') {
            const tId = String(eventData.tasbeehId);
            const card = document.getElementById(`tasbeeh-card-${tId}`) || document.querySelector(`[data-tasbeeh-card="${tId}"]`);
            let countToAdd = parseInt(card?.dataset?.dailyTarget || '100', 10);
            if (countToAdd <= 0) countToAdd = 100;
            if (tId && typeof window.updateZikrCardDom === 'function') {
                window.updateZikrCardDom(tId, countToAdd, false);
            }
        } else if (eventData.type === 'ZIKR_COMPLETE_ALL') {
            document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                const tId = card.id.replace('tasbeeh-card-', '');
                let countToAdd = parseInt(card?.dataset?.dailyTarget || '100', 10);
                if (countToAdd <= 0) countToAdd = 100;
                if (typeof window.updateZikrCardDom === 'function') {
                    window.updateZikrCardDom(tId, countToAdd, false);
                }
            });
        } else if (eventData.type === 'ZIKR_RESET_SINGLE') {
            const tId = String(eventData.tasbeehId);
            if (tId && typeof window.updateZikrCardDom === 'function') {
                window.updateZikrCardDom(tId, 0, true);
            }
        } else if (eventData.type === 'ZIKR_RESET_ALL') {
            document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                const tId = card.id.replace('tasbeeh-card-', '');
                if (typeof window.updateZikrCardDom === 'function') {
                    window.updateZikrCardDom(tId, 0, true);
                }
            });
        } else if (eventData.type === 'ZIKR_LIFETIME_RESET') {
            let lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl) {
                lifetimeEl.dataset.baseLifetime = '0';
                lifetimeEl.dataset.rawVal = '0';
                lifetimeEl.textContent = '0';
            }
            let lifetimeDurEl = document.getElementById('top-stat-lifetime-duration');
            if (lifetimeDurEl) {
                lifetimeDurEl.dataset.rawSubtext = '<i class="bi bi-clock-history me-1"></i>1 Day';
                lifetimeDurEl.innerHTML = '<i class="bi bi-clock-history me-1"></i>1 Day';
            }
            if (typeof window.renderZikrStatCards === 'function') {
                window.renderZikrStatCards();
            }
        }
    }

    // Real-Time Cross-Tab / Broadcast Listener
    if ('BroadcastChannel' in window) {
        try {
            const appBroadcastChannel = new BroadcastChannel('portfolio_zikr_channel');
            appBroadcastChannel.onmessage = (event) => {
                if (event.data && event.data.type) {
                    handleZikrLiveBroadcast(event.data);
                }
            };
        } catch (e) {}
    }

    // Cross-tab fallback via storage event
    window.addEventListener('storage', (event) => {
        if (event.key === 'pwa_zikr_live_broadcast' && event.newValue) {
            try {
                const parsed = JSON.parse(event.newValue);
                handleZikrLiveBroadcast(parsed);
            } catch (e) {}
        }
    });

    const resetFormHandler = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const modal = form.closest('.modal');
            const actionUrl = form.action || '';
            const match = actionUrl.match(/counter\/(\d+)/);
            const tasbeehId = match ? match[1] : null;

            // Immediate visual reset to 0
            if (tasbeehId && typeof window.updateZikrCardDom === 'function') {
                window.updateZikrCardDom(tasbeehId, 0, true);
            }
            if (modal && window.bootstrap) {
                window.bootstrap.Modal.getInstance(modal)?.hide();
            }

            if (!navigator.onLine) {
                if (window.PwaSync && tasbeehId) {
                    await window.PwaSync.resetTasbeeh(tasbeehId);
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
                    if (tasbeehId) {
                        const card = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
                        if (card) {
                            card.dataset.baseTodayCompleted = '0';
                            card.dataset.baseTotalCompleted = '0';
                        }
                        if (window.PwaSync && typeof window.PwaSync.broadcastEvent === 'function') {
                            window.PwaSync.broadcastEvent('ZIKR_RESET_SINGLE', { tasbeehId: String(tasbeehId) });
                        }
                    }
                    showFlashToast(payload.message || 'Tracking reset successfully.');
                })
                .catch((err) => {
                    if (window.PwaSync && tasbeehId) {
                        window.PwaSync.resetTasbeeh(tasbeehId);
                    }
                    showFlashToast('Tracking reset saved offline and updated on screen.', 'info');
                });
        });
    };
    resetFormHandler('resetTasbeehForm');
    resetFormHandler('resetDetailForm');

    const quickAddForm = document.getElementById('quickAddForm');
    if (quickAddForm) {
        quickAddForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const modal = quickAddForm.closest('.modal');
            const btn = document.getElementById('quickAddSubmitBtn');
            if (btn) btn.disabled = true;

            const formData = new FormData(quickAddForm);
            const countVal = parseInt(formData.get('count'), 10);
            if (isNaN(countVal) || countVal === 0) {
                if (btn) btn.disabled = false;
                return;
            }

            const actionUrl = quickAddForm.action || '';
            const match = actionUrl.match(/counter\/(\d+)/);
            const tasbeehId = quickAddForm.dataset.tasbeehId || (match ? match[1] : null);

            // Immediate 0ms local visual update on screen
            if (tasbeehId && typeof window.updateZikrCardDom === 'function') {
                window.updateZikrCardDom(tasbeehId, countVal, false);
            }

            if (!navigator.onLine) {
                if (window.PwaSync && tasbeehId) {
                    await window.PwaSync.saveZikrCount(tasbeehId, countVal, null, true);
                }
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getInstance(modal)?.hide();
                }
                const countInput = document.getElementById('quickAddCountInput');
                if (countInput) countInput.value = '';
                if (btn) btn.disabled = false;
                showFlashToast(`+${countVal.toLocaleString()} Zikr count saved offline and updated on screen!`, 'info');
                return;
            }

            try {
                const res = await fetch(quickAddForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': financeCsrf,
                    },
                    body: formData,
                });
                const payload = await res.json().catch(() => ({}));
                if (!res.ok) throw Object.assign(new Error('Quick add failed'), { payload });

                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getInstance(modal)?.hide();
                }
                if (tasbeehId && payload.stats) {
                    const cardCol = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
                    if (cardCol) {
                        cardCol.dataset.baseTodayCompleted = String(payload.stats.today_completed ?? payload.stats.total_completed ?? 0);
                        cardCol.dataset.baseTotalCompleted = String(payload.stats.total_completed ?? 0);
                    }
                }
                if (window.PwaSync && typeof window.PwaSync.broadcastZikrCountUpdate === 'function' && tasbeehId) {
                    window.PwaSync.broadcastZikrCountUpdate(tasbeehId, countVal);
                }
                const countInput = document.getElementById('quickAddCountInput');
                if (countInput) countInput.value = '';
                showFlashToast(payload.message || `+${countVal.toLocaleString()} Zikr added successfully.`, 'success');
            } catch (err) {
                console.warn('Quick add online request failed, saving to offline outbox:', err);
                if (window.PwaSync && tasbeehId) {
                    await window.PwaSync.saveZikrCount(tasbeehId, countVal, null, true);
                }
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getInstance(modal)?.hide();
                }
                const countInput = document.getElementById('quickAddCountInput');
                if (countInput) countInput.value = '';
                showFlashToast(`+${countVal.toLocaleString()} Zikr saved offline and updated on screen!`, 'info');
            } finally {
                if (btn) btn.disabled = false;
            }
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

    // Auto-scroll and highlight target Tasbeeh card on page load or hash change
    function handleTasbeehCardHashScroll() {
        const hash = window.location.hash;
        if (!hash || !hash.startsWith('#tasbeeh-card-')) return;

        const targetEl = document.querySelector(hash);
        if (targetEl) {
            setTimeout(() => {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.classList.add('tasbeeh-card-highlighted');
                setTimeout(() => {
                    targetEl.classList.remove('tasbeeh-card-highlighted');
                }, 2800);
            }, 150);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleTasbeehCardHashScroll);
    } else {
        handleTasbeehCardHashScroll();
    }
    window.addEventListener('hashchange', handleTasbeehCardHashScroll);

})();
