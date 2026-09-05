@extends('layouts.admin')
@section('title', 'Programs')
@section('page_title', 'Programs')

@section('content')
<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Programs</h2>
        </div>

        <div class="head-search-wrap flex-grow-1">
            <form class="admin-search financial-filter" method="GET" action="{{ route('admin.programs.index') }}" data-live-search data-live-search-target="#admin-list-results">
                <div class="search-field flex-grow-1">
                    <i class="bi bi-search"></i>
                    <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search program, location, city...">
                    <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <select class="form-select filter-select" name="status">
                    <option value="">All status</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </form>
        </div>

        <div class="responsive-actions">
            <button class="btn btn-accent btn-sm" data-crud-open data-modal="#programModal" data-store-url="{{ route('admin.programs.store') }}">
                <i class="bi bi-plus-lg me-1"></i>Add Program
            </button>
        </div>
    </div>

    <div id="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Location (City)</th>
                        <th>Date</th>
                        <th>Income Sources</th>
                        <th>Total Income</th>
                        <th>Expenses</th>
                        <th>Remaining Balance</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $row)
                        <tr>
                            <td>
                                <strong>{{ $row->name }}</strong>
                            </td>
                            <td>
                                @if($row->city)
                                    <span class="duration-pill"><i class="bi bi-geo-alt me-1"></i>{{ $row->city->name }}</span>
                                @elseif($row->location)
                                    <span class="duration-pill"><i class="bi bi-geo-alt me-1"></i>{{ $row->location }}</span>
                                @else
                                    <span class="text-muted-custom">-</span>
                                @endif
                            </td>
                            <td>{{ $row->program_date?->format('M d, Y') ?: '-' }}</td>
                            <td>{{ $row->contributors_count }}</td>
                            <td class="text-success"><strong>Rs. {{ number_format($row->total_received, 2) }}</strong></td>
                            <td>Rs. {{ number_format($row->total_expenses, 2) }}</td>
                            <td><strong>Rs. {{ number_format($row->remaining_balance, 2) }}</strong></td>
                            <td>
                                <span class="status-badge {{ $row->status === 'active' ? 'live' : 'draft' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" data-crud-edit data-modal="#programModal" data-action="{{ route('admin.programs.update', $row) }}" data-record="{{ json_encode(['name' => $row->name, 'program_date' => $row->program_date?->format('Y-m-d'), 'city_id' => $row->city_id, 'location' => $row->location, 'description' => $row->description, 'status' => $row->status]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $row) }}" data-ajax-delete data-confirm="Delete this program? Programs with transactions will only be made inactive.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-icon danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted-custom">No programs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            @include('admin.partials.pagination', ['paginator' => $programs])
        </div>
    </div>
</section>

{{-- Program Modal --}}
<div class="modal fade finance-modal" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form>
                <input type="hidden" name="_method" value="PUT" data-method disabled>
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title>Add Program</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" placeholder="e.g. Ramadan Relief 2026" required>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program Date</label>
                            <input class="form-control" name="program_date" type="date">
                            <div class="invalid-feedback" data-error-for="program_date"></div>
                        </div>

                        {{-- Location (City) with Live Search & Plus (+) Icon --}}
                        <div class="col-md-6">
                            <label class="form-label">Location (City)</label>
                            <div class="city-input-group">
                                <div class="searchable-select-wrapper" id="programCitySearchableWrapper">
                                    <select class="form-select d-none" name="city_id" id="hiddenProgramCitySelect" data-city-select>
                                        <option value="">Select city</option>
                                        @foreach($cities as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="searchable-select-display" tabindex="0" id="programCitySelectDisplay">
                                        <span class="selected-text" id="programCitySelectDisplayText">Select city</span>
                                        <i class="bi bi-chevron-down small text-muted-custom"></i>
                                    </div>
                                    <div class="searchable-select-dropdown d-none" id="programCitySelectDropdown">
                                        <div class="searchable-select-search-box">
                                            <i class="bi bi-search search-icon"></i>
                                            <input type="text" class="search-input" id="programCitySearchInput" placeholder="Search city..." autocomplete="off">
                                        </div>
                                        <div class="searchable-select-options" id="programCitySelectOptions">
                                            <div class="searchable-option text-muted-custom" data-value="" data-text="Select city">
                                                <span>None / Clear</span>
                                            </div>
                                            @foreach($cities as $c)
                                                <div class="searchable-option" data-value="{{ $c->id }}" data-text="{{ $c->name }}">
                                                    <span>{{ $c->name }}</span>
                                                    @if($c->state)<small class="text-muted-custom">{{ $c->state }}</small>@endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-city-add" type="button" data-bs-toggle="modal" data-bs-target="#quickCityModal" title="Add new city">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-error-for="city_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback" data-error-for="status"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Details about this program..."></textarea>
                            <div class="invalid-feedback" data-error-for="description"></div>
                        </div>

                        {{-- Notes field removed from CRUD per user request --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit><span data-submit-label>Save Program</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Add City Modal --}}
<div class="modal fade finance-modal" id="quickCityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form data-quick-city data-store-url="{{ route('admin.cities.store') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add City</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">City Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" placeholder="e.g. Karachi, Lahore, Sukkur" required autofocus>
                        <input type="hidden" name="status" value="active">
                        <div class="invalid-feedback" data-error-for="name"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Province / State (Optional)</label>
                        <input class="form-control" name="state" placeholder="e.g. Sindh, Punjab, Balochistan, KPK">
                        <div class="invalid-feedback" data-error-for="state"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>Save City</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cityWrapper = document.getElementById('programCitySearchableWrapper');
    const citySelect = document.getElementById('hiddenProgramCitySelect');
    const cityDisplay = document.getElementById('programCitySelectDisplay');
    const cityDisplayText = document.getElementById('programCitySelectDisplayText');
    const cityDropdown = document.getElementById('programCitySelectDropdown');
    const citySearchInput = document.getElementById('programCitySearchInput');
    const cityOptionsContainer = document.getElementById('programCitySelectOptions');

    function syncCityDisplayFromSelect() {
        if (!citySelect || !cityDisplayText) return;
        const selectedOpt = citySelect.options[citySelect.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            cityDisplayText.textContent = selectedOpt.text;
            cityDisplayText.classList.remove('text-muted-custom');
            cityDisplayText.style.color = '#ffffff';
        } else {
            cityDisplayText.textContent = 'Select city';
            cityDisplayText.classList.add('text-muted-custom');
            cityDisplayText.style.color = '';
        }
    }

    if (cityDisplay && cityDropdown) {
        cityDisplay.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = !cityDropdown.classList.contains('d-none');
            if (isOpen) {
                closeCityDropdown();
            } else {
                openCityDropdown();
            }
        });

        function openCityDropdown() {
            cityDropdown.classList.remove('d-none');
            cityDisplay.classList.add('is-open');
            if (citySearchInput) {
                citySearchInput.value = '';
                filterCityOptions('');
                setTimeout(() => citySearchInput.focus(), 50);
            }
        }

        function closeCityDropdown() {
            cityDropdown.classList.add('d-none');
            cityDisplay.classList.remove('is-open');
        }

        function filterCityOptions(query) {
            const q = query.toLowerCase().trim();
            const options = cityOptionsContainer.querySelectorAll('.searchable-option');
            options.forEach(opt => {
                const text = (opt.dataset.text || '').toLowerCase();
                if (text.includes(q) || opt.dataset.value === '') {
                    opt.style.display = 'flex';
                } else {
                    opt.style.display = 'none';
                }
            });
        }

        if (citySearchInput) {
            citySearchInput.addEventListener('input', function () {
                filterCityOptions(this.value);
            });
            citySearchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        cityOptionsContainer.addEventListener('click', function (e) {
            const opt = e.target.closest('.searchable-option');
            if (!opt) return;
            const val = opt.dataset.value;
            citySelect.value = val;
            citySelect.dispatchEvent(new Event('change', { bubbles: true }));
            syncCityDisplayFromSelect();
            closeCityDropdown();
        });

        document.addEventListener('click', function (e) {
            if (!cityWrapper.contains(e.target)) {
                closeCityDropdown();
            }
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', syncCityDisplayFromSelect);
    }

    // Hook into Edit / Create clicks to sync city select
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('[data-crud-edit][data-modal="#programModal"]');
        if (editBtn) {
            setTimeout(syncCityDisplayFromSelect, 50);
        }
        const createBtn = e.target.closest('[data-crud-open][data-modal="#programModal"]');
        if (createBtn) {
            setTimeout(() => {
                if (citySelect) citySelect.value = '';
                syncCityDisplayFromSelect();
            }, 50);
        }
    });
});
</script>
@endpush
