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
        <h2 slot="header" class="mb-0" v-if="this.formLink">
          Edit Form Link
        </h2>
        <h2 slot="header" class="mb-0" v-else>
          Add New  Form
        </h2>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit()"
            enctype="multipart/form-data"
          >
            <div class="form-row">
              <div class="col-md-6">
                <base-input
                  label="Form Link"
                  name="formlink"
                  placeholder="Form Link"
                  v-model="vid.formLink"
                >
                </base-input>
              </div>
              
            </div>
            <div class="text-right">
              <base-button type="danger" @click="$router.go(-1)"
                >Cancel</base-button
              >
              <base-button class="custom-btn" @click.prevent="saveVideos()"
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
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      hot_user: "",
      hot_token: "",
      config: "",
      formid_id: "",
      vid: {
        formLink: ""
      },
      roles: [
        {
          label: "Public",
          value: "Public"
        },
        {
          label: "Private",
          value: "Private"
        }
      ],
      status: [
        {
          label: "Active",
          value: "True"
        },
        {
          label: "Inactive",
          value: "False"
        }
      ]
    };
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    if (this.$route.query.id) {
      this.formid_id = this.$route.query.id;
      this.$http.get("employees/getsingleform/" + this.formid_id).then(resp => {
        console.log("single form")
        let data = resp.data;
        // this.vid.role = data.type;
        // this.vid.status = data.status;
        // this.vid.video_name = data.name;
        // this.vid.video_description = data.discription;
        this.vid.formLink = data.formlink;
      });
    }
  },

  methods: {
    saveVideos() {
      if (this.vid.formLink !== "" ) {
        if (this.formid_id !== "") {
        this.$http
            .put("employees/updatejotForm/" + this.formid_id, {
              formlink: this.vid.formLink
            })
            .then(resp => {
              Swal.fire({
                title: "Success!",
                text: `Form Sharable link Has been Updated!`,
                icon: "success"
              });
              this.$router.push("/jotform");
            });
        } else {
          this.$http
            .post("employees/addnewlink", {
              formlink: this.vid.formLink
            })
            .then(resp => {
              Swal.fire({
                title: "Success!",
                text: `Form Sharable link Has been Added!`,
                icon: "success"
              });
              this.$router.push("/jotform");
            });
        }
      } else {
        return Swal.fire({
          title: "Error!",
          text: `Please Fill all the mandatory fields!`,
          icon: "error"
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
</style>
