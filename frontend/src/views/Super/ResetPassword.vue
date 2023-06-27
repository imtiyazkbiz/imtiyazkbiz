<template>
  <div v-loading.fullscreen.lock="loading">
    <div class="header-section">
      <div class="toplogin-btn">
        <router-link to="/login" class="login-text">Login</router-link>
      </div>
      <div class="container">
        <div class="header-body text-center">
          <div class="row justify-content-center"></div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="login-section">
        <div class="white-shadow-box login-box positionUnset">
          <h3 class="text-center mb-5 mt-4">
            Enter Password
          </h3>
          <p class="mb-5 mt-4"></p>
          <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
            <form role="form" @submit.prevent="handleSubmit(onSubmit)">
              <div class="row">
                <div class="col-md-12">
                  <base-input
                    alternative
                    type="password"
                    class="mb-3 login-input"
                    name="New password"
                    :rules="{ required: true }"
                    placeholder="New Password"
                    v-model="form.newpassword"
                  >
                  </base-input>
                </div>
                <div class="col-md-12">
                  <base-input
                    alternative
                    type="password"
                    class="mb-3 login-input"
                    name="Confirm password"
                    :rules="{ required: true }"
                    placeholder="Confirm Password"
                    v-model="form.confirmpassword"
                  >
                  </base-input>
                </div>
              </div>

              <div class="text-right">
                <base-button native-type="submit" class="custom-btn"
                  >Submit</base-button
                >
              </div>
            </form>
            <p class="error" v-if="errors.invalid">{{ errors.invalid }}</p>
          </validation-observer>
        </div>
      </div>
    </div>
    <div class="push"></div>
  </div>
</template>
<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  data() {
    return {
      loading: false,
      form: {
        newpassword: "",
        confirmpassword: "",
        link: ""
      },
      errors: {
        invalid: ""
      }
    };
  },
  created() {
    if (this.$route.query.link) {
      this.form.link = this.$route.query.link;
    }
  },
  methods: {
    onSubmit() {
      if (this.form.newpassword == this.form.confirmpassword) {
        this.loading = true;
        this.$http
          .post("user/resetPassword", {
            link: this.form.link,
            new_password: this.form.newpassword
          })
          .then(resp => {
            this.$router.push("/login");
            Swal.fire({
              text: "Password updated successfully. You can now login.",
              icon: "success",
              confirmButtonClass: "btn btn-success btn-fill"
            });
          })
          .catch(function(error) {
            Swal.fire({
              title: "Error!",
              text: "Something went wrong! Please try again.",
              icon: "error"
            });
          })
          .finally(() => (this.loading = false));
      } else {
        Swal.fire({
          text: "Password didn't match!",
          icon: "error",
          confirmButtonClass: "btn btn-success btn-fill"
        });
      }
    }
  }
};
</script>
<style>
body,
html {
  height: 100%;
}

.error {
  color: red;
  text-align: center;
}
.py-5 {
  padding-bottom: 0px !important;
}
.mt--10 {
  margin-top: -10rem !important;
}
</style>
