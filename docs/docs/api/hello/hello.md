---
sidebar_position: 1
title: Hello
---

# Hello

Test endpoint to verify API availability and get basic system information.

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/hello` | Get API status and version info |

---

## Get API status

Returns basic information about the API.

### Request

```
GET /api/hello
```

### Example request

```bash
curl -X GET https://your-domain.com/api/hello \
  -H "Accept: application/json"
```

### Response

```json
{
  "success": true,
  "data": {
    "message": "Hello from TorrentPier API",
    "version": "3.0.0",
    "php_version": "8.4.0",
    "timestamp": 1701432000
  }
}
```

### Response fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Request status |
| `data.message` | string | Welcome message |
| `data.version` | string | TorrentPier version |
| `data.php_version` | string | PHP version |
| `data.timestamp` | integer | Current Unix timestamp |

### Status codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 500 | Server error |

### Notes

- This endpoint does not require authentication
- Useful for health checks and monitoring
- Response time can be used as a basic latency test
