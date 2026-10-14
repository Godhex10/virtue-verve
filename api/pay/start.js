/* ==========================================================
   POST /api/pay/start   { number: 'VV-123456', email: 'her@example.com' }
   Asks Paystack for a payment page for an order and returns its address.
   The amount always comes from the database, never from the browser.
   ========================================================== */
const { paystack, findOrder, kobo, send, readJson } = require('../_lib');

module.exports = async (req, res) => {
  if (req.method !== 'POST') return send(res, 405, { error: 'Use POST' });
  try {
    const { number, email } = await readJson(req);
    const order = number ? await findOrder(String(number)) : null;

    // The email must match too, so nobody can open a payment page for someone else's order
    if (!order || !email || order.email.toLowerCase() !== String(email).trim().toLowerCase()) {
      return send(res, 404, { error: 'We couldn’t find that order.' });
    }
    if (order.paid_at) return send(res, 409, { error: 'This order is already paid.' });
    if (order.status === 'cancelled') return send(res, 409, { error: 'This order was cancelled.' });

    const site = (process.env.SITE_URL || `https://${req.headers['x-forwarded-host'] || req.headers.host}`).replace(/\/$/, '');
    const tx = await paystack('/transaction/initialize', {
      method: 'POST',
      body: JSON.stringify({
        email: order.email,
        amount: kobo(order.total),
        currency: 'NGN',
        reference: `${order.number}-${Date.now().toString(36)}`, // unique per attempt, so she can try again
        callback_url: `${site}/order.html`,
        metadata: {
          order_number: order.number,
          custom_fields: [{ display_name: 'Order', variable_name: 'order_number', value: order.number }]
        }
      })
    });
    send(res, 200, { url: tx.authorization_url, reference: tx.reference });
  } catch (err) {
    console.error('pay/start', err);
    send(res, 500, { error: 'Payment couldn’t be started. Please try again in a moment.' });
  }
};
