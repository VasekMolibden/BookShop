<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bio',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Author $author) {
            if (empty($author->slug)) {
                $author->slug = Str::slug($author->name);
            }
        });

        static::updating(function (Author $author) {
            if ($author->isDirty('name') && empty($author->slug)) {
                $author->slug = Str::slug($author->name);
            }
        });
    }
}
