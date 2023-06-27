<template>
  <div>
    <div class="user-eltable">
      <el-table
        role="table"
        :data="tableData"
        stripe
        highlight-current-row
        lazy
        row-key="id"
        id="tableOne"
        header-row-class-name="thead-light"
        class="loginReportGrid table-striped"
      >
        <el-table-column min-width="100px" prop="first_name">
          <template slot="header">
            <span>Sr no.</span>
          </template>
          <template slot-scope="props"> {{ props.row.sr_no }} </template>
        </el-table-column>

        <el-table-column min-width="100px" prop="last_name">
          <template slot="header">
            <span> Title </span>
          </template>
          <template slot-scope="props"> {{ props.row.title }} </template>
        </el-table-column>
        <el-table-column min-width="100px" prop="user_name">
          <template slot="header">
            <span> Assigned </span>
          </template>
          <!--   -->
          <template slot-scope="props">
            <span
              v-if="props.row.employeeSurveyCount > 0"
              @click="showAssignedEmployees(props.row)"
              class="linkColor"
              >{{ props.row.employeeSurveyCount }}</span
            ><span v-else>{{ props.row.employeeSurveyCount }}</span></template
          >
        </el-table-column>
        <el-table-column min-width="180px" label="Actions">
          <div slot-scope="{ row }" class="d-flex custom-size">
            <el-tooltip content="Edit" placement="top">
              <base-button
                class="success"
                @click="editSurvey(row)"
                type=""
                size="sm"
                icon
                data-toggle="tooltip"
                data-original-title="Edit"
              >
                <i class="text-success fas fa-pencil-square-o"></i>
              </base-button>
            </el-tooltip>

            <el-tooltip content="Delete" placement="top">
              <base-button
                class="danger"
                @click="deleteSurvey(row)"
                type=""
                size="sm"
                icon
                data-toggle="tooltip"
                data-original-title="Edit"
              >
                <i class="text-danger fas fa-trash"></i>
              </base-button>
            </el-tooltip>
            <el-tooltip content="Assign" placement="top">
              <base-button
                class="primary"
                @click="assignSurvey(row)"
                type=""
                size="sm"
                icon
                data-toggle="tooltip"
                data-original-title="Edit"
              >
                <i class="text-primary fa fa-plus"></i>
              </base-button>
            </el-tooltip>
          </div>
        </el-table-column>
      </el-table>
    </div>
    <div v-if="viewPdf">
    
      <post-login-survey-pdf
        :survey_id="preview_survey_id"
        :employee_id="preview_employee_id"
        :key="pdfCcounter"
      ></post-login-survey-pdf>
    </div>
    <modal :show.sync="showAssignedEmployeeModal">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Assigned Employees
      </h3>
      <el-table
        role="table"
        :data="employeesData"
        stripe
        highlight-current-row
        lazy
        row-key="id"
        id="tableOne"
        header-row-class-name="thead-light"
        class="loginReportGrid table-striped"
      >
        <el-table-column min-width="100px" prop="first_name">
          <template slot="header">
            <span> First Name </span>
          </template>
          <template slot-scope="props"> {{ props.row.first_name }} </template>
        </el-table-column>
        <el-table-column min-width="100px" prop="last_name">
          <template slot="header">
            <span> Last Name </span>
          </template>
          <template slot-scope="props"> {{ props.row.last_name }} </template>
        </el-table-column>
        <el-table-column min-width="100px" prop="action">
          <template slot="header">
            <span> Action </span>
          </template>
          <template slot-scope="props">
            <el-tooltip content="Preview" placement="top">
              <base-button
                type=""
                size="sm"
                @click.native="handleView(props.row)"
                data-toggle="tooltip"
                data-original-title="Preview"
              >
                <i class="text-success fa fa-eye"></i>
              </base-button>
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>
    </modal>
    <modal :show.sync="assignEmployeeModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Assign Employee
      </h3>
      <div class="row">
        <div class="col-md-4">
          <label class="form-control-label mb-2">Admins:</label><br />
          <base-switch type="success" v-model="default_admin"></base-switch>
        </div>
        <div class="col-md-4">
          <label class="form-control-label mb-2">Mangers:</label><br />
          <base-switch type="success" v-model="default_manager"></base-switch>
        </div>
        <div class="col-md-12">
          <label class="form-control-label mb-2"
            >Assign to Perticular Employee:</label
          ><br />
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
        <div class="col-md-12 text-right mt-2">
          <base-button @click="assigPostSurveyToEmployee">Assign</base-button>
        </div>
      </div>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import BaseInput from "../../components/Inputs/BaseInput.vue";
import BaseButton from "../../components/BaseButton.vue";
import PostLoginSurveyPdf from "@/views/Super/PostLoginSurveyPdf.vue";

export default {
  name: "post-login-survey",
  components: {
    BaseInput,
    BaseButton,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    PostLoginSurveyPdf
  },
  data() {
    return {
      pdfCcounter: 0,
      loading: false,
      tableData: [],
      employeesData: [],
      showAssignedEmployeeModal: false,
      assignEmployeeModel: false,
      default_admin: false,
      default_manager: false,
      employee_ids: [],
      employees: [],
      preview_employee_id:"",
      preview_survey_id:"",
      viewPdf:false,
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.$http.get("getPostLoginSurvey").then((resp) => {
        this.tableData = [];
        var srNo = 1;
        for (let data of resp.data) {
          let obj = {
            id: data.id,
            sr_no: srNo,
            title: data.name,
            employeeSurveyCount: data.employee_survey_count,
          };
          this.tableData.push(obj);
          srNo++;
        }
      });
    },
    handleView(row){
      this.preview_employee_id=row.id;
      this.preview_survey_id=row.survey_id;
      this.pdfCcounter++;
      this.viewPdf = true;
    },
    editSurvey(row) {
      this.$emit("editSurveyGrid", row.id);
    },
    deleteSurvey(row) {
      Swal.fire({
        title: "Warning!",
        html: "Are you sure you want to delete?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        cancelButtonText: "No",
        confirmButtonText: "Yes",
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          this.$http
            .post("deletePostLoginSurvey", {
              id: row.id,
            })
            .then((resp) => {
              Swal.fire({
                title: "success!",
                html: "Survey Deleted Successfully.",
                icon: "success",
              }).then((result) => {
                if (result.value) {
                  this.fetchData();
                }
              });
            });
        }
      });
    },
    showAssignedEmployees(row) {
      this.$http.get("getPostLoginSurveyEmployees/" + row.id).then((resp) => {
        this.employeesData = [];
        for (let data of resp.data) {
          let obj = {
            survey_id:row.id,
            id: data.id,
            first_name: data.first_name,
            last_name: data.last_name,
          };
          this.employeesData.push(obj);
        }
      });
      this.showAssignedEmployeeModal = true;
    },
    assignSurvey(row) {
      this.$http.get("resources/company_type_employee").then((resp) => {
        this.employees = resp.data;
      });
      this.survey_id = row.id;
      this.$http
        .post("getAssignedPostSurveyEmployees/" + this.survey_id)
        .then((resp) => {
          let surveyDetails = resp.data;
          this.employee_ids = [];
          for (let surveyDetail of surveyDetails) {
            this.default_admin = surveyDetail.for_admins;
            this.default_manager = surveyDetail.for_managers;
            let assigned_employees = surveyDetail.employees;
            for (let data of assigned_employees) {
              this.employee_ids.push(data.id);
            }
          }
        });
      this.assignEmployeeModel = true;
    },
    assigPostSurveyToEmployee() {
      this.$http
        .post("assignPostLoginSurvey", {
          survey_id: this.survey_id,
          employee_ids: this.employee_ids,
          for_admins: this.default_admin,
          for_managers: this.default_manager,
        })
        .then((resp) => {
          Swal.fire({
            title: "success!",
            html: "Survey Assigned Successfully.",
            icon: "success",
          }).then((result) => {
            if (result.value) {
              this.assignEmployeeModel = false;
              this.fetchData();
            }
          });
        });
    },
  },
};
</script>
