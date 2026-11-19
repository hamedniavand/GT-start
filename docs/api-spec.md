# Jobs Lite: API Specification

Your API is checked automatically against this document. Field names, status codes and error codes must match exactly.

## General rules

- Base path: `/api`. All responses are JSON (`Content-Type: application/json`).
- IDs, counts and salaries are JSON **numbers**, not strings.
- Dates use the format `YYYY-MM-DDTHH:MM:SS` with no timezone, e.g. `2026-11-10T00:01:00`.
- Only **active** jobs (`is_active = 1`) are ever returned or can be applied to. An inactive job behaves as if it doesn't exist.
- Every `/api/*` response includes the headers `X-Query-Count` and `X-Response-Time-Ms`. These are already set up in the starter code. Keep them working.
- Errors always use this shape:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Human-readable message",
    "fields": { "email": "Must be a valid email address." }
  }
}
```

`fields` is only present for `VALIDATION_ERROR`. Messages are free text; codes and field keys are checked.

| HTTP | `code` | When |
|---|---|---|
| 400 | `INVALID_JSON` | Request body is not valid JSON |
| 404 | `JOB_NOT_FOUND` | Job doesn't exist or is inactive |
| 409 | `ALREADY_APPLIED` | Same email already applied to this job |
| 422 | `VALIDATION_ERROR` | Invalid query parameter or body field |

---

## 1. `GET /api/jobs`: search jobs (to implement)

### Query parameters

| Param | Type | Default | Rules |
|---|---|---|---|
| `q` | string | — | Optional. Trimmed. Case-insensitive match on **job title or company name** (substring). Empty after trimming = ignored. Max 100 characters. |
| `city_id` | integer | — | Optional. Positive integer. Unknown id = empty results, not an error. |
| `category_id` | integer | — | Optional. Positive integer. Unknown id = empty results, not an error. |
| `sort` | string | `posted_desc` | One of `posted_desc` (newest first), `posted_asc` (oldest first), `salary_desc` (highest `salary_max` first). Ties are broken by `id` descending. |
| `page` | integer | `1` | ≥ 1 |
| `per_page` | integer | `20` | 1 to 50 |

Any parameter that breaks these rules → `422 VALIDATION_ERROR` with the parameter name as the key in `fields`.

### `200` response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Junior UX Designer",
      "company": { "id": 584, "name": "Nova Star Consulting LLC" },
      "city": { "id": 12, "name": "Muscat", "country": "Oman" },
      "category": { "id": 4, "name": "Design" },
      "salary_min": 22000,
      "salary_max": 26000,
      "currency": "AED",
      "employment_type": "full_time",
      "posted_at": "2026-11-10T00:01:00"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 45000,
    "total_pages": 2250
  }
}
```

- `total` = number of matching active jobs. `total_pages` = `ceil(total / per_page)`, or `0` when `total` is `0`.
- A page beyond the last page returns `"data": []` with correct `meta`.

---

## 2. `GET /api/jobs/{id}`: job detail (to implement)

### `200` response

Same fields as one item in the list, plus `description` and `experience_min`:

```json
{
  "data": {
    "id": 1,
    "title": "Junior UX Designer",
    "company": { "id": 584, "name": "Nova Star Consulting LLC", "industry": "Hospitality" },
    "city": { "id": 12, "name": "Muscat", "country": "Oman" },
    "category": { "id": 4, "name": "Design" },
    "salary_min": 22000,
    "salary_max": 26000,
    "currency": "AED",
    "employment_type": "full_time",
    "experience_min": 3,
    "description": "We are looking for a UX Designer to join a growing team. ...",
    "posted_at": "2026-11-10T00:01:00"
  }
}
```

(These are the real values for job `1` in the seed data.)

`404 JOB_NOT_FOUND` if the job doesn't exist or is inactive.

---

## 3. `POST /api/jobs/{id}/apply`: apply to a job (to implement)

### Request body (JSON)

| Field | Required | Rules |
|---|---|---|
| `name` | yes | String, 2 to 100 characters after trimming |
| `email` | yes | Valid email address, max 190 characters. Compared case-insensitively. |
| `cv_url` | yes | Valid `http://` or `https://` URL, max 500 characters |
| `cover_note` | no | String, max 1000 characters |

### Responses

`201 Created`:

```json
{
  "data": {
    "id": 1,
    "job_id": 1,
    "created_at": "2026-11-19T10:15:00"
  }
}
```

- `400 INVALID_JSON`: body isn't valid JSON
- `404 JOB_NOT_FOUND`: job doesn't exist or is inactive (checked before validation)
- `422 VALIDATION_ERROR`: one key in `fields` per invalid field
- `409 ALREADY_APPLIED`: an application with the same email (case-insensitive) already exists for this job

---

## 4. `GET /api/meta/filters`: filter options (already implemented)

Returns all cities and categories for building filter dropdowns.

```json
{
  "data": {
    "cities": [{ "id": 1, "name": "Dubai", "country": "United Arab Emirates" }],
    "categories": [{ "id": 1, "name": "Software Engineering", "slug": "software-engineering" }]
  }
}
```

---

## 5. `GET /api/companies/featured`: featured companies (already implemented, **slow**)

Returns the 50 featured companies, ordered by name, each with its number of active jobs and its 3 most recent active jobs.

```json
{
  "data": [
    {
      "id": 1240,
      "name": "Al Noor Education Holdings",
      "industry": "Retail",
      "open_jobs": 30,
      "latest_jobs": [
        {
          "id": 3916,
          "title": "Junior Math Teacher",
          "posted_at": "2026-11-15T05:16:00",
          "city": { "id": 1, "name": "Dubai", "country": "United Arab Emirates" },
          "category": { "id": 13, "name": "Education" }
        }
      ]
    }
  ]
}
```

(The first item of the real response on the seed data, with only the first of its 3 jobs shown.) `latest_jobs` is ordered by `posted_at` descending, then `id` descending, maximum 3 items.

**Your task:** make this endpoint fast. The response body must stay exactly the same. Target: **5 queries or fewer** (`X-Query-Count`) and **under 300 ms** (`X-Response-Time-Ms`) on the seeded data.

---

## Edge cases

These rules settle cases the sections above don't cover. They are checked like everything else.

- **Order of checks for `POST /api/jobs/{id}/apply`:** `404 JOB_NOT_FOUND` → `400 INVALID_JSON` → `422 VALIDATION_ERROR` → `409 ALREADY_APPLIED`. The first one that applies is returned. So a missing job always gives `404`, even with a broken body, and a duplicate email with other invalid fields gives `422`.
- **`INVALID_JSON`** also covers an empty body and a body that is valid JSON but not an object (for example `[]` or `"hello"`).
- **Wrong types** in the body (for example `"name": 123`) are a `422 VALIDATION_ERROR` for that field.
- **`cover_note`** may be omitted or `null`. If it is present and not `null`, it must be a string of at most 1000 characters.
- **`q`** is trimmed first, then the 100-character limit is checked on the trimmed value.
- **Unknown query parameters** are ignored, not rejected.

---

## Database changes

Any change you make to the database (indexes, constraints, new columns) must be applied **automatically** when the project starts from scratch (`docker compose down -v && docker compose up`). For example, add a file `db/init/03-your-changes.sql` or run Doctrine migrations on backend start-up. Explain your choice in `SUBMISSION.md`.
