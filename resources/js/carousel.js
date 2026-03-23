//import Splide from 'splide';
//import 'splide/css';

/**
 * Initialize a carousel for book images.
 * 
 * @param {HTMLElement} container - The container element for the carousel
 * @param {Array} images - Array of image objects with 'url' and 'alt_text' properties
 * @param {Object} options - Configuration options
 */
export function initBookCarousel(container, images = [], options = {}) {
    if (!container) {
        console.warn('Carousel container not found');
        return null;
    }

    if (images.length === 0) {
        container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background-color: #f5f5f5"><p style="color: #999">Нет изображений</p></div>';
        return null;
    }

    // Clear container and set display
    container.style.display = 'block';
    container.style.position = 'relative';
    container.style.overflow = 'hidden';
    
    // Get primary image or first one
    const primaryImage = images.find(img => img.is_primary) || images[0];
    
    // Simple image display (no Splide for simplicity)
    const html = `
        <img 
            src="${primaryImage.url}"
            alt="${primaryImage.alt_text || 'Book image'}"
            style="width: 100%; height: 100%; object-fit: cover; display: block;"
        />
        ${images.length > 1 ? `
            <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; display: flex; gap: 4px; overflow-x: auto;">
                ${images.map((img, idx) => `
                    <img 
                        src="${img.url}"
                        alt="${img.alt_text || 'Thumbnail'}"
                        data-idx="${idx}"
                        class="carousel-thumb"
                        style="width: 50px; height: 50px; object-fit: cover; border: 2px solid ${img.id === primaryImage.id ? '#9a6b35' : '#ddd'}; border-radius: 4px; cursor: pointer; flex-shrink: 0;"
                    />
                `).join('')}
            </div>
        ` : ''}
    `;

    container.innerHTML = html;

    // Add click handlers for thumbnails if multiple images
    if (images.length > 1) {
        const mainImg = container.querySelector('img:not(.carousel-thumb)');
        container.querySelectorAll('.carousel-thumb').forEach((thumb, idx) => {
            thumb.addEventListener('click', () => {
                if (mainImg) {
                    mainImg.src = images[idx].url;
                    mainImg.alt = images[idx].alt_text || 'Book image';
                }
                // Update borders
                container.querySelectorAll('.carousel-thumb').forEach((t, i) => {
                    t.style.borderColor = i === idx ? '#9a6b35' : '#ddd';
                });
            });
        });
    }

    return null;
}

/**
 * Initialize all carousels on the page.
 * Usage: Add data-carousel="book" to elements that should have carousels
 */
export function initAllCarousels() {
    document.querySelectorAll('[data-carousel="book"]').forEach(element => {
        const imagesJson = element.dataset.images;
        if (imagesJson) {
            try {
                const images = JSON.parse(imagesJson);
                initBookCarousel(element, images);
            } catch (e) {
                console.error('Failed to parse carousel images:', e);
            }
        }
    });
}

/**
 * Create a simple image gallery without carousel (for single images).
 * Useful as fallback or for minimal implementations.
 */
export function initSimpleImageGallery(container, images = []) {
    if (!container || images.length === 0) return;

    const primaryImage = images.find(img => img.is_primary) || images[0];
    
    const html = `
        <div class="relative">
            <img 
                src="${primaryImage.url}"
                alt="${primaryImage.alt_text || 'Book image'}"
                class="w-full h-auto rounded"
            />
            ${images.length > 1 ? `
                <div class="flex gap-2 mt-2 overflow-x-auto">
                    ${images.map((img, idx) => `
                        <button 
                            class="thumbnail flex-shrink-0 w-16 h-16 border-2 border-gray-300 rounded hover:border-blue-500"
                            data-image-index="${idx}"
                            title="${img.alt_text || 'Image ' + (idx + 1)}"
                        >
                            <img 
                                src="${img.url}"
                                alt="${img.alt_text || 'Book image'}"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    `).join('')}
                </div>
            ` : ''}
        </div>
    `;

    container.innerHTML = html;

    // Wire up thumbnail clicks
    if (images.length > 1) {
        const mainImage = container.querySelector('img');
        container.querySelectorAll('.thumbnail').forEach((thumb, idx) => {
            thumb.addEventListener('click', () => {
                mainImage.src = images[idx].url;
                mainImage.alt = images[idx].alt_text || 'Book image';
                
                // Update active state
                container.querySelectorAll('.thumbnail').forEach(t => {
                    t.classList.remove('border-blue-500');
                    t.classList.add('border-gray-300');
                });
                thumb.classList.remove('border-gray-300');
                thumb.classList.add('border-blue-500');
            });
        });
    }
}
