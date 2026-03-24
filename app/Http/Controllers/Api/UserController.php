<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json($users);
    }

    public function store(UpsertUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        $user->syncRoles([$request->string('role')]);

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            'data' => $user->fresh()->load('roles'),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'data' => $user->load('roles'),
        ]);
    }

    public function update(UpsertUserRequest $request, User $user): JsonResponse
    {
        $payload = [
            'name' => $request->string('name'),
            'email' => $request->string('email'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->string('password'));
        }

        $user->update($payload);
        $user->syncRoles([$request->string('role')]);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente.',
            'data' => $user->fresh()->load('roles'),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario en sesión.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente.']);
    }
}
