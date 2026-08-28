@extends('layouts.admin')
@section('title', $config['label'])
@section('page_title', $config['label'])
@section('content')
<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Manage {{ strtolower($config['label']) }}</h2>
            <p class="text-muted-custom small mb-0 mt-1">Create, update, publish, or remove portfolio content.</p>
        </div>
        <div class="responsive-actions">
            @if($type === 'experiences' && $experienceProfile)
                @can('update profile')
                    <form class="experience-source-form" method="POST" action="{{ route('admin.profile.experience-source') }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="use_experience_dates" value="0">
                        <label class="experience-source-toggle {{ $experienceProfile->use_experience_dates ? 'is-on' : 'is-off' }}">
                            <span class="experience-source-copy">
                                <strong>Experience {{ $experienceProfile->use_experience_dates ? 'On' : 'Off' }}</strong>
                                <small>{{ $experienceProfile->use_experience_dates ? 'Homepage dates se total show hoga' : 'Homepage manual years show hoga' }}</small>
                            </span>
                            <input
                                type="checkbox"
                                name="use_experience_dates"
                                value="1"
                                data-auto-submit
                                @checked($experienceProfile->use_experience_dates)
                                aria-label="Toggle homepage experience calculation"
                            >
                            <span class="experience-source-switch" aria-hidden="true"></span>
                        </label>
                    </form>
                @endcan
            @endif
            @can('create '.$config['resource'])
                <a class="btn btn-accent btn-sm" href="{{ route('admin.content.create', $type) }}">
                    <i class="bi bi-plus-lg me-1"></i>Add new
                </a>
            @endcan
        </div>
    </div>

    <div class="admin-list-toolbar">
        @include('admin.partials.live-search', [
            'action' => route('admin.content.index', $type),
            'searchId' => 'content-search',
            'placeholder' => 'Search '.strtolower($config['label']).'...',
        ])
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="admin-list-summary">
            @if(request('q'))
                Search results for "{{ request('q') }}"
            @else
                {{ $config['label'] }} list
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Details</th>
                        @if($type === 'experiences')
                            <th>Experience</th>
                        @endif
                        @if($type === 'projects')
                            <th>Type</th>
                            <th>Total Views</th>
                        @endif
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $title = $item->title ?? $item->name ?? $item->position ?? $item->degree ?? 'Item #'.$item->id;
                            $detail = $item->company ?? $item->institution ?? $item->category ?? $item->excerpt ?? $item->short_description ?? null;
                            $hasStatus = in_array($type, ['projects','posts','services','skills'], true);
                            $isLive = $type === 'projects' || $type === 'posts' ? $item->is_published : ($item->is_active ?? true);
                            $imageUrl = in_array($type, ['projects', 'posts'], true) ? $item->image_url : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="content-list-item">
                                    @if(in_array($type, ['projects', 'posts'], true))
                                        <div class="content-thumb {{ $imageUrl ? 'has-image' : '' }}">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $title }} image">
                                            @else
                                                <i class="bi bi-image"></i>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <strong>{{ $title }}</strong>
                                        @if(($item->is_featured ?? false))
                                            <span class="status-badge new ms-2">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-muted-custom">{{ Str::limit($detail, 70) }}</span></td>
                            @if($type === 'experiences')
                                <td>
                                    <span class="duration-pill">{{ $item->formatted_duration }}</span>
                                    <small class="duration-range">
                                        {{ $item->start_date?->format('M Y') }} - {{ $item->is_current ? 'Present' : $item->end_date?->format('M Y') }}
                                    </small>
                                </td>
                            @endif
                            @if($type === 'projects')
                                <td>
                                    <span class="project-type-badge {{ $item->project_type_badge_class }}">
                                        @if(($item->project_type ?? 'full_development') === 'modification_enhancement')
                                            <i class="bi bi-gear-wide-connected me-1"></i>
                                        @else
                                            <i class="bi bi-hammer me-1"></i>
                                        @endif
                                        {{ $item->project_type_label }}
                                    </span>
                                    <small class="text-muted-custom d-block mt-1">{{ $item->project_type_description }}</small>
                                </td>
                                <td>
                                    <span class="duration-pill">{{ number_format($item->project_views_count ?? 0) }} views</span>
                                    <small class="text-muted-custom d-block mt-1">unique visitor count</small>
                                </td>
                            @endif
                            <td>
                                @if($hasStatus)
                                    <span class="status-badge {{ $isLive ? 'live' : 'draft' }}">{{ $isLive ? 'Live' : 'Hidden' }}</span>
                                @else
                                    <span class="text-muted-custom">Order {{ $item->sort_order ?? 0 }}</span>
                                @endif
                            </td>
                            <td>{{ $item->updated_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    @can('update '.$config['resource'])
                                        <a class="btn-icon" href="{{ route('admin.content.edit', ['type' => $type, 'id' => $item->id]) }}" aria-label="Edit {{ $title }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete '.$config['resource'])
                                        <form method="POST" action="{{ route('admin.content.destroy', ['type' => $type, 'id' => $item->id]) }}" data-confirm="Delete this item permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" aria-label="Delete {{ $title }}">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + ($type === 'experiences' ? 1 : 0) + ($type === 'projects' ? 2 : 0) }}" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">
                                    @if(request('q'))
                                        No matching items found for "{{ request('q') }}".
                                    @else
                                        No items found. Add your first one.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $items])
            </div>
        @endif
    </div>
</section>
@endsection
