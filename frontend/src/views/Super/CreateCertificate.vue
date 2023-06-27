<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <card>
        <!-- Card header -->
        <h2 slot="header" class="mb-0" v-if="certificate_id">
          Edit New Certificate
        </h2>
        <h2 slot="header" class="mb-0" v-else>
          Add New Certificate
        </h2>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit()"
            enctype="multipart/form-data"
          >
            <div class="row">
              <div class="col-md-12">
                <h5 class="step_title_certi">
                  Step 1: Enter Global Information
                </h5>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-6 ">
                <base-input
                  label="Certificate Name"
                  name="Certificate Name"
                  placeholder="Certificate Name"
                  rules="required"
                  v-model="certificate.certificate_name"
                >
                </base-input>
              </div>
              <div class="col-md-6 form-group">
                <label class="form-control-label">Template</label>
                <el-select
                  v-model="template"
                  class="select-primary select-template"
                  label="Template"
                  rules="required"
                >
                  <el-option
                    v-for="(option, index) in templates"
                    class="select-primary"
                    :value="option.value"
                    :label="option.label"
                    :key="'template_' + index"
                  >
                  </el-option>
                </el-select>
              </div>
              <!-- <div class="col-md-4">
           <label>Valid for how long?</label><br>
                <el-select 
                rules="required"
                            placeholder="Valid for how long?"
                            v-model="certificate.certificate_valid_time">
                    <el-option v-for="(option,index) in selects.valid"
                                class="select-primary"
                                :value="option.value"
                                :label="option.label"
                                :key="option.value+'_'+index">
                    </el-option>
                </el-select>
               </div> -->
              <!-- <div class="col-md-4">
            <base-input
              label="Signature Title 1"
              name="Signature Title 1"
              placeholder="Signature Title 1"
              rules="required"
              v-model="certificate.certificate_signature_title_1"
            >
            </base-input>
          </div>
          <div class="col-md-4">
            <base-input
              label="Signature Title 2"
              name="Signature Title 2"
              placeholder="Signature Title 2"
              v-model="certificate.certificate_signature_title_2"
            >
            </base-input>
          </div> -->
            </div>
            <br />
            <div class="row">
              <div class="col-md-12">
                <h5 class="step_title_certi">Step 2: Signature</h5>
              </div>
              <div class="col-md-6">
                <base-input
                  label="Signature Title"
                  name="Signature Title"
                  placeholder="Write Signature Title.."
                  v-model="certificate.certificate_signature_title_1"
                >
                </base-input>
              </div>
              <div class="col-md-6">
                <label class="form-control-label">Signature Description</label>
                <textarea
                  class="form-control"
                  label="Signature Description"
                  name="Signature Description"
                  placeholder="Write Signature Description.."
                  v-model="certificate.certificate_signature_title_2"
                >
                </textarea>
              </div>
            </div>
            <br />
            <div class="row">
              <div class="col-md-12">
                <h5 class="step_title_certi">Step 3: Customization</h5>
              </div>
              <div class="col-md-12">
                <label class="form-control-label">Footer Text</label>
                <textarea
                  class="form-control"
                  label="Footer Text"
                  name="Footer Text"
                  placeholder="Write Signature Footer Text.."
                  v-model="certificate.certificate_custom_text"
                >
                </textarea>
              </div>
            </div>
            <div class="text-right mt-3">
              <base-button
                class="custom-btn"
                type="danger"
                @click="$router.go(-1)"
                >Cancel</base-button
              >
              <base-button
                class="custom-btn"
                @click.prevent="saveCertificates()"
                >{{ certificate_id !== "" ? " Update" : "Submit" }}</base-button
              >
            </div>
          </form>
        </validation-observer>
      </card>
    </div>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      hot_user: "",
      hot_token: "",
      config: "",
      datePicker: "",
      certificate_id: "",
      certificate: {
        certificate_name: "",
        course_id: "",
        certificate_date: "",
        certificate_valid_time: "",
        certificate_custom_text: "",
        certificate_signature_title_1: "",
        certificate_signature_title_2: ""
      },
      courses_data: [],
      templates: [],
      template: "",
      selects: {
        simple: "",
        valid: [
          {
            label: "1 Year",
            value: 1
          },
          { label: "2 Year", value: 2 },
          { label: "3 Year", value: 3 },
          { label: "4 Year", value: 4 },
          { label: "5 Year", value: 5 }
        ]
      }
    };
  },
  created() {
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
    this.$http.get("course/templates").then(resp => {
      for (let template of resp.data) {
        let obj = {
          label: template.name,
          value: template.id
        };
        this.templates.push(obj);
      }
    });

    if (this.$route.query.id) {
      this.certificate_id = this.$route.query.id;
      this.$http
        .get("course/certificate/" + this.certificate_id, this.config)
        .then(resp => {
          let data = resp.data;
          this.certificate.certificate_name = data.name;
          this.certificate.course_id = data.course_id;
          this.template = data.template_id;
          this.certificate.certificate_custom_text = data.custom_text;
          this.certificate.certificate_signature_title_1 =
            data.signature_title_1;
          this.certificate.certificate_signature_title_2 =
            data.signature_title_2;
        });
    }
  },
  methods: {
    saveCertificates() {
      if (this.certificate_id !== "") {
        this.$http
          .put(
            "course/certificate/" + this.certificate_id,
            {
              certificate_id: this.certificate_id,
              course_certificate_name: this.certificate.certificate_name,
              course_certificate_template: this.template,
              course_certificate_custom_text: this.certificate
                .certificate_custom_text,
              signature_title_1: this.certificate.certificate_signature_title_1,
              signature_title_2: this.certificate.certificate_signature_title_2
            },
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success"
            });
            this.$router.push("/certificates");
          })
          .catch(function(error) {
            if (error.response.status === 422) {
              return Swal.fire({
                title: "Error!",
                text: error.response.data.message,
                icon: "error"
              });
            }
          });
      } else {
        this.$http
          .post(
            "course/certificate",
            {
              course_certificate_name: this.certificate.certificate_name,
              course_certificate_template: this.template,
              course_certificate_custom_text: this.certificate
                .certificate_custom_text,
              signature_title_1: this.certificate.certificate_signature_title_1,
              signature_title_2: this.certificate.certificate_signature_title_2
            },
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: `Certificate Has been Added!`,
              icon: "success"
            });
            this.$router.push("/certificates");
          })
          .catch(function(error) {
            if (error.response.status === 422) {
              return Swal.fire({
                title: "Error!",
                text: error.response.data.message,
                icon: "error"
              });
            }
          });
      }
    }
  }
};
</script>
<style>
.stripe-card {
  border: 1px solid grey;
}
.stripe-card.complete {
  border-color: green;
}
.logo-size {
  width: 60%;
  height: auto;
}
.step_title_certi {
  color: rgb(23 43 77);
  font-weight: 500;
  font-size: 14px;
  border-bottom: 1px solid #f3f3f3;
  line-height: 35px;
}
</style>
