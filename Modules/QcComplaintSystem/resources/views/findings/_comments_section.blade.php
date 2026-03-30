<style>
    .comments-section {
        border: 1px solid #d7e4dd;
        border-radius: 14px;
        background: #ffffff;
        overflow: hidden;
    }

    .comments-section-head {
        padding: 12px 14px;
        font-weight: 800;
        border-bottom: 1px solid #dbe7e1;
        background: linear-gradient(180deg, #f8fcfa, #ffffff);
        font-size: 13px;
    }

    .comments-section-body {
        padding: 14px;
    }

    .comment-form {
        margin-bottom: 14px;
        border: 1px solid #dbe7e1;
        border-radius: 10px;
        padding: 10px;
        background: #f8fcfa;
    }

    .comment-form textarea {
        min-height: 80px;
        font-family: inherit;
        margin-bottom: 8px;
    }

    .comment-form-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .comments-list {
        display: grid;
        gap: 12px;
    }

    .comment-item {
        padding: 10px;
        border: 1px solid #dbe7e1;
        border-radius: 10px;
        background: #ffffff;
        transition: all 0.2s;
    }

    .comment-item:hover {
        border-color: #0f766e;
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
    }

    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }

    .comment-author {
        font-weight: 700;
        color: #1e293b;
        font-size: 13px;
    }

    .comment-time {
        font-size: 11px;
        color: #64748b;
    }

    .comment-content {
        color: #334155;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 8px;
        word-wrap: break-word;
        white-space: pre-wrap;
    }

    .comment-actions {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .comment-btn {
        font-size: 11px;
        padding: 4px 8px;
        border: 1px solid #dbe7e1;
        border-radius: 6px;
        background: #f8fcfa;
        cursor: pointer;
        transition: all 0.2s;
        color: #0f766e;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .comment-btn:hover {
        background: #e2efed;
        border-color: #0f766e;
    }

    .comment-btn.delete {
        color: #b91c1c;
    }

    .comment-btn.delete:hover {
        background: #fee2e2;
        border-color: #b91c1c;
    }

    .reply-form {
        margin-top: 10px;
        padding: 8px;
        background: #f1fdf9;
        border-radius: 8px;
        border: 1px solid #dbeae4;
        display: none;
    }

    .reply-form.active {
        display: block;
    }

    .reply-form textarea {
        min-height: 60px;
        margin-bottom: 6px;
        font-family: inherit;
    }

    .reply-list {
        margin-top: 10px;
        padding-left: 14px;
        border-left: 2px solid #0f766e;
        display: grid;
        gap: 10px;
    }

    .reply-item {
        padding: 8px;
        background: #f1fdf9;
        border-radius: 8px;
        border: 1px solid #dbeae4;
    }

    .no-comments {
        text-align: center;
        padding: 20px;
        color: #64748b;
        font-size: 13px;
    }

    .comment-status-closed {
        background: #fef2f2;
        border-color: #fca5a5;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 12px;
        font-size: 12px;
        color: #991b1b;
        font-weight: 700;
    }

    .loading {
        text-align: center;
        padding: 20px;
        color: #64748b;
    }
</style>

<div class="comments-section">
    <div class="comments-section-head">
        <i class="fas fa-comments"></i> Komentar & Diskusi
    </div>
    <div class="comments-section-body">
        @if($finding->status === 'closed')
            <div class="comment-status-closed">
                <i class="fas fa-lock"></i> Temuan ini sudah ditutup. Anda dapat melihat riwayat komentar tetapi tidak dapat menambahkan komentar baru.
            </div>
        @endif

        @if($finding->status !== 'closed')
            <div class="comment-form">
                <textarea id="new-comment-input" class="input" placeholder="Tulis komentar atau pertanyaan..." maxlength="5000"></textarea>
                <div class="comment-form-actions">
                    <button id="btn-cancel-comment" class="btn" style="display:none;">Batal</button>
                    <button id="btn-submit-comment" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Komentar</button>
                </div>
                <div id="comment-error-msg" style="color:#b91c1c; font-size:12px; margin-top:6px; display:none;"></div>
            </div>
        @endif

        <div id="comments-container" class="comments-list">
            <div class="loading"><i class="fas fa-spinner fa-spin"></i> Memuat komentar...</div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const findingId = {{ $finding->id }};
            const isClosed = {{ $finding->status === 'closed' ? 'true' : 'false' }};
            const storeUrl = `{{ route('qc.findings.comments.store', $finding) }}`;
            const commentsUrl = `{{ route('qc.findings.comments.show', $finding) }}`;

            let replyingToCommentId = null;
            let currentReplyContainer = null;

            // Load comments on page load
            loadComments();

            // Handle submit new comment
            const btnSubmit = document.getElementById('btn-submit-comment');
            const btnCancel = document.getElementById('btn-cancel-comment');
            const inputComment = document.getElementById('new-comment-input');

            if (btnSubmit) {
                btnSubmit.addEventListener('click', function() {
                    if (isClosed) {
                        alert('Temuan ini sudah ditutup. Anda tidak dapat menambahkan komentar.');
                        return;
                    }

                    const content = inputComment.value.trim();
                    if (!content) {
                        showCommentError('Komentar tidak boleh kosong');
                        return;
                    }

                    submitComment(content, null);
                });
            }

            if (btnCancel) {
                btnCancel.addEventListener('click', function() {
                    cancelReply();
                });
            }

            function loadComments() {
                fetch(commentsUrl, {
                    headers: {
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && Array.isArray(data.comments)) {
                        renderComments(data.comments);
                    } else {
                        showNoComments();
                    }
                })
                .catch(e => {
                    console.error('Error loading comments:', e);
                    showNoComments();
                });
            }

            function renderComments(comments) {
                const container = document.getElementById('comments-container');
                container.innerHTML = '';

                if (comments.length === 0) {
                    showNoComments();
                    return;
                }

                comments.forEach(comment => {
                    const commentEl = createCommentElement(comment, false);
                    container.appendChild(commentEl);
                });
            }

            function createCommentElement(comment, isReply = false) {
                const wrapper = document.createElement('div');
                if (!isReply) {
                    wrapper.className = 'comment-item';
                } else {
                    wrapper.className = 'reply-item';
                }

                const header = document.createElement('div');
                header.className = 'comment-header';
                header.innerHTML = `
                    <div>
                        <div class="comment-author">${escapeHtml(comment.user.name)}</div>
                        <div class="comment-time">${comment.created_at_human}</div>
                    </div>
                `;

                const content = document.createElement('div');
                content.className = 'comment-content';
                content.innerText = comment.content;

                const actions = document.createElement('div');
                actions.className = 'comment-actions';

                if (!isClosed) {
                    const replyBtn = document.createElement('button');
                    replyBtn.className = 'comment-btn';
                    replyBtn.type = 'button';
                    replyBtn.innerHTML = '<i class="fas fa-reply"></i> Balas';
                    replyBtn.onclick = () => showReplyForm(comment.id, replyBtn.parentElement.parentElement);
                    actions.appendChild(replyBtn);
                }

                // Delete button removed as per request to keep all chat records


                wrapper.appendChild(header);
                wrapper.appendChild(content);
                wrapper.appendChild(actions);

                // Add replies if any
                if (comment.replies && comment.replies.length > 0) {
                    const replyList = document.createElement('div');
                    replyList.className = 'reply-list';
                    comment.replies.forEach(reply => {
                        const replyEl = createCommentElement(reply, true);
                        replyList.appendChild(replyEl);
                    });
                    wrapper.appendChild(replyList);
                }

                return wrapper;
            }

            function showReplyForm(parentId, commentElement) {
                // If already showing reply form for this comment, close it
                if (replyingToCommentId === parentId) {
                    cancelReply();
                    return;
                }

                // Close previous reply form
                if (currentReplyContainer) {
                    const existingForm = currentReplyContainer.querySelector('.reply-form');
                    if (existingForm) {
                        existingForm.remove();
                    }
                }

                replyingToCommentId = parentId;
                currentReplyContainer = commentElement;

                const replyForm = document.createElement('div');
                replyForm.className = 'reply-form active';
                replyForm.innerHTML = `
                    <textarea class="input reply-textarea" placeholder="Tulis balasan..." maxlength="5000"></textarea>
                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <button class="btn btn-sm" onclick="event.stopPropagation();">Batal</button>
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Kirim Balasan</button>
                    </div>
                `;

                const textarea = replyForm.querySelector('.reply-textarea');
                const cancelBtn = replyForm.querySelectorAll('button')[0];
                const submitBtn = replyForm.querySelectorAll('button')[1];

                cancelBtn.addEventListener('click', cancelReply);
                submitBtn.addEventListener('click', () => {
                    const content = textarea.value.trim();
                    if (!content) {
                        alert('Balasan tidak boleh kosong');
                        return;
                    }
                    submitComment(content, parentId);
                });

                commentElement.appendChild(replyForm);
                textarea.focus();
            }

            function cancelReply() {
                if (currentReplyContainer) {
                    const form = currentReplyContainer.querySelector('.reply-form');
                    if (form) form.remove();
                }
                replyingToCommentId = null;
                currentReplyContainer = null;

                const mainInput = document.getElementById('new-comment-input');
                if (mainInput) mainInput.focus();
            }

            function submitComment(content, parentId) {
                const formData = new FormData();
                formData.append('content', content);
                if (parentId) {
                    formData.append('parent_comment_id', parentId);
                }
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content);

                const submitBtn = document.getElementById('btn-submit-comment');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                }

                fetch(storeUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Reset form and reload comments
                        if (!parentId && document.getElementById('new-comment-input')) {
                            document.getElementById('new-comment-input').value = '';
                        }
                        cancelReply();
                        loadComments();
                        showCommentError('', false);
                    } else {
                        showCommentError(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(e => {
                    console.error('Error:', e);
                    showCommentError('Terjadi kesalahan saat mengirim komentar');
                })
                .finally(() => {
                    const submitBtn = document.getElementById('btn-submit-comment');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Komentar';
                    }
                });
            }

            function deleteComment(commentId) {
                if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) return;

                const deleteUrl = `{{ route('qc.findings.comments.destroy', [$finding, 'COMMENT_ID']) }}`.replace('COMMENT_ID', commentId);

                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loadComments();
                    } else {
                        alert('Gagal menghapus komentar');
                    }
                })
                .catch(e => {
                    console.error('Error:', e);
                    alert('Terjadi kesalahan saat menghapus komentar');
                });
            }

            function showNoComments() {
                const container = document.getElementById('comments-container');
                container.innerHTML = '<div class="no-comments">Belum ada komentar. Jadilah yang pertama berkomentar!</div>';
            }

            function showCommentError(message, show = true) {
                const errorDiv = document.getElementById('comment-error-msg');
                if (errorDiv) {
                    if (show && message) {
                        errorDiv.textContent = message;
                        errorDiv.style.display = 'block';
                    } else {
                        errorDiv.style.display = 'none';
                    }
                }
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }
        });
    </script>
@endpush
