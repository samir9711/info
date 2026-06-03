# Project Architecture

## Project Type

Mall Mobile App / E-commerce platform.

Backend: Laravel API  
Frontend: React Native  
Database: MySQL / PostgreSQL  
Cache: Redis  
Queue: Redis / Database Queue  
Auth: Laravel Sanctum

---

## Core Architecture Rule

Do not write feature code directly inside controllers.

Every feature must follow this flow:

```text
Request
  -> FormRequest
  -> Controller
  -> DTO
  -> Service
  -> Action / Repository
  -> Model / Database
  -> Resource
  -> JSON Response
```

---

## Backend Layers

### 1. Presentation Layer

Responsible only for HTTP input and HTTP output.

Allowed files:

```text
app/Http/Controllers
app/Http/Requests
app/Http/Resources
app/Http/Middleware
```

Rules:

- Controllers must be thin.
- Controllers must not contain business logic.
- Controllers must not query database directly.
- Controllers must call services or actions.
- Validation must be inside FormRequest.
- API output must use Resource classes.

Bad:

```php
public function store(Request $request)
{
    $order = Order::create($request->all());

    return response()->json($order);
}
```

Good:

```php
public function store(CreateOrderRequest $request): JsonResponse
{
    $dto = CreateOrderDTO::fromRequest($request);

    $order = $this->orderService->create($dto);

    return $this->success(
        new OrderResource($order),
        'Order created successfully',
        201
    );
}
```

---

### 2. Application Layer

Responsible for use cases and business orchestration.

Allowed files:

```text
app/Services
app/Actions
app/DataTransferObjects
```

Rules:

- Services coordinate business logic.
- Actions perform one specific task.
- DTOs transfer validated data.
- Services must use dependency injection.
- Services must use transactions for multi-step operations.
- Services must not return raw arrays when domain objects are expected.

---

### 3. Domain Layer

Responsible for business entities and rules.

Allowed files:

```text
app/Models
app/Enums
app/Events
app/Observers
app/Policies
app/Exceptions
```

Rules:

- Models should not become business logic containers.
- Use Enums for fixed states.
- Use Policies for authorization.
- Use Events for side effects.
- Use Observers for model lifecycle behavior.
- Use custom Exceptions for business errors.

---

### 4. Infrastructure Layer

Responsible for external systems and persistence.

Allowed files:

```text
app/Repositories
app/Jobs
app/Notifications
app/Mail
app/Integrations
```

Rules:

- Repositories handle database access.
- Jobs handle async work.
- Integrations handle external APIs.
- External API failures must be logged.
- Retryable work must be queued.

---

## Laravel Directory Structure

```text
app/
├── Actions/
│   ├── Order/
│   ├── Product/
│   ├── Cart/
│   └── Payment/
├── DataTransferObjects/
│   ├── Order/
│   ├── Product/
│   └── Payment/
├── Enums/
│   ├── OrderStatus.php
│   ├── PaymentStatus.php
│   ├── PaymentMethod.php
│   └── DeliveryMethod.php
├── Exceptions/
│   ├── Order/
│   ├── Payment/
│   └── BaseBusinessException.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Integrations/
│   ├── PaymentGateways/
│   └── DeliveryProviders/
├── Jobs/
├── Models/
├── Notifications/
├── Observers/
├── Policies/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
└── Services/
    ├── Order/
    ├── Product/
    ├── Cart/
    ├── Payment/
    └── Inventory/
```

---

## Required Patterns

### Repository Pattern

Use for data access.

```text
Controller -> Service -> Repository -> Model
```

Rules:

- Repository must not contain business logic.
- Repository must return models, collections, or paginated results.
- Interface goes in `app/Repositories/Contracts`.
- Implementation goes in `app/Repositories/Eloquent`.

---

### Service Pattern

Use for business logic.

Rules:

- Service coordinates multiple actions.
- Service validates business rules.
- Service manages transactions.
- Service throws business exceptions.
- Service logs important operations.

---

### Action Pattern

Use for single-purpose operations.

Rules:

- One action = one job.
- Method name should be `execute`.
- Actions should be easy to test.
- Do not put unrelated logic into one action.

Example:

```text
CreateOrderAction
CreateOrderItemsAction
ApplyCouponAction
LockInventoryAction
GenerateOrderQrCodeAction
```

---

### DTO Pattern

Use for structured data transfer.

Rules:

- DTOs should be immutable.
- Use `readonly` when possible.
- No database queries inside DTOs.
- DTOs can be created from requests.
- DTOs must have strict types.

---

### Resource Pattern

Use for API responses.

Rules:

- Never return Eloquent models directly.
- Hide internal fields.
- Format dates and money.
- Load relations conditionally.
- Keep API response stable.

---

### Event Pattern

Use for side effects.

Good side effects:

```text
Send notification
Send email
Sync analytics
Sync accounting system
Update reporting tables
```

Rules:

- Do not slow down main request.
- Heavy listeners should use queues.
- Events should contain enough context.

---

## API Versioning

All API routes must be versioned.

```text
/api/v1/products
/api/v1/orders
/api/v1/cart
```

Controllers must follow:

```text
app/Http/Controllers/Api/V1
```

---

## Error Response Format

All API errors must use the same structure:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ]
  },
  "meta": {
    "request_id": "uuid"
  }
}
```

---

## Success Response Format

All API success responses must use the same structure:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {},
  "meta": {}
}
```

---

## Database Rules

- Use migrations for all schema changes.
- Add indexes on foreign keys.
- Add indexes on frequently searched fields.
- Use soft deletes only when needed.
- Use database constraints.
- Use transactions for multi-step writes.
- Avoid nullable fields unless business requires them.
- Never store money as float.
- Store money as integer minor units or decimal with fixed precision.

---

## Performance Rules

- Use eager loading to avoid N+1 queries.
- Use pagination for lists.
- Use cache for expensive reads.
- Use queues for slow tasks.
- Use database indexes.
- Avoid loading unnecessary relations.
- Avoid returning huge payloads.
- Use API Resources to control output.

---

## Testing Architecture

```text
tests/
├── Feature/
│   └── Api/
│       └── V1/
├── Unit/
│   ├── Services/
│   ├── Actions/
│   └── DTOs/
└── Integration/
```

Required tests for every feature:

- Happy path.
- Validation failure.
- Authorization failure.
- Business rule failure.
- Database changes.
- Events dispatched.
- Jobs dispatched.

---

## React Native Architecture

```text
src/
├── api/
│   ├── client.ts
│   ├── endpoints.ts
│   └── interceptors.ts
├── components/
│   ├── common/
│   ├── products/
│   ├── cart/
│   └── orders/
├── config/
├── constants/
├── hooks/
├── models/
├── navigation/
├── screens/
├── services/
├── store/
├── theme/
├── types/
└── utils/
```

---

## React Native Rules

- Use TypeScript.
- No `any`.
- Use API services, not raw fetch inside screens.
- Use custom hooks for reusable logic.
- Use React Query or Redux Toolkit for state.
- Use memoization only when useful.
- Keep styles outside JSX.
- Use error boundaries.
- Handle loading, empty, success, and error states.
- Never store sensitive tokens in AsyncStorage without encryption.

---

## Senior-Level Definition

Code is senior-level only if it has:

- Clear architecture.
- Strong typing.
- Input validation.
- Business exceptions.
- Consistent responses.
- Tests.
- Logging.
- Security checks.
- Performance awareness.
- Maintainable file structure.