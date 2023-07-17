<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6" v-if="editor === 'super-admin' || editor === 'sub-admin' || editor === 'employee' ||editor === 'company' || editor === 'manager'">
      <div>
        <card class="no-border-card" footer-classes="pt-1">
          <template slot="header">
            <div class="row  align-items-center">
              <div class="col-md-6">
                <h2 class="mb-0">Jot Forms</h2>
              </div>

               <div class="col-lg-6 col-md-6 text-right">
                <base-button v-if="canCreate" class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >
                <base-button class="custom-btn" @click.prevent="addnewlink"
                  ><i class="fa fa-plus" aria-hidden="true"></i> New
                  JotForm </base-button
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
                <label class="form-control-label">Set Order:</label>
                <el-select
                  class="select-primary"
                  v-model="filters.filterValue"
                  placeholder="Filter"
                  v-on:change="fetchData()"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in filterOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
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
            </div>
            <div class="user-eltable">
              <el-table
                :data="tableData"
                stripe
                highlight-current-row
                row-key="id"
                role="table"
                class="tutorialGrid"
                header-row-class-name="thead-light custom-thead-light"
              >
                 <el-table-column
                  min-width="40px"
                  label="Set"
                  v-if="filters.filterValue"
                >
                  <template slot-scope="">
                    <span>
                      <el-tooltip content="Drag" placement="top">
                        <i
                          class="fas fa-grip-lines"
                          data-toggle="tooltip"
                          data-original-title="Edit"
                        ></i> </el-tooltip
                    ></span>
                  </template>
                </el-table-column>
                <el-table-column
                  min-width="60px"
                  label="Sr no."
                  v-if="filters.filterValue"
                >
                  <template slot-scope="props">
                    <span>
                      {{props.row.id}}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="200px" label="id">
                  <template slot-scope="props">
                    <span>{{ props.row.id }}</span>
                  </template>
                </el-table-column>
                <el-table-column min-width="150px" label="Sharable Form Link">
                  <template slot-scope="props">
                    <span>{{ props.row.formlink }}</span>
                    
                  </template>
                </el-table-column>
               
                <el-table-column v-if="editor == 'super-admin' || editor == 'sub-admin'"  min-width="150px" label="Actions">
                  <div slot-scope="{ $index, row }" class="d-flex custom-size">
                    <el-tooltip  v-if="canEdit" content="Edit" placement="top">
                      <base-button
                        @click.native="handleEdit($index, row)"
                        class="success"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Edit"
                      >
                        <i class="text-default fa fa-pencil-square-o  "></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip v-if="canDelete" content="Delete" placement="top">
                      <base-button
                        @click.native="handleDelete($index, row)"
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
                  </div>
                </el-table-column>

              </el-table>
            </div>
          </div>
        </card>
      </div>
    </div>
    <!-- <div class="container-fluid mt--6" v-if="editor != 'super-admin' && editor != 'sub-admin'">
      <div>
        <card class="no-border-card" footer-classes="pb-2">
          <template slot="header">
            <div class="row">
              <div class="col-md-8">
                <h2 class="mb-0">Tutorial Videos</h2>
              </div>
              <div class="col-md-4">
                <p class="mb-0" style="font-weight: 600;">Please click the name of the tutorial video you’d like to watch</p>
              </div>
            </div>
          </template>
        <div class="row">
            <div class="col-md-8">
              <iframe
                :src="current_video_url"
                frameborder="0"
                allow="autoplay; encrypted-media"
                allowfullscreen
              />
            </div>
            <div class="col-md-4">
              <ul
                v-for="(data, index) in videos"
                :key="index"
                :id="'currentvideo_div_' + index"
                class="list-group"
              >
                <li v-if="index == video_index" class="list-group-item active">
                  <i class="fas fa-video"></i>
                  <span class="linkColor" @click.prevent="redirectVideo(index)">
                    &nbsp;{{ data.title }}
                  </span>
                  <i class="fa fa-clock-o" style="float: right"></i>
                </li>
                <li v-else class="list-group-item">
                  <i class="fas fa-video"></i>
                  <span class="linkColor" @click.prevent="redirectVideo(index)">
                    &nbsp;{{ data.title }}
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </card>
      </div>
    </div> -->
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
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      title: "",
      filters: {
        videoStatus: "Active",
        filterValue:""
      },
      tableData: [],
      searchQuery: "",
      duplicate: false,
      hot_user: "",
      hot_token: "",
      config: "",
      videos: [],
      timer: null,
      editor: "",
      current_video_url: "",
      video_index: 0,
      emp_id: "",
      comp_id:"",
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
          label: "Show All Tutorials",
          value: ""
        }
      ],
      filterOptions: [
        {
          label: "Company admin",
          value: "1",
        },
        {
          label: "Manager",
          value: "3",
        },
        {
          label: "Employees",
          value: "2",
        },
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
        console.log("checking")
        this.fetchData();
      }, 300);
    },
    "filters.filterValue"() {
      console.log("here");
      console.log(this.filters.filterValue);
      if (
        localStorage.getItem("hot-user") === "super-admin" &&
        this.filters.filterValue != ""
      ) {
        const tbody = document.querySelector(".el-table__body-wrapper tbody");
        const self = this;
        Sortable.create(tbody, {
          onEnd({ newIndex, oldIndex }) {
            const targetRow = self.tableData.splice(oldIndex, 1)[0];
            self.tableData.splice(newIndex, 0, targetRow);
            self.updateTutorialVideoOrder();
          },
        });
      }
    },
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
    if (this.editor === "company") {
      this.fetchData();
      // this.$http
      //   .post("employees/tutorialVideo", {
      //     role: 1
      //   })
      //   .then(resp => {
      //     let videos = resp.data;
      //     for (let data of videos) {
      //       let obj = {
      //         video: "https://player.vimeo.com/video/" + data.video,
      //         title: data.video_title,
      //         description: data.video_description
      //       };
      //       this.videos.push(obj);
      //     }
      //     if (this.videos.length > 0) {
      //       this.redirectVideo(0);
      //     }
      //   });
    }
    if (this.editor === "employee")  {
      
      this.fetchData();
      // this.$http
      //   .post("employees/tutorialVideo", {
      //     role: 2
      //   })
      //   .then(resp => {
      //     let videos = resp.data;
      //     for (let data of videos) {
      //       let obj = {
      //         video: "https://player.vimeo.com/video/" + data.video,
      //         title: data.video_title,
      //         description: data.video_description
      //       };
      //       this.videos.push(obj);
            
      //     }
      //     if (this.videos.length > 0) {
      //       this.redirectVideo(0);
      //     }
      //   });
    }
    if (this.editor === "manager") {
      // this.$http
      //   .post("employees/tutorialVideo", {
      //     role: 3
      //   })
      //   .then(resp => {
      //     let videos = resp.data;
      //     for (let data of videos) {
      //       let obj = {
      //         video: "https://player.vimeo.com/video/" + data.video,
      //         title: data.video_title,
      //         description: data.video_description
      //       };
      //       this.videos.push(obj);
      //     }
      //      if (this.videos.length > 0) {
      //       this.redirectVideo(0);
      //     }
      //   });
    }
  },
  
  methods: {
     getRightsDetails(){
       let type="Tutorial Video";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
   },
    handleEdit(index, row) {
      this.$router.push("/add_new_Jotform?id=" + row.id);
    },
    handleDelete(index, row) {
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
              "employees/deletejotform",
              {
                form_id: row.id
              },
              this.config
            )
            .then(resp => {
              this.fetchData();
              Swal.fire({
                title: "Success!",
                text: "Form  has been Deleted.",
                icon: "success",
                confirmButtonClass: "btn btn-success btn-fill",
                buttonsStyling: false
              });
            });
        }
      });
    },
    addnewlink() {
      //this.$router.push("/add_tutorial_video");
      this.$router.push("/add_new_Jotform");
    },
     redirectVideo(index) {
      if (this.videos[index]) {
        this.video_index = index;
        this.current_video_url = this.videos[index].video;
      }
    },
    fetchData() {
      console.log("fetch data working!");
      
      if (this.editor === "employee" ) {
        //console.log("iddd")
        //console.log(localStorage.getItem("hot-user-id"));
         this.emp_id =localStorage.getItem("hot-user-id");
         this.comp_id ="";

      } else if (this.editor === "company" || this.editor === "manager"){
         //console.log("hhhhhhhhhhh")
         this.emp_id ="";
         this.comp_id =localStorage.getItem("hot-user-id");
      }else{
        this.emp_id ="";
        this.comp_id ="";
      }
      //console.log(this.comp_id);

      this.loading = true;
      this.$http
        .post(
          "employees/getjotform",
          {
            company_id: this.comp_id,
            employee_id:this.emp_id,
            search: this.searchQuery
          },
          this.config
        )
        .then(resp => {
          this.tableData = [];
          for (let vid of resp.data) {
            let obj = {
              id: vid.id,
              formlink: vid.formlink
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
      this.filters.filterValue = "";
      this.fetchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_tutorial_video_search_data",
        JSON.stringify({
          search: this.searchQuery,
          video_status: this.filters.videoStatus,
          filter_interface: this.filters.filterValue,
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
           this.filters.filterValue = previousStateData.filter_interface
          ? previousStateData.filter_interface
          : this.filters.filterValue;
      }
      this.fetchData();
    },
  }
};
</script>
<style scoped>
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

.list-group-item.active {
  background-color: #0b427b !important;
}

.list-group-item {
  padding: 8px 10px;
  font-size: 12px !important;
  border: 0px !important;
  color: #fff !important;
  background-color: #80d610;
  border-radius: 0px !important;
}
.list-group-item.current_active {
  background-color: #13569a !important;
}
.list-group-item .linkColor {
  color: white;
}

.show {
  display: block;
}
.hide {
  display: none;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .tutorialGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Role";
  }
  .tutorialGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Video Name";
  }
  .tutorialGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Video";
  }
  .tutorialGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Status";
  }
  .tutorialGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Action";
  }
}
</style>
