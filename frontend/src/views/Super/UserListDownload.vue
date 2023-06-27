<template></template>
<script>
import XLSX from "xlsx";
export default {
  name: "user-list-download",
  props: {
    report_type: String,
    company_id: Number,
    company_name: String
  },

  created() {
    this.$http
      .post("company/users", {
        report_type: this.report_type,
        company_id: this.company_id
      })
      .then(resp => {
        this.items = resp.data;
        const data = XLSX.utils.json_to_sheet(this.items);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, data, "data");
        XLSX.writeFile(
          wb,
          this.company_name + "-" + this.report_type + ".xlsx"
        );
      });
  }
};
</script>
