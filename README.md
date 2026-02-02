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

---

## 🧪 Tests & Quality

This project includes automated tests and maintains test coverage as a core standard.

Writing tests is treated as a non-negotiable practice and part of a personal commitment, following a pattern adopted in recent backend projects.

- Tests are written using **PHPUnit**
- New features are expected to include tests as part of the development workflow

---

## 📌 Project Status

🛠️ **MVP in development**  
This project is not production-ready yet and may change frequently.

---

## 📄 License

License details will be added later