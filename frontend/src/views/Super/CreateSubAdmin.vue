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
        <h2 slot="header" class="mb-0" v-if="subadmin_id">Edit Sub Admin</h2>
        <h2 slot="header" class="mb-0" v-else>Add New Sub Admin</h2>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit(saveSubAdmin)"
            enctype="multipart/form-data"
          >
            <div class="form-row">
              <div class="col-md-3">
                <label class="form-control-label"
                  >First Name <span class="requireField">*</span></label
                >
                <base-input
                  name="First Name"
                  placeholder="First Name"
                  rules="required"
                  v-model="first_name"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <label class="form-control-label"
                  >Last Name <span class="requireField">*</span></label
                >
                <base-input
                  name="Last Name"
                  placeholder="Last Name"
                  rules="required"
                  v-model="last_name"
                >
                </base-input>
              </div>

              <div class="col-md-3">
                <base-input
                  label="Email"
                  name="Email"
                  placeholder="Email"
                  v-model="email"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <base-input
                  label="Phone no."
                  name="Phone no."
                  placeholder="Phone no."
                  v-model="phone_no"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <base-input
                  label="Address"
                  name="Address"
                  placeholder="Address"
                  v-model="address"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <base-input
                  label="City"
                  name="City"
                  placeholder="City"
                  v-model="city"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <base-input
                  label="State"
                  name="State"
                  placeholder="State"
                  v-model="state"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <base-input
                  label="Zipcode"
                  name="Zipcode"
                  placeholder="Zipcode"
                  v-model="zipcode"
                >
                </base-input>
              </div>
              <div class="col-md-4" v-if="subadmin_id">
                <label class="form-control-label"
                  >Username <span class="requireField">*</span></label
                >
                <base-input
                  type="text"
                  name="username"
                  placeholder="Username"
                  rules="required"
                  v-model="username"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <label class="form-control-label"
                  >Password <span class="requireField">*</span></label
                >
                <base-input
                  type="text"
                  name="Password"
                  placeholder="Password"
                  rules="required"
                  v-model="password"
                >
                </base-input>
              </div>
            </div>

            <hr />

            <h3>Permissions</h3>
            <div class="user-eltable">
              <el-table
                class="table-striped"
                header-row-class-name="thead-light custom-thead-light"
                :data="rights"
                style="width: 100%"
              >
                <el-table-column
                  min-width="150"
                  align="left"
                  label="Module Name"
                  prop="module"
                >
                  <template slot-scope="props">
                    {{ props.row.module_name }}
                  </template>
                </el-table-column>
                <el-table-column min-width="100" align="left" label="View">
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.view"
                        type="success"
                        v-model="props.row.view"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.view"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column min-width="100" align="left" label="Create">
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.create"
                        type="success"
                        v-model="props.row.create"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.create"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="100"
                  align="left"
                  label="Edit/Update"
                >
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.edit"
                        type="success"
                        v-model="props.row.edit"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.edit"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column min-width="100" align="left" label="Delete">
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                     
                    >
                      <base-switch
                        class="mr-1"
                        v-if="props.row.delete"
                        type="success"
                        v-model="props.row.delete"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        v-else
                        type="danger"
                        v-model="props.row.delete"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
              </el-table>
            </div>
            <div class="text-right mt-3">
              <base-button
                class="custom-btn"
                type="danger"
                @click="$router.go(-1)"
                >Cancel</base-button
              >
              <base-button class="custom-btn" native-type="submit"
                >Submit</base-button
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
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      loading: false,
      subadmin_id: "",
      first_name: "",
      last_name: "",
      email: "",
      phone_no: "",
      address: "",
      city: "",
      state: "",
      zipcode: "",
      password: "",
      username: "",
      rights: [],
    };
  },
  created() {
    this.$http.get("subadmin/rights").then((resp) => {
        this.rights=[];
      let rights = resp.data;
      for (let data of rights) {
        let obj = {
          module_id: data.id,
          module_name: data.right_name,
          view: true,
          create: true,
          edit: true,
          delete: false,
        };
        this.rights.push(obj);
      }
    });
    if (this.$route.query.id) {
      this.subadmin_id = this.$route.query.id;
      this.loading = true;
      this.$http
        .put("subadmin/edit/" + this.subadmin_id)
        .then((resp) => {
          this.first_name = resp.data.first_name;
          this.last_name = resp.data.last_name;
          this.email = resp.data.email;
          this.phone_no = resp.data.phone_num;
          this.address = resp.data.address;
          this.city = resp.data.city;
          this.state = resp.data.state;
          this.zipcode = resp.data.zipcode;
          this.password = resp.data.access_code;
          this.username = resp.data.user_name;
          this.rights = [];
          for (let data of resp.data.permissions) {
            let permission = JSON.parse(data.permissions);
            let obj = {
              module_id: data.right_id,
              module_name: data.right_name,
              view: permission.indexOf("v") !== -1 ? true : false,
              create: permission.indexOf("c") !== -1 ? true : false,
              edit: permission.indexOf("e") !== -1 ? true : false,
              delete: permission.indexOf("d") !== -1 ? true : false,
            };
            this.rights.push(obj);
          }
        })
        .catch(function (error) {
          Swal.fire({
            title: "Error!",
            html: error.response.data.message,
            icon: "error",
          });
        })
        .finally(() => (this.loading = false));
    }
  },
  methods: {
    saveSubAdmin() {
      this.loading = true;
      if (!this.subadmin_id) {
        this.$http
          .post("subadmin/save_subadmin", {
            first_name: this.first_name,
            last_name: this.last_name,
            email: this.email,
            phone_no: this.phone_no,
            address: this.address,
            city: this.city,
            state: this.state,
            zipcode: this.zipcode,
            password: this.password,
            permissions: this.rights,
          })
          .then((resp) => {
            Swal.fire({
              title: "Success!",
              text: `Sub admin created succesfully!`,
              icon: "success",
            });
            this.$router.push("/all_users");
          })
          .catch(function (error) {
            Swal.fire({
              title: "Error!",
              html: error.response.data.message,
              icon: "error",
            });
          })
          .finally(() => (this.loading = false));
      } else {
        this.$http
          .post("subadmin/update_subadmin", {
            subadmin_id: this.subadmin_id,
            first_name: this.first_name,
            last_name: this.last_name,
            email: this.email,
            phone_no: this.phone_no,
            address: this.address,
            city: this.city,
            state: this.state,
            zipcode: this.zipcode,
            password: this.password,
            permissions: this.rights,
          })
          .then((resp) => {
            Swal.fire({
              title: "Success!",
              text: `Sub admin updated succesfully!`,
              icon: "success",
            });
            this.$router.push("/all_users");
          })
          .catch(function (error) {
            Swal.fire({
              title: "Error!",
              html: error.response.data.message,
              icon: "error",
            });
          })
          .finally(() => (this.loading = false));
      }
    },
  },
};
</script>
<style scoped></style>
