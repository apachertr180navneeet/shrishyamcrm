<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Nominee;
use App\Models\MemberDocument;
use App\Services\MemberRegistrationService;
use App\Services\CertificateService;
use App\Services\WhatsAppService;
use App\Services\NumberSeriesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'agent', 'ageSlab', 'nominees']);

        // Agent-level backend query scoping
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            // Escape LIKE wildcards so user input matches literally (MySQL default ESCAPE '\')
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('membership_no', 'like', "%{$search}%")
                  ->orWhere('memberID', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('aadhaar_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('scheme_id')) {
            $query->where('scheme_id', $request->scheme_id);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $members = $query->latest('id')->paginate(15)->withQueryString();
        $schemes = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')->orderBy('min_age')])
            ->where('status', 'Active')
            ->get();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->get();

        return view('admin.members.index', compact('members', 'schemes', 'agents'));
    }

    public function create()
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $schemes = Scheme::with('ageSlabs')->where('status', 'Active')->get();
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->get();
        // Use the thread-safe number series to anticipate the next membership number
        $nextMemNum = NumberSeriesService::peekNextNumber('MEM', ['prefix' => 'MEM-' . date('Y') . '-', 'initial_value' => 1001, 'padding' => 4]);

        return view('admin.members.create', compact('schemes', 'agents', 'nextMemNum'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id) {
            $request->merge(['agent_id' => $user->agent_id]);
        }

        $request->validate([
            'full_name' => 'required|string|max:150',
            'memberID' => 'nullable|string|max:100|unique:members,memberID',
            'mobile' => 'required|string|max:20',
            'dob' => 'required|date',
            'scheme_id' => 'required|exists:schemes,id',
            'agent_id' => 'required|exists:agents,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $files = [
                'photo' => $request->file('photo'),
                'aadhaar' => $request->file('aadhaar_doc'),
                'address' => $request->file('address_doc'),
            ];

            // Pass only expected member fields (prevent mass assignment injection)
            $memberData = $request->only([
                'membership_no', 'memberID', 'full_name', 'father_spouse_name', 'mother_name', 'gender',
                'dob', 'mobile', 'gotra', 'caste', 'address', 'district', 'state', 'pincode',
                'aadhaar_no', 'scheme_id', 'age_slab_id', 'joining_amount', 'monthly_support_amount',
                'agent_id', 'joining_date', 'payment_mode',
                'reference_no', 'initial_paid_amount', 'nominee1_name', 'nominee1_father',
                'nominee1_relation', 'nominee1_mobile', 'nominee1_aadhaar', 'nominee1_address',
                'nominee1_share', 'nominee2_name', 'nominee2_father', 'nominee2_relation',
                'nominee2_mobile', 'nominee2_aadhaar', 'nominee2_address', 'nominee2_share',
            ]);

            $member = MemberRegistrationService::register($memberData, array_filter($files));

            return redirect()->route('admin.members.show', $member->id)
                ->with('success', "Member {$member->full_name} enrolled successfully with Membership No: {$member->membership_no}!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error enrolling member: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'ageSlab', 'agent', 'nominees', 'payments', 'documents', 'ledgers', 'certificates']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        $member = $query->findOrFail($id);
        $whatsappData = WhatsAppService::getDueReminderMessage($member);

        return view('admin.members.show', compact('member', 'whatsappData'));
    }

    public function edit(Request $request, $id)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'ageSlab', 'agent', 'nominees', 'documents']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($member);
        }

        $schemes = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')->orderBy('min_age')])->where('status', 'Active')->get();
        $agents = Agent::where('status', 'Active')->orderBy('name')->get();
        $ageSlabs = AgeSlab::where('status', 'Active')->orderBy('min_age')->get();

        return view('admin.members.edit', compact('member', 'schemes', 'agents', 'ageSlabs'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:150',
            'memberID' => 'nullable|string|max:100|unique:members,memberID,' . $member->id,
            'mobile' => 'required|string|max:20',
            'father_spouse_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'gender' => 'required|string|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:120',
            'gotra' => 'nullable|string|max:100',
            'caste' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:20',
            'status' => 'required|string|in:Active,Inactive,Suspended',
            'scheme_id' => 'required|exists:schemes,id',
            'age_slab_id' => 'nullable|exists:age_slabs,id',
            'agent_id' => 'nullable|exists:agents,id',
            'monthly_support_amount' => 'nullable|numeric|min:0',
            'joining_amount' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'document_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        $dob = $request->filled('dob') ? Carbon::parse($request->dob) : $member->dob;
        $age = $dob ? $dob->age : ($request->input('age', $member->age));

        $schemeId = $request->scheme_id ?? $member->scheme_id;
        $ageSlabId = $request->age_slab_id;
        if (!$ageSlabId && $schemeId) {
            $matchingSlab = \App\Models\AgeSlab::where('scheme_id', $schemeId)
                ->where('status', 'Active')
                ->where('min_age', '<=', $age)
                ->where('max_age', '>=', $age)
                ->first();
            $ageSlabId = $matchingSlab ? $matchingSlab->id : $member->age_slab_id;
        }

        $memberData = [
            'memberID' => $request->filled('memberID') ? trim($request->memberID) : null,
            'full_name' => $request->full_name,
            'father_spouse_name' => $request->father_spouse_name,
            'mother_name' => $request->mother_name,
            'gender' => $request->gender,
            'dob' => $dob ? $dob->toDateString() : null,
            'age' => $age,
            'mobile' => $request->mobile,
            'gotra' => $request->gotra,
            'caste' => $request->caste,
            'address' => $request->address,
            'district' => $request->district ?? $member->district,
            'state' => $request->state ?? $member->state,
            'pincode' => $request->pincode,
            'aadhaar_no' => $request->aadhaar_no,
            'status' => $request->status,
            'scheme_id' => $schemeId,
            'age_slab_id' => $ageSlabId,
            'agent_id' => $request->agent_id ?? $member->agent_id,
        ];

        if ($request->filled('monthly_support_amount')) {
            $memberData['monthly_support_amount'] = (float)$request->monthly_support_amount;
        }
        if ($request->filled('joining_amount')) {
            $memberData['joining_amount'] = (float)$request->joining_amount;
        }
        if ($request->filled('joining_date')) {
            $memberData['joining_date'] = $request->joining_date;
        }

        // Handle Photo Upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $safeExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $filename = 'member_' . $member->id . '_photo_' . time() . '_' . random_int(1000, 9999) . '.' . $safeExt;
            $path = $file->storeAs('uploads/documents', $filename, 'public');
            // Store full image URL in database
            $memberData['photo'] = asset('storage/' . $path);

            MemberDocument::create([
                'member_id' => $member->id,
                'document_type' => 'Photo',
                'title' => 'Member Profile Photo',
                'file_path' => '/storage/' . $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->check() ? auth()->id() : null,
            ]);
        }

        // Handle Additional Document Upload
        if ($request->hasFile('document_file') && $request->file('document_file')->isValid()) {
            $file = $request->file('document_file');
            $safeExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $docType = $request->input('document_type', 'Identity');
            $filename = 'member_' . $member->id . '_' . strtolower($docType) . '_' . time() . '_' . random_int(1000, 9999) . '.' . $safeExt;
            $path = $file->storeAs('uploads/documents', $filename, 'public');

            MemberDocument::create([
                'member_id' => $member->id,
                'document_type' => ucfirst($docType),
                'title' => ucfirst($docType) . ' Document',
                'file_path' => '/storage/' . $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->check() ? auth()->id() : null,
            ]);
        }

        $member->update($memberData);

        // Nominee 1
        if ($request->filled('nominee1_name')) {
            $n1 = Nominee::where('member_id', $member->id)->where('priority', 1)->first();
            $n1Data = [
                'name' => $request->nominee1_name,
                'father_husband_name' => $request->nominee1_father ?? null,
                'relation' => $request->nominee1_relation ?? 'Spouse',
                'mobile' => $request->nominee1_mobile ?? null,
                'aadhaar_no' => $request->nominee1_aadhaar ?? null,
                'address' => $request->nominee1_address ?? $member->address,
                'percentage' => $request->nominee1_share ?? 100.0,
                'priority' => 1,
            ];
            if ($n1) {
                $n1->update($n1Data);
            } else {
                $n1Data['member_id'] = $member->id;
                Nominee::create($n1Data);
            }
        }

        // Nominee 2
        if ($request->filled('nominee2_name')) {
            $n2 = Nominee::where('member_id', $member->id)->where('priority', 2)->first();
            $n2Data = [
                'name' => $request->nominee2_name,
                'father_husband_name' => $request->nominee2_father ?? null,
                'relation' => $request->nominee2_relation ?? 'Son',
                'mobile' => $request->nominee2_mobile ?? null,
                'aadhaar_no' => $request->nominee2_aadhaar ?? null,
                'address' => $request->nominee2_address ?? $member->address,
                'percentage' => $request->nominee2_share ?? 50.0,
                'priority' => 2,
            ];
            if ($n2) {
                $n2->update($n2Data);
            } else {
                $n2Data['member_id'] = $member->id;
                Nominee::create($n2Data);
            }
        }

        return redirect()->route('admin.members.show', $member->id)->with('success', "Member {$member->full_name} ({$member->membership_no}) details updated successfully.");
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        $member->delete();
        return redirect()->route('admin.members.index')->with('success', 'Member record archived successfully.');
    }

    public function certificatePdf($id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $query->findOrFail($id); // authorization check (404 if not scoped)
        $pdf = CertificateService::generatePdf($id);
        return $pdf->download("SSWS_Certificate_{$id}.pdf");
    }

    public function ledgerPdf($id, Request $request)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        $pdf = \App\Services\MemberLedgerCardService::generatePdf($member->id);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $member->membership_no . '_' . $member->full_name);
        $fileName = "Ledger_Statement_{$cleanName}.pdf";

        if ($request->get('action') === 'stream' || $request->get('view') === '1') {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    /**
     * Download CSV Member Import Template customized for selected scheme.
     */
    public function downloadTemplate(Request $request)
    {
        $schemeId = $request->query('scheme_id');
        $scheme = null;
        if (!empty($schemeId)) {
            $scheme = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')->orderBy('min_age')])
                ->where('status', 'Active')
                ->find($schemeId);
        }

        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $defaultAgent = null;
        if ($isAgent) {
            $defaultAgent = Agent::find($user->agent_id);
        } else {
            $defaultAgent = Agent::where('status', 'Active')->orderBy('id')->first();
        }
        $agentCode = $defaultAgent ? $defaultAgent->agent_code : 'AGT-001';

        $columns = [
            'Full Name *',
            'Mobile *',
            'Date of Birth (YYYY-MM-DD) *',
            'Age',
            'Gender (Male/Female)',
            'Father / Spouse Name',
            'Mother Name',
            'Gotra',
            'Caste',
            'Address',
            'District',
            'State',
            'Pincode',
            'Aadhaar No',
            'Scheme Code',
            'Agent Code',
            'Joining Date (YYYY-MM-DD)',
            'Joining Amount',
            'Monthly Support Amount',
            'Initial Paid Amount',
            'Payment Mode',
            'Reference No',
            'Nominee 1 Name',
            'Nominee 1 Relation',
            'Nominee 1 Mobile',
            'Nominee 1 Aadhaar',
            'Nominee 1 Share %',
            'Nominee 2 Name',
            'Nominee 2 Relation',
            'Nominee 2 Mobile',
            'Nominee 2 Aadhaar',
            'Nominee 2 Share %',
        ];

        $sampleRows = [];

        if ($scheme) {
            $slabs = $scheme->ageSlabs;
            if ($slabs->isNotEmpty()) {
                foreach ($slabs as $idx => $slab) {
                    $sampleAge = min($slab->max_age, max($slab->min_age, (int)round(($slab->min_age + $slab->max_age) / 2)));
                    $sampleDob = Carbon::now()->subYears($sampleAge)->format('Y-m-d');
                    $sampleRows[] = [
                        $idx === 0 ? 'रमेश कुमार (Ramesh Kumar)' : 'सुनीता देवी (Sunita Devi)',
                        '9812' . str_pad($idx + 100001, 6, '0', STR_PAD_LEFT),
                        $sampleDob,
                        $sampleAge,
                        $idx === 0 ? 'Male' : 'Female',
                        $idx === 0 ? 'श्री सोहन लाल' : 'पत्नी श्री रमेश कुमार',
                        $idx === 0 ? 'श्रीमती शांति देवी' : '',
                        'कौशिक (Kaushik)',
                        'General',
                        'वार्ड नंबर 4, लोहिड़ी',
                        'Balotra',
                        'Rajasthan',
                        '344022',
                        '1234567890' . ($idx + 10),
                        $scheme->code,
                        $agentCode,
                        Carbon::now()->format('Y-m-d'),
                        (string)(float)$slab->joining_amount,
                        (string)(float)$slab->support_amount,
                        (string)(float)$slab->joining_amount,
                        'Cash',
                        'UTR' . time() . ($idx + 1),
                        $idx === 0 ? 'सुनीता देवी' : 'अमित कुमार',
                        $idx === 0 ? 'Spouse' : 'Son',
                        '981299999' . $idx,
                        '9876543210' . ($idx + 10),
                        '100',
                        $idx === 0 ? 'अमित कुमार' : '',
                        $idx === 0 ? 'Son' : '',
                        $idx === 0 ? '9812888881' : '',
                        $idx === 0 ? '5555666677' . ($idx + 10) : '',
                        $idx === 0 ? '50' : '',
                    ];
                }
            } else {
                $sampleRows[] = [
                    'रमेश कुमार (Ramesh Kumar)',
                    '9812000001',
                    '1985-05-15',
                    '41',
                    'Male',
                    'श्री सोहन लाल',
                    'श्रीमती शांति देवी',
                    'Kaushik',
                    'General',
                    'वार्ड नंबर 4, लोहिड़ी',
                    'Balotra',
                    'Rajasthan',
                    '344022',
                    '123456789012',
                    $scheme->code,
                    $agentCode,
                    Carbon::now()->format('Y-m-d'),
                    '1100',
                    '200',
                    '1100',
                    'Cash',
                    'UTR12345678',
                    'सुनीता देवी',
                    'Spouse',
                    '9812999999',
                    '987654321098',
                    '100',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
            }
            $cleanCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $scheme->code);
            $fileName = "Member_Import_Template_{$cleanCode}.csv";
        } else {
            // General template with all active schemes
            $activeSchemes = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')->orderBy('min_age')])
                ->where('status', 'Active')
                ->get();

            if ($activeSchemes->isEmpty()) {
                $activeSchemes = Scheme::all();
            }

            foreach ($activeSchemes as $idx => $sch) {
                $firstSlab = $sch->ageSlabs->first();
                $sampleAge = $firstSlab ? (int)round(($firstSlab->min_age + $firstSlab->max_age) / 2) : 40;
                $sampleDob = Carbon::now()->subYears($sampleAge)->format('Y-m-d');
                $joiningAmount = $firstSlab ? (string)(float)$firstSlab->joining_amount : '1100';
                $supportAmount = $firstSlab ? (string)(float)$firstSlab->support_amount : '200';

                $sampleRows[] = [
                    $idx === 0 ? 'रमेश कुमार (Ramesh Kumar)' : ($idx === 1 ? 'राधा रानी (Radha Rani)' : 'सुरेश शर्मा'),
                    '9812' . str_pad($idx + 100001, 6, '0', STR_PAD_LEFT),
                    $sampleDob,
                    $sampleAge,
                    $idx % 2 === 0 ? 'Male' : 'Female',
                    'पिता/पति का नाम',
                    'माता का नाम',
                    'Kaushik',
                    'General',
                    'वार्ड नंबर 4, लोहिड़ी',
                    'Balotra',
                    'Rajasthan',
                    '344022',
                    '1234567890' . ($idx + 10),
                    $sch->code,
                    $agentCode,
                    Carbon::now()->format('Y-m-d'),
                    $joiningAmount,
                    $supportAmount,
                    $joiningAmount,
                    'Cash',
                    'UTR' . time() . ($idx + 1),
                    'नॉमिनी 1 का नाम',
                    'Spouse',
                    '981299999' . $idx,
                    '9876543210' . ($idx + 10),
                    '100',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
            }
            $fileName = "Member_Import_Template_General.csv";
        }

        $callback = function () use ($columns, $sampleRows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens Hindi characters properly
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Import Members from uploaded CSV using selected or detected scheme.
     */
    public function import(Request $request)
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;

        $request->validate([
            'csv_file' => 'required|file|max:10240',
            'scheme_id' => 'nullable|exists:schemes,id',
            'default_agent_id' => 'nullable|exists:agents,id',
        ]);

        $file = $request->file('csv_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return back()->with('error', 'Please upload a valid CSV (.csv) file.');
        }

        $filePath = $file->getRealPath();
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to read the uploaded CSV file.');
        }

        // Detect and skip UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Auto-detect delimiter from first line
        $firstLine = fgets($handle);
        $delimiter = ',';
        if ($firstLine !== false) {
            $commaCount = substr_count($firstLine, ',');
            $semicolonCount = substr_count($firstLine, ';');
            $tabCount = substr_count($firstLine, "\t");
            if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
                $delimiter = "\t";
            }
        }
        rewind($handle);
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (!$headerRow) {
            fclose($handle);
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Map header indices
        $headerMap = [];
        foreach ($headerRow as $idx => $headerText) {
            $raw = trim($headerText);
            // Remove parenthesized comments e.g. (YYYY-MM-DD), (₹), (Male/Female)
            $noParens = preg_replace('/\(.*?\)/u', '', $raw);
            $clean = strtolower(trim($noParens));
            $clean = preg_replace('/[\*\(\)₹\/\\\]/u', '', $clean);
            $clean = str_replace([' ', '-'], '_', $clean);
            $clean = preg_replace('/_+/', '_', trim($clean, '_'));

            $rawClean = strtolower($raw);
            $rawClean = preg_replace('/[\*\(\)₹\/\\\]/u', '', $rawClean);
            $rawClean = str_replace([' ', '-'], '_', $rawClean);
            $rawClean = preg_replace('/_+/', '_', trim($rawClean, '_'));

            if (in_array($clean, ['full_name', 'name', 'member_name', 'member', 'पूरा_नाम', 'नाम']) || str_starts_with($clean, 'full_name')) {
                $headerMap['full_name'] = $idx;
            } elseif (in_array($clean, ['mobile', 'phone', 'contact', 'mobile_no', 'phone_no', 'मोबाइल']) || str_starts_with($clean, 'mobile')) {
                $headerMap['mobile'] = $idx;
            } elseif (in_array($clean, ['dob', 'date_of_birth', 'birth_date', 'birthdate', 'जन्म_तिथि', 'जन्म_दिनांक']) || str_contains($clean, 'dob') || str_contains($clean, 'birth') || str_contains($rawClean, 'birth') || str_contains($rawClean, 'dob')) {
                $headerMap['dob'] = $idx;
            } elseif ($clean === 'age' || in_array($clean, ['उम्र', 'आयु'])) {
                $headerMap['age'] = $idx;
            } elseif (in_array($clean, ['gender', 'sex', 'लिंग']) || str_starts_with($clean, 'gender')) {
                $headerMap['gender'] = $idx;
            } elseif (in_array($clean, ['father_spouse_name', 'father_name', 'husband_name', 'spouse_name', 'पिता_पति_का_नाम', 'पिता_का_नाम']) || str_contains($clean, 'father') || str_contains($clean, 'spouse') || str_contains($clean, 'husband')) {
                $headerMap['father_spouse_name'] = $idx;
            } elseif (in_array($clean, ['mother_name', 'mother', 'माता_का_नाम']) || str_contains($clean, 'mother')) {
                $headerMap['mother_name'] = $idx;
            } elseif (str_contains($clean, 'gotra') || str_contains($clean, 'gothra') || str_contains($clean, 'गोत्र')) {
                $headerMap['gotra'] = $idx;
            } elseif (str_contains($clean, 'caste') || str_contains($clean, 'category') || str_contains($clean, 'जाति')) {
                $headerMap['caste'] = $idx;
            } elseif (str_contains($clean, 'address') || str_contains($clean, 'village') || str_contains($clean, 'पता')) {
                $headerMap['address'] = $idx;
            } elseif (str_contains($clean, 'district') || str_contains($clean, 'जिला') || str_contains($clean, 'ज़िला')) {
                $headerMap['district'] = $idx;
            } elseif ($clean === 'state' || str_contains($clean, 'राज्य')) {
                $headerMap['state'] = $idx;
            } elseif (str_contains($clean, 'pincode') || str_contains($clean, 'pin') || str_contains($clean, 'zip') || str_contains($clean, 'पिनकोड')) {
                $headerMap['pincode'] = $idx;
            } elseif (str_contains($clean, 'aadhaar') || str_contains($clean, 'adhaar') || str_contains($clean, 'aadhar') || str_contains($clean, 'आधार')) {
                if (str_contains($clean, 'nominee') || str_contains($clean, 'नॉमिनी')) {
                    // Handled in nominee block below
                } else {
                    $headerMap['aadhaar_no'] = $idx;
                }
            } elseif (str_contains($clean, 'scheme') || str_contains($clean, 'योजना')) {
                $headerMap['scheme_code'] = $idx;
            } elseif (str_contains($clean, 'agent') || str_contains($clean, 'एजेंट')) {
                $headerMap['agent_code'] = $idx;
            } elseif (str_contains($clean, 'joining_date') || str_contains($clean, 'enrollment_date') || str_contains($clean, 'जुड़ने_की_तिथि')) {
                $headerMap['joining_date'] = $idx;
            } elseif (str_contains($clean, 'joining_amount') || str_contains($clean, 'joining_fee') || str_contains($clean, 'पंजीकरण_शुल्क')) {
                $headerMap['joining_amount'] = $idx;
            } elseif (str_contains($clean, 'support_amount') || str_contains($clean, 'सहयोग_राशि')) {
                $headerMap['monthly_support_amount'] = $idx;
            } elseif (str_contains($clean, 'initial_paid') || str_contains($clean, 'paid_amount') || str_contains($clean, 'जमा_राशि')) {
                $headerMap['initial_paid_amount'] = $idx;
            } elseif (str_contains($clean, 'payment_mode') || str_contains($clean, 'भुगतान_माध्यम')) {
                $headerMap['payment_mode'] = $idx;
            } elseif (str_contains($clean, 'reference_no') || str_contains($clean, 'utr') || str_contains($clean, 'संदर्भ_संख्या')) {
                $headerMap['reference_no'] = $idx;
            } elseif (str_contains($clean, 'nominee') || str_contains($clean, 'नॉमिनी')) {
                if (str_contains($clean, '2') || str_contains($clean, 'two')) {
                    if (str_contains($clean, 'relation') || str_contains($clean, 'संबंध')) {
                        $headerMap['nominee2_relation'] = $idx;
                    } elseif (str_contains($clean, 'mobile') || str_contains($clean, 'phone') || str_contains($clean, 'मोबाइल')) {
                        $headerMap['nominee2_mobile'] = $idx;
                    } elseif (str_contains($clean, 'aadhaar') || str_contains($clean, 'adhaar') || str_contains($clean, 'aadhar') || str_contains($clean, 'आधार')) {
                        $headerMap['nominee2_aadhaar'] = $idx;
                    } elseif (str_contains($clean, 'share') || str_contains($clean, 'percent') || str_contains($clean, 'हिस्सा')) {
                        $headerMap['nominee2_share'] = $idx;
                    } else {
                        $headerMap['nominee2_name'] = $idx;
                    }
                } else {
                    if (str_contains($clean, 'relation') || str_contains($clean, 'संबंध')) {
                        $headerMap['nominee1_relation'] = $idx;
                    } elseif (str_contains($clean, 'mobile') || str_contains($clean, 'phone') || str_contains($clean, 'मोबाइल')) {
                        $headerMap['nominee1_mobile'] = $idx;
                    } elseif (str_contains($clean, 'aadhaar') || str_contains($clean, 'adhaar') || str_contains($clean, 'aadhar') || str_contains($clean, 'आधार')) {
                        $headerMap['nominee1_aadhaar'] = $idx;
                    } elseif (str_contains($clean, 'share') || str_contains($clean, 'percent') || str_contains($clean, 'हिस्सा')) {
                        $headerMap['nominee1_share'] = $idx;
                    } else {
                        $headerMap['nominee1_name'] = $idx;
                    }
                }
            }
        }

        if (!isset($headerMap['full_name']) || !isset($headerMap['mobile'])) {
            fclose($handle);
            return back()->with('error', 'CSV must contain at least "Full Name" and "Mobile" columns.');
        }

        // Cache Schemes and Agents
        $allSchemes = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')])->get();
        $schemeByCode = [];
        $schemeById = [];
        foreach ($allSchemes as $s) {
            $schemeByCode[strtoupper($s->code)] = $s;
            $schemeById[$s->id] = $s;
        }

        $allAgents = Agent::where('status', 'Active')->get();
        $agentByCode = [];
        $agentById = [];
        foreach ($allAgents as $a) {
            $agentByCode[strtoupper($a->agent_code)] = $a;
            if ($a->code) {
                $agentByCode[strtoupper($a->code)] = $a;
            }
            $agentById[$a->id] = $a;
        }

        $selectedScheme = $request->filled('scheme_id') ? ($schemeById[$request->scheme_id] ?? Scheme::find($request->scheme_id)) : null;

        $rowNum = 1;
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;

            // Skip blank row
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $getValue = function($key, $default = null) use ($row, $headerMap) {
                if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                    $val = trim((string)$row[$headerMap[$key]]);
                    return $val !== '' ? $val : $default;
                }
                return $default;
            };

            $fullName = $getValue('full_name');
            if (empty($fullName)) {
                $skippedCount++;
                $errors[] = "Row #{$rowNum}: Member full name is missing.";
                continue;
            }

            // Skip template sample row if unchanged
            if (str_contains($fullName, 'Ramesh Kumar') && str_contains($fullName, 'रमेश कुमार')) {
                continue;
            }

            $rawMobile = $getValue('mobile', '');
            $mobile = preg_replace('/[^0-9]/', '', (string)$rawMobile);
            if (strlen($mobile) < 10) {
                $skippedCount++;
                $errors[] = "Row #{$rowNum} ({$fullName}): Mobile number is invalid ('{$rawMobile}'). Must contain at least 10 digits.";
                continue;
            }

            // Scheme Resolution
            $scheme = $selectedScheme;
            if (!$scheme) {
                $schemeCodeInput = strtoupper(trim((string)$getValue('scheme_code', '')));
                if ($schemeCodeInput && isset($schemeByCode[$schemeCodeInput])) {
                    $scheme = $schemeByCode[$schemeCodeInput];
                } elseif ($schemeCodeInput && is_numeric($schemeCodeInput) && isset($schemeById[(int)$schemeCodeInput])) {
                    $scheme = $schemeById[(int)$schemeCodeInput];
                } else {
                    $scheme = $allSchemes->firstWhere('status', 'Active') ?: $allSchemes->first();
                }
            }

            if (!$scheme) {
                $skippedCount++;
                $errors[] = "Row #{$rowNum} ({$fullName}): Scheme could not be determined. Please select or specify a scheme.";
                continue;
            }

            // DOB and Age Resolution
            $dobInput = $getValue('dob');
            $ageInput = $getValue('age');
            $dob = null;

            if ($dobInput) {
                try {
                    $dob = Carbon::parse(str_replace('/', '-', $dobInput));
                } catch (\Exception $e) {
                    $dob = null;
                }
            }

            if (!$dob && $ageInput && is_numeric($ageInput) && (int)$ageInput > 0) {
                $dob = Carbon::now()->subYears((int)$ageInput)->startOfYear();
            }

            if (!$dob) {
                $skippedCount++;
                $errors[] = "Row #{$rowNum} ({$fullName}): Valid Date of Birth (YYYY-MM-DD or DD-MM-YYYY) or Age is required.";
                continue;
            }

            // Agent Resolution
            $agentId = null;
            if ($isAgent) {
                $agentId = $user->agent_id;
            } else {
                $agentInput = strtoupper(trim((string)$getValue('agent_code', '')));
                if ($agentInput && isset($agentByCode[$agentInput])) {
                    $agentId = $agentByCode[$agentInput]->id;
                } elseif ($agentInput && is_numeric($agentInput) && isset($agentById[(int)$agentInput])) {
                    $agentId = $agentById[(int)$agentInput]->id;
                } elseif ($request->filled('default_agent_id')) {
                    $agentId = $request->default_agent_id;
                }
            }

            // Gender Resolution
            $rawGender = strtolower(trim((string)$getValue('gender', 'Male')));
            $gender = 'Male';
            if (str_contains($rawGender, 'fem') || str_contains($rawGender, 'महिला') || $rawGender === 'f') {
                $gender = 'Female';
            } elseif (str_contains($rawGender, 'oth') || str_contains($rawGender, 'अन्य')) {
                $gender = 'Other';
            }

            // Payment Mode Resolution
            $rawMode = trim((string)$getValue('payment_mode', 'Cash'));
            $paymentMode = 'Cash';
            if (stripos($rawMode, 'upi') !== false) {
                $paymentMode = 'UPI';
            } elseif (stripos($rawMode, 'bank') !== false || stripos($rawMode, 'neft') !== false || stripos($rawMode, 'rtgs') !== false) {
                $paymentMode = 'Bank Transfer';
            } elseif (stripos($rawMode, 'cheque') !== false || stripos($rawMode, 'check') !== false) {
                $paymentMode = 'Cheque';
            }

            // Joining Date
            $joiningDate = date('Y-m-d');
            $rawJoiningDate = $getValue('joining_date');
            if ($rawJoiningDate) {
                try {
                    $joiningDate = Carbon::parse(str_replace('/', '-', $rawJoiningDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    $joiningDate = date('Y-m-d');
                }
            }

            $memberData = [
                'full_name' => $fullName,
                'father_spouse_name' => $getValue('father_spouse_name'),
                'mother_name' => $getValue('mother_name'),
                'gender' => $gender,
                'dob' => $dob->format('Y-m-d'),
                'mobile' => $mobile,
                'gotra' => $getValue('gotra'),
                'caste' => $getValue('caste'),
                'address' => $getValue('address'),
                'district' => $getValue('district'),
                'state' => $getValue('state', 'Rajasthan'),
                'pincode' => $getValue('pincode'),
                'aadhaar_no' => $getValue('aadhaar_no'),
                'scheme_id' => $scheme->id,
                'agent_id' => $agentId,
                'joining_date' => $joiningDate,
                'payment_mode' => $paymentMode,
                'reference_no' => $getValue('reference_no'),
                'nominee1_name' => $getValue('nominee1_name'),
                'nominee1_relation' => $getValue('nominee1_relation', 'Spouse'),
                'nominee1_mobile' => $getValue('nominee1_mobile'),
                'nominee1_aadhaar' => $getValue('nominee1_aadhaar'),
                'nominee1_share' => $getValue('nominee1_share', 100),
                'nominee2_name' => $getValue('nominee2_name'),
                'nominee2_relation' => $getValue('nominee2_relation', 'Son'),
                'nominee2_mobile' => $getValue('nominee2_mobile'),
                'nominee2_aadhaar' => $getValue('nominee2_aadhaar'),
                'nominee2_share' => $getValue('nominee2_share', 50),
            ];

            if ($getValue('joining_amount') !== null && is_numeric($getValue('joining_amount'))) {
                $memberData['joining_amount'] = (float)$getValue('joining_amount');
            }
            if ($getValue('monthly_support_amount') !== null && is_numeric($getValue('monthly_support_amount'))) {
                $memberData['monthly_support_amount'] = (float)$getValue('monthly_support_amount');
            }
            if ($getValue('initial_paid_amount') !== null && is_numeric($getValue('initial_paid_amount'))) {
                $memberData['initial_paid_amount'] = (float)$getValue('initial_paid_amount');
            }

            try {
                MemberRegistrationService::register($memberData);
                $importedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = "Row #{$rowNum} ({$fullName}): " . $e->getMessage();
            }
        }

        fclose($handle);

        $flashType = $importedCount > 0 ? 'success' : 'error';
        $summary = "Import Completed: {$importedCount} members successfully imported.";
        if ($skippedCount > 0) {
            $summary .= " {$skippedCount} rows were skipped due to errors.";
        }

        return redirect()->route('admin.members.index')
            ->with($flashType, $summary)
            ->with('import_errors', $errors);
    }
}
