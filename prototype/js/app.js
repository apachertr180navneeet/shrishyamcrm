/**
 * Shri Shyam Welfare Society ERP - Main Application Controller & UI Renderer
 */

const App = {
  currentView: 'dashboard',
  currentMemberDetailId: null,
  currentAgentDetailId: null,
  currentReportType: 'collection',
  wizardData: {},

  init() {
    this.bindEvents();
    this.renderCurrentRoleUI();
    this.navigateTo(this.currentView);
  },

  bindEvents() {
    // Role switcher dropdown
    const roleSelect = document.getElementById('globalRoleSwitcher');
    if (roleSelect) {
      roleSelect.addEventListener('change', (e) => {
        State.currentRole = e.target.value;
        this.renderCurrentRoleUI();
        Utils.showToast(`Switched view role to: ${State.currentRole}`, 'info');
        this.navigateTo(this.currentView);
      });
    }

    // Sidebar navigation clicks
    document.querySelectorAll('[data-view]').forEach(item => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        const view = item.getAttribute('data-view');
        this.navigateTo(view);

        // Mobile drawer close
        document.querySelector('.app-sidebar').classList.remove('show-mobile');
      });
    });

    // Mobile sidebar toggle
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        document.querySelector('.app-sidebar').classList.toggle('show-mobile');
      });
    }
  },

  renderCurrentRoleUI() {
    const role = State.currentRole;
    const badge = document.getElementById('displayRoleBadge');
    if (badge) badge.innerText = role;

    const banner = document.getElementById('agentViewBanner');
    if (banner) {
      if (role === 'Agent') banner.classList.remove('hidden');
      else banner.classList.add('hidden');
    }

    // Filter sidebar navigation items based on role
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
      const view = item.getAttribute('data-view');
      let allowed = true;

      if (role === 'Agent') {
        allowed = ['dashboard', 'members', 'add-member', 'payments', 'payment-entry', 'receipts', 'ledger', 'events', 'payouts', 'whatsapp'].includes(view);
      } else if (role === 'Data Entry Operator') {
        allowed = ['dashboard', 'members', 'add-member', 'payments', 'payment-entry', 'receipts', 'ledger', 'events', 'payouts', 'whatsapp'].includes(view);
      } else if (role === 'Accountant') {
        allowed = ['dashboard', 'payments', 'payment-entry', 'receipts', 'ledger', 'reports', 'payouts'].includes(view);
      }

      item.style.display = allowed ? 'flex' : 'none';
    });
  },

  navigateTo(view, paramId = null) {
    this.currentView = view;

    // Highlight sidebar active link
    document.querySelectorAll('.nav-item').forEach(el => {
      if (el.getAttribute('data-view') === view) el.classList.add('active');
      else el.classList.remove('active');
    });

    const mainContainer = document.getElementById('view-content');
    if (!mainContainer) return;

    // Render corresponding view
    switch (view) {
      case 'dashboard':
        this.renderDashboard(mainContainer);
        break;
      case 'schemes':
        this.renderSchemesMaster(mainContainer);
        break;
      case 'age-slabs':
        this.renderAgeSlabsMaster(mainContainer);
        break;
      case 'members':
        this.renderMembersList(mainContainer);
        break;
      case 'add-member':
        this.renderAddMemberWizard(mainContainer);
        break;
      case 'member-detail':
        this.currentMemberDetailId = paramId || this.currentMemberDetailId || State.getMembers()[0].id;
        this.renderMemberDetail(mainContainer, this.currentMemberDetailId);
        break;
      case 'agents':
        this.renderAgentsList(mainContainer);
        break;
      case 'agent-detail':
        this.currentAgentDetailId = paramId || this.currentAgentDetailId || State.getAgents()[0].id;
        this.renderAgentDetail(mainContainer, this.currentAgentDetailId);
        break;
      case 'payments':
        this.renderPaymentsList(mainContainer);
        break;
      case 'payment-entry':
        this.renderPaymentEntry(mainContainer, paramId);
        break;
      case 'receipts':
        this.renderReceiptsList(mainContainer);
        break;
      case 'ledger':
        this.renderLedgerView(mainContainer, paramId);
        break;
      case 'certificates':
        this.renderCertificatesList(mainContainer);
        break;
      case 'events':
        this.renderEventsList(mainContainer);
        break;
      case 'payouts':
        this.renderPayoutsManager(mainContainer);
        break;
      case 'whatsapp':
        this.renderWhatsAppCenter(mainContainer);
        break;
      case 'reports':
        this.renderReportsModule(mainContainer, paramId || 'collection');
        break;
      case 'users':
        this.renderUsersPermissions(mainContainer);
        break;
      case 'settings':
        this.renderSettingsView(mainContainer);
        break;
      default:
        this.renderDashboard(mainContainer);
    }

    window.scrollTo(0, 0);
  },

  // =========================================================================
  // 1. DASHBOARD VIEW
  // =========================================================================
  renderDashboard(container) {
    const members = State.getMembers();
    const agents = State.getAgents();
    const payments = State.getPayments();
    const events = State.getEvents();

    const totalMembers = members.length;
    const activeMembers = members.filter(m => m.status === 'Active').length;
    const inactiveMembers = members.filter(m => m.status === 'Inactive').length;
    const totalAgents = agents.length;
    const todayCollection = 42500;
    const monthCollection = payments.reduce((sum, p) => sum + p.amount, 0) + 250000;
    const pendingPaymentsCount = members.filter(m => m.pendingAmount > 0).length;
    const pendingAmountSum = members.reduce((sum, m) => sum + m.pendingAmount, 0);

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>प्रशासनिक डैशबोर्ड <span class="hi-subtitle">Shri Shyam Welfare Society ERP</span></h1>
          <div class="page-subtitle">Real-time administration, collection statistics, event billing and payout pool summary</div>
        </div>
        <div class="quick-actions-bar">
          <button class="btn btn-primary" onclick="App.openEventBillingModal()"><i class="fas fa-calculator"></i> Event Monthly Billing</button>
          <button class="btn btn-warning" onclick="App.openPartialPaymentModal()"><i class="fas fa-cash-register"></i> Record Partial Payment</button>
          <button class="btn btn-gold" onclick="App.openPayoutModal()"><i class="fas fa-hand-holding-usd"></i> Disburse Payout</button>
          <button class="btn btn-success btn-whatsapp" onclick="App.openWhatsAppModal('REC-2026-5001')"><i class="fab fa-whatsapp"></i> Send WhatsApp Receipt</button>
        </div>
      </div>

      <!-- KPI Cards Grid -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Total Members</span>
            <span class="kpi-value">${Utils.formatNumber(totalMembers)}</span>
            <span class="kpi-subtext positive"><i class="fas fa-arrow-up"></i> +12% from last month</span>
          </div>
          <div class="kpi-icon blue"><i class="fas fa-users"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Active Members</span>
            <span class="kpi-value">${Utils.formatNumber(activeMembers)}</span>
            <span class="kpi-subtext positive"><i class="fas fa-check-circle"></i> ${Math.round(activeMembers / totalMembers * 100)}% Active Ratio</span>
          </div>
          <div class="kpi-icon green"><i class="fas fa-user-check"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Inactive Members</span>
            <span class="kpi-value">${Utils.formatNumber(inactiveMembers)}</span>
            <span class="kpi-subtext"><i class="fas fa-user-clock"></i> Requires follow-up</span>
          </div>
          <div class="kpi-icon amber"><i class="fas fa-user-minus"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Total Agents</span>
            <span class="kpi-value">${Utils.formatNumber(totalAgents)}</span>
            <span class="kpi-subtext positive"><i class="fas fa-building"></i> Across 4 Districts</span>
          </div>
          <div class="kpi-icon purple"><i class="fas fa-user-tie"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Today's Collection</span>
            <span class="kpi-value">${Utils.formatCurrency(todayCollection)}</span>
            <span class="kpi-subtext positive"><i class="fas fa-coins"></i> 14 Receipts today</span>
          </div>
          <div class="kpi-icon green"><i class="fas fa-rupee-sign"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">This Month Collection</span>
            <span class="kpi-value">${Utils.formatCurrency(monthCollection)}</span>
            <span class="kpi-subtext positive"><i class="fas fa-chart-line"></i> Target 92% achieved</span>
          </div>
          <div class="kpi-icon blue"><i class="fas fa-wallet"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Pending Payments</span>
            <span class="kpi-value">${Utils.formatCurrency(pendingAmountSum)}</span>
            <span class="kpi-subtext"><i class="fas fa-exclamation-triangle"></i> ${pendingPaymentsCount} Members overdue</span>
          </div>
          <div class="kpi-icon orange"><i class="fas fa-clock"></i></div>
        </div>

        <div class="kpi-card">
          <div class="kpi-info">
            <span class="kpi-label">Total Events</span>
            <span class="kpi-value">${events.length}</span>
            <span class="kpi-subtext positive"><i class="fas fa-calendar-check"></i> 1 Event upcoming</span>
          </div>
          <div class="kpi-icon amber"><i class="fas fa-calendar-alt"></i></div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="charts-grid">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line text-blue"></i> Monthly Collection Trend (Last 12 Months)</div>
          </div>
          <div class="chart-card-body">
            <canvas id="chartMonthlyCollection"></canvas>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-user-plus text-amber"></i> New Member Registrations</div>
          </div>
          <div class="chart-card-body">
            <canvas id="chartNewMembers"></canvas>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-pie-chart text-purple"></i> Scheme-wise Member Distribution</div>
          </div>
          <div class="chart-card-body">
            <canvas id="chartSchemeDistribution"></canvas>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-bar-chart text-green"></i> Top Agent Collections</div>
          </div>
          <div class="chart-card-body">
            <canvas id="chartAgentCollections"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Transactions Table -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-history text-blue"></i> Recent Payment Receipts</div>
          <button class="btn btn-secondary btn-sm" onclick="App.navigateTo('payments')">View All Payments</button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Receipt No</th>
                  <th>Date</th>
                  <th>Member Name</th>
                  <th>Scheme</th>
                  <th>Agent</th>
                  <th>Amount</th>
                  <th>Mode</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                ${payments.slice(0, 7).map(p => `
                  <tr>
                    <td><strong class="text-blue">${p.receiptNo}</strong></td>
                    <td>${Utils.formatDate(p.paymentDate)}</td>
                    <td><a href="#" onclick="App.navigateTo('member-detail', '${p.membershipNo}')"><strong>${p.memberName}</strong></a></td>
                    <td><span class="badge ${p.schemeName.includes('बुजुर्ग') ? 'badge-senior' : 'badge-marriage'}">${p.schemeName}</span></td>
                    <td>${p.agentName}</td>
                    <td><strong>${Utils.formatCurrency(p.amount)}</strong></td>
                    <td><span class="badge badge-active">${p.paymentMode}</span></td>
                    <td>
                      <button class="btn btn-secondary btn-sm" onclick="App.openReceiptModal('${p.receiptNo}')"><i class="fas fa-print"></i> Receipt</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;

    setTimeout(() => ChartsManager.initAll(), 100);
  },

  // =========================================================================
  // 2. SCHEMES & AGE SLABS MASTER
  // =========================================================================
  renderSchemesMaster(container) {
    const schemes = State.getSchemes();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>योजना प्रबंधन <span class="hi-subtitle">Membership Scheme Master</span></h1>
          <div class="page-subtitle">Configurable society membership schemes, age slabs and contribution amounts</div>
        </div>
        <button class="btn btn-primary" onclick="App.navigateTo('age-slabs')"><i class="fas fa-sliders-h"></i> Configure Age Slabs</button>
      </div>

      <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;">
        ${schemes.map(s => `
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i class="fas ${s.code === 'SENIOR' ? 'fa-user-nurse text-blue' : 'fa-female text-amber'}"></i> 
                ${s.nameHindi}
              </div>
              <span class="badge badge-active">${s.status}</span>
            </div>
            <div class="card-body">
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">${s.description}</p>
              <div style="background: var(--bg-app); padding: 0.85rem; border-radius: var(--radius-md); font-size: 0.82rem; margin-bottom: 1rem;">
                <div><strong>Effective Period:</strong> ${Utils.formatDate(s.effectiveFrom)} to ${Utils.formatDate(s.effectiveTo)}</div>
                <div><strong>Active Enrolled Members:</strong> ${s.membersCount} Members</div>
              </div>

              <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--primary-navy);">Configured Age Slabs:</h4>
              <div class="table-responsive">
                <table class="data-table" style="font-size: 0.78rem;">
                  <thead>
                    <tr>
                      <th>Age Bracket</th>
                      <th>Joining Fee</th>
                      <th>Support Amt</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${s.ageSlabs.map(sl => `
                      <tr>
                        <td><strong>${sl.minAge} – ${sl.maxAge} Years</strong></td>
                        <td>${Utils.formatCurrency(sl.joiningAmount)}</td>
                        <td>${Utils.formatCurrency(sl.supportAmount)}/mo</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  },

  renderAgeSlabsMaster(container) {
    const schemes = State.getSchemes();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>आयु स्लैब विन्यास <span class="hi-subtitle">Age Slabs Configuration</span></h1>
          <div class="page-subtitle">Dynamic age slabs & contribution amounts editor for society schemes</div>
        </div>
        <button class="btn btn-primary" onclick="App.openAddAgeSlabModal()"><i class="fas fa-plus-circle"></i> Add New Age Slab</button>
      </div>

      ${schemes.map(s => `
        <div class="card">
          <div class="card-header">
            <div class="card-title">${s.nameHindi} (${s.name})</div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Min Age</th>
                    <th>Max Age</th>
                    <th>Joining Amount</th>
                    <th>Support Amount</th>
                    <th>Effective From</th>
                    <th>Effective To</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  ${s.ageSlabs.map(sl => `
                    <tr>
                      <td><strong>${sl.minAge} Yrs</strong></td>
                      <td><strong>${sl.maxAge} Yrs</strong></td>
                      <td><span class="text-blue font-bold">${Utils.formatCurrency(sl.joiningAmount)}</span></td>
                      <td><span class="text-amber font-bold">${Utils.formatCurrency(sl.supportAmount)}</span></td>
                      <td>${Utils.formatDate(sl.effectiveFrom)}</td>
                      <td>${Utils.formatDate(sl.effectiveTo)}</td>
                      <td><span class="badge badge-active">${sl.status}</span></td>
                      <td>
                        <button class="btn btn-secondary btn-sm" onclick="Utils.showToast('Age slab configuration saved!','success')"><i class="fas fa-edit"></i> Edit</button>
                      </td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `).join('')}
    `;
  },

  // =========================================================================
  // 3. MEMBERS MANAGEMENT & LIST
  // =========================================================================
  renderMembersList(container) {
    const members = State.getMembers();
    const agents = State.getAgents();
    const schemes = State.getSchemes();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>सदस्य प्रबंधन <span class="hi-subtitle">Society Members Directory</span></h1>
          <div class="page-subtitle">Manage enrolled members, registration records, scheme payments & status</div>
        </div>
        <button class="btn btn-primary" onclick="App.navigateTo('add-member')"><i class="fas fa-user-plus"></i> Add New Member</button>
      </div>

      <!-- Filters Bar -->
      <div class="filter-bar">
        <div class="filter-item" style="flex: 1; min-width: 200px;">
          <label>Search Member</label>
          <input type="text" id="memberSearchInput" placeholder="Name, Mobile, Member No..." oninput="App.filterMembersTable()">
        </div>

        <div class="filter-item">
          <label>Scheme Filter</label>
          <select id="memberSchemeFilter" onchange="App.filterMembersTable()">
            <option value="ALL">All Schemes</option>
            ${schemes.map(s => `<option value="${s.id}">${s.nameHindi}</option>`).join('')}
          </select>
        </div>

        <div class="filter-item">
          <label>Agent Filter</label>
          <select id="memberAgentFilter" onchange="App.filterMembersTable()">
            <option value="ALL">All Agents</option>
            ${agents.map(a => `<option value="${a.id}">${a.name}</option>`).join('')}
          </select>
        </div>

        <div class="filter-item">
          <label>Status Filter</label>
          <select id="memberStatusFilter" onchange="App.filterMembersTable()">
            <option value="ALL">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Pending">Pending</option>
            <option value="Suspended">Suspended</option>
            <option value="Deceased">Deceased</option>
          </select>
        </div>
      </div>

      <!-- Members Table -->
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table" id="membersTable">
              <thead>
                <tr>
                  <th>Membership No</th>
                  <th>Photo</th>
                  <th>Member Name</th>
                  <th>Father / Husband Name</th>
                  <th>Age</th>
                  <th>Mobile</th>
                  <th>Scheme</th>
                  <th>Agent</th>
                  <th>Joining Date</th>
                  <th>Joining Amt</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="membersTableBody">
                ${this.buildMembersRows(members)}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  buildMembersRows(membersList) {
    return membersList.map(m => {
      let badgeClass = 'badge-active';
      if (m.status === 'Inactive') badgeClass = 'badge-inactive';
      if (m.status === 'Pending') badgeClass = 'badge-pending';
      if (m.status === 'Suspended') badgeClass = 'badge-suspended';

      return `
        <tr>
          <td><strong class="text-blue">${m.membershipNo}</strong></td>
          <td><img src="${m.photoUrl}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid #CBD5E1;"></td>
          <td>
            <a href="#" onclick="App.navigateTo('member-detail', '${m.id}')"><strong>${m.name}</strong></a>
            <div style="font-size:0.7rem; color:var(--text-muted);">${m.gotra ? 'Gotra: ' + m.gotra : ''}</div>
          </td>
          <td>${m.fatherHusbandName}</td>
          <td><strong>${m.age} Yrs</strong></td>
          <td>${m.mobile}</td>
          <td><span class="badge ${m.schemeCode === 'SENIOR' ? 'badge-senior' : 'badge-marriage'}">${m.schemeName}</span></td>
          <td>${m.agentName}</td>
          <td>${Utils.formatDate(m.joiningDate)}</td>
          <td><strong>${Utils.formatCurrency(m.joiningAmount)}</strong></td>
          <td><span class="badge ${badgeClass}">${m.status}</span></td>
          <td>
            <div style="display:flex; gap:0.25rem;">
              <button class="btn btn-secondary btn-sm" title="View Profile" onclick="App.navigateTo('member-detail', '${m.id}')"><i class="fas fa-eye"></i></button>
              <button class="btn btn-gold btn-sm" title="Add Payment" onclick="App.navigateTo('payment-entry', '${m.membershipNo}')"><i class="fas fa-rupee-sign"></i></button>
              <button class="btn btn-secondary btn-sm" title="Print Certificate" onclick="App.openCertificateModal('${m.membershipNo}')"><i class="fas fa-certificate"></i></button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  },

  filterMembersTable() {
    const q = (document.getElementById('memberSearchInput').value || '').toLowerCase();
    const scheme = document.getElementById('memberSchemeFilter').value;
    const agent = document.getElementById('memberAgentFilter').value;
    const status = document.getElementById('memberStatusFilter').value;

    const filtered = State.getMembers().filter(m => {
      const matchQ = m.name.toLowerCase().includes(q) || m.mobile.includes(q) || m.membershipNo.toLowerCase().includes(q);
      const matchScheme = scheme === 'ALL' || m.schemeId === scheme;
      const matchAgent = agent === 'ALL' || m.agentId === agent;
      const matchStatus = status === 'ALL' || m.status === status;
      return matchQ && matchScheme && matchAgent && matchStatus;
    });

    document.getElementById('membersTableBody').innerHTML = this.buildMembersRows(filtered);
  },

  // =========================================================================
  // 4. ADD MEMBER MULTI-STEP WIZARD (STEPS 1 - 5)
  // =========================================================================
  renderAddMemberWizard(container) {
    const autoMemNo = `MEM-2026-${1000 + State.getMembers().length + 1}`;
    const agents = State.getAgents();
    const schemes = State.getSchemes();

    this.wizardData = {
      step: 1,
      membershipNo: autoMemNo,
      joiningDate: Utils.toInputDate(new Date())
    };

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>नया सदस्य पंजीकरण <span class="hi-subtitle">New Member Registration Wizard</span></h1>
          <div class="page-subtitle">5-Step official society membership enrolment & payment form</div>
        </div>
      </div>

      <div class="wizard-container">
        <!-- Stepper Header -->
        <div class="wizard-stepper">
          <div class="wizard-step active" id="step-tab-1">
            <div class="step-number">1</div>
            <div class="step-label-box">
              <span class="step-title">Basic Details</span>
              <span class="step-subtext">व्यक्तिगत जानकारी</span>
            </div>
          </div>

          <div class="wizard-step" id="step-tab-2">
            <div class="step-number">2</div>
            <div class="step-label-box">
              <span class="step-title">Documents</span>
              <span class="step-subtext">दस्तावेज़ एवं फोटो</span>
            </div>
          </div>

          <div class="wizard-step" id="step-tab-3">
            <div class="step-number">3</div>
            <div class="step-label-box">
              <span class="step-title">Nominee Details</span>
              <span class="step-subtext">वारिसदार विवरण</span>
            </div>
          </div>

          <div class="wizard-step" id="step-tab-4">
            <div class="step-number">4</div>
            <div class="step-label-box">
              <span class="step-title">Scheme Selection</span>
              <span class="step-subtext">योजना एवं आयु स्लैब</span>
            </div>
          </div>

          <div class="wizard-step" id="step-tab-5">
            <div class="step-number">5</div>
            <div class="step-label-box">
              <span class="step-title">Payment & Submit</span>
              <span class="step-subtext">शुल्क भुगतान एवं रसीद</span>
            </div>
          </div>
        </div>

        <!-- Wizard Form Container -->
        <div class="card-body">
          <form id="addMemberWizardForm">
            <!-- STEP 1: Basic Details -->
            <div class="wizard-pane active" id="pane-step-1">
              <h3 style="color:var(--primary-navy); font-size:1.05rem; margin-bottom:1.25rem;">Step 1: Member Personal Information / सदस्य व्यक्तिगत विवरण</h3>
              
              <div class="form-grid">
                <div class="form-group">
                  <label>Membership Number (Auto Generated)</label>
                  <input type="text" class="form-control" id="wiz_membershipNo" value="${autoMemNo}" readonly>
                </div>

                <div class="form-group">
                  <label>Full Member Name / नाम <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_name" placeholder="e.g. Rameshwar Sharma" required>
                </div>

                <div class="form-group">
                  <label>Father's / Husband's Name <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_fatherHusbandName" placeholder="e.g. S/o Shri Banwari Lal" required>
                </div>

                <div class="form-group">
                  <label>Mother's Name / माता का नाम</label>
                  <input type="text" class="form-control" id="wiz_motherName" placeholder="e.g. Shanti Devi">
                </div>

                <div class="form-group">
                  <label>Date of Birth / जन्म तिथि <span class="required">*</span></label>
                  <input type="date" class="form-control" id="wiz_dob" onchange="App.onDobChange()" required>
                </div>

                <div class="form-group">
                  <label>Calculated Age (Yrs)</label>
                  <input type="number" class="form-control" id="wiz_calculatedAge" value="0" readonly>
                </div>

                <div class="form-group">
                  <label>Gender / लिंग <span class="required">*</span></label>
                  <select class="form-control" id="wiz_gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Mobile Number / मोबाइल <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_mobile" placeholder="10 Digit Mobile Number" maxlength="10" required>
                </div>

                <div class="form-group">
                  <label>Alternate Mobile</label>
                  <input type="text" class="form-control" id="wiz_altMobile" placeholder="Optional mobile number">
                </div>

                <div class="form-group">
                  <label>Caste / जाति</label>
                  <input type="text" class="form-control" id="wiz_caste" placeholder="e.g. Brahmin, Yadav, Saini">
                </div>

                <div class="form-group">
                  <label>Gotra / गौत्र</label>
                  <input type="text" class="form-control" id="wiz_gotra" placeholder="e.g. Kaushik, Vats, Bhardwaj">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                  <label>Full Residential Address / पता <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_address" placeholder="House No, Village/Street, Post Office" required>
                </div>

                <div class="form-group">
                  <label>District / जिला</label>
                  <input type="text" class="form-control" id="wiz_district" value="Mahendragarh">
                </div>

                <div class="form-group">
                  <label>State / राज्य</label>
                  <input type="text" class="form-control" id="wiz_state" value="Haryana">
                </div>

                <div class="form-group">
                  <label>PIN Code / पिन कोड</label>
                  <input type="text" class="form-control" id="wiz_pinCode" value="123001">
                </div>
              </div>
            </div>

            <!-- STEP 2: Documents Upload -->
            <div class="wizard-pane" id="pane-step-2" style="display:none;">
              <h3 style="color:var(--primary-navy); font-size:1.05rem; margin-bottom:1.25rem;">Step 2: Identity Documents & Photograph / पहचान दस्तावेज़ एवं फोटो</h3>

              <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                  <label>Aadhaar Card Number / आधार नंबर <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_aadhaarNo" placeholder="12 Digit Aadhaar Number (e.g. 4829 1000 2000)">
                </div>

                <div class="form-group">
                  <label>Member Passport Photograph</label>
                  <div class="doc-upload-box" onclick="Utils.showToast('Simulated image selected!','info')">
                    <i class="fas fa-camera fa-2x text-blue"></i>
                    <p style="font-size:0.78rem; margin-top:0.35rem;">Click to upload Member Photo</p>
                  </div>
                </div>

                <div class="form-group">
                  <label>Aadhaar Card Front Document</label>
                  <div class="doc-upload-box" onclick="Utils.showToast('Aadhaar front attached!','info')">
                    <i class="fas fa-id-card fa-2x text-amber"></i>
                    <p style="font-size:0.78rem; margin-top:0.35rem;">Click to upload Aadhaar Front</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 3: Nominees (Dual Nominees) -->
            <div class="wizard-pane" id="pane-step-3" style="display:none;">
              <h3 style="color:var(--primary-navy); font-size:1.05rem; margin-bottom:1.25rem;">Step 3: Nominee / वारिसदार विवरण (Dual Nominees Supported)</h3>

              <h4 style="font-size:0.9rem; color:var(--primary-blue); margin-bottom:0.75rem;">First Nominee (प्रथम वारिसदार):</h4>
              <div class="form-grid" style="margin-bottom:1.5rem;">
                <div class="form-group">
                  <label>Nominee 1 Name <span class="required">*</span></label>
                  <input type="text" class="form-control" id="wiz_nom1_name" placeholder="Full name of Nominee 1">
                </div>

                <div class="form-group">
                  <label>Relation / संबंध <span class="required">*</span></label>
                  <select class="form-control" id="wiz_nom1_relation">
                    <option value="Son">Son (पुत्र)</option>
                    <option value="Daughter">Daughter (पुत्री)</option>
                    <option value="Wife">Wife (पत्नी)</option>
                    <option value="Husband">Husband (पति)</option>
                    <option value="Father">Father (पिता)</option>
                    <option value="Mother">Mother (माता)</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Mobile Number</label>
                  <input type="text" class="form-control" id="wiz_nom1_mobile" placeholder="Mobile number">
                </div>

                <div class="form-group">
                  <label>Aadhaar / ID No</label>
                  <input type="text" class="form-control" id="wiz_nom1_aadhaar" placeholder="Nominee Aadhaar number">
                </div>
              </div>

              <h4 style="font-size:0.9rem; color:var(--accent-gold); margin-bottom:0.75rem;">Second Nominee (द्वितीय वारिसदार):</h4>
              <div class="form-grid">
                <div class="form-group">
                  <label>Nominee 2 Name</label>
                  <input type="text" class="form-control" id="wiz_nom2_name" placeholder="Full name of Nominee 2">
                </div>

                <div class="form-group">
                  <label>Relation / संबंध</label>
                  <select class="form-control" id="wiz_nom2_relation">
                    <option value="Daughter-in-Law">Daughter-in-Law (पुत्रवधू)</option>
                    <option value="Son">Son (पुत्र)</option>
                    <option value="Daughter">Daughter (पुत्री)</option>
                    <option value="Grandson">Grandson (पोता)</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Mobile Number</label>
                  <input type="text" class="form-control" id="wiz_nom2_mobile" placeholder="Mobile number">
                </div>

                <div class="form-group">
                  <label>Aadhaar / ID No</label>
                  <input type="text" class="form-control" id="wiz_nom2_aadhaar" placeholder="Nominee Aadhaar number">
                </div>
              </div>
            </div>

            <!-- STEP 4: Scheme & Auto Age Slab Calculation -->
            <div class="wizard-pane" id="pane-step-4" style="display:none;">
              <h3 style="color:var(--primary-navy); font-size:1.05rem; margin-bottom:1.25rem;">Step 4: Scheme Enrolment & Dynamic Age Slab / योजना चयन</h3>

              <div class="form-grid">
                <div class="form-group">
                  <label>Select Membership Scheme / योजना <span class="required">*</span></label>
                  <select class="form-control" id="wiz_schemeId" onchange="App.recalculateSchemeSlab()">
                    ${schemes.map(s => `<option value="${s.id}">${s.nameHindi} (${s.name})</option>`).join('')}
                  </select>
                </div>

                <div class="form-group">
                  <label>Enrolment Date / तिथि</label>
                  <input type="date" class="form-control" id="wiz_joiningDate" value="${Utils.toInputDate(new Date())}">
                </div>

                <div class="form-group">
                  <label>Assigned Society Agent / एजेंट <span class="required">*</span></label>
                  <select class="form-control" id="wiz_agentId">
                    ${agents.map(a => `<option value="${a.id}">${a.name} (${a.code}) - ${a.district}</option>`).join('')}
                  </select>
                </div>

                <!-- Auto calculated slab display -->
                <div class="form-group" style="grid-column: span 3; background: #EFF6FF; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid #BFDBFE;">
                  <h4 style="color:#1E3A8A; font-size:0.95rem; margin-bottom:0.5rem;"><i class="fas fa-calculator"></i> System Auto-Calculated Fee & Support Slab</h4>
                  <div style="display:flex; gap:2rem; font-size:0.9rem;">
                    <div>Calculated Age: <strong id="lbl_calcAge">0 Yrs</strong></div>
                    <div>Applicable Age Bracket: <strong id="lbl_calcSlab">None</strong></div>
                    <div>Required Joining Fee: <strong id="lbl_calcJoiningAmt" style="color:var(--primary-blue); font-size:1.1rem;">₹0</strong></div>
                    <div>Monthly Support Amount: <strong id="lbl_calcSupportAmt" style="color:var(--accent-gold); font-size:1.1rem;">₹0</strong></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 5: Initial Payment & Confirmation -->
            <div class="wizard-pane" id="pane-step-5" style="display:none;">
              <h3 style="color:var(--primary-navy); font-size:1.05rem; margin-bottom:1.25rem;">Step 5: Initial Payment & Final Submission / शुल्क भुगतान एवं रसीद</h3>

              <div class="form-grid">
                <div class="form-group">
                  <label>Payment Amount (₹) <span class="required">*</span></label>
                  <input type="number" class="form-control" id="wiz_paymentAmount" value="1100">
                </div>

                <div class="form-group">
                  <label>Payment Mode / भुगतान माध्यम <span class="required">*</span></label>
                  <select class="form-control" id="wiz_paymentMode">
                    <option value="Cash">Cash (नकद)</option>
                    <option value="UPI">UPI / GPay / PhonePe</option>
                    <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                    <option value="Cheque">Cheque (चेक)</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Transaction No. / Cheque No.</label>
                  <input type="text" class="form-control" id="wiz_transactionNo" placeholder="Enter transaction reference">
                </div>

                <div class="form-group" style="grid-column: span 3;">
                  <label>Payment Remarks / टिप्पणी</label>
                  <input type="text" class="form-control" id="wiz_remarks" value="Initial Member Registration & Joining Fee">
                </div>
              </div>

              <!-- Summary Card -->
              <div style="margin-top:1.5rem; background:#FEF3C7; padding:1.25rem; border-radius:var(--radius-md); border:1px solid #FCD34D;">
                <h4 style="color:#78350F; font-size:0.95rem; margin-bottom:0.5rem;"><i class="fas fa-info-circle"></i> Enrolment Summary Confirmation</h4>
                <div id="wiz_summaryText" style="font-size:0.85rem; color:#92400E;">Please verify all member details before generating official receipt & certificate.</div>
              </div>
            </div>

            <!-- Navigation Wizard Buttons -->
            <div style="display:flex; justify-content:space-between; margin-top:2rem; padding-top:1.25rem; border-top:1px solid var(--border-color);">
              <button type="button" class="btn btn-secondary" id="wiz_btnPrev" onclick="App.moveWizard(-1)" style="display:none;"><i class="fas fa-arrow-left"></i> Previous Step</button>
              <div style="display:flex; gap:0.75rem; margin-left:auto;">
                <button type="button" class="btn btn-secondary" onclick="App.navigateTo('members')">Cancel</button>
                <button type="button" class="btn btn-primary" id="wiz_btnNext" onclick="App.moveWizard(1)">Next Step <i class="fas fa-arrow-right"></i></button>
                <button type="button" class="btn btn-gold" id="wiz_btnSubmit" onclick="App.submitMemberRegistration()" style="display:none;"><i class="fas fa-check-circle"></i> Save & Generate Receipt</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    `;
  },

  onDobChange() {
    const dob = document.getElementById('wiz_dob').value;
    const age = Utils.calculateAge(dob);
    document.getElementById('wiz_calculatedAge').value = age;
    this.recalculateSchemeSlab();
  },

  recalculateSchemeSlab() {
    const age = Number(document.getElementById('wiz_calculatedAge').value || 0);
    const schemeId = document.getElementById('wiz_schemeId').value;
    const scheme = State.getSchemes().find(s => s.id === schemeId);

    if (!scheme) return;

    const matchedSlab = scheme.ageSlabs.find(sl => age >= sl.minAge && age <= sl.maxAge) || scheme.ageSlabs[0];

    document.getElementById('lbl_calcAge').innerText = `${age} Years`;
    document.getElementById('lbl_calcSlab').innerText = `${matchedSlab.minAge} – ${matchedSlab.maxAge} Yrs`;
    document.getElementById('lbl_calcJoiningAmt').innerText = Utils.formatCurrency(matchedSlab.joiningAmount);
    document.getElementById('lbl_calcSupportAmt').innerText = `${Utils.formatCurrency(matchedSlab.supportAmount)}/mo`;

    document.getElementById('wiz_paymentAmount').value = matchedSlab.joiningAmount;
  },

  moveWizard(direction) {
    let curr = this.wizardData.step || 1;
    let nextStep = curr + direction;

    if (nextStep < 1 || nextStep > 5) return;

    // Validate current step
    if (direction > 0 && curr === 1) {
      const name = document.getElementById('wiz_name').value;
      const dob = document.getElementById('wiz_dob').value;
      const mobile = document.getElementById('wiz_mobile').value;
      if (!name || !dob || !mobile) {
        Utils.showToast('Please fill all required member details!', 'error');
        return;
      }
    }

    this.wizardData.step = nextStep;

    for (let i = 1; i <= 5; i++) {
      document.getElementById(`step-tab-${i}`).classList.remove('active');
      document.getElementById(`pane-step-${i}`).style.display = 'none';
      if (i < nextStep) document.getElementById(`step-tab-${i}`).classList.add('completed');
    }

    document.getElementById(`step-tab-${nextStep}`).classList.add('active');
    document.getElementById(`pane-step-${nextStep}`).style.display = 'block';

    document.getElementById('wiz_btnPrev').style.display = nextStep > 1 ? 'inline-flex' : 'none';
    document.getElementById('wiz_btnNext').style.display = nextStep < 5 ? 'inline-flex' : 'none';
    document.getElementById('wiz_btnSubmit').style.display = nextStep === 5 ? 'inline-flex' : 'none';

    if (nextStep === 4) this.recalculateSchemeSlab();
    if (nextStep === 5) {
      const name = document.getElementById('wiz_name').value;
      const schemeId = document.getElementById('wiz_schemeId').value;
      const scheme = State.getSchemes().find(s => s.id === schemeId);
      const amt = document.getElementById('wiz_paymentAmount').value;
      document.getElementById('wiz_summaryText').innerHTML = `
        Registering <strong>${name}</strong> into <strong>${scheme ? scheme.nameHindi : ''}</strong> with Joining Fee of <strong>${Utils.formatCurrency(amt)}</strong>.
      `;
    }
  },

  submitMemberRegistration() {
    const schemeId = document.getElementById('wiz_schemeId').value;
    const scheme = State.getSchemes().find(s => s.id === schemeId);
    const agentId = document.getElementById('wiz_agentId').value;
    const agent = State.getAgents().find(a => a.id === agentId);
    const dob = document.getElementById('wiz_dob').value;
    const name = document.getElementById('wiz_name').value;
    const joiningAmt = Number(document.getElementById('wiz_paymentAmount').value || 1100);

    const newMember = {
      id: document.getElementById('wiz_membershipNo').value,
      membershipNo: document.getElementById('wiz_membershipNo').value,
      name,
      fatherHusbandName: document.getElementById('wiz_fatherHusbandName').value,
      motherName: document.getElementById('wiz_motherName').value,
      dob,
      age: Utils.calculateAge(dob),
      gender: document.getElementById('wiz_gender').value,
      mobile: document.getElementById('wiz_mobile').value,
      alternateMobile: document.getElementById('wiz_altMobile').value,
      caste: document.getElementById('wiz_caste').value,
      gotra: document.getElementById('wiz_gotra').value,
      address: document.getElementById('wiz_address').value,
      district: document.getElementById('wiz_district').value,
      state: document.getElementById('wiz_state').value,
      pinCode: document.getElementById('wiz_pinCode').value,
      aadhaarNo: document.getElementById('wiz_aadhaarNo').value || '4829 0000 1111',
      photoUrl: Utils.getAvatar(name, document.getElementById('wiz_gender').value),
      schemeId,
      schemeCode: scheme.code,
      schemeName: scheme.nameHindi,
      agentId,
      agentName: agent.name,
      joiningDate: document.getElementById('wiz_joiningDate').value,
      joiningAmount: joiningAmt,
      supportAmount: 200,
      status: 'Active',
      totalPaid: joiningAmt,
      pendingAmount: 0,
      paymentMode: document.getElementById('wiz_paymentMode').value,
      transactionNo: document.getElementById('wiz_transactionNo').value,
      nominees: [
        {
          name: document.getElementById('wiz_nom1_name').value || 'Nominee One',
          relation: document.getElementById('wiz_nom1_relation').value,
          mobile: document.getElementById('wiz_nom1_mobile').value,
          aadhaarNo: document.getElementById('wiz_nom1_aadhaar').value
        }
      ]
    };

    const res = State.addMember(newMember);
    Utils.showToast(`Member ${name} registered successfully! Receipt: ${res.receiptNo}`, 'success');

    // Open Receipt preview modal immediately
    this.openReceiptModal(res.receiptNo);
    this.navigateTo('members');
  },

  // =========================================================================
  // 5. MEMBER DETAIL PROFILE & TABS
  // =========================================================================
  renderMemberDetail(container, memberId) {
    const member = State.getMembers().find(m => m.id === memberId || m.membershipNo === memberId) || State.getMembers()[0];
    const memberPayments = State.getPayments().filter(p => p.membershipNo === member.membershipNo);

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>सदस्य प्रोफ़ाइल <span class="hi-subtitle">${member.name} (${member.membershipNo})</span></h1>
          <div class="page-subtitle">Detailed member profile, nominees, payment history & society certificates</div>
        </div>
        <div class="quick-actions-bar">
          <button class="btn btn-gold" onclick="App.navigateTo('payment-entry', '${member.membershipNo}')"><i class="fas fa-rupee-sign"></i> Record Payment</button>
          <button class="btn btn-secondary" onclick="App.openCertificateModal('${member.membershipNo}')"><i class="fas fa-certificate"></i> Generate Certificate</button>
        </div>
      </div>

      <!-- Profile Header Banner -->
      <div class="profile-header-card">
        <img src="${member.photoUrl}" class="profile-avatar-large">
        <div class="profile-info-main">
          <div class="profile-name">
            ${member.name}
            <span class="profile-mem-no">${member.membershipNo}</span>
            <span class="badge ${member.schemeCode === 'SENIOR' ? 'badge-senior' : 'badge-marriage'}">${member.schemeName}</span>
            <span class="badge badge-active">${member.status}</span>
          </div>
          <div style="margin-top:0.35rem; font-size:0.9rem; opacity:0.9;">
            ${member.fatherHusbandName} | Age: <strong>${member.age} Yrs</strong> | Mobile: <strong>${member.mobile}</strong>
          </div>
          <div class="profile-meta-grid">
            <div class="profile-meta-item"><i class="fas fa-calendar-alt text-amber"></i> Enrolled: ${Utils.formatDate(member.joiningDate)}</div>
            <div class="profile-meta-item"><i class="fas fa-user-tie text-amber"></i> Agent: ${member.agentName}</div>
            <div class="profile-meta-item"><i class="fas fa-wallet text-amber"></i> Total Paid: ${Utils.formatCurrency(member.totalPaid)}</div>
            <div class="profile-meta-item"><i class="fas fa-exclamation-circle text-amber"></i> Pending: ${Utils.formatCurrency(member.pendingAmount)}</div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="tab-nav-bar">
        <button class="tab-btn active" onclick="App.switchProfileTab('overview')"><i class="fas fa-th-large"></i> Overview</button>
        <button class="tab-btn" onclick="App.switchProfileTab('personal')"><i class="fas fa-id-card"></i> Personal Info</button>
        <button class="tab-btn" onclick="App.switchProfileTab('nominees')"><i class="fas fa-users"></i> Nominees (वारिसदार)</button>
        <button class="tab-btn" onclick="App.switchProfileTab('payments')"><i class="fas fa-receipt"></i> Payment History (${memberPayments.length})</button>
      </div>

      <!-- Tab 1: Overview -->
      <div class="tab-pane active" id="tab-overview">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr;">
          <div class="card">
            <div class="card-header"><div class="card-title">Recent Scheme Payments</div></div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="data-table">
                  <thead>
                    <tr><th>Receipt No</th><th>Date</th><th>Amount</th><th>Mode</th><th>Action</th></tr>
                  </thead>
                  <tbody>
                    ${memberPayments.map(p => `
                      <tr>
                        <td><strong>${p.receiptNo}</strong></td>
                        <td>${Utils.formatDate(p.paymentDate)}</td>
                        <td><strong>${Utils.formatCurrency(p.amount)}</strong></td>
                        <td><span class="badge badge-active">${p.paymentMode}</span></td>
                        <td><button class="btn btn-secondary btn-sm" onclick="App.openReceiptModal('${p.receiptNo}')">Print Receipt</button></td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title">Nominee Preview</div></div>
            <div class="card-body">
              ${(member.nominees || []).map((n, idx) => `
                <div style="background:var(--bg-app); padding:0.85rem; border-radius:var(--radius-md); margin-bottom:0.75rem;">
                  <div style="font-weight:700; color:var(--primary-navy);">#${idx + 1} ${n.name}</div>
                  <div style="font-size:0.82rem; color:var(--text-muted);">Relation: <strong>${n.relation}</strong></div>
                  <div style="font-size:0.82rem; color:var(--text-muted);">Mobile: ${n.mobile || 'N/A'}</div>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Personal Info -->
      <div class="tab-pane" id="tab-personal">
        <div class="card">
          <div class="card-body">
            <div class="form-grid">
              <div><strong>Membership No:</strong> ${member.membershipNo}</div>
              <div><strong>Full Name:</strong> ${member.name}</div>
              <div><strong>Father/Husband:</strong> ${member.fatherHusbandName}</div>
              <div><strong>Mother Name:</strong> ${member.motherName || 'N/A'}</div>
              <div><strong>Date of Birth:</strong> ${Utils.formatDate(member.dob)} (${member.age} Yrs)</div>
              <div><strong>Gender:</strong> ${member.gender}</div>
              <div><strong>Mobile:</strong> ${member.mobile}</div>
              <div><strong>Alternate Mobile:</strong> ${member.alternateMobile || 'N/A'}</div>
              <div><strong>Caste:</strong> ${member.caste || 'N/A'}</div>
              <div><strong>Gotra:</strong> ${member.gotra || 'N/A'}</div>
              <div><strong>Address:</strong> ${member.address}, ${member.district}, ${member.state} - ${member.pinCode}</div>
              <div><strong>Aadhaar No:</strong> ${member.aadhaarNo}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 3: Nominees -->
      <div class="tab-pane" id="tab-nominees">
        <div class="card">
          <div class="card-header"><div class="card-title">Enrolled Warisdar (वारिसदार / Nominee Records)</div></div>
          <div class="card-body">
            <div class="form-grid">
              ${(member.nominees || []).map((n, idx) => `
                <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.25rem; background:#FFFFFF;">
                  <h4 style="color:var(--primary-blue); font-size:1rem; margin-bottom:0.5rem;">Nominee #${idx + 1}</h4>
                  <div><strong>Name:</strong> ${n.name}</div>
                  <div><strong>Relation:</strong> ${n.relation}</div>
                  <div><strong>Mobile:</strong> ${n.mobile || 'N/A'}</div>
                  <div><strong>Aadhaar ID:</strong> ${n.aadhaarNo || 'N/A'}</div>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 4: Payments -->
      <div class="tab-pane" id="tab-payments">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr><th>Receipt No</th><th>Date</th><th>Type</th><th>Amount</th><th>Mode</th><th>Txn No</th><th>Actions</th></tr>
                </thead>
                <tbody>
                  ${memberPayments.map(p => `
                    <tr>
                      <td><strong>${p.receiptNo}</strong></td>
                      <td>${Utils.formatDate(p.paymentDate)}</td>
                      <td>${p.paymentType}</td>
                      <td><strong>${Utils.formatCurrency(p.amount)}</strong></td>
                      <td><span class="badge badge-active">${p.paymentMode}</span></td>
                      <td>${p.transactionNo}</td>
                      <td><button class="btn btn-secondary btn-sm" onclick="App.openReceiptModal('${p.receiptNo}')">Print Receipt</button></td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  switchProfileTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

    event.target.classList.add('active');
    const pane = document.getElementById(`tab-${tabName}`);
    if (pane) pane.classList.add('active');
  },

  // =========================================================================
  // 6. AGENTS MANAGEMENT
  // =========================================================================
  renderAgentsList(container) {
    const agents = State.getAgents();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>एजेंट प्रबंधन <span class="hi-subtitle">Society Agents & Commissions</span></h1>
          <div class="page-subtitle">Agent collections, member registrations & commission reports</div>
        </div>
        <button class="btn btn-primary" onclick="Utils.showToast('Add Agent feature opened!','info')"><i class="fas fa-user-plus"></i> Add New Agent</button>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Agent Code</th>
                  <th>Agent Name</th>
                  <th>District</th>
                  <th>Mobile</th>
                  <th>Members Enrolled</th>
                  <th>Total Collection</th>
                  <th>Pending Amt</th>
                  <th>Commission Rate</th>
                  <th>Total Commission</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                ${agents.map(a => `
                  <tr>
                    <td><strong class="text-blue">${a.code}</strong></td>
                    <td><a href="#" onclick="App.navigateTo('agent-detail', '${a.id}')"><strong>${a.name}</strong></a></td>
                    <td>${a.district}</td>
                    <td>${a.mobile}</td>
                    <td><strong>${a.membersCount} Members</strong></td>
                    <td><strong class="text-blue">${Utils.formatCurrency(a.collection)}</strong></td>
                    <td><span class="text-amber">${Utils.formatCurrency(a.pending)}</span></td>
                    <td>${a.commissionRate}%</td>
                    <td><strong class="text-green">${Utils.formatCurrency(a.commission)}</strong></td>
                    <td><span class="badge badge-active">${a.status}</span></td>
                    <td>
                      <button class="btn btn-secondary btn-sm" onclick="App.navigateTo('agent-detail', '${a.id}')"><i class="fas fa-chart-line"></i> View</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  renderAgentDetail(container, agentId) {
    const agent = State.getAgents().find(a => a.id === agentId) || State.getAgents()[0];
    const agentMembers = State.getMembers().filter(m => m.agentId === agent.id);

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>एजेंट विवरण डैशबोर्ड <span class="hi-subtitle">${agent.name} (${agent.code})</span></h1>
          <div class="page-subtitle">Agent collection performance, members list and commission ledger</div>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-info"><span class="kpi-label">Assigned Members</span><span class="kpi-value">${agent.membersCount}</span></div>
          <div class="kpi-icon blue"><i class="fas fa-users"></i></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-info"><span class="kpi-label">Total Collection</span><span class="kpi-value">${Utils.formatCurrency(agent.collection)}</span></div>
          <div class="kpi-icon green"><i class="fas fa-rupee-sign"></i></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-info"><span class="kpi-label">Pending Collection</span><span class="kpi-value">${Utils.formatCurrency(agent.pending)}</span></div>
          <div class="kpi-icon orange"><i class="fas fa-clock"></i></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-info"><span class="kpi-label">Earned Commission (5%)</span><span class="kpi-value">${Utils.formatCurrency(agent.commission)}</span></div>
          <div class="kpi-icon amber"><i class="fas fa-percentage"></i></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Enrolled Members under ${agent.name}</div></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr><th>Membership No</th><th>Member Name</th><th>Mobile</th><th>Scheme</th><th>Joining Date</th><th>Amount</th></tr>
              </thead>
              <tbody>
                ${agentMembers.map(m => `
                  <tr>
                    <td><strong class="text-blue">${m.membershipNo}</strong></td>
                    <td>${m.name}</td>
                    <td>${m.mobile}</td>
                    <td><span class="badge ${m.schemeCode === 'SENIOR' ? 'badge-senior' : 'badge-marriage'}">${m.schemeName}</span></td>
                    <td>${Utils.formatDate(m.joiningDate)}</td>
                    <td><strong>${Utils.formatCurrency(m.joiningAmount)}</strong></td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // 7. PAYMENTS & RECEIPT MANAGEMENT
  // =========================================================================
  renderPaymentsList(container) {
    const payments = State.getPayments();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>भुगतान सूची <span class="hi-subtitle">All Payments & Receipts</span></h1>
          <div class="page-subtitle">Recorded membership fees, support contributions and receipts ledger</div>
        </div>
        <button class="btn btn-primary" onclick="App.navigateTo('payment-entry')"><i class="fas fa-plus-circle"></i> New Payment Entry</button>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Receipt No</th>
                  <th>Payment Date</th>
                  <th>Member Name</th>
                  <th>Membership No</th>
                  <th>Scheme</th>
                  <th>Payment Type</th>
                  <th>Amount</th>
                  <th>Mode</th>
                  <th>Transaction No</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                ${payments.map(p => `
                  <tr>
                    <td><strong class="text-blue">${p.receiptNo}</strong></td>
                    <td>${Utils.formatDate(p.paymentDate)}</td>
                    <td><strong>${p.memberName}</strong></td>
                    <td>${p.membershipNo}</td>
                    <td><span class="badge ${p.schemeName.includes('बुजुर्ग') ? 'badge-senior' : 'badge-marriage'}">${p.schemeName}</span></td>
                    <td>${p.paymentType}</td>
                    <td><strong class="text-green">${Utils.formatCurrency(p.amount)}</strong></td>
                    <td><span class="badge badge-active">${p.paymentMode}</span></td>
                    <td>${p.transactionNo}</td>
                    <td>
                      <button class="btn btn-secondary btn-sm" onclick="App.openReceiptModal('${p.receiptNo}')"><i class="fas fa-print"></i> View Receipt</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  renderPaymentEntry(container, prefillMemNo = '') {
    const members = State.getMembers();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>भुगतान प्रविष्टि <span class="hi-subtitle">Payment Entry Form</span></h1>
          <div class="page-subtitle">Record monthly support contribution or joining fee payment</div>
        </div>
      </div>

      <div class="card" style="max-width:700px; margin:0 auto;">
        <div class="card-header"><div class="card-title"><i class="fas fa-receipt text-blue"></i> Record Society Receipt</div></div>
        <div class="card-body">
          <form id="paymentEntryForm">
            <div class="form-grid">
              <div class="form-group" style="grid-column: span 2;">
                <label>Select Society Member / सदस्य का चयन <span class="required">*</span></label>
                <select class="form-control" id="pay_membershipNo" onchange="App.onPaymentMemberSelect()" required>
                  <option value="">-- Choose Member --</option>
                  ${members.map(m => `
                    <option value="${m.membershipNo}" ${prefillMemNo === m.membershipNo ? 'selected' : ''}>
                      ${m.name} (${m.membershipNo}) - ${m.schemeName}
                    </option>
                  `).join('')}
                </select>
              </div>

              <div class="form-group">
                <label>Scheme Name</label>
                <input type="text" class="form-control" id="pay_schemeName" readonly placeholder="Auto filled">
              </div>

              <div class="form-group">
                <label>Assigned Agent</label>
                <input type="text" class="form-control" id="pay_agentName" readonly placeholder="Auto filled">
              </div>

              <div class="form-group">
                <label>Payment Category / प्रकार</label>
                <select class="form-control" id="pay_paymentType">
                  <option value="Monthly Support Amount">Monthly Support Amount (मासिक सहायता)</option>
                  <option value="Joining Fee">Joining Fee (सदस्यता शुल्क)</option>
                  <option value="Event Contribution">Event Contribution (आयोजन सहयोग)</option>
                </select>
              </div>

              <div class="form-group">
                <label>Amount (₹) <span class="required">*</span></label>
                <input type="number" class="form-control" id="pay_amount" placeholder="e.g. 500" required>
              </div>

              <div class="form-group">
                <label>Payment Date <span class="required">*</span></label>
                <input type="date" class="form-control" id="pay_date" value="${Utils.toInputDate(new Date())}" required>
              </div>

              <div class="form-group">
                <label>Payment Mode / माध्यम <span class="required">*</span></label>
                <select class="form-control" id="pay_mode">
                  <option value="Cash">Cash (नकद)</option>
                  <option value="UPI">UPI / GPay / PhonePe</option>
                  <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                  <option value="Cheque">Cheque (चेक)</option>
                </select>
              </div>

              <div class="form-group" style="grid-column: span 2;">
                <label>Transaction No. / Cheque Ref</label>
                <input type="text" class="form-control" id="pay_txnNo" placeholder="N/A for cash">
              </div>

              <div class="form-group" style="grid-column: span 2;">
                <label>Remarks / टिप्पणी</label>
                <input type="text" class="form-control" id="pay_remarks" value="Monthly scheme support contribution paid.">
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
              <button type="button" class="btn btn-secondary" onclick="App.navigateTo('payments')">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="App.submitPaymentEntry()"><i class="fas fa-check"></i> Record & Print Receipt</button>
            </div>
          </form>
        </div>
      </div>
    `;

    if (prefillMemNo) this.onPaymentMemberSelect();
  },

  onPaymentMemberSelect() {
    const memNo = document.getElementById('pay_membershipNo').value;
    const member = State.getMembers().find(m => m.membershipNo === memNo);

    if (member) {
      document.getElementById('pay_schemeName').value = member.schemeName;
      document.getElementById('pay_agentName').value = member.agentName;
      document.getElementById('pay_amount').value = member.supportAmount || 300;
    }
  },

  submitPaymentEntry() {
    const memNo = document.getElementById('pay_membershipNo').value;
    const amt = document.getElementById('pay_amount').value;
    if (!memNo || !amt) {
      Utils.showToast('Please select member and enter amount!', 'error');
      return;
    }

    const payObj = {
      membershipNo: memNo,
      amount: amt,
      paymentType: document.getElementById('pay_paymentType').value,
      paymentDate: document.getElementById('pay_date').value,
      paymentMode: document.getElementById('pay_mode').value,
      transactionNo: document.getElementById('pay_txnNo').value,
      remarks: document.getElementById('pay_remarks').value
    };

    const res = State.addPayment(payObj);
    Utils.showToast(`Payment recorded successfully! Receipt: ${res.receiptNo}`, 'success');
    this.openReceiptModal(res.receiptNo);
    this.navigateTo('payments');
  },

  // =========================================================================
  // 8. OFFICIAL RECEIPT & CERTIFICATE PRINT MODALS
  // =========================================================================
  renderReceiptsList(container) {
    const receipts = State.getReceipts();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>रसीद प्रबंधन <span class="hi-subtitle">Generated Receipts Ledger</span></h1>
          <div class="page-subtitle">Printable official society receipts and voucher records</div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Receipt No</th>
                  <th>Issue Date</th>
                  <th>Member Name</th>
                  <th>Scheme</th>
                  <th>Amount</th>
                  <th>Payment Mode</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                ${receipts.map(r => `
                  <tr>
                    <td><strong class="text-blue">${r.receiptNo}</strong></td>
                    <td>${Utils.formatDate(r.issueDate || r.paymentDate)}</td>
                    <td><strong>${r.memberName}</strong></td>
                    <td>${r.schemeName}</td>
                    <td><strong class="text-green">${Utils.formatCurrency(r.amount)}</strong></td>
                    <td><span class="badge badge-active">${r.paymentMode}</span></td>
                    <td>
                      <button class="btn btn-primary btn-sm" onclick="App.openReceiptModal('${r.receiptNo}')"><i class="fas fa-print"></i> View / Print</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  renderCertificatesList(container) {
    const certs = State.getCertificates();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>प्रमाण पत्र प्रबंधन <span class="hi-subtitle">Registration Certificates</span></h1>
          <div class="page-subtitle">Official society registration certificates for enrolled members</div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Certificate No</th>
                  <th>Membership No</th>
                  <th>Member Name</th>
                  <th>Father / Husband Name</th>
                  <th>Scheme</th>
                  <th>Issue Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                ${certs.map(c => `
                  <tr>
                    <td><strong class="text-blue">${c.certificateNo}</strong></td>
                    <td>${c.membershipNo}</td>
                    <td><strong>${c.memberName}</strong></td>
                    <td>${c.fatherHusbandName}</td>
                    <td><span class="badge badge-senior">${c.schemeName}</span></td>
                    <td>${Utils.formatDate(c.issueDate)}</td>
                    <td><span class="badge badge-active">${c.status}</span></td>
                    <td>
                      <button class="btn btn-gold btn-sm" onclick="App.openCertificateModal('${c.membershipNo}')"><i class="fas fa-certificate"></i> Print Certificate</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  openReceiptModal(receiptNo) {
    const rec = State.getReceipts().find(r => r.receiptNo === receiptNo) || State.getReceipts()[0];
    const settings = State.data.settings;

    const modalBody = `
      <div class="receipt-paper" id="printableReceiptArea">
        <div class="receipt-header">
          <img src="assets/logo.svg" class="receipt-logo">
          <div class="receipt-header-center">
            <div class="receipt-org-hi">${settings.societyHindiName}</div>
            <div class="receipt-org-en">${settings.societyName}</div>
            <div class="receipt-reg-info">${settings.regNo} | ${settings.sanNo} | ${settings.address}</div>
          </div>
          <div style="text-align:right; font-size:0.78rem;">
            <div><strong>Ph:</strong> ${settings.phone}</div>
            <div><strong>Email:</strong> ${settings.email}</div>
          </div>
        </div>

        <div class="receipt-title-badge">
          <span>आधिकारिक भुगतान रसीद / OFFICIAL PAYMENT RECEIPT</span>
        </div>

        <div class="receipt-meta-grid">
          <div class="receipt-meta-row"><span class="receipt-meta-label">Receipt No:</span><span class="receipt-meta-val">${rec.receiptNo}</span></div>
          <div class="receipt-meta-row"><span class="receipt-meta-label">Receipt Date:</span><span class="receipt-meta-val">${Utils.formatDate(rec.paymentDate)}</span></div>
          <div class="receipt-meta-row"><span class="receipt-meta-label">Member No:</span><span class="receipt-meta-val">${rec.membershipNo}</span></div>
          <div class="receipt-meta-row"><span class="receipt-meta-label">Payment Mode:</span><span class="receipt-meta-val">${rec.paymentMode} (${rec.transactionNo})</span></div>
        </div>

        <table class="receipt-table">
          <thead>
            <tr>
              <th>Member Name</th>
              <th>Enrolled Scheme</th>
              <th>Payment Description</th>
              <th style="text-align:right;">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>${rec.memberName}</strong></td>
              <td>${rec.schemeName}</td>
              <td>${rec.paymentType} - ${rec.remarks}</td>
              <td style="text-align:right; font-weight:700;">${Utils.formatCurrency(rec.amount)}</td>
            </tr>
          </tbody>
        </table>

        <div class="receipt-amount-words">
          <strong>Amount in Words:</strong> ${Utils.amountInWords(rec.amount)}
        </div>

        <div class="receipt-footer-signatures">
          <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-title">Member Signature</div>
          </div>
          <div class="sig-box">
            <div style="font-weight:700; color:var(--primary-navy); margin-bottom:0.25rem;">[ Society Seal Stamp ]</div>
            <div class="sig-line"></div>
            <div class="sig-title">Authorized Secretary Signature</div>
          </div>
        </div>
      </div>
    `;

    this.showModal(`Receipt Preview: ${rec.receiptNo}`, modalBody, [
      { text: 'Print Receipt', class: 'btn-primary', icon: 'fa-print', onclick: () => Utils.printContainer('printableReceiptArea') }
    ]);
  },

  openCertificateModal(membershipNo) {
    const member = State.getMembers().find(m => m.membershipNo === membershipNo || m.id === membershipNo) || State.getMembers()[0];
    const settings = State.data.settings;

    const modalBody = `
      <div class="certificate-paper" id="printableCertificateArea">
        <div class="cert-corner-ornament cert-corner-tl"></div>
        <div class="cert-corner-ornament cert-corner-tr"></div>
        <div class="cert-corner-ornament cert-corner-bl"></div>
        <div class="cert-corner-ornament cert-corner-br"></div>

        <div class="cert-header">
          <img src="assets/logo.svg" class="cert-logo">
          <div class="cert-society-hi">${settings.societyHindiName}</div>
          <div class="cert-society-en">${settings.societyName}</div>
          <div class="cert-reg-line">${settings.regNo} | ${settings.sanNo} | Lohki, Haryana</div>
        </div>

        <div class="cert-main-title">सदस्यता प्रमाण पत्र / MEMBERSHIP CERTIFICATE</div>

        <div class="cert-body-text">
          यह प्रमाणित किया जाता है कि <strong>${member.name}</strong> (${member.fatherHusbandName}), निवासी ${member.address}, श्री श्याम वेलफेयर सोसायटी लोहीकी की <span class="cert-highlight">${member.schemeName}</span> के अंतर्गत स्थायी सदस्य के रूप में सफलतापूर्वक पंजीकृत हैं।
        </div>

        <div class="cert-details-grid">
          <div><strong>Certificate No:</strong> CRT-2026-9021</div>
          <div><strong>Membership No:</strong> ${member.membershipNo}</div>
          <div><strong>Member Name:</strong> ${member.name}</div>
          <div><strong>Date of Birth:</strong> ${Utils.formatDate(member.dob)} (${member.age} Yrs)</div>
          <div><strong>Enrolled Scheme:</strong> ${member.schemeName}</div>
          <div><strong>Issue Date:</strong> ${Utils.formatDate(member.joiningDate)}</div>
        </div>

        <div class="cert-sig-row">
          <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-title">Assigned Agent Signature</div>
          </div>
          <div class="sig-box">
            <div style="font-weight:700; color:var(--accent-gold); margin-bottom:0.2rem;">[ Society Official Gold Seal ]</div>
            <div class="sig-line"></div>
            <div class="sig-title">President / Secretary</div>
          </div>
        </div>
      </div>
    `;

    this.showModal(`Certificate Preview: ${member.membershipNo}`, modalBody, [
      { text: 'Print Certificate', class: 'btn-gold', icon: 'fa-certificate', onclick: () => Utils.printContainer('printableCertificateArea') }
    ]);
  },

  // =========================================================================
  // 9. EVENTS MANAGEMENT
  // =========================================================================
  renderEventsList(container) {
    const events = State.getEvents();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>आयोजन प्रबंधन <span class="hi-subtitle">Society Welfare Events</span></h1>
          <div class="page-subtitle">Welfare functions, health camps, member assemblies & collections</div>
        </div>
        <button class="btn btn-primary" onclick="App.openAddEventModal()"><i class="fas fa-calendar-plus"></i> Create New Event</button>
      </div>

      <div class="card-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem;">
        ${events.map(evt => `
          <div class="card">
            <div class="card-header">
              <div class="card-title"><i class="fas fa-calendar-alt text-amber"></i> ${evt.name}</div>
              <span class="badge ${evt.status === 'Upcoming' ? 'badge-pending' : 'badge-active'}">${evt.status}</span>
            </div>
            <div class="card-body">
              <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">${evt.description}</p>
              <div style="font-size:0.82rem; display:flex; flex-direction:column; gap:0.35rem;">
                <div><i class="fas fa-clock text-blue"></i> <strong>Date:</strong> ${Utils.formatDate(evt.eventDate)}</div>
                <div><i class="fas fa-map-marker-alt text-amber"></i> <strong>Venue:</strong> ${evt.location}</div>
                <div><i class="fas fa-users text-green"></i> <strong>Participants:</strong> ${evt.totalMembers} Members</div>
                <div><i class="fas fa-rupee-sign text-blue"></i> <strong>Event Collection:</strong> ${Utils.formatCurrency(evt.collection)}</div>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  },

  openAddEventModal() {
    const body = `
      <form id="addEventForm">
        <div class="form-grid">
          <div class="form-group" style="grid-column:span 2;">
            <label>Event Name / आयोजन नाम <span class="required">*</span></label>
            <input type="text" class="form-control" id="evt_name" placeholder="e.g. वार्षिक सम्मान समारोह 2026" required>
          </div>

          <div class="form-group">
            <label>Event Type</label>
            <select class="form-control" id="evt_type">
              <option value="Welfare Distribution">Welfare Distribution</option>
              <option value="Marriage Assistance">Marriage Assistance</option>
              <option value="Health Camp">Health Camp</option>
              <option value="General Meeting">General Assembly</option>
            </select>
          </div>

          <div class="form-group">
            <label>Event Date <span class="required">*</span></label>
            <input type="date" class="form-control" id="evt_date" value="${Utils.toInputDate(new Date())}" required>
          </div>

          <div class="form-group" style="grid-column:span 2;">
            <label>Venue Location / स्थान</label>
            <input type="text" class="form-control" id="evt_location" value="Society Registered Office, Lohki">
          </div>

          <div class="form-group" style="grid-column:span 2;">
            <label>Description / विवरण</label>
            <input type="text" class="form-control" id="evt_desc" placeholder="Details about the event">
          </div>
        </div>
      </form>
    `;

    this.showModal('Create New Event', body, [
      {
        text: 'Save Event', class: 'btn-primary', icon: 'fa-check', onclick: () => {
          const name = document.getElementById('evt_name').value;
          if (!name) return Utils.showToast('Please enter event name!', 'error');
          State.addEvent({
            name,
            type: document.getElementById('evt_type').value,
            eventDate: document.getElementById('evt_date').value,
            location: document.getElementById('evt_location').value,
            description: document.getElementById('evt_desc').value
          });
          Utils.showToast('New Event created successfully!', 'success');
          this.closeModal();
          this.navigateTo('events');
        }
      }
    ]);
  },

  // =========================================================================
  // 10. COMPREHENSIVE REPORTS MODULE (8 REPORTS + CSV EXPORT)
  // =========================================================================
  renderReportsModule(container, reportType = 'collection') {
    this.currentReportType = reportType;
    const payments = State.getPayments();
    const members = State.getMembers();
    const agents = State.getAgents();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>रिपोर्ट सेंटर <span class="hi-subtitle">Society Reports & Analytics</span></h1>
          <div class="page-subtitle">Exportable collection reports, agent performance ledgers & pending payments</div>
        </div>
        <div class="quick-actions-bar">
          <button class="btn btn-secondary" onclick="App.exportReportCSV()"><i class="fas fa-file-excel text-green"></i> Export Excel (CSV)</button>
          <button class="btn btn-primary" onclick="Utils.printContainer('reportTableCard')"><i class="fas fa-print"></i> Print Report</button>
        </div>
      </div>

      <!-- Report Selector Tabs -->
      <div class="tab-nav-bar" style="margin-bottom:1.25rem;">
        <button class="tab-btn ${reportType === 'collection' ? 'active' : ''}" onclick="App.renderReportsModule(document.getElementById('view-content'), 'collection')">Collection Report</button>
        <button class="tab-btn ${reportType === 'agent-wise' ? 'active' : ''}" onclick="App.renderReportsModule(document.getElementById('view-content'), 'agent-wise')">Agent-wise Collection</button>
        <button class="tab-btn ${reportType === 'pending' ? 'active' : ''}" onclick="App.renderReportsModule(document.getElementById('view-content'), 'pending')">Pending Payment Report</button>
        <button class="tab-btn ${reportType === 'commission' ? 'active' : ''}" onclick="App.renderReportsModule(document.getElementById('view-content'), 'commission')">Commission Report</button>
        <button class="tab-btn ${reportType === 'member' ? 'active' : ''}" onclick="App.renderReportsModule(document.getElementById('view-content'), 'member')">Member Directory Report</button>
      </div>

      <!-- Report Content Card -->
      <div class="card" id="reportTableCard">
        <div class="card-header">
          <div class="card-title" id="reportTitleHeader"><i class="fas fa-table text-blue"></i> ${reportType.toUpperCase()} REPORT</div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table" id="activeReportTable">
              ${this.buildReportTableHTML(reportType, payments, members, agents)}
            </table>
          </div>
        </div>
      </div>
    `;
  },

  buildReportTableHTML(type, payments, members, agents) {
    if (type === 'collection') {
      return `
        <thead>
          <tr><th>Date</th><th>Receipt No</th><th>Member Name</th><th>Scheme</th><th>Agent</th><th>Amount</th><th>Mode</th></tr>
        </thead>
        <tbody>
          ${payments.map(p => `
            <tr>
              <td>${Utils.formatDate(p.paymentDate)}</td>
              <td><strong>${p.receiptNo}</strong></td>
              <td>${p.memberName}</td>
              <td>${p.schemeName}</td>
              <td>${p.agentName}</td>
              <td><strong>${Utils.formatCurrency(p.amount)}</strong></td>
              <td>${p.paymentMode}</td>
            </tr>
          `).join('')}
        </tbody>
      `;
    } else if (type === 'agent-wise') {
      return `
        <thead>
          <tr><th>Agent Code</th><th>Agent Name</th><th>District</th><th>Enrolled Members</th><th>Total Collection</th><th>Commission (5%)</th></tr>
        </thead>
        <tbody>
          ${agents.map(a => `
            <tr>
              <td><strong>${a.code}</strong></td>
              <td>${a.name}</td>
              <td>${a.district}</td>
              <td>${a.membersCount} Members</td>
              <td><strong class="text-blue">${Utils.formatCurrency(a.collection)}</strong></td>
              <td><strong class="text-green">${Utils.formatCurrency(a.commission)}</strong></td>
            </tr>
          `).join('')}
        </tbody>
      `;
    } else if (type === 'pending') {
      const pendingMembers = members.filter(m => m.pendingAmount > 0);
      return `
        <thead>
          <tr><th>Membership No</th><th>Member Name</th><th>Mobile</th><th>Agent</th><th>Scheme</th><th>Total Paid</th><th>Pending Amount</th></tr>
        </thead>
        <tbody>
          ${pendingMembers.map(m => `
            <tr>
              <td><strong>${m.membershipNo}</strong></td>
              <td>${m.name}</td>
              <td>${m.mobile}</td>
              <td>${m.agentName}</td>
              <td>${m.schemeName}</td>
              <td>${Utils.formatCurrency(m.totalPaid)}</td>
              <td><strong class="text-amber">${Utils.formatCurrency(m.pendingAmount)}</strong></td>
            </tr>
          `).join('')}
        </tbody>
      `;
    } else if (type === 'commission') {
      return `
        <thead>
          <tr><th>Agent Name</th><th>Collection Amt</th><th>Commission Rate</th><th>Commission Payable</th></tr>
        </thead>
        <tbody>
          ${agents.map(a => `
            <tr>
              <td><strong>${a.name}</strong></td>
              <td>${Utils.formatCurrency(a.collection)}</td>
              <td>5%</td>
              <td><strong class="text-green">${Utils.formatCurrency(a.commission)}</strong></td>
            </tr>
          `).join('')}
        </tbody>
      `;
    } else {
      return `
        <thead>
          <tr><th>Membership No</th><th>Member Name</th><th>DOB (Age)</th><th>Mobile</th><th>Scheme</th><th>Agent</th><th>Joining Date</th><th>Status</th></tr>
        </thead>
        <tbody>
          ${members.map(m => `
            <tr>
              <td><strong>${m.membershipNo}</strong></td>
              <td>${m.name}</td>
              <td>${m.dob} (${m.age} Yrs)</td>
              <td>${m.mobile}</td>
              <td>${m.schemeName}</td>
              <td>${m.agentName}</td>
              <td>${Utils.formatDate(m.joiningDate)}</td>
              <td><span class="badge badge-active">${m.status}</span></td>
            </tr>
          `).join('')}
        </tbody>
      `;
    }
  },

  exportReportCSV() {
    const table = document.getElementById('activeReportTable');
    if (!table) return;

    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.innerText.trim()));

    const rows = [];
    table.querySelectorAll('tbody tr').forEach(tr => {
      const row = [];
      tr.querySelectorAll('td').forEach(td => row.push(td.innerText.trim()));
      rows.push(row);
    });

    Utils.exportToCSV(`SSWS_${this.currentReportType}_Report.csv`, headers, rows);
    Utils.showToast('CSV Report downloaded successfully!', 'success');
  },

  // =========================================================================
  // 11. USERS, ROLES & PERMISSIONS
  // =========================================================================
  renderUsersPermissions(container) {
    const users = State.data.users;

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>उपयोगकर्ता एवं अधिकार <span class="hi-subtitle">Users & Roles Permission Matrix</span></h1>
          <div class="page-subtitle">Access control, system users and role permissions configuration</div>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><div class="card-title">System Users</div></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr><th>User Name</th><th>Email</th><th>Mobile</th><th>Role</th><th>Status</th><th>Last Login</th></tr>
              </thead>
              <tbody>
                ${users.map(u => `
                  <tr>
                    <td><strong>${u.name}</strong></td>
                    <td>${u.email}</td>
                    <td>${u.mobile}</td>
                    <td><span class="badge badge-senior">${u.role}</span></td>
                    <td><span class="badge badge-active">${u.status}</span></td>
                    <td>${u.lastLogin}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Role Permission Matrix</div></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="data-table permission-matrix">
              <thead>
                <tr>
                  <th>Module / Permission</th>
                  <th>Super Admin</th>
                  <th>Admin</th>
                  <th>Agent</th>
                  <th>Data Entry</th>
                  <th>Accountant</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Dashboard View</td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td></tr>
                <tr><td>Member Registration</td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox"></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox"></td></tr>
                <tr><td>Payment & Receipts Entry</td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td></tr>
                <tr><td>Age Slabs Configuration</td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox"></td><td><input type="checkbox" class="custom-checkbox"></td><td><input type="checkbox" class="custom-checkbox"></td></tr>
                <tr><td>Financial & Commission Reports</td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td><td><input type="checkbox" class="custom-checkbox"></td><td><input type="checkbox" class="custom-checkbox"></td><td><input type="checkbox" class="custom-checkbox" checked disabled></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // 12. SOCIETY SETTINGS
  // =========================================================================
  renderSettingsView(container) {
    const s = State.data.settings;

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>सोसायटी सेटिंग्स <span class="hi-subtitle">Society Settings & Profile</span></h1>
          <div class="page-subtitle">Society branding, registration metadata & prefix configuration</div>
        </div>
      </div>

      <div class="card" style="max-width:800px;">
        <div class="card-header"><div class="card-title">Society Profile & Receipt Prefixes</div></div>
        <div class="card-body">
          <form id="settingsForm">
            <div class="form-grid">
              <div class="form-group">
                <label>Society English Name</label>
                <input type="text" class="form-control" id="stg_name" value="${s.societyName}">
              </div>

              <div class="form-group">
                <label>Society Hindi Name (हिंदी नाम)</label>
                <input type="text" class="form-control" id="stg_nameHindi" value="${s.societyHindiName}">
              </div>

              <div class="form-group">
                <label>Registration Number</label>
                <input type="text" class="form-control" id="stg_regNo" value="${s.regNo}">
              </div>

              <div class="form-group">
                <label>SAN Code</label>
                <input type="text" class="form-control" id="stg_sanNo" value="${s.sanNo}">
              </div>

              <div class="form-group" style="grid-column:span 2;">
                <label>Full Address</label>
                <input type="text" class="form-control" id="stg_address" value="${s.address}">
              </div>

              <div class="form-group">
                <label>Membership Prefix</label>
                <input type="text" class="form-control" id="stg_memPrefix" value="${s.memberPrefix}">
              </div>

              <div class="form-group">
                <label>Receipt Prefix</label>
                <input type="text" class="form-control" id="stg_recPrefix" value="${s.receiptPrefix}">
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
              <button type="button" class="btn btn-primary" onclick="Utils.showToast('Society settings updated!','success')"><i class="fas fa-save"></i> Save Settings</button>
            </div>
          </form>
        </div>
      </div>
    `;
  },

  toggleLanguage() {
    const nextLang = State.currentLang === 'hi' ? 'en' : 'hi';
    State.setLanguage(nextLang);
    this.applyLanguage();
    Utils.showToast(`Language switched to: ${nextLang === 'hi' ? 'हिंदी (Hindi)' : 'English'}`, 'success');
  },

  applyLanguage() {
    const lang = State.currentLang;
    const label = document.getElementById('langToggleLabel');
    if (label) {
      label.innerHTML = lang === 'hi' ? '<i class="fas fa-globe"></i> English / <b>हिंदी</b>' : '<i class="fas fa-globe"></i> <b>English</b> / हिंदी';
    }

    const dict = Utils.i18n[lang] || Utils.i18n.hi;
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if (dict[key]) el.innerText = dict[key];
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      const key = el.getAttribute('data-i18n-placeholder');
      if (dict[key]) el.placeholder = dict[key];
    });

    this.navigateTo(this.currentView);
  },

  switchRole(role) {
    State.setRole(role);
    this.renderCurrentRoleUI();
    const banner = document.getElementById('agentViewBanner');
    if (banner) {
      if (role === 'Agent') banner.classList.remove('hidden');
      else banner.classList.add('hidden');
    }
    Utils.showToast(`Switched view role to: ${role}`, 'info');
    this.navigateTo(this.currentView);
  },

  renderCurrentRoleUI() {
    const role = State.currentRole;
    const badge = document.getElementById('displayRoleBadge');
    if (badge) badge.innerText = role;

    const banner = document.getElementById('agentViewBanner');
    if (banner) {
      if (role === 'Agent') banner.classList.remove('hidden');
      else banner.classList.add('hidden');
    }

    // Filter sidebar navigation items based on role
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
      const view = item.getAttribute('data-view');
      let allowed = true;

      if (role === 'Agent') {
        allowed = ['dashboard', 'members', 'add-member', 'payments', 'payment-entry', 'receipts', 'ledger', 'events', 'payouts', 'whatsapp'].includes(view);
      }
      item.style.display = allowed ? 'flex' : 'none';
    });
  },

  navigateTo(view, paramId = null) {
    this.currentView = view;

    document.querySelectorAll('.nav-item').forEach(el => {
      if (el.getAttribute('data-view') === view) el.classList.add('active');
      else el.classList.remove('active');
    });

    const mainContainer = document.getElementById('view-content');
    if (!mainContainer) return;

    switch (view) {
      case 'dashboard':
        this.renderDashboard(mainContainer);
        break;
      case 'schemes':
        this.renderSchemesMaster(mainContainer);
        break;
      case 'age-slabs':
        this.renderAgeSlabsMaster(mainContainer);
        break;
      case 'members':
        this.renderMembersList(mainContainer);
        break;
      case 'add-member':
        this.renderAddMemberWizard(mainContainer);
        break;
      case 'member-detail':
        this.currentMemberDetailId = paramId || this.currentMemberDetailId || State.getMembers()[0].id;
        this.renderMemberDetail(mainContainer, this.currentMemberDetailId);
        break;
      case 'agents':
        this.renderAgentsList(mainContainer);
        break;
      case 'agent-detail':
        this.currentAgentDetailId = paramId || this.currentAgentDetailId || State.getAgents()[0].id;
        this.renderAgentDetail(mainContainer, this.currentAgentDetailId);
        break;
      case 'payments':
        this.renderPaymentsList(mainContainer);
        break;
      case 'payment-entry':
        this.renderPaymentEntry(mainContainer, paramId);
        break;
      case 'receipts':
        this.renderReceiptsList(mainContainer);
        break;
      case 'ledger':
        this.renderLedgerView(mainContainer, paramId);
        break;
      case 'certificates':
        this.renderCertificatesList(mainContainer);
        break;
      case 'events':
        this.renderEventsList(mainContainer);
        break;
      case 'payouts':
        this.renderPayoutsManager(mainContainer);
        break;
      case 'whatsapp':
        this.renderWhatsAppCenter(mainContainer);
        break;
      case 'reports':
        this.renderReportsModule(mainContainer, paramId || 'collection');
        break;
      case 'users':
        this.renderUsersPermissions(mainContainer);
        break;
      case 'settings':
        this.renderSettingsView(mainContainer);
        break;
      default:
        this.renderDashboard(mainContainer);
    }

    this.initHindiInputListeners();
    window.scrollTo(0, 0);
  },

  initHindiInputListeners() {
    document.querySelectorAll('.hindi-transliterate').forEach(input => {
      if (input.dataset.transliterateBound) return;
      input.dataset.transliterateBound = 'true';

      let hintBox = input.parentElement.querySelector('.hindi-preview-hint');
      if (!hintBox) {
        hintBox = document.createElement('span');
        hintBox.className = 'hindi-preview-hint';
        input.parentElement.appendChild(hintBox);
      }

      input.addEventListener('input', (e) => {
        const val = e.target.value;
        if (!val) {
          hintBox.style.display = 'none';
          return;
        }
        const hindiText = Utils.transliterateToHindi(val);
        hintBox.style.display = 'block';
        hintBox.innerHTML = `<i class="fas fa-keyboard"></i> <b>हिंदी रूपांतरण:</b> ${hindiText}`;
      });
    });
  },

  // =========================================================================
  // NEW VIEW: BENEFICIARY PAYOUTS MANAGER (Rule #6)
  // =========================================================================
  renderPayoutsManager(container) {
    const payouts = State.getPayouts();
    const isAgent = State.currentRole === 'Agent';

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>लाभांश एवं सहायता भुगतान <span class="hi-subtitle">Beneficiary Payout & Pool Distribution</span></h1>
          <div class="page-subtitle">Marriage scheme assistance pool & elderly death claim disburse manager</div>
        </div>
        ${!isAgent ? `
          <button class="btn btn-primary" onclick="App.openPayoutModal()"><i class="fas fa-plus-circle"></i> Disburse New Payout</button>
        ` : ''}
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-hand-holding-usd"></i> Beneficiary Payout Ledger</div>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Payout ID</th>
                  <th>Member ID</th>
                  <th>Beneficiary Name</th>
                  <th>Scheme</th>
                  <th>Type</th>
                  <th>Amount Paid</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Reference / TXN</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                ${payouts.map(p => `
                  <tr>
                    <td><b>${p.id}</b></td>
                    <td><a href="#" onclick="App.navigateTo('member-detail', '${p.memberId}')">${p.memberId}</a></td>
                    <td><b>${p.beneficiaryName}</b></td>
                    <td><span class="badge badge-info">${p.schemeName}</span></td>
                    <td><small>${p.type}</small></td>
                    <td><b style="color:#16A34A;">${Utils.formatCurrency(p.amount)}</b></td>
                    <td>${Utils.formatDate(p.date)}</td>
                    <td>
                      <span class="badge ${p.status.includes('Disbursed') ? 'badge-success' : 'badge-warning'}">
                        ${p.status}
                      </span>
                    </td>
                    <td><code>${p.refNo}</code></td>
                    <td>
                      <button class="btn btn-sm btn-outline-secondary" onclick="Utils.showToast('Payout receipt downloaded','info')"><i class="fas fa-print"></i> Receipt</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // NEW VIEW: FINANCIAL LEDGERS & PARTIAL PAYMENTS (Rule #5)
  // =========================================================================
  renderLedgerView(container, memberId = null) {
    const members = State.getMembers();
    const activeMember = memberId ? members.find(m => m.id === memberId) || members[0] : members[0];

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>सदस्य वित्तीय खाता / लेजर <span class="hi-subtitle">Member Financial Ledger & Dues</span></h1>
          <div class="page-subtitle">Track month-wise dues, partial payments and carried-forward outstanding balances</div>
        </div>
        <div class="quick-actions-bar">
          <button class="btn btn-warning" onclick="App.openPartialPaymentModal('${activeMember.id}')"><i class="fas fa-hand-holding-usd"></i> Record Partial Payment</button>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-body" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
          <div style="display:flex; align-items:center; gap:1rem;">
            <label style="font-weight:700;">Select Member:</label>
            <select class="form-control" style="width:300px;" onchange="App.renderLedgerView(document.getElementById('view-content'), this.value)">
              ${members.map(m => `
                <option value="${m.id}" ${m.id === activeMember.id ? 'selected' : ''}>${m.name} (${m.id}) - Balance: ${Utils.formatCurrency(m.outstandingBalance)}</option>
              `).join('')}
            </select>
          </div>
          <div>
            <span class="badge-carried-forward" style="font-size:0.95rem; padding:0.5rem 1rem;">
              <i class="fas fa-exclamation-circle"></i> Carried Forward Outstanding: <b>${Utils.formatCurrency(activeMember.outstandingBalance)}</b>
            </span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <div class="card-title"><i class="fas fa-book-open"></i> Account Statement: ${activeMember.name} (${activeMember.id})</div>
          <button class="btn btn-sm btn-outline-primary" onclick="App.exportMemberLedger('${activeMember.id}')"><i class="fas fa-file-excel"></i> Export CSV</button>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Transaction Type</th>
                  <th>Description / Event</th>
                  <th>Total Due / Charge</th>
                  <th>Amount Paid (Credit)</th>
                  <th>Carried Balance</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                ${activeMember.ledger.map(l => `
                  <tr>
                    <td>${Utils.formatDate(l.date)}</td>
                    <td><b>${l.type}</b></td>
                    <td>${l.desc}</td>
                    <td><span class="badge-due">${Utils.formatCurrency(l.charge)}</span></td>
                    <td><span class="badge-paid">${Utils.formatCurrency(l.credit)}</span></td>
                    <td><b style="color:${l.balance > 0 ? '#DC2626' : '#16A34A'};">${Utils.formatCurrency(l.balance)}</b></td>
                    <td>
                      <button class="btn btn-sm btn-outline-success" onclick="App.openWhatsAppModal('REC-2026-5001')"><i class="fab fa-whatsapp"></i> Share</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  exportMemberLedger(memberId) {
    const members = State.getMembers();
    const activeMember = members.find(m => m.id === memberId) || members[0];
    if (!activeMember || !activeMember.ledger) return;
    const data = activeMember.ledger.map(l => [l.date, l.type, l.desc, l.charge, l.credit, l.balance]);
    Utils.exportToCSV(`${activeMember.id}_ledger.csv`, ['Date', 'Type', 'Description', 'Charge', 'Credit', 'Balance'], data);
  },

  // =========================================================================
  // NEW VIEW: WHATSAPP COMMUNICATION CENTER (Rule #7)
  // =========================================================================
  renderWhatsAppCenter(container) {
    const receipts = State.getReceipts();
    const members = State.getMembers();

    container.innerHTML = `
      <div class="page-header">
        <div class="page-title-box">
          <h1>व्हाट्सएप सूचना एवं रसीद केंद्र <span class="hi-subtitle">WhatsApp & SMS Communications</span></h1>
          <div class="page-subtitle">Send digital receipts and automated payment reminders directly to members</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fab fa-whatsapp"></i> Dispatch Payment Receipts via WhatsApp</div>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Receipt No</th>
                  <th>Member Name</th>
                  <th>Mobile Number</th>
                  <th>Amount Paid</th>
                  <th>Outstanding</th>
                  <th>Payment Date</th>
                  <th>WhatsApp Action</th>
                </tr>
              </thead>
              <tbody>
                ${receipts.slice(0, 15).map(r => {
      const m = members.find(mem => mem.membershipNo === r.membershipNo) || {};
      const shareData = Utils.buildWhatsAppShareUrl(m, r);
      return `
                    <tr>
                      <td><b>${r.receiptNo}</b></td>
                      <td>${r.memberName} (${r.membershipNo})</td>
                      <td><i class="fas fa-phone-alt" style="color:#64748B;"></i> ${m.mobile || '9876543210'}</td>
                      <td><b style="color:#16A34A;">${Utils.formatCurrency(r.amountPaid || r.amount)}</b></td>
                      <td><span class="badge-carried-forward">${Utils.formatCurrency(r.remainingBalance || 0)}</span></td>
                      <td>${Utils.formatDate(r.paymentDate || r.issueDate)}</td>
                      <td>
                        <a href="${shareData.whatsappUrl}" target="_blank" class="btn btn-sm btn-whatsapp">
                          <i class="fab fa-whatsapp"></i> Send Receipt
                        </a>
                      </td>
                    </tr>
                  `;
    }).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // CONSOLIDATED EVENT BILLING MODAL (Rule #4)
  // =========================================================================
  openEventBillingModal() {
    const bodyHTML = `
      <form id="formEventBilling">
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Billing Month & Year (संग्रह माह)</label>
          <input type="text" class="form-control" id="eb_month" value="July 2026">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Number of Marriage Events in Month (विवाह आयोजन संख्या)</label>
          <input type="number" class="form-control" id="eb_count" value="4" min="1" max="20" onchange="App.calcEventBillingPreview()">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Per-Event Contribution Rate (प्रति आयोजन दर ₹)</label>
          <input type="number" class="form-control" id="eb_rate" value="200" step="50" onchange="App.calcEventBillingPreview()">
        </div>
        <div class="card" style="background-color:#EFF6FF; border:1px solid #93C5FD; padding:1rem; margin-top:1rem;">
          <div style="font-weight:700; color:#1E40AF; font-size:1.05rem;" id="eb_preview">
            Total Monthly Contribution Due: 4 Events x ₹200 = ₹800 per active member
          </div>
          <small style="color:#1E3A8A; display:block; margin-top:0.3rem;">
            * Consolidates all 4 events into a single monthly receipt for all active Vivah Yojna members.
          </small>
        </div>
      </form>
    `;

    this.showModal('Generate Consolidated Monthly Event Billing', bodyHTML, [
      {
        text: 'Generate Consolidated Billing',
        class: 'btn-primary',
        icon: 'fa-cog',
        onclick: () => {
          const monthYear = document.getElementById('eb_month').value;
          const eventCount = document.getElementById('eb_count').value;
          const ratePerEvent = document.getElementById('eb_rate').value;

          const result = State.addConsolidatedEventBilling({ monthYear, eventCount, ratePerEvent });
          Utils.showToast(`Consolidated billing of ₹${result.totalDue} generated for ${result.countGen} active members!`, 'success');
          App.closeModal();
          App.navigateTo('ledger');
        }
      }
    ]);
  },

  calcEventBillingPreview() {
    const count = Number(document.getElementById('eb_count').value) || 1;
    const rate = Number(document.getElementById('eb_rate').value) || 200;
    const total = count * rate;
    const prev = document.getElementById('eb_preview');
    if (prev) {
      prev.innerText = `Total Monthly Contribution Due: ${count} Events x ₹${rate} = ₹${total} per active member`;
    }
  },

  // =========================================================================
  // PARTIAL PAYMENT RECORDING MODAL (Rule #5)
  // =========================================================================
  openPartialPaymentModal(memberId = null) {
    const members = State.getMembers();
    const m = memberId ? members.find(mem => mem.id === memberId) || members[0] : members[0];
    const defaultTotal = m.outstandingBalance > 0 ? m.outstandingBalance : 800;

    const bodyHTML = `
      <form id="formPartialPayment">
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Select Member</label>
          <select class="form-control" id="pp_memberId" onchange="App.updatePartialPaymentMemberInfo(this.value)">
            ${members.map(mem => `
              <option value="${mem.id}" ${mem.id === m.id ? 'selected' : ''}>${mem.name} (${mem.id}) - Scheme: ${mem.schemeName}</option>
            `).join('')}
          </select>
        </div>

        <div class="form-grid" style="margin-bottom:1rem;">
          <div class="form-group">
            <label style="font-weight:700;">Total Monthly Payable (₹)</label>
            <input type="number" class="form-control" id="pp_totalDue" value="${defaultTotal}" onchange="App.calcPartialPaymentMath()">
          </div>
          <div class="form-group">
            <label style="font-weight:700;">Amount Received Now (₹)</label>
            <input type="number" class="form-control" id="pp_amountPaid" value="500" onchange="App.calcPartialPaymentMath()">
          </div>
        </div>

        <div class="card" style="background-color:#FFFBEB; border:1px solid #FCD34D; padding:1rem;" id="pp_mathBox">
          <div style="font-weight:700; color:#B45309;">
            Carried-Forward Balance Due: ₹300
          </div>
          <small style="color:#78350F; display:block; margin-top:0.2rem;">
            The remaining ₹300 will automatically carry forward to next month's member ledger.
          </small>
        </div>

        <div class="form-grid" style="margin-top:1rem;">
          <div class="form-group">
            <label>Payment Mode</label>
            <select class="form-control" id="pp_mode">
              <option value="Cash">Cash</option>
              <option value="UPI">UPI / GPay / PhonePe</option>
              <option value="Bank Transfer">Bank Transfer</option>
            </select>
          </div>
          <div class="form-group">
            <label>Transaction / Reference No</label>
            <input type="text" class="form-control" id="pp_txn" placeholder="N/A for cash">
          </div>
        </div>
      </form>
    `;

    this.showModal('Record Payment / Partial Payment', bodyHTML, [
      {
        text: 'Save Payment & Generate Receipt',
        class: 'btn-success',
        icon: 'fa-check',
        onclick: () => {
          const memId = document.getElementById('pp_memberId').value;
          const targetMem = State.getMembers().find(mem => mem.id === memId);
          const totalDue = Number(document.getElementById('pp_totalDue').value);
          const amountPaid = Number(document.getElementById('pp_amountPaid').value);
          const mode = document.getElementById('pp_mode').value;
          const txn = document.getElementById('pp_txn').value;

          const paymentRecord = State.addPartialPayment({
            membershipNo: targetMem ? targetMem.membershipNo : memId,
            totalDue,
            amountPaid,
            paymentMode: mode,
            transactionNo: txn,
            month: 'July 2026'
          });

          Utils.showToast(`Payment of ₹${amountPaid} recorded successfully! Receipt #${paymentRecord.receiptNo}`, 'success');
          App.closeModal();
          App.navigateTo('ledger', memId);
        }
      }
    ]);
  },

  calcPartialPaymentMath() {
    const totalDue = Number(document.getElementById('pp_totalDue').value) || 0;
    const amountPaid = Number(document.getElementById('pp_amountPaid').value) || 0;
    const balance = Math.max(0, totalDue - amountPaid);
    const box = document.getElementById('pp_mathBox');
    if (box) {
      box.innerHTML = `
        <div style="font-weight:700; color:${balance > 0 ? '#B45309' : '#16A34A'};">
          Carried-Forward Balance Due: ₹${balance}
        </div>
        <small style="color:#78350F; display:block; margin-top:0.2rem;">
          ${balance > 0 ? `The remaining ₹${balance} will automatically carry forward to next month's ledger.` : 'Full payment received! No outstanding balance carried forward.'}
        </small>
      `;
    }
  },

  updatePartialPaymentMemberInfo(memId) {
    const mem = State.getMembers().find(m => m.id === memId);
    if (mem) {
      const inputDue = document.getElementById('pp_totalDue');
      if (inputDue) inputDue.value = mem.outstandingBalance > 0 ? mem.outstandingBalance : 800;
      this.calcPartialPaymentMath();
    }
  },

  // =========================================================================
  // BENEFICIARY PAYOUT MODAL (Rule #6)
  // =========================================================================
  openPayoutModal() {
    const members = State.getMembers();

    const bodyHTML = `
      <form id="formBeneficiaryPayout">
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Beneficiary Member</label>
          <select class="form-control" id="po_memberId">
            ${members.map(m => `
              <option value="${m.id}">${m.name} (${m.id}) - ${m.schemeName}</option>
            `).join('')}
          </select>
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="font-weight:700;">Beneficiary Recipient Name (लाभार्थी / वारिसदार नाम)</label>
          <input type="text" class="form-control hindi-transliterate" id="po_name" value="Kaveri Devi">
        </div>
        <div class="form-grid" style="margin-bottom:1rem;">
          <div class="form-group">
            <label style="font-weight:700;">Payout Type</label>
            <select class="form-control" id="po_type">
              <option value="Marriage Assistance Payout">Marriage Assistance (कन्यादान सहायता)</option>
              <option value="Elderly Death Claim Payout">Elderly Death Claim (निधन सहायता)</option>
            </select>
          </div>
          <div class="form-group">
            <label style="font-weight:700;">Payout Amount (₹)</label>
            <input type="number" class="form-control" id="po_amount" value="51000">
          </div>
        </div>
        <div class="form-group">
          <label style="font-weight:700;">Event / Occasion Details</label>
          <input type="text" class="form-control" id="po_event" value="सामूहिक कन्यादान सहायता वितरण 2026">
        </div>
      </form>
    `;

    this.showModal('Disburse Beneficiary Payout (लाभांश वितरण)', bodyHTML, [
      {
        text: 'Approve & Disburse Payout',
        class: 'btn-success',
        icon: 'fa-check-circle',
        onclick: () => {
          const memberId = document.getElementById('po_memberId').value;
          const beneficiaryName = document.getElementById('po_name').value;
          const type = document.getElementById('po_type').value;
          const amount = document.getElementById('po_amount').value;
          const eventName = document.getElementById('po_event').value;
          const targetMem = State.getMembers().find(m => m.id === memberId);

          State.disbursePayout({
            memberId,
            beneficiaryName,
            schemeName: targetMem ? targetMem.schemeName : 'विवाह योजना',
            type,
            amount,
            eventName
          });

          Utils.showToast(`Beneficiary payout of ₹${amount} disbursed to ${beneficiaryName}!`, 'success');
          App.closeModal();
          App.navigateTo('payouts');
        }
      }
    ]);

    this.initHindiInputListeners();
  },

  // =========================================================================
  // WHATSAPP SHARE MODAL (Rule #7)
  // =========================================================================
  openWhatsAppModal(receiptNo) {
    const receipts = State.getReceipts();
    const r = receipts.find(rec => rec.receiptNo === receiptNo) || receipts[0];
    const m = State.getMembers().find(mem => mem.membershipNo === r.membershipNo) || {};
    const shareData = Utils.buildWhatsAppShareUrl(m, r);

    const bodyHTML = `
      <div style="margin-bottom:1rem;">
        <label style="font-weight:700;">WhatsApp Message Preview (संदेश पूर्वावलोकन)</label>
        <div class="whatsapp-box">${shareData.messageText}</div>
      </div>
    `;

    this.showModal(`WhatsApp Receipt Dispatch - #${r.receiptNo}`, bodyHTML, [
      {
        text: 'Open in WhatsApp Web / App',
        class: 'btn-whatsapp',
        icon: 'fa-whatsapp',
        onclick: () => {
          window.open(shareData.whatsappUrl, '_blank');
        }
      }
    ]);
  },

  // =========================================================================
  // MODAL UTILITY HELPERS
  // =========================================================================
  showModal(title, bodyHTML, buttons = []) {
    let overlay = document.getElementById('globalModalOverlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'globalModalOverlay';
      overlay.className = 'modal-overlay';
      document.body.appendChild(overlay);
    }

    overlay.innerHTML = `
      <div class="modal-card" style="max-width:650px;">
        <div class="modal-header">
          <div class="modal-title">${title}</div>
          <button class="modal-close-btn" onclick="App.closeModal()">&times;</button>
        </div>
        <div class="modal-body">${bodyHTML}</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" onclick="App.closeModal()">Close</button>
          ${buttons.map((b, i) => `
            <button class="btn ${b.class || 'btn-primary'}" id="modal_btn_${i}">
              ${b.icon ? `<i class="fas ${b.icon}"></i>` : ''} ${b.text}
            </button>
          `).join('')}
        </div>
      </div>
    `;

    buttons.forEach((b, i) => {
      const btnEl = document.getElementById(`modal_btn_${i}`);
      if (btnEl && b.onclick) btnEl.onclick = b.onclick;
    });

    overlay.classList.add('active');
  },

  closeModal() {
    const overlay = document.getElementById('globalModalOverlay');
    if (overlay) overlay.classList.remove('active');
  }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());

