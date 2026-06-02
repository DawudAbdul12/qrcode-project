<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        User::query()->create($this->validatedData($request));

        return redirect()
            ->route('active8.page', 'accounts')
            ->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedData($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('active8.page', 'accounts')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'owner' && User::query()->where('role', 'owner')->where('id', '!=', $user->id)->doesntExist()) {
            return redirect()
                ->route('active8.page', 'accounts')
                ->withErrors('You must keep at least one owner user.');
        }

        $user->delete();

        return redirect()
            ->route('active8.page', 'accounts')
            ->with('status', 'User deleted successfully.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['activate', 'suspend', 'delete'])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::query()
            ->whereIn('id', $data['user_ids'])
            ->get();

        if ($users->isEmpty()) {
            return redirect()
                ->route('active8.page', 'accounts')
                ->withErrors('Select at least one user.');
        }

        if ($data['action'] === 'delete') {
            $remainingOwnerCount = User::query()
                ->where('role', 'owner')
                ->whereNotIn('id', $users->pluck('id'))
                ->count();

            if ($remainingOwnerCount === 0) {
                return redirect()
                    ->route('active8.page', 'accounts')
                    ->withErrors('You must keep at least one owner user.');
            }

            User::query()
                ->whereIn('id', $users->pluck('id'))
                ->delete();

            return redirect()
                ->route('active8.page', 'accounts')
                ->with('status', $users->count().' users deleted successfully.');
        }

        $status = $data['action'] === 'activate' ? 'active' : 'suspended';

        User::query()
            ->whereIn('id', $users->pluck('id'))
            ->update(['status' => $status]);

        return redirect()
            ->route('active8.page', 'accounts')
            ->with('status', $users->count().' users '.($status === 'active' ? 'activated' : 'suspended').' successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password' => $passwordRules,
            'role' => ['required', Rule::in(['owner', 'admin', 'operator'])],
            'status' => ['required', Rule::in(['active', 'invited', 'suspended'])],
        ]);
    }
}
