<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6" v-if="editor === 'super-admin' || editor === 'sub-admin'">
      <div>
        <card class="no-border-card" footer-classes="pt-1">
          <template slot="header">
            <div class="row  align-items-center">
             <div class="col-md-6 col-lg-6 ">
                
                <h2 class="mb-0">Tours Management</h2>
              </div>

              <div class="col-lg-6 col-md-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                <base-button v-if="canCreate" class="custom-btn" @click.prevent="createVideo"
                  ><i class="fa fa-plus" aria-hidden="true"></i> New
                  Video</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-5">
                <label class="form-control-label">Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <div class="col-md-3 form-group">
                <label class="form-control-label">Status:</label>
                <el-select
                  class="select-primary"
                  v-model="filters.videoStatus"
                  placeholder="Filter by Folder Status"
                  v-on:change="fetchData()"
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
              </div>
              <div class="col-md-3">
                <base-input label="Showing:">
                    <el-select class="select-primary pagination-select" v-model="perPage" v-on:change="changePage(1)" placeholder="Per page">
                        <el-option class="select-primary" v-for="item in perPageOptions" :key="item" :label="item" :value="item"></el-option>
                    </el-select>
                </base-input>
              </div>
            </div>
            <div class="user-eltable vediotable">
              <el-table
                :data="tableData"
                stripe
                highlight-current-row
                row-key="id"
                role="table"
                class="tourGrid"
                header-row-class-name="thead-light custom-thead-light"
              >
                <el-table-column min-width="80px" label="Order">
                  <template slot="header">
                    <span @click="sortByColumn(0)"
                      >Order
                      <i
                        v-if="sortedColumn == 0 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.order }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Thumbnail">
                  <template slot-scope="props">
                    <iframe
                      class="text-center"
                      style=" width: 180px; height: 120px;"
                      :src="props.row.ThumbnailDemo"
                      width="150"
                      height="150"
                      webkitallowfullscreen=""
                      mozallowfullscreen=""
                      allowfullscreen=""
                    ></iframe>
                  </template>
                </el-table-column>
                <el-table-column min-width="200px" prop="name">
                  <template slot="header">
                    <span @click="sortByColumn(1)"
                      >Name
                      <i
                        v-if="sortedColumn == 1 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.name }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="120px" label="Video">
                  <template slot-scope="props">
                    <span>{{ props.row.Video_id }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="URL">
                  <template slot-scope="props">
                    <span ref="copyUrl">{{ props.row.url }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="80px" label="">
                  <template slot="header">
                    <span @click="sortByColumn(2)"
                      >Type
                      <i
                        v-if="sortedColumn == 2 && order === 'asc'"
                        class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                        v-else
                        class="fas fa-arrow-down
                    text-blue linkColor"
                      />
                    </span>
                  </template>
                  <template slot-scope="props">
                    <span>{{ props.row.type }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="80px" label="Status">
                  <template slot-scope="props">
                    <span v-if="props.row.status">Active</span>
                    <span v-else>Inactive</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="140px" label="Action">
                  <template slot-scope="props">
                    <el-tooltip  v-if="canEdit" content="Edit" placement="top">
                      <base-button
                        @click.native="handleEdit(props.row.id)"
                        class="edit"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Edit"
                      >
                        <i class="text-default fa fa-pencil-square-o"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip v-if="canDelete" content="Delete" placement="top">
                      <base-button
                        @click.native="handleDelete(props.row.id)"
                        class="delete"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                      >
                        <i class="text-danger fas fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip content="Copy" placement="top">
                      <base-button
                        @click.native="copyclipboard(props.row.url)"
                        class="delete"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                      >
                        <i class="text-danger  far fa-copy"></i>
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
                                class="pagination custompagination justify-content-end align-items-center">
                                <p class="p-0 m-0 mr-2">
                                    Showing {{ tableData.length }} of {{ totalData }} entries
                                </p>
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                                        <i class="fa fa-caret-left"></i>
                                    </a>
                                </li>
                                <li v-for="(page, index) in pagesNumber" class="page-item" :class="{ active: page == currentPage }" v-bind:key="index">
                                    <a href="javascript:void(0)" @click.prevent="changePage(page)" class="page-link">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === last_page}">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                                        <i class="fa fa-caret-right"></i>
                                    </a>
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

<script>
import Vue from "vue";
import Sortable from "sortablejs";
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    Sortable,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      tempUrl: "",
      loading: false,
      perPage: 10,
      currentPage: 1,
      sortBy: "title",
      sortDesc: false,
      viewCertificateModal: false,
      title: "",
      filters: {
        videoStatus: "Active"
      },
      selectedRows: [],
      tableData: [],
      searchQuery: "",
      duplicate: false,
      hot_user: "",
      hot_token: "",
      config: "",
      videos: [],
      timer: null,
      currentIndex: 0,
      editor: "",
      item_id: 0,
      status: [
        {
          label: "Active",
          value: "Active"
        },
        {
          label: "Inactive",
          value: "Inactive"
        },
        {
          label: "Show All Tour",
          value: ""
        }
      ],
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
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }

    if (localStorage.getItem("hot-user") === "employee") {
      this.editor = "employee";
    } else if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
      this.getRightsDetails();
     } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "company";
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
    }
    if (this.editor === "super-admin" || this.editor === "sub-admin") {
      this.setDefaultFilterData();
    }
  },
  mounted() {
    const tbody = document.querySelector(".el-table__body-wrapper tbody");
    const self = this;
    Sortable.create(tbody, {
      onEnd({ newIndex, oldIndex }) {
        const targetRow = self.tableData.splice(oldIndex, 1)[0];
        self.tableData.splice(newIndex, 0, targetRow);
        self.updateTourVideoOrder();
      }
    });
  },
  computed: {
    rows() {
      return this.tableData.length;
    }
  },
  methods: {
     getRightsDetails(){
       let type="Tour";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
    },

    updateTourVideoOrder() {
      this.$http
        .post(
          "employees/updateTourVideoOrder",
          {
            data: this.tableData
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];

          for (let vid of resp.data) {
            let obj = {
              id: vid.id,
              order: vid.order,
              ThumbnailDemo:
                "https://player.vimeo.com/video/" + vid.vimeo_video_id,
              name: vid.name,
              Video_id: vid.vimeo_video_id,
              url: "https://lms.homeoftraining.com/#/tour?id=" + vid.tour_id,
              type: vid.type,
              status: vid.status
            };
            this.tableData.push(obj);
          }
        });
    },
    copyclipboard(url) {
      const el = document.createElement("textarea");
      el.value = url;
      el.setAttribute("readonly", "");
      el.style.position = "absolute";
      el.style.left = "-9999px";
      document.body.appendChild(el);
      const selected =
        document.getSelection().rangeCount > 0
          ? document.getSelection().getRangeAt(0)
          : false;
      el.select();
      document.execCommand("copy");
      document.body.removeChild(el);
      if (selected) {
        document.getSelection().removeAllRanges();
        document.getSelection().addRange(selected);
      }
    },
    startSlide: function() {
      this.timer = setInterval(this.next, 4000);
    },
    next: function() {
      this.currentIndex += 1;
    },
    prev: function() {
      this.currentIndex -= 1;
    },
    createVideo() {
      this.$router.push("/add_tour_video");
    },
    handleEdit(rowid) {
      this.$router.push("/add_tour_video?id=" + rowid);
    },
    handleDelete(id) {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Yes",
        cancelButtonText: "No"
      }).then(result => {
        if (result.value) {
          this.$http
            .post(
              "employees/destroyTourVideo",
              {
                id: id
              },
              this.config
            )
            .then(resp => {
              this.fetchData();
              Swal.fire({
                title: "Success!",
                text: "Video has been Deleted.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            });
        }
      });
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
        buttonsStyling: false
      })
        .then(result => {
          if (result.value) {
            self.$http
              .put(
                "/employees/update_video_status/" + row.id,
                {
                  status: status
                },
                self.config
              )
              .then(resp => {
                this.fetchData();
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
                self.tableData[index].status = !prev_val;
              });
          } else {
            self.tableData[index].status = prev_val;
          }
        })
        .catch(function() {
          self.tableData[index].status = prev_val;
        });
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post(
          "employees/getDemoTourVideo",
          {
            video_status: this.filters.videoStatus,
            search: this.searchQuery,
            column: this.sortedColumn,
            order: this.order,
            per_page: this.perPage,
            page: this.currentPage
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];
          this.totalData = resp.data.total;
          for (let vid of resp.data.data) {
            let obj = {
              id: vid.id,
              order: vid.order,
              ThumbnailDemo:
                "https://player.vimeo.com/video/" + vid.vimeo_video_id,
              name: vid.name,
              Video_id: vid.vimeo_video_id,
              url: "https://lms.homeoftraining.com/#/tour?id=" + vid.tour_id,
              type: vid.type,
              status: vid.status
            };
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.searchQuery = "";
      this.filters.videoStatus = "";
      this.fetchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_tutorial_video_search_data",
        JSON.stringify({
          search: this.searchQuery,
          video_status: this.filters.videoStatus
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_tutorial_video_search_data")
      );

      if (previousStateData !== null) {
        this.searchQuery = previousStateData.search
          ? previousStateData.search
          : this.searchQuery;
        this.filters.videoStatus = previousStateData.video_status
          ? previousStateData.video_status
          : this.filters.videoStatus;
      }
      this.fetchData();
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
    }
  }
};
</script>
<style>
table {
  width: 0px !important;
}
.fade-leave-active {
  transition: all 0.9s ease;
  overflow: hidden;
  visibility: visible;
  position: absolute;
  width: 100%;
  opacity: 1;
}

.fade-enter,
.fade-leave-to {
  visibility: hidden;
  width: 100%;
  opacity: 0;
}

iframe {
  height: 400px;
  width: 100%;
}

.prev,
.next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  padding: 16px;
  color: white;
  font-weight: bold;
  font-size: 18px;
  transition: 0.7s ease;
  border-radius: 0 4px 4px 0;
  text-decoration: none;
  user-select: none;
  background-color: rgba(0, 0, 0, 0.9);
}

.next {
  right: 0;
}

.prev {
  left: 0;
}

.prev:hover,
.next:hover {
  background-color: rgba(0, 0, 0, 0.9);
}

.no-border-card .card-footer {
  border-top: 0;
}

.custom-size .btn-sm {
  padding: 2px !important;
  font-size: 16px !important;
}

.rounded-circle {
  border-radius: none !important;
}
table {
  width: 0px !important;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .tourGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Sl no.";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Thumbnail";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Name";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Video";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Url";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Type";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Status";
  }
  .tourGrid >>> table.el-table__body td:nth-of-type(8):before {
    content: "Action";
  }

  .vediotable .el-table__body tr td:nth-child(2) {
    height: 130px;
  }
}
</style>
