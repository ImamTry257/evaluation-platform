<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/settings
     * Get all settings as key-value object.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        // Cast types for frontend convenience
        $result = [
            'activePeriodId' => $settings->get('activePeriodId') ? (int) $settings->get('activePeriodId') : null,
            'evaluationDuration' => (int) $settings->get('evaluationDuration', 60),
            'autoSaveInterval' => (int) $settings->get('autoSaveInterval', 30),
            'allowResume' => filter_var($settings->get('allowResume', 'true'), FILTER_VALIDATE_BOOLEAN),
            'timeoutMinutes' => (int) $settings->get('timeoutMinutes', 5),
        ];

        return $this->successResponse($result, 'Settings retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/settings
     * Bulk update settings by key.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activePeriodId' => 'nullable|integer',
            'evaluationDuration' => 'required|integer|min:1|max:480',
            'autoSaveInterval' => 'required|integer|min:5|max:300',
            'allowResume' => 'required|boolean',
            'timeoutMinutes' => 'required|integer|min:1|max:120',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();

        // Map API keys to setting keys
        $mapping = [
            'activePeriodId' => 'activePeriodId',
            'evaluationDuration' => 'evaluationDuration',
            'autoSaveInterval' => 'autoSaveInterval',
            'allowResume' => 'allowResume',
            'timeoutMinutes' => 'timeoutMinutes',
        ];

        foreach ($mapping as $apiKey => $settingKey) {
            if (array_key_exists($apiKey, $validated)) {
                $value = $validated[$apiKey];
                // Convert boolean to string for storage
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                Setting::set($settingKey, $value);
            }
        }

        // Return updated settings
        return $this->index();
    }
}
