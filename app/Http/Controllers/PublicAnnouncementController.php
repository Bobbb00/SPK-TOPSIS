<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ScholarshipQuota;
use App\Services\AiExplanationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicAnnouncementController extends Controller
{
    /**
     * Tampilkan halaman portal pengumuman publik.
     */
    public function index(): Response
    {
        $quota = ScholarshipQuota::active();

        return Inertia::render('Public/Announcement', [
            'quota' => $quota ? [
                'quota_limit' => $quota->quota_limit,
                'period' => $quota->period,
            ] : [
                'quota_limit' => 5,
                'period' => '2026/2027',
            ],
            'totalApplicants' => Applicant::count(),
        ]);
    }

    /**
     * Lacak dan verifikasi status pendaftar berdasarkan NIM dan nama.
     */
    public function check(Request $request, AiExplanationService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $nim = trim($validated['nim']);
        $nameInput = isset($validated['name']) ? trim($validated['name']) : '';

        /** @var Applicant|null $applicant */
        $applicant = Applicant::query()
            ->with('evaluations')
            ->where('nim', $nim)
            ->first();

        if (! $applicant) {
            return response()->json([
                'success' => false,
                'message' => "Pendaftar dengan NIM '{$nim}' tidak ditemukan dalam basis data seleksi periode ini. Silakan periksa kembali nomor pendaftaran Anda.",
            ], 404);
        }

        // Verifikasi nama jika diisi oleh pengguna (case-insensitive substring)
        if ($nameInput !== '') {
            $normalizedApplicantName = strtolower($applicant->name);
            $normalizedInput = strtolower($nameInput);

            if (! str_contains($normalizedApplicantName, $normalizedInput) && ! str_contains($normalizedInput, $normalizedApplicantName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama tidak sesuai dengan berkas pemilik NIM yang terdaftar. Masukkan nama lengkap sesuai saat pendaftaran.',
                ], 422);
            }
        }

        $quota = ScholarshipQuota::active();
        $explanationData = $aiService->explain($applicant);

        return response()->json([
            'success' => true,
            'data' => [
                'applicant' => [
                    'id' => $applicant->id,
                    'nim' => $applicant->nim,
                    'name' => $applicant->name,
                    'study_program' => $applicant->study_program,
                    'rank' => $explanationData['applicant']['rank'],
                    'score' => $explanationData['applicant']['score'],
                    'is_passed' => $explanationData['applicant']['is_passed'],
                ],
                'quota' => [
                    'quota_limit' => $explanationData['quota_limit'],
                    'period' => $quota?->period ?? '2026/2027',
                ],
                'key_factors' => $explanationData['key_factors'] ?? [],
                'explanation' => $explanationData['explanation'] ?? '',
                'engine' => $explanationData['engine'] ?? 'Smart DSS Engine',
            ],
        ]);
    }
}
