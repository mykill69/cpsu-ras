<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('maintenance_mode', false, 'boolean');
        Setting::set('maintenance_message', 'System is under maintenance. Please check back later.', 'string');
        Setting::set('system_name', 'CPSU RAS', 'string');
        Setting::set('system_email', 'cpsu@cpsu.edu.ph', 'string');
        Setting::set('max_file_size', '102400', 'integer');
        Setting::set('allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif', 'string');
        Setting::set('session_lifetime', '120', 'integer');
    }
}