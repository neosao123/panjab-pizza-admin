<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
// Models
use App\Models\Customer;
use App\Models\Customeraddress;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\SiteSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{

    public function siteData(Request $request)
    {
        try {

            // Direct fetch (NO CACHE)
            $settings = SiteSettings::whereIn('key', [
                'logo',
                'favicon',
                'site_title',
                'contact_email',
                'contact_phone',
                'contact_address'
            ])->get();

            $getValue = fn($key) => optional($settings->where('key', $key)->first())->value;

            $response = [
                'site_name'      => $getValue('site_title') ?? '',
                'contact_email'  => $getValue('contact_email') ?? '',
                'contact_phone'  => $getValue('contact_phone') ?? '',
                'address'        => $getValue('contact_address') ?? '',

                'logo'    => $getValue('logo')
                    ? asset('storage/' . $getValue('logo'))
                    : null,

                'favicon' => $getValue('favicon')
                    ? asset('storage/' . $getValue('favicon'))
                    : null,
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Site data fetched successfully.',
                'data'    => $response
            ], 200);
        } catch (\Exception $ex) {

            Log::error("[API] [SiteData] Failed", [
                'error' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch site data.',
            ], 500);
        }
    }


    public function pageData($pageKey)
    {
        try {

            $key = $pageKey;

            // NO CACHE — direct DB fetch
            if ($key == 'contact') {

                $settings = [
                    'description'       => SiteSettings::where('key', 'contact_description')->value('value') ?? "",
                    'phone'             => SiteSettings::where('key', 'contact_phone')->value('value') ?? "",
                    'email'             => SiteSettings::where('key', 'contact_email')->value('value') ?? "",
                    'address'           => SiteSettings::where('key', 'contact_address')->value('value') ?? "",
                    'metaTitle'         => SiteSettings::where('key', 'meta_contact_title')->value('value') ?? "",
                    'metaDescription'   => SiteSettings::where('key', 'meta_contact_description')->value('value') ?? "",
                ];
            } else {

                $sub_key = match ($key) {
                    'about'   => 'about',
                    'terms'   => 'terms',
                    'privacy' => 'privacy',
                    'refund'  => 'refund',
                    default   => 'about',
                };

                $settings = [
                    'htmlContent'      => SiteSettings::where('key', $key)->value('value') ?? "",
                    'metaTitle'        => SiteSettings::where('key', "meta_{$sub_key}_title")->value('value') ?? "",
                    'metaDescription'  => SiteSettings::where('key', "meta_{$sub_key}_description")->value('value') ?? "",
                ];
            }

            return response()->json([
                'data' => $settings,
                'message' => 'Data fetched',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }


    public function footerData(Request $request)
    {
        try {

            // NO CACHE — direct DB fetch
            $settings = [
                'logo'           => SiteSettings::where('key', 'logo')->value('value'),
                'footer_note'    => SiteSettings::where('key', 'footer_note')->value('value'),
                'copyright_text' => SiteSettings::where('key', 'copyright_text')->value('value'),
                'facebook'       => SiteSettings::where('key', 'facebook')->value('value'),
                'instagram'      => SiteSettings::where('key', 'instagram')->value('value'),
                'twitter'        => SiteSettings::where('key', 'twitter')->value('value'),
                'linkedin'       => SiteSettings::where('key', 'linkedin')->value('value'),
                'youtube'        => SiteSettings::where('key', 'youtube')->value('value'),
                'tiktok'         => SiteSettings::where('key', 'tiktok')->value('value'),
                'snapchat'       => SiteSettings::where('key', 'snapchat')->value('value'),
            ];

            $contact = [
                'description'       => SiteSettings::where('key', 'contact_description')->value('value') ?? "",
                'phone'             => SiteSettings::where('key', 'contact_phone')->value('value') ?? "",
                'email'             => SiteSettings::where('key', 'contact_email')->value('value') ?? "",
                'address'           => SiteSettings::where('key', 'contact_address')->value('value') ?? "",
                'metaTitle'         => SiteSettings::where('key', 'meta_contact_title')->value('value') ?? "",
                'metaDescription'   => SiteSettings::where('key', 'meta_contact_description')->value('value') ?? "",
            ];

            $response = [
                'logo' => $settings['logo'] ? asset('storage/' . $settings['logo']) : "",
                'footer_note' => $settings['footer_note'] ?? "",
                'copyright_text' => $settings['copyright_text'] ?? "",
                'social_links' => [
                    'facebook' => $settings['facebook'] ?? "",
                    'instagram' => $settings['instagram'] ?? "",
                    'twitter' => $settings['twitter'] ?? "",
                    'linkedin' => $settings['linkedin'] ?? "",
                    'youtube' => $settings['youtube'] ?? "",
                    'tiktok' => $settings['tiktok'] ?? "",
                ],
                "contact_info" => $contact
            ];

            return response()->json([
                'data' => $response,
                'message' => 'Data fetched',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
