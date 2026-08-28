<div class="admin-card-body">
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label" for="name">Permission name *</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $permission->name) }}" required>
            <p class="text-muted-custom small mb-0 mt-2">Example: view user, create role, update project</p>
        </div>
    </div>
</div>
