<template>
  <div>
    <modal :show.sync="downlaodModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Download Course Report for
        <span class="highlight-title">{{ company_name }}</span>
      </h3>
      <el-select v-model="filters.course_id" placeholder="All Courses">
        <el-option
          class="select-default"
          v-for="item in companyCourses"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        >
        </el-option>
      </el-select>
      <form>
        <br />
        <div class="row" style="text-align: center">
          <base-button
            type="warning"
            @click.prevent="downloadcourselist('open')"
            >Open Courses</base-button
          >
          <base-button
            type="danger"
            @click.prevent="downloadcourselist('non-complaint')"
          >
            Non Compliance</base-button
          >
          <base-button
            type="success"
            @click.prevent="downloadcourselist('complaint')"
            >Compliance</base-button
          >
        </div>
        <!-- <button type="button" class="download-btn" v-on:click="download">Download</button>-->
        <div class="clearfix"></div>
      </form>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import XLSX from "xlsx";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  name: "course-report-download",
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  props: {
    company_id: Number,
    company_name: String
  },
  data() {
    return {
      downlaodModel: true,
      filters: {
        course_id: "All Courses"
      },
      companyCourses: []
    };
  },

  created() {
    this.$http.get("company/all_courses/" + this.company_id).then(resp => {
      this.companyCourses = [];
      for (let data of resp.data[0].courses) {
        let obj = {
          label: data.name,
          value: data.course_id
        };
        this.companyCourses.push(obj);
      }
    });
    this.downlaodModel = true;
  },
  methods: {
    downloadcourselist(type) {
      let report_type = "";
      if (type == "open") {
        this.report_type = "open_course";
      }
      if (type == "non-complaint") {
        this.report_type = "non_compliance";
      }
      if (type == "complaint") {
        this.report_type = "compliance";
      }
      this.$http
        .post("course/certificates/report", {
          report_type: this.report_type,
          company_id: this.company_id,
          course_id: this.filters.course_id
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
          this.downlaodModel = false;
        })
        .catch(function(error) {
          self.processing = false;
          if (error.response.status === 422) {
            let respmessage = error.response.data.message;
            return Swal.fire({
              title: "Error!",
              text: respmessage,
              icon: "error"
            });
          }
        });
    }
  }
};
</script>
