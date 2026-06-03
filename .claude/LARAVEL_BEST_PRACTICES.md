# Laravel Best Practices

## Laravel Version Target

Use Laravel 10+ or Laravel 11+ standards.

Use PHP 8.2+.

Every PHP file must use:

```php
declare(strict_types=1);
```

---

## Controller Pattern

Controllers must be thin.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\DataTransferObjects\Order\CreateOrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $dto = CreateOrderDTO::fromRequest($request);

        $order = $this->orderService->create($dto);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], Response::HTTP_CREATED);
    }
}
```

---

## Form Request Pattern

Use FormRequest for all validation.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Enums\DeliveryMethod;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'payment_method' => [
                'required',
                Rule::enum(PaymentMethod::class),
            ],
            'delivery_method' => [
                'required',
                Rule::enum(DeliveryMethod::class),
            ],
            'coupon_code' => [
                'nullable',
                'string',
                'max:50',
                'exists:coupons,code',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'customer_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.exists' => 'العنوان المحدد غير موجود أو لا ينتمي لك.',
            'items.required' => 'يجب إضافة منتج واحد على الأقل.',
            'items.min' => 'يجب إضافة منتج واحد على الأقل.',
            'items.max' => 'لا يمكن إضافة أكثر من 100 منتج في طلب واحد.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('coupon_code')) {
            $this->merge([
                'coupon_code' => strtoupper(trim((string) $this->input('coupon_code'))),
            ]);
        }
    }
}
```

---

## DTO Pattern

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
        public ?string $couponCode = null,
        public ?string $customerNotes = null,
    ) {
    }

    public static function fromRequest(CreateOrderRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            userId: (int) $request->user()->id,
            addressId: (int) $validated['address_id'],
            paymentMethod: (string) $validated['payment_method'],
            deliveryMethod: (string) $validated['delivery_method'],
            items: (array) $validated['items'],
            couponCode: $validated['coupon_code'] ?? null,
            customerNotes: $validated['customer_notes'] ?? null,
        );
    }
}
```

---

## Enum Pattern

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case OutForDelivery = 'out_for_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::ReadyForPickup => 'Ready for pickup',
            self::OutForDelivery => 'Out for delivery',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Confirmed,
        ], true);
    }
}
```

---

## Repository Interface

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function update(Order $order, array $data): bool;

    public function findById(int $id): ?Order;

    public function findByIdWithRelations(int $id, array $relations): ?Order;

    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator;

    public function getPendingOrders(): Collection;
}
```

---

## Repository Implementation

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function findById(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function findByIdWithRelations(int $id, array $relations): ?Order
    {
        return Order::query()
            ->with($relations)
            ->find($id);
    }

    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function getPendingOrders(): Collection
    {
        return Order::query()
            ->where('status', 'pending')
            ->oldest()
            ->get();
    }
}
```

---

## Service Pattern

```php
<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DataTransferObjects\Order\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Exceptions\Order\InsufficientStockException;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Throwable;

final class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly InventoryService $inventory,
        private readonly OrderCalculationService $calculator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InsufficientStockException
     * @throws Throwable
     */
    public function create(CreateOrderDTO $dto): Order
    {
        $this->inventory->ensureAvailable($dto->items);

        return DB::transaction(function () use ($dto): Order {
            $calculation = $this->calculator->calculate($dto->items);

            $order = $this->orders->create([
                'user_id' => $dto->userId,
                'address_id' => $dto->addressId,
                'payment_method' => $dto->paymentMethod,
                'delivery_method' => $dto->deliveryMethod,
                'subtotal' => $calculation->subtotal,
                'discount_total' => $calculation->discountTotal,
                'delivery_fee' => $calculation->deliveryFee,
                'total' => $calculation->total,
                'status' => OrderStatus::Pending->value,
                'customer_notes' => $dto->customerNotes,
            ]);

            $this->inventory->lockForOrder($order, $dto->items);

            event(new OrderCreated($order));

            $this->logger->info('Order created', [
                'order_id' => $order->id,
                'user_id' => $dto->userId,
            ]);

            return $order->load(['items.product', 'address', 'user']);
        });
    }
}
```

---

## Action Pattern

```php
<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderItem;

final class CreateOrderItemsAction
{
    public function execute(Order $order, array $items): void
    {
        $rows = collect($items)
            ->map(fn (array $item): array => [
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        OrderItem::query()->insert($rows);
    }
}
```

---

## API Resource Pattern

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'delivery_method' => $this->delivery_method,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'customer_notes' => $this->customer_notes,
            'created_at' => $this->created_at?->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
```

---

## Exception Pattern

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Order;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        string $message = 'Insufficient stock.',
    ) {
        parent::__construct($message);
    }
}
```

---

## Service Provider Binding

Bind interfaces to implementations.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }
}
```

---

## Transaction Rules

Use transactions when:

- Creating order with items.
- Processing payment.
- Updating inventory.
- Applying coupon.
- Moving wallet balance.
- Writing multiple related records.

Example:

```php
DB::transaction(function (): void {
    // All write operations here.
});
```

---

## Query Rules

Bad:

```php
$orders = Order::all();
```

Good:

```php
$orders = Order::query()
    ->with(['items.product'])
    ->where('user_id', $userId)
    ->latest()
    ->paginate(20);
```

---

## Cache Rules

Use cache for:

- Categories.
- Settings.
- Product filters.
- Static lookup data.
- Expensive dashboard counts.

Example:

```php
$categories = Cache::remember(
    'categories.active',
    now()->addHour(),
    fn () => Category::query()->where('is_active', true)->get()
);
```

---

## Queue Rules

Queue:

- Emails.
- Push notifications.
- Payment callbacks.
- Analytics sync.
- Reports.
- Image processing.

Do not queue:

- Immediate validation.
- Critical database transaction steps.
- Response-required logic.

---

## Policy Rules

Use policies for authorization.

```php
public function view(User $user, Order $order): bool
{
    return $order->user_id === $user->id;
}
```

---

## Testing Rules

Feature test example:

```php
public function test_user_can_create_order(): void
{
    Event::fake();

    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();
    $product = Product::factory()->create(['quantity' => 10]);

    $response = $this
        ->actingAs($user)
        ->postJson('/api/v1/orders', [
            'address_id' => $address->id,
            'payment_method' => 'cash',
            'delivery_method' => 'delivery',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);

    Event::assertDispatched(OrderCreated::class);
}
```

---

## Required Laravel Quality Tools

Install and use:

```bash
composer require --dev larastan/larastan nunomaduro/pint pestphp/pest pestphp/pest-plugin-laravel
```

Run:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
php artisan test
```