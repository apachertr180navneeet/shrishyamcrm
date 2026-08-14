<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scheme;
use App\Models\AgeSlab;

class SchemeController extends Controller
{
    public function index()
    {
        $schemes = Scheme::with(['ageSlabs', 'members'])->get();
        return view('admin.schemes.index', compact('schemes'));
    }

    public function ageSlabs()
    {
        $schemes = Scheme::with('ageSlabs')->get();
        $ageSlabs = AgeSlab::with('scheme')->orderBy('scheme_id')->orderBy('min_age')->get();
        return view('admin.schemes.age_slabs', compact('schemes', 'ageSlabs'));
    }

    public function storeAgeSlab(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:schemes,id',
            'min_age' => 'required|numeric|min:0',
            'max_age' => 'required|numeric|gte:min_age',
            'joining_amount' => 'required|numeric|min:0',
            'support_amount' => 'required|numeric|min:0',
        ]);

        AgeSlab::create([
            'scheme_id' => $request->scheme_id,
            'slab_code' => 'SLAB-' . strtoupper(substr($request->min_age . '-' . $request->max_age, 0, 10)),
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'joining_amount' => $request->joining_amount,
            'support_amount' => $request->support_amount,
            'status' => 'Active',
            'effective_from' => now()->toDateString(),
            'effective_to' => '2030-12-31',
        ]);

        return back()->with('success', 'Age Slab created successfully!');
    }

    public function getSlabByAge(Request $request)
    {
        $schemeId = $request->scheme_id;
        $age = (int)$request->age;

        $slab = AgeSlab::where('scheme_id', $schemeId)
            ->where('status', 'Active')
            ->where('min_age', '<=', $age)
            ->where('max_age', '>=', $age)
            ->first();

        if (!$slab) {
            $slab = AgeSlab::where('scheme_id', $schemeId)->where('status', 'Active')->first();
        }

        return response()->json([
            'success' => true,
            'slab' => $slab
        ]);
    }
}
