<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Applicant\StoreApplicantRequest;
use App\Http\Requests\Applicant\UpdateApplicantRequest;
use App\Models\Applicant;
use App\Models\Criteria;
use App\Services\AiExplanationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantController extends Controller
{
    /**
     * Tampilkan daftar seluruh mahasiswa pendaftar beasiswa.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $studyProgram = $request->string('study_program')->toString();

        $criteriasCount = Criteria::count();

        $applicants = Applicant::query()
            ->withCount('evaluations')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                });
            })
            ->when($studyProgram !== '', function ($query) use ($studyProgram) {
                $query->where('study_program', $studyProgram);
            })
            ->orderBy('nim')
            ->get()
            ->map(function ($applicant) use ($criteriasCount) {
                return [
                    'id' => $applicant->id,
                    'nim' => $applicant->nim,
                    'name' => $applicant->name,
                    'study_program' => $applicant->study_program,
                    'evaluations_count' => $applicant->evaluations_count,
                    'is_complete' => $criteriasCount > 0 && $applicant->evaluations_count === $criteriasCount,
                ];
            });

        $studyPrograms = Applicant::query()
            ->distinct()
            ->pluck('study_program')
            ->filter()
            ->values();

        return Inertia::render('Applicants/Index', [
            'applicants' => $applicants,
            'studyPrograms' => $studyPrograms,
            'criteriasCount' => $criteriasCount,
            'filters' => [
                'search' => $search,
                'study_program' => $studyProgram,
            ],
        ]);
    }

    /**
     * Simpan data pendaftar baru.
     */
    public function store(StoreApplicantRequest $request): RedirectResponse
    {
        $applicant = Applicant::create($request->validated());
        AiExplanationService::flushCache();

        return redirect()->route('applicants.index')
            ->with('success', "Pendaftar {$applicant->name} ({$applicant->nim}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data pendaftar.
     */
    public function update(UpdateApplicantRequest $request, Applicant $applicant): RedirectResponse
    {
        $applicant->update($request->validated());
        AiExplanationService::flushCache();

        return redirect()->route('applicants.index')
            ->with('success', "Data pendaftar {$applicant->name} ({$applicant->nim}) berhasil diperbarui.");
    }

    /**
     * Hapus data pendaftar.
     */
    public function destroy(Applicant $applicant): RedirectResponse
    {
        $name = $applicant->name;
        $applicant->delete();
        AiExplanationService::flushCache();

        return redirect()->route('applicants.index')
            ->with('success', "Data pendaftar {$name} berhasil dihapus.");
    }
}
