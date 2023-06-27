<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7">
          <h3 class="text-white d-inline-block mb-0">
            <i class="ni ni-laptop" style="margin-right:10px;"></i>
            Course Catelog
          </h3>
          <!--  <h6 class="h2 text-white d-inline-block mb-0">Train 321 Courses Catalog</h6>
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <route-bread-crumb></route-bread-crumb>
          </nav>-->
        </div>
        <div class="col-lg-6 col-5 text-right">
          <!-- <base-button size="sm" type="neutral" @click.prevent="createVideo" >Create New Video</base-button>-->
        </div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card
          class="no-border-card"
          body-classes="px-0 pb-1"
          footer-classes="pb-2"
        >
          <div class="row mb-5">
            <div class="col-md-4 mt-5" v-for="(course) in courses" :key="course.course_id">
              <div
                class="article1 mx-auto"
                v-if="!companyCourse.includes(course.course_id)"
              >
                <router-link :to="'/course_details?id=' + course.course_id"
                  ><div class="art">
                    <div class="row article_title ">
                      <div class="col-md-12 my-auto text-center">
                        <span>{{ course.courseName }}</span
                        ><br />
                        <span>{{ course.coursePrice }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="btn_div" style="width: 100%">
                    <div class="col-md-12 my-auto text-center">
                      <span style="width: 100%"><b>BUY NOW</b></span>
                    </div>
                  </div></router-link
                >
              </div>
              <div
                class="article1 mx-auto"
                v-on:click="alreadyPurshaed"
                v-if="companyCourse.includes(course.course_id)"
              >
                <div class="art">
                  <div class="row article_title ">
                    <div class="col-md-12 my-auto text-center">
                      <span>{{ course.courseName }}</span
                      ><br />
                      <span>{{ course.coursePrice }}</span>
                    </div>
                  </div>
                </div>
                <div class="btn_div_dup" style="width: 100%">
                  <div class="col-md-12 my-auto text-center">
                    <span style="width: 100%"><b>Purchased</b></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </card>
      </div>
    </div>
  </div></template
>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";

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
      hot_user: "",
      hot_token: "",
      company_id: "",

      config: "",
      courses: [],
      companyCourse: []
    };
  },
  created: function() {
    this.isLoading = true;
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.company_id = localStorage.getItem("hot-user-id");
    }
    this.config = {
      headers: {
        Authorization: this.hot_token
      }
    };

    this.$http
      .post(
        "course/all_courses",
        {
          course_status: "All"
        },
        this.config
      )
      .then(resp => {
        for (let course of resp.data) {
          let obj = {
            courseName: course.course_name,
            course_id: course.id,
            coursePrice: course.course_cost
          };
          this.courses.push(obj);
          this.$http.get("company/courses/" + this.company_id).then(resp => {
            let courses = resp.data[0].courses;
            for (let course of courses) {
              this.companyCourse.push(course.id);
            }
          });
        }
      });
  },
  methods: {
    alreadyPurshaed() {
      Swal.fire({
        title: "Error!",
        text: "Already purchased!",
        icon: "error",
        confirmButtonClass: "btn btn-success btn-fill",
        buttonsStyling: true
      });
    }
  }
};
</script>
<style scoped>
a {
  text-decoration: none;
}
.article1 {
  border: 1px solid black;
  height: 250px;
  max-width: 250px;
  margin: 0;
}
.article1 a:hover {
  text-decoration: none;
}
.article1:hover {
  cursor: pointer;
}
.btn_div {
  min-width: 100%;
  background-color: #0b427b;
  height: 50px;
  padding-top: 10px;
}
.btn_div_dup {
  min-width: 100%;
  background-color: #7c7975;
  height: 50px;
  padding-top: 10px;
}
.btn_div span {
  padding-top: 20px;
  color: white;
  height: 50px;
  vertical-align: middle;
}
.btn_div_dup span {
  padding-top: 20px;
  color: white;
  height: 50px;
  vertical-align: middle;
}
.art {
  height: 200px;
}
.article_title {
  width: 100%;
  margin: 0 auto;
  height: 100%;
  font-size: 18px;
  color: rgba(0, 0, 0, 0.5);
  font-weight: bold;
}
.article_title :hover {
  /*-webkit-transform: scale(1.1);*/
  /*-ms-transform: scale(1.1);*/
  /*transform: scale(1.1);*/
}
.article_title span {
  position: relative;
  /*top: 20%;*/
  /*background-color: #b7b7b7;*/
}
</style>
