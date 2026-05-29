# Mini Store
A lightweight and efficient Sales Management System built with the Laravel framework. This application handles product inventories, and order processing.

## Key Features
- **Product Management:** Full CRUD (Create, Read, Update, Delete) functionality for products, categories, and stock levels.
- **Order Processing:** Create orders, generate invoices, and track payment statuses.
- **Authentication & Authorization:** Secure login/registration with role-based access control (Admin vs. Staff).

## Tech Stack
- **Backend:** Laravel 11.x / 10.x
- **Database:** MySQL / MariaDB
- **Frontend:** Tailwind CSS, Blade Templates, Alpine.js
- **Bundler:** Vite
- **Package Managers:** Composer & NPM

## System Requirements
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

## Installation & Setup
Follow these steps to get your local development environment running:

### 1. Clone the Repository
```bash
git clone https://github.com/QuocB-HC/MiniSalesManagementSystemLaravel.git
cd MiniSalesManagementSystemLaravel
```

### 2. Install Dependencies
```bash
# Install Node Module
npm i

# Install PHP dependencies
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```
*Open the `.env` file and update your Database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

### 4. Database Migration & Seeding
```bash
# Run migrations and seed the database with sample data
php artisan migrate:fresh --seed
```

### 5. Launch the Application
```bash
# Start the development server
npm run dev

# Start the Laravel development server
php artisan serve
```
Access the app at: `http://127.0.0.1:8000` <br />
Admin Acount:
- Email: admin@gmail.com
- Password: 123456

## Key Directory Structure
- `app/Models`: Contains Eloquent models (Product, Order, Category).
- `app/Http/Controllers`: Handles business logic and request processing.
- `resources/views`: Blade templates for the UI.
- `database/migrations`: Database schema definitions.

---
**Contact:**
- **Nguyen Vo Quoc Bao** - [nguyenvoquocbao292003@gmail.com]
- **Github:** https://github.com/QuocB-HC
