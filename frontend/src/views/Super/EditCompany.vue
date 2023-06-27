<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <card v-loading.fullscreen.lock="loading">
        <template slot="header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <h2 class="mb-0">Edit Company</h2>
            </div>
            <div class="col-md-4">
              <h5>
                <span class="requireField">*</span> Indicates a required field.
              </h5>
            </div>
            <div class="col-md-4">
              <base-button class="custom-btn float-right"
                >Go To Company</base-button
              >
            </div>
          </div>
        </template>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit(updateAccoount)"
            enctype="multipart/form-data"
          >
            <div class="row">
              <div class="col-md-12">
                <h4 style="color: rgb(0 204 255)" class="">Company Details</h4>
                <hr />
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-2">
                <label class="form-control-label">Company Type</label><br />
                <el-select
                  class="mr-3"
                  style="width: 100%"
                  placeholder="Select Type"
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
              <div class="col-md-2">
                <label class="form-control-label">Parent Company</label><br />

                <el-select
                  class="mr-3"
                  style="width: 100%"
                  placeholder="Select Company"
                  v-model="company.parent_company"
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
                  name="Company Name"
                  placeholder="Company Name"
                  rules="required"
                  v-model="company.name"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <label class="form-control-label"
                  >Number of Location <span class="requireField">*</span></label
                >
                <base-input
                  name="Number of Location"
                  min="0"
                  placeholder="Number of Location"
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
                  name="Number of Employees"
                  placeholder="Number of Employees"
                  min="0"
                  rules="required"
                  v-model="company.no_of_employees"
                >
                </base-input>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-3">
                <label class="form-control-label"
                  >Address <span class="requireField">*</span></label
                >
                <base-input
                  name="Address"
                  placeholder="Address 1"
                  rules="required"
                  v-model="company.address_1"
                >
                </base-input>
              </div>
              <div class="col-md-3" hidden>
                <base-input
                  label="Address 2"
                  name="Address 2"
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
                  name="City"
                  placeholder="City"
                  rules="required"
                  v-model="company.city"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <label class="form-control-label"
                  >State <span class="requireField">*</span></label
                >
                <base-input
                  name="State"
                  placeholder="State"
                  rules="required"
                  v-model="company.state"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <label class="form-control-label"
                  >Zip <span class="requireField">*</span></label
                >
                <base-input
                  name="Zip"
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
              <!-- <div class="col-md-3">
                <label class="form-control-label">Package Plan</label><br />
                <el-select
                  class="mr-3"
                  style="width: 100%"
                  placeholder="Select plan"
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
              <div class="col-md-3">
                <base-input
                  label="Phone"
                  name="Phone"
                  placeholder="(555) 555-5555"
                  v-model="company.phone_no"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <label class="form-control-label">Logo</label><br />
                <form>
                  <file-input v-on:change="onImageChange"></file-input>
                </form>
              </div>
             
              <div class="col-md-2" v-if="company.logo">
                <img
                  class="logo-size"
                  :src="`${baseUrl}/images/${company.logo}`"
                  style="cursor: pointer"
                />
              </div>
            </div>
            <div class="row">
             
              <div class="col-md-3">
                <base-input type="number" 
                v-model="company.fm_certificate_count" 
                label="Food Manager Certificate Count"></base-input>
              </div>
              <div class="col-md-3">
              <base-input label="Year">
               <el-select
                class="select-primary"
                placeholder="Select Year"
                v-model="company.year"
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
              <div class="col-md-2">
                <label class="form-control-label">Status</label><br />
                <div class="d-flex pt-lg-2 pb-2">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="company.status"
                  ></base-switch>
                </div>
              </div>
              <div class="col-md-2">
                <label class="form-control-label">SMS Messages</label><br />
                <div class="d-flex pt-lg-2 pb-2">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="company.sms_message"
                  ></base-switch>
                </div>
              </div>
              <div class="col-md-3" v-if="!company.parent_company">
                <label class="form-control-label">Pay By Employee</label><br />
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
                v-if="company.pay_by_employee_status && !company.parent_company"
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
                <label class="form-control-label">Document status</label><br />
                <div class="d-flex">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="company.document_status"
                  ></base-switch>
                </div>
              </div>
            </div>
            <div v-if="company.document_status && !company.parent_company">
           
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
                    title="test"
                    :disabled="disabledTrue"
                    v-model="content.text"
                  ></vue-editor>
                </div>
                <div class="col-md-10"></div>
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

            <h3 style="color: rgb(0 204 255)" class="mt-3">
              Administrator Information
            </h3>
            <hr />
            <div class="form-row">
              <div
                class="col-md-3"
                v-if="$route.query.id && company.admins.length > 0"
              >
                <label class="form-control-label">Admin(s):</label>
                <h4
                  v-for="item in company.admins"
                  :key="item.id"
                  class="linkColor"
                  @click="handleEdit(item.id)"
                >
                  {{ item.first_name }}
                  {{ item.last_name }}
                </h4>
              </div>
              <div
                class="col-md-3"
                v-if="$route.query.id && company.managers.length > 0"
              >
                <label class="form-control-label">Manager(s):</label>
                <h4
                  v-for="item in company.managers"
                  :key="item.id"
                  class="linkColor"
                  @click="handleEdit(item.id)"
                >
                  {{ item.first_name }}
                  {{ item.last_name }}
                </h4>
              </div>
              <div class="col-md-6">
                <base-input label="Notes:">
                   <el-input type="textarea" rows="5" v-model="company.notes"></el-input>
                </base-input>
              </div>
            </div>

            <div class="row" v-if="!company.parent_company">
              <div class="col-md-12  mt-2">
                <input type="checkbox" v-model="company.secondary_course_status" />
                Use Secondary Course Name
              </div>
              <div class="col-md-12">
                <h3 style="color: rgb(0 204 255)" class="mt-3">
                  Assign Courses
                </h3>
                <hr />
              </div>

              <div class="col-md-6">
                <el-select
                  class="select-primary w-100"
                  multiple
                  filterable
                  v-model="company.course_ids"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in courses"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  >
                  </el-option>
                </el-select>
              </div>
              <div class="col-md-12">
                <h3 style="color: rgb(0 204 255)" class="mt-3">
                  Assign Course Folders
                </h3>
                <hr />
              </div>

              <div class="col-md-6">
                <el-select
                  class="select-primary w-100"
                  multiple
                  filterable
                  v-model="company.folder_ids"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in coursefolders"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  >
                  </el-option>
                </el-select>
              </div>
            </div>
            <div class="text-right">
              <base-button class="custom-btn mt-4" native-type="submit"
                >Submit</base-button
              >
            </div>
          </form>
        </validation-observer>
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
import FileInput from "@/components/Inputs/FileInput";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Table, TableColumn, Select, Option } from "element-ui";
import "sweetalert2/src/sweetalert2.scss";
import { VueEditor, Quill } from "vue2-editor";
import ImageResize from "quill-image-resize-vue";
Quill.register("modules/imageResize", ImageResize);
export default {
  components: {
    FileInput,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    VueEditor,
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
      loading: false,
      baseUrl: this.$baseUrl,
      employee_id: "",
      company_types: [],
      price_plans: [],
      parent_companies: [],
      document: [
        {
          id:"",
          text: "",
          availableFor:[]
        },
      ],
      company: {
        notes:'',
        document_status: false,
        status: "",
        sms_message: "",
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
        phone_no:"",
        address_1: "",
        address_2: "",
        city: "",
        state: "",
        logo: "",
        zip: "",
        email: "",
        website: "",
        fm_certificate_count:0,
        year:"",
        password: "",
        course_ids: [],
        pay_by_employee_status: "",
        pay_by_employee_discount: "",
        secondary_course_status:false,
        folder_ids: []
      },
      image: "",
      files: [],
      courses: [],
      coursefolders: []

    };
  },
  computed: {
    // get past 10 years with current year
    years() {
      return [...Array(11)].map((a, b) => new Date().getFullYear() - b);
    },
  },
  created() {
    this.$http.post("company/company_dropdown_data", {}).then((resp) => {
      let obj = {
        label: "Select",
        value: 0,
      };
      this.company_types.push(obj);
      for (let type of resp.data.companytype) {
        let obj = {
          label: type.type,
          value: type.id,
        };
        this.company_types.push(obj);
      }
      let parent_obj = {
        label: "Select",
        value: 0,
      };
      this.parent_companies.push(parent_obj);
      for (let parent of resp.data.parentcompanies) {
        let obj = {
          label: parent.name,
          value: parent.id,
        };
        this.parent_companies.push(obj);
      }
      for (let price of resp.data.priceplan) {
        let obj = {
          label: price.title,
          value: price.id,
        };
        this.price_plans.push(obj);
      }
    });
    this.$http
      .post("course/all_courses", {
        course_status: "Active",
        search: "",
        without_folder_course: "yes"
      })
      .then((resp) => {
        let coursess = resp.data.courses;
        this.totalData = resp.data.total;
        for (let course of coursess) {
          let obj = {
            id: course.id,
            name: course.name,
          };
          this.courses.push(obj);
        }
      });
      this.$http
      .post("course/allcourse_folders", {
        folder_status: "Active",
        search: ""
      })
      .then(resp => {
        let folders = resp.data.folders;
        for (let folder of folders) {
          let obj = {
            id: folder.id,
            name: folder.folder_name
          };
          this.coursefolders.push(obj);
        }
      });
    if (this.$route.query.id) {
      this.company_id = this.$route.query.id;
      this.$http.get("company/get/" + this.company_id).then((resp) => {
        let data = resp.data[0];
        let obj = {
          name: data.name,
          administrator: data.admin,
          parent_company: data.parent_id,
          price_plan: data.price_plan,
          no_of_locations: data.location_num,
          no_of_employees: data.employee_num,
          phone_no:data.phone,
          company_type: data.type,
          website: data.website,
          fm_certificate_count: data.food_manager_total_count ? data.food_manager_total_count.fm_certificate_count :"",
          year: data.food_manager_year ? data.food_manager_year.year:"",
          address_1: data.address_1,
          address_2: data.address_2,
          city: data.location.city,
          state: data.location.state,
          zip: data.location.zip_code,
          logo: data.logo,
          status: data.status,
          sms_message: data.sms_status,
          pay_by_employee_status: data.pay_employee_status,
          pay_by_employee_discount: data.pay_employee_discount,
          secondary_course_status:data.secondary_course_status,
          notes:data.notes,
          course_ids: [],
          folder_ids: [],
          admins: [],
          managers: [],
          document_status: "",
        };
        if (data.document_status === 1) {
          obj.document_status = true;
        } else if (data.document_status === 0) {
          obj.document_status = false;
        } else {
          obj.document_status = data.document_status;
        }
        let company_admin = data.admin;
        for (let admin of company_admin) {
          obj.admins.push(admin);
        }
        let company_manager = data.manager;
        for (let manager of company_manager) {
          obj.managers.push(manager);
        }
        let company_courses = data.courses;
        for (let courses of company_courses) {
          obj.course_ids.push(courses.course_id);
        }
        let company_folders = data.folders;
        for (let courses of company_folders) {
          obj.folder_ids.push(courses.folder_id);
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
        this.company = obj;
      });
    }
  },

  methods: {
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
                })
              });
          }
        });
      } else {
        this.document.splice(index, 1);
      }
    },
    onImageChange(e) {
      let files = e;
      if (!files.length) return;
      this.createImage(files[0]);
    },
    createImage(file) {
      let reader = new FileReader();
      let vm = this;
      reader.onload = (e) => {
        vm.image = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    handleEdit(id) {
      this.$router.push("/add_employee?id=" + id);
    },

    updateAccoount() {
      this.loading = true;
      if (this.company_id !== "") {
        let data = {
          employee_id: this.employee_id,
          first_name: this.company.first_name,
          last_name: this.company.last_name,
          parent_id: this.company.parent_company,
          company_name: this.company.name,
          company_admin: this.company.administrator,
          company_location_num: this.company.no_of_locations,
          company_employee_num: this.company.no_of_employees,
          company_address_1: this.company.address_1,
          company_address_2: this.company.address_2,
          company_contact:this.company.phone_no,
          company_status: this.company.status,
          company_smsstatus: this.company.sms_message,
          company_email: this.company.email,
          company_username: this.company.username,
          company_zip: this.company.zip,
          company_website: this.company.website,
          company_fm_certificate_count:this.company.fm_certificate_count,
          year: this.company.year,
          company_price_plan: this.company.price_plan,
          company_type: this.company.company_type,
          course_ids: this.company.course_ids,
          folder_ids: this.company.folder_ids,
          company_password: "",
          image: this.image,
          company_pay_by_employee_status: this.company.pay_by_employee_status,
          company_pay_by_employee_discount:
            this.company.pay_by_employee_discount,
          secondary_course_status:  this.company.secondary_course_status,
          notes:this.company.notes,
          document_status: this.company.document_status,
          document_text: this.document,
        };
        if (this.company.password !== "") {
          data.company_password = this.company.password;
        }

        this.$http
          .put("company/update/" + this.company_id, data)
          .then((resp) => {
            this.$router.push("/dashboard/#client-section");
            Swal.fire({
              title: "Success!",
              text: `Account has been Updated!`,
              icon: "success",
            });
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
      }
    },
  },
};
</script>
<style>
.logo-size {
  width: 50%;
  height: auto;
  margin-top: 18px;
}
hr {
  margin-top: 1rem;
  margin-bottom: 1rem;
}
.el-select-dropdown__list {
  padding: 6px !important;
}

@media only screen and (min-width: 280px) and (max-width: 410px) {
  .el-select-dropdown {
    left: 0 !important;
  }
}

@media only screen and (min-width: 411px) and (max-width: 539px) {
  .el-select-dropdown {
    left: 16px !important;
  }
}
@media only screen and (min-width: 540px) and (max-width: 767px) {
  .el-select-dropdown {
    left: 40px !important;
  }
}
@media only screen and (min-width: 768px) and (max-width: 1280px) {
  .el-select-dropdown {
    left: 54px !important;
  }
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
