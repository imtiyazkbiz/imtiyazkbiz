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
            <div class="row  align-items-center">
              <div class="col-md-6">
                <h2 class="mb-0">Survey Submissions - {{ survey_name }}</h2>
              </div>
              <div class="col-lg-6 col-sm-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                 <base-button class="custom-btn" v-on:click="exportExcel()"
                  >Excel Download</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div
              class="row  d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-3">
                <base-input
                  label="Company"
                  v-model="searchQuerycompany"
                  prepend-icon="fas fa-search"
                  placeholder="Company Name"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <base-input label="Date Range">
                  <el-date-picker
                    v-on:change="changePage(1)"
                    v-model="filters.report_date"
                    type="daterange"
                    unlink-panels
                    value-format="MM/dd/yyyy"
                    format="MM/dd/yyyy"
                    style="width:100%"
                    range-separator="-"
                    start-placeholder="Start date"
                    end-placeholder="End date"
                  >
                  </el-date-picker>
                </base-input>
              </div>

              <div class="col-md-2">
                <base-input label="Course" placeholder="Select Course">
                  <el-select
                    filterable
                    class="select-primary"
                    v-model="filters.course_id"
                    v-on:change="changePage(1)"
                  >
                    <el-option
                      class="select-Select Action"
                      v-for="item in coursesList"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>

              <div class="col-md-2">
                <base-input
                  label="Employee"
                  v-model="searchQueryemployee"
                  placeholder="Employee Name"
                >
                </base-input>
              </div>


              <div class="col-md-2 form-group">
                <base-input label="Showing:">
                  <el-select
                    class="select-primary pagination-select"
                    v-model="perPage"
                    v-on:change="changePage(1)"
                    placeholder="Per page"
                  >
                    <el-option
                      class="select-primary"
                      v-for="item in perPageOptions"
                      :key="item"
                      :label="item"
                      :value="item"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
            </div>

            <div class="user-eltable">
              <el-table
                role="table"
                :data="tableData"
                stripe
                highlight-current-row
                lazy
                row-key="id"
                id="tableOne"
                header-row-class-name="thead-light"
                class="surveySubmissionGrid table-striped"
              >
                <el-table-column min-width="170px" prop="company_name">
                  <template slot="header">
                    <span> COMPANY</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.company_name }}
                  </template>
                </el-table-column>
                  <el-table-column min-width="100px" prop="user_name">
                      <template slot="header">
                        <span @click="sortByColumn('employee')">EMPLOYEE
                            <i v-if="sortedColumn == 'employee' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                        </span>
                      </template>
                      <template slot-scope="props">
                          {{ props.row.employee_name }}
                      </template>
                  </el-table-column>

                  <el-table-column min-width="100px" prop="user_name">
                      <template slot="header">
                        <span @click="sortByColumn('date')">DATE
                            <i v-if="sortedColumn == 'date' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                        </span>
                      </template>
                      <template slot-scope="props">
                          {{ props.row.date }}
                      </template>
                  </el-table-column>

                <el-table-column min-width="170px" prop="assigned_courses">
                  <template slot="header">
                    <span>COURSES</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.course_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="60px" prop="assigned_courses">
                  <template slot="header">
                    <span>ACTIONS</span>
                  </template>

                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/survey_submissions_detail?id=' +
                          props.row.employee_id +
                          '&test=' +
                          props.row.survey_id +
                          '&course=' +
                          props.row.course_id
                      "
                    >
                      <base-button
                        class="success"
                        type=""
                        size="md"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Edit"
                      >
                        <i class="text-primary fas fa-address-book"></i>
                      </base-button>
                    </router-link>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div slot="footer" class="d-flex justify-content-end ">
            <nav v-if="pagination && tableData.length > 0">
              <div class="row">
                <div class="col-md-12">
                  <ul
                    class="pagination custompagination  justify-content-end align-items-center"
                  >
                    <p class="p-0 m-0 mr-2">
                      Showing {{ tableData.length }} of {{ totalData }} entries
                    </p>
                    <li
                      class="page-item"
                      :class="{ disabled: currentPage === 1 }"
                    >
                      <a
                        class="page-link"
                        href="#"
                        @click.prevent="changePage(currentPage - 1)"
                        ><i class="fa fa-caret-left "></i>
                      </a>
                    </li>
                    <li
                      v-for="(page, index) in pagesNumber"
                      class="page-item"
                      :class="{ active: page == currentPage }"
                      v-bind:key="index"
                    >
                      <a
                        href="javascript:void(0)"
                        @click.prevent="changePage(page)"
                        class="page-link"
                        >{{ page }}</a
                      >
                    </li>
                    <li
                      class="page-item"
                      :class="{
                        disabled: currentPage === last_page
                      }"
                    >
                      <a
                        class="page-link"
                        href="#"
                        @click.prevent="changePage(currentPage + 1)"
                        ><i class="fa fa-caret-right "></i
                      ></a>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>
          </div>
        </card>
      </div>
    </div>
  </div>
</template>

<script>
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import XLSX from "xlsx";

import moment from "moment";
let timeout = null;
export default {
  name: "companies",
  mixins: [serverSidePaginationMixin],
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
      test_type: [
        {
          label: "All",
          value: ""
        },
        {
          label: "Lesson",
          value: "1"
        },
        {
          label: "Test",
          value: "2"
        }
      ],

      coursesList: [
        {
          label: "All",
          value: ""
        }
      ],
      searchQuerycompany: "",
      searchQueryemployee: "",
      filters: {
        testType: "",
        course_id: "",
        report_date: ""
      },
      survey_id: "",
      survey_name: "",
      tableData: [],
      hot_user: "",
      hot_token: "",
      editor: ""
    };
  },
  watch: {
    searchQuerycompany: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    },
    searchQueryemployee: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    }
  },
  created: function() {
    if (this.$route.query.id) {
      this.survey_id = this.$route.query.id;
    }
    if (this.$route.query.name) {
      this.survey_name = this.$route.query.name;
    }
    this.$http
      .post("course/all_courses", {
        course_status: "Active"
      })
      .then(resp => {
        let courses = resp.data.courses;
        for (let course of courses) {
          let obj = {
            value: course.id,
            label: course.name
          };

          this.coursesList.push(obj);
        }
      })
      .finally(() => (this.loading = false));

    this.fetchData();
  },
  methods: {
    resetFilters() {
      this.filters.course_id = "";
      this.searchQuerycompany = "";
      this.searchQueryemployee = "";
      this.filters.report_date = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/survey_submission_report", {
          id: this.survey_id,
          search_company: this.searchQuerycompany,
          search_employee: this.searchQueryemployee,
          report_start_date: this.filters.report_date[0],
          report_end_date: this.filters.report_date[1],
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
          course_id: this.filters.course_id
        })
        .then(resp => {
          this.tableData = [];
          let report_data = resp.data.report;
          let total_data = resp.data.total;
          this.totalData = total_data;
          for (let data of report_data) {
            let obj = {
              employee_id: data.employee_id,
              survey_id: data.survey_id,
              course_id: data.course_id,
              employee_name: data.employee_name,
              company_name: data.company_name,
              course_name: data.course_name,
              date: moment(data.updated_at).format("MM-DD-YYYY")
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    exportExcel() {
      this.$http
        .post("course/survey_submission_report", {
          id: this.survey_id,
          search_company: this.searchQuerycompany,
          search_employee: this.searchQueryemployee,
          report_start_date: this.filters.report_date[0],
          report_end_date: this.filters.report_date[1],
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
          isExcelDownload: true,
        })
        .then(resp => {
          let report_data = resp.data.download;
          this.items = report_data;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, "SurveyReport.xlsx");
        })
        .finally(
          () => ((this.loading = false))
        );
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
  .surveySubmissionGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Company";
  }
  .surveySubmissionGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Employee";
  }
  .surveySubmissionGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Date";
  }
  .surveySubmissionGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Courses";
  }
  .surveySubmissionGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Action";
  }
}
</style>
