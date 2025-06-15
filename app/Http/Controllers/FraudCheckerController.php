<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FraudCheckerController extends Controller
{
    public function check(Request $request)
    {
        $phone = $request->input('phone');

        if (!$phone) {
            return response()->json(['error' => 'Phone number is required'], 400);
        }

        try {
            $response = Http::get("https://courier.wporderplus.com/api.php", [
                'phone' => $phone
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'External API failed', 'message' => $e->getMessage()], 500);
        }
    }
}
