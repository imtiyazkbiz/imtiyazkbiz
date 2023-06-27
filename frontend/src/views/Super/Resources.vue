<script>
import { Option, Select, Table, TableColumn } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import "sweetalert2/src/sweetalert2.scss";
import "vue-phone-number-input/dist/vue-phone-number-input.css";
import Swal from "sweetalert2/dist/sweetalert2.js";

let timeout = null;
export default {
  name: "resources",
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
      courses: [
        {
          label: "All Courses",
          value: 0
        }
      ],
      statuses: [
        {
          label: "All Status",
          value: -1
        },
        {
          label: "Active",
          value: 1
        },
        {
          label: "Inactive",
          value: 0
        }
      ],
      resource: {
        name: "",
        type: "",
        availableAfterCourseCompletion: 0,
        url: "",
        file: "",
        fileName: "",
        id: 0
      },
      resourceTypes: [
        {
          label: "Link",
          value: "link"
        },
        {
          label: "File",
          value: "file"
        }
      ],
      showResourceModal: false,
      resourceModalName: "",
      resourceCourses: [],
      showResourceCoursesModal: false,
      searchQuery: "",
      filters: {
        courses: 0,
        status: -1
      },
      tableColumns: [
        {
          type: "selection"
        }
      ],
      tableData: [],
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
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
       this.getRightsDetails();
     } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "company";
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
    }
    this.setDefaultFilterData();
  },
  methods: {
    getRightsDetails(){
       let type="Resource";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
    },
    handleEdit(index, row) {
      this.$router.push("/edit_company?id=" + row.id);
    },
    selectionChange(selectedRowss) {
      this.selectedRows = [];
      for (let selectedRow of selectedRowss) {
        if (this.selectedRows.includes(selectedRow.id)) {
          this.selectedRows.splice(
            this.selectedRows.indexOf(selectedRow.id),
            1
          );
        } else {
          this.selectedRows.push(selectedRow.id);
        }
      }
    },
    validUrl(url) {
      var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      if (pattern.test(url)) {
        return true;
      }
      this.resource.url = "";
      alert("Url is not valid!");
      return false;
    },
    fetchData() {
      /**Get all the available courses*/
      if (this.courses.length == 1) {
        this.$http.post("/course/all_courses").then(response => {
          const courses = this.courses;
          response.data.courses.map(function(course) {
            courses.push({
              label: course.name,
              value: course.id
            });
          });
          this.courses = courses;
        });
      }
      /*To get all the resources*/
      const requestParameters = {
        search: this.searchQuery,
        courses: this.filters.courses,
        page: this.currentPage,
        column: this.sortedColumn,
        order: this.order,
        per_page: this.perPage,
        status: this.filters.status
      };
      this.loading = true;
      this.$http
        .get("/resources", {
          params: requestParameters
        })
        .then(response => {
          let tableData = this.tableData;
          this.totalData = response.data.total;
          tableData = response.data.resources.data;
          this.tableData = tableData;
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.filters.courses = 0;
      this.searchQuery = "";
      this.filters.status = 1;
      this.fetchData();
    },
    deleteResource(id) {
      console.log("id", id);
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
            this.$http.delete("resources/delete_resources/" + id).then(resp => {
              this.fetchData();
              Swal.fire({
                title: "Deleted!",
                text: "Resource has been deleted.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              }).then(function() {});
            });
          }
        })
        .catch(function() {});
    },
    saveSearchData() {
      localStorage.setItem(
        "resources-filters",
        JSON.stringify({
          role: "super-admin",
          search: this.searchQuery,
          courses: this.filters.courses,
          status: this.filters.status,
          page: this.currentPage,
          per_page: this.perPage
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("resources-filters")
      );
      if (previousStateData !== null) {
        this.searchQuery =
          previousStateData.search != undefined
            ? previousStateData.search
            : this.searchQuery;
        this.filters.courses =
          previousStateData.courses != undefined
            ? previousStateData.courses
            : this.filters.courses;
        this.filters.status =
          previousStateData.status != undefined
            ? previousStateData.status
            : this.filters.status;
        this.perPage =
          previousStateData.per_page != undefined
            ? previousStateData.per_page
            : this.perPage;
      }
      this.fetchData();
    },
    newResource() {
      this.resource = {
        name: "",
        type: "",
        availableAfterCourseCompletion: 0,
        url: "",
        file: "",
        fileName: "",
        id: 0
      };
      this.resourceModalName = "Add New Resource";
      this.showResourceModal = true;
    },
    getAllFiles: function(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.resource.file = file[0];
    },
    saveResource() {
      this.loading = true;

      /*To save the new resource*/
      var formData = new FormData();
      formData.append(
        "availableAfterCourseCompletion",
        this.resource.availableAfterCourseCompletion
      );
      formData.append("name", this.resource.name);
      formData.append("type", this.resource.type);
      formData.append("url", this.resource.url);
      formData.append("file", this.resource.file);
      if (this.resource.id) {
        formData.append("id", this.resource.id);
      }
      this.$http
        .post("/resources", formData)
        .then(response => {
          if (response.data.success == false) {
            Swal.fire({
              title: "Following field(s) are required!",
              text: response.data.errors,
              icon: "error"
            });
          } else {
            if (this.resource.id != 0) {
              // update the resource
              var tableData = this.tableData;
              var resourceIndex = null;
              var resourceID = this.resource.id;
              tableData.map(function(resource, index) {
                if (resource.id == resourceID) {
                  resourceIndex = index;
                }
              });
              tableData[resourceIndex] = response.data.resources;
              this.tableData = tableData;
              Swal.fire({
                title: "Success!",
                text: "Resource has been updated.",
                icon: "success"
              });
              this.showResourceModal = false;
            } else {
              // created the new resource
              var tableData = this.tableData;
              tableData.push(response.data.resources);
              this.tableData = tableData;
              Swal.fire({
                title: "Success!",
                text: "Resource has been added.",
                icon: "success"
              });
              this.totalData = this.totalData + 1;
              this.showResourceModal = false;
            }
          }
        })
        .catch(function(error) {
          Swal.fire({
            title: "Error!",
            html: error.response.data.message,
            icon: "error"
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getResourceCourses: function(resourceId) {
      this.$http
        .get("/resources/" + resourceId + "/courses")
        .then(response => {
          this.resourceCourses = response.data.resourceCourses;
          this.showResourceCoursesModal = true;
        })
        .finally(() => (this.loading = false));
    },
    editResource: function(resourceIndex) {
      this.resourceModalName = "Edit Resource";
      this.resource = {
        name: this.tableData[resourceIndex].name,
        type: this.tableData[resourceIndex].type,
        availableAfterCourseCompletion: this.tableData[resourceIndex]
          .available_after_course_completion
          ? true
          : false,
        url: "",
        file: "",
        fileName: this.tableData[resourceIndex].file_name,
        id: this.tableData[resourceIndex].id
      };

      if (this.tableData[resourceIndex].type == "file") {
        this.resource.file = this.tableData[resourceIndex].url;
      } else {
        this.resource.url = this.tableData[resourceIndex].url;
      }
      this.showResourceModal = true;
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
            this.$http
              .put(
                "/resources/update-status/" + row.id,
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
    }
  }
};
</script>
<template>
  <div>
     <div class="content">
  <div class="container-fluid mt-3">
    <card
      class="no-border-card"
      footer-classes="pb-2"
      v-loading.fullscreen.lock="loading"
    >
      <template slot="header">
        <div class="row align-items-center">
          <div class="col-lg-6 col-md-6 col-12">
            <h2 class="mb-0">Resources</h2>
          </div>
          <div class="col-lg-6 col-md-6 text-right">
            <base-button class="custom-btn" v-on:click="resetFilters()"
              ><i aria-hidden="true" class="fa fa-refresh"></i> Clear
              Filters</base-button
            >
            <base-button  v-if="canCreate" @click.prevent="newResource()" class="custom-btn"
              ><i aria-hidden="true" class="fa fa-plus"></i> Add
              Resource</base-button
            >
          </div>
        </div>
      </template>
      <div>
        <div class="row  flex-wrap">
          <div class="col-md">
            <base-input
              label="Search:"
              placeholder="Search..."
              prepend-icon="fas fa-search"
              v-model="searchQuery"
            ></base-input>
          </div>
          <div class="col-md">
            <base-input label="Status:">
              <el-select
                class="select-primary"
                v-model="filters.status"
                v-on:change="changePage(1)"
              >
                <el-option
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                  class="select-primary"
                  v-for="item in statuses"
                ></el-option>
              </el-select>
            </base-input>
          </div>
          <div class="col-md">
            <base-input label="Courses:">
              <el-select
                class="select-primary"
                v-model="filters.courses"
                v-on:change="changePage(1)"
              >
                <el-option
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                  class="select-primary"
                  v-for="item in courses"
                ></el-option>
              </el-select>
            </base-input>
          </div>
          <div class="col-md">
            <base-input label="Showing:">
              <el-select
                class="select-primary pagination-select"
                placeholder="Per page"
                v-model="perPage"
                v-on:change="changePage(1)"
              >
                <el-option
                  :key="item"
                  :label="item"
                  :value="item"
                  class="select-primary"
                  v-for="item in perPageOptions"
                ></el-option>
              </el-select>
            </base-input>
          </div>
        </div>
        <div class="user-eltable">
          <el-table
            :data="tableData"
            @selection-change="selectionChange"
            class="compGrid"
            header-row-class-name="thead-light"
            highlight-current-row
            id="tbl1"
            role="table"
            row-key="id"
            stripe
          >
            <el-table-column
              label="Name"
              min-width="280px"
              prop="name"
            ></el-table-column>
            <el-table-column label="#Courses" prop="course">
              <template slot-scope="props">
                <span
                  @click="getResourceCourses(props.row.id)"
                  class="text-blue linkColor comppaniescount"
                  >{{ props.row.course }}</span
                >
              </template>
            </el-table-column>
            <el-table-column label="Type" prop="type">
              <template slot-scope="props">
                <span class="text-capitalize">{{ props.row.type }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Available after completion?" prop="type">
              <template slot-scope="props">
                <el-tooltip
                  content="Yes"
                  placement="top"
                  v-if="props.row.available_after_course_completion"
                >
                  <span
                    class="success"
                    data-original-title=""
                    data-toggle="tooltip"
                    icon
                    size="sm"
                  >
                    <i class="text-success fa fa-check"></i>
                  </span>
                </el-tooltip>
                <el-tooltip content="No" placement="top" v-else>
                  <span
                    class="success"
                    data-original-title=""
                    data-toggle="tooltip"
                    icon
                    size="sm"
                  >
                    <i class="text-danger fa fa-times"></i>
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="Status" prop="status">
              <template slot-scope="props">
                <div
                  class="d-flex"
                  v-on:click="changeStatus(props.$index, props.row)"
                >
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-if="props.row.status"
                    v-model="props.row.status"
                  ></base-switch>
                  <base-switch
                    class="mr-1"
                    type="danger"
                    v-else
                    v-model="props.row.status"
                  ></base-switch>
                </div>
              </template>
            </el-table-column>
            <el-table-column align="left" label="Actions" min-width="150px">
              <div class="d-flex custom-size" slot-scope="{ $index, row }">
                <el-tooltip v-if="canEdit" content="Edit" placement="top">
                  <base-button
                    @click="editResource($index)"
                    class="success"
                    data-original-title="Edit"
                    data-toggle="tooltip"
                    icon
                    size="sm"
                    type=""
                  >
                    <i class="text-default fa fa-pencil-square-o"></i>
                  </base-button>
                </el-tooltip>
                <el-tooltip content="Preview" placement="top">
                  <a
                    :href="row.url"
                    class="success"
                    data-original-title="Preview"
                    data-toggle="tooltip"
                    icon
                    size="sm"
                    target="_blank"
                  >
                    <i class="text-success fa fa-eye"></i>
                  </a>
                </el-tooltip>
                <el-tooltip  v-if="canDelete" content="Delete" placement="top">
                  <base-button
                    @click.native="deleteResource(row.id)"
                    type=""
                    size="sm"
                    icon
                    data-toggle="tooltip"
                    data-original-title="Delete"
                  >
                    <i class="text-danger fa fa-trash"></i>
                  </base-button>
                </el-tooltip>
              </div>
            </el-table-column>
          </el-table>
        </div>
      </div>
      <div class="d-flex justify-content-end " slot="footer">
        <nav v-if="pagination && tableData.length > 0">
          <div class="row">
            <div class="col-md-12">
              <ul
                class="pagination custompagination  justify-content-end align-items-center"
              >
                <p class="p-0 m-0 mr-2">
                  Showing {{ tableData.length }} of {{ totalData }} entries
                </p>
                <li :class="{ disabled: currentPage === 1 }" class="page-item">
                  <a
                    @click.prevent="changePage(currentPage - 1)"
                    class="page-link"
                    href="#"
                    ><i class="fa fa-caret-left "></i
                  ></a>
                </li>
                <li
                  :class="{ active: page == currentPage }"
                  class="page-item"
                  v-bind:key="index"
                  v-for="(page, index) in pagesNumber"
                >
                  <a
                    @click.prevent="changePage(page)"
                    class="page-link"
                    href="javascript:void(0)"
                    >{{ page }}</a
                  >
                </li>
                <li
                  :class="{ disabled: currentPage === last_page }"
                  class="page-item"
                >
                  <a
                    @click.prevent="changePage(currentPage + 1)"
                    class="page-link"
                    href="#"
                    ><i class="fa fa-caret-right "></i
                  ></a>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </div>
    </card>
    <modal :show.sync="showResourceModal">
      <h4 class="modal-title mb-0" slot="header" style="color:#444C57">
        {{ this.resourceModalName }}
      </h4>
      <form>
        <div class="row">
          <div class="col-md-12">
            <base-checkbox
              class="mb-3 form-control-label"
              v-model="resource.availableAfterCourseCompletion"
              >Available after passing the course</base-checkbox
            >
          </div>
          <div class="col-md-6">
            <base-input
              label="Resource Name"
              placeholder="Resource Name"
              type="text"
              v-model="resource.name"
            ></base-input>
          </div>
          <div class="col-md-6">
            <base-input label="Resource Type">
              <el-select
                class="select-primary"
                placeholder="Select type"
                v-model="resource.type"
              >
                <el-option
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                  class="select-primary"
                  v-for="item in resourceTypes"
                ></el-option>
              </el-select>
            </base-input>
          </div>
          <div class="col-md-12" v-if="resource.type == 'link'">
            <base-input
              label="Resource URL (url should contain http:// or https://)"
              placeholder="Resource URL"
              type="text"
              v-on:blur="validUrl(resource.url)"
              v-model="resource.url"
            ></base-input>
          </div>
          <div class="col-md-12" v-if="resource.type == 'file'">
            <label class="form-control-label">Upload Resource</label>
            <div>
              <span class="">
                <input
                  class="form-control "
                  id="resource_file"
                  name="..."
                  type="file"
                  v-on:change="getAllFiles($event)"
                />
              </span>
            </div>
          </div>
          <div
            class="col-md-12 my-4 text-center"
            v-if="resource.id != 0 && resource.type == 'file'"
          >
            <a
              :download="resource.fileName"
              :href="resource.file"
              target="_blank"
            >
              <span class="text-primary">{{
                resource.fileName == "" ? "Previous File" : resource.fileName
              }}</span>
            </a>
          </div>
        </div>
        <div class="text-right ">
          <base-button @click.prevent="saveResource" class="custom-btn"
            >Save Resource</base-button
          >
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="showResourceCoursesModal">
      <h4 class="modal-title mb-0" slot="header" style="color:#444C57">
        Courses
      </h4>
      <div class="user-eltable">
        <el-table
          :data="resourceCourses"
          @selection-change="selectionChange"
          class="compGrid assign-cr-modal"
          header-row-class-name="thead-light"
          highlight-current-row
          id="tbl1"
          role="table"
          row-key="id"
          stripe
        >
          <el-table-column min-width="280px" prop="name">
            <template slot="header">
              <span>Name</span>
            </template>
            <template slot-scope="props">
              <router-link :to="'/edit_course?id=' + props.row.id">
                <span class="text-blue linkColor">{{ props.row.name }}</span>
              </router-link>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </modal>
  </div>
</div>
</div>
</template>
