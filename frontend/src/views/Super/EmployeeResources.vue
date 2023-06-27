<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card
          class="no-border-card"
          body-classes="px-0 pb-1"
          footer-classes="pb-2"
        >
          <template slot="header">
            <h2 class="mb-0">Resources</h2>
            <p style="font-weight: 600;">This is extra material & content provided for your courses. Please feel free to download, watch, save, etc.</p>
          </template>
          <div class="col-sm-12">
            <div class="row">
              <div class="col-md-12 user-eltable">
                <el-table
                  role="table"
                  class="courseresGrid"
                  :data="tbl2_data"
                  header-row-class-name="thead-light custom-thead-light"
                >
                  <el-table-column
                    min-width="300px"
                    align="left"
                    label="Course Name"
                    prop="course_name"
                  >
                    <template slot-scope="propss">
                      {{ propss.row.course_name }}
                    </template>
                  </el-table-column>
                  <el-table-column min-width="300px" label="Resources">
                    <template slot-scope="propss">
                      <div
                        class=""
                        v-for="resource in propss.row.course_resources"
                        :key="resource.id"
                      >
                        <div class="row">
                          <div class="col-md-12">
                            {{ resource.name }}
                          </div>
                        </div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column min-width="150px" label="Action">
                    <template slot-scope="propss">
                      <div
                        class="openlink-action"
                        v-for="resource in propss.row.course_resources"
                        :key="resource.id"
                      >
                        <a
                          v-if="
                            resource.type == 'file' &&
                            resource.file_name != null
                          "
                          target="_blank"
                          :href="resource.url"
                          class=""
                          title="Resource"
                          style="color: white"
                          ><i
                            name="Resources Download"
                            class="text-warning fa fa-download"
                          ></i>
                        </a>
                        <a
                          v-if="resource.type == 'link' && resource.url != null"
                          target="_blank"
                          :href="resource.url"
                          class=""
                          title="Open Link"
                        >
                          <i name="Open Resources Link" class="fa fa-link"></i>
                        </a>
                      </div>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>
          </div>
        </card>
      </div>
    </div>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import "sweetalert2/src/sweetalert2.scss";

export default {
  mixins: [serverSidePaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      hot_user: "",
      hot_token: "",
      user_id: "",
      tbl2_data: [],
      editor: "",
    };
  },

  created: function () {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      if (this.hot_user === "company-admin") {
        this.user_id = localStorage.getItem("hot-admin-id");
      } else {
        this.user_id = localStorage.getItem("hot-user-id");
      }
    }
    if (localStorage.getItem("hot-user") === "employee") {
      this.editor = "employee";
    }
    this.$http
      .post("employees/courses", {
        employee_id: this.user_id,
      })
      .then((resp) => {
        let course_data = resp.data.courses;

        for (let course of course_data) {
          let obj = {
            course_name: course.name,
            id: course.course_id,
            course_resources: [],
          };
          obj.course_resources = course.course_resources;
          if(obj.course_resources.length > 0){
          this.tbl2_data.push(obj);
          }
        }
      });
  },
  methods: {},
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .courseresGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Courses";
  }
  .courseresGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Resources";
  }
  .courseresGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Action";
  }
}
</style>
