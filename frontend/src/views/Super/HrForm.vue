<template>
  <div v-loading.fullscreen.lock="loading">
    <div>
      <div class="row">
        <div class="col-md-12 text-right mb-2">
          <base-button @click="showUploadPopup">Add New</base-button>
        </div>
        <div class="col-md-12 user-eltable">
          <el-table
            header-row-class-name="thead-light custom-thead-light"
            role="table"
            class="employeeresGrid"
            :data="hrform_data"
          >
            <el-table-column min-width="250px" align="left" label="File Name">
              <template slot-scope="props">
                {{ props.row.file_name }}
              </template>
            </el-table-column>
            <el-table-column
              min-width="250px"
              align="left"
              label="File Description"
            >
              <template slot-scope="props">
                {{ props.row.description }}
              </template>
            </el-table-column>
            <el-table-column
              min-width="200px"
              align="left"
              label="Action"
              class="table-custom-size"
            >
              <div slot-scope="{ $index, row }" class="d-flex custom-size">
                <el-tooltip content="Preview" placement="top">
                <base-button
                  v-if="row.file"
                  type=""
                  size="sm"
                  @click.native="handleView(row)"
                  data-toggle="tooltip"
                  data-original-title="Preview"
                >
                  <i class="text-success fa fa-eye"></i>
                </base-button>
                <base-button v-else 
                 data-toggle="tooltip"
                  data-original-title="Preview"
                disabled type="" size="sm">
                  <i class="text-success fa fa-eye"></i>
                </base-button>
                </el-tooltip>
                <el-tooltip content="Download" placement="top">
                <base-button
                  v-if="row.file"
                  type=""
                  size="sm"
                  @click.native="handleDownload(row)"
                  data-toggle="tooltip"
                  data-original-title="Download"
                >
                  <i class="text-warning fa fa-download"></i>
                </base-button>
                <base-button v-else disabled type="" size="sm">
                  <i class="text-warning fa fa-download"></i>
                </base-button>
                </el-tooltip>
                <el-tooltip content="Edit" placement="top">
                <base-button type=""
                 data-toggle="tooltip"
                 data-original-title="Edit"
                 size="sm" @click.native="handleEdit(row)">
                  <i class="text-primary fa fa-edit"></i>
                </base-button>
                </el-tooltip>
                <el-tooltip content="Delete" placement="top">
                <base-button
                  type=""
                  size="sm"
                  @click.native="handleDelete($index, row)"
                   data-toggle="tooltip"
                   data-original-title="Delete"
                >
                  <i class="text-danger fa fa-trash-o"></i>
                </base-button>
                </el-tooltip>
                <el-tooltip content="Assign" placement="top">
                <base-button
                  type=""
                  size="sm"
                  @click.native="handleAssignment($index, row)"
                   data-toggle="tooltip"
                   data-original-title="Assign"
                >
                  <i class="text-danger fa fa-tasks"></i>
                </base-button>
                </el-tooltip>
              </div>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </div>
    <modal :show.sync="uploadPopup" v-on:close="cleanPopUpData">
      <h3 class="mb-0" slot="header">
        {{ editHrForm ? "Update" : "Upload" }} HR Form
      </h3>
      <div class="row">
        <div class="col-md-12">
          <base-input
            label="File Name:"
            v-model="hrform.file_name"
          ></base-input>
        </div>
        <div class="col-md-12">
          <base-input label="File Description:">
            <textarea
              class="form-control"
              rows="3"
              v-model="hrform.file_description"
            >
            </textarea>
          </base-input>
        </div>
        <div class="col-md-12">
          <base-input label="Upload File:">
            <input
              class="form-control"
              name="..."
              v-on:change="getAllFiles($event)"
              type="file"
            />
          </base-input>
        </div>

        <div class="col-md-4">
          <label class="form-control-label mb-2">Admin:</label><br />
          <base-switch type="success" v-model="hrform.default_admin"></base-switch>
        </div>
         <div class="col-md-4">
          <label class="form-control-label mb-2">Mangers:</label><br />
          <base-switch type="success" v-model="hrform.default_manager"></base-switch>
        </div>

        <div class="col-md-4">
          <label class="form-control-label mb-2">Employees:</label><br />
          <base-switch type="success" v-model="hrform.default_employee"></base-switch>
        </div>

        <div class="mt-4 col-md-12 text-right">

          <base-button type="danger" @click="cleanPopUpData()"
            >Cancel</base-button
          >
          <base-button @click="saveHrFormData()">{{
            editHrForm ? "Update" : "Submit"
          }}</base-button>
        </div>
      </div>
    </modal>

    <modal :show.sync="assignPopUp" v-on:close="cleanAssignPopUpData">
      <h3 class="mb-0" slot="header">Assign Company Document</h3>
      <div class="row">
        <div class="col-md-6">
          <h4>File Name:</h4>
          <p>
            <b>{{ hrform.file_name }}</b>
          </p>
        </div>
        <div class="col-md-6">
          <h4>File:</h4>
          <p>
            <a
              :href="baseUrl + '/hr-forms/' + hrform.file"
              target="_blank"
              class="linkColor"
              ><b>Preview</b></a
            >
          </p>
        </div>
        <div class="col-md-12">
          <label class="form-control-label">Assign to:</label><br />
          <el-select
            filterable
            multiple
            class="mr-3"
            style="width: 100%"
            placeholder="Select Employees"
            v-model="employee_ids"
          >
            <el-option
              v-for="(option, index) in employees"
              class="select-primary"
              rules="required"
              :value="option.id"
              :label="option.first_name + ' ' + option.last_name"
              :key="'employees_' + index"
            >
            </el-option>
          </el-select>
        </div>
        <div class="mt-4 col-md-12 text-right">
         
          <base-button type="danger" @click="cleanAssignPopUpData()"
            >Cancel</base-button
          >
           <base-button @click="assignHrForm()"> Submit</base-button>
        </div>
      </div>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import BaseButton from "../../components/BaseButton.vue";
import BaseInput from "../../components/Inputs/BaseInput.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import BaseSwitch from "../../components/BaseSwitch.vue";

export default {
  name: "hr-form",
  components: {
    BaseButton,
    BaseInput,
    BaseSwitch,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      loading:false,
      uploadPopup: false,
      assignPopUp: false,
      editHrForm: false,
      hrform_data: [],
      employee_ids: [],
      employees: [],
      baseUrl: this.$baseUrl,
      hrform: {
        id: "",
        file_name: "",
        file_description: "",
        file: "",
        default_admin: true,
        default_manager:true,
        default_employee:false
      },
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    showUploadPopup() {
      this.uploadPopup = true;
    },
    cleanPopUpData() {
      this.uploadPopup = false;
      this.employee_ids = [];
      this.hrform = {
        id: "",
        file_name: "",
        file_description: "",
        file: "",
        default_admin: true,
        default_manager:true,
        default_employee:false
      };
      this.editHrForm = false;
    },
    getAllFiles: function (e) {
      let file = e.target.files || e.dataTransfer.files;
      this.hrform.file = file[0];
    },
    fetchData() {
      this.$http.get("resources/all_company_hr_forms").then((resp) => {
        this.hrform_data = [];
        for (let data of resp.data) {
          let obj = {
            id: data.id,
            file_name: data.name,
            description: data.description,
            file: data.file,
            default_admin: data.default_admin,
            default_manager: data.default_manager,
            default_employee: data.default_employee,
            assigned_employees: [],
          };
          for (let data1 of data.assigned_employees) {
            obj.assigned_employees.push(data1.employee_id);
          }
          this.hrform_data.push(obj);
        }
      });
    },
    handleView(row) {
      window.open(this.baseUrl + "/hr-forms/" + row.file, "_blank");
    },
        async handleDownload(row) {
            await this.$http.post("resources/download-hr-forms", {
                id: row.id,
                type: 'Company Forms',
            }).then(response => {
                var file_path = this.baseUrl + "/hr-forms/" + row.file;
                var a = document.createElement("A");
                a.href = file_path;
                a.download = file_path.substr(file_path.lastIndexOf("/") + 1);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        },
    handleAssignment(index, row) {
      this.$http.get("resources/company_type_employee").then((resp) => {
        this.employees = resp.data;
      });
      this.hrform = {
        id: row.id,
        file_name: row.file_name,
        file_description: row.description,
        file: row.file,
        default_admin: row.default_admin,
        default_manager: row.default_manager,
        default_employee: row.default_employee
      };
      this.employee_ids = row.assigned_employees;
      this.assignPopUp = true;
    },
    cleanAssignPopUpData() {
      this.assignPopUp = false;
      this.employee_ids = [];
    },
    handleEdit(row) {
      this.editHrForm = true;
      this.hrform = {
        id: row.id,
        file_name: row.file_name,
        file_description: row.description,
        file: row.file,
        default_admin: row.default_admin,
        default_manager: row.default_manager,
        default_employee: row.default_employee,
      };
      this.showUploadPopup();
    },
    saveHrFormData() {
      this.loading = true;
      var formData = new FormData();
      formData.append("file_name", this.hrform.file_name);
      formData.append("file_description", this.hrform.file_description);
      formData.append("file", this.hrform.file);
      formData.append("permissions_default_admin", this.hrform.default_admin);
       formData.append("permissions_default_manager", this.hrform.default_manager);
       formData.append("permissions_default_employee", this.hrform.default_employee);
      if (!this.editHrForm) {
        this.$http.post("resources/save_hrform_data", formData).then((resp) => {
          Swal.fire({
            text: resp.data,
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              this.hrform.file_name = "";
              this.hrform.file_description = "";
              this.hrform.file = "";
              this.employee_ids = [];
              this.hrform.default_admin = true;
              this.hrform.default_manager = true;
              this.hrform.default_employee = false;
              this.uploadPopup = false;
              this.fetchData();
            }
          });
        }).finally(() => (this.loading = false));
      } else {
        formData.append("id", this.hrform.id);
        this.$http
          .post("resources/update_hrform_data", formData)
          .then((resp) => {
            Swal.fire({
              text: resp.data,
              icon: "success",
              confirmButtonClass: "btn btn-success btn-fill",
              buttonsStyling: false,
            }).then((result) => {
              if (result.value) {
                this.hrform.file_name = "";
                this.hrform.file_description = "";
                this.hrform.file = "";
                this.employee_ids = [];
                this.hrform.default_admin = true;
                this.hrform.default_manager = true;
                this.hrform.default_employee = false;
                this.uploadPopup = false;
                this.fetchData();
              }
            });
          }).finally(() => (this.loading = false));
      }
    },
    assignHrForm() {
      this.$http
        .post("resources/assign_hrform", {
          hrform_id: this.hrform.id,
          file_name: this.hrform.file_name,
          file: this.hrform.file,
          assign_employee: this.employee_ids,
        })
        .then((resp) => {
          Swal.fire({
            text: resp.data,
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              this.hrform.file_name = "";
              this.hrform.file_description = "";
              this.hrform.file = "";
              this.employee_ids = [];
              this.hrform.default_admin = true;
              this.hrform.default_manager = true;
              this.hrform.default_employee = false;
              this.assignPopUp = false;
              this.fetchData();
            }
          });
        });
    },
    handleDelete(index, row) {
      Swal.fire({
        title: "Are you sure?",
        text: "You want to Delete?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false,
      })
        .then((result) => {
          if (result.value) {
            this.$http
              .post("resources/delete_hr_form", {
                hrform_id: row.id,
              })
              .then((resp) => {
                console.log(resp);
                Swal.fire({
                  text: resp.data,
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false,
                }).then((result) => {
                  if (result.value) {
                    this.fetchData();
                  }
                });
              });
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
        });
    },
  },
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}
.custom-size >>> .btn-sm {
  display: flex;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .employeeresGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "File Name";
  }
  .employeeresGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "File Description";
  }
  .employeeresGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Permissions";
  }
  .employeeresGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Action";
  }
}
</style>
