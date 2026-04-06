# 📄 Account — сервис формирования и управления счетами

Веб-приложение на Laravel для генерации счетов, их архивации и массового скачивания. Автоматизирует документооборот для малого бизнеса.

## 🚀 Функционал

- Создание счета через форму (реквизиты, товары/услуги, цены, НДС).
- Автоматическая генерация PDF-файла по шаблону.
- Сохранение счетов в MySQL с привязкой к контрагенту.
- Хранение PDF-файлов в защищённой папке (архивация).
- Массовое скачивание выбранных счетов одним ZIP-архивом.
- Просмотр и фильтрация списка счетов (по дате, контрагенту, статусу).
- Редактирование и удаление счетов.

## 🛠 Технологии

- **Backend:** PHP 8.1+, Laravel 10
- **Frontend:** Blade, Bootstrap 5, JavaScript (jQuery/Ajax)
- **База данных:** MySQL
- **Генерация PDF:** barryvdh/laravel-dompdf
- **Архивация:** ZipArchive (PHP)
- **Очереди:** Laravel Queues (для массовой генерации)

## 📦 Установка

```bash
git clone https://github.com/kaliganov/account.git
cd account
composer install
cp .env.example .env
# Настройте .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Приложение будет доступно по адресу http://127.0.0.1:8000.

## 📂 Основные модули

InvoiceController: CRUD счетов, массовое скачивание.
Invoice (модель): Связи с контрагентами и позициями.
PdfGenerator: Сервис генерации PDF через Dompdf.
resources/views/invoices: Blade-шаблоны (форма, список, детали).
