<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::with(['product', 'buyer', 'seller', 'latestMessage'])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->where('user_id', '!=', $user->id)
                    ->whereNull('read_at'),
            ])
            ->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return view('conversations.index', compact('conversations'));
    }

    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        if ($product->user_id === $user->id) {
            return back()->with('error', 'Nevar sūtīt ziņu sev.');
        }

        $conversation = Conversation::findOrStart($product->id, $user->id, $product->user_id);

        if ($conversation->wasRecentlyCreated && $request->filled('message')) {
            $conversation->messages()->create([
                'user_id' => $user->id,
                'body' => $request->input('message'),
            ]);
            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403);
        }

        $conversation->load(['product', 'buyer', 'seller', 'messages.user']);

        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->get()
            ->each(fn ($message) => $message->markAsRead());

        return view('conversations.show', compact('conversation'));
    }

    public function startWithUser(Request $request, User $user)
    {
        $buyer = Auth::user();

        if ($user->id === $buyer->id) {
            return back()->with('error', 'Nevar sūtīt ziņu sev.');
        }

        $conversation = Conversation::findOrStart(null, $buyer->id, $user->id);

        if ($conversation->wasRecentlyCreated && $request->filled('message')) {
            $conversation->messages()->create([
                'user_id' => $buyer->id,
                'body' => $request->input('message'),
            ]);
            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('conversations.show', $conversation);
    }
}
