<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2"></div>
    </base-header>
    <div class="container-fluid mt--6">
      <companies></companies>
    </div>
  </div>
</template>
<script>
import Companies from "./Companies.vue";

export default {
  components: {
    Companies
  },
  data() {
    return {};
  },
  created: function() {},
  methods: {}
};
</script>
<style>
.no-border-card .card-footer {
  border-top: 0;
}
</style>
