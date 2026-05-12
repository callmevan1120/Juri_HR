# ADR: Enterprise License Offline

Enterprise license validation must work without continuous internet access.

Consequences:

- License payloads need local signature verification.
- Expiry and grace policy must be testable without network calls.
- License secrets and obfuscator keys must not be committed.
