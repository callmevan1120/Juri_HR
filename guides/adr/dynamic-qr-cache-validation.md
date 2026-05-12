# ADR: Dynamic QR Cache Validation

Dynamic QR tokens are short lived and validated with server-side token state.

Consequences:

- Static barcode fallback is risk-scored.
- Token retries and expired tokens should be logged as attendance risk factors.
- Cache outages must fail closed for dynamic tokens.
