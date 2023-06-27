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
                <h2 class="mb-0">Test Question Report</h2>
              </div>
             <div class="col-lg-6 col-sm-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
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
                  label="Search"
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search Keyword"
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
                    style="width:100%"
                    value-format="MM/dd/yyyy"
                    range-separator="-"
                    start-placeholder="Start date"
                    end-placeholder="End date"
                  >
                  </el-date-picker>
                </base-input>
              </div>

              <div class="col-md-3">
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

              <div class="col-md-3">
                <base-input label="Test Type">
                  <el-select
                    class="select-primary"
                    v-model="filters.testType"
                    v-on:change="changePage(1)"
                  >
                    <el-option
                      class="select-Select Action"
                      v-for="item in test_type"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
            </div>

            <div class="row  flex-wrap">
              <div class="col-md-2">
                <base-input label="Bulk Action">
                  <el-select
                    class="select-primary"
                    v-model="filters.bulkAction"
                    v-on:change="exportExcel()"
                  >
                    <el-option
                      class="select-Select Action"
                      v-for="item in bulk_action"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>

              <div class="col-md-8 form-group"></div>

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
                class="testReportGrid table-striped"
              >
                <el-table-column min-width="25%">
                  <template slot="header">
                    <span> QUESTION</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.question_title }}
                  </template>
                </el-table-column>

                <el-table-column min-width="15%">
                  <template slot="header">
                    <span> TEST NAME </span>
                  </template>
                  <template slot-scope="props">
                    <span v-if="props.row.test_type == 1">
                      {{ props.row.test_name }}
                    </span>
                    <span v-else>
                      Test
                    </span>
                  </template>
                </el-table-column>

                <el-table-column min-width="25%">
                  <template slot="header">
                    <span>COURSE NAME </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.course_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="15%">
                  <template slot="header">
                    <span>TEST TYPE</span>
                  </template>
                  <template slot-scope="props">
                    <span v-if="props.row.test_type == 1"> Lesson </span>
                    <span v-if="props.row.test_type == 2"> Test </span>
                    <span v-else> </span>
                  </template>
                </el-table-column>

                <el-table-column min-width="10%">
                  <template slot="header">
                    <span>WRONG ATTEMPTS</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.wrong_count }}
                  </template>
                </el-table-column>

                <el-table-column min-width="10%">
                  <template slot="header">
                    <span>CORRECT ATTEMPTS</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.correct_count }}
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

      bulk_action: [
        {
          label: "Select",
          value: ""
        },
        {
          label: "Download Excel",
          value: "download_excel"
        }
      ],

      coursesList: [
        {
          label: "All",
          value: ""
        }
      ],
      searchQuery: "",

      filters: {
        bulkAction: "",
        testType: "",
        course_id: "",
        report_date: ""
      },

      tableData: [],
      hot_user: "",
      hot_token: "",
      editor: ""
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
  created: function() {
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
      this.filters.testType = "";
      this.filters.bulkAction = "";
      this.searchQuery = "";
      this.filters.report_date = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/test_question_report", {
          search: this.searchQuery,
          test_type: this.filters.testType,
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
              course_name: data.course_name,
              question_title: data.question_title,
              test_name: data.lesson_name,
              correct_count: data.correctCount,
              wrong_count: data.wrongCount,
              test_type: data.test_type
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    exportExcel() {
      this.$http
        .post("course/test_question_report", {
          search: this.searchQuery,
          test_type: this.filters.testType,
          report_start_date: this.filters.report_date[0],
          report_end_date: this.filters.report_date[1],
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
          course_id: this.filters.course_id
        })
        .then(resp => {
          let report_data = resp.data.download;
          this.items = report_data;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, "TestReport.xlsx");
        })
        .finally(
          () => ((this.loading = false), (this.filters.bulkAction = ""))
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
  .testReportGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Question";
  }
  .testReportGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Test Name";
  }
  .testReportGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Course Name";
  }
  .testReportGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Test Type";
  }
  .testReportGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Wrong Attempts";
  }
  .testReportGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Correct Attempts";
  }
}
</style>
