<template>
    <div class="content" v-loading.fullscreen.lock="loading">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <div>
                <card class="no-border-card" footer-classes="pt-1">
                    <template slot="header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h2 class="mb-0">My Certificates</h2>
                            </div>

                            <div class="col-md-6 text-right">
                                <template v-if="isCertficateChecked">
                                    <a
                                        :href="
                      baseUrl +
                        '/downloadAllCourseCertificate/single/' +
                        checked_certificate.join('_') +
                        '/' +
                        user_id
                    "
                                        class="btn base-button custom-btn btn-default"
                                        type="primary"
                                    >Download<i class=" fa fa-download"></i
                                    ></a>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div>
                        <div class="row align-items-center justify-content-between mb-2">

                            <!-- <div class=" col-md-6   col-6 text-right">
                              <input type="checkbox" v-model="selectAll" @click="select" />
                              Check All
                            </div> -->
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mt-2">
                                <div class="user-eltable">
                                    <h3 style="padding:10px;">Active Certificates</h3>
                                    <el-table
                                        :data="queriedData"
                                        row-key="id"
                                        role="table"
                                        class="certificatesGrid"
                                        header-row-class-name="thead-light custom-thead-light"
                                        @sort-change="sortChange"
                                        @selection-change="selectionChange"
                                    >
                                        <!-- <el-table-column min-width="100px" label="">
                                          <template slot-scope="props">
                                            <span>
                                              <input
                                                type="checkbox"
                                                :value="props.row.id"
                                                v-model="checked_certificate"
                                                v-on:input="certificateCheckchange(props.row)"
                                              />
                                              <i class="form-icon"></i>
                                            </span>
                                          </template>
                                        </el-table-column> -->
                                        <el-table-column
                                            min-width="250px"
                                            align="left"
                                            label="Course Name"
                                        >
                                            <template slot-scope="props">
                                                <span>{{ props.row.certificate_name }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Completion Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Expiration Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_expiration_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Action"
                                        >
                                            <div slot-scope="{ row }" class="d-flex custom-size">
                                                <el-tooltip content="Preview" placement="top">
                                                    <base-button
                                                        v-if="row.is_proctored_exam == 1"
                                                        @click.prevent="getProctoredExamCertificate(row.certificate_url,1)"
                                                        class="success"
                                                        type=""
                                                        size="sm"
                                                        icon
                                                        data-toggle="tooltip"
                                                        data-original-title="Preview"
                                                    >
                                                        <i v-if="!row.show_loader" class="text-success fa fa-eye"></i>
                                                        <i v-if="row.show_loader" class="text-success fas fa-spin fa-spinner"></i>
                                                    </base-button>
                                                    <base-button
                                                        v-else
                                                        @click="generatePreviewCertificate(row.course_id,user_id,row.id,row.is_coursefolder)"
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
                                                        v-if="row.is_proctored_exam != 1"
                                                        @click="generateDownloadCertificate(row.course_id,user_id,row.id,row.is_coursefolder)"
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
                                            </div>
                                        </el-table-column>
                                    </el-table>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-2">

                                <div class="user-eltable">
                                    <h3 style="padding:10px;">Uploaded Certificates</h3>
                                    <el-table
                                        header-row-class-name="thead-light custom-thead-light"
                                        class="thead-light custom-thead-light addcertificatesGrid"
                                        role="table"
                                        :data="tbl2_data"
                                    >
                                        <!-- <el-table-column min-width="100px" label="">
                                          <template slot-scope="props">
                                            <span>
                                              <input
                                                type="checkbox"
                                                :value="props.row.id"
                                                v-model="checked_certificate"
                                                v-on:input="certificateCheckchange(props.row)"
                                              />
                                            </span>
                                          </template>
                                        </el-table-column> -->
                                        <el-table-column
                                            min-width="250px"
                                            align="left"
                                            label="Course Name"
                                        >
                                            <template slot-scope="props">
                                                <span>{{ props.row.certificate_name }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Completion Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Expiration Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_expiration_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Action"
                                        >
                                            <div slot-scope="{ row }" class="d-flex custom-size">
                      <span
                          class="mx-2"
                          v-for="certificate in row.certificates"
                          :key="certificate.id"
                      >
                       <el-tooltip content="Preview" placement="top">
                            <base-button
                                @click="generateManualCertificate(row.certificate_url)"
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
                                @click="downloadManualCertificate(row.certificate_url)"
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
                            <div class="col-sm-12 mt-2">
                                <div class="user-eltable">
                                    <h3 style="padding:10px;">Expired Certificates</h3>
                                    <el-table
                                        header-row-class-name="thead-light custom-thead-light"
                                        class="thead-light custom-thead-light addcertificatesGrid"
                                        role="table"
                                        :data="expiredData"
                                    >
                                        <!-- <el-table-column min-width="100px" label="">
                                          <template slot-scope="props">
                                            <span>
                                              <input
                                                type="checkbox"
                                                :value="props.row.id"
                                                v-model="checked_certificate"
                                                v-on:input="certificateCheckchange(props.row)"
                                              />
                                            </span>
                                          </template>
                                        </el-table-column> -->

                                        <el-table-column
                                            min-width="250px"
                                            align="left"
                                            label="Course Name"
                                        >
                                            <template slot-scope="props">
                                                <span>{{ props.row.certificate_name }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Completion Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Expiration Date"
                                        >
                                            <template slot-scope="props">
                        <span>{{
                                formattedDate(props.row.certificate_expiration_date)
                            }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column
                                            min-width="200px"
                                            align="left"
                                            label="Action"
                                        >
                                            <div slot-scope="{ row }" class="d-flex custom-size">
                                                <el-tooltip content="Preview" placement="top">
                                                    <base-button
                                                        v-if="row.is_proctored_exam == 1"
                                                        @click.prevent="getProctoredExamCertificate(
                                    row.certificate_url,
                                    2
                                  )"
                                                        class="success"
                                                        type=""
                                                        size="sm"
                                                        icon
                                                        data-toggle="tooltip"
                                                        data-original-title="Preview"
                                                    >
                                                        <i class="text-success fa fa-eye" v-if="!row.show_loader"></i>
                                                        <i class="text-success fas fa-spin fa-spinner" v-if="row.show_loader"></i>
                                                    </base-button>
                                                    <base-button
                                                        v-else
                                                        @click="generatePreviewCertificate(row.course_id,user_id,row.id,row.is_coursefolder)"
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
                                row.is_proctored_exam != 1
                              "
                                                        @click="generateDownloadCertificate(row.course_id,user_id,row.id,row.is_coursefolder)"
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
                                            </div>
                                        </el-table-column>
                                    </el-table>
                                </div>
                            </div>
                        </div>
                    </div>
                </card>
            </div>
        </div>
    </div>
</template>
<script>
import moment from "moment";
import { Table, TableColumn, Select, Option } from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import "sweetalert2/src/sweetalert2.scss";

export default {
    mixins: [clientPaginationMixin],
    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn
    },
    data() {
        return {
            loading: true,
            isCertficateChecked: false,
            baseUrl: this.$baseUrl,
            download_certificate: false,
            download_file_link: "",
            toolbarOptions: ["PdfExport"],
            allowPdfExport: true,
            uploadCertificateModal: false,
            hot_user: "",
            company_id: "",
            hot_token: "",
            user_id: "",
            config: "",
            certificate: {
                certificate_name: "",
                certificate_date: "",
                exp_date: "",
                certificate_file: "",
                course_id: ""
            },
            checked_certificate: [],
            tbl2_data: [],
            courses: [],
            tableData: [],
            expiredData: [],
            selectedRows: [],
            selected: [],
            selectAll: false,
            editor: "",
            course_ids: ""
        };
    },
    created: function() {
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
        this.loading = true;
        this.$http
            .post("employees/certificates", {
                employee_id: this.user_id
            })
            .then(resp => {
                let certificate_data = resp.data.data;
                let expired_data = resp.data.data_expired;
                for (let data of certificate_data) {
                    let obj = {
                        id: data.id,
                        course_id: data.course_id,
                        certificate_date: data.certificate_date,
                        certificate_expiration_date: data.certificate_expiration_date,
                        is_coursefolder: data.is_coursefolder_certificate,
                        certificate_url: data.certificate_url,
                        certificate_name: data.certificate_name,
                        certificate_no: data.certificate_no,
                        is_proctored_exam: data.is_proctored_exam,
                        show_loader: false
                    };
                    if (data.is_coursefolder_certificate) {
                        obj.certificate_name = data.coursefolder.folder_name;
                    } else {
                        obj.certificate_name = data.course.name;
                    }
                    this.tableData.push(obj);
                }
                for (let data of expired_data) {
                    let obj = {
                        id: data.id,
                        course_id: data.course_id,
                        certificate_date: data.certificate_date,
                        certificate_expiration_date: data.certificate_expiration_date,
                        is_coursefolder: data.is_coursefolder_certificate,
                        certificate_url: data.certificate_url,
                        certificate_name: "",
                        is_proctored_exam: data.is_proctored_exam,
                        show_loader: false
                    };
                    if (data.is_coursefolder_certificate) {
                        obj.certificate_name = data.coursefolder.folder_name;
                    } else {
                        obj.certificate_name = data.course.name;
                    }
                    this.expiredData.push(obj);
                }
            })
            .finally(() => (this.loading = false));

        this.$http
            .post("employees/additionalCertificates", {
                employee_id: this.user_id
            })
            .then(resp => {
                let certificate_data = resp.data.data;
                for (let data of certificate_data) {
                    let obj = {
                        id: data.id,
                        course_id: data.course_id,
                        certificate_date: data.certificate_date,
                        certificate_expiration_date: data.certificate_expiration_date,
                        certificate_url: data.certificate_url,
                        certificate_name: data.course.name
                    };
                    this.tbl2_data.push(obj);
                }
            });
    },
    methods: {
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        certificateCheckchange(row) {
            this.isCertficateChecked = true;
            if (this.checked_certificate.includes(row.id)) {
                this.checked_certificate.splice(
                    this.checked_certificate.indexOf(row.id),
                    1
                );
            } else {
                this.checked_certificate.push(row.id);
            }
            if (this.checked_certificate.length == 0) {
                this.isCertficateChecked = false;
            }
        },
        select() {
            this.checked_certificate = [];
            if (!this.selectAll) {
                this.isCertficateChecked = true;
                for (let i in this.tableData) {
                    this.checked_certificate.push(this.tableData[i].id);
                }
                for (let i1 in this.tbl2_data) {
                    this.checked_certificate.push(this.tbl2_data[i1].id);
                }
                for (let i1 in this.expiredData) {
                    this.checked_certificate.push(this.expiredData[i1].id);
                }
            }
            if (this.checked_certificate.length === 0) {
                this.isCertficateChecked = false;
            }
        },

        selectionChange(selectedRows) {
            this.selectedRows = selectedRows;
        },

        getProctoredExamCertificate: function(certificateURL, action) {
            let certificateIndex = null;
            if (action == 1) {
                this.tableData.forEach(function(certificate, index) {
                    if (certificate.certificate_url == certificateURL) {
                        certificateIndex = index;
                        return true;
                    }
                });
                this.tableData[certificateIndex].show_loader = true;
            } else {
                this.expiredData.forEach(function(certificate, index) {
                    if (certificate.certificate_url == certificateURL) {
                        certificateIndex = index;
                        return true;
                    }
                });
                this.expiredData[certificateIndex].show_loader = true;
            }
            this.$http
                .post("course/proctored-exam-certificate", {
                    certificateURL: certificateURL
                })
                .then(resp => {
                    if (action == 1) {
                        this.tableData[certificateIndex].show_loader = false;
                    } else {
                        this.expiredData[certificateIndex].show_loader = false;
                    }
                    window.open(resp.data.certificate_url, "_blank");
                });
        },
        generatePreviewCertificate(course_id,user_id,certificate_id,is_coursefolder){
            window.open(this.baseUrl + '/downloadCourseCertificate/preview/' + course_id +'/' + user_id + '/' + certificate_id + '/' + is_coursefolder);
        },
        generateDownloadCertificate(course_id,user_id,certificate_id,is_coursefolder){
            window.open(this.baseUrl + '/downloadCourseCertificate/download/' + course_id +'/' + user_id + '/' + certificate_id + '/' + is_coursefolder);
        },
        generateManualCertificate(url){
            window.open(url);
        },
        downloadManualCertificate(url){
            window.open(url,'download');
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
    .certificatesGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Check";
    }
    .certificatesGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Course Name";
    }
    .certificatesGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Completion Date";
    }
    .certificatesGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Expirtion Date";
    }
    .certificatesGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Actions";
    }

    .addcertificatesGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Check";
    }
    .addcertificatesGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Course Name";
    }
    .addcertificatesGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Completion Date";
    }
    .addcertificatesGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Expirtion Date";
    }
    .addcertificatesGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Actions";
    }
}
</style>
