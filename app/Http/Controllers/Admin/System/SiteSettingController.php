<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin\System;

use App\Rules\Base64Image;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Models\System\SiteSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Base\BaseController;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SiteSettingController extends BaseController
{
    /**
     * Ensure printer columns exist in database
     */
    private function ensurePrinterColumns()
    {
        try {
            if (Schema::hasTable('site_settings')) {
                if (!Schema::hasColumn('site_settings', 'printer_type')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('printer_type', 50)->default('thermal')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'normal_paper_size')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('normal_paper_size', 50)->default('A4')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'thermal_paper_size')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('thermal_paper_size', 50)->default('80mm')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'label_preset')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('label_preset', 50)->default('4x2')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'default_vat')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->decimal('default_vat', 8, 2)->default(0)->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'sale_nature')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('sale_nature', 50)->default('both')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'invoice_prefix')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->string('invoice_prefix', 50)->default('POS')->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'show_pos_terms')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->boolean('show_pos_terms')->default(1)->nullable();
                    });
                }
                if (!Schema::hasColumn('site_settings', 'memberships')) {
                    Schema::table('site_settings', function (Blueprint $table) {
                        $table->longText('memberships')->nullable();
                    });
                }
            }
        } catch (\Exception $e) {
            // Ignore if columns exist or connection issue
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->ensurePrinterColumns();
        return response()->json(SiteSetting::with('currency:id,title,short_name')->first());
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->ensurePrinterColumns();
        Artisan::call('optimize:clear');
        Cache::flush();

        $conf = SiteSetting::first();
        $data = $request->all();
        $logo = $request->logo_base64;
        $logo_small = $request->logo_small_base64;
        $favicon = $request->file('favicon');

        if (!empty($conf)) {
            $this->validateCheck($request);

            if (!empty($logo)) {
                $resizeValue = $data['logo_resize_value'] ?? '204x70,175x60';
                $data['logo'] = cloudflare(file: $logo, folder: 'logo', resizeSize: $resizeValue, base64: true);
            }
            if (!empty($logo_small)) {
                $resizeValue = $data['logo_small_resize_value'] ?? '600x200,300x100,150x50';
                $data['logo_small'] = cloudflare(file: $logo_small, folder: 'logo_small', resizeSize: $resizeValue, base64: true);
            }

            // Favicon Icon...
            if (!empty($favicon)) {
                $data['favicon'] = $this->upload($favicon, 'conf', $conf->favicon);
            } else {
                $data['favicon'] = $this->oldFile($conf->favicon);
            }

            $this->processMembershipsData($data, $request);

            $conf->update($data);

            return $this->responseReturn('update', $conf);
        } else {
            $this->validateCheck($request);
            if (!empty($logo)) {
                $resizeValue = $data['logo_resize_value'] ?? '204x70,175x60';
                $data['logo'] = cloudflare(file: $logo, folder: 'logo', resizeSize: $resizeValue, base64: true);
            }
            if (!empty($logo_small)) {
                $resizeValue = $data['logo_small_resize_value'] ?? '600x200,300x100,150x50';
                $data['logo_small'] = cloudflare(file: $logo_small, folder: 'logo_small', resizeSize: $resizeValue, base64: true);
            }
            if (!empty($favicon)) {
                $data['favicon'] = $this->upload($favicon, 'conf');
            }

            $this->processMembershipsData($data, $request);

            $setting = SiteSetting::create($data);

            return $this->responseReturn('create', $setting);
        }
    }

    /**
     * Process memberships array and sync to organization_memberships table
     */
    private function processMembershipsData(array &$data, Request $request)
    {
        $rawMemberships = $request->input('memberships');
        if (is_string($rawMemberships)) {
            $rawMemberships = json_decode($rawMemberships, true) ?? [];
            if (is_string($rawMemberships)) {
                $rawMemberships = json_decode($rawMemberships, true) ?? [];
            }
        }
        if (!is_array($rawMemberships)) {
            $rawMemberships = [];
        }

        $processedMemberships = [];
        $existingIds = [];

        foreach ($rawMemberships as $idx => $m) {
            $orgName = trim($m['org_name'] ?? '');
            if (empty($orgName)) continue;

            $logo = $m['logo'] ?? ($m['logo_url'] ?? null);
            $logoPath = $logo;

            // If logo is a base64 string, upload as image file
            if (!empty($logo) && (str_starts_with($logo, 'data:image') || preg_match('/^data:image\/(\w+);base64,/', $logo))) {
                $code = date('ymdhis') . '-' . rand(1111, 9999);
                $cleanBase64 = preg_replace('/^data:image\/[a-zA-Z0-9]+;base64,/', '', $logo);
                $cleanBase64 = str_replace(' ', '+', $cleanBase64);
                $decodedImg = base64_decode($cleanBase64);
                if ($decodedImg !== false) {
                    $relPath = 'upload/memberships/' . $code . '.png';
                    Storage::disk('public')->put($relPath, $decodedImg);
                    $logoPath = Storage::disk('public')->url($relPath);
                }
            }

            $showInInvoice = !empty($m['show_in_invoice']) ? 1 : 0;
            $memberId = !empty($m['id']) ? $m['id'] : null;

            $orgModel = null;
            if ($memberId) {
                $orgModel = \App\Models\OrganizationMembership::find($memberId);
            }
            if (!$orgModel) {
                $orgModel = \App\Models\OrganizationMembership::where('org_name', $orgName)->first();
            }

            if ($orgModel) {
                $orgModel->update([
                    'org_name' => $orgName,
                    'logo' => $logoPath,
                    'show_in_invoice' => $showInInvoice,
                    'sorting' => $idx + 1,
                    'status' => 'active',
                ]);
            } else {
                $orgModel = \App\Models\OrganizationMembership::create([
                    'org_name' => $orgName,
                    'logo' => $logoPath,
                    'show_in_invoice' => $showInInvoice,
                    'sorting' => $idx + 1,
                    'status' => 'active',
                ]);
            }

            $existingIds[] = $orgModel->id;

            $processedMemberships[] = [
                'id' => $orgModel->id,
                'org_name' => $orgName,
                'logo' => $logoPath,
                'logo_url' => $logoPath,
                'show_in_invoice' => $showInInvoice,
                'sorting' => $idx + 1,
            ];
        }

        try {
            if (Schema::hasTable('organization_memberships')) {
                if (!empty($existingIds)) {
                    \App\Models\OrganizationMembership::whereNotIn('id', $existingIds)->delete();
                } else if (empty($rawMemberships)) {
                    \App\Models\OrganizationMembership::whereNotNull('id')->delete();
                }
            }
        } catch (\Exception $e) {
            // Ignore error
        }

        $data['memberships'] = $processedMemberships;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\SiteSetting  $SiteSetting
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if ($request->format() == 'html') {
            return view('layouts.admin_app');
        }

        $this->ensurePrinterColumns();
        $siteSetting = SiteSetting::with('currency:id,title,short_name')->first();

        return response()->json($siteSetting);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\SiteSetting  $SiteSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(SiteSetting $siteSetting)
    {
        return view('layouts.admin_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Model\SiteSetting  $SiteSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SiteSetting $siteSetting)
    {
        return view('layouts.admin_app');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\SiteSetting  $SiteSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(SiteSetting $siteSetting)
    {
        $old1 = $this->oldFile($siteSetting->logo);
        $old2 = $this->oldFile($siteSetting->logo_small);
        $old3 = $this->oldFile($siteSetting->favicon);

        if (Storage::disk('public')->exists($old1)) {
            Storage::delete($old1);
        }

        if (Storage::disk('public')->exists($old2)) {
            Storage::delete($old2);
        }

        if (Storage::disk('public')->exists($old3)) {
            Storage::delete($old3);
        }

        if ($siteSetting->delete()) {
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } else {
            return response()->json(['message' => 'Delete Unsuccessfully!'], 200);
        }
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request)
    {
        return $request->validate([
            'title' => 'required|string|min:0|max:191',
            'short_title' => 'required|string|min:0|max:191',
            'printer_type' => ['nullable', 'string'],
            'normal_paper_size' => ['nullable', 'string'],
            'thermal_paper_size' => ['nullable', 'string'],
            'label_preset' => ['nullable', 'string'],
            'default_vat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sale_nature' => ['nullable', 'string'],
            'invoice_prefix' => ['nullable', 'string', 'max:50'],
            'show_pos_terms' => ['nullable'],
            'memberships' => ['nullable'],
            'logo_base64' => ['nullable', 'string', new Base64Image()],
            'logo_resize_value' => ['nullable', 'string'],
            'logo_small_base64' => ['nullable', 'string', new Base64Image()],
            'logo_small_resize_value' => ['nullable', 'string'],
            'favicon' => ['nullable', Rule::file()->types(['jpeg', 'jpg', 'png'])->max(1024 * 5)],
        ]);
    }
}
