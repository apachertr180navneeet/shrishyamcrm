<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Services\WhatsAppService;

class SendDueRemindersCommand extends Command
{
    protected $signature = 'society:send-due-reminders';
    protected $description = 'Queue and log WhatsApp due reminder alerts for members with pending balances';

    public function handle(): int
    {
        $overdueMembers = Member::where('status', 'Active')->where('pending_amount', '>', 0)->get();
        $this->info("Found {$overdueMembers->count()} members with pending dues.");

        foreach ($overdueMembers as $member) {
            $msgData = WhatsAppService::getDueReminderMessage($member);
            WhatsAppService::logMessage(
                memberId: $member->id,
                recipientName: $member->full_name,
                mobile: $msgData['mobile'],
                messageType: 'Due Alert',
                body: $msgData['message'],
                status: 'Queued'
            );
        }

        $this->info("Due reminder alerts logged successfully.");
        return 0;
    }
}
