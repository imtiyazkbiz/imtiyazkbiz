<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7">
          <h6 class="h2 text-white d-inline-block mb-0"></h6>
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <route-bread-crumb></route-bread-crumb>
          </nav>
        </div>
        <div class="col-lg-6 col-5 text-right"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card
          class="no-border-card"
          body-classes="px-0 pb-1"
          footer-classes="pb-2"
        >
          <template slot="header">
            <div class="row">
              <div class="col-md-6 text-left">
                <h3 class="mb-0"></h3>
              </div>
              <div class="col-md-6 text-right">
                <base-button size="sm" type="danger" v-on:click="resetFilters()"
                  >Clear all Filters</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div class="row">
              <div class="col-md-12"></div>
            </div>
            <div
              class="col-12 d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-3">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  v-on:keyup="refresh()"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>

              <div class="col-md-3 form-group">
                <label>Status:</label>
                <el-select
                  v-model="filters.employeStatus"
                  v-on:change="refresh()"
                  placeholder="Filter by Employee Status"
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
                <label>Status:</label>
                <el-select
                  v-on:change="bulkClicked()"
                  v-model="bulkValue"
                  placeholder="Bulk Action"
                >
                  <el-option
                    class="select-default"
                    v-for="item in bulk_array"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
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
                class="detailsGrid"
                header-row-class-name="thead-light custom-thead-light"
                @sort-change="sortChange"
                @selection-change="selectionChange"
              >
                <el-table-column
                  v-for="column in tableColumns"
                  :key="column.label"
                  v-bind="column"
                >
                </el-table-column>

                <el-table-column
                  min-width="150px"
                  align="left"
                  label="Location"
                  prop="location"
                  sortable
                >
                  <template slot-scope="props">
                    <span>{{ props.row.location }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="200px"
                  align="left"
                  label="Name"
                  prop="first_name"
                  sortable
                >
                  <template slot-scope="props">
                    <span>{{
                      props.row.first_name + " " + props.row.last_name
                    }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="150px"
                  align="left"
                  label="Employee Type"
                  prop="type"
                  sortable
                >
                  <template slot-scope="props">
                    <span>{{ props.row.type }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Pass/Open/Fail">
                  <template slot-scope="props">
                    <span v-on:click="openCourseDetails(props.row)">
                      <base-button
                        type="danger"
                        style="background-color:#f50636; border-color: #f50636;"
                        v-if="props.row.fail != '0'"
                        >{{ props.row.passOpenFail }}</base-button
                      >
                      <base-button
                        type="warning"
                        style="background-color: #ffd600; border-color: #ffd600;"
                        v-else-if="props.row.open != '0'"
                      >
                        {{ props.row.passOpenFail }}
                      </base-button>
                      <base-button
                        style="background-color: #05bf70; border-color: #05bf70;"
                        v-else-if="props.row.pass != '0'"
                      >
                        {{ props.row.passOpenFail }}
                      </base-button>
                      <base-button v-else style="color:white;">
                        {{ props.row.passOpenFail }}
                      </base-button>
                    </span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="130px"
                  label="Status"
                  prop="status"
                  sortable
                >
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
                <el-table-column min-width="200px" label="Actions">
                  <div slot-scope="{ $index, row }" class="d-flex custom-size">
                    <base-button
                      @click="handleEdit($index, row)"
                      class="edit"
                      type=""
                      size="sm"
                      icon
                    >
                      <i class=" text-default fa fa-pencil-square-o"></i>
                    </base-button>
                    <base-button
                      @click="handleEnvelope($index, row)"
                      type=""
                      size="sm"
                      icon
                      v-if="row.email"
                    >
                      <i class=" text-warning far fa-envelope"></i>
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
    <modal :show.sync="employeeEmailModal">
      <h6 slot="header" class="title title-up text-primary">
        Send Reminder Email to {{ employeeEmailData.first_name }}
        {{ employeeEmailData.last_name }} ({{ employeeEmailData.location }})
      </h6>
      <form>
        <div class="text-center my-4">
          <div class="row">
            <div class="col-md-4">
              <base-button type="success" @click.prevent="sendReminderEmail">
                Course Due Reminder Email
              </base-button>
            </div>
            <div class="col-md-4" style="margin-top:10px;">
              <base-button type="warning" @click.prevent="sendWelcomeEmail">
                Resend Welcome Email
              </base-button>
            </div>
            <div class="col-md-4" style="margin-top:10px;">
              <base-button
                type="primary"
                @click.prevent="sendPasswordResetEmail"
              >
                Reset Password Email
              </base-button>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>

    <modal :show.sync="employeeDataModal" class="user-modal">
      <h6 slot="header" class="title title-up text-primary">
        {{ employeeCoursesData.first_name }}
        {{ employeeCoursesData.last_name }}'s Assigned Courses ({{
          employeeCoursesData.location
        }})
      </h6>
      <form>
        <div class="card-body table-responsive table-full-width user-eltable">
          <el-table
            :data="employeeCoursesData.courseData"
            class="pofGrid"
            role="table"
          >
            <el-table-column label="Lesson name" property="" min-width="200px">
              <template slot-scope="props">
                <span>{{ props.row.course_name }}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="Lesson Status"
              property=""
              min-width="200px"
            >
              <template slot-scope="props">
                <span v-if="props.row.employee_course_status === 1">Pass</span>
                <span v-if="props.row.employee_course_status === 2">Open</span>
                <span v-if="props.row.employee_course_status === 0">Fail</span>
                <span v-if="props.row.employee_course_status === 3"
                  >Expired</span
                >
              </template> </el-table-column
            ><!--employee_course_due_date-->
            <el-table-column
              label="Date Completed"
              property=""
              min-width="200px"
            >
              <template slot-scope="props">
                <span>{{
                  formattedDate(props.row.employee_course_date_completed)
                }}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="Date Assigned"
              property=""
              min-width="200px"
            >
              <template slot-scope="props">
                <span>{{
                  formattedDate(props.row.employee_course_date_assigned)
                }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Due Date" property="" min-width="160px">
              <template slot-scope="props">
                <span>{{
                  formattedDate(props.row.employee_course_due_date)
                }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Action" property="" min-width="150px">
              <template slot-scope="props">
                <base-button
                  type=""
                  size="sm"
                  @click="unAssignedCourse(props.$index, props.row)"
                >
                  <i class="text-danger fa fa-trash"></i>
                </base-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <div class="clearfix"></div>
      </form>
    </modal>

    <modal :show.sync="reassignLocationModel">
      <h6 slot="header" class="title title-up text-primary">Select Location</h6>
      <form>
        <div class="row">
          <div class="col-md-12 text-center">
            <el-select
              class="select-default"
              v-model="bulk_location_id"
              placeholder="Filter by Location"
            >
              <el-option
                class="select-default"
                v-for="item in locations"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button type="primary" @click.prevent="assignBulkLocation">
            Assign Location
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>

    <modal :show.sync="courseAssigneeModal">
      <h6 slot="header" class="title title-up text-primary">Assign Course</h6>
      <form>
        <div class="row">
          <div class="col-md-12">
            <label>Select any Course to Assign</label>
            <el-select
              class="select-default"
              v-model="assigned_course_id"
              placeholder="Filter by Location"
            >
              <el-option
                class="select-default"
                v-for="(course, index) in courses"
                :key="index"
                :label="course.course_name"
                :value="course.id"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button
            type="danger"
            @click.prevent="courseAssigneeModal = false"
          >
            Cancel
          </base-button>
          <base-button type="Success" @click.prevent="assignCourse">
            {{ "Assign Course" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>

    <modal :show.sync="courseUnassignLocation">
      <h6 slot="header" class="title title-up text-primary">
        Delete Course From Location
      </h6>
      <form>
        <div class="row">
          <div class="col-md-12">
            <label>Select any Course to Delete</label>
            <el-select
              class="select-primary"
              v-model="unAssigned_course_id"
              placeholder="Bulk Action"
            >
              <el-option
                class="select-primary"
                v-for="(course, index) in location_courses"
                :key="index"
                :label="course.course_name"
                :value="course.id"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button
            type="default"
            size="sm"
            @click.prevent="courseUnassignLocation = false"
          >
            Cancel
          </base-button>
          <base-button type="primary" size="sm" @click.prevent="unAssignCourse">
            {{ "Assign Course" }}
          </base-button>
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
//import swal from 'sweetalert';
import Swal from "sweetalert2";
import moment from "moment";
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
      tableColumns: [
        {
          type: "selection"
        }
      ],
      check_all: false,
      checked_all: false,
      courseAssigneeModal: false,
      courseUnassignLocation: false,
      assigning: false,
      course_due_date: "",
      assigned_course_id: "",
      unAssigned_course_id: "",
      courseDueDateModel: false,
      bulk_location_id: "",
      reassignLocationModel: false,
      employeeDataModal: false,
      employeeEmailModal: false,
      checked_employee: [],
      locations: [],
      courses: [],
      location_courses: [],
      hot_user: "",
      hot_token: "",
      config: "",
      company_id: "",
      bulkValue: "",
      location: {
        locationName: "",
        locationStatus: true
      },
      bulk_array: [
        {
          label: "Assign Course to Location",
          value: "assign_course_to_location"
        },
        {
          label: "Assign Course to Employee",
          value: "assign_course_to_employee"
        },
        {
          label: "Update Status Active",
          value: "active_status"
        },
        {
          label: "Update Status Inactive",
          value: "inactive_status"
        },
        {
          label: "Mass Delete Course from Location",
          value: "delete_course_from_location"
        },
        {
          label: "Reassign Location",
          value: "reassign_location"
        }
      ],
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
        employeStatus: "Active",
        location_id: ""
      },
      employeeEmailData: {
        first_name: "",
        last_name: "",
        location: "",
        id: ""
      },
      employeeCoursesData: {
        first_name: "",
        last_name: "",
        location: "",
        courseData: []
      },
      searchQuery: "",
      course_employee_id: "",
      tableData: [],
      selectedRows: []
    };
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.company_id = localStorage.getItem("hot-user-id");
    }

    if (this.$route.query.id) {
      this.filters.location_id = this.$route.query.id;
    }

    this.$http
      .post("employees/list", {
        role: "admin",
        search: this.searchQuery,
        location: this.filters.location_id,
        employee_status: this.filters.employeStatus,
        company_id: this.company_id
      })
      .then(resp => {
        let location_data = resp.data.location;
        if (location_data.status) {
          this.location.locationStatus = true;
        } else {
          this.location.locationStatus = false;
        }

        this.location.locationName = location_data.name;
        let employee_data = resp.data.employee;
        for (let data of employee_data) {
          let obj = {
            id: data.id,
            checked: false,
            first_name: data.first_name,
            last_name: data.last_name,
            email: data.email,
            location: data.location.name,
            courses: data.courses,
            passOpenFail:
              data.course_pass_count +
              " / " +
              data.course_open_count +
              " / " +
              (data.course_fail_count + data.course_expired_count),
            pass: data.course_pass_count,
            open: data.course_open_count,
            fail: data.course_fail_count + data.course_expired_count,
            status: true,
            type: data.employee_type
          };

          if (data.status) {
            obj.status = true;
          } else {
            obj.status = false;
          }
          this.tableData.push(obj);
        }
      });

    this.$http
      .post("location/all", {
        company_id: this.company_id
      })
      .then(resp => {
        for (let loc of resp.data) {
          let obj = {
            label: loc.name,
            value: loc.id
          };
          this.locations.push(obj);
        }
      });

    this.$http.get("company/courses/" + this.company_id).then(resp => {
      this.courses = resp.data[0].courses;
    });

    this.$http
      .get("location/courses/" + this.filters.location_id)
      .then(resp => {
        this.location_courses = resp.data[0].courses;
      });
  },

  methods: {
    /*   checkAllClicked(){
            this.selectedRows=[];
            if(this.check_all){
                this.checked_all=true;
                for(let data of this.tbl_data){
                    data.checked = true;
                    this.selectedRows.push(data.id);
                }
            }else{
                this.checked_all=false;
                for(let data of this.tbl_data){
                    data.checked = false;
                }
            }
        },*/
    resetFilters() {
      this.bulkValue = "";
      this.filters.employeStatus = "Active";
      this.searchQuery = "";
      this.refresh();
    },
    formattedDate(data) {
      return moment(data).format("MM-DD-YYYY");
    },
    unAssignCourse() {
      this.assigning = true;
      if (this.unAssigned_course_id !== "") {
        this.$http
          .post(
            "course/delete_location",
            {
              course_id: this.unAssigned_course_id,
              location_id: this.filters.location_id
            },
            this.config
          )
          .then(resp => {
            this.assigning = false;
            this.courseUnassignLocation = false;
            this.bulkValue = "";
            Swal.fire({
              title: "Success!",
              text: "Course Delete Successfully!",
              icon: "success"
            });
          });
      } else {
        Swal.fire({
          title: "Error!",
          text: "Please select any course to delete!",
          icon: "error"
        });
      }
    },

    assignCourse() {
      this.assigning = true;
      if (this.bulkValue === "assign_course_to_location") {
        if (this.assigned_course_id !== "") {
          let data = {
            course_id: this.assigned_course_id,
            company_id: this.company_id,
            assign_to: [
              {
                location_id: this.filters.location_id,
                assign_to: "location"
              }
            ]
          };
          this.$http
            .post("course/assign", data, this.config)
            .then(resp => {
              this.assigning = false;
              this.courseAssigneeModal = false;
              this.bulkValue = "";
              this.assigned_course_id = "";
               if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
                Swal.fire({
                  title: "Success!",
                  text: "Course has been Assigned to these Employees",
                  icon: "success"
                });
              }else{
                Swal.fire({
                  title: "Success!", 
                  html: '<ul style="text-align: left;"><li>Course(s) Assigned: '+ resp.data.assigned +'</li><li>Course(s) In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course(s) Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                  icon: "success"
                });
              }
            })
            .catch(function(error) {
              Swal.fire({
                title: "Error!",
                html: error.response.data.message,
                icon: "error"
              });
            });
        } else {
          Swal.fire({
            title: "Error!",
            text: "All fields are required!",
            icon: "error"
          });
        }
      } else if (this.bulkValue === "assign_course_to_employee") {
        if (this.assigned_course_id !== "") {
          let data = {
            course_id: this.assigned_course_id,
            company_id: this.company_id,
            assign_to: [
              {
                employee_ids: [],
                assign_to: "employee"
              }
            ]
          };
          for (let id of this.selectedRows) {
            let obj = {
              id: id
            };
            data.assign_to[0].employee_ids.push(obj);
          }
          this.$http
            .post("course/assign", data, this.config)
            .then(resp => {
              this.assigning = false;
              this.courseAssigneeModal = false;
              this.bulkValue = "";
              this.assigned_course_id = "";
              this.refresh();
               if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
                Swal.fire({
                  title: "Success!",
                  text: "Course has been Assigned to these Employees",
                  icon: "success"
                });
              }else{
                Swal.fire({
                  title: "Success!", 
                  html: '<ul style="text-align: left;"><li>Course(s) Assigned: '+ resp.data.assigned +'</li><li>Course(s) In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course(s) Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                  icon: "success"
                });
              }
            })
            .catch(function(error) {
              Swal.fire({
                title: "Error!",
                html: error.response.data.message,
                icon: "error"
              });
            });
        } else {
          Swal.fire({
            title: "Error!",
            text: "All fields are required!",
            icon: "error"
          });
        }
      }
    },
    bulkClicked() {
      this.check_all = false;
      if (this.bulkValue === "reassign_location") {
        if (this.selectedRows.length > 0) {
          this.bulkReassignLocation();
        } else {
          this.bulkValue = "";
          Swal.fire({
            title: "Error!",
            text: "Please Select Employees First!",
            icon: "error",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: true
          });
        }
      } else if (this.bulkValue === "delete_course_from_location") {
        this.courseUnassignLocation = true;
      } else if (this.bulkValue === "inactive_status") {
        if (this.selectedRows.length > 0) {
          this.bulkChangeStatusToInactive();
        } else {
          this.bulkValue = "";
          Swal.fire({
            title: "Error!",
            text: "Please Select Employees First!",
            icon: "error",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: true
          });
        }
      } else if (this.bulkValue === "active_status") {
        if (this.selectedRows.length > 0) {
          this.bulkChangeStatusToActive();
        } else {
          this.bulkValue = "";
          Swal.fire({
            title: "Error!",
            text: "Please Select Employees First!",
            icon: "error",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: true
          });
        }
      } else if (this.bulkValue === "assign_course_to_employee") {
        if (this.selectedRows.length > 0) {
          this.courseAssigneeModal = true;
        } else {
          this.bulkValue = "";
          Swal.fire({
            title: "Error!",
            text: "Please Select Employees First!",
            icon: "error",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: true
          });
        }
      } else if (this.bulkValue === "assign_course_to_location") {
        this.courseAssigneeModal = true;
      }
    },
    bulkReassignLocation() {
      this.reassignLocationModel = true;
    },
    bulkChangeStatusToActive() {
      this.bulkValue = "";
      let self = this;
      let status = 1;
      for (let id in self.selectedRows) {
        self.$http
          .put(
            "/employees/update_status/" + self.selectedRows[id],
            {
              status: status
            },
            self.config
          )
          .then(resp => {
            if (parseInt(id) === self.selectedRows.length - 1) {
              self.selectedRows = [];
              self.refresh();
              Swal.fire({
                title: "Success!",
                text: "Status has been Changed.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            }
          });
      }
    },
    bulkChangeStatusToInactive() {
      this.bulkValue = "";
      let self = this;
      let status = 0;
      for (let id in self.selectedRows) {
        self.$http
          .put(
            "/employees/update_status/" + self.selectedRows[id],
            {
              status: status
            },
            self.config
          )
          .then(resp => {
            if (parseInt(id) === self.selectedRows.length - 1) {
              self.selectedRows = [];
              self.refresh();
              Swal.fire({
                title: "Success!",
                text: "Status has been Changed.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            }
          });
      }
    },
    employeeCheckchange(row) {
      if (this.selectedRows.includes(row.id)) {
        this.selectedRows.splice(this.selectedRows.indexOf(row.id), 1);
      } else {
        this.selectedRows.push(row.id);
      }
    },
    unAssignedCourse(index, row) {
      Swal.fire({
        title: "You are about to unassign this course.",
        text: "Would you like to continue?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false
      }).then(result => {
        if (result.value) {
          this.$http
            .post(
              "employees/unassign_course",
              {
                course_id: row.id,
                employee_id: this.course_employee_id
              },
              this.config
            )
            .then(resp => {
              this.employeeDataModal = false;
              this.refresh();
              Swal.fire({
                title: "Success!",
                text: "Course has been Unassigned to this Employee!",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: true
              });
            });
        }
      });
    },
    assignBulkLocation() {
      if (this.bulk_location_id !== "") {
        let ids = [];
        for (let id of this.selectedRows) {
          let obj = {
            id: id
          };
          ids.push(obj);
        }
        this.$http
          .post(
            "employees/reassign_location",
            {
              location_id: this.bulk_location_id,
              employee_ids: ids
            },
            this.config
          )
          .then(resp => {
            this.reassignLocationModel = false;
            this.bulkValue = "";
            this.bulk_location_id = "";
            Swal.fire({
              title: "Success!",
              text: "New Location has been Assigned to these Employees!",
              icon: "success",
              confirmButtonClass: "btn btn-success btn-fill",
              buttonsStyling: true
            });
          });
      } else {
        Swal.fire({
          title: "Error!",
          text: "Please Select Any Location!",
          icon: "error",
          confirmButtonClass: "btn btn-success btn-fill",
          buttonsStyling: true
        });
      }
    },
    sendReminderEmail() {
      let self = this;
      // let done = false;
      Swal.fire({
        title: "Are you sure?",
        type: "warning",
        text:
          "You want to send Reminder Email to " +
          self.employeeEmailData.first_name +
          " " +
          self.employeeEmailData.last_name +
          "?",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(function() {
          let ids = [];
          let obj = {
            id: self.employeeEmailData.id
          };
          ids.push(obj);
          self.$http
            .post("employees/course_reminder_email", {
              company_id: self.company_id,
              ids: ids
            })
            .then(resp => {
              self.employeeEmailModal = false;
              Swal.fire({
                title: "Success!",
                text: "Email has been sent!.",
                type: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            });
        })
        .catch(function() {});
    },
    sendWelcomeEmail() {
      let self = this;
      // let done = false;
      Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        text:
          "You want to send Welcome Email to " +
          self.employeeEmailData.first_name +
          " " +
          self.employeeEmailData.last_name +
          "?",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(function() {
          let ids = [];
          let obj = {
            id: self.employeeEmailData.id
          };
          ids.push(obj);
          self.$http
            .post("employees/welcome_email", {
              company_id: self.company_id,
              ids: ids
            })
            .then(resp => {
              self.employeeEmailModal = false;
              Swal.fire({
                title: "Success!",
                text: "Email has been sent!.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            });
        })
        .catch(function() {});
    },
    sendPasswordResetEmail() {
      let self = this;
      this.$http
        .post("employees/password_reset", {
          user_id: this.employeeEmailData.id
        })
        .then(resp => {
          this.employeeEmailModal = false;
          Swal.fire({
            title: "Success!",
            text:
              "New password has been sent to " +
              self.employeeEmailData.first_name +
              "'s Email!",
            type: "success"
          });
        });
    },
    openCourseDetails(row) {
      this.course_employee_id = row.id;
      this.employeeCoursesData.first_name = row.first_name;
      this.employeeCoursesData.last_name = row.last_name;
      this.employeeCoursesData.location = row.location;
      this.employeeCoursesData.courseData = row.courses;
      this.employeeDataModal = true;
    },
    handleEnvelope(index, row) {
      this.employeeEmailData.first_name = row.first_name;
      this.employeeEmailData.last_name = row.last_name;
      this.employeeEmailData.location = row.location;
      this.employeeEmailData.id = row.id;
      this.employeeEmailModal = true;
    },
    handleEdit(index, row) {
      this.$router.push("/add_employee?id=" + row.id);
    },
    handleCertificate() {},
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
                "/employees/update_status/" + row.id,
                {
                  status: status
                },
                self.config
              )
              .then(resp => {
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
    changeLocationStatus() {
      let prev_val = this.location.locationStatus;
      let status = "";
      if (prev_val) {
        status = 0;
      } else {
        status = 1;
      }
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(function() {
          self.$http
            .put(
              "/location/update_status/" + self.filters.location_id,
              {
                status: status
              },
              self.config
            )
            .then(resp => {
              Swal.fire({
                title: "Success!",
                text: "Status has been Changed.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
              self.location.locationStatus = !prev_val;
            });
        })
        .catch(function() {
          self.location.locationStatus = prev_val;
        });
    },
    refresh() {
      this.$http
        .post("employees/list", {
          role: "admin",
          search: this.searchQuery,
          location: this.filters.location_id,
          employee_status: this.filters.employeStatus,
          company_id: this.company_id
        })
        .then(resp => {
          let employee_data = resp.data.employee;
          this.tableData = [];
          for (let data of employee_data) {
            let obj = {
              id: data.id,
              checked: false,
              first_name: data.employee_first_name,
              last_name: data.employee_last_name,
              location: data.location.location_name,
              courses: data.courses,
              passOpenFail:
                data.course_pass_count +
                " / " +
                data.course_open_count +
                " / " +
                (data.course_fail_count + data.course_expired_count),
              pass: data.course_pass_count,
              open: data.course_open_count,
              fail: data.course_fail_count + data.course_expired_count,
              status: true,
              type: data.employee_type
            };
            if (data.employee_status) {
              obj.status = true;
            } else {
              obj.status = false;
            }
            this.tableData.push(obj);
          }
        });
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
  .detailsGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Check";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Location";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Name";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Employee Type";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "P/O/F";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Status";
  }
  .detailsGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Actions";
  }

  .pofGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Lesson Name";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Lesson Status";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Date Completed";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Date Assigned";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Due Date";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Actions";
  }
}
</style>
