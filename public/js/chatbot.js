/**
 * Chatbot Widget — Sistem Absensi
 * Requires: marked.js for Markdown rendering (loaded via CDN in footer.php)
 */
(function () {
    'use strict';

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    const BASEURL = window.__BASEURL__ || '';
    const CSRF    = window.__CSRF_TOKEN__ || '';

    function renderMarkdown(text) {
        if (window.marked) {
            try { return marked.parse(text); } catch (_) { /* fallthrough */ }
        }
        // Fallback: escape HTML then handle simple markdown
        return text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g,     '<em>$1</em>')
            .replace(/`(.+?)`/g,       '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }

    function timeNow() {
        return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    /* ------------------------------------------------------------------ */
    /*  State                                                               */
    /* ------------------------------------------------------------------ */

    const state = {
        isOpen:    false,
        isLoading: false,
        messages:  [],            // { role, content, time }
        isMock:    false,
    };

    /* ------------------------------------------------------------------ */
    /*  DOM refs (set after DOMContentLoaded)                              */
    /* ------------------------------------------------------------------ */

    let toggle, window_, messages, input, sendBtn, badge, clearBtn, closeBtn;

    /* ------------------------------------------------------------------ */
    /*  Render                                                              */
    /* ------------------------------------------------------------------ */

    function renderMessages() {
        const empty = document.getElementById('chatbot-empty');

        if (state.messages.length === 0) {
            if (empty) empty.style.display = 'block';
            messages.innerHTML = '';
            messages.appendChild(empty || document.createElement('div'));
            return;
        }
        if (empty) empty.style.display = 'none';

        // Re-render all (simple approach; good enough for ≤20 msgs)
        messages.innerHTML = '';
        state.messages.forEach(function (msg) {
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' + msg.role;
            if (msg.role === 'assistant') {
                bubble.innerHTML = renderMarkdown(msg.content);
            } else {
                // User bubble — plain text, XSS-safe
                const p = document.createElement('span');
                p.textContent = msg.content;
                bubble.appendChild(p);
            }
            const t = document.createElement('time');
            t.textContent = msg.time || '';
            bubble.appendChild(t);
            messages.appendChild(bubble);
        });

        messages.scrollTop = messages.scrollHeight;
    }

    function appendTyping() {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble typing';
        bubble.id = 'chatbot-typing';
        bubble.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        const t = document.getElementById('chatbot-typing');
        if (t) t.remove();
    }

    /* ------------------------------------------------------------------ */
    /*  Toggle open/close                                                   */
    /* ------------------------------------------------------------------ */

    function openChat() {
        state.isOpen = true;
        window_.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        badge.style.display = 'none';
        input.focus();
    }

    function closeChat() {
        state.isOpen = false;
        window_.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    /* ------------------------------------------------------------------ */
    /*  API calls                                                           */
    /* ------------------------------------------------------------------ */

    async function loadHistory() {
        try {
            const res  = await fetch(BASEURL + '/chatbot/history');
            if (!res.ok) return;
            const data = await res.json();
            if (data.messages && data.messages.length > 0) {
                state.messages = data.messages.map(function (m) {
                    return {
                        role:    m.role,
                        content: m.content,
                        time:    m.time ? m.time.substring(11, 16) : '',
                    };
                });
                renderMessages();
            }
        } catch (_) { /* silently fail */ }
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text || state.isLoading) return;

        // Add user bubble
        const userMsg = { role: 'user', content: text, time: timeNow() };
        state.messages.push(userMsg);
        renderMessages();
        input.value = '';
        input.style.height = 'auto';

        // Show typing
        state.isLoading = true;
        sendBtn.disabled = true;
        appendTyping();

        try {
            // Build history for context (last 10 exchanges = 20 messages)
            const historyForApi = state.messages.slice(-20).map(function (m) {
                return { role: m.role, content: m.content };
            });

            const res = await fetch(BASEURL + '/chatbot/send', {
                method:  'POST',
                headers: {
                    'Content-Type':   'application/json',
                    'X-CSRF-Token':   CSRF,
                },
                body: JSON.stringify({
                    message: text,
                    history: historyForApi.slice(0, -1), // Exclude the message we just added
                }),
            });

            removeTyping();
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || 'Server error ' + res.status);
            }

            state.isMock = data.isMock;
            const aiMsg = { role: 'assistant', content: data.answer, time: timeNow() };
            state.messages.push(aiMsg);
            renderMessages();

            // Show mock banner if needed
            const mockBanner = document.getElementById('mock-banner');
            if (mockBanner) mockBanner.style.display = state.isMock ? 'flex' : 'none';

        } catch (err) {
            removeTyping();
            const errMsg = {
                role:    'assistant',
                content: '⚠️ ' + (err.message || 'Terjadi kesalahan. Silakan coba lagi.'),
                time:    timeNow(),
            };
            state.messages.push(errMsg);
            renderMessages();
        } finally {
            state.isLoading  = false;
            sendBtn.disabled = false;
            input.focus();
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Centered Custom Modal Confirmation                                  */
    /* ------------------------------------------------------------------ */

    function showCenteredConfirm(options) {
        const title       = (options && options.title)       || 'Hapus Riwayat Percakapan?';
        const message     = (options && options.message)     || 'Hapus seluruh riwayat percakapan?';
        const confirmText = (options && options.confirmText) || 'Ya, Hapus';
        const cancelText  = (options && options.cancelText)  || 'Batal';

        return new Promise(function(resolve) {
            let overlay = document.getElementById('chatbot-confirm-modal');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'chatbot-confirm-modal';
                overlay.className = 'chatbot-modal-overlay';
                overlay.setAttribute('role', 'dialog');
                overlay.setAttribute('aria-modal', 'true');
                overlay.innerHTML = 
                    '<div class="chatbot-modal-card">' +
                        '<div class="chatbot-modal-icon warning">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />' +
                            '</svg>' +
                        '</div>' +
                        '<h4 class="chatbot-modal-title" id="chatbot-modal-title"></h4>' +
                        '<p class="chatbot-modal-desc" id="chatbot-modal-desc"></p>' +
                        '<div class="chatbot-modal-actions">' +
                            '<button type="button" class="chatbot-modal-btn cancel" id="chatbot-modal-cancel"></button>' +
                            '<button type="button" class="chatbot-modal-btn confirm-danger" id="chatbot-modal-confirm"></button>' +
                        '</div>' +
                    '</div>';
                document.body.appendChild(overlay);
            }

            const titleEl    = overlay.querySelector('#chatbot-modal-title');
            const descEl     = overlay.querySelector('#chatbot-modal-desc');
            const cancelBtn  = overlay.querySelector('#chatbot-modal-cancel');
            const confirmBtn = overlay.querySelector('#chatbot-modal-confirm');

            titleEl.textContent    = title;
            descEl.textContent     = message;
            cancelBtn.textContent  = cancelText;
            confirmBtn.textContent = confirmText;

            function cleanup(result) {
                overlay.classList.remove('active');
                document.removeEventListener('keydown', onKey);
                overlay.removeEventListener('click', onBackdrop);
                cancelBtn.onclick = null;
                confirmBtn.onclick = null;
                resolve(result);
            }

            function onKey(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    cleanup(false);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    cleanup(true);
                }
            }

            function onBackdrop(e) {
                if (e.target === overlay) {
                    cleanup(false);
                }
            }

            cancelBtn.onclick  = function() { cleanup(false); };
            confirmBtn.onclick = function() { cleanup(true); };
            overlay.addEventListener('click', onBackdrop);
            document.addEventListener('keydown', onKey);

            requestAnimationFrame(function() {
                overlay.classList.add('active');
                confirmBtn.focus();
            });
        });
    }

    // Expose globally for convenience
    window.showCenteredConfirm = showCenteredConfirm;

    async function clearHistory() {
        const ok = await showCenteredConfirm({
            title: 'Hapus Riwayat Percakapan?',
            message: 'Hapus seluruh riwayat percakapan?',
            confirmText: 'Ya, Hapus',
            cancelText: 'Batal'
        });
        if (!ok) return;

        try {
            await fetch(BASEURL + '/chatbot/clear', {
                method:  'POST',
                headers: { 'X-CSRF-Token': CSRF },
            });
        } catch (_) { /* ignore */ }
        state.messages = [];
        renderMessages();
    }

    /* ------------------------------------------------------------------ */
    /*  Quick suggestions                                                   */
    /* ------------------------------------------------------------------ */

    function getRoleSuggestions() {
        const role = (window.__USER_ROLE__ || 'siswa').toLowerCase();
        if (role === 'guru') {
            return [
                'Status absensi saya hari ini?',
                'Berapa rekap kehadiran saya?',
                'Jadwal mengajar hari ini?',
                'Riwayat absensi saya?',
                'Rekap absensi kelas binaan?',
            ];
        }
        if (role === 'siswa') {
            return [
                'Status absensi saya hari ini?',
                'Berapa rekap kehadiran saya?',
                'Jadwal pelajaran hari ini?',
                'Riwayat absensi saya?',
                'Siapa wali kelas saya?',
            ];
        }
        return [
            'Rekapitulasi kehadiran hari ini?',
            'Siapa siswa yang alpa hari ini?',
            'Siapa guru yang belum hadir hari ini?',
            'Total siswa dan guru terdaftar?',
        ];
    }

    function buildSuggestions() {
        const container = document.getElementById('chatbot-suggestions');
        if (!container) return;
        container.innerHTML = '';
        const list = getRoleSuggestions();
        list.forEach(function (text) {
            const btn = document.createElement('button');
            btn.className   = 'suggestion-chip';
            btn.textContent = text;
            btn.addEventListener('click', function () {
                input.value = text;
                sendMessage();
            });
            container.appendChild(btn);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Init                                                                */
    /* ------------------------------------------------------------------ */

    document.addEventListener('DOMContentLoaded', function () {
        toggle   = document.getElementById('chatbot-toggle');
        window_  = document.getElementById('chatbot-window');
        messages = document.getElementById('chatbot-messages');
        input    = document.getElementById('chatbot-input');
        sendBtn  = document.getElementById('chatbot-send');
        badge    = document.getElementById('chatbot-badge');
        clearBtn = document.getElementById('chatbot-clear');
        closeBtn = document.getElementById('chatbot-close');

        // Guard: only run if widget exists (logged-in pages)
        if (!toggle || !window_) return;

        buildSuggestions();
        loadHistory();

        // Toggle open/close
        toggle.addEventListener('click', function () {
            state.isOpen ? closeChat() : openChat();
        });
        if (closeBtn) closeBtn.addEventListener('click', closeChat);

        // Send on button click
        sendBtn.addEventListener('click', sendMessage);

        // Send on Enter (Shift+Enter = new line)
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 90) + 'px';
        });

        // Clear history
        if (clearBtn) clearBtn.addEventListener('click', clearHistory);

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && state.isOpen) closeChat();
        });
    });

})();
