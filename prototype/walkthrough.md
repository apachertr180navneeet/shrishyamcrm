# Walkthrough - Shri Shyam Welfare Society ERP Prototype

We have built a modern, responsive, high-fidelity Administration & Management ERP web application prototype for **Shri Shyam Welfare Society, Lohki** (श्री श्याम वेलफेयर सोसायटी लोहीकी). 

The prototype includes state management, realistic Indian demo records (52 members, 10 agents, payments, events, certificates), dynamic business rules (auto age calculation from DOB, configurable scheme age slabs & joining/support amounts), printable official society receipts and gold-bordered certificates, 5 Chart.js analytics visualizations, role permission switching, and 8 comprehensive exportable reports.

---

## Key Highlights & Features Built

### 1. Indian ERP Design System & Responsive Layout
- **Brand Colors**: Deep Navy (`#1B365D`), Royal Blue (`#2563EB`), Saffron Gold (`#D97706`), Crisp White (`#F8FAFC`).
- **Sidebar Navigation**: Complete multi-section navigation matching exact specifications (Dashboard, Master Data, Members, Agents, Payments, Certificates, Events, Reports, Users & Permissions, Settings).
- **Role Switcher Dropdown**: Live role simulator (Super Admin, Admin, Agent, Data Entry, Accountant) that dynamically restricts UI modules and capabilities.

### 2. Admin Dashboard & Analytics
- **8 Top KPI Cards**: Total Members (52), Active Members (46), Inactive Members (3), Total Agents (10), Today's Collection (₹42,500), Monthly Collection (₹3,00,500), Overdue Pending Amounts (13 members), Total Events (5).
- **4 Chart.js Interactive Visualizations**:
  1. *Monthly Collection Trend*: 12-month area chart.
  2. *New Member Registrations*: Monthly bar chart.
  3. *Scheme-wise Member Distribution*: Doughnut chart comparing बुजुर्ग सम्मान योजना and विवाह योजना.
  4. *Top Agent Collections*: Horizontal bar chart ranking top performing agents.

![Main Dashboard](file:///C:/Users/navne/.gemini/antigravity-ide/brain/75a9f628-9139-4f67-b5b0-68916f5b11fe/main_dashboard_1786341116115.png)

---

### 3. 5-Step Member Registration Wizard
- **Step 1: Basic Details**: Auto Membership No (`MEM-2026-XXXX`), DOB picker with **auto-calculated age in years**, Gotra, Caste, Address.
- **Step 2: Documents**: Aadhaar ID input & simulated photo/document attachment boxes.
- **Step 3: Nominee / वारिसदार**: Dual Nominee input support (Name, Relation, Mobile, Aadhaar).
- **Step 4: Scheme Enrolment**: Auto-selects applicable Age Slab & populates Joining Amount (₹1,100–₹2,500) and Support Amount (₹100–₹500/mo) based on calculated age and scheme.
- **Step 5: Initial Payment & Confirmation**: Summary confirmation with options to save & generate receipt and certificate immediately.

![Add Member Wizard](file:///C:/Users/navne/.gemini/antigravity-ide/brain/75a9f628-9139-4f67-b5b0-68916f5b11fe/add_member_wizard_1786341126916.png)

---

### 4. Society Members Directory & Member Profile
- **Filterable Table**: Live search by Name, Mobile, or Member Number, with filters for Scheme, Agent, and Status.
- **Detailed Profile**: Header banner with status badges, and tabs for Overview, Personal Info, Nominees (वारिसदार), and Payment History.

![Members Directory](file:///C:/Users/navne/.gemini/antigravity-ide/brain/75a9f628-9139-4f67-b5b0-68916f5b11fe/members_directory_1786341137031.png)

---

### 5. Master Data Configurator (Age Slabs & Schemes)
- Configurable age slabs for both primary schemes:
  - **बुजुर्ग सम्मान योजना (Senior Welfare Scheme)**: 18–40 yrs (₹1,100 / ₹200), 41–60 yrs (₹1,500 / ₹300), 60–75 yrs (₹2,000 / ₹400), 75+ yrs (₹2,500 / ₹500).
  - **विवाह (कन्यादान/गौत्र) योजना (Marriage Scheme)**: 0–5 yrs (₹1,100 / ₹100), 6–9 yrs (₹1,100 / ₹200), 10–13 yrs (₹2,000 / ₹300), 14–17 yrs (₹2,500 / ₹400), 17+ yrs (₹2,500 / ₹500).
- Admin can add or modify age slab limits and amounts live.

---

### 6. Official Society Receipts & Registration Certificates
- **Official Receipt**: Styled after printed society receipts with Society Emblem logo, Registration No, SAN Code, Receipt No, Date, Member details, Amount in words, QR stamp, and Signature line.
- **Gold Border Registration Certificate**: Official society certificate layout with gold double border, traditional emblem, bilingual headers, issuance date, and president signature line.
- **Print Optimization**: Includes `@media print` rules for browser printing.

---

### 7. Reports Center & Excel CSV Export
- 8 comprehensive report modes: Collection Report, Agent-wise Collection, Pending Payment Report, Commission Report, Member Directory Report, Event Collection Report, Monthly Collection, Payment Report.
- **One-click Export to Excel (CSV)** and Print Report actions.

![Reports Center](file:///C:/Users/navne/.gemini/antigravity-ide/brain/75a9f628-9139-4f67-b5b0-68916f5b11fe/reports_center_1786341148145.png)

---

## Verification & How to Access

The prototype is currently running live on the local web server:
- **Local URL**: `http://localhost:8080`
- **Root Directory**: `d:\nitinsirproject\shrishyamcrm`

### Key Test Steps Performed
1. Navigated to `http://localhost:8080` and verified dashboard metrics & Chart.js rendering.
2. Verified 5-Step Member Registration Wizard with auto-calculated age and age slab selection.
3. Verified Members Directory search and filters.
4. Tested Reports Center switching and CSV export.
