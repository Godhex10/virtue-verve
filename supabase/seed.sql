-- ==========================================================
-- Virtue & Verve — starting catalogue
-- Run this after schema.sql. It loads the sample bags, categories,
-- delivery options and store details that the demo site uses.
--
-- Running it again resets these rows to the values below, so don't
-- re-run it once she has started editing the catalogue in the admin.
-- Stock is set to 10 of each colour as a placeholder.
-- ==========================================================

-- ---------- Categories and edits ----------
insert into public.categories (id, kind, label, singular, note, intro, image_url, rule, sort) values
  ('tote', 'category', 'Totes', 'tote', 'Carry it all', 'Room for the laptop, the lunch and the just-in-case. Totes that carry your whole day and still look composed.', 'assets/images/amara.jpg', null, 1),
  ('top-handle', 'category', 'Top-handles', 'top-handle', 'Polished & poised', 'Structured, ladylike and a little bit grand. The bags you hold, not just carry.', 'assets/images/nova.jpg', null, 2),
  ('crossbody', 'category', 'Crossbody', 'crossbody bag', 'Hands-free', 'Hands free, heart full. Easy shapes that sit just right and go everywhere.', 'assets/images/sienna.jpg', null, 3),
  ('clutch', 'category', 'Clutches', 'clutch', 'After dark', 'Small, striking and made for the evening. Just enough room for what matters.', 'assets/images/zuri.jpg', null, 4),
  ('new-in', 'edit', 'New in', null, 'Fresh arrivals', 'The latest pieces to land. Be the first to carry them.', 'assets/images/belle.jpg', '{"tag":"New"}'::jsonb, 5),
  ('bestsellers', 'edit', 'Bestsellers', null, 'Most loved', 'The ones that keep selling out. Tried, tested and adored.', 'assets/images/ines.jpg', '{"tag":"Bestseller"}'::jsonb, 6),
  ('emerald-edit', 'edit', 'The Emerald Edit', null, 'Our signature shade', 'Our signature green, from deep forest to bright emerald. One shade, every mood.', 'assets/images/hero.jpg', '{"family":"Green"}'::jsonb, 7)
on conflict (id) do update set kind = excluded.kind, label = excluded.label, singular = excluded.singular, note = excluded.note,
  intro = excluded.intro, image_url = excluded.image_url, rule = excluded.rule, sort = excluded.sort;

-- ---------- Bags ----------
insert into public.products (id, name, kind, category_id, price, tag, description, material, size, carry, sort, created_at) values
  ('seraphine', 'Seraphine', 'Top-Handle', 'top-handle', 85000, 'Bestseller', 'Our signature. A structured top-handle in pebbled leather with a polished gold clasp — ladylike, but never shy.', 'Pebbled leather, gold-tone hardware', '26 × 19 × 10 cm', 'Top handle, detachable shoulder strap', 1, '2026-06-02'),
  ('amara', 'Amara', 'Tote', 'tote', 72000, 'New', 'The carry-everything tote. Roomy enough for your laptop and your lipstick collection, in leather that only gets better with time.', 'Full-grain leather, unlined', '34 × 36 × 12 cm', 'Twin shoulder handles', 2, '2026-09-20'),
  ('lola', 'Lola', 'Crossbody', 'crossbody', 58000, null, 'Quilted, curved and finished with a gold teardrop. Carry it by the handle or sling it on — made for brunch and everything after.', 'Quilted vegan leather, gold-tone hardware', '22 × 17 × 9 cm', 'Top handle, adjustable crossbody strap', 3, '2026-05-11'),
  ('belle', 'Belle', 'Chain Clutch', 'clutch', 45000, 'New', 'A flap clutch with a delicate chain. Tuck it under your arm for dinner, drape it on your shoulder for dancing.', 'Smooth leather, gunmetal chain', '23 × 15 × 6 cm', 'Chain shoulder strap, tucks away as a clutch', 4, '2026-09-28'),
  ('mimi', 'Mimi', 'Pleated Wristlet', 'clutch', 38000, 'Bestseller', 'Small bag, big personality. A sculpted, pleated wristlet that fits your phone, your gloss and your best secrets.', 'Sculpted pleated shell, satin lining', '19 × 17 × 12 cm', 'Wrist handle', 5, '2026-07-15'),
  ('eden', 'Eden', 'Weekend Tote', 'tote', 95000, null, 'Your weekend-away companion. Heritage tweed trimmed in deep green leather, generously sized and ready whenever you are.', 'Wool tweed with leather trim', '40 × 32 × 16 cm', 'Twin handles, fits on the shoulder', 6, '2026-04-03'),
  ('nova', 'Nova', 'Mini Top-Handle', 'top-handle', 52000, null, 'The Seraphine’s little sister. All the polish in a palm-sized silhouette, with a turn-lock you’ll love to click.', 'Smooth leather, turn-lock clasp', '18 × 13 × 7 cm', 'Top handle, detachable chain', 7, '2026-08-08'),
  ('cleo', 'Cleo', 'Shoulder Bag', 'crossbody', 62000, null, 'Soft, slouchy and quietly chic. A buttery shoulder bag in a shade that goes with absolutely everything.', 'Soft nappa leather', '25 × 15 × 11 cm', 'Adjustable shoulder strap', 8, '2026-08-21'),
  ('zuri', 'Zuri', 'Box Clutch', 'clutch', 55000, 'New', 'A jewel of a clutch. Textured emerald with a crystal-set gold clasp — the one that gets noticed across the room.', 'Textured leather, crystal-set clasp', '21 × 11 × 5 cm', 'Hand-held, optional chain inside', 9, '2026-10-01'),
  ('sienna', 'Sienna', 'Crossbody', 'crossbody', 54000, null, 'The everyday one. A clean, curved flap in rich tan leather with an adjustable strap that sits just right on the hip.', 'Grained leather, magnetic flap', '24 × 17 × 8 cm', 'Adjustable crossbody strap', 10, '2026-06-24'),
  ('ines', 'Ines', 'Woven Tote', 'tote', 89000, 'Bestseller', 'Hand-woven leather with a chunky gold chain. Soft enough to slouch, strong enough for everything you throw in it.', 'Hand-woven leather, gold-tone chain', '38 × 30 × 14 cm', 'Chain and leather shoulder strap', 11, '2026-07-30'),
  ('harper', 'Harper', 'Satchel', 'top-handle', 78000, null, 'A classic satchel with twin gold buckles. Smart enough for the office, relaxed enough for the weekend.', 'Pebbled leather, twin buckles', '28 × 21 × 10 cm', 'Top handle, detachable strap', 12, '2026-05-29')
on conflict (id) do update set name = excluded.name, kind = excluded.kind, category_id = excluded.category_id, price = excluded.price,
  tag = excluded.tag, description = excluded.description, material = excluded.material, size = excluded.size, carry = excluded.carry,
  sort = excluded.sort, created_at = excluded.created_at;

-- ---------- Colours (one row per colour of each bag, with its photo and stock) ----------
delete from public.product_colours where product_id in ('seraphine', 'amara', 'lola', 'belle', 'mimi', 'eden', 'nova', 'cleo', 'zuri', 'sienna', 'ines', 'harper');
insert into public.product_colours (product_id, name, hex, family, images, stock, sort) values
  ('seraphine', 'Verve Teal', '#0f7f86', 'Green', array['assets/images/seraphine.jpg', 'assets/images/seraphine-detail.jpg'], 10, 1),
  ('amara', 'Caramel', '#b5651d', 'Brown', array['assets/images/amara.jpg', 'assets/images/amara-detail.jpg'], 10, 1),
  ('lola', 'Rosewater', '#d99a8f', 'Pink', array['assets/images/lola.jpg', 'assets/images/lola-detail.jpg'], 10, 1),
  ('belle', 'Blush', '#f1b6c1', 'Pink', array['assets/images/belle.jpg', 'assets/images/belle-detail.jpg'], 10, 1),
  ('mimi', 'Vanilla', '#e9e4d6', 'Neutral', array['assets/images/mimi-vanilla.jpg', 'assets/images/mimi-vanilla-detail.jpg'], 10, 1),
  ('mimi', 'Emerald', '#12a150', 'Green', array['assets/images/mimi-emerald.jpg', 'assets/images/mimi-emerald-detail.jpg'], 10, 2),
  ('eden', 'Forest Tweed', '#1f5a3c', 'Green', array['assets/images/eden.jpg', 'assets/images/eden-detail.jpg'], 10, 1),
  ('nova', 'Blush', '#f0b4ae', 'Pink', array['assets/images/nova.jpg', 'assets/images/nova-detail.jpg'], 10, 1),
  ('cleo', 'Sand', '#e6c9a8', 'Neutral', array['assets/images/cleo.jpg', 'assets/images/cleo-detail.jpg'], 10, 1),
  ('zuri', 'Emerald', '#1c7a5c', 'Green', array['assets/images/zuri.jpg', 'assets/images/zuri-detail.jpg'], 10, 1),
  ('sienna', 'Tan', '#9c4f2c', 'Brown', array['assets/images/sienna.jpg', 'assets/images/sienna-detail.jpg'], 10, 1),
  ('ines', 'Cognac', '#a0522d', 'Brown', array['assets/images/ines.jpg', 'assets/images/ines-detail.jpg'], 10, 1),
  ('harper', 'Dove Grey', '#8e8a93', 'Grey', array['assets/images/harper.jpg', 'assets/images/harper-detail.jpg'], 10, 1);

-- ---------- Delivery zones (which states each covers) and their options ----------
insert into public.delivery_zones (id, label, regions, sort) values
  ('lagos', 'Lagos', array['Lagos'], 1),
  ('nigeria', 'Rest of Nigeria', array['Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT (Abuja)', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'], 2),
  ('international', 'Outside Nigeria', array['Outside Nigeria'], 3)
on conflict (id) do update set label = excluded.label, regions = excluded.regions, sort = excluded.sort;

insert into public.delivery_options (id, zone_id, label, note, price, sort) values
  ('lagos-standard', 'lagos', 'Standard delivery', '1 – 2 working days', 3500, 1),
  ('lagos-express', 'lagos', 'Same-day delivery', 'Order before 12pm', 6000, 2),
  ('lagos-pickup', 'lagos', 'Collect in person', 'Ready within 24 hours', 0, 3),
  ('ng-standard', 'nigeria', 'Standard delivery', '3 – 5 working days', 6500, 4),
  ('ng-express', 'nigeria', 'Express delivery', '1 – 2 working days', 12000, 5),
  ('intl-standard', 'international', 'International delivery', '7 – 14 working days', 45000, 6)
on conflict (id) do update set zone_id = excluded.zone_id, label = excluded.label, note = excluded.note, price = excluded.price, sort = excluded.sort;

-- ---------- Store details (placeholders: replace in the admin) ----------
insert into public.settings (key, value) values
  ('currency', '"₦"'::jsonb),
  ('contact', '{"email":"hello@yourstore.com","whatsapp":"+000 000 000 0000","instagram":"@yourhandle","hours":"Monday to Saturday, 9am – 6pm"}'::jsonb),
  ('return_days', '14'::jsonb),
  ('announcement', '["Complimentary gift wrapping on every order","New in — The Emerald Edit","Carry your confidence"]'::jsonb),
  ('preview', 'true'::jsonb)
on conflict (key) do update set value = excluded.value;
