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
                <h2 class="mb-0">Survey Report</h2>
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
              <div class="col-md-4">
                <base-input
                  label="Search"
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search Keyword"
                >
                </base-input>
              </div>
              <div class="col-md-6">
                <base-input label="Date Range">
                  <el-date-picker
                    v-on:change="changePage(1)"
                    v-model="filters.report_date"
                    type="daterange"
                    unlink-panels
                    value-format="MM/dd/yyyy"
                    style="width:100%"
                    range-separator="-"
                    start-placeholder="Start date"
                    end-placeholder="End date"
                  >
                  </el-date-picker>
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
                class="surveyReportGrid table-striped"
              >
                <el-table-column min-width="220px" prop="company_name">
                  <template slot="header">
                    <span> NAME</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.survey_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="100px" prop="location">
                  <template slot="header">
                    <span> CREATED DATE </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.created_at }}
                  </template>
                </el-table-column>

                <el-table-column min-width="100px">
                  <template slot="header">
                    <span># SUBMISSIONS </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.submissions }}
                  </template>
                </el-table-column>

                <el-table-column min-width="100px">
                  <template slot="header">
                    <span>ACTIONS</span>
                  </template>
                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/survey_submissions?id=' +
                          props.row.id +
                          '&name=' +
                          props.row.survey_name
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
      searchQuery: "",

      filters: {
        testType: "",
        course_id: "",
        report_date: ""
      },

      tableData: [],
      hot_user: "",
      hot_token: "",
      editor: "",
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
          }
        ]
      }
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
    this.fetchData();
  },
  methods: {
    resetFilters() {
      this.searchQuery = "";
      this.filters.report_date = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/survey_report", {
          search: this.searchQuery,
          report_start_date: this.filters.report_date[0],
          report_end_date: this.filters.report_date[1],
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage
        })
        .then(resp => {
          this.tableData = [];
          let report_data = resp.data.report;
          let total_data = resp.data.total;
          this.totalData = total_data;
          for (let data of report_data) {
            let obj = {
              id: data.id,
              survey_name: data.name,
              created_at: moment(data.created_at).format("MM-DD-YYYY"),
              submissions: data.submissions
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },

    exportExcel() {
      this.$http
        .post("course/survey_report", {
          search: this.searchQuery,
          report_start_date: this.filters.report_date[0],
          report_end_date: this.filters.report_date[1],
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage
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
          () => (this.loading = false)
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
  .surveyReportGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Name";
  }
  .surveyReportGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Created At";
  }
  .surveyReportGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "# Submissions";
  }
  .surveyReportGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Action";
  }
}
</style>
