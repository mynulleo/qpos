<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\SoftwareUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftwareUpdateController extends Controller
{
    protected SoftwareUpdateService $updateService;

    public function __construct(SoftwareUpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Render the admin SPA view for software update.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index(Request $request)
    {
        return view('admin.layouts.admin_app');
    }

    /**
     * Check current update status (pure code-driven inspection).
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $status = $this->updateService->getUpdateStatus();
        return response()->json($status);
    }

    /**
     * Run all pending migrations and SQL patches.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function run(Request $request): JsonResponse
    {
        $result = $this->updateService->runUpdate();
        return response()->json($result);
    }

    /**
     * Mark all pending migrations as completed in the migrations table only (without running DDL).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function syncOnly(Request $request): JsonResponse
    {
        $result = $this->updateService->syncMigrationsTableOnly();
        return response()->json($result);
    }
}
