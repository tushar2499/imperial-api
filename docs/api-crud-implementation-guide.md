# API CRUD Implementation Guide

This document defines the standard structure for implementing CRUD API endpoints in this project.
Follow this pattern for every new controller. Replace `{Model}` with your actual model name (e.g. `Bus`, `Route`, `Schedule`).

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── {Model}Controller.php
│   ├── Requests/
│   │   └── Api/
│   │       └── {Model}/
│   │           ├── {Model}IndexRequest.php
│   │           ├── {Model}StoreRequest.php
│   │           ├── {Model}ShowRequest.php
│   │           ├── {Model}UpdateRequest.php
│   │           └── {Model}DestroyRequest.php
│   └── Resources/
│       └── {Model}Resource.php
├── Models/
│   └── {Model}.php
└── Services/
    └── {Model}Service.php
```

---

## 1. Model

Ensure `$fillable` matches the actual database table columns exactly.
Always define `$casts` for non-string columns (integers, booleans, dates, decimals).
All relationship methods must have a PHPDoc block with a descriptive sentence, `@param` (if applicable), and `@return`.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class {Model} extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // list actual table columns here
        'related_model_id',
        'type',
        'date',
        'amount',
        'is_active',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type'      => 'integer',
        'date'      => 'datetime',
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
        'status'    => 'integer',
    ];

    /**
     * Get the related model that owns this record.
     *
     * @return BelongsTo
     */
    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo(RelatedModel::class);
    }

    /**
     * Get all child records belonging to this model.
     *
     * @return HasMany
     */
    public function childRecords(): HasMany
    {
        return $this->hasMany(ChildModel::class);
    }
}
```

### Cast Type Reference

| Column type       | Cast value        |
|-------------------|-------------------|
| `TINYINT`/`INT`   | `'integer'`       |
| `TINYINT(1)`/bool | `'boolean'`       |
| `DECIMAL`/`FLOAT` | `'decimal:2'`     |
| `DATE`            | `'date'`          |
| `DATETIME`        | `'datetime'`      |
| `TIME`            | `'datetime:H:i'`  |
| `JSON`            | `'array'`         |

---

## 2. Form Requests

Each action gets its own Form Request under `app/Http/Requests/Api/{Model}/`.

**Rules:**
- Always `use ApiResponse` trait
- `authorize()` returns `auth()->check()`
- `failedAuthorization()` throws `HttpResponseException` with `forbiddenResponse()`
- `rules()` contains only the validation rules relevant to that action
- Show and Destroy requests have empty `rules()`
- Update request uses the route `{id}` param for the unique ignore rule

**Index Request:**
```php
namespace App\Http\Requests\Api\{Model};

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class {Model}IndexRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'search'   => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
```

**Store Request:**
```php
class {Model}StoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // define required fields and their rules
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
```

**Update Request (unique ignore by route ID):**
```php
class {Model}UpdateRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            // use "unique:table,column,{$id}" to ignore current record on update
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
```

**Show / Destroy Request (no body validation):**
```php
class {Model}ShowRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbiddenResponse('You do not have permission to perform this action.')
        );
    }
}
```

---

## 3. Resource

Located at `app/Http/Resources/{Model}Resource.php`.

**Rules:**
- `public static $wrap = null` — disables the default `data` wrapper from the resource itself
- `toArray()` explicitly lists only the fields to expose — never expose everything blindly
- `collection()` is overridden to append pagination meta when the resource wraps a paginator

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class {Model}Resource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            // list the fields to expose
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function collection($resource)
    {
        $collection = parent::collection($resource);

        if ($resource instanceof LengthAwarePaginator) {
            return $collection->additional([
                'current_page' => $resource->currentPage(),
                'per_page'     => $resource->perPage(),
                'total'        => $resource->total(),
                'last_page'    => $resource->lastPage(),
            ]);
        }

        return $collection;
    }
}
```

---

## 4. Service

Located at `app/Services/{Model}Service.php`.

**Rules:**
- All DB writes are wrapped in `DB::transaction()`
- Never use `findOrFail()` — use `find()` then throw `ModelNotFoundException` explicitly with a clear message
- `findById()` is a shared lookup reused by `update()` and `destroy()`
- Multiple inputs → `array $attributes`; ID-only → `int $id`
- Always set `created_by` / `updated_by` from `auth()->id()`

```php
namespace App\Services;

use App\Models\{Model};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class {Model}Service
{
    /**
     * Get a paginated listing with optional search.
     *
     * @param  array  $attributes
     * @return LengthAwarePaginator
     */
    public function pagination(array $attributes): LengthAwarePaginator
    {
        $perPage = $attributes['per_page'] ?? 15;
        $page    = $attributes['page'] ?? 1;
        $search  = $attributes['search'] ?? null;

        $query = {Model}::query();

        if ($search) {
            $query->where('column_name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new record inside a database transaction.
     *
     * @param  array  $attributes
     * @return {Model}
     */
    public function store(array $attributes): {Model}
    {
        return DB::transaction(fn () => {Model}::create([
            // map $attributes fields here
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Find a record by ID or throw ModelNotFoundException.
     *
     * @param  int  $id
     * @return {Model}
     * @throws ModelNotFoundException
     */
    public function findById(int $id): {Model}
    {
        $record = {Model}::find($id);

        if (! $record) {
            throw new ModelNotFoundException("{Model} with ID {$id} not found.");
        }

        return $record;
    }

    /**
     * Update a record inside a database transaction.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return {Model}
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $attributes): {Model}
    {
        return DB::transaction(function () use ($id, $attributes) {
            $record = $this->findById($id);

            $record->update([
                // map $attributes fields here
                'updated_by' => auth()->id(),
            ]);

            return $record->fresh();
        });
    }

    /**
     * Soft-delete a record inside a database transaction.
     *
     * @param  int  $id
     * @return void
     * @throws ModelNotFoundException
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->findById($id)->delete();
        });
    }
}
```

---

## 5. Controller

Located at `app/Http/Controllers/{Model}Controller.php`.

**Rules:**
- Always `use ApiResponse` trait
- Service injected via constructor as `private readonly`
- Each method uses its own typed Form Request — never use plain `Request`
- Call `$request->validated()` and pass the result as `$attributes` to the service
- No try/catch — the global exception handler covers all errors automatically
- No `Validator::make()` — validation lives in Form Request classes
- No DB calls — all logic belongs in the service
- Single item responses: `new {Model}Resource($record)` — no key wrapper
- Collection responses: `{Model}Resource::collection($paginator)->resolve()` + `$this->preparePaginator()` as meta
- Every method has a PHPDoc block with `@param` and `@return`

```php
namespace App\Http\Controllers;

use App\Http\Requests\Api\{Model}\{Model}DestroyRequest;
use App\Http\Requests\Api\{Model}\{Model}IndexRequest;
use App\Http\Requests\Api\{Model}\{Model}ShowRequest;
use App\Http\Requests\Api\{Model}\{Model}StoreRequest;
use App\Http\Requests\Api\{Model}\{Model}UpdateRequest;
use App\Http\Resources\{Model}Resource;
use App\Services\{Model}Service;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class {Model}Controller extends Controller
{
    use ApiResponse;

    public function __construct(private readonly {Model}Service $service)
    {
    }

    /**
     * Display a paginated listing of all records.
     *
     * @param  {Model}IndexRequest  $request
     * @return JsonResponse
     */
    public function index({Model}IndexRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $records    = $this->service->pagination($attributes);

        return $this->successResponse(
            {Model}Resource::collection($records)->resolve(),
            'Records retrieved successfully',
            $this->preparePaginator($records)
        );
    }

    /**
     * Store a newly created record.
     *
     * @param  {Model}StoreRequest  $request
     * @return JsonResponse
     */
    public function store({Model}StoreRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $record     = $this->service->store($attributes);

        return $this->createdResponse(new {Model}Resource($record), 'Record created successfully');
    }

    /**
     * Display the specified record.
     *
     * @param  {Model}ShowRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function show({Model}ShowRequest $request, int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $this->successResponse(new {Model}Resource($record), 'Record retrieved successfully');
    }

    /**
     * Update the specified record.
     *
     * @param  {Model}UpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update({Model}UpdateRequest $request, int $id): JsonResponse
    {
        $attributes = $request->validated();
        $record     = $this->service->update($id, $attributes);

        return $this->successResponse(new {Model}Resource($record), 'Record updated successfully');
    }

    /**
     * Remove the specified record.
     *
     * @param  {Model}DestroyRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy({Model}DestroyRequest $request, int $id): JsonResponse
    {
        $this->service->destroy($id);

        return $this->successResponse([], 'Record deleted successfully');
    }
}
```

---

## 6. Response Shape Reference

**Single item** (store / show / update):
```json
{
    "status": true,
    "status_code": 200,
    "message": "Record retrieved successfully",
    "data": { "id": 1, "...": "..." },
    "meta": null,
    "errors": null
}
```

**Collection** (index):
```json
{
    "status": true,
    "status_code": 200,
    "message": "Records retrieved successfully",
    "data": [{ "id": 1, "...": "..." }, { "id": 2, "...": "..." }],
    "meta": { "current_page": 1, "per_page": 15, "total": 50, "last_page": 4 },
    "errors": null
}
```

**Delete** (destroy):
```json
{
    "status": true,
    "status_code": 200,
    "message": "Record deleted successfully",
    "data": null,
    "meta": null,
    "errors": null
}
```

**Validation error** (422 — handled globally):
```json
{
    "status": false,
    "status_code": 422,
    "message": "The given data was invalid.",
    "data": null,
    "meta": null,
    "errors": { "field_name": "The field name is required." }
}
```

**Not found** (404 — handled globally):
```json
{
    "status": false,
    "status_code": 404,
    "message": "{Model} with ID 99 not found.",
    "data": null,
    "meta": null,
    "errors": null
}
```

---

## 7. Testing

Every CRUD feature must have a corresponding feature test in `tests/Feature/{Model}ApiTest.php`.

### Commands

```bash
# Create the test file
php artisan make:test {Model}ApiTest

# Run only this test file
php artisan test tests/Feature/{Model}ApiTest.php

# Run a single test method
php artisan test --filter=test_authenticated_user_can_create_{model}

# Run the full suite
php artisan test
```

### Rules

- Use `RefreshDatabase` on every test class — always start with a clean database state
- Authenticate via `actingAs($user, 'api')` — never skip auth or call login endpoints in tests
- Create the authenticated user with `User::factory()->create()` and pass the required fields (`user_name`, `email`, `password`, `status`)
- Create related records (e.g. `SeatPlan`) with their factory before the assertion under test
- Call `$request->validated()` paths — test via HTTP (`$this->postJson`, `$this->getJson`, etc.), not by calling the service directly
- Assert both the HTTP status code **and** the JSON structure in every test
- Every test class must cover: happy path for each endpoint, validation errors (missing/invalid fields), unauthenticated access (401), and not-found (404)

### Test Class Structure

```php
namespace Tests\Feature;

use App\Models\{Model};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {Model}ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_name' => 'testuser',
            'email'     => 'test@example.com',
            'password'  => bcrypt('password'),
            'status'    => 1,
        ]);
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_{models}(): void
    {
        {Model}::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'api')
                         ->getJson('/api/{models}');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'status_code',
                     'message',
                     'data' => [['id']],
                     'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                 ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_{models}(): void
    {
        $this->getJson('/api/{models}')
             ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_{model}(): void
    {
        $payload = [
            // valid fields for this model
        ];

        $response = $this->actingAs($this->user, 'api')
                         ->postJson('/api/{models}', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', true)
                 ->assertJsonPath('status_code', 201);

        $this->assertDatabaseHas('{models}', [
            // assert at least one key field persisted
        ]);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'api')
                         ->postJson('/api/{models}', []);

        $response->assertStatus(422)
                 ->assertJsonPath('status', false)
                 ->assertJsonStructure(['errors']);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_{model}(): void
    {
        $record = {Model}::factory()->create();

        $response = $this->actingAs($this->user, 'api')
                         ->getJson("/api/{models}/{$record->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $record->id);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_{model}(): void
    {
        $this->actingAs($this->user, 'api')
             ->getJson('/api/{models}/99999')
             ->assertStatus(404)
             ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_{model}(): void
    {
        $record = {Model}::factory()->create();

        $payload = [
            // updated fields
        ];

        $response = $this->actingAs($this->user, 'api')
                         ->putJson("/api/{models}/{$record->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('status', true);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_{model}(): void
    {
        $this->actingAs($this->user, 'api')
             ->putJson('/api/{models}/99999', [])
             ->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_{model}(): void
    {
        $record = {Model}::factory()->create();

        $response = $this->actingAs($this->user, 'api')
                         ->deleteJson("/api/{models}/{$record->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', true);

        $this->assertSoftDeleted('{models}', ['id' => $record->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_{model}(): void
    {
        $this->actingAs($this->user, 'api')
             ->deleteJson('/api/{models}/99999')
             ->assertStatus(404);
    }
}
```

### Assertion Quick Reference

| What to assert                  | Method                                              |
|---------------------------------|-----------------------------------------------------|
| HTTP status code                | `->assertStatus(200)`                               |
| JSON key value                  | `->assertJsonPath('status', true)`                  |
| JSON key exists                 | `->assertJsonStructure(['data' => ['id', 'name']])` |
| Row exists in DB                | `$this->assertDatabaseHas('table', ['col' => val])` |
| Row soft-deleted                | `$this->assertSoftDeleted('table', ['id' => $id])`  |
| Row hard-deleted                | `$this->assertDatabaseMissing('table', [...])`      |
