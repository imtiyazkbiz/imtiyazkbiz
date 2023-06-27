<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <card>
        <template slot="header">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h3 slot="header" class="mb-0" v-if="!this.$route.query.id">
                Add New Location
              </h3>
              <h3 slot="header" class="mb-0" v-else>
                Edit {{ location.location_name }}
              </h3>
            </div>
          </div>
        </template>
        <div>
          <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
            <form
              class="needs-validation"
              @submit.prevent="handleSubmit(addLocation)"
              enctype="multipart/form-data"
            >
              <div class="form-row">
                <div class="col-md-4">
                  <base-input
                    type="text"
                    label="Company Name *"
                    name="Company name"
                    rules="required"
                    placeholder="Enter Location Name"
                    v-model="location.location_name"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    type="number"
                    label="Estimated Number of Active Users *"
                    name="Number Of Acive Users"
                    min="0"
                    v-if="!editNew || editor === 'super-admin' || editor === 'sub-admin'"
                    rules="required"
                    placeholder="Enter Location Employee Count"
                    v-model="location.location_employee_count"
                  >
                  </base-input>
                  <base-input
                    type="number"
                    label="Estimated Number of Active Users *"
                    name="Number Of Acive Users"
                    readonly
                    v-else
                    placeholder="Enter Location Employee Count"
                    v-model="location.location_employee_count"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    type="text"
                    label="Phone Number"
                    name="phone number"
                    placeholder="(555) 555-5555"
                    v-model="location.location_telephone_no"
                    @input="acceptNumber"
                  >
                  </base-input>
                </div>
              </div>

              <div class="form-row">
                <div class="col-md-4">
                  <base-input
                    type="text"
                    label="Address *"
                    name="Address"
                    rules="required"
                    placeholder="Enter Address"
                    v-model="location.location_address_1"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <base-input
                    type="text"
                    label="City *"
                    name="City"
                    rules="required"
                    placeholder="Enter City"
                    v-model="location.location_city"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <base-input
                    type="text"
                    label="State *"
                    name="State"
                    rules="required"
                    placeholder="Enter State"
                    v-model="location.location_state"
                  >
                  </base-input>
                </div>
                <div class="col-md-2">
                  <base-input
                    type="number"
                    label="Zip Code *"
                    name="Zip code"
                    rules="required"
                    placeholder="Enter Zip Code"
                    v-model="location.location_zip_code"
                  >
                  </base-input>
                </div>
              </div>
              <div class="form-row">
                <div class="col-md-3" v-if="$route.query.id">
                  <label class="form-control-label">Admin(s):</label>
                  <h4
                    v-for="item in location.admin"
                    :key="item.id"
                    class="linkColor"
                    @click="handleEdit(item.employee_id)"
                  >
                    {{ item.first_name }}
                    {{ item.last_name }}
                  </h4>
                </div>
                <div
                  class="col-md-3"
                  v-if="!location.assign_employee && !$route.query.id"
                >
                  <label class="form-control-label">Admin</label>
                  <el-select
                    multiple
                    filterable
                    placeholder="Select"
                    class="select-primary"
                    v-model="location.location_admin"
                  >
                    <el-option
                      v-for="item in assign_admin"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>

                <div
                  class="col-md-1 pt-2"
                  v-if="!editNew && !location.assign_employee"
                >
                  <b>OR</b>
                </div>
                <div class="col-md-4 pt-2" v-if="!editNew">
                  <input
                    type="checkbox"
                    class="mb-4"
                    v-model="location.assign_employee"
                  />
                  Add New Admin
                </div>
                <div
                  class="col-md-3"
                  v-if="$route.query.id && location.manager.length > 0"
                >
                  <label class="form-control-label">Manager(s):</label>
                  <h4
                    v-for="item in location.manager"
                    :key="item.id"
                    class="linkColor"
                    @click="handleEdit(item.employee_id)"
                  >
                    {{ item.first_name }}
                    {{ item.last_name }}
                  </h4>
                </div>
                <div
                  class="col-md-3"
                  v-if="!location.assign_employee && !$route.query.id"
                >
                  <label class="form-control-label">Manager</label>
                  <el-select
                    multiple
                    filterable
                    placeholder="Select"
                    class="select-primary"
                    v-model="location.location_manager"
                  >
                    <el-option
                      v-for="item in assign_manager"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div class="form-row" v-if="!location.assign_employee"></div>
              <div class="form-row" v-if="location.assign_employee">
                <div class="col-md-4">
                  <base-input
                    label="First Name"
                    name="First Name"
                    placeholder="First Name"
                    rules="required"
                    v-model="location.first_name"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    label="Last Name"
                    name="Last Name"
                    placeholder="Last Name"
                    rules="required"
                    v-model="location.last_name"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    label="Phone"
                    name="Phone"
                    placeholder="(555) 555-5555"
                    v-model="location.telephone_no"
                    @input="acceptNumber"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    type="email"
                    label="Email Address *"
                    name="Email Address"
                    placeholder="Email Address"
                    rules="required"
                    v-model="location.email"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    type="text"
                    label="Username"
                    name="Username"
                    placeholder="Username"
                    rules="required"
                    readonly
                    v-model="location.email"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <base-input
                    label="Password *"
                    :type="passwordFieldType"
                    v-if="!(company_id !== '')"
                    name="Password"
                    placeholder="Password"
                    rules="required"
                    v-model="location.password"
                  >
                  </base-input>
                  <base-input
                    label="Password"
                    v-if="company_id !== ''"
                    name="Password"
                    placeholder="Password"
                    v-model="location.password"
                  >
                  </base-input>
                </div>
                <div class="col-md-1 password-eye" style="margin-top: 40px">
                  <span @click.prevent="switchVisibility"
                    ><i class="fa fa-eye"></i
                  ></span>
                </div>
              </div>

              <div class="form-row mt-3">
                <div class="col-md-2">
                  <label class="form-control-label">Status</label><br />
                  <div class="d-flex">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="location.status"
                    ></base-switch>
                  </div>
                </div>
                <div class="col-md-2">
                  <label class="form-control-label">
                    <el-popover
                      ref="fromPopOver"
                      placement="top-start"
                      width="250"
                      trigger="hover"
                    >
                      <span style="display: flex; justify-content: center">
                        The SMS Message service will send course reminders
                        through text message to all users who have a telephone
                        number stored in their accounts. By activating the SMS
                        Message, you agree to pay an additional $9 per month.
                      </span>
                    </el-popover>
                    <span
                      >SMS Messages
                      <i
                        v-popover:fromPopOver
                        class="el-icon-question text-blue"
                      />
                    </span>
                  </label>

                  <div class="d-flex">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="location.sms_message"
                    ></base-switch>
                  </div>
                </div>

                <div class="col-md-2" v-if="location.parent == 0">
                  <label class="form-control-label">Document status</label
                  ><br />
                  <div class="d-flex">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="location.document_status"
                    ></base-switch>
                  </div>
                </div>
                 <div class="col-md-2" v-if="location.parent == 0">
                  <label class="form-control-label">Post login survey status</label
                  ><br />
                  <div class="d-flex">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="location.post_login_survey_status"
                    ></base-switch>
                  </div>
                </div>
                 <div class="col-md-12 text-right mt-2">
                <base-button
                  type="danger"
                  class="custom-btn mr-3"
                  @click="$router.go(-1)"
                  >Cancel</base-button
                >
                <base-button class="custom-btn" native-type="submit">
                  {{ location_id !== "" ? "Update" : "Add" }}
                  Location</base-button
                >
              </div>
              </div>
              <hr>
              <div v-if="location.document_status && location.parent == 0">
                <div
                  class="row mt-4"
                  v-for="(content, c_index) in document"
                  :key="c_index"
                >
                  <div class="col-md-2">
                    <h4>Document {{ c_index + 1 }}</h4>
                  </div>
                  
              <div class="col-md-8"></div>
                <div class="col-md-2">
                  <span
                    class="remove-btn pull-right"
                    v-on:click="removeDocument(c_index, content)"
                  >
                    <i style="color: red" class="fa fa-remove"></i>
                  </span>
                  <span
                    class="remove-btn pull-right"
                    v-on:click="editDocumentData(content)"
                  >
                    <i style="color: green" class="fa fa-pencil"></i>
                  </span>
                </div>
                  <div class="col-md-12">
                    <vue-editor 
                    :disabled="disabledTrue"
                    v-model="content.text"></vue-editor>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9 mt-2"></div>
                  <div class="col-md-3 mt-2 text-right">
                    <base-button size="md" @click="addAnotherdocument()"
                      ><i class="fa fa-plus"></i> Add Document</base-button
                    >
                  </div>
                </div>
              </div>

            
            </form>
          </validation-observer>
            <div class="row mt-4" v-if="location.post_login_survey_status">
                  <div v-if="!addSurveyOption" class="col-lg-12 col-md-12 mb-2 text-right">
                    <base-button
                      name="Clear Filters"
                      class="custom-btn"
                      @click="addSurvey"
                      ><i class="fa fa-plus" aria-hidden="true"></i> Add Survey</base-button
                    >
                  </div>
                  <div class="col-md-12" v-else>
                    <add-post-login-survey :survey_id="survey_id" v-on:refreshSurveyGrid="refreshSurveyGrid" v-on:hideAddSurvey="refreshSurveyGrid" > </add-post-login-survey>
                  </div>
                  <div class="col-md-12" v-if="showSurveyGrid">
                    <post-login-survey :key="componentKey" v-on:editSurveyGrid="editSurveyGrid"> </post-login-survey>    
                  </div>               
            </div>
          <br />
        </div>
      </card>
    </div>
      <modal :show.sync="showDocumentPopup" class="user-modal">
      <h3 slot="header" class="title title-up text-primary">Document</h3>
      <form>
        <div class="row">
          <div class="col-md-2"><h5>Available for:</h5></div>
          <div class="col-md-2">
            <input
              type="checkbox"
              v-model="currentDoument.availableFor"
              value="2"
            />
            Admin
          </div>
          <div class="col-md-2">
            <input
              type="checkbox"
              v-model="currentDoument.availableFor"
              value="3"
            />
            Manager
          </div>
          <div class="col-md-2">
            <input
              type="checkbox"
              v-model="currentDoument.availableFor"
              value="4"
            />
            Employee
          </div>
          <div class="col-md-4"></div>
          <div class="col-md-12">
            <vue-editor :editorOptions="editorSettings" v-model="currentDoument.text"></vue-editor>
          </div>
          <div class="col-md-12 text-right mt-2">
            <base-button size="md" @click="saveDocumentData(currentDoument)"
              >Save</base-button
            >
          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import "vue-select/dist/vue-select.css";
import { VueEditor, Quill } from "vue2-editor";
import ImageResize from "quill-image-resize-vue";
import PostLoginSurvey from "./PostLoginSurvey.vue";
import AddPostLoginSurvey from "./AddPostLoginSurvey.vue";
Quill.register("modules/imageResize", ImageResize);
export default {
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    VueEditor,
     PostLoginSurvey,
    AddPostLoginSurvey
  },
  data() {
    return {
      editorSettings: {
        modules: {
          imageResize: {}
        }
      },
      currentDoument: "",
      showDocumentPopup: false,
      disabledTrue: true,
      hot_user: "",
      hot_token: "",
      config: "",
      editdata: false,
      location_employee: "",
      location_admin: [],
      location_manager: [],
      location_name: "",
      editNew: false,
      document: [
        {
          text: "",
        },
      ],
      location: {
        location_admin: [],
        assign_employee: "",
        status: true,
        sms_message: false,
        document_status: false,
        post_login_survey_status:false,
        location_type: "",
        send_weekly_progress_report: true,
        location_name: "",
        location_manager: "",
        admin_first_name: "",
        admin_last_name: "",
        location_employee_count: "",
        location_telephone_no: "",
        location_address_1: "",
        location_address_2: "",
        location_city: "",
        location_state: "",
        location_zip_code: "",
        first_name: "",
        last_name: "",
        telephone_no: "",
        phone_no: "",
        email: "",
        password: "",
        admin: [],
        manager: [],
      },
      employees: [],
      assign_admin: [],
      assign_manager: [],
      editor: "",
      location_id: "",
      company_id: "",
      returnedData: [],
      passwordFieldType: "password",
      addSurveyOption:false,
      showSurveyGrid:true,
      componentKey: 0,
      survey_id:0,
    };
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    if (this.hot_user == "company-admin") {
      this.company_id = localStorage.getItem("hot-user-id");
      this.editor = "company";
    }
    if (this.hot_user == "super-admin") {
      this.editor = "super-admin";
    }
    if (this.hot_user == "sub-admin") {
      this.editor = "sub-admin";
     }
    if (this.hot_user == "company-admin") {
      this.employeesList();
    }
    if (this.$route.query.id) {
      this.editNew = true;
      this.location_id = this.$route.query.id;
      this.$http.get("location/edit/" + this.location_id).then((resp) => {
        let data = resp.data[0];
        let admin_data = resp.data.admin;
        let manager_data = resp.data.manager;
        let obj = {
          id: data.id,
          location_type: data.type,
          parent: data.parent_id,
          company_id: data.parent_id,
          send_weekly_progress_report: false,
          location_name: data.name,
          location_admin: data.employee_id,
          location_manager: "",
          location_employee_count: data.employee_num,
          location_telephone_no: data.phone,
          location_address_1: data.address_1,
          location_state: data.state,
          location_city: data.city,
          location_zip_code: data.company_zip,
          status: "",
          admin: [],
          manager: [],
          sms_message: "",
          document_status: "",
          post_login_survey_status:""
        };
        for (let admindata of admin_data) {
          obj.admin.push(admindata);
        }
        for (let managerdata of manager_data) {
          obj.manager.push(managerdata);
        }
        if (data.sms_status === 1) {
          obj.sms_message = true;
        } else if (data.sms_status === 0) {
          obj.sms_message = false;
        } else {
          obj.sms_message = data.sms_status;
        }
        if (data.status === 1) {
          obj.status = true;
        } else if (data.status === 0) {
          obj.status = false;
        } else {
          obj.status = data.status;
        }
        if (data.document_status === 1) {
          obj.document_status = true;
        } else if (data.document_status === 0) {
          obj.document_status = false;
        } else {
          obj.document_status = data.document_status;
        }

        if (data.post_login_survey_status === 1) {
          obj.post_login_survey_status = true;
        } else if (data.post_login_survey_status === 0) {
          obj.post_login_survey_status = false;
        } else {
          obj.post_login_survey_status = data.post_login_survey_status;
        }
        

        if (data.manager !== null && data.manager !== undefined) {
          obj.location_manager = data.manager.id;
        }
        if (data.weekly_report === 1) {
          obj.send_weekly_progress_report = true;
        } else {
          obj.send_weekly_progress_report = false;
        }
        if (data.company_documents) {
          this.document = [];
          for (let documents of data.company_documents) {
            let document_obj = {
              id: documents.id,
              text: documents.document,
              availableFor: [],
            };
            if (documents.available_for) {
                document_obj.availableFor = documents.available_for.split(',');
            }
            this.document.push(document_obj);
          }
        }
        this.location = obj;

        if (this.editor == "super-admin" || this.editor == "sub-admin") {
          if (obj.company_id != 0) {
            this.company_id = obj.company_id;
          } else {
            this.company_id = obj.id;
          }

          this.employeesList();
        }
      });
    }
  },

  methods: {
    refreshSurveyGrid(){
    this.componentKey += 1; 
    this.addSurveyOption=false;
    this.showSurveyGrid=true;
    },
    editSurveyGrid(id){
    
      this.survey_id=id;
      this.addSurveyOption=true;
    },
    editDocumentData(content) {
      this.showDocumentPopup = true;
      this.currentDoument = content;
    },
    saveDocumentData(content) {
      this.$http
        .post("company/useronboarding_save", {
          company_id: this.$route.query.id,
          data: content,
        })
        .then((resp) => {
          Swal.fire({
            title: "Success!",
            text: "Document updated succesfully.",
            icon: "success",
          }).then((result)=>{
             if (result.value) {
                  this.showDocumentPopup=false;
             }
          });
        });
    },
    employeesList() {
      this.$http
        .post("employees/all_list", {
          role: "company",
          company_id: this.company_id,
        })
        .then((resp) => {
          let admin_data = resp.data.admin;
          let manager_data = resp.data.manager;
          for (let data of admin_data) {
            let obj = {
              value: data.employee_id,
              label: data.first_name + " " + data.last_name,
            };
            this.assign_admin.push(obj);
          }
          for (let data of manager_data) {
            let obj = {
              value: data.employee_id,
              label: data.first_name + " " + data.last_name,
            };
            this.assign_manager.push(obj);
          }
        });
    },
    handleEdit(id) {
      this.$router.push("/add_employee?id=" + id);
    },
    switchVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    },
    acceptNumber() {
      var x = this.location.telephone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.location.telephone_no = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
      var y = this.location.phone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.location.phone_no = !y[2]
        ? y[1]
        : "(" + y[1] + ") " + y[2] + (y[3] ? "-" + y[3] : "");
      var z = this.location.location_telephone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.location.location_telephone_no = !z[2]
        ? z[1]
        : "(" + z[1] + ") " + z[2] + (z[3] ? "-" + z[3] : "");
    },
    addAnotherdocument() {
      this.document.push({
        text: "",
        availableFor:[]
      });
    },
    removeDocument(index, content) {
      if (content.id) {
        Swal.fire({
          title: "Warning!",
          text: "Are you sure want to delete this document?",
          showCancelButton: true,
          confirmButtonClass: "btn btn-success btn-fill",
          cancelButtonClass: "btn btn-danger btn-fill",
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          icon: "warning",
          buttonsStyling: false,
        }).then((result) => {
          if (result.value) {
            this.document.splice(index, 1);
            this.$http
              .post("company/useronboarding_documentdelete", {
                company_id: this.$route.query.id,
                data: content,
              })
              .then((resp) => {
                Swal.fire({
                  title: "Success!",
                  text: "Document deleted succesfully.",
                  icon: "success",
                }).then((result) => {
                  if (result.value) {
                  }
                });
              });
          }
        });
      } else {
        this.document.splice(index, 1);
      }
    },
    addSurvey(){
      this.addSurveyOption=true;
    },
    addLocation() {
    

      let report = 0;
      if (this.location.send_weekly_progress_report) {
        report = 1;
      } else {
        report = 0;
      }

      if (
        this.location.location_name !== "" &&
        this.location.location_employee_count !== ""
      ) {
        let sms_m = 0;
        let status = 0;
        sms_m = this.location.sms_message ? 1 : 0;
        status = this.location.status ? 1 : 0;
        let data = {
          location_name: this.location.location_name,
          weekly_report: report,
          status: status,
          sms_message: sms_m,
          location_employee_count: this.location.location_employee_count,
          location_address_1: this.location.location_address_1,
          location_zip_code: this.location.location_zip_code,
          location_city: this.location.location_city,
          location_state: this.location.location_state,
          location_company_id: this.company_id,
          location_admin: this.location.location_admin,
          location_manager: this.location.location_manager,
          location_phone: this.location.location_telephone_no,
          user_phone: this.location.telephone_no,
          first_name: this.location.first_name,
          last_name: this.location.last_name,
          username: this.location.email,
          email: this.location.email,
          password: this.location.password,
          document_status: this.location.document_status,
          post_login_survey_status: this.location.post_login_survey_status,
          document_text: this.document,
        };
        if (this.location_id !== "") {
          this.$http
            .put("location/update/" + this.location_id, data)
            .then((resp) => {
              Swal.fire({
                title: "Success!",
                text: `Location Updated Successfully`,
                icon: "success",
              });
              if (this.editor == "company") {
                this.$router.push("/company_locations");
              }
              if (this.editor == "super-admin" || this.editor == "sub-admin") {
                this.$router.push("/dashboard");
              }
            });
        } else {
          if (!this.location.assign_employee) {
            if (this.location.location_admin.length === 0) {
              return Swal.fire({
                title: "Error!",
                text: `Please select the admin for location.`,
                icon: "error",
              });
            }
          }
          this.$http.post("location/register", data).then((resp) => {
            Swal.fire({
              title: "Success!",
              text: `Location Added Successfully`,
              icon: "success",
            });
            this.$router.push("/company_locations");
          });
        }
      }
    },
  },
};
</script>
<style scoped>
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
.password-eye span {
  border: 1px solid #808080b3;
  padding: 8px;
  border-radius: 5px;
  background: #80808029;
}

.remove-btn {
  border: 2px solid #dee2e6;
  padding: 2px 6px 2px 6px;
  border-bottom: 0px;
  cursor: pointer;
}
.ql-disabled {
  opacity: 0.7;
}
</style>
