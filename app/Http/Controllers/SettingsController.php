<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteSettings;
use App\Models\EmailSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSettings::whereIn('key', ['about', 'terms'])->get();
        $about = $settings->firstWhere('key', 'about');
        $terms = $settings->firstWhere('key', 'terms');

        return view('settings.about', compact('about'))
            ->with('terms', $terms);
    }

    public function about()
    {
        $about = SiteSettings::firstOrCreate(['key' => 'about'], ['value' => '']);
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_about_title'], ['value' => '']);
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_about_description'], ['value' => '']);

        $about->meta_about_title = $metaTitle->value ?? '';
        $about->meta_about_description = $metaDescription->value ?? '';

        return view('settings.about', compact('about'));
    }

    public function updateAbout(Request $request)
    {
        $value = $request->input('value');
        $plainText = trim(strip_tags($value));

        if ($plainText === '') {
            return back()->withInput()->withErrors(['value' => 'About content is required.']);
        }

        // Validate title and description
        $request->validate([
            'meta_about_title' => 'required|string|max:255',
            'meta_about_description' => 'required|string',
        ]);

        // Save About Content
        $about = SiteSettings::firstOrCreate(['key' => 'about'], ['value' => '']);
        $about->value = $value;
        $about->save();

        // Save Meta About Title
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_about_title'], ['value' => '']);
        $metaTitle->value = $request->input('meta_about_title');
        $metaTitle->save();

        // Save Meta About Description
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_about_description'], ['value' => '']);
        $metaDescription->value = $request->input('meta_about_description');
        $metaDescription->save();

        return redirect()->route('settings.about')->with('success', 'About updated successfully.');
    }

    // public function contact()
    // {
    //     $contact = SiteSettings::firstOrCreate(
    //     ['key' => 'contact_description'], 
    //     ['value' => '']
    // );

    //     $contact = SiteSettings::where('key', 'contact_description')->firstOrCreate();
    //     $contact->phone = SiteSettings::where('key', 'contact_phone')->firstOrCreate()->value ?? "";
    //     $contact->email = SiteSettings::where('key', 'contact_email')->firstOrCreate()->value ?? "";
    //     $contact->address = SiteSettings::where('key', 'contact_address')->firstOrCreate()->value ?? "";
    //     $contact->meta_title = SiteSettings::where('key', 'meta_contact_title')->firstOrCreate()->value ?? "";
    //     $contact->meta_description = SiteSettings::where('key', 'meta_contact_description')->firstOrCreate()->value ?? "";
    //     return view('settings.contact', compact('contact'));
    // }

    public function contact()
    {
        // Ensure each setting exists in the database
        $contactDescription = SiteSettings::firstOrCreate(
            ['key' => 'contact_description'],
            ['value' => '']
        );

        $contactPhone = SiteSettings::firstOrCreate(
            ['key' => 'contact_phone'],
            ['value' => '']
        );

        $contactEmail = SiteSettings::firstOrCreate(
            ['key' => 'contact_email'],
            ['value' => '']
        );

        $contactAddress = SiteSettings::firstOrCreate(
            ['key' => 'contact_address'],
            ['value' => '']
        );

        $metaTitle = SiteSettings::firstOrCreate(
            ['key' => 'meta_contact_title'],
            ['value' => '']
        );

        $metaDescription = SiteSettings::firstOrCreate(
            ['key' => 'meta_contact_description'],
            ['value' => '']
        );

        // Create an object to pass to the view
        $contact = (object)[
            'description' => $contactDescription->value,
            'phone' => $contactPhone->value,
            'email' => $contactEmail->value,
            'address' => $contactAddress->value,
            'meta_title' => $metaTitle->value,
            'meta_description' => $metaDescription->value,
        ];

        return view('settings.contact', compact('contact'));
    }


    public function updateContact(Request $request)
    {
        // Validate title and description
        $request->validate([
            'contact_description' => 'required|min:20',
            'contact_phone' => 'required|string|max:15',
            'contact_email' => 'required|email',
            'contact_address' => 'required|string|min:20',
            'meta_contact_title' => 'required|string|max:255',
            'meta_contact_description' => 'required|string',
        ], [
            'contact_description.required' => 'Contact description is required.',
            'contact_description.min' => 'Contact description must be at least 20 characters.',

            'contact_phone.required' => 'Phone number is required.',
            'contact_phone.max' => 'Phone number cannot exceed 15 characters.',

            'contact_email.required' => 'Email address is required.',
            'contact_email.email' => 'Please enter a valid email address.',

            'contact_address.required' => 'Address is required.',
            'contact_address.min' => 'Address must be at least 20 characters.',

            'meta_contact_title.required' => 'Meta title is required.',
            'meta_contact_title.max' => 'Meta title cannot exceed 255 characters.',

            'meta_contact_description.required' => 'Meta description is required.',
        ]);


        DB::transaction(function () use ($request) {
            // Save About Content
            $description = SiteSettings::firstOrCreate(['key' => 'contact_description'], ['value' => '']);
            $description->value = $request->input('contact_description') ?? $request->contact_description;
            $description->save();

            $phone = SiteSettings::firstOrCreate(['key' => 'contact_phone'], ['value' => '']);
            $phone->value = $request->input('contact_phone') ?? $request->contact_phone;
            $phone->save();


            $email = SiteSettings::firstOrCreate(['key' => 'contact_email'], ['value' => '']);
            $email->value = $request->input('contact_email') ?? $request->contact_email;
            $email->save();


            $address = SiteSettings::firstOrCreate(['key' => 'contact_address'], ['value' => '']);
            $address->value = $request->input('contact_address') ?? $request->contact_address;
            $address->save();

            // Save Meta About Title
            $metaTitle = SiteSettings::firstOrCreate(['key' => 'contact_phone'], ['value' => '']);
            $metaTitle->value = $request->input('contact_phone');
            $metaTitle->save();

            $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_contact_title'], ['value' => '']);
            $metaDescription->value = $request->input('meta_contact_title');
            $metaDescription->save();

            // Save Meta About Description
            $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_contact_description'], ['value' => '']);
            $metaDescription->value = $request->input('meta_contact_description');
            $metaDescription->save();
        });

        return redirect()->route('settings.contact')->with('success', 'Contact content updated successfully.');
    }

    public function store(Request $request)
    {
        if (!$request->key || !$request->value) {
            return back()->with('error', 'Both key and value are required.');
        }

        if (!in_array($request->key, ['about', 'terms'])) {
            return back()->with('error', 'Invalid setting key.');
        }

        $exists = SiteSettings::where('key', $request->key)->exists();
        if ($exists) {
            return back()->with('error', ucfirst($request->key) . ' already exists. Please edit instead.');
        }

        SiteSettings::create([
            'key' => $request->key,
            'value' => $request->value,
        ]);

        return redirect()->back()->with('success', ucfirst($request->key) . ' added successfully.');
    }


    public function terms()
    {
        $terms = SiteSettings::firstOrCreate(['key' => 'terms'], ['value' => '']);
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_terms_title'], ['value' => '']);
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_terms_description'], ['value' => '']);

        $terms->meta_terms_title = $metaTitle->value ?? '';
        $terms->meta_terms_description = $metaDescription->value ?? '';

        return view('settings.terms', compact('terms'));
    }

    public function updateTerms(Request $request)
    {
        $value = $request->input('value');
        $plainText = trim(strip_tags($value));

        if ($plainText === '') {
            return back()->withInput()->withErrors(['value' => 'Terms & Conditions content is required.']);
        }

        // Validate title and description
        $request->validate([
            'meta_terms_title' => 'required|string|max:255',
            'meta_terms_description' => 'required|string',
        ]);

        // Save Terms Content
        $terms = SiteSettings::firstOrCreate(['key' => 'terms'], ['value' => '']);
        $terms->value = $value;
        $terms->save();

        // Save Meta Terms Title
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_terms_title'], ['value' => '']);
        $metaTitle->value = $request->input('meta_terms_title');
        $metaTitle->save();

        // Save Meta Terms Description
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_terms_description'], ['value' => '']);
        $metaDescription->value = $request->input('meta_terms_description');
        $metaDescription->save();

        return redirect()->route('settings.terms')->with('success', 'Terms updated successfully.');
    }


    public function privacyPolicy()
    {
        $privacy = SiteSettings::firstOrCreate(['key' => 'privacy'], ['value' => '']);
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_privacy_title'], ['value' => '']);
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_privacy_description'], ['value' => '']);

        $privacy->meta_privacy_title = $metaTitle->value ?? '';
        $privacy->meta_privacy_description = $metaDescription->value ?? '';

        return view('settings.privacyPolicy', compact('privacy'));
    }

    public function updatePrivacyPolicyContent(Request $request)
    {
        $description = $request->input('description');
        $plainText = trim(strip_tags($description));

        if ($plainText === '') {
            return back()->withInput()->withErrors(['description' => 'Privacy Policy content is required.']);
        }

        // Validate title and description
        $request->validate([
            'meta_privacy_title' => 'required|string|max:255',
            'meta_privacy_description' => 'required|string',
        ]);

        // Save Privacy Policy Content
        $privacy = SiteSettings::firstOrCreate(['key' => 'privacy'], ['value' => '']);
        $privacy->value = $description;
        $privacy->save();

        // Save Meta Privacy Title
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_privacy_title'], ['value' => '']);
        $metaTitle->value = $request->input('meta_privacy_title');
        $metaTitle->save();

        // Save Meta Privacy Description
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_privacy_description'], ['value' => '']);
        $metaDescription->value = $request->input('meta_privacy_description');
        $metaDescription->save();

        return redirect()->route('settings.privacyPolicy')->with('success', 'Privacy Policy updated successfully.');
    }


    public function refund()
    {
        $refund = SiteSettings::firstOrCreate(['key' => 'refund'], ['value' => '']);
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_refund_title'], ['value' => '']);
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_refund_description'], ['value' => '']);

        $refund->meta_refund_title = $metaTitle->value ?? '';
        $refund->meta_refund_description = $metaDescription->value ?? '';

        return view('settings.refund', compact('refund'));
    }

    public function updateRefundContent(Request $request)
    {
        $description = $request->input('description');
        $plainText = trim(strip_tags($description));

        if ($plainText === '') {
            return back()->withInput()->withErrors(['description' => 'Refund content is required.']);
        }

        // Validate title and description
        $request->validate([
            'meta_refund_title' => 'required|string|max:255',
            'meta_refund_description' => 'required|string',
        ]);

        // Save Refund Content
        $refund = SiteSettings::firstOrCreate(['key' => 'refund'], ['value' => '']);
        $refund->value = $description;
        $refund->save();

        // Save Meta Refund Title
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_refund_title'], ['value' => '']);
        $metaTitle->value = $request->input('meta_refund_title');
        $metaTitle->save();

        // Save Meta Refund Description
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_refund_description'], ['value' => '']);
        $metaDescription->value = $request->input('meta_refund_description');
        $metaDescription->save();

        return redirect()->route('settings.refund')->with('success', 'Refund content updated successfully.');
    }

    /** Show logo & favicon settings */
    public function logoUpdate()
    {
        $logo = SiteSettings::where('key', 'logo')->first();
        $favicon = SiteSettings::where('key', 'favicon')->first();
        $barcode = SiteSettings::where('key', 'barcode')->first();


        $site = SiteSettings::firstOrCreate(['key' => 'site_title'], ['value' => '']);
        $metaSiteTitle = SiteSettings::firstOrCreate(['key' => 'meta_site_title'], ['value' => '']);
        $metaSiteDescription = SiteSettings::firstOrCreate(['key' => 'meta_site_description'], ['value' => '']);

        $site->site_title = $site->value ?? '';
        $site->meta_site_title = $metaSiteTitle->value ?? '';
        $site->meta_site_description = $metaSiteDescription->value ?? '';


        return view('settings.logoUpdate', [
            'logo' => $logo ? $logo->value : null,
            'favicon' => $favicon ? $favicon->value : null,
            'barcode' => $barcode ? $barcode->value : null,
            'site' => $site
        ]);
    }


    public function updateSiteDetails(Request $request)
    {
        // Validate inputs
        $request->validate([
            'site_title' => 'required|string|max:255',
            'meta_site_title' => 'required|string|max:255',
            'meta_site_description' => 'required|string',
        ]);

        // Save Site Title
        $siteTitle = SiteSettings::firstOrCreate(['key' => 'site_title'], ['value' => '']);
        $siteTitle->value = $request->input('site_title');
        $siteTitle->save();

        // Save Meta Site Title
        $metaTitle = SiteSettings::firstOrCreate(['key' => 'meta_site_title'], ['value' => '']);
        $metaTitle->value = $request->input('meta_site_title');
        $metaTitle->save();

        // Save Meta Site Description
        $metaDescription = SiteSettings::firstOrCreate(['key' => 'meta_site_description'], ['value' => '']);
        $metaDescription->value = $request->input('meta_site_description');
        $metaDescription->save();

        return redirect()->route('settings.logo.update')
            ->with('success', 'Site details updated successfully.');
    }


    /** Update Logo */
    public function updateLogo(Request $request)
    {
        try {
            // Validate only if normal file upload is provided
            if ($request->hasFile('favicon')) {
                $request->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                ], [
                    'logo.image' => 'The logo must be an image.',
                    'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, svg.',
                    'logo.max' => 'The logo may not be greater than 2 MB.',
                ]);
            }

            // Handle cropped image
            if ($request->filled('cropped_image')) {


                $data = $request->cropped_image;

                // Validate base64 format
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif, etc.
                    $data = base64_decode($data);

                    if ($data === false) {
                        throw new \Exception('Base64 decode failed.');
                    }

                    $filename = 'uploads/settings/' . uniqid('logo_') . '.' . $type;
                    Storage::disk('public')->put($filename, $data);

                    $path = $filename;
                } else {
                    throw new \Exception('Invalid image data.');
                }
            } elseif ($request->hasFile('logo')) {
                // Handle normal uploaded image
                $file = $request->file('logo');
                $ext = $file->getClientOriginalExtension();
                $filename = 'uploads/settings/' . uniqid('logo_') . '.' . $ext;
                Storage::disk('public')->putFileAs('uploads/settings', $file, basename($filename));
                $path = $filename;
            }

            if ($path) {
                SiteSettings::updateOrCreate(['key' => 'logo'], ['value' => $path]);
                $request->session()->flash('success', 'Favicon updated successfully.');
            } else {
                $request->session()->flash('error', 'No favicon or cropped image provided.');
            }

            return back();
        } catch (\Exception $e) {
            Log::error('Favicon update failed: ' . $e->getMessage());
            $request->session()->flash('error', 'An error occurred while updating the log: ' . $e->getMessage());
            return back();
        }
    }

    public function updateFavicon(Request $request)
    {
        try {
          
            if ($request->hasFile('favicon')) {
                $request->validate([
                    'favicon' => 'image|mimes:jpeg,png,jpg,svg|max:1024|dimensions:max_width=128,max_height=128',
                ], [
                    'favicon.image' => 'The favicon must be an image.',
                    'favicon.mimes' => 'The favicon must be a file of type: jpeg, png, jpg, svg.',
                    'favicon.max' => 'The favicon may not be greater than 1 MB.',
                    'favicon.dimensions' => 'The favicon may not be greater than 128x128 pixels.',
                ]);
            }

            $path = null;
            $base64Value = null;

            if ($request->filled('cropped_image')) {

                $data = $request->cropped_image;

                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {

                    $base64Value = $data;

                    $raw = substr($data, strpos($data, ',') + 1);
                    $decoded = base64_decode($raw);

                    if ($decoded === false) {
                        throw new \Exception('Base64 decode failed.');
                    }

                    $extension = strtolower($type[1]);
                    $filename = 'uploads/settings/' . uniqid('favicon_') . '.' . $extension;

                    Storage::disk('public')->put($filename, $decoded);
                    $path = $filename;

                } else {
                    throw new \Exception('Invalid image data.');
                }
            }

            elseif ($request->hasFile('favicon')) {

                $file = $request->file('favicon');
                $ext = strtolower($file->getClientOriginalExtension());

                $filename = 'favicon_' . uniqid() . '.' . $ext;
                $path = 'uploads/settings/' . $filename;

                Storage::disk('public')->putFileAs('uploads/settings', $file, $filename);

                try {
                    if (is_uploaded_file($file->getRealPath()) || file_exists($file->getRealPath())) {
                        $fileContent = file_get_contents($file->getRealPath());
                        if ($fileContent !== false) {
                            $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/' . $ext;
                            $base64Value = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
                        }
                    }
                } catch (\Throwable $ex) {
                   
                }
            }

            if ($path && $base64Value === null) {
                if (Storage::disk('public')->exists($path)) {
                    $stored = Storage::disk('public')->get($path);
                    if ($stored !== false) {
                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/' . $ext;
                        $base64Value = 'data:' . $mime . ';base64,' . base64_encode($stored);
                    }
                }
            }

            if ($path) {
                SiteSettings::updateOrCreate(
                    ['key' => 'favicon'],
                    [
                        'value' => $path,
                        'base64_value' => $base64Value
                    ]
                );

                $request->session()->flash('success', 'Favicon updated successfully.');
            } else {
                $request->session()->flash('error', 'No favicon or cropped image provided.');
            }

            return back();

        } catch (\Exception $e) {
            Log::error('Favicon update failed: ' . $e->getMessage());
            $request->session()->flash('error', 'An error occurred while updating the favicon: ' . $e->getMessage());
            return back();
        }
    }


    public function updateBarcode(Request $request)
    {
        try {
            if ($request->hasFile('barcode')) {
                $request->validate([
                    'barcode' => 'image|mimes:jpeg,png,jpg,svg|max:1024|dimensions:max_width=500,max_height=500',
                ], [
                    'barcode.image' => 'The barcode must be an image.',
                    'barcode.mimes' => 'The barcode must be a file of type: jpeg, png, jpg, svg.',
                    'barcode.max' => 'The barcode may not be greater than 1 MB.',
                    'barcode.dimensions' => 'The barcode may not be greater than 500x500 pixels.',
                ]);
            }

            $path = null;
            $base64Value = null;

            if ($request->hasFile('barcode')) {

                $file = $request->file('barcode');
                $ext = $file->getClientOriginalExtension();

                $filename = 'uploads/settings/' . uniqid('barcode_') . '.' . $ext;

                Storage::disk('public')->putFileAs('uploads/settings', $file, basename($filename));
                $path = $filename;

                $fileContent = file_get_contents($file->getRealPath());
                $base64Value = 'data:image/' . $ext . ';base64,' . base64_encode($fileContent);
            }

            if ($path) {
                SiteSettings::updateOrCreate(
                    ['key' => 'barcode'],
                    [
                        'value' => $path,
                        'base64_value' => $base64Value
                    ]
                );

                $request->session()->flash('success', 'Barcode updated successfully.');
            } else {
                $request->session()->flash('error', 'No barcode file provided.');
            }

            return back();

        } catch (\Exception $e) {
            Log::error('Barcode update failed: ' . $e->getMessage());
            $request->session()->flash('error', 'An error occurred while updating the barcode: ' . $e->getMessage());
            return back();
        }
    }


    /** Delete logo */
    public function logoDelete()
    {
        $setting = SiteSettings::where('key', 'logo')->first();

        if ($setting && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
            $setting->value = null;
            $setting->save();
        }

        return response()->json(['success' => true]);
    }

    /** Delete favicon */
    public function faviconDelete()
    {
        $setting = SiteSettings::where('key', 'favicon')->first();

        if ($setting && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
            $setting->value = null;
            $setting->base64_value = null;
            $setting->save();
        }

        return response()->json(['success' => true]);
    }

     /** Delete barcode */
    public function barcodeDelete()
    {
        $setting = SiteSettings::where('key', 'barcode')->first();

        if ($setting && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
            $setting->value = null;
            $setting->base64_value = null;
            $setting->save();
        }

        return response()->json(['success' => true]);
    }

    /** Show email settings */
    public function email()
    {
        $emailSettings = EmailSettings::first();
        return view('settings.email', compact('emailSettings'));
    }

    public function storeEmail(Request $request)
    {
        $messages = [
            'host.required' => 'SMTP host is required.',
            'port.required' => 'Port number is required.',
            'port.numeric' => 'Port must be a number.',
            'username.required' => 'Username/Email is required.',
            'password.required' => 'Password is required.',
        ];

        $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|numeric|digits_between:1,4',
            'encryption' => 'nullable|string|max:50',
            'protocol' => 'nullable|string|max:50',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ], $messages);

        EmailSettings::create($request->only([
            'from_name',
            'host',
            'port',
            'from_email',
            'encryption',
            'protocol',
            'username',
            'password'
        ]));

        return redirect()->route('settings.email')->with('success', 'Email settings saved successfully.');
    }

    public function updateEmail(Request $request, $id = null)
    {
        $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|numeric|digits_between:1,4',
            'from_email' => 'required|email|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        $emailSetting = EmailSettings::find($id) ?? EmailSettings::first();
        if (!$emailSetting) {
            return redirect()->back()->with('error', 'Email not found.');
        }

        $emailSetting->update($request->only([
            'from_name',
            'host',
            'port',
            'from_email',
            'encryption',
            'protocol',
            'username',
            'password'
        ]));

        return redirect()->route('settings.email')->with('success', 'Email updated successfully.');
    }

    public function socialMedia()
    {
        $socialSettings = SiteSettings::whereIn('key', [
            'facebook',
            'instagram',
            'twitter',
            'linkedin',
            'youtube',
            'tiktok',
            'snapchat',
            'copyright_text',
            'footer_note'
        ])->pluck('value', 'key');

        return view('settings.social-media', compact('socialSettings'));
    }

    public function updateSocialMedia(Request $request)
    {
        $request->validate([
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'snapchat' => 'nullable|url',
            'copyright_text' => 'required|string|max:255',
            'footer_note' => 'required|string|max:500',
        ]);

        $keys = ['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'snapchat', 'copyright_text', 'footer_note'];

        collect($keys)->each(function ($key) use ($request) {
            SiteSettings::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
        });

        return redirect()->route('settings.social')->with('success', 'Social Media settings updated successfully.');
    }
}
