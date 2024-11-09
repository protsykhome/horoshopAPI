# HoroshopAPI

**HoroshopAPI** — це REST API для управління користувачами.
## Опис

Це проект для створення API, яке дозволяє:

- Реєстрацію нового користувача.
- Вхід в систему з отриманням JWT токену.
- Додавання та оновлення користувачів (тільки для адміністраторів).
- Отримання інформації про користувача (для власника акаунта).
- Підтримка ролей користувачів для доступу.

## Технології

- **Symfony 7.3**
- **JWT** для автентифікації
- **PHP 8.3**
- **MySQL** (для збереження даних про користувачів)
- **PHPUnit** для тестування

### Вимоги

- PHP 8.3 або вище
- MySQL
- Composer

## Кроки для налаштування

1. **Клонувати репозиторій:**

   ```bash
   git clone https://github.com/protsykhome/horoshopAPI.git
   cd horoshopAPI


2. Встановити залежності за допомогою Composer:
composer install

3.Налаштувати базу даних:


4. Міграції
php bin/console doctrine:migrations:migrate

5. Згенерувати ключі
 php bin/console lexik:jwt:generate-keypair


6. Запуск сервера:
php -S localhost:8000 -t public

