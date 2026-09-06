@php

    $adminMode =
        $adminMode
        ??
        false;


    $roomRole =
        $roomRole
        ??
        (
            auth()->user()?->canAccessAdminPanel()
                ? 'admin'
                : (
                    (int) auth()->id() === (int) $dispute->seller_id
                        ? 'seller'
                        : 'buyer'
                )
        );


    $lastMessageId =
        $messages->max('id')
        ??
        0;

@endphp


<div
    class="dr-room"
    id="disputeResolutionRoom"
    data-dispute-id="{{ $dispute->id }}"
    data-role="{{ $roomRole }}"
    data-last-message-id="{{ $lastMessageId }}"
    data-messages-url="{{ route('dispute-room.messages.index', $dispute) }}"
    data-send-url="{{ route('dispute-room.messages.send', $dispute) }}"
>

    <div class="dr-room-header">

        <div>

            <div class="dr-room-kicker">

                <span></span>

                Dispute resolution room

            </div>


            <h2>

                Buyer · Seller · Midpoint Support

            </h2>


            <p>

                This room is the official Midpoint communication record for
                dispute #{{ $dispute->id }}.

            </p>

        </div>


        <div
            class="
                dr-room-status
                {{
                    $dispute->isRoomActive()
                        ? 'is-active'
                        : 'is-waiting'
                }}
            "
        >

            <span></span>

            {{
                $dispute->isRoomActive()
                    ? 'Room active'
                    : 'Waiting for Support'
            }}

        </div>

    </div>


    @if (!$dispute->isRoomActive())

        <div class="dr-room-waiting">

            <i class="fa-solid fa-lock"></i>

            <div>

                <strong>
                    Midpoint Support has not activated this room yet.
                </strong>

                <p>
                    Seller payout remains locked while the dispute is open.
                    Once Support activates the room, buyer and seller will
                    receive an in-app notification and email.
                </p>

            </div>

        </div>

    @endif


    <div
        class="dr-message-list"
        id="drMessageList"
    >

        @forelse ($messages as $message)

            @php

                $isOwn =
                    !$message->is_system
                    &&
                    (int) $message->sender_id
                    ===
                    (int) auth()->id();


                $messageClass =
                    $message->is_system

                        ? 'system'

                        : (
                            $message->sender_role
                            ===
                            'admin'

                                ? 'admin'

                                : (
                                    $isOwn
                                        ? 'own'
                                        : 'other'
                                )
                        );


                $senderName =
                    $message->is_system

                        ? 'Midpoint'

                        : (
                            $message->sender?->name
                            ?:
                            match ($message->sender_role) {
                                'admin' =>
                                    'Midpoint Support',

                                'buyer' =>
                                    'Buyer',

                                'seller' =>
                                    'Seller',

                                default =>
                                    'Midpoint',
                            }
                        );

            @endphp


            <article
                class="dr-message {{ $messageClass }}"
                data-message-id="{{ $message->id }}"
            >

                <div class="dr-message-meta">

                    <strong>

                        {{ $senderName }}

                    </strong>


                    @if (
                        $adminMode
                        &&
                        in_array(
                            $message->visibility,
                            [
                                'buyer',
                                'seller',
                                'internal',
                            ],
                            true
                        )
                    )

                        <span class="dr-private-badge">

                            {{
                                match ($message->visibility) {

                                    'buyer' =>
                                        'Buyer only',

                                    'seller' =>
                                        'Seller only',

                                    'internal' =>
                                        'Internal note',

                                    default =>
                                        '',
                                }
                            }}

                        </span>

                    @endif


                    <time>

                        {{
                            optional(
                                $message->created_at
                            )->format(
                                'd M Y, h:i A'
                            )
                        }}

                    </time>

                </div>


                @if ($message->message)

                    <div class="dr-message-text">

                        {!!
                            nl2br(
                                e(
                                    $message->message
                                )
                            )
                        !!}

                    </div>

                @endif


                @if (
                    is_array(
                        $message->attachments
                    )
                    &&
                    count(
                        $message->attachments
                    )
                )

                    <div class="dr-attachments">

                        @foreach ($message->attachments as $attachmentIndex => $attachment)

                            <a
                                href="{{
                                    route(
                                        'dispute-room.attachments.download',
                                        [
                                            'dispute' =>
                                                $dispute->id,

                                            'message' =>
                                                $message->id,

                                            'index' =>
                                                $attachmentIndex,
                                        ]
                                    )
                                }}"
                                class="dr-attachment"
                            >

                                <i class="fa-solid fa-paperclip"></i>


                                <span>

                                    {{
                                        $attachment[
                                            'original_name'
                                        ]
                                        ??
                                        'Attachment'
                                    }}

                                </span>

                            </a>

                        @endforeach

                    </div>

                @endif

            </article>

        @empty

            <div
                class="dr-empty"
                id="drEmptyState"
            >

                <i class="fa-regular fa-comments"></i>

                <strong>
                    No room messages yet
                </strong>

                <span>
                    Midpoint Support can start the discussion once the room is active.
                </span>

            </div>

        @endforelse

    </div>


    @if (
        $dispute->isRoomActive()
        &&
        !$dispute->isResolved()
    )

        <form
            id="drMessageForm"
            class="dr-composer"
            enctype="multipart/form-data"
        >

            @csrf


            @if ($adminMode)

                <div class="dr-audience">

                    <label for="drVisibility">
                        Message visibility
                    </label>


                    <select
                        id="drVisibility"
                        name="visibility"
                    >

                        <option value="all">
                            Buyer + Seller
                        </option>

                        <option value="buyer">
                            Buyer only
                        </option>

                        <option value="seller">
                            Seller only
                        </option>

                        <option value="internal">
                            Internal staff note
                        </option>

                    </select>

                </div>

            @endif


            <textarea
                id="drMessageInput"
                name="message"
                rows="4"
                maxlength="10000"
                placeholder="{{
                    $adminMode
                        ? 'Write to the buyer/seller, request proof, explain next steps...'
                        : 'Reply to Midpoint Support and the other party...'
                }}"
            ></textarea>


            <div
                class="dr-selected-files"
                id="drSelectedFiles"
            ></div>


            <div class="dr-composer-bottom">

                <label
                    class="dr-attach-button"
                    for="drAttachments"
                >

                    <i class="fa-solid fa-paperclip"></i>

                    Attach proof

                </label>


                <input
                    type="file"
                    id="drAttachments"
                    name="attachments[]"
                    hidden
                    multiple
                    accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4,.mov,.webm,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip"
                >


                <span
                    class="dr-character-count"
                    id="drCharacterCount"
                >
                    0 / 10,000
                </span>


                <button
                    type="submit"
                    class="dr-send-button"
                    id="drSendButton"
                >

                    <i class="fa-solid fa-paper-plane"></i>

                    Send message

                </button>

            </div>

        </form>

    @elseif ($dispute->isResolved())

        <div class="dr-room-closed">

            <i class="fa-solid fa-circle-check"></i>

            <div>

                <strong>
                    This dispute is resolved.
                </strong>

                <p>
                    The resolution room is now read-only and remains available
                    as the official communication record.
                </p>

            </div>

        </div>

    @endif

</div>


@push('styles')

<style>

    .dr-room {

        overflow:
            hidden;

        border:
            1px solid #DCE5E0;

        border-radius:
            18px;

        background:
            #FFFFFF;

        box-shadow:
            0 16px 45px -35px rgba(11,61,46,.35);

    }


    .dr-room-header {

        display:
            flex;

        align-items:
            flex-start;

        justify-content:
            space-between;

        gap:
            18px;

        padding:
            20px;

        border-bottom:
            1px solid #E8EEEA;

        background:
            linear-gradient(145deg,#F9FCFA,#F3F8F5);

    }


    .dr-room-kicker {

        display:
            flex;

        align-items:
            center;

        gap:
            7px;

        margin-bottom:
            5px;

        color:
            #087443;

        font-size:
            10px;

        font-weight:
            800;

        letter-spacing:
            .08em;

        text-transform:
            uppercase;

    }


    .dr-room-kicker span {

        width:
            18px;

        height:
            2px;

        border-radius:
            999px;

        background:
            #12B76A;

    }


    .dr-room-header h2 {

        margin:
            0;

        color:
            #14231C;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            17px;

        font-weight:
            800;

    }


    .dr-room-header p {

        margin:
            4px 0 0;

        color:
            #718078;

        font-size:
            10px;

        line-height:
            1.55;

    }


    .dr-room-status {

        flex:
            none;

        display:
            inline-flex;

        align-items:
            center;

        gap:
            6px;

        padding:
            7px 10px;

        border-radius:
            999px;

        font-size:
            9px;

        font-weight:
            800;

    }


    .dr-room-status span {

        width:
            7px;

        height:
            7px;

        border-radius:
            50%;

    }


    .dr-room-status.is-active {

        background:
            #ECFDF3;

        color:
            #067647;

    }


    .dr-room-status.is-active span {

        background:
            #12B76A;

        box-shadow:
            0 0 0 4px rgba(18,183,106,.10);

    }


    .dr-room-status.is-waiting {

        background:
            #F4F5F4;

        color:
            #68756E;

    }


    .dr-room-status.is-waiting span {

        background:
            #98A29D;

    }


    .dr-room-waiting,
    .dr-room-closed {

        display:
            flex;

        align-items:
            flex-start;

        gap:
            10px;

        margin:
            16px 18px 0;

        padding:
            13px;

        border-radius:
            12px;

        font-size:
            10px;

    }


    .dr-room-waiting {

        border:
            1px solid #F5D7A5;

        background:
            #FFF8EB;

        color:
            #7A4B09;

    }


    .dr-room-closed {

        border:
            1px solid #BFE8D2;

        background:
            #F1FCF6;

        color:
            #08663F;

    }


    .dr-room-waiting p,
    .dr-room-closed p {

        margin:
            3px 0 0;

        line-height:
            1.55;

    }


    .dr-message-list {

        height:
            500px;

        overflow-y:
            auto;

        padding:
            20px;

        background:
            #F8FAF9;

    }


    .dr-message {

        width:
            fit-content;

        max-width:
            min(
                78%,
                650px
            );

        margin:
            0 0 13px;

        padding:
            11px 13px;

        border:
            1px solid #E1E8E4;

        border-radius:
            13px;

        background:
            #FFFFFF;

        box-shadow:
            0 7px 18px -16px rgba(11,61,46,.30);

    }


    .dr-message.own {

        margin-left:
            auto;

        border-color:
            #BFE7D2;

        background:
            #ECFDF3;

    }


    .dr-message.admin {

        border-color:
            #D9CEF9;

        background:
            #F6F3FF;

    }


    .dr-message.system {

        width:
            100%;

        max-width:
            none;

        border:
            1px dashed #C9D5CF;

        background:
            #F2F5F3;

        color:
            #56645C;

        text-align:
            center;

    }


    .dr-message-meta {

        display:
            flex;

        align-items:
            center;

        gap:
            7px;

        margin-bottom:
            5px;

        font-size:
            8px;

    }


    .dr-message-meta strong {

        color:
            #1D2B24;

        font-size:
            9px;

    }


    .dr-message-meta time {

        margin-left:
            auto;

        color:
            #8A9690;

    }


    .dr-private-badge {

        padding:
            3px 6px;

        border-radius:
            999px;

        background:
            #FFF1D6;

        color:
            #8A5709;

        font-size:
            7px;

        font-weight:
            800;

    }


    .dr-message-text {

        color:
            #3D4A43;

        font-size:
            11px;

        line-height:
            1.65;

        overflow-wrap:
            anywhere;

    }


    .dr-attachments {

        display:
            flex;

        flex-wrap:
            wrap;

        gap:
            6px;

        margin-top:
            9px;

    }


    .dr-attachment {

        display:
            inline-flex;

        align-items:
            center;

        gap:
            5px;

        max-width:
            100%;

        padding:
            6px 8px;

        border:
            1px solid #D9E2DD;

        border-radius:
            8px;

        background:
            #FFFFFF;

        color:
            #0B3D2E;

        font-size:
            8px;

        font-weight:
            700;

        text-decoration:
            none;

    }


    .dr-attachment span {

        overflow:
            hidden;

        text-overflow:
            ellipsis;

        white-space:
            nowrap;

    }


    .dr-empty {

        height:
            100%;

        display:
            flex;

        flex-direction:
            column;

        align-items:
            center;

        justify-content:
            center;

        gap:
            5px;

        color:
            #86928C;

        text-align:
            center;

    }


    .dr-empty i {

        margin-bottom:
            5px;

        color:
            #12B76A;

        font-size:
            25px;

    }


    .dr-empty strong {

        color:
            #435049;

        font-size:
            11px;

    }


    .dr-empty span {

        font-size:
            9px;

    }


    .dr-composer {

        padding:
            16px;

        border-top:
            1px solid #E4EAE6;

        background:
            #FFFFFF;

    }


    .dr-audience {

        display:
            flex;

        align-items:
            center;

        gap:
            8px;

        margin-bottom:
            9px;

    }


    .dr-audience label {

        color:
            #5F6D65;

        font-size:
            9px;

        font-weight:
            700;

    }


    .dr-audience select {

        height:
            32px;

        padding:
            0 9px;

        border:
            1px solid #DCE5E0;

        border-radius:
            8px;

        background:
            #F9FBFA;

        color:
            #304039;

        font-size:
            9px;

        outline:
            none;

    }


    .dr-composer textarea {

        width:
            100%;

        min-height:
            92px;

        padding:
            11px;

        border:
            1px solid #DCE5E0;

        border-radius:
            11px;

        background:
            #FBFCFB;

        color:
            #26342D;

        font-family:
            inherit;

        font-size:
            11px;

        line-height:
            1.6;

        resize:
            vertical;

        outline:
            none;

    }


    .dr-composer textarea:focus {

        border-color:
            #12B76A;

        box-shadow:
            0 0 0 3px rgba(18,183,106,.08);

    }


    .dr-selected-files {

        display:
            flex;

        flex-wrap:
            wrap;

        gap:
            6px;

        margin-top:
            8px;

    }


    .dr-file-chip {

        padding:
            5px 7px;

        border-radius:
            7px;

        background:
            #F1F4F2;

        color:
            #56645C;

        font-size:
            8px;

    }


    .dr-composer-bottom {

        display:
            flex;

        align-items:
            center;

        gap:
            10px;

        margin-top:
            10px;

    }


    .dr-attach-button {

        display:
            inline-flex;

        align-items:
            center;

        gap:
            6px;

        padding:
            8px 10px;

        border:
            1px solid #D9E2DD;

        border-radius:
            9px;

        color:
            #0B3D2E;

        font-size:
            9px;

        font-weight:
            800;

        cursor:
            pointer;

    }


    .dr-character-count {

        margin-left:
            auto;

        color:
            #8B9690;

        font-size:
            8px;

    }


    .dr-send-button {

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        gap:
            6px;

        min-height:
            36px;

        padding:
            0 13px;

        border:
            0;

        border-radius:
            9px;

        background:
            #12B76A;

        color:
            #FFFFFF;

        font-size:
            9px;

        font-weight:
            800;

        cursor:
            pointer;

    }


    .dr-send-button:disabled {

        cursor:
            wait;

        opacity:
            .65;

    }


    @media(max-width: 640px) {

        .dr-room-header {

            flex-direction:
                column;

        }


        .dr-message-list {

            height:
                440px;

            padding:
                14px;

        }


        .dr-message {

            max-width:
                92%;

        }


        .dr-composer-bottom {

            flex-wrap:
                wrap;

        }


        .dr-character-count {

            margin-left:
                0;

        }


        .dr-send-button {

            margin-left:
                auto;

        }

    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const room =
            document.getElementById(
                'disputeResolutionRoom'
            );


        if (!room) {
            return;
        }


        const list =
            document.getElementById(
                'drMessageList'
            );


        const form =
            document.getElementById(
                'drMessageForm'
            );


        const input =
            document.getElementById(
                'drMessageInput'
            );


        const filesInput =
            document.getElementById(
                'drAttachments'
            );


        const filesPreview =
            document.getElementById(
                'drSelectedFiles'
            );


        const characterCount =
            document.getElementById(
                'drCharacterCount'
            );


        const sendButton =
            document.getElementById(
                'drSendButton'
            );


        let lastMessageId =
            Number(
                room.dataset.lastMessageId
                ||
                0
            );


        let polling =
            false;


        function escapeHtml(
            value
        ) {

            const div =
                document.createElement(
                    'div'
                );


            div.textContent =
                value
                ??
                '';


            return div.innerHTML;
        }


        function formatSize(
            bytes
        ) {

            const value =
                Number(
                    bytes
                    ||
                    0
                );


            if (
                value
                <
                1024
            ) {

                return value
                +
                ' B';
            }


            if (
                value
                <
                1024
                *
                1024
            ) {

                return (
                    value
                    /
                    1024
                )
                    .toFixed(
                        1
                    )
                    +
                    ' KB';
            }


            return (
                value
                /
                1024
                /
                1024
            )
                .toFixed(
                    1
                )
                +
                ' MB';
        }


        function appendMessage(
            message
        ) {

            if (
                !message
                ||
                !message.id
                ||
                document.querySelector(
                    '[data-message-id="'
                    +
                    message.id
                    +
                    '"]'
                )
            ) {

                return;
            }


            document
                .getElementById(
                    'drEmptyState'
                )
                ?.remove();


            const own =
                Number(
                    message.sender_id
                    ||
                    0
                )
                ===
                Number(
                    {{ auth()->id() ?? 0 }}
                );


            let cssClass =
                'other';


            if (
                message.is_system
            ) {

                cssClass =
                    'system';

            } else if (
                message.sender_role
                ===
                'admin'
            ) {

                cssClass =
                    'admin';

            } else if (
                own
            ) {

                cssClass =
                    'own';
            }


            const article =
                document.createElement(
                    'article'
                );


            article.className =
                'dr-message '
                +
                cssClass;


            article.dataset.messageId =
                message.id;


            const privateBadge =
                (
                    {{ $adminMode ? 'true' : 'false' }}
                    &&
                    [
                        'buyer',
                        'seller',
                        'internal',
                    ].includes(
                        message.visibility
                    )
                )

                    ? '<span class="dr-private-badge">'
                        +
                        (
                            message.visibility
                            ===
                            'buyer'

                                ? 'Buyer only'

                                : (
                                    message.visibility
                                    ===
                                    'seller'

                                        ? 'Seller only'

                                        : 'Internal note'
                                )
                        )
                        +
                        '</span>'

                    : '';


            const textHtml =
                message.message

                    ? '<div class="dr-message-text">'
                        +
                        escapeHtml(
                            message.message
                        )
                            .replace(
                                /\n/g,
                                '<br>'
                            )
                        +
                        '</div>'

                    : '';


            const attachments =
                Array.isArray(
                    message.attachments
                )

                    ? message.attachments

                    : [];


            const attachmentHtml =
                attachments.length

                    ? '<div class="dr-attachments">'
                        +
                        attachments
                            .map(
                                function (
                                    attachment
                                ) {

                                    return '<a href="'
                                        +
                                        escapeHtml(
                                            attachment.url
                                        )
                                        +
                                        '" class="dr-attachment">'
                                        +
                                        '<i class="fa-solid fa-paperclip"></i>'
                                        +
                                        '<span>'
                                        +
                                        escapeHtml(
                                            attachment.name
                                        )
                                        +
                                        '</span>'
                                        +
                                        '</a>';
                                }
                            )
                            .join(
                                ''
                            )
                        +
                        '</div>'

                    : '';


            article.innerHTML =

                '<div class="dr-message-meta">'
                +
                '<strong>'
                +
                escapeHtml(
                    message.sender_name
                )
                +
                '</strong>'
                +
                privateBadge
                +
                '<time>'
                +
                escapeHtml(
                    message.created_at
                )
                +
                '</time>'
                +
                '</div>'
                +
                textHtml
                +
                attachmentHtml;


            list.appendChild(
                article
            );


            lastMessageId =
                Math.max(
                    lastMessageId,
                    Number(
                        message.id
                    )
                );


            list.scrollTop =
                list.scrollHeight;
        }


        async function pollMessages()
        {
            if (
                polling
            ) {

                return;
            }


            polling =
                true;


            try {

                const url =
                    new URL(
                        room.dataset.messagesUrl,
                        window.location.origin
                    );


                url.searchParams.set(
                    'after_id',
                    String(
                        lastMessageId
                    )
                );


                const response =
                    await fetch(
                        url.toString(),
                        {
                            headers: {
                                'Accept':
                                    'application/json',
                            },

                            credentials:
                                'same-origin',
                        }
                    );


                if (
                    !response.ok
                ) {

                    return;
                }


                const data =
                    await response.json();


                (
                    data.messages
                    ||
                    []
                )
                    .forEach(
                        appendMessage
                    );

            } catch (
                error
            ) {

                console.error(
                    'Dispute room polling failed.',
                    error
                );

            } finally {

                polling =
                    false;
            }
        }


        if (
            input
        ) {

            input.addEventListener(
                'input',
                function () {

                    if (
                        characterCount
                    ) {

                        characterCount.textContent =
                            input.value.length
                            +
                            ' / 10,000';
                    }
                }
            );
        }


        if (
            filesInput
            &&
            filesPreview
        ) {

            filesInput.addEventListener(
                'change',
                function () {

                    filesPreview.innerHTML =
                        '';


                    Array
                        .from(
                            filesInput.files
                            ||
                            []
                        )
                        .slice(
                            0,
                            6
                        )
                        .forEach(
                            function (
                                file
                            ) {

                                const chip =
                                    document.createElement(
                                        'span'
                                    );


                                chip.className =
                                    'dr-file-chip';


                                chip.textContent =
                                    file.name
                                    +
                                    ' · '
                                    +
                                    formatSize(
                                        file.size
                                    );


                                filesPreview.appendChild(
                                    chip
                                );
                            }
                        );
                }
            );
        }


        if (
            form
        ) {

            form.addEventListener(
                'submit',
                async function (
                    event
                ) {

                    event.preventDefault();


                    if (
                        sendButton
                    ) {

                        sendButton.disabled =
                            true;
                    }


                    const body =
                        new FormData(
                            form
                        );


                    try {

                        const response =
                            await fetch(
                                room.dataset.sendUrl,
                                {
                                    method:
                                        'POST',

                                    body:
                                        body,

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            document
                                                .querySelector(
                                                    'meta[name="csrf-token"]'
                                                )
                                                ?.content
                                            ||
                                            '',
                                    },

                                    credentials:
                                        'same-origin',
                                }
                            );


                        const data =
                            await response.json();


                        if (
                            !response.ok
                        ) {

                            const errors =
                                data.errors
                                ||
                                {};


                            const firstError =
                                Object
                                    .values(
                                        errors
                                    )
                                    .flat()
                                    [0];


                            alert(
                                firstError
                                ||
                                data.message
                                ||
                                'Unable to send this message.'
                            );


                            return;
                        }


                        appendMessage(
                            data.room_message
                        );


                        if (
                            input
                        ) {

                            input.value =
                                '';

                            input.dispatchEvent(
                                new Event(
                                    'input'
                                )
                            );
                        }


                        if (
                            filesInput
                        ) {

                            filesInput.value =
                                '';
                        }


                        if (
                            filesPreview
                        ) {

                            filesPreview.innerHTML =
                                '';
                        }


                    } catch (
                        error
                    ) {

                        console.error(
                            error
                        );


                        alert(
                            'Unable to send this message right now.'
                        );

                    } finally {

                        if (
                            sendButton
                        ) {

                            sendButton.disabled =
                                false;
                        }
                    }
                }
            );
        }


        list.scrollTop =
            list.scrollHeight;


        setInterval(
            pollMessages,
            4000
        );

    }
);

</script>

@endpush
