<template>
    <div class="content" v-loading.fullscreen.lock="loading">
        <base-header class="pb-6">
            <div class="row align-items-center py-2"></div>
        </base-header>
        <div class="container-fluid mt--6">
            <div>
                <card class="no-border-card" body-classes="" footer-classes="pb-2">
                    <template slot="header">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-left">
                                <h2 class="mb-0">My Courses</h2>
                            </div>
                        </div>
                    </template>
 
                    <div>
                        
                        <div
                            class="col-12 d-flex justify-content-center justify-content-sm-between flex-wrap"
                        ></div>
                        <div>
                            <div
                                class="user-eltable mb-3"
                                v-if="employeeStatus === 'open' || !this.$route.query.course"
                            >
                                <h3 style="padding:10px;">
                                    Open Courses
                                </h3>

                                <el-table
                                    :data="queriedData.filter(data => data.course_status === 2)"
                                    row-key="id"
                                    role="table"
                                    class="empcoursesGrid"
                                    header-row-class-name="thead-light custom-thead-light"
                                    @sort-change="sortChange"
                                    @selection-change="selectionChange"
                                >
                                    <el-table-column min-width="220px" label="Course Name">
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Due Date">
                                        <template slot-scope="props">
                      <span
                          v-if="
                          currentDate(props.row.due_date) &&
                            props.row.course_status != '1'
                        "
                          style="color:red;"
                      ><b>{{ formattedDate(props.row.due_date) }}</b></span
                      >
                                            <span v-else>{{
                                                    formattedDate(props.row.due_date)
                                                }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Lesson Status">
                                        <template slot-scope="">
                      <span
                          type="warning"
                          style="color: #ffd600;"
                      ><b>Open</b></span
                      >
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                        min-width="200px"
                                        label="Estimated Time (in mins)"
                                        prop="name"
                                    >
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_length }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="150px" label="Action">
                                        <template slot-scope="props">
                                            <base-button
                                                name="Pay Course"
                                                v-if="is_payByEmployeeOn && !props.row.is_employee_paid"
                                                @click.prevent="PayCourse(props.row)"
                                                type="info"
                                            >
                                                {{ "Pay" }}
                                            </base-button>
                                            <base-button
                                                name="Take Course"
                                                v-else
                                                @click.prevent="
                          takingCourse(
                            props.row.id,
                            props.row.due_date,
                            props.row.is_2fa_required
                          )
                        "
                                                type="success"
                                            >
                                                {{
                                                    props.row.course_attempts !== undefined &&
                                                    props.row.course_attempts.length > 0
                                                        ? "Retake Course "
                                                        : "Take Course"
                                                }}
                                            </base-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div
                                class="user-eltable mb-3"
                                v-if="employeeStatus === 'expired' || !this.$route.query.course"
                            >
                                <h3 style="padding:10px;">
                                    Expired Courses
                                </h3>

                                <el-table
                                    :data="queriedData.filter(data => data.course_status === 3)"
                                    row-key="id"
                                    role="table"
                                    class="empcoursesGrid"
                                    header-row-class-name="thead-light custom-thead-light"
                                    @sort-change="sortChange"
                                    @selection-change="selectionChange"
                                >
                                    <el-table-column min-width="200px" label="Course Name">
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Due Date">
                                        <template slot-scope="props">
                      <span
                          v-if="
                          currentDate(props.row.due_date) &&
                            props.row.course_status != '1'
                        "
                          style="color:red;"
                      ><b>{{ formattedDate(props.row.due_date) }}</b></span
                      >
                                            <span v-else>{{
                                                    formattedDate(props.row.due_date)
                                                }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Lesson Status">
                                        <template slot-scope="">
                      <span
                          type="danger"
                          style="color:#f50636;"
                      ><b>Expired</b></span
                      >
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                        min-width="200px"
                                        label="Estimated Time (in mins)"
                                        prop="name"
                                    >
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_length }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="150px" label="Action">
                                        <template slot-scope="props">
                                            <base-button
                                                v-if="is_payByEmployeeOn && !props.row.is_employee_paid"
                                                @click.prevent="PayCourse(props.row)"
                                                type="info"
                                            >
                                                {{ "Pay" }}
                                            </base-button>
                                            <router-link
                                                v-else
                                                :to="
                          '/course_instructions?id=' +
                            props.row.id +
                            '&due_date=' +
                            props.row.due_date
                        "
                                            >
                                                <base-button type="success"  name="Take Course" class="custom-btn">
                                                    {{
                                                        props.row.course_attempts !== undefined &&
                                                        props.row.course_attempts.length > 0
                                                            ? "Retake Course "
                                                            : "Take Course"
                                                    }}
                                                </base-button>
                                            </router-link>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>

                            <div
                                class="user-eltable mb-3"
                                v-if="employeeStatus === 'failed' || !this.$route.query.course"
                            >
                                <h3 style="padding:10px;">
                                    Failed Courses
                                </h3>

                                <el-table
                                    :data="queriedData.filter(data => data.course_status === 0)"
                                    row-key="id"
                                    role="table"
                                    class="empcoursesGrid"
                                    header-row-class-name="thead-light custom-thead-light"
                                    @sort-change="sortChange"
                                    @selection-change="selectionChange"
                                >
                                    <el-table-column min-width="200px" label="Course Name">
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Due Date">
                                        <template slot-scope="props">
                      <span
                          v-if="
                          currentDate(props.row.due_date) &&
                            props.row.course_status != '1'
                        "
                          style="color:red;"
                      ><b>{{ formattedDate(props.row.due_date) }}</b></span
                      >
                                            <span v-else>{{
                                                    formattedDate(props.row.due_date)
                                                }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Lesson Status">
                                        <template slot-scope="">
                      <span
                          type="danger"
                          style="color:#f50636;"
                      ><b>Failed</b></span
                      >
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                        min-width="200px"
                                        label="Estimated Time (in mins)"
                                        prop="name"
                                    >
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_length }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="150px" label="Action">
                                        <template slot-scope="props">
                                            <base-button
                                                name="Pay Course"
                                                v-if="is_payByEmployeeOn && !props.row.is_employee_paid"
                                                @click.prevent="PayCourse(props.row)"
                                                type="info"
                                            >
                                                {{ "Pay" }}
                                            </base-button>
                                            <router-link
                                                v-else
                                                :to="
                          '/course_instructions?id=' +
                            props.row.id +
                            '&due_date=' +
                            props.row.due_date
                        "
                                            >
                                                <base-button type="success"  name="Take Course" class="custom-btn">
                                                    {{
                                                        props.row.course_attempts !== undefined &&
                                                        props.row.course_attempts.length > 0
                                                            ? "Retake Course "
                                                            : "Take Course"
                                                    }}
                                                </base-button>
                                            </router-link>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div
                                class="user-eltable mb-3"
                                v-if="employeeStatus === 'passed' || !this.$route.query.course"
                            >
                                <h3 style="padding:10px;">
                                    Passed Courses
                                </h3>

                                <el-table
                                    :data="queriedData.filter(data => data.course_status === 1)"
                                    row-key="id"
                                    role="table"
                                    class="empcoursesGrid"
                                    header-row-class-name="thead-light custom-thead-light"
                                    @sort-change="sortChange"
                                    @selection-change="selectionChange"
                                >
                                    <el-table-column min-width="200px" label="Course Name">
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Expiration Date">
                                        <template slot-scope="props">
                      <span
                          class="mx-2"
                          v-for="certificate in props.row.certificates"
                          :key="certificate.id"
                      >
                        <span
                            v-if="currentDate(certificate.certificate_exp)"
                            style="color:red;"
                        ><b>{{
                                formattedDate(certificate.certificate_exp)
                            }}</b></span
                        >

                        <span v-else>{{
                                formattedDate(certificate.certificate_exp)
                            }}</span>
                      </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="200px" label="Lesson Status">
                                        <template slot-scope="">
                      <span
                          type="success"
                          style="color: #05bf70;"
                      ><b>Passed</b></span
                      >
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                        min-width="200px"
                                        label="Estimated Time (in mins)"
                                        prop="name"
                                    >
                                        <template slot-scope="props">
                                            <span>{{ props.row.course_length }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="150px" align="left" label="Actions">
                                        <div slot-scope="{ row }" class="d-flex custom-size">
                      <span
                          class="mx-2"
                          v-for="certificate in row.certificates"
                          :key="certificate.id"
                      >
                       <el-tooltip content="Preview" placement="top">
                            <base-button
                                v-if="
                              certificate.manual == 0 &&
                                certificate.is_proctored_exam == 0
                            "
                                @click="generatePreviewCertificate(row.id,user_id,row.certificate_id,certificate.is_coursefolder)"
                                class="success"
                                type=""
                                size="sm"
                                icon
                                data-toggle="tooltip"
                                data-original-title="Preview"
                            >
                              <i class="text-success fa fa-eye"></i>
                            </base-button>
                             <base-button
                                 v-else-if="certificate.manual == 1"
                                 @click="previewManualCertificate(certificate.certificate_url)"
                                 class="success"
                                 type=""
                                 size="sm"
                                 icon
                                 data-toggle="tooltip"
                                 data-original-title="Preview"
                             >
                              <i class="text-success fa fa-eye"></i>
                            </base-button>
                            <base-button
                                v-else-if="certificate.is_proctored_exam == 1"
                                @click.prevent="getProctoredExamCertificate(certificate.certificate_url)"
                                class="success"
                                type=""
                                size="sm"
                                icon
                                data-toggle="tooltip"
                                data-original-title="Preview"
                            >
                              <i class="text-success fa fa-eye"></i>
                            </base-button>
                        </el-tooltip>
                         <el-tooltip content="Download" placement="top">
                            <base-button
                                v-if="
                              certificate.manual == 0 &&
                                certificate.is_proctored_exam == 0
                            "
                                @click="generateDownloadCertificate(row.id,user_id,row.certificate_id,certificate.is_coursefolder)"
                                class="warning"
                                type=""
                                size="sm"
                                icon
                                data-toggle="tooltip"
                                data-original-title="Download"
                            >
                              <i class="text-warning fa fa-download"></i>
                            </base-button>
                            <base-button
                                v-else-if="certificate.manual == 1"
                                @click="downloadManualCertificate(certificate.certificate_url)"
                                class="warning"
                                type=""
                                size="sm"
                                icon
                                data-toggle="tooltip"
                                data-original-title="Download"
                            >
                              <i class="text-warning fa fa-download"></i>
                            </base-button>
                        </el-tooltip>
                      </span>
                                        </div>
                                    </el-table-column>
                                </el-table>
                            </div>
                        </div>
                        <div class="row" v-if="folderProgress.length > 0" style="width:50%;">
                            <div class="col-xl-12">
                                <div class="">
                                    <div class="row">
                                        <div
                                            :class="folderProgress.length== 3 ? 'col-md-4' : folderProgress.length== 2 ? 'col-md-6': folderProgress.length== 1 ? 'col-md-12' : ''"
                                            v-for="(data, index) in folderProgress"
                                            v-bind:key="index"
                                        >
                                            <folder-progress-graph
                                                :folder_name="data.folder_name"
                                                :total_count="data.course_count"
                                                :passed_count="data.passed_course_count"
                                                :failed_count="data.failed_course_count"
                                            ></folder-progress-graph>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </card>
            </div>
        </div>
        <modal :show.sync="show2faModal">
            <h3 slot="header" class="title title-up text-primary text-center">
                Need <u>Two Factor Authentication</u> to continue
            </h3>
            <form>
                <div class="row">
                    <div class="col-md-12">
                        <h5>
                            This course requires 2 Factor authentication. We will be sending
                            you a verification code on your registered phone number.
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <label
                        ><b>Registered Phone Number:</b> {{ phonenumber }} (<a
                            class="linkColor"
                            @click.prevent="changePhone"
                        >Add</a
                        >)</label
                        >
                    </div>

                    <div class="col-md-7">
                        <base-input
                            type="tel"
                            placeholder="Enter new phone number"
                            v-if="changePhoneNum"
                            v-model="newphonenumber"
                        ></base-input>
                    </div>
                    <div
                        class="col-md-5"
                        style="padding:0px !important"
                        v-if="changePhoneNum"
                    >
                        <label>
                            (<a class="linkColor" @click.prevent="cancelPhone">Cancel</a
                        >)</label
                        >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>Select verification channel:</label>
                    </div>
                    <div class="col-md-3">
                        <input
                            type="radio"
                            name="verificationtype"
                            value="sms"
                            v-model="verificationType"
                        />
                        Text
                    </div>
                    <div class="col-md-3">
                        <input
                            type="radio"
                            name="verificationtype"
                            value="call"
                            v-model="verificationType"
                        />
                        Call
                    </div>
                    <div class="col-md-12 mt-4">
                        <base-button
                            name="Send Verification"
                            type="success"
                            @click.prevent="sendVerification"
                            size="sm"
                        >Start Verification</base-button
                        >
                    </div>
                </div>
            </form>
        </modal>
        <modal :show.sync="askOtpModal">
            <h3 slot="header" class="title title-up text-primary text-center">
                Enter Code
            </h3>
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <base-input
                            type="number"
                            v-model="otp"
                            placeholder="Enter Code"
                        ></base-input>
                    </div>
                    <div class="col-md-6">
                        <base-button name="Verify OTP" type="success" @click.prevent="verifyOtp" size="sm"
                        >Verify</base-button
                        >
                    </div>
                </div>
            </form>
        </modal>
        <modal :show.sync="payCourseModel">
            <h4 slot="header" style="color:#444C57" class="modal-title mb-0">
                Payment
            </h4>

            <pay-by-employee
                type="payByEmployee"
                :amountPayable="discountedCost"
                :orignalAmount="orignalCost"
                :discount="is_payByEmployeeDiscount"
                :address="this.address"
                :city="this.city"
                :state="this.state"
                :zipcode="this.zipcode"
                v-on:payClicked="payClicked"
            />
        </modal>
    </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import PayByEmployee from "./PayByEmployee.vue";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import Swal from "sweetalert2";
import moment from "moment";
import FolderProgressGraph from "@/views/Super/FolderProgressGraph.vue";
export default {
    mixins: [clientPaginationMixin],
    components: {
        PayByEmployee,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
        FolderProgressGraph
    },
    data() {
        return {
            loading: false,
            baseUrl: this.$baseUrl,
            height: 500,
            options: {},
            playerReady: false,
            fullPage: true,
            hot_user: "",
            hot_token: "",
            user_id: "",
            config: "",
            passed: "",
            opened: "",
            failed: "",
            expired: "",
            company_id: "",
            searchQuery: "",
            tableData: [],
            selectedRows: [],
            videos: [],
            employeeStatus: "",
            show2faModal: false,
            is_authenticated: 0,
            phonenumber: "",
            newphonenumber: "",
            verificationType: "sms",
            changePhoneNum: false,
            askOtpModal: false,
            is_payByEmployeeOn: 0,
            payCourseModel: false,
            is_payByEmployeeDiscount: "0",
            otp: "",
            discountedCost: 0,
            orignalCost: 0,
            address: "",
            city: "",
            state: "",
            zipcode: "",
            addressAvailble: true,
            folderProgress:""
        };
    },


    created() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");

            this.hot_token = localStorage.getItem("hot-token");
            this.company_id = localStorage.getItem("hot-company-id");
            if (this.hot_user === "company-admin") {
                this.user_id = localStorage.getItem("hot-admin-id");
            } else {
                this.user_id = localStorage.getItem("hot-user-id");
            }
        }
        if (this.$route.query.course) {
            this.employeeStatus = this.$route.query.course;
        }
        this.fetchData();
        this.folderProgressData();
    },
    methods: {
        folderProgressData() {
            this.$http
                .get("employees/employeeCourseFolderProgress")
                .then(response => {
                    this.folderProgress = response.data;
                })
                .catch(error => {
                    //console.log("API failed for loading progress data");
                });
        },
        PayCourse(row) {
            this.discountedCost = row.discountedCost;
            this.orignalCost = row.course_cost;
            this.formData = {
                employee_id: this.user_id,
                company_id: this.company_id,
                course_id: row.id,
                orignal_amount: row.course_cost,
                pay_by_employee_dicount: this.is_payByEmployeeDiscount,
                actual_amount: this.discountedCost
            };
            if (this.address == "" && this.zipcode == "") {
                this.addressAvailble = false;
            }
            this.payCourseModel = true;
        },
        takingCourse(course_id, due_date, is_2fa_required) {
            if (is_2fa_required && this.is_authenticated == 0) {
                this.show2faModal = true;
            } else {
                this.$router.push(
                    "/course_instructions?id=" + course_id + "&due_date=" + due_date
                );
            }
        },
        sendVerification() {
            this.loading = true;
            let data = {
                phone_no: "",
                chanel_type: this.verificationType
            };
            if (this.newphonenumber) {
                data.phone_no = this.newphonenumber;
            } else {
                data.phone_no = this.phonenumber;
            }
            this.$http
                .post("twilio/sendOTP", data)
                .then(resp => {
                    this.askOtpModal = true;
                    Swal.fire({
                        title: "Success!",
                        text: "Text sent to your phone number.",
                        icon: "success"
                    });
                })
                .catch(function(error) {
                    Swal.fire({
                        title: "Error!",
                        text: "Something went wrong!",
                        icon: "error"
                    });
                })
                .finally(() => (this.loading = false));
        },
        verifyOtp() {
            this.loading = true;
            let data = {
                phone_no: "",
                otp: this.otp
            };
            if (this.newphonenumber) {
                data.phone_no = this.newphonenumber;
            } else {
                data.phone_no = this.phonenumber;
            }
            this.$http
                .post("twilio/VerifysendOTP", data)
                .then(resp => {
                    this.show2faModal = false;
                    this.askOtpModal = false;
                    Swal.fire({
                        title: "Success!",
                        text: "Verified successfully.",
                        icon: "success"
                    });
                    this.fetchData();
                })
                .catch(function(error) {
                    if (error.response.status === 422) {
                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Something went wrong.",
                            icon: "error"
                        });
                    }
                })
                .finally(() => (this.loading = false));
        },
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        currentDate(duedate) {
            var currentDateWithFormat = new Date()
                .toJSON()
                .slice(0, 10)
                .replace(/-/g, "-");
            if (currentDateWithFormat > duedate) {
                return true;
            } else {
                return false;
            }
        },
        fetchData() {
            this.loading = true;
            this.$http
                .post("employees/courses", {
                    search: this.searchQuery,
                    employee_id: this.user_id,
                    employee_status: this.employeeStatus
                })
                .then(resp => {
                    this.tableData = [];
                    let course_data = resp.data.courses;
                    let employee_data = resp.data.employee;

                    this.address = employee_data.address;
                    this.state = employee_data.state;
                    this.city = employee_data.city;
                    this.zipcode = employee_data.zipcode;

                    this.is_authenticated = employee_data.is_2f_authenticated;
                    this.phonenumber = employee_data.phone_num;

                    let payby_employee = resp.data.company_pay_by_employee;
                    if (payby_employee != null) {
                        this.company_id = payby_employee.company_id;
                        this.is_payByEmployeeOn = payby_employee.pay_employee_status;
                        this.is_payByEmployeeDiscount =
                            payby_employee.pay_employee_discount;
                    }
                    for (let data of course_data) {
                        let obj = {
                            id: data.course_id,
                            course_name: data.name,
                            course_length: data.length,
                            course_cost: data.cost,
                            discountedCost: "",
                            due_date: data.employee_course_due_date,
                            course_status: data.employee_course_status,
                            is_2fa_required: "",
                            is_employee_paid: "",
                            certificates: [],
                            certificate_id: ""
                        };

                        if (data.employee_certiifcates) {
                            obj.certificate_id = data.employee_certiifcates[0].id;
                        }
                        if (data.course_paid_status === 0) {
                            obj.discountedCost =
                                obj.course_cost -
                                obj.course_cost * (this.is_payByEmployeeDiscount / 100);
                            obj.is_employee_paid = false;
                        } else {
                            obj.is_employee_paid = true;
                        }
                        if (data.is_2fa_required === 1) {
                            obj.is_2fa_required = true;
                        } else {
                            obj.is_2fa_required = false;
                        }
                        if (data.employee_certiifcates) {
                            let certificate_data = data.employee_certiifcates;
                            for (let certificate of certificate_data) {
                                let certificates_obj = {
                                    certificate_exp: certificate.certificate_expiration_date,
                                    certificate_url: certificate.certificate_url,
                                    is_coursefolder: certificate.is_coursefolder_certificate,
                                    manual: certificate.manual,
                                    is_proctored_exam: certificate.is_proctored_exam
                                };
                                obj.certificates.push(certificates_obj);
                            }
                        }
                        this.tableData.push(obj);
                    }
                })
                .finally(() => (this.loading = false));
        },
        generatePreviewCertificate(id,user_id,certificate_id,is_coursefolder){
            window.open(this.baseUrl + '/downloadCourseCertificate/preview/' + id +'/' + user_id + '/' + certificate_id + '/' + is_coursefolder);
        },
        generateDownloadCertificate(id,user_id,certificate_id,is_coursefolder){
            window.open(this.baseUrl + '/downloadCourseCertificate/download/' + id +'/' + user_id + '/' + certificate_id + '/' + is_coursefolder);
        },
        previewManualCertificate(url){
            window.open(url);
        },
        downloadManualCertificate(url){
            window.open(url,'download');
        },
        getProctoredExamCertificate: function(certificateURL) {
            this.$http
                .post("course/proctored-exam-certificate", {
                    certificateURL: certificateURL
                })
                .then(resp => {
                    window.open(resp.data.certificate_url, "_blank");
                });
        },
        selectionChange(selectedRows) {
            this.selectedRows = selectedRows;
        },
        changePhone() {
            this.changePhoneNum = true;
        },
        cancelPhone() {
            this.newphonenumber = "";
            this.changePhoneNum = false;
        },
        payClicked(cardData, addressData) {
            let payment = {
                payment_type: "payByEmployee",
                cardholder_street_address: "",
                cardholder_zip: "",
                cardholder_city: "",
                cardholder_state: "",
                transaction_amount: "",
                card_number: cardData.cardNumber,
                card_exp_date: cardData.expire
            };
            if (addressData.address != "") {
                payment.cardholder_street_address = addressData.address;
                payment.cardholder_zip = addressData.zipcode;
                payment.cardholder_city = addressData.city;
                payment.cardholder_state = addressData.state;
            } else {
                payment.cardholder_street_address = this.address;
                payment.cardholder_zip = this.zipcode;
            }
            payment.transaction_amount = this.discountedCost.toFixed(2);

            this.formData.payment = payment;

            this.$http
                .post("course/pay_by_employee_submission", this.formData)
                .then(resp => {
                    Swal.fire({
                        title: "Success!",
                        html: `Amount Paid Sucessfully.`,
                        icon: "success",
                        confirmButton: "btn btn-success",
                        confirmButtonText: "Ok"
                    }).then(result => {
                        this.fetchData();
                    });
                })
                .catch(function(error) {
                    if (error.response.status === 422) {
                        return Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error"
                        });
                    }
                })
                .finally(() => (this.payCourseModel = false));
        }
    }
};
</script>
<style scoped>
.no-border-card .card-footer {
    border-top: 0;
}
@media only screen and (max-width: 760px),
(min-device-width: 768px) and (max-device-width: 1024px) {
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Course Name";
    }
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Due Date";
    }
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Lesson Status";
    }
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Estimated Time";
    }
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Action";
    }
}
</style>
