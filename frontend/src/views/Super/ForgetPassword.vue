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
          <h3 class="text-center mb-3 mt-4">
           Forgot Password
          </h3>
          <p class="mb-4 mt-3">
            Your email address is usually your username for the platform. 
            Please enter your email address below and a “Reset Password” link will be sent to the email we have on file.  
           If you do not have an email address in the system, please email us at: 
                  <a :href="'mailto:' + support_email">{{ support_email }}</a
                  >.</p>
          <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
            <form role="form" @submit.prevent="handleSubmit(onSubmit)">
              <base-input
                alternative
                class="mb-3 login-input"
                name="Email"
                :rules="{ required: true }"
                prepend-icon="ni ni-email-83"
                placeholder="Email"
                v-model="form.email"
              >
              </base-input>

              <div class="text-center">
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
import { Dynamic } from "../../wl";
export default {
  data() {
    return {
      loading: false,
      form: {
        email: ""
      },
      errors: {
        invalid: ""
      },
      support_email: ""
    };
  },
  created: function() {
    this.support_email = Dynamic.SUPPORT_EMAIL;
  },
  methods: {
    onSubmit() {
      this.loading = true;
      this.$http
        .post("user/resetLink", {
          user_name: this.form.email
        })
        .then(resp => {
          Swal.fire({
            html: resp.data.message,
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill"
          });
        })
        .catch(function(error) {
          Swal.fire({
            title: "Error!",
            html: error.response.data.message,
            icon: "error"
          });
        })
        .finally(() => (this.loading = false));
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
