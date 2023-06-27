<template>
  <div v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
     <div class="content">
    <div class="container-fluid mt--6">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-wrapper">
            <!-- Input groups -->
            <card v-if="editor === 'super-admin'">
              <!-- Card header -->
              <h3 slot="header" class="mb-0"></h3>
              <!-- Card body -->
              <validation-observer ref="formValidator">
                <form class="needs-validation">
                  <!-- Input groups with icon -->
                  <div class="row">
                    <div class="col-md-12">
                      <base-input
                        prepend-icon="fas fa-user"
                        readonly
                        v-model="account.interface"
                        placeholder="Interface"
                      ></base-input>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <base-input
                        prepend-icon="fas fa-envelope"
                        readonly
                        v-model="account.email"
                        placeholder="Email"
                      ></base-input>
                    </div>
                    <div class="col-md-12">
                      <base-input
                        append-icon="fas fa-eye"
                        name="Password"
                        v-model="account.password"
                        rules="required"
                        placeholder="Password"
                        type="password"
                      ></base-input>
                    </div>
                  </div>
                  <div class="text-right">
                    <base-button
                      class="custom-btn"
                      native-type="submit"
                      @click.prevent="updatePassword"
                      >Update</base-button
                    >
                  </div>
                </form>
              </validation-observer>
            </card>
            <card v-if="editor === 'company' || editor === 'manager'|| editor === 'sub-admin'">
              <template slot="header">
                <div class="row">
                  <div class="col-md-6">
                    <h2 class="mb-0">My Profile</h2>
                  </div>
                  <div class="col-md-6 text-right">
                    <h5>
                      <span class="requireField">*</span> Indicates a required
                      field.
                    </h5>
                  </div>
                </div>
              </template>
              <!-- Card header -->
              <validation-observer
                v-slot="{ handleSubmit }"
                ref="formValidator"
              >
                <form
                  class="needs-validation"
                  @submit.prevent="handleSubmit(updateAccoount)"
                >
                  <div class="form-row">
                    <div class="col-md-4">
                      <label class="form-control-label"
                        >First Name <span class="requireField">*</span></label
                      >
                      <base-input
                        name="First Name"
                        placeholder="First Name"
                        rules="required"
                        v-model="companydata.first_name"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <label class="form-control-label"
                        >Last Name <span class="requireField">*</span></label
                      >
                      <base-input
                        name="Last Name"
                        placeholder="Last Name"
                        rules="required"
                        v-model="companydata.last_name"
                      >
                      </base-input>
                    </div>
                    <div
                      class="col-md-4"
                      v-if="companydata.user_type != 'employee'"
                    >
                      <label class="form-control-label"
                        >Email <span class="requireField">*</span></label
                      >
                      <base-input
                        name="Email Address"
                        rules="required"
                        placeholder="Email Address"
                        v-model="companydata.email"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4" v-else>
                      <base-input
                        label="Email"
                        name="Email Address"
                        placeholder="Email Address"
                        v-model="companydata.email"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <base-input
                        label="Phone no"
                        name="Phone number"
                        placeholder="Phone number"
                        v-model="companydata.phone_num"
                        @input="acceptNumber"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <base-input
                        label="Address"
                        name="Address"
                        placeholder="Address"
                        v-model="companydata.address"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <base-input
                        label="City"
                        name="City"
                        placeholder="City"
                        v-model="companydata.city"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <base-input
                        label="State"
                        name="State"
                        placeholder="State"
                        v-model="companydata.state"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-4">
                      <base-input
                        label="Zip Code"
                        name="Zip Code"
                        placeholder="Zip code"
                        v-model="companydata.zipcode"
                      >
                      </base-input>
                    </div>

                    <div class="col-md-4">
                      <label class="form-control-label">Date of birth</label>
                      <el-date-picker
                        v-model="companydata.dob"
                        placeholder="Pick a day"
                        style="width: 100%"
                        format="MM/dd/yyyy"
                        :picker-options="pickerOptions1"
                      >
                      </el-date-picker>
                    </div>

                    <div class="col-md-4">
                      <label class="form-control-label"
                        >Username <span class="requireField">*</span></label
                      >
                      <base-input
                        name="Username"
                        rules="required"
                        readonly
                        v-model="companydata.user_name"
                      >
                      </base-input>
                    </div>

                    <div class="col-md-4">
                      <label class="form-control-label"
                        >Password <span class="requireField">*</span></label
                      >
                      <base-input
                        name="Password"
                        placeholder="Password"
                        :rules="{ required: true }"
                        v-model="companydata.password"
                      >
                      </base-input>
                    </div>
                  </div>
                  <div class=" text-right">
                    <base-button class="custom-btn" native-type="submit"
                      >Update</base-button
                    >
                  </div>
                </form>
              </validation-observer>
            </card>
          </div>
        </div>
      </div>
    </div>
     </div>
  </div>
</template>

<script>
import { Select, Option, DatePicker } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import FileInput from "@/components/Inputs/FileInput";

export default {
  name: "form-components",
  components: {
    [DatePicker.name]: DatePicker,
    [Select.name]: Select,
    [Option.name]: Option
  },
  data() {
    return {
      loading: false,
      account: {
        password: "",
        interface: "Super Admin",
        email: "admin@train321.com"
      },
      editor: "",
      updateCardInfoModal: false,
      validated: false,
      baseUrl: this.$baseUrl,
      complete: false,
      image: "",
      files: [],
      company_id: "",
      company_name: "",
      user_id: "",
      admin_id: "",
      companydata: {
        first_name: "",
        last_name: "",
        email: "",
        phone_num: "",
        password: "",
        user_name: "",
        address: "",
        city: "",
        state: "",
        social_security: "",
        dob: ""
      },
      pickerOptions1: {
        shortcuts: []
      }
    };
  },
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    if (localStorage.getItem("hot-user") === "employee") {
      this.editor = "employee";
    } else if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
      this.admin_id = localStorage.getItem("hot-user-id");
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
      this.user_id = localStorage.getItem("hot-user-id");
     } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "company";
      this.user_id = localStorage.getItem("hot-admin-id");
      this.company_id = localStorage.getItem("hot-user-id");
      this.companyName = localStorage.getItem("hot-company-name");
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
      this.user_id = localStorage.getItem("hot-user-id");
    }
    if (this.editor == "company" || this.editor == "manager" || this.editor == "sub-admin") {
      this.loading = true;
      this.$http
        .get("employees/get/" + this.user_id)
        .then(resp => {
          let data = resp.data[0];
          let obj = {
            user_name: data.user_name,
            first_name: data.first_name,
            last_name: data.last_name,
            email: data.email,
            phone_num: data.phone_num,
            password: data.access_code,
            address: data.address,
            city: data.city,
            state: data.state,
            dob: data.dob,
            zipcode: data.zipcode,
            social_security: data.social_security,
            user_type: data.type
          };
          this.companydata = obj;
        })
        .finally(() => (this.loading = false));
    }
  },
  methods: {
    acceptNumber() {
      var x = this.companydata.phone_num
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.companydata.phone_num = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    updatePassword() {
      if (this.account.password !== "") {
        this.$http
          .post("super_admin/update_password", {
            id: this.admin_id,
            password: this.account.password
          })
          .then(resp => {
            Swal.fire({
              title: "Password",
              text: "Changed Successfully",

              icon: "success",
              confirmButtonText: "Ok"
            });
          });
      } else {
        Swal.fire({
          text: "Password field is required!!",
          icon: "warning",
          confirmButtonText: "Ok"
        });
      }
    },

    updateAccoount() {
      if (
        this.companydata.first_name == "" ||
        this.companydata.last_name == "" ||
        this.companydata.password == "" ||
        this.companydata.user_name == ""
      ) {
        Swal.fire({
          title: "Error!",
          text: `Please fill all required feilds.`,
          icon: "error"
        });
      } else {
        let data = {
          employee_first_name: this.companydata.first_name,
          employee_last_name: this.companydata.last_name,
          employee_email: this.companydata.email,
          employee_phone_num: this.companydata.phone_num,
          employee_access_code: this.companydata.password,
          employee_address: this.companydata.address,
          employee_city: this.companydata.city,
          employee_state: this.companydata.state,
          employee_zipcode: this.companydata.zipcode,

          employee_dob: this.companydata.dob
        };
        this.$http.put("employees/update/" + this.user_id, data).then(resp => {
          Swal.fire({
            title: "Success!",
            text: `Employee has been Updated!`,
            icon: "success"
          });
        });
      }
    }
  }
};
</script>
<style>
.stripe-card {
  border: 1px solid grey;
}
.stripe-card.complete {
  border-color: green;
}
.logo-size {
  width: 60%;
  height: auto;
}
</style>
