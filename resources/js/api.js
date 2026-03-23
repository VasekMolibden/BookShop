const TOKEN_KEY = 'bookshop_api_token';

/** @type {string} */
const apiBase = '/api/v1';

/**
 * @returns {string|null}
 */
export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

/**
 * @param {string|null} token
 */
export function setToken(token) {
    if (token) {
        localStorage.setItem(TOKEN_KEY, token);
    } else {
        localStorage.removeItem(TOKEN_KEY);
    }
}

export function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
}

/**
 * @param {string} path
 * @param {RequestInit & { auth?: boolean, json?: Record<string, unknown>|null }} [options]
 * @returns {Promise<any>}
 */
export async function api(path, options = {}) {
    const { auth = true, json, headers: extraHeaders, ...rest } = options;
    /** @type {Record<string, string>} */
    const headers = {
        Accept: 'application/json',
        ...Object.fromEntries(
            Object.entries(extraHeaders ?? {}).filter(([, v]) => v !== undefined).map(([k, v]) => [k, String(v)])
        ),
    };

    if (json !== undefined && json !== null) {
        headers['Content-Type'] = 'application/json';
    }

    const token = getToken();
    if (auth && token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const res = await fetch(`${apiBase}${path}`, {
        ...rest,
        headers,
        body: json !== undefined && json !== null ? JSON.stringify(json) : rest.body,
    });

    const payload = await res.json().catch(() => ({}));

    if (!res.ok) {
        const message =
            payload?.error?.message ??
            payload?.message ??
            res.statusText ??
            'Ошибка запроса';
        const err = new Error(message);
        err.status = res.status;
        err.payload = payload;
        throw err;
    }

    return payload;
}
