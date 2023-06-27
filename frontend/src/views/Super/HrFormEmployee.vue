<template>
  <div>
    <div>
      <div class="row">
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
              <div slot-scope="{ row }" class="d-flex custom-size">
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
                <base-button v-else 
                data-toggle="tooltip"
                   data-original-title="Download"
                disabled type="" size="sm">
                  <i class="text-warning fa fa-download"></i>
                </base-button>
                 </el-tooltip>
                 <el-tooltip content="Upload" placement="top">
                <base-button
                  type=""
                  size="sm"
                  @click.native="handleUpload(row)"
                  data-toggle="tooltip"
                  data-original-title="Upload"
                >
                  <i class="text-warning fa fa-upload"></i>
                </base-button>
                 </el-tooltip>
              </div>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </div>
    <modal :show.sync="uploadPopup" v-on:close="cleanAssignPopUpData">
      <h3 class="mb-0" slot="header">Upload HR Form</h3>
      <div class="row">
        <div class="col-md-12">
          <h4>File Name:</h4>
          <p>
            <b>{{ hrform.file_name }}</b>
          </p>
        </div>
        <div class="col-md-12">
          <label class="form-control-label">Upload File</label>
          <input
            class="form-control"
            type="file"
            v-on:change="getAllFiles($event)"
          />
        </div>

        <div class="mt-4 col-md-12 text-right">
          <base-button @click="uploadHrForm()"> Submit</base-button>
          <base-button type="danger" @click="cleanUploadPopUpData()"
            >Cancel</base-button
          >
        </div>
      </div>
    </modal>
  </div>
</template>
<script>
import axios from 'axios';
import { Table, TableColumn, Select, Option } from "element-ui";
import BaseButton from "../../components/BaseButton.vue";
import BaseInput from "../../components/Inputs/BaseInput.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import BaseSwitch from "../../components/BaseSwitch.vue";

export default {
  name: "hr-form-employee",
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
      },
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    getAllFiles: function (e) {
      let file = e.target.files || e.dataTransfer.files;
      this.hrform.file = file[0];
    },
    fetchData() {
      this.$http.get("resources/all_employee_hr_forms").then((resp) => {
        this.hrform_data = [];
        for (let data of resp.data) {
          let obj = {
            id: data.id,
            file_name: data.name,
            description: data.description,
            file: data.file,
          };

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
                type: 'My Company Forms'
            }).then(response => {
                let extension = row.file.split('.').pop();
                if (extension == 'pdf') {
                    let filenameNew = row.file.split(".pdf");
                    axios.get(this.baseUrl + "/resources/download/" + filenameNew[0], {responseType: 'arraybuffer'}).then(res => {
                        let blob = new Blob([res.data], {type: 'application/pdf'})
                        let link = document.createElement('a')
                        link.href = window.URL.createObjectURL(blob)
                        link.download = row.file
                        link.click();
                    })
                } else {
                    window.open(this.baseUrl + "/hr-forms/" + row.file, "_blank").focus();
                }
            });

        },
    handleUpload(row) {
      this.hrform.file_name = row.file_name;
      this.hrform.id = row.id;
      this.uploadPopup = true;
    },
    cleanAssignPopUpData() {
      this.uploadPopup = false;
    },
    uploadHrForm() {
      var formData = new FormData();
      formData.append("file", this.hrform.file);
      formData.append("hrform_id", this.hrform.id);

      this.$http
        .post("resources/upload_filled_hrform", formData)
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
              this.hrform.file = {};
              this.uploadPopup = false;
              this.fetchData();
            }
          });
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
