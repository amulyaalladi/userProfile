// Base URL of the PHP backend.
//  - Local (XAMPP / php -S): same origin, so it stays empty.
//  - Production: set PRODUCTION_API to your Render backend URL (keep the trailing slash).
const PRODUCTION_API = 'https://YOUR-BACKEND.onrender.com/';

const API_BASE = ['localhost', '127.0.0.1'].includes(window.location.hostname)
    ? ''
    : PRODUCTION_API;
