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
        <h2 slot="header" class="mb-0" v-if="this.video_id">
          Edit Tutorial Video
        </h2>
        <h2 slot="header" class="mb-0" v-else>
          Add Tutorial Video
        </h2>
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit()"
            enctype="multipart/form-data"
          >
            <div class="form-row">
              <div class="col-md-6">
                <label class="form-control-label">Role *</label>
                <br />
                <el-select
                  class="select-primary w-100"
                  v-model="vid.role"
                  placeholder="Role"
                  rules="required"
                  multiple
                >
                  <el-option
                    class="select-primary"
                    v-for="(option, index) in roles"
                    :value="option.value"
                    :label="option.label"
                    :key="'roles_' + index"
                  >
                  </el-option>
                </el-select>
              </div>

              <div class="col-md-6">
                <base-input
                  label="Video Title *"
                  name="Video Title"
                  placeholder="Video Title"
                  rules="required"
                  v-model="vid.video_name"
                >
                </base-input>
              </div>
              <div class="col-md-6">
                <base-input
                  label="Video Description"
                  name="Video Description"
                  placeholder="Video Description"
                  v-model="vid.video_description"
                >
                </base-input>
              </div>
              <div class="col-md-6">
                <base-input
                  label="Video Id *"
                  name="Video Id"
                  placeholder="Video Id"
                  rules="required"
                  v-model="vid.video"
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
      video_id: "",
      vid: {
        role: [],
        video_name: "",
        video: "",
        video_description: ""
      },
      roles: [
        {
          label: "Company Admin",
          value: "1"
        },
        {
          label: "Employee",
          value: "2"
        },
        {
          label: "Manager",
          value: "3"
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
      this.video_id = this.$route.query.id;
      this.$http.get("employees/tutorialVideos/" + this.video_id).then(resp => {
        let data = resp.data;
        this.vid.role.push(data.role);
        this.vid.video_name = data.video_title;
        this.vid.video_description = data.video_description;
        this.vid.video = data.video;
      });
    }
  },

  methods: {
    saveVideos() {
      if (
        this.vid.role.length > 0 &&
        this.vid.video_name !== "" &&
        this.vid.video !== ""
      ) {
        if (this.video_id !== "") {
          this.$http
            .put("employees/tutorialVideo/" + this.video_id, {
              video_id: this.video_id,
              role: this.vid.role,
              video_name: this.vid.video_name,
              video_description: this.vid.video_description,
              video: this.vid.video
            })
            .then(resp => {
              Swal.fire({
                title: "Success!",
                text: `Video Has been Updated!`,
                icon: "success"
              });
              this.$router.push("/tutorial_video");
            });
        } else {
          this.$http
            .post("employees/addtutorialVideo", {
              role: this.vid.role,
              video_name: this.vid.video_name,
              video_description: this.vid.video_description,
              video: this.vid.video
            })
            .then(resp => {
              Swal.fire({
                title: "Success!",
                text: `Video Has been Added!`,
                icon: "success"
              });
              this.$router.push("/tutorial_video");
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
