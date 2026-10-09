<?php

declare(strict_types=1);

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\PublicAnnouncementController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Portal Pengumuman Publik & Pelacakan Mandiri Mahasiswa
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});
Route::get('/pengumuman', [PublicAnnouncementController::class, 'index'])->name('announcement.index');
Route::post('/pengumuman/check', [PublicAnnouncementController::class, 'check'])->name('announcement.check')->middleware('throttle:10,1');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Manajemen Kriteria & Bobot (Fase 2)
    Route::get('/criteria', [CriteriaController::class, 'index'])->name('criteria.index');
    Route::post('/criteria', [CriteriaController::class, 'store'])->name('criteria.store');
    Route::put('/criteria/{criteria}', [CriteriaController::class, 'update'])->name('criteria.update');
    Route::delete('/criteria/{criteria}', [CriteriaController::class, 'destroy'])->name('criteria.destroy');
    Route::post('/criteria/update-weights', [CriteriaController::class, 'updateWeights'])->name('criteria.update-weights');

    // Manajemen Pendaftar (Fase 3)
    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::post('/applicants', [ApplicantController::class, 'store'])->name('applicants.store');
    Route::put('/applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');
    Route::delete('/applicants/{applicant}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');

    // Penilaian & Matriks Keputusan (Fase 3)
    Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::put('/evaluations/{applicant}', [EvaluationController::class, 'update'])->name('evaluations.update');

    // Hasil & Ranking TOPSIS, Transparansi & Ekspor (Fase 1 & Fase 4)
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('/ranking/export-csv', [RankingController::class, 'exportCsv'])->name('ranking.export-csv');
    Route::get('/ranking/print', [RankingController::class, 'print'])->name('ranking.print');
    Route::get('/ranking/{applicant}/explain', \App\Http\Controllers\AiExplanationController::class)->name('ranking.explain');

    // Manajemen Batas Kuota Penerima Beasiswa
    Route::put('/scholarship-quota', [\App\Http\Controllers\ScholarshipQuotaController::class, 'update'])->name('quota.update');

    // Manajemen Akun Panitia (Fase 5)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
