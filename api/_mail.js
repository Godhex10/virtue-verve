/* ==========================================================
   Virtue & Verve — order emails (sent through Resend, from the server)

   Environment variables (Vercel > Project > Settings > Environment Variables):
     RESEND_API_KEY  re_... from resend.com > API Keys
     OWNER_EMAIL     where her "new order" alerts go
     MAIL_FROM       optional, e.g. Virtue & Verve <orders@her-domain.com>
                     (until her domain is verified in Resend, emails come from
                     onboarding@resend.dev and only reach the Resend account's own email)
   ========================================================== */
const { env, db } = require('./_lib');

const SITE = () => (process.env.SITE_URL || 'https://virtue-verve.vercel.app').replace(/\/$/, '');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

async function sendMail({ to, subject, html, replyTo }) {
  const res = await fetch('https://api.resend.com/emails', {
    method: 'POST',
    headers: { Authorization: `Bearer ${env('RESEND_API_KEY')}`, 'Content-Type': 'application/json' },
    body: JSON.stringify({
      from: process.env.MAIL_FROM || 'Virtue & Verve <onboarding@resend.dev>',
      to: [to], subject, html, ...(replyTo ? { reply_to: replyTo } : {})
    })
  });
  if (!res.ok) throw new Error(`Resend ${res.status}: ${await res.text()}`);
}

// ---------- Data ----------
async function getOrder(id) {
  const rows = await db(`orders?id=eq.${encodeURIComponent(id)}&select=*,items:order_items(*)`);
  return rows[0] || null;
}
async function getSettings() {
  const rows = await db('settings?select=key,value');
  const v = Object.fromEntries(rows.map(r => [r.key, r.value]));
  return { currency: v.currency || '₦', contact: v.contact || {}, bank: v.bank || '' };
}
// Each email goes out once: claim it first, so two calls at the same moment can't both send it
async function claim(order, column) {
  const rows = await db(`orders?id=eq.${order.id}&${column}=is.null`, { method: 'PATCH', body: JSON.stringify({ [column]: new Date().toISOString() }) });
  return rows.length > 0;
}
const unclaim = (order, column) => db(`orders?id=eq.${order.id}`, { method: 'PATCH', body: JSON.stringify({ [column]: null }) });

async function once(order, column, send) {
  if (!await claim(order, column)) return;
  try { await send(); } catch (err) { await unclaim(order, column).catch(() => {}); throw err; }
}

// ---------- Look ----------
const C = { teal: '#088178', deep: '#05544e', ink: '#1f2b2a', muted: '#6d7b79', gold: '#c9a96e', mist: '#f3f9f7', line: '#eadfca', cream: '#fffaf6' };
const serif = "Georgia, 'Times New Roman', serif", sans = "'Helvetica Neue', Arial, sans-serif";

function money(settings, n) { return settings.currency + Number(n || 0).toLocaleString('en-NG', { maximumFractionDigits: 2 }); }
const photoUrl = url => !url ? '' : /^https?:/.test(url) ? url : `${SITE()}/${url.replace(/^\//, '')}`;

function frame(title, body) {
  return `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>${esc(title)}</title></head>
<body style="margin:0;padding:0;background:${C.cream};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:${C.cream};padding:24px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid ${C.line};">
  <tr><td style="background:${C.teal};padding:22px 28px;text-align:center;font-family:${serif};font-size:26px;color:#ffffff;">
    Virtue <span style="color:${C.gold};font-style:italic;">&amp;</span> Verve
  </td></tr>
  <tr><td style="padding:28px;font-family:${sans};font-size:15px;line-height:1.6;color:${C.ink};">${body}</td></tr>
  <tr><td style="padding:18px 28px;background:${C.mist};font-family:${sans};font-size:12px;color:${C.muted};text-align:center;">
    Bags with virtue, worn with verve · <a href="${SITE()}" style="color:${C.teal};">${esc(SITE().replace(/^https?:\/\//, ''))}</a>
  </td></tr>
</table></td></tr></table></body></html>`;
}

function itemsTable(order, settings) {
  const rows = order.items.map(i => `
    <tr>
      <td width="64" style="padding:10px 0;border-bottom:1px solid ${C.line};">${i.image_url
        ? `<img src="${esc(photoUrl(i.image_url))}" width="56" height="56" alt="" style="display:block;border-radius:10px;object-fit:cover;">` : ''}</td>
      <td style="padding:10px 8px;border-bottom:1px solid ${C.line};">
        <div style="font-family:${serif};font-size:17px;">${esc(i.name)}</div>
        <div style="font-size:13px;color:${C.muted};">${esc(i.kind || '')} · ${esc(i.colour || '')} · Qty ${i.qty}</div>
      </td>
      <td align="right" style="padding:10px 0;border-bottom:1px solid ${C.line};white-space:nowrap;">${money(settings, i.price * i.qty)}</td>
    </tr>`).join('');
  const line = (label, value, strong) => `<tr><td colspan="2" style="padding:4px 0;color:${strong ? C.ink : C.muted};${strong ? 'font-weight:bold;' : ''}">${label}</td>
    <td align="right" style="padding:4px 0;${strong ? `font-weight:bold;color:${C.deep};font-size:17px;` : ''}">${value}</td></tr>`;
  return `<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0;">${rows}
    <tr><td colspan="3" style="height:8px;"></td></tr>
    ${line('Subtotal', money(settings, order.subtotal))}
    ${line(`${esc(order.delivery_label)}${order.delivery_zone ? ` · ${esc(order.delivery_zone)}` : ''}`, Number(order.delivery_price) ? money(settings, order.delivery_price) : 'Free')}
    ${line('Gift wrapping', 'Complimentary')}
    ${line('Total', money(settings, order.total), true)}
  </table>`;
}

const box = html => `<div style="margin:16px 0;padding:14px 16px;border-radius:12px;background:${C.mist};">${html}</div>`;
const address = order => order.address.map(esc).join('<br>');

// ---------- The customer's confirmation ----------
function receipt(order, settings) {
  const online = order.payment_method === 'Pay online';
  const how = online
    ? `<strong>Payment received</strong> — thank you. Paystack has sent you a separate payment receipt.`
    : order.payment_method === 'Bank transfer'
      ? settings.bank
        ? `<strong>Paying by bank transfer.</strong> Please send ${money(settings, order.total)} to:<br><span style="white-space:pre-line;">${esc(settings.bank)}</span><br>Use <strong>${esc(order.number)}</strong> as the reference. We’ll get your bag ready as soon as it arrives.`
        : `<strong>Paying by bank transfer.</strong> We’ll send our account details shortly. Use <strong>${esc(order.number)}</strong> as the reference.`
      : `<strong>Pay on delivery.</strong> Please have ${money(settings, order.total)} ready when your bag arrives.`;
  const contact = [settings.contact.email, settings.contact.whatsapp && `WhatsApp ${settings.contact.whatsapp}`].filter(Boolean).map(esc).join(' · ');

  return {
    subject: `Your Virtue & Verve order ${order.number}`,
    html: frame('Your order', `
      <p style="margin:0;font-family:${serif};font-style:italic;color:${C.gold};font-size:18px;">it’s on its way to being yours</p>
      <h1 style="margin:4px 0 8px;font-family:${serif};font-weight:normal;font-size:30px;">Thank you, ${esc(order.first_name)}</h1>
      <p style="margin:0;">Your order <strong>${esc(order.number)}</strong> is confirmed and we’re getting it wrapped with a bow.</p>
      ${box(how)}
      ${itemsTable(order, settings)}
      <p style="margin:0 0 4px;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:${C.gold};">Delivering to</p>
      <p style="margin:0 0 16px;">${esc(order.first_name)} ${esc(order.last_name)}<br>${address(order)}<br><span style="color:${C.muted};">${esc(order.delivery_label)} · ${esc(order.delivery_note || '')}</span></p>
      ${order.gift_note ? `<p style="margin:0 0 4px;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:${C.gold};">Your gift note</p>
        <p style="margin:0 0 16px;font-family:${serif};font-style:italic;font-size:17px;">“${esc(order.gift_note)}”</p>` : ''}
      <p style="margin:0;color:${C.muted};">Questions? Just reply to this email${contact ? ` or reach us at ${contact}` : ''}.</p>`)
  };
}

// ---------- Her alert ----------
function alert(order, settings, paidNow) {
  const status = paidNow ? 'Paid online' : order.payment_method === 'Pay online' ? 'Pay online — not paid yet' : order.payment_method;
  return {
    subject: paidNow ? `Paid: ${order.number} — ${money(settings, order.total)}` : `New order ${order.number} — ${money(settings, order.total)} (${status})`,
    html: frame('New order', `
      <h1 style="margin:0 0 6px;font-family:${serif};font-weight:normal;font-size:26px;">${paidNow ? 'Payment received' : 'A new order is in'}</h1>
      <p style="margin:0;">${paidNow ? `Paystack has confirmed payment for <strong>${esc(order.number)}</strong>. It’s ready to pack.`
        : `<strong>${esc(order.number)}</strong> from ${esc(order.first_name)} ${esc(order.last_name)}.`}</p>
      ${box(`<strong>Payment:</strong> ${esc(status)}<br><strong>Customer:</strong> ${esc(order.email)}${order.phone ? ` · ${esc(order.phone)}` : ''}<br>
        <strong>Deliver to:</strong> ${address(order)}<br><strong>Delivery:</strong> ${esc(order.delivery_label)}${order.delivery_zone ? ` · ${esc(order.delivery_zone)}` : ''}`)}
      ${itemsTable(order, settings)}
      ${order.gift_note ? box(`<strong>Gift note to handwrite:</strong><br><em>${esc(order.gift_note)}</em>`) : ''}
      <p style="margin:18px 0 0;text-align:center;">
        <a href="${SITE()}/admin/order.html?id=${encodeURIComponent(order.id)}" style="display:inline-block;padding:12px 26px;border-radius:999px;background:${C.teal};color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:2px;text-transform:uppercase;">Open in admin</a>
      </p>`)
  };
}

// ---------- What to send, and when ----------
// A new order: alert her, and confirm to the customer unless they still have to pay online
async function emailsForNewOrder(orderId) {
  const order = await getOrder(orderId);
  if (!order) return { found: false };
  const settings = await getSettings(), replyTo = settings.contact.email || undefined;
  await once(order, 'alert_sent_at', () => sendMail({ to: env('OWNER_EMAIL'), ...alert(order, settings, false), replyTo: order.email }));
  if (order.payment_method !== 'Pay online') {
    await once(order, 'receipt_sent_at', () => sendMail({ to: order.email, ...receipt(order, settings), replyTo }));
  }
  return { found: true };
}

// An online payment confirmed: confirm to the customer and tell her it's paid
async function emailsAfterPayment(orderId) {
  const order = await getOrder(orderId);
  if (!order) return;
  const settings = await getSettings(), replyTo = settings.contact.email || undefined;
  await once(order, 'receipt_sent_at', () => sendMail({ to: order.email, ...receipt(order, settings), replyTo }));
  await sendMail({ to: env('OWNER_EMAIL'), ...alert(order, settings, true), replyTo: order.email });
}

module.exports = { emailsForNewOrder, emailsAfterPayment, receipt, alert };
