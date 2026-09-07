<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="expense_print_area">
    <!-- 💻 1. On-Screen Interactive Web Dashboard View -->
    <div class="expense-view-wrapper expense-web-view">
      <!-- 🌟 Top Hero / Expense Header Banner -->
      <div class="card border-0 shadow-sm mb-4 expense-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-file-invoice-dollar fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    Expense Voucher #{{ data.expenseid || data.id || 'N/A' }}
                  </h4>
                  <span class="badge" :class="data.approved_by ? 'bg-success text-white' : 'bg-warning text-dark'">
                    <i :class="data.approved_by ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ data.approved_by ? 'Approved' : 'Pending Approval' }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-info bg-opacity-25 text-white' : 'bg-secondary'">
                    {{ data.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-alt me-1"></i>Expense Date:
                    <strong class="text-white">{{ data.expense_date || 'N/A' }}</strong>
                  </span>
                  <span v-if="data.employee">
                    <i class="fas fa-user-tie me-1"></i>Employee:
                    <strong class="text-white">{{ data.employee.full_name }}</strong>
                  </span>
                  <span v-if="data.workorder">
                    <i class="fas fa-file-contract me-1"></i>Workorder:
                    <strong class="text-white">{{ data.workorder.order_no }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2">
              <router-link :to="{ name: 'expense.index' }" class="btn btn-outline-light btn-sm px-3 fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to List
              </router-link>
              <router-link
                v-if="!data.approved_by && $root.checkPermission('expense.edit')"
                :to="{ name: 'expense.edit', params: { id: data.id } }"
                class="btn btn-light btn-sm fw-bold px-3 shadow-sm">
                <i class="fas fa-edit me-1 text-primary"></i> Edit
              </router-link>
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="printExpenseVoucher">
                <i class="fas fa-print me-1"></i> Print Voucher
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- Total Expense Amount -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Total Expense Amount</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-success font-monospace text-nowrap">
                {{ $filter.formatBDT(data.total_amount || 0) }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                Total payable expense value
              </small>
            </div>
          </div>
        </div>

        <!-- Total Expense Heads -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Expense Heads (খাত)</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-list-ol"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">
                {{ (data.expense_details || []).length }} Items
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                Categorized account breakdown
              </small>
            </div>
          </div>
        </div>

        <!-- Approval Status -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Approval Status</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-shield-alt"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 font-monospace text-nowrap" :class="data.approved_by ? 'text-success' : 'text-warning'">
                {{ data.approved_admin ? data.approved_admin.full_name : 'Pending Approval' }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ data.approved_date ? 'Approved on ' + data.approved_date : 'Awaiting authorized approval' }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Section 1: Detailed Information Cards -->
      <div class="row g-3 mb-4">
        <!-- Left Card: Voucher & Staff Information -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-2 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="fas fa-info-circle text-primary"></i> Voucher & Staff Information
              </span>
              <span class="badge bg-light text-dark border font-monospace">#{{ data.expenseid || data.id }}</span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 align-middle">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 38%;">Expense ID</td>
                    <td style="width: 4%;">:</td>
                    <td class="font-monospace fw-bold text-dark">{{ data.expenseid || data.id || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Expense Date</td>
                    <td>:</td>
                    <td class="font-monospace text-dark">{{ data.expense_date || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Employee In-Charge</td>
                    <td>:</td>
                    <td class="fw-bold text-dark">
                      <span v-if="data.employee">
                        <i class="fas fa-user-tie text-secondary me-1"></i>{{ data.employee.full_name }}
                      </span>
                      <span v-else class="text-muted">Not Assigned</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Status</td>
                    <td>:</td>
                    <td>
                      <span class="badge" :class="data.status === 'active' ? 'bg-success' : 'bg-danger'">
                        {{ data.status || 'Active' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right Card: Workorder & Accounting Info -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-2 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="fas fa-file-contract text-primary"></i> Workorder & Audit Information
              </span>
              <span class="badge bg-light text-secondary border font-monospace" v-if="data.workorder">
                {{ data.workorder.order_no }}
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 align-middle">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 38%;">Linked Workorder</td>
                    <td style="width: 4%;">:</td>
                    <td>
                      <span v-if="data.workorder" class="fw-bold text-dark">
                        {{ data.workorder.order_no }}
                        <span class="text-muted small fw-normal" v-if="data.workorder.order_date">
                          ({{ data.workorder.order_date }})
                        </span>
                      </span>
                      <span v-else class="text-muted">None (General Expense)</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Approved By</td>
                    <td>:</td>
                    <td>
                      <span v-if="data.approved_admin" class="fw-bold text-success">
                        <i class="fas fa-check-circle me-1"></i>{{ data.approved_admin.full_name }}
                      </span>
                      <span v-else class="text-muted">Pending Approval</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Approved Date</td>
                    <td>:</td>
                    <td class="font-monospace text-dark">
                      {{ data.approved_date || 'Pending' }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Grand Total Amount</td>
                    <td>:</td>
                    <td class="font-monospace fw-bold text-success fs-6">
                      {{ $filter.formatBDT(data.total_amount || 0) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 📑 Section 2: Expense Breakdown & Account Heads Table -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <span class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle">
              <i class="fas fa-list-ol"></i>
            </span>
            <div>
              <h6 class="mb-0 fw-bold text-dark">
                Expense Items & Account Breakdown (খরচের খাত ও বিবরণী)
              </h6>
              <small class="text-muted">Itemized breakdown of all accounts charged in this voucher</small>
            </div>
          </div>
          <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace">
            {{ (data.expense_details || []).length }} Items
          </span>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                  <th style="width: 5%;" class="text-center">#</th>
                  <th style="width: 35%;">Expense Account Head (খাত)</th>
                  <th style="width: 42%;">Narration / Description (বিবরণ)</th>
                  <th style="width: 18%;" class="text-end">Amount (টাকা)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.expense_details" :key="index">
                  <td class="text-center font-monospace text-muted small">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-dark">
                      <i class="fas fa-landmark text-primary me-2"></i>
                      {{ item.account ? (item.account.account_code ? item.account.account_code + ' - ' : '') + (item.account.account_name || item.account.name) : 'N/A' }}
                    </div>
                  </td>
                  <td>
                    <span v-if="item.narration" class="text-dark">{{ item.narration }}</span>
                    <span v-else class="text-muted fst-italic">-</span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-dark">
                    {{ $filter.formatBDT(item.amount || 0) }}
                  </td>
                </tr>
                <tr v-if="!data.expense_details || data.expense_details.length === 0">
                  <td colspan="4" class="text-center py-4 text-muted">
                    No expense details recorded.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="3" class="text-end fw-bold py-3 text-uppercase text-muted small">
                    Total Expense Amount (সর্বমোট খরচ):
                  </td>
                  <td class="text-end font-monospace fw-bold text-success fs-5 py-3">
                    {{ $filter.formatBDT(data.total_amount || 0) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 🛡️ Section 3: Approval Management & Guidelines Card -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
          <span class="fw-bold small text-dark d-flex align-items-center gap-2">
            <i class="fas fa-shield-alt text-primary"></i> Voucher Approval & Verification
          </span>
          <span class="badge bg-secondary" v-if="!data.approved_by">Pending</span>
          <span class="badge bg-success" v-else>Approved</span>
        </div>
        <div class="card-body p-3">
          <div class="alert mb-0 border-0 shadow-sm" :class="data.approved_by ? 'alert-success' : 'alert-info'">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <h6 class="alert-heading fw-bold mb-1">
                  <i :class="data.approved_by ? 'fas fa-check-circle text-success me-1' : 'fas fa-info-circle text-info me-1'"></i>
                  {{ data.approved_by ? 'Voucher is Approved' : 'Expense Approval Guidelines' }}
                </h6>
                <p class="mb-1 small" v-if="!data.approved_by">
                  Please review the expense heads and amounts carefully. Once approved, payable vouchers will be created automatically.
                </p>
                <p class="mb-0 small" v-else>
                  Approved by <strong>{{ data.approved_admin?.full_name || 'Admin' }}</strong> on <strong>{{ data.approved_date || 'N/A' }}</strong>.
                </p>
              </div>

              <!-- Action Button Inside Alert -->
              <div v-if="!data.approved_by">
                <button
                  v-if="$root.checkPermission('expense.approved')"
                  type="button"
                  @click="approved"
                  class="btn btn-success fw-semibold px-4 shadow-sm">
                  <i class="fas fa-check-circle me-1"></i> Approve Expense Voucher
                </button>
              </div>
              <div v-else>
                <button
                  v-if="$root.checkPermission('expense.approvalcancel')"
                  type="button"
                  @click="approvalcancel"
                  class="btn btn-outline-danger fw-semibold px-4">
                  <i class="fas fa-times-circle me-1"></i> Cancel Approval
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Printable Clean Voucher Layout (#expense_print_area) -->
    <div id="expense_print_area" class="d-none">
      <div style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color: #000000; background: #ffffff; padding: 10px; width: 100%; box-sizing: border-box;">
        
        <!-- 🏢 1. Company Header -->
        <div style="text-align: center; margin-bottom: 16px; border-bottom: 2px solid #000000; padding-bottom: 12px;">
          <h1 style="font-size: 24px; font-weight: 800; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; color: #000000;">
            {{ $root.site?.title || 'DHRUPODI FASHION' }}
          </h1>
          <p style="font-size: 12px; margin: 0 0 3px 0; color: #222222;" v-if="$root.site?.address">
            {{ $root.site.address }}
          </p>
          <p style="font-size: 11px; margin: 0 0 8px 0; color: #333333;">
            <span v-if="$root.site?.phone"><strong>Phone:</strong> {{ $root.site.phone }}</span>
            <span v-if="$root.site?.email" style="margin-left: 15px;"><strong>Email:</strong> {{ $root.site.email }}</span>
          </p>
          <div style="margin-top: 6px;">
            <span style="display: inline-block; font-size: 13px; font-weight: 800; padding: 5px 22px; border: 1.5px solid #000000; border-radius: 20px; background-color: #f1f5f9; color: #000000; text-transform: uppercase; letter-spacing: 0.5px;">
              EXPENSE VOUCHER / খরচের ভাউচার
            </span>
          </div>
        </div>

        <!-- 📋 2. Meta Information Section (Clean Borderless Key-Value Layout as per PDF) -->
        <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 20px; font-size: 12px; line-height: 1.5;">
          <tbody>
            <tr>
              <td style="width: 15%; padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Voucher No:
              </td>
              <td style="width: 35%; padding: 4px 10px 4px 0; font-weight: bold; font-family: monospace; color: #000000; border: none; vertical-align: top;">
                #{{ data.expenseid || data.id }}
              </td>
              <td style="width: 18%; padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Workorder:
              </td>
              <td style="width: 32%; padding: 4px 0; color: #000000; border: none; vertical-align: top;">
                {{ data.workorder?.order_no || 'N/A' }}
              </td>
            </tr>
            <tr>
              <td style="padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Expense Date:
              </td>
              <td style="padding: 4px 10px 4px 0; color: #000000; border: none; vertical-align: top;">
                {{ data.expense_date }}
              </td>
              <td style="padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Approval Status:
              </td>
              <td style="padding: 4px 0; font-weight: bold; border: none; vertical-align: top;">
                <span :style="data.approved_by ? 'color: #166534;' : 'color: #854d0e;'">
                  {{ data.approved_by ? 'Approved' : 'Pending' }}
                  <span v-if="data.approved_date" style="font-weight: normal; color: #000000;">({{ data.approved_date }})</span>
                </span>
              </td>
            </tr>
            <tr>
              <td style="padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Employee:
              </td>
              <td style="padding: 4px 10px 4px 0; color: #000000; border: none; vertical-align: top;">
                {{ data.employee?.full_name || 'N/A' }}
              </td>
              <td style="padding: 4px 0; font-weight: bold; color: #000000; border: none; vertical-align: top;">
                Approved By:
              </td>
              <td style="padding: 4px 0; color: #000000; border: none; vertical-align: top;">
                {{ data.approved_admin?.full_name || 'N/A' }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 📊 3. Itemized Expense Table (Full 100% Solid Borders Everywhere) -->
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 0; font-size: 12.5px;">
          <thead>
            <tr style="background-color: #e2e8f0;">
              <th style="width: 6%; border: 1px solid #000000; padding: 8px 10px; text-align: center; font-weight: 800; color: #000000; font-size: 12.5px;">
                #
              </th>
              <th style="width: 38%; border: 1px solid #000000; padding: 8px 10px; text-align: left; font-weight: 800; color: #000000; font-size: 12.5px;">
                Expense Account Head (খরচের খাত)
              </th>
              <th style="width: 36%; border: 1px solid #000000; padding: 8px 10px; text-align: left; font-weight: 800; color: #000000; font-size: 12.5px;">
                Narration / Description (বিবরণ)
              </th>
              <th style="width: 20%; border: 1px solid #000000; padding: 8px 10px; text-align: right; font-weight: 800; color: #000000; font-size: 12.5px;">
                Amount (টাকা)
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data.expense_details" :key="index" :style="index % 2 === 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;'">
              <td style="border: 1px solid #000000; padding: 8px 10px; text-align: center; font-family: monospace; color: #000000;">
                {{ index + 1 }}
              </td>
              <td style="border: 1px solid #000000; padding: 8px 10px; font-weight: bold; color: #000000;">
                {{ item.account ? (item.account.account_code ? item.account.account_code + ' - ' : '') + (item.account.account_name || item.account.name) : 'N/A' }}
              </td>
              <td style="border: 1px solid #000000; padding: 8px 10px; color: #000000;">
                {{ item.narration || '-' }}
              </td>
              <td style="border: 1px solid #000000; padding: 8px 10px; text-align: right; font-weight: bold; font-family: monospace; color: #000000;">
                {{ $filter.formatBDT(item.amount || 0) }}
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr style="background-color: #f1f5f9;">
              <th colspan="3" style="border: 1px solid #000000; padding: 9px 10px; text-align: right; font-weight: 800; font-size: 13px; color: #000000; text-transform: uppercase;">
                TOTAL AMOUNT (সর্বমোট টাকা):
              </th>
              <th style="border: 1px solid #000000; padding: 9px 10px; text-align: right; font-weight: 800; font-family: monospace; font-size: 14px; color: #000000;">
                {{ $filter.formatBDT(data.total_amount || 0) }}
              </th>
            </tr>
          </tfoot>
        </table>

        <!-- 🔤 4. Amount In Words Box -->
        <div style="border: 1.5px solid #000000; border-top: none; padding: 8px 12px; background-color: #f8fafc; font-size: 12px; margin-bottom: 50px;">
          <strong style="text-transform: uppercase; color: #000000; margin-right: 6px;">IN WORDS (কথায়):</strong>
          <span style="font-weight: 600; color: #000000;">{{ currencyToWordsBD(data.total_amount || 0) }}</span>
        </div>

        <!-- ✍️ 5. Signatures Block -->
        <table style="width: 100%; border: none; margin-top: 40px; font-size: 12.5px;">
          <tbody>
            <tr>
              <td style="width: 30%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 6px; margin: 0 15px;">
                  <strong style="color: #000000;">Prepared By</strong>
                  <div style="font-size: 11px; color: #333333;">(প্রস্তুতকারী)</div>
                </div>
              </td>
              <td style="width: 40%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 6px; margin: 0 25px;">
                  <strong style="color: #000000;">Checked By</strong>
                  <div style="font-size: 11px; color: #333333;">(যাচাইকারী)</div>
                </div>
              </td>
              <td style="width: 30%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 6px; margin: 0 15px;">
                  <strong style="color: #000000;">Approved By</strong>
                  <div style="font-size: 11px; color: #333333;">({{ data.approved_admin?.full_name || 'অনুমোদনকারী' }})</div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>
  </view-page>
</template>

<script>
import axios from "axios";

const model = "expense";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  methods: {
    printExpenseVoucher() {
      const printContents = document.getElementById("expense_print_area");
      if (!printContents) return;

      const WinPrint = window.open(
        "",
        "",
        "left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0"
      );
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Expense Voucher - #${this.data.expenseid || this.data.id || ""}</title>
        <meta charset="utf-8">
        <style>
          * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
          }
          @page {
            size: A4 portrait;
            margin: 12mm 15mm;
          }
          html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
          }
          table {
            border-collapse: collapse !important;
          }
          @media print {
            body {
              -webkit-print-color-adjust: exact !important;
              print-color-adjust: exact !important;
            }
          }
        </style>
      </head>
      <body>
        <div style="width: 100%; max-width: 190mm; margin: 0 auto;">
          ${printContents.innerHTML}
        </div>
      </body>
      </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 350);
    },

    approved() {
      if (!confirm("Are you sure you want to approve this expense voucher?")) {
        return;
      }
      axios
        .post("expense/approved", {
          expense_id: this.$route.params.id,
        })
        .then((res) => {
          this.$toast(
            res.data.message || "Expense voucher approved successfully",
            "success"
          );
          this.get_data(`${this.model}/${this.$route.params.id}`);
        })
        .catch((err) => {
          this.$toast(err.response?.data?.message || "Approval failed", "error");
        });
    },

    approvalcancel() {
      if (
        !confirm("Are you sure you want to cancel the approval of this expense voucher?")
      ) {
        return;
      }

      axios
        .post("expense/approvalcancel", {
          expense_id: this.$route.params.id,
        })
        .then((res) => {
          this.$toast(
            res.data.message || "Approval cancelled successfully",
            "success"
          );
          this.get_data(`${this.model}/${this.$route.params.id}`);
        })
        .catch((err) => {
          this.$toast(err.response?.data?.message || "Cancel approval failed", "error");
        });
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.expense-view-wrapper {
  max-width: 100%;
}

/* 🌟 Hero Header Banner */
.expense-hero-banner {
  background: linear-gradient(135deg, #112c47 0%, #1e3a8a 100%);
  border-radius: 12px;
}

.hero-icon-box {
  width: 54px;
  height: 54px;
}

/* 📊 KPI Stat Cards */
.stat-card {
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 44px;
  height: 44px;
  font-size: 18px;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 71, 0.08);
}

.text-theme {
  color: #112c47 !important;
}
</style>