<template>
  <card
    class="no-border-card"
    footer-classes="pb-2"
    v-loading.fullscreen.lock="loading"
  >
    <template slot="header">
      <div class="row align-items-center">
        <div class="col-lg-12 col-md-12 col-12">
          <h2 class="mb-0">Survey Submissions Details</h2>
        </div>
      </div>
    </template>
    <div>
      <div class="row  flex-wrap mb-4">
        <div class="col-lg-3 col-md-3 col-12">
          <div class="headertext">
            <p class="mb-0">Company</p>
            <h5>{{ company_name }}</h5>
          </div>
        </div>
        <div class="col-lg-3 col-md-3 col-12">
          <div class="headertext">
            <p class="mb-0">Employee</p>
            <h5>{{ employee_name }}</h5>
          </div>
        </div>

        <div class="col-lg-3 col-md-3 col-12">
          <div class="headertext">
            <p class="mb-0">Course</p>
            <h5>{{ course_name }}</h5>
          </div>
        </div>

        <div class="col-lg-3 col-md-3 col-12">
          <div class="headertext">
            <p class="mb-0">Date</p>
            <h5>{{ date }}</h5>
          </div>
        </div>
      </div>

      <div class="user-eltable">
        <el-table
          role="table"
          :data="tableData"
          stripe
          highlight-current-row
          lazy
          row-key="id"
          id="tableOne"
          header-row-class-name="thead-light"
          class="surveySubmissionDetailsGrid table-striped"
        >
          <el-table-column min-width="50%">
            <template slot="header">
              <span>Question</span>
            </template>
            <template slot-scope="props">
              {{ props.row.question }}
            </template>
          </el-table-column>

          <el-table-column min-width="50%">
            <template slot="header">
              <span> Answer </span>
            </template>
            <template slot-scope="props">
              <span> {{ props.row.answer }}</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
    <div slot="footer" class="d-flex justify-content-end ">
      <nav v-if="pagination && tableData.length > 0">
        <div class="row">
          <div class="col-md-12">
            <ul
              class="pagination custompagination  justify-content-end align-items-center"
            >
              <p class="p-0 m-0 mr-2">
                Showing {{ tableData.length }} of {{ totalData }} entries
              </p>
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a
                  class="page-link"
                  href="#"
                  @click.prevent="changePage(currentPage - 1)"
                  ><i class="fa fa-caret-left "></i>
                </a>
              </li>
              <li
                v-for="(page, index) in pagesNumber"
                class="page-item"
                :class="{ active: page == currentPage }"
                v-bind:key="index"
              >
                <a
                  href="javascript:void(0)"
                  @click.prevent="changePage(page)"
                  class="page-link"
                  >{{ page }}</a
                >
              </li>
              <li
                class="page-item"
                :class="{
                  disabled: currentPage === last_page
                }"
              >
                <a
                  class="page-link"
                  href="#"
                  @click.prevent="changePage(currentPage + 1)"
                  ><i class="fa fa-caret-right "></i
                ></a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </card>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import moment from "moment";
let timeout = null;
export default {
  name: "companies",
  mixins: [serverSidePaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,

      tableData: [],

      hot_user: "",
      hot_token: "",
      editor: "",

      employee_id: "",
      survey_id: "",
      employee_name: "",
      company_name: "",
      course_name: "",
      date: ""
    };
  },
  watch: {},
  created: function() {
    if (this.$route.query.id) {
      this.employee_id = this.$route.query.id;
    }
    if (this.$route.query.test) {
      this.survey_id = this.$route.query.test;
    }
    if (this.$route.query.course) {
      this.course_id = this.$route.query.course;
    }
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/survey_submission_report_details", {
          test_id: this.survey_id,
          employee_id: this.employee_id,
          course_id: this.course_id
        })
        .then(resp => {
          this.tableData = [];
          let report_data = resp.data.report;
          let qreport_data = resp.data.questionreport;
          let total_data = resp.data.total;
          this.totalData = total_data;
          this.employee_name = report_data[0].employee_name;
          this.company_name = report_data[0].company_name;
          this.course_name = report_data[0].course_name;
          this.date = moment(report_data[0].updated_at).format("MM-DD-YYYY");
          for (let data of qreport_data) {
            let obj = {
              question: data.question,
              answer: data.answer_title
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    }
  }
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .surveySubmissionDetailsGrid
    >>> table.el-table__body
    td:nth-of-type(1):before {
    content: "Question";
  }
  .surveySubmissionDetailsGrid
    >>> table.el-table__body
    td:nth-of-type(2):before {
    content: "Answer";
  }
}
</style>
