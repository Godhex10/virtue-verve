-- ==========================================================
-- Virtue & Verve — database structure for Supabase
--
-- How to use:
--   1. Supabase dashboard > SQL Editor > New query
--   2. Paste this whole file and press Run
--   3. Then run seed.sql the same way to load the starting catalogue
--   4. Create her login (Authentication > Users > Add user), then run the
--      "make someone an admin" line at the very bottom with her email
--
-- Safe to run more than once.
-- ==========================================================

-- ---------- Who is an admin ----------
create table if not exists public.admins (
  user_id uuid primary key references auth.users (id) on delete cascade,
  created_at timestamptz not null default now()
);

-- Used by every security rule below. Visitors who aren't signed in are never admins.
create or replace function public.is_admin()
returns boolean
language sql stable security definer set search_path = public
as $$
  select exists (select 1 from public.admins where user_id = auth.uid());
$$;

-- ---------- Catalogue ----------
create table if not exists public.categories (
  id text primary key,                         -- used in the page address, e.g. 'tote'
  kind text not null default 'category' check (kind in ('category', 'edit')),
  label text not null,                         -- 'Totes'
  singular text,                               -- 'tote'
  note text,                                   -- short line, 'Carry it all'
  intro text,                                  -- banner paragraph
  image_url text,
  rule jsonb,                                  -- edits only: {"tag":"New"} or {"family":"Green"}
  sort int not null default 0,
  visible boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.products (
  id text primary key,                         -- used in the page address, e.g. 'seraphine'
  name text not null,
  kind text not null,                          -- 'Top-Handle'
  category_id text not null references public.categories (id) on update cascade,
  price numeric(10, 2) not null check (price >= 0),
  tag text check (tag in ('New', 'Bestseller')),
  description text,
  material text,
  size text,
  carry text,
  active boolean not null default true,        -- untick to hide from the shop without deleting
  sort int not null default 0,
  created_at timestamptz not null default now()
);

create table if not exists public.product_colours (
  id uuid primary key default gen_random_uuid(),
  product_id text not null references public.products (id) on update cascade on delete cascade,
  name text not null,                          -- 'Verve Teal'
  hex text not null,                           -- swatch colour
  family text,                                 -- for the colour filter: Green, Brown, Neutral...
  images text[] not null default '{}',         -- photos of this colour; the first is the main one
  stock int not null default 0 check (stock >= 0),
  sort int not null default 0
);
create index if not exists product_colours_product_idx on public.product_colours (product_id);

-- Each zone covers a list of states; each delivery option belongs to one zone
create table if not exists public.delivery_zones (
  id text primary key,
  label text not null,                         -- 'Lagos', 'Rest of Nigeria'
  regions text[] not null default '{}',        -- the states it covers, e.g. {'Lagos'}
  sort int not null default 0
);

create table if not exists public.delivery_options (
  id text primary key,
  zone_id text not null references public.delivery_zones (id) on update cascade on delete cascade,
  label text not null,
  note text,
  price numeric(10, 2) not null default 0 check (price >= 0),
  sort int not null default 0,
  active boolean not null default true
);

-- Store details, announcement bar text, currency and similar
create table if not exists public.settings (
  key text primary key,
  value jsonb not null
);

-- ---------- Orders ----------
create table if not exists public.orders (
  id uuid primary key default gen_random_uuid(),
  number text not null unique,                 -- 'VV-482913'
  status text not null default 'new' check (status in ('new', 'paid', 'packed', 'sent', 'delivered', 'cancelled')),
  first_name text not null,
  last_name text not null,
  email text not null,
  phone text,
  address text[] not null default '{}',
  delivery_label text not null,
  delivery_note text,
  delivery_price numeric(10, 2) not null default 0,
  delivery_zone text,                          -- e.g. 'Rest of Nigeria'
  delivery_region text,                        -- the state the customer chose
  payment_method text not null,
  payment_reference text,                      -- filled in by the payment provider later
  subtotal numeric(10, 2) not null default 0,
  total numeric(10, 2) not null default 0,
  gift_note text,
  admin_note text,
  created_at timestamptz not null default now()
);
create index if not exists orders_created_idx on public.orders (created_at desc);

create table if not exists public.order_items (
  id uuid primary key default gen_random_uuid(),
  order_id uuid not null references public.orders (id) on delete cascade,
  product_id text,                             -- kept as plain text so old orders survive a deleted bag
  colour_id uuid,
  name text not null,
  kind text,
  colour text,
  image_url text,
  price numeric(10, 2) not null,
  qty int not null check (qty > 0)
);
create index if not exists order_items_order_idx on public.order_items (order_id);

-- ---------- Contact form and newsletter ----------
create table if not exists public.messages (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  email text not null,
  topic text,
  order_number text,
  message text not null,
  is_read boolean not null default false,
  created_at timestamptz not null default now()
);

create table if not exists public.subscribers (
  email text primary key,
  created_at timestamptz not null default now()
);

-- ==========================================================
-- Security rules
-- Visitors can read the catalogue and send orders/messages.
-- Only admins can change anything or read orders, messages and subscribers.
-- ==========================================================
alter table public.admins enable row level security;
alter table public.categories enable row level security;
alter table public.products enable row level security;
alter table public.product_colours enable row level security;
alter table public.delivery_zones enable row level security;
alter table public.delivery_options enable row level security;
alter table public.settings enable row level security;
alter table public.orders enable row level security;
alter table public.order_items enable row level security;
alter table public.messages enable row level security;
alter table public.subscribers enable row level security;

-- Admins: full access to everything
do $$
declare t text;
begin
  foreach t in array array['admins', 'categories', 'products', 'product_colours', 'delivery_zones', 'delivery_options',
                           'settings', 'orders', 'order_items', 'messages', 'subscribers']
  loop
    execute format('drop policy if exists "Admins manage %1$s" on public.%1$I', t);
    execute format('create policy "Admins manage %1$s" on public.%1$I for all to authenticated using (public.is_admin()) with check (public.is_admin())', t);
  end loop;
end $$;

-- Everyone: read the parts of the catalogue that are switched on
drop policy if exists "Anyone reads visible categories" on public.categories;
create policy "Anyone reads visible categories" on public.categories
  for select to anon, authenticated using (visible);

drop policy if exists "Anyone reads active products" on public.products;
create policy "Anyone reads active products" on public.products
  for select to anon, authenticated using (active);

drop policy if exists "Anyone reads colours of active products" on public.product_colours;
create policy "Anyone reads colours of active products" on public.product_colours
  for select to anon, authenticated
  using (exists (select 1 from public.products p where p.id = product_id and p.active));

drop policy if exists "Anyone reads delivery zones" on public.delivery_zones;
create policy "Anyone reads delivery zones" on public.delivery_zones
  for select to anon, authenticated using (true);

drop policy if exists "Anyone reads active delivery options" on public.delivery_options;
create policy "Anyone reads active delivery options" on public.delivery_options
  for select to anon, authenticated using (active);

drop policy if exists "Anyone reads settings" on public.settings;
create policy "Anyone reads settings" on public.settings
  for select to anon, authenticated using (true);

-- Everyone: send a contact message or join the newsletter (but not read them back)
drop policy if exists "Anyone sends a message" on public.messages;
create policy "Anyone sends a message" on public.messages
  for insert to anon, authenticated
  with check (is_read = false and char_length(message) <= 4000 and char_length(name) <= 200 and char_length(email) <= 320);

drop policy if exists "Anyone subscribes" on public.subscribers;
create policy "Anyone subscribes" on public.subscribers
  for insert to anon, authenticated with check (char_length(email) <= 320);

-- A signed-in user can check whether they themselves are an admin (the admin login uses this)
drop policy if exists "Users see their own admin row" on public.admins;
create policy "Users see their own admin row" on public.admins
  for select to authenticated using (user_id = auth.uid());

-- ==========================================================
-- Placing an order
-- Visitors cannot write to the orders table directly. The shop calls this
-- function instead, so prices and totals always come from the database
-- (never from the visitor's browser) and stock is checked and reduced safely.
-- ==========================================================
create or replace function public.place_order(payload jsonb)
returns jsonb
language plpgsql security definer set search_path = public
as $$
declare
  cust jsonb := coalesce(payload -> 'customer', '{}'::jsonb);
  item jsonb;
  line record;
  ship record;
  area record;
  v_qty int;
  v_subtotal numeric(10, 2) := 0;
  v_order_id uuid;
  v_number text;
  v_address text[];
begin
  if jsonb_typeof(payload -> 'items') is distinct from 'array' or jsonb_array_length(payload -> 'items') = 0 then
    raise exception 'Your bag is empty';
  end if;
  if jsonb_array_length(payload -> 'items') > 50 then
    raise exception 'Too many items in one order';
  end if;
  if coalesce(trim(cust ->> 'first_name'), '') = '' or coalesce(trim(cust ->> 'last_name'), '') = ''
     or coalesce(trim(cust ->> 'email'), '') = '' then
    raise exception 'Please add your name and email address';
  end if;

  select * into ship from delivery_options where id = payload ->> 'delivery_id' and active;
  if not found then
    raise exception 'Please choose a delivery option';
  end if;

  -- The option must belong to the zone that covers the customer's state
  select * into area from delivery_zones where id = ship.zone_id;
  if not (coalesce(payload ->> 'region', '') = any (area.regions)) then
    raise exception 'That delivery option isn''t available for %', coalesce(nullif(payload ->> 'region', ''), 'your area');
  end if;

  select coalesce(array_agg(trim(a)), '{}') into v_address
  from jsonb_array_elements_text(coalesce(payload -> 'address', '[]'::jsonb)) a
  where trim(a) <> '';

  loop
    v_number := 'VV-' || (floor(random() * 900000) + 100000)::int::text;
    exit when not exists (select 1 from orders where number = v_number);
  end loop;

  insert into orders (number, first_name, last_name, email, phone, address,
                      delivery_label, delivery_note, delivery_price, delivery_zone, delivery_region, payment_method, gift_note)
  values (v_number, left(trim(cust ->> 'first_name'), 100), left(trim(cust ->> 'last_name'), 100),
          left(trim(cust ->> 'email'), 320), left(cust ->> 'phone', 40), v_address,
          ship.label, ship.note, ship.price, area.label, payload ->> 'region',
          left(coalesce(payload ->> 'payment_method', 'Card'), 40), left(payload ->> 'gift_note', 240))
  returning id into v_order_id;

  for item in select * from jsonb_array_elements(payload -> 'items')
  loop
    v_qty := (item ->> 'qty')::int;
    if v_qty is null or v_qty < 1 or v_qty > 20 then
      raise exception 'Please check the quantities in your bag';
    end if;

    select pc.id as colour_id, pc.name as colour, pc.stock, pc.images[1] as image_url,
           p.id as product_id, p.name, p.kind, p.price
      into line
      from product_colours pc
      join products p on p.id = pc.product_id
     where pc.id = (item ->> 'colour_id')::uuid and p.active
       for update of pc;

    if not found then
      raise exception 'One of the bags in your order is no longer available';
    end if;
    if line.stock < v_qty then
      raise exception 'Only % left of the % in %', line.stock, line.name, line.colour;
    end if;

    update product_colours set stock = stock - v_qty where id = line.colour_id;

    insert into order_items (order_id, product_id, colour_id, name, kind, colour, image_url, price, qty)
    values (v_order_id, line.product_id, line.colour_id, line.name, line.kind, line.colour, line.image_url, line.price, v_qty);

    v_subtotal := v_subtotal + line.price * v_qty;
  end loop;

  update orders set subtotal = v_subtotal, total = v_subtotal + ship.price where id = v_order_id;

  return jsonb_build_object('number', v_number, 'subtotal', v_subtotal,
                            'delivery_price', ship.price, 'total', v_subtotal + ship.price);
end;
$$;

revoke all on function public.place_order(jsonb) from public;
grant execute on function public.place_order(jsonb) to anon, authenticated;
grant execute on function public.is_admin() to anon, authenticated;

-- ==========================================================
-- Product photos
-- A public folder: anyone can view photos, only admins can upload or delete.
-- ==========================================================
insert into storage.buckets (id, name, public)
values ('products', 'products', true)
on conflict (id) do nothing;

drop policy if exists "Anyone views product photos" on storage.objects;
create policy "Anyone views product photos" on storage.objects
  for select to anon, authenticated using (bucket_id = 'products');

drop policy if exists "Admins manage product photos" on storage.objects;
create policy "Admins manage product photos" on storage.objects
  for all to authenticated
  using (bucket_id = 'products' and public.is_admin())
  with check (bucket_id = 'products' and public.is_admin());

-- ==========================================================
-- Make someone an admin
-- First create their login in Authentication > Users, then run this
-- one line with their email address (remove the two dashes at the start):
--
-- insert into public.admins (user_id) select id from auth.users where email = 'her-email@example.com' on conflict do nothing;
-- ==========================================================
