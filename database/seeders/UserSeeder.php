<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $clave = Hash::make('password1815');
        $clave2 = Hash::make('password');

        // rol_id: 1 = administrador, 2 = analista, 3 = supervisor, 4 = auditor
        User::insert([
            [
                'name' => 'christian',
                'username' => 'administrador1',
                'email' => 'administrador1@gmail.com',
                'password' => $clave,
                'rol_id' => 1,
            ],
            [
                'name' => 'diego',
                'username' => 'analista1',
                'email' => 'analista1@gmail.com',
                'password' => $clave2,
                'rol_id' => 2,
            ],
            [
                'name' => 'daniel',
                'username' => 'supervisor1',
                'email' => 'supervisor1@gmail.com',
                'password' => $clave2,
                'rol_id' => 3,
            ],
            [
                'name' => 'marcous',
                'username' => 'auditor1',
                'email' => 'auditor1@gmail.com',
                'password' => $clave2,
                'rol_id' => 4,
            ],
        ]);
    }
}
