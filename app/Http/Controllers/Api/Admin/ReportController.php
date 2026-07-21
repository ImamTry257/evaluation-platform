<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseSession;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/reports
     * Get report summary with paginated submissions.
     *
     * Query params: search, page, limit, questionnaireId, periodId
     */
    public function index(Request $request)
    {
        $query = ResponseSession::where('status', 'submitted')
            ->with(['user', 'questionnaire', 'result']);

        // Filter by questionnaire
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaire_id', $request->questionnaireId);
        }

        // Filter by period (via questionnaire)
        if ($request->has('periodId') && $request->periodId) {
            $query->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('evaluation_period_id', $request->periodId);
            });
        }

        // Search by respondent name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Stats (computed from ALL matching records, not paginated)
        // Must eager-load result to access relationship data in PHP
        $allSessions = (clone $query)->with('result')->get();
        $totalSessions = $allSessions->count();
        $totalRespondents = $allSessions->pluck('user_id')->unique()->count();

        // Compute averages from loaded collection (can't use SQL avg on relationship columns)
        $resultsWithScore = $allSessions->filter->result;
        $averageScore = $resultsWithScore->count() > 0
            ? round($resultsWithScore->avg('result.overall_percentage'), 2)
            : 0;
        $averageScoreRaw = $resultsWithScore->count() > 0
            ? round($resultsWithScore->avg('result.overall_score'), 2)
            : 0;

        // Category distribution (from loaded collection)
        $categoryDistribution = $allSessions
            ->map(fn($s) => $s->result?->overall_category)
            ->filter()
            ->countBy()
            ->toArray();

        $scoreRanges = [
            'A' => ['label' => 'Sangat Tinggi (86-100%)', 'count' => 0, 'percentage' => 0],
            'B' => ['label' => 'Tinggi (71-85%)', 'count' => 0, 'percentage' => 0],
            'C' => ['label' => 'Sedang (56-70%)', 'count' => 0, 'percentage' => 0],
            'D' => ['label' => 'Rendah (41-55%)', 'count' => 0, 'percentage' => 0],
            'E' => ['label' => 'Sangat Rendah (0-40%)', 'count' => 0, 'percentage' => 0],
        ];

        foreach ($categoryDistribution as $cat => $count) {
            if (isset($scoreRanges[$cat])) {
                $scoreRanges[$cat]['count'] = $count;
                $scoreRanges[$cat]['percentage'] = $totalSessions > 0
                    ? round(($count / $totalSessions) * 100, 2)
                    : 0;
            }
        }

        // Paginated submissions
        $limit = min($request->get('limit', 10), 100);
        $submissions = $query->orderBy('submitted_at', 'desc')
            ->paginate($limit);

        $contents = $submissions->getCollection()->map(function ($session) {
            return [
                'id' => $session->id,
                'respondent' => $session->user->name,
                'questionnaire' => $session->questionnaire->title,
                'score' => $session->result?->overall_score,
                'percentage' => $session->result?->overall_percentage,
                'category' => $session->result?->overall_category,
                'submittedAt' => $session->submitted_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Report retrieved successfully',
            'data' => [
                'summary' => [
                    'totalSessions' => $totalSessions,
                    'totalRespondents' => $totalRespondents,
                    'averageScore' => round($averageScoreRaw ?? 0, 2),
                    'averagePercentage' => round($averageScore ?? 0, 2),
                ],
                'categoryDistribution' => $scoreRanges,
                'contents' => $contents,
                'meta' => [
                    'page' => $submissions->currentPage(),
                    'limit' => $submissions->perPage(),
                    'total' => $submissions->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/reports/export-excel
     * Export evaluation results to Excel (CSV).
     * - Without sessionId: bulk summary list
     * - With sessionId: detail per indikator for that session
     */
    public function exportExcel(Request $request)
    {
        // Per-session detail export
        if ($request->has('sessionId') && $request->sessionId) {
            $session = ResponseSession::with([
                'user', 'questionnaire', 'result.details.indicator',
            ])->find($request->sessionId);

            if (!$session || $session->status !== 'submitted') {
                return $this->errorResponse('Session not found or not submitted', 404);
            }

            if (!$session->result) {
                return $this->errorResponse('Results not available', 404);
            }

            $result = $session->result;
            $details = $result->details;

            $filename = 'laporan-evaluasi-' . $session->id . '-' . now()->format('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($session, $result, $details) {
                $file = fopen('php://output', 'w');

                // Info responden
                fputcsv($file, ['Nama', $session->user->name]);
                fputcsv($file, ['Email', $session->user->email]);
                fputcsv($file, ['Kuesioner', $session->questionnaire->title]);
                fputcsv($file, ['Tanggal Isi Angket', $session->submitted_at->format('d/m/Y H:i')]);
                fputcsv($file, ['Persentase', $result->overall_percentage . '%']);
                fputcsv($file, []); // blank row

                // Header detail
                fputcsv($file, [
                    'No',
                    'Indikator',
                    'Persentase',
                ]);

                $no = 1;
                foreach ($details as $detail) {
                    fputcsv($file, [
                        $no++,
                        $detail->indicator->name ?? '-',
                        $detail->percentage . '%',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Bulk summary export
        $query = ResponseSession::with(['user', 'questionnaire', 'result'])
            ->where('status', 'submitted');

        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaire_id', $request->questionnaireId);
        }

        if ($request->has('periodId') && $request->periodId) {
            $query->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('evaluation_period_id', $request->periodId);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $sessions = $query->orderBy('submitted_at', 'desc')->get();

        if ($sessions->isEmpty()) {
            return $this->errorResponse('No data to export', 404);
        }

        $filename = 'laporan-evaluasi-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'No',
                'Nama Responden',
                'Email',
                'Kuesioner',
                'Persentase',
                'Tanggal Isi Angket',
            ]);

            $no = 1;
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $no++,
                    $session->user->name,
                    $session->user->email,
                    $session->questionnaire->title,
                    ($session->result?->overall_percentage ?? '-') . '%',
                    $session->submitted_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * POST /api/v1/admin/reports/export-pdf
     * Export single session report to PDF (HTML).
     */
    public function exportPdf(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'sessionId' => 'required|exists:response_sessions,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $session = ResponseSession::with([
            'user', 'questionnaire',
            'result.details.indicator',
        ])->find($request->sessionId);

        if ($session->status !== 'submitted') {
            return $this->errorResponse('Session not yet submitted', 422);
        }

        if (!$session->result) {
            return $this->errorResponse('Results not available', 404);
        }

        // Generate simple HTML report (can be converted to PDF with DomPDF)
        $html = $this->generatePdfHtml($session);

        $filename = 'laporan-evaluasi-' . $session->id . '-' . now()->format('Y-m-d') . '.html';

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate HTML content for PDF report.
     */
    private function generatePdfHtml($session): string
    {
        $user = $session->user;
        $questionnaire = $session->questionnaire;
        $result = $session->result;
        $details = $result->details;

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Evaluasi - ' . $user->name . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #006c49; padding-bottom: 20px; }
        .header h1 { color: #006c49; margin: 0; }
        .header p { color: #666; margin: 5px 0 0; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #006c49; border-bottom: 2px solid #006c49; padding-bottom: 8px; margin-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .info-item { padding: 8px; background: #f8f9fa; border-radius: 4px; }
        .info-item strong { color: #006c49; }
        .score-box { text-align: center; padding: 20px; background: #f0f9f4; border-radius: 8px; margin: 15px 0; }
        .score-box .score { font-size: 36px; font-weight: bold; color: #006c49; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #006c49; color: white; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Evaluasi Kebijakan Lingkungan Sekolah</h1>
        <p>Platform Evaluasi Kebijakan Lingkungan Sekolah</p>
    </div>

    <div class="section">
        <h3>Informasi Responden</h3>
        <div class="info-grid">
            <div class="info-item"><strong>Nama:</strong> ' . $user->name . '</div>
            <div class="info-item"><strong>Email:</strong> ' . $user->email . '</div>
            <div class="info-item"><strong>Kuesioner:</strong> ' . $questionnaire->title . '</div>
            <div class="info-item"><strong>Tanggal Isi Angket:</strong> ' . $session->submitted_at->format('d/m/Y H:i') . '</div>
        </div>
    </div>

    <div class="section">
        <h3>Hasil Evaluasi</h3>
        <div class="score-box">
            <div class="score">' . $result->overall_percentage . '%</div>
        </div>
    </div>

    <div class="section">
        <h3>Detail per Indikator</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Indikator</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>';

        $no = 1;
        foreach ($details as $detail) {
            $html .= '
                <tr>
                    <td>' . $no++ . '</td>
                    <td>' . ($detail->indicator->name ?? '-') . '</td>
                    <td>' . $detail->percentage . '%</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh Platform Evaluasi Kebijakan Lingkungan Sekolah</p>
        <p>Tanggal cetak: ' . now()->format('d/m/Y H:i') . '</p>
    </div>
</body>
</html>';

        return $html;
    }
}
