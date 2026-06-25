/**
 * Swish Payment Modal — LSUC Website
 * ============================================================
 * Self-contained payment modal supporting USSD, Push Notification,
 * QR Code, and Bank Card flows via the Netone Swish gateway.
 *
 * Usage:
 *   SwishModal.open({
 *     amount:      500,           // ZMW amount
 *     internalRef: 'LSUC20240101X', // payments.transaction_reference
 *     context:     'Application Fee',
 *     onSuccess:   function(transLinkID) { ... },
 *     onFailure:   function(reason)      { ... },
 *     onClose:     function()            { ... }
 *   });
 */

(function (global) {
    'use strict';

    // ── API base — relative path works on localhost AND production ─
    const API_BASE = './api';

    // ── Polling config (Swish recommends checking every 15 s for 3 min)
    const POLL_INTERVAL_MS = 15000;
    const POLL_MAX_SECONDS = 180;

    let _opts       = {};
    let _pollTimer  = null;
    let _countdown  = 0;
    let _cntTimer   = null;
    let _phone      = '';
    let _transLink  = '';
    let _initialised = false;

    // ── Modal HTML ─────────────────────────────────────────────
    const MODAL_HTML = `
<div id="swishPaymentOverlay" role="dialog" aria-modal="true" aria-labelledby="swishModalTitle">
  <div class="swish-modal">

    <!-- Header -->
    <div class="swish-modal-header">
      <div class="swish-brand">
        <div class="swish-brand-icon"><i class="fas fa-bolt"></i></div>
        <div>
          <h2 id="swishModalTitle">Secure Payment</h2>
          <p>Powered by Swish &mdash; Lusaka South University College</p>
        </div>
      </div>
      <button class="swish-modal-close" id="swishCloseBtn" aria-label="Close payment modal">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Amount banner -->
    <div class="swish-amount-banner">
      <span class="label">Amount Due</span>
      <div>
        <span class="currency">ZMW</span>
        <span class="amount" id="swishAmountDisplay">0.00</span>
      </div>
    </div>

    <!-- Body -->
    <div class="swish-modal-body">

      <!-- Tabs (2×2 grid) -->
      <div class="swish-method-tabs" id="swishMethodTabs">
        <button class="swish-tab-btn active" data-method="ussd">
          <div class="tab-icon"><i class="fas fa-mobile-alt"></i></div>
          <span class="tab-label">USSD Prompt</span>
        </button>
        <button class="swish-tab-btn" data-method="push">
          <div class="tab-icon"><i class="fas fa-bell"></i></div>
          <span class="tab-label">Push Notification</span>
        </button>
        <button class="swish-tab-btn" data-method="qr">
          <div class="tab-icon"><i class="fas fa-qrcode"></i></div>
          <span class="tab-label">QR Code</span>
        </button>
        <button class="swish-tab-btn" data-method="card">
          <div class="tab-icon"><i class="fas fa-credit-card"></i></div>
          <span class="tab-label">Bank Card</span>
        </button>
      </div>

      <!-- USSD Panel -->
      <div class="swish-panel active" id="swish-panel-ussd">
        <div class="swish-panel-desc">
          <i class="fas fa-info-circle info-icon"></i>
          <span>Enter your mobile money number. You will receive a <strong>USSD prompt</strong> on your device. Enter your MPIN to authorise the payment.</span>
        </div>
        <div class="swish-form-group">
          <label for="swish-phone-ussd">Mobile Money Number *</label>
          <input type="tel" id="swish-phone-ussd" placeholder="e.g. 260978905095" maxlength="15">
          <small>Include country code, e.g. 260 for Zambia</small>
        </div>
        <button class="swish-pay-btn" data-action="pay" data-method="ussd">
          <i class="fas fa-paper-plane"></i>&nbsp; Send USSD Prompt
        </button>
      </div>

      <!-- Push Panel -->
      <div class="swish-panel" id="swish-panel-push">
        <div class="swish-panel-desc">
          <i class="fas fa-info-circle info-icon"></i>
          <span>Enter your mobile money number. You will receive a <strong>push notification</strong> on your phone. Tap it to approve the payment.</span>
        </div>
        <div class="swish-form-group">
          <label for="swish-phone-push">Mobile Money Number *</label>
          <input type="tel" id="swish-phone-push" placeholder="e.g. 260978905095" maxlength="15">
          <small>Include country code, e.g. 260 for Zambia</small>
        </div>
        <button class="swish-pay-btn" data-action="pay" data-method="push">
          <i class="fas fa-bell"></i>&nbsp; Send Push Notification
        </button>
      </div>

      <!-- QR Panel -->
      <div class="swish-panel" id="swish-panel-qr">
        <div class="swish-panel-desc">
          <i class="fas fa-info-circle info-icon"></i>
          <span>Enter your mobile number below, then scan the QR code that appears with your <strong>Swish mobile app</strong> to complete payment.</span>
        </div>
        <div class="swish-form-group">
          <label for="swish-phone-qr">Mobile Money Number *</label>
          <input type="tel" id="swish-phone-qr" placeholder="e.g. 260978905095" maxlength="15">
          <small>Include country code, e.g. 260 for Zambia</small>
        </div>
        <div class="swish-qr-box" id="swishQrBox" style="display:none;">
          <img id="swishQrImage" src="" alt="Swish QR Code">
          <p><i class="fas fa-mobile-alt"></i> Open the Swish app and scan this code</p>
        </div>
        <button class="swish-pay-btn" data-action="pay" data-method="qr" id="swishQrBtn">
          <i class="fas fa-qrcode"></i>&nbsp; Generate QR Code
        </button>
      </div>

      <!-- Card Panel -->
      <div class="swish-panel" id="swish-panel-card">
        <div class="swish-panel-desc">
          <i class="fas fa-info-circle info-icon"></i>
          <span>Pay securely by credit or debit card. You will be redirected to a <strong>hosted checkout page</strong> (CyberSource) to enter your card details.</span>
        </div>
        <div class="swish-form-group">
          <label for="swish-phone-card">Confirmation SMS Number *</label>
          <input type="tel" id="swish-phone-card" placeholder="e.g. 260978905095" maxlength="15">
          <small>A confirmation SMS will be sent to this number after payment</small>
        </div>
        <button class="swish-pay-btn" data-action="pay" data-method="card">
          <i class="fas fa-lock"></i>&nbsp; Proceed to Card Checkout
        </button>
      </div>

      <!-- Status / Polling screen -->
      <div class="swish-status-screen" id="swishStatusScreen">
        <div class="swish-spinner"></div>
        <h3 id="swishStatusTitle">Processing Payment&hellip;</h3>
        <p id="swishStatusMsg">Please complete the authorisation on your device. This page will update automatically.</p>
        <div class="swish-countdown">
          <i class="fas fa-clock"></i>
          <span>Checking again in&nbsp;</span>
          <span class="timer-val" id="swishTimerVal">3:00</span>
        </div>
      </div>

      <!-- Success -->
      <div class="swish-result success" id="swishResultSuccess">
        <div class="swish-result-icon"><i class="fas fa-check"></i></div>
        <h3>Payment Successful!</h3>
        <p id="swishSuccessMsg">Your payment has been confirmed. Thank you!</p>
        <button class="swish-result-btn" id="swishSuccessClose">Continue</button>
      </div>

      <!-- Failed -->
      <div class="swish-result failed" id="swishResultFailed">
        <div class="swish-result-icon"><i class="fas fa-times"></i></div>
        <h3>Payment Unsuccessful</h3>
        <p id="swishFailedMsg">The payment could not be processed. Please try again or choose a different method.</p>
        <button class="swish-result-btn" id="swishFailedRetry">Try Again</button>
      </div>

    </div><!-- /.swish-modal-body -->

    <!-- Footer -->
    <div class="swish-modal-footer-note">
      <i class="fas fa-shield-alt"></i>
      <span>Secure, encrypted connection. Your details are protected by Swish &mdash; Netone.</span>
    </div>

  </div><!-- /.swish-modal -->
</div><!-- /#swishPaymentOverlay -->`;

    // ══════════════════════════════════════════════════════════
    const SwishModal = {

        init() {
            if (_initialised) return;
            _initialised = true;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = MODAL_HTML;
            document.body.appendChild(wrapper.firstElementChild);
            _attachEvents();
        },

        open(opts) {
            if (!_initialised) this.init();
            _opts = opts || {};
            _phone = ''; _transLink = '';
            document.getElementById('swishAmountDisplay').textContent =
                parseFloat(_opts.amount || 0).toFixed(2);
            _switchTab('ussd');
            _hideStatusAndResults();
            document.getElementById('swishPaymentOverlay').classList.add('active');
        },

        close() {
            _stopPolling();
            const overlay = document.getElementById('swishPaymentOverlay');
            if (overlay) overlay.classList.remove('active');
            if (typeof _opts.onClose === 'function') _opts.onClose();
        },
    };

    function _attachEvents() {
        const overlay = document.getElementById('swishPaymentOverlay');
        document.getElementById('swishCloseBtn').addEventListener('click', () => SwishModal.close());
        overlay.addEventListener('click', (e) => { if (e.target === overlay) SwishModal.close(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('active')) SwishModal.close();
        });
        document.querySelectorAll('#swishMethodTabs .swish-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => _switchTab(btn.dataset.method));
        });
        document.querySelector('.swish-modal-body').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action="pay"]');
            if (btn) _handlePay(btn.dataset.method);
        });
        document.getElementById('swishSuccessClose').addEventListener('click', () => {
            SwishModal.close();
            if (typeof _opts.onSuccess === 'function') _opts.onSuccess(_transLink);
        });
        document.getElementById('swishFailedRetry').addEventListener('click', () => {
            _hideStatusAndResults(); _switchTab('ussd');
        });
    }

    function _switchTab(method) {
        document.querySelectorAll('#swishMethodTabs .swish-tab-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.method === method);
        });
        document.querySelectorAll('.swish-panel').forEach(p => {
            p.classList.toggle('active', p.id === `swish-panel-${method}`);
        });
        _hideStatusAndResults();
    }

    function _hideStatusAndResults() {
        document.getElementById('swishStatusScreen').classList.remove('active');
        document.getElementById('swishResultSuccess').classList.remove('active');
        document.getElementById('swishResultFailed').classList.remove('active');
        document.getElementById('swishMethodTabs').style.display = '';
        document.querySelectorAll('.swish-panel').forEach(p => {
            if (p.classList.contains('active')) p.style.display = '';
        });
    }

    function _getPhoneForMethod(method) {
        const field = document.getElementById(`swish-phone-${method}`);
        return field ? field.value.trim() : '';
    }

    function _handlePay(method) {
        const phone = _getPhoneForMethod(method);
        if (!phone) { alert('Please enter your mobile number to proceed.'); return; }

        const amount = _opts.amount;
        const internalRef = _opts.internalRef;
        if (!amount || !internalRef) {
            alert('Payment configuration error. Please refresh and try again.');
            return;
        }

        _phone = phone;
        document.querySelectorAll('[data-action="pay"]').forEach(b => b.disabled = true);

        const fd = new FormData();
        fd.append('method',       method);
        fd.append('phone',        phone);
        fd.append('amount',       String(amount));
        fd.append('internal_ref', internalRef);
        fd.append('context',      _opts.context || 'LSUC Payment');

        fetch(`${API_BASE}/swish_initiate.php`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('[data-action="pay"]').forEach(b => b.disabled = false);
            if (!data.success) { alert('Payment initiation failed: ' + (data.error || 'Unknown error')); return; }

            _transLink = data.transLinkID || '';

            if (method === 'card') {
                if (data.paymentUrl) { window.location.href = data.paymentUrl; }
                else { alert('Card payment URL not returned. Please try again.'); }
                return;
            }

            if (method === 'qr' && data.qrImage) {
                document.getElementById('swishQrImage').src = 'data:image/png;base64,' + data.qrImage;
                document.getElementById('swishQrBox').style.display = 'block';
                document.getElementById('swishQrBtn').textContent = 'QR Code Ready — Scan Now';
            }

            _showPollingScreen(method);
            _startPolling(internalRef, phone);
        })
        .catch(err => {
            document.querySelectorAll('[data-action="pay"]').forEach(b => b.disabled = false);
            alert('Network error: ' + err.message);
        });
    }

    function _showPollingScreen(method) {
        document.getElementById('swishMethodTabs').style.display = 'none';
        document.querySelectorAll('.swish-panel').forEach(p => {
            if (method !== 'qr') p.classList.remove('active');
        });
        const titles = { ussd:'Waiting for USSD Authorisation…', push:'Waiting for Push Approval…', qr:'Waiting for QR Code Scan…' };
        const msgs   = {
            ussd: 'A USSD prompt has been sent to your phone. Please enter your MPIN to authorise. This may take up to 3 minutes.',
            push: 'A push notification has been sent to your phone. Tap it to approve the payment.',
            qr:   'Scan the QR code above with your Swish app. Your payment will process automatically.',
        };
        document.getElementById('swishStatusTitle').textContent = titles[method] || 'Processing Payment…';
        document.getElementById('swishStatusMsg').textContent   = msgs[method]   || 'Please complete payment on your device.';
        document.getElementById('swishStatusScreen').classList.add('active');
        _countdown = POLL_MAX_SECONDS;
        _updateCountdown();
    }

    function _updateCountdown() {
        const val = document.getElementById('swishTimerVal');
        if (!val) return;
        const m = Math.floor(_countdown / 60);
        const s = String(_countdown % 60).padStart(2, '0');
        val.textContent = `${m}:${s}`;
    }

    function _startPolling(internalRef, phone) {
        _stopPolling();
        _countdown = POLL_MAX_SECONDS;
        _cntTimer  = setInterval(() => { _countdown = Math.max(0, _countdown - 1); _updateCountdown(); }, 1000);
        _pollTimer = setInterval(() => { _pollStatus(internalRef, phone); }, POLL_INTERVAL_MS);
        setTimeout(() => { _stopPolling(); _showFailed('The payment timed out. Please try again or contact us.'); }, POLL_MAX_SECONDS * 1000 + 2000);
    }

    function _pollStatus(internalRef, phone) {
        fetch(`${API_BASE}/swish_status.php?internal_ref=${encodeURIComponent(internalRef)}&phone=${encodeURIComponent(phone)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            if (data.payment_status === 'success') { _stopPolling(); _showSuccess(); }
            else if (data.payment_status === 'failed') { _stopPolling(); _showFailed('Payment was declined or cancelled. Please try a different method.'); }
        })
        .catch(() => { /* network hiccup — keep polling */ });
    }

    function _stopPolling() {
        if (_pollTimer) { clearInterval(_pollTimer); _pollTimer = null; }
        if (_cntTimer)  { clearInterval(_cntTimer);  _cntTimer  = null; }
    }

    function _showSuccess() {
        document.getElementById('swishStatusScreen').classList.remove('active');
        document.getElementById('swishMethodTabs').style.display = 'none';
        document.querySelectorAll('.swish-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('swishResultSuccess').classList.add('active');
    }

    function _showFailed(msg) {
        document.getElementById('swishStatusScreen').classList.remove('active');
        document.getElementById('swishMethodTabs').style.display = 'none';
        document.querySelectorAll('.swish-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('swishFailedMsg').textContent = msg;
        document.getElementById('swishResultFailed').classList.add('active');
        if (typeof _opts.onFailure === 'function') _opts.onFailure(msg);
    }

    global.SwishModal = SwishModal;

}(window));
