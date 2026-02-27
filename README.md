# Kislay Ecosystem Examples

This repository contains integration examples for the Kislay PHP extension ecosystem.

## Examples

### 1. Ecosystem Full Flash ([ecosystem_example.php](./ecosystem_example.php))
Demonstrates a complete microservice communication loop:
- **Registry**: Tracks service health with active pings.
- **Gateway**: Handles routing and automatic header injection (X-Forwarded-*).
- **Core App**: Non-blocking HTTP server implementation.
- **Async Client**: HTTP client with automatic **Retries** and **Correlation-ID** propagation.

## Prerequisites

Ensure the following extensions are built and enabled:
1. kislayphp_extension (Core)
2. kislayphp_gateway (Gateway)
3. kislayphp_discovery (Registry)

## Running the example

```bash
# Run with extensions loaded
php -d extension=kislayphp_extension.so \n    -d extension=kislayphp_gateway.so \n    -d extension=kislayphp_discovery.so \n    ecosystem_example.php
```