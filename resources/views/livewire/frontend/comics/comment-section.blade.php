<?php

use App\Models\Chapter;
use App\Models\Comment;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Chapter $chapter;

    // State Form Utama
    public string $body = '';
    public bool $isSpoiler = false;

    // State Form Balasan
    public array $replyBodies = [];
    public array $replySpoilers = [];

    // State Sorting & Limit
    public string $sortBy = 'newest';
    public int $limit = 10;

    public function with(): array
    {
        $userId = Auth::id();

        // FAKTA: Eager Loading canggih. Hanya muat reaksi milik user yang sedang Login
        $query = Comment::with([
            'user',
            'replies.user',
            'reactions' => function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
            },
            'replies.reactions' => function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
            },
        ])
            ->where('chapter_id', $this->chapter->id)
            ->whereNull('parent_id');

        if ($this->sortBy === 'top') {
            $query->orderByDesc('likes_count');
        } else {
            $query->latest();
        }

        $comments = $query->take($this->limit)->get();
        $totalComments = Comment::where('chapter_id', $this->chapter->id)->whereNull('parent_id')->count();

        return compact('comments', 'totalComments');
    }

    public function postComment()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }
        $this->validate(['body' => 'required|string|max:1000']);

        Comment::create([
            'user_id' => Auth::id(),
            'chapter_id' => $this->chapter->id,
            'body' => $this->body,
            'is_spoiler' => $this->isSpoiler,
        ]);

        $this->reset(['body', 'isSpoiler']);
        $this->sortBy = 'newest';
    }

    public function postReply($parentId)
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $replyBody = $this->replyBodies[$parentId] ?? '';
        $isReplySpoiler = $this->replySpoilers[$parentId] ?? false;

        $this->validate(['replyBodies.' . $parentId => 'required|string|max:1000'], ['replyBodies.' . $parentId . '.required' => 'Balasan tidak boleh kosong.']);

        Comment::create([
            'user_id' => Auth::id(),
            'chapter_id' => $this->chapter->id,
            'parent_id' => $parentId,
            'body' => $replyBody,
            'is_spoiler' => $isReplySpoiler,
        ]);

        $this->replyBodies[$parentId] = '';
        $this->replySpoilers[$parentId] = false;
    }

    // FAKTA: Logika Cerdas Toggle Like (Batal, Tambah, atau Pindah dari Dislike)
    public function like($commentId)
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $comment = Comment::findOrFail($commentId);
        $reaction = $comment->reactions()->where('user_id', Auth::id())->first();

        if ($reaction) {
            if ($reaction->is_dislike) {
                // User sebelumnya Dislike, sekarang pindah ke Like
                $reaction->update(['is_dislike' => false]);
                $comment->decrement('dislikes_count');
                $comment->increment('likes_count');
            } else {
                // User klik Like 2x, artinya Batal Like
                $reaction->delete();
                $comment->decrement('likes_count');
            }
        } else {
            // User belum pernah interaksi, tambah Like baru
            $comment->reactions()->create(['user_id' => Auth::id(), 'is_dislike' => false]);
            $comment->increment('likes_count');
        }
    }

    // FAKTA: Logika Cerdas Toggle Dislike
    public function dislike($commentId)
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $comment = Comment::findOrFail($commentId);
        $reaction = $comment->reactions()->where('user_id', Auth::id())->first();

        if ($reaction) {
            if (!$reaction->is_dislike) {
                // User sebelumnya Like, sekarang pindah ke Dislike
                $reaction->update(['is_dislike' => true]);
                $comment->decrement('likes_count');
                $comment->increment('dislikes_count');
            } else {
                // User klik Dislike 2x, artinya Batal Dislike
                $reaction->delete();
                $comment->decrement('dislikes_count');
            }
        } else {
            // User belum pernah interaksi, tambah Dislike baru
            $comment->reactions()->create(['user_id' => Auth::id(), 'is_dislike' => true]);
            $comment->increment('dislikes_count');
        }
    }

    public function setSort($sort)
    {
        $this->sortBy = $sort;
        $this->limit = 10;
    }
    public function loadMore()
    {
        $this->limit += 10;
    }
}; ?>

<div class="mb-6">
    <h3 class="text-[16px] font-bold text-black mb-4 uppercase">Komentar <span
            class="text-gray-400 font-normal ml-1">{{ $totalComments }}</span></h3>

    <div class="border border-gray-200 rounded p-4 mb-8 bg-white relative shadow-sm">
        @auth
            <textarea wire:model="body" rows="2" placeholder="Ketik komentar disini..."
                class="w-full border-none focus:ring-0 text-[14px] text-gray-800 placeholder-gray-400 resize-none bg-transparent p-0 mb-2"></textarea>
            <div class="flex justify-between items-center mt-2 pt-2">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center cursor-pointer group">
                        <span
                            class="text-[12px] font-bold text-gray-400 mr-2 group-hover:text-gray-600 transition">Spoiler</span>
                        <div class="relative inline-block w-8 h-[18px] transition-colors duration-200 ease-in-out rounded-full"
                            :class="$wire.isSpoiler ? 'bg-[#00dc64]' : 'bg-gray-200'">
                            <input wire:model="isSpoiler" type="checkbox" class="opacity-0 w-0 h-0">
                            <span
                                class="absolute left-[2px] top-[2px] bg-white w-3.5 h-3.5 rounded-full transition-transform duration-200 ease-in-out shadow-sm"
                                :class="$wire.isSpoiler ? 'translate-x-[14px]' : 'translate-x-0'"></span>
                        </div>
                    </label>
                </div>
                <button wire:click="postComment" wire:loading.attr="disabled"
                    class="text-gray-300 hover:text-[#00dc64] transition-colors disabled:opacity-50 disabled:hover:text-gray-300 p-1">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                </button>
            </div>
        @else
            <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center rounded z-10">
                <p class="text-[13px] font-bold text-gray-600">Please <a href="{{ route('login') }}"
                        class="text-[#00dc64] hover:underline">Log In</a> to leave a comment.</p>
            </div>
            <textarea disabled rows="2" placeholder="Leave a comment"
                class="w-full border-none text-[14px] resize-none bg-transparent p-0 opacity-40"></textarea>
        @endauth
    </div>

    <div class="flex space-x-6 border-b border-gray-200 text-[12px] font-bold mb-6">
        <button wire:click="setSort('top')"
            class="pb-3 transition-colors {{ $sortBy === 'top' ? 'border-b-[3px] border-black text-black' : 'text-gray-400 hover:text-black' }}">TOP</button>
        <button wire:click="setSort('newest')"
            class="pb-3 transition-colors {{ $sortBy === 'newest' ? 'border-b-[3px] border-black text-black' : 'text-gray-400 hover:text-black' }}">NEWEST</button>
    </div>

    <div class="space-y-0">
        @forelse($comments as $comment)
            @php
                $userReaction = $comment->reactions->first();
                $isLiked = $userReaction && !$userReaction->is_dislike;
                $isDisliked = $userReaction && $userReaction->is_dislike;
            @endphp

            <div class="border-b border-gray-100 py-5 group" x-data="{ showReplies: false }">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[13px] text-gray-800 font-medium">{{ $comment->user->name }}</p>
                            @if ($comment->user->hasRole('admin'))
                                <span
                                    class="bg-blue-100 text-blue-600 text-[9px] font-bold px-1 py-0.5 rounded">Admin</span>
                            @endif
                            @if ($comment->user_id === $chapter->comic->author_id)
                                <span
                                    class="bg-[#00dc64]/10 text-[#00dc64] border border-[#00dc64]/20 text-[9px] font-bold px-1 py-0.5 rounded tracking-wide">Pembuat</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            {{ $comment->created_at->translatedFormat('M d, Y') }}</p>
                    </div>
                    <button
                        class="text-gray-300 hover:text-gray-500 opacity-0 group-hover:opacity-100 transition px-2">⋮</button>
                </div>

                @if ($sortBy === 'top' && $loop->index < 3 && $comment->likes_count > 0)
                    <span
                        class="inline-block border border-[#00dc64] text-[#00dc64] text-[10px] font-bold px-1.5 py-0.5 rounded-sm mb-2">TOP</span>
                @endif

                <div class="mb-4 text-[14px] text-gray-900 leading-relaxed" x-data="{ revealed: !{{ $comment->is_spoiler ? 'true' : 'false' }} }">
                    <template x-if="!revealed">
                        <div @click="revealed = true"
                            class="bg-gray-50 border border-gray-200 rounded p-4 text-center cursor-pointer hover:bg-gray-100 transition">
                            <span class="text-[12px] font-bold text-gray-500">⚠️ Komentar ini mengandung spoiler. Klik
                                untuk melihat.</span>
                        </div>
                    </template>
                    <template x-if="revealed">
                        <p class="whitespace-pre-wrap">{{ $comment->body }}</p>
                    </template>
                </div>

                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <button
                            @click="if (showReplies) { showReplies = false; } else { showReplies = true; $nextTick(() => { document.getElementById('reply-input-{{ $comment->id }}').focus() }); }"
                            class="text-[12px] font-bold text-gray-500 transition flex items-center space-x-1.5 p-1 group/btn">
                            <span
                                x-text="showReplies ? 'Sembunyikan Balasan' : 'Lihat Balasan ({{ $comment->replies->count() }})'"
                                class="group-hover/btn:text-black"></span>
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover/btn:text-gray-700 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :class="{ 'rotate-180': showReplies }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center space-x-1.5">
                        <button wire:click="like({{ $comment->id }})"
                            class="flex items-center space-x-1.5 px-2 py-1 rounded transition border {{ $isLiked ? 'bg-[#00dc64]/10 border-[#00dc64]/30 text-[#00dc64]' : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100' }}">
                            <svg class="w-4 h-4 mb-0.5 {{ $isLiked ? 'fill-current' : 'fill-none' }}"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5">
                                </path>
                            </svg>
                            <span
                                class="text-[11px] font-bold">{{ $comment->likes_count > 0 ? $comment->likes_count : '' }}</span>
                        </button>
                        <button wire:click="dislike({{ $comment->id }})"
                            class="flex items-center space-x-1.5 px-2 py-1 rounded transition border {{ $isDisliked ? 'bg-red-50 border-red-200 text-red-500' : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100' }}">
                            <svg class="w-4 h-4 mt-0.5 {{ $isDisliked ? 'fill-current' : 'fill-none' }}"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5">
                                </path>
                            </svg>
                            <span
                                class="text-[11px] font-bold">{{ $comment->dislikes_count > 0 ? $comment->dislikes_count : '' }}</span>
                        </button>
                    </div>
                </div>

                <div x-show="showReplies" x-collapse class="mt-4 space-y-4 pl-4 border-l-2 border-gray-100 ml-2">
                    @foreach ($comment->replies as $reply)
                        @php
                            $replyReaction = $reply->reactions->first();
                            $isReplyLiked = $replyReaction && !$replyReaction->is_dislike;
                            $isReplyDisliked = $replyReaction && $replyReaction->is_dislike;
                        @endphp

                        <div class="group/reply">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="text-[12px] text-gray-800 font-medium">{{ $reply->user->name }}</p>
                                        @if ($reply->user->hasRole('admin'))
                                            <span
                                                class="bg-blue-100 text-blue-600 text-[9px] font-bold px-1 py-0.5 rounded">Admin</span>
                                        @endif
                                        @if ($reply->user_id === $chapter->comic->author_id)
                                            <span
                                                class="bg-[#00dc64]/10 text-[#00dc64] border border-[#00dc64]/20 text-[9px] font-bold px-1 py-0.5 rounded tracking-wide">KREATOR</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $reply->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="mb-2 text-[13px] text-gray-800 leading-relaxed" x-data="{ revealed: !{{ $reply->is_spoiler ? 'true' : 'false' }} }">
                                <template x-if="!revealed">
                                    <div @click="revealed = true"
                                        class="bg-gray-50 border border-gray-200 rounded py-2 px-3 text-center cursor-pointer hover:bg-gray-100 transition inline-block">
                                        <span class="text-[11px] font-bold text-gray-500">⚠️ Balasan mengandung
                                            spoiler. Klik untuk melihat.</span>
                                    </div>
                                </template>
                                <template x-if="revealed">
                                    <p class="whitespace-pre-wrap">{{ $reply->body }}</p>
                                </template>
                            </div>

                            <div class="flex justify-end items-center space-x-1.5">
                                <button wire:click="like({{ $reply->id }})"
                                    class="flex items-center space-x-1 px-2 py-1 rounded transition border {{ $isReplyLiked ? 'bg-[#00dc64]/10 border-[#00dc64]/30 text-[#00dc64]' : 'bg-white border-gray-200 text-gray-400 hover:bg-gray-50' }}">
                                    <svg class="w-3 h-3 mb-0.5 {{ $isReplyLiked ? 'fill-current' : 'fill-none' }}"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 01-2 2h-2.5">
                                        </path>
                                    </svg>
                                    @if ($reply->likes_count > 0)
                                        <span class="text-[10px] font-bold">{{ $reply->likes_count }}</span>
                                    @endif
                                </button>
                                <button wire:click="dislike({{ $reply->id }})"
                                    class="flex items-center space-x-1 px-2 py-1 rounded transition border {{ $isReplyDisliked ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-400 hover:bg-gray-50' }}">
                                    <svg class="w-3 h-3 mt-0.5 {{ $isReplyDisliked ? 'fill-current' : 'fill-none' }}"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5">
                                        </path>
                                    </svg>
                                    @if ($reply->dislikes_count > 0)
                                        <span class="text-[10px] font-bold">{{ $reply->dislikes_count }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endforeach

                    @auth
                        <div class="flex flex-col gap-1 pl-2 pt-3 group/form" x-data="{ focused: false }">
                            <div class="flex gap-3 items-start">
                                <div class="flex-1">
                                    <textarea wire:model="replyBodies.{{ $comment->id }}" id="reply-input-{{ $comment->id }}" rows="1"
                                        placeholder="Balas ke {{ $comment->user->name }}..." @focus="focused = true" @blur="focused = false"
                                        class="w-full text-[13px] bg-gray-50 border border-gray-200 rounded focus:ring-0 focus:border-[#00dc64] resize-none py-2 px-3 transition-all"
                                        :class="{ 'bg-white border-[#00dc64] shadow-inner': focused }"></textarea>
                                    @error('replyBodies.' . $comment->id)
                                        <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button wire:click="postReply({{ $comment->id }})"
                                    class="bg-black text-white px-5 py-2 rounded text-[12px] font-bold hover:bg-gray-800 transition whitespace-nowrap shadow-sm transition-opacity duration-200 mt-0.5"
                                    :class="focused ? 'opacity-100' : 'opacity-0 group-hover/form:opacity-100'">Kirim</button>
                            </div>

                            <div class="flex items-center transition-opacity duration-200"
                                :class="focused ? 'opacity-100' : 'opacity-0 group-hover/form:opacity-100'">
                                <label class="flex items-center cursor-pointer group/spoiler mt-1">
                                    <span
                                        class="text-[10px] font-bold text-gray-400 mr-2 group-hover/spoiler:text-gray-600 transition">Spoiler</span>
                                    <div class="relative inline-block w-6 h-[14px] transition-colors duration-200 ease-in-out rounded-full"
                                        :class="$wire.get('replySpoilers.{{ $comment->id }}') ? 'bg-[#00dc64]' : 'bg-gray-200'">
                                        <input wire:model="replySpoilers.{{ $comment->id }}" type="checkbox"
                                            class="opacity-0 w-0 h-0">
                                        <span
                                            class="absolute left-[2px] top-[2px] bg-white w-2.5 h-2.5 rounded-full transition-transform duration-200 ease-in-out shadow-sm"
                                            :class="$wire.get('replySpoilers.{{ $comment->id }}') ? 'translate-x-[10px]' :
                                                'translate-x-0'"></span>
                                    </div>
                                </label>
                            </div>

                        </div>
                    @else
                    @endauth
                </div>

            </div>
        @empty
            <div class="text-center py-10 border border-dashed border-gray-200 rounded-lg bg-gray-50">
                <p class="text-gray-400 text-[13px] font-medium">Belum ada komentar. Jadilah yang pertama berkomentar!
                </p>
            </div>
        @endforelse

        @if ($totalComments > $limit)
            <div class="pt-4">
                <button wire:click="loadMore"
                    class="w-full py-3 text-[13px] font-bold text-gray-500 hover:text-black bg-white hover:bg-gray-50 border border-gray-200 rounded-full transition">Lebih
                    banyak komentar ∨</button>
            </div>
        @endif
    </div>
</div>
