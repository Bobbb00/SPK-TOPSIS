<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Criteria\StoreCriteriaRequest;
use App\Http\Requests\Criteria\UpdateCriteriaRequest;
use App\Models\Criteria;
use App\Services\AiExplanationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CriteriaController extends Controller
{
    /**
     * Tampilkan daftar seluruh kriteria seleksi.
     */
    public function index(): Response
    {
        $criterias = Criteria::query()
            ->orderBy('code')
            ->get();

        $totalWeight = (float) $criterias->sum('weight');

        return Inertia::render('Criteria/Index', [
            'criterias' => $criterias,
            'totalWeight' => round($totalWeight, 4),
        ]);
    }

    /**
     * Simpan kriteria baru.
     */
    public function store(StoreCriteriaRequest $request): RedirectResponse
    {
        $criteria = Criteria::create($request->validated());
        AiExplanationService::flushCache();

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$criteria->code} ({$criteria->name}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data kriteria.
     */
    public function update(UpdateCriteriaRequest $request, Criteria $criteria): RedirectResponse
    {
        $criteria->update($request->validated());
        AiExplanationService::flushCache();

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$criteria->code} ({$criteria->name}) berhasil diperbarui.");
    }

    /**
     * Hapus kriteria.
     */
    public function destroy(Criteria $criteria): RedirectResponse
    {
        $code = $criteria->code;
        $criteria->delete();
        AiExplanationService::flushCache();

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$code} berhasil dihapus.");
    }

    /**
     * Perbarui bobot seluruh kriteria secara massal.
     */
    public function updateWeights(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.*.id' => ['required', 'exists:criterias,id'],
            'weights.*.weight' => ['required', 'numeric', 'min:0.01', 'max:1.00'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['weights'] as $item) {
                Criteria::where('id', $item['id'])->update([
                    'weight' => $item['weight'],
                ]);
            }
        });

        AiExplanationService::flushCache();

        $newTotal = round((float) Criteria::sum('weight'), 4);
        if (abs($newTotal - 1.0) < 0.001) {
            return redirect()->route('criteria.index')
                ->with('success', 'Bobot seluruh kriteria berhasil diperbarui dan telah bernilai tepat 1.0 (100%).');
        }

        return redirect()->route('criteria.index')
            ->with('success', "Bobot berhasil disimpan. Total bobot saat ini: {$newTotal}.");
    }
}
