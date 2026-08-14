/**
 * Chart.js Integration for Shri Shyam Welfare Society ERP Dashboard
 */

const ChartsManager = {
  instances: {},

  initAll() {
    this.renderMonthlyCollection();
    this.renderNewMembers();
    this.renderSchemeDistribution();
    this.renderAgentCollections();
    this.renderPaymentModes();
  },

  destroyAll() {
    Object.keys(this.instances).forEach(key => {
      if (this.instances[key]) {
        this.instances[key].destroy();
      }
    });
    this.instances = {};
  },

  // 1. Monthly Collection Chart
  renderMonthlyCollection() {
    const ctx = document.getElementById('chartMonthlyCollection');
    if (!ctx) return;
    if (this.instances.monthlyCollection) this.instances.monthlyCollection.destroy();

    const months = ['Sep 25', 'Oct 25', 'Nov 25', 'Dec 25', 'Jan 26', 'Feb 26', 'Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26'];
    const data = [185000, 210000, 245000, 190000, 280000, 310000, 340000, 290000, 360000, 325000, 385000, 420000];

    this.instances.monthlyCollection = new Chart(ctx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          label: 'Collection (₹)',
          data: data,
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37, 99, 235, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: '#1E3A8A',
          pointRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => ` Collection: ${Utils.formatCurrency(ctx.raw)}`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (val) => `₹${val / 1000}k`
            }
          }
        }
      }
    });
  },

  // 2. New Members Chart
  renderNewMembers() {
    const ctx = document.getElementById('chartNewMembers');
    if (!ctx) return;
    if (this.instances.newMembers) this.instances.newMembers.destroy();

    const months = ['Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26'];
    const data = [4, 6, 8, 5, 11, 14];

    this.instances.newMembers = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [{
          label: 'New Registrations',
          data: data,
          backgroundColor: '#D97706',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } }
      }
    });
  },

  // 3. Scheme Distribution Chart
  renderSchemeDistribution() {
    const ctx = document.getElementById('chartSchemeDistribution');
    if (!ctx) return;
    if (this.instances.schemeDistribution) this.instances.schemeDistribution.destroy();

    const members = State.getMembers();
    const seniorCount = members.filter(m => m.schemeCode === 'SENIOR').length;
    const marriageCount = members.filter(m => m.schemeCode === 'MARRIAGE').length;

    this.instances.schemeDistribution = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['बुजुर्ग सम्मान योजना', 'विवाह योजना'],
        datasets: [{
          data: [seniorCount, marriageCount],
          backgroundColor: ['#1E3A8A', '#EA580C'],
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  },

  // 4. Agent Collections Bar Chart
  renderAgentCollections() {
    const ctx = document.getElementById('chartAgentCollections');
    if (!ctx) return;
    if (this.instances.agentCollections) this.instances.agentCollections.destroy();

    const agents = State.getAgents().slice(0, 5); // top 5
    const labels = agents.map(a => a.name.split(' ')[0]);
    const collections = agents.map(a => a.collection);

    this.instances.agentCollections = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Collection (₹)',
          data: collections,
          backgroundColor: '#2563EB',
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { label: (ctx) => ` ₹${Utils.formatNumber(ctx.raw)}` }
          }
        }
      }
    });
  },

  // 5. Payment Modes Donut Chart
  renderPaymentModes() {
    const ctx = document.getElementById('chartPaymentModes');
    if (!ctx) return;
    if (this.instances.paymentModes) this.instances.paymentModes.destroy();

    const payments = State.getPayments();
    const modeCounts = { Cash: 0, UPI: 0, 'Bank Transfer': 0, Cheque: 0 };
    payments.forEach(p => {
      if (modeCounts[p.paymentMode] !== undefined) modeCounts[p.paymentMode]++;
      else modeCounts.Cash++;
    });

    this.instances.paymentModes = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Cash', 'UPI', 'Bank Transfer', 'Cheque'],
        datasets: [{
          data: Object.values(modeCounts),
          backgroundColor: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }
};
