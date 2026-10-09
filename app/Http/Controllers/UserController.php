<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun panitia pengelola.
     */
    public function index(): Response
    {
        $users = User::query()
            ->select(["id", "name", "email", "created_at"])
            ->latest("id")
            ->get();

        return Inertia::render("Users/Index", [
            "users" => $users,
        ]);
    }

    /**
     * Daftarkan akun panitia baru.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            "name" => $validated["name"],
            "email" => $validated["email"],
            "password" => $validated["password"],
        ]);

        return back()->with("success", "Akun panitia berhasil ditambahkan.");
    }

    /**
     * Perbarui kata sandi akun panitia.
     */
    public function updatePassword(
        UpdateUserPasswordRequest $request,
        User $user,
    ): RedirectResponse {
        $validated = $request->validated();

        $user->update([
            "password" => $validated["password"],
        ]);

        return back()->with(
            "success",
            "Kata sandi panitia berhasil diperbarui.",
        );
    }

    /**
     * Hapus akun panitia (kecuali akun sendiri).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with(
                "error",
                "Anda tidak dapat menghapus akun Anda sendiri.",
            );
        }

        $user->delete();

        return back()->with("success", "Akun panitia berhasil dihapus.");
    }
}
