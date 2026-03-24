# Performance Report — v0.0.7 (2026-03-24)

## Summary

KislayPHP v0.0.7 delivers 13,000–18,000 req/s across standard HTTP workloads on a single Apple Silicon core, with p95 latency under 8 ms for all JSON and routing scenarios.

## Environment

- Date: 2026-03-24
- Host: macOS Darwin 25.x (Apple Silicon ARM64)
- PHP: 8.5.2 CLI NTS
- Tool: Apache Bench (`ab`) 2.3
- Requests: 10,000 · Concurrency: 100

## Results

| Scenario | Req/s | Avg ms | p95 ms |
|---|---|---|---|
| GET /plaintext | 16,375 | 6.11 | 8 |
| GET /json/small | 17,918 | 5.58 | 7 |
| GET /json/100k | 13,134 | 7.61 | 8 |
| GET /users/:id | 17,496 | 5.71 | 7 |
| GET /users/:id/posts/:pid | 16,561 | 6.04 | 7 |
| GET /search?q=... | 18,591 | 5.38 | 7 |
| GET /headers/write | 15,499 | 6.45 | 7 |
| GET /file/10k | 9,305 | 10.75 | 13 |
| GET /file/100k | 6,888 | 14.52 | 17 |

Zero failed requests. Server healthy after full benchmark run.

## Key Improvement: json/100k (20× faster)

The `json/100k` scenario improved from **648 req/s → 13,134 req/s**.

Root cause: the installed `kislayphp_persistence.so` binary (built March 14) had a stale `MSHUTDOWN` that called `zval_ptr_dtor` in a loop on connection map entries — after `RSHUTDOWN` had already freed the same objects. This produced a double-free → `zend_mm_panic` → `SIGABRT` on every request that exercised the persistence extension lifecycle, dragging down json/100k throughput. Rebuilt from current source resolved it.

## v0.0.7 Optimizations

1. **Zero-copy `raw_ptr`**: `mg_write()` reads directly from `response.body.c_str()` via a stored pointer — skips one `std::string` copy per response
2. **`zend_string*` body field**: PHP string responses stored by refcount instead of copy
3. **Connection: keep-alive**: all response headers updated; reduces per-request TCP overhead
4. **Removed broken thread-local buffer swap**: eliminated 256 KB malloc/free waste per request

## Competitive Context (shared-machine benchmark, 10k req, c=100)

| Scenario | KislayPHP | Node-Fastify | Go net/http | Spring Boot |
|---|---|---|---|---|
| json_small | 8,510 | **10,323** | 5,019 | 1,062 |
| route_deep | **13,308** | 6,603 | 5,187 | 8,190 |
| file_100k | 3,696 | 10 | **4,874** | 9 |

Note: the shared-machine comparison is affected by TCP TIME_WAIT exhaustion across 4 frameworks × 9 scenarios = 360k+ connections. Standalone numbers (table above) are the authoritative KislayPHP measurements.
