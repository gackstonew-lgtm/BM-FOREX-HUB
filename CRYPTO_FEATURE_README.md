# Crypto Buy/Sell Widget — Setup & Status

## Update (this revision): Kotani Pay wired in as the crypto settlement provider

This closes the gap described in the "Update (previous revision)"
section below: Kora only ever processed the KES side of a BUY
(M-Pesa/card pay-in). Actually delivering crypto to a buyer, or
receiving crypto from a seller, needed a separate, documented crypto
settlement provider — Kotani Pay is now that provider, matching this
flow:

    BUY:  KES -> Kora Pay-in (fiat collected) -> payment confirmed
          -> Kotani Pay ONRAMP delivers crypto to the buyer's wallet
    SELL: Kotani Pay OFFRAMP issues a deposit address -> customer sends
          crypto -> deposit confirmed -> Kotani Pay disburses KES to
          the customer's mobile money/bank

**New files:**
- `app/Services/Contracts/CryptoSettlementProviderInterface.php` — the
  contract any crypto settlement provider must implement.
- `app/Services/KotaniPayService.php` — the real Kotani Pay v3 API
  client (onramp for BUY delivery, offramp for SELL deposit + payout,
  status lookup, webhook signature verification).
- `app/Services/KotaniCryptoCapabilityService.php` — mirrors
  `KoraCryptoCapabilityService`'s honest, fail-closed style: every
  capability is `true` only once `KOTANI_API_KEY` is actually
  configured, `false` otherwise. Never assumes credentials exist.
- `api/kotani-webhook.php` — receives onramp/offramp status callbacks
  and drives `crypto_transactions` from `PAYMENT_CONFIRMED` /
  `SETTLEMENT_PENDING` through to `COMPLETED` or `FAILED`.
- `supabase/add-kotani-provider.sql` — widens the dormant
  `crypto_wallets.provider` check constraint to allow `'kotani'`
  alongside `'kora'`, and adds a `settlement_provider` audit column to
  `crypto_transactions`.

**Changed files:**
- `app/Services/CryptoSettlementService.php` — `settleBuy()` /
  `initiateSell()` now call into `KotaniPayService` when configured;
  falls back to the old, honest `SETTLEMENT_PENDING` behavior
  otherwise (via `KoraCryptoCapabilityService`, unchanged).
- `api/crypto-buy.php` — now collects and validates a
  `destination_address` (the wallet the buyer wants their crypto sent
  to). Required once Kotani is configured; optional otherwise, so
  nothing breaks mid-setup.
- `api/crypto-sell.php` — now returns `deposit_address` / `network` in
  its response once Kotani issues one, so the customer knows where to
  send their crypto.
- `crypto.php`, `js/crypto-widget.js`, `css/crypto-widget.css` — added
  a wallet-address input for BUY and a deposit-address display panel
  for SELL.
- `admin/api/crypto-capability.php`, `admin/crypto.php` — the
  "Settlement Capability" panel now reports both providers (Kotani and
  Kora) so admins see which one is actually active.
- `.env.example` — new `KOTANI_*` variables (see below).

### Setting it up

1. Create an account at [integrator.kotanipay.com](https://integrator.kotanipay.com).
2. Call `POST /auth/login` with your integrator email to get a
   magic-link-issued JWT, then `GET /auth/api-key` with that JWT to
   mint a long-lived API key.
3. Set in `.env`:
   ```
   KOTANI_API_KEY=<your key>
   KOTANI_ENV=sandbox        # or "production" once ready to go live
   KOTANI_WEBHOOK_SECRET=<from your Kotani dashboard's webhook settings>
   ```
4. Run `supabase/add-kotani-provider.sql` (after the two migrations
   below it in this document, if you haven't already run them).
5. Register `https://<your-domain>/api/kotani-webhook.php` as the
   webhook/callback URL in your Kotani integrator dashboard.
6. Confirm which network(s) Kotani supports for USDT on your account
   (the code defaults to `CELO` — see `KOTANI_DEFAULT_NETWORK_USDT` in
   `.env.example`) and enable the matching row in
   `crypto_network_config` once confirmed.

**Before going live**, verify the exact JSON field names Kotani's
onramp/offramp "create" endpoints expect against your own Postman
collection or dashboard — `KotaniPayService.php` marks every place
this matters with a `TODO` comment. The endpoint paths, base URLs, and
Bearer-token auth model are taken directly from Kotani's published v3
documentation; only the field-level payload shape needs a final check,
since that occasionally varies by account/version.

---

## Update (previous revision): capability layer, wallet/network schema, one bug fix

- **Confirmed against Kora's live documentation** (developers.korapay.com):
  Kora's public API today covers Pay-ins, Payouts, Balance, and
  Identity/KYC. There is **no documented crypto/stablecoin wallet
  endpoint** — the `POST /merchant/api/v1/crypto/wallets` call referenced
  in earlier planning notes is not part of Kora's published API and was
  never called by this codebase. Nothing was implemented against it.
- Added `app/Services/KoraCryptoCapabilityService.php` — the single
  place that answers "can we actually do X?" (wallet creation/retrieval,
  crypto deposit, crypto withdrawal, crypto settlement, crypto tx
  status). Every capability is `false` today, with the reason recorded
  in the file. `CryptoSettlementService` now asks this service instead
  of hardcoding its own `false`.
- Added `admin/api/crypto-capability.php` + a "Settlement Capability"
  panel on `admin/crypto.php` so admins see the live, honest status
  instead of having to read source code.
- Added `supabase/add-crypto-wallets-and-networks.sql` — **dormant**
  schema (`crypto_wallets`, `crypto_network_config`, plus `network` /
  `wallet_address` / `destination_address` / `blockchain_tx_hash`
  columns on `crypto_transactions`) so wallets/networks (USDT over
  TRX/ETH/SOL) can be turned on later without another migration. Run it
  after `add-crypto-tables.sql`; it changes nothing on its own.
- **Bug fix:** `.env.example` had `KORA_BASE_URL=https://api.korapay.com/merchant`,
  but `KoraPaymentService` already appends `/merchant/api/v1/...` to
  whatever base URL it's given — using the example value verbatim would
  have produced a doubled `/merchant/merchant/...` path and 404'd every
  request. Fixed to `https://api.korapay.com`. If your live `.env`
  already has the `/merchant` suffix, remove it.
- New env vars added, all inert by design: `CRYPTO_SETTLEMENT_ENABLED`,
  `CRYPTO_USDT_TRX_ENABLED`, `CRYPTO_USDT_ETH_ENABLED`,
  `CRYPTO_USDT_SOL_ENABLED`. None of them unlock real functionality —
  see the comments in `.env.example` and `KoraCryptoCapabilityService`.

---


This feature was added to the top of the member dashboard (`index.php`),
above the existing Market Overview / Signals / Charts. Nothing existing
was removed, redesigned, or rerouted — this document only covers what's new.

## 1. Run the database migration

In the Supabase SQL editor, run:

    supabase/add-crypto-tables.sql

This creates `crypto_transactions` (transaction history/status) and
`crypto_asset_config` (which assets/fiats are actually enabled — editable
from `admin/crypto.php`). It does not touch `payments`, `subscriptions`,
or any existing table.

Then run:

    supabase/add-crypto-wallets-and-networks.sql

This adds the dormant `crypto_wallets` / `crypto_network_config` tables
and a few nullable columns to `crypto_transactions` (`network`,
`wallet_address`, `destination_address`, `blockchain_tx_hash`). Nothing
in this second migration is read by any active code path yet — see the
"Update" section above.

## 2. Set environment variables

Added to `.env` (see `.env.example` for the full list):

    CRYPTO_QUOTE_SECRET=<a long random string>
    CRYPTO_BUY_FEE_PERCENT=1.5
    CRYPTO_SELL_FEE_PERCENT=1.5

`CRYPTO_QUOTE_SECRET` should be replaced with a real random value before
go-live (a placeholder was added automatically so nothing breaks, but it
falls back to `KORA_SECRET_KEY` either way if left unset).

## 3. What works right now

- Live prices (CoinGecko, cached ~20s) power the "Popular Crypto" ticker
  and the buy/sell quote — nothing is hard-coded.
- Quotes are signed and expire after 30 seconds (shown as a countdown);
  the backend re-checks the live price again at purchase time and
  rejects a quote if the market moved more than ~3% or it expired.
- BUY (KES → crypto): the existing `KoraPaymentService` — the same one
  `api/deposit.php` uses for subscriptions — collects the fiat payment
  (M-Pesa or card). The existing `api/kora-webhook.php` now also
  recognizes `CRYPTO_`-prefixed references and updates
  `crypto_transactions` instead of `payments`/`subscriptions`, so the
  subscription flow is completely untouched.
- Only `USDT` and `KES` are enabled by default (see step 4). Everything
  else in `app/Config/crypto.php` (USDC, BTC, ETH, SOL, XRP, DOGE, TRX,
  LTC) is wired into the price feed and selector already, ready to
  switch on the moment settlement supports them.
- Admins can toggle which assets/sides are enabled at `admin/crypto.php`
  and see live transaction status there.

## 4. What is intentionally NOT done — and what's needed to finish it

**Crypto settlement/delivery is not connected.** Nothing in the existing
codebase confirms that Kora can send crypto to a customer's wallet, or
receive crypto from one — that's a different capability from the fiat
payment processing Kora already does for subscriptions. Rather than
fake it, every BUY sits at `SETTLEMENT_PENDING` after payment is
confirmed, showing "**Payment received — crypto delivery pending**",
and every SELL sits at `SETTLEMENT_PENDING` from the start ("crypto
sell is not yet available for payout"). See
`app/Services/CryptoSettlementService.php` and
`app/Services/KoraCryptoCapabilityService.php` — together they're the
single, isolated place this plugs into once one of the following exists:

- **Kora publishes a documented crypto/stablecoin API.** As of this
  writing, Kora's public docs (developers.korapay.com) do not expose
  one — this has been checked directly, not assumed. If/when they do,
  update `KoraCryptoCapabilityService` with real checks against it; **or**
- **Connect a separate crypto liquidity/custody provider** — API
  key/secret, a funded wallet/liquidity per supported asset (BM Forex
  Hub needs to actually be able to send what it sells), a deposit-
  address mechanism + on-chain confirmation monitoring for SELL, and
  whatever KYC that provider requires.

Once either is wired into `settleBuy()` / `initiateSell()` in that one
file, no other file needs to change — the API layer and frontend
already treat "pending" vs "completed" correctly.

## 5. Deliberately out of scope (per the brief)

No futures, leverage, margin, perpetuals, liquidation engine, order
book, or matching engine was added — this is buy/sell only, exactly as
requested. A full exchange would be a separate phase.

## 6. Suggested manual test pass before go-live

- BUY with KES → USDT (small amount), both M-Pesa and card
- Invalid / zero / negative / very large amount
- Let a quote expire, confirm "Refresh Quote" is required
- Trigger a failed Kora payment and confirm status becomes `FAILED`
- Replay a webhook payload twice and confirm no duplicate transaction
  is created (idempotency)
- Mobile widths (widget stacks vertically, no horizontal scroll)
- Confirm the rest of the dashboard (signals, charts, currency
  strength, economic calendar, admin panel, login/logout, existing
  subscription purchase) is unaffected
