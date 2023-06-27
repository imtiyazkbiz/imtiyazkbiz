<template>
  <div>
    <!-- Header -->
    <div class="header bg-gradient-success-custom py-7 py-lg-8 pt-lg-9">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <h1 class="text-white">Company Sign Up</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="separator separator-bottom separator-skew zindex-100">
        <svg
          x="0"
          y="0"
          viewBox="0 0 2560 100"
          preserveAspectRatio="none"
          version="1.1"
          xmlns="http://www.w3.org/2000/svg"
        >
          <polygon
            class="fill-default"
            points="2560 0 2560 100 0 100"
          ></polygon>
        </svg>
      </div>
    </div>
    <!-- Page content -->
    <div class="container mt--12 pb-5">
      <div class="row mt-3 mb-2">
        <div class="col-9"></div>
        <div class="col-3">
          <router-link to="/login"
            ><base-button type="primary"
              >Back To Login</base-button
            ></router-link
          >
        </div>
      </div>
      <!-- Table -->
      <div class="row justify-content-center">
        <div class="col-lg-10 col-md-10">
          <div class="card bg-secondary border-0">
            <div class="card-body px-lg-5 py-lg-5">
              <validation-observer
                v-slot="{ handleSubmit }"
                ref="formValidator"
              >
                <form role="form" @submit.prevent="handleSubmit(createAccount)">
                  <div class="row">
                    <div class="col-md-12">
                      <h4 style="color:#444C57" class="">REQUESTED SERVICES</h4>
                      <h5>
                        Thank you for your interest in TRAIN 321's online
                        courses! Please see a list of our current courses
                        below.<br />
                        Check the box next to the courses you would like to add
                        to your account. If you don't see a course you need,
                        <span @click.prevent="showContactUs()" class="href"
                          >Click here</span
                        >
                        to contact us!
                      </h5>
                      <hr />
                    </div>
                  </div>
                  <!-- <div class="row">
                    <h5>COURSE GROUPS</h5>
                    <div class="col-md-12">
                      <base-checkbox>
                        (5 courses) Alcohol Awareness</base-checkbox
                      >
                    </div>
                    <div class="col-md-12">
                      <base-checkbox>
                        (6 courses) Alcohol Awareness and Food
                        Safety</base-checkbox
                      >
                    </div>
                    <div class="col-md-12">
                      <base-checkbox>
                        6 courses) Food and Alcohol (Manager)</base-checkbox
                      >
                    </div>
                  </div> -->

                  <div class="row mb-4">
                    <div class="col-md-5">
                      <div class="col-md-8">
                        <div class="row">
                          <div class="col-md-7">
                            <h4>COURSES <span style="color:red; ">*</span></h4>
                          </div>
                          <div class="col-md-5">
                            <div class="search-wrapper">
                              <input
                                type="text"
                                class="form-group"
                                v-model="search"
                                placeholder="Search course.."
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="courseList">
                        <div
                          class="col-md-12"
                          v-for="course in filteredList"
                          :key="course.id"
                        >
                          <base-checkbox v-on:input="courseCheckchange(course)">
                            {{ course.course_name }}
                          </base-checkbox>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-7">
                      <div class="row">
                        <div class="col-md-3">
                          <base-input
                            type="number"
                            min="1"
                            label="Location Count *"
                            name="Location"
                            rules="required"
                            v-model="company.no_of_locations"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-3">
                          <base-input
                            type="number"
                            label="Employee Count *"
                            min="1"
                            name="Employee "
                            rules="required"
                            v-model="company.no_of_employees"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-6">
                          <base-input
                            type="text"
                            label="Company Name *"
                            name="Company name"
                            placeholder="Company Name"
                            rules="required"
                            v-model="company.name"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-4">
                          <base-input
                            type="text"
                            label="First Name *"
                            name="First Name"
                            placeholder="First Name"
                            rules="required"
                            v-model="company.first_name"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-4">
                          <base-input
                            type="text"
                            label="Last Name *"
                            name="Last Name"
                            placeholder="Last Name"
                            rules="required"
                            v-model="company.last_name"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-4">
                          <base-input
                            type="email"
                            label="Email Address *"
                            name="Email Address"
                            placeholder="Email Address"
                            rules="required"
                            v-model="company.email"
                          >
                          </base-input>
                        </div>
                      </div>

                      <!-- <div class="text-center my-4">
                        <base-button type="success" @click.prevent="submitLead">
                          Submit
                        </base-button>
                        <base-button type="danger" @click.prevent="hideModel()">
                          Cancel
                        </base-button>
                      </div> -->
                    </div>
                  </div>

                  <!-- <div class="row" style="margin-top:20px;">
                    <h5>ADDITIONAL SERVICES</h5>
                    <p>
                      <small>
                        Train321 offers the following additional services to
                        enhance your user experience. Please select any of the
                        following options that you would like to add to your
                        account.</small
                      >
                    </p>
                    <div
                      class="col-md-12"
                      v-for="service in services"
                      :key="service.id"
                    >
                      <base-checkbox>
                        {{ service.name }} (${{ service.price }} per
                        {{ service.frequency }})</base-checkbox
                      >
                    </div>
                  </div>-->
                  <span
                    v-if="
                      !showCompanyinformation &&
                        checked_courses.length &&
                        !lead_id
                    "
                  >
                    <base-button type="success" @click.prevent="submitLead"
                      >Estimate</base-button
                    >
                  </span>

                  <div v-if="showPricePlan && lead_id">
                    <hr />

                    <div class="row ">
                      <div class="col-md-5" style="color:brown;">
                        <small v-if="total_discount > 0"
                          >Congratulations! You have earned a discount ({{
                            discount_msg
                          }})</small
                        >
                      </div>
                      <div class="col-md-7" style="color:darkblue;">
                        <div
                          class="row"
                          style="border-bottom: 1px solid skyblue;"
                        >
                          <div class="col-md-10">
                            <small>Sub Total</small>
                          </div>
                          <div class="col-md-2">
                            <small
                              ><b>{{ formatPrice(sub_total) }}</b></small
                            >
                          </div>
                        </div>
                        <div
                          class="row"
                          style="border-bottom: 1px solid skyblue;"
                          v-if="total_discount > 0"
                        >
                          <div class="col-md-10">
                            <small>Discount</small>
                          </div>
                          <div class="col-md-2">
                            <small
                              ><b>- {{ formatPrice(total_discount) }}</b></small
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
                            <span
                              style="display: flex; justify-content: center;"
                            >
                              <div class="row">
                                <div class="col-md-10">
                                  <small>
                                    Cost per location<br />
                                    ({{ locations_count }} locations)</small
                                  >
                                </div>
                                <div class="col-md-12">
                                  <small
                                    ><b>{{
                                      formatPrice(discountperlocation)
                                    }}</b></small
                                  >
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-10">
                                  <small>
                                    Cost per user <br />
                                    ({{ employees_count }} users)</small
                                  >
                                </div>
                                <div class="col-md-12">
                                  <small
                                    ><b>{{
                                      formatPrice(discountperemp)
                                    }}</b></small
                                  >
                                </div>
                              </div>
                            </span>
                          </el-popover>
                          <div class="col-md-10">
                            <small
                              >Estimated monthly total (EMT)
                              <i
                                v-popover:fromPopOver
                                class="el-icon-question
                    text-blue"
                            /></small>
                          </div>
                          <div class="col-md-2">
                            <small
                              ><b>{{ formatPrice(total_cost) }}</b></small
                            >
                          </div>
                        </div>
                        <!-- <div
                          class="row"
                          style="border-bottom: 1px solid skyblue;"
                        >
                          <div class="col-md-10">
                            <small>
                              Cost per location (EMT /
                              {{ locations_count }} locations)</small
                            >
                          </div>
                          <div class="col-md-2">
                            <small
                              ><b>{{
                                formatPrice(discountperlocation)
                              }}</b></small
                            >
                          </div>
                        </div>
                        <div
                          class="row"
                          style="border-bottom: 1px solid skyblue;"
                        >
                          <div class="col-md-10">
                            <small>
                              Cost per user (EMT /
                              {{ employees_count }} users)</small
                            >
                          </div>
                          <div class="col-md-2">
                            <small
                              ><b>{{ formatPrice(discountperemp) }}</b></small
                            >
                          </div>
                        </div> -->
                      </div>
                    </div>
                    <hr />
                  </div>
                  <div v-if="!showCompanyinformation && lead_id">
                    <base-button type="success" @click.prevent="submitLead"
                      >Re-estimate</base-button
                    >
                    <base-button
                      type="success"
                      @click.prevent="companyDetails()"
                      >Continue Signup</base-button
                    >
                  </div>
                  <div v-if="showCompanyinformation">
                    <div class="row" style="margin-top:5px;">
                      <div class="col-md-12">
                        <h4 style="color:#444C57" class="">
                          Company Information
                        </h4>
                        <hr />
                      </div>
                      <br />
                    </div>
                    <div class="form-row">
                      <div class="col-md-3">
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
                      <div class="col-md-3">
                        <base-input
                          type="text"
                          label="Company Name *"
                          name="Company name"
                          placeholder="Company Name"
                          rules="required"
                          v-model="company.name"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-3">
                        <base-input
                          type="text"
                          label="Website"
                          name="Website"
                          placeholder="Website"
                          v-model="company.website"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-3">
                        <label class="form-control-label">Company Logo</label>
                        <form>
                          <file-input v-on:change="onImageChange"></file-input>
                        </form>
                      </div>
                      <div class="col-md-3">
                        <base-input
                          type="text"
                          label="Address *"
                          name="Address"
                          placeholder="Address"
                          rules="required"
                          v-model="company.address_1"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-2">
                        <base-input
                          type="text"
                          label="City *"
                          name="City"
                          placeholder="city"
                          rules="required"
                          v-model="company.city"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-2">
                        <base-input
                          type="text"
                          label="State *"
                          name="State"
                          placeholder="State"
                          rules="required"
                          v-model="company.state"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-2">
                        <base-input
                          type="number"
                          label="Zip Code *"
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
                      <div class="col-md-2">
                        <base-input
                          type="text"
                          label="First Name *"
                          name="First Name"
                          placeholder="First Name"
                          rules="required"
                          v-model="company.first_name"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-2">
                        <base-input
                          type="text"
                          label="Last Name *"
                          name="Last Name"
                          placeholder="Last Name"
                          rules="required"
                          v-model="company.last_name"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-2">
                        <base-input
                          label="Phone *"
                          name="Phone Number"
                          placeholder="Phone"
                          rules="required"
                          v-model="company.telephone_no"
                          @input="acceptNumber"
                        >
                        </base-input>
                      </div>
                      <div class="col-md-3">
                        <base-input
                          type="email"
                          label="Email Address *"
                          name="Email Address"
                          placeholder="Email Address"
                          rules="required"
                          v-model="company.email"
                        >
                        </base-input>
                      </div>

                      <div class="col-md-2">
                        <base-input
                          :type="passwordFieldType"
                          label="Password *"
                          v-if="!(company_id !== '')"
                          name="Password"
                          placeholder="Password"
                          rules="required"
                          v-model="company.password"
                        >
                        </base-input>
                        <base-input
                          label="Password *"
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

                    <div class="row">
                      <h3>AUTHORIZATION</h3>
                      <p>
                        <small>
                          By clicking the "Create Account" button below, you
                          agree that the name you typed in the box above (Your
                          Full Name) will be the electronic representation of
                          your signature for all purposes in relation to the
                          Train 321 legally binding Service Activation
                          Agreement. You agree that your electronic signature is
                          considered an original, for purposes of entering into
                          a contract.</small
                        >
                      </p>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-primary mt-4">
                        Create account
                      </button>
                    </div>
                  </div>
                </form>
              </validation-observer>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <!--<router-link to="/dashboard" class="text-light"><small>Forgot password?</small></router-link>-->
            </div>
            <div class="col-6 text-right">
              <router-link to="/login" class="text-light"
                ><small>Back To Login</small></router-link
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal :show.sync="contactUsModal">
      <h3>QUICK CONTACT</h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <base-input
              type="text"
              label="Your Name"
              placeholder="Enter Your Name"
              v-model="contact.name"
            >
            </base-input>
          </div>
          <div class="col-md-12">
            <base-input
              type="text"
              label="Phone"
              placeholder="Enter Your Phone number"
              v-model="contact.phone"
              @input="acceptNumber"
            >
            </base-input>
          </div>
          <div class="col-md-12">
            <base-input
              type="email"
              label="Email"
              placeholder="Enter Your Email"
              v-model="contact.email"
            >
            </base-input>
          </div>
          <div class="col-md-12">
            <label class="form-control-label">Message</label>
            <textarea
              class="form-control"
              placeholder="Enter Message"
              v-model="contact.message"
            >
            </textarea>
          </div>
        </div>
        <div class="text-center mt-2">
          <base-button type="success" @click.prevent="saveContact">
            Submit
          </base-button>
          <base-button type="danger" @click.prevent="cancelContact">
            Cancel
          </base-button>
        </div>
      </form>
    </modal>
  </div>
</template>
<script>
import FileInput from "@/components/Inputs/FileInput";
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import { TheMask } from "vue-the-mask";
export default {
  name: "register",
  components: {
    TheMask,
    FileInput,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      baseUrl: this.$baseUrl,
      complete: false,
      stripeOptions: {
        // see https://stripe.com/docs/stripe.js#element-options for details
      },
      card_info: {
        token: "",
        last4digits: "",
        expiry_month: "",
        expiry_year: "",
        card_zip_code: ""
      },
      hot_user: "",
      hot_token: "",
      config: "",
      company_id: "",
      creatAccountClicked: false,
      modelValidations: {
        requiredText: {
          required: true
        },
        email: {
          required: true,
          email: true
        },
        number: {
          required: true,
          decimal: true
        },
        url: {
          required: true,
          url: true
        },
        idSource: {
          required: true
        },
        idDestination: {
          required: true,
          confirmed: "idSource"
        }
      },
      company_types: [],
      parent_companies: [],
      payment_screen: false,
      image: "",
      sub_total: "",
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
        password: "",
        confirm_email: "",
        confirm_password: "",
        paying_responsible: "company",
        payment_option: "credit_card",
        billing_address_same_as_account: false,
        billing_address_1: "",
        billing_address_2: "",
        billing_first_name: "",
        billing_last_name: "",
        billing_city: "",
        billing_state: "",
        billing_zip: "",
        cvv: "",
        exp_date: "",
        cc_no: "",
        account_number: "",
        routing_number: "",
        bank_name: "",
        bank_address: ""
      },
      search: "",
      estimate: false,
      selectCourse: [],
      showPass: false,
      contactUsModal: false,
      passwordFieldType: "password",
      courses: [],
      services: [],
      selectedRows: [],
      cardType: [
        {
          label: "American Express",
          value: "American Express"
        },
        {
          label: "Visa",
          value: "Visa"
        },
        {
          label: "Master Card",
          value: "Master Card"
        },
        {
          label: "Discover",
          value: "Discover"
        }
      ],
      contact: {
        name: "",
        phone: "",
        email: "",
        message: ""
      },
      lead_id: "",
      checked_courses: [],
      companyEstimateDetailModel: false,
      showCompanyinformation: false,
      showPricePlan: false,
      total_cost: "",
      total_discount: "",
      discountperlocation: "",
      discount_msg: "",
      employees_count: "",
      locations_count: "",
      discountperemp: "",
      discount_per: "",
      electronic_signature: "",
      expiration_date: "",
      security: "",
      card_number: "",
      card_type: ""
    };
  },
  created() {
    this.$http.post("company/company_dropdown_data", {}).then(resp => {
      for (let type of resp.data.companytype) {
        let obj = {
          label: type.type,
          value: type.id
        };
        this.company_types.push(obj);
      }
      for (let parent of resp.data.parentcompanies) {
        let obj = {
          label: parent.company_name,
          value: parent.id
        };
        this.parent_companies.push(obj);
      }
    });
    this.$http.get("user/discountRules").then(resp => {
      for (let course of resp.data.courses) {
        let obj = {
          checked: false,
          id: course.id,
          course_name: course.name,
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
  computed: {
    filteredList() {
      return this.courses.filter(course => {
        return course.course_name
          .toLowerCase()
          .includes(this.search.toLowerCase());
      });
    }
  },
  methods: {
    showContactUs() {
      this.contactUsModal = true;
    },

    formatPrice(value) {
      return (
        "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
      );
    },
    submitLead() {
      let data = {
        company_name: this.company.name,
        first_name: this.company.first_name,
        last_name: this.company.last_name,
        number_of_locations: this.company.no_of_locations,
        number_of_employees: this.company.no_of_employees,
        email: this.company.email,
        user_type: "corporate",
        course_ids: this.checked_courses
      };
      this.$http
        .post("user/lead", data)
        .then(resp => {
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
          this.discountperlocation =
            resp.data.total / resp.data.number_of_locations;
          this.showPricePlan = true;
          //this.showCompanyinformation = true;
          // Swal.fire({
          //   title: "Success!",
          //   text: `User information saved successfully!`,
          //   icon: "success"
          // });
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            return Swal.fire({
              title: "Error!",
              text: error.response.data.message,
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
    courseCheckchange(row) {
      if (this.checked_courses.includes(row.id)) {
        this.checked_courses.splice(this.checked_courses.indexOf(row.id), 1);
      } else {
        this.checked_courses.push(row.id);
      }
    },
    switchVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    },
    cancelContact() {
      this.contactUsModal = false;
    },
    saveContact() {
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
          Swal.fire({
            title: "Success!",
            text: resp.data.message,
            icon: "success"
          });
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            let respmessage = error.response.data.message.replace(/,/g, "\n");
            return Swal.fire({
              title: "      Error!",
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
    onSubmit() {
      // this will be called only after form is valid. You can do an api call here to register users
    },
    createAccount() {
      this.creatAccountClicked = true;
      // if(this.company.billing_address_1!=='' && this.company.billing_first_name!==''&&this.company.billing_last_name!==''&&this.company.billing_city!==''&& this.company.billing_state!==''&& this.company.billing_zip!==''){
      let data = {
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
        payment: [],
        card_info: []
      };
      this.$http.post("company/register", data).then(resp => {
        let ids = [];
        let obj = {
          id: resp.data.id
        };
        ids.push(obj);
        this.$http
          .post("company/welcome_email", {
            password: this.company.password,
            ids: ids
          })
          .then(resp => {
            this.$router.push("/login");
            Swal.fire({
              title: "Success!",
              text: `Account has been Created!`,
              icon: "success"
            });
          });
      });
    }
  }
};
</script>
<style>
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
  max-height: 190px !important;
  width: 100%;
  overflow: hidden;
  overflow-y: auto;
}
hr {
  margin-top: 2px !important;
  margin-bottom: 20px !important;
}

.form-control-label {
  color: #525f7f;
  font-size: 10px !important;
  font-weight: 700;
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
</style>
