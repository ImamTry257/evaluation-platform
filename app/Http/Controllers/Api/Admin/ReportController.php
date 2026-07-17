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
     * Get report summary with statistics.
     */
    public function index(Request $request)
    {
        $query = ResponseSession::where('status', 'submitted')->with('result');

        // Filter by questionnaire
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaireId', $request->questionnaireId);
        }

        // Filter by period (via questionnaire)
        if ($request->has('periodId') && $request->periodId) {
            $query->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('evaluationPeriodId', $request->periodId);
            });
        }

        $sessions = $query->get();

        // Calculate statistics
        $totalSessions = $sessions->count();
        $totalRespondents = $sessions->pluck('userId')->unique()->count();
        $averageScore = $sessions->avg('result.overallPercentage');
        $averageScoreRaw = $sessions->avg('result.overallScore');

        // Category distribution
        $categoryDistribution = $sessions->pluck('result.overallCategory')
            ->filter()
            ->countBy()
            ->toArray();

        // Score ranges
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

        // Recent submissions
        $recentSubmissions = $sessions->sortByDesc('submittedAt')->take(5)->map(function ($session) {
            return [
                'id' => $session->id,
                'respondent' => $session->user->name,
                'questionnaire' => $session->questionnaire->title,
                'score' => $session->result?->overallScore,
                'percentage' => $session->result?->overallPercentage,
                'category' => $session->result?->overallCategory,
                'submittedAt' => $session->submittedAt,
            ];
        })->values();

        return $this->successResponse([
            'summary' => [
                'totalSessions' => $totalSessions,
                'totalRespondents' => $totalRespondents,
                'averageScore' => round($averageScoreRaw ?? 0, 2),
                'averagePercentage' => round($averageScore ?? 0, 2),
            ],
            'categoryDistribution' => $scoreRanges,
            'recentSubmissions' => $recentSubmissions,
        ], 'Report retrieved successfully');
    }

    /**
     * POST /api/v1/admin/reports/export-excel
     * Export evaluation results to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = ResponseSession::with(['user', 'questionnaire', 'result'])
            ->where('status', 'submitted');

        // Filter by questionnaire
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaireId', $request->questionnaireId);
        }

        // Filter by period
        if ($request->has('periodId') && $request->periodId) {
            $query->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('evaluationPeriodId', $request->periodId);
            });
        }

        $sessions = $query->orderBy('submittedAt', 'desc')->get();

        if ($sessions->isEmpty()) {
            return $this->errorResponse('No data to export', 404);
        }

        // Generate CSV content (compatible with Excel)
        $filename = 'laporan-evaluasi-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'No',
                'Nama Responden',
                'Email',
                'Kuesioner',
                'Skor',
                'Persentase',
                'Kategori',
                'Kesimpulan',
                'Tanggal Submit',
            ]);

            // Data
            $no = 1;
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $no++,
                    $session->user->name,
                    $session->user->email,
                    $session->questionnaire->title,
                    $session->result?->overallScore ?? '-',
                    ($session->result?->overallPercentage ?? '-') . '%',
                    $session->result?->overallCategory ?? '-',
                    $session->result?->conclusion ?? '-',
                    $session->submittedAt->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * POST /api/v1/admin/reports/export-pdf
     * Export single session report to PDF.
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
            'user',
            'questionnaire',
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
        .score-box .category { font-size: 24px; color: #666; }
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
            <div class="info-item"><strong>Tanggal Submit:</strong> ' . $session->submittedAt->format('d/m/Y H:i') . '</div>
        </div>
    </div>

    <div class="section">
        <h3>Hasil Evaluasi</h3>
        <div class="score-box">
            <div class="score">' . $result->overallPercentage . '%</div>
            <div class="category">Kategori: ' . $result->overallCategory . '</div>
        </div>
        <p><strong>Skor:</strong> ' . $result->overallScore . ' / 7.00</p>
        <p><strong>Kesimpulan:</strong> ' . $result->conclusion . '</p>
    </div>

    <div class="section">
        <h3>Detail per Indikator</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Indikator</th>
                    <th>Skor</th>
                    <th>Persentase</th>
                    <th>Kategori</th>
                    <th>Rekomendasi</th>
                </tr>
            </thead>
            <tbody>';

        $no = 1;
        foreach ($details as $detail) {
            $html .= '
                <tr>
                    <td>' . $no++ . '</td>
                    <td>' . ($detail->indicator->name ?? '-') . '</td>
                    <td>' . $detail->score . '</td>
                    <td>' . $detail->percentage . '%</td>
                    <td>' . $detail->category . '</td>
                    <td>' . ($detail->recommendation ?? '-') . '</td>
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
