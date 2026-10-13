/* ==========================================================
   Virtue & Verve — Supabase connection
   Find both values in the Supabase dashboard under
   Project Settings > API.

   The "anon" key is meant to be public and is safe in this file:
   the security rules in supabase/schema.sql decide what it can do.
   NEVER put the "service_role" key here or anywhere on the website.
   ========================================================== */

const SUPABASE_URL = '';      // e.g. https://abcdefghijkl.supabase.co
const SUPABASE_ANON_KEY = ''; // the long key labelled "anon" / "public"
