# inkai.video

**inkai.video** is a backend MVP project that integrates with the **Replicate API** to generate AI videos from images, focused on the **tattoo artist niche**.

This repository contains **only the backend** of the MVP and is currently under active development.

---

## 🚀 Project Overview

The goal of **inkai.video** is to provide a clean and scalable backend architecture for generating videos through AI, using images as input.  
It is designed as the first step of a larger platform that will evolve in the future.

This MVP focuses on building a solid foundation to support future growth, new features, and additional integrations.

---

## 🧱 Tech Stack

- **PHP / Laravel**
- **PHPUnit** (automated testing)
- **Filament** (admin panel / internal tools)
- **Redis** (queues + pub/sub)
- **Socket.IO / Laravel Echo Server** (realtime layer)
- **MySQL** (primary relational data store)
- **Spatie Laravel Permission** (roles and access control)
- **Spatie Media Library** (media handling abstraction)

---

## 🧠 Architecture & Concepts

This project follows a **Domain-Oriented architecture**, where each domain is responsible for its own:

- Contracts (interfaces)
- Implementations
- Business rules

Domains can relate to and interact with each other, but the architecture encourages separation of responsibilities and clear boundaries.

### Interface-driven development

A key goal of this project is to drive development using **interfaces** as much as possible.  
This approach makes it easier to:

- Replace Replicate with another generative AI provider in the future
- Support multiple providers (multi-provider architecture)
- Extend the platform without rewriting core business logic

### Modular monolith by domain

The project is organized as a **domain-driven modular monolith** inside `app/Domain/*`.  
Each domain keeps its own HTTP entrypoints and business implementation:

- Routes
- Controllers
- Requests
- Use Cases
- Models
- Resources
- Tests

Current core domains:

- `Auth`
- `Videos`
- `AIProviders`
- `AIModels`
- `Credits`
- `Payments`
- `Broadcasting`
- `Invites`

This keeps boundaries explicit while preserving deployment simplicity.

### Use-case first application layer

Business logic is centered in **Use Cases** and not in controllers.  
Controllers are intentionally thin and focused on transport concerns:

- Input validation
- Use case orchestration
- Response serialization

This improves testability and reduces coupling between HTTP and business rules.

### Contracts and DTOs

The codebase uses interface-driven design and DTOs to keep cross-domain contracts explicit:

- Infrastructure details behind contracts
- Business flows expressed through use cases
- Clear input/output payloads through DTOs/resources

This makes provider replacement and feature expansion safer.

### Event-driven side effects

Video lifecycle side effects (provider calls, output processing, broadcasts) are modeled with:

- Domain events
- Listeners
- Async jobs

This design keeps synchronous requests responsive and pushes heavy/non-critical work to queues.

---

## 📡 Realtime & Socket.IO

Realtime updates are delivered through a dedicated Broadcasting module and Socket.IO.

High-level design:

- Laravel events publish job updates
- Broadcasting abstractions define channels and authorization
- Redis provides pub/sub
- Laravel Echo Server (Socket.IO) distributes realtime updates to clients

Why Socket.IO in this MVP:

- Better user experience for long-running video generation jobs
- Reduces frontend polling pressure on API endpoints
- Easier progressive UI updates (`queued`, `processing`, `failed`, `done`)
- Keeps backend ready for scale with decoupled event distribution

---

## 🔐 Security & Access Control

Security is treated as a baseline concern, not an optional layer.

Implemented protections include:

- JWT protection for authenticated API routes
- Role model with `admin`, `dev`, and `platform_user`
- Admin panel access limited to `admin` and `dev`
- Suspended/inactive user login blocking
- Login audit trail (IP, device/browser metadata, success/failure reason)
- Route-level rate limiting for sensitive and high-traffic flows
- `X-Robots-Tag` + `robots.txt` policy to prevent indexing of `/api` and `/admin`

---

## 🧪 Tests & Quality

This project includes automated tests and maintains test coverage as a core standard.

Writing tests is treated as a non-negotiable practice and part of a personal commitment, following a pattern adopted in recent backend projects.

- Tests are written using **PHPUnit**
- New features are expected to include tests as part of the development workflow

Testing strategy:

- Unit tests for use cases and support components
- Integration tests for endpoint contracts and auth flows
- Broadcasting authorization tests
- Security regression tests for protected routes

Quality principles:

- Thin controllers, heavy domain tests
- Explicit contracts and deterministic assertions
- Every critical change should add or update tests

---

## ⚙️ Infrastructure Notes

Local environment (Sail/Compose) includes:

- Application runtime
- MySQL
- Redis
- Socket.IO service

Operationally relevant decisions:

- Queue-first processing for non-blocking heavy tasks
- Redis-backed realtime distribution
- Domain modularity to keep future extraction and scaling options open

---

## 🗺️ Evolution Direction

The MVP is focused, but architecture choices intentionally support the next steps:

- Additional generative AI providers
- Advanced billing and payment provider expansion
- Stronger observability (metrics/tracing)
- Increased horizontal scalability for workers/realtime nodes
- More granular authorization policies across domains

---

## ⚖️ Trade-offs & Rationale

Every major technical decision in this MVP was made with explicit trade-offs:

### Domain modular monolith vs microservices

Why chosen:

- Faster product iteration
- Lower operational overhead
- Easier local development and onboarding

Trade-off:

- Requires discipline to keep module boundaries clean as the codebase grows
- Team scaling may eventually justify service extraction for specific domains

### Use case + contracts pattern vs direct framework-driven code

Why chosen:

- Better testability and explicit business boundaries
- Easier provider replacement and long-term maintainability

Trade-off:

- More boilerplate in the short term
- Slightly slower initial feature delivery for very small/simple endpoints

### Socket.IO + Redis broadcasting vs pure polling

Why chosen:

- Better UX for async operations (video jobs)
- Lower repeated read load from aggressive client polling
- Clear event-driven model for status propagation

Trade-off:

- Additional infrastructure/service to operate
- Requires channel authorization and realtime observability discipline

### Strict route protection + throttling vs maximum openness

Why chosen:

- Reduces abuse risk and accidental data exposure
- Improves resilience under hostile or noisy traffic

Trade-off:

- Clients must correctly handle `401`, `403`, and `429` responses
- Misconfigured limits can impact legitimate high-throughput use cases

### Comprehensive test coverage vs faster short-term coding

Why chosen:

- Safer refactors
- Better regression control in auth, billing, and async workflows

Trade-off:

- Higher upfront development effort
- Test maintenance cost as domains evolve

## 📌 Project Status

🛠️ **MVP in development**  
This project is not production-ready yet and may change frequently.

---

## 📄 License

License details will be added later
