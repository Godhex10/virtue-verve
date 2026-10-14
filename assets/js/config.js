/* ==========================================================
   Virtue & Verve — Supabase connection
   Find both values in the Supabase dashboard under
   Project Settings > API (or Data API / API Keys).

   The public ("anon" / "publishable") key is meant to be public and is
   safe in this file: the security rules in supabase/schema.sql decide
   what it can do. NEVER put the "service_role" / "secret" key here or
   anywhere on the website.

   Leave both empty to run the site in preview mode (data kept in the browser).
   ========================================================== */

const SUPABASE_URL = 'https://gbevnyfaicwdyvnjmqdk.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_T7RNDBRCS82kdc2q6azBWA_qc5nwo_P';
