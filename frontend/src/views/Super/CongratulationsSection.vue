<template>
  <div>
    <div class="col-md-12 text-center">
      <div>
        <img class="gifimg" :src="baseUrl + '/images/tenor.gif'" />
      </div>
      <div class="congrats">
        <!-- <h1>Congratulations!</h1> -->
      </div>
    </div>
    <div class="col-md-12 mt-3 mb-5">
      <h3
        style="color: #444C57;"
        class=" text-center"
        v-if="pass_message === ''"
      >
        You have passed this course!
      </h3>
      <h3 style="color: #444C57;" class=" text-center" v-else>
        {{ pass_message }}
      </h3>
    </div>

    <div
      class="col-md-12 text-center mb-4 "
      v-if="!practice_test && certificate_availbility"
    >
      <router-link
        :to="
          its_super_admin
            ? '/courses'
            : hot_user === 'company' || hot_user === 'super-admin' || hot_user === 'sub-admin'
            ? '/company_certificates'
            : '/employee_certificates'
        "
      >
        <base-button class="custom-btn">
          {{
            its_super_admin
              ? "LMS Courses"
              : hot_user === "company" || hot_user === "super-admin" || hot_user === 'sub-admin'
              ? "Certificate"
              : "Certificate"
          }}
        </base-button>
      </router-link>
    </div>

    <div class="col-md-12 text-center" v-if="next_course_message">
      <h3>{{ next_course_message }}.</h3>
      <h3>
        <router-link to="/employee_courses" class="linkColor"
          ><u>Click here</u></router-link
        >
        to go to the course now.
      </h3>
    </div>
    <div class="text-center">
      <router-link class="linkColor" to="/dashboard"
        >Back to dashboard</router-link
      >
    </div>
  </div>
</template>

<script>
import Vue from "vue";
export default {
  name: "congratualtions-section",
  props: {
    practice_test: Number,
    pass_message: String,
    next_course_message: String,
    certificate_availbility: Number,
    its_super_admin: Boolean,
    hot_user: String
  },
  data() {
    return {
      baseUrl: this.$baseUrl
    };
  },
  created() {},
  methods: {}
};
</script>
<style scoped>
h1 {
  transform-origin: 50% 50%;
  font-size: 50px;
  font-family: "Sigmar One", cursive;
  cursor: pointer;
  z-index: 2;
  top: 0;
  text-align: center;
  width: 100%;
}

.blob {
  height: 50px;
  width: 50px;
  color: #ffcc00;
  position: absolute;
  top: 45%;
  left: 45%;
  z-index: 1;
  font-size: 30px;
  display: none;
}
.gifimg {
  opacity: 0.5;
  width: 26%;
}
</style>
