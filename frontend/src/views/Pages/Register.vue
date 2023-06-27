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
              <div v-if="showCompanyinformation">
                <form role="form" @submit.prevent="handleSubmit(showAgreement)">
                  <div class="form-row">
                    <div class="col-md-6">
                      <label class="form-control-label">Company Type *</label
                      ><br />
                      <el-select
                        class=" mr-3"
                        style="width: 100%"
                        placeholder="Select Company Type"
                        rules="required"
                        v-model="company.company_type"
                      >
                        <el-option
                          v-for="(option, index) in company_types"
                          class="select-primary"
                          :value="option.value"
                          :label="option.label"
                          :key="'company_type_' + index"
                        >
                        </el-option>
                      </el-select>
                    </div>
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                      <base-input
                        type="text"
                        label="Website"
                        name="Website"
                        placeholder="Website"
                        v-model="company.website"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-6">
                      <label class="form-control-label">Company Logo</label>
                      <form>
                        <file-input v-on:change="onImageChange"></file-input>
                      </form>
                    </div>
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
                  </div>
                  <div class="row">
                    <h3 style="color: #444C57" class=" mt-4 ml-2">
                      Administrator Login Information
                    </h3>
                  </div>
                  <hr />
                  <div class="form-row">
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    <div
                      class="col-md-1 password-eye"
                      style="margin-top: 40px;"
                    >
                      <span @click.prevent="switchVisibility"
                        ><i class="fa fa-eye" title="Show Password"></i
                      ></span>
                    </div>
                  </div>
                  <div class="form-row">
                    <h3>AUTHORIZATION</h3>
                    <p>
                      <small>
                        By clicking the "Create Account" button below, you agree
                        that the name you typed in the box above (Your Full
                        Name) will be the electronic representation of your
                        signature for all purposes in relation to the
                        {{ siteName }}, LLC legally binding Service Activation
                        Agreement. You agree that your electronic signature is
                        considered an original, for purposes of entering into a
                        contract.
                      </small>
                    </p>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-4" :disabled="isContinueButtonDisabled">
                      Continue
                    </button>
                  </div>
                </form>
              </div>
              <div class="row" v-else>
                <div class="col-md-12">
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
                    <div class="col-md-6">
                      <label class="form-control-label"
                        ># of Locations <span class="req"> *</span></label
                      >
                      <base-input
                        type="number"
                        min="1"
                        name="Location"
                        rules="required"
                        v-model="company.no_of_locations"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-6">
                      <label class="form-control-label"
                        >Estimated # of Users <span class="req"> *</span></label
                      >
                      <base-input
                        type="number"
                        min="1"
                        name="Users "
                        rules="required"
                        v-model="company.no_of_employees"
                      >
                      </base-input>
                    </div>
                  </div>
                </div>
                <div class="col-md-12" style="font-style: italic;">
                  <h5 class="reduceFont">
                    <span class="text-danger">*</span>Indicates a required
                    field. The estimated pricing is based upon the number of
                    locations, users and courses selected for your company. All
                    prices are based on a 1-year agreement that will
                    automatically renew each year.
                  </h5>
                </div>
              </div>
            </validation-observer>
          </div>
          <div class="col-md-5 ">
            <h2>
              Select Course(s)
              <span style="color:red; ">*</span>
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
                  ref="dropdown"
                  v-model="checked_courses"
                  style="width: 100%;"
                  multiple
                  filterable
                  placeholder="Select Course(s)"
                  @focus="showDone"
                  @visible-change="doneClicked"
                  @change="dropdownselectionChange"
                >
                  <el-option-group label="State Compliant Courses:">
                    <span v-for="option in course" :key="option.id">
                      <el-option
                        v-if="option.course_type == 1"
                        :label="option.course_name"
                        :value="option.id"
                      >
                      </el-option>
                    </span>
                  </el-option-group>
                  <el-option-group label="Additional Courses:">
                    <span v-for="option in course" :key="option.id">
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
              <br />
            </div>

            <div
              class="text-right"
              v-if="
                !showCompanyinformation &&
                  (checked_courses.length ||
                    spacialCourseFlag.includes(true)) &&
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

              <div class="row" v-if="!specialCourseFlag">
                <div class="col-md-12" style="color:darkblue;">
                  <div class="row" v-if="total_discount > 0">
                    <div class="col-md-9 col-8">
                      <small>Discount </small>
                      <small
                        style="color:brown;font-size:70%"
                        v-if="total_discount > 0"
                        >Congratulations! You have earned a discount ({{
                          discount_msg
                        }}):</small
                      >
                    </div>
                    <div class="col-md-3 col-4">
                      <small>{{ formatPrice(total_discount) }}</small>
                    </div>
                  </div>
                  <div class="row" v-if="promoCodeApplied">

                    <div class="col-md-12 mb-4">
                      <span class="promocode-applied "> <span class="promocode-applied">Promotional Discount Applied ({{discount_percentage}}%)</span></span>
                    </div>


                    <div class="col-md-9 col-8">
                      <small style="text-decoration: underline;"
                        ><b>Original Cost Per Month:</b>
                      </small>
                    </div>
                    <div class="col-md-3 col-4">
                      <small style="text-decoration: underline;"
                        >{{ formatPrice(course_cost_monthly) }}</small
                      >
                    </div>

                  </div>


                  <div class="row">
                    <el-popover
                      ref="fromPopOver"
                      placement="top-start"
                      width="300"
                      trigger="hover"
                    >
                      <div class="row">
                        <div class="col-md-12">
                          <small
                            >Monthly pricing is based on a 1-year agreement that
                            automatically renews each year.</small
                          >
                        </div>
                      </div>
                    </el-popover>
                    <div class="col-md-9 col-8">
                      <small style="text-decoration: underline;"
                        ><b>Total Cost Per Month <span v-if="promoCodeApplied">(including discount)</span>:</b>
                        <i
                          v-popover:fromPopOver
                          class="el-icon-question text-blue"
                      /></small>
                    </div>
                    <div class="col-md-3 col-4">
                      <small style="text-decoration: underline;"
                        ><b>{{ formatPrice(total_cost) }}</b></small
                      >
                    </div>

                  </div>

                </div>

                <div class="col-md-12" style="color:darkblue;">

                  <div class="row">
                    <div class="col-md-9 col-8">
                      <small
                        >Price if paid in full for the year
                        <span v-if="!specialCourseFlag">(10% Off)</span>:
                      </small>
                      <hr
                        style="margin-top:-9px !important; margin-bottom: 4px !important; margin-left: 250px;"
                      />
                    </div>
                    <div class="col-md-3 col-4">
                      <small>{{ formatPrice(perYearCost) }}</small>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-9 col-8">
                      <small><i>Monthly Cost per location:</i></small>
                      <hr
                        style="margin-top:-9px !important; margin-bottom: 4px !important;margin-left: 165px;"
                      />
                    </div>
                    <div class="col-md-3 col-4">
                      <small
                        ><i>{{ formatPrice(discountperlocation) }}</i></small
                      >
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-9 col-8">
                      <small><i>Monthly Cost per user:</i> </small>
                      <hr
                        style="margin-top:-9px !important; margin-bottom: 4px !important;margin-left: 145px;"
                      />
                    </div>
                    <div class="col-md-3 col-4">
                      <small
                        ><i>{{ formatPrice(discountperemp) }}</i></small
                      >
                    </div>
                  </div>
                </div>
              </div>

              <div class="row" v-else>
                <div class="col-md-12" style="color:darkblue;">
                  <div class="row payable-content" v-if="!promoCodeApplied">
                    <div class="col-md-6 col-6">
                      <small>Amount Payable:</small>
                    </div>
                    <div class="col-md-6 col-6">
                      <small>{{ formatPrice(perYearCost) }}</small>
                    </div>
                  </div>
                    <div class="row payable-content" v-else>
                    <div class="col-md-12 mb-4">
                      <span class="promocode-applied "> <span class="promocode-applied">Promotional Discount Applied ({{discount_percentage}}%)</span></span>
                    </div>
                    <div class="col-md-6 col-6">
                      <small>Original Cost:</small>
                    </div>
                    <div class="col-md-6 col-6">
                      <small>{{ formatPrice(course_cost_yearly) }}</small>
                    </div>
                     <div class="col-md-6 col-6">
                      <small> Total Cost (including discount):</small>
                    </div>
                    <div class="col-md-6 col-6">
                      <small>{{ formatPrice(perYearCost) }}</small>
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
                   rules="required" name="Promotional Code" v-model="promo_code"></base-input>
                </div>
                <div class="col-md-6">
                  <base-button size="md"  type="success" @click.prevent="applyPromoCode" >Apply Coupon</base-button>
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
                :disabled="isContinueButtonDisabled"
                >Continue Signup</base-button
              >
            </div>
          </div>
        </div>
      </div>
    </div>

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
        style="color:#444C57"
        class="title title-up text-center"
      >
        Payment
      </h4>

      <credit-card
        v-if="showPaymentOption"
        type="company"
        :monthlyAmount="total_cost"
        :yearlyAmount="perYearCost"
        :specialCourseFlag="specialCourseFlag"
        :city="company.city"
        :state="company.state"
        :address="company.address_1"
        :zip="company.zip"
        :enablePaymentButton="enablePaymentButton"
        v-on:payClicked="payClicked"
      />
    </modal>
    <modal :show.sync="showusermodel">
      <h4
        slot="header"
        style="color:#444C57"
        class="title title-up text-center"
      >
        {{ specialCourseName }}
      </h4>

      <div class="col-md-8">
        <base-input
          label="How many users will be taking this course:"
          type="number"
          name="# Users"
          min="0"
          value="0"
          :key="specialCourseName"
          v-model="users"
        ></base-input>
      </div>
      <div class="col-md-6">
        <base-button
          size="md"
          class="custom-btn right"
          @click.prevent="updateUsers()"
          >Done</base-button
        >
      </div>
    </modal>
  </div>
</template>

<script>
import Vue from "vue";
import FileInput from "@/components/Inputs/FileInput";
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
        FileInput,
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
            users: 0,
            courseSelectionFocused: false,
            focused: "blue-theme",
            formData: {
                company_name: "",
                first_name: "",
                last_name: "",
                company_location_num: "",
                company_employee_num: "",
                company_address_1: "",
                company_address_2: "",
                company_phone: "",
                company_email: "",
                company_zip: "",
                website: "",
                company_type: "",
                username: "",
                parent_id: "",
                image: "",
                company_city: "",
                company_state: "",
                company_password: ""
            },
            value_true: true,
            baseUrl: this.$baseUrl,
            complete: false,
            hot_user: "",
            hot_token: "",
            config: "",
            company_id: "",
            creatAccountClicked: false,
            company_types: [],
            parent_companies: [],
            payment_screen: false,
            image: "",
            sub_total: "",
            special_courses: [],
            company: {
                first_name: "",
                last_name: "",
                company_type: "",
                parent_company: "",
                name: "",
                administrator: "",
                no_of_locations: "",
                no_of_employees: "",
                address_1: "",
                address_2: "",
                city: "",
                state: "",
                zip: "",
                logo: "",
                telephone_no: "",
                email: "",
                password: ""
            },

            search: "",
            estimate: false,
            showPass: false,
            contactUsModal: false,
            agreementModal: false,
            passwordFieldType: "password",
            courses: [],
            services: [],
            contact: {
                name: "",
                phone: "",
                email: "",
                message: ""
            },
            siteName: "",
            infoEmail: "",
            lead_id: "",
            checked_courses: [],
            companyEstimateDetailModel: false,
            showCompanyinformation: false,
            showPricePlan: false,
            total_cost: "",
            total_discount: "",
            specialCourseFlag: "",
            special_courses_with_users: "",
            discountperlocation: "",
            perYearCost: "",
            discount_msg: "",
            employees_count: "",
            locations_count: "",
            discountperemp: "",
            discount_per: "",
            specialCourses: [],
            course: "",
            role: "",
            showPaymentOption: false,
            spacialCourseFlag: [],
            selectedRows: [],
            selectedUsers: [],
            olde: "",
            showusermodel: false,
            specialCourseId: "",
            specialCourseName: "",
            old_length: "",
            specialCoursescopy: [],
            cardProcessing: false,
            promo_code: "",
            appliedText: "",
            showPromoCodeOption: false,
            promoCodeApplied: false,
            course_cost_monthly: "",
            yearlyAmount: "",
            discount_percentage: "",
            isContinueButtonDisabled: true,
            enablePaymentButton: false,
        };
    },
    mounted() {
        this.siteName = Dynamic.SITE_NAME;
        this.infoEmail = Dynamic.INFO_EMAIL;
        this.$gtag.event("Company signup", {method: "Google"});
    },
    created() {
        localStorage.removeItem("fname");
        localStorage.removeItem("lname");
        localStorage.removeItem("email");
        localStorage.removeItem("courses");
        if (this.$route.query.role) {
            this.role = this.$route.query.role;
        }
        this.$http.post("company/company_dropdown_data", {}).then(resp => {
            for (let type of resp.data.companytype) {
                let obj = {
                    label: type.type,
                    value: type.id
                };
                this.company_types.push(obj);
            }
        });
        this.$http.get("user/discountRules").then(resp => {
            for (let course of resp.data.courses) {
                let obj = {
                    checked: false,
                    id: course.id,
                    course_name: course.name,
                    course_type: course.course_type,
                    cost: course.cost,
                    discounted: course.is_discounted_course
                };
                this.courses.push(obj);
            }

            var result1 = this.courses.filter(obj => {
                return obj.discounted === 1;
            });

            this.course = this.courses;

            this.specialCourses = [];

            for (let course of resp.data.non_discounted_courses) {
                let obj = {
                    checked: false,
                    id: course.id,
                    course_name: course.name,
                    course_type: course.course_type,
                    cost: course.cost,
                    discounted: course.is_discounted_course,
                    discounted_comment: course.discounted_course_comment,
                    users: 0
                };

                this.specialCourses.push(obj);
            }
            this.specialCoursescopy = JSON.parse(JSON.stringify(this.specialCourses));

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
            this.loading = true;
            let data = {
                monthlyAmount: this.total_cost,
                yearlyAmount: this.perYearCost,
                locationAmount: this.discountperlocation,
                userAmount: this.discountperemp,
                promocode: this.promo_code
            }
            this.$http
                .post("promocode/apply_promocode", data)
                .then(resp => {
                    this.total_cost = resp.data.final_amount_monthly;
                    this.course_cost_monthly = resp.data.previous_amount_monthly;
                    this.course_cost_yearly = resp.data.previous_amount_yearly;
                    this.perYearCost = resp.data.final_amount_yearly;
                    this.discount_percentage = resp.data.discount_percentage;
                    this.discountperlocation = resp.data.final_location_amount;
                    this.discountperemp = resp.data.final_user_amount;
                    this.loading = false;
                    this.submitLead();

                }).catch(function (error) {
                this.loading = false;
                if (error.response.status === 422) {
                    return Swal.fire({
                        title: "Error!",
                        html: error.response.data.message,
                        icon: "error"
                    });
                }
            });
        },
        updateUsers() {
            this.specialCourses.filter(obj => {
                if (obj.id == this.specialCourseId) {
                    obj.users = parseInt(this.users);
                }
            });
            this.showusermodel = false;
            this.users = 0;
        },
        dropdownselectionChange(e) {
            if (e.length < this.old_length) {
                const result1 = this.specialCourses.map(data => {
                    return data.id;
                });
                let diff10 = result1.filter(data1 => e.indexOf(data1) === -1);
                if (diff10 === result1 || e.length == 0 || e.indexOf(result1) != -1) {
                    // this.specialCourses = this.specialCoursescopy;

                    this.resetSpecialCourseUsers();
                } else if (diff10.length < result1.length) {
                    //  this.specialCourseId = diff10;
                    let diff11 = this.olde.filter(data1 => e.indexOf(data1) === -1);
                    this.specialCourseId = parseInt(diff11.toString());
                    this.users = 0;
                    this.updateUsers();
                } else {
                    this.users = 0;
                    this.updateUsers();
                }
            }

            var existSpecialCourse = this.specialCourses.filter(obj => {
                return obj.id == e[e.length - 1];
            });

            if (existSpecialCourse.length > 0) {
                this.$nextTick(() => {
                    this.$refs.dropdown.visible = false;
                });

                this.specialCourseId = e[e.length - 1];

                var result2 = this.courses.filter(obj => {
                    return obj.id === this.specialCourseId;
                });

                this.specialCourseName = result2[0].course_name;

                if (e.length > this.old_length) {
                    this.showusermodel = true;
                } else {
                }
            }
            this.olde = e;
            this.old_length = e.length;
        },
        specialCourseUsers() {
            this.spacialCourseFlag = this.specialCourses.map(item => {
                if (item.users > 0) {
                    return true;
                }
                return false;
            });
        },
        resetSpecialCourseUsers() {
            for (let i = 0; i < this.specialCourses.length; i++) {
                this.specialCourses[i].users = 0;
            }

            return this.specialCourses;
        },
        showDone() {
            this.courseSelectionFocused = true;
        },
        doneClicked(e) {
            this.courseSelectionFocused = false;
        },
        cancelAgreement() {
            this.agreementModal = false;
        },
        showContactUs() {
            this.contactUsModal = true;
        },
        finalCreateAccount() {
            //this.createAccount(this.formData);
            this.agreementModal = false;
            this.showPaymentOption = true;
        },
        payClicked(cardData) {
            this.enablePaymentButton = true;
            this.loading = true;
            let payment = {
                payment_type: "",
                cardholder_street_address:
                    cardData.address + "," + cardData.city + "," + cardData.state,
                cardholder_zip: cardData.zip,
                transaction_amount: "",
                token: cardData.token
            };
            if (this.specialCourseFlag) {
                payment.payment_type = "one-time";
            } else {
                payment.payment_type = cardData.paymentType;
            }
            if (cardData.paymentType == "monthly") {
                payment.transaction_amount = this.total_cost.toFixed(2);
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

            if (this.promoCodeApplied) {
                this.formData.promo_code = this.promo_code;
                this.formData.course_cost_monthly = this.course_cost_monthly;
            }
            this.loading = false;
            this.createAccount(this.formData);
        },

        showAgreement() {
            const result1 = this.specialCourses.map(data => {
                return data.id;
            });
            const diff1 = this.checked_courses.filter(
                data1 => result1.indexOf(data1) === -1
            );
            for (let special of this.specialCourses) {
                this.selectedRows.push(special.id);
                this.selectedUsers.push(special.users);
            }
            this.formData = {
                company_name: this.company.name,
                first_name: this.company.first_name,
                last_name: this.company.last_name,
                company_location_num: this.company.no_of_locations,
                company_employee_num: this.company.no_of_employees,
                company_address_1: this.company.address_1,
                company_address_2: this.company.address_2,
                company_phone: this.company.telephone_no,
                company_email: this.company.email,
                company_zip: this.company.zip,
                website: this.company.website,
                company_type: this.company.company_type,
                username: this.company.email,
                parent_id: this.company.parent_company,
                image: this.image,
                company_city: this.company.city,
                company_state: this.company.state,
                company_password: this.company.password,
                course_ids: diff1,
                // special_courses: this.selectedRows,
                // selected_users: this.selectedUsers,
                special_courses: this.specialCourses,
                status: 1,
                payment: [],
                card_info: [],
                i_agree: true
            };
            this.agreementModal = true;
        },

        formatPrice(value) {
            return (
                "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
            );
        },
        submitLead() {
            const result = this.specialCourses.map(data => {
                return data.id;
            });
            const diff = this.checked_courses.filter(
                data1 => result.indexOf(data1) === -1
            );
            let selectedSpecialCourses = [];

            var result4 = this.specialCourses.filter(obj => {
                return obj.users != 0;
            });

            selectedSpecialCourses = result4;

            var resulttest = selectedSpecialCourses.map(function (a) {
                return a.id;
            });
            const finalids = diff.concat(resulttest);
            if (
                this.company.no_of_locations !== "" &&
                this.company.no_of_employees !== "" &&
                this.company.no_of_locations < 2 &&
                this.company.no_of_employees < 2
            ) {
                if (
                    this.company.name !== "" &&
                    this.company.first_name !== "" &&
                    this.company.last_name !== "" &&
                    this.company.email !== ""
                ) {
                    return Swal.fire({
                        title: "Warning!",
                        html:
                            "Based on the details provided, it appears you requesting a quote for an individual user.  Please click OK to proceed.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn btn-success btn-fill",
                        cancelButtonClass: "btn btn-danger btn-fill",
                        confirmButtonText: "Ok",
                        cancelButtonText: "Cancel"
                    }).then(result => {
                        if (result.value) {
                            localStorage.setItem("fname", this.company.first_name);
                            localStorage.setItem("lname", this.company.last_name);
                            localStorage.setItem("email", this.company.email);
                            localStorage.setItem("courses", JSON.stringify(finalids));
                            window.location.href = "#/user_register?redirection=yes";
                        } else {
                            this.company.no_of_locations = "";
                            this.company.no_of_employees = "";
                        }
                    });
                } else {
                    return Swal.fire({
                        title: "Error",
                        html: "Please fill all required feilds.",
                        icon: "error"
                    });
                }
            }
            this.loading = true;
            let data = {
                company_name: this.company.name,
                first_name: this.company.first_name,
                last_name: this.company.last_name,
                number_of_locations: this.company.no_of_locations,
                number_of_employees: this.company.no_of_employees,
                phone_num: this.company.telephone_no,
                email: this.company.email,
                user_type: "corporate",
                course_ids: diff,
                special_courses: selectedSpecialCourses,
                promo_code: this.promo_code,
                course_cost: this.course_cost_monthly,
                discounted_cost: this.total_cost,
                total_cost_per_year: this.perYearCost,
                per_location: this.discountperlocation,
                per_user: this.discountperemp
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

                        this.employees_count = resp.data.number_of_employees;
                        this.locations_count = resp.data.number_of_locations;
                        this.discountperemp = resp.data.total / resp.data.number_of_employees;
                        this.discountperlocation = resp.data.per_location_cost;
                        this.showPricePlan = true;
                        this.specialCourseFlag = resp.data.onlySpecialCourse;
                        this.special_courses_with_users = resp.data.special_courses_users;
                        if (this.special_courses_with_users) {
                            var result1 = this.special_courses_with_users.filter(obj => {
                                return obj.users != 0;
                            });
                            this.special_courses_with_users = result1;
                        }
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
        },
        companyDetails() {
            this.showCompanyinformation = true;
        },
        companyEstimateDetail() {
            this.companyEstimateDetailModel = true;
        },
        hideModel() {
            this.companyEstimateDetailModel = false;
        },
        switchVisibility() {
            this.passwordFieldType =
                this.passwordFieldType === "password" ? "text" : "password";
        },
        cancelContact() {
            this.contactUsModal = false;
        },
        saveContact() {
            this.loading = true;
            let data = {
                name: this.contact.name,
                email: this.contact.email,
                phone: this.contact.phone,
                message: this.contact.message
            };
            this.$http
                .post("user/contact", data)
                .then(resp => {
                    this.contactUsModal = false;
                    this.loading = false;
                    Swal.fire({
                        title: "Success!",
                        text: resp.data.message,
                        icon: "success"
                    });
                })
                .catch(function (error) {
                    this.loading = false;
                    if (error.response.status === 422) {
                        let respmessage = error.response.data.message.replace(/,/g, "\n");
                        Swal.fire({
                            title: "Error!",
                            text: respmessage,
                            icon: "error"
                        });
                    }
                });
        },

        acceptNumber() {
            var x = this.company.telephone_no
                .replace(/\D/g, "")
                .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.company.telephone_no = !x[2]
                ? x[1]
                : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");

            var y = this.contact.phone
                .replace(/\D/g, "")
                .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.contact.phone = !y[2]
                ? y[1]
                : "(" + y[1] + ") " + y[2] + (y[3] ? "-" + y[3] : "");
        },
        onImageChange(e) {
            let files = e;
            if (!files.length) return;
            this.createImage(files[0]);
        },
        createImage(file) {
            let reader = new FileReader();
            let vm = this;
            reader.onload = e => {
                vm.image = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        createAccount(formDataSubmitted) {
            this.loading = true;
            delete this.$http.defaults.headers["authorization"];
            this.creatAccountClicked = true;
            this.$http
                .post("company/register", formDataSubmitted)
                .then(resp => {
                    let ids = [];
                    let obj = {
                        id: resp.data.id
                    };
                    ids.push(obj);
                    this.loading = false;
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    this.welcomeEmail(formDataSubmitted, ids)
                })
                .catch((error) => {
                    this.loading = false;
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    if (error.response.status === 422) {

                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error"
                        });
                        this.loading = false;
                    }
                }).finally(() => (this.loading = false));
        },

        welcomeEmail(formDataSubmitted, ids) {
            this.loading = true;
            this.$http
                .post("company/welcome_email", {
                    form_data: formDataSubmitted,
                    password: this.company.password,
                    ids: ids
                })
                .then(resp => {
                    this.loading = false;
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    this.$router.push("/login");
                    this.agreementModal = false;
                    Swal.fire({
                        title: "Success!",
                        text: `Account created successfully.`,
                        icon: "success"
                    });
                })
                .catch(function (error) {
                    this.loading = false;
                    this.agreementModal = this.showPaymentOption = this.showusermodel = this.loading = this.enablePaymentButton = false;
                    this.$router.push("/login");
                    Swal.fire({
                        title: "Success!",
                        text: "Account created successfully.",
                        icon: "success"
                    });
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
.promocode-applied {
  color:#00ccff;
  font-weight:bold;
  font-size:14px;
}
.course-section {
  padding: 40px;
  background-color: #ffffff !important;
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
.search-wrapper {
  position: relative;
}
.courseList {
  max-height: 250px !important;
  width: 100%;
  overflow: hidden;
  overflow-y: auto;
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
  text-decoration: underline !important;
  font-weight: bolder !important;
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
</style>
