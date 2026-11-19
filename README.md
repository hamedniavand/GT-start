# Jobs Lite: Take-Home Test

**Role:** Senior Full-Stack Product Engineer at Jobsite

Build a small slice of a job portal: a job search API, a performance fix, and a React frontend.

- **Expected effort:** about 3 hours
- **Time to submit:** 4 days, including a weekend
- **AI tools:** allowed. You'll walk through and change your code live in a follow-up session, so make sure you understand everything you submit.

---

## Getting started

Requirements: Docker with Docker Compose. Nothing else needs to be installed locally.

```bash
docker compose up --build
```

The first start takes a few minutes (images, Composer and npm install, database seed).

| Service | URL |
|---|---|
| Backend API (Symfony) | http://localhost:8000/api/health |
| Frontend (React + Vite) | http://localhost:5173 |
| MySQL | `localhost:3307`, database `jobs`, user `app`, password `app` |

Useful commands:

```bash
docker compose exec backend php bin/phpunit              # backend tests
docker compose exec backend composer require <package>   # add a PHP package
docker compose exec db mysql -uapp -papp jobs            # MySQL shell
docker compose down -v && docker compose up --build      # reset everything, including the database
```

---

## What's in the starter

| Path | What it is |
|---|---|
| `docs/api-spec.md` | **The API contract. Follow it exactly.** Your API is checked automatically against it. |
| `db/init/` | Schema and seed: 15 cities, 20 categories, 2,000 companies, 50,000 jobs |
| `backend/` | Symfony 7 app. Working: `/api/health`, `/api/meta/filters`, `/api/companies/featured` (slow). Stubs returning `501`: the three job endpoints |
| `frontend/` | Empty React + TypeScript app (Vite), with `/api` proxied to the backend |
| `SUBMISSION.md` | Fill this in before you submit |

Every `/api` response includes `X-Query-Count` and `X-Response-Time-Ms` headers. Use them to measure your performance fix.

**Symfony is preferred.** Laravel is accepted: you may replace `backend/` with a Laravel app, as long as it uses the same database, follows `docs/api-spec.md`, keeps the two headers above, and still starts with `docker compose up`.

---

## What to build

### 1. Job search API
Implement the stubs in `backend/src/Controller/JobController.php` according to `docs/api-spec.md`:
- `GET /api/jobs`: keyword search, filter by city and category, sorting, pagination
- `GET /api/jobs/{id}`: job detail
- `POST /api/jobs/{id}/apply`: validate the input and block duplicate applications

### 2. Performance fix
`GET /api/companies/featured` is too slow. Find out why and fix it without changing the response body. Target: 5 queries or fewer and under 300 ms. Record the response time and number of queries before and after.

### 3. React frontend
- A job search page with filters, results and pagination
- A job detail page with an apply form
- Loading, empty and error states

### 4. Tests
- At least 3 backend tests
- 1 end-to-end test (Cypress, Playwright or similar): search → open a job → apply. Explain in `SUBMISSION.md` how to run it.

### 5. SUBMISSION.md
Keep it short. Answer the 4 questions in the file.

**Database changes** (indexes, constraints) must apply automatically on a fresh start. See the last section of `docs/api-spec.md`.

---

## How to submit

1. On this repository's page, click **"Use this template"** → **"Create a new repository"**.
2. Create it as a **private** repository under your own account. Please don't fork it.
3. Commit as you go. We look at the commit history.
4. Add the reviewer GitHub account you were given as a collaborator (Settings → Collaborators).
5. Send us the repository link before the deadline.

---

## What happens next

After you submit, we'll schedule a 45-minute session where you:
- Walk us through your code and decisions
- Explain your performance fix
- Make one small change to your project live

Good luck!
