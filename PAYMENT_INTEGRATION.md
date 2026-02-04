# LSUC Website Payment Integration

This document provides a comprehensive overview of the mobile money payment system integrated into the Lusaka South College website.

## Overview

The payment system integrates with Zambian mobile money providers (MTN Mobile Money, Airtel Money, Zamtel EMoney) to allow students and visitors to make payments directly from the website.

## Components

### Frontend
- **HTML Button**: Added inside the Mobile Money card under #payment-instructions, labeled "Make Payment"
- **JavaScript Handler**: Located at `js/payment-handler.js`
  - Modal interface for collecting payment details
  - Real-time feedback to users
  - AJAX communication with backend

### Backend
- **API Endpoints**:
  - `/api/initiate-payment.php` - Initiates payment requests
  - `/api/payment-callback.php` - Handles payment confirmations
- **Database**: Tracks payment status and records

## Features

1. **User-Friendly Interface**:
   - Clean modal form for entering payment details
   - Real-time status updates
   - Responsive design matching existing website

2. **Payment Flow**:
   - User clicks "Pay or Make Payment" button
   - Modal appears requesting phone number, amount, and optional reference
   - Payment request sent to mobile money provider
   - User receives prompt on their phone
   - System waits for confirmation
   - Success/failure feedback provided

3. **Security Measures**:
   - Server-side validation of all inputs
   - Unique transaction references
   - Secure database storage
   - No sensitive information exposed in frontend

## Installation & Configuration

### Prerequisites
- Apache web server
- PHP 7.0 or higher
- MySQL/MariaDB database
- cURL extension enabled in PHP

### Steps

1. **Database Setup**:
   ```sql
   -- Execute the SQL in api/database-schema.sql to create required tables
   ```

2. **Configuration**:
   - Update database credentials in `config/db_config.php`
   - Adjust database settings constants (DB_HOST, DB_USER, DB_PASS, DB_NAME)

3. **Mobile Money Provider Integration** (Production):
   - Register with your chosen mobile money provider
   - Obtain API credentials
   - Replace the simulated functions in `initiate-payment.php` and `payment-callback.php` with actual API calls
   - Configure webhook/callback URLs with the provider

## File Structure

```
lsuc_website/
├── index.html                      # Main website file with payment button
├── js/
│   ├── payment-handler.js          # Frontend payment handling
│   └── festive-glitter.js          # Existing scripts
├── config/                         # Configuration files
│   └── db_config.php               # Database configuration
├── api/                            # Backend API endpoints
│   ├── initiate-payment.php        # Payment initiation handler
│   ├── payment-callback.php        # Payment confirmation handler
│   ├── database-schema.sql         # Database schema
│   └── README.md                   # API documentation
├── test_payment_system.php         # System test script
└── PAYMENT_INTEGRATION.md          # This documentation
```

## Customization

### Styling
The payment button matches the existing website design. To customize:
- Edit the CSS styles in the button element in `index.html`
- Modify modal styles in the JavaScript file `js/payment-handler.js`

### Validation
Input validation can be adjusted in:
- Frontend: `js/payment-handler.js`
- Backend: `api/initiate-payment.php`

## Troubleshooting

### Common Issues

1. **Payment button not appearing**: Ensure the HTML was properly updated in `index.html`
2. **API calls failing**: Check PHP error logs and database connectivity
3. **Modal not showing**: Verify `js/payment-handler.js` is properly linked in `index.html`

### Testing

For testing purposes:
1. Use test credentials provided by the mobile money provider
2. Check PHP error logs for any server-side issues
3. Use browser developer tools to inspect network requests

## Security Notes

- Never expose API keys or secrets in JavaScript
- All payment processing occurs server-side
- Input sanitization is implemented at multiple levels
- Transaction references prevent duplicate payments

## Production Deployment

Before deploying to production:
1. Obtain proper mobile money API credentials
2. Update all configuration values
3. Test thoroughly in a staging environment
4. Implement proper logging and monitoring
5. Ensure SSL/HTTPS is enabled