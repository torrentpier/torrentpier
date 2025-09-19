# Security Policy

## Supported Versions

We release security updates for the following versions:

| Version | Supported            |
|---------|----------------------|
| 3.x     | ✅ Active development |
| 2.8.x   | ✅ Supported          |
| <2.8    | ❌ Not supported      |

## Reporting a Vulnerability

Please **do not file public issues or discussions** if you’ve discovered a potential security vulnerability.

Instead, use GitHub's private security advisory form:

👉 **[New Security Advisory](https://github.com/torrentpier/torrentpier/security/advisories/new)**

Include the following details (if possible):

- A clear description of the vulnerability
- Steps to reproduce the issue
- Affected versions or commit hashes
- The potential impact (e.g. RCE, XSS, privilege escalation)
- Any suggested remediation (optional)

We typically respond within **72 hours**, and address validated issues within **30 days**.

## Alternative Reporting (via Email)

You may also contact us privately via email at:

📧 **admin@torrentpier.com**

## Disclosure Policy

After verification and fix:

1. A patch will be released.
2. A GitHub Security Advisory will be published.
3. A CVE will be requested if the severity justifies it.
4. Credit will be given to the reporter (unless anonymity is requested).

We follow [responsible disclosure principles](https://securitytxt.org/) and the [OpenSSF guidelines](https://openssf.org/).

## User Security Best Practices

If you self-host this project:

- Always keep up with releases
- Use automated scanners like `npm audit`, `trivy`, or `snyk`
- Do not expose admin tools to public networks
- Use HTTPS and strong authentication
- Monitor logs and set up alerts for anomalies
