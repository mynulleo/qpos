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
}
