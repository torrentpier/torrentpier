# Security Policy

## Closure Notice

**TorrentPier 3.0 (Ox) is the final release of this codebase.** The project
closed in May 2026. No further patches, security fixes, or feature work are
planned.

Read the closure announcement at
[https://sunset.torrentpier.com/](https://sunset.torrentpier.com/). The
community forum is preserved read-only at
[https://ox.torrentpier.com/](https://ox.torrentpier.com/). A new generation
of the engine — codename **Dexter** — is being written from scratch,
expected in 2027.

If you self-host this release, **you are responsible for your own security
maintenance**.

## Reporting a Vulnerability

Reports are accepted as a courtesy but will not be patched. You may still
file a private security advisory via GitHub:

[New Security Advisory](https://github.com/torrentpier/torrentpier/security/advisories/new)

Or contact us by email at **admin@torrentpier.com**.

Acknowledgement is the most that can be promised; downstream operators are
expected to apply their own mitigations.

## User Security Best Practices

If you self-host this project:

- Apply your own patches as new CVEs surface in upstream dependencies
- Use automated scanners like `npm audit`, `trivy`, or `snyk`
- Do not expose admin tools to public networks
- Use HTTPS and strong authentication
- Monitor logs and set up alerts for anomalies
