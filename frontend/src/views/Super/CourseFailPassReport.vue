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
              <div class="col-md-6 col-lg-6">
                <h2 class="mb-0">Course Pass/Fail Report</h2>
              </div>

             <div class="col-lg-6 col-md-6 text-right">
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
              class="
                row
                d-flex
                justify-content-center justify-content-sm-between
                flex-wrap
              "
            >
              <div class="col-md-3">
                <base-input
                  label="Search:"
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search Keyword"
                >
                </base-input>
              </div>

              <div class="col-md-3">
                <base-input label="Location:">
                  <el-select
                    filterable
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
                </base-input>
              </div>
              <div class="col-md-3">
                <base-input label="Course" placeholder="Select Course">
                  <el-select
                    filterable
                    v-on:change="fetchData()"
                    class="select-primary"
                    v-model="filters.course_id"
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
                class="failPassGrid table-striped"
              >
                <el-table-column min-width="80px" prop="first_name">
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
                    {{ props.row.first_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="80px" prop="last_name">
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
                    {{ props.row.last_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="100px">
                  <template slot="header">
                    <span @click="sortByColumn(2)"
                      >Location
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
                    <span v-if="props.row.location">
                      {{ props.row.location }}</span
                    >
                    <span v-else> -</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="100px">
                  <template slot="header">
                    <span @click="sortByColumn(3)"
                      >Course
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
                    <span v-if="props.row.course_name">
                      {{ props.row.course_name }}</span
                    >
                    <span v-else> -</span>
                  </template>
                </el-table-column>

                <el-table-column min-width="80px">
                 <template slot="header">
                    <span @click="sortByColumn(4)"
                      >Status
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
                    <span v-if="props.row.course_status == 1">Passed</span>
                    <span v-else-if="props.row.course_status == 0">Failed</span>
                    <span v-else>-</span>
                  </template>
                </el-table-column>

                  <el-table-column min-width="80px">
                      <template slot="header">
                          Percentage
                      </template>
                      <template slot-scope="props">
                          {{ props.row.percentage }}
                      </template>
                  </el-table-column>

                <el-table-column min-width="100px">
                  <template slot="header">
                    <span @click="sortByColumn(5)"
                      >Completion Date
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
                    <span v-if="props.row.completion_date !== 'Invalid date'">
                      {{ props.row.completion_date }}</span
                    >
                    <span v-else> -</span>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div slot="footer" class="d-flex justify-content-end">
            <nav v-if="pagination && tableData.length > 0">
              <div class="row">
                <div class="col-md-12">
                  <ul
                    class="
                      pagination
                      custompagination
                      justify-content-end
                      align-items-center
                    "
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
                        ><i class="fa fa-caret-left"></i>
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
                        disabled: currentPage === last_page,
                      }"
                    >
                      <a
                        class="page-link"
                        href="#"
                        @click.prevent="changePage(currentPage + 1)"
                        ><i class="fa fa-caret-right"></i
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
import {DatePicker, Option, Select, Table, TableColumn} from "element-ui";
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
    [DatePicker.name]: DatePicker,
  },
  data() {
    return {
      loading: false,
      locations: [
        {
          label: "All",
          value: "",
        },
      ],
      coursesList: [
        {
          label: "All",
          value: "",
        },
      ],
      searchQuery: "",

      filters: {
        location_id: "",
        course_id: "",
      },

      tableData: [],
      editor: "",
      totalData: "",
    };
  },
  watch: {
    searchQuery: function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    },
  },
  created: function () {
    if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }
     if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
     }
    this.$http
      .post("location/all_company_location", {
        role: this.editor,
      })
      .then((resp) => {
        for (let loc of resp.data) {
          let obj = {
            label: loc.name,
            value: loc.id,
          };
          this.locations.push(obj);
        }
      });
    this.$http
      .post("course/all_courses", {
        course_status: "Active",
      })
      .then((resp) => {
        let courses = resp.data.courses;
        for (let course of courses) {
          let obj = {
            value: course.id,
            label: course.name,
          };

          this.coursesList.push(obj);
        }
      })
      .finally(() => (this.loading = false));
    this.fetchData();
  },
  methods: {
    resetFilters() {
      this.searchQuery = "";
      this.filters.location_id = "";
      this.filters.course_id = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/fail_pass_report", {
          search: this.searchQuery,
          page: this.currentPage,
          column: this.sortedColumn,
          per_page: this.perPage,
          order: this.order,
          company_id: this.filters.location_id,
          course_id: this.filters.course_id,
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
              course_name: data.course_name,
              completion_date: "",
              course_status: data.employee_course_status,
              location: data.company_name,
              percentage: data.percentage,
            };

            if (
              data.employee_course_date_completed !== null ||
              data.employee_course_date_completed != "0000-00-00"
            ) {
              obj.completion_date = moment(
                data.employee_course_date_completed
              ).format("MM-DD-YYYY");
            }

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },

    exportExcel() {
      this.$http
        .post("course/fail_pass_report", {
          search: this.searchQuery,
          page: this.currentPage,
          column: this.sortedColumn,
          per_page: this.perPage,
          order: this.order,
          company_id: this.filters.location_id,
          course_id: this.filters.course_id,
          isExcelDownload: true,
        })
        .then((resp) => {
          let report_data = resp.data.download;
          this.items = report_data;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, "CourseFailPassReport.xlsx");
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .failPassGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "First Name";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Last Name";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Location";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Course";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Attempt Date";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Status";
  }
  .failPassGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Completion Date";
  }
}
</style>
