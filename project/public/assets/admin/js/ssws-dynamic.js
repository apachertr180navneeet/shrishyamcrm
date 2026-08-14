/**
 * Shri Shyam Welfare Society ERP - Dynamic UI & Flow Engine
 */

const SSWS = {
    currentLang: localStorage.getItem('SSWS_LANG') || 'hi',
    currentRole: localStorage.getItem('SSWS_ROLE') || 'Super Admin',

    i18n: {
        en: {
            appName: "Shri Shyam Welfare Society ERP",
            societyTitle: "Shri Shyam Welfare Society Lohki",
            dashboard: "Dashboard",
            schemesMaster: "Schemes Master",
            ageSlabs: "Age Slabs",
            allMembers: "All Members",
            addMember: "Add Member (5-Step)",
            allAgents: "All Agents",
            paymentEntry: "Payment Entry",
            paymentList: "Payment History",
            receipts: "Receipts",
            certificates: "Certificates",
            events: "Marriage Events",
            payouts: "Beneficiary Payouts",
            ledger: "Financial Ledgers",
            whatsapp: "WhatsApp Center",
            reports: "Reports Center",
            users: "Users & Roles",
            settings: "Settings",
            mainMenu: "Main Menu",
            masterRecords: "Master Records",
            memberEnrolment: "Member Enrolment",
            agentNetwork: "Agent Network",
            collections: "Collections & Accounting",
            certificatesEvents: "Certificates & Events",
            reportsAdmin: "Reports & Admin",
            totalMembers: "Total Members",
            activeMembers: "Active Members",
            inactiveMembers: "Inactive Members",
            totalAgents: "Total Agents",
            todayCollection: "Today's Collection",
            monthlyCollection: "Month Collection",
            overduePending: "Pending Payments",
            totalEvents: "Total Events",
            adminDashboardTitle: "Administrative Dashboard",
            adminSubtitle: "Real-time administration, collection statistics, event billing and payout pool summary",
            agentViewBanner: "Agent Mode: Showing only members assigned to Agent (Rameshwar Lal Sharma - AGT-001)"
        },
        hi: {
            appName: "श्री श्याम वेलफेयर सोसायटी ईआरपी",
            societyTitle: "श्री श्याम वेलफेयर सोसायटी लोहीकी",
            dashboard: "डैशबोर्ड",
            schemesMaster: "योजनाएं (Schemes)",
            ageSlabs: "आयु वर्ग (Age Slabs)",
            allMembers: "सभी सदस्य (Members)",
            addMember: "नया सदस्य जोड़ें (5-Step)",
            allAgents: "सभी एजेंट (Agents)",
            paymentEntry: "भुगतान प्रविष्टि",
            paymentList: "भुगतान इतिहास",
            receipts: "सोसायटी रसीदें",
            certificates: "सदस्यता प्रमाण-पत्र",
            events: "विवाह सहायता कार्यक्रम",
            payouts: "सहायता वितरण (Payouts)",
            ledger: "वित्तीय लेजर खाता",
            whatsapp: "व्हाट्सएप सेवा केंद्र",
            reports: "रिपोर्ट केंद्र (Reports)",
            users: "उपयोगकर्ता एवं अधिकार",
            settings: "प्रणाली सेटिंग्स",
            mainMenu: "मुख्य मेनू",
            masterRecords: "मास्टर रिकॉर्ड",
            memberEnrolment: "सदस्य नामांकन",
            agentNetwork: "एजेंट नेटवर्क",
            collections: "संग्रह एवं लेखा",
            certificatesEvents: "प्रमाणपत्र व कार्यक्रम",
            reportsAdmin: "रिपोर्ट व प्रशासन",
            totalMembers: "कुल पंजीकृत सदस्य",
            activeMembers: "सक्रिय सदस्य",
            inactiveMembers: "निष्क्रिय सदस्य",
            totalAgents: "कुल एजेंट",
            todayCollection: "आज का संग्रह",
            monthlyCollection: "इस माह का संग्रह",
            overduePending: "बकाई राशि (Pending)",
            totalEvents: "कुल विवाह कार्यक्रम",
            adminDashboardTitle: "प्रशासनिक डैशबोर्ड",
            adminSubtitle: "वास्तविक समय प्रशासन, संग्रह आंकड़े, विवाह सहायता बिलिंग एवं लाभांश विवरण",
            agentViewBanner: "एजेंट मोड: केवल आपके आवंटित सदस्य (रामेश्वर लाल शर्मा - AGT-001) प्रदर्शित हो रहे हैं"
        }
    },

    init() {
        this.applyLanguage(this.currentLang);
        this.applyRole(this.currentRole);
    },

    toggleLanguage() {
        this.currentLang = (this.currentLang === 'hi') ? 'en' : 'hi';
        localStorage.setItem('SSWS_LANG', this.currentLang);
        this.applyLanguage(this.currentLang);
        this.showToast(`Language switched to: ${this.currentLang === 'hi' ? 'हिंदी (Hindi)' : 'English'}`, 'info');
    },

    applyLanguage(lang) {
        const dict = this.i18n[lang] || this.i18n.en;
        const toggleBtn = document.getElementById('langToggleLabel');
        if (toggleBtn) {
            toggleBtn.innerText = (lang === 'hi') ? 'हिंदी / English' : 'English / हिंदी';
        }

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key]) {
                el.innerText = dict[key];
            }
        });
    },

    switchRole(role) {
        this.currentRole = role;
        localStorage.setItem('SSWS_ROLE', this.currentRole);
        this.applyRole(role);
        this.showToast(`Switched active view role to: ${role}`, 'info');
    },

    applyRole(role) {
        const roleSwitcher = document.getElementById('globalRoleSwitcher');
        if (roleSwitcher) roleSwitcher.value = role;

        const roleBadge = document.getElementById('displayRoleBadge');
        if (roleBadge) roleBadge.innerText = role;

        const banner = document.getElementById('agentViewBanner');
        if (banner) {
            if (role === 'Agent') banner.classList.remove('d-none');
            else banner.classList.add('d-none');
        }
    },

    showToast(message, type = 'success', duration = 3500) {
        let container = document.getElementById('toast-container-custom');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container-custom';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '999999';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : (type === 'info' ? 'primary' : 'success')} alert-dismissible shadow-lg fade show mb-2`;
        toast.style.minWidth = '300px';
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-check-circle')} me-2 fs-5"></i>
                <div class="fw-semibold">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, duration);
    }
};

document.addEventListener('DOMContentLoaded', function () {
    SSWS.init();
});
