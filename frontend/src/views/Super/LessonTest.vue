<template>
  <div>
    <form v-if="!lesson_preview">
      <div class="row">
        <div class="col-md-3">
          <base-input
            type="text"
            label="Lesson Name"
            placeholder="Lesson Name"
            v-model="lesson.lesson_name"
          >
          </base-input>
          <span
            class="text-danger small"
            v-if="lesson_save_clicked && lesson.lesson_name === ''"
            >Lesson Name Field is Required!</span
          >
        </div>
        <div class="col-md-3">
          <label class="form-control-label">Select Type</label>
          <el-select
            class="select-primary"
            v-model="lesson.lesson_type"
            placeholder="Select Type"
          >
            <el-option
              class="select-primary"
              v-for="item in lessontype"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            >
            </el-option>
          </el-select>
        </div>
        <div class="col-md-3" v-if="lesson.lesson_type == 'video'">
          <base-input
            type="text"
            label="Video Id"
            placeholder="Video Id"
            v-model="lesson.lesson_video_url"
          >
          </base-input>
        </div>
        <div class="col-md-3" v-if="lesson.lesson_type == 'pdf'">
          <label class="form-control-label">Upload Pdf</label>
          <file-input v-on:change="onImageChange"></file-input>
        </div>
        <div class="col-md-3">
          <label class="form-control-label "><b>Lesson Quiz:</b></label
          ><br />
          <div class="d-flex">
            <base-switch
              class="mr-1"
              type="success"
              id="status-switch"
              v-model="lesson.quizStatus"
            ></base-switch>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-control-label">Lesson Content</label>
            <vue-editor
              placeholder="Lesson Content Here..."
              v-model="lesson.lesson_content"
            ></vue-editor>
          </div>
        </div>
      </div>
      <hr class="mt-0" />

      <div v-if="lesson.quizStatus">
        <div class="row">
          <div class="col-md-2">
            <base-input
              type="number"
              min="1"
              label="Allowed Attempts"
              name="Allowed Attempts"
              placeholder="Allowed Attempts"
              v-model="lesson.allowed_attempts"
            >
            </base-input>
            <span
              class="text-danger small"
              v-if="
                lesson.quizStatus &&
                  lesson_save_clicked &&
                  lesson.allowed_attempts === ''
              "
              >Allowed Attempts Field is Required!</span
            >
          </div>
          <div class="col-md-2">
            <label class="form-control-label">Passing Rate</label>
            <the-mask
              class="form-control"
              v-model="lesson.passing_rate"
              mask="##%"
              value=""
              type="text"
              placeholder=" Pass Rate"
            ></the-mask>
          </div>
          <div class="col-md-2">
            <base-input
              type="number"
              min="1"
              label="Number of Questions"
              placeholder=""
              v-model="lesson.no_of_questions"
            >
            </base-input>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-control-label"> Quiz Instruction: </label>
              <vue-editor
                placeholder="Quiz Instructions here..."
                v-model="lesson.lesson_quiz_instruction"
              ></vue-editor>
            </div>
          </div>
        </div>
        <div
          class="brdr mt-3"
          v-for="(question, q_index) in lesson.lesson_questions"
          :key="question.id"
        >
          <div class="row">
            <div class="col-md-6">
              <h4 style=" color: #272C33;" class="card-subtitle mb-2 ">
                Question {{ q_index + 1 }}
              </h4>
            </div>
            <div class="col-md-3">
              <div
                class="d-flex justify-content-center"
                v-on:click="changeStatus(props.$index, props.row)"
              >
                <base-switch
                  class="mr-1"
                  v-if="question.question_status"
                  type="success"
                  :id="'lessonQuestion-switch_' + q_index"
                  v-model="question.question_status"
                ></base-switch>
                <base-switch
                  class="mr-1"
                  v-else
                  type="danger"
                  v-model="question.question_status"
                  :id="'lessonQuestion-switch_' + q_index"
                ></base-switch>
              </div>
            </div>
            <div class="col-md-3">
              <base-button
                type="danger"
                style="float:right;"
                size="sm"
                @click.prevent="removeLessonQuestion(q_index)"
              >
                <i class="fa fa-trash"></i>
              </base-button>
            </div>
          </div>
          <div class="row ">
            <div class="col-md-12">
              <div class="row">
                <div class="col-md-4 pt-4">
                  <base-input
                    type="text"
                    label="Question"
                    placeholder="Question"
                    v-model="question.question_text"
                  >
                  </base-input>
                  <span
                    class="text-danger small"
                    v-if="
                      lesson.quizStatus &&
                        isRequired &&
                        lesson_save_clicked &&
                        question.question_text === '' &&
                        question.question_status
                    "
                    >Question Field is Required!</span
                  >
                </div>
                <div class="col-md-8">
                  <div class="row">
                    <div class="col-sm-7 col-7"></div>
                    <div class="col-sm-5 col-5 ">
                      <label>Correct Answer</label>
                    </div>
                  </div>
                  <div
                    class="row"
                    v-for="(option, o_index) in question.options"
                    :key="option.id"
                  >
                    <div class="col-sm-7 col-7">
                      <base-input
                        type="text"
                        :label="'Answer Option ' + (o_index + 1)"
                        placeholder="Option"
                        v-model="option.option_text"
                      >
                      </base-input>
                      <span
                        class="text-danger small"
                        v-if="
                          lesson.quizStatus &&
                            isRequired &&
                            lesson_save_clicked &&
                            option.option_text === '' &&
                            question.question_status
                        "
                        >Option Field is Required!</span
                      >
                    </div>
                    <div class="col-sm-5  col-5">
                      <div class="row">
                        <div class="col-sm-8  col-8 pt-2">
                          <base-checkbox
                            class="pull-right"
                            v-model="option.correct"
                          ></base-checkbox>
                        </div>
                        <div class="col-sm-4 col-4 pt-4">
                          <base-button
                            type="danger"
                            size="sm"
                            style="float:right;"
                            @click.prevent="
                              removeLessonOption(q_index, o_index)
                            "
                          >
                            <i class="fa fa-trash"></i>
                          </base-button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-7">
                      <label
                        style=" color: #272C33;"
                        class=" cursor"
                        v-on:click="addOptionlesson(q_index)"
                        ><b class="mr-1">+</b>Add Another Option</label
                      >
                    </div>
                    <div class="col-md-5 text-center"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <label
              style=" color: #272C33;"
              class=" cursor"
              v-on:click="addQuestionlesson()"
              ><b>+</b>Add Another Question</label
            >
          </div>
        </div>
      </div>
      <div class="text-center my-4">
        <base-button type="primary" @click.prevent="saveLesson">
          Save Lesson
        </base-button>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <base-button type="default" @click.prevent="previewLesson">
          Preview
        </base-button>
      </div>
      <div class="clearfix"></div>
    </form>
    <div class="container" v-if="lesson_preview">
      <div class="row brdr">
        <div class="col-md-12">
          <p><b>Lesson:</b> {{ lesson.lesson_name }}</p>
          <div v-html="lesson.lesson_content"></div>
          <div
            class="row brdr mb"
            v-for="(question, index) in lesson.lesson_questions"
            :key="question.id"
          >
            <div class="col-md-12">
              <p>
                <b>Question {{ index + 1 }} </b> {{ question.question_text }}
              </p>
              <ul class="list-unstyled ">
                <li
                  v-for="(option, index) in question.options"
                  :key="option.id"
                >
                  <base-radio
                    :label="'radio_' + index"
                    v-model="question.answer"
                    >{{ option.option_text }}</base-radio
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center my-4">
        <base-button type="default" size="sm" @click.prevent="backToLesson">
          Back
        </base-button>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <base-button type="primary" size="sm" @click.prevent="saveLesson">
          Save
        </base-button>
      </div>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import { BaseAlert } from "@/components";
import { VueEditor } from "vue2-editor";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import BaseCheckbox from "../../components/Inputs/BaseCheckbox.vue";
Vue.component("v-select", vSelect);
export default {
  name: "lesson-test",
  components: {
    VueEditor,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      pretest_save_clicked: false,
      isRequired: true,
      previewPhone: "",
      previewEmail: "",
      previewText: "",
      previewSsn: "",
      previewDate: "",
      checked_validations: [],
      duplicateFlag: false,
      pretestModal: false,
      saving: false,
      hot_user: "",
      hot_token: "",
      config: "",
      answer_type: [
        {
          label: "Text",
          value: "1"
        },
        {
          label: "Option",
          value: "2"
        }
      ],
      validationtype: [
        {
          label: "Phone number",
          value: "1"
        },
        {
          label: "Email",
          value: "2"
        },
        {
          label: "Text",
          value: "3"
        },
        {
          label: "Date",
          value: "4"
        },
        {
          label: "SSN",
          value: "5"
        }
      ],
      processing: false,
      pretestQuizModal: false,
      pretest: {
        name: "",
        instructions: "",
        no_of_questions: "",
        pretest_questions: [
          {
            question: "",
            answer_type: "",
            question_status: true,
            answers: [
              {
                answer: "",
                correct_answer: false
              }
            ]
          }
        ]
      },

      requiredFeilds: ""
    };
  },
  methods: {
    acceptNumber() {
      var x = this.previewPhone
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.previewPhone = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    validationCheckchange(row) {
      if (this.checked_validations.includes(row)) {
        this.checked_validations.splice(
          this.checked_validations.indexOf(row),
          1
        );
      } else {
        this.checked_validations.push(row);
      }
    },
    addQuestionpretest() {
      this.pretestQuizModal = true;
    },
    addPreTestClicked() {
      this.pretestModal = true;
    },
    addPreOptionTest(question_index) {
      this.pretest.pretest_questions[question_index].answers.push({
        answer: "",
        correct_answer: false
      });
    },
    addQuestionPretest() {
      this.pretest.pretest_questions.push({
        question: "",
        question_status: true,
        answer_type: "",
        validation: [],

        answers: [
          {
            answer: "",
            correct_answer: false
          }
        ]
      });
    },
    removePreTestQuestion(index) {
      this.pretest.pretest_questions.splice(index, 1);
    },
    removePreTestOption(q_index, opt_index) {
      this.pretest.pretest_questions[q_index].answers.splice(opt_index, 1);
    }
  }
};
</script>
<style>
.stripe-card {
  border: 1px solid grey;
}

.stripe-card.complete {
  border-color: green;
}

.logo-size {
  width: 60%;
  height: auto;
}
hr {
  border-top: 1px solid #28c0e75c !important;
}
.border-right {
  border-right: 1px solid darkblue;
}
</style>
