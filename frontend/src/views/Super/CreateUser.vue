<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pb-2">
          <template slot="header">
            <div class="align-items-center row">
              <h3 class="mb-0 col-md-2">Add User</h3>

              <h5 class="col-md-6 ">
                <span class="requireField">*</span> Indicates a required field.
              </h5>
            </div>
          </template>
          <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
            <form class="" @submit.prevent="handleSubmit(createUser)">
              <label><b>Select Type : </b></label>
              <div class="row">
                <div class="col-md-2">
                  <input
                    type="radio"
                    name="type"
                    v-model="user_type"
                    value="company_user"
                  />
                  &nbsp;<label> Company User</label>
                </div>
                <div class="col-md-2">
                  <input
                    type="radio"
                    name="type"
                    v-model="user_type"
                    value="individual_user"
                  />
                  &nbsp;<label> Individual User</label>
                </div>
              </div>

              <div class="row mt-2" v-if="company">
                <div class="col-md-4">
                  <base-input label="Select Company">
                    <el-select
                      filterable
                      v-model="selected_company"
                      v-on:change="getCompanyCourse()"
                    >
                      <el-option
                        v-for="(option, index) in locations"
                        class="select-primary"
                        :value="option.value"
                        :label="option.label"
                        :key="index"
                      >
                      </el-option>
                    </el-select>
                  </base-input>
                </div>
              </div>
              <div class="row mt-2">
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <label class="form-control-label"
                    >First Name <span class="requireField">*</span></label
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
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <label class="form-control-label"
                    >Last Name <span class="requireField">*</span></label
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
                <div class="col-md-3" v-if="company && selectedCompany">
                  <label class="form-control-label"
                    >User Type <span class="requireField">*</span></label
                  >

                  <el-select
                    class=" mr-3"
                    style="width: 100%"
                    placeholder="Select User Type"
                    rules="required"
                    v-model="employee.user_type"
                  >
                    <el-option
                      v-for="(option, index) in user_types"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'user_type_' + index"
                    >
                    </el-option>
                  </el-select>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <label class="form-control-label"
                    >Email<span
                      class="requireField"
                      v-if="
                        employee.user_type == 'admin' ||
                          employee.user_type == 'location_manager'
                      "
                    >
                      *</span
                    ></label
                  >
                  <base-input
                    type="email"
                    name="Email"
                    v-if="
                      employee.user_type == 'admin' ||
                        employee.user_type == 'location_manager'
                    "
                    rules="required"
                    placeholder="Email"
                    v-model="employee.email"
                  >
                  </base-input>
                  <base-input
                    v-else
                    type="email"
                    label=""
                    name="Email"
                    placeholder="Email"
                    v-model="employee.email"
                  >
                  </base-input>
                </div>
                <div
                  class="col-md-6"
                  v-if="individual || (company && selectedCompany)"
                >
                  <el-popover
                    ref="fromPopOver"
                    placement="top-start"
                    width="250"
                    trigger="hover"
                  >
                    <span style="display: flex; justify-content: center;">
                      You can search and select multiple courses to assign.
                    </span>
                  </el-popover>
                  <label style="color: #444C57;" class=""
                    >Assigned Courses
                    <i
                      v-popover:fromPopOver
                      class="el-icon-question
                             text-blue"
                    />
                  </label>
                  <br />
                  <el-select
                    multiple
                    filterable
                    style="width:100%"
                    placeholder="Select Course"
                    v-model="employee.selected_courses"
                  >
                    <el-option
                      v-for="(option, index) in courses"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="index"
                    >
                    </el-option>
                  </el-select>
                </div>
              </div>

              <div class="row mt-3">
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input label="Progress Report">
                    <div class="d-flex">
                      <base-switch
                        class="mr-1"
                        type="success"
                        v-model="employee.progress"
                      ></base-switch>
                    </div>
                  </base-input>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input label="Status">
                    <div class="d-flex">
                      <base-switch
                        class="mr-1"
                        type="success"
                        v-model="employee.status"
                      ></base-switch>
                    </div>
                  </base-input>
                </div>
              </div>
              <hr v-if="individual || (company && selectedCompany)" />
              <h5 v-if="individual || (company && selectedCompany)">
                <span style="text-decoration:underline;"
                  >Non-Required fields:</span
                >
              </h5>
              <div class="row">
                <div class="col-md-3" v-if="company && selectedCompany">
                  <base-input label="Job Title">
                    <el-select
                      class=" mr-3"
                      style="width: 100%"
                      placeholder="Select Job Title"
                      v-model="employee.job_title"
                    >
                      <el-option
                        v-for="(option, index) in job_titles"
                        class="select-primary"
                        :value="option.value"
                        :label="option.label"
                        :key="'job_title_' + index"
                      >
                      </el-option>
                    </el-select>
                  </base-input>
                </div>

                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    label="Phone"
                    name="Telephone"
                    placeholder="Phone"
                    v-model="employee.phone"
                    @input="acceptNumber"
                  >
                  </base-input>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    type="text"
                    label="Address"
                    name="Address"
                    placeholder="Address"
                    v-model="employee.address"
                  ></base-input>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    type="text"
                    label="City"
                    name="City"
                    placeholder="City"
                    v-model="employee.city"
                  >
                  </base-input>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    type="text"
                    label="State"
                    name="State"
                    placeholder="State"
                    v-model="employee.state"
                  >
                  </base-input>
                </div>
                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    type="number"
                    label="Zip Code"
                    name="Zip code"
                    placeholder="Zip Code"
                    v-model="employee.zipcode"
                  >
                  </base-input>
                </div>

                <div class="col-md-3" v-if="company && selectedCompany">
                  <base-input label="DOB">
                    <el-date-picker
                      v-model="employee.dob"
                      placeholder="Pick a day"
                      format="MM/dd/yyyy"
                      :picker-options="pickerOptions1"
                    >
                    </el-date-picker>
                  </base-input>
                </div>
                <div class="col-md-3" v-if="company && selectedCompany">
                  <base-input
                    type="text"
                    label="Social Security Number"
                    name="Social Security Number"
                    placeholder="Social Security"
                    v-model="employee.social_security"
                  ></base-input>
                </div>

                <div
                  class="col-md-3"
                  v-if="individual || (company && selectedCompany)"
                >
                  <base-input
                    type="text"
                    label="Password"
                    name="Password"
                    placeholder="Password"
                    v-model="employee.password"
                  >
                  </base-input>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 text-right">
                  <base-button
                    :disabled="company && !selected_company"
                    class="custom-btn"
                    native-type="submit"
                    >Submit</base-button
                  >
                </div>
              </div>
            </form>
          </validation-observer>
        </card>
      </div>
    </div>
  </div>
</template>
<script>
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2";
export default {
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    [DatePicker.name]: DatePicker
  },
  data() {
    return {
      loading: false,
      user_type: "company_user",
      employee: {
        first_name: "",
        last_name: "",
        email: "",
        phone: "",
        address: "",
        city: "",
        state: "",
        zipcode: "",
        username: "",
        password: "",
        selected_courses: [],
        user_type: "",
        job_title: "",
        dob: "",
        social_security: "",
        progress: false,
        status: true
      },
      selected_company: "",
      individual: false,
      company: false,
      courses: [],
      locations: [],
      selectedCompany: false,
      company_id: "",
      user_types: [
        {
          label: "Admin",
          value: "admin"
        },
        {
          label: "Manager",
          value: "location_manager"
        },
        {
          label: "Employee",
          value: "employee"
        }
      ],
      job_titles: [],
      pickerOptions1: {
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              picker.$emit("pick", new Date());
            }
          },
          {
            text: "Yesterday",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit("pick", date);
            }
          },
          {
            text: "A week ago",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", date);
            }
          }
        ]
      }
    };
  },
  watch: {
    user_type: function() {
      if (this.user_type == "individual_user") {
        this.individual = true;
        this.company = false;
      }
      if (this.user_type == "company_user") {
        this.individual = false;
        this.company = true;
      }
    },
    selected_company: function() {
      if (this.selected_company) {
        this.selectedCompany = true;
      } else {
        this.selectedCompany = false;
      }
    }
  },
  created() {
    if (this.user_type == "individual_user") {
      this.individual = true;
      this.company = false;
    }
    if (this.user_type == "company_user") {
      this.individual = false;
      this.company = true;
    }
    this.fetchData();
  },
  methods: {
    getCompanyCourse() {
      this.company_id = this.selected_company;
      this.$http.get("company/all_courses/" + this.company_id).then(resp => {
        this.courses = [];
        for (let data of resp.data[0].courses) {
          let obj = {
            label: data.name,
            value: data.course_id
          };
          this.courses.push(obj);
        }
      });
    },
    fetchData() {
      this.$http.get("user/discountRules").then(resp => {
        for (let course of resp.data.courses) {
          let obj = {
            value: course.id,
            label: course.name
          };
          this.courses.push(obj);
        }
      });

      this.$http.post("location/all").then(resp => {
        for (let loc of resp.data) {
          let obj = {
            label: loc.name,
            value: loc.id
          };
          this.locations.push(obj);
        }
      });
      this.$http.get("employees/jobTitles").then(resp => {
        let jobtitle = resp.data;
        for (let data of jobtitle) {
          let obj = {
            value: data.id,
            label: data.name
          };
          this.job_titles.push(obj);
        }
      });
    },
    acceptNumber() {
      var x = this.employee.phone
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.employee.phone = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    createUser() {
      this.loading = true;
      let progress = 0;
      progress = this.employee.progress ? 1 : 0;
      let status = 0;
      status = this.employee.status ? 1 : 0;
      let data = {
        employee_first_name: this.employee.first_name,
        employee_last_name: this.employee.last_name,
        user_type: "",
        employee_location_id: "",
        employee_job_title_id: "",
        dob: "",
        social_security: "",
        employee_address: this.employee.address,
        employee_city: this.employee.city,
        employee_state: this.employee.state,
        employee_zipcode: this.employee.zipcode,
        employee_email: this.employee.email,
        employee_phone_num: this.employee.phone,
        password: this.employee.password,
        address: this.employee.address,
        employee_progress: progress,
        employee_status: status,
        i_agree: true,
        courses: this.employee.selected_courses,
        payment: []
      };
      if (this.individual) {
        data.user_type = "individual";
      } else {
        data.user_type = this.employee.user_type;
        data.employee_location_id = this.selected_company;
        data.employee_job_title_id = this.employee.job_title;
        data.dob = this.employee.dob;
        data.social_security = this.employee.social_security;
      }
      this.$http
        .post("employees/register", data)
        .then(resp => {
          let ids = [];
          let obj = {
            id: resp.data.id
          };
          ids.push(obj);
          Swal.fire({
            title: "Success!",
            text: `New Employee has been Added!`,
            icon: "success"
          });

          this.$router.push("/all_users");
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error"
            });
          }
        })
        .finally(() => (this.loading = false));
    }
  }
};
</script>
