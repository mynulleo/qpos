<template>
  <create-form @onSubmit="submit">
    <div class="col-lg-3">
      <div class="row g-3">
        <File
          title="Image"
          field="data.original_image"
          mime="img"
          fileClassName="file2"
          accept=".jpg, .jpeg, .png"
          :showCrop="true"
          :vHeight="$root.media_validators?.agent?.min_height ?? $root.media_validators?.image?.min_height ?? 600"
          :vWidth="$root.media_validators?.agent?.min_width ?? $root.media_validators?.image?.min_width ?? 600"
          :vSizeInKb="$root.media_validators?.agent?.max_size ?? $root.media_validators?.image?.max_size ?? 5000"
          col="12"
        />
        <GlobalCrop
          field="data.original_image"
          v-on:update:modelValue="data.original_image = $event"
          :image="profile.original_image"
          :aspectRatio="{
            aspectRatio:
              ($root.media_validators?.agent?.min_width ?? 600) /
              ($root.media_validators?.agent?.min_height ?? 600),
          }"
          :minWidth="$root.media_validators?.agent?.min_width ?? 600"
          :minHeight="$root.media_validators?.agent?.min_height ?? 600"
        ></GlobalCrop>
      </div>
    </div>
    <div class="col-lg-9">
      <div class="row g-3">
        <Input v-model="data.full_name" field="data.full_name" col="6" title="Full Name" :req="true" />
        <Input v-model="data.mobile" field="data.mobile" col="6" title="Mobile" :req="false" />
        <Input v-model="data.organization" field="data.organization" col="6" title="Organization" :req="false" />
        <Input v-model="data.designation" field="data.designation" col="6" title="Designation" :req="false" />
        <Textarea v-model="data.address" field="data.address" :required="false" title="Address" col="12" />
        <Switch v-model="data.status" field="data.status" title="Status" col="3" on-label="Active" off-label="Deactive" :req="true" />
      </div>
    </div>
  </create-form>
</template>

<script>
const model = "agent";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      data: {
        full_name: "",
        mobile: "",
        organization: "",
        designation: "",
        original_image: "",
        address: "",
        status: "active",
      },
      profile: {},
    };
  },

  provide() {
    return {
      validate: this.validation,
      data: () => this.data,
      image: this.profile,
    };
  },

  methods: {
    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          this.$toast(
            "You need to fill " + error + " more empty mandatory fields",
            "warning"
          );
          return false;
        }

        if (res) {
          var form = document.getElementById("form");
          var formData = new FormData(form);
          formData.append("status", this.data.status || "active");
          formData.append("address", this.data.address || "");
          formData.append("image_base64", this.data.original_image ?? null);
          formData.append(
            "image_resize_value",
            this.$root.media_validators?.agent?.resize_value ?? ""
          );

          if (this.data.id) {
            this.update(this.model, formData, this.data.id, true);
          } else {
            this.store(this.model, formData);
          }
        }
      });
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + " Edit";
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + " Create";
    }
    this.getMediaValidators("Agent");
  },

  validators: {
    "data.full_name": function (value = null) {
      return Validator.value(value).required("Full Name is required");
    },
  },
};
</script>
