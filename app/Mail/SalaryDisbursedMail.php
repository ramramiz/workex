<?php

namespace App\Mail;

use App\Models\SalaryDisbursal;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalaryDisbursedMail extends Mailable
{
    use Queueable, SerializesModels;

    public SalaryDisbursal $slip;
    public string $companyName;
    public string $companyEmail;
    public string $companyPhone;
    public string $companyAddress;
    public ?string $companyLogo;

    /**
     * Create a new message instance.
     */
    public function __construct(SalaryDisbursal $slip)
    {
        $this->slip = $slip->load('employee.user', 'employee.department', 'employee.designation', 'company');
        
        $company = $slip->company;
        $this->companyName = $company && $company->name ? $company->name : Setting::get('company_name', 'WorkeX');
        $this->companyEmail = $company && $company->email ? $company->email : Setting::get('company_email', 'info@company.com');
        $this->companyPhone = $company && $company->phone ? $company->phone : Setting::get('company_phone', '+91-9999999999');
        $this->companyAddress = $company && $company->address ? $company->address : Setting::get('company_address', 'Your Company Address');
        $this->companyLogo = Setting::get('company_logo');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $monthName = date('F', mktime(0, 0, 0, $this->slip->month, 1));
        
        return $this->subject("Salary Disbursed for {$monthName} {$this->slip->year} — {$this->companyName}")
                    ->view('emails.salary_disbursed');
    }
}
