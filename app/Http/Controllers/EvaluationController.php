<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Evaluation\UpdateEvaluationRequest;
use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Services\AiExplanationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationController extends Controller
{
    /*
     * Tampilkan matriks evaluasi keputusan (Matriks X_ij).
     */
    public function index(): Response
    {
        $criterias = Criteria::query()->orderBy("code")->get();
        $criteriasCount = $criterias->count();
        $totalWeight = (float) $criterias->sum("weight");

        $applicants = Applicant::query()
            ->with([
                "evaluations" => fn($q) => $q->select([
                    "id",
                    "applicant_id",
                    "criteria_id",
                    "score",
                ]),
            ])
            ->orderBy("nim")
            ->get()
            ->map(function ($applicant) use ($criteriasCount) {
                $evalMap = [];
                foreach ($applicant->evaluations as $eval) {
                    $evalMap[$eval->criteria_id] = (float) $eval->score;
                }

                $evalCount = count($evalMap);

                return [
                    "id" => $applicant->id,
                    "nim" => $applicant->nim,
                    "name" => $applicant->name,
                    "study_program" => $applicant->study_program,
                    "scores" => $evalMap,
                    "evaluated_count" => $evalCount,
                    "is_complete" =>
                        $criteriasCount > 0 && $evalCount === $criteriasCount,
                ];
            });

        $totalApplicants = $applicants->count();
        $completeCount = $applicants->where("is_complete", true)->count();
        $isReady =
            $totalApplicants > 0 &&
            $criteriasCount > 0 &&
            $completeCount === $totalApplicants &&
            abs($totalWeight - 1.0) < 0.001;

        return Inertia::render("Evaluations/Index", [
            "criterias" => $criterias,
            "applicants" => $applicants,
            "totalWeight" => round($totalWeight, 4),
            "summary" => [
                "total_applicants" => $totalApplicants,
                "complete_count" => $completeCount,
                "incomplete_count" => $totalApplicants - $completeCount,
                "is_ready_for_calculation" => $isReady,
            ],
        ]);
    }

    /**
     * Simpan atau perbarui nilai evaluasi satu pendaftar.
     */
    public function update(
        UpdateEvaluationRequest $request,
        Applicant $applicant,
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($applicant, $validated) {
            foreach ($validated["scores"] as $item) {
                Evaluation::updateOrCreate(
                    [
                        "applicant_id" => $applicant->id,
                        "criteria_id" => $item["criteria_id"],
                    ],
                    [
                        "score" => $item["score"],
                    ],
                );
            }
        });

        AiExplanationService::flushCache();

        return back()->with(
            "success",
            "Nilai evaluasi mahasiswa {$applicant->name} ({$applicant->nim}) berhasil diperbarui.",
        );
    }
}
