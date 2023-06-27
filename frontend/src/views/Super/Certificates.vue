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
            <div class="row align-items-center">
              <div class="col-md-6">
                <h2 class="mb-0">Certificates</h2>
              </div>
              <div class="col-lg-6 col-sm-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                <router-link v-if="canCreate" to="/create_certificate"
                  ><base-button class="custom-btn"
                    ><i class="fa fa-plus" aria-hidden="true"></i> New
                    Certificate</base-button
                  ></router-link
                >
              </div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-6">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <!-- <div class="col-md-2 form-group">
                <label>Showing:</label>

                <el-select
                  class="select-primary pagination-select"
                  v-model="perPage"
                  v-on:change="fetchData()"
                  placeholder="Per page"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in perPageOptions"
                    :key="item"
                    :label="item"
                    :value="item"
                  >
                  </el-option>
                </el-select>
              </div> -->
            </div>
            <div class="user-eltable">
              <el-table
                role="table"
                :data="tableData"
                stripe
                highlight-current-row
                row-key="id"
                header-row-class-name="thead-light custom-thead-light"
                class="certmainGrid"
                @selection-change="selectionChange"
              >
                <el-table-column min-width="300px" label="Certificate Name">
                  <template slot-scope="props">
                    <router-link
                      :to="
                        '/certificate_details?id=' +
                          props.row.id +
                          '&certificate=' +
                          props.row.certificate_name
                      "
                      ><span class="">{{
                        props.row.certificate_name
                      }}</span></router-link
                    >
                  </template>
                </el-table-column>
                <el-table-column
                  v-for="column in tableColumns"
                  :key="column.label"
                  v-bind="column"
                >
                </el-table-column>
                <el-table-column min-width="180px" label="Actions">
                  <div slot-scope="{ $index, row }" class="d-flex custom-size">
                    <el-tooltip  v-if="canEdit" content="Edit" placement="top">
                      <router-link :to="'/create_certificate?id=' + row.id">
                        <base-button
                          class="success"
                          type=""
                          size="sm"
                          icon
                          data-toggle="tooltip"
                          data-original-title="Edit"
                        >
                          <i class="text-success fa fa-pencil-square-o"></i>
                        </base-button>
                      </router-link>
                    </el-tooltip>
                    <el-tooltip content="View Certificate" placement="top">
                      <base-button
                        @click.native="viewCertificate($index, row)"
                        class="like btn-link"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="View Certificate"
                        style="color:white;margin-left:10px;"
                      >
                        <i class="text-primary fas fa-eye"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip v-if="canDelete" content="Delete" placement="top">
                      <base-button
                        @click.native="handleDelete($index, row)"
                        class="remove btn-link"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                        style="margin-left:10px;"
                      >
                        <i class="text-danger fas fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                  </div>
                </el-table-column>
              </el-table>
            </div>
          </div>
        </card>
      </div>
    </div>
    <modal
      v-if="viewCertificateModal"
      :show.sync="viewCertificateModal"
      class="user-modal modal-overflow"
    >
      <h3 slot="header" class="text-center mb-0">Preview</h3>
      <adobe-pdf path="previewCertificate" :url="certificate_id"></adobe-pdf>
    </modal>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2";
import AdobePdf from "./AdobePdf.vue";
let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    AdobePdf,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      baseUrl: this.$baseUrl,
      viewCertificateModal: false,
      title: "",
      custom_text: "",
      signature_title_1: "",
      signature_title_2: "",
      tableColumns: [
        {
          prop: "course_count",
          label: "Number of Courses",
          minWidth: 220
        },
        {
          prop: "user_count",
          label: "# of active employees",
          minWidth: 220
        }
      ],
      tableData: [],
      selectedRows: [],
      searchQuery: "",
      certificate_id: "",
      canCreate:true,
      canEdit:true,
      canDelete:true,
    };
  },
  watch: {
    searchQuery: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    }
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (localStorage.getItem("hot-user") === "sub-admin") {
       this.getRightsDetails();
    }
    this.setDefaultFilterData();
  },
  methods: {
    getRightsDetails(){
       let type="Certificate";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
      });
    },
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
              course_id: "",
              certificate_name: certificate.name,
              user_count: certificate.employee_count,
              course_count: certificate.course_count
            };
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.searchQuery = "";
      this.fetchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_certificate_search_data",
        JSON.stringify({
          role: "super-admin",
          search: this.searchQuery
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_certificate_search_data")
      );

      if (previousStateData !== null) {
        this.searchQuery = this.searchQuery
          ? this.searchQuery
          : previousStateData.search;
      }
      this.fetchData();
    },
    viewCertificate(index, row) {
      this.certificate_id = row.id.toString();
      this.viewCertificateModal = true;
    },
    handleDelete(index, row) {
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false
      }).then(result => {
        if (result.value) {
          this.$http
            .delete("/course/certificate/" + row.id + "/deleteCertificate")
            .then(resp => {
              this.tableData.splice(index, 1);
              Swal.fire({
                title: "Deleted!",
                text: "Certificate has been deleted.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            });
        }
      });
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    }
  }
};
</script>

<style scoped>
#adobe-dc-view {
  height: 470px !important;
  overflow-y: auto;
}
@media screen and (min-width: 1920px) {
  .login-left-panel {
    max-width: 700px;
  }
}

.custom-size .btn-sm {
  padding: 2px !important;
  font-size: 16px !important;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .certmainGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Certificate Name";
  }
  .certmainGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Course Count";
  }
  .certmainGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "User Count";
  }
  .certmainGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Actions";
  }
}
</style>
