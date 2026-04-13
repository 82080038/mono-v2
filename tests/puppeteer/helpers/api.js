const https = require('http');
const querystring = require('querystring');

const BASE_URL = 'http://localhost/mono-v2';
const API_BASE = `${BASE_URL}/api`;

/**
 * POST request to API
 */
function apiPost(endpoint, data) {
    return new Promise((resolve, reject) => {
        const body = querystring.stringify(data);
        const options = {
            hostname: 'localhost',
            path: `/mono-v2/api/${endpoint}`,
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': Buffer.byteLength(body)
            }
        };
        const req = https.request(options, (res) => {
            let raw = '';
            res.on('data', chunk => (raw += chunk));
            res.on('end', () => {
                try {
                    resolve({ status: res.statusCode, body: JSON.parse(raw) });
                } catch (e) {
                    resolve({ status: res.statusCode, body: raw });
                }
            });
        });
        req.on('error', reject);
        req.write(body);
        req.end();
    });
}

/**
 * GET request to API
 */
function apiGet(endpoint, params = {}, token = null) {
    return new Promise((resolve, reject) => {
        const qs = Object.keys(params).length ? '?' + querystring.stringify(params) : '';
        const options = {
            hostname: 'localhost',
            path: `/mono-v2/api/${endpoint}${qs}`,
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {})
            }
        };
        const req = https.request(options, (res) => {
            let raw = '';
            res.on('data', chunk => (raw += chunk));
            res.on('end', () => {
                try {
                    resolve({ status: res.statusCode, body: JSON.parse(raw) });
                } catch (e) {
                    resolve({ status: res.statusCode, body: raw });
                }
            });
        });
        req.on('error', reject);
        req.end();
    });
}

/**
 * Login and get real JWT token
 */
async function getAuthToken(username = 'admin', password = 'password') {
    const res = await apiPost('auth.php', { action: 'login', username, password });
    if (res.body && res.body.success) {
        return res.body.data.user.token;
    }
    throw new Error('Login failed: ' + (res.body?.message || 'unknown'));
}

module.exports = { apiPost, apiGet, getAuthToken, BASE_URL, API_BASE };
