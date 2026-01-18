# Laravel API Boilerplate

A robust, modular Laravel 12 API boilerplate designed for scalability and modern development best practices.

## 🚀 Tech Stack

- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Authentication:** Laravel Sanctum
- **Permissions:** Spatie Laravel Permission
- **Activity Logging:** Spatie Laravel Activitylog
- **Code Quality:** Laravel Pint, Rector
- **Deployment:** Deployer
- **Modular Structure:** Laravel Modular

## ✨ Key Features

### 🔐 Authentication & User Management
- **Complete Auth Suite:** Login, Registration, Password Reset, Email Verification.
- **Profile Management:** Update profile details, change password, and "Me" endpoint.
- **Role-Based Access Control (RBAC):** Manage roles and permissions using Spatie's package.
- **User Status:** Toggle user active/inactive status via API or CLI.

### 📁 Media Management
- **Upload & Manage:** Dedicated media endpoints for handling file uploads.
- **S3 Support:** Ready for AWS S3 or compatible storage (via Flysystem).

### 🔔 Notifications
- **System Notifications:** Fetch unread count, mark as read, and mark all as read.

### 🛠 Developer Tools & Utilities
- **API Documentation:** Auto-generate Swagger/OpenAPI style documentation.
- **Refactoring:** Automated refactoring via Rector.
- **Coding Standard:** Consistent code style with Laravel Pint.
- **Modular Design:** Support for modular application structure.
- **Activity Log:** Track changes and user activities across the system.

### ⚙️ System Utilities
- **Health Check:** `/api/v1/up` endpoint for monitoring.
- **Common Utilities:** Generic "Toggle Active" and "Status" controllers for various models.

## 🛠 Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd laravel-api-boilerplate
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

## 📖 API Documentation

The boilerplate includes a custom command to generate API documentation.

- **Generate Documentation:**
  ```bash
  composer generate-api-docs
  ```
  or
  ```bash
  php artisan app:generate-api-documentation --UI
  ```

## 🧹 Code Quality

- **Format Code:**
  ```bash
  composer format
  ```
  This runs both Laravel Pint and Rector to ensure code quality and consistency.

## 🚀 Deployment

The project uses [Deployer](https://deployer.org/) for automated deployments.

- **Deploy:**
  ```bash
  dep deploy
  ```
  *Note: Configure your host in `deploy.php`.*

## 💻 CLI Commands

- **Toggle User Status:**
  ```bash
  php artisan users:toggle-status {email} {--active=true|false}
  ```

## 🧪 Testing

- **Run Tests:**
  ```bash
  composer test
  ```
  or
  ```bash
  php artisan test
  ```
