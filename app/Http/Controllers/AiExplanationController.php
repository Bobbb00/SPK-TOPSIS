<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Services\AiExplanationService;
use Illuminate\Http\JsonResponse;

class AiExplanationController extends Controller
{
    /**
     * Hasilkan penjelasan berbasis AI / DSS untuk pelamar tertentu.
     */
    public function __invoke(Applicant $applicant, AiExplanationService $service): JsonResponse
    {
        $data = $service->explain($applicant);

        return response()->json($data);
    }
}
