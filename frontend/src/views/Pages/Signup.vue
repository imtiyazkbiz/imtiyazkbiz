<template>
  <div>
    <div class="header-section">
      <div class="toplogin-btn">
        <router-link class="login-text" to="/login">Login</router-link>
      </div>
      <div class="container">
        <div class="header-body text-center mb-3">
          <div class="row justify-content-center"></div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="login-section">
        <div class="white-shadow-box signup-box">
          <h4 style="text-align:justify; padding: 2.5%;" class="pt-2">
            If you are a staff member of a company that uses our service, please
            <a :href="'mailto:' + infoEmail"><u>click here</u></a> to email our
            Support team, and they will add you to the system. For an individual
            currently not working for a company we provide training to, click
            Individual User. To sign up a company currently not using our
            service, select Company. Thank you for your business!
          </h4>
          <div class="d-flex align-items-center justify-content-around row">
            <div class="mx-3 text-center mobCard">
              <i class="fas fa-user-alt user-icon"></i>

              <h3>Individual User</h3>
              <router-link to="/user_register" class="text-light"
                ><base-button>Click Here</base-button></router-link
              >
              <!-- 
                <button class="btn  btn-default disabled-button" disabled>
                  (Coming Soon)
                </button> -->
            </div>
            <div class="horizontal-border ml-5"></div>

            <div class="mx-3 text-center  mobCard">
              <i class="fas fa-users user-icon-company"></i>

              <h3>Company</h3>
              <router-link to="/register" class="text-light"
                ><base-button>Click Here</base-button></router-link
              >
            </div>
          </div>
          <div class="text-center mt-3 mb-2">
            <p class="mb-0">Already have an account?</p>
            <router-link to="/login" class="underline-class">Login</router-link>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5"></div>
        <div class="col-md-2 text-center">
          <img
            width="200px"
            src="img/icons/cards/credit-card.jpg"
            alt="Image placeholder"
            class="mb-2 mt-2"
          />
        </div>
        <div class="col-md-5"></div>
      </div>
    </div>
    <!-- <div class="push"></div> -->
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import "sweetalert2/src/sweetalert2.scss";
import { Dynamic } from "../../wl";
export default {
  name: "register",
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      baseUrl: this.$baseUrl,
      complete: false,
      hot_user: "",
      hot_token: "",
      config: "",
      infoEmail: ""
    };
  },
  mounted() {
    this.infoEmail = Dynamic.INFO_EMAIL;
  },
  created() {},

  methods: {}
};
</script>

<style>
.form-section {
  background-color: transparent;
}
.login-section {
  background-color: #ececf9;
  padding: 0px;
}
.course-section {
  padding: 40px;
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
.disabled-button {
  transform: none;
}
.user-icon,
.user-icon-company {
  color: #80d610;
}
.btn-default {
  background-color: #80d610;
  border-color: #80d610;
}
</style>
