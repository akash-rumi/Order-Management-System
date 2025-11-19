# Authentication Guide

This API uses **Bearer tokens** issued by **Laravel Sanctum** .

Tokens are long-lived by default in Sanctum but can be configured to expire.  
All protected endpoints require the token in the `Authorization` header.

## Endpoints Overview

| Method | Endpoint            | Description                       | Auth Required |
|--------|---------------------|-----------------------------------|---------------|
| POST   | `/auth/register`    | Create a new user                 | No            |
| POST   | `/auth/login`       | Login → receive access token      | No            |
| POST   | `/auth/refresh`     | Get a new token (if expiry enabled) | Yes         |
| POST   | `/auth/logout`      | Revoke current token              | Yes           |

## 1. Register (optional – enable only if you allow self-signup)

```bash
POST {{base_url}}/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "role": "customer"          // optional: admin | vendor | customer
}