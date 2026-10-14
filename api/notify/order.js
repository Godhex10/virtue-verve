/* ==========================================================
   POST /api/notify/order   { order_id: '<uuid>' }
   The database calls this by itself whenever a new order is created
   (see supabase/003_order_emails.sql). It sends her alert and, unless the
   customer still has to pay online, the customer's confirmation.

   It needs no password: order ids are long random codes nobody can guess,
   and each email is only ever sent once per order.
   ========================================================== */
const { send, readJson } = require('../_lib');
const { emailsForNewOrder } = require('../_mail');

module.exports = async (req, res) => {
  if (req.method !== 'POST') return send(res, 405, { error: 'Use POST' });
  try {
    const { order_id: id } = await readJson(req);
    if (!/^[0-9a-f-]{36}$/i.test(String(id || ''))) return send(res, 400, { error: 'Bad order id' });
    const result = await emailsForNewOrder(id);
    send(res, result.found ? 200 : 404, result);
  } catch (err) {
    console.error('notify/order', err);
    send(res, 500, { error: 'Emails failed' });
  }
};
