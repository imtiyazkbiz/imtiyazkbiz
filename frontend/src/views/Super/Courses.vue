<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <!-- <div v-if="loading" class="loader-overlay">
      <div class="loader"></div>
    </div>

    <div v-else></div> -->

    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-sm-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pt-1">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-4 text-left">
                <h2 class="mb-0">Courses</h2>
              </div>
               <div class="col-md-8 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                <base-button
                 v-if="canCreate"
                  class="custom-btn mb-1"
                  @click.prevent="duplicateCourse"
                  ><i class="fa fa-plus" aria-hidden="true"></i> Duplicate
                  Course</base-button
                >
                &nbsp;&nbsp;
                <router-link  v-if="canCreate"  to="/create_course"  class="addnew-course">
                  <base-button class="custom-btn mb-1"
                    ><i class="fa fa-plus" aria-hidden="true"></i> New
                    Course</base-button
                  >
                </router-link>
              </div>
            </div>
          </template>
          <div>
            <div
              class="row mb-2 d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-5">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <label>Status:</label>
                <el-select
                  class="select-primary"
                  v-on:change="changePage(1)"
                  v-model="filters.courseStatus"
                  placeholder="Filter by Company Status"
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
              <div class="col-sm-3">
                <label></label>
                <JsonExcel
                  v-if="this.filters.courseStatus === 'Active'"
                  :data="json_data"
                  :exportFields="json_fields"
                  :name="'ActiveCourses.xls'"
                >
                  <base-button class="custom-btn mt-1  w-100">
                    <i class="fas fa-download" aria-hidden="true"></i> Download
                    Excel</base-button
                  >
                </JsonExcel>
                <JsonExcel
                  v-if="this.filters.courseStatus === 'Inactive'"
                  :data="json_data"
                  :exportFields="json_fields"
                  :name="'InactiveCourses.xls'"
                >
                  <base-button class="custom-btn mt-1 w-100">
                    <i class="fas fa-download" aria-hidden="true"></i> Download
                    Excel</base-button
                  >
                </JsonExcel>
                <JsonExcel
                  v-if="this.filters.courseStatus === ''"
                  :data="json_data"
                  :exportFields="json_fields"
                  :name="'AllCourses.xls'"
                >
                  <base-button class="custom-btn mt-1 w-100">
                    <i class="fas fa-download" aria-hidden="true"></i> Download
                    Excel</base-button
                  >
                </JsonExcel>
              </div>
              <div class="col-md-2">
                <label>Showing:</label>
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
              </div>
            </div>
            <div class="user-eltable">
              <el-table
                :data="tableData"
                stripe
                highlight-current-row
                row-key="id"
                role="table"
                class="coursesMainGrid"
                header-row-class-name="thead-light custom-thead-light"
                @selection-change="selectionChange"
              >
                <el-table-column
                  label=""
                  align="left"
                  property=""
                  min-width="30px"
                >
                  <template slot-scope="props">
                    <div v-if="checked_course === props.row.id">
                      <input
                        type="radio"
                        v-model="duplicate"
                        :checked="true"
                        :label="'val_' + props.$index"
                        v-on:input="courseCheckchange(props.row)"
                      />
                    </div>
                    <div v-else>
                      <input
                        type="radio"
                        :checked="false"
                        :label="'val_' + props.$index"
                        v-on:input="courseCheckchange(props.row)"
                      />
                    </div>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Name"
                  align="left"
                  property="name"
                  min-width="300px"
                  prop="name"
                  sortable
                >
                  <template slot-scope="props">
                    <span v-if="canEdit">
                    <router-link :to="'/edit_course?id=' + props.row.id">
                      <span>{{ props.row.name }}</span>
                    </router-link>
                    </span>
                     <span v-else>
                        <span>{{ props.row.name }}</span>
                     </span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Length"
                  property="length"
                  min-width="100px"
                  align="left"
                ></el-table-column>
                <el-table-column
                  label="Cost"
                  property="cost"
                  align="left"
                  min-width="120px"
                ></el-table-column>
                <el-table-column min-width="100px" label="Status" prop="status">
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                      v-on:click="changeStatus(props.$index, props.row)"
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.status"
                        type="success"
                        v-model="props.row.status"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.status"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Actions">
                  <div slot-scope="{ row }" class="d-flex custom-size">
                    <el-tooltip v-if="canEdit" content="Edit" placement="top">
                      <base-button
                        @click.native="editCourse(row.id)"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Edit"
                      >
                        <i class="text-primary fa fa-pencil-square-o"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip v-if="canDelete" content="Delete" placement="top">
                      <base-button
                        @click.native="deleteCourse(row.id)"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                      >
                        <i class="text-danger fa fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip
                      content="Assign to Company"
                      placement="top"
                      v-if="filters.courseStatus === 'Active'"
                    >
                      <base-button
                        type=""
                        size="sm"
                        @click.native="assignCourseToCompany(row.id)"
                        data-toggle="tooltip"
                        data-original-title="Assign to Company"
                      >
                        <i class="text-primary fa fa-building"></i>
                      </base-button>
                    </el-tooltip>
                  </div>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div slot="footer" class="d-flex justify-content-end">
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
          </div>
        </card>
      </div>
    </div>
    <modal :show.sync="assignCourseModel">
      <h3 slot="header">Assign</h3>
      <div class="row">
        <div class="col-md-9">
          <el-select
            style="width:100%"
            filterable
            multiple
            placeholder="Select Company"
            v-model="assigncompanies"
          >
            <el-option
              v-for="(option, index) in companies"
              class="select-primary"
              :value="option.value"
              :label="option.label"
              :key="'companies' + index"
            >
            </el-option>
          </el-select>
        </div>
        <div class="col-md-3">
          <base-button size="md" @click.prevent="assignCourse(course_id)"
            >Assign</base-button
          >
        </div>
      </div>
      <br />

      <base-button
        class="mt-2 text-right"
        type="danger"
        @click.prevent="cancelAssignCourse()"
        >Close</base-button
      >
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
//import { BasePagination } from "@/components";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2";
import JsonExcel from "vue-json-excel";
let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    JsonExcel,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      viewCertificateModal: false,
      assignCourseModel: false,
      title: "",
      filters: {
        courseStatus: "Active"
      },
      company_id: "",
      searchQuery: "",
      duplicate: false,
      hot_user: "",
      hot_token: "",
      config: "",
      checked_course: "",
      certificate_Data: [],
      tableData: [],
      selectedRows: [],
      companies: [],
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
          label: "Show All Courses",
          value: ""
        }
      ],
      assigncompanies: "",
      json_fields: {
        "Course Name": "Course Name",
        Cost: "Cost",
        Length: "Length",
        "# of lessons": "# of lessons",
        "# of resources": "# of resources",
        "# of companies assigned": "# of companies assigned",
        "# of users assigned": "# of users assigned",
        "# of users completed": "# of users completed"
      },
      json_data: [],
      canCreate:true,
      canEdit:true,
      canDelete:true,

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
    if (this.$route.query.id) {
      this.company_id = this.$route.query.id;
    }
    if (localStorage.getItem("hot-user") === "sub-admin") {
       this.getRightsDetails();
    }

    this.setDefaultFilterData();
  },
  methods: {
    getRightsDetails(){
       let type="Course";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
    },

    cancelAssignCourse() {
      this.assignCourseModel = false;
    },
    assignCourse(course_id) {
      this.$http
        .post("company/assigncourse", {
          course_id: course_id,
          companies: this.assigncompanies
        })
        .then(resp => {
          this.assignCourseModel = false;
          Swal.fire({
            title: "Success",
            text: resp.data.message,
            icon: "success"
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
        });
    },
    assignCourseToCompany(id) {
      this.course_id = id;
      this.$http
        .post("company/all_companies", {
          company_type: "parent",
          company_status: "Active",
          course_id: this.course_id
        })
        .then(resp => {
          this.companies = [];
          let companies = resp.data.companies;
          for (let company of companies) {
            let obj = {
              value: company.id,
              label: company.name
            };
            this.companies.push(obj);
          }
        });
      this.assignCourseModel = true;
    },
    editCourse(id) {
      this.$router.push("/edit_course?id=" + id);
    },
    deleteCourse(id) {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
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
              .delete("/course/delete/" + id, self.config)
              .then(resp => {
                self.fetchData();
                Swal.fire({
                  title: "Deleted!",
                  text: "Course has been deleted.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                }).then(function() {});
              });
          }
        })
        .catch(function() {});
    },
    duplicateCourse() {
      if (this.checked_course === "") {
        Swal.fire({
          title: "Error!",
          text: `Please Select Any Course!`,
          icon: "error"
        });
      } else {
        this.$router.push("/create_course?id=" + this.checked_course);
      }
    },
    changeStatus(index, row) {
      let prev_val = row.status;
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
              .put(
                "/course/update_status/" + row.id,
                {
                  status: status
                },
                self.config
              )
              .then(resp => {
                this.fetchData();
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
                self.tableData[index].status = !prev_val;
              });
          } else {
            self.tableData[index].status = prev_val;
          }
        })
        .catch(function() {
          self.tableData[index].status = prev_val;
        });
    },
    courseCheckchange(row) {
      this.checked_course = row.id;
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/courses_excel", {
          course_status: this.filters.courseStatus,
          company_id: this.company_id
        })
        .then(resp => {
          let courses = resp.data.original.courses;
          this.json_data = [];
          for (let course of courses) {
            let row = {
              "Course Name": course.name,
              Cost: course.cost,
              Length: course.length,
              "# of lessons": course.lessons_count,
              "# of resources": course.resources_count,
              "# of companies assigned": course.companies_count,
              "# of users assigned": course.employees_count,
              "# of users completed": course.passed_employees_count
            };
            this.json_data.push(row);
          }
        })
        .finally(() => (this.loading = false));

      this.$http
        .post(
          "course/all_courses",
          {
            course_status: this.filters.courseStatus,
            search: this.searchQuery,
            page: this.currentPage,
            column: this.sortedColumn,
            order: this.order,
            per_page: this.perPage,
            company_id: this.company_id
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];
          let courses = resp.data.courses;
          this.totalData = resp.data.total;
          for (let course of courses) {
            let obj = {
              id: course.id,
              duplicate: false,
              name: course.name,
              length: course.length,
              cost: course.cost,
              status: true
            };
            if (course.status === 1) {
              obj.status = true;
            } else if (course.status === 0) {
              obj.status = false;
            } else {
              obj.status = course.status;
            }
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.company_id = "";
      this.filters.courseStatus = "Active";
      this.searchQuery = "";
      this.fetchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_courses_search_data",
        JSON.stringify({
          role: "super-admin",
          course_status: this.filters.courseStatus,
          search: this.searchQuery,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
          company_id: this.company_id
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_courses_search_data")
      );

      if (previousStateData !== null) {
        this.searchQuery = this.searchQuery
          ? this.searchQuery
          : previousStateData.search;
        this.filters.company_id = this.$route.query.id
          ? this.$route.query.id
          : previousStateData.company_id
          ? previousStateData.company_id
          : this.filters.company_id;

        this.filters.courseStatus = previousStateData.course_status
          ? previousStateData.course_status
          : this.filters.courseStatus;
        this.filters.userfilterType = previousStateData.filter_type
          ? previousStateData.filter_type
          : this.filters.userfilterType;
        this.currentPage = previousStateData.page
          ? previousStateData.page
          : this.currentPage;
        this.sortedColumn = previousStateData.column
          ? previousStateData.column
          : this.sortedColumn;
        this.order = previousStateData.order
          ? previousStateData.order
          : this.order;
        this.perPage = previousStateData.per_page
          ? previousStateData.per_page
          : this.perPage;
      }
      this.fetchData();
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

.custom-size .btn-sm {
  padding: 2px !important;
  font-size: 16px !important;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Check";
  }
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Course Name";
  }
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Course Length";
  }
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Allowed Attempts";
  }
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Status";
  }
  .coursesMainGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Actions";
  }
}
</style>
<style lang="scss">
.loader-overlay {
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 999;
  cursor: pointer;
  span.text {
    display: inline-block;
    position: relative;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
  }
  .loader {
    animation: loader-animate 1.5s linear infinite;
    clip: rect(0, 80px, 80px, 40px);
    height: 80px;
    width: 80px;
    position: absolute;
    left: calc(50% - 40px);
    top: calc(50% - 40px);
    &:after {
      animation: loader-animate-after 1.5s ease-in-out infinite;
      clip: rect(0, 80px, 80px, 40px);
      content: "";
      border-radius: 50%;
      height: 80px;
      width: 80px;
      position: absolute;
    }
  }

  @keyframes loader-animate {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(220deg);
    }
  }
  @keyframes loader-animate-after {
    0% {
      box-shadow: inset #fff 0 0 0 17px;
      transform: rotate(-140deg);
    }
    50% {
      box-shadow: inset #fff 0 0 0 2px;
    }
    100% {
      box-shadow: inset #fff 0 0 0 17px;
      transform: rotate(140deg);
    }
  }
}
</style>
