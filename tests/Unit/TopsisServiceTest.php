<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Services\TopsisService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * TopsisServiceTest — Validasi keakuratan matematis engine TOPSIS.
 *
 * Data acuan perhitungan manual:
 * ─────────────────────────────────────────────────────────────────
 *  Kriteria: C1 (benefit, w=0.4), C2 (cost, w=0.3), C3 (benefit, w=0.3)
 *
 *  Pendaftar │  C1  │  C2  │  C3
 *  ──────────┼──────┼──────┼──────
 *  A1        │  80  │  30  │  70
 *  A2        │  90  │  50  │  60
 *  A3        │  70  │  20  │  80
 *
 *  Norm C1 = sqrt(80^2 + 90^2 + 70^2) = sqrt(6400+8100+4900) = sqrt(19400) ≈ 139.2839
 *  Norm C2 = sqrt(30^2 + 50^2 + 20^2) = sqrt(900+2500+400)  = sqrt(3800)  ≈ 61.6441
 *  Norm C3 = sqrt(70^2 + 60^2 + 80^2) = sqrt(4900+3600+6400)= sqrt(14900) ≈ 122.0656
 * ─────────────────────────────────────────────────────────────────
 */
class TopsisServiceTest extends TestCase
{
    private TopsisService $service;

    /** @var Collection<int, Criteria> */
    private Collection $criterias;

    /** @var Collection<int, Applicant> */
    private Collection $applicants;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TopsisService();

        // Buat objek Criteria tanpa DB (plain object)
        $c1 = new Criteria(['id' => 1, 'code' => 'C1', 'name' => 'IPK',        'weight' => 0.4, 'type' => 'benefit']);
        $c2 = new Criteria(['id' => 2, 'code' => 'C2', 'name' => 'Penghasilan', 'weight' => 0.3, 'type' => 'cost']);
        $c3 = new Criteria(['id' => 3, 'code' => 'C3', 'name' => 'Prestasi',   'weight' => 0.3, 'type' => 'benefit']);

        // Paksa ID agar tidak null
        $c1->id = 1;
        $c2->id = 2;
        $c3->id = 3;

        $this->criterias = collect([$c1, $c2, $c3]);

        // Buat objek Applicant dengan evaluasi masing-masing
        $this->applicants = collect([
            $this->makeApplicant(1, 'A001', 'Andi', 'Informatika', [1 => 80.0, 2 => 30.0, 3 => 70.0]),
            $this->makeApplicant(2, 'A002', 'Budi', 'Sistem Informasi', [1 => 90.0, 2 => 50.0, 3 => 60.0]),
            $this->makeApplicant(3, 'A003', 'Cici', 'Teknik Komputer', [1 => 70.0, 2 => 20.0, 3 => 80.0]),
        ]);
    }

    // =========================================================================
    // Helper
    // =========================================================================

    /**
     * @param array<int, float> $scores  [criteria_id => score]
     */
    private function makeApplicant(int $id, string $nim, string $name, string $prodi, array $scores): Applicant
    {
        $applicant = new Applicant(['nim' => $nim, 'name' => $name, 'study_program' => $prodi]);
        $applicant->id = $id;

        $evaluations = collect();
        foreach ($scores as $criteriaId => $score) {
            $eval = new Evaluation(['applicant_id' => $id, 'criteria_id' => $criteriaId, 'score' => $score]);
            $eval->criteria_id = $criteriaId;
            $eval->score       = $score;
            $evaluations->push($eval);
        }

        // Set relasi tanpa DB menggunakan setRelation
        $applicant->setRelation('evaluations', $evaluations);

        return $applicant;
    }

    // =========================================================================
    // Test: Struktur output
    // =========================================================================

    public function test_calculate_returns_correct_structure(): void
    {
        $results = $this->service->calculate($this->applicants, $this->criterias);

        $this->assertCount(3, $results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('applicant_id', $result);
            $this->assertArrayHasKey('nim', $result);
            $this->assertArrayHasKey('name', $result);
            $this->assertArrayHasKey('study_program', $result);
            $this->assertArrayHasKey('score', $result);
            $this->assertArrayHasKey('d_plus', $result);
            $this->assertArrayHasKey('d_minus', $result);
            $this->assertArrayHasKey('rank', $result);
        }
    }

    // =========================================================================
    // Test: Skor antara 0 dan 1
    // =========================================================================

    public function test_scores_are_between_zero_and_one(): void
    {
        $results = $this->service->calculate($this->applicants, $this->criterias);

        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual(0.0, $result['score'], 'Skor harus >= 0');
            $this->assertLessThanOrEqual(1.0, $result['score'], 'Skor harus <= 1');
        }
    }

    // =========================================================================
    // Test: Urutan rank
    // =========================================================================

    public function test_results_are_sorted_by_score_descending(): void
    {
        $results = $this->service->calculate($this->applicants, $this->criterias);

        for ($i = 0; $i < count($results) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $results[$i + 1]['score'],
                $results[$i]['score'],
                'Hasil harus diurutkan dari skor terbesar ke terkecil'
            );
        }
    }

    public function test_rank_numbers_are_sequential_from_one(): void
    {
        $results = $this->service->calculate($this->applicants, $this->criterias);

        foreach ($results as $i => $result) {
            $this->assertSame($i + 1, $result['rank'], "Rank ke-{$result['rank']} harus = " . ($i + 1));
        }
    }

    // =========================================================================
    // Test: Akurasi matematis (dibandingkan perhitungan manual, toleransi 0.0001)
    // =========================================================================

    public function test_normalization_column_norms_are_correct(): void
    {
        // Verifikasi via intermediate matrices
        $matrices = $this->service->getIntermediateMatrices($this->applicants, $this->criterias);

        // Verifikasi normalisasi A1/C1: 80 / sqrt(19400) ≈ 0.5743
        $expectedNormA1C1 = 80.0 / sqrt(19400);
        $this->assertEqualsWithDelta(
            round($expectedNormA1C1, 4),
            $matrices['normal_matrix'][1][1],
            0.0001,
            'Normalisasi A1 C1 tidak akurat'
        );

        // A2/C2: 50 / sqrt(3800) ≈ 0.8111
        $expectedNormA2C2 = 50.0 / sqrt(3800);
        $this->assertEqualsWithDelta(
            round($expectedNormA2C2, 4),
            $matrices['normal_matrix'][2][2],
            0.0001,
            'Normalisasi A2 C2 tidak akurat'
        );

        // A3/C3: 80 / sqrt(14900) ≈ 0.6554
        $expectedNormA3C3 = 80.0 / sqrt(14900);
        $this->assertEqualsWithDelta(
            round($expectedNormA3C3, 4),
            $matrices['normal_matrix'][3][3],
            0.0001,
            'Normalisasi A3 C3 tidak akurat'
        );
    }

    public function test_weighted_matrix_values_are_correct(): void
    {
        $matrices = $this->service->getIntermediateMatrices($this->applicants, $this->criterias);

        // normal_matrix is rounded to 4 decimals in getIntermediateMatrices
        $normA1C1 = round(80.0 / sqrt(19400), 4);
        $expected = $normA1C1 * 0.4;
        
        $this->assertEqualsWithDelta(
            round($expected, 4),
            $matrices['weighted_matrix'][1][1],
            0.0001,
            'Matriks terbobot A1 C1 tidak akurat'
        );
    }

    public function test_ideal_positive_benefit_is_max_and_cost_is_min(): void
    {
        $matrices = $this->service->getIntermediateMatrices($this->applicants, $this->criterias);

        // C1 (benefit): A+ = max weighted value
        $c1Values     = array_column($matrices['weighted_matrix'], 1);
        $expectedAPlC1 = max($c1Values);
        $this->assertEqualsWithDelta(
            round($expectedAPlC1, 4),
            $matrices['ideal_positive'][1],
            0.0001,
            'Ideal positif C1 (benefit) harus = max'
        );

        // C2 (cost): A+ = min weighted value
        $c2Values     = array_column($matrices['weighted_matrix'], 2);
        $expectedAPlC2 = min($c2Values);
        $this->assertEqualsWithDelta(
            round($expectedAPlC2, 4),
            $matrices['ideal_positive'][2],
            0.0001,
            'Ideal positif C2 (cost) harus = min'
        );
    }

    public function test_ideal_negative_benefit_is_min_and_cost_is_max(): void
    {
        $matrices = $this->service->getIntermediateMatrices($this->applicants, $this->criterias);

        // C1 (benefit): A- = min
        $c1Values      = array_column($matrices['weighted_matrix'], 1);
        $expectedAMiC1 = min($c1Values);
        $this->assertEqualsWithDelta(
            round($expectedAMiC1, 4),
            $matrices['ideal_negative'][1],
            0.0001,
            'Ideal negatif C1 (benefit) harus = min'
        );

        // C2 (cost): A- = max
        $c2Values      = array_column($matrices['weighted_matrix'], 2);
        $expectedAMiC2 = max($c2Values);
        $this->assertEqualsWithDelta(
            round($expectedAMiC2, 4),
            $matrices['ideal_negative'][2],
            0.0001,
            'Ideal negatif C2 (cost) harus = max'
        );
    }

    // =========================================================================
    // Test: Edge cases (division by zero protection)
    // =========================================================================

    public function test_returns_empty_array_when_applicants_are_empty(): void
    {
        $results = $this->service->calculate(collect(), $this->criterias);
        $this->assertSame([], $results);
    }

    public function test_returns_empty_array_when_criterias_are_empty(): void
    {
        $results = $this->service->calculate($this->applicants, collect());
        $this->assertSame([], $results);
    }

    public function test_handles_zero_score_column_without_division_error(): void
    {
        // Semua nilai kolom C1 = 0 → norm = 0, harus tidak throw exception
        $applicants = collect([
            $this->makeApplicant(1, 'Z001', 'Zero A', 'Prodi A', [1 => 0.0, 2 => 10.0, 3 => 20.0]),
            $this->makeApplicant(2, 'Z002', 'Zero B', 'Prodi B', [1 => 0.0, 2 => 20.0, 3 => 10.0]),
        ]);

        $results = $this->service->calculate($applicants, $this->criterias);

        // Tidak ada exception, dan skor tetap antara 0-1
        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual(0.0, $result['score']);
            $this->assertLessThanOrEqual(1.0, $result['score']);
        }
    }

    public function test_handles_identical_scores_without_division_error(): void
    {
        // Semua nilai sama → D+ + D- = 0 → Vi harus = 0, bukan NaN atau exception
        $applicants = collect([
            $this->makeApplicant(1, 'S001', 'Same A', 'Prodi A', [1 => 50.0, 2 => 50.0, 3 => 50.0]),
            $this->makeApplicant(2, 'S002', 'Same B', 'Prodi B', [1 => 50.0, 2 => 50.0, 3 => 50.0]),
        ]);

        $results = $this->service->calculate($applicants, $this->criterias);

        foreach ($results as $result) {
            $this->assertSame(0.0, $result['score'], 'Skor identik harus menghasilkan Vi = 0');
        }
    }

    // =========================================================================
    // Test: Single applicant
    // =========================================================================

    public function test_single_applicant_has_rank_one(): void
    {
        $single = collect([$this->applicants->first()]);
        $results = $this->service->calculate($single, $this->criterias);

        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]['rank']);
    }
}
