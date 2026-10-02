<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\BaseController;
use App\Models\OrganizationMembership;
use App\Models\System\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationMembershipController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $memberships = OrganizationMembership::orderBy('sorting', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($memberships);
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
            'org_name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'logo_file' => 'nullable|image|max:3072',
            'show_in_invoice' => 'nullable|boolean',
            'sorting' => 'nullable|integer',
        ]);

        $logoPath = $request->input('logo');

        // Handle uploaded file
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $logoPath = $this->upload($file, 'memberships');
        } elseif (!empty($request->logo_base64) && is_base64($request->logo_base64)) {
            $logoPath = $this->upload($request->logo_base64, 'memberships', null, true);
        }

        $membership = OrganizationMembership::create([
            'org_name' => $validated['org_name'],
            'logo' => $logoPath,
            'show_in_invoice' => $request->boolean('show_in_invoice', true),
            'sorting' => $validated['sorting'] ?? 0,
            'status' => 'active',
        ]);

        $this->syncSiteSettingMemberships();

        return response()->json([
            'message' => 'Organization Membership added successfully',
            'data' => $membership,
        ], 201);
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
        $membership = OrganizationMembership::findOrFail($id);

        $validated = $request->validate([
            'org_name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'logo_file' => 'nullable|image|max:3072',
            'show_in_invoice' => 'nullable|boolean',
            'sorting' => 'nullable|integer',
        ]);

        $logoPath = $membership->logo;

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $logoPath = $this->upload($file, 'memberships', $membership->logo);
        } elseif (!empty($request->logo_base64) && is_base64($request->logo_base64)) {
            $logoPath = $this->upload($request->logo_base64, 'memberships', $membership->logo, true);
        } elseif ($request->has('logo') && !empty($request->logo)) {
            $logoPath = $request->logo;
        }

        $membership->update([
            'org_name' => $validated['org_name'],
            'logo' => $logoPath,
            'show_in_invoice' => $request->has('show_in_invoice') ? $request->boolean('show_in_invoice') : $membership->show_in_invoice,
            'sorting' => $validated['sorting'] ?? $membership->sorting,
        ]);

        $this->syncSiteSettingMemberships();

        return response()->json([
            'message' => 'Organization Membership updated successfully',
            'data' => $membership,
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
        $membership = OrganizationMembership::findOrFail($id);
        if (!empty($membership->logo) && Storage::disk('public')->exists($membership->logo)) {
            Storage::disk('public')->delete($membership->logo);
        }
        $membership->delete();

        $this->syncSiteSettingMemberships();

        return response()->json([
            'message' => 'Organization Membership removed successfully',
        ]);
    }

    /**
     * Toggle show_in_invoice
     */
    public function toggleInvoiceShow($id)
    {
        $membership = OrganizationMembership::findOrFail($id);
        $membership->show_in_invoice = !$membership->show_in_invoice;
        $membership->save();

        $this->syncSiteSettingMemberships();

        return response()->json([
            'message' => 'Visibility in invoice updated',
            'show_in_invoice' => $membership->show_in_invoice,
        ]);
    }

    /**
     * Sync memberships into site_settings table as JSON cache
     */
    private function syncSiteSettingMemberships()
    {
        try {
            $all = OrganizationMembership::where('status', 'active')
                ->orderBy('sorting', 'asc')
                ->get();
            $conf = SiteSetting::first();
            if ($conf) {
                $conf->memberships = $all->toArray();
                $conf->save();
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }
}
