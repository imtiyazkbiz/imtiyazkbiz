<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2"></div>
    </base-header>
    <div class="container-fluid mt--6">
      <card class="no-border-card" footer-classes="pb-2">
           <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-6">
                <h2 class="mb-0">User Onboarding Report</h2>
              </div>

              
             
            </div>
          </template>

        <user-onboarding-report></user-onboarding-report>
      </card>
    </div>
  </div>
</template>
<script>
import Card from "../../components/Cards/Card.vue";
import UserOnboardingReport from "./UserOnboardingReport.vue";

export default {
  components: {
    UserOnboardingReport,
    Card,
  },
  data() {
    return {};
  },
  created: function () {},
  methods: {},
};
</script>
<style>
.no-border-card .card-footer {
  border-top: 0;
}
</style>
