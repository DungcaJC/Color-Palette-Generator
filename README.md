# 🎨 Color Palette Generator

A full-stack web app for generating, saving, and sharing color palettes. Upload an image, type a keyword, or build one from scratch — then share your work with the community.

---

## ✨ Features

### Palette Tools
- **Keyword Palette** — type any word or mood and get an instant palette
- **Image Extraction** — upload a photo and extract dominant colors using k-means clustering
- **Manual Creator** — build a custom palette with HEX, RGB, or HSL values
- **Save & Export** — save palettes to your account or export as JSON

### Community
- Post your artwork alongside the palette you used
- Like, comment, reply to, and save posts from other users
- Follow other creators and get notified of their activity
- Search posts by caption or browse by category
- Report inappropriate content

### User Accounts
- Register and log in with email and password
- Upload a profile avatar and write a bio
- View your posts, warnings, and appeal history
- Dark mode support (saved per user)

### Admin Panel
- Dashboard with live stats and charts
- Manage users (ban, unban, delete)
- Review reported posts and comments
- Send warnings and direct bans
- Review user appeals
- Manage roles (Super Admin only)

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue 3 + Vite + Tailwind CSS |
| Backend | Laravel 11 (PHP 8.3+) |
| Database | MySQL (via Laragon) |
| Auth | Laravel Sanctum (Bearer token) |
| Color APIs | Colormind, ColorMagic |

---

## 📦 Requirements

Make sure you have all of these installed before starting:

| Tool | Version | Link |
|------|---------|------|
| PHP | 8.3 or higher | https://www.php.net/ |
| Composer | Latest | https://getcomposer.org/ |
| Node.js | 18 or higher | https://nodejs.org/ |
| Git | Latest | https://git-scm.com/ |
| Laragon | Latest | https://laragon.org/ |

---

## 🚀 Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd Color-Palette-Generator
```

### 2. Set up the backend
```bash
cd BACKEND
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Configure your database

Open `.env` and update these values:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=color_palette_generator
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Set up the database
```bash
php artisan migrate
php artisan storage:link
```

Then open HeidiSQL (or any MySQL client) and create a super admin account:
```sql
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES (
  'Super Admin',
  'admin@palette.app',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'superadmin',
  NOW(),
  NOW()
);
```
> Default password is `password`. Change it after first login.

### 5. Set up the frontend
```bash
cd ../FRONTEND/Palette
npm install
```

---

## ▶️ Running the App

You need **two terminals** running at the same time.

**Terminal 1 — Backend:**
```bash
cd C:\laragon\www\Color-Palette-Generator\BACKEND
php artisan serve
```

**Terminal 2 — Frontend:**
```bash
cd C:\laragon\www\Color-Palette-Generator\FRONTEND\Palette
npm run dev
```

Then open your browser and go to:
```
http://localhost:5173
```

> The backend API runs on `http://localhost:8000`

---

## 🔑 Default Accounts

After seeding, you can log in with these credentials (change them after first login):

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@palette.app | password |

To reset a password via tinker:
```bash
php artisan tinker
\App\Models\User::where('email', 'admin@palette.app')->first()->update(['password' => bcrypt('new_password')])
```

---

## 📁 Project Structure

```
Color-Palette-Generator/
├── BACKEND/                  # Laravel 11 API
│   ├── app/
│   │   ├── Http/Middleware/  # isAdmin, isSuperAdmin
│   │   └── Models/           # User, Post, Palette, etc.
│   ├── routes/
│   │   └── api.php           # All API routes
│   └── .env                  # Environment config
│
└── FRONTEND/
    └── Palette/              # Vue 3 + Vite app
        └── src/
            ├── components/   # All Vue components
            ├── composables/  # useAuth, useNotifications, etc.
            └── assets/       # Styles and images
```

---

## ⚙️ Useful Commands

```bash
# Clear all Laravel caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# Re-link storage (for images)
php artisan storage:link

# Open tinker (PHP REPL)
php artisan tinker

# Stop any server
Ctrl + C
```

---

## 🐛 Common Issues

**"Invalid credentials" on login**
→ Reset your password via tinker (see above)

**Images not showing**
→ Run `php artisan storage:link`

**401 Unauthorized on all API calls**
→ Make sure `php artisan serve` is running in a terminal

**CORS errors in browser**
→ Check `config/cors.php` — allowed origins should include `localhost:5173`

---

## 📄 License

This project is for educational purposes.