# Repository Yo‘riqnomasi

## Loyiha tuzilmasi va modul tashkiloti

* `src/` (PSR-4 `Katm\KatmSdk\`): asosiy kod.

    * `Dto/` request/response DTOlar (masalan, `InitClientRequestDto`).
    * `Enums/` API enumlari (masalan, `KatmApiEndpointEnum`).
    * `Services/` HTTP va manager servislar.
    * `Facades/Katm.php` facade; `Providers/KatmSdkServiceProvider.php` service provider.
    * `HttpExceptions/` istisnolarni moslashtirish (mapping) va turlari.
* `config/katm.php`: paket konfiguratsiyasi (env: `KATM_*`, `HTTP_*`).
* `tests/` PHPUnit + Orchestra Testbench (`Feature/`, `Unit/`, `tests/TestCase.php`).
* `Makefile`: umumiy vazifalar; `release.sh`: semver bo‘yicha reliz yordamchisi.
* `composer.json`, `phpunit.xml`: autoloading va test sozlamalari.

## Build, test va dasturlash buyruqlari

* Kutilmalarni o‘rnatish: `composer install`
* Formatlash va tekshirish: `make format` (yoki `vendor/bin/pint`), `make dump-autoload`
* Testlarni ishga tushirish: `make u-tests` (yoki `vendor/bin/phpunit`)
* Interaktiv tinker (Testbench): `make tinker`
* Git teglar ro‘yxati: `make git-tag`
* Reliz yordamchisi: `./release.sh` (birlashtiradi, teg qo‘yadi, push qiladi)

## Kod uslubi va nomlash konventsiyalari

* PHP 8.2+, PSR-12 (Laravel Pint orqali); 4 bo‘shliq (space) bilan indentatsiya.
* Namespace’lar `Katm\KatmSdk\...` ostida; klasslar — PascalCase, metodlar — camelCase.
* DTOlar: `*RequestDto`, `*ResponseDto`; Enums: `*Enum`; Services: `*Service`.
* HTTP array payload kalitlarini API bilan izchil saqlang (nomini o‘zgartirmang).

## Testlash bo‘yicha yo‘riqnomalar

* PHPUnit 10/11 Orchestra Testbench bilan.
* Testlarni `tests/Feature` yoki `tests/Unit` ostida joylashtiring; fayl nomi andozasi `*Test.php`.
* `Http::fake()` va cache fakes’dan foydalaning; real tarmoq/IO’dan saqlaning.
* Sozlash `tests/TestCase.php` orqali; tashqi servislariga qaram bo‘lmang.
* Lokal ishga tushirish: `make u-tests` va asserstiyalar “happy-path” hamda xatolarni moslashtirishni qamrab olganini ta’minlang.

## Commit va Pull Request bo‘yicha yo‘riqnomalar

* Commitlar: qisqa, buyruq ohangida, aniq doirada (masalan, "Add InitClient retry logic"). Conventional Commits (`feat:`, `fix:`) ma’qul, lekin majburiy emas.
* PRlar: aniq tavsif, bog‘langan issue’lar, foydalanish/konfiguratsiya snippetlari va yangilangan testlar bilan. Breaking change’larni qayd eting va zarur bo‘lsa skrinshotlar/loglarni kiriting.

## Xavfsizlik va konfiguratsiya bo‘yicha maslahatlar

* Maxfiy ma’lumotlar yoki `.env` faylini commit qilmang.
* `KATM_*` va `HTTP_*` uchun env o‘zgaruvchilarni afzal ko‘ring; production’da SSL verifikatsiyani yoqing.
* `config/katm.php` da timeout/retry/proxy’larni sozlang; endpointlarni qattiq kodlashdan saqlaning.

---

### 🧠 Termin lug‘ati

* `DTO` → Data Transfer Object; qatlamlar orasida ma’lumot tashuvchi struktura.
* `Enum` → Cheklangan qiymatlar to‘plamini ifodalovchi tip.
* `Facade` → Murakkab ichki logikaga soddalashtirilgan interfeys beruvchi naqsh (pattern).
* `Service provider` → Servislarni ro‘yxatdan o‘tkazish/sozlash uchun bootstrap komponenti.
* `Orchestra Testbench` → Laravel paketlarini testlashni osonlashtiruvchi muhit.
* `PSR-4` → PHP uchun autoloading standarti.
* `PSR-12` → PHP kodlash standartlari to‘plami.
* `Autoloading` → Klasslarni avtomatik yuklash mexanizmi.
* `Semver` → Semantik versiyalash qoidalari (MAJOR.MINOR.PATCH).
* `Endpoint` → API’da so‘rov yuboriladigan aniq manzil (URL yo‘li).
* `SSL verification` → TLS/SSL sertifikatini tekshirish jarayoni.
* `Happy-path` → Kutilgan, xatosiz ishlash stsenariysi.
* `Cache fakes` → Testlarda kechiktirishlarsiz/real IO’siz keshlashni soxtalashtirish.
