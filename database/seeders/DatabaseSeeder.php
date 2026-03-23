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
        // Локальные тестовые аккаунты (пароль у обоих: password)
        User::factory()->create([
            'name' => 'Покупатель Тестовый',
            'email' => 'user@bookshop.test',
            'is_admin' => false,
        ]);

        User::factory()->create([
            'name' => 'Администратор',
            'email' => 'admin@bookshop.test',
            'is_admin' => true,
        ]);

        $this->call(CatalogSeeder::class);
    }
}
