        <?php if(isset($_SESSION['user_id'])): ?>
            </div> <!-- End container-fluid -->
        </main> <!-- End main-content -->
        <?php else: ?>
        </main> <!-- End container -->
        <?php endif; ?>

    <?php if(isset($_SESSION['user_id'])): ?>
    <!-- ================================================
         AI CHATBOT WIDGET (Hanya tampil pada sesi yang sudah login)
         ================================================ -->

    <!-- Toggle Button -->
    <button id="chatbot-toggle" aria-label="Buka AI Chatbot" aria-expanded="false" title="AI Helpdesk">
        <span id="chatbot-badge"></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
        </svg>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" role="dialog" aria-label="AI Helpdesk Chatbot">

        <!-- Header -->
        <div id="chatbot-header">
            <div class="avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M6 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5M3 8.062C3 6.76 4.235 5.765 5.53 5.886a26.6 26.6 0 0 0 4.94 0C11.765 5.765 13 6.76 13 8.062v1.157a.93.93 0 0 1-.765.935c-.845.147-2.34.346-4.235.346s-3.39-.2-4.235-.346A.93.93 0 0 1 3 9.219zm4.542-.827a.25.25 0 0 0-.217.068l-.92.9a24.8 24.8 0 0 1-1.871-.183.25.25 0 0 0-.068.495c.55.076 1.232.149 2.02.193a.25.25 0 0 0 .189-.071l.754-.736.847 1.71a.25.25 0 0 0 .404.062l.932-.97a25 25 0 0 0 1.922-.188.25.25 0 0 0-.068-.495c-.538.074-1.207.145-1.98.189a.25.25 0 0 0-.166.076l-.754.785-.842-1.7a.25.25 0 0 0-.182-.134"/>
                    <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8"/>
                </svg>
            </div>
            <div class="info">
                <strong>AI Helpdesk</strong>
                <small>Asisten SMK NU WARGABINANGUN &bull; <?= e($_SESSION['username'] ?? '') ?></small>
            </div>
            <div class="actions">
                <button id="chatbot-clear" title="Hapus riwayat chat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                    </svg>
                </button>
                <button id="chatbot-close" title="Tutup chatbot">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mock Mode Banner -->
        <div id="mock-banner" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            Mode Demo — Konfigurasi AI_API_KEY di .env untuk aktifkan AI sesungguhnya.
        </div>

        <!-- Messages -->
        <div id="chatbot-messages" role="log" aria-live="polite">
            <div id="chatbot-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg>
                <p><strong><?= e($_SESSION['username'] ?? 'Pengguna') ?></strong><br>Asisten SMK NU WARGABINANGUN</p>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div id="chatbot-suggestions"></div>

        <!-- Input -->
        <div id="chatbot-input-area">
            <textarea id="chatbot-input" placeholder="Ketik pertanyaan Anda..." rows="1" maxlength="1000" aria-label="Pesan chatbot"></textarea>
            <button id="chatbot-send" title="Kirim pesan" aria-label="Kirim pesan">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                </svg>
            </button>
        </div>

    </div> <!-- #chatbot-window -->

    <!-- Pass PHP vars to JS securely -->
    <script>
        window.__BASEURL__   = '<?= e(BASEURL) ?>';
        window.__CSRF_TOKEN__ = '<?= e(Csrf::getToken()) ?>';
        window.__USER_ROLE__  = '<?= e($_SESSION['role'] ?? 'siswa') ?>';
    </script>

    <!-- Marked.js for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>
    <!-- Chatbot CSS & JS -->
    <link rel="stylesheet" href="<?= e(BASEURL) ?>/css/chatbot.css?v=<?= time() ?>">
    <script src="<?= e(BASEURL) ?>/js/chatbot.js?v=<?= time() ?>"></script>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(BASEURL); ?>/js/script.js?v=<?= time(); ?>"></script>
</body>
</html>
