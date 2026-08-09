<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RespondentType;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RespondentTypeController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/respondent-types
     * List tipe responden aktif (KS, GURU, MURID).
     */
    public function index(Request $request)
    {
        try {
            $types = RespondentType::where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'title', 'descriptions']);

            $response = $this->successResponse($types, 'Respondent types retrieved successfully');

            Log::info('Get respondent types successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);

            Log::error('Get respondent types error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);

            return $response;
        }
    }
}
