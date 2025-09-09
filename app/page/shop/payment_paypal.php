<?php
require '../../base.php';

// Redirect to login if not logged in
auth();

// Check if checkout data exists in session
if (!isset($_SESSION['checkout_data'])) {
    temp('error', 'Checkout session expired. Please try again.');
    redirect('/page/shop/cart.php');
}

$checkout_data = $_SESSION['checkout_data'];

// PayPal Sandbox Configuration
// IMPORTANT: Replace these with your actual PayPal sandbox credentials
$paypal_config = [
    'client_id' => 'AboUGUMG7E9GbWY00cumH919v-hb0Fj3lDvbmI8SQgWtGjxu8Bd6aInv6nEER3YclCkJfFgJmEJxmL8O', // Replace with your sandbox client ID
    'client_secret' => 'EEZS_aeEls2MZVJ86z-KL-7TB6atHdBHjViMPXJDHmPMzX_jTo6JT3wQIfpiTPckkJh7vXtllmUqH6MT', // Replace with your sandbox client secret
    'environment' => 'sandbox', // Use 'live' for production
    'sandbox_url' => 'https://api-m.sandbox.paypal.com',
    'live_url' => 'https://api-m.paypal.com'
];

$_title = 'PayPal Payment';
include '../../head.php';
?>

<main>
    <div class="payment-container">
        <h1>PayPal Payment</h1>
        
        <div class="payment-layout">
            <!-- Payment Summary -->
            <div class="payment-summary">
                <h2>Order Summary</h2>
                <div class="summary-details">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>RM<?= number_format($checkout_data['subtotal'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>RM<?= number_format($checkout_data['shipping_fee'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>RM<?= number_format($checkout_data['tax_amount'], 2) ?></span>
                    </div>
                    <?php if (isset($checkout_data['loyalty_discount']) && $checkout_data['loyalty_discount'] > 0): ?>
                    <div class="summary-row loyalty-discount" style="color: #28a745; background: #d4edda; padding: 8px; border-radius: 4px; margin: 5px 0;">
                        <span>🎉 Loyalty Discount (-<?= number_format($checkout_data['points_used']) ?> points):</span>
                        <span><strong>-RM<?= number_format($checkout_data['loyalty_discount'], 2) ?></strong></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span><strong>Total:</strong></span>
                        <span><strong>RM<?= number_format($checkout_data['grand_total'], 2) ?></strong></span>
                    </div>
                </div>
                
                <div class="shipping-details">
                    <h3>Shipping Address</h3>
                    <p>
                        <?= $checkout_data['first_name'] . ' ' . $checkout_data['last_name'] ?><br>
                        <?= $checkout_data['address_line_1'] ?><br>
                        <?php if ($checkout_data['address_line_2']): ?>
                            <?= $checkout_data['address_line_2'] ?><br>
                        <?php endif; ?>
                        <?= $checkout_data['city'] . ', ' . $checkout_data['state'] . ' ' . $checkout_data['postal_code'] ?><br>
                        Phone: <?= $checkout_data['phone'] ?>
                    </p>
                </div>
            </div>
            
            <!-- PayPal Payment Section -->
            <div class="payment-section">
                <div class="paypal-container">
                    <h2>Complete Your Payment</h2>
                    <p>Click the PayPal button below to complete your payment securely.</p>
                    
                    <!-- PayPal Button Container -->
                    <div id="paypal-button-container"></div>
                    
                    <!-- Alternative payment methods -->
                    <div class="payment-alternatives">
                        <hr>
                        <p>Or choose an alternative payment method:</p>
                        <a href="payment_cod.php" class="btn btn-secondary">Cash on Delivery</a>
                        <a href="checkout.php" class="btn btn-outline">Back to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?= $paypal_config['client_id'] ?>&currency=MYR&intent=capture"></script>

<script>
// PayPal Button Configuration
paypal.Buttons({
    createOrder: function(data, actions) {
        // Build amount breakdown
        var breakdown = {
            item_total: {
                currency_code: 'MYR',
                value: '<?= number_format($checkout_data['subtotal'], 2, '.', '') ?>'
            },
            shipping: {
                currency_code: 'MYR',
                value: '<?= number_format($checkout_data['shipping_fee'], 2, '.', '') ?>'
            },
            tax_total: {
                currency_code: 'MYR',
                value: '<?= number_format($checkout_data['tax_amount'], 2, '.', '') ?>'
            }
        };
        
        <?php if (isset($checkout_data['loyalty_discount']) && $checkout_data['loyalty_discount'] > 0): ?>
        breakdown.discount = {
            currency_code: 'MYR',
            value: '<?= number_format($checkout_data['loyalty_discount'], 2, '.', '') ?>'
        };
        <?php endif; ?>
        
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= number_format($checkout_data['grand_total'], 2, '.', '') ?>',
                    currency_code: 'MYR',
                    breakdown: breakdown
                },
                description: 'Osbuzzz Shoe Order',
                shipping: {
                    name: {
                        full_name: '<?= $checkout_data['first_name'] . ' ' . $checkout_data['last_name'] ?>'
                    },
                    address: {
                        address_line_1: '<?= addslashes($checkout_data['address_line_1']) ?>',
                        address_line_2: '<?= addslashes($checkout_data['address_line_2'] ?? '') ?>',
                        admin_area_2: '<?= addslashes($checkout_data['city']) ?>',
                        admin_area_1: '<?= addslashes($checkout_data['state']) ?>',
                        postal_code: '<?= $checkout_data['postal_code'] ?>',
                        country_code: 'MY'
                    }
                }
            }],
            application_context: {
                shipping_preference: 'SET_PROVIDED_ADDRESS'
            }
        });
    },
    
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            // Payment successful
            console.log('Payment captured:', details);
            
            // Send payment details to your server for order creation
            const formData = new FormData();
            formData.append('action', 'process_payment');
            formData.append('payment_id', details.id);
            formData.append('payer_id', details.payer.payer_id);
            formData.append('payment_status', details.status);
            formData.append('amount', details.purchase_units[0].amount.value);
            formData.append('currency', details.purchase_units[0].amount.currency_code);
            
            fetch('payment_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                
                const parts = text.trim().split(':');
                if (parts[0] === 'SUCCESS' && parts.length >= 2) {
                    // parts[1] should contain the order_id
                    const order_id = parts[1];
                    window.location.href = 'payment_success.php?order_id=' + order_id;
                } else {
                    alert('Error processing order: ' + parts.slice(1).join(':'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while processing your order: ' + error.message);
            });
        });
    },
    
    onError: function(err) {
        console.error('PayPal Error:', err);
        alert('An error occurred with PayPal. Please try again or choose a different payment method.');
    },
    
    onCancel: function(data) {
        console.log('Payment cancelled:', data);
        alert('Payment was cancelled. You can try again when ready.');
    }
}).render('#paypal-button-container');
</script>

<style>
.payment-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.payment-container h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
}

.payment-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 20px;
}

.payment-summary,
.payment-section {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.payment-summary h2,
.payment-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.summary-details {
    margin-bottom: 30px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #2c3e50;
}

.summary-row.total {
    border-top: 2px solid #3498db;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
}

.shipping-details {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
}

.shipping-details h3 {
    margin-bottom: 15px;
    color: #2c3e50;
}

.shipping-details p {
    margin: 0;
    line-height: 1.6;
    color: #666;
}

.paypal-container {
    text-align: center;
}

.paypal-container p {
    margin-bottom: 30px;
    color: #666;
}

#paypal-button-container {
    margin: 30px 0;
    min-height: 100px;
}

.payment-alternatives {
    margin-top: 40px;
}

.payment-alternatives hr {
    margin: 20px 0;
    border: none;
    border-top: 1px solid #ddd;
}

.payment-alternatives p {
    margin-bottom: 20px;
    color: #666;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    margin: 0 10px;
    transition: all 0.3s;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

@media (max-width: 768px) {
    .payment-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .payment-summary,
    .payment-section {
        padding: 20px;
    }
    
    .btn {
        display: block;
        margin: 10px 0;
        text-align: center;
    }
}
</style>

<?php
include '../../foot.php';
?>
