<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\ScholarshipQuota;
use App\Services\TopsisService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected TopsisService $topsisService,
    ) {}

    public function __invoke(): Response
    {
        $allCriteria = Criteria::query()->orderBy('code')->get();
        $criteriaCount = $allCriteria->count();
        $totalWeight = (float) $allCriteria->sum('weight');
        $benefitCount = $allCriteria->where('type', 'benefit')->count();
        $costCount = $allCriteria->where('type', 'cost')->count();

        $applicantCount = Applicant::count();
        $quota = ScholarshipQuota::active();

        // Hitung pendaftar yang memiliki nilai lengkap
        $completeEvaluatedCount = 0;
        if ($criteriaCount > 0) {
            $completeEvaluatedCount = Evaluation::query()
                ->select('applicant_id')
                ->groupBy('applicant_id')
                ->havingRaw('COUNT(DISTINCT criteria_id) = ?', [$criteriaCount])
                ->get()
                ->count();
        }

        $isReadyForCalculation = $criteriaCount > 0 
            && abs($totalWeight - 1.0) < 0.001 
            && $applicantCount > 0 
            && $completeEvaluatedCount === $applicantCount;

        $topRankings = [];
        if ($isReadyForCalculation) {
            $applicants = Applicant::query()
                ->with(['evaluations' => fn ($q) => $q->select(['id', 'applicant_id', 'criteria_id', 'score'])])
                ->get();
            $rawResults = $this->topsisService->calculate($applicants, $allCriteria);
            $quotaLimit = $quota?->quota_limit ?? 5;

            $topRankings = array_slice(array_map(function (array $item) use ($quotaLimit) {
                return [
                    'id' => $item['applicant_id'],
                    'applicant_id' => $item['applicant_id'],
                    'rank' => $item['rank'],
                    'nim' => $item['nim'],
                    'name' => $item['name'],
                    'study_program' => $item['study_program'],
                    'preference_score' => $item['score'],
                    'score' => $item['score'],
                    'is_passed' => $item['rank'] <= $quotaLimit,
                ];
            }, $rawResults), 0, 5);
        }

        $criteriaList = $allCriteria->map(fn (Criteria $c) => [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'type' => $c->type,
            'weight' => (float) $c->weight,
            'percentage' => round(((float) $c->weight) * 100, 1),
        ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'criteria_count' => $criteriaCount,
                'benefit_count' => $benefitCount,
                'cost_count' => $costCount,
                'total_weight' => round($totalWeight, 4),
                'applicant_count' => $applicantCount,
                'complete_evaluated_count' => $completeEvaluatedCount,
                'quota' => $quota ? [
                    'quota_limit' => $quota->quota_limit,
                    'period' => $quota->period,
                ] : null,
                'is_ready_for_calculation' => $isReadyForCalculation,
            ],
            'top_rankings' => $topRankings,
            'criterias' => $criteriaList,
        ]);
    }
}
