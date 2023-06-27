<script>
import { Option, Select, Table, TableColumn } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import "sweetalert2/src/sweetalert2.scss";
import XLSX from "xlsx";
let timeout = null;
export default {
  name: "promo-code",
  mixins: [serverSidePaginationMixin],
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      loading: false,
      searchQuery: "",
      filters: {
        company: "",
      },
      tableData: [],
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
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    this.setDefaultFilterData();
  },
  methods: {
    fetchData() {
      /*To get all the promocodes*/
      this.loading = true;
      this.$http
        .post("/promocode/get_promo_code_reports", {
          search: this.searchQuery,
          page: this.currentPage,
          per_page: this.perPage,
          column: this.sortedColumn,
          order: this.order,
          status: this.filters.status,
        })
        .then((response) => {
          this.tableData = [];
          let promocodeReports = response.data.data;
          this.totalData = response.data.total;
          for (let promocodeReport of promocodeReports) {
            let obj = {
              id: promocodeReport.id,
              first_name: promocodeReport.first_name,
              last_name: promocodeReport.last_name,
              promo_code: promocodeReport.promocode,
              actual_cost: promocodeReport.total_amount,
              amount_paid: promocodeReport.amount_paid,
            };

            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    downloadExcel() {
      this.loading = true;
      this.$http
        .post("/promocode/get_promo_code_reports", {
          search: this.searchQuery,
          page: this.currentPage,
          per_page: this.perPage,
          column: this.sortedColumn,
          order: this.order,
          status: this.filters.status,
        })
        .then((resp) => {
          this.items = resp.data.download;
          const data1 = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data1, "data");
          XLSX.writeFile(wb, "Promocode.xlsx");
        })
        .finally(() => (this.loading = false));
    },
    resetFilters() {
      this.searchQuery = "";
      this.filters.status = "Active";
      this.fetchData();
    },

    saveSearchData() {
      localStorage.setItem(
        "promocode-filters",
        JSON.stringify({
          role: "super-admin",
          search: this.searchQuery,
          status: this.filters.status,
          page: this.currentPage,
          per_page: this.perPage,
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("promocode-filters")
      );
      if (previousStateData !== null) {
        this.searchQuery =
          previousStateData.search != undefined
            ? previousStateData.search
            : this.searchQuery;
        this.filters.status =
          previousStateData.status != undefined
            ? previousStateData.status
            : this.filters.status;
        this.perPage =
          previousStateData.per_page != undefined
            ? previousStateData.per_page
            : this.perPage;
      }

      this.fetchData();
    },
  },
};
</script>
<template>
  <div>
    <div class="content">
      <div class="container-fluid mt-3">
        <card
          class="no-border-card"
          footer-classes="pb-2"
          v-loading.fullscreen.lock="loading"
        >
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-lg-6 col-md-6 col-12">
                <h2 class="mb-0">Promo Code Report</h2>
              </div>
              <div class="col-lg-6 col-md-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i aria-hidden="true" class="fa fa-refresh"></i> Clear
                  Filters</base-button
                >

                <base-button class="custom-btn" v-on:click="downloadExcel()"
                  >Download Excel</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div class="row flex-wrap">
              <div class="col-md">
                <base-input
                  label="Search:"
                  placeholder="Search..."
                  prepend-icon="fas fa-search"
                  v-model="searchQuery"
                ></base-input>
              </div>
              <div class="col-md"></div>
              <div class="col-md">
                <base-input label="Showing:">
                  <el-select
                    class="select-primary pagination-select"
                    placeholder="Per page"
                    v-model="perPage"
                    v-on:change="changePage(1)"
                  >
                    <el-option
                      :key="item"
                      :label="item"
                      :value="item"
                      class="select-primary"
                      v-for="item in perPageOptions"
                    ></el-option>
                  </el-select>
                </base-input>
              </div>
            </div>
            <div class="user-eltable">
              <el-table
                :data="tableData"
                class="compGrid"
                header-row-class-name="thead-light"
                highlight-current-row
                id="tbl1"
                role="table"
                row-key="id"
                stripe
              >
                <el-table-column min-width="150px" prop="first_name">
                  <template slot="header">
                    <span @click="sortByColumn(0)"
                      >First Name
                      <i
                        v-if="sortedColumn == 0 && order === 'asc'"
                        class="fas fa-arrow-up text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span> {{ props.row.first_name }}</span>
                  </template></el-table-column
                >

                <el-table-column min-width="150px" prop="last_name">
                  <template slot="header">
                    <span @click="sortByColumn(1)"
                      >Last Name
                      <i
                        v-if="sortedColumn == 1 && order === 'asc'"
                        class="fas fa-arrow-up text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.last_name }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Promo Code"
                  min-width="150px"
                  prop="promo_code"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.promo_code }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Course Cost"
                  min-width="150px"
                  prop="actual_cost"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.actual_cost }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Paid Cost"
                  min-width="150px"
                  prop="amount_paid"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.amount_paid }}</span>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
          <div class="d-flex justify-content-end" slot="footer">
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
                      :class="{ disabled: currentPage === 1 }"
                      class="page-item"
                    >
                      <a
                        @click.prevent="changePage(currentPage - 1)"
                        class="page-link"
                        href="#"
                        ><i class="fa fa-caret-left"></i
                      ></a>
                    </li>
                    <li
                      :class="{ active: page == currentPage }"
                      class="page-item"
                      v-bind:key="index"
                      v-for="(page, index) in pagesNumber"
                    >
                      <a
                        @click.prevent="changePage(page)"
                        class="page-link"
                        href="javascript:void(0)"
                        >{{ page }}</a
                      >
                    </li>
                    <li
                      :class="{ disabled: currentPage === last_page }"
                      class="page-item"
                    >
                      <a
                        @click.prevent="changePage(currentPage + 1)"
                        class="page-link"
                        href="#"
                        ><i class="fa fa-caret-right"></i
                      ></a>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>
          </div>
        </card>
      </div>
    </div>
  </div>
</template>
