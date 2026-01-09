# 🎫 ITicket - Support Ticket Management System

ITicket is a web-based Support Ticket Management System designed for non-IT users, IT staff, and administrators. It helps submit and manage support requests, track resolution progress, monitor performance, and maintain system health using a clean, user-friendly interface.

---

## Overview

ITicket focuses on:

- Reducing manual work in submitting and tracking requests
- Increasing visibility of progress and performance
- Standardizing workflows for triage and resolution
- Accelerating outcomes with organized queues and dashboards
- Deflecting common issues via a searchable knowledge base
- Maintaining system health through admin compliance metrics

---

## Tech Stack

- **Backend:** Laravel (PHP)
- **Frontend:** Blade templates + HTML/CSS/JavaScript
- **Database:** Microsoft SQL Server
- **Local Dev:** XAMPP (PHP) + SQL Server Express + SSMS

---

## Prerequisites

Install these first:

1. **Git**
2. **XAMPP with PHP 8.2 (ZTS x64)** — https://www.apachefriends.org/download.html
3. **Composer** (PHP dependency manager) — https://getcomposer.org/
4. **Node.js + npm** (for Vite assets) — https://nodejs.org/
5. **Microsoft SQL Server Express** (local database)
6. **SQL Server Management Studio (SSMS)**
7. **ODBC Driver 18 for SQL Server** — https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
8. **Visual C++ Redistributable 2015–2022** (x64)

> Note: Laravel uses the `sqlsrv` driver, so SQL Server + drivers must be installed correctly.

---

## Quick Start (Step-by-step)

### 1) Clone the repository

```bash
git clone https://github.com/masquerad3/ITicket.git
cd ITicket
```

> If your Laravel project folder is named differently (example: `ITicket-laravel`), `cd` into that folder instead.

---

### 2) Install PHP dependencies (Composer)

```bash
composer install
```

If `composer` is not recognized, install Composer and try again.

---

### 3) Install frontend dependencies (npm)

```bash
npm install
```

---

### 4) Create your `.env` file

Copy `.env.example` into `.env`:

```bash
copy .env.example .env
```

---

### 5) Generate Laravel App Key

```bash
php artisan key:generate
```

This fills in `APP_KEY=` inside `.env`.

---

## Database Setup (SQL Server)

### 6) Create database in SQL Server

Open **SSMS** → connect to your instance (example: `DESKTOP-K3DB62P\SQLEXPRESS01`)

Create a database named:

- `ITicket`

(Or use any name you want, but it must match `DB_DATABASE` in `.env`.)

---

### 7) Enable SQL Server Authentication (Mixed Mode)

In SSMS:

1. Right-click the server → **Properties**
2. **Security**
3. Select **SQL Server and Windows Authentication mode**
4. **Restart** the SQL Server instance

---

### 8) Create a SQL login for the app

In SSMS, run (change password if you want):

```sql
CREATE LOGIN [iticket_user]
WITH PASSWORD = 'Iticket!12345',
     CHECK_POLICY = OFF,
     CHECK_EXPIRATION = OFF,
     DEFAULT_DATABASE = [master];
GO

USE [ITicket];
GO
CREATE USER [iticket_user] FOR LOGIN [iticket_user];
EXEC sp_addrolemember 'db_owner', 'iticket_user';
GO
```

---

### 9) Update `.env` database settings

Open `.env` and set:

```env
DB_CONNECTION=sqlsrv
DB_HOST=DESKTOP-K3DB62P\SQLEXPRESS01
DB_PORT=
DB_DATABASE=ITicket
DB_USERNAME=iticket_user
DB_PASSWORD=Iticket!12345

DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=true
```

> If you get an SSL/certificate error, ensure `DB_TRUST_SERVER_CERTIFICATE=true`.

---

### 10) Run migrations (creates tables)

```bash
php artisan migrate
```

---

## Running the App

### 11) Start the dev server

```bash
php artisan serve
```

Open:

- http://127.0.0.1:8000

---

## Useful Commands

Clear config cache (do this after changing `.env`):

```bash
php artisan config:clear
```

List all routes:

```bash
php artisan route:list
```

Build frontend assets:

```bash
npm run dev
```

---

## Common Troubleshooting

### SQL Server login fails (Error 18456)
- Make sure SQL Server is set to **Mixed Mode**
- Restart SQL Server after changing authentication mode
- Ensure your `.env` username/password matches the SQL login

### SSL / certificate chain not trusted
- In `.env` set:
  - `DB_ENCRYPT=yes`
  - `DB_TRUST_SERVER_CERTIFICATE=true`

### “Class 'PDO' not found” or sqlsrv missing
- Ensure you are using the correct PHP version (XAMPP PHP)
- Ensure SQL Server drivers are installed and enabled

---

## Screens in Scope

- Login
- Non-IT: Dashboard, My Tickets, New Ticket, Knowledge Base
- IT: Dashboard, Assigned Tickets, Ticket Detail (comments), Resolved Tickets, Performance
- Admin: Dashboard, Tickets, Staffs, System/Business Health

---

## Contributors

- Samuel Muralidharan
- Esquivel Municht
- Prince Amorsolo Remo
- Claire Ulgasan
