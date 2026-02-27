# Frontend API Guide (MVP Beta)

## Authenticated Endpoints (`Authorization: Bearer <token>`)

### `GET /api/auth/me`
Returns logged user profile, language and theme preferences.

### `PATCH /api/auth/preferences`
Updates user preferences.

Payload:
```json
{
  "language_id": 1,
  "theme_preference": "dark"
}
```

### `POST /api/input/create`
Creates a new input and starts generation pipeline.

Form-data:
- `preset_id` (required)
- `image` (required)
- `title` (optional)

### `GET /api/jobs`
Lists user jobs.

### `GET /api/jobs/{job}`
Returns job detail.

### `GET /api/jobs/{job}/download`
Downloads generated video file from our server.

### `PATCH /api/jobs/{job}/title`
Renames input title.

Payload:
```json
{
  "title": "New title"
}
```

### `GET /api/jobs/quota`
Daily generation quota status.

Response:
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
Current credit balance.

### `GET /api/credits/statement`
Credit statement entries.

### `GET /api/credits/video-generations`
Generation + credit audit timeline.

### `GET /api/analytics/mvp-kpis` (admin/dev only)
Global MVP metrics for internal dashboard.

### `GET /api/analytics/ops-metrics` (admin/dev only)
Operational metrics for queue/failures/latency.

---

## Public Endpoints

### `GET /api/institutional`
List active institutional content with language fallback.

### `GET /api/institutional/{slug}`
Get institutional content by slug (supports translated slug).

### `GET /api/seo/{slug}`
Get SEO metadata by slug.

### `GET /api/social-networks`
List active social links.

### `POST /api/contacts`
Create support/contact message.

Payload:
```json
{
  "name": "John",
  "email": "john@example.com",
  "phone": "+1...",
  "message": "Need help"
}
```

---

## Realtime (Socket.IO)

Private channel:
- `user.{userId}`

Events:
- `UserJobUpdatedBroadcast`: job status/progress updates.
- `UserGenerationLimitAlertBroadcast`: quota warning/reached alert.

Frontend fallback:
- If socket disconnects, keep polling `GET /api/jobs` and `GET /api/jobs/quota`.

---

## Language Resolution
Backend decides language in this priority:
1. `user.language_id`
2. country header (`CF-IPCountry`, `CloudFront-Viewer-Country`, `X-Country-Code`)
3. `Accept-Language`
4. default language

Canonical institutional homepage slug:
- `initial-page-text`
