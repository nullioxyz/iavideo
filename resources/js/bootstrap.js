import axios from 'axios';
import Echo from 'laravel-echo';
import { io } from 'socket.io-client';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.io = io;

function buildEcho(jwtToken = null) {
    if (import.meta.env.VITE_WS_ENABLED === 'false') {
        return null;
    }

    const protocol = import.meta.env.VITE_WS_SCHEME || window.location.protocol.replace(':', '');
    const host = import.meta.env.VITE_WS_HOST || window.location.hostname;
    const port = import.meta.env.VITE_WS_PORT || '6001';
    const authEndpoint = import.meta.env.VITE_WS_AUTH_ENDPOINT || '/api/broadcasting/auth';

    const headers = jwtToken
        ? { Authorization: `Bearer ${jwtToken}` }
        : {};

    return new Echo({
        broadcaster: 'socket.io',
        host: `${protocol}://${host}:${port}`,
        authEndpoint,
        auth: {
            headers,
        },
    });
}

window.initEcho = (jwtToken = null) => {
    if (window.Echo) {
        window.Echo.disconnect();
    }

    window.Echo = buildEcho(jwtToken);

    return window.Echo;
};

window.setRealtimeToken = (jwtToken) => window.initEcho(jwtToken);

window.initEcho(localStorage.getItem('access_token'));
