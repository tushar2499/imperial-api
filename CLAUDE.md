# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the backend API for **Imperial Bus Ticketing System** — a Laravel 10 REST API serving both an admin panel and a public website frontend. It handles bus scheduling, seat management, bookings, and supporting entities like routes, coaches, employees, and promotions.

## Common Commands

```bash
# Install dependencies
composer install

# Run migrations
php artisan migrate

# Run a single test
php artisan test --filter TestClassName

# Run all tests
php artisan test

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Generate JWT secret
php artisan jwt:secret

# Clear caches
php artisan optimize:clear

# Tinker (REPL)
php artisan tinker
```

## Architecture

### Authentication
JWT-based auth via `tymon/jwt-auth`. The `auth:api` middleware guards all routes except `POST /api/register`, `POST /api/login`, and `GET /api/home-page`. Tokens are returned on login/register and must be sent as `Authorization: Bearer <token>`.

### Response Format
All controllers use the `ApiResponse` trait ([app/Traits/ApiResponse.php](app/Traits/ApiResponse.php)), which standardizes JSON responses:
```json
{ "status": "success|error", "code": 200, "message": "...", "data": {...} }
```
Always use `$this->successResponse()`, `$this->errorResponse()`, `$this->validationErrorResponse()`, or `$this->notFoundResponse()` — never return raw `response()->json()`.

### Table Partitioning (Critical Pattern)
`TripInstance` and `SeatInventory` use **monthly table partitioning** for scale. Instead of one `trip_instances` table, records live in tables like `trip_instances_202506`, `trip_instances_202507`, etc.

The `AutoPartitionManager` trait ([app/Traits/AutoPartitionManager.php](app/Traits/AutoPartitionManager.php)) handles:
- Auto-creating partition tables via `SHOW CREATE TABLE` + DDL copy
- Routing queries to the correct `YYYYMM`-suffixed table

The `GlobalUniqueId` trait ([app/Traits/GlobalUniqueId.php](app/Traits/GlobalUniqueId.php)) generates IDs from a separate `*_sequences` table (e.g., `trip_instance_sequences`) to ensure uniqueness across partitions. Models using these traits set `$incrementing = false`.

**Key methods:**
- `TripInstance::findAcrossPartitions($id)` — searches all partition tables
- `TripInstance::forDate($date)` — returns a model instance pointing at the correct partition
- `SeatInventory::forTrip($tripId)` — resolves partition via the trip's date

### Domain Model
- **Route** → has many **Schedules** (departure/arrival time, frequency)
- **Coach** (vehicle type/seat plan) + **Bus** (physical vehicle) → assigned to a **TripInstance** per day
- **CoachConfiguration** — links Coach + Schedule + Route with boarding/dropping stops
- **TripInstance** — one actual trip on a specific date, partitioned by month
- **SeatInventory** — per-seat availability record for a TripInstance, also partitioned by month; statuses: `0=Cancelled, 1=Available, 2=Booked, 3=Blocked, 4=Sold`
- **SeatRequest** — temporary 5-minute seat hold before booking
- **Booking** + **BookingDetail** — confirmed reservations with PNR numbers

### Seat Lifecycle
`Available → Blocked (5 min hold via SeatRequest) → Booked → Sold`  
Or: any state → `Cancelled` or `Released` back to Available.

The `SeatInventoryService` ([app/Services/SeatInventoryService.php](app/Services/SeatInventoryService.php)) handles batch creation of seat inventory records when a new TripInstance is created.

### API Structure
All routes are under `/api` prefix (see [routes/api.php](routes/api.php)):
- **Public:** `GET /home-page`, `POST /register`, `POST /login`
- **Protected:** Everything else requires JWT

### Website vs Admin Routes
Routes under the "Website routes" comment section (`offer-and-promos`, `customer-reviews`, `faqs`) serve the public-facing website. The public `HomePageController` at [app/Http/Controllers/Api/Public/HomePageController.php](app/Http/Controllers/Api/Public/HomePageController.php) aggregates data for the website home page.

### Validation
Use `Validator::make()` inline in controllers (no Form Request classes). Return errors via `$this->validationErrorResponse($validator->errors())`.

### Database
MySQL. The `.env` file configures the connection. Migrations in [database/migrations/](database/migrations/) track the full schema history including incremental column additions.

## Laravel Specialist Skill

The laravel-specialist skill (.ai/skills/laravel-specialist/) provides deep Laravel guidance. Follow these rules in addition to Boost guidelines below.

### Core Workflow
1. **Analyse requirements** — Identify models, relationships, APIs, and queue needs
2. **Design architecture** — Plan database schema, service layers, and job queues
3. **Implement models** — Create Eloquent models with relationships, scopes, and casts
4. **Build features** — Develop controllers, services, API resources, and jobs
5. **Test thoroughly** — Write tests before considering any step complete

### Constraints — MUST NOT
- Use raw queries without protection (SQL injection)
- Skip eager loading (causes N+1 problems)
- Store sensitive data unencrypted
- Mix business logic in controllers — use Service classes
- Hardcode configuration values
- Skip validation on user input
- Ignore queue failures

### On-Demand Reference Files
Load these deep-dive guides from `.ai/skills/laravel-specialist/references/` when the task context matches:

| Topic | File | Load When |
|-------|------|-----------|
| Eloquent ORM | `references/eloquent.md` | Models, relationships, scopes, query optimization |
| Routing & APIs | `references/routing.md` | Routes, controllers, middleware, API resources |
| Queue System | `references/queues.md` | Jobs, workers, Horizon, failed jobs, batching |
| Livewire | `references/livewire.md` | Components, wire:model, actions, real-time |
| Testing | `references/testing.md` | Feature tests, factories, mocking |

### Validation Checkpoints
Run these at each workflow stage:
- After migration: `php artisan migrate:status` — all show `Ran`
- After routing: `php artisan route:list --path=api` — new routes appear
- After implementation: `php artisan test --filter=<TestName>` — 0 failures
- Before finalizing: `vendor/bin/pint --dirty` — formatting passes

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.31
- laravel/framework (LARAVEL) - v10
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v10


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v10 rules ===

## Laravel 10

- Use the `search-docs` tool to get version specific documentation.
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in Laravel 10:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`
- When using Eloquent model casts, you must use `protected $casts = [];` and not the `casts()` method. The `casts()` method isn't available on models in Laravel 10.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>