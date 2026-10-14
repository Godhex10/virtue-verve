/* ==========================================================
   Virtue & Verve — shared helpers for the payment functions
   These files run on Vercel's servers, never in the browser, so they can
   hold secret keys. Files starting with "_" are not public addresses.

   Environment variables (Vercel > Project > Settings > Environment Variables):
     PAYSTACK_SECRET_KEY        sk_test_... while testing, sk_live_... at launch
     SUPABASE_URL               https://gbevnyfaicwdyvnjmqdk.supabase.co
     SUPABASE_SERVICE_ROLE_KEY  the secret "service_role" key (server only)
     SITE_URL                   optional, e.g. https://virtueandverve.com
   ========================================================== */

const PAYSTACK = 'https://api.paystack.co';

function env(name) {
  const value = process.env[name];
  if (!value) throw new Error(`Missing environment variable ${name}`);
  return value;
}

async function paystack(path, options = {}) {
  const res = await fetch(PAYSTACK + path, {
    ...options,
    headers: { Authorization: `Bearer ${env('PAYSTACK_SECRET_KEY')}`, 'Content-Type': 'application/json' }
  });
  const body = await res.json().catch(() => ({}));
  if (!res.ok || body.status === false) throw new Error(body.message || `Paystack error ${res.status}`);
  return body.data;
}

// Talks to the database with the server key, which is allowed to read and update orders
async function db(path, options = {}) {
  const key = env('SUPABASE_SERVICE_ROLE_KEY');
  const res = await fetch(`${env('SUPABASE_URL')}/rest/v1/${path}`, {
    ...options,
    headers: { apikey: key, Authorization: `Bearer ${key}`, 'Content-Type': 'application/json', Prefer: 'return=representation' }
  });
  const body = await res.json().catch(() => null);
  if (!res.ok) throw new Error((body && body.message) || `Database error ${res.status}`);
  return body;
}

async function findOrder(number) {
  const rows = await db(`orders?number=eq.${encodeURIComponent(number)}&select=id,number,email,total,status,paid_at,payment_reference`);
  return rows[0] || null;
}

// Paystack works in kobo: ₦85,000 is 8500000
const kobo = naira => Math.round(Number(naira) * 100);

// Marks an order paid, but only once Paystack confirms the full amount arrived in naira.
// Safe to call more than once: returns { paid, now }, where now is true only for the call that marked it.
async function markPaid(order, tx) {
  if (tx.status !== 'success') return { paid: false, now: false };
  if (tx.currency !== 'NGN' || Number(tx.amount) !== kobo(order.total)) {
    throw new Error(`Paid amount ${tx.amount} ${tx.currency} does not match order ${order.number} (${kobo(order.total)} NGN)`);
  }
  if (order.paid_at) return { paid: true, now: false };
  const rows = await db(`orders?id=eq.${order.id}&paid_at=is.null`, {
    method: 'PATCH',
    body: JSON.stringify({
      payment_reference: tx.reference,
      paid_at: tx.paid_at || tx.paidAt || new Date().toISOString(),
      ...(order.status === 'new' ? { status: 'paid' } : {})
    })
  });
  return { paid: true, now: rows.length > 0 };
}

// Paystack sends back whatever we attached when the payment started
function orderNumberOf(tx) {
  let meta = tx.metadata;
  if (typeof meta === 'string') { try { meta = JSON.parse(meta); } catch { meta = null; } }
  return meta && meta.order_number;
}

function send(res, code, body) {
  res.statusCode = code;
  res.setHeader('Content-Type', 'application/json');
  res.setHeader('Cache-Control', 'no-store');
  res.end(JSON.stringify(body));
}

function readRaw(req) {
  return new Promise((resolve, reject) => {
    let data = '';
    req.on('data', chunk => { data += chunk; });
    req.on('end', () => resolve(data));
    req.on('error', reject);
  });
}

async function readJson(req) {
  if (req.body && typeof req.body === 'object') return req.body;
  if (typeof req.body === 'string') return JSON.parse(req.body || '{}');
  const raw = await readRaw(req);
  return raw ? JSON.parse(raw) : {};
}

module.exports = { env, paystack, db, findOrder, kobo, markPaid, orderNumberOf, send, readRaw, readJson };
