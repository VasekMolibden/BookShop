<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BookImageResource;
use App\Models\Book;
use App\Models\BookImage;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BookImageController extends Controller
{
    /**
     * Upload an image for a book.
     */
    public function store(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,webp,gif|max:5120',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        
        // Create a unique filename with timestamp
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Optimize and save the image
        $path = Storage::disk('public')->putFileAs(
            'books',
            $file,
            $filename
        );

        // Create the database record
        $image = $book->images()->create([
            'file_path' => $filename,
            'alt_text' => $request->input('alt_text'),
            'is_primary' => $book->images()->count() === 0,
            'sort_order' => $book->images()->max('sort_order') + 1 ?? 0,
        ]);

        return ApiResponse::success([
            'image' => new BookImageResource($image),
        ], 'Изображение загружено', 201);
    }

    /**
     * Update image metadata (alt text, sort order, primary status).
     */
    public function update(Request $request, Book $book, BookImage $image): JsonResponse
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_primary' => 'nullable|boolean',
        ]);

        // Get validated data
        $data = [];
        if ($request->has('alt_text')) {
            $data['alt_text'] = $request->input('alt_text');
        }
        if ($request->has('sort_order')) {
            $data['sort_order'] = $request->input('sort_order');
        }
        if ($request->has('is_primary')) {
            $data['is_primary'] = $request->boolean('is_primary');
        }

        if ($request->has('is_primary') && $request->boolean('is_primary')) {
            // Unset primary for other images
            $book->images()->update(['is_primary' => false]);
        }

        $image->update($data);

        return ApiResponse::success([
            'image' => new BookImageResource($image),
        ], 'Изображение обновлено');
    }

    /**
     * Delete an image.
     */
    public function destroy(Book $book, BookImage $image): JsonResponse
    {
        // Delete the file from storage
        Storage::disk('public')->delete('books/' . $image->file_path);

        // If it was primary, make the first remaining image primary
        if ($image->is_primary && $book->images()->count() > 1) {
            $book->images()
                ->where('id', '!=', $image->id)
                ->orderBy('sort_order')
                ->first()
                ->update(['is_primary' => true]);
        }

        $image->delete();

        return ApiResponse::success(null, 'Изображение удалено');
    }

    /**
     * Reorder images.
     */
    public function reorder(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:book_images,id',
        ]);

        $imageIds = $request->input('image_ids');
        
        foreach ($imageIds as $index => $imageId) {
            $book->images()
                ->where('id', $imageId)
                ->update(['sort_order' => $index]);
        }

        return ApiResponse::success(null, 'Порядок изображений обновлён');
    }
}
