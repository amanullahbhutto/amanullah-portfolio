@if($selectedUser ?? ($user ?? null))
<div class="zikr-filter-card mb-4 p-3 rounded-4 border position-relative" style="background: linear-gradient(135deg, rgba(8, 17, 30, 0.95) 0%, rgba(13, 30, 51, 0.95) 100%); border-color: rgba(6, 182, 212, 0.3) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);">
    <form method="GET" action="{{ url()->current() }}" id="zikr-date-filter-form" class="m-0">
        @if(request('user_id'))
            <input type="hidden" name="user_id" value="{{ request('user_id') }}" id="filter_user_id">
        @endif

        <div class="row g-2 align-items-center">
            {{-- Left: Title & Icon --}}
            <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background: rgba(6, 182, 212, 0.18); color: #06b6d4; font-size: 1.1rem; box-shadow: 0 0 10px rgba(6, 182, 212, 0.2);">
                    <i class="bi bi-calendar-range"></i>
                </div>
                <div>
                    <span class="small fw-bold text-uppercase d-block" style="color: #38bdf8; font-size: 0.72rem; letter-spacing: 0.6px;">
                        Date Range Filter
                    </span>
                    <small class="text-muted-custom" style="font-size: 0.7rem;">
                        Kitne Zikr kiye (Instant AJAX)
                    </small>
                </div>
            </div>

            {{-- Middle: From & To Date Inputs + Buttons --}}
            <div class="col-12 col-md d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-1.5 flex-grow-1 flex-sm-grow-0">
                    <label for="filter_from_date" class="form-label mb-0 small text-muted-custom fw-semibold" style="font-size: 0.75rem;">From:</label>
                    <input type="date" name="from_date" id="filter_from_date" class="form-control form-control-sm font-monospace text-white flex-grow-1" value="{{ request('from_date') }}" style="background: #08111e; border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 10px; font-size: 0.82rem; padding: 5px 10px; min-width: 135px; color-scheme: dark;">
                </div>

                <div class="d-flex align-items-center gap-1.5 flex-grow-1 flex-sm-grow-0">
                    <label for="filter_to_date" class="form-label mb-0 small text-muted-custom fw-semibold" style="font-size: 0.75rem;">To:</label>
                    <input type="date" name="to_date" id="filter_to_date" class="form-control form-control-sm font-monospace text-white flex-grow-1" value="{{ request('to_date') }}" style="background: #08111e; border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 10px; font-size: 0.82rem; padding: 5px 10px; min-width: 135px; color-scheme: dark;">
                </div>

                <div class="d-flex align-items-center gap-1.5">
                    <button type="submit" id="zikr-filter-submit-btn" class="btn btn-sm btn-info text-dark fw-bold px-3 d-inline-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.8rem; padding: 6px 14px;">
                        <span class="spinner-border spinner-border-sm d-none" id="zikr-filter-spinner" role="status" aria-hidden="true" style="width: 0.75rem; height: 0.75rem;"></span>
                        <i class="bi bi-funnel-fill" id="zikr-filter-btn-icon"></i>
                        <span id="zikr-filter-btn-text">Filter</span>
                    </button>
                    <button type="button" id="zikr-filter-clear-btn" class="btn btn-sm btn-outline-secondary px-2.5 d-inline-flex align-items-center gap-1 {{ empty($summary['date_range']['is_filtered']) ? 'd-none' : '' }}" style="border-radius: 10px; font-size: 0.8rem; padding: 6px 10px;" title="Reset Filter">
                        <i class="bi bi-x-lg"></i> Clear
                    </button>
                </div>
            </div>

            {{-- Right: Filter Result Pill (If Filtered or updated via AJAX) --}}
            <div class="col-12 col-xl-auto ms-xl-auto {{ empty($summary['date_range']['is_filtered']) ? 'd-none' : '' }}" id="zikr-filter-result-container">
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border w-100" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(16, 185, 129, 0.15) 100%); border-color: rgba(6, 182, 212, 0.5); box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);">
                    <i class="bi bi-check2-circle text-info fs-5"></i>
                    <div>
                        <div class="small fw-semibold text-white" style="line-height: 1.2;">
                            Total Zikr: <strong class="text-warning font-monospace fs-6" id="zikr-filter-total-count">{{ number_format($summary['date_range']['total_count'] ?? 0) }}</strong>
                        </div>
                        <small class="text-muted-custom" style="font-size: 0.68rem;" id="zikr-filter-details">
                            @if(!empty($summary['date_range']['is_filtered']))
                                {{ $summary['date_range']['range_days'] }} {{ $summary['date_range']['range_days'] === 1 ? 'Day' : 'Days' }} (Avg: {{ number_format($summary['date_range']['avg_per_day'] ?? 0) }}/day) • Active on {{ $summary['date_range']['active_days'] }} {{ $summary['date_range']['active_days'] === 1 ? 'Day' : 'Days' }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Presets Row --}}
        <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2 mt-2 border-top border-secondary border-opacity-10">
            <span class="small text-muted-custom me-1" style="font-size: 0.7rem;"><i class="bi bi-lightning-charge text-warning me-0.5"></i>Quick:</span>
            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill font-monospace zikr-preset-btn" data-preset="today" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; font-size: 0.7rem;">Today</button>
            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill font-monospace zikr-preset-btn" data-preset="yesterday" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; font-size: 0.7rem;">Yesterday</button>
            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill font-monospace zikr-preset-btn" data-preset="last7" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; font-size: 0.7rem;">Last 7 Days</button>
            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill font-monospace zikr-preset-btn" data-preset="thisMonth" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; font-size: 0.7rem;">This Month</button>
            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill font-monospace zikr-preset-btn" data-preset="lastMonth" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; font-size: 0.7rem;">Last Month</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('zikr-date-filter-form');
    const fromInput = document.getElementById('filter_from_date');
    const toInput = document.getElementById('filter_to_date');
    const submitBtn = document.getElementById('zikr-filter-submit-btn');
    const clearBtn = document.getElementById('zikr-filter-clear-btn');
    const spinner = document.getElementById('zikr-filter-spinner');
    const btnIcon = document.getElementById('zikr-filter-btn-icon');
    const btnText = document.getElementById('zikr-filter-btn-text');
    const resultContainer = document.getElementById('zikr-filter-result-container');
    const totalCountEl = document.getElementById('zikr-filter-total-count');
    const detailsEl = document.getElementById('zikr-filter-details');
    const userIdInput = document.getElementById('filter_user_id');

    if (!form || !fromInput || !toInput) return;

    function formatYMD(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function setLoading(isLoading) {
        if (isLoading) {
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnIcon.classList.add('d-none');
            btnText.textContent = 'Filtering...';
        } else {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnIcon.classList.remove('d-none');
            btnText.textContent = 'Filter';
        }
    }

    // Perform AJAX request to fetch filtered stats without page reload
    function performFilter(fromDate, toDate) {
        const url = new URL(form.action || window.location.href);
        
        if (fromDate) {
            url.searchParams.set('from_date', fromDate);
        } else {
            url.searchParams.delete('from_date');
        }

        if (toDate) {
            url.searchParams.set('to_date', toDate);
        } else {
            url.searchParams.delete('to_date');
        }

        if (userIdInput && userIdInput.value) {
            url.searchParams.set('user_id', userIdInput.value);
        }

        setLoading(true);

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            setLoading(false);
            if (!data.success || !data.date_range) return;

            const dr = data.date_range;

            // Update URL in browser without reloading
            window.history.replaceState({}, '', url.toString());

            if (dr.is_filtered) {
                // Show result container
                resultContainer.classList.remove('d-none');
                clearBtn.classList.remove('d-none');

                totalCountEl.textContent = Number(dr.total_count).toLocaleString();
                const daysLabel = dr.range_days === 1 ? '1 Day' : `${dr.range_days} Days`;
                const activeLabel = dr.active_days === 1 ? '1 Day' : `${dr.active_days} Days`;
                detailsEl.textContent = `${daysLabel} (Avg: ${Number(dr.avg_per_day).toLocaleString()}/day) • Active on ${activeLabel}`;

                // Update individual tasbeeh card badges
                document.querySelectorAll('.zikr-period-count-badge').forEach(badge => {
                    const tId = badge.getAttribute('data-tasbeeh-id');
                    const count = (dr.tasbeeh_counts && dr.tasbeeh_counts[tId]) ? dr.tasbeeh_counts[tId] : 0;
                    const valEl = badge.querySelector('.zikr-period-val');
                    if (valEl) valEl.textContent = Number(count).toLocaleString();
                    badge.classList.remove('d-none');
                });
            } else {
                // Clear state
                resultContainer.classList.add('d-none');
                clearBtn.classList.add('d-none');
                document.querySelectorAll('.zikr-period-count-badge').forEach(badge => {
                    badge.classList.add('d-none');
                });
            }
        })
        .catch(err => {
            setLoading(false);
            console.error('Zikr filter error:', err);
        });
    }

    // Form submit event -> Prevent reload and trigger AJAX
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        performFilter(fromInput.value, toInput.value);
    });

    // Clear button event -> Clear inputs & trigger AJAX reset
    clearBtn.addEventListener('click', function () {
        fromInput.value = '';
        toInput.value = '';
        performFilter('', '');
    });

    // Quick Presets
    document.querySelectorAll('.zikr-preset-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const preset = this.dataset.preset;
            const now = new Date();
            let start = new Date();
            let end = new Date();

            if (preset === 'today') {
                // today
            } else if (preset === 'yesterday') {
                start.setDate(now.getDate() - 1);
                end.setDate(now.getDate() - 1);
            } else if (preset === 'last7') {
                start.setDate(now.getDate() - 6);
            } else if (preset === 'last30') {
                start.setDate(now.getDate() - 29);
            } else if (preset === 'thisMonth') {
                start = new Date(now.getFullYear(), now.getMonth(), 1);
            } else if (preset === 'lastMonth') {
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
            }

            const fromVal = formatYMD(start);
            const toVal = formatYMD(end);
            fromInput.value = fromVal;
            toInput.value = toVal;

            performFilter(fromVal, toVal);
        });
    });
});
</script>
@endif
