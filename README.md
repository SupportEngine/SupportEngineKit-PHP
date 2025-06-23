# SupportEngine API Documentation

## Authentication
All API requests require an API token in the `Authorization` header:

```
Authorization: Bearer YOUR_API_TOKEN
```

---

## Endpoints

### 1. Create Ticket
- **URL:** `/api/tickets_create.php`
- **Method:** `POST`
- **Parameters:**
  - `subject` (string, required)
  - `message` (string, required)
  - `user_email` (string, required)
  - `name` (string, optional)
  - `company` (string, optional)
  - `priority` (string, optional: `low`, `medium`, `high`)
  - `category_id` (int, optional)
  - `custom_fields` (JSON object, optional)
    - Example: `{ "1": "Value for field 1", "2": "Value for field 2" }`

- **Example Request (cURL):**
```
curl -X POST https://yourdomain.com/api/tickets_create.php \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d "subject=Test Ticket" \
  -d "message=This is a test." \
  -d "user_email=user@example.com" \
  -d "custom_fields={\"1\":\"Value1\"}"
```

- **Success Response:**
```
{
  "success": true,
  "ticket_id": 123
}
```
- **Error Response (missing custom field):**
```
{
  "success": false,
  "error": "Missing required custom fields",
  "custom_field_errors": { "1": "This field is required." }
}
```

---

### 2. List Tickets
- **URL:** `/api/tickets_list.php`
- **Method:** `GET` or `POST`
- **Parameters:**
  - `user_email` (string, required)

- **Example Request (cURL):**
```
curl -X GET "https://yourdomain.com/api/tickets_list.php?user_email=user@example.com" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

- **Success Response:**
```
{
  "success": true,
  "tickets": [
    {
      "id": 123,
      "subject": "Test Ticket",
      "status": "open",
      "priority": "medium",
      "created_at": "2024-06-01 12:00:00",
      "updated_at": "2024-06-01 13:00:00"
    }
  ]
}
```

---

### 3. View Ticket & Replies
- **URL:** `/api/ticket_view.php`
- **Method:** `GET` or `POST`
- **Parameters:**
  - `ticket_id` (int, required)
  - `user_email` (string, required)

- **Example Request (cURL):**
```
curl -X GET "https://yourdomain.com/api/ticket_view.php?ticket_id=123&user_email=user@example.com" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

- **Success Response:**
```
{
  "success": true,
  "ticket": {
    "id": 123,
    "subject": "Test Ticket",
    ...
  },
  "replies": [
    {
      "id": 1,
      "ticket_id": 123,
      "user_id": null,
      "message": "Initial message",
      "attachment": null,
      "created_at": "2024-06-01 12:00:00"
    }
  ],
  "custom_fields": {
    "1": {
      "name": "Order Number",
      "type": "text",
      "required": true,
      "value": "12345"
    }
  }
}
```

---

### 4. Reply to Ticket
- **URL:** `/api/ticket_reply.php`
- **Method:** `POST`
- **Parameters:**
  - `ticket_id` (int, required)
  - `user_email` (string, required)
  - `message` (string, required)

- **Example Request (cURL):**
```
curl -X POST https://yourdomain.com/api/ticket_reply.php \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d "ticket_id=123" \
  -d "user_email=user@example.com" \
  -d "message=This is a reply."
```

- **Success Response:**
```
{
  "success": true,
  "reply_id": 1
}
```

---

## Notes
- All requests must use the correct API token.
- All responses are in JSON.
- Attachments are not supported via the API.
- Custom fields must be passed as a JSON object in the `custom_fields` parameter when creating a ticket.


## Notes
- All API requests require the correct API token.
- All responses are handled asynchronously via completion handlers.
- Custom fields must be passed as a `[String: String]` dictionary.
- Attachments are not supported in the API.

## License
MIT 
