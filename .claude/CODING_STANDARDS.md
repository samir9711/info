# Coding Standards

## Global Rules

- Use strict types.
- Use clear names.
- Use dependency injection.
- Avoid magic numbers.
- Avoid hidden side effects.
- Avoid global helpers in business logic.
- Keep classes small.
- Keep methods small.
- Prefer explicit code over clever code.
- Every public method must have a return type.
- Every parameter must have a type.
- Every feature must be testable.

---

## PHP Rules

Every PHP file must start with:

```php
<?php

declare(strict_types=1);
```

---

## Naming

### Classes

Use PascalCase.

```php
class OrderService
{
}
```

### Methods

Use camelCase.

```php
public function calculateTotal(): Money
{
}
```

### Variables

Use camelCase.

```php
$orderTotal = 1000;
```

### Constants

Use UPPER_SNAKE_CASE.

```php
private const MAX_ORDER_ITEMS = 100;
```

### Database

Use snake_case.

```text
user_id
order_items
created_at
```

### Booleans

Use prefixes:

```php
$isActive
$hasPermission
$canCancel
$shouldNotify
```

---

## Function Length

Maximum: 50 lines.

If a method becomes longer:

- Extract private methods.
- Extract action class.
- Extract service class.
- Extract value object.

---

## Class Length

Maximum: 300 lines.

If a class becomes longer:

- Split responsibilities.
- Move calculations to another service.
- Move persistence to repository.
- Move side effects to events/listeners.

---

## Controller Rules

Controllers must only:

- Receive request.
- Create DTO.
- Call service/action.
- Return resource/response.
- Catch only expected business exceptions if needed.

Bad:

```php
public function store(Request $request)
{
    $product = Product::find($request->product_id);

    if ($product->quantity < $request->quantity) {
        return response()->json(['error' => 'No stock'], 422);
    }

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
        Response::HTTP_CREATED
    );
}
```

---

## Service Rules

Services must:

- Contain business logic.
- Use dependency injection.
- Use transactions when needed.
- Throw business exceptions.
- Log important actions.
- Return domain objects or DTOs.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DataTransferObjects\Order\CreateOrderDTO;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

final class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto): Order {
            $order = $this->orders->create([
                'user_id' => $dto->userId,
                'address_id' => $dto->addressId,
                'status' => 'pending',
            ]);

            $this->logger->info('Order created', [
                'order_id' => $order->id,
                'user_id' => $dto->userId,
            ]);

            return $order;
        });
    }
}
```

---

## Repository Rules

Repositories must:

- Only access data.
- Not contain business rules.
- Not send emails.
- Not dispatch notifications.
- Not validate requests.
- Not return raw query builders to controllers.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function findById(int $id): ?Order;

    public function findByIdWithRelations(int $id, array $relations): ?Order;
}
```

---

## DTO Rules

DTOs must:

- Be immutable.
- Use typed properties.
- Use explicit constructor.
- Avoid database queries.
- Avoid request validation logic.

Example:

```php
<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Order;

use App\Http\Requests\Order\CreateOrderRequest;

final readonly class CreateOrderDTO
{
    public function __construct(
        public int $userId,
        public int $addressId,
        public string $paymentMethod,
        public string $deliveryMethod,
        public array $items,
        public ?string $couponCode,
    ) {
    }

    public static function fromRequest(CreateOrderRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            addressId: (int) $request->validated('address_id'),
            paymentMethod: (string) $request->validated('payment_method'),
            deliveryMethod: (string) $request->validated('delivery_method'),
            items: (array) $request->validated('items'),
            couponCode: $request->validated('coupon_code'),
        );
    }
}
```

---

## Exception Rules

Use custom exceptions for business errors.

Bad:

```php
throw new Exception('No stock');
```

Good:

```php
throw new InsufficientStockException($productId);
```

---

## Logging Rules

Log:

- Failed payments.
- Failed external API calls.
- Order creation.
- Login anomalies.
- Authorization failures.
- Critical business operations.

Do not log:

- Passwords.
- Tokens.
- Credit card numbers.
- Full payment credentials.
- Sensitive personal data.

Good:

```php
$this->logger->warning('Payment failed', [
    'order_id' => $order->id,
    'gateway' => $gatewayName,
    'reason' => $exception->getMessage(),
]);
```

---

## Validation Rules

- Validate all external input.
- Use FormRequest.
- Use custom messages when needed.
- Validate ownership.
- Validate enum values.
- Validate uploaded files.
- Validate pagination parameters.

Example:

```php
public function rules(): array
{
    return [
        'address_id' => ['required', 'integer', 'exists:addresses,id'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
        'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
    ];
}
```

---

## API Response Rules

Never return inconsistent responses.

Success:

```json
{
  "success": true,
  "message": "Created successfully",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Something went wrong",
  "errors": {}
}
```

---

## TypeScript Rules

- No `any`.
- Use interfaces for models.
- Use union types for fixed strings.
- Use API response types.
- Use typed navigation.
- Use typed hooks.
- Handle loading/error/empty states.

Bad:

```typescript
const ProductCard = ({ product }: any) => {
  return <Text>{product.name}</Text>;
};
```

Good:

```typescript
interface ProductCardProps {
  product: Product;
  onPress: (product: Product) => void;
}

export function ProductCard({ product, onPress }: ProductCardProps) {
  return (
    <Pressable onPress={() => onPress(product)}>
      <Text>{product.name}</Text>
    </Pressable>
  );
}
```

---

## Comments

Bad:

```php
// Get user
$user = User::find($id);
```

Good:

```php
// Cache this because the profile is used by several dashboard widgets
// and changes rarely during a single user session.
$user = Cache::remember("user_profile:{$id}", 3600, fn () => User::find($id));
```

---

## Never Allow

- Business logic in controllers.
- Database queries in controllers.
- Missing type hints.
- Missing return types.
- Raw model responses from API.
- Unvalidated input.
- Unhandled external API errors.
- Magic numbers.
- More than 3 nesting levels.
- Methods with more than 4 parameters.
- `any` in TypeScript.
- Inline secrets.
- Huge components.
- Huge services.
- Copy-pasted logic.

---

## Always Require

- FormRequest validation.
- API Resources.
- DTOs for complex input.
- Services for business logic.
- Repositories for data access.
- Transactions for multi-step writes.
- Custom exceptions for business errors.
- Tests for core logic.
- Logging for important operations.
- Pagination for lists.
- Authorization checks.