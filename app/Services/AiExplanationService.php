<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\ScholarshipQuota;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiExplanationService
{
    public const CACHE_TAG = 'ai_explanations';
    public const CACHE_TTL_SECONDS = 86400; // 24 jam

    public function __construct(
        protected TopsisService $topsisService,
    ) {}

    /**
     * Hasilkan penjelasan berbasis AI / Algoritma untuk kelulusan pelamar.
     * Hasil disimpan di Cache (Redis) selama 24 jam untuk respon instan (< 5ms).
     *
     * @return array{
     *     applicant: array{id: int, nim: string, name: string, study_program: string, rank: int, score: float, is_passed: bool},
     *     quota_limit: int,
     *     explanation: string,
     *     key_factors: array<int, string>,
     *     strengths: array<int, string>,
     *     engine: string
     * }
     */
    public function explain(Applicant $targetApplicant, bool $forceFresh = false): array
    {
        $cacheKey = "ai_explanation:applicant_{$targetApplicant->id}";
        $cacheStore = Cache::supportsTags() ? Cache::tags([self::CACHE_TAG]) : Cache::store();

        if ($forceFresh) {
            $cacheStore->forget($cacheKey);
        }

        return $cacheStore->remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($targetApplicant) {
            return $this->generateExplanation($targetApplicant);
        });
    }

    /**
     * Kosongkan seluruh cache penjelasan AI (otomatis dipanggil saat nilai, kriteria, kuota, atau pendaftar berubah).
     */
    public static function flushCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->flush();
        } else {
            Cache::flush();
        }
    }

    /**
     * Hitung hasil TOPSIS dan generate penjelasan naratif keputusan beasiswa.
     *
     * @return array{
     *     applicant: array{id: int, nim: string, name: string, study_program: string, rank: int, score: float, is_passed: bool},
     *     quota_limit: int,
     *     explanation: string,
     *     key_factors: array<int, string>,
     *     strengths: array<int, string>,
     *     engine: string
     * }
     */
    protected function generateExplanation(Applicant $targetApplicant): array
    {
        $criterias = Criteria::query()->orderBy('code')->get();
        $allApplicants = Applicant::query()
            ->with(['evaluations' => fn ($q) => $q->select(['id', 'applicant_id', 'criteria_id', 'score'])])
            ->get();

        $quota = ScholarshipQuota::active();
        $quotaLimit = $quota?->quota_limit ?? 5;

        // Hitung hasil TOPSIS lengkap
        $results = $this->topsisService->calculate($allApplicants, $criterias);
        $targetResult = null;

        foreach ($results as $res) {
            if ($res['applicant_id'] === $targetApplicant->id) {
                $targetResult = $res;
                break;
            }
        }

        if (! $targetResult) {
            return [
                'applicant' => [
                    'id' => $targetApplicant->id,
                    'nim' => $targetApplicant->nim,
                    'name' => $targetApplicant->name,
                    'study_program' => $targetApplicant->study_program,
                    'rank' => 0,
                    'score' => 0.0,
                    'is_passed' => false,
                ],
                'quota_limit' => $quotaLimit,
                'explanation' => 'Data penilaian pendaftar belum lengkap untuk dapat dianalisis.',
                'key_factors' => [],
                'strengths' => [],
                'engine' => 'none',
            ];
        }

        $isPassed = $targetResult['rank'] <= $quotaLimit;
        $evaluationsMap = $targetApplicant->evaluations->keyBy('criteria_id');

        // Ringkasan nilai kriteria untuk applicant
        $criteriaDetails = [];
        $keyFactors = [];
        $strengths = [];

        foreach ($criterias as $c) {
            $scoreVal = (float) ($evaluationsMap[$c->id]?->score ?? 0);
            $criteriaDetails[] = [
                'code' => $c->code,
                'name' => $c->name,
                'type' => $c->type,
                'weight' => (float) $c->weight,
                'score' => $scoreVal,
            ];

            if ($c->isBenefit() && $scoreVal >= 3.0) {
                $strengths[] = "Keunggulan pada {$c->name} ({$c->code}) dengan capaian nilai {$scoreVal}.";
            } elseif (! $c->isBenefit() && $scoreVal > 0) {
                $strengths[] = "Kondisi ekonomi ({$c->name}) memenuhi kriteria prioritas bantuan biaya pendidikan.";
            }
        }

        $scorePercent = round($targetResult['score'] * 100, 1);
        if ($isPassed) {
            $keyFactors[] = "Masuk Kuota Utama: Meraih Peringkat {$targetResult['rank']} dari kuota {$quotaLimit} orang dengan tingkat kecocokan {$scorePercent}%.";
            if (! empty($strengths)) {
                $keyFactors[] = $strengths[0];
            } else {
                $keyFactors[] = "Kombinasi capaian prestasi akademik dan kriteria penunjang sangat kompetitif.";
            }
            $keyFactors[] = "Profil mahasiswa secara keseluruhan sangat mendekati standar kualifikasi penerima beasiswa terbaik.";
        } else {
            $keyFactors[] = "Status Cadangan: Berada di Peringkat {$targetResult['rank']} (batas kuota penerima utama adalah {$quotaLimit} orang).";
            $keyFactors[] = "Tingkat persaingan sangat ketat: Skor kelayakan ({$scorePercent}%) berada sedikit di bawah ambang batas kuota utama.";
            $keyFactors[] = "Masih memiliki peluang dipromosikan jika ada pendaftar di kuota utama yang mengundurkan diri.";
        }

        // Coba panggil Groq atau Gemini AI jika API key tersedia di .env
        $groqKey = config('services.groq.key', env('GROQ_API_KEY'));
        $geminiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $preferredProvider = config('services.ai_provider', env('AI_PROVIDER', 'groq'));

        $aiExplanation = null;
        $engineName = 'Smart Decision Engine';

        // Coba provider yang diinginkan terlebih dahulu
        if ($preferredProvider === 'groq' && ! empty($groqKey)) {
            $aiExplanation = $this->callGroqApi($targetApplicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails, (string) $groqKey);
            if ($aiExplanation) {
                $engineName = 'Groq Cloud AI';
            }
        } elseif (! empty($geminiKey)) {
            $aiExplanation = $this->callGeminiApi($targetApplicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails, (string) $geminiKey);
            if ($aiExplanation) {
                $engineName = 'Google Gemini AI';
            }
        }

        // Fallback antar-provider
        if (! $aiExplanation && ! empty($groqKey)) {
            $aiExplanation = $this->callGroqApi($targetApplicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails, (string) $groqKey);
            if ($aiExplanation) {
                $engineName = 'Groq Cloud AI';
            }
        } elseif (! $aiExplanation && ! empty($geminiKey)) {
            $aiExplanation = $this->callGeminiApi($targetApplicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails, (string) $geminiKey);
            if ($aiExplanation) {
                $engineName = 'Google Gemini AI';
            }
        }

        // Jika tidak ada API key atau request gagal, gunakan Rule-based Heuristic Generator
        if (! $aiExplanation) {
            $aiExplanation = $this->generateHeuristicExplanation($targetApplicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails);
        }

        return [
            'applicant' => [
                'id' => $targetApplicant->id,
                'nim' => $targetApplicant->nim,
                'name' => $targetApplicant->name,
                'study_program' => $targetApplicant->study_program,
                'rank' => $targetResult['rank'],
                'score' => $targetResult['score'],
                'd_plus' => $targetResult['d_plus'],
                'd_minus' => $targetResult['d_minus'],
                'is_passed' => $isPassed,
            ],
            'quota_limit' => $quotaLimit,
            'explanation' => $aiExplanation,
            'key_factors' => array_slice($keyFactors, 0, 3),
            'strengths' => array_slice($strengths, 0, 3),
            'engine' => $engineName,
        ];
    }

    /**
     * Generator narasi analisis heuristik TOPSIS (bahasa ramah awam).
     *
     * @param array<string, mixed> $targetResult
     * @param array<int, array<string, mixed>> $criteriaDetails
     */
    protected function generateHeuristicExplanation(
        Applicant $applicant,
        array $targetResult,
        bool $isPassed,
        int $quotaLimit,
        array $criteriaDetails,
    ): string {
        $scorePercent = round($targetResult['score'] * 100, 1);

        $criteriaHighlights = [];
        foreach ($criteriaDetails as $cd) {
            $typeDesc = $cd['type'] === 'benefit' ? 'capaian akademik/prestasi' : 'kondisi kebutuhan finansial';
            $criteriaHighlights[] = "- **{$cd['name']} ({$cd['code']})**: Nilai {$cd['score']} ({$typeDesc}).";
        }
        $criteriaListStr = implode("\n", $criteriaHighlights);

        if ($isPassed) {
            return "**Ringkasan Keputusan**\n" .
                "Selamat, mahasiswa **{$applicant->name}** (NIM: {$applicant->nim}) dinyatakan **LOLOS** menerima beasiswa pada **Peringkat ke-{$targetResult['rank']}** dari batas kuota {$quotaLimit} penerima. Tingkat kecocokan kualifikasi mencapai **{$scorePercent}%**.\n\n" .
                "**Faktor Utama Penentu**\n" .
                "Keberhasilan ini didukung oleh capaian yang sangat baik pada kriteria penilaian utama:\n{$criteriaListStr}\n" .
                "Kombinasi antara prestasi belajar yang unggul dan pemenuhan syarat prioritas menjadikan profil mahasiswa ini sangat layak diprioritaskan.\n\n" .
                "**Kesimpulan**\n" .
                "Keputusan ini sah, transparan, dan objektif berdasarkan hasil kalkulasi sistem pendukung keputusan. Mahasiswa berhak melangkah ke tahap pengesahan akhir penerima beasiswa.";
        }

        return "**Ringkasan Keputusan**\n" .
            "Mahasiswa **{$applicant->name}** (NIM: {$applicant->nim}) saat ini berada pada status **CADANGAN** di **Peringkat ke-{$targetResult['rank']}**. Kuota penerima beasiswa utama saat ini dibatasi untuk {$quotaLimit} orang teratas.\n\n" .
            "**Faktor Penentu & Catatan Evaluasi**\n" .
            "Mahasiswa memiliki skor kelayakan sebesar **{$scorePercent}%**. Rincian nilai yang tercatat:\n{$criteriaListStr}\n" .
            "Meskipun kualifikasinya sudah baik, persaingan seleksi periode ini sangat ketat sehingga nilai kandidat berada sedikit di luar batas kuota utama.\n\n" .
            "**Saran & Kesimpulan**\n" .
            "Mahasiswa tetap terdaftar sebagai kandidat cadangan prioritas. Apabila ada penerima utama yang mengundurkan diri atau gugur verifikasi, posisi mahasiswa ini dapat langsung dipromosikan.";
    }

    /**
     * Buat prompt standar analisis keputusan beasiswa dalam bahasa ramah awam.
     *
     * @param array<string, mixed> $targetResult
     * @param array<int, array<string, mixed>> $criteriaDetails
     */
    protected function buildPrompt(
        Applicant $applicant,
        array $targetResult,
        bool $isPassed,
        int $quotaLimit,
        array $criteriaDetails,
    ): string {
        $scorePercent = round($targetResult['score'] * 100, 1);
        $criteriaSummary = [];
        foreach ($criteriaDetails as $cd) {
            $criteriaSummary[] = "- {$cd['name']} ({$cd['code']}): Nilai {$cd['score']} (Bobot " . ($cd['weight'] * 100) . "%)";
        }
        $criteriaText = implode("\n", $criteriaSummary);

        return "Anda adalah konselor beasiswa. Jelaskan hasil seleksi kepada mahasiswa, orang tua, dan masyarakat umum dengan bahasa Indonesia yang ramah, sopan, komunikatif, dan SANGAT MUDAH DIPAHAMI TANPA ISTILAH MATEMATIKA RUMIT.\n\n" .
            "Data Mahasiswa:\n" .
            "- Nama: {$applicant->name}\n" .
            "- Program Studi: {$applicant->study_program}\n" .
            "- Status: " . ($isPassed ? "LOLOS BEASISWA (Peringkat {$targetResult['rank']} dari kuota {$quotaLimit} orang)" : "BELUM LOLOS / CADANGAN (Peringkat {$targetResult['rank']}, kuota {$quotaLimit} orang)") . "\n" .
            "- Skor Kelayakan Akhir: {$scorePercent}%\n" .
            "- Rincian Kriteria Mahasiswa:\n{$criteriaText}\n\n" .
            "PANDUAN PENULISAN WAJIB:\n" .
            "1. HINDARI SEMUA ISTILAH TEKNIS MATEMATIKA: JANGAN sebut 'D+', 'D-', 'Euclidean', 'solusi ideal positif/negatif', 'matriks terbobot', 'kriteria benefit/cost', atau rumus \$V_i\$.\n" .
            "2. Gunakan bahasa manusia sehari-hari:\n" .
            "   - 'D+ mendekati 0' jelaskan sebagai: 'kualifikasinya sangat mendekati profil mahasiswa ideal/terbaik'.\n" .
            "   - 'kriteria benefit' sebut sebagai: 'prestasi atau capaian akademik'.\n" .
            "   - 'kriteria cost' sebut sebagai: 'kondisi ekonomi / penghasilan orang tua'.\n" .
            "3. Susun penjelasan dalam 3 bagian singkat dengan judul tebal:\n" .
            "   - **Ringkasan Keputusan**: Status kelulusan, peringkat, dan persentase kelayakannya.\n" .
            "   - **Faktor Utama Penentu**: Jelaskan keunggulan nilai yang dimiliki (sebutkan angka IPK, prestasi, tanggungan keluarga, dll.). Jika berstatus cadangan, jelaskan dengan berempati aspek yang masih kalah bersaing dibanding pendaftar lain.\n" .
            "   - **Kesimpulan / Saran**: Pesan penutup yang memotivasi dan informasi peluang cadangan.\n" .
            "4. Buat dalam 2-3 paragraf ringkas, bahasa yang hangat, profesional, dan mudah dimengerti siapa saja.";
    }

    /**
     * Panggil Groq API untuk menghasilkan penjelasan naratif cerdas kilat.
     *
     * @param array<string, mixed> $targetResult
     * @param array<int, array<string, mixed>> $criteriaDetails
     */
    protected function callGroqApi(
        Applicant $applicant,
        array $targetResult,
        bool $isPassed,
        int $quotaLimit,
        array $criteriaDetails,
        string $apiKey,
    ): ?string {
        try {
            $prompt = $this->buildPrompt($applicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails);
            $model = config('services.groq.model', 'qwen/qwen3.8-27b');

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(8)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah konselor beasiswa yang ramah, berempati, dan profesional. Jelaskan hasil seleksi dalam bahasa Indonesia yang sangat mudah dipahami masyarakat umum tanpa rumus matematika rumit.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 600,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? null;
                if (! empty($text)) {
                    return trim($text);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Groq API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Panggil Google Gemini API untuk menghasilkan penjelasan naratif cerdas.
     *
     * @param array<string, mixed> $targetResult
     * @param array<int, array<string, mixed>> $criteriaDetails
     */
    protected function callGeminiApi(
        Applicant $applicant,
        array $targetResult,
        bool $isPassed,
        int $quotaLimit,
        array $criteriaDetails,
        string $apiKey,
    ): ?string {
        try {
            $prompt = $this->buildPrompt($applicant, $targetResult, $isPassed, $quotaLimit, $criteriaDetails);
            $model = config('services.gemini.model', 'gemini-flash-latest');

            $response = Http::timeout(8)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 700,
                    ],
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (! empty($text)) {
                    return trim($text);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini API call failed, falling back to heuristic engine: ' . $e->getMessage());
        }

        return null;
    }
}
