# Frontend API Guide

## Pricing and Credits

- Pricing is calculated only on the backend.
- The frontend may estimate cost with `POST /api/input/estimate`, but `POST /api/input/create` always recalculates credits on the server.
- Credit consumption formula:
- `generation_cost_usd = model.cost_per_second_usd * duration_seconds`
- `credits_required = ceil(duration_seconds * model.credits_per_second)`
- Billing flow:
  - the wallet is charged before the external provider call
  - failed, canceled, timed out or output-less generations are refunded automatically
  - refunds are idempotent and recorded in the credit ledger

## Authenticated Endpoints

All endpoints below require `Authorization: Bearer <token>`.

### `GET /api/auth/me`

Returns the authenticated user profile.

### `POST /api/auth/logout`

Invalidates the current authenticated session/token.

### `PATCH /api/auth/preferences`

Updates user preferences.

Payload:

```json
{
  "language_id": 1,
  "theme_preference": "dark"
}
```

### `GET /api/models`

Lists models available for generation. Only active, public models with defined `cost_per_second_usd` and `credits_per_second` are returned.

Query params:

- `per_page` (optional, default `15`)
- `page` (optional, default `1`)

Returned model fields:

- `id`
- `name`
- `slug`
- `provider_model_key`
- `active`
- `public_visible`
- `default`
- `available_for_generation`
- `cost_per_second_usd`
- `credits_per_second`
- `sort_order`

### `GET /api/models/{model}/presets`

Lists active presets for a model.

Query params:

- `per_page` (optional)
- `page` (optional)
- `aspect_ratio` (optional: `16:9`, `9:16`, `1:1`)
- `tag` (optional)
- `tags[]` (optional, multi-tag filter)

Returned preset fields:

- `id`
- `default_model_id`
- `name`
- `prompt`
- `negative_prompt`
- `duration_seconds`
- `preview_image_url`
- `preview_video_url`
- `aspect_ratio`
- `tags`

### `GET /api/models/{model}/presets/filters`

Returns available preset filter values for the selected model.

Response:

```json
{
  "data": {
    "aspect_ratios": ["16:9", "9:16"],
    "tags": [
      {
        "id": 1,
        "name": "Anime",
        "slug": "anime"
      }
    ]
  }
}
```

### `POST /api/input/estimate`

Returns the current backend estimate for a generation request.

Payload:

```json
{
  "model_id": 1,
  "preset_id": 12,
  "duration_seconds": 5
}
```

Response:

```json
{
  "data": {
    "model_id": 1,
    "preset_id": 12,
    "duration_seconds": 5,
    "credits_required": 3,
    "model_cost_per_second_usd": "0.1500",
    "model_credits_per_second": "0.6000",
    "estimated_generation_cost_usd": "0.7500"
  }
}
```

Validation errors return `422`.

### `POST /api/input/create`

Creates a generation input and charges the required credits atomically.

Multipart form-data:

- `model_id` (required)
- `preset_id` (required)
- `duration_seconds` (optional, if the UI allows overriding the preset duration)
- `image` (required)
- `title` (optional)

Response:

```json
{
  "data": {
    "id": 101,
    "model_id": 1,
    "preset_id": 12,
    "user_id": 9,
    "status": "created",
    "title": "input.png",
    "original_filename": "input.png",
    "mime_type": "image/png",
    "size_bytes": 512000,
    "duration_seconds": 5,
    "estimated_cost_usd": "0.3500",
    "credits_charged": 1,
    "billing_status": "charged"
  }
}
```

Notes:

- The backend ignores any client-sent `credits_required` or price fields.
- Insufficient balance returns `422` with message `Insufficient balance`.

### `GET /api/jobs`

Lists the authenticated user's jobs.

Each item includes:

- input identifiers and status
- selected `model` and `preset`
- `duration_seconds`
- `estimated_cost_usd`
- `credits_charged`
- `billing_status`
- current prediction data when available

### `GET /api/jobs/{job}`

Returns the job detail for a specific input.

### `GET /api/jobs/{job}/download`

Downloads the generated video file hosted by the backend.

### `PATCH /api/jobs/{job}/title`

Renames an existing input title.

Payload:

```json
{
  "title": "New title"
}
```

### `POST /api/jobs/{job}/cancel`

Cancels an in-flight generation. Successful cancellation may trigger an automatic refund.

### `POST /api/prediction/cancel`

Legacy-compatible cancellation endpoint.

Payload:

```json
{
  "input_id": 101
}
```

### `GET /api/jobs/quota`

Returns daily generation quota status.

```json
{
  "data": {
    "daily_limit": 3,
    "used_today": 2,
    "remaining_today": 1,
    "near_limit": true,
    "limit_reached": false
  }
}
```

### `GET /api/credits/balance`

Returns the current wallet balance.

```json
{
  "data": {
    "user_id": 9,
    "credit_balance": 12,
    "updated_at": "2026-03-07T10:12:00Z"
  }
}
```

### `GET /api/credits/statement`

Returns paginated credit ledger entries.

Each entry includes:

- `delta`
- `amount`
- `balance_before`
- `balance_after`
- `reason`
- `operation_type`
- `reference_type`
- `reference_id`
- `model`
- `preset`
- `duration_seconds`
- `generation_cost_usd`
- `metadata`
- `created_at`

Typical `operation_type` values:

- `credit_purchase`
- `generation_debit`
- `generation_refund`
- `invite_redemption`
- `credit_debit`
- `credit_refund`

### `GET /api/credits/video-generations`

Returns paginated generation history joined with prediction state and credit audit information.

Each entry includes:

- input status and title
- selected model and preset
- `duration_seconds`
- `estimated_cost_usd`
- prediction status, failure details and output URL
- `credits_debited`
- `credits_refunded`
- `credits_used`
- `credits_charged`
- `billing_status`
- `ledger_entries`
- `credit_events`

### `GET /api/analytics/mvp-kpis`

Internal admin/dev analytics endpoint.

### `GET /api/analytics/ops-metrics`

Internal admin/dev operational metrics endpoint.

## Public Endpoints

### `GET /api/institutional`

Lists active institutional content with language fallback.

### `GET /api/institutional/{slug}`

Returns institutional content by slug.

### `GET /api/seo/{slug}`

Returns SEO metadata by slug.

### `GET /api/social-networks`

Lists active social links.

### `POST /api/contacts`

Creates a contact/support request.

Payload:

```json
{
  "name": "John",
  "email": "john@example.com",
  "phone": "+1...",
  "message": "Need help"
}
```

## Realtime

Private channel:

- `user.{userId}`

Events:

- `UserJobUpdatedBroadcast`
- `UserGenerationLimitAlertBroadcast`
- `UserSessionLoggedOutBroadcast`

Fallback:

- poll `GET /api/jobs`
- poll `GET /api/jobs/quota`

## Language Resolution

Backend resolves language in this order:

1. `user.language_id`
2. country header (`CF-IPCountry`, `CloudFront-Viewer-Country`, `X-Country-Code`)
3. `Accept-Language`
4. default language
