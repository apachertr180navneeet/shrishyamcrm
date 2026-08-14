/**
 * State Management & Initial Mock Database for Shri Shyam Welfare Society ERP
 */

const State = {
  // Active state data initialized from localStorage or default mock data
  data: null,
  currentLang: 'hi',
  currentRole: 'Super Admin',
  activeAgentId: 'AGT-001',
  currentUser: {
    name: 'Shri Navneet Sharma',
    role: 'Super Admin',
    email: 'admin@shrishyamwelfare.org',
    mobile: '9876543210'
  },

  init() {
    const savedState = localStorage.getItem('SSWS_ERP_STATE');
    if (savedState) {
      try {
        this.data = JSON.parse(savedState);
      } catch (e) {
        console.error('Failed to parse state from localStorage, loading default mock state.', e);
        this.loadDefaultState();
      }
    } else {
      this.loadDefaultState();
    }
  },

  save() {
    localStorage.setItem('SSWS_ERP_STATE', JSON.stringify(this.data));
  },

  reset() {
    localStorage.removeItem('SSWS_ERP_STATE');
    this.loadDefaultState();
    Utils.showToast('State reset to default initial data', 'info');
  },

  setLanguage(lang) {
    this.currentLang = lang;
    this.save();
  },

  setRole(role) {
    this.currentRole = role;
    if (role === 'Agent') {
      this.currentUser.name = 'Rameshwar Lal Sharma';
      this.currentUser.role = 'Agent';
      this.currentUser.agentId = 'AGT-001';
    } else {
      this.currentUser.name = 'Shri Navneet Sharma';
      this.currentUser.role = 'Super Admin';
    }
    this.save();
  },

  loadDefaultState() {
    const agents = [
      { id: 'AGT-001', name: 'Rameshwar Lal Sharma', code: 'AGT01', mobile: '9829012345', membersCount: 12, collection: 85500, pending: 4500, commissionRate: 5, commission: 4275, status: 'Active', district: 'Mahendragarh' },
      { id: 'AGT-002', name: 'Suresh Kumar Yadav', code: 'AGT02', mobile: '9414023456', membersCount: 8, collection: 54000, pending: 3200, commissionRate: 5, commission: 2700, status: 'Active', district: 'Bhiwani' },
      { id: 'AGT-003', name: 'Rajendra Prasad Verma', code: 'AGT03', mobile: '9812034567', membersCount: 7, collection: 48500, pending: 2000, commissionRate: 5, commission: 2425, status: 'Active', district: 'Rewari' },
      { id: 'AGT-004', name: 'Sunita Devi Saini', code: 'AGT04', mobile: '9784045678', membersCount: 6, collection: 39000, pending: 1500, commissionRate: 5, commission: 1950, status: 'Active', district: 'Mahendragarh' },
      { id: 'AGT-005', name: 'Mahesh Kumar Garg', code: 'AGT05', mobile: '9672056789', membersCount: 5, collection: 32500, pending: 2500, commissionRate: 5, commission: 1625, status: 'Active', district: 'Charkhi Dadri' },
      { id: 'AGT-006', name: 'Virendra Singh Shekhawat', code: 'AGT06', mobile: '9828067890', membersCount: 4, collection: 28000, pending: 1200, commissionRate: 5, commission: 1400, status: 'Active', district: 'Jhunjhunu' },
      { id: 'AGT-007', name: 'Mamta Sharma', code: 'AGT07', mobile: '9413078901', membersCount: 3, collection: 19500, pending: 800, commissionRate: 5, commission: 975, status: 'Active', district: 'Rewari' },
      { id: 'AGT-008', name: 'Deepak Kumar Khandelwal', code: 'AGT08', mobile: '9829089012', membersCount: 3, collection: 18000, pending: 1000, commissionRate: 5, commission: 900, status: 'Active', district: 'Mahendragarh' },
      { id: 'AGT-009', name: 'Pawan Kumar Saini', code: 'AGT09', mobile: '9414090123', membersCount: 2, collection: 12500, pending: 500, commissionRate: 5, commission: 625, status: 'Active', district: 'Bhiwani' },
      { id: 'AGT-010', name: 'Anil Kumar Yadav', code: 'AGT10', mobile: '9812001234', membersCount: 2, collection: 11000, pending: 0, commissionRate: 5, commission: 550, status: 'Active', district: 'Charkhi Dadri' }
    ];

    const schemes = [
      {
        id: 'SCH-001',
        code: 'SENIOR',
        name: 'Senior Welfare Scheme',
        nameHindi: 'बुजुर्ग सम्मान योजना',
        type: 'Senior Welfare Scheme',
        status: 'Active',
        effectiveFrom: '2021-01-01',
        effectiveTo: '2030-12-31',
        description: 'Monthly financial support and welfare scheme for elderly society members.',
        membersCount: 34,
        ageSlabs: [
          { id: 'SLAB-S1', minAge: 18, maxAge: 40, joiningAmount: 1100, supportAmount: 200, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-S2', minAge: 41, maxAge: 60, joiningAmount: 1500, supportAmount: 300, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-S3', minAge: 60, maxAge: 75, joiningAmount: 2000, supportAmount: 400, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-S4', minAge: 75, maxAge: 120, joiningAmount: 2500, supportAmount: 500, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' }
        ]
      },
      {
        id: 'SCH-002',
        code: 'MARRIAGE',
        name: 'Marriage Scheme (Kanyadaan/Gotra)',
        nameHindi: 'विवाह (कन्यादान/गौत्र) योजना',
        type: 'Marriage Scheme',
        status: 'Active',
        effectiveFrom: '2021-01-01',
        effectiveTo: '2030-12-31',
        description: 'Financial assistance scheme for girl child marriage and family welfare support.',
        membersCount: 18,
        ageSlabs: [
          { id: 'SLAB-M1', minAge: 0, maxAge: 5, joiningAmount: 1100, supportAmount: 100, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-M2', minAge: 6, maxAge: 9, joiningAmount: 1100, supportAmount: 200, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-M3', minAge: 10, maxAge: 13, joiningAmount: 2000, supportAmount: 300, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-M4', minAge: 14, maxAge: 17, joiningAmount: 2500, supportAmount: 400, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' },
          { id: 'SLAB-M5', minAge: 17, maxAge: 120, joiningAmount: 2500, supportAmount: 500, status: 'Active', effectiveFrom: '2021-01-01', effectiveTo: '2030-12-31' }
        ]
      }
    ];

    const firstNamesM = ['Ramchandra', 'Satyanarayan', 'Ghanashyam', 'Bhagwan Das', 'Jagdish', 'Kishore', 'Omprakash', 'Banwari Lal', 'Goyal', 'Subhash', 'Tarachand', 'Mahavir', 'Dharamvir', 'Bhikam Chand', 'Harish', 'Nirmal', 'Sohan Lal', 'Prabhu Dayal', 'Radheshyam', 'Shriniwas', 'Vijay', 'Manojh', 'Mukesh', 'Rajesh', 'Sanjay', 'Sunil', 'Devender'];
    const firstNamesF = ['Shanti', 'Kamla', 'Rami', 'Bhagwati', 'Kausalya', 'Ganga', 'Geeta', 'Saraswati', 'Savitri', 'Laxmi', 'Parvati', 'Sita', 'Suman', 'Bimla', 'Sunita', 'Anita', 'Manju', 'Rekha', 'Prem', 'Santosh', 'Renu', 'Kavita', 'Pooja', 'Aarti', 'Kiran'];
    const lastNames = ['Sharma', 'Verma', 'Yadav', 'Saini', 'Gupta', 'Shekhawat', 'Khandelwal', 'Jangir', 'Agarwal', 'Choudhary', 'Rathore', 'Meena', 'Kanwar', 'Garg', 'Bhardwaj'];
    const gotras = ['Kaushik', 'Vats', 'Bhardwaj', 'Kashyap', 'Garg', 'Goyal', 'Bansal', 'Tanwar', 'Chauhan', 'Rathore', 'Dhillon', 'Sheoran'];
    const castes = ['Brahmin', 'Yadav', 'Saini', 'Mahajan', 'Rajput', 'Jat', 'Jangid'];

    const members = [];
    let memCounter = 1001;

    for (let i = 0; i < 52; i++) {
      const isSenior = i < 34;
      const schemeId = isSenior ? 'SCH-001' : 'SCH-002';
      const schemeCode = isSenior ? 'SENIOR' : 'MARRIAGE';
      const schemeName = isSenior ? 'बुजुर्ग सम्मान योजना' : 'विवाह (कन्यादान/गौत्र) योजना';

      const gender = (i % 3 === 0) ? 'Female' : 'Male';
      const firstName = (gender === 'Female')
        ? firstNamesF[i % firstNamesF.length]
        : firstNamesM[i % firstNamesM.length];
      const lastName = lastNames[i % lastNames.length];
      const name = `${firstName} ${lastName}`;
      const fatherName = (gender === 'Female' && i % 2 === 0) ? `W/o ${firstNamesM[(i + 5) % firstNamesM.length]} ${lastName}` : `S/o ${firstNamesM[(i + 2) % firstNamesM.length]} ${lastName}`;
      const motherName = `${firstNamesF[(i + 3) % firstNamesF.length]} Devi`;

      let dobYear = isSenior ? 1945 + (i % 35) : 2008 + (i % 16);
      const dobMonth = String((i % 12) + 1).padStart(2, '0');
      const dobDay = String((i % 28) + 1).padStart(2, '0');
      const dob = `${dobYear}-${dobMonth}-${dobDay}`;
      const age = Utils.calculateAge(dob);

      const schemeObj = schemes.find(s => s.id === schemeId);
      const matchedSlab = schemeObj.ageSlabs.find(sl => age >= sl.minAge && age <= sl.maxAge) || schemeObj.ageSlabs[0];

      const agent = agents[i % agents.length];
      const joiningDate = `2024-${String((i % 8) + 1).padStart(2, '0')}-${String((i % 25) + 1).padStart(2, '0')}`;

      let status = 'Active';
      if (i === 5 || i === 18 || i === 31) status = 'Inactive';
      if (i === 42) status = 'Pending';
      if (i === 49) status = 'Suspended';
      if (i === 51) status = 'Deceased';

      const memId = `MEM-2026-${memCounter++}`;
      const totalPaid = matchedSlab.joiningAmount + (matchedSlab.supportAmount * ((i % 10) + 1));
      const outstandingBalance = (status === 'Active' && i % 4 === 0) ? matchedSlab.supportAmount * 2 : (i % 7 === 0 ? 300 : 0);

      members.push({
        id: memId,
        membershipNo: memId,
        name,
        fatherHusbandName: fatherName,
        motherName,
        dob,
        age,
        gender,
        mobile: `98${String(10000000 + i * 123456).slice(0, 8)}`,
        alternateMobile: `94${String(20000000 + i * 654321).slice(0, 8)}`,
        caste: castes[i % castes.length],
        gotra: gotras[i % gotras.length],
        address: `House No. ${i + 12}, Main Bazar, Lohki`,
        district: agent.district,
        state: 'Haryana',
        pinCode: '123001',
        aadhaarNo: `4829 ${1000 + i * 12} ${2000 + i * 34}`,
        photoUrl: Utils.getAvatar(name, gender),
        schemeId,
        schemeCode,
        schemeName,
        agentId: agent.id,
        agentName: agent.name,
        joiningDate,
        joiningAmount: matchedSlab.joiningAmount,
        supportAmount: matchedSlab.supportAmount,
        ageSlabId: matchedSlab.id,
        status,
        totalPaid,
        pendingAmount: outstandingBalance,
        outstandingBalance,
        ledger: [
          { date: joiningDate, type: 'Joining Fee', desc: 'Initial Enrollment', charge: matchedSlab.joiningAmount, credit: matchedSlab.joiningAmount, balance: 0 },
          { date: '2026-06-01', type: 'Monthly Dues', desc: 'June 2026 Contribution', charge: matchedSlab.supportAmount, credit: matchedSlab.supportAmount, balance: 0 },
          { date: '2026-07-01', type: 'Event Billing', desc: 'July 2026 Consolidated Billing (4 Events x ₹200)', charge: 800, credit: 500, balance: 300 }
        ],
        nominees: [
          { name: `${firstNamesM[(i + 1) % firstNamesM.length]} ${lastName}`, fatherHusbandName: fatherName, relation: isSenior ? 'Son' : 'Father', mobile: `98${String(30000000 + i * 333333).slice(0, 8)}`, aadhaarNo: `3920 ${2000 + i * 15} ${3000 + i * 22}` },
          { name: `${firstNamesF[(i + 4) % firstNamesF.length]} ${lastName}`, fatherHusbandName: fatherName, relation: isSenior ? 'Daughter-in-Law' : 'Mother', mobile: `94${String(40000000 + i * 444444).slice(0, 8)}`, aadhaarNo: `7712 ${4000 + i * 18} ${5000 + i * 11}` }
        ]
      });
    }

    const payments = [];
    const receipts = [];
    let recCounter = 5001;

    members.slice(0, 25).forEach((m, idx) => {
      const modeOptions = ['Cash', 'UPI', 'Bank Transfer', 'Cheque'];
      const mode = modeOptions[idx % modeOptions.length];
      const pDate = `2026-0${(idx % 6) + 1}-${String((idx % 25) + 1).padStart(2, '0')}`;
      const recNo = `REC-2026-${recCounter++}`;

      const paymentObj = {
        id: `PAY-${100 + idx}`,
        receiptNo: recNo,
        membershipNo: m.membershipNo,
        memberName: m.name,
        schemeName: m.schemeName,
        agentId: m.agentId,
        agentName: m.agentName,
        amount: m.joiningAmount,
        totalDue: m.joiningAmount,
        amountPaid: m.joiningAmount,
        remainingBalance: 0,
        month: 'July 2026',
        paymentType: 'Joining & Support Fee',
        paymentDate: pDate,
        paymentMode: mode,
        transactionNo: mode === 'Cash' ? 'N/A' : `TXN98234${idx}892`,
        remarks: 'Monthly scheme contribution received.',
        status: 'Successful'
      };

      payments.push(paymentObj);
      receipts.push({
        ...paymentObj,
        issueDate: pDate,
        authorizedBy: 'Admin Secretary'
      });
    });

    const certificates = members.slice(0, 15).map((m, idx) => ({
      certificateNo: `CRT-2026-${8001 + idx}`,
      membershipNo: m.membershipNo,
      memberName: m.name,
      fatherHusbandName: m.fatherHusbandName,
      dob: m.dob,
      schemeName: m.schemeName,
      issueDate: m.joiningDate,
      agentName: m.agentName,
      status: 'Active'
    }));

    const events = [
      { id: 'EVT-001', name: 'वार्षिक बुजुर्ग सम्मान समारोह 2026', type: 'Welfare Distribution', eventDate: '2026-09-15', location: 'Society Community Hall, Lohki', description: 'Grand annual ceremony honoring senior citizens with blankets and financial aid.', status: 'Upcoming', totalMembers: 35, collection: 45000 },
      { id: 'EVT-002', name: 'सामूहिक कन्यादान सहायता वितरण शिविर (4 विवाह आयोजन)', type: 'Marriage Support', eventDate: '2026-07-20', location: 'Government High School Ground, Lohki', description: 'Consolidated July marriage support distribution for 4 member family marriages.', status: 'Completed', totalMembers: 22, collection: 65000, eventsCount: 4, ratePerEvent: 200 },
      { id: 'EVT-003', name: 'निःशुल्क स्वास्थ्य एवं नेत्र जाँच शिविर', type: 'Health Camp', eventDate: '2026-06-10', location: 'Primary Health Centre, Lohki', description: 'Free health checkup, eye surgery assistance, and medicine distribution for society members.', status: 'Completed', totalMembers: 48, collection: 12000 }
    ];

    const payouts = [
      { id: 'POUT-101', memberId: 'MEM-2026-1004', beneficiaryName: 'Kaveri Devi (Daughter of Subhash Verma)', schemeName: 'विवाह (कन्यादान) योजना', type: 'Marriage Assistance Payout', amount: 51000, eventName: 'सामूहिक कन्यादान सहायता शिविर - 4 विवाह', date: '2026-07-22', status: 'Disbursed', refNo: 'PAYOUT-UPI-9823412' },
      { id: 'POUT-102', memberId: 'MEM-2026-1052', beneficiaryName: 'Sohan Lal (Nominee / Son of Deceased Jagdish Sharma)', schemeName: 'बुजुर्ग सम्मान योजना', type: 'Elderly Death Claim Payout', amount: 25000, eventName: 'निधन सहायता लाभांश भुगतान', date: '2026-07-10', status: 'Disbursed', refNo: 'BANK-TR-772810' },
      { id: 'POUT-103', memberId: 'MEM-2026-1011', beneficiaryName: 'Pooja Rani (Daughter of Tarachand Shekhawat)', schemeName: 'विवाह (कन्यादान) योजना', type: 'Marriage Assistance Payout', amount: 51000, eventName: 'आगामी अगस्त कन्यादान वितरण', date: '2026-08-18', status: 'Eligible (Pending Approval)', refNo: 'N/A' }
    ];

    const users = [
      { id: 'USR-01', name: 'Navneet Sharma', email: 'admin@shrishyamwelfare.org', mobile: '9876543210', role: 'Super Admin', status: 'Active', lastLogin: '2026-08-10 10:45 AM' },
      { id: 'USR-02', name: 'Nitin Kumar', email: 'secretary@shrishyamwelfare.org', mobile: '9812345678', role: 'Admin', status: 'Active', lastLogin: '2026-08-09 04:20 PM' },
      { id: 'USR-03', name: 'Rameshwar Lal Sharma', email: 'agent1@shrishyamwelfare.org', mobile: '9829012345', role: 'Agent', status: 'Active', lastLogin: '2026-08-10 09:15 AM' }
    ];

    const settings = {
      societyName: 'Shri Shyam Welfare Society, Lohki',
      societyHindiName: 'श्री श्याम वेलफेयर सोसायटी लोहीकी',
      regNo: 'Reg. No. LHK/2021/8945',
      sanNo: 'SAN-SSWS-7821',
      address: 'Village & Post Office Lohki, Tehsil Mahendragarh, District Mahendragarh, Haryana - 123001',
      phone: '+91 98290 12345 / 94140 23456',
      email: 'info@shrishyamwelfare.org',
      website: 'www.shrishyamwelfare.org',
      memberPrefix: 'MEM-2026-',
      receiptPrefix: 'REC-2026-',
      certificatePrefix: 'CRT-2026-',
      agentPrefix: 'AGT-'
    };

    const whatsappLogs = [];

    this.data = {
      agents,
      schemes,
      members,
      payments,
      receipts,
      certificates,
      events,
      payouts,
      users,
      settings,
      whatsappLogs
    };

    this.save();
  },

  // Helper getters (With Agent role restriction filtering)
  getMembers() {
    if (this.currentRole === 'Agent') {
      return this.data.members.filter(m => m.agentId === this.activeAgentId);
    }
    return this.data.members;
  },

  getAllMembersList() {
    return this.data.members;
  },

  getFilteredMembers(filters) {
    let list = this.getMembers();
    if (filters.status) list = list.filter(m => m.status === filters.status);
    if (filters.agentId) list = list.filter(m => m.agentId === filters.agentId);
    return list;
  },

  getAgents() {
    return this.data.agents;
  },

  getSchemes() {
    return this.data.schemes;
  },

  getPayments() {
    if (this.currentRole === 'Agent') {
      return this.data.payments.filter(p => p.agentId === this.activeAgentId);
    }
    return this.data.payments;
  },

  getReceipts() {
    if (this.currentRole === 'Agent') {
      return this.data.receipts.filter(r => r.agentId === this.activeAgentId);
    }
    return this.data.receipts;
  },

  getCertificates() {
    return this.data.certificates;
  },

  getEvents() {
    return this.data.events;
  },

  getPayouts() {
    return this.data.payouts;
  },

  // Add Member Action
  addMember(memberObj) {
    this.data.members.unshift(memberObj);

    const agent = this.data.agents.find(a => a.id === memberObj.agentId);
    if (agent) {
      agent.membersCount++;
      agent.collection += memberObj.joiningAmount;
      agent.commission += Math.round(memberObj.joiningAmount * (agent.commissionRate / 100));
    }

    const recNo = `${this.data.settings.receiptPrefix}${5000 + this.data.receipts.length + 1}`;
    const pDate = memberObj.joiningDate || Utils.toInputDate(new Date());

    const paymentRecord = {
      id: `PAY-${100 + this.data.payments.length + 1}`,
      receiptNo: recNo,
      membershipNo: memberObj.membershipNo,
      memberName: memberObj.name,
      schemeName: memberObj.schemeName,
      agentId: memberObj.agentId,
      agentName: memberObj.agentName,
      amount: memberObj.joiningAmount,
      totalDue: memberObj.joiningAmount,
      amountPaid: memberObj.joiningAmount,
      remainingBalance: 0,
      month: 'Initial Registration',
      paymentType: 'Joining Fee',
      paymentDate: pDate,
      paymentMode: memberObj.paymentMode || 'Cash',
      transactionNo: memberObj.transactionNo || 'N/A',
      remarks: 'Initial Member Registration Payment',
      status: 'Successful'
    };

    this.data.payments.unshift(paymentRecord);
    this.data.receipts.unshift({
      ...paymentRecord,
      issueDate: pDate,
      authorizedBy: 'Admin Secretary'
    });

    const certNo = `${this.data.settings.certificatePrefix}${8000 + this.data.certificates.length + 1}`;
    this.data.certificates.unshift({
      certificateNo: certNo,
      membershipNo: memberObj.membershipNo,
      memberName: memberObj.name,
      fatherHusbandName: memberObj.fatherHusbandName,
      dob: memberObj.dob,
      schemeName: memberObj.schemeName,
      issueDate: memberObj.joiningDate,
      agentName: memberObj.agentName,
      status: 'Active'
    });

    this.save();
    return { member: memberObj, payment: paymentRecord, receiptNo: recNo, certNo };
  },

  // Event Consolidated Billing Generator (Rule #4)
  addConsolidatedEventBilling(params) {
    const { monthYear, eventCount, ratePerEvent, schemeId } = params;
    const count = Number(eventCount) || 1;
    const rate = Number(ratePerEvent) || 200;
    const totalDue = count * rate;
    let countGen = 0;

    this.data.members.forEach(m => {
      if (m.status === 'Active' && (!schemeId || m.schemeId === schemeId)) {
        m.pendingAmount += totalDue;
        m.outstandingBalance += totalDue;
        m.ledger.unshift({
          date: Utils.toInputDate(new Date()),
          type: 'Event Monthly Dues',
          desc: `${monthYear} Consolidated Billing (${count} Marriage Events x ₹${rate})`,
          charge: totalDue,
          credit: 0,
          balance: m.outstandingBalance
        });
        countGen++;
      }
    });

    this.save();
    return { countGen, totalDue };
  },

  // Record Partial or Full Payment with Carried Forward Outstanding (Rule #5)
  addPartialPayment(payObj) {
    const member = this.data.members.find(m => m.membershipNo === payObj.membershipNo);
    const recNo = `${this.data.settings.receiptPrefix}${5000 + this.data.receipts.length + 1}`;

    const totalDue = Number(payObj.totalDue) || Number(payObj.amount);
    const amountPaid = Number(payObj.amountPaid) || Number(payObj.amount);
    const remainingBalance = Math.max(0, totalDue - amountPaid);

    const paymentRecord = {
      id: `PAY-${100 + this.data.payments.length + 1}`,
      receiptNo: recNo,
      membershipNo: payObj.membershipNo,
      memberName: member ? member.name : payObj.memberName,
      schemeName: member ? member.schemeName : payObj.schemeName,
      agentId: member ? member.agentId : payObj.agentId,
      agentName: member ? member.agentName : payObj.agentName,
      amount: amountPaid,
      totalDue,
      amountPaid,
      remainingBalance,
      eventsCount: payObj.eventsCount || null,
      month: payObj.month || 'Current Month',
      paymentType: payObj.paymentType || 'Monthly Contribution',
      paymentDate: payObj.paymentDate || Utils.toInputDate(new Date()),
      paymentMode: payObj.paymentMode || 'Cash',
      transactionNo: payObj.transactionNo || 'N/A',
      remarks: payObj.remarks || 'Monthly contribution payment',
      status: 'Successful'
    };

    this.data.payments.unshift(paymentRecord);
    this.data.receipts.unshift({
      ...paymentRecord,
      issueDate: paymentRecord.paymentDate,
      authorizedBy: 'Society Accountant'
    });

    if (member) {
      member.totalPaid += amountPaid;
      member.outstandingBalance = remainingBalance;
      member.pendingAmount = remainingBalance;

      member.ledger.unshift({
        date: paymentRecord.paymentDate,
        type: payObj.paymentType || 'Monthly Contribution',
        desc: `${payObj.month || 'Monthly'} Payment (Receipt #${recNo})`,
        charge: totalDue,
        credit: amountPaid,
        balance: remainingBalance
      });

      const agent = this.data.agents.find(a => a.id === member.agentId);
      if (agent) {
        agent.collection += amountPaid;
        agent.commission += Math.round(amountPaid * (agent.commissionRate / 100));
      }
    }

    this.save();
    return paymentRecord;
  },

  // Beneficiary Payout Approval (Rule #6)
  disbursePayout(payoutObj) {
    const newPayout = {
      id: `POUT-${100 + this.data.payouts.length + 1}`,
      memberId: payoutObj.memberId,
      beneficiaryName: payoutObj.beneficiaryName,
      schemeName: payoutObj.schemeName,
      type: payoutObj.type,
      amount: Number(payoutObj.amount),
      eventName: payoutObj.eventName,
      date: payoutObj.date || Utils.toInputDate(new Date()),
      status: 'Disbursed',
      refNo: payoutObj.refNo || `TRF-BANK-${Math.floor(100000 + Math.random() * 900000)}`
    };

    this.data.payouts.unshift(newPayout);
    this.save();
    return newPayout;
  },

  // Age Slab Configurator Update
  addAgeSlab(schemeId, slabObj) {
    const scheme = this.data.schemes.find(s => s.id === schemeId);
    if (scheme) {
      scheme.ageSlabs.push({
        id: `SLAB-${Date.now()}`,
        minAge: Number(slabObj.minAge),
        maxAge: Number(slabObj.maxAge),
        joiningAmount: Number(slabObj.joiningAmount),
        supportAmount: Number(slabObj.supportAmount),
        effectiveFrom: slabObj.effectiveFrom || '2026-01-01',
        effectiveTo: slabObj.effectiveTo || '2030-12-31',
        status: 'Active'
      });
      this.save();
    }
  },

  // Add Event
  addEvent(eventObj) {
    const newEvt = {
      id: `EVT-${String(this.data.events.length + 1).padStart(3, '0')}`,
      name: eventObj.name,
      type: eventObj.type,
      eventDate: eventObj.eventDate,
      location: eventObj.location,
      description: eventObj.description,
      status: 'Upcoming',
      totalMembers: 0,
      collection: 0
    };
    this.data.events.unshift(newEvt);
    this.save();
    return newEvt;
  }
};

// Initialize State immediately
State.init();
