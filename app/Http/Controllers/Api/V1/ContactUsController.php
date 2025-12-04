<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ContactUsController extends Controller
{

public function contact_us(Request $request)
    {
        try {
            // Validation rules
            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:10',
                'city' => 'required|string',
                'province' => 'required|string',
                'country' => 'required|string',
                'preferred_area' => 'required|string',
                'total_liquid_assets' => 'required|string',
                'timeframe' => 'required|string|in:Immediately,1-2 months,3-6 months,Greater than 1 year',
                'preferred_language' => 'required|string|max:255',
                'marketing_consent'=>'required',
                'terms_consent'=>'required',
            ];

            $messages = [
                'first_name.required' => 'First Name is required.',
                'first_name.string' => 'First Name must be a valid string.',
                'first_name.max' => 'First Name may not exceed 255 characters.',

                'last_name.required' => 'Last Name is required.',
                'last_name.string' => 'Last Name must be a valid string.',
                'last_name.max' => 'Last Name may not exceed 255 characters.',

                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email may not exceed 255 characters.',


                'phone.string' => 'Phone must be a valid string.',
                'phone.max' => 'Phone may not exceed 20 characters.',
                'phone.required'=>'Phone number is required',

                'city.required' => 'City is required.',
                'city.string' => 'City must be a valid string.',
                'city.max' => 'City may not exceed 255 characters.',

                'province.required' => 'Province is required.',
                'province.string' => 'Province must be a valid string.',
                'province.max' => 'Province may not exceed 255 characters.',

                'country.required' => 'Country is required.',
                'country.string' => 'Country must be a valid string.',
                'country.max' => 'Country may not exceed 255 characters.',

                'preferred_area.required' => 'Preferred Area is required.',
                'preferred_area.string' => 'Preferred Area must be a valid string.',
                'preferred_area.max' => 'Preferred Area may not exceed 255 characters.',

                'total_liquid_assets.required' => 'Total liquid assets is required.',
                'total_liquid_assets.string' => 'Total liquid assets must be a valid string.',
                'total_liquid_assets.max' => 'Total liquid assets may not exceed 255 characters.',

                'timeframe.required' => 'Timeframe for starting a business is required.',
                'timeframe.string' => 'Timeframe must be a valid string.',
                'timeframe.in' => 'Timeframe must be one of: Immediately, 1-2 months, 3-6 months, Greater than 1 year.',

                'preferred_language.required' => 'Preferred Language is required.',
                'preferred_language.string' => 'Preferred Language must be a valid string.',
                'preferred_language.max' => 'Preferred Language may not exceed 255 characters.',

                'marketing_consent.required'=>'You must consent to receive marketing messages.',
                'terms_consent.required'=>'You must consent to the terms and conditions.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            // Extract all fields
            $applicationData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'preferred_area' => $request->preferred_area,
                'total_liquid_assets' => $request->total_liquid_assets,
                'timeframe' => $request->timeframe,
                'preferred_language' => $request->preferred_language
            ];

            // Email to applicant
            Mail::send([], [], function ($mail) use ($applicationData) {
                $mail->to($applicationData['email'])
                    ->subject('Franchise Application Received - Pizza franchise')
                    ->html("
                    <p>Dear {$applicationData['first_name']} {$applicationData['last_name']},</p>
                    <p>Thank you for your interest in becoming a franchisee! We have received your application and our team will review it shortly.</p>
                    <p>We appreciate your time and look forward to the possibility of working together.</p>
                    <br>
                    <p>Best regards,<br>Pizza franchise/p>
                ");
            });

            // Email to admin/franchise team
            $adminEmail = 'testing.neosaoservices@gmail.com'; // Change to actual franchise email
            Mail::send([], [], function ($mail) use ($applicationData, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject('New Franchise Application Received')
                    ->html("
                    <h3>New Franchise Application</h3>
                    <p><strong>Personal Information:</strong></p>
                    <p><strong>Name:</strong> {$applicationData['first_name']} {$applicationData['last_name']}</p>
                    <p><strong>Email:</strong> {$applicationData['email']}</p>
                    <p><strong>Phone:</strong> {$applicationData['phone']}</p>
                    <p><strong>City:</strong> {$applicationData['city']}</p>

                    <p><strong>Location Details:</strong></p>
                    <p><strong>Province:</strong> {$applicationData['province']}</p>
                    <p><strong>Country:</strong> {$applicationData['country']}</p>
                    <p><strong>Preferred Area:</strong> {$applicationData['preferred_area']}</p>

                    <p><strong>Financial Information:</strong></p>
                    <p><strong>Total Liquid Assets:</strong> {$applicationData['total_liquid_assets']}</p>
                    <p><strong>Timeframe:</strong> {$applicationData['timeframe']}</p>

                    <p><strong>Preferred Language:</strong> {$applicationData['preferred_language']}</p>

                    <br>
                    <p>Application submitted on: " . now()->format('Y-m-d H:i:s') . "</p>
                ");
            });

            return response()->json([
                "status" => 200,
                'message' => 'Thank you! Your franchise application has been submitted successfully. We will contact you shortly.'
            ], 200);

       } catch (\Exception $ex) {
            Log::error('Failed to submit franchise application', [
                'error' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine()
            ]);

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong while submitting your application. Please try again.'
            ], 400);
        }
    }
}
