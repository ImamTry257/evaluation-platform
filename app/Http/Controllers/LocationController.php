<?php

namespace App\Http\Controllers;

use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/locations/provinces
     * Proxy daftar provinsi dari https://wilayah.id/api/provinces.json
     */
    public function provinces(Request $request)
    {
        try {
            $response = Http::timeout(10)->get('https://wilayah.id/api/provinces.json');

            if ($response->failed()) {
                $response = $this->errorResponse('Failed to fetch provinces from external API', 502);

                Log::error('Register error', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true)
                ]);

                return $response;
            }

            $response = $this->successResponse($response->json('data'), 'Provinces retrieved successfully');

            Log::info('Register successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Failed to fetch provinces from external API', 502);

            Log::error('Register error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }

    /**
     * GET /api/v1/locations/regencies/{provinceCode}
     * Proxy daftar kabupaten/kota dari https://wilayah.id/api/regencies/{provinceCode}.json
     */
    public function regencies(Request $request, string $provinceCode)
    {
        try {
            // Regional lock: kalau APP_ALL_PROVINCE=false, paksa ke DEFAULT_CITY_CODE
            if (!config('location.all_province')) {
                $provinceCode = config('location.default_city_code');
            }
            $response = Http::timeout(10)->get("https://wilayah.id/api/regencies/{$provinceCode}.json");

            if ($response->failed()) {
                $response = $this->errorResponse('Failed to fetch regencies from external API', 502);

                Log::error('Fetch regencies error', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);

                return $response;
            }

            $response = $this->successResponse($response->json('data'), 'Regencies retrieved successfully');

            Log::info('Fetch regencies successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Failed to fetch regencies from external API', 502);

            Log::error('Fetch regencies error', [
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
