<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div></div
    ></base-header>
    <div class="container-fluid mt--6">
      <card>
        <template slot="header">
          <div class="row align-items-center">
            <div class="col-md-9">
              <h2 class="mb-0">Add New Company</h2>
            </div>
            <div class="col-md-3">
              <h5>
                <span class="requireField">*</span> Indicates a required field.
              </h5>
            </div>
          </div>
        </template>
        <div v-if="!payment_screen">
          <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
            <form
              class=""
              @submit.prevent="handleSubmit(createAccount)"
              enctype="multipart/form-data"
            >
              <div class="row">
                <div class="col-md-12">
                  <h4 style="color:rgb(0 204 255)" class="">Company Details</h4>
                  <hr />
                </div>
              </div>

              <div class="form-row">
                <div class="col-md-2 col-6">
                  <label class="form-control-label">Company Type</label><br />
                  <el-select
                    class=" mr-3"
                    style="width: 100%"
                    placeholder="Select Type"
                    :required="true"
                    v-model="company.company_type"
                  >
                    <el-option
                      v-for="(option, index) in company_types"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'company_type_' + index"
                    >
                    </el-option>
                  </el-select>
                </div>
                <div class="col-md-2 col-6">
                  <label class="form-control-label">Parent Company</label><br />
                  <el-select
                    class=" mr-3"
                    style="width: 100%"
                    placeholder="Select Company"
                    v-model="company.parent_company"
                    filterable
                  >
                    <el-option
                      v-for="(option, index) in parent_companies"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'company_type_' + index"
                    >
                    </el-option>
                  </el-select>
                </div>
                <div class="col-md-3">
                  <label class="form-control-label"
                    >Company Name<span class="requireField"> *</span></label
                  >
                  <base-input
                    type="text"
                    name="company name"
                    placeholder="Company Name"
                    rules="required"
                    v-model="company.name"
                  >
                  </base-input>
                </div>
                <div class="col-md-2">
                  <label class="form-control-label"
                    >Number of Location
                    <span class="requireField">*</span></label
                  >
                  <base-input
                    type="number"
                    name="No. of location"
                    min="0"
                    placeholder="Location Count"
                    rules="required"
                    v-model="company.no_of_locations"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <label class="form-control-label"
                    >Number of Employees
                    <span class="requireField">*</span></label
                  >
                  <base-input
                    type="number"
                    name="No. of employees"
                    min="0"
                    placeholder="Employee Count"
                    rules="required"
                    v-model="company.no_of_employees"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <label class="form-control-label"
                    >Address <span class="requireField">*</span></label
                  >
                  <base-input
                    type="text"
                    name="address"
                    placeholder="Address"
                    rules="required"
                    v-model="company.address_1"
                  >
                  </base-input>
                </div>
                <div class="col-md-3" hidden>
                  <base-input
                    type="text"
                    label="Address 2"
                    placeholder="Address 2"
                    v-model="company.address_2"
                  >
                  </base-input>
                </div>

                <div class="col-md-2">
                  <label class="form-control-label"
                    >City <span class="requireField">*</span></label
                  >
                  <base-input
                    type="text"
                    name="city"
                    placeholder="city"
                    rules="required"
                    v-model="company.city"
                  >
                  </base-input>
                </div>
                <div class="col-md-2 col-6">
                  <label class="form-control-label"
                    >State <span class="requireField">*</span></label
                  >
                  <base-input
                    type="text"
                    name="state"
                    placeholder="State"
                    rules="required"
                    v-model="company.state"
                  >
                  </base-input>
                </div>
                <div class="col-md-2 col-6">
                  <label class="form-control-label"
                    >Zip <span class="requireField">*</span></label
                  >
                  <base-input
                    type="number"
                    name="zip"
                    placeholder="Zip"
                    rules="required"
                    v-model="company.zip"
                  >
                  </base-input>
                </div>
                <div class="col-md-3">
                  <base-input
                    type="text"
                    label="Website"
                    name="website"
                    placeholder="Website"
                    v-model="company.website"
                  >
                  </base-input>
                </div>
                <!-- <div class="col-md-4 col-6">
                  <label class="form-control-label">Package Plan</label><br />
                  <el-select
                    class=" mr-3"
                    style="width: 100%"
                    placeholder="Select Plan"
                    rules="required"
                    v-model="company.price_plan"
                  >
                    <el-option
                      v-for="(option, index) in price_plans"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="'price_plan_' + index"
                    >
                    </el-option>
                  </el-select>
                </div> -->
                <div class="col-md-4 col-6">
                  <base-input
                    label="Phone"
                    name="Phone"
                    placeholder="(555) 555-5555"
                    v-model="company.phone_no"
                    @input="acceptNumber"
                  >
                  </base-input>
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-control-label">Logo</label>
                  <form>
                    <file-input v-on:change="onImageChange"></file-input>
                  </form>
                </div>
                <div class="col-md-2" v-if="company.logo != ''">
                  <img
                    class="logo-size"
                    :src="`${baseUrl}/images/${company.logo}`"
                    style="cursor:pointer;"
                  />
                </div>

                <div class="col-md-2 col-6">
                  <label class="form-control-label">Status</label><br />
                  <div class="d-flex pt-2 pb-2">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="company.status"
                    ></base-switch>
                  </div>
                </div>
                <div class="col-md-2 col-6">
                  <label class="form-control-label">
                    <el-popover
                      ref="fromPopOver"
                      placement="top-start"
                      width="250"
                      trigger="hover"
                    >
                      <span style="display: flex; justify-content: center;">
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
                        class="el-icon-question
                    text-blue"
                      />
                    </span>
                  </label>
                  <div class="d-flex  pt-2 pb-2">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="company.sms_message"
                    ></base-switch>
                  </div>
                </div>
                <div class="col-md-3" v-if="!company.parent_company">
                  <label class="form-control-label">Pay By Employee</label
                  ><br />
                  <div class="d-flex pt-lg-2 pb-2">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="company.pay_by_employee_status"
                    ></base-switch>
                  </div>
                </div>
                <div
                  class="col-md-2"
                  v-if="
                    company.pay_by_employee_status && !company.parent_company
                  "
                >
                  <base-input
                    type="text"
                    label="Discount (in %)"
                    name="Discount"
                    placeholder="Discount"
                    v-model="company.pay_by_employee_discount"
                  >
                  </base-input>
                </div>
                <div class="col-md-2" v-if="!company.parent_company">
                  <label class="form-control-label" >Document status</label
                  ><br />
                  <div class="d-flex">
                    <base-switch
                      class="mr-1"
                      type="success"
                      v-model="company.document_status"
                    ></base-switch>
                  </div>
                </div>
                 <div class="col-md-6">
                <base-input label="Notes:">
                   <el-input type="textarea" rows="5" v-model="company.notes"></el-input>
                </base-input>
              </div>
              </div>
              <div v-if="company.document_status && !company.parent_company">
                <div
                  class="row mt-4"
                  v-for="(content, c_index) in document"
                  :key="c_index"
                >
                  <div class="col-md-11">
                    <h4>Document {{ c_index + 1 }}</h4>
                  </div>
                  <div class="col-md-1">
                    <span
                      class="remove-btn pull-right"
                      v-on:click="removeDocument(c_index)"
                    >
                      <i style="color: red" class="fa fa-remove"></i>
                    </span>
                  </div>
                  <div class="col-md-12">
                    <vue-editor :editorOptions="editorSettings" v-model="content.text"></vue-editor>
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
              <div class="row" style="margin-top:25px;">
                <div class="col-sm-12">
                  <h3 style="color: rgb(0 204 255)">
                    Administrator Login Information
                  </h3>
                  <hr />
                </div>
              </div>

              <div class="form-row">
                <div class="col-md-4">
                  <label class="form-control-label"
                    >First Name <span class="requireField">*</span></label
                  >
                  <base-input
                    name="First Name"
                    rules="required"
                    placeholder="First Name"
                    v-model="company.first_name"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <label class="form-control-label"
                    >Last Name <span class="requireField">*</span></label
                  >
                  <base-input
                    name="Last Name"
                    rules="required"
                    placeholder="Last Name"
                    v-model="company.last_name"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    label="Phone"
                    name="Phone"
                    placeholder="(555) 555-5555"
                    v-model="company.telephone_no"
                    @input="acceptNumber"
                  >
                  </base-input>
                </div>
                <div class="col-md-4">
                  <label class="form-control-label"
                    >Email Address <span class="requireField">*</span></label
                  >
                  <base-input
                    type="email"
                    name="Email Address"
                    placeholder="Email Address"
                    rules="required"
                    v-model="company.email"
                  >
                  </base-input>
                </div>

                <div class="col-md-4">
                  <base-input
                    type="text"
                    label="Username"
                    name="Username"
                    placeholder="Username"
                    readonly
                    v-model="company.email"
                  >
                  </base-input>
                </div>

                <div class="col-md-3">
                  <base-input
                    label="Password"
                    :type="passwordFieldType"
                    v-if="!(company_id !== '')"
                    name="Password"
                    placeholder="Password"
                    rules="required"
                    v-model="company.password"
                  >
                  </base-input>
                  <base-input
                    label="Password"
                    v-if="company_id !== ''"
                    name="Password"
                    placeholder="Password"
                    v-model="company.password"
                  >
                  </base-input>
                </div>
                <div class="col-md-1 password-eye" style="margin-top: 40px;">
                  <span @click.prevent="switchVisibility"
                    ><i class="fa fa-eye"></i
                  ></span>
                </div>
              </div>

              <div class="text-right mt-2">
                <base-button
                  type="danger"
                  class="custom-btn mr-3"
                  @click="$router.go(-1)"
                  >Cancel</base-button
                >
                <base-button class="custom-btn" native-type="submit">{{
                  "Add Account"
                }}</base-button>
              </div>
            </form>
          </validation-observer>
        </div>
      </card>
    </div>
  </div>
</template>

<script>
import FileInput from "@/components/Inputs/FileInput";
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import { TheMask } from "vue-the-mask";
import { VueEditor, Quill } from "vue2-editor";
import ImageResize from "quill-image-resize-vue";
Quill.register("modules/imageResize", ImageResize);
export default {
  components: {
    //TheMask,
    FileInput,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    VueEditor
  },
  data() {
    return {
       editorSettings: {
        modules: {
          imageResize: {}
        }
      },
      loading: false,
      baseUrl: this.$baseUrl,
      complete: false,
      hot_user: "",
      hot_token: "",
      config: "",
      company_id: "",
      creatAccountClicked: false,
      value: "",
      company_types: [],
      price_plans: [],
      parent_companies: [],
      document: [
        {
          text: "",
        },
      ],
      payment_screen: false,
      image: "",
      company: {
        notes:"",
        document_status: false,
        status: 1,
        sms_message: 0,
        name: "",
        first_name: "",
        last_name: "",
        username: "",
        company_type: "",
        parent_company: "",
        price_plan: "",
        administrator: "",
        no_of_locations: "",
        no_of_employees: "",
        address_1: "",
        address_2: "",
        city: "",
        state: "",
        zip: "",
        logo: "",
        telephone_no: "",
        phone_no:"",
        email: "",
        password: "",
        confirm_email: "",
        confirm_password: "",
        website: "",
        pay_by_employee_status: 0,
        pay_by_employee_discount: ""
      },
      passwordFieldType: "password"
    };
  },
  created() {
    this.$http.post("company/company_dropdown_data", {}).then(resp => {
      for (let type of resp.data.companytype) {
        let obj = {
          label: type.type,
          value: type.id
        };
        this.company_types.push(obj);
      }
      for (let parent of resp.data.parentcompanies) {
        let obj = {
          label: parent.name,
          value: parent.id
        };
        this.parent_companies.push(obj);
      }
      for (let price of resp.data.priceplan) {
        let obj = {
          label: price.title,
          value: price.id
        };
        this.price_plans.push(obj);
      }
    });
  },
  methods: {
     addAnotherdocument() {
      this.document.push({
        text: "",
      });
    },
    removeDocument(index) {
      this.document.splice(index, 1);
    },
    switchVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    },
    acceptNumber() {
      var x = this.company.telephone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.company.telephone_no = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");

         var y = this.company.phone_no
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.company.phone_no = !y[2]
        ? y[1]
        : "(" + y[1] + ") " + y[2] + (y[3] ? "-" + y[3] : "");
    },
    onImageChange(e) {
      let files = e;
      if (!files.length) return;
      this.createImage(files[0]);
    },
    createImage(file) {
      let reader = new FileReader();
      let vm = this;
      reader.onload = e => {
        vm.image = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    createAccount() {
      this.loading = true;
      this.creatAccountClicked = true;
      let data = {
        company_name: this.company.name,
        company_admin: this.company.administrator,
        company_location_num: this.company.no_of_locations,
        company_employee_num: this.company.no_of_employees,
        company_address_1: this.company.address_1,
        company_address_2: this.company.address_2,
        company_phone: this.company.telephone_no,
        company_contact: this.company.phone_no,
        first_name: this.company.first_name,
        last_name: this.company.last_name,
        username: this.company.email,
        company_email: this.company.email,
        company_zip: this.company.zip,
        website: this.company.website,
        company_type: this.company.company_type,
        parent_id: this.company.parent_company,
        price_plan: this.company.price_plan,
        status: this.company.status,
        sms_status: this.company.sms_message,
        image: this.image,
        company_city: this.company.city,
        company_state: this.company.state,
        company_password: this.company.password,
        company_pay_by_employee_status: this.company.pay_by_employee_status,
        company_pay_by_employee_discount: this.company.pay_by_employee_discount,
         document_status: this.company.document_status,
          document_text: this.document,
           notes:this.company.notes
      };

      this.$http
        .post("company/register", data)
        .then(resp => {
          let ids = [];
          let obj = {
            id: resp.data.id
          };
          ids.push(obj);
          this.$http
            .post("company/welcome_email", {
              password: this.company.password,
              ids: ids
            })
            .then(resp => {
              this.$router.push("/dashboard");
              Swal.fire({
                title: "Success!",
                text: `Account has been Created!`,
                icon: "success"
              });
            })
            .catch(function(error) {
              this.$router.push("/dashboard");
              Swal.fire({
                title: "Success!",
                text: `Account has been Created!`,
                icon: "success"
              });
            });
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error"
            });
          }
        })
        .finally(() => (this.loading = false));
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
.password-eye span {
  border: 1px solid #808080b3;
  padding: 8px;
  border-radius: 5px;
  background: #80808029;
}
hr {
  margin-top: 1rem;
  margin-bottom: 1rem;
}
.remove-btn {
  border: 2px solid #dee2e6;
  padding: 2px 6px 2px 6px;
  border-bottom: 0px;
  cursor: pointer;
}
</style>
