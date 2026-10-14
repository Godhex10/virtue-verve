/* ==========================================================
   Virtue & Verve — the database connection, shared by the shop and the admin
   Needs the supabase-js library and config.js loaded first.

   The rest of the site works with one shape of data (the same shape the
   admin used in preview mode). The converters here translate between
   that shape and the database tables in supabase/schema.sql.
   ========================================================== */

const LIVE = !!(window.supabase && typeof SUPABASE_URL !== 'undefined' && SUPABASE_URL && SUPABASE_ANON_KEY);
const sb = LIVE ? window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;

// ---------- Database rows -> site data ----------
const fromDb = {
  product: r => ({
    ...r, price: Number(r.price), tag: r.tag || '', description: r.description || '',
    material: r.material || '', size: r.size || '', carry: r.carry || '',
    colours: (r.colours || []).sort((a, b) => a.sort - b.sort).map(c => ({
      id: c.id, name: c.name, hex: c.hex, family: c.family || '', images: c.images || [],
      image_url: (c.images || [])[0] || '', stock: c.stock, sort: c.sort
    }))
  }),
  option: r => ({ ...r, zone: r.zone_id, note: r.note || '', price: Number(r.price) }),
  order: r => ({
    ...r, subtotal: Number(r.subtotal), total: Number(r.total), delivery_price: Number(r.delivery_price),
    address: r.address || [], gift_note: r.gift_note || '', admin_note: r.admin_note || '',
    items: (r.items || []).map(i => ({ ...i, price: Number(i.price) }))
  }),
  settings: rows => {
    const v = Object.fromEntries((rows || []).map(r => [r.key, r.value])), c = v.contact || {};
    return {
      currency: v.currency || '₦', email: c.email || '', whatsapp: c.whatsapp || '', instagram: c.instagram || '',
      hours: c.hours || '', return_days: v.return_days ?? 14, preview: v.preview !== false, announcement: v.announcement || [], bank: v.bank || ''
    };
  }
};

// ---------- Site data -> database rows ----------
const toDbSettings = s => [
  { key: 'currency', value: s.currency },
  { key: 'contact', value: { email: s.email, whatsapp: s.whatsapp, instagram: s.instagram, hours: s.hours } },
  { key: 'return_days', value: Number(s.return_days) || 0 },
  { key: 'announcement', value: s.announcement || [] },
  { key: 'preview', value: !!s.preview },
  { key: 'bank', value: s.bank || '' }
];

// Everything the shop needs to draw itself, in one go
async function loadCatalogue() {
  const results = await Promise.all([
    sb.from('categories').select('*').order('sort'),
    sb.from('products').select('*, colours:product_colours(*)').order('sort'),
    sb.from('delivery_zones').select('*').order('sort'),
    sb.from('delivery_options').select('*').order('sort'),
    sb.from('settings').select('*')
  ]);
  const failed = results.find(r => r.error);
  if (failed) throw failed.error;
  const [cats, prods, zones, opts, sets] = results.map(r => r.data);
  return {
    version: DATA_VERSION, categories: cats, products: prods.map(fromDb.product),
    delivery_zones: zones, delivery_options: opts.map(fromDb.option), settings: fromDb.settings(sets)
  };
}
