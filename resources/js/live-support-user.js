import './bootstrap';
document.addEventListener(
    'DOMContentLoaded',
    () => {

        const widget =
            document.getElementById(
                'liveSupportWidget'
            );


        if (!widget) {
            return;
        }


        const trigger =
            document.getElementById(
                'live-chat-button'
            );


        const panel =
            document.getElementById(
                'lsPanel'
            );


        const closeButton =
            document.getElementById(
                'lsClose'
            );


        const messages =
            document.getElementById(
                'lsMessages'
            );


        const status =
            document.getElementById(
                'lsStatus'
            );


        const composer =
            document.getElementById(
                'lsComposer'
            );


        const input =
            document.getElementById(
                'lsInput'
            );


        const fileInput =
            document.getElementById(
                'lsFiles'
            );


        const selectedFiles =
            document.getElementById(
                'lsSelectedFiles'
            );


        const ratingBox =
            document.getElementById(
                'lsRating'
            );


        const stars =
            document.querySelectorAll(
                '#lsStars button'
            );


        let session =
            null;


        let currentChannel =
            null;


        let chosenRating =
            0;


        /*
        |--------------------------------------------------------------------------
        | HTTP
        |--------------------------------------------------------------------------
        */

        function getCsrfToken()
        {
            const token =
                document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    )
                    ?.getAttribute(
                        'content'
                    );


            if (!token) {

                throw new Error(
                    'CSRF token is missing. Refresh the page and try again.'
                );

            }


            return token;
        }


        async function request(
            url,
            options = {}
        ) {

            const {
                headers:
                    optionHeaders = {},

                ...restOptions
            } = options;


            const method =
                (
                    restOptions.method
                    || 'GET'
                )
                    .toUpperCase();


            const headers =
                new Headers(
                    optionHeaders
                );


            headers.set(
                'Accept',
                'application/json'
            );


            headers.set(
                'X-Requested-With',
                'XMLHttpRequest'
            );
if (
    window.Echo
    &&
    typeof window.Echo.socketId
        === 'function'
) {

    const socketId =
        window.Echo.socketId();


    if (socketId) {

        headers.set(
            'X-Socket-ID',
            socketId
        );

    }

}

            if (
                ![
                    'GET',
                    'HEAD',
                    'OPTIONS',
                ]
                    .includes(
                        method
                    )
            ) {

                headers.set(
                    'X-CSRF-TOKEN',
                    getCsrfToken()
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Do not set multipart Content-Type manually for FormData.
            | Browser will add the multipart boundary automatically.
            |--------------------------------------------------------------------------
            */

            if (
                restOptions.body
                instanceof FormData
            ) {

                headers.delete(
                    'Content-Type'
                );

            }


            const response =
                await fetch(
                    url,
                    {
                        ...restOptions,

                        method:
                            method,

                        credentials:
                            'same-origin',

                        headers:
                            headers,
                    }
                );


            const contentType =
                response
                    .headers
                    .get(
                        'content-type'
                    )
                || '';


            let data = {};


            try {

                if (
                    contentType.includes(
                        'application/json'
                    )
                ) {

                    data =
                        await response.json();

                } else {

                    const text =
                        await response.text();


                    data =
                        text
                            ? {
                                message:
                                    text,
                            }
                            : {};

                }

            } catch (error) {

                data = {};

            }


            if (
                response.status
                ===
                419
            ) {

                throw new Error(
                    'Your session or security token has expired. Refresh the page and try again.'
                );

            }


            if (
                response.status
                ===
                401
            ) {

                throw new Error(
                    'Your login session has expired. Please log in again.'
                );

            }


            if (
                response.status
                ===
                403
            ) {

                throw new Error(
                    data.message
                    ||
                    'You are not allowed to perform this action.'
                );

            }


            if (!response.ok) {

                throw new Error(
                    data.message
                    ||
                    'Something went wrong.'
                );

            }


            return data;
        }


        /*
        |--------------------------------------------------------------------------
        | Open
        |--------------------------------------------------------------------------
        */

        async function openChat()
        {
            panel.classList.add(
                'open'
            );


            status.textContent =
                'Connecting to Midpoint Support...';


            try {

                const data =
                    await request(
                        widget.dataset.startUrl,
                        {
                            method:
                                'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',
                            },

                            body:
                                JSON.stringify({
                                    topic:
                                        'Live Support',
                                }),
                        }
                    );


                session =
                    data.session;


                renderConversation(
                    data.messages
                );


                applySessionState(
                    session
                );


                subscribe(
                    session.uuid
                );

            } catch (error) {

                appendSystemMessage(
                    error.message
                );

                status.textContent =
                    'Unable to start live chat';

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Conversation
        |--------------------------------------------------------------------------
        */

        function renderConversation(
            items
        ) {

            messages.innerHTML =
                '';


            items.forEach(
                appendMessage
            );


            scrollBottom();
        }


        function appendMessage(
            message
        ) {

            const row =
                document.createElement(
                    'div'
                );


            row.className =
                'ls-message-row '
                +
                (
                    message.sender_type
                    || 'system'
                );


            if (
                message.sender_type
                ===
                'system'
            ) {

                const system =
                    document.createElement(
                        'div'
                    );


                system.className =
                    'ls-system-message';


                system.textContent =
                    message.body
                    || '';


                row.appendChild(
                    system
                );


            } else {

                const bubble =
                    document.createElement(
                        'div'
                    );


                bubble.className =
                    'ls-message-bubble';


                const sender =
                    document.createElement(
                        'div'
                    );


                sender.className =
                    'ls-message-sender';


                sender.textContent =
                    message.sender?.name
                    ||
                    (
                        message.sender_type
                        ===
                        'agent'
                            ? 'Support'
                            : 'You'
                    );


                bubble.appendChild(
                    sender
                );


                if (message.body) {

                    const body =
                        document.createElement(
                            'div'
                        );


                    body.className =
                        'ls-message-body';


                    body.textContent =
                        message.body;


                    bubble.appendChild(
                        body
                    );

                }


                (
                    message.attachments
                    || []
                ).forEach(
                    attachment => {

                        bubble.appendChild(
                            createAttachment(
                                attachment
                            )
                        );

                    }
                );


                row.appendChild(
                    bubble
                );

            }


            messages.appendChild(
                row
            );


            scrollBottom();
        }


        function appendSystemMessage(
            text
        ) {

            appendMessage({
                sender_type:
                    'system',

                body:
                    text,

                attachments:
                    [],
            });

        }


        function createAttachment(
            attachment
        ) {

            const wrap =
                document.createElement(
                    'div'
                );


            wrap.className =
                'ls-attachment';


            if (
                attachment.kind
                ===
                'image'
            ) {

                const image =
                    document.createElement(
                        'img'
                    );


                image.src =
                    attachment.url;


                image.alt =
                    attachment.name;


                image.loading =
                    'lazy';


                image.addEventListener(
                    'click',
                    () => {

                        window.open(
                            attachment.url,
                            '_blank'
                        );

                    }
                );


                wrap.appendChild(
                    image
                );


            } else if (
                attachment.kind
                ===
                'video'
            ) {

                const video =
                    document.createElement(
                        'video'
                    );


                video.src =
                    attachment.url;


                video.controls =
                    true;


                video.preload =
                    'metadata';


                wrap.appendChild(
                    video
                );


            } else {

                const link =
                    document.createElement(
                        'a'
                    );


                link.href =
                    attachment.url;


                link.target =
                    '_blank';


                link.rel =
                    'noopener';


                link.textContent =
                    '📎 '
                    +
                    attachment.name;


                wrap.appendChild(
                    link
                );

            }


            return wrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Session State
        |--------------------------------------------------------------------------
        */

        function applySessionState(
            updated
        ) {

            session =
                updated;


            if (
                session.status
                ===
                'waiting'
            ) {

                status.innerHTML =
                    `
                    <span class="ls-wait-dot"></span>
                    Waiting for an agent ·
                    Queue #${session.queue_position || 1}
                    `;


                composer.hidden =
                    false;


                ratingBox.hidden =
                    true;


            } else if (
                session.status
                ===
                'active'
            ) {

                status.innerHTML =
                    `
                    <span class="ls-online-dot"></span>
                    Connected with
                    ${escapeHtml(
                        session.agent?.name
                        || 'Midpoint Support'
                    )}
                    `;


                composer.hidden =
                    false;


                ratingBox.hidden =
                    true;


            } else if (
                session.status
                ===
                'resolved'
                ||
                session.status
                ===
                'closed'
            ) {

                status.textContent =
                    'Conversation resolved';


                composer.hidden =
                    true;


                if (
                    !session.rating
                    &&
                    session.status
                    ===
                    'resolved'
                ) {

                    ratingBox.hidden =
                        false;

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Pusher / Echo
        |--------------------------------------------------------------------------
        */

        function subscribe(
            uuid
        ) {

            if (
                !window.Echo
            ) {

                appendSystemMessage(
                    'Realtime connection is unavailable. Please refresh the page.'
                );

                return;
            }


            if (currentChannel) {

                window.Echo.leave(
                    currentChannel
                );

            }


            currentChannel =
                `support.session.${uuid}`;


            window.Echo
                .private(
                    currentChannel
                )

                .listen(
                    '.support.message',
                    event => {

                        appendMessage(
                            event.message
                        );

                    }
                )

                .listen(
                    '.support.session.updated',
                    event => {

                        applySessionState(
                            event.session
                        );

                    }
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Send
        |--------------------------------------------------------------------------
        */

        composer.addEventListener(
            'submit',
            async event => {

                event.preventDefault();


                if (!session) {
                    return;
                }


                const body =
                    input.value.trim();


                const files =
                    Array.from(
                        fileInput.files
                        || []
                    );


                if (
                    !body
                    &&
                    files.length === 0
                ) {

                    return;

                }


                const form =
                    new FormData();


                if (body) {

                    form.append(
                        'body',
                        body
                    );

                }


                files.forEach(
                    file => {

                        form.append(
                            'attachments[]',
                            file
                        );

                    }
                );


                input.disabled =
                    true;


                try {

                    const data =
                        await request(
                            `${
                                widget.dataset.sessionBase
                            }/${
                                session.uuid
                            }/messages`,
                            {
                                method:
                                    'POST',

                                body:
                                    form,
                            }
                        );


                    appendMessage(
                        data.message
                    );


                    input.value =
                        '';


                    fileInput.value =
                        '';


                    selectedFiles.innerHTML =
                        '';


                } catch (error) {

                    appendSystemMessage(
                        error.message
                    );

                } finally {

                    input.disabled =
                        false;


                    input.focus();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Files
        |--------------------------------------------------------------------------
        */

        fileInput.addEventListener(
            'change',
            () => {

                selectedFiles.innerHTML =
                    '';


                Array.from(
                    fileInput.files
                    || []
                )
                .forEach(
                    file => {

                        const chip =
                            document.createElement(
                                'span'
                            );


                        chip.textContent =
                            '📎 '
                            +
                            file.name;


                        selectedFiles.appendChild(
                            chip
                        );

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Rating
        |--------------------------------------------------------------------------
        */

        stars.forEach(
            star => {

                star.addEventListener(
                    'click',
                    () => {

                        chosenRating =
                            Number(
                                star.dataset.rating
                            );


                        stars.forEach(
                            button => {

                                button.classList.toggle(
                                    'selected',
                                    Number(
                                        button.dataset.rating
                                    )
                                    <=
                                    chosenRating
                                );

                            }
                        );

                    }
                );

            }
        );


        document
            .getElementById(
                'lsSubmitRating'
            )
            .addEventListener(
                'click',
                async () => {

                    if (
                        !session
                        ||
                        chosenRating === 0
                    ) {

                        return;

                    }


                    const review =
                        document
                            .getElementById(
                                'lsReview'
                            )
                            .value
                            .trim();


                    await request(
                        `${
                            widget.dataset.sessionBase
                        }/${
                            session.uuid
                        }/rating`,
                        {
                            method:
                                'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',
                            },

                            body:
                                JSON.stringify({
                                    rating:
                                        chosenRating,

                                    review:
                                        review,
                                }),
                        }
                    );


                    ratingBox.innerHTML =
                        `
                        <div class="ls-rating-thanks">
                            <div>💚</div>
                            <strong>Thank you!</strong>
                            <p>Your feedback has been recorded.</p>
                        </div>
                        `;

                }
            );


        document
            .getElementById(
                'lsSkipRating'
            )
            .addEventListener(
                'click',
                async () => {

                    if (!session) {
                        return;
                    }


                    await request(
                        `${
                            widget.dataset.sessionBase
                        }/${
                            session.uuid
                        }/skip-rating`,
                        {
                            method:
                                'POST',
                        }
                    );


                    panel.classList.remove(
                        'open'
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | UI
        |--------------------------------------------------------------------------
        */

        trigger?.addEventListener(
            'click',
            openChat
        );


        closeButton.addEventListener(
            'click',
            () => {

                panel.classList.remove(
                    'open'
                );

            }
        );


        if (
            widget.dataset.autoOpen
            ===
            '1'
        ) {

            openChat();

        }


        function scrollBottom()
        {
            requestAnimationFrame(
                () => {

                    messages.scrollTop =
                        messages.scrollHeight;

                }
            );
        }


        function escapeHtml(
            value
        ) {

            const div =
                document.createElement(
                    'div'
                );


            div.textContent =
                value || '';


            return div.innerHTML;
        }

    }
);