/* ==========================================================
   GET /api/pay/verify?reference=VV-123456-abc
   Called by the confirmation page when Paystack sends the customer back.
   Checks the payment with Paystack and marks the order paid if it went through.
   ========================================================== */
const { paystack, findOrder, markPaid, orderNumberOf, send } = require('../_lib');
const { emailsAfterPayment } = require('../_mail');

module.exports = async (req, res) => {
  try {
    const reference = new URL(req.url, 'http://localhost').searchParams.get('reference');
    if (!reference) return send(res, 400, { error: 'No payment reference.' });

    const tx = await paystack(`/transaction/verify/${encodeURIComponent(reference)}`);
    const order = await findOrder(orderNumberOf(tx) || '');
    if (!order) return send(res, 404, { error: 'We couldn’t find the order for this payment.' });

    const result = await markPaid(order, tx);
    if (result.now) await emailsAfterPayment(order.id).catch(err => console.error('payment emails', err));
    send(res, 200, { paid: result.paid, status: tx.status, number: order.number });
  } catch (err) {
    console.error('pay/verify', err);
    send(res, 500, { error: 'We couldn’t confirm the payment yet. If money left your account, we’ll match it up — please contact us.' });
  }
};
