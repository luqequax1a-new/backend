# Laravel Backend - Product Management API

Bu proje, Laravel framework'ü kullanılarak geliştirilmiş bir ürün yönetim API'sidir. Otomatik slug üretimi, ürün kopyalama ve kapsamlı ürün yönetimi özellikleri içerir.

## 🚀 Özellikler

### ✨ Slug Yönetimi
- **Otomatik Slug Üretimi**: Ürün adından otomatik olarak SEO dostu slug oluşturma
- **Benzersizlik Kontrolü**: Aynı slug'dan varsa otomatik olarak sayı ekleme (örn: `urun-adi`, `urun-adi-2`)
- **Özel Slug Desteği**: Manuel slug girişi imkanı
- **Güncelleme Koruması**: Ürün güncellenirken slug'ın korunması

### 📦 Ürün Yönetimi
- **CRUD İşlemleri**: Ürün oluşturma, okuma, güncelleme, silme
- **Ürün Kopyalama**: Mevcut ürünleri kopyalama özelliği
- **Kategori İlişkileri**: Çoklu kategori desteği
- **Resim Yönetimi**: Ürün resimlerini yönetme
- **Marka ve Mağaza İlişkileri**: Ürünleri marka ve mağazalarla ilişkilendirme

### 🔧 Teknik Özellikler
- **RESTful API**: Standart REST API yapısı
- **Form Request Validation**: Ayrı validation sınıfları
- **Helper Sınıfları**: Yeniden kullanılabilir Slugger helper
- **Database Migrations**: Veritabanı yapısı yönetimi
- **Seeders**: Test verileri

## 📋 Gereksinimler

- PHP >= 8.1
- Laravel >= 10.x
- MySQL >= 5.7
- Composer

## 🛠️ Kurulum

### 1. Projeyi Klonlayın
```bash
git clone https://github.com/luqequax1a-new/backend.git
cd backend
```

### 2. Bağımlılıkları Yükleyin
```bash
composer install
```

### 3. Ortam Dosyasını Yapılandırın
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Veritabanını Yapılandırın
`.env` dosyasında veritabanı bilgilerini güncelleyin:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fabric
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Veritabanını Oluşturun ve Migrate Edin
```bash
php artisan migrate
php artisan db:seed
```

### 6. Sunucuyu Başlatın
```bash
php artisan serve
```

## 📚 API Dokümantasyonu

### Base URL
```
http://localhost:8000/api/admin/v1
```

### 🛍️ Ürün Endpoint'leri

#### Tüm Ürünleri Listele
```http
GET /products
```

#### Ürün Detayı
```http
GET /products/{id}
```

#### Yeni Ürün Oluştur
```http
POST /products
Content-Type: application/json

{
    "name": "Örnek Ürün",
    "description": "Ürün açıklaması",
    "price": 99.99,
    "stock_quantity": 100,
    "sku": "SKU001",
    "status": "active",
    "slug": "ornek-urun" // Opsiyonel - belirtilmezse otomatik oluşturulur
}
```

#### Ürün Güncelle
```http
PATCH /products/{id}
Content-Type: application/json

{
    "name": "Güncellenmiş Ürün Adı",
    "price": 149.99
}
```

#### Ürün Sil
```http
DELETE /products/{id}
```

#### Ürün Kopyala
```http
POST /products/{id}/duplicate
```

#### Ürün Durumunu Güncelle
```http
PATCH /products/{id}/status
Content-Type: application/json

{
    "status": "inactive"
}
```

## 🏗️ Proje Yapısı

### Önemli Dosyalar

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       └── ProductController.php     # Ana ürün controller'ı
│   └── Requests/
│       ├── ProductStoreRequest.php       # Ürün oluşturma validation
│       └── ProductUpdateRequest.php      # Ürün güncelleme validation
├── Models/
│   ├── Product.php                       # Ürün modeli
│   ├── Category.php                      # Kategori modeli
│   ├── ProductImage.php                  # Ürün resim modeli
│   ├── Brand.php                         # Marka modeli
│   └── Store.php                         # Mağaza modeli
└── Support/
    └── Slugger.php                       # Slug helper sınıfı

database/
├── migrations/                           # Veritabanı migration'ları
└── seeders/                             # Test verileri

routes/
└── api.php                              # API route'ları
```

### Slugger Helper Sınıfı

`app/Support/Slugger.php` sınıfı slug işlemleri için kullanılır:

```php
use App\Support\Slugger;

// Basit slug oluşturma
$slug = Slugger::make('Örnek Ürün Adı'); // 'ornek-urun-adi'

// Benzersiz slug oluşturma
$uniqueSlug = Slugger::unique('Örnek Ürün Adı', Product::class); // 'ornek-urun-adi-2'

// Slug varlığını kontrol etme
$exists = Slugger::exists('ornek-slug', Product::class); // true/false
```

## 🧪 Test Etme

### Ürün Oluşturma Testi
```bash
# PowerShell
Invoke-WebRequest -Uri "http://localhost:8000/api/admin/v1/products" -Method POST -Headers @{"Content-Type"="application/json"} -Body '{"name":"Test Ürünü","description":"Test açıklaması","price":99.99,"stock_quantity":50,"sku":"TEST001","status":"active"}'
```

### Ürün Kopyalama Testi
```bash
# PowerShell
Invoke-WebRequest -Uri "http://localhost:8000/api/admin/v1/products/1/duplicate" -Method POST
```

## 🔧 Geliştirme

### Cache Temizleme
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Yeni Migration Oluşturma
```bash
php artisan make:migration create_table_name
```

### Yeni Model Oluşturma
```bash
php artisan make:model ModelName -m
```

## 📝 Veritabanı Yapısı

### Products Tablosu
- `id` - Primary key
- `name` - Ürün adı
- `slug` - SEO dostu URL (benzersiz)
- `description` - Ürün açıklaması
- `price` - Fiyat
- `stock_quantity` - Stok miktarı
- `sku` - Stok kodu
- `status` - Durum (active/inactive)
- `brand_id` - Marka ID (foreign key)
- `store_id` - Mağaza ID (foreign key)
- `created_at` - Oluşturulma tarihi
- `updated_at` - Güncellenme tarihi

### İlişkiler
- **Categories**: Many-to-Many (product_categories tablosu)
- **Images**: One-to-Many (product_images tablosu)
- **Brand**: Many-to-One
- **Store**: Many-to-One

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

Proje hakkında sorularınız için GitHub Issues kullanabilirsiniz.

---

**Not**: Bu README dosyası projenin mevcut durumunu yansıtmaktadır. Yeni özellikler eklendikçe güncellenecektir.
