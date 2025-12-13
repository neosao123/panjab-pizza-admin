<?php

namespace App\Http\Controllers;

use App\Models\DoorDash;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalModel;
use Illuminate\Http\Request;

class DoorDashController extends Controller
{
    private $role, $rights;

    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $this->role = Auth::guard('admin')->user()['role'];
            $this->rights = $this->model->getMenuRights('16.1', $this->role);

            if ($this->rights == '') {
                return redirect('access/denied');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $setting = DoorDash::first();
        return view('doordash.edit', compact('setting'));
    }

    public function store(Request $request)
    {
        // Debug: Check what's being received
        \Log::info('Request Data:', $request->all());

        try {
            $validated = $request->validate([
                'mode' => 'required',
                'test_developer_id' => 'required|string',
                'live_developer_id' => 'required|string',
                'test_key_id' => 'required|string',
                'live_key_id' => 'required|string',
                'test_signing_secret' => 'required|string',
                'live_signing_secret' => 'required|string',
            ]);

            \Log::info('Validated Data:', $validated);

            $existing = DoorDash::first();

            if ($existing) {
                $existing->update($validated);
                \Log::info('Updated existing record:', $existing->toArray());
                return back()->with('success', 'DoorDash settings updated successfully!');
            }

            $created = DoorDash::create($validated);
            \Log::info('Created new record:', $created->toArray());

            return back()->with('success', 'DoorDash settings saved successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('DoorDash Store Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error saving data: ' . $e->getMessage());
        }
    }
}
