<!-- Mobile Money Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">
          <i class="fas fa-mobile-alt me-2"></i>Complete Payment
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="payment-step-1">
          <div class="text-center mb-4">
            <div class="payment-icon mb-3">
              <i class="fas fa-mobile-alt fa-3x text-primary"></i>
            </div>
            <h6 class="mb-2">Mobile Money Payment</h6>
            <p class="text-muted small">You will receive a prompt on your phone to complete the payment</p>
          </div>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Payment Details:</strong><br>
            Amount: <span id="payment-amount"></span><br>
            Method: <span id="payment-method-display"></span><br>
            Phone: <span id="payment-phone-display"></span>
          </div>
          
          <form id="pin-form">
            <input type="hidden" id="payment-order-code" name="order_code">
            <input type="hidden" id="payment-method-value" name="payment_method">
            <input type="hidden" id="payment-phone-value" name="phone_number">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <div class="mb-3">
              <label for="payment-pin" class="form-label">
                <i class="fas fa-lock me-1"></i>Enter your Mobile Money PIN
              </label>
              <input type="password" class="form-control form-control-lg text-center" id="payment-pin" name="pin" 
                     maxlength="4" pattern="[0-9]{4}" placeholder="••••" required
                     style="letter-spacing: 0.5em; font-size: 1.5rem;">
              <div class="form-text">Enter your 4-digit Mobile Money PIN</div>
            </div>
            
            <div class="alert alert-warning">
              <i class="fas fa-shield-alt me-2"></i>
              <small>Your PIN is secure and encrypted. We never store your PIN.</small>
            </div>
          </form>
        </div>
        
        <div id="payment-step-2" style="display: none;">
          <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
              <span class="visually-hidden">Processing...</span>
            </div>
            <h6>Processing Payment...</h6>
            <p class="text-muted">Please wait while we process your payment</p>
            <div class="progress mb-3">
              <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
          </div>
        </div>
        
        <div id="payment-step-3" style="display: none;">
          <div class="text-center">
            <div id="payment-result-icon" class="mb-3">
              <!-- Success or error icon will be inserted here -->
            </div>
            <h6 id="payment-result-title"></h6>
            <p id="payment-result-message" class="text-muted"></p>
            <div id="payment-reference" class="alert alert-success" style="display: none;">
              <strong>Reference:</strong> <span id="reference-number"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div id="payment-buttons-1">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirm-payment-btn">
            <i class="fas fa-credit-card me-2"></i>Confirm Payment
          </button>
        </div>
        <div id="payment-buttons-2" style="display: none;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        <div id="payment-buttons-3" style="display: none;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="continue-btn" style="display: none;">
            <i class="fas fa-check me-2"></i>Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.payment-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: white;
}

#payment-pin {
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  transition: all 0.3s ease;
}

#payment-pin:focus {
  background: white;
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.progress-bar-animated {
  animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
  0% { background-position: 1rem 0; }
  100% { background-position: 0 0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const pinForm = document.getElementById('pin-form');
    const pinInput = document.getElementById('payment-pin');
    const confirmBtn = document.getElementById('confirm-payment-btn');
    const continueBtn = document.getElementById('continue-btn');
    
    // Auto-format PIN input
    pinInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) value = value.slice(0, 4);
        e.target.value = value;
        
        // Enable/disable confirm button
        confirmBtn.disabled = value.length !== 4;
    });
    
    // Handle payment confirmation
    confirmBtn.addEventListener('click', function() {
        const pin = pinInput.value;
        if (pin.length !== 4) {
            alert('Please enter a 4-digit PIN');
            return;
        }
        
        processPayment();
    });
    
    // Continue button handler
    continueBtn.addEventListener('click', function() {
        const redirectUrl = continueBtn.getAttribute('data-redirect');
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    });
    
    function showStep(step) {
        // Hide all steps
        document.getElementById('payment-step-1').style.display = 'none';
        document.getElementById('payment-step-2').style.display = 'none';
        document.getElementById('payment-step-3').style.display = 'none';
        document.getElementById('payment-buttons-1').style.display = 'none';
        document.getElementById('payment-buttons-2').style.display = 'none';
        document.getElementById('payment-buttons-3').style.display = 'none';
        
        // Show current step
        document.getElementById('payment-step-' + step).style.display = 'block';
        document.getElementById('payment-buttons-' + step).style.display = 'block';
    }
    
    function processPayment() {
        showStep(2);
        
        // Animate progress bar
        const progressBar = document.querySelector('.progress-bar');
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);
        
        // Submit payment
        const formData = new FormData(pinForm);
        
        fetch('process_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(interval);
            progressBar.style.width = '100%';
            
            setTimeout(() => {
                showPaymentResult(data);
            }, 500);
        })
        .catch(error => {
            clearInterval(interval);
            console.error('Payment error:', error);
            showPaymentResult({
                success: false,
                message: 'Network error. Please check your connection and try again.'
            });
        });
    }
    
    function showPaymentResult(result) {
        showStep(3);
        
        const iconEl = document.getElementById('payment-result-icon');
        const titleEl = document.getElementById('payment-result-title');
        const messageEl = document.getElementById('payment-result-message');
        const referenceEl = document.getElementById('payment-reference');
        const referenceNumEl = document.getElementById('reference-number');
        
        if (result.success) {
            iconEl.innerHTML = '<i class="fas fa-check-circle fa-4x text-success"></i>';
            titleEl.textContent = 'Payment Successful!';
            messageEl.textContent = result.message || 'Your payment has been processed successfully.';
            
            if (result.reference) {
                referenceNumEl.textContent = result.reference;
                referenceEl.style.display = 'block';
            }
            
            if (result.redirect) {
                continueBtn.style.display = 'inline-block';
                continueBtn.setAttribute('data-redirect', result.redirect);
            }
        } else {
            iconEl.innerHTML = '<i class="fas fa-times-circle fa-4x text-danger"></i>';
            titleEl.textContent = 'Payment Failed';
            messageEl.textContent = result.message || 'Payment could not be processed. Please try again.';
        }
    }
    
    // Reset modal when closed
    document.getElementById('paymentModal').addEventListener('hidden.bs.modal', function() {
        showStep(1);
        pinForm.reset();
        confirmBtn.disabled = true;
        continueBtn.style.display = 'none';
        document.getElementById('payment-reference').style.display = 'none';
    });
    
    // Global function to open payment modal
    window.openPaymentModal = function(orderCode, paymentMethod, phoneNumber, amount) {
        document.getElementById('payment-order-code').value = orderCode;
        document.getElementById('payment-method-value').value = paymentMethod;
        document.getElementById('payment-phone-value').value = phoneNumber;
        document.getElementById('payment-method-display').textContent = paymentMethod.replace('_', ' ');
        document.getElementById('payment-phone-display').textContent = phoneNumber;
        document.getElementById('payment-amount').textContent = 'UGX ' + parseInt(amount).toLocaleString();
        
        paymentModal.show();
        
        // Focus on PIN input after modal is shown
        setTimeout(() => {
            pinInput.focus();
        }, 500);
    };
});
</script>
