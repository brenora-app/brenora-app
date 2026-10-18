<?php

namespace App\Http\Controllers;

use App\Services\DemoDealAuditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoDealAuditorController extends Controller
{
    public function show(): View
    {
        return view('marketing.deal-health-demo');
    }

    public function analyze(Request $request, DemoDealAuditor $auditor): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'min:20', 'max:6000'],
        ]);

        return response()->json($auditor->analyze($validated['notes']));
    }
}
