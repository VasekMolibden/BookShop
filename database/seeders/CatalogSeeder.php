<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Seed genres and books for the catalog.
     */
    public function run(): void
    {
        $fiction = Genre::query()->firstOrCreate(
            ['slug' => 'fiction'],
            ['name' => 'Художественная литература']
        );
        $nonFiction = Genre::query()->firstOrCreate(
            ['slug' => 'non-fiction'],
            ['name' => 'Нехудожественная литература']
        );
        $kids = Genre::query()->firstOrCreate(
            ['slug' => 'kids'],
            ['name' => 'Детская литература']
        );

        $books = [
            [
                'title' => 'Война и мир',
                'slug' => 'voyna-i-mir',
                'author' => 'Л. Н. Толстой',
                'description' => 'Роман-эпопея о русском обществе эпохи войн против Наполеона.',
                'price' => 1299.00,
                'isbn' => '978-5-389-07477-6',
                'stock' => 12,
                'published_at' => '1869-01-01',
                'genres' => [$fiction],
            ],
            [
                'title' => 'Преступление и наказание',
                'slug' => 'prestuplenie-i-nakazanie',
                'author' => 'Ф. М. Достоевский',
                'description' => 'Социально-психологический роман о нравственных исканиях.',
                'price' => 599.00,
                'isbn' => '978-5-389-12345-1',
                'stock' => 8,
                'published_at' => '1866-01-01',
                'genres' => [$fiction],
            ],
            [
                'title' => 'Sapiens. Краткая история человечества',
                'slug' => 'sapiens',
                'author' => 'Юваль Ной Харари',
                'description' => 'История Homo sapiens от каменного века до наших дней.',
                'price' => 890.00,
                'isbn' => '978-5-00195-876-3',
                'stock' => 25,
                'published_at' => '2011-01-01',
                'genres' => [$nonFiction],
            ],
            [
                'title' => 'Маленький принц',
                'slug' => 'malenkiy-prints',
                'author' => 'Антуан де Сент-Экзюпери',
                'description' => 'Повесть-сказка о Маленьком принце и его планете.',
                'price' => 350.00,
                'isbn' => '978-5-389-11111-2',
                'stock' => 40,
                'published_at' => '1943-01-01',
                'genres' => [$fiction, $kids],
            ],
            [
                'title' => 'Чистый код',
                'slug' => 'clean-code',
                'author' => 'Роберт Мартин',
                'description' => 'Руководство по гибкой разработке программного обеспечения.',
                'price' => 2200.00,
                'isbn' => '978-5-93286-201-3',
                'stock' => 5,
                'published_at' => '2008-01-01',
                'genres' => [$nonFiction],
            ],
        ];

        foreach ($books as $row) {
            $genres = $row['genres'];
            unset($row['genres']);

            $row['slug'] = $row['slug'] ?? Str::slug($row['title']);

            $book = Book::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row
            );

            $book->genres()->sync(collect($genres)->pluck('id')->all());
        }
    }
}
