/* ============================================================
   FitZone — API wrapper
   Chuẩn hóa lỗi 401/403/422.
   ============================================================ */
(function () {
    'use strict';

    const config = {
        base: (window.APP_BASE || '') + '/index.php'
    };

    async function request(method, path, body, options) {
        const opts = options || {};
        const headers = Object.assign({ Accept: 'application/json' }, opts.headers || {});
        let payload;

        if (body !== undefined && body !== null) {
            if (body instanceof FormData) {
                payload = body;
            } else {
                headers['Content-Type'] = 'application/json';
                payload = JSON.stringify(body);
            }
        }

        const response = await fetch(config.base + path, {
            method: method,
            headers: headers,
            body: payload,
            credentials: 'same-origin'
        });

        let json = null;
        try {
            json = await response.json();
        } catch (error) {
            json = { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
        }

        if (response.status === 401) {
            window.dispatchEvent(new CustomEvent('api:unauthorized', { detail: json }));
            throw Object.assign(new Error(json.message || 'Chưa đăng nhập.'), { status: 401, data: json });
        }

        if (response.status === 403) {
            window.dispatchEvent(new CustomEvent('api:forbidden', { detail: json }));
        }

        if (!response.ok) {
            throw Object.assign(new Error(json.message || 'Yêu cầu thất bại.'), { status: response.status, data: json });
        }

        return json;
    }

    window.api = {
        config: config,
        get: function (path, params) {
            let url = path;
            if (params) {
                const query = new URLSearchParams();
                Object.keys(params).forEach(function (key) {
                    if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                        query.append(key, params[key]);
                    }
                });
                const qs = query.toString();
                if (qs) url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
            }
            return request('GET', url);
        },
        post: function (path, body) { return request('POST', path, body || {}); },
        put: function (path, body) { return request('PUT', path, body || {}); },
        delete: function (path) { return request('DELETE', path); }
    };
})();
