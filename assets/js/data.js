/* ==========================================================
   Virtue & Verve — store data (edit this file to change the catalogue)
   ========================================================== */

let CURRENCY = '₦';

// Raise this number whenever the starting catalogue below changes shape or prices.
// Saved preview data from an older version is then ignored and started afresh.
const DATA_VERSION = 4;

// The scrolling line at the very top of every page
const ANNOUNCE = ['Complimentary gift wrapping on every order', 'New in — The Emerald Edit', 'Carry your confidence'];

// Each colour is [name, swatch hex, main photo, colour family, extra photos, database id].
// The main photo is the one on the cards; the extras appear in the gallery on the bag's page.
// Give a product more than one colour and swatches appear on its card.
const PRODUCTS = [
  { id: 'seraphine', name: 'Seraphine', kind: 'Top-Handle', cat: 'top-handle', price: 85000, tag: 'Bestseller', added: '2026-06-02',
    desc: 'Our signature. A structured top-handle in pebbled leather with a polished gold clasp — ladylike, but never shy.',
    colors: [['Verve Teal', '#0f7f86', 'assets/images/seraphine.jpg', 'Green', ['assets/images/seraphine-detail.jpg']]] },
  { id: 'amara', name: 'Amara', kind: 'Tote', cat: 'tote', price: 72000, tag: 'New', added: '2026-09-20',
    desc: 'The carry-everything tote. Roomy enough for your laptop and your lipstick collection, in leather that only gets better with time.',
    colors: [['Caramel', '#b5651d', 'assets/images/amara.jpg', 'Brown', ['assets/images/amara-detail.jpg']]] },
  { id: 'lola', name: 'Lola', kind: 'Crossbody', cat: 'crossbody', price: 58000, added: '2026-05-11',
    desc: 'Quilted, curved and finished with a gold teardrop. Carry it by the handle or sling it on — made for brunch and everything after.',
    colors: [['Rosewater', '#d99a8f', 'assets/images/lola.jpg', 'Pink', ['assets/images/lola-detail.jpg']]] },
  { id: 'belle', name: 'Belle', kind: 'Chain Clutch', cat: 'clutch', price: 45000, tag: 'New', added: '2026-09-28',
    desc: 'A flap clutch with a delicate chain. Tuck it under your arm for dinner, drape it on your shoulder for dancing.',
    colors: [['Blush', '#f1b6c1', 'assets/images/belle.jpg', 'Pink', ['assets/images/belle-detail.jpg']]] },
  { id: 'mimi', name: 'Mimi', kind: 'Pleated Wristlet', cat: 'clutch', price: 38000, tag: 'Bestseller', added: '2026-07-15',
    desc: 'Small bag, big personality. A sculpted, pleated wristlet that fits your phone, your gloss and your best secrets.',
    colors: [['Vanilla', '#e9e4d6', 'assets/images/mimi-vanilla.jpg', 'Neutral', ['assets/images/mimi-vanilla-detail.jpg']], ['Emerald', '#12a150', 'assets/images/mimi-emerald.jpg', 'Green', ['assets/images/mimi-emerald-detail.jpg']]] },
  { id: 'eden', name: 'Eden', kind: 'Weekend Tote', cat: 'tote', price: 95000, added: '2026-04-03',
    desc: 'Your weekend-away companion. Heritage tweed trimmed in deep green leather, generously sized and ready whenever you are.',
    colors: [['Forest Tweed', '#1f5a3c', 'assets/images/eden.jpg', 'Green', ['assets/images/eden-detail.jpg']]] },
  { id: 'nova', name: 'Nova', kind: 'Mini Top-Handle', cat: 'top-handle', price: 52000, added: '2026-08-08',
    desc: 'The Seraphine’s little sister. All the polish in a palm-sized silhouette, with a turn-lock you’ll love to click.',
    colors: [['Blush', '#f0b4ae', 'assets/images/nova.jpg', 'Pink', ['assets/images/nova-detail.jpg']]] },
  { id: 'cleo', name: 'Cleo', kind: 'Shoulder Bag', cat: 'crossbody', price: 62000, added: '2026-08-21',
    desc: 'Soft, slouchy and quietly chic. A buttery shoulder bag in a shade that goes with absolutely everything.',
    colors: [['Sand', '#e6c9a8', 'assets/images/cleo.jpg', 'Neutral', ['assets/images/cleo-detail.jpg']]] },
  { id: 'zuri', name: 'Zuri', kind: 'Box Clutch', cat: 'clutch', price: 55000, tag: 'New', added: '2026-10-01',
    desc: 'A jewel of a clutch. Textured emerald with a crystal-set gold clasp — the one that gets noticed across the room.',
    colors: [['Emerald', '#1c7a5c', 'assets/images/zuri.jpg', 'Green', ['assets/images/zuri-detail.jpg']]] },
  { id: 'sienna', name: 'Sienna', kind: 'Crossbody', cat: 'crossbody', price: 54000, added: '2026-06-24',
    desc: 'The everyday one. A clean, curved flap in rich tan leather with an adjustable strap that sits just right on the hip.',
    colors: [['Tan', '#9c4f2c', 'assets/images/sienna.jpg', 'Brown', ['assets/images/sienna-detail.jpg']]] },
  { id: 'ines', name: 'Ines', kind: 'Woven Tote', cat: 'tote', price: 89000, tag: 'Bestseller', added: '2026-07-30',
    desc: 'Hand-woven leather with a chunky gold chain. Soft enough to slouch, strong enough for everything you throw in it.',
    colors: [['Cognac', '#a0522d', 'assets/images/ines.jpg', 'Brown', ['assets/images/ines-detail.jpg']]] },
  { id: 'harper', name: 'Harper', kind: 'Satchel', cat: 'top-handle', price: 78000, added: '2026-05-29',
    desc: 'A classic satchel with twin gold buckles. Smart enough for the office, relaxed enough for the weekend.',
    colors: [['Dove Grey', '#8e8a93', 'assets/images/harper.jpg', 'Grey', ['assets/images/harper-detail.jpg']]] }
];

const CATEGORIES = [
  { cat: 'tote', label: 'Totes', one: 'tote', note: 'Carry it all', img: 'assets/images/amara.jpg' },
  { cat: 'top-handle', label: 'Top-handles', one: 'top-handle', note: 'Polished & poised', img: 'assets/images/nova.jpg' },
  { cat: 'crossbody', label: 'Crossbody', one: 'crossbody bag', note: 'Hands-free', img: 'assets/images/sienna.jpg' },
  { cat: 'clutch', label: 'Clutches', one: 'clutch', note: 'After dark', img: 'assets/images/zuri.jpg' }
];

// Swatch shown for each colour family in the shop filters
const FAMILIES = { Green: '#088178', Brown: '#a8632a', Neutral: '#e9e0cf', Pink: '#eab0b6', Grey: '#9a9aa3' };

const MOODS = [
  { label: 'Brunch', id: 'lola', color: 0, line: 'Mimosas at eleven. The Lola keeps your hands free for the important things.' },
  { label: 'Boss day', id: 'seraphine', color: 0, line: 'Walk in like you own the room. The Seraphine does half the talking for you.' },
  { label: 'Date night', id: 'zuri', color: 0, line: 'Lipstick, keys, a little mystery. The Zuri carries exactly enough.' },
  { label: 'Getaway', id: 'eden', color: 0, line: 'Out of office, into the weekend. The Eden packs for wherever you’re headed.' }
];

// ---------- Store details (placeholders — replace with her real details) ----------
const STORE = {
  preview: true, // set to false at launch to hide every "preview" note on the site
  email: 'hello@yourstore.com',
  whatsapp: '+000 000 000 0000',
  instagram: '@yourhandle',
  hours: 'Monday to Saturday, 9am – 6pm',
  returnDays: 14
};

// Where she delivers. Every state belongs to one zone, and each zone has its own options.
// The customer picks their state at checkout and sees only that zone's options.
const REGIONS = ['Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River', 'Delta',
  'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT (Abuja)', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi',
  'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe',
  'Zamfara', 'Outside Nigeria'];

const ZONES = [
  { id: 'lagos', label: 'Lagos', regions: ['Lagos'] },
  { id: 'nigeria', label: 'Rest of Nigeria', regions: REGIONS.filter(r => r !== 'Lagos' && r !== 'Outside Nigeria') },
  { id: 'international', label: 'Outside Nigeria', regions: ['Outside Nigeria'] }
];

// Prices and timings are placeholders — replace with her real ones
const DELIVERY = [
  { id: 'lagos-standard', zone: 'lagos', label: 'Standard delivery', note: '1 – 2 working days', price: 3500 },
  { id: 'lagos-express', zone: 'lagos', label: 'Same-day delivery', note: 'Order before 12pm', price: 6000 },
  { id: 'lagos-pickup', zone: 'lagos', label: 'Collect in person', note: 'Ready within 24 hours', price: 0 },
  { id: 'ng-standard', zone: 'nigeria', label: 'Standard delivery', note: '3 – 5 working days', price: 6500 },
  { id: 'ng-express', zone: 'nigeria', label: 'Express delivery', note: '1 – 2 working days', price: 12000 },
  { id: 'intl-standard', zone: 'international', label: 'International delivery', note: '7 – 14 working days', price: 45000 }
];

const PAYMENTS = [
  { id: 'card', label: 'Pay online', icon: 'credit-card', note: 'Card, bank transfer or USSD — paid securely through Paystack.' },
  { id: 'transfer', label: 'Bank transfer', icon: 'building-columns', note: 'We’ll send our account details with your order confirmation.' },
  { id: 'cod', label: 'Pay on delivery', icon: 'hand-holding-dollar', note: 'Pay when your bag arrives.' }
];

// Product details shown on each product page: [material, size, carry]
const SPECS = {
  seraphine: ['Pebbled leather, gold-tone hardware', '26 × 19 × 10 cm', 'Top handle, detachable shoulder strap'],
  amara: ['Full-grain leather, unlined', '34 × 36 × 12 cm', 'Twin shoulder handles'],
  lola: ['Quilted vegan leather, gold-tone hardware', '22 × 17 × 9 cm', 'Top handle, adjustable crossbody strap'],
  belle: ['Smooth leather, gunmetal chain', '23 × 15 × 6 cm', 'Chain shoulder strap, tucks away as a clutch'],
  mimi: ['Sculpted pleated shell, satin lining', '19 × 17 × 12 cm', 'Wrist handle'],
  eden: ['Wool tweed with leather trim', '40 × 32 × 16 cm', 'Twin handles, fits on the shoulder'],
  nova: ['Smooth leather, turn-lock clasp', '18 × 13 × 7 cm', 'Top handle, detachable chain'],
  cleo: ['Soft nappa leather', '25 × 15 × 11 cm', 'Adjustable shoulder strap'],
  zuri: ['Textured leather, crystal-set clasp', '21 × 11 × 5 cm', 'Hand-held, optional chain inside'],
  sienna: ['Grained leather, magnetic flap', '24 × 17 × 8 cm', 'Adjustable crossbody strap'],
  ines: ['Hand-woven leather, gold-tone chain', '38 × 30 × 14 cm', 'Chain and leather shoulder strap'],
  harper: ['Pebbled leather, twin buckles', '28 × 21 × 10 cm', 'Top handle, detachable strap']
};

// ---------- Collections (each one gets its own page: category.html?c=<id>) ----------
const INTROS = {
  tote: 'Room for the laptop, the lunch and the just-in-case. Totes that carry your whole day and still look composed.',
  'top-handle': 'Structured, ladylike and a little bit grand. The bags you hold, not just carry.',
  crossbody: 'Hands free, heart full. Easy shapes that sit just right and go everywhere.',
  clutch: 'Small, striking and made for the evening. Just enough room for what matters.'
};
const COLLECTIONS = [
  ...CATEGORIES.map(c => ({ id: c.cat, type: 'category', label: c.label, note: c.note, img: c.img, intro: INTROS[c.cat], match: p => p.cat === c.cat })),
  { id: 'new-in', type: 'edit', label: 'New in', note: 'Fresh arrivals', img: 'assets/images/belle.jpg',
    intro: 'The latest pieces to land. Be the first to carry them.', match: p => p.tag === 'New' },
  { id: 'bestsellers', type: 'edit', label: 'Bestsellers', note: 'Most loved', img: 'assets/images/ines.jpg',
    intro: 'The ones that keep selling out. Tried, tested and adored.', match: p => p.tag === 'Bestseller' },
  { id: 'emerald-edit', type: 'edit', label: 'The Emerald Edit', note: 'Our signature shade', img: 'assets/images/hero.jpg', family: 'Green',
    intro: 'Our signature green, from deep forest to bright emerald. One shade, every mood.', match: p => p.colors.some(c => c[3] === 'Green') }
];

// ---------- The real catalogue ----------
// The lists above are the starting catalogue. applyCatalogue() replaces them with
// the real one: from the database (boot.js calls it), or in preview mode from what
// the admin saved in this browser (see the bottom of this file).
function applyCatalogue(saved) {
  if (!saved || saved.version !== DATA_VERSION || !Array.isArray(saved.products) || !Array.isArray(saved.categories)) return;

  const s = saved.settings || {};
  if (s.currency) CURRENCY = s.currency;
  Object.assign(STORE, {
    preview: typeof s.preview === 'boolean' ? s.preview : STORE.preview,
    email: s.email || STORE.email, whatsapp: s.whatsapp || STORE.whatsapp, instagram: s.instagram || STORE.instagram,
    hours: s.hours || STORE.hours, returnDays: s.return_days ?? STORE.returnDays
  });
  const replace = (list, rows) => list.splice(0, list.length, ...rows);
  if (Array.isArray(s.announcement) && s.announcement.length) replace(ANNOUNCE, s.announcement);

  const zones = (saved.delivery_zones || []).sort((a, b) => a.sort - b.sort);
  if (zones.length) replace(ZONES, zones.map(z => ({ id: z.id, label: z.label, regions: z.regions || [] })));
  const options = (saved.delivery_options || []).filter(d => d.active !== false && d.label).sort((a, b) => a.sort - b.sort);
  if (options.length) replace(DELIVERY, options.map(d => ({ id: d.id, zone: d.zone, label: d.label, note: d.note || '', price: Number(d.price) || 0 })));

  const cats = saved.categories.filter(c => c.visible).sort((a, b) => a.sort - b.sort);
  replace(CATEGORIES, cats.filter(c => c.kind === 'category')
    .map(c => ({ cat: c.id, label: c.label, one: c.singular || c.label.toLowerCase(), note: c.note || '', img: c.image_url })));

  replace(PRODUCTS, saved.products
    .filter(p => p.active && p.colours.length && CATEGORIES.some(c => c.cat === p.category_id))
    .sort((a, b) => a.sort - b.sort)
    .map(p => {
      SPECS[p.id] = [p.material, p.size, p.carry];
      return { id: p.id, name: p.name, kind: p.kind, cat: p.category_id, price: Number(p.price) || 0, tag: p.tag || undefined,
        added: p.created_at || '', desc: p.description || '', colors: p.colours.map(c => {
          const photos = c.images && c.images.length ? c.images : [c.image_url];
          return [c.name, c.hex, photos[0], c.family, photos.slice(1), c.id];
        }) };
    }));

  replace(COLLECTIONS, cats.map(c => ({
    id: c.id, type: c.kind, label: c.label, note: c.note || '', img: c.image_url, intro: c.intro || '', family: c.rule && c.rule.family,
    match: c.kind === 'category' ? p => p.cat === c.id
      : c.rule && c.rule.tag ? p => p.tag === c.rule.tag
      : c.rule && c.rule.family ? p => p.colors.some(col => col[3] === c.rule.family) : () => false
  })));

  replace(MOODS, MOODS.filter(m => PRODUCTS.some(p => p.id === m.id)));
}

// Preview mode (no database): use what the admin saved in this browser
if (typeof LIVE === 'undefined' || !LIVE) {
  let saved = null;
  try { saved = JSON.parse(localStorage.getItem('vv_db')); } catch { /* storage unavailable */ }
  applyCatalogue(saved);
}
