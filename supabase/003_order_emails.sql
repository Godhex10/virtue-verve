-- ==========================================================
-- Virtue & Verve — order emails (run once, after 002_payments.sql)
--
-- Whenever a new order is created, the database calls the website's
-- /api/notify/order function, which sends the emails. pg_net is Supabase's
-- built-in extension for calling web addresses; the call goes out once the
-- order is fully saved.
--
-- If the site moves to her own domain, run the "create or replace function"
-- part again with the new address.
-- Safe to run more than once.
-- ==========================================================

create extension if not exists pg_net;

-- Remember which emails have gone out, so none is ever sent twice
alter table public.orders add column if not exists alert_sent_at timestamptz;
alter table public.orders add column if not exists receipt_sent_at timestamptz;

-- Kept out of the public schema so it can't be called from the website
create schema if not exists private;

create or replace function private.email_new_order()
returns trigger
language plpgsql security definer set search_path = public
as $$
begin
  perform net.http_post(
    url := 'https://virtue-verve.vercel.app/api/notify/order',
    body := jsonb_build_object('order_id', new.id),
    headers := '{"Content-Type": "application/json"}'::jsonb
  );
  return new;
end;
$$;

drop trigger if exists email_new_order on public.orders;
create trigger email_new_order
  after insert on public.orders
  for each row execute function private.email_new_order();
