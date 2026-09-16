/* ============================================================
   BM Forex Hub — Supabase Auth Module
   Provides: signUp, signIn, signOut, getSession, getUser,
             verifyOtp, resetPasswordOtp, updatePassword, updateProfile
   ============================================================ */
const BMAuth = (() => {
  'use strict';

  const SUPABASE_URL  = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
  const SUPABASE_ANON = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM5ODM1NjEsImV4cCI6MjA5OTU1OTU2MX0.cKpLKN-Azoj7UUUp3_uodYraYlJH4fQtKpyRitnbMgk';

  let _supabase = null;

  function getClient() {
    if (_supabase) return _supabase;
    if (!window.supabase || !window.supabase.createClient) {
      throw new Error('Supabase JS library not loaded. Add the CDN script before auth.js.');
    }
    _supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON);
    return _supabase;
  }
  function getApiEndpoint(path) {
    const API_BASE = (typeof window !== 'undefined' && window.API_BASE) ? window.API_BASE : '';
    if (API_BASE) {
      return API_BASE.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
    }
    return path.replace(/^\/+/, '');
  }

  async function postApiWithFallback(endpointName, payload) {
    const primary = getApiEndpoint('api/' + endpointName);
    const fallback = getApiEndpoint('admin/api/' + endpointName);
    try {
      let resp = await fetch(primary, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      if (resp.status === 404) {
        resp = await fetch(fallback, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      }
      return resp;
    } catch (e) {
      return await fetch(fallback, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    }
  }

  /**
   * Register a new user. Creates auth account + sends OTP confirmation email.
   * Accepts first_name and last_name; auto-generates a username from them.
   * Returns { user, error }.
   */
  async function signUp(firstName, lastName, email, password, metadata) {
    let base = (firstName + '.' + lastName)
      .toLowerCase()
      .replace(/[^a-z0-9._-]/g, '')
      .replace(/\.\./g, '.')
      .replace(/^-+|-+$/g, '');
    if (!base) base = (email || '').split('@')[0].replace(/[^a-z0-9._-]/g, '');
    const uniqueUsername = base + Math.floor(1000 + Math.random() * 9000);

    const { country_code, phone, ...rest } = metadata || {};

    try {
      const resp = await postApiWithFallback('register-user.php', {
        email: email,
        password: password,
        metadata: {
          username: uniqueUsername,
          first_name: firstName,
          last_name: lastName,
          country_code: country_code || '',
          phone: phone || '',
          phone_number: phone || '',
          ...rest
        }
      });

      let data = null;
      try {
        data = await resp.json();
      } catch(err) {
        data = null;
      }

      if (!resp.ok || !data || !data.success) {
        const errMsg = (data && data.error) || 'Registration failed. Please try again.';
        if (errMsg.toLowerCase().includes('already')) {
          return { user: null, error: 'An account with this email already exists. Please sign in instead.' };
        }
        return { user: null, error: errMsg };
      }

      return { user: data.user, error: null };
    } catch (e) {
      console.error('Registration request failed:', e);
      return { user: null, error: "Could not connect to the server. Please check your connection." };
    }
  }

  /**
   * Resend the signup confirmation email (same token type as signUp).
   * Uses Supabase's resend endpoint so the token type stays 'signup'.
   * Returns { error }.
   */
  async function resendSignup(email) {
    const sb = getClient();
    const { error } = await sb.auth.resend({ type: 'signup', email });
    if (error) return { error: error.message || 'Failed to resend code.' };
    return { error: null };
  }

  /**
   * Verify OTP code sent to email (signup or recovery).
   * Returns { data, error }.
   */
  async function verifyOtp(email, token, type) {
    const sb = getClient();
    const { data, error } = await sb.auth.verifyOtp({ email, token, type });
    if (error) return { data: null, error: error.message };
    return { data, error: null };
  }

  /**
   * Send password reset OTP to email.
   * Returns { error }.
   */
  async function resetPasswordOtp(email) {
    try {
      const resp = await postApiWithFallback('otp.php', { action: 'resend', email: email, type: 'recovery' });
      let data = null;
      try {
        data = await resp.json();
      } catch(err) {
        data = null;
      }
      if (resp.ok && data && data.success) {
        return { error: null };
      }
      if (resp.status === 429) {
        return { error: 'Too many attempts. Please wait before trying again.' };
      }
      return { error: (data && data.error) || 'Failed to send reset code. Please try again.' };
    } catch (e) {
      return { error: "Could not connect to the server. Please check your connection." };
    }
  }

  /**
   * Update user password (must be authenticated first).
   * Returns { error }.
   */
  async function updatePassword(newPassword) {
    const sb = getClient();
    const { error } = await sb.auth.updateUser({ password: newPassword });
    if (error) return { error: error.message };
    return { error: null };
  }

  /**
   * Update profile row in the profiles table.
   * Returns { error }.
   */
  async function updateProfile(userId, updates) {
    const sb = getClient();
    const { error } = await sb.from('profiles').update(updates).eq('id', userId);
    if (error) return { error: error.message };
    return { error: null };
  }

  /**
   * Update auth user metadata (user_metadata) so changes persist for
   * _loadProfile()-style readers that pull from the session user object.
   * Returns { user, error }.
   */
  async function updateMetadata(updates) {
    const sb = getClient();
    const { data, error } = await sb.auth.updateUser({ data: updates });
    if (error) return { user: null, error: error.message };
    return { user: data?.user ?? null, error: null };
  }

  /**
   * Sign in with email + password.
   * Returns { user, error }.
   */
  async function signIn(email, password) {
    const sb = getClient();

    const { data, error } = await sb.auth.signInWithPassword({
      email,
      password
    });

    if (error) return { user: null, error: error.message };
    return { user: data.user, error: null };
  }

  /**
   * Sign in with username: looks up the email via the Supabase RPC,
   * then signs in with that email.
   */
  async function signInWithUsername(username, password) {
    const sb = getClient();

    const { data: email, error: rpcError } = await sb.rpc('get_email_by_username', {
      p_username: username
    });

    if (rpcError || !email) {
      return { user: null, error: 'No account found with that username.' };
    }

    return signIn(email, password);
  }

  /**
   * Sign out the current user.
   */
  async function signOut() {
    const sb = getClient();
    const { error } = await sb.auth.signOut();
    return { error: error ? error.message : null };
  }

  /**
   * Get the current session. Returns { session, user } or nulls.
   */
  async function getSession() {
    const sb = getClient();
    const { data: { session }, error } = await sb.auth.getSession();
    if (error) return { session: null, user: null };
    return { session, user: session?.user ?? null };
  }

  /**
   * Get the current user (sync, from stored session).
   * Returns user object or null.
   */
  async function getUser() {
    const { user } = await getSession();
    return user;
  }

  /**
   * Extract display name from a user object.
   * Prefers first_name + last_name, falls back to username, then email prefix.
   */
  function displayName(user) {
    if (!user) return '';
    const m = user.user_metadata || {};
    if (m.first_name) {
      return (m.first_name + ' ' + (m.last_name || '')).trim();
    }
    return m.username || user.email?.split('@')[0] || 'Trader';
  }

  /**
   * Listen for auth state changes. Returns unsubscribe function.
   */
  function onAuthStateChange(callback) {
    const sb = getClient();
    const { data: { subscription } } = sb.auth.onAuthStateChange(callback);
    return () => subscription.unsubscribe();
  }

  /**
   * Centralized Membership Validation
   */
  async function getMembershipStatus(user, session) {
    let result = { level: 'limited', plans: [], subscriptions: [], subscription_plan: null, subscription_expiry: null, days_remaining: 0 };
    if (!user) return result;
    const permanentEmails = ['bonfacewana3072@gmail.com','langatgift6@gmail.com','gackstoneb@gmail.com'];
    if (permanentEmails.includes((user.email || '').trim().toLowerCase())) {
        return {
            level: 'full',
            plans: ['all', 'elite_elite', 'copytrading', 'grid_lifetime'],
            subscriptions: [{
                id: 'perm-admin-access',
                plan: 'elite_elite',
                plan_name: 'BM Elites — Permanent Admin Access',
                status: 'active',
                starts_at: '2024-01-01T00:00:00Z',
                expires_at: '2036-12-31T23:59:59Z',
                amount_usd: 10000
            }],
            subscription_plan: 'BM Elites — Permanent Admin Access',
            subscription_expiry: '2036-12-31T23:59:59Z',
            days_remaining: 3650,
            permanent_access: true
        };
    }

    try {
      const token = session ? session.access_token : '';
      const resp = await fetch((typeof API_BASE !== 'undefined' ? API_BASE : '') + '/api/subscription-status.php', {
        headers: { 'Authorization': 'Bearer ' + token }
      });
      if (resp.ok) {
        const data = await resp.json();
        result.plans = data.active_plans || [];
        result.subscriptions = data.subscriptions || [];
        result.subscription_plan = data.subscription_plan || null;
        result.subscription_expiry = data.subscription_expiry || null;
        result.days_remaining = data.days_remaining || 0;
        result.raw = data;

        if (data.premium) {
          result.level = 'full';
          return result;
        }
        if (data.trial_active) {
          window._trialDaysLeft = data.trial_days_left;
          window._trialHoursLeft = data.trial_hours_left || 0;
          if (data.trial_started) {
            window._trialEndTs = new Date(data.trial_started).getTime() + (3 * 86400000);
          }
          result.level = 'trial';
          return result;
        }
        return result;
      }
      return await clientSideTrialCheck(user);
    } catch (e) {
      return await clientSideTrialCheck(user);
    }
  }

  async function clientSideTrialCheck(user) {
    let result = { level: 'limited', plans: [] };
    try {
      const sb = getClient();
      const now = new Date().toISOString();
      const { data: subs, error: subErr } = await sb.from('subscriptions')
        .select('id,plan,status,expires_at')
        .eq('user_id', user.id)
        .eq('status', 'active')
        .gt('expires_at', now)
        .order('expires_at', { ascending: false });
      if (!subErr && subs && subs.length > 0) {
          result.level = 'full';
          result.plans = subs.map(function(s){ return s.plan; });
          return result;
      }

      const { data: prof, error: profErr } = await sb.from('profiles')
        .select('trial_started_at')
        .eq('id', user.id)
        .single();
      if (profErr) return result;

      let trialStarted = prof ? prof.trial_started_at : null;
      if (!trialStarted) {
        const nowISO = new Date().toISOString();
        const { error: updateErr } = await sb.from('profiles').update({ trial_started_at: nowISO }).eq('id', user.id);
        if (updateErr) return result;
        trialStarted = nowISO;
      }

      const trialEnd = new Date(trialStarted).getTime() + (3 * 86400000);
      const nowTs = Date.now();
      if (nowTs < trialEnd) {
        var rem = trialEnd - nowTs;
        window._trialDaysLeft = Math.max(0, Math.floor(rem / 86400000));
        window._trialHoursLeft = Math.max(0, Math.floor((rem % 86400000) / 3600000));
        window._trialEndTs = trialEnd;
        result.level = 'trial';
        return result;
      }
      return result;
    } catch(e) {
      return result;
    }
  }

  /**
   * Sign in / Sign up using Google OAuth.
   * Redirects user to Google OAuth consent screen, then back to target URL.
   * Returns { data, error }.
   */
  async function signInWithGoogle(redirectTo) {
    const sb = getClient();
    const targetUrl = redirectTo || (window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/index.php'));
    const { data, error } = await sb.auth.signInWithOAuth({
      provider: 'google',
      options: {
        redirectTo: targetUrl
      }
    });
    if (error) {
      return { data: null, error: error.message || 'Unable to connect to Google. Please try again.' };
    }
    return { data, error: null };
  }

  /**
   * Sync Google OAuth profile metadata into the profiles table.
   * Auto-populates first_name, last_name, avatar_url, and email.
   */
  async function syncGoogleProfile(user) {
    if (!user) return { error: null };
    const sb = getClient();
    const meta = user.user_metadata || {};
    const fullName = meta.full_name || meta.name || '';
    const parts = fullName.trim().split(' ');
    const firstName = meta.first_name || parts[0] || user.email?.split('@')[0] || 'Trader';
    const lastName = meta.last_name || parts.slice(1).join(' ') || '';
    const avatarUrl = meta.avatar_url || meta.picture || '';

    try {
      const { data: existing } = await sb.from('profiles').select('id, first_name').eq('id', user.id).single();
      if (!existing) {
        await sb.from('profiles').insert({
          id: user.id,
          first_name: firstName,
          last_name: lastName,
          email: user.email,
          avatar_url: avatarUrl,
          created_at: new Date().toISOString()
        });
      } else if (avatarUrl && !existing.avatar_url) {
        await sb.from('profiles').update({ avatar_url: avatarUrl }).eq('id', user.id);
      }
    } catch(e) {
      console.warn('Google profile sync note:', e);
    }
    return { error: null };
  }

  return {
    getClient: getClient,
    signUp: signUp,
    resendSignup,
    verifyOtp,
    resetPasswordOtp,
    updatePassword,
    updateProfile,
    signIn: signIn,
    signInWithUsername: signInWithUsername,
    signInWithGoogle: signInWithGoogle,
    syncGoogleProfile: syncGoogleProfile,
    signOut: signOut,
    getSession: getSession,
    getUser,
    displayName,
    onAuthStateChange,
    getMembershipStatus: getMembershipStatus
  };
})();
