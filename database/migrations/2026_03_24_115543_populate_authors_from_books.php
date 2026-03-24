<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get unique authors from books table
        $authors = DB::table('books')
            ->whereNotNull('author')
            ->where('author', '!=', '')
            ->distinct()
            ->pluck('author')
            ->toArray();

        foreach ($authors as $authorName) {
            // Create author if doesn't exist
            $author = DB::table('authors')->where('name', $authorName)->first();

            if (!$author) {
                $authorId = DB::table('authors')->insertGetId([
                    'name' => $authorName,
                    'slug' => Str::slug($authorName),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update books with this author
                DB::table('books')
                    ->where('author', $authorName)
                    ->update(['author_id' => $authorId]);
            } else {
                // Update books with existing author
                DB::table('books')
                    ->where('author', $authorName)
                    ->whereNull('author_id')
                    ->update(['author_id' => $author->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear author_id from books
        DB::table('books')->update(['author_id' => null]);
    }
};
