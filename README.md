# Pigz Challenge Backend API

API RESTful developed for the Pigz technical challenge, featuring vehicle management, FIPE table integration, and price comparison using clean architecture (DDD) and SOLID principles.

## 🚀 Technologies

- **PHP 8.2**
- **Symfony 7.4**
- **MySQL 8.0**
- **Docker & Docker Compose**
- **JWT Authentication** (LexikJWTAuthenticationBundle)
- **Brasil API** (Integration for FIPE data)

## 🛠️ Setup & Installation

### Requirements

- Docker and Docker Compose installed.

### Quick Start (for technical assessment)

**One command** – everything is configured automatically:

```bash
docker-compose up -d --build
```

The container entrypoint automatically runs:
- ✅ Dependency installation (`composer install`)
- ✅ Database migrations
- ✅ JWT key generation
- ✅ Admin user creation for testing

**Test credentials:**
- **Email:** `admin@pigz.com`
- **Password:** `password123`
- **API:** http://localhost:8080/api

### Manual commands (optional)

If you need to run something manually:

```bash
# Migrations
docker-compose exec app php bin/console doctrine:migrations:migrate

# Generate JWT keys
docker-compose exec app php bin/console lexik:jwt:generate-keypair

# Create admin user
docker-compose exec app php bin/console app:create-user admin@pigz.com password123 --admin
```

---

## ✅ Technical Requirements Verification

Here is how each technical requirement was implemented and can be tested:

### 1. CRUDs (Vehicle & FIPE)

- **Vehicle CRUD**:
    - `GET /api/vehicles` (List)
    - `POST /api/vehicles` (Create)
    - `PUT /api/vehicles/{id}` (Update - Owner/Admin only)
    - `DELETE /api/vehicles/{id}` (Delete - Owner/Admin only)
- **FIPE CRUD**:
    - `POST /api/fipe` (Admin only)
    - `PUT /api/fipe/{id}` (Admin only)
    - `DELETE /api/fipe/{id}` (Admin only)

### 2. Access Control (ACL)

- **Implementation**: Uses Symfony Security Voters (`VehicleVoter`, `FipeVoter`).
- **Test**: Try to delete a vehicle created by another user. You will receive `403 Access Denied`. Try to create a FIPE entry with a non-admin user. You will receive `403 Access Denied`.

### 3. Vehicle Listing

- **Endpoint**: `GET /api/vehicles`
- Features optimized queries with pagination support.

### 4. Price Comparison

- **Endpoint**: `GET /api/vehicles/{id}/price-comparison`
- **Logic**: Hybrid approach. Checks local `fipe_price` table first. If not found, fetches automatically from **Brasil API**.
- **Response**: Includes `difference`, `percentageDifference`, and status (`above`, `below`, `equal`).

### 5. JWT Authentication

- **Endpoint**: `POST /api/login_check`
- Returns a JWT token that must be sent in the `Authorization: Bearer <token>` header for all protected endpoints.

### 6. Domain-Driven Design (DDD)

- **Structure**: `src/Context/{Domain}/{Layer}`
- **Layers**: Application, Domain, Infrastructure.
- **Entities**: `Vehicle`, `User`, `FipePrice` (Domain Layer).
- **Repositories**: Interfaces in Domain, Implementations in Infrastructure.

### 7. DTOs (Data Transfer Objects)

- All controllers use DTOs for input (`CreateVehicleDTO`) and output (`VehicleResponseDTO`, `PriceComparisonDTO`), preventing Entity leakage.

### 8. Custom Queries & Filters

- **Endpoint**: `GET /api/vehicles?brand=Honda&minPrice=50000`
- Implemented in `VehicleRepository` using Doctrine QueryBuilder for optimized filtering.

### 9. Market Research (Benchmark)

- Implementation inspired by OLX/Webmotors:
    - **FIPE Code**: precise model identification.
    - **Price Comparison**: Visual indicators (below/above market price).
    - **Fallback API**: Ensures data availability even without manual admin entry.

## 🧪 Testing with Insomnia (Step-by-Step)

### 1. Configure Environment

1. Create a new Environment in Insomnia.
2. Add a variable `base_url` with value `http://localhost:8080/api`.
3. Add a variable `token` (leave empty for now).

### 2. Login (Get Token)

- **Method**: `POST`
- **URL**: `{{ base_url }}/login_check`
- **Body** (JSON):
    ```json
    {
        "email": "admin@pigz.com",
        "password": "password123"
    }
    ```
- **Action**: Send request. Copy the `token` from the response.

### 3. Set Token

- Go to your Environment settings.
- Paste the copied token into the `token` variable.

### 4. Test FIPE Lookup (API Integration)

- **Method**: `GET`
- **URL**: `{{ base_url }}/fipe/lookup/001461-3`
- **Auth**: Bearer Token -> Token: `{{ token }}`
- **Response**: Should return vehicle details directly from Brasil API.

### 5. Create Vehicle (for Comparison)

- **Method**: `POST`
- **URL**: `{{ base_url }}/vehicles`
- **Auth**: Bearer Token -> Token: `{{ token }}`
- **Body** (JSON):
    ```json
    {
        "make": "Fiat",
        "model": "Mobi",
        "version": "Like 1.0",
        "kms": 0,
        "price": 50000,
        "yearModel": 2022,
        "yearFab": 2022,
        "color": "Branco",
        "fipeCode": "001461-3"
    }
    ```

### 6. Compare Price

- **Method**: `GET`
- **URL**: `{{ base_url }}/vehicles/{id}/price-comparison` (Replace `{id}` with the ID from step 5)
- **Auth**: Bearer Token -> Token: `{{ token }}`
- **Response**:
    ```json
    {
        "vehiclePrice": 50000,
        "fipePrice": 58900,
        "difference": -8900,
        "status": "below_market",
        "source": "brasil_api"
    }
    ```
