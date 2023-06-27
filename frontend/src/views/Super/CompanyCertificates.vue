<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pt-1">
          <template slot="header">
            <h2 class="mb-0">Certificates</h2>
          </template>
          <div>
            <div
              class="row mb-2 d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-5">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>

              <div class="col-md-2 form-group"></div>
            </div>
            <div class="user-eltable">
              <el-table
                :data="tableData"
                row-key="id"
                role="table"
                class="certificateGrid"
                header-row-class-name="thead-light custom-thead-light"
                @sort-change="sortChange"
                @selection-change="selectionChange"
              >
                <el-table-column
                  align="left"
                  min-width="300px"
                  label="Course Name"
                >
                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/certificate_details?id=' +
                          props.row.id +
                          '&course=' +
                          props.row.course_id
                      "
                      ><span>{{ props.row.course_name }}</span></router-link
                    >
                  </template>
                </el-table-column>
                <!-- <el-table-column align="left" min-width="150px" label="Certificate Name">
                  <template slot-scope="props">
                    <span>{{ props.row.certificate_name }}</span></template
                  >
                </el-table-column> -->
                <el-table-column
                  align="left"
                  min-width="150px"
                  label="# of active employees"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.user_count }}</span></template
                  >
                </el-table-column>
                <el-table-column label="Action" min-width="100px">
                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/certificate_details?id=' +
                          props.row.id +
                          '&course=' +
                          props.row.course_id
                      "
                      ><base-button name="Certificate Details" class="custom-btn" size="sm"
                        >Details</base-button
                      >
                    </router-link>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
        </card>
      </div>
    </div>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
//import swal from 'sweetalert';
import Swal from "sweetalert2";
let timeout = null;
export default {
  mixins: [clientPaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      searchQuery: "",
      company_name: "",
      hot_user: "",
      hot_token: "",
      user_id: "",
      config: "",
      certificateStatus: [
        {
          label: "My Certificates",
          value: "Company"
        },
        {
          label: "Employee Certificates",
          value: "Employee"
        }
      ],
      filters: {
        certStatus: "Employee"
      },
      locationManager: false,
      location_id: "",
      tableData: [],
      selectedRows: []
    };
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.user_id = localStorage.getItem("hot-user-id");
      this.company_name = localStorage.getItem("hot-company-name");
    }
    if (localStorage.getItem("hot-sidebar") === "location_manager") {
      this.locationManager = true;
      this.location_id = localStorage.getItem("hot-location-id");
    }
    this.fetchData();
  },
  watch: {
    searchQuery: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    }
  },
  methods: {
    fetchData() {
      this.loading = true;
      this.$http
        .post("course/certificatesList", {
          search: this.searchQuery
        })
        .then(resp => {
          this.tableData = [];

          let certificates = resp.data.certificates;

          for (let certificate of certificates) {
            let obj = {
              id: certificate.id,
              course_id: certificate.course_id,
              certificate_name: certificate.certificate_name,
              course_name: certificate.course_name,
              user_count: certificate.employee_count,
              course_count: certificate.course_count
            };
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
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
  .certificateGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Course Name";
  }

  .certificateGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "# of active employees";
  }
  .certificateGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Action";
  }
}
</style>
