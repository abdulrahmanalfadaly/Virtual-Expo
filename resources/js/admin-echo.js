import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const host = import.meta.env.VITE_REVERB_HOST;
const key = import.meta.env.VITE_REVERB_APP_KEY;
const isLocalHost = ! host || host === 'localhost' || host === '127.0.0.1';

if (key && ! isLocalHost) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: host,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
