# E-Commerce Order Management System

## Project Overview and Features
This project is a scalable RESTful API built with Laravel 10+ and PHP 8.2+ for managing e-commerce orders with integrated inventory tracking. It simulates a real-world order processing system, emphasizing clean architecture, performance, and extensibility. The API follows API versioning (v1) and uses JWT for authentication with role-based access control (RBAC) via Spatie Laravel Permission.

### Core Features
1. **Product & Inventory Management**
    - **Product CRUD with Variants:** Full create, read, update, delete operations for products, including support for variants (e.g., size, color) with SKUs.
    - **Real-time Inventory Tracking:** Automatic stock adjustments on order confirmation/cancellation, with dedicated inventory models for each variant.
    - **Low Stock Alerts (Queue Job):** Asynchronous queue jobs trigger email notifications when stock falls below a threshold (configurable).
    - **Bulk Product Import via CSV:** Upload and process CSV files to import products and variants in bulk, queued for background processing.
    - **Product Search:** Integrated with Laravel Scout and Elasticsearch for full-text search on product names, descriptions, and attributes.

2. **Order Processing**
    - **Create Orders with Multiple Items:** Customers can create orders with line items referencing variants, including shipping address.
    - **Order Status Workflow:** Supports transitions: Pending → Processing → Shipped → Delivered → Cancelled. Status changes trigger events for notifications.
    - **Inventory Deduction on Confirmation:** Deducts stock atomically using database transactions on order confirmation.
    - **Order Rollback on Cancellation:** Restores inventory on cancellation, ensuring data integrity.
    - **Invoice Generation (PDF):** Generates downloadable PDF invoices using Barryvdh Laravel DomPDF, triggered on status changes.
    - **Email Notifications:** Event-driven emails for order updates (e.g., confirmation, shipment) using Laravel Mail and queue jobs.

3. **Authentication & Authorization**

    - **JWT Authentication with Refresh Tokens:** Uses Tymon JWT-Auth for secure token-based auth, including login, register, logout, and refresh endpoints.
    - R**ole-Based Access:**
        * **Admin:** Full access to all resources (CRUD products, orders, users).
        * **Vendor:** Manage own products, variants, inventory, and view associated orders.
        * **Customer:** Place orders, view personal order history.


### Technical Highlights
- **Architecture:** Repository pattern for data access, Service classes for business logic, Actions/Commands for complex ops (e.g., order confirmation), Events & Listeners for decoupling (e.g., stock alerts, emails).
- **Async Operations:** Queue jobs (database driver) for emails, PDF generation, imports, and notifications.
- **Data Integrity:** Database transactions for critical operations like inventory updates.
- **Performance:** Eager loading to prevent N+1 queries, indexing on searchable fields (e.g., slugs, SKUs), pagination for lists.
- **Scalability:** Documented database sharding strategy in SCALING.md (e.g., shard by vendor_id for large-scale vendor growth). Caching via file driver for frequent reads (e.g., product lists).
- **Dependencies:** Key packages include `tymon/jwt-auth` for auth, `spatie/laravel-permission` for RBAC, `maatwebsite/excel` for CSV imports, `barryvdh/laravel-dompdf` for PDFs, and `laravel/scout` for search integration.

**Note:** Testing (feature/unit tests) is pending implementation. API documentation via OpenAPI/Swagger and Postman collection are included.



## Local Setup Instructions (Step-by-Step)

This project supports local development with Docker for a full-stack setup (PHP, MySQL, Nginx, Mailpit for emails, phpMyAdmin). Alternatively, use native PHP/MySQL if preferred.

### Prerequisites

- Docker & Docker Compose (v2+)
- Git
- Composer (for non-Docker setup)
- PHP 8.2+ (if not using Docker)
- MySQL 5.7+ (if not using Docker)

#### Option 1: Docker Setup (Recommended)
1. **clone repository:**
    > `git clone https://github.com/akash-rumi/OMSDE ecommerce-order-system-docker`
    > `cd ecommerce-order-system-docker`
2. **Run the provided setup script for one-command initialization:**
    >`chmod +x setup.sh` 
    > `./setup.sh`

**This:**
- Prunes existing containers.
- Starts services via docker-compose -p srtsch up -d (in ./docker dir).
- Waits 10s for readiness.
- **Configures Laravel:** Copies .env.example to .env, installs Composer deps, generates key, runs migrate:fresh --seed.
- **Sets permissions:** chmod -R 777 src.

**Access:** API '`http://localhost/api/v1`',  phpMyAdmin `localhost:8080`, Mailpit `localhost:8025`. **Start queue:** `docker exec -it php php artisan queue:work` .

#### Option 2: Native Setup (Non-Docker)

1. **clone repository:**
    > `git clone https://github.com/akash-rumi/Order-Management-System ecommerce-order-system`
    > `cd ecommerce-order-system`

2. **Copy Environment File:**
    >`cp .env.example .env`

3. **Install deps:** `composer install`
4. **Generate key:** `php artisan key:generate`
5. **Set up MySQL locally:** `(DB_NAME=laravel, USER=root, PASS=empty or custom)`.
6. **Run migrations:** `php artisan migrate --seed`
7. **Start server:** php artisan serve
8. For emails, install Mailpit locally or use a service like Mailtrap.
9. **Queue:** php artisan queue:work

#### Troubleshooting

- **Permissions:** Run `chmod -R 775` storage `bootstrap/cache` (or in Docker: docker-compose exec php chmod...).
- **Logs:** Check `storage/logs/laravel.log` or Docker logs: docker-compose logs php.

## Environment Variables

Copy `.env.example` to `.env` and configure. Defaults for local dev (Docker: adjust DB/MAIL).

| Variable             | Description                                                      | Default/Example                   | Required          |
|----------------------|------------------------------------------------------------------|-----------------------------------|-------------------|
| `APP_NAME`           | Application name                                                 | `Laravel`                         | No                |
| `APP_ENV`            | Environment (local/production)                                   | `local`                           | No                |
| `APP_KEY`            | Laravel encryption key (generate via `php artisan key:generate`) | Generated                         | Yes               |
| `APP_DEBUG`          | Enable debug mode                                                | `true`                            | No                |
| `APP_URL`            | Base URL                                                         | `http://localhost`                | No                |
| `LOG_CHANNEL`        | Log driver                                                       | `stack`                           | No                |
| `LOG_LEVEL`          | Log verbosity                                                    | `debug`                           | No                |
| `DB_CONNECTION`      | Database driver                                                  | `mysql`                           | Yes               |
| `DB_HOST`            | DB host (`db` in Docker)                                         | `127.0.0.1`                       | Yes               |
| `DB_PORT`            | DB port                                                          | `3306`                            | Yes               |
| `DB_DATABASE`        | DB name                                                          | `laravel`                         | Yes               |
| `DB_USERNAME`        | DB user                                                          | `root` (native) / `akash` (Docker)| Yes               |
| `DB_PASSWORD`        | DB password                                                      | `` (empty native) / `root` (Docker)| Yes              |
| `BROADCAST_DRIVER`   | Broadcasting driver                                              | `log`                             | No                |
| `CACHE_DRIVER`       | Cache driver (file local)                                        | `file`                            | No                |
| `FILESYSTEM_DRIVER`  | Storage driver                                                   | `local`                           | No                |
| `QUEUE_CONNECTION`   | Queue driver (database for jobs)                                 | `database`                        | Yes               |
| `SESSION_DRIVER`     | Session driver                                                   | `file`                            | No                |
| `SESSION_LIFETIME`   | Session expiry (minutes)                                         | `120`                             | No                |
| `MAIL_MAILER`        | Mail driver                                                      | `smtp`                            | Yes               |
| `MAIL_HOST`          | Mail host (`mailpit` in Docker)                                  | `mailpit`                         | Yes               |
| `MAIL_PORT`          | Mail port                                                        | `1025`                            | Yes               |
| `MAIL_USERNAME`      | Mail username                                                    | `null`                            | No                |
| `MAIL_PASSWORD`      | Mail password                                                    | `null`                            | No                |
| `MAIL_ENCRYPTION`    | Mail encryption                                                  | `null`                            | No                |
| `MAIL_FROM_ADDRESS`  | From email                                                       | `no-reply@example.com`            | Yes               |
| `MAIL_FROM_NAME`     | From name                                                        | `${APP_NAME}`                     | Yes               |
| `JWT_SECRET`         | JWT token secret                                                 | Generated long string             | Yes               |
| `SCOUT_DRIVER`       | Search driver                                                    | `elasticsearch` (or `database`)   | If search enabled |
| `ELASTICSEARCH_HOST` | ES host (if Scout)                                               | `localhost:9200`                  | If ES enabled     |

## Authentication Guide

This API uses **Bearer tokens** issued by **Laravel Sanctum** .

Tokens are long-lived by default in Sanctum but can be configured to expire.  
All protected endpoints require the token in the `Authorization` header.

### Endpoints Overview

| Method | Endpoint            | Description                       | Auth Required |
|--------|---------------------|-----------------------------------|---------------|
| POST   | `/auth/register`    | Create a new user                 | No            |
| POST   | `/auth/login`       | Login → receive access token      | No            |
| POST   | `/auth/refresh`     | Get a new token (if expiry enabled) | Yes         |
| POST   | `/auth/logout`      | Revoke current token              | Yes           |

### 1. Register (optional – enable only if you allow self-signup)

    POST {{base_url}}/auth/register
    Content-Type: application/json

    {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "role": "customer"          // optional: admin | vendor | customer
    }

### API Documentation
The API is documented using OpenAPI 3.1.0 specification in openapi.yaml. For an interactive Swagger UI, visit the deployed documentation at [Order Management System API](https://akash-rumi.github.io/Order-Management-System/).

### Testing Instruction
Feature test/Unit tests are not implemented.

### Author
- *Name:* Munshi Allama Rumi
- *Email:* akashrumi@gmail.com
- *Github:* https://github.com/akash-rumi/