document.addEventListener(
    'DOMContentLoaded',
    () => {

        const app =
            document.getElementById(
                'adminLiveSupport'
            );


        if (!app) {
            return;
        }
        const waitingList =
            document.getElementById(
                'alsWaitingList'
            );


        const activeList =
            document.getElementById(
                'alsActiveList'
            );


        const empty =
            document.getElementById(
                'alsEmptyChat'
            );


        const conversation =
            document.getElementById(
                'alsConversation'
            );


        const messages =
            document.getElementById(
                'alsMessages'
            );


        const composer =
            document.getElementById(
                'alsComposer'
            );


        const claimButton =
            document.getElementById(
                'alsClaim'
            );


        const resolveButton =
            document.getElementById(
                'alsResolve'
            );


        let currentSession =
            null;


        let sessionChannel =
            null;


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
                    'Request failed.'
                );

            }


            return data;
        }


        /*
        |--------------------------------------------------------------------------
        | Inbox Feed
        |--------------------------------------------------------------------------
        */

        async function loadFeed()
        {
            const data =
                await request(
                    app.dataset.feedUrl
                );


            renderWaiting(
                data.waiting
            );


            renderActive(
                data.active
            );


            document
                .getElementById(
                    'alsWaitingCount'
                )
                .textContent =
                    data.waiting.length;


            document
                .getElementById(
                    'alsActiveCount'
                )
                .textContent =
                    data.active.length;
        }


        function renderWaiting(
            items
        ) {

            waitingList.innerHTML =
                '';


            if (!items.length) {

                waitingList.innerHTML =
                    `
                    <div class="als-no-items">
                        No customers waiting.
                    </div>
                    `;

                return;
            }


            items.forEach(
                session => {

                    waitingList.appendChild(
                        createListItem(
                            session,
                            true
                        )
                    );

                }
            );

        }


        function renderActive(
            items
        ) {

            activeList.innerHTML =
                '';


            if (!items.length) {

                activeList.innerHTML =
                    `
                    <div class="als-no-items">
                        No active conversations.
                    </div>
                    `;

                return;
            }


            items.forEach(
                session => {

                    activeList.appendChild(
                        createListItem(
                            session,
                            false
                        )
                    );

                }
            );

        }


        function createListItem(
            session,
            waiting
        ) {

            const button =
                document.createElement(
                    'button'
                );


            button.type =
                'button';


            button.className =
                'als-session-item';


            const queue =
                waiting
                    ?
                    `
                    <span class="als-queue-number">
                        #${session.queue_position}
                    </span>
                    `
                    :
                    `
                    <span class="als-active-dot"></span>
                    `;


            button.innerHTML =
                `
                <div class="als-session-top">
                    <strong>
                        ${escapeHtml(
                            session.user?.name
                            || 'Customer'
                        )}
                    </strong>

                    ${queue}
                </div>

                <span>
                    ${
                        escapeHtml(
                            session.topic
                            || 'Live Support'
                        )
                    }
                </span>
                `;


            button.addEventListener(
                'click',
                () => {

                    openSession(
                        session.uuid
                    );

                }
            );


            return button;
        }


        /*
        |--------------------------------------------------------------------------
        | Open Session
        |--------------------------------------------------------------------------
        */

        async function openSession(
            uuid
        ) {

            const data =
                await request(
                    `${
                        app.dataset.sessionBase
                    }/${uuid}`
                );


            currentSession =
                data.session;


            empty.hidden =
                true;


            conversation.hidden =
                false;


            document
                .getElementById(
                    'alsCustomerName'
                )
                .textContent =
                    currentSession
                        .user
                        ?.name
                    ||
                    'Customer';


            document
                .getElementById(
                    'alsCustomerEmail'
                )
                .textContent =
                    currentSession
                        .user
                        ?.email
                    ||
                    '';


            claimButton.hidden =
                currentSession.status
                !==
                'waiting';


            resolveButton.hidden =
                currentSession.status
                !==
                'active';


            composer.hidden =
                currentSession.status
                !==
                'active';


            renderMessages(
                data.messages
            );


            subscribeSession(
                uuid
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Chat Render
        |--------------------------------------------------------------------------
        */

        function renderMessages(
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
                'als-message '
                +
                message.sender_type;


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
                    'als-system-message';


                system.textContent =
                    message.body;


                row.appendChild(
                    system
                );


            } else {

                const bubble =
                    document.createElement(
                        'div'
                    );


                bubble.className =
                    'als-message-bubble';


                const name =
                    document.createElement(
                        'small'
                    );


                name.textContent =
                    message.sender?.name
                    || 'Customer';


                bubble.appendChild(
                    name
                );


                if (message.body) {

                    const body =
                        document.createElement(
                            'div'
                        );


                    body.textContent =
                        message.body;


                    bubble.appendChild(
                        body
                    );

                }


                (
                    message.attachments
                    || []
                )
                .forEach(
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

                const img =
                    document.createElement(
                        'img'
                    );


                img.src =
                    attachment.url;


                img.onclick =
                    () => window.open(
                        attachment.url,
                        '_blank'
                    );


                wrap.appendChild(
                    img
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
        | Realtime
        |--------------------------------------------------------------------------
        */

function subscribeSession(
    uuid
) {

    if (!window.Echo) {

        console.error(
            'Laravel Echo is not available.'
        );


        toast(
            'Realtime connection is unavailable.'
        );


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Leave Previous Channel
    |--------------------------------------------------------------------------
    */

    if (sessionChannel) {

        window.Echo.leave(
            sessionChannel
        );

    }


    sessionChannel =
        `support.session.${uuid}`;


    console.log(
        '[Live Support] subscribing:',
        sessionChannel
    );


    const channel =
        window.Echo
            .private(
                sessionChannel
            );


    /*
    |--------------------------------------------------------------------------
    | Subscription Success
    |--------------------------------------------------------------------------
    */

    channel.subscribed(
        () => {

            console.log(
                '[Live Support] subscribed:',
                sessionChannel
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Subscription Error
    |--------------------------------------------------------------------------
    */

    channel.error(
        error => {

            console.error(
                '[Live Support] subscription failed:',
                error
            );


            toast(
                'Realtime subscription failed.'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | New Message
    |--------------------------------------------------------------------------
    */

    channel.listen(
        '.support.message',
        event => {

            console.log(
                '[Live Support] message received:',
                event
            );


            if (
                event.message
            ) {

                appendMessage(
                    event.message
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Session Updated
    |--------------------------------------------------------------------------
    */

    channel.listen(
        '.support.session.updated',
        event => {

            console.log(
                '[Live Support] session updated:',
                event
            );


            currentSession =
                event.session;


            claimButton.hidden =
                currentSession.status
                !==
                'waiting';


            resolveButton.hidden =
                currentSession.status
                !==
                'active';


            composer.hidden =
                currentSession.status
                !==
                'active';


            loadFeed();

        }
    );
}


        /*
        |--------------------------------------------------------------------------
        | Agent Global Channel
        |--------------------------------------------------------------------------
        */

        if (window.Echo) {

            window.Echo
                .private(
                    'support.agents'
                )

                .listen(
                    '.support.inbox.updated',
                    event => {

                        loadFeed();


                        if (
                            event.action
                            ===
                            'new_request'
                        ) {

                            toast(
                                `${
                                    event.session
                                        .user
                                        ?.name
                                    || 'A customer'
                                } requested live support.`
                            );

                        }

                    }
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Claim
        |--------------------------------------------------------------------------
        */

        claimButton.addEventListener(
            'click',
            async () => {

                if (!currentSession) {
                    return;
                }


                try {

                    await request(
                        `${
                            app.dataset.claimBase
                        }/${
                            currentSession.uuid
                        }/claim`,
                        {
                            method:
                                'POST',
                        }
                    );


                    await openSession(
                        currentSession.uuid
                    );


                    await loadFeed();

                } catch (error) {

                    toast(
                        error.message
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Send Reply
        |--------------------------------------------------------------------------
        */

        composer.addEventListener(
            'submit',
            async event => {

                event.preventDefault();


                if (!currentSession) {
                    return;
                }


                const input =
                    document.getElementById(
                        'alsInput'
                    );


                const fileInput =
                    document.getElementById(
                        'alsFiles'
                    );


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
                    !files.length
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
                    file =>
                        form.append(
                            'attachments[]',
                            file
                        )
                );


                try {

                    const data =
                        await request(
                            `${
                                app.dataset.sessionBase
                            }/${
                                currentSession.uuid
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


                } catch (error) {

                    toast(
                        error.message
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        */

        const availableToggle =
            document.getElementById(
                'alsAvailable'
            );


        availableToggle.addEventListener(
            'change',
            async () => {

                try {

                    const data =
                        await request(
                            app.dataset
                                .availabilityUrl,
                            {
                                method:
                                    'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',
                                },

                                body:
                                    JSON.stringify({
                                        available:
                                            availableToggle
                                                .checked
                                                ? 1
                                                : 0,
                                    }),
                            }
                        );


                    document
                        .getElementById(
                            'alsAvailabilityText'
                        )
                        .textContent =
                            data.available
                                ? 'Available'
                                : 'Unavailable';


                    await loadFeed();

                } catch (error) {

                    availableToggle.checked =
                        !availableToggle.checked;


                    toast(
                        error.message
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Heartbeat
        |--------------------------------------------------------------------------
        */

        async function heartbeat()
        {
            try {

                await request(
                    app.dataset.heartbeatUrl,
                    {
                        method:
                            'POST',
                    }
                );

            } catch (e) {
                //
            }
        }


        heartbeat();


        setInterval(
            heartbeat,
            30000
        );


        /*
        |--------------------------------------------------------------------------
        | Resolution
        |--------------------------------------------------------------------------
        */

        const modal =
            document.getElementById(
                'alsResolveModal'
            );


        resolveButton.addEventListener(
            'click',
            () => {

                modal.hidden =
                    false;

            }
        );


        document
            .getElementById(
                'alsResolveClose'
            )
            .addEventListener(
                'click',
                () => {

                    modal.hidden =
                        true;

                }
            );


        document
            .getElementById(
                'alsConfirmResolve'
            )
            .addEventListener(
                'click',
                async () => {

                    if (!currentSession) {
                        return;
                    }


                    const code =
                        document
                            .getElementById(
                                'alsResolutionCode'
                            )
                            .value;


                    const note =
                        document
                            .getElementById(
                                'alsResolutionNote'
                            )
                            .value
                            .trim();


                    try {

                        await request(
                            `${
                                app.dataset.claimBase
                            }/${
                                currentSession.uuid
                            }/resolve`,
                            {
                                method:
                                    'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',
                                },

                                body:
                                    JSON.stringify({
                                        resolution_code:
                                            code,

                                        resolution_note:
                                            note,
                                    }),
                            }
                        );


                        modal.hidden =
                            true;


                        composer.hidden =
                            true;


                        resolveButton.hidden =
                            true;


                        toast(
                            'Support conversation resolved.'
                        );


                        await loadFeed();

                    } catch (error) {

                        toast(
                            error.message
                        );

                    }

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Tabs
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-support-tab]'
            )
            .forEach(
                button => {

                    button.addEventListener(
                        'click',
                        () => {

                            document
                                .querySelectorAll(
                                    '[data-support-tab]'
                                )
                                .forEach(
                                    item =>
                                        item.classList
                                            .remove(
                                                'active'
                                            )
                                );


                            button.classList.add(
                                'active'
                            );


                            const waiting =
                                button.dataset
                                    .supportTab
                                ===
                                'waiting';


                            waitingList.hidden =
                                !waiting;


                            activeList.hidden =
                                waiting;

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        function toast(
            message
        ) {

            const element =
                document.getElementById(
                    'alsToast'
                );


            element.textContent =
                message;


            element.classList.add(
                'show'
            );


            clearTimeout(
                element._timer
            );


            element._timer =
                setTimeout(
                    () => {

                        element.classList
                            .remove(
                                'show'
                            );

                    },
                    3500
                );
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


        /*
        |--------------------------------------------------------------------------
        | Start
        |--------------------------------------------------------------------------
        */

        loadFeed();

    }
);