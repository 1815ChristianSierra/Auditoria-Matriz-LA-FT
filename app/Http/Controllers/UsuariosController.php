<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    public function index()
    {
        // Obtener todos los usuarios con su relación de roles
        $usuarios = User::with('rol')->get();
        $roles = Rol::all();
        // Devolver la vista con los usuarios listados
        //dd($usuarios);
        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        /*dd($request->all());//Valida que la informacion llegue desde la vista*/
        $request->validate([
            'usuario' => 'required|string|max:40|unique:users,username',
            'nombre' => 'required|string|max:120',
            'correo' => 'required|string|email|max:255|unique:users,email',
            'rol_id' => 'required|integer|exists:rol,id',
            'estado' => 'required|in:Activo,Bloqueado',
            'contrasena' => 'required|string|min:8|max:255',
        ]);

        User::create([
            'name' => $request->nombre,
            'username' => $request->usuario,
            'email' => $request->correo,
            'password' => Hash::make($request->contrasena),
            'rol_id' => $request->rol_id,
            'estado' => strtolower($request->estado),
        ]);

        // Redirigir a la lista de usuarios con un mensaje de éxito
        return redirect()->route('usuarios.index')->with('success', 'Registro Creado Correctamente');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Rol::all();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    // Método para actualizar los datos de un usuario en la base de datos
    public function update(Request $request, $id)
    {
        // Validación de los datos recibidos (el usuario/username no se modifica)
        $request->validate([
            'nombre' => 'required|string|max:120',
            'correo' => 'required|string|email|max:255|unique:users,email,' . $id, // Permite el mismo correo para el usuario actual
            'rol_id' => 'required|integer|exists:rol,id',
            'estado' => 'required|in:Activo,Bloqueado',
            'contrasena' => 'nullable|string|min:8|max:255',
        ]);

        // Encontrar el usuario por su ID
        $usuario = User::findOrFail($id);

        // Actualización de los datos del usuario
        $usuario->update([
            'name' => $request->nombre,
            'email' => $request->correo,
            'rol_id' => $request->rol_id,
            'estado' => strtolower($request->estado),
        ]);

        // Cambiar la contraseña solo si se escribió una nueva
        if ($request->filled('contrasena')) {
            $usuario->update(['password' => Hash::make($request->contrasena)]);
        }

        // Redirigir a la lista de usuarios con un mensaje de éxito
        return redirect()->route('usuarios.index')->with('success', 'Registro Actualizado Correctamente.');
    }

    // Método para eliminar un usuario de la base de datos
    public function destroy($id)
    {
        // Encontrar el usuario por su ID
        $usuario = User::findOrFail($id);
        // Eliminar el usuario
        $usuario->delete();
        // Redirigir a la lista de usuarios con un mensaje de éxito
        return redirect()->route('usuarios.index')->with('success', 'Registro Eliminado Correctamente.');
    }
}
