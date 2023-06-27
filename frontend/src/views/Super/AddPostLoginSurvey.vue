<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <label class="form-control-label"
          >Title <span class="req">*</span></label
        >
        <base-input
          required
          name="Survey title"
          v-model="survey.name"
          placeholder="Enter Survey title"
        ></base-input>
      </div>
      <div class="col-md-12">
        <label class="form-control-label"
          >Instructions <span class="req">*</span></label
        >
        <vue-editor v-model="survey.instructions"></vue-editor>
      </div>
    </div>

    <div
      class="brdr question_box mt-3"
      v-for="(question, q_index) in survey.surveytest_questions"
      :key="question.id"
    >
      <div class="row align-items-center">
        <div class="col-md-7">
          <h4 style="color: #444c57">Question {{ q_index + 1 }}</h4>
        </div>
        <div class="col-md-4 col-6">
          <div
            class="d-flex justify-content-md-end"
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
        <div class="col-md-1 col-6">
          <base-button
            type="danger"
            style="float: right"
            size="sm"
            @click.prevent="removeSurveyQuestion(q_index)"
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
              survey_save_clicked &&
              question.question === '' &&
              question.question_status
            "
            >Question Field is Required!</span
          >
        </div>

        <div class="col-md-4">
          <label class="form-control-label">Answer Type: </label>
          <el-select
            class="w-100"
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
      <div class="row mt-2 mb-2" v-if="question.answer_type == 1">
        <div class="col-md-4">
          <div class="row">
            <div class="col-md-12">
              <label class="form-control-label">Select Validation </label>
            </div>
            <div class="col-md-12">
              <el-select v-model="question.checked_validations" class="w-100">
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
      </div>

      <div class="row" v-if="question.answer_type == 2">
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
                <div class="col-sm-6 col-7 mt-2">
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
                      survey_save_clicked &&
                      option.answer === '' &&
                      question.question_status
                    "
                    >Option Field is Required!</span
                  >
                </div>
                <div class="col-sm-5 col-5 mt-md-5">
                  <base-button
                    size="sm"
                    type="danger"
                    @click.prevent="removeSurveyOption(q_index, o_index)"
                  >
                    <i class="fa fa-trash"></i>
                  </base-button>
                </div>
              </div>
              <div class="row">
                <div class="col-md-7">
                  <label
                    style="color: #444c57"
                    class="cursor"
                    v-on:click="addSurveyOption(q_index)"
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
    <div class="row mt-2">
      <div class="col-md-12">
        <base-button v-on:click="addQuestionSurvey()"
          ><b>+</b> Add Another Question</base-button
        >
      </div>
    </div>
    <div class="text-right mb-2">
      <base-button
        type="danger"
        class="custom-btn"
        @click.prevent="cancelSurvey"
      >
        Cancel
      </base-button>
      <base-button class="custom-btn" @click.prevent="saveSurvey">
       <span v-if="survey_id">{{'Update Survey'}} </span><span v-else> {{'Save Survey'}}</span>
      </base-button>
    </div>
  </div>
</template>
<script>
import { Select, Option } from "element-ui";
import { VueEditor } from "vue2-editor";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
export default {
  name: "add-post-login-survey",
  components: {
    VueEditor,
    [Select.name]: Select,
    [Option.name]: Option,
  },
  props: {
    survey_id: Number,
  },
  data() {
    return {
      loading: false,
      isRequired: true,
      answer_type: [
        {
          label: "Text",
          value: 1,
        },
        {
          label: "Option",
          value: 2,
        },
      ],
      validationtype: [
        {
          label: "Please Select validation",
          value: 0,
        },
        {
          label: "Phone number",
          value: 1,
        },
        {
          label: "Email",
          value: 2,
        },
        {
          label: "Text",
          value: 3,
        },
        {
          label: "Date",
          value: 4,
        },
        {
          label: "SSN",
          value: 5,
        },
      ],

      survey_save_clicked: false,
      survey: {
        name: "",
        instructions: "",
        no_of_questions: "",
        type: "post-login",
        surveytest_questions: [
          {
            question: "",
            answer_type: "",
            question_status: true,
            checked_validations: "",
            answers: [
              {
                answer: "",
                correct_answer: false,
              },
            ],
          },
        ],
      },
    };
  },
  created() {
      if(this.survey_id!=0){
        this.editSurvey(this.survey_id);  
      }
  },
  methods: {
      editSurvey(id){
      this.$http.get("editPostLoginSurvey/" + id).then((resp) => {
          let surveytests=resp.data;
            let surveytest_obj = {
              id: surveytests.id,
              name: surveytests.name,
              instructions: surveytests.instruction,
              surveytest_questions: []
            };
            for (let question of surveytests.questions) {
              let question_obj = {
                id: question.id,
                question: question.question,
                answer_type: question.question_type,
                checked_validations: question.validation,
                question_status: "",
                answers: []
              };
              if (question.status === 1) {
                question_obj.question_status = true;
              } else if (question.status === 0) {
                question_obj.question_status = false;
              }

              for (let answer of question.answers) {
                let option_obj = {
                  id: answer.id,
                  answer: answer.answer
                };
                question_obj.answers.push(option_obj);
              }

              surveytest_obj.surveytest_questions.push(question_obj);
            }
            this.survey=surveytest_obj;
        
      });
      },
    removeSurveyQuestion(index) {
      this.survey.surveytest_questions.splice(index, 1);
    },
    removeSurveyOption(q_index, opt_index) {
      this.survey.surveytest_questions[q_index].answers.splice(opt_index, 1);
    },
    addSurveyOption(question_index) {
      this.survey.surveytest_questions[question_index].answers.push({
        answer: "",
        correct_answer: false,
      });
    },
    addQuestionSurvey() {
      this.survey.surveytest_questions.push({
        question: "",
        question_status: true,
        answer_type: "",
        checked_validations: "",
        answers: [
          {
            answer: "",
            correct_answer: false,
          },
        ],
      });
    },
    saveSurvey() {
      this.survey_save_clicked = true;
      this.isRequired = true;
      if (this.survey.name !== "" && this.survey.instructions !== "") {
        for (let question of this.survey.surveytest_questions) {
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
                    icon: "error",
                  });
                }
              }
            } else {
              return Swal.fire({
                title: "Error!",
                text: `All Questions's fields are required!`,
                icon: "error",
              });
            }
          }
        }
      } else {
        return Swal.fire({
          title: "Error!",
          text: `Please fill all required fields of Survey!`,
          icon: "error",
        });
      }
      if(!this.survey_id){
      this.$http
        .post("course/createSurvey", this.survey, this.config)
        .then((resp) => {
          this.survey_question = false;
          this.survey_save_clicked = false;
          Swal.fire({
            title: "Success!",
            html: "Survey added successfully.",
            icon: "success",
          }).then((result) => {
            if (result.value) {
              this.$emit("refreshSurveyGrid");
            }
          });
        });
        }else{
            this.survey.id=this.survey_id;
         this.$http
        .post("updateSurvey", this.survey, this.config)
        .then((resp) => {
          this.survey_question = false;
          this.survey_save_clicked = false;
          Swal.fire({
            title: "Success!",
            html: "Survey added successfully.",
            icon: "success",
          }).then((result) => {
            if (result.value) {
              this.$emit("refreshSurveyGrid");
            }
          });
        });
        }
    },
    cancelSurvey() {
      this.$emit("hideAddSurvey");
    },
  },
};
</script>
<style scoped>
.req {
  color: red;
}
</style>
