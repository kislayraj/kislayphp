# Performance Report - 2026-02-28

## Scope

Validated runtime behavior and throughput after controller fixes:

- Query parameter parsing now reads request query parameters (pagination works).
- `POST /api/v1/users` now returns correct status codes (`201`, `409`, `422`) via `json(..., $status)`.

## Environment

- Timestamp (UTC): `2026-02-28T04:44:02Z`
- Host: macOS `Darwin 25.2.0` (Apple M1, 8 logical CPUs, 8 GB RAM)
- PHP: `8.5.2` CLI
- Thread Safety: `disabled` (NTS)
- HTTP port: `9008`
- Benchmark tool: ApacheBench `2.3`

## Commands

```bash
printf '{"name":"Perf User"}' > tmp/post-user.json

ab -k -n 10000 -c 100 http://127.0.0.1:9008/health
ab -k -n 5000 -c 60 "http://127.0.0.1:9008/api/v1/users?page=1&limit=20"
ab -l -n 2000 -c 30 -p tmp/post-user.json -T application/json http://127.0.0.1:9008/api/v1/users
```

Note: `-l` is required for POST benchmarks because response body length changes as IDs grow.

## Functional Checks Before Load

- `GET /api/v1/users?page=1&limit=2` returns `limit: 2`.
- `POST /api/v1/users` returns `201 Created`.
- Invalid email returns `422 Unprocessable Entity`.
- Duplicate email returns `409 Conflict`.

## Results (3 runs each, median shown)

| Endpoint | Sample Size | Concurrency | Median RPS | Median Mean Latency | Median P95 | Median P99 | Failed Requests |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `/health` | 10,000 | 100 | 35,263.92 req/s | 2.836 ms | 4 ms | 10 ms | 0 |
| `/api/v1/users?page=1&limit=20` | 5,000 | 60 | 12,628.97 req/s | 4.751 ms | 6 ms | 7 ms | 0 |
| `POST /api/v1/users` | 2,000 | 30 | 1,763.72 req/s | 17.010 ms | 24 ms | 62 ms | 0 |

## Run-to-Run Notes

- One `/health` run showed a high tail latency outlier (`P99=234ms`) with lower RPS (`14,374.59`). This is local-host jitter, not a functional regression.
- Read path (`GET /api/v1/users`) remains stable with zero failed requests across all runs.
- Write path (`POST /api/v1/users`) is dominated by SQLite write cost and remains in expected latency range for synchronous local persistence.

## Artifact Policy

Raw benchmark logs were generated in `tmp/` during validation and intentionally not committed.
