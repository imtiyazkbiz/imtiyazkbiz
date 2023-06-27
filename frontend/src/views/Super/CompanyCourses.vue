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
              <div class="col-md-12 text-left">
                <h2 class="mb-0">Courses</h2>
              </div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex justify-content-center justify-content-sm-between flex-wrap mb-2"
            >
              <div class="col-md-5">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  v-on:keyup="fetchData()"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>

              <div class="col-md-4 form-group" v-if="editor != 'manager'">
                <label>Status:</label>
                <el-select
                  v-model="filters.courseStatus"
                  v-on:change="changePage(1)"
                  placeholder="Filter by Course Status"
                >
                  <el-option
                    class="select-default"
                    v-for="item in status"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>
              <div class="col-md-3 form-group">
                <label>Showing:</label>
                <el-select
                  v-on:change="changePage(1)"
                  class="select-primary pagination-select"
                  v-model="perPage"
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
              </div>
            </div>

            <div class="user-eltable">
              <el-table
                :data="tableData"
                row-key="id"
                role="table"
                class="coursesGrid"
                header-row-class-name="thead-light custom-thead-light"
              >
                <el-table-column
                  align="left"
                  min-width="400px"
                  label="Course Name"
                  prop="course_name"
                  sortable
                >
                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/company_course_details?id=' +
                          props.row.id +
                          '&certificate_id=' +
                          props.row.certificate_id
                      "
                    >
                      <span v-if="secondaryCourseCheack && props.row.secondary_course_name">{{ props.row.secondary_course_name }}</span>
                       <span v-else>{{ props.row.course_name }}</span></router-link
                     >
                  </template>
                </el-table-column>
                <el-table-column
                  align="left"
                  min-width="200px"
                  label="Course Length (mins)"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.course_length }}</span>
                  </template>
                </el-table-column>

                <el-table-column
                  align="left"
                  min-width="200px"
                  label="Assigned Employees"
                >
                  <template slot-scope="props">
                    {{ props.row.assigned_employees }}
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="120px"
                  label="Status"
                  prop="status"
                  v-if="editor != 'manager'"
                >
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                      v-on:click="changeStatus(props.$index, props.row)"
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.company_course_status"
                        type="success"
                        v-model="props.row.company_course_status"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.company_course_status"
                      ></base-switch>
                    </div>
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
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
//import swal from 'sweetalert';
import Swal from "sweetalert2";
let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      employee_course_name: "",
      courseEmployeeData: [],
      EmployeeData: [],
      checked_course: "",
      hot_user: "",
      hot_token: "",
      config: "",
      company_id: "",
      bulkValue: "",
      companyName: "",
      assigned_course_id: "",
      status: [
        {
          label: "Active",
          value: "Active"
        },
        {
          label: "Inactive",
          value: "Inactive"
        },
        {
          label: "Show All",
          value: ""
        }
      ],
      filters: {
        courseStatus: "Active"
      },
      searchQuery: "",
      locationManager: false,
      location_id: "",
      tableData: [],
      selectedRows: [],
      admin_id: "",
      editor: "",
      secondaryCourseCheack:0
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
      this.company_id = localStorage.getItem("hot-user-id");
      this.companyName = localStorage.getItem("hot-company-name");
      this.admin_id = localStorage.getItem("hot-admin-id");
    }

    if (localStorage.getItem("hot-sidebar") === "location_manager") {
      this.locationManager = true;
      this.location_id = localStorage.getItem("hot-location-id");
    }
    if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
    }
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.loading = true;
      this.$http
        .post("company/courses", {
          search: this.searchQuery,
          company_course_status: this.filters.courseStatus,
          company_id: this.company_id,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage
        })
        .then(resp => {
          let data = resp.data;
          this.companyName = data.company_name.company_name;
          this.secondaryCourseCheack = data.company_name.secondary_course_status;
          let course_data = resp.data.courses;
          this.totalData = resp.data.total;
          this.tableData = [];
          for (let course of course_data) {
            let obj = {
              id: course.course_id,
              course_name: course.info.name,
              secondary_course_name: course.info.secondary_course_name,
              course_length: course.info.length,
              assigned_location: course.location_count,
              assigned_employees: course.employee_count,
              courseEmployeeData: course.course_employee,
              certificate_id: "",
              company_course_status: true
            };
            if (course.certificate) {
              obj.certificate_id = course.certificate.certificate_id;
            }
            if (course.company_course_status === 1) {
              obj.company_course_status = true;
            } else if (course.company_course_status === 0) {
              obj.company_course_status = false;
            } else {
              obj.company_course_status = course.company_course_status;
            }

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    changeStatus(index, row) {
      let prev_val = row.company_course_status;
      let status = "";
      if (prev_val) {
        status = 0;
      } else {
        status = 1;
      }
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: "You want to change status!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(result => {
          if (result.value) {
            self.$http
              .post(
                "/company/change_course_status",
                {
                  status: status,
                  course_id: row.id,
                  company_id: self.company_id
                },
                self.config
              )
              .then(resp => {
                self.fetchData();
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
                self.tableData[index].company_course_status = !prev_val;
              })
              .catch(function() {
                Swal.fire({
                  title: "Error!",
                  text: "Status Changed!",
                  icon: "error",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
                self.tableData[index].company_course_status = prev_val;
              });
          } else {
            self.tableData[index].company_course_status = prev_val;
          }
        })
        .catch(function() {
          self.tableData[index].company_course_status = prev_val;
        });
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
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
  .coursesGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Course Name";
  }
  .coursesGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Course Length";
  }
  .coursesGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Assigned Employees";
  }
  .coursesGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Status";
  }
}
</style>
