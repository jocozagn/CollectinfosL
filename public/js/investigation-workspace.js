(function () {
    function initWorkspaceChat() {
        const config = window.CollectinfosWorkspace;
        if (!config || config.section !== 'discussion') {
            return;
        }

        const chat = document.getElementById('workspace-chat');
        const messagesEl = document.getElementById('workspace-chat-messages');
        const form = document.getElementById('workspace-chat-form');
        const emptyEl = document.getElementById('workspace-chat-empty');

        if (!chat || !messagesEl || !form) {
            return;
        }

        let lastId = parseInt(chat.dataset.lastId || '0', 10);
        let eventSource = null;
        let pollTimer = null;
        let reconnectTimer = null;
        let streamEnabled = typeof EventSource !== 'undefined';
        let sending = false;

        function csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                return meta.getAttribute('content') || '';
            }

            const tokenInput = form.querySelector('input[name="_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        function scrollToBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function messageExists(id) {
            return Boolean(messagesEl.querySelector('[data-message-id="' + id + '"]'));
        }

        function appendMessage(message) {
            if (!message || !message.id) {
                return;
            }

            if (messageExists(message.id)) {
                lastId = Math.max(lastId, message.id);
                return;
            }

            lastId = Math.max(lastId, message.id);

            const emptyNode = document.getElementById('workspace-chat-empty');
            if (emptyNode) {
                emptyNode.remove();
            }

            const article = document.createElement('article');
            article.className = 'workspace-message' + (message.is_mine ? ' workspace-message--mine' : '');
            article.classList.add('workspace-message--new');
            article.dataset.messageId = String(message.id);

            article.innerHTML =
                '<header>' +
                '<strong>' + escapeHtml(message.user.name) + '</strong>' +
                '<time>' + escapeHtml(message.created_at) + '</time>' +
                '</header>' +
                '<p>' + escapeHtml(message.body).replace(/\n/g, '<br>') + '</p>';

            messagesEl.appendChild(article);
            scrollToBottom();

            window.setTimeout(function () {
                article.classList.remove('workspace-message--new');
            }, 400);
        }

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        function fetchMessages() {
            const url = config.messagesUrl + (lastId > 0 ? '?after=' + lastId : '');

            fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('fetch failed');
                    }

                    return response.json();
                })
                .then(function (data) {
                    (data.messages || []).forEach(appendMessage);
                })
                .catch(function () {});
        }

        function startPolling() {
            if (pollTimer) {
                return;
            }

            fetchMessages();
            pollTimer = window.setInterval(fetchMessages, 1500);
        }

        function closeStream() {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }

            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
        }

        function connectStream() {
            if (!streamEnabled || document.hidden || !config.streamUrl) {
                startPolling();
                return;
            }

            closeStream();
            stopPolling();

            const url = config.streamUrl + '?after=' + lastId;
            eventSource = new EventSource(url);

            eventSource.onmessage = function (event) {
                try {
                    const data = JSON.parse(event.data);

                    if (data.type === 'message' && data.message) {
                        appendMessage(data.message);
                    } else if (data.type === 'reconnect') {
                        closeStream();
                        connectStream();
                    }
                } catch (error) {}
            };

            eventSource.onerror = function () {
                closeStream();
                streamEnabled = false;
                startPolling();
            };
        }

        function setSendingState(isSending) {
            sending = isSending;
            const submitBtn = form.querySelector('button[type="submit"]');
            const textarea = form.querySelector('textarea[name="body"]');

            if (submitBtn) {
                submitBtn.disabled = isSending;
            }
        }

        function sendMessage() {
            if (sending) {
                return;
            }

            const textarea = form.querySelector('textarea[name="body"]');
            const body = textarea ? textarea.value.trim() : '';

            if (!body) {
                return;
            }

            const formData = new FormData(form);
            formData.set('body', body);

            setSendingState(true);

            fetch(config.storeMessageUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('send failed');
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (data.message) {
                        appendMessage(data.message);
                    }

                    if (textarea) {
                        textarea.value = '';
                    }
                })
                .catch(function () {
                    form.removeEventListener('submit', onSubmit);
                    form.submit();
                })
                .finally(function () {
                    setSendingState(false);
                });
        }

        function onSubmit(event) {
            event.preventDefault();
            sendMessage();
        }

        form.addEventListener('submit', onSubmit);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                closeStream();
                stopPolling();
                return;
            }

            connectStream();
        });

        window.addEventListener('beforeunload', closeStream);

        scrollToBottom();
        connectStream();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWorkspaceChat);
    } else {
        initWorkspaceChat();
    }
})();
