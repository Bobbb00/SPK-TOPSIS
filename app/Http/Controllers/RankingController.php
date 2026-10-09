<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\ScholarshipQuota;
use App\Services\TopsisService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RankingController extends Controller
{
    public function __construct(
        protected TopsisService $topsisService,
    ) {}

    /**
     * Tampilkan hasil perangkingan TOPSIS beserta transparansi perhitungan.
     */
    public function index(Request $request): Response
    {
        $criterias = Criteria::query()->orderBy('code')->get();
        $applicants = Applicant::query()
            ->with(['evaluations' => fn ($q) => $q->select(['id', 'applicant_id', 'criteria_id', 'score'])])
            ->orderBy('nim')
            ->get();

        $quota = ScholarshipQuota::active();
        $quotaLimit = $quota?->quota_limit ?? 5;

        // Jalankan kalkulasi TOPSIS
        $rawResults = $this->topsisService->calculate($applicants, $criterias);
        $intermediates = $this->topsisService->getIntermediateMatrices($applicants, $criterias);

        // Tambahkan status kelulusan kuota
        $results = array_map(function (array $item) use ($quotaLimit) {
            $item['id'] = $item['applicant_id'];
            $item['is_passed'] = $item['rank'] <= $quotaLimit;
            return $item;
        }, $rawResults);

        // Filter di backend atau teruskan raw untuk reaktifitas
        $search = $request->string('search')->toString();
        $studyProgram = $request->string('study_program')->toString();
        $status = $request->string('status', 'all')->toString();

        $filteredResults = array_values(array_filter($results, function ($item) use ($search, $studyProgram, $status) {
            if ($search !== '') {
                $matchNim = str_contains(strtolower($item['nim']), strtolower($search));
                $matchName = str_contains(strtolower($item['name']), strtolower($search));
                if (! $matchNim && ! $matchName) return false;
            }

            if ($studyProgram !== '' && $item['study_program'] !== $studyProgram) {
                return false;
            }

            if ($status === 'passed' && ! $item['is_passed']) return false;
            if ($status === 'failed' && $item['is_passed']) return false;

            return true;
        }));

        $studyPrograms = collect($results)->pluck('study_program')->unique()->values()->all();

        return Inertia::render('Ranking/Index', [
            'results' => $filteredResults,
            'allResults' => $results,
            'criterias' => $criterias,
            'intermediates' => $intermediates,
            'quota' => [
                'quota_limit' => $quotaLimit,
                'period' => $quota?->period ?? '2026/2027',
            ],
            'studyPrograms' => $studyPrograms,
            'filters' => [
                'search' => $search,
                'study_program' => $studyProgram,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Unduh laporan hasil seleksi dalam format CSV / Excel.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $scope = $request->string('scope', 'all')->toString(); // 'all' atau 'passed'
        $criterias = Criteria::query()->orderBy('code')->get();
        $applicants = Applicant::query()->with('evaluations')->get();
        $quota = ScholarshipQuota::active();
        $quotaLimit = $quota?->quota_limit ?? 5;

        $results = $this->topsisService->calculate($applicants, $criterias);

        $filename = 'hasil_seleksi_topsis_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($results, $quotaLimit, $scope) {
            $handle = fopen('php://output', 'w');
            // Tambahkan BOM untuk UTF-8 compatibility di Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Peringkat', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Nilai Preferensi (V)', 'Jarak Solusi Positif (D+)', 'Jarak Solusi Negatif (D-)', 'Status Rekomendasi']);

            foreach ($results as $item) {
                $isPassed = $item['rank'] <= $quotaLimit;
                if ($scope === 'passed' && ! $isPassed) {
                    continue;
                }

                fputcsv($handle, [
                    $item['rank'],
                    $item['nim'],
                    $item['name'],
                    $item['study_program'],
                    number_format($item['score'], 4, '.', ''),
                    number_format($item['d_plus'], 4, '.', ''),
                    number_format($item['d_minus'], 4, '.', ''),
                    $isPassed ? 'LOLOS KUOTA' : 'TIDAK LOLOS',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Tampilan cetak resmi hasil seleksi beasiswa untuk print / save to PDF.
     */
    public function print(Request $request): Response
    {
        $scope = $request->string('scope', 'all')->toString();
        $criterias = Criteria::query()->orderBy('code')->get();
        $applicants = Applicant::query()->with('evaluations')->get();
        $quota = ScholarshipQuota::active();
        $quotaLimit = $quota?->quota_limit ?? 5;

        $results = $this->topsisService->calculate($applicants, $criterias);

        $finalList = [];
        foreach ($results as $item) {
            $isPassed = $item['rank'] <= $quotaLimit;
            if ($scope === 'passed' && ! $isPassed) {
                continue;
            }
            $item['is_passed'] = $isPassed;
            $finalList[] = $item;
        }

        return Inertia::render('Ranking/Print', [
            'results' => $finalList,
            'quota' => [
                'quota_limit' => $quotaLimit,
                'period' => $quota?->period ?? '2026/2027',
            ],
            'scope' => $scope,
            'totalApplicants' => count($results),
            'generatedAt' => date('d F Y'),
        ]);
    }
}
