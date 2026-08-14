/**
 * Utility functions for Shri Shyam Welfare Society ERP
 */

const Utils = {
  /**
   * Format amount into Indian Rupee format (e.g. ₹1,25,000)
   */
  formatCurrency(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) return '₹0';
    const num = Math.round(Number(amount));
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      maximumFractionDigits: 0
    }).format(num);
  },

  /**
   * Format numbers with Indian comma separation without currency symbol
   */
  formatNumber(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return new Intl.NumberFormat('en-IN').format(Number(num));
  },

  /**
   * Format date into DD-MM-YYYY format
   */
  formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
  },

  /**
   * Format date to YYYY-MM-DD for input[type="date"]
   */
  toInputDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '';
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${year}-${month}-${day}`;
  },

  /**
   * Calculate exact age from Date of Birth string (YYYY-MM-DD)
   */
  calculateAge(dobStr) {
    if (!dobStr) return 0;
    const dob = new Date(dobStr);
    if (isNaN(dob.getTime())) return 0;
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
      age--;
    }
    return Math.max(0, age);
  },

  /**
   * Convert number into Indian Rupees words
   */
  amountInWords(amount) {
    const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
    const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    const num = Math.round(Number(amount));
    if (num === 0) return 'Zero Rupees Only';

    function inWords(n) {
      if ((n = n.toString()).length > 9) return 'overflow';
      const n_array = ('000000000' + n).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
      if (!n_array) return '';
      let str = '';
      str += (n_array[1] != 0) ? (a[Number(n_array[1])] || b[n_array[1][0]] + ' ' + a[n_array[1][1]]) + 'Crore ' : '';
      str += (n_array[2] != 0) ? (a[Number(n_array[2])] || b[n_array[2][0]] + ' ' + a[n_array[2][1]]) + 'Lakh ' : '';
      str += (n_array[3] != 0) ? (a[Number(n_array[3])] || b[n_array[3][0]] + ' ' + a[n_array[3][1]]) + 'Thousand ' : '';
      str += (n_array[4] != 0) ? (a[Number(n_array[4])] || b[n_array[4][0]] + ' ' + a[n_array[4][1]]) + 'Hundred ' : '';
      str += (n_array[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n_array[5])] || b[n_array[5][0]] + ' ' + a[n_array[5][1]]) : '';
      return str;
    }

    return (inWords(num) + 'Rupees Only').replace(/\s+/g, ' ').trim();
  },

  /**
   * Export JSON array to downloadable CSV file
   */
  exportToCSV(filename, headers, rows) {
    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${String(cell || '').replace(/"/g, '""')}"`).join(','))
    ].join('\n');

    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  },

  /**
   * Trigger browser print for a specific container ID
   */
  printContainer(containerId) {
    window.print();
  },

  /**
   * Show Toast Notification
   */
  showToast(message, type = 'success', duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let iconClass = 'fa-check-circle';
    if (type === 'error') iconClass = 'fa-exclamation-circle';
    if (type === 'warning') iconClass = 'fa-exclamation-triangle';
    if (type === 'info') iconClass = 'fa-info-circle';

    toast.innerHTML = `
      <i class="fas ${iconClass} toast-icon"></i>
      <div class="toast-message">${message}</div>
      <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('toast-fade-out');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  /**
   * Get random Indian avatar placeholder based on gender/name
   */
  getAvatar(name, gender = 'Male') {
    const isFemale = gender.toLowerCase() === 'female' || name.includes('Devi') || name.includes('Kaur') || name.includes('Kumari');
    const set = isFemale ? 'women' : 'men';
    const hash = Math.abs(this.hashCode(name)) % 70;
    return `https://randomuser.me/api/portraits/${set}/${hash}.jpg`;
  },

  hashCode(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = (hash << 5) - hash + str.charCodeAt(i);
      hash |= 0;
    }
    return hash;
  },

  /**
   * Phonetic English-to-Hindi Transliteration Engine
   */
  transliterateToHindi(str) {
    if (!str) return '';
    let result = str.trim();

    // Direct Common Name & Word Dictionary
    const wordMap = {
      'navneet': 'नवनीत', 'gehlot': 'गेहलोत', 'rameshwar': 'रामेश्वर', 'lal': 'लाल',
      'sharma': 'शर्मा', 'suresh': 'सुरेश', 'kumar': 'कुमार', 'yadav': 'यादव',
      'rajendra': 'राजेन्द्र', 'prasad': 'प्रसाद', 'verma': 'वर्मा', 'sunita': 'सुनीता',
      'devi': 'देवी', 'saini': 'सैनी', 'mahesh': 'महेश', 'garg': 'गर्ग',
      'virendra': 'विरेन्द्र', 'singh': 'सिंह', 'shekhawat': 'शेखावत', 'mamta': 'ममता',
      'deepak': 'दीपक', 'khandelwal': 'खंडेलवाल', 'pawan': 'पवन', 'anil': 'अनिल',
      'lohi': 'लोहीकी', 'lohki': 'लोहीकी', 'shri': 'श्री', 'shyam': 'श्याम',
      'welfare': 'वेलफेयर', 'society': 'सोसायटी', 'vivah': 'विवाह', 'bujurg': 'बुजुर्ग',
      'samman': 'सम्मान', 'kanyadan': 'कन्यादान', 'yojna': 'योजना', 'plan': 'योजना',
      'member': 'सदस्य', 'agent': 'एजेंट', 'receipt': 'रसीद', 'payment': 'भुगतान',
      'event': 'कार्यक्रम', 'payout': 'लाभांश', 'mahendragarh': 'महेंद्रगढ़',
      'bhiwani': 'भिवानी', 'rewari': 'रेवाड़ी', 'charkhi': 'चरखी', 'dadri': 'दादरी',
      'jhunjhunu': 'झुंझुनूं'
    };

    const words = result.split(/\s+/);
    const converted = words.map(w => {
      const lower = w.toLowerCase().replace(/[^a-z]/g, '');
      if (wordMap[lower]) return wordMap[lower];

      // Character-by-character phonetic heuristic
      let s = lower;
      s = s.replace(/kh/g, 'ख').replace(/gh/g, 'घ').replace(/ch/g, 'च').replace(/jh/g, 'झ');
      s = s.replace(/th/g, 'थ').replace(/dh/g, 'ध').replace(/ph/g, 'फ').replace(/bh/g, 'भ');
      s = s.replace(/sh/g, 'श').replace(/ee/g, 'ी').replace(/oo/g, 'ू').replace(/ai/g, 'ै');
      s = s.replace(/au/g, 'ौ').replace(/aa/g, 'ा').replace(/ri/g, 'रि');
      s = s.replace(/k/g, 'क').replace(/g/g, 'ग').replace(/j/g, 'ज').replace(/t/g, 'त');
      s = s.replace(/d/g, 'द').replace(/n/g, 'न').replace(/p/g, 'प').replace(/b/g, 'ब');
      s = s.replace(/m/g, 'म').replace(/y/g, 'य').replace(/r/g, 'र').replace(/l/g, 'ल');
      s = s.replace(/v/g, 'व').replace(/w/g, 'व').replace(/s/g, 'स').replace(/h/g, 'ह');
      s = s.replace(/a/g, 'ा').replace(/i/g, 'ि').replace(/u/g, 'ु').replace(/e/g, 'े').replace(/o/g, 'ो');
      return s;
    });

    return converted.join(' ');
  },

  /**
   * Build WhatsApp share URL and preformatted Hindi/English message
   */
  buildWhatsAppShareUrl(member, payment) {
    if (!member || !payment) return '#';
    const cleanMobile = String(member.mobile || '').replace(/\D/g, '');
    const mobileWithCode = cleanMobile.startsWith('91') ? cleanMobile : `91${cleanMobile}`;

    const message = `🚩 *श्री श्याम वेलफेयर सोसायटी लोहीकी* 🚩
━━━━━━━━━━━━━━━━━━━━
📄 *आधिकारिक भुगतान रसीद (Payment Receipt)*

👤 *सदस्य नाम:* ${member.name} (${member.id})
📑 *योजना:* ${member.schemeName}
🎫 *रसीद सं:* ${payment.receiptNo}
📅 *दिनांक:* ${this.formatDate(payment.date)}
🗓 *संग्रह माह:* ${payment.month}

💰 *मासिक देय राशि:* ${this.formatCurrency(payment.totalDue || payment.amount)}
✅ *प्राप्त भुगतान:* ${this.formatCurrency(payment.amountPaid || payment.amount)}
⚠️ *शेष बकाई राशि:* ${this.formatCurrency(payment.remainingBalance || 0)}

${payment.eventsCount ? `🎉 *मासिक विवाह कार्यक्रम संख्या:* ${payment.eventsCount} कार्यक्रम` : ''}

धन्यवाद! श्री श्याम वेलफेयर सोसायटी लोहीकी।
📞 संपर्क: 9876543210`;

    return {
      messageText: message,
      whatsappUrl: `https://wa.me/${mobileWithCode}?text=${encodeURIComponent(message)}`
    };
  },

  /**
   * Complete i18n Translation Dictionary (English & Hindi)
   */
  i18n: {
    en: {
      appName: "Shri Shyam Welfare Society ERP",
      societyTitle: "Shri Shyam Welfare Society Lohki",
      dashboard: "Dashboard",
      schemesMaster: "Schemes Master",
      ageSlabs: "Age Slabs",
      allMembers: "All Members",
      addMember: "Add Member",
      allAgents: "All Agents",
      paymentEntry: "Payment Entry",
      paymentList: "Payment History",
      receipts: "Receipts",
      certificates: "Certificates",
      events: "Marriage Events",
      payouts: "Beneficiary Payouts",
      ledger: "Financial Ledgers",
      whatsapp: "WhatsApp Communications",
      reports: "Reports Center",
      users: "Users & Roles",
      settings: "Settings",
      mainMenu: "Main Menu",
      masterRecords: "Master Records",
      memberEnrolment: "Member Enrolment",
      agentNetwork: "Agent Network",
      collections: "Collections & Dues",
      certificatesEvents: "Certificates & Events",
      reportsAdmin: "Reports & Admin",
      totalMembers: "Total Members",
      activeMembers: "Active Members",
      inactiveMembers: "Inactive Members",
      totalAgents: "Total Agents",
      todayCollection: "Today's Collection",
      monthlyCollection: "Monthly Collection",
      overduePending: "Overdue Pending Amount",
      totalEvents: "Total Marriage Events",
      totalPayouts: "Total Beneficiary Payouts",
      quickActions: "Quick Actions",
      genEventBilling: "Generate Event Monthly Billing",
      recordPayment: "Record Member Payment",
      disbursePayout: "Disburse Beneficiary Payout",
      sendWhatsApp: "Send WhatsApp Receipt",
      searchPlaceholder: "Search Member by Name, ID or Mobile...",
      filterScheme: "Filter Scheme",
      filterAgent: "Filter Agent",
      filterStatus: "Filter Status",
      memberNo: "Member No",
      name: "Member Name",
      age: "Age",
      mobile: "Mobile",
      scheme: "Scheme",
      agent: "Agent",
      status: "Status",
      joiningDate: "Joining Date",
      actions: "Actions",
      save: "Save",
      cancel: "Cancel",
      print: "Print",
      exportCSV: "Export to Excel CSV",
      viewProfile: "View Profile",
      amountPaid: "Amount Paid",
      totalDue: "Total Payable",
      remainingBalance: "Outstanding Balance",
      partialPayment: "Partial Payment",
      roleAdmin: "Super Admin (Full Access)",
      roleAgent: "Agent Mode (Assigned Members Only)",
      agentViewBanner: "Showing only members assigned to Agent: Rameshwar Lal Sharma (AGT-001)"
    },
    hi: {
      appName: "श्री श्याम वेलफेयर सोसायटी ईआरपी",
      societyTitle: "श्री श्याम वेलफेयर सोसायटी लोहीकी",
      dashboard: "डैशबोर्ड (मुख्य पृष्ठ)",
      schemesMaster: "योजना मास्टर",
      ageSlabs: "आयु वर्ग (Age Slabs)",
      allMembers: "सभी सदस्य (Members)",
      addMember: "नया सदस्य जोड़ें",
      allAgents: "सभी एजेंट (Agents)",
      paymentEntry: "मासिक किस्त प्रविष्टि",
      paymentList: "भुगतान इतिहास (Payments)",
      receipts: "रसीदें (Receipts)",
      certificates: "प्रमाण पत्र (Certificates)",
      events: "विवाह कार्यक्रम (Events)",
      payouts: "लाभांश / सहायता भुगतान",
      ledger: "सदस्य वित्तीय लेजर",
      whatsapp: "व्हाट्सएप संचार (WhatsApp)",
      reports: "रिपोर्ट केंद्र (Reports)",
      users: "उपयोगकर्ता एवं अधिकार",
      settings: "प्रणाली सेटिंग्स",
      mainMenu: "मुख्य मेनू",
      masterRecords: "मास्टर रिकॉर्ड",
      memberEnrolment: "सदस्य नामांकन",
      agentNetwork: "एजेंट नेटवर्क",
      collections: "किस्त संग्रह व बकाई",
      certificatesEvents: "प्रमाणपत्र व कार्यक्रम",
      reportsAdmin: "रिपोर्ट व प्रशासन",
      totalMembers: "कुल पंजीकृत सदस्य",
      activeMembers: "सक्रिय सदस्य",
      inactiveMembers: "निष्क्रिय सदस्य",
      totalAgents: "कुल एजेंट",
      todayCollection: "आज का कुल संग्रह",
      monthlyCollection: "इस माह का कुल संग्रह",
      overduePending: "कुल बकाई (Pending Dues)",
      totalEvents: "मासिक विवाह कार्यक्रम",
      totalPayouts: "कुल वितरित लाभांश",
      quickActions: "त्वरित कार्य (Quick Actions)",
      genEventBilling: "मासिक विवाह कार्यक्रम बिलिंग बनाएं",
      recordPayment: "मासिक किस्त / आंशिक भुगतान दर्ज करें",
      disbursePayout: "लाभांश सहायता राशि स्वीकृत करें",
      sendWhatsApp: "व्हाट्सएप पर रसीद भेजें",
      searchPlaceholder: "नाम, सदस्य ID या मोबाइल नंबर से खोजें...",
      filterScheme: "योजना चुनें",
      filterAgent: "एजेंट चुनें",
      filterStatus: "स्थिति चुनें",
      memberNo: "सदस्य क्रमांक",
      name: "सदस्य का नाम",
      age: "आयु",
      mobile: "मोबाइल नंबर",
      scheme: "योजना",
      agent: "एजेंट",
      status: "स्थिति",
      joiningDate: "जुड़ने की तिथि",
      actions: "कार्य",
      save: "सुरक्षित करें",
      cancel: "रद्द करें",
      print: "प्रिंट रसीद",
      exportCSV: "एक्सेल CSV डाउनलोड करें",
      viewProfile: "प्रोफाइल देखें",
      amountPaid: "प्राप्त राशि",
      totalDue: "कुल देय राशि",
      remainingBalance: "शेष बकाई राशि",
      partialPayment: "आंशिक भुगतान",
      roleAdmin: "सुपर एडमिन (पूर्ण नियंत्रण)",
      roleAgent: "एजेंट मोड (केवल आवंटित सदस्य)",
      agentViewBanner: "एजेंट मोड: केवल आपके आवंटित सदस्य (रामेश्वर लाल शर्मा - AGT-001) प्रदर्शित हो रहे हैं"
    }
  }
};

