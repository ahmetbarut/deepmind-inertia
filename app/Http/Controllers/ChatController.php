<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Models\Chat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Chats/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($chat = null)
    {
        if ($chat === null && Chat::count() === 0) {
            $chat = Chat::create([
                'name' => 'General',
                'user_id' => auth()->user()->id,
            ]);
        } elseif ($chat === null && Chat::has('items', '=', 0)->where('user_id', auth()->id())->count() > 0) {
            $chat = Chat::has('items', '=', 0)->where('user_id', auth()->id())->first();
        } elseif ($chat === null) {
            $chat = Chat::create([
                'name' => 'General',
                'user_id' => auth()->user()->id,
            ]);
        } else {
            $chat = Chat::findOrFail($chat);
        }

        return Inertia::render('Chats/Show', [
            'chat' => $chat->load('items'),
            'apiKey' => config('services.google.deepmind.key'),
            'chats' => Chat::where('user_id', auth()->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatRequest $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        //
    }

    public function storeItem(Chat $chat, Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'response' => 'required|string'
        ]);

        foreach ($request->only(['prompt', 'response']) as $key => $value) {
            $chat->items()->create([
                'user_id' => auth()->user()->id,
                'message' => $value,
                'type' => $key,
            ]);
        }

        return redirect()->back();
    }
}
