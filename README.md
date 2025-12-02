# Clash of Pop Culture: Rediscovering Modern PHP

Welcome to **Clash of Pop Culture**, a demo application designed to showcase the capabilities and elegance of **Modern PHP (8.3+)** and the **Symfony** framework.

If you are coming from a **Java** or **JavaScript** background, this project is specifically crafted to help you map your existing knowledge to the modern PHP ecosystem.

![Screenshot](./assets/screenshot.jpg)

## Play it!

![QRCode](./assets/qrcode.jpg)

---

## 🚀 For Java Developers

If you are familiar with **Spring Boot**, you will feel right at home with **Symfony**.

### 1. Dependency Injection & Service Container
Just like Spring, Symfony is built around a powerful Dependency Injection (DI) container.
*   **Java:** `@Autowired` or Constructor Injection.
*   **PHP:** Constructor Injection (Standard).

```php
// src/Service/GameEngine.php
public function __construct(
    private readonly BattleRepository $repository, // Auto-wired by Type Hint
) {}
```

### 2. Attributes (Annotations)
PHP 8 introduced **Attributes**, which are native metadata classes, similar to Java Annotations.
*   **Java:** `@GetMapping("/api/state")`
*   **PHP:** `#[Route('/api/state', methods: ['GET'])]`

### 3. Strict Typing & DTOs
Modern PHP embraces strict typing. We use Data Transfer Objects (DTOs) with validation, similar to Java Records or POJOs with Hibernate Validator.

```php
// src/Dto/VoteDto.php
class VoteDto
{
    public function __construct(
        #[Assert\NotNull]
        public VoteChoice $choice,
    ) {}
}
```

### 4. Enums
PHP 8.1 added native Enums.
*   **Java:** `public enum VoteChoice { A, B }`
*   **PHP:** `enum VoteChoice: string { case A = 'A'; case B = 'B'; }`

---

## ⚡ For JavaScript Developers

If you are used to **Node.js**, **Express**, or **Next.js**, here is how PHP compares today.

### 1. Composer vs NPM
*   **NPM/Yarn:** Manages `node_modules`.
*   **Composer:** Manages `vendor` packages. It handles autoloading automatically (no more manual `require` calls).

### 2. The Runtime: FrankenPHP
Traditionally, PHP required Nginx + PHP-FPM. This project uses **FrankenPHP**, a modern application server built on Go (Caddy).
*   It runs like a Node.js server (long-running process).
*   It supports "Worker Mode" for high performance, keeping your app in memory between requests.

### 3. Match Expression
Think of `match` as a `switch` statement on steroids, or pattern matching. It returns a value directly.

```php
// Similar to a concise switch or object map lookup in JS
$phase = match(true) {
    $cycleTime < 10 => 'VOTE',
    default => 'RESULT',
};
```

### 4. Async & Non-Blocking
While this demo is synchronous, modern PHP (via Fibers and frameworks like ReactPHP or Amphp) supports full async I/O, just like Node.js.

---

## 🛠️ Key Modern PHP Features Used

### Constructor Property Promotion
Reduce boilerplate code significantly.

**Old PHP (Pre-8.0):**
```php
class Point {
    public float $x;
    public float $y;
    public function __construct(float $x, float $y) {
        $this->x = $x;
        $this->y = $y;
    }
}
```

**Modern PHP (8.0+):**
```php
class Point {
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}
```

### Named Arguments
Pass arguments by name, skipping optional ones.
```php
// Order doesn't matter!
$response = new JsonResponse(data: ['status' => 'ok'], status: 200);
```

---

## 🏁 Getting Started

### Prerequisites
*   **PHP 8.3+**
*   **Composer**
*   **Symfony CLI** (Optional, but recommended)

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-repo/clash-of-pop-culture.git
    cd clash-of-pop-culture
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    ```

3.  **Setup Database (SQLite):**
    ```bash
    # Create the database file
    touch var/data.db
    
    # Run migrations to create tables
    php bin/console doctrine:migrations:migrate
    
    # Load sample data
    php bin/console app:load-battles
    ```

4.  **Start the Server:**
    ```bash
    symfony server:start
    ```
    Or using PHP's built-in server:
    ```bash
    php -S 127.0.0.1:8000 -t public
    ```

5.  **Play!**
    Open `http://127.0.0.1:8000` in your browser.

---

## 📂 Project Structure

*   `src/Controller`: API Endpoints (like Spring Controllers or Express Routes).
*   `src/Service`: Business Logic (Service Layer).
*   `src/Entity`: Database Models (Hibernate/JPA Entities).
*   `src/Repository`: Data Access Layer (Spring Data Repositories).
*   `src/Command`: CLI Commands (Scripts).
*   `public/index.html`: The Frontend (Vanilla JS + Tailwind).

---

*Built with ❤️ for the "Rediscovering Modern PHP" presentation.*

---

## ☁️ Deployment on Clever Cloud

To deploy this application on [Clever Cloud](https://www.clever-cloud.com/):

1.  **Prerequisites:**
    *   A Clever Cloud account.
    *   [Clever Tools CLI](https://www.clever-cloud.com/doc/clever-tools/getting-started/) installed (optional, but recommended).

2.  **Create Application:**
    *   Create a **PHP** application.
    *   Select **Apache** as the web server (default).

3.  **Environment Variables:**
    Set the following variables in the Clever Cloud dashboard or via CLI:
    *   `APP_ENV`: `prod`
    *   `APP_SECRET`: (Generate a random string)
    *   `DATABASE_URL`: (Your database connection string, e.g., PostgreSQL addon)
    *   `CC_WEBROOT`: `/public` (Points to the public directory)

4.  **Deploy:**
    Push your code to the Clever Cloud remote:
    ```bash
    git push clever main
    ```

5.  **Database Migration & Bootstrap:**
    Set the `CC_POST_BUILD_HOOK` environment variable to run migrations and load initial data automatically:
    *   **Variable:** `CC_POST_BUILD_HOOK`
    *   **Value:** `./bin/console doctrine:migrations:migrate --no-interaction; ./bin/console app:load-battles`
    *Note: `app:load-battles` is safe to run multiple times.*

6.  **Database Configuration (MySQL Addon):**
    If you are using a Clever Cloud MySQL addon, it exposes a `MYSQL_URI` environment variable. You must map this to `DATABASE_URL`.
    In the **Environment variables** section of your application:
    *   Add `DATABASE_URL` with the value `MYSQL_URI` (select "Value is a variable").




