<template>
  <div class="container-fluid p-3">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fas fa-file-contract"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ isEdit ? $t('Edit Terms & Condition') : $t('Create Terms & Condition') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Configure condition description and target business module') }}</small>
              </div>
            </div>
            <router-link to="/termsCondition" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
            </router-link>
          </div>
          <div class="card-body p-4">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">{{ $t('Target Module') }}:</label>
                <select class="form-select" v-model="formData.module_name" required>
                  <option value="Invoice">{{ $t('Invoice') }} ({{ $t('POS / Sales') }})</option>
                  <option value="Purchase Order">{{ $t('Purchase Order') }}</option>
                  <option value="Warranty">{{ $t('Warranty & Claims') }}</option>
                  <option value="Quotation">{{ $t('Quotation') }}</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">{{ $t('Condition Description') }}:</label>
                <textarea class="form-control" rows="5" :placeholder="$t('Enter condition statement...')" v-model="formData.condition_text" required></textarea>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-dark">{{ $t('Sorting Order') }}:</label>
                  <input type="number" class="form-control" v-model.number="formData.sorting" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-dark">{{ $t('Status') }}:</label>
                  <select class="form-select" v-model="formData.status">
                    <option value="active">{{ $t('Active') }}</option>
                    <option value="inactive">{{ $t('Inactive') }}</option>
                  </select>
                </div>
              </div>

              <div class="p-3 border rounded bg-light mb-4">
                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                  <input class="form-check-input mt-0 cursor-pointer" type="checkbox" role="switch" id="defaultSwitch" v-model="formData.is_default" />
                  <label class="form-check-label fw-bold text-dark cursor-pointer small" for="defaultSwitch">
                    {{ $t('Include by default in POS Terminal & Document Generation') }}
                  </label>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <router-link to="/termsCondition" class="btn btn-secondary btn-sm px-3">{{ $t('Cancel') }}</router-link>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" :disabled="isSubmitting">
                  <i class="fas fa-spinner fa-spin me-1" v-if="isSubmitting"></i>
                  <i class="fas fa-save me-1" v-else></i>
                  {{ isEdit ? $t('Update Condition') : $t('Save Condition') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "TermsConditionCreate",
  data() {
    return {
      isEdit: false,
      isSubmitting: false,
      formData: {
        id: null,
        module_name: "Invoice",
        condition_text: "",
        sorting: 1,
        is_default: true,
        status: "active",
      },
    };
  },
  methods: {
    fetchData(id) {
      axios.get(`termsCondition/${id}`).then((res) => {
        if (res.data) {
          this.formData = {
            id: res.data.id,
            module_name: res.data.module_name,
            condition_text: res.data.condition_text,
            sorting: res.data.sorting,
            is_default: !!res.data.is_default,
            status: res.data.status,
          };
        }
      });
    },
    submitForm() {
      this.isSubmitting = true;
      const url = this.isEdit ? `termsCondition/${this.formData.id}` : "termsCondition";
      const method = this.isEdit ? "put" : "post";

      axios[method](url, this.formData)
        .then(() => {
          this.isSubmitting = false;
          this.$toast("Saved successfully", "success");
          this.$router.push({ name: "termsCondition.index" });
        })
        .catch((err) => {
          this.isSubmitting = false;
          this.$toast("Failed to save", "error");
        });
    },
  },
  created() {
    const id = this.$route.params.id;
    if (id) {
      this.isEdit = true;
      this.fetchData(id);
    }
  },
};
</script>
