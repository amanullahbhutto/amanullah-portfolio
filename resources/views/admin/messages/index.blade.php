@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('page_title', 'Contact Messages')
@section('content')
<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Inbox</h2>
            <p class="text-muted-custom small mb-0 mt-1">Opening a new message marks it as read and updates the notification count.</p>
        </div>
        <div class="responsive-actions">
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.messages.index', array_filter(['filter' => request('filter') === 'unread' ? null : 'unread', 'q' => request('q')], fn ($value) => filled($value))) }}">
                {{ request('filter') === 'unread' ? 'Show all' : 'Unread only' }}
            </a>
            @can('update message')
                <form method="POST" action="{{ route('admin.messages.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-soft btn-sm" type="submit">Mark all read</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="admin-list-toolbar">
        @include('admin.partials.live-search', [
            'action' => route('admin.messages.index'),
            'searchId' => 'message-search',
            'placeholder' => 'Search messages...',
            'hiddenInputs' => ['filter' => request('filter')],
        ])
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="admin-list-summary">
            @if(request('q'))
                Search results for "{{ request('q') }}"
            @elseif(request('filter') === 'unread')
                Unread messages
            @else
                All messages
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>
                                <strong>{{ $message->name }}</strong><br>
                                <small class="text-muted-custom">{{ $message->email }}</small>
                            </td>
                            <td>{{ Str::limit($message->subject, 55) }}</td>
                            <td>
                                @if($message->read_at)
                                    <span class="status-badge live">Read</span>
                                @else
                                    <span class="status-badge new">New</span>
                                @endif
                            </td>
                            <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn-icon" href="{{ route('admin.messages.show', $message) }}" aria-label="Open message">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('delete message')
                                        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" aria-label="Delete message">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-envelope-open fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">
                                    @if(request('q'))
                                        No matching messages found for "{{ request('q') }}".
                                    @else
                                        No messages found.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $messages])
            </div>
        @endif
    </div>
</section>
@endsection
