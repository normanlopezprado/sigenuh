<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Roles permitidos en la app (sin CRUD de roles) */
    private const ALLOWED_ROLES = ['Administrador','Nutrición','Recolector','Visualizador'];



    public function index()
    {
        $users = User::with(['hospital','roles'])->latest()->paginate(50);
        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $hospitals = Hospital::orderBy('name')->get(['id','name']);

        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', self::ALLOWED_ROLES)
            ->orderBy('name')
            ->pluck('name')
            ->all();

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
            'role'               => [
                'required','string',
                Rule::in(self::ALLOWED_ROLES),
                Rule::exists('roles','name')->where('guard_name','web'),
            ],
        ]);

        // Username sugerido → base única
        $base = $request->input('user') ?: \App\Models\User::baseUsernameFromName($data['name']);
        $data['user'] = \App\Models\User::generateUniqueUsername($base);

        // Password auto si no viene (tu modelo lo hashea con cast 'hashed')
        $data['password'] = $data['password'] ?? Str::random(12);

        // UUID manual para nombrar avatar
        $uuid = (string) Str::uuid();
        $data['id'] = $uuid;

        if ($request->hasFile('avatar')) {
            $ext = $request->file('avatar')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('avatar')->storeAs('avatars', $filename, 'public');
            $data['avatar'] = "avatars/{$filename}";
        }

        $user = User::create($data);

        // Asignar ÚNICO rol
        $user->syncRoles([$data['role']]);

        return redirect()->route('usuarios.index')->with('success','Usuario creado y rol asignado.');
    }

    public function show(User $usuario)
    {
        $usuario->load(['hospital','roles']);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $hospitals = Hospital::orderBy('name')->get(['id','name']);

        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', self::ALLOWED_ROLES)
            ->orderBy('name')
            ->pluck('name')
            ->all();

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
            'role'               => [
                'required','string',
                Rule::in(self::ALLOWED_ROLES),
                Rule::exists('roles','name')->where('guard_name','web'),
            ],
        ]);

        if (empty($data['email_verified_at'])) {
            unset($data['email_verified_at']);
        }

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

        // Mantener SOLO el rol elegido
        $usuario->syncRoles([$data['role']]);

        return redirect()->route('usuarios.index')->with('success','Usuario actualizado y rol sincronizado.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
        return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->hasRole('Administrador')) {
            $admins = \Spatie\Permission\Models\Role::findByName('Administrador', 'web')
                        ->users()->count();
            if ($admins <= 1) {
                return back()->with('error', 'No puedes eliminar al último Administrador.');
            }
        }
        // (opcional) Evitar borrarte a ti mismo o al último Administrador:
        // if (auth()->id() === $usuario->id) { abort(403, 'No puedes eliminar tu propio usuario.'); }
        // if ($usuario->hasRole('Administrador') && User::role('Administrador')->count() <= 1) { abort(403, 'Debe existir al menos un Administrador.'); }

        if ($usuario->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($usuario->avatar);
        }
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
