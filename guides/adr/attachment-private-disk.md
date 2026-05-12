# ADR: Attachment Private Disk

Attachments are private business records and must not be served directly from public storage.

Consequences:

- Downloads go through routes, policies, and path validation.
- Uploads use allowlists and random storage paths.
- Public disk fallback is legacy only and should be disabled in hardened deployments.
