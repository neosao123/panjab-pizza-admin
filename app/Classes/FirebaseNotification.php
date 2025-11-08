<?php

namespace App\Classes;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class FirebaseNotification
{

	public function sendNotification($data, $notification)
	{
		$url = 'https://fcm.googleapis.com/fcm/send';

		if (isset($data['image'])) {
			if ($data['image'] != "" || $data['image'] != null) $image = $data['image'];
			else $image = url('notify.png');
		} else {
			$image = url('notify.png');
		}

		$message = array(
			'title' => $data['title'],
			'message' => $data['message'],
			'random_id' => $data['random_id'],
			'image' => $image,
			'priority' => 1,
			'android_channel_id' => Config::get('constant.FIREBASE_NOTIFICATION_DEFAULT_CHANNEL')
		);

		$time_to_live = array("ttl" => '1000s');

		$fields = array(
			'registration_ids' => $data['device_id'],
			'data' => $message,
			'android' => $time_to_live,
		);
		$notifyData = array(
			'title' => $notification['title'],
			'body' => $notification['message'],
			'random_id' => $notification['random_id'],
			'image' => $image,
			'priority' => 1,
			'android_channel_id' => Config::get('constant.FIREBASE_NOTIFICATION_DEFAULT_CHANNEL')
		);
		$fields['notification'] = $notifyData;

		$firebaseAccesskey = Config::get('constant.FIREBASE_ACCESS_KEY');
		$headers = array(
			'Authorization:key=AAAA--m0ORA:APA91bHbKJVXU0W1JCqhJfxi-dD3n-rYXa3Gn0EGXg-Gs1RWfraSA_sqjULpZf8AEj48Rty2HpCD7eE9n0bCpRtNGremJFdB-HytCwbNxdkeeYvljkaEHcb-K2Ka16hYIIE7Lqj43gbP',
			'Content-Type:application/json',
			'mutable-content: 1'
		);
		$ch = curl_init();
		if ($url) {
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}

			// Execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}

			// Close connection
			curl_close($ch);

			return $result;
		}
	}
}
