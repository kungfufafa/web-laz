# Account Deletion Feature Design

**Date:** 2025-02-21
**Status:** Approved
**Author:** Tikom DG1

## Overview

Account deletion mechanism for LAZ-WEB application that allows users to delete their accounts by providing their registered phone number. This feature is required for Google Play Store compliance.

## Requirements

### Functional Requirements
1. Public webpage at `/account-deletion` with form to input phone number
2. API endpoint to validate phone and soft delete user account
3. Client-side validation for Indonesian phone format
4. Rate limiting to prevent abuse
5. User-friendly error messages

### Non-Functional Requirements
1. Soft delete (using existing `softDeletes` in User model)
2. Rate limit: 5 requests per minute
3. Consistent UI with existing pages (privacy-policy, terms-of-service)
4. Responsive design using Tailwind CSS
5. No authentication required (public endpoint)

## Architecture

### Components

#### 1. Frontend (Blade View)
- **Location:** `resources/views/account-deletion.blade.php`
- **Route:** `GET /account-deletion`
- **Features:**
  - Phone input field with validation
  - Confirmation dialog before submission
  - Loading state during API call
  - Success/error message display
  - Consistent layout with other pages

#### 2. Backend (API Endpoint)
- **Location:** `app/Http/Controllers/Api/AuthController.php`
- **Method:** `deleteAccount(Request $request)`
- **Route:** `POST /api/account-deletion`
- **Features:**
  - Phone format validation (Indonesian format)
  - User lookup by phone number
  - Soft delete execution
  - Rate limiting (5 req/min)

## API Specification

### POST /api/account-deletion

**Request Body:**
```json
{
  "phone": "08123456789"
}
```

**Validation Rules:**
- `phone`: required, string, regex: `^(?:\+62|62|0)8[1-9][0-9]{6,9}$`

**Success Response (200):**
```json
{
  "message": "Akun berhasil dihapus"
}
```

**Error Responses:**

*Validation Error (422):*
```json
{
  "message": "Format nomor telepon tidak valid",
  "errors": {
    "phone": ["Format nomor telepon harus dimulai dengan 08, 628, atau +628"]
  }
}
```

*User Not Found (404):*
```json
{
  "message": "Akun dengan nomor telepon tersebut tidak ditemukan"
}
```

*Rate Limit Exceeded (429):*
```json
{
  "message": "Terlalu banyak permintaan. Silakan coba lagi dalam beberapa saat."
}
```

## User Flow

```
1. User visits /account-deletion
   ↓
2. Page displays form with phone input
   ↓
3. User enters phone number
   ↓
4. User clicks "Hapus Akun" button
   ↓
5. Confirmation dialog appears
   ↓
6a. User confirms → API call → Show success message
6b. User cancels → Return to form
```

## Technical Implementation

### Phone Validation Regex

```php
^(?:\+62|62|0)8[1-9][0-9]{6,9}$
```

Accepts:
- `08123456789` (10-13 digits)
- `628123456789` (11-14 digits)
- `+628123456789` (12-15 digits)

### Rate Limiting

Using Laravel throttle middleware:
```php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/api/account-deletion', [AuthController::class, 'deleteAccount']);
});
```

### Soft Delete

Utilize existing `softDeletes` in User model:
```php
$user->delete(); // Sets deleted_at timestamp
```

## Security Considerations

1. **Rate Limiting**: Prevents brute force and abuse
2. **Consistent Error Messages**: Prevents user enumeration
3. **Soft Delete**: Data recovery possible if needed
4. **No Authentication**: Public access as required
5. **Client-Side Validation**: Reduces unnecessary API calls

## Testing Strategy

### Unit Tests
- Phone validation regex
- User lookup by phone
- Soft delete execution

### Feature Tests
- Successful account deletion
- Validation errors (invalid format)
- User not found scenario
- Rate limiting
- Various phone formats
- Frontend form submission

## UI/UX Considerations

### Visual Design
- Centered card layout
- Warning color for destructive action (red)
- Clear typography and spacing
- Loading indicator during submission
- Accessible form labels

### User Feedback
- Success message: "Akun berhasil dihapus"
- Error message: "Format nomor telepon tidak valid"
- Not found message: "Akun dengan nomor telepon tersebut tidak ditemukan"
- Rate limit message: "Terlalu banyak permintaan. Silakan coba lagi dalam beberapa saat."

### Confirmation Dialog
```
Anda yakin ingin menghapus akun?
Tindakan ini tidak dapat dibatalkan.

[Cancel]  [Hapus Akun]
```

## Implementation Checklist

- [ ] Create web route for `/account-deletion`
- [ ] Create Blade view with form
- [ ] Add client-side validation
- [ ] Implement API endpoint in AuthController
- [ ] Add API route with rate limiting
- [ ] Write unit tests
- [ ] Write feature tests
- [ ] Test with various phone formats
- [ ] Verify rate limiting works
- [ ] Test frontend functionality
- [ ] Run Pint for code formatting
- [ ] Update documentation

## Notes

- This feature is mandatory for Google Play Store compliance
- Soft delete allows data recovery if needed
- Phone format follows Indonesian mobile number standards
- Rate limit matches existing auth endpoints (login/register)
