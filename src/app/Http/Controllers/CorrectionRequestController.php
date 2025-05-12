<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
{
    $status = $request->input('status', 'pending'); // デフォルトは承認待ち

    $correctionRequests = CorrectionRequest::with(['user', 'attendance'])
        ->when(in_array($status, ['pending', 'approved']), function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return view('correction_requests.index', compact('correctionRequests', 'status'));
}
}
