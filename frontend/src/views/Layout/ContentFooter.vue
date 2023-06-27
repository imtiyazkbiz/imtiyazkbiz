<template>
  <footer class="footer px-4 mx-2">
    <div class="row align-items-center justify-content-lg-between">
      <div class="col-lg-6">
        <div class="copyright text-center text-lg-left text-muted">
          © {{ year }}
          <a href="" class="font-weight-bold ml-1" target="_blank">{{
            site_name
          }}</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="text-right  text-muted text-center">
          <img
            width="100px"
            src="img/icons/cards/pci-dss-logo.png"
            alt="Image placeholder"
          />
        </div>
      </div>
    </div>
  </footer>
</template>
<script>
import { Dynamic } from "../../wl";
export default {
  data() {
    return {
      site_name: "",
      year: new Date().getFullYear()
    };
  },
  created: function() {
    this.site_name = Dynamic.SITE_NAME;
  }
};
</script>
<style></style>
