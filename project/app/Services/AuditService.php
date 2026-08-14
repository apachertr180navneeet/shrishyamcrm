<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, string $module, ?string $recordId = null, ?array $oldValues = null, ?array $newValues = null, ?int $userId = null): AuditLog
    {
        $userId = $userId ?: (auth()->check() ? auth()->id() : null);

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
        ]);
    }
}
