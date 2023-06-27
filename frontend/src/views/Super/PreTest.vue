<template>
  <div>
    <div class="row">
      <div class="col-md-4">
        <div class="col-md-12">
          <base-input
            v-model="pretest.name"
            label="Title *"
            placeholder="Enter pre test title"
          ></base-input>
        </div>
        <div class="col-md-12">
          <base-input
            type="number"
            v-model="pretest.no_of_questions"
            label="Number of Questions *"
          ></base-input>
        </div>
      </div>
      <div class="col-md-8">
        <div class="col-md-12">
          <label class="form-control-label">Instructions *</label>
          <vue-editor v-model="pretest.instructions"></vue-editor>
        </div>
      </div>
    </div>
    <hr />
    <div
      class="brdr mt-3"
      v-for="(question, q_index) in pretest.pretest_questions"
      :key="question.id"
    >
      <div class="row mt-4">
        <div class="col-md-6">
          <h4 style="color: #444C57;" class="card-subtitle mb-2 ">
            Question {{ q_index + 1 }}
          </h4>
        </div>
        <div class="col-md-3  ">
          <div
            class="d-flex justify-content-center"
            v-on:click="changeStatus(props.$index, props.row)"
          >
            <base-switch
              class="mr-1"
              v-if="question.question_status"
              type="success"
              :id="'testQuestion-switch_' + q_index"
              v-model="question.question_status"
            ></base-switch>
            <base-switch
              class="mr-1"
              v-else
              type="danger"
              v-model="question.question_status"
              :id="'testQuestion-switch_' + q_index"
            ></base-switch>
          </div>
        </div>
        <div class="col-md-3">
          <base-button
            type="danger"
            style="float:right;"
            size="sm"
            @click.prevent="removePreTestQuestion(q_index)"
          >
            <i class="fa fa-trash"></i>
          </base-button>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 pt-2 pb-4">
          <textarea
            class="form-control"
            type="text"
            placeholder="Question"
            v-model="question.question"
          >
          </textarea>
          <span
            class="text-danger small"
            v-if="
              isRequired &&
                pretest_save_clicked &&
                question.question === '' &&
                question.question_status
            "
            >Question Field is Required!</span
          >
        </div>

        <div class="col-md-12">
          <label>Answer Type: </label>
          <el-select
            class="ml-2"
            v-model="question.answer_type"
            placeholder="Select Answer Type"
          >
            <el-option
              v-for="option in answer_type"
              class="select-primary"
              :value="option.value"
              :label="option.label"
              :key="option.value"
            >
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="row mt-4 mb-2" v-if="question.answer_type == 1">
        <div class="col-md-8">
          <div class="row">
            <div class="col-md-12">
              <h4>Select Validation</h4>
            </div>
            <div class="col-md-6">
              <el-select v-model="question.checked_validations">
                <el-option
                  v-for="text in validationtype"
                  :key="text.value"
                  :value="text.value"
                  :label="text.label"
                ></el-option>
              </el-select>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <h3 style="color: #28c0e7;">Preview</h3>
          <base-input
            v-model="previewPhone"
            v-if="question.checked_validations.includes('1')"
            placeholder="(555) 555-5555"
            @input="acceptNumber"
          ></base-input>
          <base-input
            type="email"
            name="Email"
            v-model="previewEmail"
            v-if="question.checked_validations.includes('2')"
            placeholder="Enter valid email"
          ></base-input>
          <base-input
            v-model="previewText"
            v-if="question.checked_validations.includes('3')"
            placeholder="Enter Text"
          ></base-input>
          <el-date-picker
            v-model="previewDate"
            v-if="question.checked_validations.includes('4')"
            style="width: 100%"
            type="date"
            format="MM/dd/yyyy"
            placeholder="Pick a day"
            :picker-options="pickerOptions1"
          >
          </el-date-picker>
          <base-input
            v-model="previewSsn"
            v-if="question.checked_validations.includes('5')"
            placeholder="Enter SSN"
          ></base-input>
        </div>
      </div>

      <div class="row " v-if="question.answer_type == 2">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-8">
              <div class="row">
                <div class="col-sm-7 col-7"></div>
              </div>
              <div
                class="row"
                v-for="(option, o_index) in question.answers"
                :key="option.id"
              >
                <div class="col-sm-7 col-7">
                  <base-input
                    type="text"
                    :label="'Answer Option ' + (o_index + 1)"
                    placeholder="Option"
                    v-model="option.answer"
                  >
                  </base-input>
                  <span
                    class="text-danger small"
                    v-if="
                      isRequired &&
                        pretest_save_clicked &&
                        option.answer === '' &&
                        question.question_status
                    "
                    >Option Field is Required!</span
                  >
                </div>
                <div class="col-sm-5  col-5">
                  <div class="row">
                    <div class="col-sm-4 col-4 pt-4">
                      <base-button
                        size="sm"
                        type="danger"
                        @click.prevent="removePreTestOption(q_index, o_index)"
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
                    style="color: #444C57;"
                    class=" cursor"
                    v-on:click="addPreOptionTest(q_index)"
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
    <div class="row mt-4">
      <div class="col-md-12">
        <label
          class=" cursor"
          style="color: #444C57;"
          v-on:click="addQuestionPretest()"
          ><b>+</b>Add Another Question</label
        >
      </div>
    </div>
    <div class="text-center my-4">
      <base-button type="primary" size="md" @click.prevent="savePreTest">
        Save Pre Test
      </base-button>
    </div>
    <div class="clearfix"></div>
  </div>
</template>
<script>
import Vue from "vue";
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import { BaseAlert } from "@/components";
import { VueEditor } from "vue2-editor";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import BaseCheckbox from "../../components/Inputs/BaseCheckbox.vue";
Vue.component("v-select", vSelect);
export default {
  name: "pre-test",
  components: {
    [DatePicker.name]: DatePicker,
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

      pickerOptions1: {
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              picker.$emit("pick", new Date());
            }
          },
          {
            text: "Yesterday",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit("pick", date);
            }
          },
          {
            text: "A week ago",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", date);
            }
          }
        ]
      },
      processing: false,
      pretestQuizModal: false,
      checkedNames: [],
      pretest: {
        name: "",
        instructions: "",
        no_of_questions: "",
        pretest_questions: [
          {
            question: "",
            answer_type: "",
            question_status: true,
            checked_validations: [],
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
        checked_validations: [],
        answers: [
          {
            answer: "",
            correct_answer: false
          }
        ]
      });
    },
    savePreTest() {
      this.pretest_save_clicked = true;
      this.isRequired = true;
      if (
        this.pretest.name !== "" &&
        this.pretest.instructions !== "" &&
        this.pretest.no_of_questions !== ""
      ) {
        for (let question of this.pretest.pretest_questions) {
          let correct_answer = false;
          if (question.question_status && question.answer_type == "2") {
            this.isRequired = true;
          } else {
            this.isRequired = false;
          }
          if (this.isRequired) {
            if (question.question !== "") {
              for (let answer of question.answers) {
                if (answer.answer === "") {
                  return Swal.fire({
                    title: "Error!",
                    text: `All options's fields are required!`,
                    icon: "error"
                  });
                }
              }
            } else {
              return Swal.fire({
                title: "Error!",
                text: `All Questions's fields are required!`,
                icon: "error"
              });
            }
          }
        }
      } else {
        return Swal.fire({
          title: "Error!",
          text: `All Test's Fields are required!`,
          icon: "error"
        });
      }
      this.pretest_question = false;
      this.main_test = true;
      this.test_preview = false;
      this.pretestModal = false;
      this.test_save_clicked = false;
      if (!this.updating) {
        this.course.course_pretest.push(this.pretest);
      }
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
