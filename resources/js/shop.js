import { api, clearToken, getToken, setToken } from './api';
import { initBookCarousel } from './carousel';

/** @type {number|null} */
let alertTimerId = null;

/**
 * @param {string} text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * @param {string|number} value
 */
function formatPrice(value) {
    return `${Number(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')} ₽`;
}

/**
 * @param {string} iso
 */
function formatDateTime(iso) {
    try {
        return new Date(iso).toLocaleString('ru-RU');
    } catch {
        return iso;
    }
}

/**
 * @param {number} count
 */
function formatReviewsCount(count) {
    const n = Math.abs(Math.trunc(Number(count) || 0));
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) {
        return `${n} отзыв`;
    }
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
        return `${n} отзыва`;
    }
    return `${n} отзывов`;
}

/**
 * @param {HTMLElement|null} el
 * @param {string} message
 * @param {'error'|'success'} [variant]
 */
function showAlert(el, message, variant = 'error') {
    if (!el) {
        return;
    }
    const messageEl = document.getElementById('shop-alert-message');
    if (messageEl) {
        messageEl.textContent = message;
    } else {
        el.textContent = message;
    }
    el.classList.remove('hidden');
    if (variant === 'success') {
        el.classList.remove('border-red-200', 'bg-red-50', 'text-red-800');
        el.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
    } else {
        el.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
    }

    if (alertTimerId) {
        window.clearTimeout(alertTimerId);
    }
    alertTimerId = window.setTimeout(() => {
        hideAlert(el);
    }, 5000);
}

/**
 * @param {HTMLElement|null} el
 */
function hideAlert(el) {
    if (alertTimerId) {
        window.clearTimeout(alertTimerId);
        alertTimerId = null;
    }
    el?.classList.add('hidden');
}

function initAlertClose() {
    const alertEl = document.getElementById('shop-alert');
    const closeBtn = document.getElementById('shop-alert-close');
    if (!alertEl || !closeBtn) {
        return;
    }
    closeBtn.addEventListener('click', () => {
        hideAlert(alertEl);
    });
}

/**
 * @param {HTMLElement} container
 * @param {{ current_page: number, last_page: number }} pagination
 * @param {(page: number) => void} onPage
 */
function renderPagination(container, pagination, onPage) {
    container.innerHTML = '';
    if (pagination.last_page <= 1) {
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');

    const mkBtn = (label, page, disabled = false) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = label;
        b.className =
            'rounded-lg border border-stone-300 px-3 py-1.5 text-sm hover:bg-stone-50 disabled:cursor-not-allowed disabled:opacity-50';
        b.disabled = disabled;
        b.addEventListener('click', () => onPage(page));
        return b;
    };

    container.append(
        mkBtn('Назад', pagination.current_page - 1, pagination.current_page <= 1),
        mkBtn('Вперёд', pagination.current_page + 1, pagination.current_page >= pagination.last_page)
    );
}

async function refreshNav() {
    const userEl = document.getElementById('shop-nav-user');
    const userMenuEl = document.getElementById('shop-user-menu');
    const loginEl = document.getElementById('shop-nav-login');
    const registerEl = document.getElementById('shop-nav-register');
    const logoutEl = document.getElementById('shop-nav-logout');

    if (!userEl || !loginEl || !registerEl || !logoutEl || !userMenuEl) {
        return;
    }

    const token = getToken();
    if (!token) {
        userMenuEl.classList.add('hidden');
        logoutEl.classList.add('hidden');
        loginEl.classList.remove('hidden');
        registerEl.classList.remove('hidden');
        userEl.textContent = '';
        document.getElementById('shop-nav-settings')?.classList.add('hidden');
        document.querySelectorAll('[data-auth-only]').forEach(el => {
            el.classList.add('hidden');
        });
        void refreshCartBadge();
        return;
    }

    try {
        const res = await api('/auth/me');
        const user = res.data?.user;
        if (user?.name) {
            userEl.textContent = user.name;
            userMenuEl.classList.remove('hidden');
            logoutEl.classList.remove('hidden');
            loginEl.classList.add('hidden');
            registerEl.classList.add('hidden');
        } else {
            userMenuEl.classList.add('hidden');
        }

        const adminLink = document.getElementById('shop-nav-admin');
        if (adminLink) {
            if (user?.is_admin) {
                adminLink.classList.remove('hidden');
            } else {
                adminLink.classList.add('hidden');
            }
        }

        const settingsLink = document.getElementById('shop-nav-settings');
        if (settingsLink) {
            if (user?.name) {
                settingsLink.classList.remove('hidden');
            } else {
                settingsLink.classList.add('hidden');
            }
        }

        document.querySelectorAll('[data-auth-only]').forEach(el => {
            el.classList.remove('hidden');
        });
        void refreshCartBadge();
    } catch {
        clearToken();
        userMenuEl.classList.add('hidden');
        logoutEl.classList.add('hidden');
        loginEl.classList.remove('hidden');
        registerEl.classList.remove('hidden');
        document.getElementById('shop-nav-settings')?.classList.add('hidden');
        document.querySelectorAll('[data-auth-only]').forEach(el => {
            el.classList.add('hidden');
        });
        void refreshCartBadge();
    }
}

async function refreshCartBadge() {
    const badgeEl = document.getElementById('shop-nav-cart-count');
    if (!badgeEl) {
        return;
    }

    if (!getToken()) {
        badgeEl.classList.add('hidden');
        return;
    }

    try {
        const res = await api('/cart');
        const totalQty = Number(res.data?.cart?.total_quantity ?? 0);
        if (totalQty > 0) {
            badgeEl.textContent = totalQty > 99 ? '99+' : String(totalQty);
            badgeEl.classList.remove('hidden');
        } else {
            badgeEl.classList.add('hidden');
        }
    } catch {
        badgeEl.classList.add('hidden');
    }
}

// Global state for favorite and cart books
/** @type {Set<number>} */
let favoriteBookIds = new Set();
/** @type {Set<number>} */
let cartBookIds = new Set();
/** @type {Map<number, number>} */
let cartItemIdsByBookId = new Map();

/**
 * Load user's favorite and cart items
 */
async function loadUserState() {
    favoriteBookIds = new Set();
    cartBookIds = new Set();
    cartItemIdsByBookId = new Map();

    if (!getToken()) {
        return;
    }

    try {
        const [favRes, cartRes] = await Promise.all([
            api('/favorites?per_page=100'),
            api('/cart'),
        ]);

        const favs = favRes.data?.favorites ?? [];
        for (const fav of favs) {
            favoriteBookIds.add(Number(fav.book_id));
        }

        const cartItems = cartRes.data?.items ?? [];
        for (const item of cartItems) {
            cartBookIds.add(Number(item.book_id));
            cartItemIdsByBookId.set(Number(item.book_id), Number(item.id));
        }
    } catch {
        // Silently fail if we can't load user state
    }
}

/**
 * Update catalog button states (used by both catalog and search pages)
 */
function updateCatalogButtons() {
    document.querySelectorAll('.catalog-fav-btn').forEach((btn) => {
        const bookId = Number(btn.getAttribute('data-book-id'));
        if (favoriteBookIds.has(bookId)) {
            btn.innerHTML = `
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            `;
            btn.setAttribute('title', 'Убрать из избранного');
            btn.setAttribute('aria-label', 'Убрать из избранного');
            btn.classList.add('text-red-600', 'bg-red-50/95', 'border-red-200');
            btn.classList.remove('bg-white/95', 'border-white/80');
            btn.disabled = false;
        } else {
            btn.innerHTML = `
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            `;
            btn.setAttribute('title', 'Добавить в избранное');
            btn.setAttribute('aria-label', 'Добавить в избранное');
            btn.classList.remove('text-red-600', 'bg-red-50/95', 'border-red-200');
            btn.classList.add('bg-white/95', 'border-white/80');
            btn.disabled = false;
        }
    });

    document.querySelectorAll('.catalog-cart-btn').forEach((btn) => {
        const bookId = Number(btn.getAttribute('data-book-id'));
        if (cartBookIds.has(bookId)) {
            btn.textContent = 'В корзине';
            btn.classList.remove('bg-amber-800', 'hover:bg-amber-900');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
            btn.disabled = false;
        } else {
            btn.textContent = 'В корзину';
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-amber-800', 'hover:bg-amber-900');
            btn.disabled = false;
        }
    });
}

function initNav() {
    void refreshNav();

    const searchWrapEl = document.getElementById('shop-nav-search');
    const searchToggleEl = document.getElementById('shop-nav-search-toggle');
    const searchFormEl = document.getElementById('shop-nav-search-form');
    const searchInputEl = document.getElementById('shop-nav-search-input');
    const searchSubmitEl = document.getElementById('shop-nav-search-submit');

    const closeSearch = () => {
        if (!searchToggleEl || !searchFormEl) {
            return;
        }
        searchToggleEl.setAttribute('aria-expanded', 'false');
        searchFormEl.classList.add('hidden');
    };

    const openSearch = () => {
        if (!searchToggleEl || !searchFormEl) {
            return;
        }
        searchToggleEl.setAttribute('aria-expanded', 'true');
        searchFormEl.classList.remove('hidden');
        searchInputEl?.focus();
    };

    if (searchWrapEl && searchToggleEl && searchFormEl) {
        const runHeaderSearch = () => {
            const query = (searchInputEl?.value ?? '').trim();
            const target = query ? `/search?search=${encodeURIComponent(query)}` : '/search';
            window.location.href = target;
        };

        searchToggleEl.addEventListener('click', (e) => {
            e.preventDefault();
            const expanded = searchToggleEl.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                closeSearch();
            } else {
                openSearch();
            }
        });

        searchFormEl.addEventListener('submit', (e) => {
            e.preventDefault();
            runHeaderSearch();
        });

        searchFormEl.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        searchInputEl?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                runHeaderSearch();
            }
        });

        searchSubmitEl?.addEventListener('click', (e) => {
            e.preventDefault();
            runHeaderSearch();
        });
    }

    const userMenuEl = document.getElementById('shop-user-menu');
    const triggerEl = document.getElementById('shop-user-menu-trigger');
    const dropdownEl = document.getElementById('shop-user-dropdown');
    const arrowEl = document.getElementById('shop-user-menu-arrow');

    const closeUserMenu = () => {
        if (!triggerEl || !dropdownEl || !arrowEl) {
            return;
        }
        triggerEl.setAttribute('aria-expanded', 'false');
        dropdownEl.classList.add('hidden');
        arrowEl.classList.remove('rotate-180');
    };

    const openUserMenu = () => {
        if (!triggerEl || !dropdownEl || !arrowEl) {
            return;
        }
        triggerEl.setAttribute('aria-expanded', 'true');
        dropdownEl.classList.remove('hidden');
        arrowEl.classList.add('rotate-180');
    };

    if (userMenuEl && triggerEl && dropdownEl && arrowEl) {
        triggerEl.addEventListener('click', (e) => {
            e.preventDefault();
            const expanded = triggerEl.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                closeUserMenu();
            } else {
                openUserMenu();
            }
        });

        document.addEventListener('click', (e) => {
            if (!userMenuEl.contains(/** @type {Node} */ (e.target))) {
                closeUserMenu();
            }
            if (searchWrapEl && !searchWrapEl.contains(/** @type {Node} */ (e.target))) {
                closeSearch();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeUserMenu();
                closeSearch();
            }
        });
    }

    document.getElementById('shop-nav-logout')?.addEventListener('click', async () => {
        closeUserMenu();
        try {
            if (getToken()) {
                await api('/auth/logout', { method: 'POST', json: {} });
            }
        } catch {
            /* ignore */
        }
        clearToken();
        window.location.href = '/';
    });
}

function initCatalog() {
    const genreSelect = document.getElementById('catalog-genre');
    const searchInput = document.getElementById('catalog-search');
    const applyBtn = document.getElementById('catalog-apply');
    const loading = document.getElementById('catalog-loading');
    const empty = document.getElementById('catalog-empty');
    const list = document.getElementById('catalog-list');
    const pagEl = document.getElementById('catalog-pagination');
    const alertEl = document.getElementById('shop-alert');

    if (!genreSelect || !searchInput || !list || !pagEl) {
        return;
    }

    let page = 1;
    const perPage = 12;

    async function loadGenres() {
        const res = await api('/genres', { auth: false });
        const genres = res.data?.genres ?? [];
        for (const g of genres) {
            const opt = document.createElement('option');
            opt.value = g.slug;
            opt.textContent = g.name;
            genreSelect.appendChild(opt);
        }
    }

    async function loadBooks() {
        hideAlert(alertEl);
        loading?.classList.remove('hidden');
        empty?.classList.add('hidden');
        list.classList.add('hidden');

        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        const genre = genreSelect.value;
        if (genre) {
            params.set('genre', genre);
        }
        const q = searchInput.value.trim();
        if (q) {
            params.set('search', q);
        }

        try {
            const res = await api(`/books?${params.toString()}`, { auth: false });
            const books = res.data?.books ?? [];
            const pagination = res.data?.pagination;

            loading?.classList.add('hidden');

            if (!books.length) {
                empty?.classList.remove('hidden');
                pagEl.classList.add('hidden');
                return;
            }

            list.innerHTML = '';
            list.classList.remove('hidden');

            for (const b of books) {
                const li = document.createElement('li');
                li.className =
                    'rounded-xl border border-stone-200 bg-white overflow-hidden shadow-sm transition hover:border-amber-300';
                const genres =
                    (b.genres ?? [])
                        .map((g) => `<span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-900">${escapeHtml(g.name)}</span>`)
                        .join(' ') || '';
                
                const imageHtml = b.primary_image && b.primary_image.url 
                    ? `<img src="${b.primary_image.url}" alt="${escapeHtml(b.primary_image.alt_text || b.title)}" class="w-full h-64 object-contain bg-stone-50">`
                    : `<div class="w-full h-64 bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center text-4xl">📖</div>`;
                const rating = Number(b.average_rating ?? 0);
                const reviewsCount = Number(b.reviews_count ?? 0);
                const ratingHtml = reviewsCount > 0
                    ? `<div class="mt-1 text-xs text-stone-500">★ ${rating.toFixed(1)} · ${formatReviewsCount(reviewsCount)}</div>`
                    : `<div class="mt-1 text-xs text-stone-400">Нет отзывов</div>`;
                
                li.innerHTML = `
                    <div class="relative">
                        <a href="/books/${b.id}" class="block">
                            ${imageHtml}
                        </a>
                        <button
                            type="button"
                            class="catalog-fav-btn absolute right-2 top-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border bg-white/95 text-stone-600 shadow-sm backdrop-blur-sm transition-colors hover:text-red-600"
                            data-book-id="${b.id}"
                            title="Добавить в избранное"
                            aria-label="Добавить в избранное"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </div>
                    <a href="/books/${b.id}" class="block p-4">
                            <h2 class="font-semibold text-stone-900 line-clamp-2">${escapeHtml(b.title)}</h2>
                            <p class="mt-1 text-sm text-stone-600">${escapeHtml(b.author)}</p>
                            ${ratingHtml}
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-lg font-medium text-amber-900">${formatPrice(b.price)}</p>
                                <button type="button" class="catalog-cart-btn rounded-lg bg-amber-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-900 transition-colors" data-book-id="${b.id}">
                                    В корзину
                                </button>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">${genres}</div>
                    </a>
                `;
                list.appendChild(li);
            }

            updateCatalogButtons();

            // Add event listeners for favorite and cart buttons
            list.querySelectorAll('.catalog-fav-btn').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!getToken()) {
                        window.location.href = '/login';
                        return;
                    }
                    
                const bookId = Number(btn.getAttribute('data-book-id'));
                    try {
                    if (favoriteBookIds.has(bookId)) {
                        await api(`/favorites/${bookId}`, { method: 'DELETE' });
                        favoriteBookIds.delete(bookId);
                        showAlert(alertEl, 'Книга удалена из избранного', 'success');
                    } else {
                        await api('/favorites', { method: 'POST', json: { book_id: bookId } });
                        favoriteBookIds.add(bookId);
                        showAlert(alertEl, 'Книга добавлена в избранное', 'success');
                    }
                        updateCatalogButtons();
                    } catch (err) {
                        const error = /** @type {any} */ (err);
                        if (error.status === 409) {
                            favoriteBookIds.add(bookId);
                            updateCatalogButtons();
                            showAlert(alertEl, 'Книга уже в избранном', 'success');
                    } else if (error.status === 404) {
                        favoriteBookIds.delete(bookId);
                        updateCatalogButtons();
                        showAlert(alertEl, 'Книги уже нет в избранном', 'success');
                        } else {
                        showAlert(alertEl, error.message || 'Ошибка обновления избранного');
                        }
                    }
                });
            });

            list.querySelectorAll('.catalog-cart-btn').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!getToken()) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    const bookId = Number(btn.getAttribute('data-book-id'));
                    try {
                    if (cartBookIds.has(bookId)) {
                        const itemId = cartItemIdsByBookId.get(bookId);
                        if (!itemId) {
                            await loadUserState();
                        }
                        const actualItemId = cartItemIdsByBookId.get(bookId);
                        if (!actualItemId) {
                            throw new Error('Не удалось определить товар в корзине');
                        }
                        await api(`/cart/items/${actualItemId}`, { method: 'DELETE' });
                        cartBookIds.delete(bookId);
                        cartItemIdsByBookId.delete(bookId);
                        showAlert(alertEl, 'Книга удалена из корзины', 'success');
                    } else {
                        const addRes = await api('/cart/items', { method: 'POST', json: { book_id: bookId, quantity: 1 } });
                        cartBookIds.add(bookId);
                        const itemId = addRes.data?.item?.id;
                        if (itemId) {
                            cartItemIdsByBookId.set(bookId, Number(itemId));
                        }
                        showAlert(alertEl, 'Книга добавлена в корзину', 'success');
                    }
                        void refreshCartBadge();
                        updateCatalogButtons();
                    } catch (err) {
                        const error = /** @type {any} */ (err);
                    if (error.status === 404) {
                        cartBookIds.delete(bookId);
                        cartItemIdsByBookId.delete(bookId);
                        updateCatalogButtons();
                        showAlert(alertEl, 'Книги уже нет в корзине', 'success');
                        void refreshCartBadge();
                    } else {
                        showAlert(alertEl, error.message || 'Ошибка обновления корзины');
                    }
                    }
                });
            });

            if (pagination) {
                renderPagination(pagEl, pagination, (p) => {
                    page = p;
                    void loadBooks();
                });
            }
        } catch (e) {
            loading?.classList.add('hidden');
            showAlert(alertEl, e instanceof Error ? e.message : 'Не удалось загрузить каталог');
        }
    }

    void loadGenres().then(async () => {
        await loadUserState();
        await loadBooks();
    });

    applyBtn?.addEventListener('click', () => {
        const params = new URLSearchParams();
        const genre = genreSelect.value;
        if (genre) {
            params.set('genre', genre);
        }
        const q = searchInput.value.trim();
        if (q) {
            params.set('search', q);
        }

        const queryString = params.toString();
        window.location.href = queryString ? `/search?${queryString}` : '/search';
    });
}

function initBookPage() {
    const bookId = document.body.dataset.bookId;
    const alertEl = document.getElementById('shop-alert');
    if (!bookId) {
        return;
    }

    const loading = document.getElementById('book-loading');
    const root = document.getElementById('book-root');
    const reviewsUl = document.getElementById('book-reviews');
    const reviewsPag = document.getElementById('reviews-pagination');
    const reviewForm = document.getElementById('review-form');
    const reviewHint = document.getElementById('review-login-hint');

    let reviewsPage = 1;
    let inFavorite = false;
    let inCart = false;
    /** @type {number|null} */
    let cartItemId = null;

    async function checkFavorite() {
        if (!getToken()) {
            inFavorite = false;
            return;
        }
        try {
            const res = await api('/favorites?per_page=100');
            const favs = res.data?.favorites ?? [];
            inFavorite = favs.some((f) => f.book?.id === Number(bookId));
        } catch {
            inFavorite = false;
        }
        updateFavButton();
    }

    function updateFavButton() {
        const btn = document.getElementById('book-toggle-fav');
        if (!btn) {
            return;
        }
        if (!getToken()) {
            btn.classList.add('hidden');
            return;
        }
        btn.classList.remove('hidden');
        btn.textContent = inFavorite ? 'Убрать из избранного' : 'В избранное';
    }

    async function checkCart() {
        inCart = false;
        cartItemId = null;
        if (!getToken()) {
            updateCartButton();
            return;
        }
        try {
            const res = await api('/cart');
            const items = res.data?.cart?.items ?? [];
            const line = items.find((item) => Number(item.book?.id) === Number(bookId));
            if (line) {
                inCart = true;
                cartItemId = Number(line.id);
            }
        } catch {
            inCart = false;
            cartItemId = null;
        }
        updateCartButton();
    }

    function updateCartButton() {
        const btn = document.getElementById('book-add-cart');
        if (!btn) {
            return;
        }
        if (!getToken()) {
            btn.textContent = 'В корзину';
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-amber-800', 'hover:bg-amber-900');
            return;
        }

        if (inCart) {
            btn.textContent = 'В корзине';
            btn.classList.remove('bg-amber-800', 'hover:bg-amber-900');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
        } else {
            btn.textContent = 'В корзину';
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-amber-800', 'hover:bg-amber-900');
        }
    }

    async function loadBook() {
        hideAlert(alertEl);
        try {
            const res = await api(`/books/${bookId}`, { auth: false });
            const b = res.data?.book;
            if (!b) {
                throw new Error('Книга не найдена');
            }
            document.getElementById('book-title') && (document.getElementById('book-title').textContent = b.title);
            document.getElementById('book-author') && (document.getElementById('book-author').textContent = b.author);

            const meta = [];
            if (b.isbn) {
                meta.push(`ISBN: ${b.isbn}`);
            }
            if (b.published_at) {
                meta.push(`Выход: ${b.published_at}`);
            }
            if (b.reviews_count != null) {
                meta.push(`Отзывов: ${b.reviews_count}`);
            }
            const metaEl = document.getElementById('book-meta');
            if (metaEl) {
                metaEl.textContent = meta.join(' · ');
            }

            const genresEl = document.getElementById('book-genres');
            if (genresEl) {
                genresEl.innerHTML = (b.genres ?? [])
                    .map(
                        (g) =>
                            `<span class="rounded-full bg-amber-50 px-2 py-1 text-xs text-amber-900">${escapeHtml(g.name)}</span>`
                    )
                    .join(' ');
            }

            const priceEl = document.getElementById('book-price');
            if (priceEl) {
                priceEl.textContent = formatPrice(b.price);
            }
            const stockEl = document.getElementById('book-stock');
            if (stockEl) {
                stockEl.textContent = `На складе: ${b.stock}`;
            }
            const descEl = document.getElementById('book-description');
            if (descEl) {
                descEl.textContent = b.description ?? '—';
            }

            // Initialize book carousel
            const carouselEl = document.getElementById('book-carousel');
            if (carouselEl && b.images && b.images.length > 0) {
                initBookCarousel(carouselEl, b.images);
            }

            loading?.classList.add('hidden');
            root?.classList.remove('hidden');

            if (getToken()) {
                reviewForm?.classList.remove('hidden');
                reviewHint?.classList.add('hidden');
            } else {
                reviewForm?.classList.add('hidden');
                reviewHint?.classList.remove('hidden');
            }

            await checkFavorite();
            updateFavButton();
            await checkCart();
            updateCartButton();
        } catch (e) {
            loading?.classList.add('hidden');
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка загрузки');
        }
    }

    async function loadReviews() {
        if (!reviewsUl) {
            return;
        }
        try {
            const res = await api(`/books/${bookId}/reviews?page=${reviewsPage}&per_page=10`, { auth: false });
            const reviews = res.data?.reviews ?? [];
            const pagination = res.data?.pagination;

            reviewsUl.innerHTML = '';
            for (const r of reviews) {
                const li = document.createElement('li');
                li.className = 'rounded-lg border border-stone-100 bg-white p-4';
                const name = r.user?.name ? escapeHtml(r.user.name) : 'Пользователь';
                li.innerHTML = `
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <span class="font-medium text-stone-900">${name}</span>
                        <span class="text-sm text-amber-800">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-600">${escapeHtml(r.comment ?? '')}</p>
                    <p class="mt-2 text-xs text-stone-400">${formatDateTime(r.created_at)}</p>
                `;
                reviewsUl.appendChild(li);
            }

            if (pagination && reviewsPag) {
                renderPagination(reviewsPag, pagination, (p) => {
                    reviewsPage = p;
                    void loadReviews();
                });
            }
        } catch {
            reviewsUl.innerHTML = '<li class="text-stone-500">Не удалось загрузить отзывы</li>';
        }
    }

    document.getElementById('book-add-cart')?.addEventListener('click', async () => {
        hideAlert(alertEl);
        if (!getToken()) {
            showAlert(alertEl, 'Войдите, чтобы добавить товар в корзину.');
            return;
        }
        try {
            if (inCart) {
                if (!cartItemId) {
                    await checkCart();
                }
                if (!cartItemId) {
                    throw new Error('Не удалось определить позицию в корзине');
                }
                await api(`/cart/items/${cartItemId}`, { method: 'DELETE' });
                inCart = false;
                cartItemId = null;
                showAlert(alertEl, 'Книга удалена из корзины.', 'success');
            } else {
                const qtyInput = document.getElementById('book-qty');
                const qty = Math.max(1, Number(qtyInput?.value) || 1);
                const res = await api('/cart/items', {
                    method: 'POST',
                    json: { book_id: Number(bookId), quantity: qty },
                });
                inCart = true;
                const addedItemId = res.data?.item?.id;
                cartItemId = addedItemId ? Number(addedItemId) : null;
                showAlert(alertEl, 'Добавлено в корзину.', 'success');
            }
            void refreshCartBadge();
            updateCartButton();
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    });

    document.getElementById('book-toggle-fav')?.addEventListener('click', async () => {
        hideAlert(alertEl);
        if (!getToken()) {
            return;
        }
        try {
            if (inFavorite) {
                await api(`/favorites/${bookId}`, { method: 'DELETE' });
                inFavorite = false;
            } else {
                await api('/favorites', { method: 'POST', json: { book_id: Number(bookId) } });
                inFavorite = true;
            }
            updateFavButton();
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка избранного');
        }
    });

    reviewForm?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const rating = Number(document.getElementById('review-rating')?.value);
        const comment = document.getElementById('review-comment')?.value ?? '';
        try {
            await api('/reviews', {
                method: 'POST',
                json: {
                    book_id: Number(bookId),
                    rating,
                    comment: comment || null,
                },
            });
            reviewsPage = 1;
            await loadReviews();
            reviewForm.reset();
            document.getElementById('review-rating').value = '5';
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Не удалось отправить отзыв');
        }
    });

    void loadBook().then(() => loadReviews());
}

function initLogin() {
    const form = document.getElementById('login-form');
    const alertEl = document.getElementById('shop-alert');
    form?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(form);
        try {
            const res = await api('/auth/login', {
                method: 'POST',
                auth: false,
                json: {
                    email: String(fd.get('email') ?? ''),
                    password: String(fd.get('password') ?? ''),
                },
            });
            const token = res.data?.token;
            if (token) {
                setToken(token);
            }
            window.location.href = '/';
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка входа');
        }
    });
}

function initRegister() {
    const form = document.getElementById('register-form');
    const alertEl = document.getElementById('shop-alert');
    form?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(form);
        try {
            const res = await api('/auth/register', {
                method: 'POST',
                auth: false,
                json: {
                    name: String(fd.get('name') ?? ''),
                    email: String(fd.get('email') ?? ''),
                    password: String(fd.get('password') ?? ''),
                    password_confirmation: String(fd.get('password_confirmation') ?? ''),
                },
            });
            const token = res.data?.token;
            if (token) {
                setToken(token);
            }
            window.location.href = '/';
        } catch (e) {
            const err = /** @type {Error & { payload?: any }} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAlert(alertEl, msg);
        }
    });
}

function initCart() {
    const guest = document.getElementById('cart-guest');
    const loading = document.getElementById('cart-loading');
    const empty = document.getElementById('cart-empty');
    const root = document.getElementById('cart-root');
    const itemsUl = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    const alertEl = document.getElementById('shop-alert');

    if (!itemsUl || !root) {
        return;
    }

    if (!getToken()) {
        guest?.classList.remove('hidden');
        loading?.classList.add('hidden');
        return;
    }

    async function loadCart() {
        hideAlert(alertEl);
        loading?.classList.remove('hidden');
        empty?.classList.add('hidden');
        root.classList.add('hidden');

        try {
            const res = await api('/cart');
            const cart = res.data?.cart;
            const items = cart?.items ?? [];
            void refreshCartBadge();

            loading?.classList.add('hidden');

            if (!items.length) {
                empty?.classList.remove('hidden');
                return;
            }

            root.classList.remove('hidden');
            itemsUl.innerHTML = '';
            for (const line of items) {
                const b = line.book;
                const li = document.createElement('li');
                li.className = 'flex flex-col gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between';
                li.innerHTML = `
                    <div>
                        <a href="/books/${b.id}" class="font-medium text-stone-900 hover:underline">${escapeHtml(b.title)}</a>
                        <p class="text-sm text-stone-600">${escapeHtml(b.author)}</p>
                        <p class="mt-1 text-sm text-amber-900">${formatPrice(b.price)} × ${line.quantity} = ${formatPrice(line.line_total)}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="number" min="1" value="${line.quantity}" data-item-id="${line.id}" class="cart-qty w-20 rounded-lg border border-stone-300 px-2 py-1 text-sm" />
                        <button type="button" data-remove="${line.id}" class="rounded-lg border border-red-200 px-3 py-1 text-sm text-red-800 hover:bg-red-50">Удалить</button>
                    </div>
                `;
                itemsUl.appendChild(li);
            }

            if (totalEl && cart) {
                totalEl.textContent = `${formatPrice(cart.total_amount)} (${cart.total_quantity} шт.)`;
            }

            itemsUl.querySelectorAll('.cart-qty').forEach((input) => {
                input.addEventListener('change', async () => {
                    const id = input.getAttribute('data-item-id');
                    const q = Math.max(1, Number(input.value) || 1);
                    try {
                        await api(`/cart/items/${id}`, { method: 'PATCH', json: { quantity: q } });
                        await loadCart();
                        void refreshCartBadge();
                    } catch (e) {
                        showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
                    }
                });
            });

            itemsUl.querySelectorAll('[data-remove]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-remove');
                    try {
                        await api(`/cart/items/${id}`, { method: 'DELETE' });
                        await loadCart();
                        void refreshCartBadge();
                    } catch (e) {
                        showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
                    }
                });
            });
        } catch (e) {
            loading?.classList.add('hidden');
            if (/** @type {any} */ (e).status === 401) {
                guest?.classList.remove('hidden');
                return;
            }
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка корзины');
        }
    }

    document.getElementById('cart-checkout')?.addEventListener('click', async () => {
        hideAlert(alertEl);
        try {
            const res = await api('/orders/checkout', { method: 'POST', json: {} });
            const paymentUrl = res.data?.payment?.payment_url;
            if (paymentUrl) {
                window.location.href = paymentUrl;
                return;
            }
            window.location.href = '/orders';
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Не удалось оформить заказ');
        }
    });

    void loadCart();
}

function initOrders() {
    const guest = document.getElementById('orders-guest');
    const loading = document.getElementById('orders-loading');
    const empty = document.getElementById('orders-empty');
    const list = document.getElementById('orders-list');
    const pagEl = document.getElementById('orders-pagination');
    const alertEl = document.getElementById('shop-alert');

    if (!list || !pagEl) {
        return;
    }

    if (!getToken()) {
        guest?.classList.remove('hidden');
        loading?.classList.add('hidden');
        return;
    }

    let page = 1;

    async function load() {
        hideAlert(alertEl);
        loading?.classList.remove('hidden');
        empty?.classList.add('hidden');
        list.classList.add('hidden');

        try {
            const res = await api(`/orders?page=${page}&per_page=10`);
            const orders = res.data?.orders ?? [];
            const pagination = res.data?.pagination;

            loading?.classList.add('hidden');

            if (!orders.length) {
                empty?.classList.remove('hidden');
                pagEl.innerHTML = '';
                return;
            }

            list.classList.remove('hidden');
            list.innerHTML = '';

            for (const o of orders) {
                const li = document.createElement('li');
                li.className = 'rounded-xl border border-stone-200 bg-white p-4 shadow-sm';
                const cancelBtn = o.status === 'pending'
                    ? `<button type="button" class="ml-2 text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200" data-cancel-order="${o.id}">Отменить</button>`
                    : '';
                li.innerHTML = `
                    <a href="/orders/${o.id}" class="font-medium text-amber-900 hover:underline">Заказ №${o.id}</a>
                    <span class="ml-2 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700">${escapeHtml(o.status)}</span>
                    ${cancelBtn}
                    <p class="mt-1 text-sm text-stone-600">${formatDateTime(o.created_at)}</p>
                    <p class="mt-2 font-semibold text-stone-900">${formatPrice(o.total)}</p>
                `;
                list.appendChild(li);
            }

            // Attach cancel button handlers
            list.querySelectorAll('[data-cancel-order]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const orderId = btn.getAttribute('data-cancel-order');
                    if (!confirm('Вы уверены, что хотите отменить этот заказ?')) {
                        return;
                    }
                    try {
                        await api(`/orders/${orderId}`, { 
                            method: 'PATCH', 
                            json: { status: 'cancelled' } 
                        });
                        showAlert(alertEl, 'Заказ отменён', 'success');
                        await load();
                    } catch (e) {
                        showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка отмены');
                    }
                });
            });

            if (pagination) {
                renderPagination(pagEl, pagination, (p) => {
                    page = p;
                    void load();
                });
            }
        } catch (e) {
            loading?.classList.add('hidden');
            if (/** @type {any} */ (e).status === 401) {
                guest?.classList.remove('hidden');
                return;
            }
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    }

    void load();
}

function initOrderDetail() {
    const orderId = document.body.dataset.orderId;
    const loading = document.getElementById('order-loading');
    const root = document.getElementById('order-root');
    const alertEl = document.getElementById('shop-alert');

    if (!orderId || !root) {
        return;
    }

    if (!getToken()) {
        window.location.href = '/login';
        return;
    }

    void (async () => {
        hideAlert(alertEl);
        try {
            const res = await api(`/orders/${orderId}`);
            const o = res.data?.order;
            if (!o) {
                throw new Error('Заказ не найден');
            }

            const idEl = document.getElementById('order-id');
            if (idEl) {
                idEl.textContent = String(o.id);
            }
            const st = document.getElementById('order-status');
            if (st) {
                st.textContent = o.status;
            }
            const dt = document.getElementById('order-date');
            if (dt) {
                dt.textContent = formatDateTime(o.created_at);
            }
            const tot = document.getElementById('order-total');
            if (tot) {
                tot.textContent = formatPrice(o.total);
            }

            const itemsUl = document.getElementById('order-items');
            if (itemsUl) {
                itemsUl.innerHTML = '';
                for (const it of o.items ?? []) {
                    const b = it.book;
                    const li = document.createElement('li');
                    li.className = 'flex flex-wrap items-center justify-between gap-2 py-3';
                    li.innerHTML = `
                        <div>
                            <span class="font-medium text-stone-900">${escapeHtml(b.title)}</span>
                            <span class="text-stone-500"> — ${escapeHtml(b.author)}</span>
                        </div>
                        <div class="text-sm text-stone-700">${it.quantity} × ${formatPrice(it.unit_price)} = ${formatPrice(it.subtotal)}</div>
                    `;
                    itemsUl.appendChild(li);
                }
            }

            loading?.classList.add('hidden');
            root.classList.remove('hidden');
        } catch (e) {
            loading?.classList.add('hidden');
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    })();
}

function initFavorites() {
    const guest = document.getElementById('favorites-guest');
    const loading = document.getElementById('favorites-loading');
    const empty = document.getElementById('favorites-empty');
    const list = document.getElementById('favorites-list');
    const pagEl = document.getElementById('favorites-pagination');
    const alertEl = document.getElementById('shop-alert');

    if (!list || !pagEl) {
        return;
    }

    if (!getToken()) {
        guest?.classList.remove('hidden');
        loading?.classList.add('hidden');
        return;
    }

    let page = 1;

    async function load() {
        hideAlert(alertEl);
        loading?.classList.remove('hidden');
        empty?.classList.add('hidden');
        list.classList.add('hidden');

        try {
            const res = await api(`/favorites?page=${page}&per_page=12`);
            const favs = res.data?.favorites ?? [];
            const pagination = res.data?.pagination;

            loading?.classList.add('hidden');

            if (!favs.length) {
                empty?.classList.remove('hidden');
                pagEl.classList.add('hidden');
                return;
            }

            list.innerHTML = '';
            list.classList.remove('hidden');

            for (const f of favs) {
                const b = f.book;
                const li = document.createElement('li');
                li.className = 'rounded-xl border border-stone-200 bg-white p-4 shadow-sm';
                li.innerHTML = `
                    <a href="/books/${b.id}" class="font-semibold text-stone-900 hover:underline">${escapeHtml(b.title)}</a>
                    <p class="mt-1 text-sm text-stone-600">${escapeHtml(b.author)}</p>
                    <p class="mt-2 text-amber-900 font-medium">${formatPrice(b.price)}</p>
                    <button type="button" data-book="${b.id}" class="mt-3 text-sm text-red-700 hover:underline">Убрать</button>
                `;
                list.appendChild(li);
            }

            list.querySelectorAll('[data-book]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const bid = btn.getAttribute('data-book');
                    try {
                        await api(`/favorites/${bid}`, { method: 'DELETE' });
                        await load();
                    } catch (e) {
                        showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
                    }
                });
            });

            if (pagination) {
                renderPagination(pagEl, pagination, (p) => {
                    page = p;
                    void load();
                });
            }
        } catch (e) {
            loading?.classList.add('hidden');
            if (/** @type {any} */ (e).status === 401) {
                guest?.classList.remove('hidden');
                return;
            }
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    }

    void load();
}

function initReviews() {
    const guest = document.getElementById('reviews-guest');
    const loading = document.getElementById('reviews-loading');
    const empty = document.getElementById('reviews-empty');
    const list = document.getElementById('reviews-list');
    const pagEl = document.getElementById('reviews-pagination');
    const alertEl = document.getElementById('shop-alert');

    if (!list || !pagEl) {
        return;
    }

    if (!getToken()) {
        guest?.classList.remove('hidden');
        loading?.classList.add('hidden');
        return;
    }

    let page = 1;

    async function load() {
        hideAlert(alertEl);
        loading?.classList.remove('hidden');
        empty?.classList.add('hidden');
        list.classList.add('hidden');

        try {
            const res = await api(`/reviews?page=${page}&per_page=10`);
            const reviews = res.data?.reviews ?? [];
            const pagination = res.data?.pagination;

            loading?.classList.add('hidden');

            if (!reviews.length) {
                empty?.classList.remove('hidden');
                pagEl.classList.add('hidden');
                return;
            }

            list.innerHTML = '';
            list.classList.remove('hidden');

            for (const r of reviews) {
                const b = r.book;
                const li = document.createElement('li');
                li.className = 'rounded-xl border border-stone-200 bg-white p-4 shadow-sm';
                li.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <a href="/books/${b.id}" class="font-semibold text-stone-900 hover:underline">${escapeHtml(b.title)}</a>
                            <p class="mt-1 text-sm text-stone-600">${escapeHtml(b.author)}</p>
                            <div class="mt-2 flex items-center gap-1">
                                ${Array.from({ length: 5 }, (_, i) => `
                                    <svg class="h-4 w-4 ${i < r.rating ? 'text-amber-400' : 'text-stone-300'}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                `).join('')}
                                <span class="ml-2 text-sm text-stone-600">${formatDateTime(r.created_at)}</span>
                            </div>
                            ${r.comment ? `<p class="mt-2 text-stone-700">${escapeHtml(r.comment)}</p>` : ''}
                        </div>
                        <button type="button" data-review="${r.id}" class="ml-4 text-sm text-red-700 hover:underline">Удалить</button>
                    </div>
                `;
                list.appendChild(li);
            }

            list.querySelectorAll('[data-review]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const rid = btn.getAttribute('data-review');
                    if (!confirm('Удалить этот отзыв?')) {
                        return;
                    }
                    try {
                        await api(`/reviews/${rid}`, { method: 'DELETE' });
                        showAlert(alertEl, 'Отзыв удалён.', 'success');
                        await load();
                    } catch (e) {
                        showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
                    }
                });
            });

            if (pagination) {
                renderPagination(pagEl, pagination, (p) => {
                    page = p;
                    void load();
                });
            }
        } catch (e) {
            loading?.classList.add('hidden');
            if (/** @type {any} */ (e).status === 401) {
                guest?.classList.remove('hidden');
                return;
            }
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    }

    void load();
}

function initForgotPassword() {
    const form = document.getElementById('forgot-form');
    const alertEl = document.getElementById('shop-alert');
    form?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(form);
        try {
            await api('/auth/forgot-password', {
                method: 'POST',
                auth: false,
                json: { email: String(fd.get('email') ?? '') },
            });
            showAlert(
                alertEl,
                'Если такой email есть в системе, на него отправлена инструкция. Проверьте почту (в т.ч. папку «Спам»).',
                'success'
            );
            form.reset();
        } catch (e) {
            const err = /** @type {any} */ (e);
            if (err.status === 429) {
                showAlert(alertEl, err.message);
            } else {
                showAlert(alertEl, err instanceof Error ? err.message : 'Ошибка');
            }
        }
    });
}

function initResetPassword() {
    const form = document.getElementById('reset-form');
    const alertEl = document.getElementById('shop-alert');
    const token = document.body.dataset.resetToken;
    const email = document.body.dataset.resetEmail;

    if (!form || !token || !email) {
        showAlert(
            document.getElementById('shop-alert'),
            'Некорректная ссылка. Запросите новый сброс пароля.',
            'error'
        );
        return;
    }

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(form);
        try {
            await api('/auth/reset-password', {
                method: 'POST',
                auth: false,
                json: {
                    token,
                    email,
                    password: String(fd.get('password') ?? ''),
                    password_confirmation: String(fd.get('password_confirmation') ?? ''),
                },
            });
            showAlert(alertEl, 'Пароль обновлён. Сейчас перенаправим на страницу входа.', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 1500);
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    });
}

function initSettings() {
    const guest = document.getElementById('settings-guest');
    const root = document.getElementById('settings-root');
    const profileForm = document.getElementById('profile-form');
    const passwordForm = document.getElementById('password-form');
    const alertEl = document.getElementById('shop-alert');

    if (!profileForm || !passwordForm) {
        return;
    }

    if (!getToken()) {
        guest?.classList.remove('hidden');
        root?.classList.add('hidden');
        return;
    }

    guest?.classList.add('hidden');
    root?.classList.remove('hidden');

    async function loadProfile() {
        hideAlert(alertEl);
        try {
            const res = await api('/auth/me');
            const u = res.data?.user;
            const nameEl = document.getElementById('settings-name');
            const emailEl = document.getElementById('settings-email');
            if (nameEl && u?.name) {
                nameEl.value = u.name;
            }
            if (emailEl && u?.email) {
                emailEl.value = u.email;
                emailEl.readOnly = true;
                emailEl.classList.add('bg-gray-100', 'cursor-not-allowed');
            }
        } catch {
            window.location.href = '/login';
        }
    }

    profileForm.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(profileForm);
        try {
            await api('/auth/profile', {
                method: 'PATCH',
                json: {
                    name: String(fd.get('name') ?? ''),
                },
            });
            showAlert(alertEl, 'Профиль сохранён.', 'success');
            void refreshNav();
        } catch (e) {
            const err = /** @type {any} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAlert(alertEl, msg);
        }
    });

    passwordForm.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAlert(alertEl);
        const fd = new FormData(passwordForm);
        try {
            await api('/auth/password', {
                method: 'PATCH',
                json: {
                    current_password: String(fd.get('current_password') ?? ''),
                    password: String(fd.get('password') ?? ''),
                    password_confirmation: String(fd.get('password_confirmation') ?? ''),
                },
            });
            showAlert(alertEl, 'Пароль изменён. Переходим на вход…', 'success');
            passwordForm.reset();
            setTimeout(() => {
                clearToken();
                window.location.href = '/login';
            }, 1200);
        } catch (e) {
            const err = /** @type {any} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAlert(alertEl, msg);
        }
    });

    void loadProfile();
}

function initSearch() {
    const form = document.getElementById('search-form');
    const resultsEl = document.getElementById('search-results');
    const loadingEl = document.getElementById('search-loading');
    const paginationEl = document.getElementById('search-pagination');
    const alertEl = document.getElementById('shop-alert');

    if (!form || !resultsEl) {
        return;
    }

    // Initialize form from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const searchInput = document.getElementById('search');
    const genreSelect = document.getElementById('genre');
    const authorSelect = document.getElementById('author');
    const minPriceInput = document.getElementById('min_price');
    const maxPriceInput = document.getElementById('max_price');
    const ratingSelect = document.getElementById('min_rating');
    const inStockCheckbox = document.getElementById('in_stock');
    const publishedAfterInput = document.getElementById('published_after');
    const publishedBeforeInput = document.getElementById('published_before');
    const yearInput = document.getElementById('year');
    const sortSelect = document.getElementById('sort');

    if (searchInput) searchInput.value = urlParams.get('search') || '';
    if (genreSelect) genreSelect.value = urlParams.get('genre') || '';
    if (authorSelect) authorSelect.value = urlParams.get('author') || '';
    if (minPriceInput) minPriceInput.value = urlParams.get('min_price') || '';
    if (maxPriceInput) maxPriceInput.value = urlParams.get('max_price') || '';
    if (ratingSelect) ratingSelect.value = urlParams.get('min_rating') || '';
    if (inStockCheckbox) inStockCheckbox.checked = urlParams.get('in_stock') === '1';
    if (publishedAfterInput) publishedAfterInput.value = urlParams.get('published_after') || '';
    if (publishedBeforeInput) publishedBeforeInput.value = urlParams.get('published_before') || '';
    if (yearInput) yearInput.value = urlParams.get('year') || '';
    if (sortSelect) sortSelect.value = urlParams.get('sort') || 'title';

    async function loadResults(page = 1) {
        hideAlert(alertEl);
        loadingEl?.classList.remove('hidden');
        resultsEl.classList.add('hidden');
        paginationEl?.classList.add('hidden');

        try {
            const params = new URLSearchParams();
            const fd = new FormData(form);

            // Add form data to params
            for (const [key, value] of fd.entries()) {
                if (key === 'in_stock') {
                    // Handle checkbox specially - send '1' if checked, skip if unchecked
                    if (inStockCheckbox?.checked) {
                        params.set(key, '1');
                    }
                } else if (value && String(value).trim()) {
                    params.set(key, String(value));
                }
            }

            // Add page
            params.set('page', String(page));

            // Update URL without reloading
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);

            const res = await api(`/books?${params.toString()}`);
            const books = res.data?.books ?? [];
            const meta = res.data?.pagination;

            // Render results
            resultsEl.innerHTML = books.map(book => {
                const genres = (book.genres ?? [])
                    .map((g) => `<span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-900">${escapeHtml(g.name)}</span>`)
                    .join(' ') || '';
                
                const imageHtml = book.primary_image && book.primary_image.url 
                    ? `<img src="${book.primary_image.url}" alt="${escapeHtml(book.primary_image.alt_text || book.title)}" class="w-full h-64 object-contain bg-stone-50">`
                    : `<div class="w-full h-64 bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center text-4xl">📖</div>`;
                
                const rating = Number(book.average_rating ?? 0);
                const reviewsCount = Number(book.reviews_count ?? 0);
                const ratingHtml = reviewsCount > 0
                    ? `<div class="mt-1 text-xs text-stone-500">★ ${rating.toFixed(1)} · ${formatReviewsCount(reviewsCount)}</div>`
                    : `<div class="mt-1 text-xs text-stone-400">Нет отзывов</div>`;
                
                return `
                    <div class="rounded-xl border border-stone-200 bg-white overflow-hidden shadow-sm transition hover:border-amber-300">
                        <div class="relative">
                            <a href="/books/${book.id}" class="block">
                                ${imageHtml}
                            </a>
                            <button
                                type="button"
                                class="catalog-fav-btn absolute right-2 top-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border bg-white/95 text-stone-600 shadow-sm backdrop-blur-sm transition-colors hover:text-red-600"
                                data-book-id="${book.id}"
                                title="Добавить в избранное"
                                aria-label="Добавить в избранное"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <a href="/books/${book.id}" class="block p-4">
                            <h2 class="font-semibold text-stone-900 line-clamp-2">${escapeHtml(book.title)}</h2>
                            <p class="mt-1 text-sm text-stone-600">${escapeHtml(book.author)}</p>
                            ${ratingHtml}
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-lg font-medium text-amber-900">${formatPrice(book.price)}</p>
                                <button type="button" class="catalog-cart-btn rounded-lg bg-amber-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-900 transition-colors" data-book-id="${book.id}">
                                    В корзину
                                </button>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">${genres}</div>
                        </a>
                    </div>
                `;
            }).join('');

            // Handle empty state
            const emptyEl = document.getElementById('search-empty');
            if (books.length === 0) {
                emptyEl?.classList.remove('hidden');
            } else {
                emptyEl?.classList.add('hidden');
            }

            // Render pagination
            if (meta && meta.last_page > 1) {
                const pages = [];
                const current = meta.current_page;
                const last = meta.last_page;

                // Previous button
                if (current > 1) {
                    pages.push(`<button class="px-3 py-1 border rounded hover:bg-gray-50" data-page="${current - 1}">‹</button>`);
                }

                // Page numbers
                for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                    pages.push(`<button class="px-3 py-1 border rounded hover:bg-gray-50 ${i === current ? 'bg-blue-500 text-white' : ''}" data-page="${i}">${i}</button>`);
                }

                // Next button
                if (current < last) {
                    pages.push(`<button class="px-3 py-1 border rounded hover:bg-gray-50" data-page="${current + 1}">›</button>`);
                }

                paginationEl.innerHTML = pages.join('');
                paginationEl.classList.remove('hidden');
            }

            resultsEl.classList.remove('hidden');

            // Add event listeners for favorite and cart buttons
            resultsEl.querySelectorAll('.catalog-fav-btn').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!getToken()) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    const bookId = Number(btn.getAttribute('data-book-id'));
                    try {
                        if (favoriteBookIds.has(bookId)) {
                            await api(`/favorites/${bookId}`, { method: 'DELETE' });
                            favoriteBookIds.delete(bookId);
                            showAlert(alertEl, 'Книга удалена из избранного', 'success');
                        } else {
                            await api('/favorites', { method: 'POST', json: { book_id: bookId } });
                            favoriteBookIds.add(bookId);
                            showAlert(alertEl, 'Книга добавлена в избранное', 'success');
                        }
                        await updateSearchButtons();
                    } catch (err) {
                        const error = /** @type {any} */ (err);
                        if (error.status === 409) {
                            favoriteBookIds.add(bookId);
                            await updateSearchButtons();
                            showAlert(alertEl, 'Книга уже в избранном', 'success');
                        } else if (error.status === 404) {
                            favoriteBookIds.delete(bookId);
                            await updateSearchButtons();
                            showAlert(alertEl, 'Книги уже нет в избранном', 'success');
                        } else {
                            showAlert(alertEl, error.message || 'Ошибка обновления избранного');
                        }
                    }
                });
            });

            resultsEl.querySelectorAll('.catalog-cart-btn').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!getToken()) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    const bookId = Number(btn.getAttribute('data-book-id'));
                    try {
                        if (cartBookIds.has(bookId)) {
                            const itemId = cartItemIdsByBookId.get(bookId);
                            if (!itemId) {
                                await loadUserState();
                            }
                            const actualItemId = cartItemIdsByBookId.get(bookId);
                            if (!actualItemId) {
                                throw new Error('Не удалось определить товар в корзине');
                            }
                            await api(`/cart/items/${actualItemId}`, { method: 'DELETE' });
                            cartBookIds.delete(bookId);
                            cartItemIdsByBookId.delete(bookId);
                            showAlert(alertEl, 'Книга удалена из корзины', 'success');
                        } else {
                            const addRes = await api('/cart/items', { method: 'POST', json: { book_id: bookId, quantity: 1 } });
                            cartBookIds.add(bookId);
                            const itemId = addRes.data?.item?.id;
                            if (itemId) {
                                cartItemIdsByBookId.set(bookId, Number(itemId));
                            }
                            showAlert(alertEl, 'Книга добавлена в корзину', 'success');
                        }
                        void refreshCartBadge();
                        await updateSearchButtons();
                    } catch (err) {
                        const error = /** @type {any} */ (err);
                        if (error.status === 404) {
                            cartBookIds.delete(bookId);
                            cartItemIdsByBookId.delete(bookId);
                            await updateSearchButtons();
                            showAlert(alertEl, 'Книги уже нет в корзине', 'success');
                            void refreshCartBadge();
                        } else {
                            showAlert(alertEl, error.message || 'Ошибка обновления корзины');
                        }
                    }
                });
            });

            // Helper function to update button states on the search page
            async function updateSearchButtons() {
                updateCatalogButtons();
            }

            // Update button states after rendering
            await updateSearchButtons();
        } catch (e) {
            showAlert(alertEl, e instanceof Error ? e.message : 'Ошибка загрузки результатов');
        } finally {
            loadingEl?.classList.add('hidden');
        }
    }

    // Form change handler
    form.addEventListener('change', () => {
        loadResults(1);
    });

    // Search input handler (debounced)
    let searchTimeout;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadResults(1), 300);
    });

    // Pagination handler
    paginationEl?.addEventListener('click', (e) => {
        const button = e.target.closest('button[data-page]');
        if (button) {
            const page = Number(button.dataset.page);
            loadResults(page);
        }
    });

    // Clear filters handler
    const clearBtn = document.getElementById('clear-filters');
    clearBtn?.addEventListener('click', () => {
        // Reset all form fields
        form.reset();
        // Clear URL parameters
        window.history.replaceState({}, '', window.location.pathname);
        // Reload results
        loadResults(1);
    });

    // Initial load
    void loadUserState().then(() => {
        loadResults();
    });
}

export function initShop() {
    initAlertClose();
    initNav();

    const page = document.body.dataset.page;
    switch (page) {
        case 'catalog':
            initCatalog();
            break;
        case 'book':
            initBookPage();
            break;
        case 'login':
            initLogin();
            break;
        case 'register':
            initRegister();
            break;
        case 'forgot-password':
            initForgotPassword();
            break;
        case 'reset-password':
            initResetPassword();
            break;
        case 'settings':
            initSettings();
            break;
        case 'cart':
            initCart();
            break;
        case 'orders':
            initOrders();
            break;
        case 'order':
            initOrderDetail();
            break;
        case 'favorites':
            initFavorites();
            break;
        case 'reviews':
            initReviews();
            break;
        case 'search':
            initSearch();
            break;
        default:
            break;
    }
}
