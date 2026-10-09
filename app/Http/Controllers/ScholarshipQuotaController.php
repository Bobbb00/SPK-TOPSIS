<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ScholarshipQuota\UpdateScholarshipQuotaRequest;
use App\Models\ScholarshipQuota;
use App\Services\AiExplanationService;
use Illuminate\Http\RedirectResponse;

class ScholarshipQuotaController extends Controller
{
    /**
     * Perbarui atau buat kuota beasiswa aktif.
     */
    public function update(UpdateScholarshipQuotaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $quota = ScholarshipQuota::active();

        if ($quota) {
            $quota->update($validated);
        } else {
            $quota = ScholarshipQuota::create($validated);
        }

        AiExplanationService::flushCache();

        return back()->with('success', "Batas kuota penerima beasiswa berhasil diperbarui menjadi {$quota->quota_limit} orang ({$quota->period}).");
    }
}
