/**
 * Payment Handler for LSUC Mobile Money Integration
 * Handles the frontend interaction for Swish/Zambia Mobile Money payments
 */

class PaymentHandler {
    constructor() {
        this.apiBaseUrl = './api';
        this.paymentModal = null;
        this.init();
    }

    init() {
        // Bind event to the payment button when it's available
        document.addEventListener('DOMContentLoaded', () => {
            const payButtons = document.querySelectorAll('.pay-or-make-payment-btn');
            payButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showPaymentForm();
                });
            });
        });
    }

    /**
     * Show the payment form modal
     */
    showPaymentForm() {
        // Remove existing modal if present
        const existingModal = document.querySelector('#payment-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal HTML
        const modalHtml = `
            <div id="payment-modal" class="payment-modal-overlay">
                <div class="payment-modal">
                    <div class="payment-modal-header">
                        <h3>Make Payment</h3>
                        <button class="close-modal" onclick="paymentHandler.closeModal()">&times;</button>
                    </div>
                    <div class="payment-modal-body">
                        <form id="payment-form">
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number (e.g. 0971234567)" required>
                                <small class="help-text">Enter your mobile money registered phone number</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="amount">Amount (ZMW) *</label>
                                <input type="number" id="amount" name="amount" placeholder="Enter amount" min="1" step="0.01" required>
                                <small class="help-text">Enter the amount you wish to pay</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="reference">Reference (Optional)</label>
                                <input type="text" id="reference" name="reference" placeholder="Student ID, Full Name, or Reference">
                                <small class="help-text">Additional reference for your payment</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="paymentHandler.closeModal()">Cancel</button>
                                <button type="submit" class="btn-primary" id="submit-payment-btn">
                                    <span class="btn-text">Initiate Payment</span>
                                    <span class="loading-spinner" style="display: none;">⏳</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Add modal styles if not already present
        this.addModalStyles();

        // Bind form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.processPayment();
        });

        // Show the modal
        this.paymentModal = document.getElementById('payment-modal');
        this.paymentModal.style.display = 'flex';
    }

    /**
     * Process the payment
     */
    async processPayment() {
        const phone = document.getElementById('phone').value.trim();
        const amount = document.getElementById('amount').value.trim();
        const reference = document.getElementById('reference').value.trim();

        // Validate inputs
        if (!phone) {
            this.showMessage('Please enter your phone number', 'error');
            return;
        }

        if (!amount || parseFloat(amount) <= 0) {
            this.showMessage('Please enter a valid amount', 'error');
            return;
        }

        // Update button to show loading state
        const submitBtn = document.getElementById('submit-payment-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');
        
        btnText.textContent = 'Processing...';
        spinner.style.display = 'inline';
        submitBtn.disabled = true;

        try {
            // Show processing message
            this.showMessage('Initiating payment request...', 'info');

            // Send payment request to backend
            const response = await fetch(`${this.apiBaseUrl}/initiate-payment.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    phone: phone,
                    amount: amount,
                    reference: reference
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success message and wait for payment
                this.showMessage(`Payment request sent successfully. Transaction ID: ${result.transaction_reference}`, 'success');
                
                // Show waiting message
                this.showMessage('Waiting for payment confirmation...', 'info');
                
                // Poll for payment status or wait for callback
                await this.waitForPaymentConfirmation(result.transaction_reference);
            } else {
                this.showMessage(result.message || 'Payment initiation failed', 'error');
            }
        } catch (error) {
            console.error('Payment error:', error);
            this.showMessage('An error occurred while processing your payment. Please try again.', 'error');
        } finally {
            // Reset button state
            btnText.textContent = 'Initiate Payment';
            spinner.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    /**
     * Wait for payment confirmation
     */
    async waitForPaymentConfirmation(transactionReference) {
        // In a real implementation, you'd listen for a webhook/callback from the payment provider
        // For now, we'll simulate the process with a timeout and status checking
        
        this.showMessage('Please confirm the payment on your phone...', 'info');
        
        // Simulate waiting for payment confirmation
        // In a real implementation, you would listen for a server callback or poll for status
        let attempts = 0;
        const maxAttempts = 30; // 30 attempts * 2 seconds = 60 seconds max wait time
        
        const checkStatus = async () => {
            if (attempts >= maxAttempts) {
                this.showMessage('Payment confirmation timed out. Please check your transaction status.', 'warning');
                return;
            }
            
            attempts++;
            
            try {
                // In a real implementation, you would check the payment status from your backend
                // which would have received the callback from the payment provider
                const response = await fetch(`${this.apiBaseUrl}/payment-callback.php?ref=${encodeURIComponent(transactionReference)}`);
                const result = await response.json();
                
                if (result.success && result.payment && result.payment.status === 'successful') {
                    this.showMessage('Payment completed successfully!', 'success');
                    setTimeout(() => {
                        this.closeModal();
                        this.showMessage('Thank you for your payment!', 'success');
                    }, 2000);
                } else if (result.success && result.payment && result.payment.status === 'failed') {
                    this.showMessage('Payment failed. Please try again.', 'error');
                } else {
                    // Continue waiting
                    setTimeout(checkStatus, 2000);
                }
            } catch (error) {
                console.error('Error checking payment status:', error);
                setTimeout(checkStatus, 2000);
            }
        };
        
        setTimeout(checkStatus, 2000);
    }

    /**
     * Close the payment modal
     */
    closeModal() {
        if (this.paymentModal) {
            this.paymentModal.remove();
            this.paymentModal = null;
        }
    }

    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.payment-message');
        existingMessages.forEach(msg => msg.remove());

        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `payment-message payment-message-${type}`;
        messageEl.textContent = message;
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            word-wrap: break-word;
            animation: slideInRight 0.3s ease-out;
        `;

        // Style based on type
        switch(type) {
            case 'success':
                messageEl.style.backgroundColor = '#28a745';
                break;
            case 'error':
                messageEl.style.backgroundColor = '#dc3545';
                break;
            case 'warning':
                messageEl.style.backgroundColor = '#ffc107';
                messageEl.style.color = '#212529';
                break;
            default:
                messageEl.style.backgroundColor = '#007bff';
        }

        // Add to document
        document.body.appendChild(messageEl);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageEl.parentNode) {
                messageEl.remove();
            }
        }, 5000);
    }

    /**
     * Add necessary CSS styles for the modal
     */
    addModalStyles() {
        // Check if styles are already added
        if (document.querySelector('#payment-modal-styles')) {
            return;
        }

        const styleElement = document.createElement('style');
        styleElement.id = 'payment-modal-styles';
        styleElement.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .payment-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .payment-modal {
                background: white;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                position: relative;
            }

            .payment-modal-header {
                padding: 20px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .payment-modal-header h3 {
                margin: 0;
                font-size: 1.2em;
                color: #333;
            }

            .close-modal {
                background: none;
                border: none;
                font-size: 1.5em;
                cursor: pointer;
                color: #999;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .close-modal:hover {
                color: #333;
            }

            .payment-modal-body {
                padding: 20px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #333;
            }

            .form-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }

            .form-group input:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            }

            .help-text {
                display: block;
                margin-top: 5px;
                color: #666;
                font-size: 0.9em;
            }

            .form-actions {
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                margin-top: 30px;
            }

            .btn-primary, .btn-secondary {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .btn-primary {
                background-color: #007bff;
                color: white;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            }

            .btn-primary:disabled {
                background-color: #6c757d;
                cursor: not-allowed;
            }

            .btn-secondary {
                background-color: #6c757d;
                color: white;
            }

            .btn-secondary:hover {
                background-color: #545b62;
            }

            .payment-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                max-width: 400px;
                word-wrap: break-word;
                animation: slideInRight 0.3s ease-out;
            }

            .payment-message-success { background-color: #28a745; }
            .payment-message-error { background-color: #dc3545; }
            .payment-message-warning { background-color: #ffc107; color: #212529; }
            .payment-message-info { background-color: #007bff; }
        `;

        document.head.appendChild(styleElement);
    }
}

// Initialize payment handler when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.paymentHandler = new PaymentHandler();
});