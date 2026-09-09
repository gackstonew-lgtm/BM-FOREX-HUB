  <!-- Copy Traders Section -->
  <section class="section" id="sectionCopyTraders" style="display:none">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(22,199,132,0.15);color:#16C784">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="copyTraderStatActive">0</div>
          <div class="stat-label">Active Copy Traders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(240,180,41,0.15);color:#F0B429">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="copyTraderStatPending">0</div>
          <div class="stat-label">Pending MT5 Accounts</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(22,119,255,0.15);color:#1677FF">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="copyTraderStatTotal">0</div>
          <div class="stat-label">Total Subscriptions Logged</div>
        </div>
      </div>
    </div>

    <div class="users-panel">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">Copy Trading Investor Accounts &amp; MT5 Management</h3>
          <p style="font-size:0.78rem; color:#7f8b99; margin:4px 0 0;">Manage automated copy trading subscriptions, MT5 broker connectivity, and secure account credentials.</p>
        </div>
        <div class="panel-tools">
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="copyTraderSearchInput" placeholder="Search trader, email, broker, login..." autocomplete="off">
          </div>
          <select id="copyTraderFilterStatus" class="select-sm" style="background:#202B3A;border:1px solid #283548;color:#fff;padding:6px 12px;border-radius:6px;font-size:0.78rem;outline:none;">
            <option value="all">All Statuses</option>
            <option value="active">Active MT5 Connected</option>
            <option value="pending">Pending MT5 Setup</option>
            <option value="awaiting">Awaiting MT5 Submission</option>
            <option value="cancelled">Cancelled / Inactive</option>
          </select>
          <button class="btn-primary-sm" id="grantCopyTraderModalBtn" style="background:linear-gradient(135deg,#1677FF,#0D47A1);display:inline-flex;align-items:center;gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Grant Copy Trading
          </button>
          <button class="btn-secondary-admin" id="copyTraderRefreshBtn" style="padding:6px 12px;font-size:0.78rem;" title="Refresh list">
            &#x21bb; Refresh
          </button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Trader</th>
              <th>Plan</th>
              <th>Broker &amp; Server</th>
              <th>MT5 Login</th>
              <th>Password</th>
              <th>MT5 Status</th>
              <th>Expires At</th>
              <th>Granted By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="copyTraderTableBody">
            <tr><td colspan="9" class="table-empty">Loading Copy Traders...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <span class="table-count" id="copyTraderShowingCount">0 traders</span>
      </div>
    </div>

    <!-- Modal: Grant Copy Trading -->
    <div class="modal-overlay" id="grantCopyTraderModal">
      <div class="modal modal-grant">
        <div class="modal-grant-header">
          <button class="modal-close" id="grantCopyTraderModalClose" aria-label="Close modal">&times;</button>
          <div class="modal-grant-title-wrap">
            <div class="modal-grant-icon" style="background:rgba(22,199,132,0.15);color:#16C784;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
            </div>
            <div>
              <h3 class="modal-grant-title">Grant Copy Trading Plan</h3>
              <p class="modal-grant-subtitle">Manually allocate Copy Trading Integration access and optionally link MT5 account</p>
            </div>
          </div>
        </div>

        <form id="grantCopyTraderForm">
          <div class="modal-grant-body">
            <div class="grant-alert" id="grantCopyTraderAlert"></div>

            <div class="grant-form-group">
              <label class="grant-label" for="grantCopyTraderUsername">
                <span>Target User (Username or Email)</span>
                <span class="grant-label-optional" style="color:var(--blue);font-weight:600;">* Required</span>
              </label>
              <div class="grant-input-wrap">
                <span class="grant-input-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input type="text" id="grantCopyTraderUsername" class="grant-input has-icon" list="grantUserList" required placeholder="Enter username or email address..." autocomplete="off">
              </div>
              <span class="grant-hint">User must be registered on BM Forex Hub</span>
            </div>

            <div class="grant-form-group">
              <label class="grant-label" for="grantCopyTraderDuration">
                <span>Access Duration (Days)</span>
                <span class="grant-label-optional" style="color:var(--blue);font-weight:600;">* Required</span>
              </label>
              <input type="number" id="grantCopyTraderDuration" class="grant-input" min="1" max="36500" value="36500" required>
              <div class="grant-preset-pills">
                <button type="button" class="grant-preset-btn ct-preset" data-days="30">30 Days</button>
                <button type="button" class="grant-preset-btn ct-preset" data-days="90">90 Days</button>
                <button type="button" class="grant-preset-btn ct-preset" data-days="365">1 Year</button>
                <button type="button" class="grant-preset-btn ct-preset active" data-days="36500">Lifetime</button>
              </div>
            </div>

            <div style="border-top:1px solid #283548;margin:16px 0;padding-top:14px;">
              <h4 style="font-size:0.82rem;font-weight:600;color:#fff;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                Optional: Pre-configure MT5 Broker Credentials
              </h4>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group" style="margin-bottom:8px;">
                  <label style="font-size:0.75rem;color:#7F8B99;">Broker Name</label>
                  <input type="text" id="grantCtBroker" placeholder="e.g. Exness, IC Markets">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                  <label style="font-size:0.75rem;color:#7F8B99;">MT5 Server</label>
                  <input type="text" id="grantCtServer" placeholder="e.g. Exness-MT5Real7">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                  <label style="font-size:0.75rem;color:#7F8B99;">MT5 Login Account</label>
                  <input type="text" id="grantCtLogin" placeholder="e.g. 10293847">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                  <label style="font-size:0.75rem;color:#7F8B99;">MT5 Master Password</label>
                  <input type="password" id="grantCtPassword" placeholder="Enter password">
                </div>
              </div>
            </div>

            <div class="grant-form-group">
              <label class="grant-label" for="grantCopyTraderNotes">
                <span>Admin Notes</span>
                <span class="grant-label-optional">Optional</span>
              </label>
              <textarea id="grantCopyTraderNotes" class="grant-textarea" rows="2" placeholder="e.g. Granted via VIP package, custom broker agreement..."></textarea>
            </div>
          </div>

          <div class="modal-grant-footer">
            <button type="button" class="btn-grant-cancel" id="grantCopyTraderCancelBtn">Cancel</button>
            <button type="submit" class="btn-grant-submit" id="grantCopyTraderSubmitBtn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
              <span>Grant Copy Trading</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Manage MT5 Account / View Credentials -->
    <div class="modal-overlay" id="manageCopyTraderModal">
      <div class="modal" style="max-width:560px;">
        <button class="modal-close" id="manageCopyTraderModalClose">&times;</button>
        <h3 class="modal-title" style="display:flex;align-items:center;gap:8px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
          Manage Copy Trader Account
        </h3>
        <p style="font-size:0.78rem;color:#7F8B99;margin:-6px 0 16px;">View broker connection details, manage credentials, and update MT5 synchronization status.</p>

        <form id="manageCopyTraderForm">
          <input type="hidden" id="manageCtId">
          <input type="hidden" id="manageCtUserId">

          <div class="form-group">
            <label>Trader Name &amp; Email</label>
            <input type="text" id="manageCtTraderInfo" readonly style="background:#151D29;color:#fff;border-color:#283548;cursor:default;">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label>Broker Name</label>
              <input type="text" id="manageCtBroker" placeholder="e.g. Exness, XM, FXTM" required>
            </div>
            <div class="form-group">
              <label>MT5 Server</label>
              <input type="text" id="manageCtServer" placeholder="e.g. Exness-MT5Real" required>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label>MT5 Login Number</label>
              <div style="display:flex;gap:6px;">
                <input type="text" id="manageCtLogin" placeholder="e.g. 10928374" required style="flex:1;">
                <button type="button" class="btn-secondary-admin" id="manageCtCopyLoginBtn" title="Copy Login" style="padding:0 10px;">📋</button>
              </div>
            </div>
            <div class="form-group">
              <label>MT5 Password</label>
              <div style="display:flex;gap:6px;">
                <input type="password" id="manageCtPassword" placeholder="••••••••••••" style="flex:1;">
                <button type="button" class="btn-secondary-admin" id="manageCtRevealPassBtn" title="Reveal / Copy Password" style="padding:0 10px;font-size:0.8rem;">👁️ Reveal</button>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>MT5 Account Status</label>
            <select id="manageCtStatus" required style="background:#202B3A;border:1px solid #283548;color:#fff;padding:8px 12px;border-radius:6px;width:100%;">
              <option value="Active">Active (Connected &amp; Trading)</option>
              <option value="Pending">Pending (Awaiting Verification/Connection)</option>
              <option value="Disconnected">Disconnected (Invalid Credentials / Server Error)</option>
              <option value="Cancelled">Cancelled (Access Revoked)</option>
            </select>
          </div>

          <div class="form-group">
            <label>Internal Notes &amp; Logs</label>
            <textarea id="manageCtNotes" rows="3" placeholder="Notes on account connection, risk settings, lot sizing..." style="background:#202B3A;border:1px solid #283548;color:#fff;padding:8px 12px;border-radius:6px;width:100%;font-size:0.85rem;"></textarea>
          </div>

          <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button type="button" class="btn-secondary-admin" id="manageCtCloseBtn">Cancel</button>
            <button type="submit" class="btn-primary-sm" id="manageCtSubmitBtn" style="padding:8px 18px;">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </section>