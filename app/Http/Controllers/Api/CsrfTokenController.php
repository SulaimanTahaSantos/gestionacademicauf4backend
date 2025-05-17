<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CsrfTokenController extends Controller
{   
    public function getToken(): JsonResponse
    {
        return response()->json([
            'token' => csrf_token()
        ]);
    }
}
