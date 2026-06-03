# Security Guidelines

## Main Security Rule

Never trust client input.

Everything coming from:

- Mobile app.
- Web app.
- API client.
- Admin panel.
- Webhook.
- External service.

Must be validated, authorized, and sanitized.

---

## Authentication

Use Laravel Sanctum for API authentication.

Rules:

- Protect private routes with `auth:sanctum`.
- Rotate tokens when needed.
- Revoke tokens on logout.
- Revoke all tokens on password change.
- Do not return tokens in logs.
- Do not store plain tokens.

Example:

```php
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
```

---

## Authorization

Use Policies and Gates.

Do not authorize only by checking route IDs.

Bad:

```php
$order = Order::find($id);
```

Good:

```php
$order = Order::query()->findOrFail($id);

$this->authorize('view', $order);
```

Policy example:

```php
public function view(User $user, Order $order): bool
{
    return $order->user_id === $user->id;
}
```

---

## Input Validation

Use FormRequest for all write operations.

Validate:

- Required fields.
- Types.
- Min/max lengths.
- Enum values.
- Ownership.
- Uploaded files.
- Numeric ranges.
- Date ranges.
- Nested array structure.

Example:

```php
'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
```

---

## Ownership Validation

Always validate that the resource belongs to the authenticated user.

Example:

```php
Rule::exists('addresses', 'id')->where('user_id', $this->user()->id)
```

---

## SQL Injection Protection

Use:

- Eloquent.
- Query Builder.
- Bound parameters.

Do not concatenate raw SQL.

Bad:

```php
DB::select("SELECT * FROM users WHERE email = '$email'");
```

Good:

```php
User::query()->where('email', $email)->first();
```

---

## XSS Protection

Rules:

- Escape output on web.
- Sanitize user-generated HTML.
- Do not trust rich text input.
- Use API Resources to control output.
- Strip tags when HTML is not required.

Example:

```php
$cleanNotes = strip_tags($request->input('customer_notes'));
```

---

## CSRF

For web routes:

- Keep CSRF enabled.

For API routes:

- Use token authentication.
- Do not use session-based auth unless required.

---

## Rate Limiting

Apply rate limits to sensitive endpoints.

Examples:

```php
Route::middleware(['auth:sanctum', 'throttle:10,60'])->post('/orders', [OrderController::class, 'store']);
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);
```

Recommended limits:

```text
login: 5 attempts per minute
register: 3 attempts per minute
password reset: 3 attempts per hour
create order: 10 per hour
payment callback: gateway-specific
coupon check: 30 per hour
```

---

## Password Rules

- Hash with Laravel Hash facade.
- Never store plain passwords.
- Never log passwords.
- Use strong password validation.
- Invalidate sessions after password change.

Example:

```php
'password' => ['required', 'string', 'min:8', 'confirmed']
```

---

## File Upload Security

Validate:

- MIME type.
- File size.
- Extension.
- Image dimensions when needed.

Rules:

- Store uploads outside public path when private.
- Generate random filenames.
- Never trust original filename.
- Scan files if business requires it.
- Do not allow executable uploads.

Example:

```php
'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']
```

---

## Payment Security

Rules:

- Never store full card data.
- Use payment gateway tokens.
- Verify webhook signatures.
- Use idempotency keys.
- Log payment status without sensitive data.
- Treat payment callbacks as untrusted.
- Re-check payment status from gateway when needed.

---

## Webhook Security

Every webhook must:

- Verify signature.
- Check timestamp.
- Use idempotency.
- Log request ID.
- Reject replay attacks.
- Return correct HTTP status.

Example checks:

```text
signature valid
timestamp fresh
event id not processed before
payload schema valid
```

---

## API Error Security

Do not leak internal details.

Bad:

```json
{
  "message": "SQLSTATE[23000]: Integrity constraint violation..."
}
```

Good:

```json
{
  "message": "Unable to process request."
}
```

In production:

- Hide traces.
- Hide SQL errors.
- Hide server paths.
- Hide environment values.

---

## Logging Security

Never log:

- Passwords.
- Tokens.
- Full credit card numbers.
- Secret keys.
- Private documents.
- OTP codes.
- Reset tokens.

Safe logging:

```php
Log::warning('Unauthorized order access attempt', [
    'user_id' => $user->id,
    'order_id' => $order->id,
    'ip' => request()->ip(),
]);
```

---

## Environment Variables

Rules:

- Never commit `.env`.
- Never hardcode secrets.
- Use different keys per environment.
- Rotate leaked keys immediately.
- Use config files to read env values.

Bad:

```php
$apiKey = 'sk_live_xxx';
```

Good:

```php
$apiKey = config('services.payment.api_key');
```

---

## CORS

Rules:

- Do not allow `*` in production with credentials.
- Restrict origins.
- Restrict methods.
- Restrict headers.

---

## Mobile App Security

Rules:

- Store tokens in secure storage.
- Do not store secrets in the app.
- Do not log API responses containing sensitive data.
- Validate deep links.
- Use HTTPS only.
- Handle token expiration.
- Clear sensitive data on logout.

---

## Admin Security

Rules:

- Use role-based access control.
- Require strong passwords.
- Add audit logging.
- Protect destructive actions.
- Add confirmation for dangerous operations.
- Restrict admin routes.
- Add two-factor authentication when possible.

---

## Audit Logging

Log sensitive operations:

- Login.
- Logout.
- Failed login.
- Password change.
- Order cancellation.
- Refund.
- Payment failure.
- Admin changes.
- Role changes.
- User ban/unban.

Audit log fields:

```text
actor_id
actor_type
action
target_id
target_type
ip_address
user_agent
metadata
created_at
```

---

## Security Checklist

Before accepting code:

```text
[ ] All input is validated.
[ ] Authorization is checked.
[ ] Ownership is checked.
[ ] No secrets are hardcoded.
[ ] No sensitive data is logged.
[ ] File uploads are validated.
[ ] Payment webhooks are verified.
[ ] API errors do not leak internals.
[ ] Rate limiting is applied.
[ ] Tests cover forbidden access.
[ ] Tests cover validation failure.
```