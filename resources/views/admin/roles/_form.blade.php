@php
    $selectedPermissions = old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []);
@endphp

<div class="admin-card-body">
    @error('permissions')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <label class="form-label" for="name">Role name *</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required placeholder="e.g. Super Admin, Manager, Cashier">
            <p class="text-muted-custom small mb-0 mt-2">Example: Super Admin, Admin, Manager, Cashier, User</p>
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-md-end">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-theme" id="selectAllPermissionsBtn">
                    <i class="bi bi-check-all me-1"></i>Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-theme" id="deselectAllPermissionsBtn">
                    <i class="bi bi-x-lg me-1"></i>Deselect All
                </button>
            </div>
        </div>
    </div>

    <div class="permission-grid">
        @foreach($permissions as $group => $groupPermissions)
            <fieldset class="permission-panel">
                <legend>
                    <span>{{ $group }}</span>
                    <button class="permission-group-toggle" type="button" data-permission-toggle>
                        <i class="bi bi-check2-square me-1"></i>Toggle group
                    </button>
                </legend>

                <div class="permission-options">
                    @foreach($groupPermissions as $permission)
                        @php
                            $actionLabel = str_contains($permission->name, '.')
                                ? str($permission->name)->after('.')->headline()
                                : (str_contains($permission->name, ' ') ? str($permission->name)->before(' ')->headline() : str($permission->name)->headline());
                        @endphp
                        <label class="permission-option">
                            <input
                                class="form-check-input permission-checkbox"
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->name }}"
                                @checked(in_array($permission->name, $selectedPermissions, true))
                            >
                            <span>
                                <strong>{{ $actionLabel }}</strong>
                                <small>{{ $permission->name }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAllBtn = document.getElementById('selectAllPermissionsBtn');
    const deselectAllBtn = document.getElementById('deselectAllPermissionsBtn');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        });
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        });
    }
});
</script>
@endpush
