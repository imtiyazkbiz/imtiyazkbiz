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
                <h2 class="mb-0">Course Folders</h2>
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

              <div class="col-md-4 form-group" v-if="editor != 'manager'"></div>
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
                  label="Course Folder Name"
                  prop="folder_name"
                  sortable
                >
                  <template slot-scope="props">
                    <span class="linkColor" @click="showFolderCourses(props.row.id)">{{ props.row.folder_name }}</span>
                  </template>
                </el-table-column>

                <el-table-column
                  align="left"
                  min-width="200px"
                  label="Assigned Employees"
                  prop="assigned_employees"
                >
                  <template slot-scope="props">{{props.row.assigned_employees}} </template>
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
     <modal :show.sync="showFolderCoursesModal">
      <h4 class="modal-title mb-0" slot="header" style="color:#444C57">
        Courses
      </h4>
      <div class="user-eltable">
        <el-table
          :data="folderCourses"
          class="compGrid assign-cr-modal"
          header-row-class-name="thead-light"
          highlight-current-row
          id="tbl1"
          role="table"
          row-key="id"
          stripe
        >
          <el-table-column min-width="100%" prop="name">
            <template slot="header">
              <span>Name</span>
            </template>
            <template slot-scope="props">
                <span>{{ props.row.course_name }}</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
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
      folderCourses:[],
      showFolderCoursesModal: false,
      admin_id: "",
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
        .post("company/course_folders", {
          search: this.searchQuery,
          company_course_status: this.filters.courseStatus,
          company_id: this.company_id,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage
        })
        .then(resp => {
          let course_data = resp.data.coursefolders;
          this.totalData = resp.data.total;
          this.tableData = [];
          for (let course of course_data) {
            let obj = {
              id: course.folder_id,
              folder_name: course.folder_name,
              assigned_employees:course.employee_count
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    showFolderCourses(id){
       this.$http.post("course/foldercourses", {
         folder_id: id
        }) .then(resp => {
          this.folderCourses=[];
          let courses = resp.data.courses;
          for (let course of courses) {
            let obj = {
              id: course.id,
              course_id: course.course_id,
              course_name: course.name,
            };
            this.folderCourses.push(obj);
          }
        });    
     this.showFolderCoursesModal = true;    
    }
  }
};
</script>
<style>
.no-border-card .card-footer {
  border-top: 0;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  table,
  thead,
  tbody,
  th,
  td,
  tr {
    display: block;
  }

  thead tr {
    position: absolute;
    top: -9999px;
    left: -9999px;
  }

  tr {
    margin: 0 0 1rem 0;
  }

  tr:nth-child(odd) {
    background: #ccc;
  }
  td {
    /* Behave  like a "row" */
    border: none;
    border-bottom: 1px solid #eee;
    position: relative;
    padding-left: 50%;
  }

  td:before {
    /* Now like a table header */
    position: absolute;
    /* Top/left values mimic padding */
    top: 0;
    left: 6px;
    width: 45%;
    padding-right: 10px;
    white-space: nowrap;
  }

  .coursesGrid table.el-table__body td:nth-of-type(1):before {
    content: "Course Name";
  }
  .coursesGrid table.el-table__body td:nth-of-type(2):before {
    content: "Course Length";
  }
  .coursesGrid table.el-table__body td:nth-of-type(3):before {
    content: "Assigned Employees";
  }
  .coursesGrid table.el-table__body td:nth-of-type(4):before {
    content: "Status";
  }
}
</style>
