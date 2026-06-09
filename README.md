# AI Customer Operations Platform

An AI-powered automation stack for small-to-medium businesses that handles lead capture, email triage, knowledge-base-powered replies, and human-in-the-loop approval — all from a single dashboard.

## Features

- **Lead Management** — Automatically capture and qualify leads from website forms, email, and other channels.
- **Email Triage** — AI reads incoming emails, classifies intent, and drafts context-aware replies using your knowledge base.
- **Knowledge Base (RAG)** — Vector search over your docs so the AI gives accurate, grounded answers.
- **Human Approval Loop** — Nothing goes out without a human OK. Review, edit, or reject AI drafts from the dashboard.
- **Workflow Automation** — n8n orchestrates multi-step processes (lead nurture sequences, follow-ups, escalations).

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Dashboard | Laravel 12 + Inertia.js (React/TypeScript) + Tailwind CSS |
| Workflow Engine | n8n (self-hosted) |
| Database | PostgreSQL |
| Vector Store | Qdrant |
| AI | OpenAI API (GPT-4) |
| Deployment | Docker Compose on a VPS |

## Project Structure

```
├── laravel-dashboard/   # Main application (Laravel 12)
├── docker/              # Docker Compose & service configs
└── README.md
```

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+
- PostgreSQL 15+
- Docker & Docker Compose (for deployment)

### Local Development

```bash
# Install PHP dependencies
cd laravel-dashboard
composer install

# Install front-end dependencies
npm install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Start the dev server
php artisan serve

# In a separate terminal — compile front-end assets
npm run dev
```

### Docker Deployment

```bash
cd docker
cp .env.example .env   # configure your secrets
docker compose up -d
```

## Environment Variables

Key variables to configure in `laravel-dashboard/.env`:

| Variable | Purpose |
|----------|---------|
| `DB_CONNECTION` | Database driver (pgsql) |
| `DB_HOST` / `DB_PORT` | PostgreSQL connection |
| `OPENAI_API_KEY` | OpenAI API access |
| `QDRANT_HOST` | Qdrant vector DB endpoint |
| `N8N_WEBHOOK_URL` | n8n webhook base URL |

## License

This project is proprietary. All rights reserved.
