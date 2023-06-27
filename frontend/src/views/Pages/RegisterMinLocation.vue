<template>
  <div>
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
          management platform and courses! To get an estimate for our training
          services, please provide your company and contact information below,
          check the box next to the courses you wish to sign up for and click
          the button to continue. If you don't see a course you need, contact us
          at <a :href="'mailto:' + infoEmail">{{ infoEmail }}</a
          >!
        </h5>
        <div class="row">
          <div class="col-md-7 form-section">
            <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
              <div class="row">
                    <div class="col-md-6">
                      <label class="form-control-label"
                        >First Name <span class="req"> *</span></label
                      >
                      <base-input
                        type="text"
                        name="First Name"
                        placeholder="First Name"
                        rules="required"
                        v-model="company.first_name"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-6">
                      <label class="form-control-label"
                        >Last Name <span class="req"> *</span></label
                      >
                      <base-input
                        type="text"
                        name="Last Name"
                        placeholder="Last Name"
                        rules="required"
                        v-model="company.last_name"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-6">
                      <label class="form-control-label"
                        >Email Address <span class="req"> *</span></label
                      >
                      <base-input
                        type="email"
                        name="Email Address"
                        placeholder="Email Address"
                        rules="required"
                        v-model="company.email"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-6">
                      <label class="form-control-label"
                        >Phone <span class="req"> *</span></label
                      >
                      <base-input
                        name="Phone Number"
                        placeholder="Phone"
                        rules="required"
                        v-model="company.telephone_no"
                        @input="acceptNumber"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-12">
                      <label class="form-control-label"
                        >Company Name <span class="req"> *</span></label
                      >
                      <base-input
                        type="text"
                        name="Company name"
                        placeholder="Company Name"
                        rules="required"
                        v-model="company.name"
                      >
                      </base-input>
                    </div>
                  </div>
                  <div class="row" v-if="showCompanyinformation">
                    <div class="col-md-3">
                      <label class="form-control-label"
                        >Address <span class="req"> *</span></label
                      >
                      <base-input
                        type="text"
                        name="Address"
                        placeholder="Address"
                        rules="required"
                        v-model="company.address_1"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-3">
                      <label class="form-control-label"
                        >City <span class="req"> *</span></label
                      >
                      <base-input
                        type="text"
                        name="City"
                        placeholder="city"
                        rules="required"
                        v-model="company.city"
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
                        rules="required"
                        v-model="company.state"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-3">
                      <label class="form-control-label"
                        >Zip code <span class="req"> *</span></label
                      >
                      <base-input
                        type="number"
                        name="Zip code"
                        placeholder="Zip"
                        rules="required"
                        v-model="company.zip"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-5">
                      <label class="form-control-label"
                        >Password <span class="req"> *</span></label
                      >
                      <base-input
                        :type="passwordFieldType"
                        v-if="!(company_id !== '')"
                        name="Password"
                        placeholder="Password"
                        rules="required"
                        v-model="company.password"
                      >
                      </base-input>
                      <base-input
                        :type="passwordFieldType"
                        v-if="company_id !== ''"
                        name="Password"
                        placeholder="Password"
                        v-model="company.password"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-1 password-eye" style="margin-top: 40px">
                      <span @click.prevent="switchVisibility"
                        ><i class="fa fa-eye" title="Show Password"></i
                      ></span>
                    </div>
                 
                <div class="col-md-12" style="font-style: italic">
                  <h5 class="reduceFont">
                    <span class="text-danger">*</span>Indicates a required
                    field. The estimated pricing is based upon the number of
                    locations, users and courses selected for your company. All
                    prices are based on a 1-year agreement that will
                    automatically renew each year.
                  </h5>
                </div>
              </div>
              <div v-if="showCompanyinformation">
                <form role="form" @submit.prevent="handleSubmit(showAgreement)">
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-4">
                      Continue
                    </button>
                  </div>
                </form>
              </div>
            </validation-observer>
          </div>
          <div class="col-md-5">
            <h5>
              The package below only includes the required Food Safety and
              Alcohol Awareness courses:
            </h5>
            <div class="row">
              <div class="col-md-12">
                <base-input label="Number of Locations:">
                  <el-select
                    class="mr-3"
                    style="width: 100%"
                    placeholder="Select Number of Locations"
                    rules="required"
                    v-model="company.no_of_locations"
                  >
                    <el-option
                      v-for="(option, index) in company_locations"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'company_location_' + index"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
              <div class="col-md-12">
                <h5>
                  4+ locations,
                  <router-link to="/register" class="linkColor underline-class"
                    >click here</router-link
                  >
                </h5>
              </div>
              <div class="col-md-12">
                <base-input
                  label="Estimated number of team members at each location:"
                >
                  <el-select
                    class="mr-3"
                    style="width: 100%"
                    placeholder="Select Number of Employees"
                    rules="required"
                    v-model="company.no_of_employees"
                  >
                    <el-option
                      v-for="(option, index) in company_members"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'company_member_' + index"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
               <div class="col-md-12">
                <input type="checkbox" v-model="is_sexualHarassment" />
                <span class="courseName"
                  > Add Sexual Harassment for $5 per month.</span
                >
                <br />
              </div>
              <div class="col-md-12">
                <input type="checkbox" v-model="is_foodManager" />
                <span class="courseName"
                  > Add Food Manager for $9.75 per manager, per month.</span
                >
                <br />
              </div>
             

              <div class="col-md-12 mt-4">
                <h5> Add any other courses below for $2 per month, per location</h5>
                <div class="coursesSection">
                  <span v-for="option in courses" :key="option.id">
                  
                    <input
                      type="checkbox"
                      :value="option.id"
                      v-model="checked_courses"
                    />
                    <span class="courseName"> {{ option.course_name }}</span>
                    
                    <br />
                  </span>
                </div>
              </div>
              <div class="col-md-12 mt-4">
                <input type="checkbox" v-model="sms_notification" />
                <span class="courseName"
                  > Add text message notification for $3 per month.</span
                >
                <br />
              </div>
            </div>
            <div
              class="text-right"
              v-if="
                !showCompanyinformation &&
                company.no_of_locations &&
                company.no_of_employees &&
                !lead_id
              "
            >
              <base-button class="mt-2" @click.prevent="submitLead"
                >Click here for estimate</base-button
              >
            </div>
            <div class="text-right" v-else>
              <base-button
                v-if="!showPricePlan"
                class="basebutton mt-2"
                @click.prevent="submitLead"
                disabled
                >Click here for Estimate</base-button
              >
            </div>

            <div v-if="showPricePlan && lead_id" class="price-area">
              <hr />

              <div class="row">
                <div class="col-md-12" style="color: darkblue">
                  <div class="row">
                    <div class="col-md-9 col-8">
                      <small style="text-decoration: underline"
                        ><b>Total Cost Per Month:</b>
                      </small>
                    </div>
                    <div class="col-md-3 col-4">
                      <small style="text-decoration: underline"
                        ><b>{{ formatPrice(perMonthCost) }}</b></small
                      >
                    </div>
                  </div>
                </div>

                <div class="col-md-12" style="color: darkblue">
                  <div class="row">
                    <div class="col-md-9 col-8">
                      <small
                        >Price if paid in full for the year
                        <span>(10% Off)</span>:
                      </small>
                      <hr
                        style="
                          margin-top: -9px !important;
                          margin-bottom: 4px !important;
                          margin-left: 250px;
                        "
                      />
                    </div>
                    <div class="col-md-3 col-4">
                      <small>{{ formatPrice(perYearCost) }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <br />
            </div>

            <div>
              <base-button
                v-if="showCompanyinformation || lead_id"
                type="danger"
                @click.prevent="submitLead"
                >Re-estimate</base-button
              >
              <base-button
                v-if="!showCompanyinformation && lead_id"
                type="success"
                @click.prevent="companyDetails()"
                >Continue Signup</base-button
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal :show.sync="foodManagerCount" v-on:close="updateFoodManagerCount">
      <h3 slot="header">FOOD MANAGER</h3>
      <div class="row">
        <div class="col-md-12">
          <base-input
            label="Enter number of managers for each location:"
            placeholder="0"
            type="number"
            v-model="no_of_managers"
          ></base-input>
        </div>
        <div class="col-md-12">
          <base-button class="btn btn-primary" @click="updateFoodManagerCount()"
            >Done</base-button
          >
        </div>
      </div>
    </modal>
    <modal :show.sync="agreementModal" class="user-modal">
      <h3 slot="header">Service Activation Agreement</h3>
      <form>
        <div class="agreement-content">
          <agreement type="company"></agreement>
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
      <h4
        slot="header"
        style="color: #444c57"
        class="title title-up text-center"
      >
        Payment
      </h4>

      <credit-card
        v-if="showPaymentOption"
        type="company"
        :monthlyAmount="perMonthCost"
        :yearlyAmount="perYearCost"
        :specialCourseFlag="0"
        :city="company.city"
        :state="company.state"
        :address="company.address_1"
        :zip="company.zip"
        v-on:payClicked="payClicked"
      />
    </modal>
  </div>
</template>
<script>
import FileInput from "@/components/Inputs/FileInput";
import { Table, TableColumn, Select, Option, OptionGroup } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import CreditCard from "@/views/Widgets/CreditCard";
import Agreement from "./Agreement.vue";
import { Dynamic } from "../../wl";
import BaseInput from "../../components/Inputs/BaseInput.vue";
import BaseCheckbox from "../../components/Inputs/BaseCheckbox.vue";

export default {
  name: "register",
  components: {
    Agreement,
    FileInput,
    CreditCard,
    BaseInput,
    BaseCheckbox,
    [Select.name]: Select,
    [Option.name]: Option,
    [OptionGroup.name]: OptionGroup,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      foodManagerCount: false,
      baseUrl: this.$baseUrl,
      company_id: "",
      creatAccountClicked: false,
      company_locations: [
        {
          label: "1",
          value: 1,
        },
        {
          label: "2",
          value: 2,
        },
        {
          label: "3",
          value: 3,
        },
      ],
      company_members: [
        {
          label: "1-10 ($9 per month)",
          value: 10,
        },
        {
          label: "11-30 ($19 per month)",
          value: 30,
        },
        {
          label: "31-50 ($29 per month)",
          value: 50,
        },
        {
          label: "51-100 ($39 per month)",
          value: 100,
        },
        {
          label: "101-250 ($59 per month)",
          value: 250,
        },
        {
          label: "250+ ($99 per month)",
          value: 251,
        },
      ],
      company: {
        first_name: "",
        last_name: "",
        name: "",
        no_of_locations: "",
        no_of_employees: "",
        address_1: "",
        address_2: "",
        city: "",
        state: "",
        zip: "",
        telephone_no: "",
        email: "",
        password: "",
      },
      agreementModal: false,
      passwordFieldType: "password",
      courses: [],
      siteName: "",
      infoEmail: "",
      lead_id: "",
      checked_courses: [],
      companyEstimateDetailModel: false,
      showCompanyinformation: false,
      showPricePlan: false,
      perYearCost: "",
      perMonthCost: "",
      course: "",
      showPaymentOption: false,
      is_foodManager: false,
      is_sexualHarassment: false,
      no_of_managers: "",
      sms_notification: false,
      hideCourses:[122,70,123,115,101,102,192,103,364,365,366,313,105,106,291]
    };
  },
  mounted() {
    this.siteName = Dynamic.SITE_NAME;
    this.infoEmail = Dynamic.INFO_EMAIL;
  },
  watch: {
    is_foodManager() {
      if (this.is_foodManager == true) {
        this.openFoodMangerCountModel();
      }
    },
  },
  created() {
    //get courses
    this.$http.get("user/discountRules").then((resp) => {
      for (let course of resp.data.courses) {
        let obj = {
          checked: false,
          id: course.id,
          course_name: course.name,
          course_type: course.course_type,
          cost: course.cost,
          discounted: course.is_discounted_course,
        };
        if(!this.hideCourses.includes(obj.id)){
        this.courses.push(obj);
        }
      } 
    });
  }, 
  methods: {
    companyDetails() {
      this.showCompanyinformation = true;
    },
    openFoodMangerCountModel() {
      this.foodManagerCount = true;
    },
    updateFoodManagerCount() {
      if(this.no_of_managers < 1){
        this.is_foodManager=false;
      }
      this.foodManagerCount = false;
    },
    cancelAgreement() {
      this.agreementModal = false;
    },
    finalCreateAccount() {
      this.agreementModal = false;
      this.showPaymentOption = true;
    },
    payClicked(cardData) {
      let payment = {
        payment_type: "",
        cardholder_street_address:
          cardData.address + "," + cardData.city + "," + cardData.state,
        cardholder_zip: cardData.zip,
        transaction_amount: "",
        card_number: cardData.cardNumber,
        card_exp_date: cardData.expire,
      };

      payment.payment_type = cardData.paymentType;

      if (cardData.paymentType == "monthly") {
        payment.transaction_amount = this.perMonthCost.toFixed(2);
      }
      if (cardData.paymentType == "yearly") {
        payment.transaction_amount = this.perYearCost.toFixed(2);
      }
      this.formData.payment = payment;
      this.formData.address_1 = cardData.address;
      this.formData.company_address_1 = cardData.address;
      this.formData.company_state = cardData.state;
      this.formData.company_city = cardData.city;
      this.formData.company_zip = cardData.zip;
      this.createAccount(this.formData);
    },

    showAgreement() {
      this.formData = {
        company_name: this.company.name,
        first_name: this.company.first_name,
        last_name: this.company.last_name,
        company_location_num: this.company.no_of_locations,
        company_employee_num:
          this.company.no_of_employees == "10"
            ? "1-10"
            : this.company.no_of_employees == "30"
            ? "11-30"
            : this.company.no_of_employees == "50"
            ? "31-50"
            : this.company.no_of_employees == "100"
            ? "51-100"
            : this.company.no_of_employees == "250"
            ? "101-250"
            : "250+",
        company_address_1: this.company.address_1,
        company_address_2: this.company.address_2,
        company_phone: this.company.telephone_no,
        company_email: this.company.email,
        company_zip: this.company.zip,
        username: this.company.email,
        company_city: this.company.city,
        company_state: this.company.state,
        company_password: this.company.password,
        course_ids: this.checked_courses,
        user_type: "1-3 location Signup",
        is_foodManager: this.is_foodManager,
        no_of_managers: this.no_of_managers,
        is_sexualHarassment: this.is_sexualHarassment,
        sms_notification: this.sms_notification,
        status: 1,
        payment: [],
        i_agree: true,
      };
      this.agreementModal = true;
    },

    formatPrice(value) {
      return (
        "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
      );
    },
    // Lead generation
    submitLead() {
      let data = {
        company_name: this.company.name,
        first_name: this.company.first_name,
        last_name: this.company.last_name,
        number_of_locations: this.company.no_of_locations,
        number_of_employees: this.company.no_of_employees,
        email: this.company.email,
        user_type: "1-3 location Signup",
        course_ids: this.checked_courses,
        is_foodManager: this.is_foodManager,
        no_of_managers: this.no_of_managers,
        is_sexualHarassment: this.is_sexualHarassment,
        sms_notification: this.sms_notification,
      };
      this.$http
        .post("user/minLocationLead", data)
        .then((resp) => {
          this.lead_id = resp.data.user_id;
          this.companyEstimateDetailModel = false;
          this.perMonthCost = resp.data.perMonthCost;
          this.perYearCost = resp.data.perYearCost;
          this.showPricePlan = true;
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            return Swal.fire({
              title: "Error!",
              html: error.response.data.message,
              icon: "error",
            });
          }
        });
    },
    switchVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    },
    acceptNumber() {
      var x = this.company.telephone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.company.telephone_no = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    //account creation
    createAccount(formDataSubmitted) {
      delete this.$http.defaults.headers["authorization"];
      this.creatAccountClicked = true;
      this.$http
        .post("company/registerMinLocation", formDataSubmitted)
        .then((resp) => {
          let ids = [];
          let obj = {
            id: resp.data.id,
          };
          ids.push(obj);
          this.$http
            .post("company/welcome_email", {
              form_data: formDataSubmitted,
              password: this.company.password,
              ids: ids,
            })
            .then((resp) => {
              this.$router.push("/login");
              this.agreementModal = false;
              Swal.fire({
                title: "Success!",
                text: `Account created successfully.`,
                icon: "success",
              });
            })
            .catch(function (error) {
              this.$router.push("/login");
              return Swal.fire({
                title: "Success!",
                text: "Account created successfully.",
                icon: "success",
              });
            });
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            return Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          }
        });
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
.basebutton.disabled:hover {
  cursor: not-allowed;
}
@media (min-width: 992px) {
  .pt-lg-9,
  .py-lg-9 {
    padding-top: 3rem !important;
  }
  .password-eye span {
    border: 1px solid #808080b3;
    padding: 8px;
    border-radius: 5px;
    background: #80808029;
  }
}
@media (min-width: 768px) {
  .col-md-4 {
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.33333%;
    flex: 0 0 33.33333%;
    max-width: 25.33333%;
  }
}

/* ============17/11/2020============ */

#serviceAgreement {
  float: left;
  height: 300px;
  overflow: auto;
}
#serviceAgreement p {
  font-size: 0.81rem;
  text-align: justify;
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
.coursesSection {
  overflow-y: auto !important;
  height: 250px !important;
}
.courseName {
  font-size: 13px;
}
</style>
