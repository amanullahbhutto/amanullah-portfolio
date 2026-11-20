@if($selectedUser ?? ($user ?? null))
<div class="zikr-filter-card mb-4 p-3 rounded-4 border position-relative" style="background: linear-gradient(135deg, rgba(8, 17, 30, 0.95) 0%, rgba(13, 30, 51, 0.95) 100%); border-color: rgba(6, 182, 212, 0.3) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);">
    <form method="GET" action="{{ url()->current() }}" id="zikr-date-filter-form" class="m-0">
        @if(request('user_id'))
            <input type="hidden" name="user_id" value="{{ request('user_id') }}" id="filter_user_id">
        @endif

        {{-- Top Header: Title & Clear Action --}}
        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom border-secondary border-opacity-10">
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.18); color: #06b6d4; font-size: 1rem; box-shadow: 0 0 10px rgba(6, 182, 212, 0.2);">
                    <i class="bi bi-calendar-range"></i>
                </div>
                <div>
                    <span class="fw-bold text-uppercase d-block" style="color: #38bdf8; font-size: 0.75rem; letter-spacing: 0.6px;">
                        Date Range Filter
                    </span>
                </div>
            </div>

            <button type="button" id="zikr-filter-clear-btn" class="btn btn-sm btn-outline-danger px-2.5 py-1 d-inline-flex align-items-center gap-1 {{ empty($summary['date_range']['is_filtered']) ? 'd-none' : '' }}" style="border-radius: 8px; font-size: 0.72rem; border-color: rgba(239, 68, 68, 0.35); color: #f87171; background: rgba(239, 68, 68, 0.08);" title="Reset Filter">
                <i class="bi bi-x-circle"></i> Clear
            </button>
        </div>

        {{-- Quick Presets: Sleek touch-scroll chips on mobile, wrapping pills on desktop --}}
        <div class="zikr-presets-wrapper mb-2.5 position-relative">
            <div class="zikr-presets-scroll d-flex align-items-center gap-1.5 overflow-x-auto py-1">
                <span class="small text-muted-custom flex-shrink-0 me-1" style="font-size: 0.7rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-0.5"></i>Quick:
                </span>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="today">Today</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="yesterday">Yesterday</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="thisWeek">This Week</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="lastWeek">Last Week</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="last7">Last 7 Days</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="thisMonth">This Month</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="lastMonth">Last Month</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="thisYear">This Year</button>
                <button type="button" class="btn btn-sm font-monospace zikr-preset-btn" data-preset="lastYear">Last Year</button>
            </div>
        </div>

        {{-- Custom Date Inputs & Action Buttons Row --}}
        <div class="row g-2 align-items-center">
            <div class="col-6 col-md-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text border-end-0 text-muted-custom fw-semibold" style="background: rgba(8, 17, 30, 0.85); border-color: rgba(6, 182, 212, 0.35); font-size: 0.72rem; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">From</span>
                    <input type="date" name="from_date" id="filter_from_date" class="form-control form-control-sm font-monospace text-white border-start-0" value="{{ request('from_date') }}" style="background: #08111e; border-color: rgba(6, 182, 212, 0.35); border-top-right-radius: 10px; border-bottom-right-radius: 10px; font-size: 0.78rem; padding: 6px 8px; color-scheme: dark;">
                </div>
            </div>

            <div class="col-6 col-md-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text border-end-0 text-muted-custom fw-semibold" style="background: rgba(8, 17, 30, 0.85); border-color: rgba(6, 182, 212, 0.35); font-size: 0.72rem; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">To</span>
                    <input type="date" name="to_date" id="filter_to_date" class="form-control form-control-sm font-monospace text-white border-start-0" value="{{ request('to_date') }}" style="background: #08111e; border-color: rgba(6, 182, 212, 0.35); border-top-right-radius: 10px; border-bottom-right-radius: 10px; font-size: 0.78rem; padding: 6px 8px; color-scheme: dark;">
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <button type="submit" id="zikr-filter-submit-btn" class="btn btn-sm btn-info text-dark fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1.5 px-3" style="border-radius: 10px; font-size: 0.8rem; height: 33px; box-shadow: 0 0 12px rgba(6, 182, 212, 0.25);">
                    <span class="spinner-border spinner-border-sm d-none" id="zikr-filter-spinner" role="status" aria-hidden="true" style="width: 0.75rem; height: 0.75rem;"></span>
                    <i class="bi bi-funnel-fill" id="zikr-filter-btn-icon"></i>
                    <span id="zikr-filter-btn-text">Apply Filter</span>
                </button>
            </div>
        </div>

        {{-- Filter Result Pill (When Filtered) --}}
        <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-10 {{ empty($summary['date_range']['is_filtered']) ? 'd-none' : '' }}" id="zikr-filter-result-container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-2 rounded-3 border" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.18) 0%, rgba(16, 185, 129, 0.12) 100%); border-color: rgba(6, 182, 212, 0.45); box-shadow: 0 0 15px rgba(6, 182, 212, 0.15);">
                <div class="d-flex align-items-center gap-2">
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
    </form>
</div>

<style>
.zikr-presets-scroll {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.zikr-presets-scroll::-webkit-scrollbar {
    display: none;
}
.zikr-preset-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    font-size: 0.72rem;
    padding: 4px 11px;
    border-radius: 50rem;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.zikr-preset-btn:hover {
    background: rgba(6, 182, 212, 0.15);
    border-color: rgba(6, 182, 212, 0.4);
    color: #e0f2fe;
}
.zikr-preset-btn.active {
    background: rgba(6, 182, 212, 0.25) !important;
    border-color: #38bdf8 !important;
    color: #38bdf8 !important;
    box-shadow: 0 0 10px rgba(6, 182, 212, 0.35);
    font-weight: 600;
}
@media (min-width: 992px) {
    .zikr-presets-scroll {
        flex-wrap: wrap;
        overflow-x: visible;
    }
}
</style>

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
            btnText.textContent = 'Apply Filter';
        }
    }

    function resetPresetHighlights() {
        document.querySelectorAll('.zikr-preset-btn').forEach(function (b) {
            b.classList.remove('active');
        });
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
                if (detailsEl) {
                    detailsEl.textContent = `${daysLabel} (Avg: ${Number(dr.avg_per_day).toLocaleString()}/day) • Active on ${activeLabel}`;
                }

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
        resetPresetHighlights();
        performFilter(fromInput.value, toInput.value);
    });

    // Clear button event -> Clear inputs & trigger AJAX reset
    clearBtn.addEventListener('click', function () {
        fromInput.value = '';
        toInput.value = '';
        resetPresetHighlights();
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
            } else if (preset === 'thisMonth') {
                start = new Date(now.getFullYear(), now.getMonth(), 1);
            } else if (preset === 'thisWeek') {
                const day = now.getDay();
                const diffToMonday = (day === 0 ? -6 : 1) - day;
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diffToMonday);
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            } else if (preset === 'lastWeek') {
                const day = now.getDay();
                const diffToCurrentMonday = (day === 0 ? -6 : 1) - day;
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diffToCurrentMonday - 7);
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diffToCurrentMonday - 1);
            } else if (preset === 'lastMonth') {
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
            } else if (preset === 'thisYear') {
                start = new Date(now.getFullYear(), 0, 1);
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            } else if (preset === 'lastYear') {
                start = new Date(now.getFullYear() - 1, 0, 1);
                end = new Date(now.getFullYear() - 1, 11, 31);
            }

            const fromVal = formatYMD(start);
            const toVal = formatYMD(end);
            fromInput.value = fromVal;
            toInput.value = toVal;

            resetPresetHighlights();
            this.classList.add('active');

            performFilter(fromVal, toVal);
        });
    });
});
</script>
@endif