<template>
  <div
    v-if="isExpired"
    id="software-lock-overlay"
    class="software-lock-overlay-wrapper"
  >
    <!-- Background Animated Glow Circles -->
    <div class="lock-bg-glow glow-1"></div>
    <div class="lock-bg-glow glow-2"></div>

    <div class="lock-card-container">
      <div class="lock-card text-center shadow-lg position-relative">
        
        <!-- ==============================================
             VIEW 1: SOFTWARE LOCK OVERVIEW
             ============================================== -->
        <div v-if="currentView === 'overview'" class="view-transition">
          <!-- Animated Security Shield & Padlock -->
          <div class="lock-icon-wrapper mb-3">
            <div class="lock-icon-circle">
              <i class="fas fa-lock fs-1 text-white"></i>
            </div>
            <div class="lock-badge-expired">
              <i class="fas fa-exclamation-triangle"></i> EXPIRED
            </div>
          </div>

          <!-- Lock Status Title -->
          <h3 class="fw-bold text-white mb-1 tracking-wide">
            Software Locked
          </h3>
          <h6 class="text-danger-custom fw-semibold mb-3">
            সফটওয়্যার ব্যবহারের মেয়াদ উত্তীর্ণ হয়েছে
          </h6>

          <!-- Organization & Expiry Info Box -->
          <div class="org-info-box p-3 rounded-3 mb-3 text-start">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted-custom small">Organization:</span>
              <span class="fw-bold text-white font-monospace">{{ subscription.organization_name || 'My Organization' }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted-custom small">Expired Date:</span>
              <span class="badge bg-danger fs-6 font-monospace px-2.5 py-1">
                {{ subscription.expired_date || 'Expired' }}
              </span>
            </div>
            <div class="d-flex align-items-center justify-content-between" v-if="subscription.days_overdue > 0">
              <span class="text-muted-custom small">Overdue:</span>
              <span class="text-warning fw-bold font-monospace">
                {{ subscription.days_overdue }} দিন আগে শেষ হয়েছে
              </span>
            </div>
          </div>

          <!-- Explanatory Message -->
          <p class="text-light-desc small mb-3">
            আপনার প্রতিষ্ঠানের সাবস্ক্রিপশনের মেয়াদ শেষ হওয়ায় সফটওয়্যারটির সকল ফিচার সাময়িকভাবে লক করা হয়েছে। অনলাইন পেমেন্টের মাধ্যমে সরাসরি রিনিউ করুন।
          </p>

          <!-- Action Buttons: Pay Now & Logout -->
          <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3 mb-3">
            <button
              type="button"
              id="lock-btn-paynow"
              class="btn btn-paynow px-4 py-2.5 rounded-pill fw-bold shadow d-inline-flex align-items-center justify-content-center gap-2 flex-grow-1"
              @click.stop="currentView = 'payment'"
            >
              <i class="fas fa-credit-card"></i>
              <span>Pay Now / রিনিউ করুন</span>
            </button>

            <button
              type="button"
              id="lock-btn-logout"
              class="btn btn-outline-danger px-4 py-2.5 rounded-pill fw-bold d-inline-flex align-items-center justify-content-center gap-2"
              @click.stop="handleLogout"
            >
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </button>
          </div>

          <!-- Support Contact Details -->
          <div class="support-contact-box p-2.5 rounded-3">
            <div class="fw-semibold text-muted-custom small mb-1.5 d-flex align-items-center justify-content-center gap-2">
              <i class="fas fa-headset text-warning"></i>
              <span>জরুরী সাপোর্ট ও অফলাইন পেমেন্ট:</span>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 small">
              <a
                href="tel:01912252095"
                class="contact-chip"
                id="lock-btn-call"
                @click.stop
              >
                <i class="fas fa-phone-alt text-success me-1"></i>
                <span>01912-252095</span>
              </a>
              <a
                href="https://wa.me/8801912252095"
                target="_blank"
                class="contact-chip"
                id="lock-btn-whatsapp"
                @click.stop
              >
                <i class="fab fa-whatsapp text-success me-1"></i>
                <span>WhatsApp</span>
              </a>
            </div>
          </div>
        </div>


        <!-- ==============================================
             VIEW 2: SUBSCRIPTION PLAN & SSLCOMMERZ CHECKOUT
             ============================================== -->
        <div v-else-if="currentView === 'payment'" class="view-transition text-start">
          <!-- Header with Back Button -->
          <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-secondary border-opacity-25">
            <button
              type="button"
              class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5"
              @click.stop="currentView = 'overview'"
            >
              <i class="fas fa-arrow-left"></i>
              <span>Back</span>
            </button>
            <h6 class="fw-bold text-white mb-0 text-center flex-grow-1">
              Select Subscription Plan
            </h6>
          </div>

          <!-- Month Duration Selection Cards -->
          <div class="plan-selection-grid mb-3">
            <div
              v-for="plan in planOptions"
              :key="plan.months"
              class="plan-card"
              :class="{ 'active': selectedMonths === plan.months }"
              @click.stop="selectedMonths = plan.months"
            >
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="plan-title fw-bold">{{ plan.label }}</span>
                <span v-if="plan.discountBadge" :class="plan.badgeClass" class="badge">
                  {{ plan.discountBadge }}
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="plan-price fw-bold">৳ {{ plan.total.toLocaleString() }}</span>
                <span v-if="plan.discount > 0" class="plan-original-price text-decoration-line-through small text-muted">
                  ৳ {{ plan.original.toLocaleString() }}
                </span>
              </div>
              <small class="plan-subtitle text-muted-custom">{{ plan.subtitle }}</small>
            </div>
          </div>

          <!-- Calculation Breakdown Summary Box -->
          <div class="breakdown-card p-3 rounded-3 mb-3">
            <div class="d-flex justify-content-between mb-1 text-muted-custom small">
              <span>Duration (মেয়াদ):</span>
              <span class="text-white fw-bold">{{ selectedPlan.months }} Month(s)</span>
            </div>
            <div class="d-flex justify-content-between mb-1 text-muted-custom small">
              <span>Regular Fee (মূল ফি):</span>
              <span class="text-white">৳ {{ (selectedPlan.months * baseMonthlyRate).toLocaleString() }}</span>
            </div>
            <div v-if="selectedPlan.discount > 0" class="d-flex justify-content-between mb-1 text-success small">
              <span>Discount (ছাড় {{ selectedPlan.discount }}%):</span>
              <span class="fw-bold">- ৳ {{ discountAmount.toLocaleString() }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 text-muted-custom small">
              <span>New Expiry (নতুন মেয়াদ হবে):</span>
              <span class="text-info fw-bold font-monospace">{{ projectedExpiryDate }}</span>
            </div>
            <div class="d-flex justify-content-between pt-2 border-top border-secondary border-opacity-25 align-items-center">
              <span class="fw-bold text-white fs-6">Total Payable:</span>
              <span class="fw-bold fs-4 text-emerald font-monospace">৳ {{ totalPayable.toLocaleString() }}</span>
            </div>
          </div>

          <!-- SSLCommerz Secure Pay Button -->
          <button
            type="button"
            class="btn btn-sslcommerz w-100 py-3 rounded-3 fw-bold shadow d-flex align-items-center justify-content-center gap-2 mb-2"
            :disabled="isPaying"
            @click.stop="proceedToSSLCommerz"
          >
            <span v-if="isPaying" class="spinner-border spinner-border-sm me-1" role="status"></span>
            <i v-else class="fas fa-shield-alt text-warning fs-5"></i>
            <span class="fs-6">
              {{ isPaying ? 'Connecting to SSLCommerz...' : `Pay ৳ ${totalPayable.toLocaleString()} with SSLCommerz` }}
            </span>
          </button>

          <!-- Accepted Payment Methods Logos Bar -->
          <div class="text-center">
            <small class="text-muted-custom extra-small d-block mb-1">
              <i class="fas fa-lock me-1"></i> Secured 128-bit SSL Gateway • Instant Auto-Renewal
            </small>
            <div class="d-flex align-items-center justify-content-center gap-2 text-muted-custom small opacity-75">
              <span>bKash</span> • <span>Nagad</span> • <span>Rocket</span> • <span>Visa</span> • <span>MasterCard</span> • <span>Nexus</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SoftwareLockModal',
  data() {
    return {
      currentView: 'overview', // 'overview' or 'payment'
      selectedMonths: 12, // Default to 12 months for best value
      isPaying: false,
      observer: null,
      heartbeatTimer: null,
    };
  },
  computed: {
    baseMonthlyRate() {
      if (this.subscription && this.subscription.subscription_fee && Number(this.subscription.subscription_fee) > 0) {
        return Number(this.subscription.subscription_fee);
      }
      return 500;
    },
    planOptions() {
      const base = this.baseMonthlyRate;
      return [
        {
          months: 1,
          label: '1 Month',
          subtitle: 'Monthly subscription',
          original: base * 1,
          total: base * 1,
          discount: 0,
          discountBadge: null,
          badgeClass: '',
        },
        {
          months: 2,
          label: '2 Months',
          subtitle: 'Standard renewal',
          original: base * 2,
          total: base * 2,
          discount: 0,
          discountBadge: null,
          badgeClass: '',
        },
        {
          months: 3,
          label: '3 Months',
          subtitle: 'Quarterly billing',
          original: base * 3,
          total: base * 3,
          discount: 0,
          discountBadge: null,
          badgeClass: '',
        },
        {
          months: 6,
          label: '6 Months',
          subtitle: 'Half-yearly plan',
          original: base * 6,
          total: (base * 6) - ((base * 6) * 0.10),
          discount: 10,
          discountBadge: '10% OFF',
          badgeClass: 'bg-success',
        },
        {
          months: 12,
          label: '12 Months (1 Year)',
          subtitle: 'Annual plan • Best value',
          original: base * 12,
          total: (base * 12) - ((base * 12) * 0.20),
          discount: 20,
          discountBadge: '20% OFF ⭐ BEST VALUE',
          badgeClass: 'bg-warning text-dark fw-bold',
        },
      ];
    },
    selectedPlan() {
      return this.planOptions.find(p => p.months === this.selectedMonths) || this.planOptions[0];
    },
    discountAmount() {
      const raw = this.selectedPlan.months * this.baseMonthlyRate;
      return (raw * this.selectedPlan.discount) / 100;
    },
    totalPayable() {
      return this.selectedPlan.total;
    },
    projectedExpiryDate() {
      const today = new Date();
      let exp = this.subscription && this.subscription.expired_date ? new Date(this.subscription.expired_date) : today;
      if (isNaN(exp.getTime()) || exp < today) {
        exp = new Date();
      }
      exp.setMonth(exp.getMonth() + this.selectedMonths);
      return exp.toISOString().split('T')[0];
    },
  },
  mounted() {
    if (this.isExpired) {
      this.initTamperProtection();
    }
  },
  watch: {
    isExpired(newVal) {
      if (newVal) {
        this.initTamperProtection();
      } else {
        this.teardownProtection();
      }
    },
  },
  beforeUnmount() {
    this.teardownProtection();
  },
  methods: {
    initTamperProtection() {
      document.body.classList.add('software-locked');
      window.__IS_SOFTWARE_EXPIRED__ = true;

      // Continuous DOM Healing (MutationObserver)
      if (window.MutationObserver && !this.observer) {
        this.observer = new MutationObserver((mutations) => {
          if (!this.isExpired) return;

          const overlay = document.getElementById('software-lock-overlay');
          if (!overlay || !document.body.contains(overlay)) {
            document.body.classList.add('software-locked');
            this.$forceUpdate();
          } else {
            if (overlay.style.display === 'none' || overlay.style.visibility === 'hidden' || overlay.style.opacity === '0') {
              overlay.style.removeProperty('display');
              overlay.style.removeProperty('visibility');
              overlay.style.removeProperty('opacity');
            }
          }
        });

        this.observer.observe(document.body, {
          childList: true,
          subtree: true,
          attributes: true,
          attributeFilter: ['style', 'class', 'hidden'],
        });
      }

      // Heartbeat Guard
      if (!this.heartbeatTimer) {
        this.heartbeatTimer = setInterval(() => {
          if (this.isExpired) {
            window.__IS_SOFTWARE_EXPIRED__ = true;
            if (!document.body.classList.contains('software-locked')) {
              document.body.classList.add('software-locked');
            }
          }
        }, 500);
      }
    },
    teardownProtection() {
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }
      if (this.heartbeatTimer) {
        clearInterval(this.heartbeatTimer);
        this.heartbeatTimer = null;
      }
      document.body.classList.remove('software-locked');
      window.__IS_SOFTWARE_EXPIRED__ = false;
    },
    proceedToSSLCommerz() {
      this.isPaying = true;
      axios.post('subscription/initiate-payment', {
        months: this.selectedMonths,
      })
      .then((res) => {
        if (res.data && res.data.gateway_url) {
          window.location.href = res.data.gateway_url;
        } else {
          this.isPaying = false;
          alert('Unable to connect to payment gateway. Please contact support.');
        }
      })
      .catch((err) => {
        this.isPaying = false;
        alert(err.response?.data?.message || 'Payment initiation failed. Please try again.');
      });
    },
    handleLogout() {
      axios.post('logout')
        .then(() => {
          this.$store.dispatch('auth/logout');
          window.location.href = this.$root.baseurl ? (this.$root.baseurl + '/') : '/';
        })
        .catch(() => {
          this.$store.dispatch('auth/logout');
          window.location.href = this.$root.baseurl ? (this.$root.baseurl + '/') : '/';
        });
    },
  },
};
</script>

<style scoped>
/* Fullscreen Unclosable Overlay */
.software-lock-overlay-wrapper {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  z-index: 2147483647 !important;
  background: radial-gradient(circle at 50% 30%, rgba(15, 23, 42, 0.98), rgba(8, 11, 20, 0.99)) !important;
  backdrop-filter: blur(18px) saturate(180%) !important;
  -webkit-backdrop-filter: blur(18px) saturate(180%) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 16px !important;
  overflow-y: auto !important;
  pointer-events: auto !important;
  user-select: none !important;
}

.lock-card-container {
  max-width: 540px;
  width: 100%;
  position: relative;
  z-index: 10;
  margin: auto;
}

.lock-card {
  background: rgba(30, 41, 59, 0.92);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: 20px;
  padding: 28px 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 40px rgba(220, 38, 38, 0.15) !important;
}

/* Security Icon Pulse & Styling */
.lock-icon-wrapper {
  position: relative;
  display: inline-block;
  margin: 0 auto;
}

.lock-icon-circle {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: linear-gradient(135deg, #dc2626, #991b1b);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 25px rgba(220, 38, 38, 0.5);
  position: relative;
  z-index: 2;
  animation: pulseGlow 2s infinite ease-in-out;
}

@keyframes pulseGlow {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
  70% { transform: scale(1.05); box-shadow: 0 0 0 16px rgba(220, 38, 38, 0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}

.lock-badge-expired {
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  background: #ffffff;
  color: #dc2626;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1px;
  padding: 2px 8px;
  border-radius: 12px;
  border: 2px solid #dc2626;
  z-index: 3;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

.text-danger-custom {
  color: #f87171 !important;
}

.text-muted-custom {
  color: #94a3b8 !important;
}

.text-light-desc {
  color: #cbd5e1 !important;
  line-height: 1.5;
}

.text-emerald {
  color: #10b981 !important;
}

.extra-small {
  font-size: 11px;
}

.org-info-box {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.support-contact-box {
  background: rgba(15, 23, 42, 0.6);
  border: 1px dashed rgba(234, 179, 8, 0.4);
}

.contact-chip {
  background: rgba(30, 41, 59, 0.9);
  color: #ffffff !important;
  padding: 5px 12px;
  border-radius: 20px;
  text-decoration: none !important;
  border: 1px solid rgba(255, 255, 255, 0.15);
  font-weight: 600;
  transition: all 0.2s ease;
  pointer-events: auto !important;
}

.contact-chip:hover {
  background: #1e293b;
  border-color: #22c55e;
  transform: translateY(-1px);
}

/* Pay Now Button (Glowing Green / Emerald) */
.btn-paynow {
  background: linear-gradient(135deg, #10b981, #059669);
  color: #ffffff !important;
  border: none;
  font-size: 15px;
  letter-spacing: 0.5px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4) !important;
}

.btn-paynow:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6) !important;
}

/* Plan Selection Grid */
.plan-selection-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 250px;
  overflow-y: auto;
  padding-right: 4px;
}

.plan-card {
  background: rgba(15, 23, 42, 0.7);
  border: 2px solid rgba(148, 163, 184, 0.2);
  border-radius: 12px;
  padding: 10px 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.plan-card:hover {
  border-color: rgba(16, 185, 129, 0.5);
  background: rgba(15, 23, 42, 0.9);
}

.plan-card.active {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.12);
  box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
}

.plan-title {
  color: #ffffff;
  font-size: 14px;
}

.plan-price {
  color: #10b981;
  font-size: 16px;
}

.plan-subtitle {
  font-size: 11px;
}

/* Breakdown Card */
.breakdown-card {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.25);
}

/* SSLCommerz Pay Button */
.btn-sslcommerz {
  background: linear-gradient(135deg, #0f766e, #0d9488);
  color: #ffffff !important;
  border: none;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(13, 148, 136, 0.4);
}

.btn-sslcommerz:hover:not(:disabled) {
  background: linear-gradient(135deg, #0d9488, #14b8a6);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(13, 148, 136, 0.6);
}

/* Smooth Fade/Slide View Transition */
.view-transition {
  animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
