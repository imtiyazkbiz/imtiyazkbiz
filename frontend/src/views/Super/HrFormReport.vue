<template>
  <!-- <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
      <card class="no-border-card" footer-classes="pt-1"> -->
          <!-- <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-6">
                <h2 class="mb-0">HR Forms Report</h2>
              </div>

              <div class="col-lg-6 col-md-6 text-right">
                <base-button
                  name="Clear Filters"
                  class="custom-btn"
                  v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
              </div>
            </div>
          </template> -->
<div>
          <div>
             <div class="row align-items-center mb-2">
              <!-- <div class="col-md-6 col-lg-6">
                <h2 class="mb-0">HR Forms Report</h2>
              </div> -->
<div class="col-lg-6 col-md-6 text-right"></div>
              <div class="col-lg-6 col-md-6 text-right">
                <base-button
                  class="custom-btn"
                  v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
              </div>
            </div>
            <div
              class="
                row
                d-flex
                justify-content-center justify-content-sm-between
                flex-wrap
              "
            >
              <div class="col-md-6">
                <base-input
                  label="Search:"
                  name="Search"
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search Keyword"
                >
                </base-input>
              </div>

              <div class="col-md-4">
                <base-input label="Company:">
                  <el-select
                    filterable
                    class="select-primary"
                    v-on:change="fetchData()"
                    v-model="filters.location_id"
                    placeholder="Filter by Location"
                  >
                    <el-option
                      class="select-primary"
                      v-for="item in locations"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>

              <div class="col-md-2 form-group">
                <base-input label="Showing:">
                  <el-select
                    class="select-primary pagination-select"
                    v-model="perPage"
                    v-on:change="changePage(1)"
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
                </base-input>
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
                class="hrformReportGrid table-striped"
              >
                <el-table-column min-width="150px" prop="first_name">
                  <template slot="header">
                    <span>FIRST NAME</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.first_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="150px" prop="last_name">
                  <template slot="header">
                    <span> LAST NAME </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.last_name }}
                  </template>
                </el-table-column>
                <el-table-column min-width="150px">
                  <template slot="header">
                    <span>LOCATION </span>
                  </template>
                  <template slot-scope="props">
                    <span v-if="props.row.location">
                      {{ props.row.location }}</span
                    >
                    <span v-else>-</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="100px" prop="date">
                  <template slot="header">
                    <span> DATE </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.date }}
                  </template>
                </el-table-column>
                <el-table-column min-width="100px" prop="form">
                  <template slot="header">
                    <span> HR FORM </span>
                  </template>
                  <template slot-scope="props">
                    <a :href="baseUrl + '/hr-forms/' + props.row.file">{{
                      props.row.file_name
                    }}</a>
                  </template>
                </el-table-column>
                <el-table-column min-width="100px" prop="form">
                  <template slot="header">
                    <span> USER UPLOADED FILE</span>
                  </template>
                  <template slot-scope="props">
                     <el-tooltip content="Download" placement="top">
                    <base-button
                      type=""
                      size="md"
                      @click.native="handleView(props.row.uploaded)"
                      data-toggle="tooltip"
                    data-original-title="Download"
                    >
                      <i class="text-warning fa fa-download"></i>
                    </base-button>
                     </el-tooltip>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>

          <div slot="footer" class="d-flex justify-content-end">
            <nav v-if="pagination && tableData.length > 0">
              <div class="row">
                <div class="col-md-12">
                  <ul
                    class="
                      pagination
                      custompagination
                      justify-content-end
                      align-items-center
                    "
                  >
                    <p class="p-0 m-0 mr-2">
                      Showing {{ tableData.length }} of {{ totalData }} entries
                    </p>
                    <li
                      class="page-item"
                      :class="{ disabled: currentPage === 1 }"
                    >
                      <a
                        class="page-link"
                        href="#"
                        @click.prevent="changePage(currentPage - 1)"
                        ><i class="fa fa-caret-left"></i>
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
                        disabled: currentPage === last_page,
                      }"
                    >
                      <a
                        class="page-link"
                        href="#"
                        @click.prevent="changePage(currentPage + 1)"
                        ><i class="fa fa-caret-right"></i
                      ></a>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>
          </div>
        </div>
        <!-- </card>
      </div>
    </div>
  </div> -->
</template>
<script>
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import moment from "moment";
import axios from 'axios'
let timeout = null;
export default {
  name: "hr-form-report",
  mixins: [serverSidePaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    [DatePicker.name]: DatePicker,
  },
  data() {
    return {
      loading: false,
      locations: [
        {
          label: "All",
          value: "",
        },
      ],
      searchQuery: "",
      filters: {
        location_id: "",
      },
      tableData: [],
      editor: "",
      totalData: "",
      baseUrl: this.$baseUrl,
    };
  },
  watch: {
    searchQuery: function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    },
  },
  created: function () {
    if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }
    if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
    }
    this.$http
      .post("location/all_company_location", {
        role: this.editor,
        onlyParent: true,
      })
      .then((resp) => {
        for (let loc of resp.data) {
          let obj = {
            label: loc.name,
            value: loc.id,
          };
          this.locations.push(obj);
        }
      });
    this.fetchData();
  },
  methods: {
    handleView(row) {
      let extension= row.split('.').pop();
      if(extension=='pdf'){
      let filenameNew =row.split(".pdf");
      axios.get(this.baseUrl + "/resources/download_uploads/" + filenameNew[0], {responseType: 'arraybuffer'}).then(res=>{
        let blob = new Blob([res.data], {type:'application/pdf'})
        let link = document.createElement('a')
        link.href = window.URL.createObjectURL(blob)
        link.download = row
        link.click();
      })
      }else{
        window.open(this.baseUrl + "/hr-forms/user-uploads/" + row, "_blank").focus();
      }
      
    },
    resetFilters() {
      this.searchQuery = "";
      this.filters.location_id = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("resources/hr_form_report", {
          search: this.searchQuery,
          page: this.currentPage,
          column: this.sortedColumn,
          per_page: this.perPage,
          company_id: this.filters.location_id,
        })
        .then((resp) => {
          this.tableData = [];
          let report_data = resp.data.report;
          let total_data = resp.data.total;
          this.totalData = total_data;
          for (let data of report_data) {
            let obj = {
              id: data.id,
              first_name: data.first_name,
              last_name: data.last_name,
              ip: data.ip,
              date: moment(data.date).format("MM-DD-YYYY"),
              company_id: data.company_id,
              location: data.location,
              file_name: data.name,
              file: data.file,
              uploaded: data.upload,
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .hrformReportGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "First Name";
  }
  .hrformReportGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Last Name";
  }
  .hrformReportGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Location";
  }
  .hrformReportGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Date";
  }
  .hrformReportGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Uploaded Hr Form";
  }
}
</style>
