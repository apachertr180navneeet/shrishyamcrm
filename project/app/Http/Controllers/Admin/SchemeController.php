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

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:schemes,code',
            'name' => 'required|string|max:150',
            'name_hindi' => 'required|string|max:150',
            'type' => 'required|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        Scheme::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'name_hindi' => $request->name_hindi,
            'type' => $request->type,
            'status' => $request->status,
            'effective_from' => $request->effective_from ?: now()->toDateString(),
            'effective_to' => $request->effective_to ?: '2030-12-31',
            'description' => $request->description,
        ]);

        return back()->with('success', 'Scheme created successfully!');
    }

    public function update(Request $request, $id)
    {
        $scheme = Scheme::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:schemes,code,' . $scheme->id,
            'name' => 'required|string|max:150',
            'name_hindi' => 'required|string|max:150',
            'type' => 'required|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $scheme->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'name_hindi' => $request->name_hindi,
            'type' => $request->type,
            'status' => $request->status,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Scheme updated successfully!');
    }

    public function destroy($id)
    {
        $scheme = Scheme::findOrFail($id);
        $scheme->delete();

        return back()->with('success', 'Scheme deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $scheme = Scheme::findOrFail($id);
        $scheme->status = ($scheme->status === 'Active') ? 'Inactive' : 'Active';
        $scheme->save();

        return back()->with('success', "Scheme '{$scheme->name_hindi}' status updated to {$scheme->status}!");
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
            'status' => 'required|in:Active,Inactive',
        ]);

        AgeSlab::create([
            'scheme_id' => $request->scheme_id,
            'slab_code' => $request->slab_code ?: ('SLAB-' . $request->min_age . '-' . $request->max_age),
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'joining_amount' => $request->joining_amount,
            'support_amount' => $request->support_amount,
            'status' => $request->status,
            'effective_from' => $request->effective_from ?: now()->toDateString(),
            'effective_to' => $request->effective_to ?: '2030-12-31',
        ]);

        return back()->with('success', 'Age Slab created successfully!');
    }

    public function updateAgeSlab(Request $request, $id)
    {
        $slab = AgeSlab::findOrFail($id);

        $request->validate([
            'scheme_id' => 'required|exists:schemes,id',
            'min_age' => 'required|numeric|min:0',
            'max_age' => 'required|numeric|gte:min_age',
            'joining_amount' => 'required|numeric|min:0',
            'support_amount' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        $slab->update([
            'scheme_id' => $request->scheme_id,
            'slab_code' => $request->slab_code ?: ('SLAB-' . $request->min_age . '-' . $request->max_age),
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'joining_amount' => $request->joining_amount,
            'support_amount' => $request->support_amount,
            'status' => $request->status,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        return back()->with('success', 'Age Slab updated successfully!');
    }

    public function destroyAgeSlab($id)
    {
        $slab = AgeSlab::findOrFail($id);
        $slab->delete();

        return back()->with('success', 'Age Slab deleted successfully!');
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
