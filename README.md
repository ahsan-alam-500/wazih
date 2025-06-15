# 📦 like_bponi - Smart Order Management System

A modern, secure, and interactive order management system built with **Laravel 10**, **Vue 3**, **Inertia.js**, and **Tailwind CSS**. Featuring real-time order notifications via **Laravel Reverb**, trust level checks using a **fraud detection API**, dynamic invoices, and a beautiful dark mode UI.

---

## 🚀 Features

- ✅ **Customer Order Creation** with validation
- 🧾 **Invoice Generation & Download**
- 🔔 **Real-Time Notifications** (via Laravel Reverb & Pusher)
- 🔍 **Fraud Detection System** using mobile number history
- 🌗 **Fully Dark Mode Supported**
- 📊 **Trust Level Insights** (VIP, Fresh, Fraud)
- 🔄 **Order Update with Activity Logging**
- 🧩 Modular Vue 3 Components with TypeScript
- 🔐 Auth-protected routes using Laravel Sanctum

---

## 🧱 Tech Stack

| Frontend | Backend | Realtime | Styling | API |
|----------|---------|----------|---------|-----|
| Vue 3 + TypeScript | Laravel 10 | Laravel Reverb (WebSocket) | Tailwind CSS | FraudBD |

---

## 🖼️ Screenshots

| 🧾 Invoice | 📊 Trust Check | 🔔 Live Order |
|-----------|----------------|---------------|
| ![Invoice](docs/invoice.png) | ![Trust](docs/trust-check.png) | ![Live](docs/realtime.png) |

---

## ⚙️ Installation

### 1. Clone the Repo

```bash
git clone https://github.com/yourname/like_bponi.git
cd like_bponi
```

### 2. Backend Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### 3. Frontend Setup

```bash
npm install
npm run dev
```

### 4. Run Laravel Reverb Server (WebSocket)

```bash
php artisan reverb:start
```

---

## 📡 WebSocket & Notification Setup

In `.env`:

```
REVERB_APP_KEY=your-key
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```

Vue3 uses:

```ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
```

Listen to events in Vue:

```ts
window.Echo.channel('orders')
  .listen('.order.placed', (e) => {
    notify({ title: 'New Order', text: `Order by ${e.order.user.name}` });
  });
```

---

## 📁 Folder Structure Highlights

```
resources/
  └── js/
      ├── layouts/
      ├── pages/
      │   └── Orders/
      ├── partials/
      └── composables/
```

---

## ✨ Contribution

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes
4. Push and create a PR

---

## 🛡️ License

MIT License © 2025 [Your Name or Company]
