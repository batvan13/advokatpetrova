<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::orderBy('name')->get();

        return view('admin.admins.index', [
            'admins'     => $admins,
            'totalCount' => $admins->count(),
        ]);
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Въведете име.',
            'email.required'     => 'Въведете имейл.',
            'email.email'        => 'Невалиден имейл адрес.',
            'email.unique'       => 'Този имейл вече е зает.',
            'password.required'  => 'Въведете парола.',
            'password.min'       => 'Паролата трябва да е поне 8 символа.',
            'password.confirmed' => 'Потвърждението не съвпада.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Администраторът е създаден успешно.');
    }

    public function edit(User $admin)
    {
        return view('admin.admins.edit', ['admin' => $admin]);
    }

    public function update(Request $request, User $admin)
    {
        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
        ];

        $messages = [
            'name.required'  => 'Въведете име.',
            'email.required' => 'Въведете имейл.',
            'email.email'    => 'Невалиден имейл адрес.',
            'email.unique'   => 'Този имейл вече е зает.',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
            $messages['password.min']       = 'Паролата трябва да е поне 8 символа.';
            $messages['password.confirmed'] = 'Потвърждението не съвпада.';
        }

        $request->validate($rules, $messages);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $admin->update($data);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Администраторът е обновен успешно.');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === Auth::id()) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'Не можете да изтриете собствения си акаунт.');
        }

        if (User::count() <= 1) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'Не можете да изтриете последния администратор.');
        }

        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Администраторът е изтрит.');
    }
}
