<?php
namespace App\Console\Commands;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CreateAdmin extends Command {
    protected $signature = 'app:create-admin';
    protected $description = 'Create an administrator interactively without demo credentials';
    public function handle(): int {
        $data = [
            'name' => $this->ask('Nombre'),
            'username' => $this->ask('Usuario'),
            'email' => $this->ask('Correo'),
            'password' => $this->secret('Clave (minimo 12 caracteres)'),
        ];
        $validation = Validator::make($data, [
            'name'=>'required|string|max:120',
            'username'=>'required|string|max:40|unique:users,username',
            'email'=>'required|email|max:255|unique:users,email',
            'password'=>'required|string|min:12|max:255',
        ]);
        if ($validation->fails()) {
            $this->error('Datos invalidos: revise nombre, usuario, correo y longitud de clave.');
            return self::FAILURE;
        }
        if ($data['password'] !== $this->secret('Repita la clave')) {
            $this->error('Las claves no coinciden.'); return self::FAILURE;
        }
        DB::transaction(function () use ($data) {
            $this->call('db:seed', ['--class'=>'RolSeeder', '--force'=>true]);
            $role = Rol::where('nombre', 'administrador')->firstOrFail();
            User::create(array_merge($data, ['password'=>Hash::make($data['password']), 'rol_id'=>$role->id, 'estado'=>'activo']));
        });
        $this->info('Administrador creado.');
        return self::SUCCESS;
    }
}
