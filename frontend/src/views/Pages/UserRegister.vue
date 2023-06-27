<template>
  <div v-loading.fullscreen.lock="loading">
    <div class="header-section">
      <div class="toplogin-btn">
        <router-link class="login-text" to="/login">Login</router-link>
      </div>
      <div class="container">
        <div class="header-body text-center mb-6">
          <div class="row justify-content-center"></div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid form-box-wrapper">
      <div class="white-shadow-box my-4">
        <h5 class="mb-4">
          Thank you for your interest in {{ siteName }}’s online learning
          system. To get an estimate for a course, please provide your contact
          information below. To select the courses you are interested in, click
          on the drop down box under “Select Courses” scroll through the list of
          courses and click on the ones you want to take. Then click the green
          “Done” button and “Click here for estimate.” The total charge for the
          courses you selected will appear. If acceptable, click the “Continue”
          button to proceed with the sign up. If you don’t see a course you
          need, contact us at
          <a
            :href="'mailto:' + infoEmail"
            style="text-decoration: underline; color: blue"
            >{{ infoEmail }}</a
          >. Good luck and train wise!
        </h5>
        <div class="row">
          <div class="col-md-7 form-section">
            <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
              <form role="form" @submit.prevent="handleSubmit()">
                <div class="row" style="margin-top: 10px">
                  <div class="col-md-12">
                    <h4 style="color: #444c57" class="">Your Details</h4>
                    <hr />
                  </div>
                  <br />
                </div>

                <div class="form-row">
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >First Name <span class="req"> *</span></label
                    >
                    <base-input
                      type="text"
                      name="First name"
                      placeholder="First Name"
                      rules="required"
                      v-model="employee.first_name"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >Last Name <span class="req"> *</span></label
                    >
                    <base-input
                      type="text"
                      name="Last name"
                      placeholder="Last Name"
                      rules="required"
                      v-model="employee.last_name"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >Email <span class="req"> *</span></label
                    >
                    <base-input
                      type="email"
                      name="Email"
                      placeholder="Email"
                      rules="required"
                      v-model="employee.email"
                    >
                    </base-input>
                  </div>

                  <div class="col-md-3">
                     <label class="form-control-label">Phone no. <span class="req"> *</span></label>
                    <base-input
                      rules="required"
                      name="Telephone"
                      placeholder="Phone"
                      v-model="employee.phone"
                      @input="acceptNumber"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >Address <span class="req"> *</span></label
                    >
                    <base-input
                      type="text"
                      name="Address"
                      placeholder="Address"
                      v-model="employee.address"
                    ></base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >City <span class="req"> *</span></label
                    >
                    <base-input
                      type="text"
                      name="City"
                      placeholder="City"
                      v-model="employee.city"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >State <span class="req"> *</span></label
                    >
                    <base-input
                      type="text"
                      name="State"
                      placeholder="State"
                      v-model="employee.state"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-3">
                    <label class="form-control-label"
                      >Zip Code <span class="req"> *</span></label
                    >
                    <base-input
                      type="number"
                      name="Zip code"
                      placeholder="Zip Code"
                      v-model="employee.zipcode"
                    >
                    </base-input>
                  </div>
                </div>
                <hr />
                <div class="row" v-if="lead_id">
                  <h3 style="color: #444c57" class="mt-2 ml-2">
                    Login Information
                  </h3>
                </div>
                <hr v-if="lead_id" />
                <div class="form-row" v-if="lead_id">
                  <div class="col-md-6">
                    <label class="form-control-label"
                      >Username <span class="req"> *</span></label
                    >
                    <base-input
                      type="test"
                      name="Username"
                      placeholder="Username"
                      readonly
                      v-model="employee.email"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <label class="form-control-label"
                      >Password <span class="req"> *</span></label
                    >
                    <base-input
                      :type="passwordFieldType"
                      name="Password"
                      placeholder="Password"
                      v-model="employee.password"
                    >
                    </base-input>
                    <div class="password-eye passwordview">
                      <span @click.prevent="switchVisibility"
                        ><i class="fa fa-eye" title="Show Password"></i
                      ></span>
                    </div>
                  </div>
                </div>
              </form>
            </validation-observer>
          </div>
          <div class="col-md-5">
            <h2>
              Select Course(s) <span style="color: red">*</span>
              <base-button
                v-if="courseSelectionFocused"
                type="success"
                size="sm"
                class="right"
                plain
                @click="doneClicked"
                >Done</base-button
              >
            </h2>
            <div class="row">
              <div class="col-md-12">
                <el-select
                  class="select1"
                  v-model="checked_courses"
                  style="width: 100%"
                  multiple
                  filterable
                  placeholder="Select Course(s)"
                  @focus="showDone"
                  @visible-change="doneClicked"
                >
                  <el-option-group label="Compliance">
                    <span v-for="option in courses" :key="option.id">
                      <el-option
                        v-if="option.course_type == 1"
                        :label="option.course_name"
                        :value="option.id"
                      >
                      </el-option>
                    </span>
                  </el-option-group>
                  <el-option-group label="Others">
                    <span v-for="option in courses" :key="option.id">
                      <el-option
                        v-if="option.course_type == 0"
                        :label="option.course_name"
                        :value="option.id"
                      >
                      </el-option>
                    </span>
                  </el-option-group>
                </el-select>
              </div>
            </div>
            <div class="text-right" v-if="checked_courses.length && !lead_id">
              <base-button class="mt-2" @click.prevent="submitLead()"
                >Click here for estimate</base-button
              >
            </div>
            <div class="text-right" v-else>
              <base-button
                v-if="!showPricePlan"
                class="basebutton mt-2"
                @click.prevent="submitLead()"
                disabled
                >Click here for Estimate</base-button
              >
            </div>
            <div v-if="showPricePlan && lead_id" class="price-area">
              <hr />

              <div class="row">
                <div class="col-md-12" style="color: darkblue" v-if="!promoCodeApplied">
                  <div class="row payable-content">
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">Amount Payable:</label>
                    </div>
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">{{ formatPrice(sub_total) }}</label>
                    </div>
                  </div>
                  
                </div>
                <div class="col-md-12 mt-2" v-else>
                  <div class="row payable-content">
                    <div class="col-md-12 col-12 mb-4">
                     <span class="promocode-applied">Promotional Discount Applied ({{discount_percentage}}%)</span>
                    </div>
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">Course Cost:</label>
                    </div>
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">{{ formatPrice(course_cost) }}</label>
                    </div>
                  </div>
                   <div class="row payable-content">
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">Minus Discount:</label>
                    </div>
                    <div class="col-md-6 col-6">
                      <label class="form-control-label">-{{ formatPrice(discounted_cost) }}</label>
                    </div>
                  </div>
                  <div class="row payable-content">
                    <div class="col-md-6 col-6" >
                      <label class="form-control-label" style="text-decoration:underline; font-weight:bold;">Total Amount Due:</label>
                    </div>
                    <div class="col-md-6 col-6" >
                      <label class="form-control-label" style="text-decoration:underline; font-weight:bold;">{{ formatPrice(sub_total) }}</label>
                    </div>
                  </div>
                
                </div>
              </div>
              <div class="row mt-4" v-if="showPromoCodeOption">
               <div class="col-md-12" >
                  <label class="form-control-label">Promotional Code: </label>
               </div>
                <div class="col-md-6" >
                  <base-input
                   placeholder="Enter Promotional Code Here" rules="required" name="Promotional Code" v-model="promo_code"></base-input>
                </div>
                <div class="col-md-6">
                  <base-button size="md"  type="success" @click.prevent="applyPromoCode" >Apply Coupon</base-button>
                </div>
                 
               
              </div>
              <br />
              <div v-if="lead_id" class="mb-2 text-right">
                <base-button type="danger" @click.prevent="submitLead()"
                  >Re-estimate</base-button
                >
                <base-button class="custom-btn" @click.prevent="showAgreement()" :disabled="isContinueButtonDisabled">Continue</base-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal :show.sync="agreementModal" class="user-modal">
      <h3 slot="header">Service Activation Agreement</h3>
      <form>
        <div class="agreement-content">
          <agreement type="individual"></agreement>
        </div>
        <div class="text-center mt-2">
          <base-button type="success" @click.prevent="finalCreateAccount">
            I Agree
          </base-button>
          <base-button type="danger" @click.prevent="cancelAgreement">
            Cancel
          </base-button>
        </div>
      </form>
    </modal>
    <modal :show.sync="showPaymentOption">
      <h4 slot="header" style="color: #444c57" class="modal-title mb-0">
        Payment
      </h4>

      <credit-card
        v-if="showPaymentOption"
        type="individual"
        :monthlyAmount="sub_total"
        :yearlyAmount="perYearCost"
        :specialCourseFlag="0"
        :city="employee.city"
        :state="employee.state"
        :address="employee.address"
        :zip="employee.zipcode"
        :enablePaymentButton="enablePaymentButton"
        v-on:payClicked="payClicked"
      />
    </modal>
  </div>
</template>
<script>
import Vue from "vue";
import {Option, OptionGroup, Select, Table, TableColumn} from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import CreditCard from "@/views/Widgets/CreditCard";
import Agreement from "./Agreement.vue";
import {Dynamic} from "../../wl";
import VueGtag from "vue-gtag";

Vue.use(VueGtag, {
    config: {id: "AW-754017760"}
});
export default {
    name: "register",
    components: {
        Agreement,
        CreditCard,
        [Select.name]: Select,
        [Option.name]: Option,
        [OptionGroup.name]: OptionGroup,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn
    },
    data() {
        return {
            loading: false,
            baseUrl: this.$baseUrl,
            agreementModal: false,
            courseSelectionFocused: false,
            showPaymentOption: false,
            search: "",
            courses: [],
            checked_courses: [],
            formData: {
                employee_first_name: "",
                employee_last_name: "",
                user_type: "",
                employee_address: "",
                employee_city: "",
                employee_state: "",
                employee_zipcode: "",
                employee_email: "",
                employee_phone_num: "",
                password: "",
                address: ""
            },
            employee: {
                first_name: "",
                last_name: "",
                emial: "",
                phone: "",
                address: "",
                city: "",
                state: "",
                zipcode: "",
                username: "",
                password: ""
            },
            passwordFieldType: "password",
            showPricePlan: false,
            total_cost: "",
            total_discount: "",
            discountperlocation: "",
            perYearCost: "",
            discount_msg: "",
            lead_id: "",
            services: [],
            siteName: "",
            infoEmail: "",
            promo_code: "",
            appliedText: "",
            showPromoCodeOption: false,
            promoCodeApplied: false,
            course_cost: "",
            discounted_cost: "",
            ispromocode: 0,
            isContinueButtonDisabled: true,
            enablePaymentButton: false,
        };
    },
    mounted() {
        this.siteName = Dynamic.SITE_NAME;
        this.infoEmail = Dynamic.INFO_EMAIL;
        this.$gtag.event("Individual signup", {method: "Google"});
    },
    created() {
        if (this.$route.query.redirection === "yes") {
            this.employee.first_name = localStorage.getItem("fname");
            this.employee.last_name = localStorage.getItem("lname");
            this.employee.email = localStorage.getItem("email");
            this.checked_courses = JSON.parse(localStorage.getItem("courses"));
        } else {
            localStorage.removeItem("fname");
            localStorage.removeItem("lname");
            localStorage.removeItem("email");
            localStorage.removeItem("courses");
        }
        this.$http.get("user/discountRules").then(resp => {
            for (let course of resp.data.courses) {
                let obj = {
                    checked: false,
                    id: course.id,
                    course_name: course.name,
                    course_type: course.course_type,
                    cost: course.cost
                };
                this.courses.push(obj);
            }
            for (let service of resp.data.services) {
                let obj = {
                    id: service.id,
                    name: service.name,
                    price: service.price,
                    frequency: service.frequency
                };
                this.services.push(obj);
            }
        });
    },
    methods: {
        applyPromoCode() {

            let data = {
                amount: this.sub_total,
                promocode: this.promo_code,
            }
            this.$http
                .post("promocode/apply_promocode", data)
                .then(resp => {
                    this.sub_total = resp.data.final_amount;
                    this.course_cost = resp.data.previous_amount;
                    this.discounted_cost = resp.data.discounted_amount;
                    this.discount_percentage = resp.data.discount_percentage;
                    this.submitLead();

                }).catch(function (error) {
                if (error.response.status === 422) {
                    return Swal.fire({
                        title: "Error!",
                        html: error.response.data.message,
                        icon: "error"
                    });
                }
            });

        },
        finalCreateAccount() {
            this.agreementModal = false;
            this.showPaymentOption = true;
        },
        showDone() {
            this.courseSelectionFocused = true;
        },
        doneClicked() {
            this.courseSelectionFocused = false;
        },
        cancelAgreement() {
            this.agreementModal = false;
        },
        switchVisibility() {
            this.passwordFieldType =
                this.passwordFieldType === "password" ? "text" : "password";
        },
        formatPrice(value) {
            return (
                "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
            );
        },
        acceptNumber() {
            var x = this.employee.phone
                .replace(/\D/g, "")
                .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.employee.phone = !x[2]
                ? x[1]
                : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
        },
        showAgreement() {
            if (this.employee.password == "") {
                return Swal.fire({
                    title: "Error!",
                    text: "Please enter password to continue.",
                    icon: "error"
                });
            }
            this.formData = {
                employee_first_name: this.employee.first_name,
                employee_last_name: this.employee.last_name,
                user_type: "individual",
                employee_address: this.employee.address,
                employee_city: this.employee.city,
                employee_state: this.employee.state,
                employee_zipcode: this.employee.zipcode,
                employee_email: this.employee.email,
                employee_phone_num: this.employee.phone,
                password: this.employee.password,
                address: this.employee.address,
                courses: this.checked_courses,
                i_agree: true
            };
            this.agreementModal = true;
        },
        submitLead() {
            if (
                this.employee.first_name &&
                this.employee.last_name &&
                this.employee.address &&
                this.employee.city &&
                this.employee.state &&
                this.employee.zipcode &&
                this.employee.email
            ) {
                this.loading = true;
                let data = {
                    company_name: "individual",
                    first_name: this.employee.first_name,
                    last_name: this.employee.last_name,
                    number_of_locations: 1,
                    number_of_employees: 1,
                    phone_num: this.employee.phone,
                    email: this.employee.email,
                    user_type: "individual",
                    course_ids: this.checked_courses,
                    promo_code: this.promo_code,
                    course_cost: this.course_cost,
                    discounted_cost: this.sub_total

                };
                this.$http
                    .post("user/lead", data)
                    .then(resp => {
                        if (!this.promo_code) {
                            this.lead_id = resp.data.user_id;
                            this.companyEstimateDetailModel = false;
                            this.total_cost = resp.data.total;
                            this.sub_total = resp.data.sub_total;
                            this.total_discount = resp.data.discount_value;
                            if (resp.data.discount != null) {
                                this.discount_msg = resp.data.discount.title;
                            }

                            this.showPricePlan = true;
                            this.promo_code = "";
                            this.showPromoCodeOption = true;
                            this.appliedText = "";
                            this.promoCodeApplied = false;
                            this.perYearCost = resp.data.perYearCost;
                        } else {
                            this.promoCodeApplied = true;
                            this.showPromoCodeOption = false;
                            this.promo_code = "";
                        }
                        this.loading = false;
                        this.isContinueButtonDisabled = false;
                    })
                    .catch(function (error) {
                        this.loading = false;
                        if (error.response.status === 422) {
                            return Swal.fire({
                                title: "Error!",
                                html: error.response.data.message,
                                icon: "error"
                            });
                        }
                    });
            } else {
                this.loading = false;
                Swal.fire({
                    title: "Error!",
                    text: "Please fill all mandatory fields.",
                    icon: "error"
                });
            }
        },
        payClicked(cardData) {
            this.enablePaymentButton = true;
            this.loading = true;
            let payment = {
                payment_type: cardData.paymentType,
                cardholder_street_address:
                    cardData.address + "," + cardData.city + "," + cardData.state,
                cardholder_zip: cardData.zip,
                transaction_amount: "",
                token: cardData.token
            };
            if (payment.payment_type == "monthly") {
                payment.transaction_amount = this.sub_total.toFixed(2);
            }
            if (payment.payment_type == "yearly") {
                payment.transaction_amount = this.perYearCost.toFixed(2);
            }
            if (this.promoCodeApplied) {
                this.formData.promo_code = this.promo_code;
                this.formData.course_cost = this.course_cost;
                this.formData.discounted_cost = this.discounted_cost;
            }
            this.formData.payment = payment;
            this.formData.employee_address = cardData.address;
            this.formData.address = cardData.address;
            this.formData.employee_state = cardData.state;
            this.formData.employee_city = cardData.city;
            this.formData.employee_zipcode = cardData.zip;
            this.loading = false;
            this.createAccount(this.formData);
        },
        createAccount(formDataSubmitted) {
            this.loading = true;
            delete this.$http.defaults.headers["authorization"];
            this.$http
                .post("employees/register", formDataSubmitted)
                .then(resp => {
                    let ids = [];
                    let obj = {
                        id: resp.data.id
                    };
                    ids.push(obj);
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    Swal.fire({
                        title: "Success!",
                        html: `Your account has been created and is now active! <a href="https://lms.homeoftraining.com/#/login">Click here</a> to Login`,
                        icon: "success",
                        confirmButton: "btn btn-success",
                        confirmButtonText: "Ok"
                    }).then(result => {
                        if (result.value) {
                            this.$router.push("/login");
                        }
                    });
                })
                .catch((error) => {
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    Swal.fire({
                        title: "Error!",
                        text: error.response.data.message,
                        icon: "error"
                    });
                    this.loading = false;
                }).finally(() => (this.loading = false));
        }
    },
    watch: {
        lead_id: function () {
            if (this.lead_id == "") {
                this.isContinueButtonDisabled = true;
            }
        },
        checked_courses: function () {
            this.isContinueButtonDisabled = true;
        },
    },
};
</script>
<style scoped>
.form-section {
  background-color: transparent;
  padding: 40px;
  border-right: 1px solid #999999;
}
.promocode-applied{
  color:#5ec75e; 
  font-weight:bold; 
  font-size:14px;
}
.py-5 {
  padding-bottom: 0px !important;
}
.mt--6 {
  margin-top: -6rem !important;
}
.mt--12 {
  margin-top: -12rem !important;
}
hr {
  margin-top: 2px !important;
  margin-bottom: 20px !important;
}
#selected_course li {
  font-size: 0.89em;
}
#serviceAgreement {
  float: left;
  height: 300px;
  overflow: auto;
}
#serviceAgreement p {
  font-size: 0.81rem;
  text-align: justify;
}
.el-select-group__title {
  font-size: 14px !important;
  font-weight: bolder;
  color: #28c0e7;
}
.el-select-dropdown__item {
  font-size: 13px !important;
}
.reduceFont {
  font-weight: 400 !important;
}
.price-area .row {
  margin-top: 5px;
}
.bg-gradient-primary {
  background: linear-gradient(87deg, #07c9fb 0, #ffffff 100%) !important;
}
.req {
  color: red;
}
@media (min-width: 992px) {
  .pt-lg-9,
  .py-lg-9 {
    padding-top: 3rem !important;
  }
}
</style>
