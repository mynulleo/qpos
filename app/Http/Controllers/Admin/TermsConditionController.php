<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\BaseController;
use App\Models\TermsCondition;
use Illuminate\Http\Request;

class TermsConditionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->format() == 'html') {
            return view('layouts.admin_app');
        }

        $query = TermsCondition::query();

        if (!empty($request->module_name) && $request->module_name !== 'all') {
            $query->where('module_name', $request->module_name);
        }

        if (!empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if (!empty($request->keyword)) {
            $kw = trim($request->keyword);
            $query->where(function ($q) use ($kw) {
                $q->where('condition_text', 'like', "%{$kw}%")
                  ->orWhere('module_name', 'like', "%{$kw}%");
            });
        }

        $terms = $query->orderBy('module_name')
                       ->orderBy('sorting')
                       ->orderBy('id', 'asc')
                       ->get();

        return response()->json($terms);
    }

    /**
     * Get active terms by specific module name (e.g. Invoice, Purchase Order, Warranty, Quotation)
     */
    public function byModule($module)
    {
        $module = urldecode($module);
        $terms = TermsCondition::active()
            ->byModule($module)
            ->orderBy('sorting', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($terms);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.admin_app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_name' => 'required|string|max:100',
            'condition_text' => 'required|string',
            'sorting' => 'nullable|integer',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $term = TermsCondition::create([
            'module_name' => $validated['module_name'],
            'condition_text' => $validated['condition_text'],
            'sorting' => $validated['sorting'] ?? 0,
            'is_default' => $request->boolean('is_default', true),
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'Terms & Condition created successfully',
            'data' => $term,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.admin_app');
        }

        $term = TermsCondition::findOrFail($id);
        return response()->json($term);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('layouts.admin_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $term = TermsCondition::findOrFail($id);

        $validated = $request->validate([
            'module_name' => 'required|string|max:100',
            'condition_text' => 'required|string',
            'sorting' => 'nullable|integer',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $term->update([
            'module_name' => $validated['module_name'],
            'condition_text' => $validated['condition_text'],
            'sorting' => $validated['sorting'] ?? $term->sorting,
            'is_default' => $request->has('is_default') ? $request->boolean('is_default') : $term->is_default,
            'status' => $validated['status'] ?? $term->status,
        ]);

        return response()->json([
            'message' => 'Terms & Condition updated successfully',
            'data' => $term,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $term = TermsCondition::findOrFail($id);
        $term->delete();

        return response()->json([
            'message' => 'Terms & Condition deleted successfully',
        ]);
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        $term = TermsCondition::findOrFail($id);
        $term->status = ($term->status === 'active') ? 'inactive' : 'active';
        $term->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $term->status,
        ]);
    }

    /**
     * Toggle is_default status
     */
    public function toggleDefault($id)
    {
        $term = TermsCondition::findOrFail($id);
        $term->is_default = !$term->is_default;
        $term->save();

        return response()->json([
            'message' => 'Default status updated successfully',
            'is_default' => $term->is_default,
        ]);
    }
}
