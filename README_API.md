# WarungKu API Documentation

API untuk aplikasi kasir UMKM WarungKu yang menyediakan semua fitur lengkap untuk manajemen penjualan, hutang, pengeluaran, dan pembayaran.

## Base URL
```
http://localhost:8000/api
```

## Authentication
API menggunakan Laravel Sanctum untuk autentikasi. Setelah login, gunakan token yang diterima dalam header Authorization:
```
Authorization: Bearer {your_token}
```

## Endpoints

### Authentication

#### Register
```http
POST /register
```
**Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login
```http
POST /login
```
**Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Get User Info
```http
GET /user
```
*Requires authentication*

#### Logout
```http
POST /logout
```
*Requires authentication*

### Dashboard

#### Get Dashboard Stats
```http
GET /dashboard/stats
```
*Requires authentication*

Mengembalikan statistik:
- Penjualan hari ini
- Pengeluaran hari ini
- Profit hari ini
- Penjualan bulanan
- Pengeluaran bulanan
- Profit bulanan
- Total hutang belum dibayar
- Jumlah hutang jatuh tempo
- Aktivitas terbaru

### Sales (Penjualan)

#### Get All Sales
```http
GET /sales
```
*Requires authentication*

#### Create Sale
```http
POST /sales
```
*Requires authentication*

**Body:**
```json
{
    "product_name": "Nasi Goreng",
    "quantity": 2,
    "price": 15000,
    "notes": "Pedas level 2"
}
```

#### Get Sale
```http
GET /sales/{id}
```
*Requires authentication*

#### Update Sale
```http
PUT /sales/{id}
```
*Requires authentication*

#### Delete Sale
```http
DELETE /sales/{id}
```
*Requires authentication*

#### Get Today's Sales
```http
GET /sales/today
```
*Requires authentication*

### Debts (Hutang)

#### Get All Debts
```http
GET /debts
```
*Requires authentication*

#### Create Debt
```http
POST /debts
```
*Requires authentication*

**Body:**
```json
{
    "customer_name": "Bu Siti",
    "amount": 500000,
    "due_date": "2024-12-31",
    "notes": "Hutang untuk belanja bulanan"
}
```

#### Get Debt
```http
GET /debts/{id}
```
*Requires authentication*

#### Update Debt
```http
PUT /debts/{id}
```
*Requires authentication*

#### Delete Debt
```http
DELETE /debts/{id}
```
*Requires authentication*

#### Get Unpaid Debts
```http
GET /debts/unpaid
```
*Requires authentication*

#### Get Overdue Debts
```http
GET /debts/overdue
```
*Requires authentication*

### Expenses (Pengeluaran)

#### Get All Expenses
```http
GET /expenses
```
*Requires authentication*

#### Create Expense
```http
POST /expenses
```
*Requires authentication*

**Body:**
```json
{
    "description": "Beli bahan baku",
    "amount": 250000,
    "expense_date": "2024-01-15",
    "category": "supplies",
    "notes": "Bahan untuk menu baru"
}
```

**Categories:** `supplies`, `utilities`, `rent`, `salary`, `other`

#### Get Expense
```http
GET /expenses/{id}
```
*Requires authentication*

#### Update Expense
```http
PUT /expenses/{id}
```
*Requires authentication*

#### Delete Expense
```http
DELETE /expenses/{id}
```
*Requires authentication*

#### Get Today's Expenses
```http
GET /expenses/today
```
*Requires authentication*

#### Get Expenses by Category
```http
GET /expenses/by-category
```
*Requires authentication*

### Payments (Pembayaran)

#### Get All Payments
```http
GET /payments
```
*Requires authentication*

#### Create Payment
```http
POST /payments
```
*Requires authentication*

**Body:**
```json
{
    "debt_id": 1,
    "amount": 200000,
    "payment_method": "cash",
    "reference_number": null,
    "notes": "Cicilan pertama"
}
```

**Payment Methods:** `cash`, `qris`, `bank_transfer`

#### Get Payment
```http
GET /payments/{id}
```
*Requires authentication*

#### Update Payment
```http
PUT /payments/{id}
```
*Requires authentication*

#### Delete Payment
```http
DELETE /payments/{id}
```
*Requires authentication*

#### Get Debt Payments
```http
GET /debts/{debt_id}/payments
```
*Requires authentication*

## Response Format

Semua response menggunakan format JSON dengan struktur:

### Success Response
```json
{
    "success": true,
    "message": "Pesan sukses",
    "data": {
        // data response
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Pesan error",
    "errors": {
        // detail errors (validation)
    }
}
```

## Setup untuk Testing

1. Pastikan database sudah dibuat dan migrasi sudah dijalankan
2. Jalankan server Laravel: `php artisan serve`
3. Import Postman collection: `WarungKu_API.postman_collection.json`
4. Set base URL di Postman: `http://localhost:8000/api`
5. Register atau login untuk mendapatkan token
6. Token akan otomatis tersimpan di collection variable setelah login

## Notes

- Semua endpoint (kecuali register dan login) memerlukan autentikasi
- Data yang ditampilkan hanya milik user yang sedang login
- Pagination otomatis diterapkan pada list endpoints (15 items per page)
- Semua tanggal menggunakan format: `YYYY-MM-DD`
- Semua amount/price dalam format number (integer/decimal)
