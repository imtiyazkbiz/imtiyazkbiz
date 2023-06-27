<template>
    <div class="content">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <div>
                <card body-classes="px-0 pb-1" class="no-border-card" footer-classes="pb-2">
                    <template slot="header">
                        <h2 class="mb-0">Company Documents</h2>
                        <p style="font-weight: 600;">Please find any onboarding/required forms you filled out and/or documents you have uploaded.</p>
                    </template>
                    <div class="col-sm-12">
                        <tabs :defaultIndex="0">
                            <tab v-if="this.company_level == 'parent'" name="Company Forms" title="Company Forms">
                                <hr-form></hr-form>
                            </tab>
                            <tab name="My Company Forms" title="My Company Forms">
                                <hr-form-employee></hr-form-employee>
                            </tab>
                            <tab v-if="this.editor != 'employee'" name="Onboarding Documents" title="Onboarding Documents">
                                <user-onboarding-report></user-onboarding-report>
                            </tab>
                            <tab v-if="this.editor != 'employee'" title="Employee Documents">
                                <div>
                                    <div>
                                        <div class="row">
                                            <div class="col-md-12 user-eltable">
                                                <el-table :data="tbl1_data" class="employeeresGrid" header-row-class-name="thead-light custom-thead-light" role="table" row-key="id">
                                                    <el-table-column align="left" label="" min-width="180px" prop="first_name">
                                                        <template slot="header">
                                                            <span> First Name </span>
                                                        </template>
                                                        <template slot-scope="props">
                                                            {{ props.row.first_name }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column align="left" label="" min-width="180px" prop="last_name">
                                                        <template slot="header">
                                                            <span>Last Name </span>
                                                        </template>
                                                        <template slot-scope="props">
                                                            {{ props.row.last_name }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column align="left" label="" min-width="180px" prop="location">
                                                        <template slot="header">
                                                            <span>Location </span>
                                                        </template>
                                                        <template slot-scope="props">
                                                            {{ props.row.location }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column align="left" label="" min-width="100px" prop="date">
                                                        <template slot="header">
                                                            <span>Date </span>
                                                        </template>
                                                        <template slot-scope="props">
                                                            {{ props.row.date }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column align="left" label="Document" min-width="200px" prop="document">
                                                        <template slot-scope="props">
                                                            <span v-if="props.row.id">
                                                                <a :href="baseUrl + '/hr-forms/' + props.row.file_url">{{ props.row.document_name }}</a>
                                                            </span>
                                                            <span v-else> {{ props.row.document_name }}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column align="left" class="table-custom-size" label="Action" min-width="150px">
                                                        <template slot-scope="props">
                                                            <div class="row">
                                                                <div class="col-md-1">
                                                                    <span v-if="props.row.id">
                                                                        <el-tooltip content="Download" placement="top">
                                                                            <span class="linkColor" data-original-title="Download" data-toggle="tooltip" @click="handleView(props.row)">
                                                                                <i class="text-warning fa fa-download"></i>
                                                                            </span>
                                                                        </el-tooltip>
                                                                    </span>
                                                                    <span v-else>
                                                                        <a v-if="props.row.document_type == 'file' && props.row.document != null" :href="baseUrl + '/employee/documents/' + props.row.document" class="" style="color: white" target="_blank" title="View Resource"><i class="text-warning fa fa-eye" name="Preview Resources"></i> </a>
                                                                        <a v-if="props.row.document_type == 'link' && props.row.document_url != null" :href="props.row.document_url" class="" target="_blank" title="Open Link">
                                                                            <i class="fa fa-link" title="Open Resource Link"></i>
                                                                        </a>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tab>
                            <tab name="Other" title="Other">
                                <div class="row">
                                    <div class="col-md-12 user-eltable">
                                        <el-table :data="tbl_data" class="resourceGride" header-row-class-name="thead-light custom-thead-light" role="table">
                                            <el-table-column align="left" label="Resource Name" min-width="250px">
                                                <template slot-scope="propsss">
                                                    <span>{{ propsss.row.document_name }}</span>
                                                </template>
                                            </el-table-column>
                                            <el-table-column align="left" label="Resource Description" min-width="250px">
                                                <template slot-scope="propsss">
                                                    <span>{{ propsss.row.document_description }}</span>
                                                </template>
                                            </el-table-column>
                                            <el-table-column align="left" class="table-custom-size" label="Action" min-width="150px">
                                                <template slot-scope="propsss">
                                                    <div class="row">
                                                        <div class="col-md-1">
                                                            <a v-if="propsss.row.document_type == 'file' && propsss.row.document != null" :href="baseUrl + '/employee/documents/' + propsss.row.document" class="" style="color: white" target="_blank" title="Download Resource"><i class="text-warning fa fa-download"></i> </a>
                                                            <a v-if="propsss.row.document_type == 'link' && propsss.row.document_url != null" :href="propsss.row.document_url" class="" target="_blank" title="Open Link">
                                                                <i class="fa fa-link"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </div>
                                </div>
                            </tab>
                        </tabs>
                    </div>
                </card>
            </div>
        </div>
    </div>
</template>
<script>
import Vue from "vue";
import {Option, Select, Table, TableColumn} from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import HrForm from "./HrForm.vue";
import HrFormEmployee from "./HrFormEmployee.vue";
import UserOnboardingReport from "./UserOnboardingReport.vue";
import "sweetalert2/src/sweetalert2.scss";
import {Tab, Tabs} from "vue-slim-tabs";
import moment from "moment";
import axios from "axios";

Vue.use(Tabs);

export default {
    mixins: [serverSidePaginationMixin],
    components: {
        //BasePagination,
        HrForm,
        HrFormEmployee,
        UserOnboardingReport,
        Tabs,
        Tab,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
    },
    data() {
        return {
            baseUrl: this.$baseUrl,
            hot_user: "",
            hot_token: "",
            user_id: "",
            tbl_data: [],
            tbl1_data: [],
            editor: "",
            company_level: "",
        };
    },
    created: function () {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
            if (this.hot_user === "company-admin") {
                this.user_id = localStorage.getItem("hot-admin-id");
                this.company_level = localStorage.getItem("hot-company-level");
            } else {
                this.user_id = localStorage.getItem("hot-user-id");
            }
        }
        if (localStorage.getItem("hot-user") === "employee") {
            this.editor = "employee";
        }
        this.$http
            .post("employees/documents", {
                employee_id: this.user_id,
            })
            .then((resp) => {
                let document_data = resp.data;
                for (let data of document_data) {
                    let obj = {
                        document: data.document,
                        document_name: data.title,
                        document_type: data.type,
                        document_description: data.description,
                        document_url: data.url,
                    };
                    this.tbl_data.push(obj);
                }
            });
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.tbl1_data = [];
            this.$http
                .post("employees/employee_documents", {
                    column: this.sortedColumn,
                    order: this.order,
                })
                .then((resp) => {
                    let employee_document_data = resp.data;
                    for (let data of employee_document_data) {
                        let obj1 = {
                            first_name: data.first_name,
                            last_name: data.last_name,
                            location: data.location,
                            date: moment(data.created_at).format("MM-DD-YYYY"),
                            document: data.document,
                            document_name: data.title,
                            document_type: data.type,
                            document_url: data.url,
                        };
                        this.tbl1_data.push(obj1);
                    }
                });
            this.$http
                .post("resources/hr_form_report", {
                    search: this.searchQuery,
                    page: this.currentPage,
                    column: this.sortedColumn,
                    per_page: this.perPage,
                })
                .then((resp) => {
                    this.tableData = [];
                    let report_data = resp.data.report;
                    let total_data = resp.data.total;
                    this.totalData = total_data;
                    for (let data of report_data) {
                        let obj = {
                            id: data.id,
                            first_name: data.first_name,
                            last_name: data.last_name,
                            date: moment(data.created_at).format("MM-DD-YYYY"),
                            location: data.location,
                            document_name: data.name,
                            file_url: data.file,
                            document_url: data.upload,
                        };
                        this.tbl1_data.push(obj);
                    }
                })
                .finally(() => (this.loading = false));
        },
        async handleView(file) {
            let row = file.document_url;
            await this.$http
                .post("resources/download-hr-forms", {
                    id: file.id,
                    type: "Employee Documents",
                })
                .then((response) => {
                    let extension = row.split(".").pop();
                    if (extension == "pdf") {
                        let filenameNew = row.split(".pdf");
                        axios
                            .get(this.baseUrl + "/resources/download_uploads/" + filenameNew[0], {
                                responseType: "arraybuffer",
                            })
                            .then((res) => {
                                let blob = new Blob([res.data], {type: "application/pdf"});
                                let link = document.createElement("a");
                                link.href = window.URL.createObjectURL(blob);
                                link.download = row;
                                link.click();
                            });
                    } else {
                        window.open(this.baseUrl + "/hr-forms/user-uploads/" + row, "_blank").focus();
                    }
                });
        },
    },
};
</script>
<style scoped>
.no-border-card .card-footer {
    border-top: 0;
}

@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px) {
    .resourceGride >>> table.el-table__body td:nth-of-type(1):before {
        content: "Resource Name";
    }

    .resourceGride >>> table.el-table__body td:nth-of-type(2):before {
        content: "Resource Description";
    }

    .resourceGride >>> table.el-table__body td:nth-of-type(3):before {
        content: "Action";
    }

    .employeeresGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Employee Name";
    }

    .employeeresGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Resource Name";
    }

    .employeeresGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Action";
    }
}
</style>
<style src="vue-slim-tabs/themes/default.css"></style>
