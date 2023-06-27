<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pb-2">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6">
                <h2 class="mb-0">Course Folders</h2>
              </div>
              <div class="col-lg-6 col-sm-5 text-right"></div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-6">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  v-on:keyup="refresh()"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <div class="col-md-2 form-group">
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
              </div>
            </div>
            <div class="user-eltable">
              <el-table
                :data="queriedData"
                row-key="id"
                role="table"
                class="courseFoldersGrid"
                header-row-class-name="thead-light custom-thead-light"
                @sort-change="sortChange"
                @selection-change="selectionChange"
              >
                <el-table-column
                  label="Course Name"
                  align="left"
                  property="course_name"
                  min-width="300px"
                  prop="course_name"
                  sortable
                >
                  <template slot-scope="props">
                    <router-link
                      :to="'/edit_course?id=' + props.row.course_id"
                      class=" btn btn-link"
                    >
                      <span>{{ props.row.course_name }}</span>
                    </router-link>
                  </template>
                </el-table-column>
                <el-table-column min-width="250px" label="Status" prop="status">
                  <template slot-scope="props">
                    <div class="d-flex justify-content ">
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
                <el-table-column min-width="300px" label="Actions">
                  <div slot-scope="{ $index }" class="d-flex  custom-size">
                    <base-button
                      type=""
                      size="sm"
                      @click="removeCoursefromFolder($index)"
                    >
                      <i class="text-danger fa fa-trash"></i>
                    </base-button>
                  </div>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div
            slot="footer"
            class="col-12 d-flex justify-content-center justify-content-sm-between flex-wrap"
          >
            <div class="">
              <p class="card-category">
                Showing {{ from + 1 }} to {{ to }} of {{ total }} entries
                <span v-if="selectedRows.length">
                  &nbsp; &nbsp; {{ selectedRows.length }} rows selected
                </span>
              </p>
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
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import { BasePagination } from "@/components";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  mixins: [clientPaginationMixin],
  components: {
    BasePagination,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      viewCertificateModal: false,
      title: "",
      filters: {
        folder_id: "",
        course_id: "",
        courseStatus: "Active"
      },
      folder_id: "",
      company_id: "",
      searchQuery: "",
      duplicate: false,
      hot_user: "",
      hot_token: "",
      config: "",
      //status: true,
      checked_course: "",
      tableData: [],
      checked_courses: [],
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
          label: "Show All Folders",
          value: ""
        }
      ],
      selectedRows: []
    };
  },
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    this.config = {
      headers: {
        authorization: "Bearer " + localStorage.getItem("hot-token"),
        "content-type": "application/json"
      }
    };
    if (this.$route.query.id) {
      this.filters.folder_id = this.$route.query.id;
    }
    this.$http
      .post(
        "course/foldercourses",
        {
          folder_id: this.filters.folder_id,
          search: this.searchQuery
        },
        this.config
      )
      .then(resp => {
        let courses = resp.data.courses;
        for (let course of courses) {
          let obj = {
            course_id: course.course_id,
            id: course.id,
            course_name: course.name,
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
      });
  },
  methods: {
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    },
    removeCoursefromFolder(index) {
      let id = this.tableData[index].id;
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: `You want to remove this course!`,
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
              .delete("/course/removecoursefolder/" + id, self.config)
              .then(resp => {
                self.tableData.splice(index, 1);
                Swal.fire({
                  title: "Deleted!",
                  text: "Course has been removed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                }).then(function() {});
              });
          }
        })
        .catch(function() {});
    },
    refresh() {
      this.$http
        .post(
          "course/foldercourses",
          {
            folder_id: this.filters.folder_id,
            search: this.searchQuery
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];
          let courses = resp.data.courses;
          for (let course of courses) {
            let obj = {
              id: course.id,
              course_id: course.course_id,
              course_name: course.name,
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
        });
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
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Course Name";
  }
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Status";
  }
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Action";
  }
}
</style>
