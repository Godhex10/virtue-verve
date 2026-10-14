-- ==========================================================
-- Virtue & Verve — online payments (run once, after schema.sql)
-- Adds the time an order was paid. The payment functions on Vercel fill it in
-- together with payment_reference once Paystack confirms the payment.
-- Safe to run more than once.
-- ==========================================================
alter table public.orders add column if not exists paid_at timestamptz;
