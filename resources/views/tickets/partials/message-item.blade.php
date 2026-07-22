@php
    $isNote = ($message['message_type'] ?? 'reply') === 'internal_note';
    $channel = $message['channel'] ?? null;
    $emailStatus = $message['email_status'] ?? null;
    $emailError = $message['email_error'] ?? null;
    $emailFailed = !$isNote && in_array($emailStatus, ['failed', 'partial'], true);
    $emailSent = !$isNote && !$emailFailed && $channel === 'email';
    $senderId = $message['sender_id'] ?? null;
    $isEmployeeSender = ($message['sender_type'] ?? null) === 'employee';
    $senderName = $message['sender_name'] ?? ($isEmployeeSender ? 'Employee' : 'Customer');
    $isOwn = $currentUserId !== null && $senderId !== null && (string) $senderId === (string) $currentUserId;
    $showSenderName = !$isOwn && $senderName !== $lastSenderState->value;
    $lastSenderState->value = $senderName;

    $messageBody = trim((string) ($message['message_body'] ?? ''));
    $inlineSignatureName = null;
    if ($isOwn && preg_match('/-\s*([A-Z][\p{L}.\']*(?:\s+[A-Z][\p{L}.\']*){0,2})$/u', $messageBody, $signatureMatch)) {
        $inlineSignatureName = trim($signatureMatch[1]);
        $messageBody = rtrim(substr($messageBody, 0, -strlen($signatureMatch[0])));
    }
    $hasInlineSignature = $inlineSignatureName !== null;
    $displaySenderName = $hasInlineSignature ? $inlineSignatureName : $senderName;

    $isDeletedNote = $isNote && ($message['is_deleted'] ?? false);
    $isOwnNote = $isNote && $isOwn;
    $withinEditWindow = false;
    if ($isNote && !empty($message['created_at'])) {
        try {
            $withinEditWindow = \Carbon\Carbon::parse($message['created_at'])->diffInMinutes(now()) < 10;
        } catch (\Exception $e) {
            $withinEditWindow = false;
        }
    }
    $canEditNote = $isOwnNote && !$isDeletedNote && $withinEditWindow;
    $canDeleteNote = !$isDeletedNote && (($isOwnNote && $withinEditWindow) || ($isNote && $isAdmin));
    $editedAt = $message['edited_at'] ?? null;

    $replyToPreview = $message['reply_to_preview'] ?? null;
    $replyToId = $message['reply_to_id'] ?? null;
    $replyToSenderName = is_array($replyToPreview) ? ($replyToPreview['sender_name'] ?? 'Message') : null;
    $replyToBody = is_array($replyToPreview) ? trim((string) ($replyToPreview['message_body'] ?? '')) : '';

    $mentions = $message['mentions'] ?? [];
    $renderMessageBody = function ($text, $mentions, $currentUserId) {
        $escaped = e($text);
        if (empty($mentions)) {
            return $escaped;
        }

        usort($mentions, fn ($a, $b) => mb_strlen($b['name'] ?? '') <=> mb_strlen($a['name'] ?? ''));

        foreach ($mentions as $mention) {
            $name = trim((string) ($mention['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $needle = e('@' . $name);
            $isSelf = $currentUserId !== null
                && isset($mention['employee_id'])
                && (string) $mention['employee_id'] === (string) $currentUserId;
            $class = $isSelf ? 'mention-highlight mention-highlight-self' : 'mention-highlight';

            $escaped = str_replace($needle, '<span class="' . $class . '">' . $needle . '</span>', $escaped);
        }

        return $escaped;
    };
@endphp
<div id="msg-{{ $message['id'] ?? '' }}"
     class="message flex items-end gap-2 py-1 min-w-0 max-w-full {{ $isOwn ? 'justify-end' : 'justify-start' }}"
     @if ($isNote)
         data-note-message="true"
         data-message-id="{{ $message['id'] ?? '' }}"
         data-raw-message="{{ $message['message_body'] ?? '' }}"
         data-sender-name="{{ $senderName }}"
     @endif
     @if($message['is_highlighted'] ?? false) data-highlighted="true" @endif>
    @unless ($isOwn)
        <span class="chat-avatar shrink-0 rounded-full primary-gradient text-white flex items-center justify-center font-semibold {{ $showSenderName ? '' : 'invisible' }}">
            {{ $initialsOf($senderName) }}
        </span>
    @endunless

    <div class="bubble {{ $isOwn ? 'bubble-own' : 'bubble-other' }} {{ $isNote ? 'bubble-note' : '' }} min-w-0 px-3.5 py-2 shadow-sm">
        @if ($isNote)
            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700">
                    <i class="fas fa-lock text-[10px]"></i> Internal Note
                </div>
                @if (!$isDeletedNote)
                    <div class="flex items-center gap-2">
                        <button type="button" class="note-reply-btn text-[11px] text-amber-700 hover:underline" title="Reply to note">
                            <i class="fas fa-reply text-[10px]"></i>
                        </button>
                        @if ($canEditNote)
                            <button type="button" class="note-edit-btn text-[11px] text-amber-700 hover:underline" title="Edit note">
                                <i class="fas fa-pen text-[10px]"></i>
                            </button>
                        @endif
                        @if ($canDeleteNote)
                            <button type="button" class="note-delete-btn text-[11px] text-amber-700 hover:underline" title="Delete note">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if ($replyToId && $replyToPreview)
            <div class="reply-quote-ref cursor-pointer mb-1.5 pl-2 border-l-2 {{ $isNote ? 'border-amber-400/70' : 'border-gray-300' }} opacity-80 hover:opacity-100"
                 data-scroll-to-message="{{ $replyToId }}">
                <div class="text-[10px] font-semibold truncate">{{ $replyToSenderName }}</div>
                <div class="text-[11px] truncate">{{ \Illuminate\Support\Str::limit($replyToBody, 80) ?: '(message)' }}</div>
            </div>
        @endif

        @if ($isDeletedNote)
            <div class="note-body text-sm italic text-gray-400">This note has been deleted.</div>
        @else
            <div class="note-body text-sm whitespace-pre-wrap break-words">{!! $renderMessageBody($messageBody, $mentions, $currentUserId) !!}</div>
        @endif

        @if ($isNote && $editedAt && !$isDeletedNote)
            <div class="note-edited-label text-[10px] italic text-amber-600">(edited)</div>
        @endif

        @if (!empty($message['attachments']))
            <div class="mt-2 space-y-2">
                @foreach ($message['attachments'] as $attachment)
                    @if ($attachment['is_image'] ?? false)
                        <img src="{{ $attachment['url'] }}" alt="{{ $attachment['file_name'] ?? 'Attachment' }}"
                             class="max-w-full max-h-64 rounded-lg border border-black/10 cursor-zoom-in lightbox-image"
                             data-lightbox-src="{{ $attachment['url'] }}"
                             data-lightbox-filename="{{ $attachment['file_name'] ?? 'attachment' }}" loading="lazy">
                    @else
                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 text-xs underline break-all text-blue-600">
                            <i class="fas fa-paperclip shrink-0"></i>
                            <span class="break-all">{{ $attachment['file_name'] ?? 'Attachment' }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($showSenderName || $hasInlineSignature)
            <div class="text-[9px] italic font-semibold mt-1 text-right primary-text">{{ $displaySenderName }}</div>
        @endif

        @if ($emailFailed)
            <div class="inline-flex items-center gap-1 text-[10px] font-semibold mt-1 px-1.5 py-0.5 rounded bg-red-600 text-white cursor-help"
                 title="{{ $emailError ?: 'Message failed to send to the customer\'s email.' }}">
                <i class="fas fa-triangle-exclamation text-[9px]"></i> Not delivered
            </div>
        @endif

        <div class="text-[10px] mt-0.5 text-right text-gray-400">
            {{ $formatMessageTime($message['created_at'] ?? null) }}
        </div>
    </div>
</div>
