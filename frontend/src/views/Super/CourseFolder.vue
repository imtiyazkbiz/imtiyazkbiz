<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6 mt__4">
      <div>
        <card class="no-border-card" footer-classes="pb-2">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6">
                <h2 class="mb-0">Course Folders</h2>
              </div>
              <div class="col-lg-6 col-sm-5 text-right">
                <router-link :to="'/create_course_folder'">
                  <base-button class="custom-btn">
                    <i class="fa fa-plus" aria-hidden="true"></i> Add Course
                    Folder</base-button
                  >
                </router-link>
              </div>
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
              <div class="col-md-3 form-group">
                <label>Status:</label>
                <el-select
                  class="select-primary"
                  v-model="filters.folderStatus"
                  placeholder="Filter by Folder Status"
                  v-on:change="refresh()"
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
              <div class="col-md-3 form-group">
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
                  min-width="400px"
                  label="Folder Name"
                  prop="folder_name"
                  sortable
                >
                  <template slot-scope="props">
                    <span>{{ props.row.folder_name }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="120px"
                  label="Course Count"
                  prop="course_count"
                >
                  <template slot-scope="props">
                    <el-tooltip content="Count" placement="top">
                      <router-link
                        :to="'/course_folder_details?id=' + props.row.id"
                      >
                        <base-button
                          class="count"
                          type="warning"
                          size="sm"
                          data-toggle="tooltip"
                          data-original-title="Count"
                          >{{ props.row.course_count }}
                        </base-button>
                      </router-link>
                    </el-tooltip>
                  </template>
                </el-table-column>
                <el-table-column min-width="130px" label="Status" prop="status">
                  <template slot-scope="props">
                    <div
                      class="d-flex justify-content"
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
                <el-table-column min-width="180px" label="Actions">
                  <div slot-scope="{ $index, row }" class="d-flex  custom-size">
                    <el-tooltip content="Edit" placement="top">
                      <router-link :to="'/create_course_folder?id=' + row.id">
                        <base-button
                          class="success"
                          type=""
                          size="sm"
                          icon
                          data-toggle="tooltip"
                          data-original-title="Edit"
                        >
                          <i class="text-default fa fa-pencil-square-o"></i>
                        </base-button>
                      </router-link>
                    </el-tooltip>
                    <el-tooltip content="Delete" placement="top">
                      <base-button
                        @click="deleteCourseFolder($index)"
                        class="danger"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                        style="margin-left:10px;"
                      >
                        <i class="text-danger fa fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip content="Assign Course" placement="top">
                      <base-button
                        @click="assignCourseFolder($index, row)"
                        class="primary"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Assign Course"
                      >
                        <i class="text-primary fa fa-plus"></i>
                      </base-button>
                    </el-tooltip>
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
    <modal :show.sync="openAssignFolderModel">
      <h3 slot="header" class="mb-0" style="text-align:center;">
        Assign Course Folder
      </h3>
      <form>
        <div class="row">
            <div class="col-md-12 text-right" v-if="selectedCourses.length && showDoneButton">
                <base-button size="sm mb-2" class="custom-btn right" @click="showDoneButton = false">Done</base-button>
            </div>
          <div class="col-sm-12">
            <div class="user-eltable assign-course-popup">
                <el-select class="select1"  ref="dropdown" v-model="selectedCourses" style="width: 100%;" multiple filterable placeholder="Select Course(s)" @focus="showDoneButton = true" @blur="showDoneButton = false">
                    <el-option class="select-primary" v-for="item in courses_data" :key="item.id" :label="item.course_name" :value="item.id"/>
                </el-select>
            </div>
          </div>
        </div>
        <div class="text-right mt-3">
          <base-button type="default" @click.prevent="assignCourse">
            Assign Course Folder</base-button
          >
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
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
      openAssignFolderModel: false,
      viewCertificateModal: false,
      title: "",
      filters: {
        course_id: "",
        folderStatus: "Active"
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
      courses_data: [],
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
      selectedRows: [],
      selectedCourses: [],
      showDoneButton: false,
    };
  },
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    this.$http
      .post(
        "course/allcourse_folders",
        {
          folder_status: this.filters.folderStatus,
          search: this.searchQuery
        },
        this.config
      )
      .then(resp => {
        let folders = resp.data.folders;
        for (let folder of folders) {
          let obj = {
            id: folder.id,
            folder_name: folder.folder_name,
            folder_description: folder.folder_description,
            course_count: folder.courses_count,
            status: true
          };
          if (folder.folder_status === 1) {
            obj.status = true;
          } else if (folder.folder_status === 0) {
            obj.status = false;
          } else {
            obj.status = folder.folder_status;
          }
          this.tableData.push(obj);
        }
      });
  },
  methods: {
    assignCourse() {
      let data = {
        folder_id: this.folder_id,
        assign_to: [
          {
            course_ids: []
          }
        ]
      };
      for (let id of this.selectedCourses) {
        let obj = {
          id: id
        };
        data.assign_to[0].course_ids.push(obj);
      }
      if (data.assign_to[0].course_ids.length > 0) {
        this.$http
          .post("course/assignCourseFolder", data, this.config)
          .then(resp => {
            this.openAssignFolderModel = false;
            this.checked_courses = [];
            this.refresh();
            Swal.fire({
              title: "Success!",
              text: "Courses has been assigned to this course folder",
              icon: "success"
            });
          });
      } else {
        Swal.fire({
          title: "Error",
          text: "Please Select any course!",
          icon: "error"
        });
      }
    },
    courseCheckchange(row) {
      if (this.checked_courses.includes(row.id)) {
        this.checked_courses.splice(this.checked_courses.indexOf(row.id), 1);
      } else {
        this.checked_courses.push(row.id);
      }
    },
    assignCourseFolder(index, row) {
      this.folder_id = row.id;
      this.$http
        .post(
          "course/unassignedCourses",
          {
            folder_id: this.folder_id
          },
          this.config
        )
        .then(resp => {
          this.courses_data = [];
          let courses = resp.data;
          for (let course of courses) {
            let obj = {
              checked: false,
              course_name: course.name,
              id: course.id
            };
            this.courses_data.push(obj);
          }
        });
      this.openAssignFolderModel = true;
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
        text: "You want to chnage status",
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
                "/course/update_folder_status/" + row.id,
                {
                  status: status
                },
                self.config
              )
              .then(resp => {
                this.refresh();
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
    deleteCourseFolder(index) {
      let id = this.tableData[index].id;
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
              .delete("/course/coursefolder/" + id, self.config)
              .then(resp => {
                self.tableData.splice(index, 1);
                Swal.fire({
                  title: "Deleted!",
                  text: "Course Folder has been deleted.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                }).then(function() {});
              });
          }
        })
        .catch(function() {});
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    },
    refresh() {
      this.$http
        .post(
          "course/allcourse_folders",
          {
            folder_status: this.filters.folderStatus,
            search: this.searchQuery
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];
          let folders = resp.data.folders;
          for (let folder of folders) {
            let obj = {
              id: folder.id,
              folder_name: folder.folder_name,
              folder_description: folder.folder_description,
              course_count: folder.courses_count,
              status: true
            };
            if (folder.folder_status === 1) {
              obj.status = true;
            } else if (folder.folder_status === 0) {
              obj.status = false;
            } else {
              obj.status = folder.folder_status;
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
.content .mt__4,
.content .mt--6 {
  margin-top: -4.5rem !important;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Folder Name";
  }

  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Course Count";
  }
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Status";
  }
  .courseFoldersGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Action";
  }
}
</style>
