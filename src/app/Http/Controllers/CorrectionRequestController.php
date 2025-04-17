<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $correctionRequests = CorrectionRequest::with('user', 'attendance')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('correction_requests.index', compact('correctionRequests'));
    }
}
