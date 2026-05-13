# Modern Stack Baseline

PasPapan tidak mengadopsi stack eksternal berbayar sebagai dependency inti. Fokus modernisasi runtime adalah Laravel-first:

- Laravel `13.x` untuk backend, routing, queue, policy/gate, notification, dan operational commands.
- Livewire `4.x` untuk UI interaktif tanpa rewrite ke React/Next.js.
- Tailwind CSS `4.x` dengan CSS-first configuration di `resources/css/app.css`.
- Tailwind Vite plugin `@tailwindcss/vite`; konfigurasi style pusat ada di `resources/css/app.css`, tanpa config JS/CJS/MJS terpisah dan tanpa direct `postcss` atau direct `autoprefixer`.
- Capacitor `8.x` untuk wrapper Android/APK.
- PHP `8.3+` sebagai minimum, PHP `8.4` direkomendasikan.
- Node.js `20+` dan Bun `1.3.6+` untuk frontend/tooling.

## Guard

Jalankan:

```bash
composer check:modern-stack
```

Command ini gagal jika menemukan jejak stack lama di file tracked yang relevan, termasuk:

- wording major version lama untuk framework, UI layer, CSS tooling, Capacitor, PHP, Bun, atau Node.js;
- config Tailwind/PostCSS lama berbasis file JS/CJS/MJS;
- direct dependency `postcss` atau `autoprefixer`;
- directive Tailwind lama berbasis `@tailwind`;
- routing/directives Livewire lama seperti full-page component via `Route::get(Component::class)`, immediate model modifier lama, scroll directive lama, transition modifiers lama, Volt import, atau legacy emit/browser-event API.

## Prinsip Adopsi Teknologi

Teknologi eksternal seperti analytics, email provider, error monitoring, payment gateway, atau hosted storage hanya boleh ditambahkan sebagai integrasi opsional dan default-off. Data sensitif HR/payroll tetap melewati Laravel policy/gate, private storage, audit trail, dan queue internal.
