<script>
import {
  DatePicker,
  TimeSelect,
  Table,
  TableColumn,
  Select,
  Option,
} from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    [DatePicker.name]: DatePicker,
    [TimeSelect.name]: TimeSelect,
  },
  data() {
    return {
      loading: false,
      promo_code_id: "",
      promocode: {
        promo_code: "",
        description: "",
        percentage: "",
        valid_upto: "",
        status: true,
      },
    };
  },
  created() {
    if (this.$route.query.id) {
      this.promo_code_id = this.$route.query.id;

      this.$http
        .get("promocode/edit_promo_code/" + this.promo_code_id)
        .then((resp) => {
          let data = resp.data;
          this.promocode.promo_code = data.name;
          this.promocode.description = data.description;
          this.promocode.percentage = data.percentage;
          this.promocode.valid_upto = data.validity;
          this.promocode.status = data.status ? true : false;
        });
    }
  },
  methods: {
    savePromoCode() {
      if (this.promocode.valid_upto != "") {
        if (this.promo_code_id !== "") {
          this.loading = true;
          this.$http
            .put("promocode/update_promo_code/" + this.promo_code_id, {
              promo_code: this.promocode.promo_code,
              description: this.promocode.description,
              percentage: this.promocode.percentage,
              valid_upto: this.promocode.valid_upto,
              status: this.promocode.status ? 1 : 0,
            })
            .then((resp) => {
              Swal.fire({
                title: "Success!",
                text: `Promo Code updated successfully.`,
                icon: "success",
              });
              this.$router.push("/promo_codes");
            })
            .finally(() => (this.loading = false));
        } else {
          this.loading = true;
          this.$http
            .post("promocode/save_promo_code", {
              promo_code: this.promocode.promo_code,
              description: this.promocode.description,
              percentage: this.promocode.percentage,
              valid_upto: this.promocode.valid_upto,
              status: this.promocode.status ? 1 : 0,
            })
            .then((resp) => {
              Swal.fire({
                title: "Success!",
                text: `Promo Code saved successfully.`,
                icon: "success",
              });
              this.$router.push("/promo_codes");
            })
            .finally(() => (this.loading = false));
        }
      } else {
        Swal.fire({
          title: "Error!",
          text: `Please fill all required feilds.`,
          icon: "error",
        });
      }
    },
  },
};
</script>
<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <card>
        <!-- Card header -->
        <h2 slot="header" class="mb-0" v-if="promo_code_id">Edit Promo Code</h2>
        <h2 slot="header" class="mb-0" v-else>Add Promo Code</h2>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit(savePromoCode)"
            enctype="multipart/form-data"
          >
            <div class="form-row">
              <div class="col-md-6">
                <label class="form-control-label"
                  >Promo Code <span class="requireField">*</span></label
                >
                <base-input
                  name="Promo code"
                  placeholder="Promo Code"
                  rules="required"
                  v-model="promocode.promo_code"
                >
                </base-input>
              </div>

              <div class="col-md-6">
                <label class="form-control-label">Description</label>
                <textarea
                  placeholder="Promo Code Description"
                  class="form-control"
                  v-model="promocode.description"
                ></textarea>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-control-label"
                  >Percentage (%) <span class="requireField">*</span></label
                >
                <base-input
                  type="number"
                  name="Percentage"
                  placeholder="Percentage"
                  rules="required"
                  max="100"
                  min="0"
                  v-model="promocode.percentage"
                >
                </base-input>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-control-label"
                  >Valid Until <span class="requireField">*</span></label
                >
                <el-date-picker
                  v-model="promocode.valid_upto"
                  placeholder="Pick a date"
                  style="width: 100%"
                  format="MM/dd/yyyy"
                >
                </el-date-picker>
              </div>

              <div class="col-md-6">
                <label class="form-control-label">Status</label>
                <div class="d-flex">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="promocode.status"
                  ></base-switch>
                </div>
              </div>
            </div>
            <div class="text-right mt-2">
              <base-button
                class="custom-btn"
                type="danger"
                @click="$router.go(-1)"
                >Cancel</base-button
              >
              <base-button class="custom-btn" native-type="submit">{{
                promo_code_id !== "" ? " Update" : "Submit"
              }}</base-button>
            </div>
          </form>
        </validation-observer>
      </card>
    </div>
  </div>
</template>

<style></style>
