
## Project Setup

### 1. Clone Repository

```bash
git clone https://github.com/sajamagde/school-management-backend.git
cd school-management-backend
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Create Environment File

```bash
copy .env.example .env
```

### 4. Configure Database

Update `.env` with your local database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_system
DB_USERNAME=root
DB_PASSWORD=
```

---

### 5. Generate App Key

```bash
php artisan key:generate
```

---

### 6. Run Migrations

```bash
php artisan migrate
```

---

### 7. Run Frontend Assets

```bash
npm run dev
```

---

## Running the Project

```bash
php artisan serve
```

---

