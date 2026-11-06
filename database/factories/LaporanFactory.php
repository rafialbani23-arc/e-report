<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pelapor_id' => User::factory(),
            'judul' => fake()->sentence(5),
            'deskripsi' => fake()->paragraph(),
            'tanggal' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => 'pending',
        ];
    }
}
