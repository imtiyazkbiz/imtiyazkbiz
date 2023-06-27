<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6">
                <h3 class="mb-0">
                  Assigned Employees
                </h3>
              </div>
              <div class="col-md-6 ">
                <div style="float:right;">
                  <router-link
                    :to="
                      '/certificate_details?id=' +
                        certificate_id +
                        '&course=' +
                        course_id
                    "
                  >
                    <base-button class="custom-btn">
                      Course Certificates
                    </base-button>
                  </router-link>
                </div>
              </div>
            </div>
          </template>

          <div>
            <div class="row text-right justify-content-end">
              <div class="col-3 form-group">
                <el-select
                  v-model="bulkValue"
                  placeholder="Assign Course"
                  v-on:change="bulkClicked()"
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
            </div>
            <div class="user-eltable">
              <el-table
                :data="queriedData"
                row-key="id"
                role="table"
                class="coursedetailGrid"
                header-row-class-name="thead-light custom-thead-light"
                @sort-change="sortChange"
                @selection-change="selectionChange"
              >
                <el-table-column
                  min-width="120"
                  align="left"
                  label="First Name"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.first_name }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="120" align="left" label="Last Name">
                  <template slot-scope="props">
                    <span>{{ props.row.last_name }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="80" align="left" label="Due Date">
                  <template slot-scope="props">
                    <span>{{ formattedDate(props.row.due_date) }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="120"
                  align="left"
                  label="Completion Date"
                >
                  <template slot-scope="props">
                    <span v-if="props.row.completion_date">{{
                      formattedDate(props.row.completion_date)
                    }}</span>
                    <span v-else>Not Available</span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="120"
                  align="left"
                  label="Lesson Status"
                >
                  <template slot-scope="props">
                    <span
                      v-if="props.row.lesson_status === 1"
                      style="color:green"
                      >Passed</span
                    >
                    <span v-if="props.row.lesson_status === 0" style="color:red"
                      >Failed</span
                    >
                    <span
                      v-if="props.row.lesson_status === 2"
                      style="color:rgb(255, 214, 0)"
                      >Open</span
                    >
                    <span v-if="props.row.lesson_status === 3" style="color:red"
                      >Expired</span
                    >
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="170"
                  align="left"
                  label="Location Name"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.location }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Action"
                  align="left"
                  property=""
                  min-width="100px"
                >
                  <template slot-scope="props">
                    <base-button
                      v-if="props.row.lesson_status != 1"
                      class="custom-btn POF_btn"
                      v-on:click="unAssignedCourse(props.row.id)"
                      >Unassign</base-button
                    >
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
        </card>
      </div>
    </div>

    <modal :show.sync="employeeAssigneeModal">
      <h3 slot="header" style="color: #444C57" class="title title-up ">
        Assign Course to Employee
      </h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <div>
              <el-select
                class="w-100"
                filterable
                multiple
                v-model="employees_ids"
                placeholder="Choose Employees"
              >
                <el-option
                  v-for="(option, index) in employees"
                  class="select-primary"
                  :value="option.id"
                  :label="option.first_name + ' ' + option.last_name"
                  :key="index"
                >
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <div class="text-right mt-4">
          <base-button type="danger" @click.prevent="closeModels">
            Cancel
          </base-button>
          <base-button class="custom-btn" @click.prevent="assignCourse">
            {{ "Assign Course" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="locationAssigneeModal">
      <h3 slot="header" class="title title-up ">Assign Course to Location</h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <el-select
              class=" mr-3"
              style="width: 100%"
              placeholder="Select Location"
              v-model="assignee.id"
            >
              <el-option
                v-for="(option, index) in locations"
                class="select-primary"
                :value="option.value"
                :label="option.label"
                :key="'test_question' + index"
              >
              </el-option>
            </el-select>
          </div>
        </div>

        <div class="text-right mt-4">
          <base-button type="danger" @click.prevent="closeModels">
            Cancel </base-button
          >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <base-button class="custom-btn" @click.prevent="assignCourse">
            {{ "Assign Course" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="jobTitleAssigneeModal">
      <h3 slot="header">Assign Course to Job Title</h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <el-select
              class=" mr-3"
              style="width: 100%"
              placeholder="Select Job Title"
              v-model="assignee.id"
            >
              <el-option
                v-for="(option, index) in jobTitles"
                class="select-primary"
                :value="option.value"
                :label="option.label"
                :key="'test_question' + index"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="text-right mt-4">
          <base-button type="danger" @click.prevent="closeModels">
            Cancel
          </base-button>
          <base-button class="custom-btn" @click.prevent="assignCourse">
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
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
//import swal from 'sweetalert';
import Swal from "sweetalert2";
import moment from "moment";
export default {
  mixins: [clientPaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      selectedRows: [],
      searchQuery: "",
      employeeAssigneeModal: false,
      locationAssigneeModal: false,
      jobTitleAssigneeModal: false,
      locations: [],
      jobTitles: [],
      employees: [],
      employees_ids: [],
      assignee: {
        id: "",
        due_date: ""
      },
      confirmPurchaseModal: false,
      bulkValue: "",
      bulk_array: [
        {
          label: "Assign By Location",
          value: "location"
        },
        {
          label: "Assign By Employee",
          value: "employee"
        },
        {
          label: "Assign By Job Title",
          value: "job_title"
        }
      ],

      course: {
        courseLocation: [],
        course_name: "",
        course_length: "",
        allowed_attempts: "",
        course_cost: "",
        course_description: "",
        course_resources: [],
        course_lessons: [],
        course_test: []
      },
      tableData: [],
      company_id: "",
      locationManager: false,
      location_id: ""
    };
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.company_id = localStorage.getItem("hot-user-id");
    }
    if (localStorage.getItem("hot-sidebar") === "location_manager") {
      this.locationManager = true;
      this.location_id = localStorage.getItem("hot-location-id");
    }
    this.$http.get("employees/jobTitles").then(resp => {
      let jobtitle = resp.data;
      for (let data of jobtitle) {
        let obj = {
          value: data.id,
          label: data.name
        };
        this.jobTitles.push(obj);
      }
    });

    if (this.$route.query.id) {
      this.course_id = this.$route.query.id;
      this.certificate_id = this.$route.query.certificate_id;
      this.$http
        .post(
          "course/employees",
          {
            course_id: this.course_id,
            company_id: this.company_id
          },
          this.config
        )
        .then(resp => {
          let data = resp.data;
          this.tableData = [];
          for (let emp of data.data) {
            let empl_obj = {
              first_name: emp.first_name,
              id: emp.employee_id,
              last_name: emp.last_name,
              due_date: emp.employee_course_due_date,
              completion_date: emp.employee_course_date_completed,
              lesson_status: emp.employee_course_status,
              location: emp.company_name
            };

            this.tableData.push(empl_obj);
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
          if (resp.data.length > 1) {
            let defaultOption = {
              label: "All Location",
              value: "all_location"
            };
            this.locations.push(defaultOption);
          }
        });
    }
  },
  methods: {
    formattedDate(data) {
      return moment(data).format("MM-DD-YYYY");
    },
    unAssignedCourse(emp_id) {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes, unassign it!",
        cancelButtonText: "No",
        buttonsStyling: false
      }).then(result => {
        if (result.value) {
          self.$http
            .post(
              "employees/unassign_course",
              {
                course_id: self.course_id,
                employee_id: emp_id
              },
              self.config
            )
            .then(resp => {
              self.refresh();
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
    getEmployees() {
      this.$http
        .post(
          "employees/company_employee",
          {
            search: this.searchQuery,
            company_id: this.company_id
          },
          this.config
        )
        .then(resp => {
          this.employees = resp.data;
        });
    },
    assignCourse() {
      if (this.bulkValue === "location") {
        if (this.assignee.id !== "") {
          let data = {
            course_id: this.course_id,
            company_id: this.company_id,
            assign_to: [
              {
                location_id: this.assignee.id,
                assign_to: "location"
              }
            ]
          };
          this.$http
            .post("course/assign", data, this.config)
            .then(resp => {
              this.locationAssigneeModal = false;
              this.assignee.id = "";
              this.refresh();
               if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
              Swal.fire({
                title: "Success!",
                text: "Course has been Assigned to this Location",
                icon: "success"
              });
              }else{
                 Swal.fire({
                  title: "Success!", 
                  html: '<ul style="text-align: left;"><li>Course Assigned: '+ resp.data.assigned +'</li><li>Course In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
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
            text: "All Fields are Required!",
            icon: "error"
          });
        }
      } else if (this.bulkValue === "employee") {
        if (!(this.employees_ids.length <= 0)) {
          let data = {
            course_id: this.course_id,
            company_id: this.company_id,
            assign_to: [
              {
                employee_ids: [],
                assign_to: "employee"
              }
            ]
          };
          for (let id of this.employees_ids) {
            let obj = {
              id: id
            };
            data.assign_to[0].employee_ids.push(obj);
          }
          this.$http
            .post("course/assign", data, this.config)
            .then(resp => {
              this.employeeAssigneeModal = false;
              this.refresh();
               if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
              Swal.fire({
                title: "Success!",
                text: "Course has been Assigned to this Employee",
                icon: "success"
              });
              }else{
                 Swal.fire({
                  title: "Success!", 
                  html: '<ul style="text-align: left;"><li>Course Assigned: '+ resp.data.assigned +'</li><li>Course In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
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
            text: "All Fields are Required!",
            icon: "error"
          });
        }
      } else if (this.bulkValue === "job_title") {
        if (this.assignee.id !== "") {
          let data = {
            course_id: this.course_id,
            company_id: this.company_id,
            assign_to: [
              {
                job_title: this.assignee.id,
                assign_to: "job_title"
              }
            ]
          };
          this.$http.post("course/assign", data, this.config).then(resp => {
            this.jobTitleAssigneeModal = false;
            this.assignee.id = "";
            this.refresh();
            if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
              Swal.fire({
                title: "Success!",
                text: "Course has been Assigned to this Job Title",
                icon: "success"
              });
              }else{
                 Swal.fire({
                  title: "Success!", 
                  html: '<ul style="text-align: left;"><li>Course Assigned: '+ resp.data.assigned +'</li><li>Course In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                  icon: "success"
                });
              }
          });
        } else {
          Swal.fire({
            title: "Error!",
            text: "All Fields are Required!",
            icon: "error"
          });
        }
      }
    },
    closeModels() {
      this.employeeAssigneeModal = false;
      this.locationAssigneeModal = false;
      this.jobTitleAssigneeModal = false;

      this.bulkValue = "";
    },
    refresh() {
      this.$http
        .post(
          "course/employees",
          {
            course_id: this.course_id,
            company_id: this.company_id
          },
          this.config
        )
        .then(resp => {
          let data = resp.data;
          this.tableData = [];
          for (let emp of data.data) {
            let empl_obj = {
              first_name: emp.first_name,
              last_name: emp.last_name,
              id: emp.employee_id,
              due_date: emp.employee_course_due_date,
              completion_date: emp.employee_course_date_completed,
              lesson_status: emp.employee_course_status,
              location: emp.company_name
            };

            this.tableData.push(empl_obj);
          }
        });
      this.$http
        .post("location/all", {
          company_id: this.company_id
        })
        .then(resp => {
          this.locations = [];
          for (let loc of resp.data) {
            let obj = {
              label: loc.name,
              value: loc.id
            };
            this.locations.push(obj);
          }
          if (resp.data.length > 1) {
            let defaultOption = {
              label: "All Location",
              value: "all_location"
            };
            this.locations.push(defaultOption);
          }
        });
    },
    bulkClicked() {
      this.assignee.id = "";
      if (this.bulkValue === "location") {
        this.locationAssigneeModal = true;
      } else if (this.bulkValue === "employee") {
        this.employeeAssigneeModal = true;
        this.getEmployees();
      } else if (this.bulkValue === "job_title") {
        this.jobTitleAssigneeModal = true;
      }
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    }
  },

  watch: {
    locationAssigneeModal: function(val) {
      if (!val) {
        this.bulkValue = "";
      }
    },
    employeeAssigneeModal: function(val) {
      if (!val) {
        this.bulkValue = "";
      }
    },
    jobTitleAssigneeModal: function(val) {
      if (!val) {
        this.bulkValue = "";
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
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "First Name";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Last Name";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Due Date";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Completion Date";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Lesson Status";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Location";
  }
  .coursedetailGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Actions";
  }
}
</style>
