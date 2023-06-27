<script>
import { Option, Select, Table, TableColumn } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import "sweetalert2/src/sweetalert2.scss";
import Swal from "sweetalert2/dist/sweetalert2.js";
import moment from "moment";
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
        status: "Active",
      },
      status: [
        {
          label: "Active",
          value: "Active",
        },
        {
          label: "Inactive",
          value: "Inactive",
        },
        {
          label: "Show All",
          value: "",
        },
      ],
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
        .post("/promocode/get_promo_codes", {
          search: this.searchQuery,
          page: this.currentPage,
          per_page: this.perPage,
          status: this.filters.status,
        })
        .then((response) => {
          this.tableData = [];
          let promocodes = response.data.data;
          this.totalData = response.data.total;
          for (let promocode of promocodes) {
            let obj = {
              id: promocode.id,
              name: promocode.name,
              percentage: promocode.percentage,
              description: promocode.description,
              created_at: moment(promocode.created_at).format("MM-DD-YYYY"),
              valid_upto: moment(promocode.validity).format("MM-DD-YYYY"),
              status: true,
            };
            if (promocode.status === 1) {
              obj.status = true;
            } else if (promocode.status === 0) {
              obj.status = false;
            } else {
              obj.status = promocode.status;
            }
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.searchQuery = "";
      this.filters.status = "Active";
      this.fetchData();
    },
    deletePromoCode(id) {
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false,
      })
        .then((result) => {
          if (result.value) {
            this.$http
              .delete("promocode/delete_promo_code/" + id)
              .then((resp) => {
                this.fetchData();
                Swal.fire({
                  title: "Deleted!",
                  text: "Promo Code has been deleted.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false,
                }).then(function () {});
              });
          }
        })
        .catch(function () {});
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

    changeStatus(index, row) {
      let prev_val = row.status;
      let status = "";
      if (prev_val) {
        status = 0;
      } else {
        status = 1;
      }
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: "You want to change status!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false,
      })
        .then((result) => {
          if (result.value) {
            this.$http
              .put(
                "/promocode/update_status/" + row.id,
                {
                  status: status,
                },
                self.config
              )
              .then((resp) => {
                this.fetchData();
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false,
                });
                self.tableData[index].status = !prev_val;
              });
          } else {
            self.tableData[index].status = prev_val;
          }
        })
        .catch(function () {
          self.tableData[index].status = prev_val;
        });
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
                <h2 class="mb-0">Promo Codes</h2>
              </div>
              <div class="col-lg-6 col-md-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i aria-hidden="true" class="fa fa-refresh"></i> Clear
                  Filters</base-button
                >
                <router-link to="/create_promo_code">
                  <base-button class="custom-btn"
                    ><i aria-hidden="true" class="fa fa-plus"></i> Add Promo
                    Code</base-button
                  >
                </router-link>
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
              <div class="col-md">
                <base-input label="Status:">
                  <el-select
                    class="select-primary"
                    v-on:change="changePage(1)"
                    v-model="filters.status"
                    placeholder="Filter by Company Status"
                  >
                    <el-option
                      class="select-primary"
                      v-for="item in status"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
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
                <el-table-column label="Name" min-width="150px" prop="name">
                  <template slot-scope="props">
                    <span> {{ props.row.name }}</span>
                  </template></el-table-column
                >
                <el-table-column
                  label="Percentage(%)"
                  min-width="100px"
                  prop="percentage"
                >
                  <template slot-scope="props">
                    <span> {{ props.row.percentage }}%</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Description"
                  min-width="150px"
                  prop="description"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.description }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Created On"
                  min-width="100px"
                  prop="created_at"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.created_at }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="Valid Until"
                  min-width="100px"
                  prop="valid_upto"
                >
                  <template slot-scope="props">
                    <span>{{ props.row.valid_upto }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="Status" min-width="100px" prop="status">
                  <template slot-scope="props">
                    <div
                      class="d-flex"
                      v-on:click="changeStatus(props.$index, props.row)"
                    >
                      <base-switch
                        class="mr-1"
                        type="success"
                        v-if="props.row.status"
                        v-model="props.row.status"
                      ></base-switch>
                      <base-switch
                        class="mr-1"
                        type="danger"
                        v-else
                        v-model="props.row.status"
                      ></base-switch>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column align="left" label="Actions" min-width="100px">
                  <div class="d-flex custom-size" slot-scope="{ row }">
                    <el-tooltip content="Edit" placement="top">
                      <router-link :to="'/create_promo_code?id=' + row.id">
                        <base-button
                          class="success"
                          type=""
                          size="sm"
                          icon
                          data-toggle="tooltip"
                          data-original-title="Edit"
                        >
                          <i class="text-default fa fa-pencil-square-o"></i>
                        </base-button>
                      </router-link>
                    </el-tooltip>
                    <el-tooltip content="Delete" placement="top">
                      <base-button
                        @click.native="deletePromoCode(row.id)"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                      >
                        <i class="text-danger fa fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                  </div>
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
