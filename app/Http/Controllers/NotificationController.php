<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event\SendNotification;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        try {
            event(new SendNotification($request->message));
            return response()->json(['success' => true, 'msg' => 'Notification Added']);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'msg' => $e->getMessage()]);
        }
    }
}
