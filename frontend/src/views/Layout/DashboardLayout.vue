<template>
    <div class="wrapper">
        <v-idle :wait="5" @remind="onremind" :reminders="[30]" :loop="true" :events="['mousemove']" @idle="onidle" :duration="logoutDuration"/>
        <notifications></notifications>
        <side-bar>
            <template slot="links">
                <div v-if="editor === 'super-admin' || editor === 'sub-admin'">
                    <sidebar-item :link="{ name: 'Dashboard', icon: 'dashbaordicon menu-icons', path: '/dashboard',}"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewCompany))" :link="{ name: 'Companies', icon: 'menu-icons companiesicon', path: '/all_companies', }"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewUser))" :link="{ name: 'Users', icon: 'menu-icons usersicon', path: '/all_users',}"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewCourse))" :link="{ name: 'Courses', icon: 'menu-icons coursesicon',}">
                        <sidebar-item :link="{ name: 'Courses', path: '/courses' }"/>
                        <sidebar-item :link="{ name: 'Course Folders', path: '/course_folder' }"/>
                    </sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewResource))" :link="{ name: 'Resources', icon: 'menu-icons Resourcesicon', path: '/resources', }"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewCertificate))" :link="{ name: 'Certificates', icon: 'menu-icons certificatesicon', path: '/certificates', }"></sidebar-item>
                    <sidebar-item :link="{ name: 'Test Question Report', icon: 'menu-icons TutorialVideosicon', path: '/test_question_report',}"></sidebar-item>
                    <sidebar-item :link="{ name: 'Survey Report', icon: 'menu-icons TutorialVideosicon', path: '/survey_report', }"></sidebar-item>
                    <sidebar-item :link="{ name: 'Login Report', icon: 'menu-icons certificatesicon',path: '/login_report',}"></sidebar-item>
                    <sidebar-item :link="{ name: 'Activity Report', icon: 'menu-icons certificatesicon', path: '/activity_report'}"></sidebar-item>
                    <sidebar-item :link="{name: 'Course Pass/Fail Report', icon: 'menu-icons certificatesicon', path: '/course_fail_pass_report',}"></sidebar-item>
                    <sidebar-item :link="{name: 'Documents Report', icon: 'menu-icons certificatesicon',path: '/documents-report'}"></sidebar-item>
                    <sidebar-item :link="{ name: 'Onboarding Report', icon: 'menu-icons certificatesicon', path: '/all_onboarding_report'}"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewTutorial))" :link="{name: 'Tutorial Videos', icon: 'menu-icons TutorialVideosicon', path: '/tutorial_video'}"></sidebar-item>
                    <sidebar-item v-if="(editor === 'super-admin') || ((editor === 'sub-admin') && (canViewTour))" :link="{name: 'Tour',icon: 'menu-icons TutorialVideosicon',path: '/tour_page',}"></sidebar-item>
                    <sidebar-item v-if="editor!='sub-admin'" :link="{name: 'Sub Admin',icon: 'menu-icons Employeesicon',path: '/create_subadmin',}"></sidebar-item>
                    <sidebar-item :link="{name: 'Promo Codes',icon: 'menu-icons certificatesicon',path: '/promo_codes',}"></sidebar-item>
                    <sidebar-item :link="{name: 'Promo Code Reports',icon: 'menu-icons certificatesicon',path: '/promo_code_report',}"></sidebar-item>
                    <sidebar-item :link="{name: 'My Profile',icon: 'menu-icons MyProfileicon',path: '/account',}"></sidebar-item>
                </div>
                <div v-if="editor === 'company' || editor === 'manager'">
                    <sidebar-item :link="{name: 'Dashboard',icon: 'dashbaordicon menu-icons',path: '/dashboard',}"></sidebar-item>
                    <sidebar-item v-intro="'Click here to see a list of all employees in your account.'" v-intro-step="4" :link="{name: 'Employees',icon: 'menu-icons Employeesicon',path: '/company_employees',}"></sidebar-item>
                    <sidebar-item v-intro="'Click here to see a list of all locations in your account.'" v-intro-step="5" :link="{name: 'Locations',icon: 'menu-icons Locationsicon',path: '/company_locations',}"></sidebar-item>
                    <sidebar-item v-intro="'Click here to see the courses assigned to your company as well as the courses assigned to you in the My Courses link.'" v-intro-step="6" :link="{name: 'Courses',icon: 'menu-icons coursesicon',}">
                        <sidebar-item :link="{ name: 'Company Courses', path: '/company_courses' }"/>
                        <sidebar-item :link="{name: 'Company Course Folders', path: '/company_coursefolders'}"/>
                        <sidebar-item :link="{ name: 'My Courses', path: '/employee_courses' }"/>
                        <sidebar-item :link="{ name: 'Purchase Course', icon: 'menu-icons coursesicon', path: '/employee_course_purchase',}"/>
                    </sidebar-item>
                    <sidebar-item v-intro="'Click here to see employee certificates as well as any certificates you have earned.'" v-intro-step="7" :link="{ name: 'Certificates', icon: 'menu-icons certificatesicon',}">
                        <sidebar-item :link="{ name: 'Employee Certificates', path: '/company_certificates',}"/>
                        <sidebar-item :link="{name: 'My Certificates', path: '/employee_certificates', }"/>
                    </sidebar-item>
                    <sidebar-item :link="{ name: 'Resources', icon: 'menu-icons Resourcesicon', path: '/employee_resources', }">
                    </sidebar-item>
                    <sidebar-item :link="{ name: 'Documents', icon: 'menu-icons Resourcesicon',path: '/employee_documents',}"></sidebar-item>
                    <sidebar-item data-tour-step="7" v-intro="'Click here to download various reports for your company.'" v-intro-step="8" class="hideActive" :link="{ name: 'Reports', icon: 'menu-icons Reportsicon',}">
                        <sidebar-item v-if="!progressButtonContent" :link="{name: 'Send Progress Report', path: $route.path + '#sending_progress_report',}" @click.native="generateProgress"/>
                        <sidebar-item v-else :link="{ name: 'Sending...', icon: 'ni ni-collection text-blue', path: $route.path + '#sending_progress_report', }"/>
                        <sidebar-item v-if="!userDownloadButtonContent" :link="{ name: 'User Report', path: $route.path + '#downloading_user_report', }" @click.native="downloadUserList"/>
                        <sidebar-item v-else :link="{ name: 'Downloading...', icon: 'ni ni-collection text-blue', path: $route.path + '#downloading_user_report', }"/>
                        <sidebar-item v-if="!courseDownloadButtonContent" :link="{name: 'Course Report', path: $route.path + '#downloading_course_report', }" @click.native="handleDownload"/>
                        <sidebar-item v-else :link="{ name: 'Downloading...', icon: 'ni ni-collection text-blue', path: $route.path + '#downloading_course_report', }"/>
                        <sidebar-item v-if="!certificateDownloadButtonContent" :link="{ name: 'Certificate Report', path: $route.path + '#downloading_certificate_report', }" @click.native="downloadCertificateList"/>
                        <sidebar-item v-else :link="{ name: 'Downloading...', icon: 'ni ni-collection text-blue', path: $route.path + '#downloading_certificate_report', }"/>
                        <sidebar-item :link="{ name: 'Login Report', icon: 'menu-icons certificatesicon',path: '/login_report',}"/>
                        <sidebar-item :link="{ name: 'Activity Report', icon: 'menu-icons certificatesicon', path: '/activity_report', }"/>
                        <sidebar-item :link="{ name: 'Course Pass/Fail Report', icon: 'menu-icons certificatesicon', path: '/course_fail_pass_report', }"/>
                        <sidebar-item :link="{name: 'Documents Report',path: '/documents-report'}"/>
                    </sidebar-item>
                    <sidebar-item v-intro=" 'Here you will find helpful tutorial videos that will show you how to navigate through your account.' " v-intro-step="1" :link="{ name: 'Tutorial Videos', icon: 'menu-icons TutorialVideosicon', path: '/tutorial_video', }"></sidebar-item>
                    <sidebar-item v-intro="'Click here to edit your profile information.'" v-intro-step="9" name="My Profile" :link="{ name: 'My Profile', icon: 'menu-icons MyProfileicon', path: '/account', }"></sidebar-item>
                </div>
                <div v-if="editor === 'employee'">
                    <sidebar-item :link="{ name: 'Dashboard', icon: 'dashbaordicon menu-icons', path: '/dashboard', }"></sidebar-item>
                    <sidebar-item :link="{ name: 'Purchase Course', icon: 'menu-icons coursesicon', path: '/employee_course_purchase', }"/>
                    <sidebar-item v-intro="'Here you will find all of your certificates.'" v-intro-step="3" :link="{ name: 'Certificates', icon: 'menu-icons certificatesicon', path: '/employee_certificates',}"></sidebar-item>
                    <sidebar-item :link="{name: 'Resources',icon: 'menu-icons Resourcesicon',path: '/employee_resources',}"></sidebar-item>
                    <sidebar-item :link="{name: 'Documents',icon: 'menu-icons Resourcesicon',path: '/employee_documents',}"></sidebar-item>
                    <sidebar-item v-intro="'Here you will find all of your courses, Open, Expired, Failed, and Passed.'" v-intro-step="4" :link="{ name: 'Courses', icon: 'menu-icons coursesicon', path: '/employee_courses', }"></sidebar-item>
                    <sidebar-item v-intro=" 'Here you will find helpful tutorial videos that will show you how to navigate through your account.'" v-intro-step="2" :link="{name: 'Tutorial Video', icon: 'menu-icons TutorialVideosicon', path: '/tutorial_video',}"></sidebar-item>
                    <sidebar-item v-intro="'Here you can update your profile information.'" v-intro-step="5" name="My Profile" :link="{ name: 'My Profile ', icon: 'menu-icons MyProfileicon', path: '/add_employee', }"></sidebar-item>
                </div>
            </template>
        </side-bar>
        <div class="main-content">
            <dashboard-navbar :type="$route.meta.navbarType"></dashboard-navbar>
            <div @click="$sidebar.displaySidebar(false)">
                <fade-transition :duration="200" origin="center top" mode="out-in">
                    <router-view></router-view>
                </fade-transition>
            </div>
            <content-footer v-if="!$route.meta.hideFooter"></content-footer>
        </div>
        <modal :show.sync="downlaodModel">
            <h3 slot="header" style="color: #444c57" class="title title-up">Download Course Report</h3>
            <el-select v-model="filters.course_id" placeholder="All Courses">
                <el-option class="select-default" v-for="item in companyCourses" :key="item.value" :label="item.label" :value="item.value"></el-option>
            </el-select>
            <form>
                <br/>
                <div class="row download-btn" style="text-align: center">
                    <base-button type="warning" @click.prevent="downloadcourselist('open')">Open Courses</base-button>
                    <base-button type="danger" @click.prevent="downloadcourselist('non-complaint')">Non Compliance</base-button>
                    <base-button type="success" @click.prevent="downloadcourselist('complaint')">Compliance</base-button>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="documentSignModel" v-on:close="onclosedocumentSignModel" size="xl">
            <h4 slot="header" style="color: #444c57" class="modal-title mb-0">
                User Onboarding
            </h4>
            <user-onboarding/>
        </modal>
        <modal :show.sync="showEmployeePostLoginSurvey" size="xl">
            <h4 slot="header" style="color: #444c57" class="modal-title mb-0">
                Post Login Survey
            </h4>
            <post-login-survey-employee v-if="showEmployeePostLoginSurvey" v-on:hideEmployeeSurveyPopup="hideEmployeeSurveyPopup"/>
        </modal>
    </div>
</template>
<script>
/* eslint-disable no-new */
import Vue from "vue";
import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";
import UserOnboarding from "@/views/Super/UserOnboarding.vue";
import PostLoginSurveyEmployee from "@/views/Super/PostLoginSurveyEmployee.vue";
import {Option, Select} from "element-ui";
import DashboardNavbar from "./DashboardNavbar.vue";
import ContentFooter from "./ContentFooter.vue";
import {FadeTransition} from "vue2-transitions";
import XLSX from "xlsx";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import introJs from "intro.js";
import Vidle from "v-idle";

function hasElement(className) {
    return document.getElementsByClassName(className).length > 0;
}

function initScrollbar(className) {
    if (hasElement(className)) {
        new PerfectScrollbar(`.${className}`);
    } else {
        // try to init it later in case this component is loaded async
        setTimeout(() => {
            initScrollbar(className);
        }, 100);
    }
}

Vue.use(Vidle);
export default {
    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        DashboardNavbar,
        ContentFooter,
        FadeTransition,
        UserOnboarding,
        PostLoginSurveyEmployee
    },
    data() {
        return {
            progressButtonContent: false,
            userDownloadButtonContent: false,
            certificateDownloadButtonContent: false,
            courseDownloadButtonContent: false,
            companyCourses: [],
            downlaodModel: false,
            hot_user: "",
            hot_token: "",
            editor: "",
            filters: {
                course_id: "All Courses",
            },
            steps: "",
            company_onboarding_status: false,
            documentSignModel: false,
            logoutDuration: 1800,
            canCreate: true,
            canEdit: true,
            canDelete: true,
            canViewCompany: false,
            canViewUser: false,
            canViewCourse: false,
            canViewCertificate: false,
            canViewResource: false,
            canViewTutorial: false,
            canViewTour: false,
            showEmployeePostLoginSurvey: false
        };
    },
    created: function () {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        } else {
            this.$router.push("/login");
        }

        if (localStorage.getItem("hot-user") === "employee") {
            this.editor = "employee";
        } else if (localStorage.getItem("hot-user") === "super-admin") {
            this.editor = "super-admin";
        } else if (localStorage.getItem("hot-user") === "sub-admin") {
            this.editor = "sub-admin";
            this.getRightsDetails();
        } else if (localStorage.getItem("hot-user") === "company-admin") {
            this.editor = "company";
        }
        if (localStorage.getItem("hot-sidebar") === "manager") {
            this.editor = "manager";
        }
    },

    methods: {
        getRightsDetails() {
            let type = "All";
            this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
                this.canViewCompany = resp.data[0].permissions.indexOf('v') !== -1;
                this.canViewUser = resp.data[1].permissions.indexOf('v') !== -1;
                this.canViewCourse = resp.data[2].permissions.indexOf('v') !== -1;
                this.canViewCertificate = resp.data[3].permissions.indexOf('v') !== -1;
                this.canViewResource = resp.data[4].permissions.indexOf('v') !== -1;
                this.canViewTutorial = resp.data[5].permissions.indexOf('v') !== -1;
                this.canViewTour = resp.data[6].permissions.indexOf('v') !== -1;
            });
        },
        onidle() {
            this.onclosedocumentSignModel();
        },
        onremind(time) {
            const timertest = this.set(time);
            Swal.fire({
                html: `Your session will automatically time out in
        <span id="time" style="color:red;font-weight:bold">${
                    timertest != undefined ? timertest : time
                }</span>
        seconds due to inactivity.`,
                icon: "warning",
                confirmButtonClass: "btn",
                cancelButtonClass: "btn",
                confirmButtonColor: "#999",
                cancelButtonColor: "#0b427b",
                confirmButtonText: "Ok",
                showCancelButton: true,
                cancelButtonText: "Keep me Logged In",
            }).then((result) => {
                if (result.value) {
                    this.onclosedocumentSignModel();
                } else {
                    this.$router.go(this.$route.path);
                }
            });
        },
        set(time) {
            var timer = time - 1,
                seconds;
            setInterval(function () {
                seconds = parseInt(timer % 60, 10);
                seconds = seconds < 10 ? "0" + seconds : seconds;
                if (document.querySelector("#time").textContent) {
                    document.querySelector("#time").textContent = seconds;
                }
                if (--timer < 0) {
                    clearInterval();
                    timer = time;
                }
            }, 1000);
        },
        hideEmployeeSurveyPopup() {
            this.showEmployeePostLoginSurvey = false;
        },
        initScrollbar() {
            let isWindows = navigator.platform.startsWith("Win");
            if (isWindows) {
                initScrollbar("sidenav");
            }
        },
        generateProgress() {
            this.progressButtonContent = true;
            this.$http.get("progress/generate_report").then((resp) => {
                this.progressButtonContent = false;
                if ((resp.data.length > 0) && resp.data.includes(null)) {
                    this.notifyVue("success", resp.data);
                } else if ((resp.data.length > 0) && !resp.data.includes(null)) {
                    this.notifyVue("danger", resp.data[0]);
                } else if (resp.data.length == 0) {
                    this.notifyVue("danger", 'No Admins/Mangers active for progress report.');
                } else {
                    this.notifyVue("danger", '');
                }
                this.$router.push(this.$route.path);
            });
        },
        downloadUserList() {
            this.userDownloadButtonContent = true;
            this.report_type = "all_user";
            this.$http
                .post("company/users", {
                    report_type: this.report_type,
                })
                .then((resp) => {
                    this.userDownloadButtonContent = false;
                    this.items = resp.data;
                    const data = XLSX.utils.json_to_sheet(this.items);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, data, "data");
                    XLSX.writeFile(wb, this.report_type + ".xlsx");

                    this.$router.push(this.$route.path);
                });
        },
        downloadCertificateList() {
            this.certificateDownloadButtonContent = true;
            this.$http
                .post("course/pass_employee", {
                    certificate_status: "Active Certificates",
                })
                .then((resp) => {
                    this.certificateDownloadButtonContent = false;
                    let employee_data = resp.data.employee;
                    for (let data of employee_data) {
                        let obj = [];
                        obj = {
                            course_name: data.course_name,
                        };
                    }
                    //this.course_name = this.course_name;
                    this.items = resp.data.download;
                    const data1 = XLSX.utils.json_to_sheet(this.items);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, data1, "data");
                    XLSX.writeFile(wb, "Certificate.xlsx");
                })
                .finally(() => (this.loading = false));
        },
        handleDownload() {
            this.$http.get("company/all_courses/" + 0).then((resp) => {
                this.companyCourses = [];
                let fobj = {
                    label: "All Courses",
                    value: "All Courses",
                };
                this.companyCourses.push(fobj);
                for (let data of resp.data[0].courses) {
                    let obj = {
                        label: data.name,
                        value: data.course_id,
                    };
                    this.companyCourses.push(obj);
                }
            });
            this.downlaodModel = true;
        },
        downloadcourselist(type) {
            this.loading = true;
            let report_type = "";
            if (type == "open") {
                this.report_type = "open_course";
            }
            if (type == "non-complaint") {
                this.report_type = "non_compliance";
            }
            if (type == "complaint") {
                this.report_type = "compliance";
            }
            this.$http
                .post("course/certificates/report", {
                    report_type: this.report_type,
                    company_id: this.company_id,
                    course_id: this.filters.course_id,
                })
                .then((resp) => {
                    this.items = resp.data;
                    const data = XLSX.utils.json_to_sheet(this.items);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, data, "data");
                    XLSX.writeFile(wb, this.report_type + ".xlsx");
                })
                .catch(function (error) {
                    self.processing = false;
                    if (error.response.status === 422) {
                        let respmessage = error.response.data.message;
                        Swal.fire({
                            title: "Error!",
                            text: respmessage,
                            icon: "error",
                        });
                    }
                })
                .finally(() => (this.loading = false));
        },
        notifyVue(type, data) {
            if (type == "success") {
                this.$notify({
                    message: "Progress Report Generated Successfully.",
                    timeout: 3000,
                    icon: "ni ni-bell-55",
                    type,
                });
            } else {
                this.$notify({
                    message: "Progress Report Not Generated. " + data,
                    timeout: 3000,
                    icon: "ni ni-bell-55",
                    type,
                });
            }
        },
        onclosedocumentSignModel() {
            this.$http.get("user/logout_time").then((resp) => {
                localStorage.removeItem("hot-token");
                localStorage.removeItem("hot_payment_responsible");
                localStorage.removeItem("hot-sidebar");
                localStorage.removeItem("hot-user-id");
                localStorage.removeItem("hot-company-id");
                localStorage.removeItem("hot-user");
                localStorage.removeItem("all_user_search_data");
                localStorage.removeItem("all_company_search_data");
                localStorage.removeItem("all_courses_search_data");
                localStorage.removeItem("all_certificate_search_data");
                localStorage.removeItem("all_tutorial_video_search_data");
                localStorage.removeItem("all_certificate_detail_search_data");

                delete this.$http.defaults.headers["authorization"];

                this.$router.go("/login");
            });
        },

    },
    mounted() {
        if (this.editor !== "super-admin" && this.editor !== "sub-admin") {
            document.addEventListener("mouseup", (e) => {
                if (e.target.attributes.name) {
                    let data = {
                        event: "Clicked on " + e.target.attributes.name.nodeValue,
                    };
                    this.$http
                        .post("user/add_activity", data)
                        .then((resp) => {
                        })
                        .catch(function () {
                        })
                        .finally(() => (this.loading = false));
                }
            });
            this.$http.get("user/login_check").then((resp) => {
                if (resp.data[0].company_onboarding_status) {
                    this.documentSignModel = true;
                }
                if (resp.data[0].is_first_login === 1) {
                    introJs().start();
                    this.$http.get("user/update_first_login_status").then((resp) => {
                        console.log("Done");
                    });
                }
                if (resp.data[0].company_survey_status.length > 0) {
                    this.showEmployeePostLoginSurvey = true;
                }

            });
        }

        this.initScrollbar();
    },
};
</script>
<style scoped>
.v-idle {
    text-align: center;
    display: none;
}
</style>
