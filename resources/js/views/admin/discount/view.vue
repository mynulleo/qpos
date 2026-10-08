<template>
  <view-page>
    <template #custom_header>
      <div class="card border shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-tags text-primary fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark">{{ data.name }}</h5>
          </div>
          <span class="badge" :class="data.status === 'active' ? 'bg-success' : 'bg-secondary'">
            {{ data.status }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Scope') }}</label>
              <strong class="text-dark">{{ data.discount_type === 'category' ? $t('Category Wise') : $t('Item Wise') }}</strong>
            </div>

            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Target') }}</label>
              <strong class="text-dark">{{ data.target_title || 'All' }}</strong>
            </div>

            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Applicable On') }}</label>
              <strong class="text-dark text-capitalize">{{ data.applicable_on }}</strong>
            </div>

            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Discount Value') }}</label>
              <strong class="text-success font-monospace fs-6">{{ data.discount_display }}</strong>
            </div>

            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Validity Period') }}</label>
              <strong class="text-primary font-monospace">{{ data.validity }}</strong>
            </div>

            <div class="col-md-4">
              <label class="small text-muted d-block">{{ $t('Created By') }}</label>
              <strong class="text-dark">{{ data.creator?.name || 'Admin' }}</strong>
            </div>

            <div class="col-12" v-if="data.description">
              <label class="small text-muted d-block">{{ $t('Description') }}</label>
              <p class="text-dark mb-0 bg-light p-3 rounded border">{{ data.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </view-page>
</template>

<script>
const model = "discount";

export default {
  data() {
    return {
      model: model,
      page_title: "Discount Details",
      data: {},
    };
  },
  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "view");
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>
