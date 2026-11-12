<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Member;
use App\Models\Nominee;
use App\Models\Certificate;
use App\Models\Ledger;
use App\Services\KrutiDevConverter;
use App\Services\XlsxReader;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportExcelMembersCommand extends Command
{
    protected $signature = 'society:import-excel 
                            {--buzurg= : Path to Buzurg Excel file}
                            {--vivah= : Path to Vivah Excel file}';

    protected $description = 'Import society members and agents from legacy Kruti Dev Excel files separated by Yojna (BUZURG, KDVY, NVY)';

    public function handle(): int
    {
        $this->info("==================================================================");
        $this->info("   Shri Shyam Society - Members & Agents Excel Importer");
        $this->info("   Schemes: बुजुर्ग सम्मान योजना | कन्यादान विवाह (KDVY) | नेत्र विवाह (NVY)");
        $this->info("==================================================================");

        $buzurgPath = $this->option('buzurg') ?: storage_path('app/imports/BuguraG APP HETU.xlsx');
        $vivahPath = $this->option('vivah') ?: storage_path('app/imports/VIVAH APP HETU.xlsx');

        if (!file_exists($buzurgPath)) {
            $this->error("Buzurg Excel file not found at: {$buzurgPath}");
            return 1;
        }

        if (!file_exists($vivahPath)) {
            $this->error("Vivah Excel file not found at: {$vivahPath}");
            return 1;
        }

        // 1. Initialize Schemes & Slabs (Buzurg, KDVY - Female, NVY - Male)
        $this->info("\n[1/4] Ensuring 3 Schemes and Age Slabs exist in database...");
        $schemes = $this->initializeSchemesAndSlabs();

        // 2. Read Excel Files
        $this->info("\n[2/4] Reading and parsing Excel sheets...");
        $bRows = XlsxReader::readRows($buzurgPath);
        $vRows = XlsxReader::readRows($vivahPath);
        $this->info("  -> Buzurg rows loaded: " . count($bRows));
        $this->info("  -> Vivah rows loaded: " . count($vRows));

        // 3. Process & Create Agents
        $this->info("\n[3/4] Resolving and creating Agents in database...");
        $agentLookup = $this->syncAgents($bRows, $vRows);

        // 4. Import Members
        $this->info("\n[4/4] Importing Members, Nominees, Certificates & Ledgers...");

        $this->info("\n--- Importing Buzurg Scheme Members (बुजुर्ग सम्मान योजना) ---");
        $buzurgStats = $this->importBuzurgMembers($bRows, $schemes['buzurg'], $agentLookup);

        $this->info("\n--- Importing Vivah Scheme Members into KDVY (Female) and NVY (Male) ---");
        $vivahStats = $this->importVivahMembers($vRows, $schemes['kdvy'], $schemes['nvy'], $agentLookup);

        // Summary
        $this->info("\n==================================================================");
        $this->info("                     IMPORT SUMMARY");
        $this->info("==================================================================");
        $this->info("Total Active Agents in DB:               " . Agent::count());
        $this->info("Buzurg Scheme Members (बुजुर्ग सम्मान):     " . Member::where('scheme_id', $schemes['buzurg']->id)->count());
        $this->info("KDVY Scheme Members (कन्यादान विवाह):     " . Member::where('scheme_id', $schemes['kdvy']->id)->count());
        $this->info("NVY Scheme Members (नेत्र विवाह):          " . Member::where('scheme_id', $schemes['nvy']->id)->count());
        $this->info("Total Society Members across all 3:      " . Member::count());
        $this->info("Total Nominees in DB:                    " . Nominee::count());
        $this->info("Total Certificates in DB:                " . Certificate::count());
        $this->info("==================================================================");

        return 0;
    }

    protected function initializeSchemesAndSlabs(): array
    {
        // 1. Buzurg Scheme
        $buzurg = Scheme::firstOrCreate(
            ['code' => 'BUZURG'],
            [
                'name' => 'Buzurg Samman Yojana',
                'name_hindi' => 'बुजुर्ग सम्मान योजना',
                'type' => 'Senior Welfare Scheme',
                'status' => 'Active',
                'description' => 'बुजुर्ग सम्मान कल्याण योजना',
            ]
        );

        if ($buzurg->ageSlabs()->count() === 0) {
            AgeSlab::create(['scheme_id' => $buzurg->id, 'slab_code' => 'SLAB-B1', 'min_age' => 18, 'max_age' => 65, 'joining_amount' => 1100, 'support_amount' => 400, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $buzurg->id, 'slab_code' => 'SLAB-B2', 'min_age' => 66, 'max_age' => 75, 'joining_amount' => 1100, 'support_amount' => 500, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $buzurg->id, 'slab_code' => 'SLAB-B3', 'min_age' => 76, 'max_age' => 120, 'joining_amount' => 1100, 'support_amount' => 500, 'status' => 'Active']);
            $this->info("  -> Created 3 Age Slabs for Buzurg Scheme.");
        }

        // 2. Kanyadan Vivah Yojana (KDVY) - For Female members
        $kdvy = Scheme::where('code', 'KDVY')->first();
        if (!$kdvy) {
            $existingVivah = Scheme::where('code', 'VIVAH')->first();
            if ($existingVivah) {
                $existingVivah->update([
                    'code' => 'KDVY',
                    'name' => 'Kanyadan Vivah Yojana',
                    'name_hindi' => 'कन्यादान विवाह योजना',
                    'type' => 'Marriage Scheme',
                    'status' => 'Active',
                    'description' => 'कन्यादान विवाह योजना (KDVY) - कन्या/महिला सदस्यों हेतु',
                ]);
                $kdvy = $existingVivah;
            } else {
                $kdvy = Scheme::create([
                    'code' => 'KDVY',
                    'name' => 'Kanyadan Vivah Yojana',
                    'name_hindi' => 'कन्यादान विवाह योजना',
                    'type' => 'Marriage Scheme',
                    'status' => 'Active',
                    'description' => 'कन्यादान विवाह योजना (KDVY) - कन्या/महिला सदस्यों हेतु',
                ]);
            }
        }

        if ($kdvy->ageSlabs()->count() === 0) {
            AgeSlab::create(['scheme_id' => $kdvy->id, 'slab_code' => 'SLAB-KD1', 'min_age' => 0, 'max_age' => 15, 'joining_amount' => 1100, 'support_amount' => 300, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $kdvy->id, 'slab_code' => 'SLAB-KD2', 'min_age' => 16, 'max_age' => 25, 'joining_amount' => 1100, 'support_amount' => 400, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $kdvy->id, 'slab_code' => 'SLAB-KD3', 'min_age' => 26, 'max_age' => 45, 'joining_amount' => 1100, 'support_amount' => 500, 'status' => 'Active']);
            $this->info("  -> Created 3 Age Slabs for Kanyadan Vivah Yojana (KDVY).");
        }

        // 3. Netra Vivah Yojana (NVY) - For Male members
        $nvy = Scheme::firstOrCreate(
            ['code' => 'NVY'],
            [
                'name' => 'Netra Vivah Yojana',
                'name_hindi' => 'नेत्र विवाह योजना',
                'type' => 'Marriage Scheme',
                'status' => 'Active',
                'description' => 'नेत्र विवाह योजना (NVY) - वर/पुरुष सदस्यों हेतु',
            ]
        );

        if ($nvy->ageSlabs()->count() === 0) {
            AgeSlab::create(['scheme_id' => $nvy->id, 'slab_code' => 'SLAB-NV1', 'min_age' => 0, 'max_age' => 15, 'joining_amount' => 1100, 'support_amount' => 300, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $nvy->id, 'slab_code' => 'SLAB-NV2', 'min_age' => 16, 'max_age' => 25, 'joining_amount' => 1100, 'support_amount' => 400, 'status' => 'Active']);
            AgeSlab::create(['scheme_id' => $nvy->id, 'slab_code' => 'SLAB-NV3', 'min_age' => 26, 'max_age' => 45, 'joining_amount' => 1100, 'support_amount' => 500, 'status' => 'Active']);
            $this->info("  -> Created 3 Age Slabs for Netra Vivah Yojana (NVY).");
        }

        return [
            'buzurg' => $buzurg->load('ageSlabs'),
            'kdvy' => $kdvy->load('ageSlabs'),
            'nvy' => $nvy->load('ageSlabs'),
        ];
    }

    protected function syncAgents(array $bRows, array $vRows): array
    {
        $uniqueAgents = [];

        foreach ($bRows as $num => $r) {
            if ($num <= 2) continue;
            $raw = trim($r['I'] ?? '');
            if ($raw !== '') {
                $clean = KrutiDevConverter::normalizeAgentName($raw);
                if ($clean !== '') {
                    $uniqueAgents[$clean] = true;
                }
            }
        }

        foreach ($vRows as $num => $r) {
            if ($num <= 2) continue;
            $raw = trim($r['K'] ?? '');
            if ($raw !== '') {
                $clean = KrutiDevConverter::normalizeAgentName($raw);
                if ($clean !== '') {
                    $uniqueAgents[$clean] = true;
                }
            }
        }

        $agentLookup = [];
        $existingAgents = Agent::all();
        foreach ($existingAgents as $ea) {
            $agentLookup[trim($ea->name)] = $ea->id;
            if ($ea->name === 'बुधराम') {
                $agentLookup['बुधाराम'] = $ea->id;
            }
        }

        $createdCount = 0;
        foreach (array_keys($uniqueAgents) as $name) {
            if (isset($agentLookup[$name])) {
                continue;
            }

            $lastCode = DB::table('agents')->where('agent_code', 'like', 'AGT-%')
                ->orderByRaw('LENGTH(agent_code) DESC, agent_code DESC')
                ->value('agent_code');
            $nextNum = 1;
            if ($lastCode) {
                $nextNum = ((int)str_replace('AGT-', '', $lastCode)) + 1;
            }
            $code = 'AGT-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            $agent = Agent::create([
                'agent_code' => $code,
                'name' => $name,
                'district' => 'बालोतरा',
                'commission_rate' => 5.0,
                'status' => 'Active',
            ]);

            $agentLookup[$name] = $agent->id;
            $createdCount++;
        }

        $this->info("  -> Total unique agents mapped: " . count($agentLookup) . " ({$createdCount} newly created).");
        return $agentLookup;
    }

    protected function importBuzurgMembers(array $bRows, Scheme $scheme, array $agentLookup): array
    {
        $imported = 0;
        $skipped = 0;
        $slabs = $scheme->ageSlabs;

        $bar = $this->output->createProgressBar(count($bRows) - 2);
        $bar->start();

        foreach ($bRows as $num => $r) {
            if ($num <= 2) continue;

            $fullName = KrutiDevConverter::toUnicode($r['B'] ?? '');
            if (empty($fullName)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $fatherName = KrutiDevConverter::toUnicode($r['C'] ?? '');
            $caste = KrutiDevConverter::toUnicode($r['D'] ?? '');
            $age = (int)($r['E'] ?? 0);
            if ($age <= 0) $age = 65;

            $nomineeRaw = KrutiDevConverter::toUnicode($r['F'] ?? '');
            $village = KrutiDevConverter::toUnicode($r['G'] ?? '');
            if ($village === '<.<' || $village === 'ढ.ढ' || $village === '') {
                $village = 'लोहिड़ी';
            }

            $rawMobile = preg_replace('/[^0-9]/', '', (string)($r['H'] ?? ''));
            $mobile = strlen($rawMobile) >= 10 ? $rawMobile : ($rawMobile ?: null);

            $rawAgent = $r['I'] ?? '';
            $agentName = KrutiDevConverter::normalizeAgentName($rawAgent);
            $agentId = $agentLookup[$agentName] ?? ($agentLookup['बुधाराम'] ?? 1);

            $supportAmount = (float)($r['J'] ?? 0);
            if ($supportAmount <= 0) $supportAmount = 400.0;

            $joiningDate = '2025-01-01';
            $rawDate = trim($r['K'] ?? '');
            if ($rawDate) {
                try {
                    $cleanedDate = str_replace(['--', '/'], ['-', '-'], $rawDate);
                    $joiningDate = Carbon::parse($cleanedDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $joiningDate = '2025-01-01';
                }
            }

            // Gender
            $gender = (str_contains($fullName, 'देवी') || str_contains($fullName, 'बाई')) ? 'Female' : 'Male';
            $dob = Carbon::now()->subYears($age)->startOfYear()->toDateString();
            $slab = $slabs->first(fn($s) => $age >= $s->min_age && $age <= $s->max_age) ?: $slabs->first();

            DB::beginTransaction();
            try {
                $existing = Member::where('scheme_id', $scheme->id)
                    ->where('full_name', $fullName)
                    ->where(function($q) use ($fatherName) {
                        if ($fatherName) {
                            $q->where('father_spouse_name', $fatherName);
                        }
                    })
                    ->first();

                if ($existing) {
                    $existing->update([
                        'full_name' => $fullName,
                        'father_spouse_name' => $fatherName,
                        'gender' => $gender,
                        'dob' => $dob,
                        'age' => $age,
                        'mobile' => $mobile ?? $existing->mobile,
                        'gotra' => $caste,
                        'caste' => $caste,
                        'address' => $village,
                        'district' => 'बालोतरा',
                        'state' => 'राजस्थान',
                        'scheme_id' => $scheme->id,
                        'age_slab_id' => $slab ? $slab->id : null,
                        'monthly_support_amount' => $supportAmount,
                        'agent_id' => $agentId,
                    ]);
                    $member = $existing;
                } else {
                    $currentYear = date('Y');
                    $last = DB::table('members')->where('membership_no', 'like', "MEM-{$currentYear}-%")
                        ->orderByRaw('LENGTH(membership_no) DESC, membership_no DESC')
                        ->value('membership_no');
                    $nextNum = 1001;
                    if ($last) {
                        $nextNum = ((int)str_replace("MEM-{$currentYear}-", '', $last)) + 1;
                    }
                    $memNo = "MEM-{$currentYear}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                    $member = Member::create([
                        'membership_no' => $memNo,
                        'full_name' => $fullName,
                        'father_spouse_name' => $fatherName,
                        'mother_name' => null,
                        'gender' => $gender,
                        'dob' => $dob,
                        'age' => $age,
                        'mobile' => $mobile,
                        'gotra' => $caste,
                        'caste' => $caste,
                        'address' => $village,
                        'district' => 'बालोतरा',
                        'state' => 'राजस्थान',
                        'pincode' => '344022',
                        'scheme_id' => $scheme->id,
                        'age_slab_id' => $slab ? $slab->id : null,
                        'joining_amount' => $slab ? (float)$slab->joining_amount : 1100.0,
                        'monthly_support_amount' => $supportAmount,
                        'agent_id' => $agentId,
                        'joining_date' => $joiningDate,
                        'status' => 'Active',
                        'pending_amount' => 0,
                        'total_paid' => 1100.0,
                    ]);

                    if ($nomineeRaw) {
                        Nominee::create([
                            'member_id' => $member->id,
                            'name' => $nomineeRaw,
                            'relation' => 'वारिसदार',
                            'priority' => 1,
                            'percentage' => 100.0,
                        ]);
                    }

                    $lastCert = DB::table('certificates')->where('certificate_no', 'like', "CRT-{$currentYear}-%")
                        ->orderByRaw('LENGTH(certificate_no) DESC, certificate_no DESC')
                        ->value('certificate_no');
                    $nextCertNum = 8001;
                    if ($lastCert) {
                        $nextCertNum = ((int)str_replace("CRT-{$currentYear}-", '', $lastCert)) + 1;
                    }
                    $certNo = "CRT-{$currentYear}-" . str_pad($nextCertNum, 4, '0', STR_PAD_LEFT);

                    Certificate::create([
                        'certificate_no' => $certNo,
                        'member_id' => $member->id,
                        'scheme_id' => $scheme->id,
                        'issue_date' => $joiningDate,
                        'authorized_by' => 'General Secretary',
                        'verification_code' => strtoupper(substr(md5($certNo . $member->id), 0, 10)),
                    ]);

                    $lastTxn = DB::table('ledgers')->where('transaction_no', 'like', "TXN-{$currentYear}-%")
                        ->orderByRaw('LENGTH(transaction_no) DESC, transaction_no DESC')
                        ->value('transaction_no');
                    $nextTxnNum = 10001;
                    if ($lastTxn) {
                        $nextTxnNum = ((int)str_replace("TXN-{$currentYear}-", '', $lastTxn)) + 1;
                    }
                    $txnNo = "TXN-{$currentYear}-" . str_pad($nextTxnNum, 5, '0', STR_PAD_LEFT);

                    Ledger::create([
                        'transaction_no' => $txnNo,
                        'member_id' => $member->id,
                        'agent_id' => $agentId,
                        'transaction_date' => $joiningDate,
                        'entry_type' => 'Joining Fee',
                        'description' => 'Initial Membership Enrollment Registration Fee',
                        'debit' => 1100.0,
                        'credit' => 1100.0,
                        'running_balance' => 0.0,
                    ]);
                }

                DB::commit();
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->info("Buzurg import finished: {$imported} processed, {$skipped} skipped.");
        return ['imported' => $imported, 'skipped' => $skipped];
    }

    protected function importVivahMembers(array $vRows, Scheme $kdvy, Scheme $nvy, array $agentLookup): array
    {
        $imported = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($vRows) - 2);
        $bar->start();

        foreach ($vRows as $num => $r) {
            if ($num <= 2) continue;

            $fullName = KrutiDevConverter::toUnicode($r['B'] ?? '');
            if (empty($fullName)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $fatherName = KrutiDevConverter::toUnicode($r['C'] ?? '');
            $age = (int)($r['D'] ?? 0);
            if ($age <= 0) $age = 18;

            // Separate Female (KDVY) vs Male (NVY)
            $rawGender = trim((string)($r['E'] ?? ''));
            if ($rawGender === 'e-' || $rawGender === 'e' || str_contains($rawGender, 'महिला') || str_contains($fullName, 'कुमारी') || str_contains($fullName, 'देवी') || str_contains($fullName, 'बाई')) {
                $gender = 'Female';
                $targetScheme = $kdvy;
            } elseif ($rawGender === 'iq-' || $rawGender === 'iq' || str_contains($rawGender, 'पुरुष') || (str_contains($fullName, 'कुमार') && !str_contains($fullName, 'कुमारी')) || str_contains($fullName, 'राम') || str_contains($fullName, 'सिंह') || str_contains($fullName, 'लाल')) {
                $gender = 'Male';
                $targetScheme = $nvy;
            } else {
                $gender = 'Female';
                $targetScheme = $kdvy;
            }

            $slabs = $targetScheme->ageSlabs;
            $slab = $slabs->first(fn($s) => $age >= $s->min_age && $age <= $s->max_age) ?: $slabs->first();

            $caste = KrutiDevConverter::toUnicode($r['F'] ?? '');
            $nomineeRaw = KrutiDevConverter::toUnicode($r['G'] ?? '');
            $village = KrutiDevConverter::toUnicode($r['H'] ?? '');
            if ($village === '') $village = 'लोहिड़ी';
            $district = KrutiDevConverter::toUnicode($r['I'] ?? '');
            if ($district === '') $district = 'बालोतरा';

            $rawMobile = preg_replace('/[^0-9]/', '', (string)($r['J'] ?? ''));
            $mobile = strlen($rawMobile) >= 10 ? $rawMobile : ($rawMobile ?: null);

            $rawAgent = $r['K'] ?? '';
            $agentName = KrutiDevConverter::normalizeAgentName($rawAgent);
            $agentId = $agentLookup[$agentName] ?? ($agentLookup['बुधाराम'] ?? 1);

            $supportAmount = (float)($r['L'] ?? 0);
            if ($supportAmount <= 0) $supportAmount = 300.0;

            $joiningDate = '2025-09-01';
            $rawDate = trim($r['M'] ?? '');
            if ($rawDate) {
                try {
                    $cleanedDate = str_replace(['--', '/'], ['-', '-'], $rawDate);
                    $joiningDate = Carbon::parse($cleanedDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $joiningDate = '2025-09-01';
                }
            }

            $dob = Carbon::now()->subYears($age)->startOfYear()->toDateString();

            DB::beginTransaction();
            try {
                // Check if already exists in either KDVY or NVY
                $existing = Member::whereIn('scheme_id', [$kdvy->id, $nvy->id])
                    ->where('full_name', $fullName)
                    ->where(function($q) use ($fatherName) {
                        if ($fatherName) {
                            $q->where('father_spouse_name', $fatherName);
                        }
                    })
                    ->first();

                if ($existing) {
                    $existing->update([
                        'full_name' => $fullName,
                        'father_spouse_name' => $fatherName,
                        'gender' => $gender,
                        'dob' => $dob,
                        'age' => $age,
                        'mobile' => $mobile ?? $existing->mobile,
                        'gotra' => $caste,
                        'caste' => $caste,
                        'address' => $village,
                        'district' => $district,
                        'state' => 'राजस्थान',
                        'scheme_id' => $targetScheme->id,
                        'age_slab_id' => $slab ? $slab->id : null,
                        'monthly_support_amount' => $supportAmount,
                        'agent_id' => $agentId,
                    ]);
                    // Also ensure existing certificate reflects new scheme
                    Certificate::where('member_id', $existing->id)->update([
                        'scheme_id' => $targetScheme->id,
                    ]);
                    $member = $existing;
                } else {
                    $currentYear = date('Y');
                    $last = DB::table('members')->where('membership_no', 'like', "MEM-{$currentYear}-%")
                        ->orderByRaw('LENGTH(membership_no) DESC, membership_no DESC')
                        ->value('membership_no');
                    $nextNum = 1001;
                    if ($last) {
                        $nextNum = ((int)str_replace("MEM-{$currentYear}-", '', $last)) + 1;
                    }
                    $memNo = "MEM-{$currentYear}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                    $member = Member::create([
                        'membership_no' => $memNo,
                        'full_name' => $fullName,
                        'father_spouse_name' => $fatherName,
                        'mother_name' => null,
                        'gender' => $gender,
                        'dob' => $dob,
                        'age' => $age,
                        'mobile' => $mobile,
                        'gotra' => $caste,
                        'caste' => $caste,
                        'address' => $village,
                        'district' => $district,
                        'state' => 'राजस्थान',
                        'pincode' => '344022',
                        'scheme_id' => $targetScheme->id,
                        'age_slab_id' => $slab ? $slab->id : null,
                        'joining_amount' => $slab ? (float)$slab->joining_amount : 1100.0,
                        'monthly_support_amount' => $supportAmount,
                        'agent_id' => $agentId,
                        'joining_date' => $joiningDate,
                        'status' => 'Active',
                        'pending_amount' => 0,
                        'total_paid' => 1100.0,
                    ]);

                    if ($nomineeRaw) {
                        $delimiters = ['@', '/'];
                        $parts = [];
                        foreach ($delimiters as $d) {
                            if (str_contains($nomineeRaw, $d)) {
                                $parts = array_filter(array_map('trim', explode($d, $nomineeRaw)));
                                break;
                            }
                        }

                        if (count($parts) >= 2) {
                            Nominee::create([
                                'member_id' => $member->id,
                                'name' => $parts[0],
                                'relation' => 'वारिसदार 1',
                                'priority' => 1,
                                'percentage' => 50.0,
                            ]);
                            Nominee::create([
                                'member_id' => $member->id,
                                'name' => $parts[1],
                                'relation' => 'वारिसदार 2',
                                'priority' => 2,
                                'percentage' => 50.0,
                            ]);
                        } else {
                            Nominee::create([
                                'member_id' => $member->id,
                                'name' => $nomineeRaw,
                                'relation' => 'वारिसदार',
                                'priority' => 1,
                                'percentage' => 100.0,
                            ]);
                        }
                    }

                    $lastCert = DB::table('certificates')->where('certificate_no', 'like', "CRT-{$currentYear}-%")
                        ->orderByRaw('LENGTH(certificate_no) DESC, certificate_no DESC')
                        ->value('certificate_no');
                    $nextCertNum = 8001;
                    if ($lastCert) {
                        $nextCertNum = ((int)str_replace("CRT-{$currentYear}-", '', $lastCert)) + 1;
                    }
                    $certNo = "CRT-{$currentYear}-" . str_pad($nextCertNum, 4, '0', STR_PAD_LEFT);

                    Certificate::create([
                        'certificate_no' => $certNo,
                        'member_id' => $member->id,
                        'scheme_id' => $targetScheme->id,
                        'issue_date' => $joiningDate,
                        'authorized_by' => 'General Secretary',
                        'verification_code' => strtoupper(substr(md5($certNo . $member->id), 0, 10)),
                    ]);

                    $lastTxn = DB::table('ledgers')->where('transaction_no', 'like', "TXN-{$currentYear}-%")
                        ->orderByRaw('LENGTH(transaction_no) DESC, transaction_no DESC')
                        ->value('transaction_no');
                    $nextTxnNum = 10001;
                    if ($lastTxn) {
                        $nextTxnNum = ((int)str_replace("TXN-{$currentYear}-", '', $lastTxn)) + 1;
                    }
                    $txnNo = "TXN-{$currentYear}-" . str_pad($nextTxnNum, 5, '0', STR_PAD_LEFT);

                    Ledger::create([
                        'transaction_no' => $txnNo,
                        'member_id' => $member->id,
                        'agent_id' => $agentId,
                        'transaction_date' => $joiningDate,
                        'entry_type' => 'Joining Fee',
                        'description' => 'Initial Membership Enrollment Registration Fee',
                        'debit' => 1100.0,
                        'credit' => 1100.0,
                        'running_balance' => 0.0,
                    ]);
                }

                DB::commit();
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->info("Vivah import finished: {$imported} processed, {$skipped} skipped.");
        return ['imported' => $imported, 'skipped' => $skipped];
    }
}
