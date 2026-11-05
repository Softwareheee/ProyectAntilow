<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Crea únicamente el administrador inicial con credenciales explícitas del entorno. */
    public function run(): void
    {
        $name = trim((string) env('ADMIN_NAME'));
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 16) {
            throw new InvalidArgumentException(
                'Define ADMIN_NAME, un ADMIN_EMAIL válido y ADMIN_PASSWORD de al menos 16 caracteres antes de ejecutar db:seed.',
            );
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => 'admin',
            ],
        );

        if ($admin->role !== 'admin') {
            $admin->role = 'admin';
            $admin->save();
        }
    }
}
