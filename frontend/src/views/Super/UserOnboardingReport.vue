<template>
        <div>
         
          <div>
            
            <div class="row align-items-center mb-2">
              <!-- <div class="col-md-6 col-lg-6">
                <h2 class="mb-0">User Onboarding Report</h2>
              </div> -->
              <div class="col-lg-6 col-md-6 text-right">
              </div>
              <div class="col-lg-6 col-md-6 text-right">
               
                <base-button
                  class="custom-btn"
                  v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
<!--                <base-button class="custom-btn" v-on:click="exportExcel()">Excel Download</base-button-->
<!--                >  -->
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
                    name="Company Filter"
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
                class="onboardingReportGrid table-striped"
              >
                <el-table-column min-width="180px" prop="first_name">
                  <template slot="header">
                    <span>FIRST NAME</span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.first_name }}
                  </template>
                </el-table-column>

                <el-table-column min-width="180px" prop="last_name">
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
                <el-table-column min-width="120px" prop="date">
                  <template slot="header">
                    <span> DATE </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.date }}
                  </template>
                </el-table-column>

                <el-table-column min-width="120px" prop="ip">
                  <template slot="header">
                    <span> IP </span>
                  </template>
                  <template slot-scope="props">
                    {{ props.row.ip }}
                  </template>
                </el-table-column>

                <el-table-column
                  min-width="120px"
                  align="left"
                  label="Action"
                  class="table-custom-size"
                >
                  <template slot-scope="props">
                    <div class="row">
                      <div class="col-md-2">
                        <a
                          target="_blank"
                          class="linkColor"
                          title="View document"
                          style="color: white"
                          @click="disaplayUserOnboarding(props)"
                          ><i
                            class="text-warning fa fa-eye"
                          ></i>
                        </a>
                      </div>
                    </div> </template
                ></el-table-column>
              </el-table>
            </div>
          </div>
          <div v-if="viewPdf">
            <!-- The pdfCounter is binded to key to reload this component addedbysaksham -->
            <user-onboarding-document-pdf
              :userOnboardingDataRow="userOnboardingInfoModal.row"
              :key="pdfCcounter"
            ></user-onboarding-document-pdf>
          </div>
          <!-- User Details Model addedbysaksham-->

          <modal
            :show.sync="userOnboardingInfoModal.isUserOnboardinfoModalDisplayed"
          >
            <template v-slot:header>
              <h6 class="modal-title">User Onboarding Details</h6>
            </template>

            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">First Name </label>
              </div>
              <div class="col-7">
                <base-input
                  rules="required"
                  name="First Name"
                  placeholder="First Name"
                  disabled
                  :value="userOnboardingInfoModal.row.first_name"
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">Last Name </label>
              </div>
              <div class="col-7">
                <base-input
                  name="Last Name"
                  placeholder="Last Name"
                  :value="userOnboardingInfoModal.row.last_name"
                  disabled
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">Location </label>
              </div>
              <div class="col-7">
                <base-input
                  name="Location"
                  placeholder="Location"
                  disabled
                  :value="userOnboardingInfoModal.row.location"
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">Date </label>
              </div>
              <div class="col-7">
                <base-input
                  name="date"
                  placeholder="Date"
                  :value="userOnboardingInfoModal.row.date"
                  disabled
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">IP </label>
              </div>
              <div class="col-7">
                <base-input
                  name="IP"
                  placeholder="IP"
                  :value="userOnboardingInfoModal.row.ip"
                  disabled
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-5 col-md-3">
                <label class="form-control-label">Signature </label>
              </div>
              <div class="col-8">
                <img
                  :src="userOnboardingInfoModal.row.signature"
                  class="signImg"
                />
              </div>
            </div>

            <template v-slot:footer>
              <base-button type="primary" @click="generatePdfDocument"
                >Download PDF</base-button
              >
              <base-button type="link" class="ml-auto" @click="closeModal"
                >Close</base-button
              >
            </template>
          </modal>

          <!--  -->

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
</template>
<script>
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import XLSX from "xlsx";
import moment from "moment";
import UserOnboardingDocumentPdf from "@/views/Super/UserOnboardingDocumentPdf.vue";
let timeout = null;
export default {
  name: "user-onboarding-report",
  mixins: [serverSidePaginationMixin],
  components: {
    UserOnboardingDocumentPdf,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    [DatePicker.name]: DatePicker,
  },
  data() {
    return {
      pdfCcounter: 0,
      userOnboardingInfoModal: {
        isUserOnboardinfoModalDisplayed: false,
        row: {},
      },
      loading: false,
      isUserOnboardinfoModalDisplayed: false,
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
      viewPdf: false,
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
    closeModal() {
      this.userOnboardingInfoModal.isUserOnboardinfoModalDisplayed = false;
      this.userOnboardingInfoModal.row = {};
    },
    disaplayUserOnboarding(val) {
      this.userOnboardingInfoModal.isUserOnboardinfoModalDisplayed = true;
      this.userOnboardingInfoModal.row = { ...val.row };
    },
    resetFilters() {
      this.searchQuery = "";
      this.filters.location_id = "";
      this.fetchData();
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("company/user_onboarding_report", {
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
              document_id: data.document_id,
              first_name: data.first_name,
              last_name: data.last_name,
              ip: data.ip,
              date: moment(data.date).format("MM-DD-YYYY"),
              company_id: data.company_id,
              location: data.location,
              signature: data.signature,
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
    },
    generatePdfDocument() {
      this.pdfCcounter++;
      this.viewPdf = true;
    },
  },
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}

img.signImg {
    max-width: 100%;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "First Name";
  }
  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Last Name";
  }
  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Location";
  }
  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Date";
  }

  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Ip";
  }
  .onboardingReportGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Signature";
  }
}
</style>

