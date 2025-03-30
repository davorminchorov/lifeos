# LifeOS API Design Guidelines

This document outlines the API design principles and standards for the LifeOS application, ensuring consistent, intuitive, and maintainable API endpoints.

## API Architecture

LifeOS follows a RESTful API architecture with the following principles:

1. **Resource-Oriented Design**: APIs are organized around resources
2. **HTTP Method Semantics**: HTTP methods define the operation on resources
3. **JSON Data Format**: All API responses use JSON
4. **Versioning**: APIs are versioned for compatibility
5. **Consistent Error Handling**: Standardized error responses

## URL Structure

### Base URL

All API endpoints are prefixed with:

```
/api/v1/
```

### Resource Naming

- Use plural nouns for resource collections
- Use kebab-case for multi-word resource names
- Nest resources logically

Examples:
- `/api/v1/subscriptions`
- `/api/v1/utility-bills`
- `/api/v1/job-applications`
- `/api/v1/subscriptions/{id}/payments`

## HTTP Methods

| Method | Purpose | Example |
|--------|---------|---------|
| GET | Retrieve resources | GET /api/v1/subscriptions |
| POST | Create a resource | POST /api/v1/subscriptions |
| PUT | Replace a resource | PUT /api/v1/subscriptions/{id} |
| PATCH | Partially update a resource | PATCH /api/v1/subscriptions/{id} |
| DELETE | Delete a resource | DELETE /api/v1/subscriptions/{id} |

## Request & Response Format

### Request Format

For POST, PUT, and PATCH requests, the body should be JSON:

```json
{
  "name": "Netflix",
  "amount": 14.99,
  "billingCycle": "monthly",
  "startDate": "2023-05-15"
}
```

### Response Format

All responses should be JSON with consistent structure:

```json
{
  "data": {
    "id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
    "name": "Netflix",
    "amount": 14.99,
    "billingCycle": "monthly",
    "status": "active",
    "startDate": "2023-05-15",
    "nextBillingDate": "2023-06-15"
  }
}
```

For collections:

```json
{
  "data": [
    {
      "id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
      "name": "Netflix",
      "amount": 14.99,
      // other fields...
    },
    {
      "id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
      "name": "Spotify",
      "amount": 9.99,
      // other fields...
    }
  ],
  "meta": {
    "total": 25,
    "count": 2,
    "perPage": 10,
    "currentPage": 1,
    "totalPages": 3
  },
  "links": {
    "first": "/api/v1/subscriptions?page=1",
    "last": "/api/v1/subscriptions?page=3",
    "prev": null,
    "next": "/api/v1/subscriptions?page=2"
  }
}
```

## Status Codes

| Code | Name | Description |
|------|------|-------------|
| 200 | OK | Request succeeded |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request succeeded but no content to return |
| 400 | Bad Request | Invalid request format or parameters |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

## Error Handling

All error responses follow a consistent format:

```json
{
  "error": {
    "code": "validation_error",
    "message": "The given data was invalid.",
    "details": {
      "name": ["The name field is required."],
      "amount": ["The amount must be a positive number."]
    }
  }
}
```

### Common Error Codes

| Code | Description |
|------|-------------|
| validation_error | Input validation failed |
| not_found | Resource not found |
| unauthorized | Authentication required |
| forbidden | Permission denied |
| internal_error | Server error |
| rate_limited | Too many requests |

## Query Parameters

### Filtering

Use the `filter[field]` parameter format:

```
GET /api/v1/subscriptions?filter[status]=active
```

Multiple filters can be combined:

```
GET /api/v1/subscriptions?filter[status]=active&filter[billingCycle]=monthly
```

### Sorting

Use the `sort` parameter with `-` prefix for descending order:

```
GET /api/v1/subscriptions?sort=name (ascending)
GET /api/v1/subscriptions?sort=-amount (descending)
```

Multiple sort fields can be comma-separated:

```
GET /api/v1/subscriptions?sort=-amount,name
```

### Pagination

Use `page` and `per_page` parameters:

```
GET /api/v1/subscriptions?page=2&per_page=20
```

Default values:
- page: 1
- per_page: 10 (max: 100)

### Field Selection

Use the `fields` parameter to select specific fields:

```
GET /api/v1/subscriptions?fields=id,name,amount
```

### Relationships

Use the `include` parameter to include related resources:

```
GET /api/v1/subscriptions?include=payments,category
```

## Security

### Authentication

LifeOS uses Laravel Sanctum for API authentication:

1. **Token Authentication**:
   - All API requests must include a Bearer token
   - Tokens are obtained via the login endpoint

Example header:
```
Authorization: Bearer {token}
```

2. **CSRF Protection**:
   - Web-based requests require CSRF tokens
   - Include the X-CSRF-TOKEN header for non-GET requests

### Rate Limiting

API rate limiting is implemented to prevent abuse:

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // API routes...
});
```

The default limit is 60 requests per minute.

## Versioning

API versioning is managed via URL prefixing:

```
/api/v1/subscriptions
/api/v2/subscriptions
```

When introducing breaking changes:
1. Create a new API version
2. Maintain backward compatibility for at least one version
3. Document migration paths between versions

## API Documentation

LifeOS API documentation is generated using API Blueprint and includes:

1. Complete endpoint descriptions
2. Request/response examples
3. Authentication requirements
4. Error cases

Documentation is available at:
```
/api/docs
```

## Feature-Specific Endpoints

### Subscriptions API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/subscriptions` | GET | List all subscriptions |
| `/api/v1/subscriptions` | POST | Create a subscription |
| `/api/v1/subscriptions/{id}` | GET | Get a subscription |
| `/api/v1/subscriptions/{id}` | PUT | Update a subscription |
| `/api/v1/subscriptions/{id}` | DELETE | Delete a subscription |
| `/api/v1/subscriptions/{id}/cancel` | POST | Cancel a subscription |
| `/api/v1/subscriptions/{id}/payments` | GET | List subscription payments |

### Utility Bills API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/utility-bills` | GET | List all bills |
| `/api/v1/utility-bills` | POST | Create a bill |
| `/api/v1/utility-bills/{id}` | GET | Get a bill |
| `/api/v1/utility-bills/{id}/pay` | POST | Mark bill as paid |
| `/api/v1/utility-bills/{id}/reminders` | GET | Get bill reminders |

### Investments API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/investments` | GET | List investments |
| `/api/v1/investments` | POST | Create investment |
| `/api/v1/investments/{id}/transactions` | GET | Get investment transactions |
| `/api/v1/investments/{id}/transactions` | POST | Record investment transaction |
| `/api/v1/investments/{id}/performance` | GET | Get investment performance |

### Job Applications API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/job-applications` | GET | List applications |
| `/api/v1/job-applications` | POST | Create application |
| `/api/v1/job-applications/{id}/interviews` | GET | List interviews |
| `/api/v1/job-applications/{id}/interviews` | POST | Schedule interview |
| `/api/v1/job-applications/{id}/status` | PATCH | Update application status |

### Expenses API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/expenses` | GET | List expenses |
| `/api/v1/expenses` | POST | Record expense |
| `/api/v1/expenses/categories` | GET | Get expense categories |
| `/api/v1/expenses/reports/monthly` | GET | Get monthly expense report |
| `/api/v1/expenses/budget` | GET | Get budget status |
| `/api/v1/expenses/budget` | PUT | Update budget | 
