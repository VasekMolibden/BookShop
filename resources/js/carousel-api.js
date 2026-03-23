/**
 * Example: How to use the carousel with book data from the API
 * 
 * This file shows how to integrate the carousel component with 
 * fetched book data from your REST API
 */

import { api } from './api.js';
import { initBookCarousel, initSimpleImageGallery } from './carousel.js';

/**
 * Load and display a book with carousel
 * @param {number} bookId - The book ID to load
 * @param {string} containerId - The ID of the carousel container element
 * @param {boolean} useFullCarousel - Use Splide carousel (true) or simple gallery (false)
 */
export async function loadBookWithCarousel(bookId, containerId, useFullCarousel = true) {
    try {
        const response = await api(`/books/${bookId}`);
        const book = response.data.book;

        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Carousel container #${containerId} not found`);
            return;
        }

        // Use the carousel or simple gallery based on preference
        if (useFullCarousel) {
            initBookCarousel(container, book.images);
        } else {
            initSimpleImageGallery(container, book.images);
        }

        return book;
    } catch (error) {
        console.error('Failed to load book:', error);
    }
}

/**
 * Handle image upload in admin panel
 * @param {number} bookId - The book ID
 * @param {File} imageFile - The image file to upload
 * @param {string} altText - Alternative text for the image
 */
export async function uploadBookImage(bookId, imageFile, altText = '') {
    try {
        const formData = new FormData();
        formData.append('image', imageFile);
        if (altText) {
            formData.append('alt_text', altText);
        }

        const response = await api(`/admin/books/${bookId}/images`, {
            method: 'POST',
            body: formData,
        });

        return response.data.image;
    } catch (error) {
        console.error('Failed to upload image:', error);
        throw error;
    }
}

/**
 * Delete a book image
 * @param {number} bookId - The book ID
 * @param {number} imageId - The image ID to delete
 */
export async function deleteBookImage(bookId, imageId) {
    try {
        const response = await api(`/admin/books/${bookId}/images/${imageId}`, {
            method: 'DELETE',
        });

        return response;
    } catch (error) {
        console.error('Failed to delete image:', error);
        throw error;
    }
}

/**
 * Update image metadata
 * @param {number} bookId - The book ID
 * @param {number} imageId - The image ID
 * @param {Object} data - The data to update (alt_text, is_primary, sort_order)
 */
export async function updateBookImage(bookId, imageId, data) {
    try {
        const response = await api(`/admin/books/${bookId}/images/${imageId}`, {
            method: 'PATCH',
            json: data,
        });

        return response.data.image;
    } catch (error) {
        console.error('Failed to update image:', error);
        throw error;
    }
}

/**
 * Reorder images for a book
 * @param {number} bookId - The book ID
 * @param {number[]} imageIds - Array of image IDs in the desired order
 */
export async function reorderBookImages(bookId, imageIds) {
    try {
        const response = await api(`/admin/books/${bookId}/images/reorder`, {
            method: 'POST',
            json: { image_ids: imageIds },
        });

        return response;
    } catch (error) {
        console.error('Failed to reorder images:', error);
        throw error;
    }
}

/**
 * Example HTML structure for a product page with carousel:
 * 
 * <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
 *   <!-- Carousel container -->
 *   <div id="bookCarousel" class="bg-gray-100 rounded-lg overflow-hidden" style="height: 500px;"></div>
 * 
 *   <!-- Book details -->
 *   <div>
 *     <h1 id="bookTitle"></h1>
 *     <p id="bookAuthor"></p>
 *     <p id="bookPrice"></p>
 *     <!-- ... other details ... -->
 *   </div>
 * </div>
 * 
 * <script type="module">
 *   import { loadBookWithCarousel } from './carousel-api.js';
 *   
 *   const bookId = 1; // Get from URL or data attribute
 *   loadBookWithCarousel(bookId, 'bookCarousel', true);
 * </script>
 */
