# LSUC Payment System API

This directory contains the backend implementation for the mobile money payment system integrated into the Lusaka South College website.

## Files

### `initiate-payment.php`
- Handles payment initiation requests from the frontend
- Validates user input (phone number, amount)
- Creates payment records in the database
- Interfaces with the mobile money provider API (simulated in this implementation)
- Returns transaction details to the frontend

### `payment-callback.php`
- Receives payment confirmation callbacks from the mobile money provider
- Updates payment status in the database
- Provides payment status checking functionality

### `database-schema.sql`
- SQL schema for creating the necessary database tables
- Includes payments table and supporting log/metadata tables

## Configuration

Before deploying this system, you need to update the configuration:

1. Update database credentials in `config/db_config.php`:
   - `DB_HOST`: Your database host (usually 'localhost')
   - `DB_USER`: Your database username
   - `DB_PASS`: Your database password
   - `DB_NAME`: Your database name (suggested: 'lsuc_payment_db')

2. Create the database and tables using the `database-schema.sql` file

## Implementation Notes

This implementation simulates the mobile money API integration since actual API credentials would be required for a live implementation. In a production environment, you would need to:

1. Register with the mobile money provider (MTN Mobile Money, Airtel Money, or Zamtel EMoney)
2. Obtain API credentials
3. Replace the simulated payment functions with actual API calls
4. Configure the callback URLs with the mobile money provider

## Security Considerations

- All sensitive operations are handled server-side
- Input validation is performed on both client and server
- Transaction references are uniquely generated
- Proper error handling prevents information disclosure

## Frontend Integration

The JavaScript file `js/payment-handler.js` handles the frontend interaction:
- Modal display for payment information collection
- AJAX communication with the backend APIs
- Real-time status updates for the user