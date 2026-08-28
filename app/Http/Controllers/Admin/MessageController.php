<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view message', ['only' => ['index', 'show']]);
        $this->middleware('permission:update message', ['only' => ['markAllRead']]);
        $this->middleware('permission:delete message', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $messages = ContactMessage::query()
            ->when($request->string('filter')->toString() === 'unread', fn ($query) => $query->unread())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->markAsRead();

        return view('admin.messages.show', compact('contactMessage'));
    }

    public function markAllRead(): RedirectResponse
    {
        ContactMessage::query()->unread()->update(['read_at' => now()]);

        return back()->with('success', 'All messages marked as read.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted successfully.');
    }
}
