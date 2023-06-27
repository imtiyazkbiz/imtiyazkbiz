<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" body-classes="pb-1" footer-classes="pb-2">
          <template slot="header">
            <h3 class="mb-0"></h3>
            <div
              class="row"
              v-if="show_test || show_passed_msg || !showQuizFlag"
            >
              <div class="col-md-6">
                <span class="form-inline">
                  <h3 class="mb-0">
                    {{ course_name }}: {{ show_test ? "Test " : "" }}
                  </h3>
                  <br />
                  <span class="">{{
                    show_test
                      ? " : Passing Grade " + open_test.passing_grade + "%"
                      : ""
                  }}</span>
                </span>
              </div>
              <div class="remain-attempts col-md-6" v-if="!show_passed_msg">
                <p>
                  <b>Remaining attempts: </b>{{ open_test.remaining_attempts }}
                </p>
              </div>
            </div>
            <div class="row" v-if="show_lesson && showQuizFlag">
              <div class="col-md-6">
                <h3>{{ course_name }}: {{ open_lesson.name }} : Quiz</h3>
                <span class="form-inline"> </span>
              </div>
              <div class="remain-attempts col-md-6" v-if="!show_passed_msg">
                <p>
                  {{ open_lesson.remaining_attempts }}
                  <b>Remaining attempts: </b
                  >{{ open_lesson.remaining_attempts }}
                </p>
              </div>
            </div>
          </template>
          <div class="container" v-if="show_lesson">
            <div v-if="!showQuizFlag">
              <div class="row">
                <div class="col-md-6">
                  <p>
                    <b>{{ open_lesson.name }}</b>
                  </p>
                </div>
                <div class="col-md-6 text-right">
                  <base-button
                    class="custom-btn mr-3"
                    @click.prevent="backToPreviousLesson()"
                  >
                    {{ "Back" }}
                  </base-button>
                  <!-- <base-button
                    class="custom-secondary-btn"
                    @click.prevent="showQuiz()"
                    v-if="playerEnded"
                    >Take Quiz</base-button
                  >-->
                  <base-button
                    class="custom-secondary-btn"
                    @click.prevent="showQuiz()"
                    >Take Quiz</base-button
                  >
                </div>
              </div>
              <div class="row mb-4 ">
                <div class="col-md-12 ">
                  <div v-html="open_lesson.lesson_content"></div>
                </div>
              </div>
              <div
                class="row mb-1 text-center"
                style="height:500px;"
                v-if="
                  open_lesson.video_url !== '' && open_lesson.video_url !== null
                "
              >
                <div
                  class="col-md-12"
                  v-if="
                    open_lesson.mp4 && open_lesson.video_url.includes('vimeo')
                  "
                >
                  <vimeo-player
                    ref="player"
                    :video-id="open_lesson.video_url"
                    @ready="onReady"
                    @play="play"
                    @pause="pause"
                    @ended="ended"
                    :player-height="height"
                  ></vimeo-player>
                </div>
                <div v-else-if="open_lesson.video_url.includes('pdf')">
                  <embed
                    :src="
                      baseUrl +
                        '/employee/documents/' +
                        open_lesson.video_url +
                        '#toolbar=0'
                    "
                    frameBorder="0"
                    style="width:600px; height:500px;"
                  />
                </div>
                <div
                  class="col-md-12 iframe_append"
                  v-if="!open_lesson.mp4"
                ></div>
              </div>
            </div>
            <div v-if="showQuizFlag">
              <div class="container ml-5 mr-5">
                <div class="row">
                  <div class="col-md-8">
                    <div v-html="open_lesson.quiz_instruction"></div>
                  </div>
                  <div class="col-md-4 px-4 text-right">
                    <base-button
                      class="custom-btn mr-3"
                      @click.prevent="backToLessonInstruction()"
                    >
                      {{ "Back" }}
                    </base-button>
                    <base-button
                      class="custom-secondary-btn"
                      @click.prevent="submitLesson()"
                    >
                      {{
                        open_lesson.result === 1 || its_super_admin
                          ? "Next"
                          : "Submit"
                      }}
                    </base-button>
                  </div>
                </div>
                <div class="row" id="ques">
                  <div
                    class="col-md-12"
                    v-for="(question, q_index) in open_lesson.questions"
                    :key="question.id"
                  >
                    <div class="row">
                      <div class="col-md-12 form-inline"></div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-md-12 form-inline">
                        <span class=""
                          ><b
                            >{{ q_index + 1 }}. {{ question.question_text }}</b
                          ></span
                        >
                        <div class=" ml-3"></div>
                      </div>
                    </div>
                    <div
                      class="row"
                      v-for="(option, o_index) in question.options"
                      :key="option.id"
                    >
                      <div class="col-md-12">
                        <div>
                          <div v-if="question.selected">
                            <input
                              type="radio"
                              v-model="question.selected"
                              :checked="true"
                              v-bind:value="'val_' + option.id"
                              v-on:input="
                                optionChecked(
                                  q_index,
                                  o_index,
                                  'val_' + option.id
                                )
                              "
                              :label="'val_' + option.id"
                              v-bind:id="
                                o_index + '_' + q_index + '_' + option.id
                              "
                            />
                            {{ option.option_text }}
                          </div>
                          <div v-else>
                            <input
                              type="radio"
                              v-model="question.selected"
                              v-bind:value="'val_' + option.id"
                              :checked="false"
                              v-on:input="
                                optionChecked(
                                  q_index,
                                  o_index,
                                  'val_' + option.id
                                )
                              "
                              :label="'val_' + option.id"
                              v-bind:id="
                                o_index + '_' + q_index + '_' + option.id
                              "
                            />
                            {{ option.option_text }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <hr />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row" v-if="open_lesson.result === 1">
                  <div class="col-md-12 text-center">
                    <small class="">You've passed this lesson!</small>
                  </div>
                </div>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <div class="clearfix"></div>
              </div>
            </div>
          </div>
          <div class="container ml-5 mr-5" v-if="show_test">
            <div class="row" id="test_top"></div>
            <div class="col-md-12">
              <p>{{ open_test.quiz_instruction }}</p>
            </div>
            <div class="row">
              <div
                class="col-md-12"
                v-for="(question, q_index) in open_test.questions"
                :key="question.id"
              >
                <div class="row">
                  <div class="col-md-12 form-inline"></div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-12 form-inline">
                    <span class=""
                      ><b
                        >{{ q_index + 1 }}. {{ question.question_text }}</b
                      ></span
                    >
                  </div>
                </div>
                <div
                  class="row"
                  v-for="(option, o_index) in question.options"
                  :key="option.id"
                >
                  <div class="col-md-12">
                    <div>
                      <base-checkbox
                        class=""
                        v-model="option.selected_answers"
                        v-bind:value="option.id"
                        v-bind:id="o_index + '_' + q_index + '_' + option.id"
                        v-on:input="optionTestChecked(q_index, o_index)"
                        >{{ option.option_text }}</base-checkbox
                      >
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <hr />
                  </div>
                </div>
              </div>
            </div>
            <div class="row" v-if="open_test.result === 1">
              <div class="col-md-12 text-center">
                <small style="color: #444C57" class=" "
                  >You've passed this Test!</small
                >
              </div>
            </div>
            <div class="text-center my-4">
              <base-button type="default" @click.prevent="backToPreviousTest()">
                {{ "Back" }}
              </base-button>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <base-button type="primary" @click.prevent="submitTest()">
                Next
              </base-button>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="container" id="msg_top" v-if="show_passed_msg">
            <div class="row">
              <div class="col-md-12 text-center">
                <img src="img/brand/congratulations.gif" />
              </div>
              <div class="col-md-12">
                <h3
                  style="color: #444C57;"
                  class=" text-center"
                  v-if="pass_message === ''"
                >
                  Congratulations you have passed this course!
                </h3>
                <h3 style="color: #444C57;" class=" text-center" v-else>
                  {{ pass_message }}
                </h3>
              </div>
            </div>
            <div class="text-center my-4">
              Your certificate for the course is generated under Certificates.
              Please
              <router-link
                :to="
                  its_super_admin
                    ? '/courses'
                    : hot_user === 'company' || hot_user === 'super-admin'
                    ? '/company_certificates'
                    : '/employee_certificates'
                "
              >
                <base-button type="success" size="sm">
                  {{
                    its_super_admin
                      ? "LMS Courses"
                      : hot_user === "company" || hot_user === "super-admin"
                      ? "Click here"
                      : "Click here"
                  }}
                </base-button>
              </router-link>
              to download your certificate.
            </div>
            <div class="clearfix"></div>
          </div>
        </card>
      </div>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import { Table, TableColumn, Select, Option } from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
//import Swal from 'sweetalert';
import Swal from "sweetalert2";
import moment from "moment";
// import Vimeo from "./Vimeo";
import vueVimeoPlayer from "vue-vimeo-player";
import {
  PdfViewerPlugin,
  Toolbar,
  Magnification,
  Navigation,
  LinkAnnotation,
  BookmarkView,
  ThumbnailView,
  Print,
  TextSelection,
  TextSearch
} from "@syncfusion/ej2-vue-pdfviewer";
Vue.use(PdfViewerPlugin);
Vue.use(vueVimeoPlayer);
export default {
  mixins: [clientPaginationMixin],
  components: {
    // Vimeo,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      baseUrl: this.$baseUrl,
      height: 400,
      playerReady: false,
      playerEnded: false,
      mp4: false,
      show_lesson: false,
      show_test: false,
      show_passed_msg: false,
      isLoading: false,
      fullPage: true,
      processing: false,
      selected_ids: [],
      submitted: false,
      hot_user: "",
      hot_token: "",
      user_id: "",
      interface: "",
      config: "",
      course_id: "",
      course_name: "",
      pass_message: "",
      course_content: "",
      lesson_allowed_attempts: 0,
      user_attempts: 0,
      lessons: [],
      passed_lesson: [],
      passed_test: [],
      tests: [],
      open_lesson_index: -1,
      open_test_index: -1,
      open_lesson: {},
      open_test: {},
      all_tests: [],
      its_super_admin: false,
      showQuizFlag: false,
      passed_msg: ""
    };
  },
  created() {
    this.isLoading = true;
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
      this.user_id = localStorage.getItem("hot-user-id");
    }
    if (this.$route.query.id) {
      this.course_id = this.$route.query.id;
      this.getData();
    }
    if (this.$route.query.interface) {
      this.interface = "company";
    }
    if (this.$route.query.super_admin) {
      this.its_super_admin = true;
    }
  },
  methods: {
    // Vimeo
    onReady() {
      this.playerReady = true;
    },
    play() {
      this.$refs.player.play();
    },
    ended() {
      this.playerEnded = true;
    },
    backToLessonInstruction() {
      this.showQuizFlag = false;
    },
    showQuiz() {
      this.showQuizFlag = true;
    },
    handler: function(e) {
      //do stuff
      e.preventDefault();
    },
    backToPreviousLesson() {
      let self = this;
      if (this.open_lesson_index > 0) {
        this.open_lesson_index--;
        this.open_lesson = this.lessons[this.open_lesson_index];
      } else {
        Swal.fire({
          title: "Are you sure?",
          icon: "warning",
          text: "Leave This Course?",
          showCancelButton: true,
          confirmButtonClass: "btn btn-success btn-fill",
          cancelButtonClass: "btn btn-danger btn-fill",
          confirmButtonText: "Yes!",
          buttonsStyling: false
        }).then(result => {
          if (result.value) {
            if (self.its_super_admin) {
              self.$router.push("/courses");
            } else {
              self.$router.push("/course_instructions?id=" + self.course_id);
            }
          }
        });
      }
    },
    backToPreviousTest() {
      if (this.open_test_index > 0) {
        this.open_test_index--;
        this.open_test = this.tests[this.open_test_index];
      } else {
        this.open_lesson_index = this.lessons.length - 1;
        this.open_lesson = this.lessons[this.open_lesson_index];
        this.show_test = false;
        this.show_passed_msg = false;
        this.show_lesson = true;
      }
    },
    getData() {
      this.$http
        .post(
          "course/full_data",
          {
            course_id: this.course_id,
            user_id: this.user_id
          },
          this.config
        )
        .then(resp => {
          this.course_name = resp.data[0].name;
          this.pass_message = resp.data[0].pass_message;
          let lessons = resp.data[0].lessons;
          this.all_tests = resp.data[0].tests;
          for (let lesson of lessons) {
            let obj = {
              id: lesson.id,
              name: lesson.course_lesson_name,
              allowed_attempts: lesson.allowed_attempts,
              remaining_attempts: lesson.remaining_attempts,
              video_url: lesson.course_lesson_video,
              mp4: false,
              lesson_content: lesson.course_lesson_content,
              quiz_instruction: lesson.course_lesson_quiz,
              result: lesson.result,
              questions: []
            };
            if (
              lesson.course_lesson_video !== null &&
              lesson.course_lesson_video !== "" &&
              lesson.course_lesson_video !== undefined
            ) {
              if (lesson.course_lesson_video.includes("iframe")) {
                obj.mp4 = false;
              } else {
                // if(lesson.course_lesson_video.includes('watch') && lesson.course_lesson_video.includes('youtube')){
                //     let url = '';
                //     let i=0;
                //     while(i !== lesson.course_lesson_video.length-1){
                //         if(lesson.course_lesson_video[i]==='w'&&lesson.course_lesson_video[i+1]==='a'&&lesson.course_lesson_video[i+2]==='t'&&lesson.course_lesson_video[i+3]==='c'&&lesson.course_lesson_video[i+4]==='h'&&lesson.course_lesson_video[i+5]==='?'){
                //             url+='embed/';
                //             i += 8;
                //         }else{
                //             url+=lesson.course_lesson_video[i];
                //             i++;
                //         }
                //     }
                //     obj.video_url=url;
                // }
                obj.mp4 = true;
              }
            }
            let questions = lesson.questions;
            for (let quest of questions) {
              let question_obj = {
                id: quest.id,
                pass: false,
                question_text: quest.question,
                attempts: quest.allowed_attempts,
                allowed_attempts: quest.allowed_attempts,
                status: false,
                correct_answers: [],
                selected_answers: [],
                options: []
              };
              if (quest.status) {
                question_obj.status = true;
              } else {
                question_obj.status = false;
              }
              let options = quest.answers;
              for (let opt of options) {
                if (opt.course_quiz_correct_answer) {
                  question_obj.correct_answers.push(opt.id);
                }
                let opt_obj = {
                  id: opt.id,
                  selected_answers: false,
                  option_text: opt.course_quiz_question_option
                };
                question_obj.options.push(opt_obj);
              }
              obj.questions.push(question_obj);
            }
            this.lessons.push(obj);
            if (!obj.result && this.open_lesson_index < 0) {
              this.open_lesson_index = lessons.indexOf(lesson);
            }
          }
          if (this.open_lesson_index >= 0) {
            this.show_lesson = true;
            this.open_lesson = this.lessons[this.open_lesson_index];
            if (!this.open_lesson.mp4) {
              setTimeout(() => {
                $(".iframe_append iframe").remove();
                $(".iframe_append").append(this.open_lesson.video_url);
                $(".iframe_append iframe").css({
                  width: "100%",
                  height: "100%"
                });
              }, 500);
            }
          } else {
            this.getTest();
          }
        });
    },
    getTest() {
      if (this.all_tests.length <= 0) {
        this.showPassedMsg();
        return;
      }
      for (let test of this.all_tests) {
        let obj = {
          id: test.id,
          passed_msg: test.course_test_pass_msg,
          allowed_attempts: test.allowed_attempts,
          remaining_attempts: test.remaining_attempts,
          quiz_instruction: test.course_test_instruction,
          result: test.result,
          questions: []
        };
        let questions = test.questions;
        for (let quest of questions) {
          let question_obj = {
            id: quest.id,
            pass: false,
            question_text: quest.question,
            attempts: quest.allowed_attempts,
            allowed_attempts: quest.allowed_attempts,
            status: false,
            correct_answers: [],
            selected_answers: [],
            options: []
          };
          if (quest.status) {
            question_obj.status = true;
          } else {
            question_obj.status = false;
          }
          let options = quest.answers;
          for (let opt of options) {
            if (opt.course_quiz_correct_answer) {
              question_obj.correct_answers.push(opt.id);
            }
            let opt_obj = {
              id: opt.id,
              selected_answers: false,
              option_text: opt.course_quiz_question_option
            };
            question_obj.options.push(opt_obj);
          }
          obj.questions.push(question_obj);
        }
        this.tests.push(obj);
        if (!obj.result && this.open_test_index < 0) {
          this.open_test_index = this.all_tests.indexOf(test);
        }
      }
      if (this.open_test_index >= 0) {
        this.show_test = true;
        this.show_lesson = false;
        this.show_passed_msg = false;
        this.submitted = false;
        this.open_test = this.tests[this.open_test_index];
      } else {
        this.showPassedMsg();
      }
    },
    showPassedMsg() {
      this.show_lesson = false;
      this.show_test = false;
      this.show_passed_msg = true;
    },
    optionChecked(q_index, o_index, val) {
      this.open_lesson.questions[q_index].selected_answers = [];
      this.open_lesson.questions[q_index].selected_answers.push(val);
    },
    optionTestChecked(q_index, o_index) {
      if (
        this.open_test.questions[q_index].selected_answers.includes(
          this.open_test.questions[q_index].options[o_index].id
        )
      ) {
        this.open_test.questions[q_index].selected_answers.splice(
          this.open_test.questions[q_index].selected_answers.indexOf(
            this.open_test.questions[q_index].options[o_index].id
          ),
          1
        );
      } else {
        this.open_test.questions[q_index].selected_answers.push(
          this.open_test.questions[q_index].options[o_index].id
        );
      }
    },
    submitLesson() {
      this.isLoading = true;
      if (this.open_lesson.result === 1 || this.its_super_admin) {
        if (!this.open_lesson.mp4) {
          $("iframe").remove();
        }
        if (this.open_lesson_index < this.lessons.length - 1) {
          this.open_lesson_index++;
          this.open_lesson = this.lessons[this.open_lesson_index];
          this.isLoading = false;
          if (!this.open_lesson.mp4) {
            setTimeout(() => {
              $(".iframe_append ").append(this.open_lesson.video_url);
              $(".iframe_append iframe").css({
                width: "100%",
                height: "100%"
              });
            }, 500);
          }
        } else {
          this.getTest();
        }
        return;
      }
      this.isLoading = true;
      let lesson_id = this.open_lesson.id;
      let lesson_passed = 0;
      let counter = 0;
      if (
        this.open_lesson.remaining_attempts <= 0 &&
        (this.open_lesson.result === null || this.open_lesson.result === 0)
      ) {
        this.isLoading = false;
        Swal.fire({
          title: "Sorry!",
          text:
            "You have failed this lesson, please retake prior to its expiration.",
          icon: "warning",
          confirmButtonClass: "btn btn-success btn-fill",
          confirmButtonText: "OK",
          buttonsStyling: false
        }).then(result => {
          if (result.value) {
            this.$router.push("/course_instructions?id=" + this.course_id);
            this.$http
              .post(
                "employees/reset_attempts",
                {
                  course_id: this.course_id,
                  employee_id: this.user_id,
                  type: "lesson",
                  lesson_id: this.open_lesson.id
                },
                self.config
              )
              .then(resp => {});
          }
        });
        return;
      }
      for (let question of this.open_lesson.questions) {
        let correct = true;
        if (question.selected_answers.length <= 0) {
          this.isLoading = false;
          Swal.fire({
            title: "Error!",
            text: `Answer All Questions!`,
            icon: "error"
          });
          return;
        }
        if (
          question.correct_answers.length === question.selected_answers.length
        ) {
          for (let selected_answer of question.selected_answers) {
            if (
              !question.correct_answers.includes(
                parseInt(selected_answer.slice(4))
              )
            ) {
              correct = false;
              break;
            }
          }
          if (!correct) {
            question.pass = false;
            question.selected = "";
            Swal.fire({
              title: "Sorry!",
              text: `You have answered incorrectly.  Please try again.`,
              icon: "error"
            });
          } else {
            question.pass = true;
            question.selected = "";
            counter++;
          }
        } else {
          question.pass = false;
        }
      }
      this.submitted = true;
      let course_passed = "";
      if (counter === this.open_lesson.questions.length) {
        lesson_passed = 1;
        if (
          this.open_lesson_index === this.lessons.length - 1 &&
          this.all_tests.length <= 0
        ) {
          course_passed = 1;
        }
      }
      this.$http
        .post(
          "course/submit",
          {
            course_id: this.course_id,
            user_id: this.user_id,
            interface: this.interface,
            test_lesson_id: lesson_id,
            test_lesson: "lesson",
            pass: lesson_passed,
            course_pass: course_passed,
            status: "lesson"
          },
          this.config
        )
        .then(resp => {
          this.open_lesson.remaining_attempts =
            parseInt(this.open_lesson.remaining_attempts) - 1;
          if (lesson_passed) {
            this.open_lesson.result = 1;
            if (this.open_lesson_index < this.lessons.length - 1) {
              let self = this;
              // let done = false;
              this.submitted = false;
              this.isLoading = false;
              Swal.fire({
                title: "Passed!",
                icon: "success",
                text:
                  "You've completed this lesson, would you like to start next lesson?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                buttonsStyling: false
              })
                .then(function() {
                  if (!self.open_lesson.mp4) {
                    $("iframe").remove();
                  }
                  self.showQuizFlag = false;
                  self.open_lesson_index++;
                  self.open_lesson = self.lessons[self.open_lesson_index];
                  if (!self.open_lesson.mp4) {
                    setTimeout(() => {
                      $(".iframe_append ").append(self.open_lesson.video_url);
                      $(".iframe_append iframe").css({
                        width: "100%",
                        height: "100%"
                      });
                    }, 500);
                  }
                })
                .catch(function() {});
            } else {
              this.isLoading = false;
              let self = this;
              // let done = false;
              Swal.fire({
                title: "Passed!",
                icon: "success",
                text: "Congratulations you have passed this lesson!",
                confirmButtonClass: "btn btn-success btn-fill",
                confirmButtonText: "Continue",
                buttonsStyling: false
              })
                .then(function() {
                  self.getTest();
                })
                .catch(function() {});
            }
          } else {
          }
        });
    },
    submitTest() {
      this.isLoading = true;
      if (this.open_test.result === 1 || this.its_super_admin) {
        if (this.open_test_index < this.tests.length - 1) {
          this.open_test_index++;
          this.open_test = this.tests[this.open_test_index];
          this.isLoading = false;
        } else {
          this.showPassedMsg();
        }
        return;
      }
      let test_id = this.open_test.id;
      let test_passed = 0;
      let counter = 0;
      if (
        this.open_test.remaining_attempts <= 0 &&
        (this.open_test.result === null || this.open_test.result === 0)
      ) {
        this.isLoading = false;
        Swal.fire({
          title: "Error!",
          text: `You are Fail in this Test!`,
          icon: "error"
        }).then(result => {
          if (result.value) {
            this.$router.push("/course_instructions?id=" + this.course_id);
            this.$http
              .post(
                "employees/reset_attempts",
                {
                  course_id: this.course_id,
                  employee_id: this.user_id,
                  company_id: this.company_id,
                  type: "test",
                  test_id: this.open_test.id
                },
                self.config
              )
              .then(resp => {});
          }
        });
        return;
      }
      for (let question of this.open_test.questions) {
        let correct = true;
        if (question.selected_answers.length <= 0) {
          this.isLoading = false;
          Swal.fire({
            title: "Error!",
            text: `Answer All Questions!`,
            icon: "error"
          });
          return;
        }
        if (
          question.correct_answers.length === question.selected_answers.length
        ) {
          for (let selected_answer of question.selected_answers) {
            if (!question.correct_answers.includes(selected_answer)) {
              correct = false;
              break;
            }
          }
          if (!correct) {
            question.pass = false;
            Swal.fire({
              title: "Sorry!",
              text: `You are failed in this Test!`,
              icon: "error"
            });
          } else {
            question.pass = true;
            counter++;
          }
        } else {
          question.pass = false;
        }
      }
      let course_passed = "";
      if (counter === this.open_test.questions.length) {
        test_passed = 1;
        if (this.open_test_index === this.tests.length - 1) {
          course_passed = 1;
        }
      }
      this.submitted = true;
      this.$http
        .post(
          "course/submit",
          {
            course_id: this.course_id,
            user_id: this.user_id,
            interface: this.interface,
            test_lesson_id: test_id,
            test_lesson: "test",
            pass: test_passed,
            course_pass: course_passed,
            status: "test"
          },
          this.config
        )
        .then(resp => {
          this.open_test.remaining_attempts =
            parseInt(this.open_test.remaining_attempts) - 1;
          if (test_passed) {
            this.open_test.result = 1;
            if (this.open_test_index < this.tests.length - 1) {
              this.submitted = false;
              this.isLoading = false;
              let self = this;
              // let done = false;
              Swal.fire({
                title: "Passed!",
                type: "info",
                text:
                  "You've completed this Test, would you like to start next Test?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                buttonsStyling: false
              })
                .then(function() {
                  self.open_test_index++;
                  self.open_test = self.tests[self.open_test_index];
                })
                .catch(function() {});
            } else {
              this.showPassedMsg();
            }
          } else {
            this.isLoading = false;
          }
        });
    },
    backToCourseInstruction() {
      this.$router.push("/course_instructions?id=" + this.course_id);
    }
  }
};
</script>
<style>
.no-border-card .card-footer {
  border-top: 0;
}

.remain-attempts {
  text-align: right;
}

.remain-attempts p {
  color: red;
}

.disabled:hover {
  cursor: not-allowed;
}
.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;
}

@import "../../../node_modules/@syncfusion/ej2-base/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-buttons/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-dropdowns/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-inputs/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-navigations/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-popups/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-splitbuttons/styles/material.css";
@import "../../../node_modules/@syncfusion/ej2-vue-pdfviewer/styles/material.css";
</style>
