<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            ['agent_code' => 'AGT-001', 'code' => 'AGT01', 'name' => 'Rameshwar Lal Sharma', 'name_hindi' => 'रामेश्वर लाल शर्मा', 'mobile' => '9829012345', 'district' => 'Mahendragarh', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-002', 'code' => 'AGT02', 'name' => 'Suresh Kumar Yadav', 'name_hindi' => 'सुरेश कुमार यादव', 'mobile' => '9414023456', 'district' => 'Bhiwani', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-003', 'code' => 'AGT03', 'name' => 'Rajendra Prasad Verma', 'name_hindi' => 'राजेन्द्र प्रसाद वर्मा', 'mobile' => '9812034567', 'district' => 'Rewari', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-004', 'code' => 'AGT04', 'name' => 'Sunita Devi Saini', 'name_hindi' => 'सुनीता देवी सैनी', 'mobile' => '9784045678', 'district' => 'Mahendragarh', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-005', 'code' => 'AGT05', 'name' => 'Mahesh Kumar Garg', 'name_hindi' => 'महेश कुमार गर्ग', 'mobile' => '9672056789', 'district' => 'Charkhi Dadri', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-006', 'code' => 'AGT06', 'name' => 'Virendra Singh Shekhawat', 'name_hindi' => 'वीरेंद्र सिंह शेखावत', 'mobile' => '9828067890', 'district' => 'Jhunjhunu', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-007', 'code' => 'AGT07', 'name' => 'Mamta Sharma', 'name_hindi' => 'ममता शर्मा', 'mobile' => '9413078901', 'district' => 'Rewari', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-008', 'code' => 'AGT08', 'name' => 'Deepak Kumar Khandelwal', 'name_hindi' => 'दीपक कुमार खंडेलवाल', 'mobile' => '9829089012', 'district' => 'Mahendragarh', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-009', 'code' => 'AGT09', 'name' => 'Pawan Kumar Saini', 'name_hindi' => 'पवन कुमार सैनी', 'mobile' => '9414090123', 'district' => 'Bhiwani', 'commission_rate' => 5.0],
            ['agent_code' => 'AGT-010', 'code' => 'AGT10', 'name' => 'Anil Kumar Yadav', 'name_hindi' => 'अनिल कुमार यादव', 'mobile' => '9812001234', 'district' => 'Charkhi Dadri', 'commission_rate' => 5.0],
        ];

        foreach ($agents as $agent) {
            Agent::updateOrCreate(
                ['agent_code' => $agent['agent_code']],
                array_merge($agent, [
                    'state' => 'Haryana',
                    'address' => 'Main Market, ' . $agent['district'],
                    'pincode' => '123001',
                    'status' => 'Active',
                    'joining_date' => '2024-01-15',
                ])
            );
        }
    }
}
