<template>
  <div>
    <div class="row" v-if="showSurveyTest">
      <div class="col-md-12">
        <h4>{{ survey.name }}</h4>
      </div>
      <div class="col-md-12">
        <div class="text-justify course-disc" v-html="survey.instruction"></div>
      </div>

      <div
        :key="question.id"
        class="col-md-12"
        v-for="(question, index) in survey.questions"
      >
        <div>
          <h6 class="questionname mt-2">
            {{ index + 1 }}. {{ question.question_text }}
          </h6>

          <div v-if="question.question_type == 1">
            <div v-if="question.validation === 1">
              <base-input
                @click.prevent="acceptNumber"
                placeholder="(555) 555-5555"
                v-model="question.selected_answers"
              ></base-input>
            </div>
            <div v-if="question.validation === 2">
              <base-input
                name="Email"
                placeholder="Enter email"
                type="email"
                v-model="question.selected_answers"
              ></base-input>
            </div>
            <div v-if="question.validation === 3">
              <base-input
                type="text"
                v-model="question.selected_answers"
              ></base-input>
            </div>
            <div v-if="question.validation === 4">
              <el-date-picker
                :picker-options="pickerOptions1"
                format="MM/dd/yyyy"
                placeholder="Pick a day"
                style="width: 100%"
                type="date"
                v-model="question.selected_answers"
              >
              </el-date-picker>
            </div>
            <div class="col-md-6" v-if="question.validation === 5">
              <base-input
                placeholder="Enter SSN"
                v-model="question.selected_answers"
              ></base-input>
            </div>
          </div>
          <div class="qtn-checkbox" v-else>
            <div :key="option.id" v-for="option in question.options">
              <input
                :checked="true"
                :name="'surveytest_' + question.id"
                type="radio"
                v-bind:value="true"
                v-model="option.selected_answers"
              />
              {{ option.option_text }}
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12 text-left mt-3">
        <base-button
          name="Submit Survey"
          @click.prevent="saveSurveytest()"
          class="custom-btn"
          >Save
        </base-button>
      </div>
    </div>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import BaseInput from "../../components/Inputs/BaseInput.vue";
import BaseButton from "../../components/BaseButton.vue";
export default {
  name: "post-login-survey-employee",
  components: {
    BaseInput,
    BaseButton,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      loading: false,
      survey: [],
      showSurveyTest: false,
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.$http.get("getEmployeePostLoginSurvey").then((resp) => {
        let surveytest = resp.data;
         if (typeof surveytest == "object") {
          this.showSurveyTest = true;
          for (let test of surveytest) {
            let obj = {
              id: test.id,
              name: test.name,
              instruction: test.instruction,
              questions: [],
            };
            let questions = test.questions;
            for (let quest of questions) {
              let question_obj = {
                id: quest.id,
                pass: false,
                question_text: quest.question,
                question_type: quest.question_type,
                validation: quest.validation,
                status: false,
                selected_answers: "",
                options: [],
              };
              if (quest.status) {
                question_obj.status = true;
              } else {
                question_obj.status = false;
              }
              let options = quest.answers;
              for (let opt of options) {
                let opt_obj = {
                  id: opt.id,
                  selected_answers: false,
                  option_text: opt.answer,
                };
                question_obj.options.push(opt_obj);
              }
              obj.questions.push(question_obj);
            }
            this.survey = obj;
          }
        } else {
          this.$emit("hideEmployeeSurveyPopup");
          this.showSurveyTest = false;
        }
      });
    },
    saveSurveytest() {
      this.formattedSurveytest = {
        test_id: this.survey.id,
        questions: [],
      };
      for (let quest of this.survey.questions) {
        let question_obj = {
          question_id: quest.id,
          question: quest.question_text,
          answer: "",
          answer_id: "0",
        };
        if (quest.selected_answers == null || quest.selected_answers == "") {
          for (let option of quest.options) {
            if (option.selected_answers) {
              question_obj.answer = option.option_text;
              question_obj.answer_id = option.id;
            }
          }
        } else {
          question_obj.answer = quest.selected_answers;
        }
        this.formattedSurveytest.questions.push(question_obj);
      }
      this.$http
        .post("postLoginSurveySubmissions", this.formattedSurveytest)
        .then((resp) => {
          Swal.fire({
            icon: "success",
            html: resp.data.message,
            confirmButtonClass: "btn btn-success btn-fill",
            confirmButtonText: "OK",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              this.fetchData();
            }
          });
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            return Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          }
        });
    },
  },
};
</script>
