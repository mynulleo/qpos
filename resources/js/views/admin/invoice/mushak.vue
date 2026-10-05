<template>
  <div
    class="mushak-page bg-light min-vh-100 py-3 font-bangla"
    :style="{ '--paper-bg': paperColor }"
  >
    <!-- Top Action & Config Bar (d-print-none) -->
    <div class="container mb-3 d-print-none" style="max-width: 1060px;">
      <div class="bg-white p-3 rounded shadow-sm border">
        
        <!-- Row 1: Navigation & Actions -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-2 border-bottom mb-2">
          <div class="d-flex align-items-center gap-2">
            <router-link :to="{ name: 'invoice.show', params: { id: $route.params.id } }" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i> Back to Invoice
            </router-link>
            <router-link to="/invoice" class="btn btn-sm btn-outline-dark">
              <i class="fas fa-list me-1"></i> All Invoices
            </router-link>
            <span class="badge bg-success font-monospace px-2 py-1 fs-6">মূসক-৬.৩</span>
            <span class="badge bg-secondary font-monospace px-2 py-1"><i class="fas fa-arrows-alt-h me-1"></i> Landscape (A4)</span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-dark btn-sm d-flex align-items-center gap-1 font-monospace fw-bold px-3 shadow-sm" @click="printMushak">
              <i class="fas fa-print"></i>{{ $t('Print Mushak 6.3') }}</button>
          </div>
        </div>

        <!-- Row 2: Print Settings Controls (Copy Type & Paper Color) -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-1">
          
          <!-- 1. Copy Type Radio Selector -->
          <div class="d-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded border">
            <span class="small fw-bold text-dark text-nowrap"><i class="fas fa-copy text-primary me-1"></i> কপির ধরন (Copy Type):</span>
            <div class="d-flex align-items-center gap-3">
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0">
                <input type="radio" class="form-check-input mt-0" name="copyType" value="মূল কপি" v-model="copyType">
                <span :class="{ 'fw-bold text-primary': copyType === 'মূল কপি' }">মূল কপি</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0">
                <input type="radio" class="form-check-input mt-0" name="copyType" value="১ম কপি" v-model="copyType">
                <span :class="{ 'fw-bold text-primary': copyType === '১ম কপি' }">১ম কপি</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0">
                <input type="radio" class="form-check-input mt-0" name="copyType" value="২য় কপি" v-model="copyType">
                <span :class="{ 'fw-bold text-primary': copyType === '২য় কপি' }">২য় কপি</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0">
                <input type="radio" class="form-check-input mt-0" name="copyType" value="৩য় কপি" v-model="copyType">
                <span :class="{ 'fw-bold text-primary': copyType === '৩য় কপি' }">৩য় কপি</span>
              </label>
            </div>
          </div>

          <!-- 2. Paper Background Color Selector -->
          <div class="d-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded border">
            <span class="small fw-bold text-dark text-nowrap"><i class="fas fa-palette text-warning me-1"></i> কাগজের রঙ (Paper Color):</span>
            <div class="d-flex align-items-center gap-2">
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0" title="সাদা">
                <input type="radio" class="form-check-input mt-0" name="paperColor" value="#ffffff" v-model="paperColor">
                <span class="color-swatch border" style="background-color: #ffffff;"></span>
                <span>সাদা</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0" title="হালকা হলুদ">
                <input type="radio" class="form-check-input mt-0" name="paperColor" value="#fffbe6" v-model="paperColor">
                <span class="color-swatch border" style="background-color: #fffbe6;"></span>
                <span>হলুদ</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0" title="হালকা নীল">
                <input type="radio" class="form-check-input mt-0" name="paperColor" value="#e6f7ff" v-model="paperColor">
                <span class="color-swatch border" style="background-color: #e6f7ff;"></span>
                <span>নীল</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0" title="হালকা সবুজ">
                <input type="radio" class="form-check-input mt-0" name="paperColor" value="#f6ffed" v-model="paperColor">
                <span class="color-swatch border" style="background-color: #f6ffed;"></span>
                <span>সবুজ</span>
              </label>
              <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer small mb-0" title="হালকা গোলাপি">
                <input type="radio" class="form-check-input mt-0" name="paperColor" value="#fff0f6" v-model="paperColor">
                <span class="color-swatch border" style="background-color: #fff0f6;"></span>
                <span>গোলাপি</span>
              </label>
            </div>
          </div>

        </div>

      </div>
    </div>

    <!-- 📄 Authentic Mushak 6.3 Challan Paper (Landscape A4 Format) -->
    <div
      class="mushak-paper mx-auto shadow-sm"
      :style="{ backgroundColor: paperColor, '--paper-bg': paperColor, '--filler-height': fillerHeight + 'px' }"
      id="mushak_print"
    >
      
      <!-- Top Content Container -->
      <div class="mushak-top-content">
        <!-- 1. Top Header: Gov Logo (Left) | Description (Middle) | Mushak 6.3 Badge & Copy Text (Right) -->
        <div class="mushak-header-grid mb-3">
          <!-- Left: Bangladesh Government Logo (Transparent circle) -->
          <div class="header-left">
            <img :src="bdGovLogo" alt="গণপ্রজাতন্ত্রী বাংলাদেশ সরকার" class="bd-gov-logo-img" @error="onGovLogoError">
          </div>

          <!-- Middle: Description and Company Information (Clean Transparent Header) -->
          <div class="header-middle text-center">
            <div class="header-gov-title">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার, জাতীয় রাজস্ব বোর্ড</div>
            <div class="header-main-title">কর চালানপত্র</div>
            <div class="header-sub-ref">[বিধি ৪০ এর উপ-বিধি (১) এর দফা (গ) ও দফা (চ) দ্রষ্টব্য]</div>

            <div class="supplier-info-box mt-1">
              <div class="supp-line">
                <span class="supp-lbl">নিবন্ধিত ব্যক্তির নাম</span>
                <span class="supp-col">:</span>
                <span class="supp-val fw-bold">
                  {{ site?.title || 'M.K. Electronics' }}
                </span>
              </div>
              <div class="supp-line">
                <span class="supp-lbl">নিবন্ধিত ব্যক্তির বিআইএন</span>
                <span class="supp-col">:</span>
                <span class="supp-val font-monospace fw-bold">
                  {{ site?.vat_no || site?.bin_no || '০০০৩৩৩৪১৩-০২০৮' }}
                </span>
              </div>
              <div class="supp-line">
                <span class="supp-lbl">চালানপত্র ইস্যুর ঠিকানা</span>
                <span class="supp-col">:</span>
                <span class="supp-val">
                  <span>{{ site?.address || 'আলমগীর প্লাজা, গ্রাউন্ড ফ্লোর, চ-৮৯, গুলশান বাড্ডা লিঙ্ক রোড, বাড্ডা, ঢাকা ১২১২' }}</span>
                </span>
              </div>
              <div class="supp-line" v-if="siteHelpline || siteWebsite">
                <span class="supp-lbl">সহায়তায়</span>
                <span class="supp-col">:</span>
                <span class="supp-val">
                  <span v-if="siteHelpline">{{ siteHelpline }}</span>
                  <span v-if="siteHelpline && siteWebsite"> ; </span>
                  <span v-if="siteWebsite">{{ siteWebsite }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Right: Mushak 6.3 Box Badge & Selected Copy Type under it -->
          <div class="header-right text-end">
            <div class="mushak-badge-box">
              মূসক-৬.৩
            </div>
            <div class="copy-type-text mt-1 fw-bold text-center">
              [ {{ copyType }} ]
            </div>
          </div>
        </div>

        <!-- 2. Buyer & Invoice Information Section (Aligned Side-by-Side as authentic Challan) -->
        <div class="buyer-invoice-section mb-2">
          <div class="d-flex justify-content-between align-items-start gap-3">
            
            <!-- Left: Buyer Details (Clean plain text alignment with no table cell borders) -->
            <div class="buyer-details-block" style="flex: 1 1 65%;">
              <table class="buyer-table">
                <tbody>
                  <tr>
                    <td class="b-lbl">ক্রেতার নাম</td>
                    <td class="b-col">:</td>
                    <td class="b-val fw-bold text-dark">{{ buyerNameWithPhone }}</td>
                  </tr>
                  <tr>
                    <td class="b-lbl">ক্রেতার বিআইএন</td>
                    <td class="b-col">:</td>
                    <td class="b-val font-monospace text-dark">{{ data.client?.bin_no || data.client?.vat || '' }}</td>
                  </tr>
                  <tr>
                    <td class="b-lbl">ক্রেতার ঠিকানা</td>
                    <td class="b-col">:</td>
                    <td class="b-val text-dark">{{ buyerAddress }}</td>
                  </tr>
                  <tr>
                    <td class="b-lbl">সরবরাহের গন্তব্যস্থল</td>
                    <td class="b-col">:</td>
                    <td class="b-val text-dark">{{ deliveryDestination }}</td>
                  </tr>
                  <tr>
                    <td class="b-lbl">যানবাহনের প্রকৃতি ও নং</td>
                    <td class="b-col">:</td>
                    <td class="b-val text-dark">{{ data.vehicle_info || '' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Right: Invoice Metadata Box (2-row bordered box matching physical pad) -->
            <div class="invoice-meta-block" style="flex: 0 0 30%; max-width: 32%;">
              <table class="challan-meta-table">
                <tbody>
                  <tr>
                    <td class="c-lbl">চালানপত্র নম্বর:</td>
                    <td class="c-val font-monospace fw-bold">{{ data.invoice_no }}</td>
                  </tr>
                  <tr>
                    <td class="c-lbl">ইস্যুর তারিখ:</td>
                    <td class="c-val font-monospace fw-bold">{{ formattedChallanDate }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>

        <!-- 3. Official 11-Column Products Table (Extended Height Matching Physical Pad) -->
        <table class="mushak-main-table w-100 mb-2">
          <thead>
            <tr>
              <th style="width: 4%;">ক্রমিক</th>
              <th style="width: 25%;">পণ্য বা সেবার বর্ণনা<br><span class="header-sub-text">(ব্র্যান্ড নামসহ)</span></th>
              <th style="width: 5.5%;">সরবরাহ<br>একক</th>
              <th style="width: 5.5%;">পরিমাণ</th>
              <th style="width: 9%;">একক মূল্য *<br><span class="header-sub-text">(টাকায়)</span></th>
              <th style="width: 9.5%;">মোট মূল্য<br><span class="header-sub-text">(টাকায়)</span></th>
              <th style="width: 6%;">সম্পূরক<br>শুল্কের হার</th>
              <th style="width: 7%;">সম্পূরক<br>শুল্কের পরিমাণ<br><span class="header-sub-text">(টাকায়)</span></th>
              <th style="width: 7.5%;">মূল্য সংযোজন<br>করের হার/<br>সুনির্দিষ্ট কর</th>
              <th style="width: 8%;">মূল্য সংযোজন কর/<br>সুনির্দিষ্ট কর এর<br>পরিমাণ (টাকায়)</th>
              <th style="width: 13%;">সকল প্রকার শুল্ক<br>ও করসহ মূল্য</th>
            </tr>
          </thead>
          <tbody>
            <!-- Actual Products (No horizontal borders between td cells) -->
            <tr v-for="(item, idx) in mushakItems" :key="'item-' + idx" class="product-item-row">
              <td class="text-center font-monospace">{{ formatBanglaNumber(idx + 1) }}</td>
              <td class="text-start">
                <div class="fw-bold product-title">{{ item.title }}</div>
                <div class="product-sub font-monospace" v-if="item.model_or_brand">{{ item.model_or_brand }}</div>
                <div class="product-sub font-monospace" v-if="item.serial_no">S/N: {{ item.serial_no }}</div>
                <div class="product-sub" v-if="item.variant">({{ item.variant }})</div>
              </td>
              <td class="text-center">{{ item.unit || 'Pc' }}</td>
              <td class="text-center font-monospace fw-bold">{{ formatBanglaNumber(item.qty) }}</td>
              <td class="text-end font-monospace text-nowrap">{{ formatMoney(item.unit_price) }}</td>
              <td class="text-end font-monospace text-nowrap">{{ formatMoney(item.total_price) }}</td>
              <td class="text-center font-monospace">-</td>
              <td class="text-center font-monospace">-</td>
              <td class="text-center font-monospace">{{ item.vat_rate }}%</td>
              <td class="text-end font-monospace text-nowrap">{{ formatMoney(item.vat_amount) }}</td>
              <td class="text-end font-monospace fw-bold text-nowrap">{{ formatMoney(item.total_with_vat) }}</td>
            </tr>

            <!-- Continuous Vertical Grid Filler Row with Generous Height -->
            <tr class="blank-filler-row">
              <td :style="{ height: fillerHeight + 'px' }">&nbsp;</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="fw-bold footer-total-row">
              <td colspan="4" class="text-center fw-bold fs-6">সর্বমোট :</td>
              <td></td>
              <td class="text-end font-monospace text-nowrap">{{ formatMoney(totalBasePrice) }}</td>
              <td class="text-center font-monospace">-</td>
              <td class="text-center font-monospace">-</td>
              <td class="text-center font-monospace">-</td>
              <td class="text-end font-monospace text-nowrap">{{ formatMoney(totalVatAmount) }}</td>
              <td class="text-end font-monospace fw-bold fs-6 text-nowrap" style="white-space: nowrap !important;">= {{ formatMoney(totalGrandPrice) }}</td>
            </tr>
          </tfoot>
        </table>

        <!-- 4. In Words Section -->
        <div class="in-words-section my-2 text-dark">
          <span class="fw-bold">কথায় : </span>
          <span class="fw-bold">{{ inWordsText }}</span>
        </div>
      </div>

      <!-- 5. Footer Section: Signatures & Note with generous space for seal & sign -->
      <div class="mushak-footer-section position-relative">
        <div class="d-flex justify-content-between align-items-end">
          
          <!-- Left: Authorized Person Signature Line and Note with space for seal -->
          <div class="signature-block">
            <div class="signature-line mb-1"></div>
            <div class="fw-bold text-dark mb-1 sig-title">
              প্রতিষ্ঠান কর্তৃপক্ষের দায়িত্বপ্রাপ্ত ব্যক্তির নাম, পদবী, স্বাক্ষর ও সিল
            </div>
            <div class="text-dark note-text">
              * সকল প্রকার কর ব্যতীত মূল্য
            </div>
          </div>

          <!-- Right: Empty spacer -->
          <div class="text-end"></div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
const model = "invoice";

export default {
  name: "InvoiceMushak",
  data() {
    return {
      model,
      page_title: "কর চালানপত্র (মূসক-৬.৩)",
      data: {},
      siteSetting: {},
      loading: true,
      govLogoFallback: false,
      copyType: "মূল কপি", // Default Copy Option
      paperColor: "#ffffff", // Default Paper Color
    };
  },

  computed: {
    site() {
      return this.$root.site || this.siteSetting || {};
    },

    bdGovLogo() {
      if (this.govLogoFallback) {
        return "/images/bd-gov-logo.png";
      }
      const assetUrl = this.$root.asset_url || "";
      return `${assetUrl}/images/bd-gov-logo.png`;
    },

    siteHelpline() {
      const phones = [];
      if (this.site?.mobile1) phones.push(this.site.mobile1);
      if (this.site?.mobile2) phones.push(this.site.mobile2);
      if (phones.length === 0 && this.site?.phone) phones.push(this.site.phone);
      return phones.join(", ");
    },

    siteWebsite() {
      if (this.site?.website) return this.site.website;
      if (this.site?.web) return this.site.web;
      if (this.site?.contact_email) return this.site.contact_email;
      if (this.site?.email) return this.site.email;
      return "www.mke.com.bd";
    },

    formattedChallanDate() {
      if (!this.data.invoice_date) return "";
      const d = new Date(this.data.invoice_date);
      if (!isNaN(d.getTime())) {
        const day = String(d.getDate()).padStart(2, "0");
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
      }
      return this.data.invoice_date;
    },

    buyerNameWithPhone() {
      const client = this.data.client;
      if (!client) return "Walk-in Customer (খুচরা ক্রেতা)";
      let name = client.name || "Walk-in Customer";
      if (client.mobile && client.mobile !== "00000000000") {
        name += " / " + client.mobile;
      }
      return name;
    },

    buyerAddress() {
      const client = this.data.client;
      if (!client || !client.address || client.address === "N/A") return "";
      return client.address;
    },

    deliveryDestination() {
      if (this.data.delivery_address) return this.data.delivery_address;
      if (this.data.client?.address && this.data.client.address !== "N/A") return this.data.client.address;
      return this.site?.address ? "কাউন্টার ডেলিভারি" : "";
    },

    effectiveVatRate() {
      if (this.data.vat_percent !== undefined && Number(this.data.vat_percent) > 0) {
        return Number(this.data.vat_percent);
      }
      const taxable = Math.max(0, (Number(this.data.original_amount) || 0) - (Number(this.data.discount) || 0));
      if (taxable > 0 && Number(this.data.vat) > 0) {
        return Number(((Number(this.data.vat) / taxable) * 100).toFixed(1));
      }
      return Number(this.site?.default_vat || 0);
    },

    mushakItems() {
      const details = this.data.details || this.data.invoice_details || [];
      if (!Array.isArray(details) || details.length === 0) return [];

      const origTotal = Number(this.data.original_amount) || 0;
      const discount = Number(this.data.discount) || 0;
      const taxableBase = Math.max(0, origTotal - discount);
      const discountRatio = origTotal > 0 ? taxableBase / origTotal : 1;
      const vatRate = this.effectiveVatRate;

      return details.map((d) => {
        const qty = Number(d.qty) || 1;
        const lineGross = Number(d.total_amount) || qty * (Number(d.amount) || 0);
        const lineBase = lineGross * discountRatio;
        const unitPrice = qty > 0 ? lineBase / qty : 0;
        const vatAmount = lineBase * (vatRate / 100);
        const totalWithVat = lineBase + vatAmount;

        let variant = "";
        if (d.color_title || (d.color && d.color.title)) {
          variant += d.color_title || d.color.title;
        }
        if (d.size_title || (d.size && d.size.title)) {
          variant += (variant ? " / " : "") + (d.size_title || d.size.title);
        }

        let modelBrand = "";
        if (d.item?.model_no) {
          modelBrand += d.item.model_no;
        }
        if (d.item?.brand && d.item.brand.title) {
          modelBrand += (modelBrand ? " - " : "") + d.item.brand.title;
        }

        return {
          title: d.title || (d.item ? d.item.title : "Product"),
          model_or_brand: modelBrand,
          barcode: d.barcode || (d.item ? d.item.barcode : ""),
          serial_no: d.serial_no || "",
          variant: variant,
          unit: d.unit_title || (d.item && d.item.unit ? d.item.unit.title : "Pc"),
          qty: qty,
          unit_price: unitPrice,
          total_price: lineBase,
          vat_rate: vatRate,
          vat_amount: vatAmount,
          total_with_vat: totalWithVat,
        };
      });
    },

    /* Intelligent filler height for authentic pad look */
    fillerHeight() {
      const count = this.mushakItems.length;
      if (count <= 1) return 185;
      if (count === 2) return 155;
      if (count === 3) return 125;
      if (count === 4) return 95;
      return 45;
    },

    totalBasePrice() {
      return this.mushakItems.reduce((sum, item) => sum + item.total_price, 0);
    },

    totalVatAmount() {
      return this.mushakItems.reduce((sum, item) => sum + item.vat_amount, 0);
    },

    totalGrandPrice() {
      return this.mushakItems.reduce((sum, item) => sum + item.total_with_vat, 0);
    },

    inWordsText() {
      const grandTotal = this.totalGrandPrice;
      return this.bengaliNumberToWords(grandTotal);
    },
  },

  methods: {
    onGovLogoError() {
      this.govLogoFallback = true;
    },

    formatMoney(val) {
      return Number(val || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatBanglaNumber(val) {
      const banglaDigits = {
        0: "০",
        1: "০১",
        2: "০২",
        3: "০৩",
        4: "০৪",
        5: "০৫",
        6: "০৬",
        7: "০৭",
        8: "০৮",
        9: "০৯",
      };
      if (val >= 0 && val <= 9) return banglaDigits[val] || val;
      return val;
    },

    bengaliNumberToWords(number) {
      if (number === undefined || number === null || isNaN(number) || Number(number) === 0) {
        return "শূন্য টাকা।";
      }

      const bangla0to99 = [
        "", "এক", "দুই", "তিন", "চার", "পাঁচ", "ছয়", "সাত", "আট", "নয়", "দশ",
        "এগারো", "বারো", "তেরো", "চৌদ্দ", "পনেরো", "ষোল", "সতেরো", "আঠারো", "উনিশ", "বিশ",
        "একুশ", "বাইশ", "তেইশ", "চব্বিশ", "পঁচিশ", "ছাব্বিশ", "সাতাশ", "আটাশ", "উনত্রিশ", "ত্রিশ",
        "একত্রিশ", "বত্রিশ", "তেত্রিশ", "চৌত্রিশ", "পঁয়ত্রিশ", "ছত্রিশ", "সাঁইত্রিশ", "আটত্রিশ", "ঊনচল্লিশ", "চল্লিশ",
        "একচল্লিশ", "বিয়াল্লিশ", "তেতাল্লিশ", "চুয়াল্লিশ", "পঁয়তাল্লিশ", "ছেচল্লিশ", "সাতচল্লিশ", "আটচল্লিশ", "ঊনপঞ্চাশ", "পঞ্চাশ",
        "একান্ন", "বায়ান্ন", "তিপ্পান্ন", "চুয়ান্ন", "পঞ্চান্ন", "ছাপ্পান্ন", "সাতান্ন", "আটান্ন", "ঊনষাট", "ষাট",
        "একষট্টি", "বাষট্টি", "তেষট্টি", "চৌষট্টি", "পঁয়ষট্টি", "ছেষট্টি", "সাতষট্টি", "আটষট্টি", "ঊনসত্তর", "সত্তর",
        "একাত্তর", "বাহাত্তর", "তিয়াত্তর", "চুয়াত্তর", "পঁচাত্তর", "ছিয়াত্তর", "সাতাত্তর", "আটাত্তর", "ঊনআশি", "আশি",
        "একাশি", "বিরাশি", "তিরাশি", "চুরাশি", "পঁচাশি", "ছিয়াশি", "সাতাশি", "আটাশি", "ঊননব্বই", "নব্বই",
        "একানব্বই", "বানব্বই", "তিরানব্বই", "চুরানব্বই", "পঁচানব্বই", "ছিয়ানব্বই", "সাতানব্বই", "আটানব্বই", "নিরানব্বই"
      ];

      const hundreds = [
        "", "একশত", "দুইশত", "তিনশত", "চারশত", "পাঁচশত", "ছয়শত", "সাতশত", "আটশত", "নয়শত"
      ];

      function convertNumber(n) {
        n = parseInt(n, 10);
        if (n <= 0) return "";
        if (n < 100) return bangla0to99[n] || "";

        let words = [];

        // কোটি (Crore - 1,00,00,000)
        if (n >= 10000000) {
          const crore = Math.floor(n / 10000000);
          words.push(convertNumber(crore) + " কোটি");
          n = n % 10000000;
        }

        // লক্ষ (Lakh - 1,00,000)
        if (n >= 100000) {
          const lakh = Math.floor(n / 100000);
          words.push(bangla0to99[lakh] + " লক্ষ");
          n = n % 100000;
        }

        // হাজার (Thousand - 1,000)
        if (n >= 1000) {
          const thousand = Math.floor(n / 1000);
          words.push(bangla0to99[thousand] + " হাজার");
          n = n % 1000;
        }

        // শত (Hundred - 100)
        if (n >= 100) {
          const hundred = Math.floor(n / 100);
          words.push(hundreds[hundred] || (bangla0to99[hundred] + " শত"));
          n = n % 100;
        }

        // 1 to 99
        if (n > 0) {
          words.push(bangla0to99[n]);
        }

        return words.join(" ");
      }

      const numFixed = Number(number).toFixed(2);
      const [takaStr, paisaStr] = numFixed.split(".");

      const taka = parseInt(takaStr, 10) || 0;
      const paisa = parseInt(paisaStr, 10) || 0;

      let result = "";
      if (taka > 0) {
        result += convertNumber(taka) + " টাকা";
      }

      if (paisa > 0) {
        result += (result ? " " : "") + bangla0to99[paisa] + " পয়সা";
      } else if (taka > 0) {
        result += " মাত্র";
      }

      return result.trim() ? result.trim() + "।" : "শূন্য টাকা।";
    },

    loadInvoice() {
      const id = this.$route.params.id;
      if (!id) return;

      this.loading = true;
      this.$root.spinner = true;
      axios
        .get(`invoice/${id}`)
        .then((res) => {
          this.data = res.data || {};
        })
        .catch((err) => {
          console.error(err);
          this.$toast("Failed to load invoice details", "error");
        })
        .finally(() => {
          this.loading = false;
          this.$root.spinner = false;
        });
    },

    getSiteSettings() {
      axios
        .get("siteSetting")
        .then((res) => {
          if (res.data) {
            this.siteSetting = res.data;
          }
        })
        .catch((e) => console.error(e));
    },

    printMushak() {
      window.print();
    },
  },

  mounted() {
    this.getSiteSettings();
    this.loadInvoice();
  },
};
</script>

<!-- 🌐 Global Print Styles (Must be un-scoped for @page and body isolation) -->
<style>
@media print {
  @page {
    size: A4 landscape !important;
    margin: 0mm !important;
  }

  html,
  body {
    width: 297mm !important;
    height: 210mm !important;
    max-width: 297mm !important;
    max-height: 210mm !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    background-color: var(--paper-bg, #ffffff) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Hide entire admin layout, sidebar, navbars, and outer components */
  body * {
    visibility: hidden !important;
  }

  /* Only make #mushak_print and its children visible */
  #mushak_print,
  #mushak_print * {
    visibility: visible !important;
  }

  #mushak_print {
    position: fixed !important;
    left: 0 !important;
    top: 0 !important;
    width: 297mm !important;
    height: 210mm !important;
    max-width: 297mm !important;
    max-height: 210mm !important;
    min-height: 210mm !important;
    margin: 0 !important;
    padding: 6mm 10mm 6mm 10mm !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    page-break-after: avoid !important;
    page-break-before: avoid !important;
    page-break-inside: avoid !important;
    background-color: var(--paper-bg, #ffffff) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    z-index: 999999 !important;
  }

  #mushak_print .bd-gov-logo-img {
    width: 65px !important;
    height: 65px !important;
  }

  #mushak_print .mushak-main-table th {
    padding: 3px 2px !important;
    font-size: 10px !important;
    background: transparent !important;
    background-color: transparent !important;
    color: #000 !important;
  }

  #mushak_print .mushak-main-table tbody td {
    padding: 2px 4px !important;
    font-size: 10px !important;
    background: transparent !important;
    background-color: transparent !important;
    color: #000 !important;
  }

  #mushak_print .blank-filler-row td {
    height: var(--filler-height, 180px) !important;
  }

  #mushak_print .mushak-main-table tfoot td {
    padding: 3px 4px !important;
    font-size: 10.5px !important;
    background: transparent !important;
    background-color: transparent !important;
    color: #000 !important;
  }

  #mushak_print .mushak-footer-section {
    margin-top: auto !important;
  }

  #mushak_print .signature-block {
    padding-top: 35px !important;
  }
}
</style>

<!-- 🎨 Component Scoped Screen Styles -->
<style scoped>
.font-bangla {
  font-family: 'Nikosh', 'SolaimanLipi', 'Kalpurush', 'SutonnyMJ', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #000;
}

.cursor-pointer {
  cursor: pointer;
}

.color-swatch {
  display: inline-block;
  width: 14px;
  height: 14px;
  border-radius: 3px;
  vertical-align: middle;
}

/* 📄 Landscape Paper Presentation */
.mushak-paper {
  max-width: 1040px;
  width: 100%;
  min-height: 680px;
  margin: 0 auto;
  border-radius: 4px;
  border: 1px solid #dcdcdc;
  color: #000000;
  padding: 24px 30px;
  transition: background-color 0.2s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.mushak-top-content {
  width: 100%;
}

/* 1. Top Header Grid (Clean transparent background) */
.mushak-header-grid {
  display: grid;
  grid-template-columns: 85px 1fr 85px;
  align-items: start;
  gap: 12px;
  background: transparent !important;
}

.header-left {
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
}

.bd-gov-logo-img {
  width: 72px;
  height: 72px;
  object-fit: contain;
  border-radius: 50%;
  background-color: transparent !important;
  background: transparent !important;
  display: block;
}

.header-gov-title {
  font-size: 15px;
  font-weight: 700;
  line-height: 1.25;
  color: #000;
}

.header-main-title {
  font-size: 19px;
  font-weight: 900;
  line-height: 1.25;
  margin: 2px 0;
  color: #000;
}

.header-sub-ref {
  font-size: 11.5px;
  font-weight: 600;
  line-height: 1.2;
  color: #000;
}

.supplier-info-box {
  font-size: 12px;
  color: #000;
  line-height: 1.35;
  background: transparent !important;
}

.supp-line {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4px;
  margin-top: 1px;
}

.supp-lbl {
  font-weight: bold;
}

.supp-col {
  font-weight: bold;
}

.mushak-badge-box {
  display: inline-block;
  border: 1.5px solid #000;
  padding: 3px 10px;
  font-size: 13px;
  font-weight: bold;
  letter-spacing: 0.5px;
  background-color: transparent !important;
  background: transparent !important;
  color: #000;
}

.copy-type-text {
  font-size: 11.5px;
  font-weight: 700;
  color: #000;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

/* 2. Buyer & Invoice Section */
.buyer-table {
  border-collapse: collapse;
  font-size: 12px;
  color: #000;
  width: 100%;
  background: transparent !important;
}

.buyer-table td {
  padding: 1.5px 3px;
  vertical-align: top;
  border: none !important;
  background: transparent !important;
}

.b-lbl {
  width: 140px;
  font-weight: bold;
  white-space: nowrap;
}

.b-col {
  width: 12px;
  font-weight: bold;
  text-align: center;
}

.b-val {
  color: #000;
}

/* Right Challan Box Frame */
.challan-meta-table {
  border-collapse: collapse;
  width: 100%;
  border: 1.5px solid #000 !important;
  background: transparent !important;
}

.challan-meta-table td {
  border: 1px solid #000 !important;
  padding: 4px 8px;
  font-size: 12px;
  vertical-align: middle;
  background: transparent !important;
}

.c-lbl {
  width: 50%;
  font-weight: bold;
  color: #000;
  white-space: nowrap;
}

.c-val {
  width: 50%;
  text-align: center;
  font-size: 13px;
  color: #000;
}

/* 3. Products Table (11 Columns) */
.mushak-paper .mushak-main-table,
.mushak-paper table.mushak-main-table {
  border-collapse: collapse !important;
  font-size: 11px;
  border: 1px solid #000000 !important;
  background-color: transparent !important;
  background: transparent !important;
}

.mushak-paper .mushak-main-table thead,
.mushak-paper .mushak-main-table thead tr,
.mushak-paper .mushak-main-table thead th,
.mushak-paper .mushak-main-table th,
.mushak-paper table.mushak-main-table thead th {
  border: 1px solid #000000 !important;
  padding: 4px 3px !important;
  color: #000000 !important;
  text-align: center;
  font-weight: bold;
  background-color: transparent !important;
  background: transparent !important;
  background-image: none !important;
  line-height: 1.2;
  vertical-align: middle;
}

.mushak-paper .mushak-main-table th *,
.mushak-paper .mushak-main-table th span,
.mushak-paper .mushak-main-table th .header-sub-text {
  color: #000000 !important;
}

.header-sub-text {
  font-size: 9.5px;
  font-weight: normal;
}

/* In table body: NO horizontal row borders, only vertical column dividers */
.mushak-paper .mushak-main-table tbody,
.mushak-paper .mushak-main-table tbody tr,
.mushak-paper .mushak-main-table tbody td {
  background-color: transparent !important;
  background: transparent !important;
}

.mushak-main-table tbody td {
  border-left: 1px solid #000000 !important;
  border-right: 1px solid #000000 !important;
  border-top: none !important;
  border-bottom: none !important;
  padding: 3px 5px;
  color: #000000 !important;
  vertical-align: top;
}

.product-title {
  font-size: 11.5px;
  line-height: 1.2;
}

.product-sub {
  font-size: 10px;
  line-height: 1.15;
  color: #222;
}

/* Blank filler row to stretch column lines down like authentic physical pad */
.blank-filler-row td {
  height: 185px;
  padding: 0 !important;
}

.mushak-paper .mushak-main-table tfoot,
.mushak-paper .mushak-main-table tfoot tr,
.mushak-paper .mushak-main-table tfoot td {
  border-top: 1.5px solid #000000 !important;
  border-bottom: 1.5px solid #000000 !important;
  border-left: 1px solid #000000 !important;
  border-right: 1px solid #000000 !important;
  background-color: transparent !important;
  background: transparent !important;
  padding: 4px 5px;
  color: #000000 !important;
  vertical-align: middle;
}

/* 4. In Words */
.in-words-section {
  font-size: 12px;
  background: transparent !important;
}

/* 5. Footer & Signatures (Ample room for seal & physical signature) */
.mushak-footer-section {
  width: 100%;
  margin-top: 25px;
  background: transparent !important;
}

.signature-block {
  padding-top: 40px; /* Generous space for rubber seal and physical signature */
}

.signature-line {
  width: 320px;
  border-top: 1.2px solid #000000;
}

.sig-title {
  font-size: 11.5px;
  margin-top: 2px;
}

.note-text {
  font-size: 11px;
}
</style>
