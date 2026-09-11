<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@antilow.test')],
            [
                'name' => env('ADMIN_NAME', 'Administrador Antilow'),
                'password' => env('ADMIN_PASSWORD', 'cambia-esta-clave'),
                'role' => 'admin',
            ],
        );
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'role' => 'client',
            ],
        );
    }
}
