# Junior Developer Test Project

## Plugin

- Path: wp-content/plugins/test-resources-plugin
- REST endpoint: GET /wp-json/test/v1/resources
- Query param: min_level one of [beginner, intermediate, advanced] (defaults to beginner)
- Auth: plugin accepts `Authorization: Bearer dev-secret-token-CHANGE-THIS` or header `X-Auth-Token`. This is a simulation for the test; change token in `test-resources-plugin.php` constant if you want.

## Reading estimate formula

reading_estimate (minutes) = ceil(word_count(summary) / 200)

## To test

1. Install plugin, activate.
2. Sample data created on activation: "Sample Resource".
3. Use curl:
   - Unauthenticated: `curl "http://localhost:8000/wp-json/test/v1/resources"`
   - Authenticated: `curl -H "Authorization: Bearer dev-secret-token-CHANGE-THIS" "http://localhost:8000/wp-json/test/v1/resources"`

## Frontend

- Folder: frontend
- Start: `cd frontend && npm install && npm run dev`
- Change `VITE_API_BASE` in frontend/.env` to point to your WordPress base URL.
- Toggle "Authenticated" checkbox in UI to have the frontend include the simulated auth header.
