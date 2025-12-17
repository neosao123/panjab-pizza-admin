<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $settings = [
        // Favicon
        [
            'key' => 'favicon',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // About Page
        [
            'key' => 'about',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_about_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_about_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Contact Page
        [
            'key' => 'contact_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'contact_phone',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'contact_email',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'contact_address',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_contact_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_contact_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Terms & Conditions
        [
            'key' => 'terms',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_terms_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_terms_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Privacy Policy
        [
            'key' => 'privacy',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_privacy_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_privacy_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Refund Policy
        [
            'key' => 'refund',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_refund_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_refund_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Logo
        [
            'key' => 'logo',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Social Media
        [
            'key' => 'facebook',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'instagram',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'twitter',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'linkedin',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'youtube',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'tiktok',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'snapchat',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Footer Settings
        [
            'key' => 'copyright_text',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'footer_note',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Site Settings
        [
            'key' => 'site_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_site_title',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'key' => 'meta_site_description',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],

        // Barcode
        [
            'key' => 'barcode',
            'value' => null,
            'base64_value' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ];
    DB::table('site_settings')->insert($settings);

    }
}
