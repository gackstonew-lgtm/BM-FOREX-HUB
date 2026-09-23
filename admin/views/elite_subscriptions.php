  <!-- Elite Subscriptions Section -->
  <section class="section" id="sectionEliteSubscriptions" style="display:none">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(240,180,41,0.15);color:#F0B429">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="eliteStatActive">0</div>
          <div class="stat-label">Active Elite Members</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--teal">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="eliteStatCapital">$0</div>
          <div class="stat-label">Total Allocated Capital (USD)</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(22,119,255,0.15);color:#1677FF">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-info">
          <div class="stat-value" id="eliteStatTotal">0</div>
          <div class="stat-label">Total Subscriptions Logged</div>
        </div>
      </div>
    </div>

    <div class="users-panel">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">BM Elites Trading Circle Subscriptions</h3>
          <p style="font-size:0.78rem; color:#7f8b99; margin:4px 0 0;">Manage active and historical investor memberships, capital allocation, and grant access.</p>
        </div>
        <div class="panel-tools">
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="eliteSearchInput" placeholder="Search member, email or plan..." autocomplete="off">
          </div>
          <select id="eliteFilterStatus" class="select-sm" style="background:#202B3A;border:1px solid #283548;color:#fff;padding:6px 12px;border-radius:6px;font-size:0.78rem;outline:none;">
            <option value="all">All Statuses</option>
            <option value="active">Active Only</option>
            <option value="expired">Expired</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button class="btn-primary-sm" id="grantEliteModalBtn" style="background:linear-gradient(135deg,#1677FF,#0D47A1);display:inline-flex;align-items:center;gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Grant Elite Plan
          </button>
          <button class="btn-secondary-admin" id="eliteRefreshBtn" style="padding:6px 12px;font-size:0.78rem;" title="Refresh list">
            &#x21bb; Refresh
          </button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Member</th>
              <th>Tier Plan</th>
              <th>Capital USD</th>
              <th>Amount KES</th>
              <th>Start Date</th>
              <th>Expires At</th>
              <th>Status</th>
              <th>Granted By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="eliteTableBody">
            <tr><td colspan="9" class="table-empty">Loading Elite members...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <span class="table-count" id="eliteShowingCount">0 members</span>
      </div>
    </div>

    <!-- Elite Circle Electronic Enrollments & Terms Acceptance Audit Trail -->
    <div class="users-panel" style="margin-top:24px;">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">BM Elites — Electronic Enrollment &amp; Terms Acceptance Audit Trail</h3>
          <p style="font-size:0.78rem; color:#7f8b99; margin:4px 0 0;">Mandatory legal records, ID verification, authoritative server timestamps, and 4-month cycle records.</p>
        </div>
        <div class="panel-tools">
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="eliteEnrollSearchInput" placeholder="Search name, ID, email, country..." autocomplete="off">
          </div>
          <button class="btn-secondary-admin" id="eliteEnrollRefreshBtn" style="padding:6px 12px;font-size:0.78rem;" title="Refresh list">
            &#x21bb; Refresh
          </button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Member Name</th>
              <th>ID / Passport No.</th>
              <th>Email &amp; Phone</th>
              <th>Country</th>
              <th>Intended Capital</th>
              <th>Cycle (Start &rarr; End)</th>
              <th>Terms Version</th>
              <th>Accepted Timestamp</th>
              <th>Notification</th>
            </tr>
          </thead>
          <tbody id="eliteEnrollTableBody">
            <tr><td colspan="9" class="table-empty">Loading enrollment records...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <span class="table-count" id="eliteEnrollShowingCount">0 enrollments</span>
      </div>
    </div>
  </section>
