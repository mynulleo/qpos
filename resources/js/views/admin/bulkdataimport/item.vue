<template>
  <div class="col-md-12 item-import-container pb-5">
    <!-- Page Header Breadcrumb & Actions -->
    <div class="card shadow-sm border-0 mb-3 rounded-3">
      <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="header-icon-box theme-bg text-white shadow-sm">
            <i class="fas fa-file-import fs-5"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold theme-text">Bulk Item CSV / Excel Import</h5>
            <small class="text-secondary">একসাথে একাধিক আইটেম/পণ্য দ্রুত আপলোড ও ইম্পোর্ট করুন</small>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Download Sample CSV Button -->
          <button
            type="button"
            class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm fw-semibold"
            @click="downloadSampleCsv"
          >
            <i class="fas fa-file-csv fs-6 text-success"></i>
            <span>Download Sample CSV</span>
          </button>

          <!-- Back to Item List -->
          <router-link
            :to="{ name: 'item.index' }"
            class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm"
          >
            <i class="fas fa-boxes"></i>
            <span>Item List</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Main Dual-Pane Layout -->
    <div class="row g-3">
      <!-- 📋 Left Column: Step-by-Step Instructions & Field Guidelines -->
      <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100 rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
            <i class="fas fa-info-circle text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">ইম্পোর্ট নির্দেশিকা ও কলাম নিয়মাবলী (Import Instructions)</h6>
          </div>
          <div class="card-body p-3 p-md-4">
            <!-- Step 1 -->
            <div class="instruction-step mb-3 p-3 rounded-2 bg-light border-start border-primary border-4">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="fw-bold text-primary">
                  <i class="fas fa-1 me-1"></i> ধাপ ১: স্যাম্পল ফাইল ডাউনলোড করুন
                </span>
                <button
                  type="button"
                  class="btn btn-xs btn-primary fw-bold"
                  @click="downloadSampleCsv"
                >
                  <i class="fas fa-download me-1"></i> Sample .CSV
                </button>
              </div>
              <p class="small text-secondary mb-0">
                প্রথমে স্যাম্পল CSV ফাইলটি ডাউনলোড করে নিন। ফাইলের কলাম হেডার পরিবর্তন করবেন না, শুধুমাত্র রো অনুযায়ী ডাটা পূরণ করুন।
              </p>
            </div>

            <!-- Step 2 -->
            <div class="instruction-step mb-3 p-3 rounded-2 bg-light border-start border-warning border-4">
              <span class="fw-bold text-dark d-block mb-2">
                <i class="fas fa-2 me-1 text-warning"></i> ধাপ ২: কলাম নিয়মাবলী ও গ্রহণযোগ্য ভ্যালু (Field Specifications)
              </span>
              <div class="table-responsive rounded border">
                <table class="table table-sm table-hover table-striped mb-0 text-start align-middle">
                  <thead class="theme-bg text-white">
                    <tr>
                      <th class="py-2" width="22%">Column Name</th>
                      <th class="py-2" width="16%">Status</th>
                      <th class="py-2">Rules & Sample Value</th>
                    </tr>
                  </thead>
                  <tbody class="small font-monospace">
                    <tr>
                      <td class="fw-bold text-primary">title</td>
                      <td><span class="badge bg-danger">Required</span></td>
                      <td class="text-dark font-sans-serif">পণ্যের নাম। উদাঃ <code>Dell Inspiron 15 Laptop</code></td>
                    </tr>
                    <tr>
                      <td class="fw-bold">category</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        ক্যাটাগরির নাম বা আইডি। নাম না থাকলে স্বয়ংক্রিয়ভাবে নতুন ক্যাটাগরি তৈরি হবে। উদাঃ <code>Electronics</code>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">barcode</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        ইউনিক বারকোড। খালি রাখলে সিস্টেম থেকে অটো বারকোড তৈরি হবে।
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">unit</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        এককের নাম বা আইডি। যেমনঃ <code>Pcs</code>, <code>Box</code>, <code>Kg</code> (ডিফল্ট Pcs)
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">opening_rate</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        ক্রয়মূল্য / প্রাথমিক রেট (সংখ্যা)। উদাঃ <code>62000</code>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">opening_qty</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        প্রাথমিক স্টক পরিমাণ (সংখ্যা)। উদাঃ <code>10</code>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">warranty_type</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        <code>none</code>, <code>warranty</code> অথবা <code>guarantee</code>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">warranty_period</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        ওয়ারেন্টির মেয়াদ। উদাঃ <code>1 Year</code>, <code>6 Months</code>, <code>7 Days</code>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold">description</td>
                      <td><span class="badge bg-info text-dark">Optional</span></td>
                      <td class="text-dark font-sans-serif">
                        পণ্যের সংক্ষিপ্ত বিবরণ বা স্পেসিফিকেশন।
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Step 3 -->
            <div class="instruction-step p-3 rounded-2 bg-light border-start border-success border-4">
              <span class="fw-bold text-success d-block mb-1">
                <i class="fas fa-3 me-1"></i> ধাপ ৩: ফাইলটি সেভ ও আপলোড করুন
              </span>
              <p class="small text-secondary mb-0">
                ফাইলটি <code>.csv</code> বা <code>.xlsx</code> ফরম্যাটে সেভ করে ডানপাশের আপলোড ফর্ম দিয়ে সাবমিট করুন।
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- 📤 Right Column: File Upload Form -->
      <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100 rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
            <i class="fas fa-cloud-upload-alt text-success fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">ফাইল আপলোড করুন (Upload CSV / Excel)</h6>
          </div>
          <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
            <form @submit.prevent="submitImport" enctype="multipart/form-data">
              <!-- Drag and Drop Dropzone -->
              <div
                class="upload-dropzone p-4 text-center rounded-3 border-2 border-dashed mb-3"
                :class="{ 'border-primary bg-primary-soft': isDragging, 'bg-light': !isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleFileDrop"
                @click="$refs.fileInput.click()"
              >
                <input
                  type="file"
                  ref="fileInput"
                  class="d-none"
                  accept=".csv, .xlsx, .xls, text/csv"
                  @change="onFileSelected"
                />

                <div v-if="!selectedFile">
                  <div class="upload-icon-circle mx-auto mb-2 text-primary">
                    <i class="fas fa-file-upload fs-3"></i>
                  </div>
                  <h6 class="fw-bold text-dark mb-1">ক্লিক করুন অথবা ফাইল ড্র্যাগ করে এখানে ছাড়ুন</h6>
                  <p class="text-secondary small mb-2">সমর্থিত ফরম্যাট: <strong>.CSV</strong>, <strong>.XLSX</strong>, <strong>.XLS</strong></p>
                  <span class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold">
                    <i class="fas fa-folder-open me-1"></i> Browse File
                  </span>
                </div>

                <div v-else class="selected-file-info py-2">
                  <div class="upload-icon-circle mx-auto mb-2 text-success bg-success-soft">
                    <i class="fas fa-file-csv fs-3"></i>
                  </div>
                  <h6 class="fw-bold text-dark mb-1 text-truncate" style="max-width: 280px; margin: 0 auto;">
                    {{ selectedFile.name }}
                  </h6>
                  <span class="badge bg-secondary mb-2">{{ formatFileSize(selectedFile.size) }}</span>
                  <div>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-danger px-2 rounded-pill"
                      @click.stop="removeFile"
                    >
                      <i class="fas fa-times me-1"></i> Remove File
                    </button>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="btn theme_search_btn w-100 py-2.5 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2 fs-6"
                :disabled="!selectedFile || isUploading"
              >
                <span v-if="isUploading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <i v-else class="fas fa-check-circle"></i>
                <span>{{ isUploading ? 'ইম্পোর্ট প্রসেস হচ্ছে...' : 'Start Import Items' }}</span>
              </button>
            </form>

            <!-- Quick Template Download Banner -->
            <div class="mt-4 p-3 rounded-2 bg-light border d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-file-csv fs-4 text-success"></i>
                <div>
                  <div class="fw-bold small text-dark">স্যাম্পল ফাইল দরকার?</div>
                  <div class="text-muted" style="font-size: 11px;">হেডার ও ডামি ডাটা সহ ডাউনলোড করুন</div>
                </div>
              </div>
              <button
                type="button"
                class="btn btn-sm btn-outline-success fw-bold px-3 rounded-pill"
                @click="downloadSampleCsv"
              >
                <i class="fas fa-download me-1"></i> Download
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📊 Real-time Result & Error Audit Section (Rendered below the form) -->
    <div class="row mt-4" v-if="importResult">
      <div class="col-12">
        <!-- Success Banner -->
        <div
          v-if="importResult.success && importResult.imported_count > 0"
          class="alert alert-success shadow-sm rounded-3 d-flex align-items-center justify-content-between p-3 mb-3 border-0 border-start border-success border-5"
        >
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fs-4 text-success"></i>
            <div>
              <h6 class="mb-0 fw-bold text-success">ইম্পোর্ট সফলভাবে সম্পন্ন হয়েছে!</h6>
              <span class="small text-dark">
                মোট <strong>{{ importResult.imported_count }}</strong> টি পণ্য সফলভাবে ডাটাবেজে সংরক্ষণ করা হয়েছে।
              </span>
            </div>
          </div>
          <router-link :to="{ name: 'item.index' }" class="btn btn-sm btn-success fw-bold px-3">
            <i class="fas fa-list me-1"></i> View Item List
          </router-link>
        </div>

        <!-- ⚠️ Failure / Error Audit Table (Requirement 4: Row, Column, Reason) -->
        <div
          v-if="importResult.failures && importResult.failures.length > 0"
          class="card shadow-sm border-0 rounded-3 border-start border-danger border-5"
        >
          <div class="card-header bg-danger bg-opacity-10 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-exclamation-triangle text-danger fs-5"></i>
              <div>
                <h6 class="mb-0 fw-bold text-danger">
                  ইম্পোর্ট ত্রুটি ও সংশোধনের বিবরণ ({{ importResult.failures.length }} টি সমস্যা চিহ্নিত)
                </h6>
                <small class="text-secondary">নিচের রো ও কলামগুলোর ডাটা সংশোধন করে পুনরায় আপলোড করুন</small>
              </div>
            </div>
            <span class="badge bg-danger fs-6 px-3 py-1.5">
              {{ importResult.skipped_count || importResult.failures.length }} Failed Rows
            </span>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="theme-bg text-white">
                  <tr>
                    <th class="text-center py-2.5" width="10%">Row # (রো)</th>
                    <th class="py-2.5" width="18%">Column / Field (কলাম)</th>
                    <th class="py-2.5" width="22%">Submitted Value (প্রদত্ত মান)</th>
                    <th class="py-2.5">Failure Reason & Suggestion (ত্রুটির কারণ ও সমাধান)</th>
                  </tr>
                </thead>
                <tbody class="font-monospace small">
                  <tr v-for="(err, i) in importResult.failures" :key="i" class="error-row">
                    <td class="text-center fw-bold text-danger font-monospace">
                      <span class="badge bg-danger-soft text-danger border border-danger px-2">
                        Row {{ err.row }}
                      </span>
                    </td>
                    <td class="fw-bold text-dark">
                      <span class="badge bg-light text-dark border px-2 py-1">
                        {{ err.column }}
                      </span>
                    </td>
                    <td class="text-secondary text-truncate" style="max-width: 220px;">
                      <code class="text-dark bg-light px-1.5 py-0.5 rounded border">{{ err.value || 'N/A' }}</code>
                    </td>
                    <td class="text-danger font-sans-serif">
                      <i class="fas fa-times-circle me-1 text-danger"></i>
                      <span>{{ err.error }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BulkItemImport',
  data() {
    return {
      selectedFile: null,
      isDragging: false,
      isUploading: false,
      importResult: null,
    };
  },
  methods: {
    onFileSelected(e) {
      const file = e.target.files[0];
      this.validateAndSetFile(file);
    },
    handleFileDrop(e) {
      this.isDragging = false;
      const file = e.dataTransfer.files[0];
      this.validateAndSetFile(file);
    },
    validateAndSetFile(file) {
      if (!file) return;

      const ext = file.name.split('.').pop().toLowerCase();
      if (!['csv', 'xlsx', 'xls', 'txt'].includes(ext)) {
        if (typeof this.$toast === 'function') {
          this.$toast('Only .csv or .xlsx files are supported.', 'error');
        } else {
          alert('Only .csv or .xlsx files are supported.');
        }
        return;
      }

      this.selectedFile = file;
      this.importResult = null;
    },
    removeFile() {
      this.selectedFile = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    submitImport() {
      if (!this.selectedFile) return;

      this.isUploading = true;
      this.importResult = null;

      const formData = new FormData();
      formData.append('itemfile', this.selectedFile);

      axios.post('bulkdataimport/itemimport', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      .then(res => {
        this.importResult = res.data;
        if (res.data.success) {
          if (typeof this.$toast === 'function') {
            this.$toast(res.data.message || 'Items imported successfully!', 'success');
          }
        } else {
          if (typeof this.$toast === 'function') {
            this.$toast('Some rows had errors during import.', 'warning');
          }
        }
      })
      .catch(err => {
        if (err.response && err.response.data) {
          this.importResult = err.response.data;
        } else {
          this.importResult = {
            success: false,
            message: 'Failed to upload file.',
            failures: [
              {
                row: 'General',
                column: 'Network/Server',
                value: this.selectedFile.name,
                error: err.message || 'Server returned an error.',
              }
            ]
          };
        }
        if (typeof this.$toast === 'function') {
          this.$toast('Import failed with errors. Please check the table below.', 'error');
        }
      })
      .finally(() => {
        this.isUploading = false;
      });
    },
    downloadSampleCsv() {
      // 1. Try direct backend stream endpoint
      const downloadUrl = (this.$root.baseurl ? this.$root.baseurl + '/' : '/') + 'admin/bulkdataimport/item-sample-csv';
      
      // 2. Also prepare dynamic browser-generated CSV fallback
      const csvHeaders = [
        'title',
        'category',
        'barcode',
        'unit',
        'opening_rate',
        'opening_qty',
        'warranty_type',
        'warranty_period',
        'description'
      ];

      const sampleRows = [
        [
          'Dell Inspiron 15 Core i5 Laptop',
          'Electronics',
          '8801001001',
          'Pcs',
          '62000',
          '10',
          'warranty',
          '2 Years',
          '15.6 inch FHD 8GB RAM 512GB SSD'
        ],
        [
          'Samsung 24 Inch IPS Borderless Monitor',
          'Electronics',
          '8801001002',
          'Pcs',
          '14500',
          '15',
          'warranty',
          '3 Years',
          '75Hz Refresh Rate HDMI/VGA'
        ],
        [
          'Logitech Wireless Keyboard & Mouse Combo MK270',
          'Electronics',
          '8801001003',
          'Pcs',
          '2200',
          '25',
          'guarantee',
          '1 Year',
          'USB Nano Receiver 2.4GHz'
        ]
      ];

      try {
        let csvContent = 'data:text/csv;charset=utf-8,' + [
          csvHeaders.join(','),
          ...sampleRows.map(e => e.map(val => `"${val}"`).join(','))
        ].join('\n');

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'item_import_sample.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        if (typeof this.$toast === 'function') {
          this.$toast('Sample CSV downloaded!', 'success');
        }
      } catch (e) {
        window.open(downloadUrl, '_blank');
      }
    },
  },
};
</script>

<style scoped>
.item-import-container {
  font-family: inherit;
}

.theme-bg {
  background-color: rgb(17, 44, 70) !important;
}

.theme-text {
  color: rgb(17, 44, 70) !important;
}

.theme_search_btn {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  border: 1px solid rgb(17, 44, 70) !important;
  font-weight: 600;
  transition: all 0.2s ease-in-out;
}

.theme_search_btn:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

.header-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-dropzone {
  border: 2px dashed #cbd5e1;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}

.upload-dropzone:hover {
  border-color: rgb(17, 44, 70);
  background-color: #f8fafc !important;
}

.bg-primary-soft {
  background-color: rgba(17, 44, 70, 0.05) !important;
}

.bg-success-soft {
  background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-danger-soft {
  background-color: rgba(220, 53, 69, 0.1) !important;
}

.upload-icon-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e2e8f0;
}

.font-sans-serif {
  font-family: system-ui, -apple-system, sans-serif !important;
}

.btn-xs {
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
  line-height: 1;
}

.error-row:hover td {
  background-color: #fff1f2 !important;
}
</style>
