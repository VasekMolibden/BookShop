import { api, getToken } from './api';

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
 */
function showAdminAlert(el, message) {
    if (!el) {
        return;
    }
    el.textContent = message;
    el.classList.remove('hidden');
}

/**
 * @param {HTMLElement|null} el
 */
function hideAdminAlert(el) {
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
            'rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50';
        b.disabled = disabled;
        b.addEventListener('click', () => onPage(page));
        return b;
    };

    container.append(
        mkBtn('Назад', pagination.current_page - 1, pagination.current_page <= 1),
        mkBtn('Вперёд', pagination.current_page + 1, pagination.current_page >= pagination.last_page)
    );
}

/**
 * @returns {Promise<boolean>}
 */
async function assertAdminAccess() {
    const gate = document.getElementById('admin-gate');
    const content = document.getElementById('admin-content');

    if (!getToken()) {
        gate?.classList.remove('hidden');
        content?.classList.add('hidden');
        return false;
    }

    try {
        const res = await api('/auth/me');
        const user = res.data?.user;
        if (!user?.is_admin) {
            gate?.classList.remove('hidden');
            content?.classList.add('hidden');
            return false;
        }
    } catch {
        gate?.classList.remove('hidden');
        content?.classList.add('hidden');
        return false;
    }

    gate?.classList.add('hidden');
    content?.classList.remove('hidden');
    return true;
}

function initAdminGenres() {
    const tbody = document.getElementById('genres-tbody');
    const loading = document.getElementById('genres-loading');
    const form = document.getElementById('genre-form');
    const idInput = document.getElementById('genre-id');
    const nameInput = document.getElementById('genre-name');
    const slugInput = document.getElementById('genre-slug');
    const submitBtn = document.getElementById('genre-submit');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !form) {
        return;
    }

    async function loadTable() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            const res = await api('/genres', { auth: false });
            const genres = res.data?.genres ?? [];
            tbody.innerHTML = '';
            for (const g of genres) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100';
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs text-slate-500">${g.id}</td>
                    <td class="px-4 py-2">${escapeHtml(g.name)}</td>
                    <td class="px-4 py-2 font-mono text-xs">${escapeHtml(g.slug)}</td>
                    <td class="px-4 py-2 text-right">
                        <button type="button" class="text-violet-700 hover:underline" data-edit="${g.id}">Изменить</button>
                        <button type="button" class="ml-2 text-red-700 hover:underline" data-del="${g.id}">Удалить</button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            tbody.querySelectorAll('[data-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = Number(btn.getAttribute('data-edit'));
                    const genre = genres.find((x) => x.id === id);
                    if (genre && idInput && nameInput && slugInput && submitBtn) {
                        idInput.value = String(genre.id);
                        nameInput.value = genre.name;
                        slugInput.value = genre.slug;
                        submitBtn.textContent = 'Обновить';
                    }
                });
            });

            tbody.querySelectorAll('[data-del]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-del');
                    if (!confirm('Удалить жанр?')) {
                        return;
                    }
                    try {
                        await api(`/admin/genres/${id}`, { method: 'DELETE' });
                        await loadTable();
                    } catch (e) {
                        const err = /** @type {any} */ (e);
                        const msg =
                            err.payload?.error?.errors?.genre?.[0] ?? err.message ?? 'Ошибка удаления';
                        showAdminAlert(alertEl, msg);
                    }
                });
            });
        } catch (e) {
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка загрузки');
        }
        loading?.classList.add('hidden');
    }

    document.getElementById('genre-reset')?.addEventListener('click', () => {
        if (idInput) {
            idInput.value = '';
        }
        if (nameInput) {
            nameInput.value = '';
        }
        if (slugInput) {
            slugInput.value = '';
        }
        if (submitBtn) {
            submitBtn.textContent = 'Сохранить';
        }
    });

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAdminAlert(alertEl);
        const id = idInput?.value?.trim();
        const payload = {
            name: nameInput?.value?.trim() ?? '',
            slug: slugInput?.value?.trim() ?? '',
        };
        try {
            if (id) {
                await api(`/admin/genres/${id}`, { method: 'PATCH', json: payload });
            } else {
                await api('/admin/genres', { method: 'POST', json: payload });
            }
            document.getElementById('genre-reset')?.click();
            await loadTable();
        } catch (e) {
            const err = /** @type {any} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAdminAlert(alertEl, msg);
        }
    });

    void loadTable();
}

function initAdminBooks() {
    const tbody = document.getElementById('books-tbody');
    const loading = document.getElementById('books-loading');
    const pagEl = document.getElementById('books-pagination');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !pagEl) {
        return;
    }

    let page = 1;

    async function load() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            const res = await api(`/books?page=${page}&per_page=15`, { auth: false });
            const books = res.data?.books ?? [];
            const pagination = res.data?.pagination;
            tbody.innerHTML = '';
            for (const b of books) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100';
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs">${b.id}</td>
                    <td class="px-4 py-2">${escapeHtml(b.title)}</td>
                    <td class="px-4 py-2">${escapeHtml(b.author)}</td>
                    <td class="px-4 py-2">${formatPrice(b.price)}</td>
                    <td class="px-4 py-2">${b.stock}</td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="/admin/books/${b.id}/edit" class="text-violet-700 hover:underline">Изменить</a>
                        <button type="button" class="ml-2 text-red-700 hover:underline" data-del="${b.id}">Удалить</button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            tbody.querySelectorAll('[data-del]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-del');
                    if (!confirm('Удалить книгу и связанные данные?')) {
                        return;
                    }
                    try {
                        await api(`/admin/books/${id}`, { method: 'DELETE' });
                        await load();
                    } catch (e) {
                        showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
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
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
        loading?.classList.add('hidden');
    }

    void load();
}

const ORDER_STATUSES = [
    { value: 'pending', label: 'Ожидает' },
    { value: 'paid', label: 'Оплачен' },
    { value: 'shipped', label: 'Отправлен' },
    { value: 'cancelled', label: 'Отменён' },
];

function initAdminOrders() {
    const tbody = document.getElementById('admin-orders-tbody');
    const loading = document.getElementById('orders-loading');
    const pagEl = document.getElementById('admin-orders-pagination');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !pagEl) {
        return;
    }

    let page = 1;

    async function load() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            const res = await api(`/admin/orders?page=${page}&per_page=15`);
            const orders = res.data?.orders ?? [];
            const pagination = res.data?.pagination;

            tbody.innerHTML = '';
            for (const o of orders) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100';
                const user = o.user;
                const who = user ? `${escapeHtml(user.name)}<br><span class="text-xs text-slate-500">${escapeHtml(user.email)}</span>` : '—';
                const opts = ORDER_STATUSES.map(
                    (s) =>
                        `<option value="${s.value}" ${s.value === o.status ? 'selected' : ''}>${s.label}</option>`
                ).join('');
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono">${o.id}</td>
                    <td class="px-4 py-2 text-sm">${who}</td>
                    <td class="px-4 py-2 text-sm text-slate-600">${formatDateTime(o.created_at)}</td>
                    <td class="px-4 py-2">${formatPrice(o.total)}</td>
                    <td class="px-4 py-2">
                        <select data-order="${o.id}" class="rounded border border-slate-300 px-2 py-1 text-sm">${opts}</select>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            tbody.querySelectorAll('select[data-order]').forEach((sel) => {
                sel.addEventListener('change', async () => {
                    const id = sel.getAttribute('data-order');
                    const status = sel.value;
                    try {
                        await api(`/admin/orders/${id}`, { method: 'PATCH', json: { status } });
                    } catch (e) {
                        showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
                        await load();
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
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
        loading?.classList.add('hidden');
    }

    void load();
}
function initAdminAuthors() {
    const tbody = document.getElementById('authors-tbody');
    const loading = document.getElementById('authors-loading');
    const form = document.getElementById('author-form');
    const idInput = document.getElementById('author-id');
    const nameInput = document.getElementById('author-name');
    const slugInput = document.getElementById('author-slug');
    const bioInput = document.getElementById('author-bio');
    const submitBtn = document.getElementById('author-submit');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !form) {
        return;
    }

    async function loadTable() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            const res = await api('/authors', { auth: false });
            const authors = res.data?.authors ?? [];
            tbody.innerHTML = '';
            for (const a of authors) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100';
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs text-slate-500">${a.id}</td>
                    <td class="px-4 py-2">${escapeHtml(a.name)}</td>
                    <td class="px-4 py-2 font-mono text-xs">${escapeHtml(a.slug)}</td>
                    <td class="px-4 py-2">${a.books_count || 0}</td>
                    <td class="px-4 py-2 text-right">
                        <button type="button" class="text-violet-700 hover:underline" data-edit="${a.id}">Изменить</button>
                        <button type="button" class="ml-2 text-red-700 hover:underline" data-del="${a.id}">Удалить</button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            tbody.querySelectorAll('[data-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = Number(btn.getAttribute('data-edit'));
                    const author = authors.find((x) => x.id === id);
                    if (author && idInput && nameInput && slugInput && bioInput && submitBtn) {
                        idInput.value = String(author.id);
                        nameInput.value = author.name;
                        slugInput.value = author.slug;
                        bioInput.value = author.bio || '';
                        submitBtn.textContent = 'Обновить';
                    }
                });
            });

            tbody.querySelectorAll('[data-del]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-del');
                    if (!confirm('Удалить автора?')) {
                        return;
                    }
                    try {
                        await api(`/admin/authors/${id}`, { method: 'DELETE' });
                        await loadTable();
                    } catch (e) {
                        const err = /** @type {any} */ (e);
                        const msg =
                            err.payload?.error?.errors?.author?.[0] ?? err.message ?? 'Ошибка удаления';
                        showAdminAlert(alertEl, msg);
                    }
                });
            });
        } catch (e) {
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка загрузки');
        }
        loading?.classList.add('hidden');
    }

    document.getElementById('author-reset')?.addEventListener('click', () => {
        if (idInput) {
            idInput.value = '';
        }
        if (nameInput) {
            nameInput.value = '';
        }
        if (slugInput) {
            slugInput.value = '';
        }
        if (bioInput) {
            bioInput.value = '';
        }
        if (submitBtn) {
            submitBtn.textContent = 'Сохранить';
        }
    });

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAdminAlert(alertEl);
        const id = idInput?.value?.trim();
        const payload = {
            name: nameInput?.value?.trim() ?? '',
            slug: slugInput?.value?.trim() ?? '',
            bio: bioInput?.value?.trim() ?? '',
        };
        try {
            if (id) {
                await api(`/admin/authors/${id}`, { method: 'PATCH', json: payload });
            } else {
                await api('/admin/authors', { method: 'POST', json: payload });
            }
            document.getElementById('author-reset')?.click();
            await loadTable();
        } catch (e) {
            const err = /** @type {any} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAdminAlert(alertEl, msg);
        }
    });

    void loadTable();
}
function initAdminReviews() {
    const tbody = document.getElementById('admin-reviews-tbody');
    const loading = document.getElementById('reviews-loading');
    const pagEl = document.getElementById('admin-reviews-pagination');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !pagEl) {
        return;
    }

    let page = 1;

    async function load() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            const res = await api(`/admin/reviews?page=${page}&per_page=15`);
            const reviews = res.data?.reviews ?? [];
            const pagination = res.data?.pagination;

            tbody.innerHTML = '';
            for (const r of reviews) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100 align-top';
                const bookTitle = r.book?.title ? escapeHtml(r.book.title) : '—';
                const userName = r.user?.name ? escapeHtml(r.user.name) : '—';
                const excerpt = (r.comment ?? '').slice(0, 120);
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs">${r.id}</td>
                    <td class="px-4 py-2 max-w-[10rem] text-sm">${bookTitle}</td>
                    <td class="px-4 py-2 text-sm">${userName}</td>
                    <td class="px-4 py-2">${r.rating}</td>
                    <td class="px-4 py-2 text-sm text-slate-600 max-w-md">${escapeHtml(excerpt)}${(r.comment ?? '').length > 120 ? '…' : ''}</td>
                    <td class="px-4 py-2">
                        <button type="button" class="text-red-700 hover:underline" data-del="${r.id}">Удалить</button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            tbody.querySelectorAll('[data-del]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-del');
                    if (!confirm('Удалить отзыв?')) {
                        return;
                    }
                    try {
                        await api(`/admin/reviews/${id}`, { method: 'DELETE' });
                        await load();
                    } catch (e) {
                        showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
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
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
        loading?.classList.add('hidden');
    }

    void load();
}

function initAdminBookForm() {
    const bookId = document.body.dataset.bookId;
    const form = document.getElementById('book-form');
    const genresWrap = document.getElementById('bf-genres');
    const alertEl = document.getElementById('admin-alert');

    if (!form || !genresWrap) {
        return;
    }

    const isEdit = Boolean(bookId);

    async function loadGenresCheckboxes(selectedIds = []) {
        const res = await api('/genres', { auth: false });
        const genres = res.data?.genres ?? [];
        genresWrap.innerHTML = '';
        for (const g of genres) {
            const id = `bf-g-${g.id}`;
            const label = document.createElement('label');
            label.className = 'flex items-center gap-2 text-sm';
            const checked = selectedIds.includes(g.id) ? 'checked' : '';
            label.innerHTML = `<input type="checkbox" name="genre" value="${g.id}" id="${id}" ${checked} class="rounded border-slate-300 text-violet-700 focus:ring-violet-500"> <span>${escapeHtml(g.name)}</span>`;
            genresWrap.appendChild(label);
        }
    }

    async function loadAuthorsSelect(selectedId = null) {
        const authorSelect = document.getElementById('bf-author');
        if (!authorSelect) {
            return;
        }
        const res = await api('/authors', { auth: false });
        const authors = res.data?.authors ?? [];
        authorSelect.innerHTML = '<option value="">Выберите автора</option>';
        for (const a of authors) {
            const option = document.createElement('option');
            option.value = String(a.id);
            option.textContent = a.name;
            if (selectedId && a.id === selectedId) {
                option.selected = true;
            }
            authorSelect.appendChild(option);
        }
    }

    async function fillIfEdit() {
        if (!isEdit) {
            await loadGenresCheckboxes([]);
            await loadAuthorsSelect();
            return;
        }
        try {
            const res = await api(`/books/${bookId}`, { auth: false });
            const b = res.data?.book;
            if (!b) {
                throw new Error('Книга не найдена');
            }
            /** @type {HTMLInputElement|null} */
            const title = document.getElementById('bf-title');
            const slug = document.getElementById('bf-slug');
            const author = document.getElementById('bf-author');
            const desc = document.getElementById('bf-description');
            const price = document.getElementById('bf-price');
            const isbn = document.getElementById('bf-isbn');
            const stock = document.getElementById('bf-stock');
            const published = document.getElementById('bf-published');
            if (title) {
                title.value = b.title ?? '';
            }
            if (slug) {
                slug.value = b.slug ?? '';
            }
            if (author) {
                await loadAuthorsSelect(b.author_id);
            }
            if (desc) {
                desc.value = b.description ?? '';
            }
            if (price) {
                price.value = String(b.price ?? '');
            }
            if (isbn) {
                isbn.value = b.isbn ?? '';
            }
            if (stock) {
                stock.value = String(b.stock ?? '');
            }
            if (published && b.published_at) {
                published.value = b.published_at.slice(0, 10);
            }
            const gIds = (b.genres ?? []).map((g) => g.id);
            await loadGenresCheckboxes(gIds);
        } catch (e) {
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
    }

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        hideAdminAlert(alertEl);
        const genreIds = [...form.querySelectorAll('input[name="genre"]:checked')].map((el) =>
            Number(/** @type {HTMLInputElement} */ (el).value)
        );
        if (!genreIds.length) {
            showAdminAlert(alertEl, 'Выберите хотя бы один жанр.');
            return;
        }

        const slugVal = document.getElementById('bf-slug')?.value?.trim();
        /** @type {Record<string, unknown>} */
        const payload = {
            title: document.getElementById('bf-title')?.value?.trim(),
            author_id: Number(document.getElementById('bf-author')?.value),
            description: document.getElementById('bf-description')?.value?.trim() || null,
            price: Number(document.getElementById('bf-price')?.value),
            stock: Number(document.getElementById('bf-stock')?.value),
            genre_ids: genreIds,
        };

        const isbnRaw = document.getElementById('bf-isbn')?.value?.trim();
        payload.isbn = isbnRaw === '' ? null : isbnRaw;

        const pub = document.getElementById('bf-published')?.value;
        payload.published_at = pub || null;

        if (slugVal) {
            payload.slug = slugVal;
        } else if (!isEdit) {
            payload.slug = null;
        }
        /* при редактировании пустой slug не отправляем — slug книги не меняем */

        try {
            if (isEdit) {
                await api(`/admin/books/${bookId}`, { method: 'PATCH', json: payload });
            } else {
                await api('/admin/books', { method: 'POST', json: payload });
            }
            window.location.href = '/admin/books';
        } catch (e) {
            const err = /** @type {any} */ (e);
            const errors = err.payload?.error?.errors;
            let msg = err.message;
            if (errors && typeof errors === 'object') {
                msg = Object.values(errors)
                    .flat()
                    .join(' ');
            }
            showAdminAlert(alertEl, msg);
        }
    });

    // === Управление картинками (только для редактирования) ===
    if (isEdit) {
        const imagesSection = document.getElementById('images-section');
        const fileInput = document.getElementById('image-file-input');
        const uploadZone = document.getElementById('image-upload-zone');
        const altTextInput = document.getElementById('image-alt-text');
        const uploadBtn = document.getElementById('upload-image-btn');
        const imagesList = document.getElementById('images-list');
        const imageAlert = document.getElementById('image-alert');

        if (uploadZone && fileInput) {
            // Клик по зоне
            uploadZone.addEventListener('click', () => fileInput.click());

            // Drag and drop
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('bg-slate-50', 'border-violet-300');
            });
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('bg-slate-50', 'border-violet-300');
            });
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('bg-slate-50', 'border-violet-300');
                if (e.dataTransfer?.files?.[0]) {
                    fileInput.files = e.dataTransfer.files;
                }
            });
        }

        async function showImageAlert(message, isError = true) {
            if (!imageAlert) return;
            imageAlert.textContent = message;
            imageAlert.classList.remove('hidden');
            if (isError) {
                imageAlert.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                imageAlert.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
            } else {
                imageAlert.classList.remove('border-red-200', 'bg-red-50', 'text-red-800');
                imageAlert.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
            }
            setTimeout(() => imageAlert.classList.add('hidden'), 4000);
        }

        async function loadImagesList() {
            try {
                const res = await api(`/books/${bookId}`, { auth: false });
                const images = res.data?.book?.images ?? [];
                
                if (!imagesList) return;
                imagesList.innerHTML = '';

                if (images.length === 0) {
                    imagesList.innerHTML = '<p class="text-center text-slate-500 py-8">Нет загруженных картинок</p>';
                    return;
                }

                for (const img of images) {
                    const div = document.createElement('div');
                    div.className = 'flex gap-4 rounded-lg border border-slate-200 p-4';
                    div.innerHTML = `
                        <div class="flex-shrink-0">
                            <img src="${img.url}" alt="${escapeHtml(img.alt_text || 'Image')}" class="h-24 w-24 object-cover rounded">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 text-sm">
                                    <input 
                                        type="text" 
                                        value="${escapeHtml(img.alt_text || '')}" 
                                        placeholder="Альтернативный текст"
                                        class="img-alt-text w-full rounded border border-slate-300 px-2 py-1 text-sm mb-2"
                                        data-image-id="${img.id}"
                                    >
                                    <label class="flex items-center gap-2 text-slate-600">
                                        <input type="checkbox" class="img-is-primary rounded" data-image-id="${img.id}" ${img.is_primary ? 'checked' : ''} />
                                        <span>Основная картинка</span>
                                    </label>
                                </div>
                                <button 
                                    type="button"
                                    class="img-delete-btn text-red-600 hover:text-red-700 text-sm font-medium"
                                    data-image-id="${img.id}"
                                >
                                    ✕ Удалить
                                </button>
                            </div>
                        </div>
                    `;
                    imagesList.appendChild(div);
                }

                // Обработчики для обновления картинок
                imagesList.querySelectorAll('.img-alt-text').forEach(el => {
                    el.addEventListener('change', async (e) => {
                        const imageId = Number(e.target.dataset.imageId);
                        try {
                            await api(`/admin/books/${bookId}/images/${imageId}`, {
                                method: 'PATCH',
                                json: { alt_text: e.target.value }
                            });
                            showImageAlert('Альтернативный текст обновлён', false);
                        } catch (err) {
                            showImageAlert('Ошибка обновления');
                            await loadImagesList();
                        }
                    });
                });

                imagesList.querySelectorAll('.img-is-primary').forEach(el => {
                    el.addEventListener('change', async (e) => {
                        const imageId = Number(e.target.dataset.imageId);
                        try {
                            await api(`/admin/books/${bookId}/images/${imageId}`, {
                                method: 'PATCH',
                                json: { is_primary: e.target.checked }
                            });
                            showImageAlert('Основная картинка обновлена', false);
                            await loadImagesList();
                        } catch (err) {
                            showImageAlert('Ошибка обновления');
                            await loadImagesList();
                        }
                    });
                });

                imagesList.querySelectorAll('.img-delete-btn').forEach(el => {
                    el.addEventListener('click', async (e) => {
                        const imageId = Number(e.target.dataset.imageId);
                        if (!confirm('Удалить эту картинку?')) return;
                        try {
                            await api(`/admin/books/${bookId}/images/${imageId}`, { method: 'DELETE' });
                            showImageAlert('Картинка удалена', false);
                            await loadImagesList();
                        } catch (err) {
                            showImageAlert('Ошибка удаления');
                        }
                    });
                });
            } catch (e) {
                showImageAlert('Ошибка загрузки картинок');
            }
        }

        if (uploadBtn) {
            uploadBtn.addEventListener('click', async () => {
                if (!fileInput?.files?.[0]) {
                    showImageAlert('Выберите файл');
                    return;
                }

                const file = fileInput.files[0];
                const altText = altTextInput?.value?.trim() || '';

                uploadBtn.disabled = true;
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    if (altText) {
                        formData.append('alt_text', altText);
                    }

                    await api(`/admin/books/${bookId}/images`, {
                        method: 'POST',
                        body: formData,
                    });

                    showImageAlert('Картинка загружена успешно', false);
                    fileInput.value = '';
                    if (altTextInput) altTextInput.value = '';
                    await loadImagesList();
                } catch (err) {
                    const errMsg = err instanceof Error ? err.message : 'Ошибка загрузки';
                    showImageAlert(errMsg);
                } finally {
                    uploadBtn.disabled = false;
                }
            });
        }

        // Загрузить список картинок при инициализации
        void loadImagesList();
    }

    void fillIfEdit();
}

function initAdminUsers() {
    const tbody = document.getElementById('admin-users-tbody');
    const loading = document.getElementById('users-loading');
    const pagEl = document.getElementById('admin-users-pagination');
    const alertEl = document.getElementById('admin-alert');

    if (!tbody || !pagEl) {
        return;
    }

    let page = 1;

    async function load() {
        hideAdminAlert(alertEl);
        loading?.classList.remove('hidden');
        try {
            let meId = null;
            try {
                const me = await api('/auth/me');
                meId = me.data?.user?.id ?? null;
            } catch {
                /* ignore */
            }

            const res = await api(`/admin/users?page=${page}&per_page=15`);
            const users = res.data?.users ?? [];
            const pagination = res.data?.pagination;

            tbody.innerHTML = '';
            for (const u of users) {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100';
                const you = meId != null && u.id === meId ? ' <span class="text-xs text-slate-400">(вы)</span>' : '';
                const cbId = `admin-user-${u.id}`;
                tr.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs">${u.id}</td>
                    <td class="px-4 py-2">${escapeHtml(u.name)}${you}</td>
                    <td class="px-4 py-2 text-slate-700">${escapeHtml(u.email)}</td>
                    <td class="px-4 py-2 text-slate-600">${formatDateTime(u.created_at)}</td>
                    <td class="px-4 py-2">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="checkbox" id="${cbId}" class="rounded border-slate-300 text-violet-700 focus:ring-violet-500" data-user-id="${u.id}" ${u.is_admin ? 'checked' : ''}>
                            <span class="text-slate-600">Да</span>
                        </label>
                    </td>
                `;
                tbody.appendChild(tr);

                const cb = tr.querySelector(`#${cbId}`);
                if (cb instanceof HTMLInputElement) {
                    cb.addEventListener('change', async () => {
                        const wantAdmin = cb.checked;
                        const prev = !wantAdmin;
                        try {
                            await api(`/admin/users/${u.id}`, {
                                method: 'PATCH',
                                json: { is_admin: wantAdmin },
                            });
                        } catch (e) {
                            cb.checked = prev;
                            const err = /** @type {any} */ (e);
                            const msg =
                                err.payload?.error?.errors?.is_admin?.[0] ?? err.message ?? 'Ошибка';
                            showAdminAlert(alertEl, msg);
                        }
                    });
                }
            }

            if (pagination) {
                renderPagination(pagEl, pagination, (p) => {
                    page = p;
                    void load();
                });
            }
        } catch (e) {
            showAdminAlert(alertEl, e instanceof Error ? e.message : 'Ошибка');
        }
        loading?.classList.add('hidden');
    }

    void load();
}

export async function initAdmin() {
    if (!document.body.dataset.admin) {
        return;
    }

    const ok = await assertAdminAccess();
    if (!ok) {
        return;
    }

    const page = document.body.dataset.page;
    switch (page) {
        case 'admin-genres':
            initAdminGenres();
            break;
        case 'admin-authors':
            initAdminAuthors();
            break;
        case 'admin-books':
            initAdminBooks();
            break;
        case 'admin-book-form':
            initAdminBookForm();
            break;
        case 'admin-orders':
            initAdminOrders();
            break;
        case 'admin-reviews':
            initAdminReviews();
            break;
        case 'admin-users':
            initAdminUsers();
            break;
        default:
            break;
    }
}
