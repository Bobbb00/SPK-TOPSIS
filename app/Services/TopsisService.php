<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Applicant;
use App\Models\Criteria;
use Illuminate\Support\Collection;

/**
 * TopsisService — Engine kalkulasi metode TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution).
 *
 * Alur perhitungan:
 *  1. Bangun matriks keputusan mentah dari data evaluasi.
 *  2. Normalisasi matriks (pembagian dengan norm kolom).
 *  3. Kalikan dengan bobot kriteria → matriks terbobot.
 *  4. Tentukan solusi ideal positif (A+) dan negatif (A-) per kriteria.
 *  5. Hitung jarak setiap alternatif ke A+ dan A-.
 *  6. Hitung skor preferensi Vi = D- / (D+ + D-).
 *  7. Urutkan dari Vi terbesar ke terkecil.
 */
class TopsisService
{
    /**
     * Jalankan seluruh kalkulasi TOPSIS dan kembalikan hasil terurut.
     *
     * @param  Collection<int, Applicant> $applicants  Koleksi pendaftar dengan relasi `evaluations` di-load.
     * @param  Collection<int, Criteria>  $criterias   Koleksi kriteria yang aktif.
     * @return array<int, array{
     *     applicant_id: int,
     *     nim: string,
     *     name: string,
     *     study_program: string,
     *     score: float,
     *     d_plus: float,
     *     d_minus: float,
     *     rank: int,
     * }>
     */
    public function calculate(Collection $applicants, Collection $criterias): array
    {
        if ($applicants->isEmpty() || $criterias->isEmpty()) {
            return [];
        }

        $criteriaIds = $criterias->pluck('id')->all();

        // -------------------------------------------------------------------------
        // Langkah 1: Bangun matriks keputusan mentah x[applicantId][criteriaId]
        // -------------------------------------------------------------------------
        /** @var array<int, array<int, float>> $rawMatrix */
        $rawMatrix = [];
        foreach ($applicants as $applicant) {
            $rawMatrix[$applicant->id] = [];
            foreach ($criteriaIds as $cId) {
                $evaluation = $applicant->evaluations->firstWhere('criteria_id', $cId);
                $rawMatrix[$applicant->id][$cId] = $evaluation?->score ?? 0.0;
            }
        }

        // -------------------------------------------------------------------------
        // Langkah 2: Normalisasi r_ij = x_ij / sqrt(sum(x_kj^2))
        // Antisipasi division by zero: jika norm = 0, seluruh nilai kolom = 0.
        // -------------------------------------------------------------------------
        /** @var array<int, float> $columnNorm  sqrt(sum(x^2)) per kriteria */
        $columnNorm = [];
        foreach ($criteriaIds as $cId) {
            $sumSq = 0.0;
            foreach ($applicants as $applicant) {
                $val   = $rawMatrix[$applicant->id][$cId];
                $sumSq += $val * $val;
            }
            $columnNorm[$cId] = $sumSq > 0.0 ? sqrt($sumSq) : 0.0;
        }

        /** @var array<int, array<int, float>> $normalMatrix */
        $normalMatrix = [];
        foreach ($applicants as $applicant) {
            $normalMatrix[$applicant->id] = [];
            foreach ($criteriaIds as $cId) {
                $norm = $columnNorm[$cId];
                $normalMatrix[$applicant->id][$cId] = $norm > 0.0
                    ? $rawMatrix[$applicant->id][$cId] / $norm
                    : 0.0;
            }
        }

        // -------------------------------------------------------------------------
        // Langkah 3: Matriks terbobot y_ij = w_j * r_ij
        // -------------------------------------------------------------------------
        /** @var array<int, float> $weights  bobot per criteria id */
        $weights = $criterias->pluck('weight', 'id')->map(fn ($w) => (float) $w)->all();

        /** @var array<int, array<int, float>> $weightedMatrix */
        $weightedMatrix = [];
        foreach ($applicants as $applicant) {
            $weightedMatrix[$applicant->id] = [];
            foreach ($criteriaIds as $cId) {
                $weightedMatrix[$applicant->id][$cId] = $weights[$cId] * $normalMatrix[$applicant->id][$cId];
            }
        }

        // -------------------------------------------------------------------------
        // Langkah 4: Solusi ideal positif (A+) dan negatif (A-)
        // Benefit → A+ = max, A- = min
        // Cost    → A+ = min, A- = max
        // -------------------------------------------------------------------------
        $criteriaMap = $criterias->keyBy('id');

        /** @var array<int, float> $idealPositive  A+ per criteria id */
        $idealPositive = [];
        /** @var array<int, float> $idealNegative  A- per criteria id */
        $idealNegative = [];

        foreach ($criteriaIds as $cId) {
            $colValues = array_column($weightedMatrix, $cId);

            /** @var Criteria $criteria */
            $criteria = $criteriaMap[$cId];

            if ($criteria->isBenefit()) {
                $idealPositive[$cId] = max($colValues);
                $idealNegative[$cId] = min($colValues);
            } else {
                // cost: nilai kecil lebih baik
                $idealPositive[$cId] = min($colValues);
                $idealNegative[$cId] = max($colValues);
            }
        }

        // -------------------------------------------------------------------------
        // Langkah 5 & 6: Jarak D+ dan D-, lalu skor Vi
        // D+_i = sqrt(sum((y_ij - A+_j)^2))
        // D-_i = sqrt(sum((y_ij - A-_j)^2))
        // Vi   = D-_i / (D+_i + D-_i)   — antisipasi denominator = 0 → Vi = 0
        // -------------------------------------------------------------------------
        $results = [];
        foreach ($applicants as $applicant) {
            $sumPlus  = 0.0;
            $sumMinus = 0.0;

            foreach ($criteriaIds as $cId) {
                $y = $weightedMatrix[$applicant->id][$cId];

                $diffPlus  = $y - $idealPositive[$cId];
                $diffMinus = $y - $idealNegative[$cId];

                $sumPlus  += $diffPlus * $diffPlus;
                $sumMinus += $diffMinus * $diffMinus;
            }

            $dPlus  = sqrt($sumPlus);
            $dMinus = sqrt($sumMinus);

            $denominator = $dPlus + $dMinus;
            $score       = $denominator > 0.0 ? $dMinus / $denominator : 0.0;

            $results[] = [
                'applicant_id'  => $applicant->id,
                'nim'           => $applicant->nim,
                'name'          => $applicant->name,
                'study_program' => $applicant->study_program,
                'score'         => round($score, 4),
                'd_plus'        => round($dPlus, 4),
                'd_minus'       => round($dMinus, 4),
                // rank ditambahkan setelah sorting
                'rank'          => 0,
            ];
        }

        // -------------------------------------------------------------------------
        // Langkah 7: Urutkan dari skor terbesar, lalu tambahkan nomor rank
        // -------------------------------------------------------------------------
        usort($results, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        foreach ($results as $i => &$result) {
            $result['rank'] = $i + 1;
        }
        unset($result);

        return $results;
    }

    /**
     * Kembalikan detail intermediate matrices untuk keperluan transparansi.
     *
     * @param  Collection<int, Applicant> $applicants
     * @param  Collection<int, Criteria>  $criterias
     * @return array{
     *     raw_matrix: array<int, array<int, float>>,
     *     normal_matrix: array<int, array<int, float>>,
     *     weighted_matrix: array<int, array<int, float>>,
     *     ideal_positive: array<int, float>,
     *     ideal_negative: array<int, float>,
     * }
     */
    public function getIntermediateMatrices(Collection $applicants, Collection $criterias): array
    {
        $criteriaIds = $criterias->pluck('id')->all();

        // Raw matrix
        $rawMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $evaluation = $applicant->evaluations->firstWhere('criteria_id', $cId);
                $rawMatrix[$applicant->id][$cId] = $evaluation?->score ?? 0.0;
            }
        }

        // Column norms
        $columnNorm = [];
        foreach ($criteriaIds as $cId) {
            $sumSq = 0.0;
            foreach ($applicants as $applicant) {
                $val   = $rawMatrix[$applicant->id][$cId];
                $sumSq += $val * $val;
            }
            $columnNorm[$cId] = $sumSq > 0.0 ? sqrt($sumSq) : 0.0;
        }

        // Normal matrix
        $normalMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $norm = $columnNorm[$cId];
                $normalMatrix[$applicant->id][$cId] = $norm > 0.0
                    ? round($rawMatrix[$applicant->id][$cId] / $norm, 4)
                    : 0.0;
            }
        }

        // Weighted matrix
        $weights = $criterias->pluck('weight', 'id')->map(fn ($w) => (float) $w)->all();
        $weightedMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $weightedMatrix[$applicant->id][$cId] = round(
                    $weights[$cId] * ($normalMatrix[$applicant->id][$cId]),
                    4
                );
            }
        }

        // Ideal solutions
        $criteriaMap   = $criterias->keyBy('id');
        $idealPositive = [];
        $idealNegative = [];
        foreach ($criteriaIds as $cId) {
            $colValues = array_column($weightedMatrix, $cId);
            /** @var Criteria $criteria */
            $criteria = $criteriaMap[$cId];
            if ($criteria->isBenefit()) {
                $idealPositive[$cId] = round(max($colValues), 4);
                $idealNegative[$cId] = round(min($colValues), 4);
            } else {
                $idealPositive[$cId] = round(min($colValues), 4);
                $idealNegative[$cId] = round(max($colValues), 4);
            }
        }

        return [
            'raw_matrix'      => $rawMatrix,
            'normal_matrix'   => $normalMatrix,
            'weighted_matrix' => $weightedMatrix,
            'ideal_positive'  => $idealPositive,
            'ideal_negative'  => $idealNegative,
        ];
    }
}
