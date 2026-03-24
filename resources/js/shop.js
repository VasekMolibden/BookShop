import { api, clearToken, getToken, setToken } from './api';
import { initBookCarousel } from './carousel';

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
 * @param {HTMLElement|null} el
 * @param {string} message
 * @param {'error'|'success'} [variant]
 */
function showAlert(el, message, variant = 'error') {
    if (!el) {
        return;
    }
    el.textContent = message;
    el.classList.remove('hidden');
    if (variant === 'success') {
        el.classList.remove('border-red-200', 'bg-red-50', 'text-red-800');
        el.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
    } else {
        el.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
    }
}

/**
 * @param {HTMLElement|null} el
 */
function hideAlert(el) {
    el?.classList.add('hidden');
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
    const loginEl = document.getElementById('shop-nav-login');
    const registerEl = document.getElementById('shop-nav-register');
    const logoutEl = document.getElementById('shop-nav-logout');

    if (!userEl || !loginEl || !registerEl || !logoutEl) {
        return;
    }

    const token = getToken();
    if (!token) {
        userEl.classList.add('hidden');
        logoutEl.classList.add('hidden');
        loginEl.classList.remove('hidden');
        registerEl.classList.remove('hidden');
        userEl.textContent = '';
        document.getElementById('shop-nav-settings')?.classList.add('hidden');
        document.querySelectorAll('[data-auth-only]').forEach(el => {
            el.classList.add('hidden');
        });
        return;
    }

    try {
        const res = await api('/auth/me');
        const user = res.data?.user;
        if (user?.name) {
            userEl.textContent = user.name;
            userEl.classList.remove('hidden');
            logoutEl.classList.remove('hidden');
            loginEl.classList.add('hidden');
            registerEl.classList.add('hidden');
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
    } catch {
        clearToken();
        userEl.classList.add('hidden');
        logoutEl.classList.add('hidden');
        loginEl.classList.remove('hidden');
        registerEl.classList.remove('hidden');
        document.getElementById('shop-nav-settings')?.classList.add('hidden');
        document.querySelectorAll('[data-auth-only]').forEach(el => {
            el.classList.add('hidden');
        });
    }
}

function initNav() {
    void refreshNav();

    document.getElementById('shop-nav-logout')?.addEventListener('click', async () => {
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
                    ? `<img src="${b.primary_image.url}" alt="${escapeHtml(b.primary_image.alt_text || b.title)}" class="w-full h-48 object-cover">`
                    : `<div class="w-full h-48 bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center text-4xl">📖</div>`;
                
                li.innerHTML = `
                    <a href="/books/${b.id}" class="block">
                        ${imageHtml}
                        <div class="p-4">
                            <h2 class="font-semibold text-stone-900 line-clamp-2">${escapeHtml(b.title)}</h2>
                            <p class="mt-1 text-sm text-stone-600">${escapeHtml(b.author)}</p>
                            <p class="mt-3 text-lg font-medium text-amber-900">${formatPrice(b.price)}</p>
                            <div class="mt-2 flex flex-wrap gap-1">${genres}</div>
                        </div>
                    </a>
                `;
                list.appendChild(li);
            }

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

    void loadGenres().then(() => loadBooks());

    applyBtn?.addEventListener('click', () => {
        page = 1;
        void loadBooks();
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
        const qtyInput = document.getElementById('book-qty');
        const qty = Math.max(1, Number(qtyInput?.value) || 1);
        try {
            await api('/cart/items', {
                method: 'POST',
                json: { book_id: Number(bookId), quantity: qty },
            });
            showAlert(alertEl, 'Добавлено в корзину.', 'success');
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
            await api('/orders/checkout', { method: 'POST', json: {} });
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
                li.innerHTML = `
                    <a href="/orders/${o.id}" class="font-medium text-amber-900 hover:underline">Заказ №${o.id}</a>
                    <span class="ml-2 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700">${escapeHtml(o.status)}</span>
                    <p class="mt-1 text-sm text-stone-600">${formatDateTime(o.created_at)}</p>
                    <p class="mt-2 font-semibold text-stone-900">${formatPrice(o.total)}</p>
                `;
                list.appendChild(li);
            }

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
                    email: String(fd.get('email') ?? ''),
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

export function initShop() {
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
        default:
            break;
    }
}
