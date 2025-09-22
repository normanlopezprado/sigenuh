<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('hospital')->latest()->paginate(50);
        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $hospitals = Hospital::orderBy('name')->get(['id','name']);

        // NUEVO: lista de roles fijos
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Administrador','Nutrición','Recolector','Visualizador'])
            ->orderBy('name')
            ->pluck('name')
            ->all();

        // Pasa hospitales + roles a la vista
        return view('usuarios.create', compact('hospitals','roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required','string','max:120'],
            'email'              => ['required','email','max:255','unique:users,email'],
            'hospital_selected'  => ['nullable','uuid','exists:hospitals,id'],
            'password'           => ['nullable','string','min:8'],
            'user'               => ['nullable','string','max:50'],
            'avatar'             => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            // NUEVO: rol obligatorio, debe existir para guard web
            'role'               => [
                'required','string',
                Rule::exists('roles','name')->where('guard_name','web'),
            ],
        ]);

        // base para username (según tu implementación actual)
        $base = $request->input('user') ?: \App\Models\User::baseUsernameFromName($data['name']);
        $data['user'] = \App\Models\User::generateUniqueUsername($base);
        $data['password'] = $data['password'] ?? \Illuminate\Support\Str::random(12);

        // Subir avatar con UUID del user
        $uuid = (string) Str::uuid();
        $data['id'] = $uuid;

        if ($request->hasFile('avatar')) {
            $ext = $request->file('avatar')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('avatar')->storeAs('avatars', $filename, 'public');
            $data['avatar'] = "avatars/{$filename}";
        }

        // Crear usuario
        $user = \App\Models\User::create($data);

        // NUEVO: asignar ÚNICO rol
        $user->syncRoles([$data['role']]);

        return redirect()->route('usuarios.index')->with('success','Usuario creado y rol asignado.');
    }

    public function show(User $usuario)
    {
        $usuario->load('hospital');
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $hospitals = Hospital::orderBy('name')->get(['id','name']);

        // NUEVO: lista de roles fijos
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Administrador','Nutrición','Recolector','Visualizador'])
            ->orderBy('name')
            ->pluck('name')
            ->all();

        // NUEVO: rol actual (siempre 1)
        $currentRole = $usuario->roles()->pluck('name')->first();

        return view('usuarios.edit', compact('usuario','hospitals','roles','currentRole'));
    }

    public function update(Request $request, User $usuario)
    {
        if ($request->filled('password') === false) {
            $request->request->remove('password');
        }

        $data = $request->validate([
            'name'               => ['required','string','max:120'],
            'user'               => ['required','string','max:50','unique:users,user,'.$usuario->id],
            'email'              => ['required','email','max:255','unique:users,email,'.$usuario->id],
            'email_verified_at'  => ['nullable','date'],
            'hospital_selected'  => ['nullable','uuid','exists:hospitals,id'],
            'password'           => ['sometimes','string','min:8'],
            'avatar'             => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            // NUEVO: rol obligatorio, debe existir para guard web
            'role'               => [
                'required','string',
                Rule::exists('roles','name')->where('guard_name','web'),
            ],
        ]);

        if (empty($data['email_verified_at'])) unset($data['email_verified_at']);

        // avatar nuevo
        if ($request->hasFile('avatar')) {
            if ($usuario->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($usuario->avatar);
            }
            $ext = $request->file('avatar')->extension();
            $filename = "{$usuario->id}.{$ext}";
            $request->file('avatar')->storeAs('avatars', $filename, 'public');
            $data['avatar'] = "avatars/{$filename}";
        }

        $usuario->update($data);

        // NUEVO: mantener SOLO el rol elegido
        $usuario->syncRoles([$data['role']]);

        return redirect()->route('usuarios.index')->with('success','Usuario actualizado y rol sincronizado.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($usuario->avatar);
        }
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
