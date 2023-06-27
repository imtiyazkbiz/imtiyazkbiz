<template>
    <div class="content" v-loading.fullscreen.lock="loading">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <card class="no-border-card" footer-classes="pb-2">
                <template slot="header">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-12">
                            <h2 class="mb-0" v-if="!admin_adding">Edit User</h2>
                            <h2 class="mb-0" v-else>Add User</h2>
                        </div>
                        <div class="col-lg-6 col-12 text-right">
                            <h5>
                                <span class="requireField">*</span> Indicates a required field.
                            </h5>
                        </div>
                    </div>
                </template>
                <div class="row mb-3" v-if="!admin_adding">
                    <div class="col-md-6">
                        <h3 class="">
                            Last Sign in:
                            <span style="color: #00ccff" v-if="employee.last_sign_in">{{
                                    formattedDate(employee.last_sign_in)
                                }}</span
                            ><span style="color: #00ccff" v-else>Never</span>
                        </h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <h3>
                            Type:
                            <span
                                style="color: #00ccff"
                                v-if="employee.user_type === 'individual'"
                            >Individual</span
                            >
                            <span style="color: #00ccff" v-else>Company</span>
                        </h3>
                    </div>
                </div>
                <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
                    <form class="" @submit.prevent="handleSubmit(addUser)">
                        <h5>
                            <span style="text-decoration:underline;">Required fields:</span>
                        </h5>
                        <div class="row">
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label"
                                >First Name <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    name="first name"
                                    rules="required"
                                    placeholder="First Name"
                                    v-model="employee.first_name"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label"
                                >Last Name <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    name="last name"
                                    rules="required"
                                    placeholder="Last Name"
                                    v-model="employee.last_name"
                                >
                                </base-input>
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="!employee_editing && employee.user_type != 'individual'"
                            >
                                <label class="form-control-label"
                                >User Type <span class="requireField">*</span></label
                                >
                                <br />
                                <el-select
                                    class="mr-3"
                                    name="User Type"
                                    style="width: 100%"
                                    placeholder="Select Type"
                                    v-model="employee.user_type"
                                >
                                    <el-option
                                        rules="required"
                                        v-for="(option, index) in user_types"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'user_type_' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div v-if="employee_editing" class='col-md-3'>
                                <base-input
                                    type="text"
                                    label="User Type"
                                    placeholder="Select User Type"
                                    :disabled="disable"
                                    v-model="employee.user_type"
                                >
                                </base-input>
                            </div>

                            <div :class="admin_adding ? 'col-md-2' : 'col-md-2'">
                                <label class="form-control-label"
                                >Email Address<span
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
                                    v-if="
                    employee.user_type == 'admin' ||
                      employee.user_type == 'location_manager'
                  "
                                    rules="required"
                                    type="email"
                                    label=""
                                    name="Email Address"
                                    placeholder="Email"
                                    v-model="employee.email"
                                >
                                </base-input>
                                <base-input
                                    v-else
                                    type="email"
                                    label=""
                                    name="Email Address"
                                    placeholder="Email"
                                    v-model="employee.email"
                                >
                                </base-input>
                            </div>
                            <div class="col-md-2" v-if="employee_editing">
                                <label class="form-control-label"
                                >Username <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    placeholder="Username"
                                    v-model="employee.userName"
                                >
                                </base-input>
                                <span
                                    class="text-danger"
                                    v-if="pressed && employee.userName.length <= 0"
                                >Username is required</span
                                >
                            </div>
                            <div class="col-md-2" v-if="!employee_editing">
                                <label class="form-control-label">Username </label>
                                <base-input
                                    type="text"
                                    placeholder="Username"
                                    v-model="employee.userName"
                                >
                                </base-input>
                            </div>
                        </div>
                        <div class="row">
                            <div
                                :class="admin_adding ? 'col-md-6' : 'col-md-6'"
                                v-if="editor == 'super-admin' || editor == 'sub-admin'"
                            >
                                <label class="form-control-label">Parent Location </label>

                                <br />
                                <el-select
                                    filterable
                                    class="mr-3"
                                    style="width: 100%"
                                    @change="childLocationsDropdown($event)"
                                    v-model="employee.assignedParentLocation"
                                >
                                    <el-option
                                        v-for="(option, index) in parentLocations"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'location_' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div
                                :class="admin_adding ? 'col-md-6' : 'col-md-6'"
                                v-if="!individual_editing"
                            >
                                <div
                                    v-if="!employee_editing "
                                >
                                    <label class="form-control-label"
                                    >Assigned Location
                                        <span class="requireField" v-if="employee.user_type != 'individual'">*</span></label
                                    >

                                    <br />
                                    <el-select
                                        v-if="employee.user_type == 'employee'"
                                        multiple
                                        filterable
                                        class="mr-3"
                                        style="width: 100%"
                                        v-model="employee.assigned_location"
                                    >
                                        <el-option
                                            :disabled="employee.assigned_location.length >= 1"
                                            v-for="(option, index) in locations"
                                            class="select-primary"
                                            :value="option.value"
                                            :label="option.label"
                                            :key="'location_' + index"
                                        >
                                        </el-option>
                                    </el-select>
                                    <el-select
                                        v-else
                                        multiple
                                        filterable
                                        class="mr-3"
                                        style="width: 100%"
                                        v-model="employee.assigned_location"
                                    >
                                        <el-option
                                            v-for="(option, index) in locations"
                                            class="select-primary"
                                            :value="option.value"
                                            :label="option.label"
                                            :key="'location_' + index"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div v-if="employee_editing">
                                    <base-input
                                        type="text"
                                        label="Assigned Location"
                                        placeholder="Assigned Location"
                                        :disabled="disable"
                                        v-model="employee.assigned_location_name"
                                    >
                                    </base-input>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="admin_adding">
                                <el-popover
                                    ref="fromPopOver"
                                    placement="top-start"
                                    width="250"
                                    trigger="hover"
                                >
                  <span style="display: flex; justify-content: center">
                    You can search and select multiple courses to assign.
                  </span>
                                </el-popover>
                                <label style="color: #444c57" class=""
                                >Assigned Courses
                                    <i v-popover:fromPopOver class="el-icon-question text-blue" />
                                </label>
                                <br />
                                <el-select
                                    multiple
                                    filterable
                                    class="mr-3"
                                    style="width: 100%"
                                    placeholder="Select Course"
                                    v-model="employee.assigned_classes"
                                >
                                    <el-option
                                        v-for="(option, index) in classes"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'test_question' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3" v-if="!admin_adding">
                                <base-input
                                    :type="feildType"
                                    label="Password"
                                    placeholder="Password"
                                    v-model="employee.access_code"
                                >
                                </base-input>
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-3 mt-4' : 'col-md-3'"
                                v-if="admin_adding"
                            >
                                <base-checkbox v-model="employee.password_genrate"
                                >Auto Generate password for this user</base-checkbox
                                >
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-3 mt-2' : 'col-md-3'"
                                v-if="admin_adding && !employee.password_genrate"
                            >
                                <base-input
                                    type="password"
                                    label="Password"
                                    Placeholder="Enter Password"
                                    v-model="employee.password"
                                ></base-input>
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="
                  !employee_editing &&
                    (employee.user_type == 'admin' ||
                      employee.user_type == 'location_manager')
                "
                            >
                                <label class="form-control-label">Progress Report</label>
                                <div class="d-flex">
                                    <base-switch
                                        class="mr-1"
                                        type="success"
                                        v-model="employee.progress"
                                    ></base-switch>
                                </div>
                            </div>
                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="!employee_editing"
                            >
                                <label class="form-control-label">User Status</label>
                                <div class="d-flex">
                                    <base-switch
                                        class="mr-1"
                                        type="success"
                                        v-model="employee.userstatus"
                                    ></base-switch>
                                </div>
                            </div>
                        </div>
                        <hr />
                        <h5>
              <span style="text-decoration: underline"
              >Non-Required fields:</span
              >
                        </h5>
                        <div class="row">
                            <div
                                :class="admin_adding ? 'col-md-3' : 'col-md-3'"
                                v-if="!individual_editing"
                            >
                                <div v-if="!employee_editing">
                                    <label class="form-control-label">Employee Job Title</label>
                                    <br />
                                    <el-select
                                        class="mr-3"
                                        style="width: 100%"
                                        placeholder="Select Title"
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
                                </div>
                                <div v-if="employee_editing">
                                    <base-input
                                        type="text"
                                        label="Job Title"
                                        placeholder="Job Title"
                                        :disabled="disable"
                                        v-model="employee.job_title_label"
                                    >
                                    </base-input>
                                </div>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="Phone Number"
                                    name="phone number"
                                    placeholder="(555) 555-5555"
                                    v-model="employee.phone_number"
                                    @input="acceptNumber"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="Address"
                                    name="Address"
                                    placeholder="Address"
                                    v-model="employee.address"
                                ></base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="City"
                                    name="City"
                                    placeholder="City"
                                    v-model="employee.city"
                                >
                                </base-input>
                            </div>
                        </div>
                        <div class="row">
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="State"
                                    name="State"
                                    placeholder="State"
                                    v-model="employee.state"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="number"
                                    label="Zip Code"
                                    name="Zip code"
                                    placeholder="Zip Code"
                                    v-model="employee.zipcode"
                                >
                                </base-input>
                            </div>

                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label">Date of birth</label>
                                <el-date-picker
                                    v-model="employee.dob"
                                    placeholder="Pick a day"
                                    style="width: 100%"
                                    format="MM/dd/yyyy"
                                    :picker-options="pickerOptions1"
                                >
                                </el-date-picker>
                            </div>
                            <div
                                v-if="editor == 'super-admin' || editor == 'sub-admin'"
                                :class="admin_adding ? 'col-md-3' : 'col-md-3'"
                            >
                                <base-input
                                    type="text"
                                    label="Social Security Number"
                                    name="Social Security Number"
                                    placeholder="Social Security"
                                    v-model="employee.social_security"
                                ></base-input>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <router-link
                                :hide="editor === 'employee'"
                                :to="
                  editor === 'super-admin' || editor === 'sub-admin'
                    ? '/all_users'
                    : editor === 'admin'
                    ? '/company_employees'
                    : '/dashboard'
                "
                            >
                                <base-button  type="danger" class="custom-btn mr-3">
                                    Cancel
                                </base-button>
                            </router-link>
                            <base-button  :name="admin_adding ? 'Add User ' : 'Update Profile'" class="custom-btn" native-type="submit">
                                {{ admin_adding ? "Submit" : "Update" }}
                            </base-button>
                        </div>
                    </form>
                </validation-observer>
            </card>

            <card
                class="no-border-card"
                footer-classes="pb-2"
                v-if="!employee_editing && !admin_adding"
            >
                <template slot="header">
                    <div class="row align-items-center" v-if="!admin_adding">
                        <div class="col-md-6">
                            <h2 class="mb-0">Employee Courses</h2>
                        </div>
                        <div class="col-sm-6 text-right" v-if="!employee_editing">
                            <label></label>
                            <base-button
                                @click.prevent="showAssigncourse()"
                                class="custom-btn"
                            >
                                Assign course
                            </base-button>
                        </div>
                    </div>
                </template>
                <div v-if="!admin_adding">
                    <div>
                        <div class="row" v-if="admin_adding">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <label style="color: #444c57" class="">Assigned Courses </label>

                                <br />
                                <el-select
                                    class="mr-3"
                                    style="width: 100%"
                                    placeholder="Select Course"
                                    v-model="employee.assigned_classes"
                                >
                                    <el-option
                                        v-for="(option, index) in classes"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'test_question' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <course-assignment
                            :employee_id="this.employee_id"
                            ref="form"
                        ></course-assignment>
                    </div>
                </div>
            </card>
        </div>
        <modal :show.sync="courseAssigneeModal">
            <h3 slot="header" style="color: #444c57" class="modal-title">
                Assign Course
            </h3>
            <form>
                <div class="row">
                    <div class="col-sm-12">
                        <label class="form-control-label"
                        >Select any Course to Assign
                        </label>
                    </div>
                    <div class="col-sm-12">
                        <el-select
                            multiple
                            filterable
                            class="company_dropdown2 w-100"
                            v-model="assigned_course_id"
                            @change="getAssignCourseId($event)"
                            placeholder="Select Course"
                        >
                            <el-option
                                class="select-default"
                                v-for="(course, index) in courses"
                                :key="index"
                                :label="course.name"
                                :value="course.id"
                            >
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="pt-2 mt-2 text-right">
                    <base-button
                        type="danger"
                        class="custom-btn mr-3"
                        @click.prevent="courseAssigneeModal = false"
                    >
                        Cancel
                    </base-button>
                    <base-button class="custom-btn" @click.prevent="assignCourse">
                        {{ "Assign Course" }}
                    </base-button>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
    </div>
</template>
<script>
import {
    DatePicker,
    TimeSelect,
    Table,
    TableColumn,
    Select,
    Option
} from "element-ui";
import Swal from "sweetalert2";
import moment from "moment";
import CourseAssignment from "./CourseAssignment.vue";
export default {
    components: {
        CourseAssignment,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
        [DatePicker.name]: DatePicker,
        [TimeSelect.name]: TimeSelect
    },
    data() {
        return {
            disable: true,
            loading: false,

            assigned_course_id: "",
            pressed: false,
            courseAssigneeModal: false,
            employee_id: "",
            company_id: "",
            hot_user: "",
            hot_token: "",
            employee_editing: false,
            individual_editing: false,
            admin_adding: true,
            admin_editing: false,
            employee: {
                userName: "",
                first_name: "",
                last_name: "",
                user_type: "",
                email: "",
                address: "",
                city: "",
                state: "",
                zipcode: "",
                progress: false,
                userstatus: true,
                phone_number: "",
                assigned_location: [],
                assignedParentLocation: "",
                assigned_location_name: "",
                assignedParentLocationName: "",
                job_title: "",
                assigned_classes: "",
                password_genrate: "1",
                password: "",
                access_code: "",
                last_sign_in: "",
                address: "",
                dob: "",
                social_security: ""
            },
            editor: "",
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
            feildType: "password",
            super_admin: false,
            locations: [],
            parentLocations: [],
            companies: [],
            courses: [],

            classes: [],
            location_id: "",
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
            },
            company_name: "",
            job_titles: []
        };
    },
    watch: {
        "employee.email": function() {
            if (this.admin_adding) {
                this.employee.userName = this.employee.email;
            }
        }
    },
    created() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        }
        if (localStorage.getItem("hot-user") === "employee") {
            this.company_id = localStorage.getItem("hot-company-id");
            this.employee_id = localStorage.getItem("hot-user-id");
            this.editor = "employee";
        } else if (localStorage.getItem("hot-user") === "super-admin") {
            this.editor = "super-admin";
        }else if (localStorage.getItem("hot-user") === "sub-admin") {
            this.editor = "sub-admin";
        } else if (
            localStorage.getItem("hot-user") === "company-admin" ||
            localStorage.getItem("hot-user") === "manager"
        ) {
            this.editor = "admin";
            this.company_id = localStorage.getItem("hot-user-id");
            this.childLocationsDropdown(this.company_id);
        }

        this.feildType = this.editor === "super-admin" || this.editor === "sub-admin" ? "text" : "password";
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
        if (this.$route.query.id) {
            this.loading = true;
            this.employee_id = this.$route.query.id;
            this.$http
                .get("employees/get/" + this.employee_id)
                .then(resp => {
                    let data = resp.data[0];

                    if (data.type === "individual") {
                        this.company_id = 0;
                    } else if (data.company[0] != null) {
                        if (data.company[0].parent_id) {
                            this.company_id = data.company[0].parent_id;
                        } else {
                            this.company_id = data.company[0].id;
                        }
                    }

                    let obj = {
                        last_sign_in: data.last_sign_in,
                        userName: data.user_name,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        user_type: data.type,
                        email: data.email,
                        phone_number: data.phone_num,
                        assigned_location: [],
                        assignedParentLocation: "",
                        job_title: data.job_title_id,
                        assigned_classes: data.last_name,
                        address: data.address,
                        dob: data.dob,
                        social_security: data.social_security,
                        city: data.city,
                        state: data.state,
                        zipcode: data.zipcode,
                        userstatus: "",
                        progress: "",
                        access_code: data.access_code
                    };
                    if (data.status === 1) {
                        obj.userstatus = true;
                    } else {
                        obj.userstatus = false;
                    }
                    if (data.progress_status === 1) {
                        obj.progress = true;
                    } else {
                        obj.progress = false;
                    }
                    if (data.type === "individual") {
                        obj.assigned_location = [];
                        obj.assignedParentLocation = "";
                    } else {
                        data.company.forEach(item => {
                            obj.assigned_location.push(item.id);
                            data.companyList.forEach(parent => {
                                if (parent.parent_id != 0) {
                                    obj.assignedParentLocation = parent.parent_id;
                                } else {
                                    obj.assignedParentLocation = parent.id;
                                }
                            });
                        });
                    }
                    this.employee = obj;
                    if (this.company_id == null) {
                        //console.log("this.company_id", this.company_id);
                        this.$http.get("course/all_course").then(resp => {
                            this.courses = resp.data;
                        });
                    } else {
                        this.$http.get("company/courses/" + this.company_id).then(resp => {
                            this.courses = resp.data[0].courses;
                        });
                        this.companyLocations();
                        this.companyDropdown();
                        this.parentLocationsDropdown();
                    }
                })
                .finally(() => (this.loading = false));
        }
        if (this.editor === "admin" || this.editor === "super-admin" || this.editor === "sub-admin") {
            if (this.employee_id !== "") {
                this.admin_editing = true;
                this.admin_adding = false;
                this.employee_editing = false;
            } else {
                this.admin_editing = false;
                this.admin_adding = true;
                this.employee_editing = false;
            }
        } else if (this.editor === "employee") {
            this.loading = true;
            this.admin_editing = false;
            this.admin_adding = false;
            this.employee_editing = true;
            this.$http
                .get("employees/get/" + this.employee_id)
                .then(resp => {
                    let data = resp.data[0];
                    if (data.type === "individual") {
                        this.individual_editing = true;
                        this.company_id = 0;
                    } else if (data.company[0] != null) {
                        this.company_id = data.company[0].id;
                    }
                    let obj = {
                        last_sign_in: data.last_sign_in,
                        userName: data.user_name,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        user_type: data.type,
                        email: data.email,
                        phone_number: data.phone_num,
                        assigned_location: "",
                        assignedParentLocation: "",
                        employee_company_id: data.company_id,
                        job_title: data.job_title_id,
                        assigned_classes: data.last_name,
                        address: data.address,
                        dob: data.dob,
                        social_security: data.social_security,
                        zipcode: data.zipcode,
                        city: data.city,
                        state: data.state,
                        access_code: data.employee_access_code,
                        assigned_location_name: "",
                        assignedParentLocationName: "",
                        job_title_label: ""
                    };
                    if (data.type === "individual") {
                        this.individual_editing = true;
                        obj.assigned_location = "";
                        obj.assignedParentLocation = "";
                    } else {
                        obj.assigned_location = data.company[0].id;
                        data.company.forEach(parent => {
                            if (parent.parent_id != 0) {
                                obj.assignedParentLocation = parent.parent_id;
                            } else {
                                obj.assignedParentLocation = parent.id;
                            }
                        });
                    }
                    let result = this.job_titles.find(
                        ({ value }) => value === data.job_title_id
                    );
                    if (result) {
                        obj.job_title_label = result.label;
                    }

                    this.employee = obj;
                    obj.assigned_location_name = data.company[0].name;
                    data.companyList.forEach(parent => {
                        if (obj.assigned_location == parent.id) {
                            obj.assignedParentLocationName = parent.name;
                        }
                    });
                })
                .finally(() => (this.loading = false));
        } else if (this.editor === "employee") {
            this.admin_editing = true;
            this.admin_adding = false;
            this.employee_editing = false;
        }
        if (this.editor != "super-admin" || this.editor === "sub-admin") {
            if (this.company_id !== null || this.company_id !== "") {
                this.$http.get("company/courses/" + this.company_id).then(resp => {
                    this.company_name = resp.data[0].name;
                    this.courses = resp.data[0].courses;
                    for (let obj of this.courses) {
                        let classObj = {
                            label: obj.name,
                            value: obj.id
                        };
                        this.classes.push(classObj);
                    }
                });
                this.companyLocations();
                this.companyDropdown();
                this.parentLocationsDropdown();
            }
        }
    },
    methods: {
        companyLocations() {
            this.$http
                .post("location/all_company_location", {
                    role: this.editor,
                    employee_id: this.employee_id,
                    company_id: this.company_id
                })
                .then(resp => {
                    this.locations = [];
                    for (let loc of resp.data) {
                        let obj = {
                            label: loc.name,
                            value: loc.id
                        };
                        this.locations.push(obj);
                    }

                    // if (
                    //   this.locations.length === 1 &&
                    //   this.employee.assigned_location.length < 1
                    // ) {
                    //   console.log("1");
                    //   // this.employee.assigned_location = [];
                    //   this.employee.assigned_location.push(this.locations[0].value);
                    // }
                });
        },
        companyDropdown() {
            this.$http.get("company/company_dropdown").then(resp => {
                this.companies = [];
                for (let company of resp.data) {
                    let obj = {
                        label: company.name,
                        value: company.id
                    };
                    this.companies.push(obj);
                }
            });
        },
        parentLocationsDropdown() {
            this.$http.get("company/parent_company_dropdown").then(resp => {
                this.parentLocations = [];
                for (let company of resp.data) {
                    let obj = {
                        label: company.name,
                        value: company.id
                    };
                    this.parentLocations.push(obj);
                }
            });
        },
        childLocationsDropdown(event) {
            var parentLocationId = event;
            if (parentLocationId != "") {
                this.$http
                    .post("company/child_company_dropdown", { id: parentLocationId })
                    .then(resp => {
                        this.locations = [];
                        this.employee.assigned_location = [];
                        for (let company of resp.data) {
                            let obj = {
                                label: company.name,
                                value: company.id
                            };
                            this.locations.push(obj);
                        }
                    });
            }
        },
        acceptNumber() {
            var x = this.employee.phone_number
                .replace(/\D/g, "")
                .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.employee.phone_number = !x[2]
                ? x[1]
                : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
        },
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        showAssigncourse() {
            this.courseAssigneeModal = true;
        },
        getAssignCourseId(event) {
            this.assigned_course_id = event;
        },
        assignCourse() {
            if (this.assigned_course_id !== "") {
                let data = {
                    course_id: this.assigned_course_id,
                    company_id: this.company_id,
                    assign_to: [
                        {
                            employee_ids: [],
                            assign_to: "employee"
                        }
                    ]
                };
                let obj = {
                    id: this.employee_id
                };
                data.assign_to[0].employee_ids.push(obj);
                this.$http
                    .post("course/assign", data)
                    .then(resp => {
                        this.courseAssigneeModal = false;
                        this.assigned_course_id = "";
                        this.$refs.form.refresh();
                        if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
                            Swal.fire({
                                title: "Success!",
                                text: "Course(s) has been Assigned to these Employee",
                                icon: "success"
                            });
                        }else{
                            Swal.fire({
                                title: "Success!",
                                html: '<ul style="text-align: left;"><li>Course(s) Assigned: '+ resp.data.assigned +'</li><li>Course(s) In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course(s) Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                                icon: "success"
                            });
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            title: "Error!",
                            html: error.response.data.message,
                            icon: "error"
                        });
                    });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        addUser() {
            if (
                (this.editor === "admin" || this.editor === "super-admin" ||  this.editor === "sub-admin") &&
                this.employee.assigned_location.length == 0 &&
                this.employee.user_type != "individual"
            ) {
                return Swal.fire({
                    title: "Error!",
                    text: `Please assign location to user.`,
                    icon: "error"
                });
            }
            this.pressed = true;

            this.loading = true;
            let user_status = "";
            user_status = this.employee.userstatus ? 1 : 0;
            let progress_status = "";
            progress_status = this.employee.progress ? 1 : 0;
            if (this.admin_adding) {
                let query;
                let data = {
                    employee_first_name: this.employee.first_name,
                    employee_last_name: this.employee.last_name,
                    employee_job_title_id: this.employee.job_title,
                    user_type: this.employee.user_type,
                    employee_address: this.employee.address,
                    employee_status: user_status,
                    employee_city: this.employee.city,
                    employee_state: this.employee.state,
                    employee_zipcode: this.employee.zipcode,
                    employee_progress: progress_status,
                    employee_email: this.employee.email,
                    employee_username: this.employee.userName,
                    employee_phone_num: this.employee.phone_number,
                    employee_company_id: this.company_id,
                    employee_location_id: this.employee.assigned_location,
                    employee_course: this.employee.assigned_classes,
                    password: this.employee.password,
                    address: this.employee.address,
                    social_security: this.employee.social_security,
                    dob: this.employee.dob
                };
                query = data;
                this.$http
                    .post("employees/register", query)
                    .then(resp => {
                        Swal.fire({
                            title: "Success!",
                            html: resp.data.message,
                            icon: "success"
                        });
                        if (this.editor === "super-admin" || this.editor === "sub-admin") {
                            this.$router.push("/all_users");
                        } else {
                            this.$router.push("/company_employees");
                        }
                    })
                    .catch(function(error) {
                        if (error.response.status === 422) {
                            Swal.fire({
                                title: "Error!",
                                html: error.response.data.message,
                                icon: "error"
                            });
                        }
                    })
                    .finally(() => (this.loading = false));
            } else if (this.admin_editing) {
                let data = {
                    employee_first_name: this.employee.first_name,
                    employee_last_name: this.employee.last_name,
                    employee_job_title: this.employee.job_title,
                    employee_status: user_status,
                    employee_type: this.employee.user_type,
                    employee_email: this.employee.email,
                    employee_phone_num: this.employee.phone_number,
                    employee_user_name: this.employee.userName,
                    employee_location_id: this.employee.assigned_location,
                    employee_access_code: this.employee.access_code,
                    employee_address: this.employee.address,
                    employee_city: this.employee.city,
                    employee_state: this.employee.state,
                    employee_zipcode: this.employee.zipcode,
                    employee_progress: progress_status,

                    employee_soical_security: this.employee.social_security,
                    employee_dob: this.employee.dob
                };
                this.$http
                    .put("employees/update/" + this.employee_id, data)
                    .then(resp => {
                        Swal.fire({
                            title: "Success!",
                            text: `Employee has been Updated!`,
                            icon: "success"
                        });

                        this.pressed = false;
                        if (this.editor === "super-admin" || this.editor === "sub-admin") {
                            this.$router.push("/all_users");
                        } else {
                            this.$router.push("/company_employees");
                        }
                    })
                    .catch(function(error) {
                        if (error.response.status === 422) {
                            Swal.fire({
                                title: "Error!",
                                text: error.response.data.employee,
                                icon: "error"
                            });
                        }
                    })
                    .finally(() => (this.loading = false));
            } else if (this.employee_editing) {
                if (this.employee.userName !== "" && this.employee.access_code !== "") {
                    let data = {
                        employee_first_name: this.employee.first_name,
                        employee_last_name: this.employee.last_name,
                        employee_job_title: this.employee.job_title,
                        employee_type: this.employee.user_type,
                        employee_email: this.employee.email,
                        employee_phone_num: this.employee.phone_number,
                        employee_user_name: this.employee.userName,
                        employee_location_id: this.employee.assigned_location,
                        employee_access_code: this.employee.access_code,
                        employee_city: this.employee.city,
                        employee_soical_security: this.employee.social_security,
                        employee_address: this.employee.address,
                        employee_state: this.employee.state,
                        employee_zipcode: this.employee.zipcode,
                        employee_dob: this.employee.dob
                    };
                    this.$http
                        .put("employees/update/" + this.employee_id, data)
                        .then(resp => {
                            this.pressed = false;
                            Swal.fire({
                                title: "Success!",
                                text: `Your Account has been Updated!`,
                                icon: "success"
                            });
                        })
                        .finally(() => (this.loading = false));
                } else {
                    this.loading = false;
                    Swal.fire({
                        title: "Error!",
                        text: `UserName/ Password is required!`,
                        icon: "error"
                    });
                }
            }
        }
    }
};
</script>
<style>
.el-select-dropdown__list {
    padding: 6px !important;
}

@media only screen and (min-width: 280px) and (max-width: 410px) {
    .el-select-dropdown {
        left: 0 !important;
        right: 0 !important;
    }
}

@media only screen and (min-width: 411px) and (max-width: 539px) {
    .el-select-dropdown {
        left: 8px !important;
    }
}
@media only screen and (min-width: 540px) and (max-width: 767px) {
    .el-select-dropdown {
        left: 30px !important;
    }
}
@media only screen and (min-width: 768px) and (max-width: 1023px) {
    .el-select-dropdown {
        left: 158px !important;
    }
}
@media only screen and (min-width: 1024px) and (max-width: 1279px) {
    .el-select-dropdown {
        left: 284px !important;
    }
}
@media only screen and (min-width: 1280px) and (max-width: 1366px) {
    .el-select-dropdown {
        left: 415px !important;
    }
}
</style>
<template>
    <div class="content" v-loading.fullscreen.lock="loading">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <card class="no-border-card" footer-classes="pb-2">
                <template slot="header">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-12">
                            <h2 class="mb-0" v-if="!admin_adding">Edit User</h2>
                            <h2 class="mb-0" v-else>Add User</h2>
                        </div>
                        <div class="col-lg-6 col-12 text-right">
                            <h5>
                                <span class="requireField">*</span> Indicates a required field.
                            </h5>
                        </div>
                    </div>
                </template>
                <div class="row mb-3" v-if="!admin_adding">
                    <div class="col-md-6">
                        <h3 class="">
                            Last Sign in:
                            <span style="color: #00ccff" v-if="employee.last_sign_in">{{
                                    formattedDate(employee.last_sign_in)
                                }}</span
                            ><span style="color: #00ccff" v-else>Never</span>
                        </h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <h3>
                            Type:
                            <span
                                style="color: #00ccff"
                                v-if="employee.user_type === 'individual'"
                            >Individual</span
                            >
                            <span style="color: #00ccff" v-else>Company</span>
                        </h3>
                    </div>
                </div>
                <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
                    <form class="" @submit.prevent="handleSubmit(addUser)">
                        <h5>
                            <span style="text-decoration:underline;">Required fields:</span>
                        </h5>
                        <div class="row">
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label"
                                >First Name <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    name="first name"
                                    rules="required"
                                    placeholder="First Name"
                                    v-model="employee.first_name"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label"
                                >Last Name <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    name="last name"
                                    rules="required"
                                    placeholder="Last Name"
                                    v-model="employee.last_name"
                                >
                                </base-input>
                            </div>

                            <div class="col-md-4" v-if="employee_editing">
                                <label class="form-control-label">Email Address</label>
                                <base-input
                                    type="email"
                                    label=""
                                    name="Email Address"
                                    placeholder="Email"
                                    v-model="employee.email"
                                    :class="admin_adding ? 'admin_edit' : 'employee_edit_email'"
                                    @keyup="get_employee_email"
                                >
                                </base-input>
                            </div>

                            <div class="col-md-2" v-if="!employee_editing">
                                <label class="form-control-label"
                                >Email Address
                                <span class="requireField"
                                    v-if="employee.user_type == 'admin' || employee.user_type == 'location_manager'"
                                >
                                *</span
                                ></label>
                                <base-input
                                    rules="required"
                                    type="email"
                                    label=""
                                    name="Email Address"
                                    placeholder="Email"
                                    v-model="employee.email"
                                >
                                </base-input>
                            </div>


                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="!employee_editing && employee.user_type != 'individual'"
                            >
                                <label class="form-control-label"
                                >User Type <span class="requireField">*</span></label
                                >
                                <br />
                                <el-select
                                    class="mr-3"
                                    name="User Type"
                                    style="width: 100%"
                                    placeholder="Select Type"
                                    v-model="employee.user_type"
                                >
                                    <el-option
                                        rules="required"
                                        v-for="(option, index) in user_types"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'user_type_' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                           

                            
                            <div class="col-md-4" v-if="employee_editing">
                                <label class="form-control-label"
                                >Username <span class="requireField">*</span></label
                                >
                                <base-input
                                    type="text"
                                    placeholder="Username"
                                    v-model="employee.userName"
                                >
                                </base-input>
                                <span
                                    class="text-danger"
                                    v-if="pressed && employee.userName.length <= 0"
                                >Username is required</span
                                >
                            </div>
                             <div v-if="employee_editing" class='col-md-3'>
                                <base-input
                                    type="text"
                                    label="User Type"
                                    placeholder="Select User Type"
                                    :disabled="disable"
                                    v-model="employee.user_type"
                                >
                                </base-input>
                            </div>
                            <div class="col-md-2" v-if="!employee_editing">
                                <label class="form-control-label">Username </label>
                                <base-input
                                    type="text"
                                    placeholder="Username"
                                    v-model="employee.userName"
                                >
                                </base-input>
                            </div>
                        </div>
                        <div class="row">
                            <div
                                :class="admin_adding ? 'col-md-6' : 'col-md-6'"
                                v-if="editor == 'super-admin' || editor == 'sub-admin'"
                            >
                                <label class="form-control-label">Parent Location </label>

                                <br />
                                <el-select
                                    filterable
                                    class="mr-3"
                                    style="width: 100%"
                                    @change="childLocationsDropdown($event)"
                                    v-model="employee.assignedParentLocation"
                                >
                                    <el-option
                                        v-for="(option, index) in parentLocations"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'location_' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div
                                :class="admin_adding ? 'col-md-6' : 'col-md-6'"
                                v-if="!individual_editing"
                            >
                                <div
                                    v-if="!employee_editing "
                                >
                                    <label class="form-control-label"
                                    >Assigned Location
                                        <span class="requireField" v-if="employee.user_type != 'individual'">*</span></label
                                    >

                                    <br />
                                    <el-select
                                        v-if="employee.user_type == 'employee'"
                                        multiple
                                        filterable
                                        class="mr-3"
                                        style="width: 100%"
                                        v-model="employee.assigned_location"
                                    >
                                        <el-option
                                            :disabled="employee.assigned_location.length >= 1"
                                            v-for="(option, index) in locations"
                                            class="select-primary"
                                            :value="option.value"
                                            :label="option.label"
                                            :key="'location_' + index"
                                        >
                                        </el-option>
                                    </el-select>
                                    <el-select
                                        v-else
                                        multiple
                                        filterable
                                        class="mr-3"
                                        style="width: 100%"
                                        v-model="employee.assigned_location"
                                    >
                                        <el-option
                                            v-for="(option, index) in locations"
                                            class="select-primary"
                                            :value="option.value"
                                            :label="option.label"
                                            :key="'location_' + index"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div v-if="employee_editing">
                                    <base-input
                                        type="text"
                                        label="Assigned Location"
                                        placeholder="Assigned Location"
                                        :disabled="disable"
                                        v-model="employee.assigned_location_name"
                                    >
                                    </base-input>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="admin_adding">
                                <el-popover
                                    ref="fromPopOver"
                                    placement="top-start"
                                    width="250"
                                    trigger="hover"
                                >
                  <span style="display: flex; justify-content: center">
                    You can search and select multiple courses to assign.
                  </span>
                                </el-popover>
                                <label style="color: #444c57" class=""
                                >Assigned Courses
                                    <i v-popover:fromPopOver class="el-icon-question text-blue" />
                                </label>
                                <br />
                                <el-select
                                    multiple
                                    filterable
                                    class="mr-3"
                                    style="width: 100%"
                                    placeholder="Select Course"
                                    v-model="employee.assigned_classes"
                                >
                                    <el-option
                                        v-for="(option, index) in classes"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'test_question' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3" v-if="employee_editing" style="position: relative;">
                                <base-input
                                    :type="feildType"
                                    label="Password"
                                    placeholder="Password"
                                    v-model="employee.access_code"
                                >
                                </base-input>
                                <span class="hideshowpass" @click="show_hide_password()">
                                    <i :class="hideshowclass" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div class="col-md-3" v-if="!employee_editing">
                                <base-input
                                    :type="feildType"
                                    label="Password"
                                    placeholder="Password"
                                    v-model="employee.access_code"
                                >
                                </base-input>
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-3 mt-4' : 'col-md-3'"
                                v-if="admin_adding"
                            >
                                <base-checkbox v-model="employee.password_genrate"
                                >Auto Generate password for this user</base-checkbox
                                >
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-3 mt-2' : 'col-md-3'"
                                v-if="admin_adding && !employee.password_genrate"
                            >
                                <base-input
                                    type="password"
                                    label="Password"
                                    Placeholder="Enter Password"
                                    v-model="employee.password"
                                ></base-input>
                            </div>

                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="
                  !employee_editing &&
                    (employee.user_type == 'admin' ||
                      employee.user_type == 'location_manager')
                "
                            >
                                <label class="form-control-label">Progress Report</label>
                                <div class="d-flex">
                                    <base-switch
                                        class="mr-1"
                                        type="success"
                                        v-model="employee.progress"
                                    ></base-switch>
                                </div>
                            </div>
                            <div
                                :class="admin_adding ? 'col-md-2' : 'col-md-2'"
                                v-if="!employee_editing"
                            >
                                <label class="form-control-label">User Status</label>
                                <div class="d-flex">
                                    <base-switch
                                        class="mr-1"
                                        type="success"
                                        v-model="employee.userstatus"
                                    ></base-switch>
                                </div>
                            </div>
                        </div>
                        <hr />
                        <h5>
              <span style="text-decoration: underline"
              >Non-Required fields:</span
              >
                        </h5>
                        <div class="row">
                            <div
                                :class="admin_adding ? 'col-md-3' : 'col-md-3'"
                                v-if="!individual_editing"
                            >
                                <div v-if="!employee_editing">
                                    <label class="form-control-label">Employee Job Title</label>
                                    <br />
                                    <el-select
                                        class="mr-3"
                                        style="width: 100%"
                                        placeholder="Select Title"
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
                                </div>
                                <div v-if="employee_editing">
                                    <base-input
                                        type="text"
                                        label="Job Title"
                                        placeholder="Job Title"
                                        :disabled="disable"
                                        v-model="employee.job_title_label"
                                    >
                                    </base-input>
                                </div>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="Phone Number"
                                    name="phone number"
                                    placeholder="(555) 555-5555"
                                    v-model="employee.phone_number"
                                    @input="acceptNumber"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="Address"
                                    name="Address"
                                    placeholder="Address"
                                    v-model="employee.address"
                                ></base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="City"
                                    name="City"
                                    placeholder="City"
                                    v-model="employee.city"
                                >
                                </base-input>
                            </div>
                        </div>
                        <div class="row">
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="text"
                                    label="State"
                                    name="State"
                                    placeholder="State"
                                    v-model="employee.state"
                                >
                                </base-input>
                            </div>
                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <base-input
                                    type="number"
                                    label="Zip Code"
                                    name="Zip code"
                                    placeholder="Zip Code"
                                    v-model="employee.zipcode"
                                >
                                </base-input>
                            </div>

                            <div :class="admin_adding ? 'col-md-3' : 'col-md-3'">
                                <label class="form-control-label">Date of birth</label>
                                <el-date-picker
                                    v-model="employee.dob"
                                    placeholder="Pick a day"
                                    style="width: 100%"
                                    format="MM/dd/yyyy"
                                    :picker-options="pickerOptions1"
                                >
                                </el-date-picker>
                            </div>
                            <div
                                v-if="editor == 'super-admin' || editor == 'sub-admin'"
                                :class="admin_adding ? 'col-md-3' : 'col-md-3'"
                            >
                                <base-input
                                    type="text"
                                    label="Social Security Number"
                                    name="Social Security Number"
                                    placeholder="Social Security"
                                    v-model="employee.social_security"
                                ></base-input>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <router-link
                                :hide="editor === 'employee'"
                                :to="
                  editor === 'super-admin' || editor === 'sub-admin'
                    ? '/all_users'
                    : editor === 'admin'
                    ? '/company_employees'
                    : '/dashboard'
                "
                            >
                                <base-button  type="danger" class="custom-btn mr-3">
                                    Cancel
                                </base-button>
                            </router-link>
                            <base-button  :name="admin_adding ? 'Add User ' : 'Update Profile'" class="custom-btn" native-type="submit">
                                {{ admin_adding ? "Submit" : "Update" }}
                            </base-button>
                        </div>
                    </form>
                </validation-observer>
            </card>

            <card
                class="no-border-card"
                footer-classes="pb-2"
                v-if="!employee_editing && !admin_adding"
            >
                <template slot="header">
                    <div class="row align-items-center" v-if="!admin_adding">
                        <div class="col-md-6">
                            <h2 class="mb-0">Employee Courses</h2>
                        </div>
                        <div class="col-sm-6 text-right" v-if="!employee_editing">
                            <label></label>
                            <base-button
                                @click.prevent="showAssigncourse()"
                                class="custom-btn"
                            >
                                Assign course
                            </base-button>
                        </div>
                    </div>
                </template>
                <div v-if="!admin_adding">
                    <div>
                        <div class="row" v-if="admin_adding">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <label style="color: #444c57" class="">Assigned Courses </label>

                                <br />
                                <el-select
                                    class="mr-3"
                                    style="width: 100%"
                                    placeholder="Select Course"
                                    v-model="employee.assigned_classes"
                                >
                                    <el-option
                                        v-for="(option, index) in classes"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="'test_question' + index"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <course-assignment
                            :employee_id="this.employee_id"
                            ref="form"
                        ></course-assignment>
                    </div>
                </div>
            </card>
        </div>
        <modal :show.sync="courseAssigneeModal">
            <h3 slot="header" style="color: #444c57" class="modal-title">
                Assign Course
            </h3>
            <form>
                <div class="row">
                    <div class="col-sm-12">
                        <label class="form-control-label"
                        >Select any Course to Assign
                        </label>
                    </div>
                    <div class="col-sm-12">
                        <el-select
                            multiple
                            filterable
                            class="company_dropdown2 w-100"
                            v-model="assigned_course_id"
                            @change="getAssignCourseId($event)"
                            placeholder="Select Course"
                        >
                            <el-option
                                class="select-default"
                                v-for="(course, index) in courses"
                                :key="index"
                                :label="course.name"
                                :value="course.id"
                            >
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="pt-2 mt-2 text-right">
                    <base-button
                        type="danger"
                        class="custom-btn mr-3"
                        @click.prevent="courseAssigneeModal = false"
                    >
                        Cancel
                    </base-button>
                    <base-button class="custom-btn" @click.prevent="assignCourse">
                        {{ "Assign Course" }}
                    </base-button>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
    </div>
</template>
<script>
import {
    DatePicker,
    TimeSelect,
    Table,
    TableColumn,
    Select,
    Option
} from "element-ui";
import Swal from "sweetalert2";
import moment from "moment";
import CourseAssignment from "./CourseAssignment.vue";
export default {
    components: {
        CourseAssignment,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
        [DatePicker.name]: DatePicker,
        [TimeSelect.name]: TimeSelect
    },
    data() {
        return {
            disable: true,
            loading: false,

            hideshowclass: "fa fa-eye",
            assigned_course_id: "",
            pressed: false,
            courseAssigneeModal: false,
            employee_id: "",
            company_id: "",
            hot_user: "",
            hot_token: "",
            employee_editing: false,
            individual_editing: false,
            admin_adding: true,
            admin_editing: false,
            employee: {
                userName: "",
                first_name: "",
                last_name: "",
                user_type: "",
                email: "",
                address: "",
                city: "",
                state: "",
                zipcode: "",
                progress: false,
                userstatus: true,
                phone_number: "",
                assigned_location: [],
                assignedParentLocation: "",
                assigned_location_name: "",
                assignedParentLocationName: "",
                job_title: "",
                assigned_classes: "",
                password_genrate: "1",
                password: "",
                access_code: "",
                last_sign_in: "",
                address: "",
                dob: "",
                social_security: ""
            },
            editor: "",
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
            feildType: "password",
            super_admin: false,
            locations: [],
            parentLocations: [],
            companies: [],
            courses: [],

            classes: [],
            location_id: "",
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
            },
            company_name: "",
            job_titles: []
        };
    },
    watch: {
        "employee.email": function() {
            if (this.admin_adding) {
                this.employee.userName = this.employee.email;
            }
        }
    },
    created() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        }
        if (localStorage.getItem("hot-user") === "employee") {
            this.company_id = localStorage.getItem("hot-company-id");
            this.employee_id = localStorage.getItem("hot-user-id");
            this.editor = "employee";
        } else if (localStorage.getItem("hot-user") === "super-admin") {
            this.editor = "super-admin";
        }else if (localStorage.getItem("hot-user") === "sub-admin") {
            this.editor = "sub-admin";
        } else if (
            localStorage.getItem("hot-user") === "company-admin" ||
            localStorage.getItem("hot-user") === "manager"
        ) {
            this.editor = "admin";
            this.company_id = localStorage.getItem("hot-user-id");
            this.childLocationsDropdown(this.company_id);
        }

        this.feildType = this.editor === "super-admin" || this.editor === "sub-admin" ? "text" : "password";
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
        if (this.$route.query.id) {
            this.loading = true;
            this.employee_id = this.$route.query.id;
            this.$http
                .get("employees/get/" + this.employee_id)
                .then(resp => {
                    let data = resp.data[0];

                    if (data.type === "individual") {
                        this.company_id = 0;
                    } else if (data.company[0] != null) {
                        if (data.company[0].parent_id) {
                            this.company_id = data.company[0].parent_id;
                        } else {
                            this.company_id = data.company[0].id;
                        }
                    }
                    let obj = {
                        last_sign_in: data.last_sign_in,
                        userName: data.user_name,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        user_type: data.type,
                        email: data.email,
                        phone_number: data.phone_num,
                        assigned_location: [],
                        assignedParentLocation: "",
                        job_title: data.job_title_id,
                        assigned_classes: data.last_name,
                        address: data.address,
                        dob: data.dob,
                        social_security: data.social_security,
                        city: data.city,
                        state: data.state,
                        zipcode: data.zipcode,
                        userstatus: "",
                        progress: "",
                        access_code: data.access_code
                    };
                    if (data.status === 1) {
                        obj.userstatus = true;
                    } else {
                        obj.userstatus = false;
                    }
                    if (data.progress_status === 1) {
                        obj.progress = true;
                    } else {
                        obj.progress = false;
                    }
                    if (data.type === "individual") {
                        obj.assigned_location = [];
                        obj.assignedParentLocation = "";
                    } else {
                        data.company.forEach(item => {
                            obj.assigned_location.push(item.id);
                            data.companyList.forEach(parent => {
                                if (parent.parent_id != 0) {
                                    obj.assignedParentLocation = parent.parent_id;
                                } else {
                                    obj.assignedParentLocation = parent.id;
                                }
                            });
                        });
                    }

                    this.employee = obj;
                    if (this.company_id == null) {
                        //console.log("this.company_id", this.company_id);
                        this.$http.get("course/all_course").then(resp => {
                            this.courses = resp.data;
                        });
                    } else {
                        this.$http.get("company/courses/" + this.company_id).then(resp => {
                            this.courses = resp.data[0].courses;
                        });
                        this.companyLocations();
                        this.companyDropdown();
                        this.parentLocationsDropdown();
                    }
                })
                .finally(() => (this.loading = false));
        }
        if (this.editor === "admin" || this.editor === "super-admin" || this.editor === "sub-admin") {
            if (this.employee_id !== "") {
                this.admin_editing = true;
                this.admin_adding = false;
                this.employee_editing = false;
            } else {
                this.admin_editing = false;
                this.admin_adding = true;
                this.employee_editing = false;
            }
        } else if (this.editor === "employee") {
            this.loading = true;
            this.admin_editing = false;
            this.admin_adding = false;
            this.employee_editing = true;
            this.$http
                .get("employees/get/" + this.employee_id)
                .then(resp => {
                    let data = resp.data[0];

                    if (data.type === "individual") {
                        this.individual_editing = true;
                        this.company_id = 0;
                    } else if (data.company[0] != null) {
                        this.company_id = data.company[0].id;
                    }
                    let obj = {
                        last_sign_in: data.last_sign_in,
                        userName: data.user_name,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        user_type: data.type,
                        email: data.email,
                        phone_number: data.phone_num,
                        assigned_location: "",
                        assignedParentLocation: "",
                        employee_company_id: data.company_id,
                        job_title: data.job_title_id,
                        assigned_classes: data.last_name,
                        address: data.address,
                        dob: data.dob,
                        social_security: data.social_security,
                        zipcode: data.zipcode,
                        city: data.city,
                        state: data.state,
                        access_code: data.access_code,
                        assigned_location_name: "",
                        assignedParentLocationName: "",
                        job_title_label: ""
                    };
                    if (data.type === "individual") {
                        this.individual_editing = true;
                        obj.assigned_location = "";
                        obj.assignedParentLocation = "";
                    } else {
                        obj.assigned_location = data.company[0].id;
                        data.company.forEach(parent => {
                            if (parent.parent_id != 0) {
                                obj.assignedParentLocation = parent.parent_id;
                            } else {
                                obj.assignedParentLocation = parent.id;
                            }
                        });
                    }
                    let result = this.job_titles.find(
                        ({ value }) => value === data.job_title_id
                    );
                    if (result) {
                        obj.job_title_label = result.label;
                    }
                   
                    this.employee = obj;
                    obj.assigned_location_name = data.company[0].name;
                    data.companyList.forEach(parent => {
                        if (obj.assigned_location == parent.id) {
                            obj.assignedParentLocationName = parent.name;
                        }
                    });
                })
                .finally(() => (this.loading = false));
        } else if (this.editor === "employee") {
            this.admin_editing = true;
            this.admin_adding = false;
            this.employee_editing = false;
        }
        if (this.editor != "super-admin" || this.editor === "sub-admin") {
            if (this.company_id !== null || this.company_id !== "") {
                this.$http.get("company/courses/" + this.company_id).then(resp => {
                    this.company_name = resp.data[0].name;
                    this.courses = resp.data[0].courses;
                    for (let obj of this.courses) {
                        let classObj = {
                            label: obj.name,
                            value: obj.id
                        };
                        this.classes.push(classObj);
                    }
                });
                this.companyLocations();
                this.companyDropdown();
                this.parentLocationsDropdown();
            }
        }
    },
    methods: {

        show_hide_password() {
          if(this.feildType == 'password')
          {
            this.hideshowclass = 'fa fa-eye-slash';
            this.feildType = 'text';
          }
          else if(this.feildType == 'text'){
            this.hideshowclass = 'fa fa-eye';
            this.feildType = 'password';
          }
        },
        get_employee_email(event) {
            if (this.editor == "employee")
            {
                this.employee.userName = event.target.value;
            }
        },
        companyLocations() {
            this.$http
                .post("location/all_company_location", {
                    role: this.editor,
                    employee_id: this.employee_id,
                    company_id: this.company_id
                })
                .then(resp => {
                    this.locations = [];
                    for (let loc of resp.data) {
                        let obj = {
                            label: loc.name,
                            value: loc.id
                        };
                        this.locations.push(obj);
                    }

                    // if (
                    //   this.locations.length === 1 &&
                    //   this.employee.assigned_location.length < 1
                    // ) {
                    //   console.log("1");
                    //   // this.employee.assigned_location = [];
                    //   this.employee.assigned_location.push(this.locations[0].value);
                    // }
                });
        },
        companyDropdown() {
            this.$http.get("company/company_dropdown").then(resp => {
                this.companies = [];
                for (let company of resp.data) {
                    let obj = {
                        label: company.name,
                        value: company.id
                    };
                    this.companies.push(obj);
                }
            });
        },
        parentLocationsDropdown() {
            this.$http.get("company/parent_company_dropdown").then(resp => {
                this.parentLocations = [];
                for (let company of resp.data) {
                    let obj = {
                        label: company.name,
                        value: company.id
                    };
                    this.parentLocations.push(obj);
                }
            });
        },
        childLocationsDropdown(event) {
            var parentLocationId = event;
            if (parentLocationId != "") {
                this.$http
                    .post("company/child_company_dropdown", { id: parentLocationId })
                    .then(resp => {
                        this.locations = [];
                        this.employee.assigned_location = [];
                        for (let company of resp.data) {
                            let obj = {
                                label: company.name,
                                value: company.id
                            };
                            this.locations.push(obj);
                        }
                    });
            }
        },
        acceptNumber() {
            var x = this.employee.phone_number
                .replace(/\D/g, "")
                .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.employee.phone_number = !x[2]
                ? x[1]
                : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
        },
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        showAssigncourse() {
            this.courseAssigneeModal = true;
        },
        getAssignCourseId(event) {
            this.assigned_course_id = event;
        },
        assignCourse() {
            if (this.assigned_course_id !== "") {
                let data = {
                    course_id: this.assigned_course_id,
                    company_id: this.company_id,
                    assign_to: [
                        {
                            employee_ids: [],
                            assign_to: "employee"
                        }
                    ]
                };
                let obj = {
                    id: this.employee_id
                };
                data.assign_to[0].employee_ids.push(obj);
                this.$http
                    .post("course/assign", data)
                    .then(resp => {
                        this.courseAssigneeModal = false;
                        this.assigned_course_id = "";
                        this.$refs.form.refresh();
                        if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
                            Swal.fire({
                                title: "Success!",
                                text: "Course(s) has been Assigned to these Employee",
                                icon: "success"
                            });
                        }else{
                            Swal.fire({
                                title: "Success!",
                                html: '<ul style="text-align: left;"><li>Course(s) Assigned: '+ resp.data.assigned +'</li><li>Course(s) In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course(s) Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                                icon: "success"
                            });
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            title: "Error!",
                            html: error.response.data.message,
                            icon: "error"
                        });
                    });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        addUser() {
            if (
                (this.editor === "admin" || this.editor === "super-admin" ||  this.editor === "sub-admin") &&
                this.employee.assigned_location.length == 0 &&
                this.employee.user_type != "individual"
            ) {
                return Swal.fire({
                    title: "Error!",
                    text: `Please assign location to user.`,
                    icon: "error"
                });
            }
            this.pressed = true;

            this.loading = true;
            let user_status = "";
            user_status = this.employee.userstatus ? 1 : 0;
            let progress_status = "";
            progress_status = this.employee.progress ? 1 : 0;
            if (this.admin_adding) {
                let query;
                let data = {
                    employee_first_name: this.employee.first_name,
                    employee_last_name: this.employee.last_name,
                    employee_job_title_id: this.employee.job_title,
                    user_type: this.employee.user_type,
                    employee_address: this.employee.address,
                    employee_status: user_status,
                    employee_city: this.employee.city,
                    employee_state: this.employee.state,
                    employee_zipcode: this.employee.zipcode,
                    employee_progress: progress_status,
                    employee_email: this.employee.email,
                    employee_username: this.employee.userName,
                    employee_phone_num: this.employee.phone_number,
                    employee_company_id: this.company_id,
                    employee_location_id: this.employee.assigned_location,
                    employee_course: this.employee.assigned_classes,
                    password: this.employee.password,
                    address: this.employee.address,
                    social_security: this.employee.social_security,
                    dob: this.employee.dob
                };
                query = data;
                this.$http
                    .post("employees/register", query)
                    .then(resp => {
                        Swal.fire({
                            title: "Success!",
                            html: resp.data.message,
                            icon: "success"
                        });
                        if (this.editor === "super-admin" || this.editor === "sub-admin") {
                            this.$router.push("/all_users");
                        } else {
                            this.$router.push("/company_employees");
                        }
                    })
                    .catch(function(error) {
                        if (error.response.status === 422) {
                            Swal.fire({
                                title: "Error!",
                                html: error.response.data.message,
                                icon: "error"
                            });
                        }
                    })
                    .finally(() => (this.loading = false));
            } else if (this.admin_editing) {
                let data = {
                    employee_first_name: this.employee.first_name,
                    employee_last_name: this.employee.last_name,
                    employee_job_title: this.employee.job_title,
                    employee_status: user_status,
                    employee_type: this.employee.user_type,
                    employee_email: this.employee.email,
                    employee_phone_num: this.employee.phone_number,
                    employee_user_name: this.employee.userName,
                    employee_location_id: this.employee.assigned_location,
                    employee_access_code: this.employee.access_code,
                    employee_address: this.employee.address,
                    employee_city: this.employee.city,
                    employee_state: this.employee.state,
                    employee_zipcode: this.employee.zipcode,
                    employee_progress: progress_status,

                    employee_soical_security: this.employee.social_security,
                    employee_dob: this.employee.dob
                };
                this.$http
                    .put("employees/update/" + this.employee_id, data)
                    .then(resp => {
                        Swal.fire({
                            title: "Success!",
                            text: `Employee has been Updated!`,
                            icon: "success"
                        });

                        this.pressed = false;
                        if (this.editor === "super-admin" || this.editor === "sub-admin") {
                            this.$router.push("/all_users");
                        } else {
                            this.$router.push("/company_employees");
                        }
                    })
                    .catch(function(error) {
                        if (error.response.status === 422) {
                            Swal.fire({
                                title: "Error!",
                                text: error.response.data.employee,
                                icon: "error"
                            });
                        }
                    })
                    .finally(() => (this.loading = false));
            } else if (this.employee_editing) {
                if (this.employee.userName !== "" && this.employee.access_code !== "") {
                    let data = {
                        employee_first_name: this.employee.first_name,
                        employee_last_name: this.employee.last_name,
                        employee_job_title: this.employee.job_title,
                        employee_type: this.employee.user_type,
                        employee_email: this.employee.email,
                        employee_phone_num: this.employee.phone_number,
                        employee_user_name: this.employee.userName,
                        employee_location_id: this.employee.assigned_location,
                        employee_access_code: this.employee.access_code,
                        employee_city: this.employee.city,
                        employee_soical_security: this.employee.social_security,
                        employee_address: this.employee.address,
                        employee_state: this.employee.state,
                        employee_zipcode: this.employee.zipcode,
                        employee_dob: this.employee.dob
                    };
                    this.$http
                        .put("employees/update/" + this.employee_id, data)
                        .then(resp => {
                            this.pressed = false;
                            Swal.fire({
                                title: "Success!",
                                text: `Your Account has been Updated!`,
                                icon: "success"
                            });
                        })
                        .finally(() => (this.loading = false));
                } else {
                    this.loading = false;
                    Swal.fire({
                        title: "Error!",
                        text: `UserName/ Password is required!`,
                        icon: "error"
                    });
                }
            }
        }
    }
};
</script>
<style>
.el-select-dropdown__list {
    padding: 6px !important;
}

@media only screen and (min-width: 280px) and (max-width: 410px) {
    .el-select-dropdown {
        left: 0 !important;
        right: 0 !important;
    }
}

@media only screen and (min-width: 411px) and (max-width: 539px) {
    .el-select-dropdown {
        left: 8px !important;
    }
}
@media only screen and (min-width: 540px) and (max-width: 767px) {
    .el-select-dropdown {
        left: 30px !important;
    }
}
@media only screen and (min-width: 768px) and (max-width: 1023px) {
    .el-select-dropdown {
        left: 158px !important;
    }
}
@media only screen and (min-width: 1024px) and (max-width: 1279px) {
    .el-select-dropdown {
        left: 284px !important;
    }
}
@media only screen and (min-width: 1280px) and (max-width: 1366px) {
    .el-select-dropdown {
        left: 415px !important;
    }
}
span.hideshowpass {
    position: absolute;
    top: 39px;
    right: 22px;
    cursor: pointer;
}
</style>
