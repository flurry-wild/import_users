<?php

namespace App\Http\Controllers;

use App\Jobs\ParseUsers;
use App\Services\UsersImportService;
use Illuminate\Http\JsonResponse;

class XlsxController extends Controller
{
    public function __construct(protected UsersImportService $service) {}

    public function createImportJob()
    {
        return new JsonResponse(ParseUsers::dispatch());
    }

    public function info()
    {
        return new JsonResponse($this->service->info());
    }
}
