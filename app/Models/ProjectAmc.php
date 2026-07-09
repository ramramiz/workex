<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class ProjectAmc extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id',
        'amount',
        'start_date',
        'end_date',
        'frequency',
        'status', // active, expired, pending_renewal
        'remarks',
        'company_id',
        'alert_phone',
        'alert_email',
        'service_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'amount'     => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function logs()
    {
        return $this->hasMany(ProjectAmcLog::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active'          => 'success',
            'expired'         => 'danger',
            'pending_renewal' => 'warning',
            default           => 'secondary',
        };
    }

    public function sendWhatsappReminderNotification($daysRemainingOverride = null)
    {
        $this->loadMissing(['project.client']);
        
        $phoneRaw = $this->alert_phone;
        if (empty($phoneRaw)) {
            if (!$this->project || !$this->project->client || empty($this->project->client->phone)) {
                return [
                    'success' => false,
                    'error' => 'Client does not have a phone number configured.'
                ];
            }
            $phoneRaw = $this->project->client->phone;
        }

        $client = $this->project->client;
        $phone = preg_replace('/\D/', '', $phoneRaw);
        
        $company = $this->company ?? \App\Models\Company::find($this->company_id);
        $companyPhoneRaw = $company && $company->phone ? $company->phone : \App\Models\Setting::get('company_phone', '8848787656');
        $companyPhone = preg_replace('/\D/', '', $companyPhoneRaw);
        if (empty($companyPhone)) {
            $companyPhone = '8848787656';
        }

        // Parse Domain
        $domain = '';
        if ($this->project && $this->project->url) {
            $domain = parse_url($this->project->url, PHP_URL_HOST);
        }
        if (empty($domain)) {
            $domain = $this->project->name ?? 'techsoul.in';
        }

        // Days remaining
        if ($daysRemainingOverride !== null) {
            $daysRemaining = (int) $daysRemainingOverride;
        } else {
            $daysRemaining = (int) max(0, today()->diffInDays($this->end_date, false));
        }

        // Get Bank Details
        $bank = \App\Models\Bank::where('status', 'active')->first() ?? \App\Models\Bank::first();

        // Clean fields for CSV (Params) formatting to avoid comma issues
        $customerParam = str_replace(',', '', $client->company_name);
        $serviceParam  = strtolower($this->service_type ?? 'AMC'); 
        $domainParam   = str_replace(',', '', $domain);
        $daysParam     = (string) $daysRemaining;
        $amountParam   = number_format($this->amount, 0, '.', '');
        $accHolder     = str_replace(',', '', $bank ? $bank->account_name : 'Account holder');
        $accNo         = str_replace(',', '', $bank ? $bank->account_number : '12345678');
        $ifsc          = str_replace(',', '', $bank ? $bank->ifsc_code : 'IF678');
        $supportPhone  = $companyPhone;

        // Params list: Customer,service,domain,days,amount,accHolder,accNo,ifsc,supportPhone
        $paramsString = implode(',', [
            $customerParam,
            $serviceParam,
            $domainParam,
            $daysParam,
            $amountParam,
            $accHolder,
            $accNo,
            $ifsc,
            $supportPhone
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::get('https://bhashsms.com/api/sendmsgutil.php', [
                'user'     => 'Techsoul_BW',
                'pass'     => '123456',
                'sender'   => 'BUZWAP',
                'phone'    => $phone,
                'text'     => 'pending_renewal',
                'priority' => 'wa',
                'stype'    => 'normal',
                'Params'   => $paramsString
            ]);

            if ($response->successful()) {
                $msgBody = "Hello {$customerParam},\n\nThis is to inform you that your {$serviceParam} for the {$domainParam} expires in {$daysParam} days. To avoid service disconnection, please clear the pending renewal amount of ₹{$amountParam} at the earliest.\n\nPayment Details:\nAccount Name: {$accHolder}\nA/C No: {$accNo}\nIFSC: {$ifsc}\n\nPlease share the screenshot once paid. For queries, call Support: {$supportPhone}.\nThank you,\nTeam Techsoul";
                
                \App\Models\ActivityLog::log(
                    'amc_whatsapp_reminder_sent', 
                    "Sent WhatsApp AMC renewal reminder to {$phone} (Client: {$client->company_name}) for project {$this->project->name}",
                    $this,
                    [],
                    [
                        'phone'          => $phone,
                        'message'        => $msgBody,
                        'days_remaining' => $daysRemaining,
                        'end_date'       => $this->end_date->toDateString(),
                    ]
                );
                
                return [
                    'success' => true
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API returned status ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp AMC reminder error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
