# PayPal Integration Setup Instructions

## 1. PayPal Sandbox Account Setup

1. Go to https://developer.paypal.com/
2. Sign in with your PayPal account
3. Create a new application:
   - Click "Create App"
   - Choose "Default Application" as app name
   - Select "Sandbox" for environment
   - Choose "Merchant" as account type

## 2. Get Your Credentials

After creating the app, you'll get:
- **Client ID**: This is your sandbox client ID
- **Client Secret**: This is your sandbox client secret

## 3. Configure the System

Edit the file: `c:\Osbuzzz\app\page\shop\payment_paypal.php`

Replace these lines:
```php
$paypal_config = [
    'client_id' => 'YOUR_PAYPAL_SANDBOX_CLIENT_ID', // Replace with your sandbox client ID
    'client_secret' => 'YOUR_PAYPAL_SANDBOX_CLIENT_SECRET', // Replace with your sandbox client secret
    'environment' => 'sandbox', // Use 'live' for production
    'sandbox_url' => 'https://api-m.sandbox.paypal.com',
    'live_url' => 'https://api-m.paypal.com'
];
```

With your actual credentials:
```php
$paypal_config = [
    'client_id' => 'AeB7QhPKP8xD...', // Your actual sandbox client ID
    'client_secret' => 'EPL3h9J8F2...',  // Your actual sandbox client secret
    'environment' => 'sandbox',
    'sandbox_url' => 'https://api-m.sandbox.paypal.com',
    'live_url' => 'https://api-m.paypal.com'
];
```

## 4. Test the Integration

1. Use PayPal sandbox test accounts to test payments
2. You can create test buyer and seller accounts in your PayPal developer dashboard
3. Test credit card: 4111 1111 1111 1111 (Visa)
4. Any future expiry date and 3-digit CVV

## 5. Database Setup

Run the SQL file to create order tables:
```sql
-- Run this in your MySQL/phpMyAdmin
SOURCE c:\Osbuzzz\database\orders_tables.sql;
```

Or manually execute the SQL commands from `orders_tables.sql`

## 6. Going Live

When ready for production:
1. Change environment from 'sandbox' to 'live'
2. Replace sandbox credentials with live credentials
3. Test thoroughly with real small amounts first

## Features Included

✅ Checkout form with validation
✅ PayPal payment integration
✅ Cash on Delivery option
✅ Order management system
✅ Order confirmation page
✅ Stock management (reduces inventory on successful order)
✅ Order history tracking
✅ Error handling and validation

## Next Steps

- Add email notifications for order confirmations
- Add order tracking system
- Add admin panel for order management
- Add shipping tracking integration
