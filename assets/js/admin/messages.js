/* ==========================================================
   Virtue & Verve — admin messages (from the shop's contact form)
   ========================================================== */
(async () => {
  let messages = (await api.list('messages')).sort((a, b) => b.created_at.localeCompare(a.created_at));
  let openId = new URLSearchParams(location.search).get('id') || (messages[0] && messages[0].id);

  setHead('Messages');

  async function markRead(m, read) {
    if (m.is_read === read) return;
    m.is_read = read;
    await api.save('messages', m);
    const n = messages.filter(x => !x.is_read).length, badge = $('#count-messages');
    if (badge) { badge.textContent = n; badge.hidden = !n; }
  }

  async function render() {
    const open = messages.find(m => m.id === openId);
    if (open) await markRead(open, true);
    const unread = messages.filter(m => !m.is_read).length;
    $('#pageSub').textContent = messages.length ? `${messages.length} messages, ${unread} unread` : '';

    if (!messages.length) {
      app.innerHTML = `<div class="card">${emptyState('envelope-open', 'No messages', 'Notes sent from the shop’s contact page land here.')}</div>`;
      return;
    }
    app.innerHTML = `
      <div class="inbox">
        <div class="msg-list">${messages.map(m => `
          <button class="msg-item${m.id === openId ? ' active' : ''}${m.is_read ? '' : ' unread'}" data-id="${m.id}">
            <strong>${esc(m.name)}<span>${ago(m.created_at)}</span></strong>
            <small>${esc(m.topic || 'Message')} — ${esc(m.message)}</small>
          </button>`).join('')}</div>
        ${open ? `<div class="card">
          <div class="card-head">
            <div><h2>${esc(open.topic || 'Message')}</h2><small>${fmtDate(open.created_at, true)}</small></div>
            <div class="row-actions">
              <button class="icon-btn" id="unread" title="Mark as unread" aria-label="Mark as unread">${icon('envelope')}</button>
              <button class="icon-btn danger" id="delete" title="Delete" aria-label="Delete message">${icon('trash-can', 'regular')}</button>
            </div>
          </div>
          <dl class="info">
            <dt>From</dt><dd>${esc(open.name)}</dd>
            <dt>Email</dt><dd><a class="link" href="mailto:${esc(open.email)}">${esc(open.email)}</a></dd>
            ${open.order_number ? `<dt>Order</dt><dd>${esc(open.order_number)}</dd>` : ''}
          </dl>
          <p class="msg-body">${esc(open.message)}</p>
          <a class="btn btn-primary" href="mailto:${esc(open.email)}?subject=${encodeURIComponent('Re: ' + (open.topic || 'Your message to Virtue & Verve'))}">${icon('reply')}Reply by email</a>
        </div>` : `<div class="card">${emptyState('envelope-open', 'Choose a message', 'Pick one from the list to read it.')}</div>`}
      </div>`;
  }

  app.addEventListener('click', async e => {
    const item = e.target.closest('.msg-item');
    const open = messages.find(m => m.id === openId);
    if (item) { openId = item.dataset.id; render(); }
    else if (e.target.closest('#unread') && open) { await markRead(open, false); openId = null; render(); toast('Marked as unread', 'envelope'); }
    else if (e.target.closest('#delete') && open) {
      if (!await confirmBox('Delete this message?', `From ${open.name}. This can’t be undone.`)) return;
      await api.remove('messages', open.id);
      messages = messages.filter(m => m !== open);
      openId = messages[0] ? messages[0].id : null;
      render();
      toast('Message deleted', 'trash-can');
    }
  });

  render();
})();
