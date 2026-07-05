<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Company
            ['key' => 'company_name',       'value' => 'WorkeX',       'group' => 'company',   'label' => 'Company Name',          'type' => 'text'],
            ['key' => 'company_email',      'value' => 'info@company.com',      'group' => 'company',   'label' => 'Company Email',         'type' => 'text'],
            ['key' => 'company_phone',      'value' => '+91-9999999999',        'group' => 'company',   'label' => 'Company Phone',         'type' => 'text'],
            ['key' => 'company_address',    'value' => 'Your Company Address',  'group' => 'company',   'label' => 'Company Address',       'type' => 'textarea'],
            ['key' => 'company_gst',        'value' => '',                      'group' => 'company',   'label' => 'GST Number',            'type' => 'text'],
            ['key' => 'company_logo',       'value' => '',                      'group' => 'company',   'label' => 'Company Logo',          'type' => 'file'],
            ['key' => 'currency_symbol',    'value' => '₹',                    'group' => 'company',   'label' => 'Currency Symbol',       'type' => 'text'],
            ['key' => 'currency_code',      'value' => 'INR',                   'group' => 'company',   'label' => 'Currency Code',         'type' => 'text'],
            // Work
            ['key' => 'work_start_time',    'value' => '09:00',                 'group' => 'work',      'label' => 'Work Start Time',       'type' => 'text'],
            ['key' => 'work_end_time',      'value' => '18:00',                 'group' => 'work',      'label' => 'Work End Time',         'type' => 'text'],
            ['key' => 'work_hours_per_day', 'value' => '8',                     'group' => 'work',      'label' => 'Work Hours Per Day',    'type' => 'text'],
            ['key' => 'late_threshold',     'value' => '15',                    'group' => 'work',      'label' => 'Late Threshold (mins)', 'type' => 'text'],
            ['key' => 'working_days',       'value' => 'mon,tue,wed,thu,fri',   'group' => 'work',      'label' => 'Working Days',          'type' => 'text'],
            ['key' => 'week_start_day',     'value' => 'mon',                   'group' => 'work',      'label' => 'Week Starting Day',     'type' => 'text'],
            ['key' => 'week_off_days',      'value' => 'sun',                   'group' => 'work',      'label' => 'Weekly Off Days',       'type' => 'text'],
            // Tax
            ['key' => 'invoice_tax',        'value' => '18',                    'group' => 'invoice',   'label' => 'Default Tax %',         'type' => 'text'],
            ['key' => 'invoice_prefix',     'value' => 'INV',                   'group' => 'invoice',   'label' => 'Invoice Prefix',        'type' => 'text'],
            ['key' => 'quotation_prefix',   'value' => 'QUO',                   'group' => 'invoice',   'label' => 'Quotation Prefix',      'type' => 'text'],
            ['key' => 'proforma_prefix',    'value' => 'PRO',                   'group' => 'invoice',   'label' => 'Proforma Prefix',       'type' => 'text'],
            // Notifications
            ['key' => 'notify_task_assign',         'value' => '1', 'group' => 'notifications', 'label' => 'Notify on Task Assign',    'type' => 'boolean'],
            ['key' => 'notify_deadline',            'value' => '1', 'group' => 'notifications', 'label' => 'Notify on Deadline',        'type' => 'boolean'],
            ['key' => 'notify_leave_request',       'value' => '1', 'group' => 'notifications', 'label' => 'Notify on Leave Request',   'type' => 'boolean'],
            ['key' => 'notify_report_pending',      'value' => '1', 'group' => 'notifications', 'label' => 'Notify on Report Pending',  'type' => 'boolean'],
            ['key' => 'notify_payment_pending',     'value' => '1', 'group' => 'notifications', 'label' => 'Notify on Payment Pending', 'type' => 'boolean'],
            ['key' => 'notify_amc_renewal',         'value' => '1', 'group' => 'notifications', 'label' => 'Notify on AMC Renewal',     'type' => 'boolean'],
            // Mailbox IMAP Configuration
            ['key' => 'mailbox_imap_enabled',       'value' => '0',                     'group' => 'mailbox',   'label' => 'Enable Domain Mailbox', 'type' => 'boolean'],
            ['key' => 'mailbox_imap_host',          'value' => 'imap.gmail.com',        'group' => 'mailbox',   'label' => 'IMAP Host',             'type' => 'text'],
            ['key' => 'mailbox_imap_port',          'value' => '993',                   'group' => 'mailbox',   'label' => 'IMAP Port',             'type' => 'text'],
            ['key' => 'mailbox_imap_encryption',    'value' => 'ssl',                   'group' => 'mailbox',   'label' => 'IMAP Encryption',       'type' => 'text'],
            ['key' => 'mailbox_imap_username',      'value' => '',                      'group' => 'mailbox',   'label' => 'IMAP Username',         'type' => 'text'],
            ['key' => 'mailbox_imap_password',      'value' => '',                      'group' => 'mailbox',   'label' => 'IMAP Password',         'type' => 'password'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
