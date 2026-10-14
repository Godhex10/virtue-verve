/* ==========================================================
   POST /api/pay/webhook
   Paystack calls this itself whenever a payment succeeds, so an order is
   marked paid even if the customer closes the browser before coming back.

   In the Paystack dashboard, set Settings > API Keys & Webhooks > Webhook URL to:
     https://<her-domain>/api/pay/webhook
   ========================================================== */
const crypto = require('crypto');
const { env, paystack, findOrder, markPaid, orderNumberOf, send, readRaw } = require('../_lib');
const { emailsAfterPayment } = require('../_mail');

module.exports = async (req, res) => {
  if (req.method !== 'POST') return send(res, 405, { error: 'Use POST' });
  try {
    // Only accept messages signed with her secret key (i.e. really from Paystack)
    const raw = await readRaw(req);
    const expected = crypto.createHmac('sha512', env('PAYSTACK_SECRET_KEY')).update(raw).digest('hex');
    const given = String(req.headers['x-paystack-signature'] || '');
    if (given.length !== expected.length || !crypto.timingSafeEqual(Buffer.from(given), Buffer.from(expected))) {
      return send(res, 401, { error: 'Invalid signature' });
    }

    const event = JSON.parse(raw);
    if (event.event === 'charge.success') {
      // Double-check with Paystack rather than trusting the message alone
      const tx = await paystack(`/transaction/verify/${encodeURIComponent(event.data.reference)}`);
      const order = await findOrder(orderNumberOf(tx) || '');
      if (order) {
        const result = await markPaid(order, tx);
        if (result.now) await emailsAfterPayment(order.id).catch(err => console.error('payment emails', err));
      } else console.error('pay/webhook: no order for', event.data.reference);
    }
    send(res, 200, { received: true });
  } catch (err) {
    console.error('pay/webhook', err);
    send(res, 500, { error: 'Webhook failed' }); // Paystack retries later
  }
};
