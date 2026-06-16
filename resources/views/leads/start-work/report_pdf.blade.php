<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Call Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 5px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #1e293b;
        }
        .header p {
            margin: 3px 0 0 0;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 0;
            margin-bottom: 10px;
            border-left: 3px solid #6366f1;
            padding-left: 6px;
        }
        .meta-info {
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: 4px;
        }
        .meta-table {
            width: 100%;
            border: none;
            margin-bottom: 0;
        }
        .meta-table td {
            border: none;
            padding: 3px 0;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>
<body>
    @php
        $startVal = isset($startedAt) ? $startedAt : null;
        $endVal = isset($endedAt) ? $endedAt : null;
        $totalSecsVal = isset($totalSeconds) ? $totalSeconds : 0;

        $totalWorkHrs = floor($totalSecsVal / 3600);
        $totalWorkMins = floor(($totalSecsVal % 3600) / 60);
        $totalWorkSecs = $totalSecsVal % 60;
        
        $totalWorkFormatted = '';
        if ($totalWorkHrs > 0) $totalWorkFormatted .= "{$totalWorkHrs}h ";
        if ($totalWorkMins > 0) $totalWorkFormatted .= "{$totalWorkMins}m ";
        $totalWorkFormatted .= "{$totalWorkSecs}s";

        $totalCallSeconds = $todayCalls->sum('duration');
        $totalCallHrs = floor($totalCallSeconds / 3600);
        $totalCallMins = floor(($totalCallSeconds % 3600) / 60);
        $totalCallSecs = $totalCallSeconds % 60;
        
        $totalCallFormatted = '';
        if ($totalCallHrs > 0) $totalCallFormatted .= "{$totalCallHrs}h ";
        if ($totalCallMins > 0) $totalCallFormatted .= "{$totalCallMins}m ";
        $totalCallFormatted .= "{$totalCallSecs}s";

        // Group calls
        $connectedCalls = $todayCalls->where('status', 'Connected');
        $notConnectedCalls = $todayCalls->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
    @endphp

    <!-- PAGE 1: Connected Calls Report -->
    <div class="header">
        <h1>Session Call Report</h1>
        <p>Generated on {{ now()->format('Y-m-d H:i A') }}</p>
    </div>

    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td style="width: 50%;"><span class="meta-label">Telecaller:</span> {{ $telecaller->name }} ({{ $telecaller->email }})</td>
                <td style="width: 50%;"><span class="meta-label">Date:</span> {{ today()->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><span class="meta-label">Lead Room:</span> {{ $room->name ?? 'N/A' }}</td>
                <td><span class="meta-label">Total Calls in Session:</span> {{ $todayCalls->count() }}</td>
            </tr>
            @if($startVal && $endVal)
            <tr>
                <td><span class="meta-label">Work Started:</span> {{ $startVal->format('Y-m-d h:i A') }}</td>
                <td><span class="meta-label">Work Stopped:</span> {{ $endVal->format('Y-m-d h:i A') }}</td>
            </tr>
            <tr>
                <td><span class="meta-label">Total Work Time:</span> {{ $totalWorkFormatted }}</td>
                <td><span class="meta-label">Total Call Time:</span> {{ $totalCallFormatted }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section-title">Connected Calls (Called Leads)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Customer Name</th>
                <th style="width: 20%;">Contact Number</th>
                <th style="width: 20%;">Response</th>
                <th style="width: 15%;">Time Consumed</th>
                <th style="width: 15%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($connectedCalls as $index => $call)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $call->lead->client_name ?? 'N/A' }}</td>
                    <td>{{ $call->lead->client_phone ?? 'N/A' }}</td>
                    <td>{{ $call->customer_response ?: '—' }}</td>
                    <td>
                        @if($call->duration)
                            {{ $call->duration >= 60 ? floor($call->duration / 60) . 'm ' . ($call->duration % 60) . 's' : $call->duration . 's' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $call->remarks ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">No connected calls logged in this session.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PAGE BREAK FOR UNCONNECTED CALLS -->
    <div class="page-break"></div>

    <!-- PAGE 2: Unconnected Calls -->
    <div class="header">
        <h1>Session Unconnected Calls</h1>
        <p>Generated on {{ now()->format('Y-m-d H:i A') }}</p>
    </div>

    <div class="section-title">Unconnected Calls (Not Connected Leads)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Customer Name</th>
                <th style="width: 25%;">Contact Number</th>
                <th style="width: 20%;">Call Status</th>
                <th style="width: 20%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notConnectedCalls as $index => $call)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $call->lead->client_name ?? 'N/A' }}</td>
                    <td>{{ $call->lead->client_phone ?? 'N/A' }}</td>
                    <td>{{ $call->status }}</td>
                    <td>{{ $call->remarks ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No unconnected calls logged in this session.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PAGE BREAK FOR INTERESTED CONTACTS -->
    <div class="page-break"></div>

    <!-- PAGE 3: Interested Leads -->
    <div class="header">
        <h1>Interested Contacts Report</h1>
        <p>Generated on {{ now()->format('Y-m-d H:i A') }}</p>
    </div>

    <div class="section-title">Interested Persons Contact Details</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Customer Name</th>
                <th style="width: 20%;">Contact Number</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 30%;">Requirements</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interestedLeads as $index => $lead)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $lead->client_name }}</td>
                    <td>{{ $lead->client_phone }}</td>
                    <td>{{ $lead->client_email ?: '—' }}</td>
                    <td>
                        @if($lead->service_required)
                            <strong>Service Required:</strong> {{ $lead->service_required }}<br>
                        @endif
                        @if($lead->requirement)
                            <strong>Detail:</strong> {{ $lead->requirement }}
                        @endif
                        @if(!$lead->service_required && !$lead->requirement)
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No interested leads recorded/called in this session.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
