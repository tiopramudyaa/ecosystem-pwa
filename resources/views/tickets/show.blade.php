@extends('layouts.app')

@section('title', $ticket['ticket_number'] ?? 'Ticket')
@section('hideBottomNav', 'yes')
@section('backUrl', route('tickets.index'))

@section('subtitle')
    <span class="truncate" title="{{ $ticket['description'] ?? '-' }}">{{ $ticket['description'] ?? '-' }}</span>
    <span class="text-gray-300 shrink-0">&bull;</span>
    <span class="inline-flex items-center gap-1 shrink-0 truncate" title="{{ $ticket['customer']['customer_name'] ?? '-' }}">
        <i class="fas fa-building text-[10px] text-gray-400"></i>{{ $ticket['customer']['customer_name'] ?? '-' }}
    </span>
@endsection

@push('styles')
    <style>
        .message { transition: box-shadow 0.3s ease, border-color 0.3s ease; }
        .message.highlight-flash .bubble { box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.5); }

        .chat-panel {
            background-color: #e9e3d8;
            background-image: radial-gradient(rgba(0,0,0,0.035) 1px, transparent 1px);
            background-size: 16px 16px;
        }

        .bubble {
            max-width: 82%;
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        .bubble-own {
            background: #ffffff;
            color: #111827;
            border: 1px solid #e5e7eb;
            border-radius: 16px 16px 4px 16px;
        }

        .bubble-other {
            background: #ffffff;
            color: #111827;
            border: 1px solid #e5e7eb;
            border-radius: 16px 16px 16px 4px;
        }

        .bubble-note {
            background: #fef3c7 !important;
            border: 1px solid #fde68a !important;
            color: #78350f !important;
        }

        .chat-avatar {
            width: 28px;
            height: 28px;
            font-size: 11px;
        }

        .chat-input-bar textarea {
            resize: none;
            max-height: 6.5rem;
        }

        @media (max-width: 1023.98px) {
            form.chat-input-bar {
                position: fixed !important;
                left: 0 !important;
                right: 0 !important;
                bottom: calc(0.75rem + env(safe-area-inset-bottom)) !important;
                z-index: 30 !important;
            }

            #messages {
                padding-bottom: 6rem;
            }

            #messages .message:last-child {
                scroll-margin-bottom: 6rem;
            }
        }
    </style>
@endpush

@section('content')
    <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-sm primary-text hover:underline mb-4">
        <i class="fas fa-arrow-left text-xs"></i> Daftar Tiket
    </a>

    {{-- Desktop: detail selalu tampil inline --}}
    <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        @include('tickets.partials.detail-card', ['ticket' => $ticket])
    </div>

    {{-- Mobile: detail muncul sebagai modal saat nomor tiket di header diklik --}}
    <div id="ticket-detail-modal" class="hidden lg:hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" onclick="closeTicketDetail()"></div>
        <div class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto bg-white rounded-t-2xl shadow-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-700">Detail Tiket</span>
                <button type="button" onclick="closeTicketDetail()" class="icon-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @include('tickets.partials.detail-card', ['ticket' => $ticket])
        </div>
    </div>

    <h2 class="text-base font-semibold text-gray-900 mb-3">Ubah Status</h2>
    <form method="POST" action="{{ route('tickets.status', $ticket['ticket_id']) }}" class="flex gap-2 mb-6">
        @csrf
        @method('PATCH')
        <select name="status" class="primary-focus bg-white text-sm">
            @foreach (['open','inprocess','waiting_on_customer','waiting_on_3rd_party','waiting_to_confirmation','hold','cancelled','closed'] as $status)
                <option value="{{ $status }}" @selected(($ticket['status'] ?? '') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 rounded-lg primary-gradient text-white text-sm font-medium">Update Status</button>
    </form>

    <h2 class="text-base font-semibold text-gray-900 mb-3">Pesan</h2>

    @php
        $currentUserId = session('lite_api_user')['id'] ?? null;
        $currentUserRoleIds = session('lite_api_user')['role_ids'] ?? [];
        $isAdmin = in_array(1, $currentUserRoleIds, false);
        $lastSenderState = new \stdClass();
        $lastSenderState->value = null;
        $initialsOf = function ($name) {
            $name = trim((string) $name);
            if ($name === '') {
                return '?';
            }
            return collect(explode(' ', $name))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
        };
        $formatMessageTime = function ($value) {
            if (empty($value)) {
                return '-';
            }
            try {
                $date = \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
                return $date->isToday() ? $date->format('H:i') : $date->format('d M, H:i');
            } catch (\Exception $e) {
                return $value;
            }
        };
    @endphp

    <div id="messages" data-ticket-id="{{ (int) ($ticket['ticket_id'] ?? 0) }}" data-last-message-id="{{ (int) collect($messages)->max('id') }}" class="chat-panel rounded-xl border border-gray-200 p-3 sm:p-4 mb-6 space-y-1">
        @forelse ($messages as $message)
            @include('tickets.partials.message-item', [
                'message' => $message,
                'currentUserId' => $currentUserId,
                'isAdmin' => $isAdmin,
                'formatMessageTime' => $formatMessageTime,
                'initialsOf' => $initialsOf,
                'lastSenderState' => $lastSenderState,
            ])
        @empty
            <p class="text-sm text-gray-500 bg-white rounded-xl border border-gray-200 p-4 text-center">Belum ada pesan.</p>
        @endforelse
    </div>

    @php
        $normalizeEmailList = function ($list) {
            if (! is_array($list)) {
                return [];
            }
            return collect($list)->map(function ($item) {
                if (is_array($item)) {
                    return $item['address'] ?? $item['email'] ?? null;
                }
                return $item;
            })->filter()->values()->all();
        };
        $ticketToEmails = $normalizeEmailList($ticket['to_emails'] ?? []);
        $ticketCcEmails = $normalizeEmailList($ticket['cc_emails'] ?? []);
        $replyStatusOptions = ['inprocess', 'waiting_on_customer', 'waiting_to_confirmation', 'waiting_on_3rd_party', 'hold'];
    @endphp

    <form method="POST" action="{{ route('tickets.messages.store', $ticket['ticket_id']) }}"
          class="chat-input-bar relative bg-white border border-gray-200 shadow-lg lg:shadow-sm rounded-2xl lg:rounded-xl px-3 py-2.5 flex items-end gap-2 mx-4 lg:mx-0 has-[#note\_toggle:checked]:bg-amber-50 has-[#note\_toggle:checked]:border-amber-300 transition-colors"
          id="chat-form">
        @csrf

        <div id="composer-options" class="hidden absolute left-0 right-0 bottom-full mb-2 z-10 bg-white border border-gray-200 rounded-xl shadow-lg p-3 space-y-3 text-sm max-h-[70vh] overflow-y-auto">
            <div class="reply-only-option">
                <label class="block text-xs font-medium text-gray-600 mb-1">Penerima Email (To)</label>
                <div id="to-chips" class="flex flex-wrap gap-1.5 mb-1.5"></div>
                <div class="flex gap-1.5">
                    <input type="email" id="to-email-input" placeholder="Tambah email To lalu Enter"
                           class="primary-focus flex-1 bg-gray-50 text-xs rounded-lg border border-gray-200 px-2.5 py-1.5">
                    <button type="button" id="to-email-add" class="px-2.5 py-1.5 rounded-lg bg-gray-100 text-xs text-gray-600">Tambah</button>
                </div>
                <div id="to-emails-fields"></div>
                <input type="hidden" name="to_emails_touched" id="to_emails_touched" value="0">
            </div>

            <div class="reply-only-option">
                <label class="block text-xs font-medium text-gray-600 mb-1">Penerima Email (CC)</label>
                <div id="cc-chips" class="flex flex-wrap gap-1.5 mb-1.5"></div>
                <div class="flex gap-1.5">
                    <input type="email" id="cc-email-input" placeholder="Tambah email CC lalu Enter"
                           class="primary-focus flex-1 bg-gray-50 text-xs rounded-lg border border-gray-200 px-2.5 py-1.5">
                    <button type="button" id="cc-email-add" class="px-2.5 py-1.5 rounded-lg bg-gray-100 text-xs text-gray-600">Tambah</button>
                </div>
                <div id="cc-emails-fields"></div>
                <input type="hidden" name="cc_emails_touched" id="cc_emails_touched" value="0">
            </div>
        </div>

        <input type="hidden" name="message_type" id="message_type" value="reply">
        <input type="hidden" name="reply_to_id" id="reply_to_id" value="">

        <div id="reply-quote-banner" class="hidden absolute left-0 right-0 bottom-full mb-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 flex items-start gap-2">
            <div class="flex-1 min-w-0">
                <div class="text-[11px] font-semibold text-amber-800">Membalas <span id="reply-quote-sender"></span></div>
                <div id="reply-quote-text" class="text-xs text-amber-700 truncate"></div>
            </div>
            <button type="button" id="reply-quote-cancel" class="shrink-0 w-6 h-6 rounded-full text-amber-600 hover:bg-amber-100 flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <input type="checkbox" id="note_toggle" class="peer sr-only" onchange="document.getElementById('message_type').value = this.checked ? 'internal_note' : 'reply'; window.toggleComposerOptionsForNote && window.toggleComposerOptionsForNote(this.checked);">
        <label for="note_toggle"
               title="Tandai sebagai Internal Note"
               class="shrink-0 w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 cursor-pointer peer-checked:bg-amber-100 peer-checked:border-amber-300 peer-checked:text-amber-700 transition-colors">
            <i class="fas fa-lock text-sm"></i>
        </label>

        <button type="button" id="options-toggle" title="Opsi tambahan (status &amp; penerima email)"
                class="shrink-0 w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors">
            <i class="fas fa-sliders text-sm"></i>
        </button>

        <textarea name="message" id="chat-textarea" rows="1" required placeholder="Tulis pesan..."
                  class="primary-focus bg-gray-50 text-sm flex-1 rounded-2xl border border-gray-200 px-4 py-2 peer-checked:bg-amber-100 peer-checked:border-amber-200 transition-colors"></textarea>

        <button type="submit" class="shrink-0 w-10 h-10 rounded-full primary-gradient text-white flex items-center justify-center">
            <i class="fas fa-paper-plane text-sm"></i>
        </button>

        <div id="status-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm p-5">
                <div class="flex items-start justify-between mb-1">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Send &amp; Set Status</h3>
                        <p class="text-sm text-gray-400">Pilih status setelah reply dikirim</p>
                    </div>
                    <button type="button" id="status-modal-cancel" class="shrink-0 w-9 h-9 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <hr class="border-gray-100 my-3">

                <input type="hidden" id="status-modal-select" name="ticket_status" value="">

                @php
                    $statusModalOptions = [
                        'inprocess' => ['label' => 'Inprocess', 'desc' => 'Helpdesk sedang mengerjakan', 'icon' => 'fa-bolt', 'theme' => 'amber'],
                        'waiting_on_customer' => ['label' => 'Waiting on Customer', 'desc' => 'Menunggu balasan customer', 'icon' => 'fa-user', 'theme' => 'yellow'],
                        'waiting_to_confirmation' => ['label' => 'Waiting to Confirmation', 'desc' => 'Menunggu konfirmasi customer', 'icon' => 'fa-circle-check', 'theme' => 'teal'],
                        'waiting_on_3rd_party' => ['label' => 'Waiting on 3rd Party', 'desc' => 'Diteruskan ke SAP / pihak ketiga', 'icon' => 'fa-people-arrows', 'theme' => 'indigo'],
                        'hold' => ['label' => 'Hold', 'desc' => 'Ticket ditahan sementara', 'icon' => 'fa-circle-pause', 'theme' => 'orange'],
                    ];
                    $statusModalThemes = [
                        'amber' => 'bg-amber-50 border-amber-200 hover:border-amber-300 [&_.status-icon]:bg-amber-400 [&_.status-title]:text-amber-900 [&_.status-desc]:text-amber-600 [&_.status-chevron]:text-amber-400',
                        'yellow' => 'bg-yellow-50 border-yellow-200 hover:border-yellow-300 [&_.status-icon]:bg-yellow-400 [&_.status-title]:text-yellow-900 [&_.status-desc]:text-yellow-600 [&_.status-chevron]:text-yellow-400',
                        'teal' => 'bg-teal-50 border-teal-200 hover:border-teal-300 [&_.status-icon]:bg-teal-400 [&_.status-title]:text-teal-900 [&_.status-desc]:text-teal-600 [&_.status-chevron]:text-teal-400',
                        'indigo' => 'bg-indigo-50 border-indigo-200 hover:border-indigo-300 [&_.status-icon]:bg-indigo-400 [&_.status-title]:text-indigo-900 [&_.status-desc]:text-indigo-600 [&_.status-chevron]:text-indigo-400',
                        'orange' => 'bg-orange-50 border-orange-200 hover:border-orange-300 [&_.status-icon]:bg-orange-500 [&_.status-title]:text-orange-900 [&_.status-desc]:text-orange-600 [&_.status-chevron]:text-orange-400',
                    ];
                @endphp

                <div id="status-modal-options" class="space-y-2.5 max-h-[60vh] overflow-y-auto">
                    @foreach ($replyStatusOptions as $status)
                        @php $opt = $statusModalOptions[$status] ?? ['label' => ucwords(str_replace('_', ' ', $status)), 'desc' => '', 'icon' => 'fa-circle', 'theme' => 'amber']; @endphp
                        <button type="button" data-status="{{ $status }}" data-label="{{ $opt['label'] }}"
                                class="status-modal-option w-full flex items-center gap-3 rounded-2xl border px-3.5 py-3 text-left transition-colors {{ $statusModalThemes[$opt['theme']] }}">
                            <span class="status-icon shrink-0 w-10 h-10 rounded-xl text-white flex items-center justify-center">
                                <i class="fas {{ $opt['icon'] }}"></i>
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="status-title block text-sm font-bold">{{ $opt['label'] }}</span>
                                <span class="status-desc block text-xs">{{ $opt['desc'] }}</span>
                            </span>
                            <i class="status-chevron fas fa-chevron-right text-sm"></i>
                        </button>
                    @endforeach
                </div>

                <button type="button" id="status-modal-skip" class="w-full text-center text-sm text-gray-400 hover:text-gray-600 mt-4 py-1">Cancel</button>
            </div>
        </div>

        <div id="confirm-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm p-5">
                <div class="flex items-start justify-between mb-1">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Konfirmasi Kirim</h3>
                        <p class="text-sm text-gray-400">Periksa kembali sebelum dikirim</p>
                    </div>
                    <button type="button" id="confirm-modal-close" class="shrink-0 w-9 h-9 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <hr class="border-gray-100 my-3">

                <div class="space-y-3 max-h-[55vh] overflow-y-auto text-sm">
                    <div>
                        <div class="text-xs font-medium text-gray-400 mb-1">Isi Pesan</div>
                        <div id="confirm-message" class="bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 text-gray-800 whitespace-pre-wrap break-words"></div>
                    </div>

                    <div id="confirm-to-wrap">
                        <div class="text-xs font-medium text-gray-400 mb-1">Kepada (To)</div>
                        <div id="confirm-to" class="flex flex-wrap gap-1"></div>
                    </div>

                    <div id="confirm-cc-wrap">
                        <div class="text-xs font-medium text-gray-400 mb-1">Tembusan (CC)</div>
                        <div id="confirm-cc" class="flex flex-wrap gap-1"></div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-400 mb-1">Status Tiket</div>
                        <div id="confirm-status" class="text-gray-800 font-medium"></div>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="button" id="confirm-modal-back" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-medium">Kembali</button>
                    <button type="button" id="confirm-modal-send" class="flex-1 px-4 py-2.5 rounded-xl primary-gradient text-white text-sm font-semibold">Kirim Sekarang</button>
                </div>
            </div>
        </div>
    </form>

    <div id="note-edit-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm p-5">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Edit Catatan Internal</h3>
                    <p class="text-sm text-gray-400">Ubah isi catatan lalu simpan</p>
                </div>
                <button type="button" id="note-edit-modal-close" class="shrink-0 w-9 h-9 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <hr class="border-gray-100 my-3">

            <textarea id="note-edit-textarea" rows="4"
                      class="primary-focus bg-gray-50 text-sm w-full rounded-xl border border-gray-200 px-3 py-2"></textarea>

            <div class="flex gap-2 mt-4">
                <button type="button" id="note-edit-modal-cancel" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-medium">Batal</button>
                <button type="button" id="note-edit-modal-save" class="flex-1 px-4 py-2.5 rounded-xl primary-gradient text-white text-sm font-semibold">Simpan</button>
            </div>
        </div>
    </div>

    <div id="note-delete-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm p-5">
            <div class="flex items-start gap-3 mb-1">
                <span class="shrink-0 w-11 h-11 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-lg">
                    <i class="fas fa-trash"></i>
                </span>
                <div class="pt-0.5">
                    <h3 class="text-lg font-bold text-gray-900">Hapus Catatan?</h3>
                    <p class="text-sm text-gray-400">Catatan internal ini akan ditandai terhapus dan tidak bisa dikembalikan.</p>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="button" id="note-delete-modal-cancel" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-medium">Batal</button>
                <button type="button" id="note-delete-modal-confirm" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Hapus</button>
            </div>
        </div>
    </div>

    <div id="app-toast" class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-[60] max-w-sm w-[calc(100%-2rem)] px-4 py-3 rounded-xl shadow-lg text-sm text-white"></div>

    <div id="image-lightbox" class="hidden fixed inset-0 z-50 bg-black/80 items-center justify-center p-4" onclick="closeImageLightbox()">
        <img id="image-lightbox-img" src="" alt="" class="max-w-full max-h-full rounded-lg" onclick="event.stopPropagation()">
        <a id="image-lightbox-download" href="" download
           class="absolute top-4 right-16 w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center text-xl"
           onclick="event.stopPropagation()">
            <i class="fas fa-download"></i>
        </a>
        <button type="button" onclick="closeImageLightbox()"
                class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center text-xl">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        function openTicketDetail() {
            var modal = document.getElementById('ticket-detail-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeTicketDetail() {
            var modal = document.getElementById('ticket-detail-modal');
            if (modal) modal.classList.add('hidden');
        }

        (function () {
            var titleEl = document.getElementById('page-title');
            if (titleEl) {
                titleEl.classList.add('cursor-pointer', 'lg:cursor-default');
                titleEl.addEventListener('click', function () {
                    if (window.innerWidth < 1024) {
                        openTicketDetail();
                    }
                });
            }
        })();

        (function () {
            function scrollToLatest() {
                var highlighted = document.querySelector('#messages [data-highlighted="true"]');

                if (highlighted) {
                    highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    highlighted.classList.add('highlight-flash');
                    setTimeout(function () {
                        highlighted.classList.remove('highlight-flash');
                    }, 2000);
                    return;
                }

                var lastMessage = document.querySelector('#messages .message:last-child');
                if (lastMessage) {
                    lastMessage.scrollIntoView({ behavior: 'auto', block: 'end' });
                }
            }

            scrollToLatest();
            window.addEventListener('load', scrollToLatest);
        })();

        function openImageLightbox(src, filename) {
            var lightbox = document.getElementById('image-lightbox');
            var img = document.getElementById('image-lightbox-img');
            var download = document.getElementById('image-lightbox-download');
            if (!lightbox || !img) return;
            img.src = src;
            if (download) {
                download.href = src;
                download.setAttribute('download', filename || '');
            }
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }

        function closeImageLightbox() {
            var lightbox = document.getElementById('image-lightbox');
            if (!lightbox) return;
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.getElementById('image-lightbox-img').src = '';
        }

        (function () {
            document.getElementById('messages').addEventListener('click', function (event) {
                var target = event.target.closest('.lightbox-image');
                if (target) {
                    openImageLightbox(target.getAttribute('data-lightbox-src'), target.getAttribute('data-lightbox-filename'));
                }
            });
        })();

        (function () {
            var textarea = document.getElementById('chat-textarea');
            var form = document.getElementById('chat-form');

            if (!textarea || !form) return;

            textarea.addEventListener('input', function () {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 104) + 'px';
            });

            textarea.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    if (textarea.value.trim() !== '') {
                        form.requestSubmit();
                    }
                }
            });
        })();

        (function () {
            var form = document.getElementById('chat-form');
            var textarea = document.getElementById('chat-textarea');
            var messageTypeInput = document.getElementById('message_type');
            var statusModal = document.getElementById('status-modal');
            var statusCancelBtn = document.getElementById('status-modal-cancel');
            var statusSkipBtn = document.getElementById('status-modal-skip');
            var statusInput = document.getElementById('status-modal-select');
            var statusOptions = document.querySelectorAll('.status-modal-option');

            var confirmModal = document.getElementById('confirm-modal');
            var confirmCloseBtn = document.getElementById('confirm-modal-close');
            var confirmBackBtn = document.getElementById('confirm-modal-back');
            var confirmSendBtn = document.getElementById('confirm-modal-send');
            var confirmMessage = document.getElementById('confirm-message');
            var confirmToWrap = document.getElementById('confirm-to-wrap');
            var confirmTo = document.getElementById('confirm-to');
            var confirmCcWrap = document.getElementById('confirm-cc-wrap');
            var confirmCc = document.getElementById('confirm-cc');
            var confirmStatus = document.getElementById('confirm-status');

            if (!form || !statusModal || !statusCancelBtn || !statusSkipBtn || !statusInput) return;
            if (!confirmModal || !confirmCloseBtn || !confirmBackBtn || !confirmSendBtn) return;

            var confirmed = false;
            var pendingStatus = '';
            var pendingStatusLabel = '';

            function openStatusModal() {
                statusModal.classList.remove('hidden');
                statusModal.classList.add('flex');
            }

            function closeStatusModal() {
                statusModal.classList.add('hidden');
                statusModal.classList.remove('flex');
            }

            function closeConfirmModal() {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
            }

            function renderEmailChips(target, container) {
                target.innerHTML = '';
                var inputs = container ? container.querySelectorAll('input[type="hidden"]') : [];
                if (!inputs.length) {
                    var empty = document.createElement('span');
                    empty.className = 'text-xs text-gray-400 italic';
                    empty.textContent = 'Tidak ada';
                    target.appendChild(empty);
                    return;
                }
                Array.prototype.forEach.call(inputs, function (input) {
                    var chip = document.createElement('span');
                    chip.className = 'bg-gray-100 text-gray-700 text-xs rounded-full px-2 py-0.5';
                    chip.textContent = input.value;
                    target.appendChild(chip);
                });
            }

            function openConfirmModal(status, statusLabel) {
                pendingStatus = status;
                pendingStatusLabel = statusLabel;

                var isNote = messageTypeInput.value === 'internal_note';

                confirmMessage.textContent = textarea.value.trim();

                confirmToWrap.classList.toggle('hidden', isNote);
                confirmCcWrap.classList.toggle('hidden', isNote);
                if (!isNote) {
                    renderEmailChips(confirmTo, document.getElementById('to-emails-fields'));
                    renderEmailChips(confirmCc, document.getElementById('cc-emails-fields'));
                }

                confirmStatus.textContent = statusLabel || 'Tidak diubah';

                closeStatusModal();
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
            }

            function doSend() {
                statusInput.value = pendingStatus;
                confirmed = true;
                closeConfirmModal();
                form.requestSubmit();
            }

            statusOptions.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openConfirmModal(btn.dataset.status, btn.dataset.label);
                });
            });

            form.addEventListener('submit', function (event) {
                if (confirmed) return;
                if (messageTypeInput.value === 'internal_note') {
                    statusInput.value = '';
                    return;
                }
                event.preventDefault();
                statusInput.value = '';
                openStatusModal();
            });

            statusCancelBtn.addEventListener('click', function () {
                openConfirmModal('', '');
            });

            statusSkipBtn.addEventListener('click', function () {
                openConfirmModal('', '');
            });

            statusModal.addEventListener('click', function (event) {
                if (event.target === statusModal) openConfirmModal('', '');
            });

            confirmBackBtn.addEventListener('click', function () {
                closeConfirmModal();
                openStatusModal();
            });

            confirmCloseBtn.addEventListener('click', closeConfirmModal);

            confirmModal.addEventListener('click', function (event) {
                if (event.target === confirmModal) closeConfirmModal();
            });

            confirmSendBtn.addEventListener('click', doSend);
        })();

        (function () {
            var panel = document.getElementById('composer-options');
            var toggleBtn = document.getElementById('options-toggle');

            if (!panel || !toggleBtn) return;

            toggleBtn.addEventListener('click', function () {
                panel.classList.toggle('hidden');
            });

            document.addEventListener('click', function (event) {
                if (panel.classList.contains('hidden')) return;
                if (panel.contains(event.target) || toggleBtn.contains(event.target)) return;
                panel.classList.add('hidden');
            });

            window.toggleComposerOptionsForNote = function (isNote) {
                document.querySelectorAll('.reply-only-option').forEach(function (el) {
                    el.classList.toggle('hidden', isNote);
                });
            };
        })();

        (function () {
            var emailChipEditor = function (config) {
                var list = config.initial.slice();
                var touched = false;
                var chipsEl = document.getElementById(config.chipsId);
                var fieldsEl = document.getElementById(config.fieldsId);
                var touchedEl = document.getElementById(config.touchedId);
                var input = document.getElementById(config.inputId);
                var addBtn = document.getElementById(config.addBtnId);

                function render() {
                    chipsEl.innerHTML = '';
                    fieldsEl.innerHTML = '';

                    if (list.length === 0) {
                        var empty = document.createElement('span');
                        empty.className = 'text-[11px] text-gray-400 italic';
                        empty.textContent = 'Belum ada penerima';
                        chipsEl.appendChild(empty);
                    }

                    list.forEach(function (email, index) {
                        var chip = document.createElement('span');
                        chip.className = 'inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs rounded-full pl-2.5 pr-1 py-1';

                        var label = document.createElement('span');
                        label.textContent = email;
                        chip.appendChild(label);

                        var removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'w-4 h-4 rounded-full flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50';
                        removeBtn.innerHTML = '<i class="fas fa-times text-[9px]"></i>';
                        removeBtn.addEventListener('click', function () {
                            list.splice(index, 1);
                            touched = true;
                            render();
                        });
                        chip.appendChild(removeBtn);

                        chipsEl.appendChild(chip);

                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = config.fieldName + '[]';
                        hidden.value = email;
                        fieldsEl.appendChild(hidden);
                    });

                    touchedEl.value = touched ? '1' : '0';
                }

                function addEmail() {
                    var value = (input.value || '').trim();
                    if (value === '') return;

                    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(value)) {
                        input.classList.add('border-red-400');
                        return;
                    }
                    input.classList.remove('border-red-400');

                    if (list.some(function (e) { return e.toLowerCase() === value.toLowerCase(); })) {
                        input.value = '';
                        return;
                    }

                    list.push(value);
                    touched = true;
                    input.value = '';
                    render();
                }

                addBtn.addEventListener('click', addEmail);
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        addEmail();
                    }
                });

                render();
            };

            emailChipEditor({
                initial: @json($ticketToEmails),
                chipsId: 'to-chips',
                fieldsId: 'to-emails-fields',
                touchedId: 'to_emails_touched',
                inputId: 'to-email-input',
                addBtnId: 'to-email-add',
                fieldName: 'to_emails',
            });

            emailChipEditor({
                initial: @json($ticketCcEmails),
                chipsId: 'cc-chips',
                fieldsId: 'cc-emails-fields',
                touchedId: 'cc_emails_touched',
                inputId: 'cc-email-input',
                addBtnId: 'cc-email-add',
                fieldName: 'cc_emails',
            });
        })();

        (function () {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            var messagesEl = document.getElementById('messages');
            var ticketId = messagesEl ? messagesEl.getAttribute('data-ticket-id') : '';
            var editModal = document.getElementById('note-edit-modal');
            var editTextarea = document.getElementById('note-edit-textarea');
            var editCloseBtn = document.getElementById('note-edit-modal-close');
            var editCancelBtn = document.getElementById('note-edit-modal-cancel');
            var editSaveBtn = document.getElementById('note-edit-modal-save');
            var toastEl = document.getElementById('app-toast');
            var activeMessageEl = null;

            var deleteModal = document.getElementById('note-delete-modal');
            var deleteCancelBtn = document.getElementById('note-delete-modal-cancel');
            var deleteConfirmBtn = document.getElementById('note-delete-modal-confirm');
            var pendingDeleteMessageEl = null;

            if (!messagesEl || !editModal || !editTextarea || !editSaveBtn) return;

            function showToast(message, isError) {
                if (!toastEl) return;
                toastEl.textContent = message;
                toastEl.classList.remove('hidden', 'bg-gray-900', 'bg-red-600');
                toastEl.classList.add(isError ? 'bg-red-600' : 'bg-gray-900');
                clearTimeout(toastEl._timer);
                toastEl._timer = setTimeout(function () {
                    toastEl.classList.add('hidden');
                }, 3500);
            }

            function openEditModal(messageEl) {
                activeMessageEl = messageEl;
                editTextarea.value = messageEl.getAttribute('data-raw-message') || '';
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
                editTextarea.focus();
            }

            function closeEditModal() {
                editModal.classList.add('hidden');
                editModal.classList.remove('flex');
                activeMessageEl = null;
            }

            function requestJson(url, options) {
                return fetch(url, options).then(function (response) {
                    return response.json().then(function (body) {
                        return { ok: response.ok, body: body || {} };
                    });
                });
            }

            if (editCloseBtn) editCloseBtn.addEventListener('click', closeEditModal);
            if (editCancelBtn) editCancelBtn.addEventListener('click', closeEditModal);
            editModal.addEventListener('click', function (event) {
                if (event.target === editModal) closeEditModal();
            });

            var editSaveBtnHtml = editSaveBtn.innerHTML;

            function setButtonLoading(btn, originalHtml, loading) {
                if (!btn) return;
                btn.disabled = loading;
                btn.classList.toggle('opacity-70', loading);
                btn.classList.toggle('cursor-not-allowed', loading);
                btn.innerHTML = loading ? '<span class="spinner"></span>' : originalHtml;

                if (window.AppLoading) {
                    if (loading) window.AppLoading.show(); else window.AppLoading.hide();
                }
            }

            editSaveBtn.addEventListener('click', function () {
                if (!activeMessageEl) return;

                var messageId = activeMessageEl.getAttribute('data-message-id');
                var newMessage = editTextarea.value.trim();
                if (newMessage === '') return;

                setButtonLoading(editSaveBtn, editSaveBtnHtml, true);

                requestJson('/tickets/' + ticketId + '/messages/' + messageId + '/internal-note', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ message: newMessage }),
                }).then(function (result) {
                    if (!result.ok || !result.body.success) {
                        showToast(result.body.message || 'Gagal mengubah catatan.', true);
                        return;
                    }

                    var data = result.body.data || {};
                    var updatedBody = data.message_body || newMessage;
                    var bodyEl = activeMessageEl.querySelector('.note-body');
                    if (bodyEl) bodyEl.textContent = updatedBody;
                    activeMessageEl.setAttribute('data-raw-message', updatedBody);

                    if (data.edited_at) {
                        var label = activeMessageEl.querySelector('.note-edited-label');
                        if (!label && bodyEl && bodyEl.parentNode) {
                            label = document.createElement('div');
                            label.className = 'note-edited-label text-[10px] italic text-amber-600';
                            bodyEl.parentNode.insertBefore(label, bodyEl.nextSibling);
                        }
                        if (label) label.textContent = '(diedit)';
                    }

                    showToast('Catatan berhasil diubah.', false);
                    closeEditModal();
                }).catch(function () {
                    showToast('Gagal menghubungi server.', true);
                }).finally(function () {
                    setButtonLoading(editSaveBtn, editSaveBtnHtml, false);
                });
            });

            function deleteNote(messageEl) {
                var messageId = messageEl.getAttribute('data-message-id');

                return requestJson('/tickets/' + ticketId + '/messages/' + messageId + '/internal-note', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                }).then(function (result) {
                    if (!result.ok || !result.body.success) {
                        showToast(result.body.message || 'Gagal menghapus catatan.', true);
                        return;
                    }

                    var bodyEl = messageEl.querySelector('.note-body');
                    if (bodyEl) {
                        bodyEl.textContent = 'Catatan ini telah dihapus.';
                        bodyEl.classList.add('italic', 'text-gray-400');
                    }

                    var label = messageEl.querySelector('.note-edited-label');
                    if (label) label.remove();

                    var editBtn = messageEl.querySelector('.note-edit-btn');
                    if (editBtn) editBtn.remove();

                    var deleteBtn = messageEl.querySelector('.note-delete-btn');
                    if (deleteBtn) deleteBtn.remove();

                    showToast('Catatan berhasil dihapus.', false);
                }).catch(function () {
                    showToast('Gagal menghubungi server.', true);
                });
            }

            function openDeleteModal(messageEl) {
                pendingDeleteMessageEl = messageEl;
                if (!deleteModal) return;
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
            }

            function closeDeleteModal() {
                if (!deleteModal) return;
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                pendingDeleteMessageEl = null;
            }

            if (deleteCancelBtn) deleteCancelBtn.addEventListener('click', closeDeleteModal);
            if (deleteModal) {
                deleteModal.addEventListener('click', function (event) {
                    if (event.target === deleteModal) closeDeleteModal();
                });
            }
            if (deleteConfirmBtn) {
                var deleteConfirmBtnHtml = deleteConfirmBtn.innerHTML;

                deleteConfirmBtn.addEventListener('click', function () {
                    var messageEl = pendingDeleteMessageEl;
                    if (!messageEl) {
                        closeDeleteModal();
                        return;
                    }

                    setButtonLoading(deleteConfirmBtn, deleteConfirmBtnHtml, true);
                    if (deleteCancelBtn) deleteCancelBtn.disabled = true;

                    deleteNote(messageEl).finally(function () {
                        setButtonLoading(deleteConfirmBtn, deleteConfirmBtnHtml, false);
                        if (deleteCancelBtn) deleteCancelBtn.disabled = false;
                        closeDeleteModal();
                    });
                });
            }

            messagesEl.addEventListener('click', function (event) {
                var editBtn = event.target.closest('.note-edit-btn');
                if (editBtn) {
                    var messageElForEdit = editBtn.closest('[data-note-message]');
                    if (messageElForEdit) openEditModal(messageElForEdit);
                    return;
                }

                var deleteBtn = event.target.closest('.note-delete-btn');
                if (deleteBtn) {
                    var messageElForDelete = deleteBtn.closest('[data-note-message]');
                    if (messageElForDelete) openDeleteModal(messageElForDelete);
                    return;
                }

                var replyBtn = event.target.closest('.note-reply-btn');
                if (replyBtn) {
                    var messageElForReply = replyBtn.closest('[data-note-message]');
                    if (messageElForReply && window.startNoteReply) window.startNoteReply(messageElForReply);
                    return;
                }

                var quoteRef = event.target.closest('.reply-quote-ref');
                if (quoteRef) {
                    var targetId = quoteRef.getAttribute('data-scroll-to-message');
                    var targetEl = targetId ? document.getElementById('msg-' + targetId) : null;
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetEl.classList.add('highlight-flash');
                        setTimeout(function () { targetEl.classList.remove('highlight-flash'); }, 2000);
                    }
                }
            });
        })();

        (function () {
            var replyToInput = document.getElementById('reply_to_id');
            var messageTypeInput = document.getElementById('message_type');
            var noteToggle = document.getElementById('note_toggle');
            var banner = document.getElementById('reply-quote-banner');
            var bannerSender = document.getElementById('reply-quote-sender');
            var bannerText = document.getElementById('reply-quote-text');
            var cancelBtn = document.getElementById('reply-quote-cancel');
            var textarea = document.getElementById('chat-textarea');

            if (!replyToInput || !banner || !cancelBtn) return;

            function clearReply() {
                replyToInput.value = '';
                banner.classList.add('hidden');
                if (noteToggle) noteToggle.disabled = false;
            }

            window.startNoteReply = function (messageEl) {
                var messageId = messageEl.getAttribute('data-message-id');
                var senderName = messageEl.getAttribute('data-sender-name') || 'Pesan';
                var rawMessage = messageEl.getAttribute('data-raw-message') || '';

                replyToInput.value = messageId || '';
                bannerSender.textContent = senderName;
                bannerText.textContent = rawMessage.length > 100 ? rawMessage.slice(0, 100) + '…' : rawMessage;
                banner.classList.remove('hidden');

                if (noteToggle) {
                    noteToggle.checked = true;
                    noteToggle.disabled = true;
                }
                if (messageTypeInput) messageTypeInput.value = 'internal_note';
                window.toggleComposerOptionsForNote && window.toggleComposerOptionsForNote(true);

                if (textarea) textarea.focus();
            };

            cancelBtn.addEventListener('click', clearReply);
        })();

        (function () {
            var messagesEl = document.getElementById('messages');
            if (!messagesEl) return;

            var ticketId = messagesEl.getAttribute('data-ticket-id');
            var pollUrl = '{{ route('tickets.messages.poll', ['id' => $ticket['ticket_id'] ?? 0]) }}';

            function isNearBottom() {
                return window.innerHeight + window.scrollY >= document.body.scrollHeight - 200;
            }

            function pollMessages() {
                var lastId = messagesEl.getAttribute('data-last-message-id') || '0';

                fetch(pollUrl + '?after_id=' + encodeURIComponent(lastId), { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) {
                        if (!data || !data.success || !data.html) return;

                        var placeholder = messagesEl.querySelector('p');
                        if (placeholder) placeholder.remove();

                        var wasNearBottom = isNearBottom();
                        messagesEl.insertAdjacentHTML('beforeend', data.html);
                        messagesEl.setAttribute('data-last-message-id', data.last_id);

                        if (wasNearBottom) {
                            var lastMessage = messagesEl.querySelector('.message:last-child');
                            if (lastMessage) lastMessage.scrollIntoView({ behavior: 'smooth', block: 'end' });
                        }
                    })
                    .catch(function () {});
            }

            setInterval(pollMessages, 8000);
        })();

        (function () {
            var form = document.getElementById('chat-form');
            var sendBtn = form ? form.querySelector('button[type="submit"]') : null;
            if (!form || !sendBtn) return;

            var originalHtml = sendBtn.innerHTML;

            form.addEventListener('submit', function (event) {
                if (event.defaultPrevented) return;

                sendBtn.disabled = true;
                sendBtn.classList.add('opacity-70', 'cursor-not-allowed');
                sendBtn.innerHTML = '<span class="spinner"></span>';
            });

            window.addEventListener('pageshow', function () {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                sendBtn.innerHTML = originalHtml;
            });
        })();
    </script>
@endpush
