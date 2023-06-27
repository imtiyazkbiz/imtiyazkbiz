<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <!-- <div class="col-lg-6 col-5">
          <h6 class="h2 text-white d-inline-block mb-0">
            {{ certificate_name }}
          </h6>
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            {{ course_name }}
          </nav>
        </div> -->
        <h3 class="text-white d-inline-block mb-0"></h3>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pt-1">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-4 text-left">
                <h2 class="mb-0">{{ certificate }} Certificate</h2>
              </div>
              <div class="col-lg-8 col-12 text-right certificate-btn">
                <base-button class="custom-btn" name="Certificate Reset Filters" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                <div class="d-inline-block">
                  <!-- <JsonExcel
                    :data="json_data"
                    header="applcation/vnd.ms-excel"
                    :exportFields="json_fields"
                    :name="course_name + ' Certificates.xls'"
                  >
                    <base-button class="custom-btn" style="margin-top:5px;">
                      Export Certificate Data
                    </base-button>
                  </JsonExcel> -->
                  <el-tooltip content="View Certificates" placement="top">
                    <base-button
                    name="View Certificate"
                      @click="viewCertificates"
                      class="custom-btn"
                      data-toggle="tooltip"
                      data-original-title="View Certificates"
                    >
                      View Certificates
                    </base-button>
                  </el-tooltip>
                  <!-- <a
                    :href="
                      baseUrl +
                        '/employee/certificate_manual/saved_pdf/result.pdf'
                    "
                    target="_blank"
                    @click="viewCertificates"
                  >
                    View Certificates</a
                  > -->
                  <el-tooltip content="Export Certificate Data" placement="top">
                    <base-button
                      name="Export Certificate Data"
                      @click="downloadExcel"
                      class="custom-btn"
                      data-toggle="tooltip"
                      data-original-title="Export Certificate Data"
                    >
                      Export Certificate Data
                    </base-button>
                  </el-tooltip>
                </div>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <!-- <a
                  :href="
                    baseUrl +
                      '/downloadAllCourseCertificate/' +
                      employeeUrl.join('_') +
                      '/' +
                      courseUrl.join('_')
                  "
                  class="custom-btn"
                  data-toggle="tooltip"
                  data-original-title="Download"
                  target="_blank"
                >
                  <span> Download Certificates</span></a
                > -->
              </div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex mb-2 justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-3">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <div class="col-md-3 form-group">
                <label>Company:</label>

                <el-select
                  filterable
                  name="Certificate Screen Company filter"
                  class="select-primary"
                  v-on:change="fetchData()"
                  v-model="filters.location_id"
                  placeholder="Filter by Location"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in locations"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>

              <div class="col-md-3 form-group">
                <label>Status:</label>
                <el-select
                  class="select-primary"
                  v-on:change="fetchData()"
                  v-model="filters.certificateStatus"
                  placeholder="Filter by Certificate Status"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in status"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>
              <!-- <div class="col-md-3 form-group">
                <label>Showing:</label>

                <el-select
                  class="select-primary pagination-select"
                  v-model="pagination.perPage"
                  placeholder="Per page"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in pagination.perPageOptions"
                    :key="item"
                    :label="item"
                    :value="item"
                  >
                  </el-option>
                </el-select>
              </div> -->
            </div>
            <div class="user-eltable">
              <el-table
                :data="tableData"
                class="certGrid"
                role="table"
                row-key="id"
                header-row-class-name="thead-light custom-thead-light"
                @selection-change="selectionChange"
              >
                <el-table-column min-width="150px" label="">
                  <template slot="header">
                    <span @click="sortByColumn(0)"
                      >First Name
                      <i
                        v-if="sortedColumn == 0 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.first_name }}</span>
                  </template>
                </el-table-column>

                <el-table-column min-width="150px" label="">
                  <template slot="header">
                    <span @click="sortByColumn(1)"
                      >Last Name
                      <i
                        v-if="sortedColumn == 1 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.last_name }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="">
                  <template slot="header">
                    <span @click="sortByColumn(2)"
                      >Completion Date
                      <i
                        v-if="sortedColumn == 2 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ formattedDate(props.row.certificate_date) }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="">
                  <template slot="header">
                    <span @click="sortByColumn(3)"
                      >Expiration Date
                      <i
                        v-if="sortedColumn == 3 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ formattedDate(props.row.expiration_date) }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Location">
                  <template slot="header">
                    <span @click="sortByColumn(4)"
                      >Location
                      <i
                        v-if="sortedColumn == 4 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.location }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="120px" label="Status">
                  <template slot-scope="props">
                    <span v-if="isActive(props.row)">Active</span>
                    <span v-else>Expired</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Course">
                  <template slot="header">
                    <span @click="sortByColumn(5)"
                      >Course
                      <i
                        v-if="sortedColumn == 5 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.course_name }}</span>
                  </template>
                </el-table-column>

                <el-table-column
                  min-width="80px"
                  class-name="td-actions"
                  label="Action"
                >
                  <template slot-scope="props">
                    <div class="row">
                      <div class="col-md-1">
                        <el-tooltip
                          content="Preview"
                          placement="top"
                          v-if="props.row.is_proctored_exam == 1"
                        >
                          <a
                            @click.prevent="
                              getProctoredExamCertificate(
                                props.row.certificate_url
                              )
                            "
                            data-original-title="Preview"
                            data-toggle="tooltip"
                          >
                            <span>
                              <i
                              name="Preview Proctored Exam Certificate"
                                class="text-success fa fa-eye"
                                v-if="!props.row.show_loader"
                              ></i>
                              <i
                                class="text-success fas fa-spin fa-spinner"
                                v-if="props.row.show_loader"
                              ></i>
                            </span>
                          </a>
                        </el-tooltip>
                        <el-tooltip content="Preview" placement="top" v-else>
                          <a
                            :href="
                              baseUrl +
                                '/downloadCourseCertificate/preview/' +
                                props.row.course_id +
                                '/' +
                                props.row.employee_id +
                                '/' +
                                props.row.certificate_id
                            "
                            data-toggle="tooltip"
                            data-original-title="Preview"
                            target="_blank"
                          >
                            <span> <i name="Preview Certificate" class="text-success fa fa-eye"></i> </span
                          ></a>
                        </el-tooltip>
                      </div>
                      <div class="col-md-1">
                        <el-tooltip
                          content="Download"
                          placement="top"
                          v-if="props.row.is_proctored_exam == 0"
                        >
                          <a
                            :href="
                              baseUrl +
                                '/downloadCourseCertificate/download/' +
                                props.row.course_id +
                                '/' +
                                props.row.employee_id +
                                '/' +
                                props.row.certificate_id
                            "
                            data-toggle="tooltip"
                            data-original-title="Download"
                            target="_blank"
                          >
                            <span>
                              <i name="Download Certificate" class="text-warning fa fa-download"></i> </span
                          ></a>
                        </el-tooltip>
                      </div>
                    </div>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div
            slot="footer"
            class="col-12 d-flex justify-content-center justify-content-sm-between flex-wrap"
          >
            <div class="">
              <!-- <p class="card-category">
                Showing {{ from + 1 }} to {{ to }} of {{ total }} entries

                <span v-if="selectedRows.length">
                  &nbsp; &nbsp; {{ selectedRows.length }} rows selected
                </span>
              </p> -->
            </div>
            <base-pagination
              class="pagination-no-border"
              v-model="pagination.currentPage"
              :per-page="pagination.perPage"
              :total="total"
            >
            </base-pagination>
          </div>
        </card>
      </div>
    </div>
    <modal :show.sync="download_certificate">
      <h6 class="title title-up ">Download all the certificates</h6>
      <a download :href="download_file_link" class="btn btn-success"
        >Download</a
      >
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import { BasePagination } from "@/components";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
//import swal from 'sweetalert';
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import XLSX from "xlsx";
import moment from "moment";
let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    BasePagination,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      baseUrl: this.$baseUrl,
      download_certificate: false,
      download_file_link: "",
      isLoading: false,
      fullPage: true,
      company_id: "",
      hot_user: "",
      hot_token: "",
      config: "",
      searchQuery: "",
      course_id: "",
      tbl_data: [],
      course_name: "",
      resultGenerated: false,
      json_fields: {
        "Employee First Name": "First Name",
        "Employee Last Name": "Last Name",
        "Course Name": "Course Name",
        "Certificate Name": "Certificate Name",
        "Certificate Date": "Certificate Date",
        "Certificate Expiry Date": "Certificate Expiry Date"
      },

      json_data: [],
      locationManager: false,
      location_id: "",
      certificate_name: "",
      status: [
        {
          label: "Active Certificates",
          value: "Active Certificates"
        },
        {
          label: "Expired Certificates",
          value: "Expired Certificates"
        },
        {
          label: "Show All",
          value: ""
        }
      ],
      locations: [
        {
          label: "All",
          value: "",
          parent_id: 0
        }
      ],
      filters: {
        certificateStatus: "Active Certificates",
        location_id: ""
      },
      editor: "",
      interface: "",
      tableData: [],
      selectedRows: [],
      employeeUrl: [],
      courseUrl: [],
      certificate: "",
      course_ids: [],
      certificate_ids: [],
      employee_ids: []
    };
  },

  watch: {
    searchQuery: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    }
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (localStorage.getItem("hot-user") === "employee") {
      this.editor = "employee";
    } else if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
     } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "admin";
      this.company_id = localStorage.getItem("hot-user-id");
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
    }

    if (this.$route.query.id) {
      this.certificate_id = this.$route.query.id;
    }
    if (this.$route.query.course) {
      this.course_id = this.$route.query.course;
    }
    if (this.$route.query.certificate) {
      this.certificate = this.$route.query.certificate;
    }
    // this.fetchData();
    this.setDefaultFilterData();
    this.$http
      .post("location/all_company_location", {
        role: this.editor
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
      });
  },
  methods: {
    isActive(row) {
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth() + 1;
      var yyyy = today.getFullYear();
      if (dd < 10) {
        dd = "0" + dd;
      }
      if (mm < 10) {
        mm = "0" + mm;
      }
      today = yyyy + "-" + mm + "-" + dd;
      if (row.expiration_date >= today) {
        return true;
      }
    },
    formattedDate(data) {
      return moment(data).format("MM-DD-YYYY");
    },
    fetchData() {
      this.loading = true;
      let self = this;
      this.$http
        .post("course/pass_employee", {
          search: this.searchQuery,
          certificate_status: this.filters.certificateStatus,
          course_id: this.course_id,
          certificate_id: this.certificate_id,
          interface: "Employee",
          company_id: this.filters.location_id,
          column: this.sortedColumn,
          order: this.order
        })
        .then(resp => {
          let employee_data = resp.data.employee;
          this.tableData = [];
          this.employeeUrl = [];
          this.courseUrl = [];
          this.json_data = [];
          for (let data of employee_data) {
            let obj = [];

            obj = {
              certificate_id: data.employee_certifcate_id,
              employee_id: data.employee_id,
              course_id: data.course_id,
              course_name: data.course_name,
              first_name: data.first_name,
              last_name: data.last_name,
              location: data.company_name,
              certificate_date: data.employee_course_date_completed,
              expiration_date: data.certificate_expiration_date,
              is_proctored_exam: data.is_proctored_exam,
              certificate_url: data.certificate_url,
              show_loader: false
            };
            this.employeeUrl.push(obj.employee_id);
            this.courseUrl.push(obj.course_id);
            this.tableData.push(obj);
            this.course_name = obj.course_name;
            let row = {
              "First Name": data.first_name,
              "Last Name": data.last_name,
              "Course Name": data.course_name,
              "Certificate Name": data.certificate_name,
              "Certificate Date": data.employee_course_date_completed,
              "Certificate Expiry Date": data.certificate_expiration_date
            };

            this.json_data.push(row);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_certificate_detail_search_data",
        JSON.stringify({
          search: this.searchQuery,
          certificate_status: this.filters.certificateStatus,
          course_id: this.course_id,
          certificate_id: this.certificate_id,
          company_id: this.filters.location_id,
          column: this.sortedColumn,
          order: this.order
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_certificate_detail_search_data")
      );

      if (previousStateData !== null) {
        this.searchQuery = previousStateData.search
          ? previousStateData.search
          : this.searchQuery;
        this.filters.certificateStatus = previousStateData.certificate_status
          ? previousStateData.certificate_status
          : this.filters.certificateStatus;
        this.course_id = this.$route.query.course
          ? this.$route.query.course
          : previousStateData.course_id
          ? previousStateData.course_id
          : this.course_id;
        this.certificate_id = this.$route.query.id
          ? this.$route.query.id
          : previousStateData.certificate_id
          ? previousStateData.certificate_id
          : this.certificate_id;
        this.company_id = previousStateData.company_id
          ? previousStateData.company_id
          : this.company_id;
        this.sortedColumn = previousStateData.column
          ? previousStateData.column
          : this.sortedColumn;
        this.order = previousStateData.order
          ? previousStateData.order
          : this.order;
      }
      this.fetchData();
    },
    resetFilters() {
      this.company_id = "";
      this.filters.certificateStatus = "Active Certificates";
      this.searchQuery = "";
      this.filters.location_id = "";
      this.sortedColumn = 0;
      this.order = "asc";
      this.fetchData();
    },
 viewCertificates() {
      this.loading = true;
      this.certificate_ids = [];
      this.course_ids = [];
      this.employee_ids = [];
      for (let data of this.tableData) {
        this.course_ids.push(data.course_id);
        this.employee_ids.push(data.employee_id);
        this.certificate_ids.push(data.certificate_id);
      }
      this.$http
        .post("user/saveCertificatePdf", {
          course_id: this.course_ids,
          employee_id: this.employee_ids,
          certificate_id: this.certificate_ids,
        })
        .then((resp) => {
          this.resultGenerated = true;
          if (resp.data.user_not_merged) {
            Swal.fire({
              title: `Error`,
              html:"Due to security settings, the following certificates cannot be included in the mass download:</br>" +
                resp.data.user_not_merged +
                "</br> Please download the above user certificates individually.",
              icon: "error",
            }).then((result) => {
              if (result.value) {
                window.open(
                  this.$baseUrl +
                    "/employee/certificate_manual/saved_pdf/" +
                    resp.data.file_name,
                  "_blank"
                );
              }
            });
          } else {
            window.open(
              this.$baseUrl +
                "/employee/certificate_manual/saved_pdf/" +
                resp.data.file_name,
              "_blank"
            );
          }
        })
        .catch(function (error) {
          console.log(error);
          if (error.response.status === 422) {
            return Swal.fire({
              title: `Error`,
              html: `Due to security settings, these certificates cannot be included in the mass download.</br>
                    Please download these users certificates individually.`,
              icon: "error",
            });
          }
        }).finally(() => (this.loading = false));
    },

    downloadExcel() {
      this.loading = true;
      this.$http
        .post("course/pass_employee", {
          search: this.searchQuery,
          certificate_status: this.filters.certificateStatus,
          course_id: this.course_id,
          certificate_id: this.certificate_id,
          interface: "Employee"
        })
        .then(resp => {
          let employee_data = resp.data.employee;
          for (let data of employee_data) {
            let obj = [];
            obj = {
              course_name: data.course_name
            };
          }
          //this.course_name = this.course_name;
          this.items = resp.data.download;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, this.course_name + "Certificate.xlsx");
        })
        .finally(() => (this.loading = false));
    },
    printAllCertificates() {
      this.loading = true;
      this.$http
        .get("course/download_certificate/" + this.course_id)
        .then(resp => {
          this.download_certificate = true;
          this.download_file_link = resp.data;
        })
        .finally(() => (this.loading = false));
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    },
    getProctoredExamCertificate: function(certificateURL) {
      console.clear();
      console.log(certificateURL);
      let certificateIndex = null;
      this.tableData.forEach(function(certificate, index) {
        if (certificate.certificate_url == certificateURL) {
          certificateIndex = index;
          return true;
        }
      });
      this.tableData[certificateIndex].show_loader = true;
      this.$http
        .post("course/proctored-exam-certificate", {
          certificateURL: certificateURL
        })
        .then(resp => {
          this.tableData[certificateIndex].show_loader = false;
          window.open(resp.data.certificate_url, "_blank");
        });
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
  .certGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "First Name";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Last Name";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Competion Date";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Expiration Date";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Status";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Course";
  }
  .certGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Actions";
  }
}
</style>
