# Dokumentasi API Otentikasi (Token-Based)

Dokumentasi ini menjelaskan penggunaan API Otentikasi untuk aplikasi PSRiau, yang menggunakan mekanisme **Bearer Token** untuk mengamankan endpoint API.

---

## 1. Protokol Otentikasi

Semua API yang dilindungi (protected) memerlukan header HTTP berikut:

```http
Authorization: Bearer <your_access_token>
```

Jika token tidak disediakan, tidak valid, atau kedaluwarsa, server akan mengembalikan respon `401 Unauthorized`.

---

## 2. Struktur Respon Standar

### Respon Sukses (Format Umum)
```json
{
  "status": "success",
  "message": "Pesan deskriptif sukses",
  "data": { ... }
}
```

### Respon Error (Format Umum)
```json
{
  "status": "error",
  "message": "Pesan kesalahan atau kegagalan"
}
```

---

## 3. Referensi API

### A. Login Pengguna

Mengautentikasi pengguna menggunakan kredensial username dan password, serta menghasilkan token akses baru.

* **URL:** `/api/auth/login`
* **Metode:** `POST`
* **Content-Type:** `application/json` atau `application/x-www-form-urlencoded`
* **Parameter Request (Body):**
  * `username` (Wajib, string): Username pengguna.
  * `password` (Wajib, string): Password pengguna.
  * `device_name` (Opsional, string): Label/Nama perangkat atau client API (Contoh: "Aplikasi Mobile", "Postman"). Default: "API Token".

#### Respon Sukses (200 OK)
```json
{
  "status": "success",
  "message": "Login berhasil.",
  "data": {
    "token": "49557434a9b5f483c65c404663673c683b5840d2f0df7a4e69b2d86161474272",
    "user": {
      "id": 12,
      "username": "pegawai.riau",
      "full_name": "Agung Kesuma",
      "role": "admin"
    }
  }
}
```

#### Respon Error (400 Bad Request - Parameter Kurang)
```json
{
  "status": "error",
  "message": "Username dan password wajib diisi."
}
```

#### Respon Error (401 Unauthorized - Kredensial Salah)
```json
{
  "status": "error",
  "message": "Kredensial login tidak valid."
}
```

#### Respon Error (403 Forbidden - Akun Nonaktif)
```json
{
  "status": "error",
  "message": "Akun Anda nonaktif. Hubungi administrator untuk aktivasi."
}
```

---

### B. Profil Pengguna (Protected)

Mendapatkan informasi detail akun dari pengguna yang saat ini terautentikasi.

* **URL:** `/api/auth/profile`
* **Metode:** `GET`
* **Otentikasi:** Bearer Token wajib diisi pada header.

#### Respon Sukses (200 OK)
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 12,
      "username": "pegawai.riau",
      "full_name": "Agung Kesuma",
      "role": "admin",
      "is_active": 1,
      "created_at": "2026-04-20 10:00:00",
      "updated_at": "2026-07-08 15:30:22"
    }
  }
}
```

#### Respon Error (401 Unauthorized - Token Tidak Valid)
```json
{
  "status": "error",
  "message": "Token otentikasi tidak valid."
}
```

---

### C. Keluar / Revoke Token (Protected)

Mencabut/menghapus token akses aktif dari database sehingga tidak dapat digunakan kembali.

* **URL:** `/api/auth/logout`
* **Metode:** `POST`
* **Otentikasi:** Bearer Token wajib diisi pada header.

#### Respon Sukses (200 OK)
```json
{
  "status": "success",
  "message": "Logout berhasil. Token telah dicabut."
}
```

---

## 4. Contoh Integrasi Kode

### A. Menggunakan cURL

**Login:**
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "pegawai.riau", "password": "password123", "device_name": "Terminal Test"}'
```

**Mendapatkan Profil:**
```bash
curl -X GET http://localhost:8080/api/auth/profile \
  -H "Authorization: Bearer <ACCESS_TOKEN>"
```

**Logout:**
```bash
curl -X POST http://localhost:8080/api/auth/logout \
  -H "Authorization: Bearer <ACCESS_TOKEN>"
```

### B. Menggunakan JavaScript (Fetch API)

**Melakukan Login dan Menyimpan Token:**
```javascript
async function loginUser(username, password) {
  try {
    const response = await fetch('http://localhost:8080/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password, device_name: 'Browser' })
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
      localStorage.setItem('api_token', result.data.token);
      console.log('Login sukses!', result.data.user);
    } else {
      console.error('Login gagal:', result.message);
    }
  } catch (error) {
    console.error('Terjadi kesalahan jaringan:', error);
  }
}
```

**Mengakses Endpoint Terproteksi:**
```javascript
async function getUserProfile() {
  const token = localStorage.getItem('api_token');
  
  try {
    const response = await fetch('http://localhost:8080/api/auth/profile', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    const result = await response.json();
    if (response.ok) {
      console.log('Data profil:', result.data.user);
    } else {
      console.error('Error:', result.message);
    }
  } catch (error) {
    console.error('Gagal memuat profil:', error);
  }
}
```
