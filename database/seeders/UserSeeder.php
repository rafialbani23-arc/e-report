<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat 1 akun admin
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
        ]);

        // Membuat 20 akun warga
        User::factory()->count(20)->create();
    }
}
