<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-md-6 text-left">
                <h2 class="mb-0">{{ course.course_name }}</h2>
              </div>
              <div class="col-md-6 text-right hideOnMobileView">
                <router-link
                  v-if="'interfaces'"
                  :to="
                    '/lesson_form?id=' + course_id + '&interface=' + interfaces
                  "
                  ><base-button class="custom-btn"
                    >Take Course</base-button
                  ></router-link
                >
                <router-link v-else :to="'/lesson_form?id=' + course_id"
                  ><base-button class="custom-btn"
                    >Take Course</base-button
                  ></router-link
                >
              </div>
            </div>
          </template>

          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <span><b>Length: </b></span>
                <span style="color:#28c0e7"
                  ><i class="fas fa-clock mt-2 state_icon mr-2"></i
                  >{{ course.course_length }} minutes</span
                >
              </div>
              <div>
                <div
                  class="text-justify course-disc"
                  v-html="course.course_description"
                ></div>
              </div>
              <div>
                <router-link
                  v-if="'interfaces'"
                  :to="
                    '/lesson_form?id=' + course_id + '&interface=' + interfaces
                  "
                  ><base-button class="custom-btn"
                    >Take Course</base-button
                  ></router-link
                >
                <router-link v-else :to="'/lesson_form?id=' + course_id"
                  ><base-button class="custom-btn"
                    >Take Course</base-button
                  ></router-link
                >
              </div>
            </div>
            <div class="col-md-4 ">
              <div
                class="hideOnMobileView"
                v-if="
                  course.courseResources.length > 0 &&
                    check_availability == true
                "
              >
                <div class="card resources-card">
                  <div class="card-header">
                    <h5 class="h3 mb-0">Resources</h5>
                  </div>
                  <div class="card-body">
                    <span
                      v-for="resource in course.courseResources"
                      :key="resource.id"
                    >
                      <div
                        class="row"
                        v-if="
                          resource.status == 1 &&
                            resource.available_after_course_completion == 0
                        "
                      >
                        <ul>
                          <li>
                            <span v-if="resource.type == 'link'"
                              ><a target="__blank" :href="resource.url">{{
                                resource.name
                              }}</a></span
                            >
                            <span v-if="resource.type == 'file'"
                              ><a
                                :download="resource.file_name"
                                target="__blank"
                                :href="resource.url"
                                >{{ resource.file_name }}</a
                              ></span
                            >
                          </li>
                        </ul>
                        <br />
                      </div>
                    </span>
                  </div>
                </div>
              </div>
              <div class="progress-status">
                <h4 class="mb-4">Progress:</h4>
                <div
                  class="scroll-timeline timeline timeline-one-side"
                  data-timeline-content="axis"
                  data-timeline-axis-style="dashed"
                >
                  <div
                    v-for="(test, index) in tests"
                    :key="index"
                    class="timeline-block"
                  >
                    <span
                      class="timeline-step"
                      style="cursor:pointer;"
                      v-if="test.status == 1"
                      @click.prevent="redirectLesson(test.id, test.type)"
                      :class="`badge-success`"
                    >
                      <i class="ni ni-check-bold"></i>
                    </span>
                    <span class="timeline-step" v-else :class="`badge-danger`">
                      <i class="ni ni-books"></i>
                    </span>
                    <div class="timeline-content progress-sec">
                      <div class="d-flex justify-content-between pt-1">
                        <div>
                          <span
                            class="text-muted  lesson-progress"
                            v-if="test.status == 1"
                            style="cursor:pointer;"
                            @click.prevent="redirectLesson(test.id, test.type)"
                          >
                            <i
                              class="fab fa-youtube"
                              v-if="test.lesson_type == 'video'"
                            ></i>
                            <i
                              class="fab fa-youtube"
                              v-if="test.lesson_type == 'youtube-video'"
                            ></i>
                            <i
                              class="fa fa-file-pdf-o"
                              v-else-if="test.lesson_type == 'pdf'"
                            ></i>
                            {{ test.name ? test.name : "Test" }}</span
                          >
                          <span class="text-muted  lesson-progress" v-else>
                            <i
                              class="fab fa-youtube"
                              v-if="test.lesson_type == 'video'"
                            ></i>
                            <i
                              class="fab fa-youtube"
                              v-if="test.lesson_type == 'youtube-video'"
                            ></i>
                            <i
                              class="fa fa-file-pdf-o"
                              v-else-if="test.lesson_type == 'pdf'"
                            ></i>
                            {{ test.name ? test.name : "Test" }}</span
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4"></div>
          </div>
          <div class="row mt-2">
            <div class="col-md-7"></div>
          </div>
          <div class="row" style="text-align:right;">
            <div class="col-md-5">
              <!-- Timeline card -->
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
import "vue-step-progress/dist/main.css";
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
      due_date: "",
      config: "",
      course_id: "",
      interfaces: "",
      mySteps: [],
      activeThickness: 2,
      passiveThickness: 2,
      activeColor: "#11cdef",
      passiveColor: "#172b4d",
      currentStep: 0,
      lineThickness: 2,
      tests: [],
      redirect_id: "",
      course: {
        live: true,
        status: true,
        course_name: "",
        course_length: "",
        allowed_attempts: "",
        course_cost: "",
        course_description: "",
        course_resources: [],
        courseResources: [],
        course_lessons: [],
        course_test: [],
        course_tests: [],
        course_certificate: [],
        assigned_companies_id: [],
        user_id: ""
      },
      check_availability: false
    };
  },
  created() {
    this.isLoading = true;
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.user_id = localStorage.getItem("hot-user-id");
    }

    if (this.$route.query.due_date) {
      this.due_date = this.$route.query.due_date;
    }
    if (this.$route.query.interface) {
      this.interfaces = "company";
    }

    if (this.$route.query.id) {
      this.course_id = this.$route.query.id;
      this.$http
        .get("course/edit/" + this.course_id, this.config)
        .then(resp => {
          let data = resp.data[0];
          let course_obj = {
            live: "",
            status: "",
            course_name: data.name,
            course_length: data.length,
            allowed_attempts: data.allow_attempts,
            course_cost: data.course_cost,
            course_description: data.description,

            course_lessons: [],
            course_test: [],
            course_certificate: [],
            assigned_companies_id: []
          };
          if (data.in_store === 1) {
            course_obj.live = true;
          } else if (data.in_store === 0) {
            course_obj.live = false;
          } else {
            course_obj.live = data.in_store;
          }
          if (data.status === 1) {
            course_obj.status = true;
          } else if (data.status === 0) {
            course_obj.status = false;
          } else {
            course_obj.status = data.status;
          }
          this.course = course_obj;
          this.course.courseResources = data.courseResources;
          this.checkAvailbleCourse();
        });

      this.$http
        .post("course/totalpassedtest", {
          course_id: this.$route.query.id
        })
        .then(resp => {
          let data = resp.data.data.tests;
          const testData = data.filter(obj => {
            return obj.pass_fail == 0;
          });

          if (testData.length > 0) {
            if (testData[0].course_lesson_id) {
              this.redirect_id = testData[0].course_lesson_id;
            } else if (testData[0].course_test_id) {
              this.redirect_id = testData[0].course_test_id;
            }
          }
          this.tests = [];
          for (let tests of data) {
            let test_obj = {
              id: "",
              name: "",
              lesson_type: "",
              type: tests.lesson_type,
              content: "",
              status: tests.pass_fail
            };
            if (tests.course_lesson_id) {
              test_obj.id = tests.course_lesson_id;
            } else if (tests.course_test_id) {
              test_obj.id = tests.course_test_id;
            }
            if (tests.course_lesson_name) {
              test_obj.name = tests.course_lesson_name;
            }
            if (tests.type) {
              test_obj.lesson_type = tests.type;
            }
            if (tests.course_lesson_content) {
              test_obj.lesson_content =
                tests.course_lesson_content.substring(0, 100) + "...";
            }
            this.tests.push(test_obj);
          }
        });
    }
  },
  methods: {
    redirectLesson(id, type) {
      this.$router.push(
        "/lesson_form?id=" +
          this.course_id +
          "&interface='company'&redirection=yes&type=" +
          type +
          "&lesson_id=" +
          id
      );
    },
    checkAvailbleCourse() {
      this.course.courseResources.map(data => {
        if (data.available_after_course_completion === 0) {
          this.check_availability = true;
        }
      });
    }
  }
};
</script>
<style>
.no-border-card .card-footer {
  border-top: 0;
}

.scroll-timeline {
  overflow-y: auto !important;
  min-height: 200px;
  max-height: 200px;
}
.timeline-content {
  top: -9px;
}
</style>
