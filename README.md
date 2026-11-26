# Order Service

🇬🇧 English · [🇷🇺 Русский](#русский)

Orders and payments microservice (Laravel 11) behind [`gateway-service`](../gateway-service). Fictional data — portfolio project.

## Features

- Create orders from a product catalogue: **prices are computed on the server** and snapshotted into order items.
- Customers see only their own orders (foreign ids → 404).
- Lifecycle `new → paid` / `new → cancelled`, enforced under row locks.
- **Payments** through the `PaymentProvider` adapter interface (`FakePayProvider` is a fictional acquirer with a payment page and signed webhooks; no card data is handled):
  - `Idempotency-Key` and "one pending payment per order" prevent double charges;
  - webhook: HMAC-SHA256 over the raw body, amount check, row lock, **duplicates are no-ops**;
  - money arriving for a cancelled order is marked `refund_required`.
- Events `OrderCreated` / `OrderPaid` → listener → queued job (**dispatched after commit**) → `notification-service`.
- Authentication is delegated to the gateway: requests must carry the shared `X-Api-Key`; identity comes from `X-User*` headers.
- `GET /api/openapi.json` — the document the gateway imports routes from.

## Architecture

```
Controller → FormRequest → DTO → Action → Repository (interface) → Model
CreateOrder · GetCustomerOrder · CancelOrder · StartPayment · HandlePaymentWebhook
PaymentProvider ← ProviderRegistry (Strategy/Adapter)    Events → Listener → Job → NotificationClient
```

SOLID: one action per use case; a new provider is a new class plus one tag; actions depend on interfaces.
DRY: ownership lives only in `GetCustomerOrder`, one listener serves every customer notification.

## Run

The easiest way is the platform script in `gateway-service` (`./scripts/platform-up.sh`). Standalone:

```bash
cp .env.example .env      # set INTERNAL_API_KEY and FAKEPAY_WEBHOOK_SECRET
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## API examples (through the gateway)

```bash
GW=localhost:8000/gateway/orders
A=(-H "Authorization: Bearer <token>" -H "Content-Type: application/json" -H "Accept: application/json")

curl $GW/api/products "${A[@]}"
curl -X POST $GW/api/orders "${A[@]}" -d '{"delivery_address":"12 Lake St","items":[{"sku":"BOX-M","quantity":3}]}'
curl -X POST $GW/api/orders/1/pay "${A[@]}" -H "Idempotency-Key: a1" -d '{"provider":"fakepay"}'
curl -X POST $GW/api/orders/1/cancel "${A[@]}"

# play the provider (local environment)
docker compose exec app php artisan payments:simulate <payment-uuid>
```

Quality: `composer test`, `composer analyse` (Larastan 8), `composer lint`, `composer docs`.

---

## Русский

Микросервис заказов и оплаты на Laravel 11 за `gateway-service`.

**Что внутри.** Заказ собирается из каталога, цены считает сервер и фиксирует в позициях. Клиент видит только свои заказы. Статусы `new → paid | cancelled` меняются под блокировкой строки. Оплата — через интерфейс `PaymentProvider` (вымышленный эквайер `FakePay`, карточные данные сервис не обрабатывает): `Idempotency-Key` и один ожидающий платёж на заказ против двойного списания; вебхук с HMAC-подписью, проверкой суммы и идемпотентностью; деньги по отменённому заказу помечаются `refund_required`. События → Job после коммита → `notification-service`. Аутентификацию делает шлюз: сервис проверяет внутренний ключ и читает заголовки `X-User*`.

**Слои:** Controller → FormRequest → DTO → Action → Repository (интерфейс) → Model.

**Запуск:** из `gateway-service` — `./scripts/platform-up.sh`; отдельно — блок выше. Проверки: `composer test`, `composer analyse`, `composer lint`.
