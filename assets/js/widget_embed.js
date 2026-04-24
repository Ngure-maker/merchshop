(function () {
    const script = document.currentScript;
    const baseUrl = script && script.getAttribute('data-endpoint');
    const title = (script && script.getAttribute('data-title')) || 'Chat Support';
    const subtitle = (script && script.getAttribute('data-subtitle')) || 'Zetech University';

    if (!baseUrl) {
        console.warn('Chat widget embed: data-endpoint is required.');
        return;
    }

    const css = `
        .chat-widget{position:fixed;right:24px;bottom:24px;z-index:1000;font-family:"Segoe UI","Helvetica Neue",Arial,sans-serif;}
        .chat-widget__launcher{width:56px;height:56px;border-radius:50%;border:none;background:#0b5bd3;box-shadow:0 12px 28px rgba(11,91,211,.3);cursor:pointer;display:flex;align-items:center;justify-content:center}
        .chat-widget__launcher.is-hidden{display:none}
        .chat-widget__icon{width:24px;height:24px;background:white;mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M20 3H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3v3l4-3h9a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z'/%3E%3C/svg%3E") center/contain no-repeat}
        .chat-widget__panel{position:absolute;right:0;bottom:72px;width:min(380px,92vw);height:min(520px,80vh);background:white;border-radius:16px;box-shadow:0 24px 60px rgba(11,21,40,.25);overflow:hidden;display:none;flex-direction:column;border:1px solid #e5e9f2}
        .chat-widget__panel.is-open{display:flex}
        .chat-widget__header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#0b1d3a;color:white;gap:12px}
        .chat-widget__title small{display:block;color:rgba(255,255,255,.7);font-size:.75rem}
        .chat-widget__status{display:inline-flex;align-items:center;gap:6px;font-size:.75rem;color:rgba(255,255,255,.9);margin-left:auto}
        .chat-widget__status-dot{width:8px;height:8px;border-radius:50%;background:#f39c12;box-shadow:0 0 6px rgba(243,156,18,.6)}
        .chat-widget__status.is-online .chat-widget__status-dot{background:#27ae60;box-shadow:0 0 6px rgba(39,174,96,.6)}
        .chat-widget__status.is-offline .chat-widget__status-dot{background:#e74c3c;box-shadow:0 0 6px rgba(231,76,60,.6)}
        .chat-widget__close{background:transparent;border:none;color:white;font-size:1.2rem;cursor:pointer}
        .chat-widget__frame{width:100%;height:100%;border:none;background:white}
        @media (max-width:600px){.chat-widget{right:16px;bottom:16px}.chat-widget__panel{width:min(360px,92vw);height:min(520px,75vh)}}
    `;

    const style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-widget';
    wrapper.innerHTML = `
        <button class="chat-widget__launcher" id="chatWidgetLauncher" aria-label="Open chat">
            <span class="chat-widget__icon"></span>
        </button>
        <div class="chat-widget__panel" id="chatWidgetPanel" aria-hidden="true">
            <div class="chat-widget__header">
                <div class="chat-widget__title">
                    <strong>${title}</strong>
                    <small>${subtitle}</small>
                </div>
                <div class="chat-widget__status" id="chatWidgetStatus">
                    <span class="chat-widget__status-dot"></span>
                    <span class="chat-widget__status-text">Connecting...</span>
                </div>
                <button class="chat-widget__close" id="chatWidgetClose" aria-label="Close chat">x</button>
            </div>
            <iframe class="chat-widget__frame" id="chatWidgetFrame" title="Chat support"></iframe>
        </div>
    `;
    document.body.appendChild(wrapper);

    const launcher = wrapper.querySelector('#chatWidgetLauncher');
    const panel = wrapper.querySelector('#chatWidgetPanel');
    const closeBtn = wrapper.querySelector('#chatWidgetClose');
    const frame = wrapper.querySelector('#chatWidgetFrame');
    const statusEl = wrapper.querySelector('#chatWidgetStatus');
    const statusText = statusEl ? statusEl.querySelector('.chat-widget__status-text') : null;

    const fallbackToNewTab = (chatUrl) => {
        try {
            if (!panel) return;
            const safeUrl = String(chatUrl || '');
            panel.innerHTML = `
                <div class="chat-widget__header">
                    <div class="chat-widget__title">
                        <strong>${title}</strong>
                        <small>${subtitle}</small>
                    </div>
                    <button class="chat-widget__close" id="chatWidgetClose" aria-label="Close chat">x</button>
                </div>
                <div style="padding:16px;">
                    <div style="font-weight:600;margin-bottom:8px;">Chat cannot be embedded here.</div>
                    <div style="color:#4b5563;font-size:14px;margin-bottom:12px;">Your chat service refused to connect inside an iframe. Open it in a new tab instead.</div>
                    <a href="${safeUrl}" target="_blank" rel="noopener" style="display:inline-block;padding:10px 12px;background:#0b5bd3;color:#fff;border-radius:10px;text-decoration:none;">Open chat</a>
                </div>
            `;
            const newClose = panel.querySelector('#chatWidgetClose');
            if (newClose) newClose.addEventListener('click', closeWidget);
        } catch (_) {
            // ignore
        }
    };

    const openWidget = () => {
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        launcher.classList.add('is-hidden');
        if (!frame.getAttribute('src')) {
            const normalizedBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const chatUrl = `${normalizedBaseUrl}/chat/new/`;
            let loaded = false;

            frame.addEventListener('load', () => {
                loaded = true;
            }, { once: true });

            frame.setAttribute('src', chatUrl);

            setTimeout(() => {
                if (!loaded) {
                    fallbackToNewTab(chatUrl);
                }
            }, 2500);
        }
    };

    const closeWidget = () => {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        launcher.classList.remove('is-hidden');
    };

    const setStatus = (text, color) => {
        if (!statusEl || !statusText) return;
        statusText.textContent = text || 'Connecting...';
        statusEl.classList.remove('is-online', 'is-offline');
        if (color === 'green') statusEl.classList.add('is-online');
        if (color === 'red') statusEl.classList.add('is-offline');
    };

    launcher.addEventListener('click', () => {
        if (panel.classList.contains('is-open')) {
            closeWidget();
        } else {
            openWidget();
        }
    });
    closeBtn.addEventListener('click', closeWidget);

    window.addEventListener('message', (event) => {
        const data = event.data || {};
        if (!data || data.type !== 'chatStatus') return;
        setStatus(data.text, data.color);
    });
})();
