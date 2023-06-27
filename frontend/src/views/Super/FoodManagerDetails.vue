<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <div class="row mb-2">
      <div class="col-md-12 text-right">
        <base-button class="custom-btn" v-on:click="exportExcel()"
          >Excel Download</base-button
        >
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <base-input label="Bulk Action">
          <el-select
            class="select-primary"
            placeholder="Select Action"
            v-model="filters.action"
            v-on:change="bulkAction()"
          >
            <el-option
              v-for="item in actionList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            >
            </el-option>
          </el-select>
        </base-input>
      </div>
      <div class="col-md-6">
        <base-input label="Year Filter">
          <el-select
            v-on:change="fetchData()"
            class="select-primary"
            placeholder="Filter By Year"
            v-model="filters.year"
          >
            <el-option
              v-for="year in years"
              :key="year"
              :label="year"
              :value="year"
            >
            </el-option>
          </el-select>
        </base-input>
      </div>
    </div>
    <el-table
      class="table-striped"
      header-row-class-name="thead-light custom-thead-light"
      @selection-change="selectionChange"
      :data="foodMangerEmployees"
    >
      <el-table-column type="selection"></el-table-column>

      <el-table-column min-width="120" align="left" label="First Name">
        <template slot-scope="props">
          <span>{{ props.row.first_name }}</span>
        </template>
      </el-table-column>
      <el-table-column min-width="120" align="left" label="Last Name">
        <template slot-scope="props">
          <span>{{ props.row.last_name }}</span>
        </template>
      </el-table-column>
      <el-table-column min-width="110" align="left" label="Status">
        <template slot-scope="props">
          <span>{{ props.row.status }}</span>
        </template>
      </el-table-column>
      <el-table-column min-width="100" align="left" label="Action">
        <template slot-scope="props">
          <div class="d-flex custom-size" v-if="props.row.status == 'Passed'">
            <span
              ><base-button
                @click.prevent="
                  getProctoredExamCertificate(props.row.certificate_url)
                "
                class="success"
                type=""
                size="sm"
                ><i class="text-success fa fa-eye"></i></base-button
            ></span>
          </div>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import XLSX from "xlsx";
export default {
  name: "food-manager-details",
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  props: {
    company_id: Number,
  },
  data() {
    return {
      selectedRows: [],
      foodMangerEmployees: [],
      loading: false,
      filters: [
        {
          action: "",
          year: "",
        },
      ],
      actionList: [
        {
          label: "Send Email Reminder",
          value: "email_reminder",
        },
        {
          label: "Reassign Course",
          value: "reassign_course",
        },
        {
          label: "Download Certificates",
          value: "download_certificates",
        },
      ],
    };
  },
  computed: {
    // get past 10 years with current year
    years() {
      return [...Array(11)].map((a, b) => new Date().getFullYear() - b);
    },
  },
  created: function () {
    this.filters.year = new Date().getFullYear();
    this.fetchData();
  },
  methods: {
    // fetch employee data to list
    fetchData() {
      this.loading = true;
      this.$http
        .post("foodmanager/food_manager_employees", {
          company_id: this.company_id,
          year: this.filters.year,
        })
        .then((resp) => {
          let empData = resp.data.report;
          this.foodMangerEmployees = [];
          for (let data of empData) {
            let obj = {
              id: data.employee_id,
              certificate_id: data.id,
              first_name: data.first_name,
              last_name: data.last_name,
              certificate_url: data.certificate_url,
              status:
                data.employee_course_status == 1
                  ? "Passed"
                  : data.employee_course_status == 2
                  ? "Open"
                  : data.employee_course_status == 0
                  ? "Failed"
                  : data.employee_course_status == 3
                  ? "Expired"
                  : "",
            };
            this.foodMangerEmployees.push(obj);
            this.$emit("fm_data_fetched", this.foodMangerEmployees.length);
          }
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          }
        })
        .finally(() => (this.loading = false));
    },

    // get food manager certiticate
    getProctoredExamCertificate: function (certificateURL) {
      this.$http
        .post("course/proctored-exam-certificate", {
          certificateURL: certificateURL,
        })
        .then((resp) => {
          window.open(resp.data.certificate_url, "_blank");
        });
    },

    // get selected employees
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

        //
      }
    },

    // Preform bulk action
    bulkAction() {
      if (this.selectedRows.length == 0) {
        return Swal.fire({
          title: "Error",
          text: "Please Select Employee(s) to continue!",
          icon: "error",
          buttonsStyling: true,
        });
      } else {
        if (this.filters.action == "email_reminder") {
          this.sendEmailReminder();
        } else if (this.filters.action == "reassign_course") {
          this.reassignCourse();
        } else if (this.filters.action == "download_certificates") {
          this.downloadCertificate();
        }
      }
    },

    //download report in excel format
    exportExcel() {
      this.$http
        .post("foodmanager/food_manager_employees", {
          company_id: this.company_id,
          year: this.filters.year,
        })
        .then((resp) => {
          let report_data = resp.data.download;
          this.items = report_data;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, "FoodManagerReport.xlsx");
        })
        .finally(() => (this.loading = false));
    },

    //send reminder of course
    sendEmailReminder() {
      Swal.fire({
        title: "Are you sure?",
        text: "You want to send reminder for this course.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        cancelButtonText: "No",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          if (this.selectedRows.length > 0) {
            this.$http
              .post("foodmanager/reminder_food_manager", {
                employee_id: this.selectedRows,
              })
              .then((resp) => {
                this.fetchData();
                Swal.fire({
                  title: "Success",
                  text: "Course reminder sent successfully.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  confirmButtonText: "Ok",
                  buttonsStyling: false,
                });
              });
          } else {
            this.filters.action = "";
            Swal.fire({
              title: "Error",
              text: "Please Select Employee(s) to continue!",
              confirmButtonClass: "btn btn-success btn-fill",
              confirmButtonText: "Ok",
              icon: "error",
              buttonsStyling: false,
            });
          }
        }
        this.filters.action = "";
      });
    },

    // reassign course
    reassignCourse() {
      // alert("We have to reassign course here.");
      Swal.fire({
        title: "Are you sure?",
        text: "You want to reassign this course.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        cancelButtonText: "No",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          if (this.selectedRows.length > 0) {
            this.$http
              .post("foodmanager/reassign_food_manager", {
                employee_id: this.selectedRows,
              })
              .then((resp) => {
                this.fetchData();
                Swal.fire({
                  title: "Success",
                  text: "Course Reassigned Successfully.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  confirmButtonText: "Ok",
                  buttonsStyling: false,
                });
              });
          } else {
            this.filters.action = "";
            Swal.fire({
              title: "Error",
              text: "Please Select Employee(s) to continue!",
              confirmButtonClass: "btn btn-success btn-fill",
              confirmButtonText: "Ok",
              icon: "error",
              buttonsStyling: false,
            });
          }
        }
        this.filters.action = "";
      });
    },

    // download multiple certiifcates
    downloadCertificate() {
      if (this.selectedRows.length > 0) {

       window.open(this.baseUrl + '/downloadFoodManagerCertificates/' + this.selectedRows.join('_'));

      } else {
        return Swal.fire({
          title: "Error",
          text: "Please Select Employee(s) to continue!",
          icon: "error",
          buttonsStyling: true,
        });
      }
     
    },
  },
};
</script>
