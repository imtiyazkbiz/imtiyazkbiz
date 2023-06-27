<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <card>
        <!-- Card header -->
        <h2 slot="header" class="mb-0">Create New Course</h2>
        <div>
          <div class="row" style="text-align:right;">
            <div class="col-lg-3 form-inline">
              <b class="card-title1 mr-3 ">Add to Store</b>
              <div class="d-flex justify-content-center">
                <base-switch
                  class="mr-1"
                  type="success"
                  id="live-switch"
                  v-model="course.live"
                ></base-switch>
              </div>
            </div>
            <div class="col-lg-3 form-inline">
              <b class="card-title1 mr-3 ">Status</b>
              <div class="d-flex justify-content-center">
                <base-switch
                  class="mr-1"
                  type="success"
                  id="status-switch"
                  v-model="course.status"
                ></base-switch>
              </div>
            </div>
            <div class="col-lg-3  form-inline">
              <b class="card-title1 mr-3 ">For Managers</b>
              <div class="d-flex justify-content-center">
                <base-switch
                  class="mr-1"
                  type="success"
                  v-model="course.formanager"
                ></base-switch>
              </div>
            </div>
            <div class="col-lg-3  form-inline">
              <b class="card-title1 mr-3 ">For Employees</b>
              <div class="d-flex justify-content-center">
                <base-switch
                  class="mr-1"
                  type="success"
                  v-model="course.foremployee"
                ></base-switch>
              </div>
            </div>
          </div>
        </div>
        <hr />
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit(saveCourse)"
            enctype="multipart/form-data"
          >
            <div class="form-row">
              <div class="col-md-6">
                <div class="form-row">
                  <div class="col-md-6">
                    <base-input
                      label="Course Name *"
                      name="Course Name"
                      placeholder="Course Name"
                      rules="required"
                      v-model="course.course_name"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <base-input
                      label="Course Name On Certificate *"
                      name="Name As Per Certificate"
                      :maxlength="max"
                      placeholder="Course Name As Per Certificate"
                      rules="required"
                      v-model="course.course_name_certificate"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-12">
                    <base-input
                      label="Secondary Course Name"
                      name="Secondary Course Name"
                      placeholder="Secondary Course Name"
                      rules="required"
                      v-model="course.secondary_course_name"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <base-input
                      label="Course Length (mins) *"
                      name="Course Length"
                      placeholder="Course Length"
                      type="number"
                      min="1"
                      rules="required"
                      v-model="course.course_length"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <label class="form-control-label">Course Cost *</label>
                    <money
                      class="form-control"
                      v-model="course.course_cost"
                      v-bind="money"
                    ></money>
                  </div>
                  <div class="col-md-6">
                    <base-input
                      label="Attempts *"
                      name="Allowed Attempts"
                      type="number"
                      min="1"
                      placeholder=""
                      v-model="course.allowed_attempts"
                    >
                    </base-input>
                  </div>

                  <div class="col-md-6">
                    <label class="form-control-label">Passing Rate</label>
                    <br />
                    <base-input
                      v-model="course.course_pass_rate"
                      value=""
                      type="text"
                      placeholder="Pass Rate"
                    ></base-input>
                  </div>
                </div>
                <div class="form-row">
                  <div class="col-md-6">
                    <base-input
                      type="number"
                      min="0"
                      label="Employee Days to Complete"
                      name="Employee Days to Complete:"
                      placeholder=""
                      v-model="course.employees_days_to_complete"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <base-input
                      type="number"
                      min="0"
                      label="Managers Days to Complete"
                      name="Managers Days to Complete"
                      placeholder=""
                      v-model="course.manager_days_to_complete"
                    >
                    </base-input>
                  </div>
                </div>
                <div class="form-row">
                  <div class="col-md-6">
                    <base-input
                      label="Pass Message"
                      name="Pass Message"
                      placeholder="Pass Message"
                      v-model="course.course_passmessage"
                    >
                    </base-input>
                  </div>
                  <div class="col-md-6">
                    <base-input
                      label="Fail Message"
                      name="Fail Message"
                      placeholder="Fail Message"
                      v-model="course.course_failmessage"
                    >
                    </base-input>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-control-label">Course Description *</label>
                <vue-editor v-model="course.course_description"></vue-editor>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-3">
                <base-input label="Next Course">
                  <el-select
                    filterable
                    placeholder="None"
                    v-model="course.nextcourse"
                  >
                    <el-option
                      v-for="option in courses"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="option.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>

              <div class="col-md-2" v-if="course.nextcourse != '0'">
                <base-input
                  type="number"
                  label="Assignment Gap (Days)"
                  name="Assignment Gap (Days)"
                  placeholder="Assignment Gap"
                  v-model="course.assignment_gap"
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <base-input label="Company Specific">
                  <el-select
                    filterable
                    placeholder="Select"
                    v-model="course.companyspecific"
                  >
                    <el-option
                      v-for="option in company_specific"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="option.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
              <div class="col-md-2" v-if="course.companyspecific">
                <el-popover
                  ref="fromPopOver"
                  placement="top-start"
                  width="250"
                  trigger="hover"
                >
                  <span style="display: flex; justify-content: center;">
                    This Course will Automatically assign to
                    {{ course.companyspecific.label }} Company.
                  </span>
                </el-popover>
                <span>
                  <i
                    v-popover:fromPopOver
                    class="fa fa-info-circle
                    text-blue"
                  />
                </span>
              </div>
              <div class="col-md-6" v-if="!course.companyspecific">
                <base-input label="Assigned Companies">
                  <el-select
                    filterable
                    multiple
                    placeholder="Select"
                    v-model="course.assigned_companies_id"
                  >
                    <el-option
                      v-for="option in companies"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="option.value"
                    >
                    </el-option>
                  </el-select>
                </base-input>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-3">
                <input type="checkbox" v-model="course.course_type" />
                Compliance Course
              </div>
              <div class="col-md-4">
                <input type="checkbox" v-model="course.course_2fa" /> Require 2
                Factor Authentication
              </div>
              <div class="col-md-3">
                <input type="checkbox" v-model="course.discounted_course" />
                Discounted Course
              </div>
              <div class="col-md-2">
                <input type="checkbox" v-model="course.weekly_report" />
                Include Weekly Report
              </div>
              <div class="col-md-2" v-if="course_id == 123">
                <input
                  type="checkbox"
                  v-model="course.food_safe_online_proctored_exam"
                />
                Food Safe Online Proctored Exam
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-12" v-if="!course.discounted_course">
                <label class="form-control-label"
                  >Discounted Course Comment</label
                >
                <textarea
                  class="form-control"
                  rows="20"
                  v-model="course.discounted_course_comment"
                ></textarea>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-3">
                <input type="checkbox" v-model="course.reassignment_expiry" />
                Reassign On Expire
              </div>
              <div class="col-md-3" v-if="course.reassignment_expiry">
                <base-input
                  type="number"
                  min="0"
                  label="# of attempts before Expiration"
                  name="# of attempts before Expiration"
                  placeholder=""
                  v-model="course.expiry_attempts"
                >
                </base-input>
              </div>
            </div>
            <hr />
            <div class="row mt-2">
              <div class="col-md-12">
                <h3>Course Resources</h3>
                <base-input label="Resources">
                  <el-select
                    filterable
                    multiple
                    placeholder="Select"
                    v-model="course.courseResources"
                  >
                    <el-option
                      v-for="courseResource in courseResources"
                      class="select-primary"
                      :value="courseResource.id"
                      :label="courseResource.name"
                      :key="courseResource.id"
                    ></el-option
                  ></el-select>
                </base-input>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-md-3 border-right">
                <div class="row ">
                  <div class="col-md-12">
                    <h3>Course Lessons</h3>
                  </div>
                </div>
                <div
                  class="row p-1"
                  v-for="(lesson, index) in course.course_lessons"
                  :key="lesson.id"
                >
                  <div class="col-md-12">
                    <span class="cursor"
                      ><b v-on:click="openThisLesson(index)"
                        ><a class="href">{{ lesson.lesson_name }}</a></b
                      ></span
                    >
                    <base-button
                      class="btn btn-danger  btn-sm pull-right"
                      type="danger"
                      size="sm"
                      style="float:right;"
                      @click.prevent="removeLesson(index)"
                    >
                      <i class="fa fa-trash "></i>
                    </base-button>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <label v-on:click="addLessonClicked()">
                      <i class="fas fa-plus-circle mr-1"></i
                      ><b class="add_text">Add Lesson</b>
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-3 border-right">
                <div class="row">
                  <div class="col-md-12">
                    <h3>Course Test</h3>
                  </div>
                </div>
                <div
                  class="row"
                  v-for="(test, index) in course.course_test"
                  :key="test.id"
                >
                  <div class="col-md-12">
                    <span
                      ><b v-on:click="openThisTest(index)"
                        ><a class="href">{{ "Test " + (index + 1) }}</a></b
                      ></span
                    >
                    <base-button
                      class="btn btn-danger  btn-sm pull-right"
                      type="danger"
                      size="sm"
                      style="float:right;"
                      @click.prevent="removeTest(index)"
                    >
                      <i class="fa fa-trash"></i>
                    </base-button>
                  </div>
                </div>

                <div class="row" v-if="course.course_test.length == 0">
                  <div class="col-md-12">
                    <label class="cursor" v-on:click="addTestClicked()">
                      <i class="fas fa-plus-circle mr-1"></i
                      ><b class="add_text">Add Test</b>
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-3 border-right">
                <div class="row">
                  <div class="col-md-12">
                    <h3>Pre Test</h3>
                  </div>
                </div>
                <div
                  class="row"
                  v-for="(pretest, index) in course.course_pretest"
                  :key="pretest.id"
                >
                  <div class="col-md-12">
                    <span
                      ><b v-on:click="openThisPreTest(index)"
                        ><a class="href">{{ pretest.name }}</a></b
                      ></span
                    >
                    <base-button
                      class="btn btn-danger  btn-sm pull-right"
                      type="danger"
                      size="sm"
                      style="float:right;"
                      @click.prevent="removePreTest(index)"
                    >
                      <i class="fa fa-trash"></i>
                    </base-button>
                  </div>
                </div>

                <div class="row" v-if="course.course_pretest == 0">
                  <div class="col-md-12">
                    <label class="cursor" v-on:click="addPreTestClicked()">
                      <i class="fas fa-plus-circle mr-1"></i
                      ><b class="add_text">Add Pre Test</b>
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-3 border-right">
                <div class="row">
                  <div class="col-md-12">
                    <h3>Survey Test</h3>
                  </div>
                </div>
                <div
                  class="row"
                  v-for="(surveytest, index) in course.course_surveytest"
                  :key="surveytest.id"
                >
                  <div class="col-md-12">
                    <span
                      ><b v-on:click="openThisSurveyTest(index)"
                        ><a class="href">{{ surveytest.name }}</a></b
                      ></span
                    >
                    <base-button
                      class="btn btn-danger  btn-sm pull-right"
                      type="danger"
                      size="sm"
                      style="float:right;"
                      @click.prevent="removeSurveyTest(index)"
                    >
                      <i class="fa fa-trash"></i>
                    </base-button>
                  </div>
                </div>

                <div class="row" v-if="course.course_surveytest == 0">
                  <div class="col-md-12">
                    <label class="cursor" v-on:click="addSurveyTestClicked()">
                      <i class="fas fa-plus-circle mr-1"></i
                      ><b class="add_text">Add Survey Test</b>
                    </label>
                  </div>
                  <el-select
                    placeholder="Select Survey"
                    class="ml-2 mr-2"
                    rules="required"
                    v-model="course.course_survey"
                  >
                    <el-option
                      v-for="option in surveyData"
                      class="select-primary mr-3"
                      :value="option.id"
                      :label="option.survey_name"
                      :key="option.id"
                    >
                    </el-option>
                  </el-select>
                </div>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-md-3  form-inline">
                <label class="form-control-label mr-3 "
                  ><b>Allow Certificate</b></label
                ><br />
                <div class="d-flex">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="course.certificateavilable"
                  ></base-switch>
                </div>
              </div>

              <div
                class="col-md-4 form-inline"
                v-if="course.certificateavilable"
              >
                <el-select
                  placeholder="Select Certificate"
                  class="mt-3 w-100"
                  rules="required"
                  v-model="course.course_certificate"
                >
                  <el-option
                    v-for="option in certificate_Data"
                    class="select-primary mr-3"
                    :value="option.id"
                    :label="option.certificate_name"
                    :key="option.id"
                  >
                  </el-option>
                </el-select>
              </div>
              <div class="col-md-5">
                <base-input
                  label="Certificate Valid For (Days)"
                  name="Certificate Valid For (Days)"
                  placeholder="Certificate Validity"
                  v-model="course.certificate_validity"
                >
                </base-input>
              </div>
            </div>

            <div class="text-right">
              <base-button
                class="custom-btn mr-3"
                type="danger"
                @click="$router.go(-1)"
                >Cancel</base-button
              >
              <base-button class="custom-btn" @click.prevent="saveCourse">
                {{ "Submit" }}
              </base-button>
            </div>
          </form>
        </validation-observer>
        <!--            lesson modal-->
        <modal :show.sync="lessonModal" class="user-modal">
          <h4
            slot="header"
            class="modal-title mb-0"
            style="color: #272C33;"
            v-if="!lesson_preview"
          >
            Add New Lesson
          </h4>
          <h4
            slot="header"
            class="modal-title mb-0"
            style="color: #272C33;"
            v-if="lesson_preview"
          >
            <small>Course Name:</small> {{ course.course_name }}
          </h4>
          <form v-if="!lesson_preview">
            <div class="row">
              <div class="col-md-12 text-right mb-3">
                <base-button class="custom-btn" @click.prevent="saveLesson">
                  Save Lesson
                </base-button>
              </div>
              <div class="col-md-4">
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
                  class="select-primary w-100"
                  v-model="lesson.lesson_type"
                  placeholder="Select Type"
                >
                  <el-option
                    class="select-primary w-100"
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
              <div
                class="col-md-3"
                v-if="lesson.lesson_type == 'youtube-video'"
              >
                <base-input
                  label="YouTube Id"
                  placeholder="Youtube Id"
                  type="text"
                  v-model="lesson.youtube_lesson_video_url"
                >
                </base-input>
              </div>
              <div class="col-md-3" v-if="lesson.lesson_type == 'pdf'">
                <div class="" v-if="!lesson.lesson_pdf_url">
                  <label>Upload Pdf</label>
                  <file-input v-on:change="onImageChange"></file-input>
                </div>
                <div v-else>
                  <a
                    :href="
                      baseUrl + '/employee/documents/' + lesson.lesson_pdf_url
                    "
                    target="_blank"
                    >{{ lesson.lesson_pdf_url }}</a
                  >
                </div>
              </div>
              <div class="col-md-3">
                <label class="form-control-label "
                  ><b>Next Button Timer:</b></label
                >
                <div class="d-flex">
                  <base-switch
                    class="mb-1"
                    type="success"
                    id="status-switch"
                    v-model="lesson.nextButtonTimerStatus"
                  ></base-switch>
                </div>
              </div>
              <div class="col-md-2" v-if="lesson.nextButtonTimerStatus">
                <label class="form-control-label "><b>Select Timing:</b></label>

                <vue-timepicker
                  format="HH:mm:ss"
                  v-model="lesson.timerValue"
                ></vue-timepicker>
              </div>
              <div class="col-md-2">
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

              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-control-label">Lesson Content</label>
                  <vue-editor
                    placeholder="Lesson Content Here..."
                    v-model="lesson.lesson_content"
                  ></vue-editor>
                </div>
              </div>
            </div>

            <div v-if="lesson.quizStatus">
              <div class="row">
                <div class="col-md-4">
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
                <div class="col-md-4">
                  <label class="form-control-label">Passing Rate</label>
                  <base-input
                    v-model="lesson.passing_rate"
                    min="0"
                    max="100"
                    value=""
                    type="text"
                    placeholder="Pass Rate"
                  ></base-input>
                </div>
                <div class="col-md-4">
                  <base-input
                    type="number"
                    min="1"
                    label="Number of Questions"
                    placeholder=""
                    v-model="lesson.no_of_questions"
                  >
                  </base-input>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="form-control-label">Quiz Instruction: </label>
                    <vue-editor
                      placeholder="Quiz Instructions here..."
                      v-model="lesson.lesson_quiz_instruction"
                    ></vue-editor>
                  </div>
                </div>
              </div>
              <div
                class="brdr question_box mt-3"
                v-for="(question, q_index) in lesson.lesson_questions"
                :key="question.id"
              >
                <div class="row align-items-center mb-4">
                  <div class="col-md-9">
                    <h4 style=" color: #272C33;">Question {{ q_index + 1 }}</h4>
                  </div>
                  <div class="col-md-2 col-6">
                    <div
                      class="d-flex justify-content-md-end"
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
                  <div class="col-md-1 col-6">
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
                      <div class="col-md-6">
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
                      <div class="col-md-6">
                        <div class="currest-ans-label">
                          <label class="form-control-label"
                            >Correct Answer</label
                          >
                        </div>
                        <div
                          class="row"
                          v-for="(option, o_index) in question.options"
                          :key="option.id"
                        >
                          <div class="col-sm-8 col-7">
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
                          <div class="col-sm-4  col-5">
                            <div class="que-ans-row">
                              <div>
                                <base-checkbox
                                  class="pull-right"
                                  v-model="option.correct"
                                ></base-checkbox>
                              </div>
                              <div>
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
            <div class="text-right">
              <base-button class="custom-btn" @click.prevent="saveLesson">
                Save Lesson
              </base-button>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <base-button class="custom-btn" @click.prevent="previewLesson">
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
                      <b>Question {{ index + 1 }} </b>
                      {{ question.question_text }}
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
              <base-button
                type="default"
                size="sm"
                @click.prevent="backToLesson"
              >
                Back
              </base-button>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <base-button type="primary" size="sm" @click.prevent="saveLesson">
                Save
              </base-button>
            </div>
          </div>
        </modal>
        <!--            end of lesson modal-->
        <!--            test modal-->
        <modal :show.sync="testModal" class="user-modal">
          <h4
            v-if="main_test && !test_preview && !test_question"
            style="color: #444C57;"
            slot="header"
            class="modal-title mb-0"
          >
            Course Test
          </h4>
          <h4
            v-if="!main_test && !test_preview && test_question"
            style="color: #444C57;"
            slot="header"
            class="modal-title mb-0"
          >
            Test Question
          </h4>
          <h4
            v-if="!main_test && test_preview && !test_question"
            style="color: #444C57;"
            slot="header"
            class="modal-title mb-0"
          >
            Test Question Preview
          </h4>
          <form v-if="main_test && !test_preview && !test_question">
            <div class="row">
              <div class="mb-2 col-md-12 text-right">
                <base-button class="custom-btn" @click.prevent="saveTest">
                  Save Test
                </base-button>
                <base-button
                  class="custom-btn"
                  v-on:click="testQuestionClicked()"
                  style="float:right;"
                >
                  <i
                    slot="label"
                    class=" nc-icon nc-cloud-upload-94 mr-2 icon_btn"
                  ></i>
                  <span class="">Upload Test Questions</span>
                </base-button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2">
                <base-checkbox
                  label="Practice test"
                  v-model="test.practice_test"
                  >Practice test</base-checkbox
                >
              </div>
              <div class="col-md-3" v-if="test.practice_test">
                <div>
                  <base-input
                    label="After # of questions enable submit button"
                    type="number"
                    min="1"
                    v-model="test.enable_submit_button"
                  >
                  </base-input>
                </div>
              </div>
              <div class="col-md-2">
                <div>
                  <base-input
                    label="Number of questions"
                    type="number"
                    min="1"
                    v-model="test.test_no_of_questions"
                  >
                  </base-input>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-control-label">Pass Message</label>
                  <textarea
                    rows="1"
                    class="form-control border-input"
                    placeholder="Pass Message..."
                    v-model="test.test_pass_message"
                  >
                  </textarea>
                  <span
                    class="text-danger small"
                    v-if="test_save_clicked && test.test_pass_message === ''"
                    >Pass Message Field is Required!</span
                  >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-control-label">Fail Message</label>
                  <textarea
                    rows="1"
                    class="form-control border-input"
                    placeholder="Fail Message..."
                    v-model="test.test_fail_message"
                  >
                  </textarea>
                  <span
                    class="text-danger small"
                    v-if="test_save_clicked && test.test_fail_message === ''"
                    >Fail Message Field is Required!</span
                  >
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-control-label">Test Instruction</label>
                  <vue-editor v-model="test.test_instruction"></vue-editor>
                  <span
                    class="text-danger small"
                    v-if="test_save_clicked && test.test_instruction === ''"
                    >Test Instruction Field is Required!</span
                  >
                </div>
              </div>
            </div>
            <div
              class="brdr question_box mt-3"
              v-for="(question, q_index) in test.test_questions"
              :key="question.id"
            >
              <div class="row align-items-center mb-4">
                <div class="col-md-9">
                  <h4 style="color: #444C57;">Question {{ q_index + 1 }}</h4>
                </div>
                <div class="col-md-2 col-6 ">
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
                <div class="col-md-2 col-6">
                  <base-button
                    type="danger"
                    style="float:right;"
                    size="sm"
                    @click.prevent="removeTestQuestion(q_index)"
                  >
                    <i class="fa fa-trash"></i>
                  </base-button>
                </div>
              </div>
              <div class="row ">
                <div class="col-md-12">
                  <div class="row">
                    <div class="col-md-6">
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
                          isRequired &&
                            test_save_clicked &&
                            question.question_text === '' &&
                            question.question_status
                        "
                        >Question Field is Required!</span
                      >
                    </div>
                    <div class="col-md-6">
                      <div class="currest-ans-label">
                        <label class="form-control-label">Correct Answer</label>
                      </div>

                      <div
                        class="row"
                        v-for="(option, o_index) in question.options"
                        :key="option.id"
                      >
                        <div class="col-sm-8 col-7">
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
                              isRequired &&
                                test_save_clicked &&
                                option.option_text === '' &&
                                question.question_status
                            "
                            >Option Field is Required!</span
                          >
                        </div>
                        <div class="col-sm-4  col-5">
                          <div class="que-ans-row">
                            <div>
                              <base-checkbox
                                class="pull-right"
                                v-model="option.correct"
                              ></base-checkbox>
                            </div>
                            <div>
                              <base-button
                                size="sm"
                                type="danger"
                                @click.prevent="
                                  removeTestOption(q_index, o_index)
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
                            style="color: #444C57;"
                            class=" cursor"
                            v-on:click="addOptionTest(q_index)"
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
                  class=" cursor"
                  style="color: #444C57;"
                  v-on:click="addQuestiontest()"
                  ><b>+</b>Add Another Question</label
                >
              </div>
            </div>
            <div class="text-right">
              <base-button
                class="custom-btn"
                size="md"
                @click.prevent="saveTest"
              >
                Save Test
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
          <form v-if="!main_test && !test_preview && test_question">
            <div class="row">
              <div class="col-md-3"></div>
              <div class="col-md-6">
                <label class="form-control-label">Upload Test Questions</label>
                <div>
                  <span class="">
                    <!-- accept=" .xlsx,csv,application/vnd.ms-excel" -->
                    <input
                      type="file"
                      name="..."
                      class="form-control "
                      v-on:change="getTestFile($event)"
                    />
                  </span>
                </div>
                <h5 class="mt-2">
                  <a
                    class="underline-class"
                    href="/assets/uploadBulkTestQuestionAnswerFormat.xlsx"
                    download
                    style="padding-right:5px; color:green;"
                    >Click here</a
                  >
                  to download upload format.
                </h5>
              </div>
              <div class="col-md-3"></div>
            </div>
            <div class="text-right mt-2">
              <base-button class="custom-btn" @click.prevent="openTestPreview">
                Upload Test
              </base-button>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <base-button class="custom-btn" @click.prevent="backToMainTest">
                Cancel
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
          <form v-if="!main_test && test_preview && !test_question">
            <div class="text-center my-3">
              <button
                type="submit"
                style="border:2px solid #272C33;background-color: transparent; color: #272C33;"
                class="btn  mt-3 btn-wd"
                @click.prevent="backToTestQuestion"
              >
                Back</button
              >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <button
                type="submit"
                style="background-color: #444C57;border:2px solid #444C57;"
                class="btn  mt-3  btn-wd"
                @click.prevent="saveTest"
              >
                Save Test
              </button>
            </div>
            <div class="clearfix"></div>
          </form>
        </modal>
        <!--            end of test modal-->
        <!--                certificate modal-->
        <modal :show.sync="certificateModal">
          <h4 slot="header" style="color: #444C57" class="title title-up ">
            Add New Certificate
          </h4>
          <form>
            <div class="row">
              <div class="col-md-12">
                <h5 style="color: #444C57" class=" text-center">
                  Step 1: Enter Global Information
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3"></div>
              <div class="col-md-6 col-12">
                <base-input
                  type="text"
                  label="Certificate Name"
                  placeholder="Certificate Name"
                  v-model="certificate.certificate_name"
                >
                </base-input>
              </div>
            </div>

            <hr />
            <div class="row">
              <div class="col-md-12">
                <h5 style="color: #444C57" class=" text-center">
                  Step 2: Customization
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3"></div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <base-input
                    label="Signature Title 1"
                    name="Signature Title 1"
                    placeholder="Signature Title 1"
                    rules="required"
                    v-model="certificate.certificate_signature_title_1"
                  >
                  </base-input>
                </div>
              </div>
              <div class="col-md-3"></div>
              <div class="col-md-3"></div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <base-input
                    label="Signature Title 2"
                    name="Signature Title 2"
                    placeholder="Signature Title 2"
                    v-model="certificate.certificate_signature_title_2"
                  >
                  </base-input>
                </div>
              </div>
              <div class="col-md-3"></div>
              <div class="col-md-3"></div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label>Custom Text</label>
                  <textarea
                    rows="1"
                    class="form-control border-input"
                    placeholder="Custom Text Here..."
                    v-model="certificate.certificate_custom_text"
                  >
                  </textarea>
                </div>
              </div>
              <div class="col-md-3"></div>
            </div>
            <div class="text-center my-4">
              <base-button
                type="primary"
                size="sm"
                @click.prevent="saveCertificates"
              >
                Add Certificate
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
        </modal>
        <modal :show.sync="pretestModal" class="user-modal">
          <h2 slot="header" class="modal-title mb-0 ">
            Add Pre Test
          </h2>
          <div class="row">
            <div class="col-md-12 text-right">
              <base-button class="custom-btn" @click.prevent="savePreTest">
                Save Pre Test
              </base-button>
            </div>

            <div class="col-md-12">
              <base-input
                v-model="pretest.name"
                label="Title *"
                placeholder="Enter pre test title"
              ></base-input>
            </div>

            <div class="col-md-12">
              <label class="form-control-label">Instructions *</label>
              <vue-editor v-model="pretest.instructions"></vue-editor>
            </div>
          </div>

          <div
            class="brdr mt-3  question_box"
            v-for="(question, q_index) in pretest.pretest_questions"
            :key="question.id"
          >
            <div class="">
              <div class="row align-items-center">
                <div class="col-md-3">
                  <h4 style="color: #444C57;">Question {{ q_index + 1 }}</h4>
                </div>
                <div class="col-md-4">
                  <label
                    class="form-control-label"
                    v-if="question.answer_type == 1"
                    >Map Field: </label
                  >&nbsp;
                  <el-select
                    v-if="question.answer_type == 1"
                    v-model="question.is_update_employee"
                  >
                    <el-option
                      v-for="option in updateEmployeeOptions"
                      class="select-primary"
                      :value="option.value"
                      :label="option.label"
                      :key="option.value"
                    >
                    </el-option>
                  </el-select>
                  &nbsp;
                  <el-popover
                    v-if="question.answer_type == 1"
                    ref="fromPopOver"
                    placement="top-start"
                    width="250"
                    trigger="hover"
                  >
                    <span style="display: flex; justify-content: center;">
                      Selected option will be updated with user information.
                    </span>
                  </el-popover>
                  <span v-if="question.answer_type == 1">
                    <i
                      v-popover:fromPopOver
                      class="fa fa-info-circle
                    text-blue"
                    />
                  </span>
                </div>
                <div class="col-md-4 col-6 ">
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
                    style="float:right;"
                    size="sm"
                    @click.prevent="removePreTestQuestion(q_index)"
                  >
                    <i class="fa fa-trash"></i>
                  </base-button>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 questionbox">
                <textarea
                  class="form-control form-control mt-2"
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
                    <label class="form-control-label">Select Validation</label>
                  </div>
                  <div class="col-md-12">
                    <el-select
                      v-model="question.checked_validations"
                      class="w-100"
                    >
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
                <label class="form-control-label" style="color: #28c0e7;"
                  >Preview</label
                >

                <base-input
                  v-model="previewPhone"
                  v-if="question.checked_validations == 1"
                  placeholder="(555) 555-5555"
                  @input="acceptNumber"
                ></base-input>
                <base-input
                  type="email"
                  name="Email"
                  v-model="previewEmail"
                  v-if="question.checked_validations == 2"
                  placeholder="Enter valid email"
                ></base-input>
                <base-input
                  v-model="previewText"
                  v-if="question.checked_validations == 3"
                  placeholder="Enter Text"
                ></base-input>
                <el-date-picker
                  v-model="previewDate"
                  v-if="question.checked_validations == 4"
                  style="width: 100%"
                  type="date"
                  placeholder="Pick a day"
                  format="MM/dd/yyyy"
                  :picker-options="pickerOptions1"
                >
                </el-date-picker>
                <base-input
                  v-model="previewSsn"
                  v-if="question.checked_validations == 5"
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
                              pretest_save_clicked &&
                              option.answer === '' &&
                              question.question_status
                          "
                          >Option Field is Required!</span
                        >
                      </div>
                      <div class="col-sm-5  col-5 mt-md-5">
                        <base-button
                          size="sm"
                          type="danger"
                          @click.prevent="removePreTestOption(q_index, o_index)"
                        >
                          <i class="fa fa-trash"></i>
                        </base-button>
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
          <div class="row mt-2">
            <div class="col-md-12">
              <label
                class=" cursor"
                style="color: #444C57;"
                v-on:click="addQuestionPretest()"
                ><b>+</b>Add Another Question</label
              >
            </div>
          </div>
          <div class="text-right">
            <base-button
              class="custom-btn"
              size="md"
              @click.prevent="savePreTest"
            >
              Save Pre Test
            </base-button>
          </div>
        </modal>
        <!-- Survey  -->
        <modal :show.sync="surveytestModal" class="user-modal">
          <h4 slot="header" class="modal-title mb-0">
            Add Survey Test
          </h4>
          <div class="row">
            <div class="col-md-12 text-right">
              <base-button class="custom-btn" @click.prevent="saveSurveyTest">
                Save Survey Test
              </base-button>
            </div>
            <div class="col-md-12">
              <base-input
                v-model="surveytest.name"
                label="Title *"
                placeholder="Enter Survey test title"
              ></base-input>
            </div>
            <div class="col-md-12">
              <label class="form-control-label">Instructions *</label>
              <vue-editor v-model="surveytest.instructions"></vue-editor>
            </div>
          </div>

          <div
            class="brdr question_box mt-3"
            v-for="(question, q_index) in surveytest.surveytest_questions"
            :key="question.id"
          >
            <div class="row align-items-center">
              <div class="col-md-7">
                <h4 style="color: #444C57;">Question {{ q_index + 1 }}</h4>
              </div>
              <div class="col-md-4 col-6 ">
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
              <div class="col-md-1 col-6 ">
                <base-button
                  type="danger"
                  style="float:right;"
                  size="sm"
                  @click.prevent="removeSurveyTestQuestion(q_index)"
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
                      surveytest_save_clicked &&
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
                    <el-select
                      v-model="question.checked_validations"
                      class="w-100"
                    >
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
                <label class="form-control-label">Preview </label>
                <base-input
                  v-model="previewPhone"
                  v-if="question.checked_validations == 1"
                  placeholder="(555) 555-5555"
                  @input="acceptNumber"
                ></base-input>
                <base-input
                  type="email"
                  name="Email"
                  v-model="previewEmail"
                  v-if="question.checked_validations == 2"
                  placeholder="Enter valid email"
                ></base-input>
                <base-input
                  v-model="previewText"
                  v-if="question.checked_validations == 3"
                  placeholder="Enter Text"
                ></base-input>
                <el-date-picker
                  v-model="previewDate"
                  v-if="question.checked_validations == 4"
                  style="width: 100%"
                  type="date"
                  placeholder="Pick a day"
                  format="MM/dd/yyyy"
                  :picker-options="pickerOptions1"
                >
                </el-date-picker>
                <base-input
                  v-model="previewSsn"
                  v-if="question.checked_validations == 5"
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
                              surveytest_save_clicked &&
                              option.answer === '' &&
                              question.question_status
                          "
                          >Option Field is Required!</span
                        >
                      </div>
                      <div class="col-sm-5  col-5 mt-md-5">
                        <base-button
                          size="sm"
                          type="danger"
                          @click.prevent="
                            removeSurveyTestOption(q_index, o_index)
                          "
                        >
                          <i class="fa fa-trash"></i>
                        </base-button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-7">
                        <label
                          style="color: #444C57;"
                          class=" cursor"
                          v-on:click="addSurveyOptionTest(q_index)"
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
              <label
                class=" cursor"
                style="color: #444C57;"
                v-on:click="addQuestionSurveytest()"
                ><b>+</b>Add Another Question</label
              >
            </div>
          </div>
          <div class="text-right">
            <base-button class="custom-btn" @click.prevent="saveSurveyTest">
              Save Survey Test
            </base-button>
          </div>
        </modal>
           <!--                Gamification modal-->
        <modal :show.sync="gamifiedModel" v-on:close="closegamification()">
          <h4 slot="header" style="color: #444c57" class="modal-title mb-0">
            Add Content
          </h4>
          <form>
            <div class="row">
              <div
                class="col-md-12 mt-1"
                v-for="(content, c_index) in lesson.gamified_content"
                :key="c_index"
              >
                <span :id="'hidegamfication_' + c_index">
                  <h4>Content {{ c_index + 1 }}</h4>
                  <vue-editor :editorOptions="editorSettings" v-model="content.text"></vue-editor>
                </span>
               
              </div>
            </div>
            <div class="row mt-4">
              <div class="col-md-6">
                <base-button
                  v-if="currindex == '0'"
                  disabled
                  style="float: left"
                  size="sm"
                  class="previous"
                  @click="removeAnotherEditor()"
                  >Previous</base-button
                >
                <base-button
                  v-else
                  style="float: left"
                  size="sm"
                  class="previous"
                  @click="removeAnotherEditor()"
                  >Previous</base-button
                >
              </div>
              <div class="col-md-6">
                <base-button
                  style="float: right"
                  size="sm"
                  @click="addAnotherEditor()"
                  >Next</base-button
                >
              </div>
              <div class="col-md-12 mt-4">
                <base-button style="float: right" @click="closegamification"
                  >Done</base-button
                >
              </div>
            </div>
            <div class="clearfix"></div>
          </form>
          <!---------- Gamification close ---------->
        </modal>
      </card>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import { DatePicker, Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
// import { TheMask } from "vue-the-mask";
import { Money } from "v-money";
import FileInput from "@/components/Inputs/FileInput";
import { Modal, BaseAlert } from "@/components";
import { VueEditor,Quill } from "vue2-editor";
import ImageResize from "quill-image-resize-vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import BaseCheckbox from "../../components/Inputs/BaseCheckbox.vue";
import VueTimepicker from "vue2-timepicker";
import "vue2-timepicker/dist/VueTimepicker.css";
Quill.register("modules/imageResize", ImageResize);
Vue.component("v-select", vSelect);
export default {
  components: {
    // PreTest,
    VueTimepicker,
    [DatePicker.name]: DatePicker,
    VueEditor,
    Modal,
    // TheMask,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    FileInput,
    Money,
    BaseCheckbox
  },
  data() {
    return {
       editorSettings: {
      modules: {
          imageResize: {}
        }
      },
      currindex: 0,
      hideGamificationBlock: true,
      closegamification:false,
      baseUrl: this.$baseUrl,
      checked_validations: "",
      loading: false,
      duplicateFlag: false,
      pretestModal: false,
      saving: false,
      hot_user: "",
      hot_token: "",
      config: "",
      //   courses_data: [],
      certificate_Data: [],
      surveyData: [],
      percentage: {
        thousands: ",",
        prefix: "",
        suffix: "%",
        masked: false /* doesn't work with directive */
      },
      money: {
        decimal: ".",
        thousands: ",",
        prefix: "$",
        suffix: "",
        precision: 2,
        masked: false /* doesn't work with directive */
      },
      lesson_preview: false,
      test_question: false,
      main_test: true,
      test_preview: false,
      updating: false,

      lesson_save_clicked: false,
      isRequired: true,
      test_save_clicked: false,
      courses: [],
      company_specific: [],
      question_type: [
        {
          label: "Text",
          value: "1"
        },
        {
          label: "Option",
          value: "2"
        }
      ],
      max: 38,
      course: {
        live: true,
        status: true,
        course_name: "",
        secondary_course_name:"",
        course_name_certificate: "",
        course_length: "",
        course_due: "",
        allowed_attempts: "",
        coursestatus: "",
        course_passmessage: "",
        course_failmessage: "",
        course_cost: "",
        course_description: "",
        course_resources: [],
        course_pass_rate: "",
        formanager: true,
        foremployee: true,
        certificateavilable: true,
        nextcourse: "0",
        companyspecific: "",
        employees_days_to_complete: "",
        manager_days_to_complete: "",
        reassignment_expiry: true,
        expiry_attempts: "",
        certificate_validity: "",
        assignment_gap: "",
        course_lessons: [],
        course_test: [],
        course_pretest: [],
        course_surveytest: [],
        course_certificate: "",
        course_survey: "",
        course_type: false,
        course_2fa: false,
        discounted_course: true,
        discounted_course_comment: "",
        weekly_report: false,
        food_safe_online_proctored_exam: false,
        assigned_companies_id: [],
        courseResources: []
      },
      companies: [],
      lessonModal: false,
      certificateModal: false,
      testModal: false,
      lesson: {
        nextButtonTimerStatus: false,
        timerValue: "",
        quizStatus: false,
        lesson_type: "Select Type",
        lesson_name: "",
        allowed_attempts: "",
        passing_rate: "",
        no_of_questions: "",
        lesson_video_url: "",
        youtube_lesson_video_url: "",
        gamified_content: [
          {
            text: "",
          },
        ],
        lesson_pdf_url: "",
        lesson_content: "",
        lesson_quiz_instruction: "",
        lesson_questions: [
          {
            question_text: "",
            allowed_attempts: "",
            question_status: true,
            options: [
              {
                option_text: "",
                correct: false
              }
            ]
          }
        ]
      },
      processing: false,
      test: {
        practice_test: 0,
        enable_submit_button: "",
        test_instruction: "",
        test_pass_message: "",
        test_fail_message: "",
        test_no_of_questions: "",
        test_questions: [
          {
            question_text: "",
            question_status: true,
            allowed_attempts: "",
            options: [
              {
                option_text: "",
                correct: false
              }
            ]
          }
        ]
      },
      lessontype: [
        {
          label: "Vimeo video",
          value: "video"
        },
        {
          label: "YouTube video",
          value: "youtube-video"
        },
        {
          label: "Pdf",
          value: "pdf"
        },{
          label: "Gamification",
          value: "gamification",
        },
      ],
      updateEmployeeOptions: [
        {
          label: "Dob",
          value: 1
        },
        {
          label: "SSN",
          value: 2
        },
        {
          label: "Address",
          value: 3
        },
        {
          label: "City",
          value: 4
        },
        {
          label: "State",
          value: 5
        },
        {
          label: "Email",
          value: 6
        },
        {
          label: "Phone number",
          value: 7
        }
      ],
      resourceType: [
        {
          label: "Link",
          value: "link"
        },
        {
          label: "File",
          value: "file"
        }
      ],
      certificate: {
        certificate_name: "",
        course_id: "",
        certificate_date: "",
        certificate_valid_time: "",
        certificate_custom_text: "",
        certificate_signature_title_1: "",
        certificate_signature_title_2: ""
      },

      requiredFeilds: "",
      selected: {
        simple: "",
        countries: [],
        multiple: "ARS"
      },
      pretest_save_clicked: false,
      surveytest_save_clicked: false,
      excel_data: {
        test_question: "",
        option_1: "",
        option_2: "",
        option_3: "",
        option_4: "",
        correct_answer: "",
        question_status: "",
        file: ""
      },
      previewPhone: "",
      previewEmail: "",
      previewText: "",
      previewSsn: "",
      previewDate: "",
      answer_type: [
        {
          label: "Text",
          value: 1
        },
        {
          label: "Option",
          value: 2
        }
      ],
      validationtype: [
        {
          label: "Please Select validation",
          value: 0
        },
        {
          label: "Phone number",
          value: 1
        },
        {
          label: "Email",
          value: 2
        },
        {
          label: "Text",
          value: 3
        },
        {
          label: "Date",
          value: 4
        },
        {
          label: "SSN",
          value: 5
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
       gamifiedModel: false,
      surveytestModal: false,
      surveyQuizModal: false,
      pretestQuizModal: false,
      surveytest: {
        name: "",
        instructions: "",
        no_of_questions: "",
        type:"course",
        surveytest_questions: [
          {
            question: "",
            answer_type: "",
            question_status: true,
            checked_validations: "",
            answers: [
              {
                answer: "",
                correct_answer: false
              }
            ]
          }
        ]
      },
      courseResources: [],
      course_id: "",
      checkedNames: [],
      pretest: {
        name: "",
        instructions: "",
        no_of_questions: "",
        pretest_questions: [
          {
            question: "",
            is_update_employee: "",
            answer_type: "",
            question_status: true,
            checked_validations: "",
            answers: [
              {
                answer: "",
                correct_answer: false
              }
            ]
          }
        ]
      }
    };
  },
   watch: {
    "lesson.lesson_type"(val) {
      if (val === "gamification") {
       this.openGamificationModel();
      }
    },
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    this.config = {
      headers: {
        authorization: "Bearer " + localStorage.getItem("hot-token"),
        "content-type": "application/json"
      }
    };
    this.$http.get("course/unassignedCertificates", this.config).then(resp => {
      this.certificate_Data = [];
      for (let certificate of resp.data) {
        let obj = {
          id: certificate.id,
          certificate_name: certificate.name
        };
        this.certificate_Data.push(obj);
      }
    });
    this.getSurvey();

    if (this.$route.query.id) {
      let duplicate_id = this.$route.query.id;
      this.loading = true;
      this.$http
        .get("/course/edit/" + duplicate_id, this.config)
        .then(resp => {
          this.duplicateFlag = true;
          let data = resp.data[0];
          let course_obj = {
            live: "",
            status: "",
            formanager: "",
            foremployee: "",
            course_name: data.name,
            secondary_course_name: data.secondary_course_name,
            course_name_certificate: data.course_name_certificate,
            course_length: data.length,
            employees_days_to_complete: data.employees_days_to_complete,
            manager_days_to_complete: data.managers_days_to_complete,
            reassignment_expiry: data.reassignment_expiry,
            expiry_attempts: data.expiry_attempts,

            course_pass_rate: data.passing_percent,
            nextcourse: data.next_course,
            companyspecific: data.company_specific,
            course_type: data.course_type,
            course_2fa: data.is_2fa_required,
            discounted_course: data.is_discounted_course,
            discounted_course_comment: data.discounted_course_comment,
            weekly_report: data.is_weekly_report,
            food_safe_online_proctored_exam:
              data.food_safe_online_proctored_exam,
            assignment_gap: data.assignment_gap,
            certificate_validity: data.certificate_validity,
            certificateavilable: data.certificate_available,
            //course_due: data.due_days,
            allowed_attempts: data.allow_attempts,
            course_cost: data.cost,
            course_passmessage: data.pass_message,
            course_failmessage: data.fail_message,
            course_description: data.description,
            course_resources: [],
            course_lessons: [],
            course_test: [],
            course_pretest: [],
            course_surveytest: [],
            course_certificate: "",
            assigned_companies_id: [],
            courseResources: []
          };
          if (data.in_store === 1) {
            course_obj.live = true;
          } else if (data.in_store === 0) {
            course_obj.live = false;
          } else {
            course_obj.live = data.in_store;
          }
          if (data.for_managers === 1) {
            course_obj.formanager = true;
          } else if (data.for_managers === 0) {
            course_obj.formanager = false;
          } else {
            course_obj.formanager = data.for_managers;
          }
          if (data.for_employees === 1) {
            course_obj.foremployee = true;
          } else if (data.for_employees === 0) {
            course_obj.foremployee = false;
          } else {
            course_obj.formanager = data.for_employees;
          }

          if (data.certificate_available === 1) {
            course_obj.certificateavilable = true;
          } else if (data.certificate_available === 0) {
            course_obj.certificateavilable = false;
          } else {
            course_obj.certificateavilable = data.certificate_available;
          }
          let course_companies = data.course_companies;
          for (let companies of course_companies) {
            course_obj.assigned_companies_id.push(companies.id);
          }
          for (let courseResource of data.courseResources) {
            course_obj.courseResources.push(courseResource.id);
          }
          if (data.status === 1) {
            course_obj.status = true;
          } else if (data.status === 0) {
            course_obj.status = false;
          } else {
            course_obj.status = data.status;
          }
          let lesson_data = data.lessons;
          for (let lesson of lesson_data) {
            let lesson_obj = {
              id: lesson.id,
              allowed_attempts: lesson.allowed_attempts,
              course_passmessage: lesson.course_passmessage,
              lesson_name: lesson.course_lesson_name,
              lesson_type: lesson.type,
              passing_rate: lesson.passing_rate,
              quizStatus: "",
              no_of_questions: lesson.no_of_questions,
              lesson_content: lesson.course_lesson_content,
              lesson_quiz_instruction: lesson.course_lesson_quiz,
              lesson_questions: []
            };
            if (lesson.quiz_status == 1) {
              lesson_obj.quizStatus = true;
            } else if (lesson.quiz_status == 0) {
              lesson_obj.quizStatus = false;
            } else {
              lesson_obj.quizStatus = lesson.quiz_status;
            }
            if (lesson.type == "video" && lesson.course_lesson_video) {
              lesson_obj.lesson_video_url = lesson.course_lesson_video.substr(
                31
              );
            } else {
              lesson_obj.lesson_pdf_url = lesson.course_lesson_video;
            }
            for (let question of lesson.questions) {
              let question_obj = {
                id: question.id,
                question_text: question.question,
                // allowed_attempts:question.allowed_attempts,
                question_status: "",
                options: []
              };
              if (question.status === 1) {
                question_obj.question_status = true;
              } else if (question.status === 0) {
                question_obj.question_status = false;
              }
              for (let option of question.answers) {
                let option_obj = {
                  id: option.id,
                  option_text: option.course_quiz_question_option,
                  correct: ""
                };
                if (option.course_quiz_correct_answer === 1) {
                  option_obj.correct = true;
                } else if (option.course_quiz_correct_answer === 0) {
                  option_obj.correct = false;
                }
                question_obj.options.push(option_obj);
              }
              lesson_obj.lesson_questions.push(question_obj);
            }
            course_obj.course_lessons.push(lesson_obj);
          }

          let tests_data = data.tests;
          for (let test of tests_data) {
            let test_obj = {
              id: test.id,
              practice_test: test.practice_test,
              enable_submit_button: test.enable_submit_button,
              test_instruction: test.course_test_instruction,
              test_pass_message: test.course_test_pass_msg,
              test_fail_message: test.course_test_fail_msg,
              test_no_of_questions: test.course_no_of_questions,
              test_questions: []
            };
            for (let question of test.questions) {
              let question_obj = {
                id: question.id,
                question_text: question.question,
                allowed_attempts: question.allowed_attempts,
                question_status: "",
                options: []
              };
              if (question.status === 1) {
                question_obj.question_status = true;
              } else if (question.status === 0) {
                question_obj.question_status = false;
              }
              for (let option of question.answers) {
                let option_obj = {
                  id: option.id,
                  option_text: option.course_quiz_question_option,
                  correct: ""
                };
                if (option.course_quiz_correct_answer === 1) {
                  option_obj.correct = true;
                } else if (option.course_quiz_correct_answer === 0) {
                  option_obj.correct = false;
                }
                question_obj.options.push(option_obj);
              }
              test_obj.test_questions.push(question_obj);
            }
            course_obj.course_test.push(test_obj);
          }
          let pretest = data.pretest;
          if (pretest != null && pretest != "") {
            let pretest_obj = {
              id: pretest.id,
              name: pretest.name,
              instructions: pretest.instruction,
              pretest_questions: []
            };
            for (let question of pretest.questions) {
              let question_obj = {
                id: question.id,
                question: question.question,
                is_update_employee: question.is_update_employee,
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

              pretest_obj.pretest_questions.push(question_obj);
            }
            course_obj.course_pretest.push(pretest_obj);
          }

          let surveytests = data.survey;
          for (let surveytest of surveytests) {
            let surveytest_obj = {
              id: surveytest.id,
              name: surveytest.name,
              instructions: surveytest.instruction,
              surveytest_questions: []
            };
            for (let question of surveytest.questions) {
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
            course_obj.course_surveytest.push(surveytest_obj);
          }
          let certificate_data = data.certificates;
          for (let certificate of certificate_data) {
            course_obj.course_certificate = certificate.id;
          }
          this.course = course_obj;
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error"
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: "Something went wrong.",
              icon: "error"
            });
          }
        })
        .finally(() => (this.loading = false));
    }
    this.$http
      .post(
        "course/all_courses",
        {
          course_status: "Active"
        },
        this.config
      )
      .then(resp => {
        let courses = resp.data.courses;
        this.courses = [
          {
            label: "None",
            value: "0"
          },
          {
            label: "This Course",
            value: "this_course"
          }
        ];
        for (let course of courses) {
          let obj = {
            label: course.name,
            value: course.id
          };

          this.courses.push(obj);
        }
      });
    this.$http
      .post(
        "company/all_companies",
        {
          company_type: "parent"
        },
        this.config
      )
      .then(resp => {
        let comp_data = resp.data.companies;
        let let_obj = {
          label: "Select",
          value: 0
        };
        this.company_specific.push(let_obj);
        for (let data of comp_data) {
          let obj = {
            label: data.name,
            value: data.id
          };

          this.company_specific.push(obj);
          this.companies.push(obj);
        }
      });

    this.$http.get("/resources").then(response => {
      this.courseResources = response.data.resources;
    });
  },
  methods: {
    //gamification
      openGamificationModel(){
      if(this.lesson.gamified_content.length > 0){
       for(var i=1; i<this.lesson.gamified_content.length; i++){
          document.getElementById("hidegamfication_" + i).classList.add("hide");
        }
      }else{
         this.lesson.gamified_content.push({
            text: "",
          });
      }
      this.gamifiedModel = true;
    },
    closegamification() {
      console.log("Closed");
      this.gamifiedModel = false;
    },
     addAnotherEditor() {
      document.getElementById("hidegamfication_" + this.currindex).classList.remove("show");

      document.getElementById("hidegamfication_" + this.currindex).classList.add("hide");
      
      this.currindex=this.currindex+1;
       if(this.currindex >= this.lesson.gamified_content.length){
          this.lesson.gamified_content.push({
            text: "",
          });
       }else{
          document.getElementById("hidegamfication_" + this.currindex).classList.remove("hide");

          document.getElementById("hidegamfication_" + this.currindex).classList.add("show");
    
       }
     
    },
    removeAnotherEditor() {
      if (this.currindex == 0) {
        var id = "hidegamfication_" + parseInt(this.currindex - 2);
        document.getElementById(id).classList.remove("hide");
        document.getElementById(id).classList.add("show");
        var curid = "hidegamfication_" + parseInt(this.currindex - 1);
        document.getElementById(curid).classList.add("hide");
        this.currindex = this.currindex - 1;
      } else {
        var id = "hidegamfication_" + parseInt(this.currindex - 1);
        document.getElementById(id).classList.remove("hide");
        document.getElementById(id).classList.add("show");
        var curid = "hidegamfication_" + parseInt(this.currindex);
        document.getElementById(curid).classList.remove("show");
        document.getElementById(curid).classList.add("hide");
        this.currindex = this.currindex - 1;
      }
    },
    // end gamification
    onImageChange(e) {
      let files = e;
      this.file = files[0];
    },
    validUrl(url) {
      var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      if (pattern.test(url)) {
        return true;
      }
      alert("Url is not valid!");
      return false;
    },
    backToTestQuestion() {
      this.test_question = true;
      this.main_test = false;
      this.test_preview = false;
    },
    getTestFile(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.excel_data.file = file[0];
    },
    openTestPreview() {
      if (this.excel_data.file !== "") {
        let formData = new FormData();
        formData.append("questionAnswerFile", this.excel_data.file);
        this.$http
          .post("course/readQuestionAnswer", formData, this.config)
          .then(resp => {
            let data = resp.data.data;
            for (let question of data) {
              let question_obj = {
                question_text: question.question,
                question_status: "",
                options: []
              };
              if (question.status === 1) {
                question_obj.question_status = true;
              } else if (option.currect_answer === 0) {
                question_obj.question_status = false;
              }

              for (let option of question.answers) {
                let option_obj = {
                  option_text: option.answer,
                  correct: ""
                };
                if (option.currect_answer === 1) {
                  option_obj.correct = true;
                } else if (option.currect_answer === 0) {
                  option_obj.correct = false;
                }
                question_obj.options.push(option_obj);
              }
              //test_obj.test_questions.push(question_obj);
              this.test.test_questions.push(question_obj);
            }

            this.test_question = false;
            this.main_test = true;
            this.test_preview = false;
          })
          .catch(function(error) {
            if (error.response.status === 422) {
              Swal.fire({
                title: "Error!",
                text: error.response.data.message,
                icon: "error"
              });
            } else {
              Swal.fire({
                title: "Error!",
                text: "Something went wrong.",
                icon: "error"
              });
            }
          });
      } else {
        return Swal.fire({
          title: "Error!",
          text: `Please Select any File!`,
          icon: "error"
        });
      }
    },
    getSurvey() {
      this.$http.get("course/surveytests", this.config).then(resp => {
        this.surveyData = [];
        let obj1 = {
          id: 0,
          survey_name: "Select"
        };
        this.surveyData.push(obj1);
        for (let survey of resp.data) {
          let obj = {
            id: survey.id,
            survey_name: survey.name
          };
          this.surveyData.push(obj);
        }
      });
    },
    backToMainTest() {
      this.test_question = false;
      this.main_test = true;
      this.test_preview = false;
    },
    testQuestionClicked() {
      this.test_question = true;
      this.main_test = false;
      this.test_preview = false;
    },
    getAllFiles: function(e, index) {
      let file = e.target.files || e.dataTransfer.files;
      this.course.course_resources[index].resource_file = file[0];
    },
    openThisTest(test_index) {
      this.updating = true;
      this.testModal = true;
      this.test = this.course.course_test[test_index];
    },

    removeTest(test_index) {
      this.course.course_test.splice(test_index, 1);
    },
    removeLesson(lesson_index) {
      this.course.course_lessons.splice(lesson_index, 1);
    },
    openThisLesson(lesson_index) {
      this.lesson_preview = false;
      this.updating = true;
      this.lessonModal = true;
      this.lesson = this.course.course_lessons[lesson_index];
    },
    backToLesson() {
      this.lesson_preview = false;
    },
    previewLesson() {
      this.lesson_preview = true;
    },
  
    saveLesson() {
      this.lesson_save_clicked = true;
      this.isRequired = true;
      if (this.lesson.lesson_name !== "" && this.lesson.lesson_content !== "") {
        for (let question of this.lesson.lesson_questions) {
          let correct_answer = false;
          if (question.question_status) {
            this.isRequired = true;
          } else {
            this.isRequired = false;
          }
          if (this.isRequired && this.lesson.quizStatus) {
            if (question.question_text !== "") {
              for (let answer of question.options) {
                if (answer.correct) {
                  correct_answer = true;
                }
                if (answer.option_text === "") {
                  return Swal.fire({
                    title: "Error!",
                    text: `All options's fields are required!`,
                    icon: "error"
                  });
                }
              }
              if (!correct_answer) {
                return Swal.fire({
                  title: "Error!",
                  text: `Please select one or multiple correct answers for every question!`,
                  icon: "error"
                });
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
          text: `All Lesson's Fields are required!`,
          icon: "error"
        });
      }
      this.lessonModal = false;
      this.lesson_preview = false;
      this.lesson_save_clicked = false;
      this.isRequired = true;
      if (!this.updating) {
        this.course.course_lessons.push(this.lesson);
      }
    },
    saveTest() {
      this.test_save_clicked = true;
      this.isRequired = true;
      if (
        this.test.test_fail_message !== "" &&
        this.test.test_instruction !== "" &&
        this.test.test_pass_message !== ""
      ) {
        for (let question of this.test.test_questions) {
          let correct_answer = false;
          if (question.question_status) {
            this.isRequired = true;
          } else {
            this.isRequired = false;
          }
          if (this.isRequired) {
            if (question.question_text !== "") {
              for (let answer of question.options) {
                if (answer.correct) {
                  correct_answer = true;
                }
                if (answer.option_text === "") {
                  return Swal.fire({
                    title: "Error!",
                    text: `All options's fields are required!`,
                    icon: "error"
                  });
                }
              }
              if (!correct_answer) {
                return Swal.fire({
                  title: "Error!",
                  text: `Please select one or multiple correct answers for every question!`,
                  icon: "error"
                });
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
      this.test_question = false;
      this.main_test = true;
      this.test_preview = false;
      this.testModal = false;
      this.test_save_clicked = false;

      if (!this.updating) {
        this.course.course_test.push(this.test);
      }
    },
    saveCertificates() {
      if (
        this.certificate.certificate_name !== "" &&
        this.certificate.certificate_signature_title_1
      ) {
        this.$http
          .post(
            "course/certificate",
            {
              course_certificate_name: this.certificate.certificate_name,
              course_certificate_valid: this.certificate.certificate_valid_time,
              course_certificate_custom_text: this.certificate
                .certificate_custom_text,
              signature_title_1: this.certificate.certificate_signature_title_1,
              signature_title_2: this.certificate.certificate_signature_title_2
            },
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: `Certificate Added! Now You can select this certificate from Dropdown`,
              icon: "success"
            });
          });
      } else {
        return Swal.fire({
          title: "Error!",
          text: `All fields are required!`,
          icon: "error"
        });
      }
    },
    addTestClicked() {
      this.test_question = false;
      this.main_test = true;
      this.test_preview = false;
      this.updating = false;
      this.testModal = true;
      this.test = {
        practice_test: 0,
        enable_submit_button: "",
        test_instruction: "",
        test_pass_message: "",
        test_fail_message: "",
        test_no_of_questions: "",
        test_questions: [
          {
            question_text: "",
            question_status: true,
            allowed_attempts: "",
            options: [
              {
                option_text: "",
                correct: false
              }
            ]
          }
        ]
      };
    },
    deleteCourse() {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(function() {
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: false
          }).then(function() {
            self.$router.push({
              path: "/courses"
            });
          });
        })
        .catch(function() {});
    },

    saveCourse() {
      this.processing = true;
      this.loading = true;
      if (
        this.course.course_name !== "" &&
        this.course.course_description !== "" &&
        this.course.course_cost !== "" &&
        this.course.status !== "" &&
        this.course.live !== "" &&
        this.course.course_length !== "" &&
        this.course.allowed_attempts !== ""
      ) {
        const config = {
          headers: {
            "content-type": "multipart/form-data"
          }
        };
        let formData = new FormData();
        formData.append("course_name", this.course.course_name);
        formData.append("secondary_course_name", this.course.secondary_course_name)
        formData.append(
          "course_name_certificate",
          this.course.course_name_certificate
        );
        formData.append("course_length", this.course.course_length);
        formData.append("course_pass_rate", this.course.course_pass_rate);
        formData.append("nextcourse", this.course.nextcourse);
        if (this.course.companyspecific) {
          formData.append("companyspecific", this.course.companyspecific);
        }
        let fm = 0;
        let fe = 0;
        fm = this.course.formanager ? 1 : 0;
        fe = this.course.foremployee ? 1 : 0;
        formData.append("formanager", fm);
        formData.append("foremployee", fe);
        formData.append(
          "employees_days_to_complete",
          this.course.employees_days_to_complete
        );
        formData.append(
          "manager_days_to_complete",
          this.course.manager_days_to_complete
        );
        let re = 0;
        re = this.course.reassignment_expiry ? 1 : 0;
        formData.append("reassignment_expiry", re);
        formData.append("expiry_attempts", this.course.expiry_attempts);
        formData.append(
          "certificate_validity",
          this.course.certificate_validity
        );
        let ca = 0;
        let ct = 0;
        let c2 = 0;
        let dc = 0;
        let wr = 0;
        let food_safe_online_proctored_exam_form_data = 0;
        ca = this.course.certificateavilable ? 1 : 0;
        ct = this.course.course_type ? 1 : 0;
        c2 = this.course.course_2fa ? 1 : 0;
        dc = this.course.discounted_course ? 1 : 0;
        wr = this.course.weekly_report ? 1 : 0;
        food_safe_online_proctored_exam_form_data = this.course
          .food_safe_online_proctored_exam
          ? 1
          : 0;
        formData.append("assignment_gap", this.course.assignment_gap);
        formData.append("certificateavilable", ca);
        formData.append("course_allow_attempts", this.course.allowed_attempts);
        formData.append("course_cost", this.course.course_cost);
        formData.append("pass_message", this.course.course_passmessage);
        formData.append("fail_message", this.course.course_failmessage);
        //formData.append('course_due', this.course.course_due);
        formData.append("course_description", this.course.course_description);
        formData.append("course_type", ct);
        formData.append("course_2fa", c2);
        formData.append("discounted_course", dc);
        formData.append("weekly_report", wr);
        formData.append(
          "food_safe_online_proctored_exam",
          food_safe_online_proctored_exam_form_data
        );
        formData.append("courseResources", this.course.courseResources);
        formData.append(
          "discounted_course_comment",
          this.course.discounted_course_comment
        );
        formData.append("weekly_report", wr);
        let st = 0;
        let liv = 0;
        st = this.course.status ? 1 : 0;
        liv = this.course.live ? 1 : 0;

        formData.append("course_status", st);
        formData.append("course_in_store", liv);

        if (this.course.course_lessons.length <= 0) {
          this.processing = false;
          this.saving = false;
          return Swal.fire({
            title: "Error!",
            text: `At least One Lesson is Required!`,
            icon: "error"
          });
        } else {
          let i = 0;
          for (let lesson of this.course.course_lessons) {
            formData.append(
              "lessons[" + i + "][course_lesson_name]",
              lesson.lesson_name
            );
            let nbt = 0;

            nbt = lesson.nextButtonTimerStatus ? 1 : 0;

            formData.append(
              "lessons[" + i + "][course_next_button_timer_status]",

              nbt
            );

            formData.append(
              "lessons[" + i + "][course_timer_value]",

              lesson.timerValue
            );

            formData.append(
              "lessons[" + i + "][allowed_attempts]",
              lesson.allowed_attempts
            );
            formData.append(
              "lessons[" + i + "][course_passmessage]",
              lesson.course_passmessage
            );
            formData.append("lessons[" + i + "][type]", lesson.lesson_type);
            if (this.lesson.lesson_type == "video") {
              formData.append(
                "lessons[" + i + "][course_lesson_video]",
                "https://player.vimeo.com/video/" + lesson.lesson_video_url
              );
            } 
             if (this.lesson.lesson_type == "youtube-video") {
              formData.append(
                "lessons[" + i + "][course_lesson_video]",
                "https://www.youtube.com/embed/" +
                  lesson.youtube_lesson_video_url
              );
            } 
            if(this.lesson.lesson_type == "pdf" && this.file != undefined) {
              if (this.file) {
                formData.append(
                  "lessons[" + i + "][course_lesson_pdf]",
                  this.file
                );
              } else if (!lesson.lesson_video_url) {
                formData.append(
                  "lessons[" + i + "][course_lesson_pdf]",
                  lesson.lesson_pdf_url
                );
              }
            }
            if (this.lesson.lesson_type == "gamification") {
                let l = 0;
                for (let content of this.lesson.gamified_content) {
                  if (content.text) {
                    formData.append(
                    "lessons[" + i + "][course_lesson_gamification][" + l + "]",
                    content.text
                  );
                  }
                  l++;
                }
             }

            formData.append(
              "lessons[" + i + "][course_lesson_content]",
              lesson.lesson_content
            );
            formData.append(
              "lessons[" + i + "][course_lesson_quiz]",
              lesson.lesson_quiz_instruction
            );

            let qs = 0;

            qs = lesson.quizStatus ? 1 : 0;
            formData.append("lessons[" + i + "][quizStatus]", qs);

            formData.append(
              "lessons[" + i + "][passing_rate]",
              lesson.passing_rate
            );
            formData.append(
              "lessons[" + i + "][no_of_questions]",
              lesson.no_of_questions
            );
            let j = 0;
            for (let question of lesson.lesson_questions) {
              formData.append(
                "lessons[" + i + "][questions][" + j + "][question]",
                question.question_text
              );
              formData.append(
                "lessons[" + i + "][questions][" + j + "][status]",
                question.question_status
              );
              let k = 0;
              for (let answer of question.options) {
                formData.append(
                  "lessons[" +
                    i +
                    "][questions][" +
                    j +
                    "][answers][" +
                    k +
                    "][course_quiz_question_option]",
                  answer.option_text
                );
                let ans = 0;
                if (answer.correct) {
                  ans = 1;
                } else {
                  ans = 0;
                }
                formData.append(
                  "lessons[" +
                    i +
                    "][questions][" +
                    j +
                    "][answers][" +
                    k +
                    "][course_quiz_correct_answer]",
                  ans
                );
                k++;
              }
              j++;
            }
            i++;
          }
        }
        let i = 0;
        for (let test of this.course.course_test) {
          formData.append(
            "tests[" + i + "][practice_test]",
            test.practice_test
          );
          formData.append(
            "tests[" + i + "][enable_submit_button]",
            test.enable_submit_button
          );
          formData.append(
            "tests[" + i + "][course_test_instruction]",
            test.test_instruction
          );
          formData.append(
            "tests[" + i + "][course_test_pass_msg]",
            test.test_pass_message
          );
          formData.append(
            "tests[" + i + "][course_test_fail_msg]",
            test.test_fail_message
          );
          formData.append(
            "tests[" + i + "][course_no_of_questions]",
            test.test_no_of_questions
          );
          let j = 0;
          for (let question of test.test_questions) {
            formData.append(
              "tests[" + i + "][questions][" + j + "][question]",
              question.question_text
            );
            formData.append(
              "tests[" + i + "][questions][" + j + "][status]",
              question.question_status
            );
            let k = 0;
            for (let answer of question.options) {
              formData.append(
                "tests[" +
                  i +
                  "][questions][" +
                  j +
                  "][answers][" +
                  k +
                  "][course_quiz_question_option]",
                answer.option_text
              );
              let ans = 0;
              ans = answer.correct ? 1 : 0;

              formData.append(
                "tests[" +
                  i +
                  "][questions][" +
                  j +
                  "][answers][" +
                  k +
                  "][course_quiz_correct_answer]",
                ans
              );
              k++;
            }
            j++;
          }
          i++;
        }
        i = 0;
        for (let pretest of this.course.course_pretest) {
          formData.append("pretests[" + i + "][name]", pretest.name);
          formData.append(
            "pretests[" + i + "][instructions]",
            pretest.instructions
          );
          let j = 0;
          for (let question of pretest.pretest_questions) {
            formData.append(
              "pretests[" + i + "][pretest_questions][" + j + "][question]",
              question.question
            );
            formData.append(
              "pretests[" +
                i +
                "][pretest_questions][" +
                j +
                "][is_update_employee]",
              question.is_update_employee
            );
            formData.append(
              "pretests[" + i + "][pretest_questions][" + j + "][answer_type]",
              question.answer_type
            );
            formData.append(
              "pretests[" +
                i +
                "][pretest_questions][" +
                j +
                "][question_status]",
              question.question_status
            );
            formData.append(
              "pretests[" +
                i +
                "][pretest_questions][" +
                j +
                "][checked_validations]",
              question.checked_validations
            );
            let k = 0;
            for (let answer of question.answers) {
              formData.append(
                "pretests[" +
                  i +
                  "][pretest_questions][" +
                  j +
                  "][answers][" +
                  k +
                  "][answer]",
                answer.answer
              );
              k++;
            }
            j++;
          }
          i++;
        }

        if (this.course.assigned_companies_id.length > 0) {
          i = 0;
          for (let company_id of this.course.assigned_companies_id) {
            formData.append("companies[" + i + "][id]", company_id);
            i++;
          }
        }
        if (this.course.course_certificate != "") {
          formData.append("certificate_ids", this.course.course_certificate);
        }
        if (this.course.course_survey !== "") {
          i = 0;
          formData.append(
            "survey_ids[" + i + "][id]",
            this.course.course_survey
          );
        }
        this.$http
          .post("course", formData, this.config)
          .then(resp => {
            this.$router.push("courses");
            Swal.fire({
              title: "Success!",
              text: `Course has been added!`,
              icon: "success"
            });
          })
          .catch(function(error) {
            if (error.response.status === 422) {
              Swal.fire({
                title: "Error!",
                text: error.response.data.message,
                icon: "error"
              });
            } else {
              Swal.fire({
                title: "Error!",
                text: "Something went wrong.",
                icon: "error"
              });
            }
          })
          .finally(() => (this.loading = false));
      } else {
        this.loading = false;
        this.processing = false;
        this.saving = false;
        let requiredFeilds = "";
        if (this.course.course_name == "") {
          requiredFeilds = "Course Name";
        }

        if (this.course.course_description == "") {
          if (requiredFeilds == "") {
            requiredFeilds = "Description";
          } else {
            requiredFeilds += ", Description";
          }
        }
        if (this.course.course_cost == "") {
          if (requiredFeilds == "") {
            requiredFeilds = "Cost";
          } else {
            requiredFeilds += ", Cost";
          }
        }

        if (this.course.course_length == "") {
          if (requiredFeilds == "") {
            requiredFeilds = "Length";
          } else {
            requiredFeilds += ", Length";
          }
        }
        if (this.course.allowed_attempts == "") {
          if (requiredFeilds == "") {
            requiredFeilds = "Allowed Attempts";
          } else {
            requiredFeilds += ", Allowed Attempts";
          }
        }
        Swal.fire({
          title: "Following field(s) are required!",
          text: requiredFeilds,
          icon: "error"
        });
      }
    },
    addFile() {},
    addOptionTest(question_index) {
      this.test.test_questions[question_index].options.push({
        option_text: "",
        correct: false
      });
    },
    addQuestiontest() {
      this.test.test_questions.push({
        question_text: "",
        question_status: true,
        allowed_attempts: "",
        options: [
          {
            option_text: "",
            correct: false
          }
        ]
      });
    },

    addOptionlesson(question_index) {
      this.lesson.lesson_questions[question_index].options.push({
        option_text: "",
        correct: false
      });
    },
    removeTestQuestion(index) {
      this.test.test_questions.splice(index, 1);
    },
    removeTestOption(q_index, opt_index) {
      this.test.test_questions[q_index].options.splice(opt_index, 1);
    },
    removeLessonOption(q_index, opt_index) {
      this.lesson.lesson_questions[q_index].options.splice(opt_index, 1);
    },
    removeLessonQuestion(index) {
      this.lesson.lesson_questions.splice(index, 1);
    },
    addQuestionlesson() {
      this.lesson.lesson_questions.push({
        question_text: "",
        question_status: true,
        allowed_attempts: "",
        options: [
          {
            option_text: "",
            correct: false
          }
        ]
      });
    },
    addCertificateClicked() {
      this.certificateModal = true;
      this.certificate = {
        certificate_name: "",
        course_id: "",
        certificate_date: "",
        certificate_valid_time: "",
        certificate_custom_text: ""
      };
    },
    addLessonClicked() {
      this.lesson_preview = false;
      this.updating = false;
      this.lessonModal = true;
      this.lesson = {
        lesson_name: "",
        lesson_video_url: "",
        gamified_content: [
          {
            text: "",
          },
        ],
        nextButtonTimerStatus: false,
        timerValue: "",
        allowed_attempts: "",
        lesson_content: "",
        lesson_quiz_instruction: "",
        lesson_questions: [
          {
            question_text: "",
            question_status: true,
            allowed_attempts: "",
            options: [
              {
                option_text: "",
                correct: false
              }
            ]
          }
        ]
      };
    },
    addNewResource() {
      this.course.course_resources.push({
        resource_name: "",
        resource_type: "",
        resource_url: "",
        resource_file: ""
      });
    },
    removeNewResource(resource_index) {
      this.course.course_resources.splice(resource_index, 1);
    },
    // PreTestVue
    acceptNumber() {
      var x = this.previewPhone
        .replace(/\D/g, "")
        .match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      this.previewPhone = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    addQuestionpretest() {
      this.pretestQuizModal = true;
    },
    removePreTest(test_index) {
      this.course.course_pretest.splice(test_index, 1);
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
        is_update_employee: "",
        question_status: true,
        answer_type: "",
        checked_validations: "",
        answers: [
          {
            answer: "",
            correct_answer: false
          }
        ]
      });
    },
    openThisPreTest(test_index) {
      this.updating = true;
      this.pretestModal = true;
      this.pretest = this.course.course_pretest[test_index];
    },
    savePreTest() {
      this.pretest_save_clicked = true;
      this.isRequired = true;
      if (this.pretest.name !== "" && this.pretest.instructions !== "") {
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
    },
    // Survey Test

    addQuestionsurveytest() {
      this.surveytestQuizModal = true;
    },
    removeSurveyTest(test_index) {
      this.course.course_surveytest.splice(test_index, 1);
    },
    addSurveyTestClicked() {
      this.surveytestModal = true;
    },
    addSurveyOptionTest(question_index) {
      this.surveytest.surveytest_questions[question_index].answers.push({
        answer: "",
        correct_answer: false
      });
    },
    addQuestionSurveytest() {
      this.surveytest.surveytest_questions.push({
        question: "",
        question_status: true,
        answer_type: "",
        checked_validations: "",
        answers: [
          {
            answer: "",
            correct_answer: false
          }
        ]
      });
    },
    openThisSurveyTest(test_index) {
      this.updating = true;
      this.surveytestModal = true;
      this.surveytest = this.course.course_surveytest[test_index];
    },
    saveSurveyTest() {
      this.surveytest_save_clicked = true;
      this.isRequired = true;
      if (this.surveytest.name !== "" && this.surveytest.instructions !== "") {
        for (let question of this.surveytest.surveytest_questions) {
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

      this.$http
        .post("course/createSurvey", this.surveytest, this.config)
        .then(resp => {
          this.surveytest_question = false;
          this.getSurvey();
          this.main_test = true;
          this.surveytestModal = false;
          this.surveytest_save_clicked = false;
          Swal.fire({
            title: "Success!",
            html:
              "Survey added successfully.</br>You can select it from dropdown.",
            icon: "success"
          });
        });
    },
    removeSurveyTestQuestion(index) {
      this.surveytest.surveytest_questions.splice(index, 1);
    },
    removeSurveyTestOption(q_index, opt_index) {
      this.surveytest.surveytest_questions[q_index].answers.splice(
        opt_index,
        1
      );
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
  border-top: 1px solid #f2f2f2 !important;
  margin-top: 12px;
  margin-bottom: 12px;
}
.border-right {
  border-right: 1px solid darkblue;
}
.ql-editor {
  border: none;
  border-top: 1px solid #cccccc;
}

.que-box-titel {
  border-bottom: 1px solid red;
}
.que-ans-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
}
.currest-ans-label {
  position: absolute;
  right: 16%;
  left: 67%;
  width: 100px;
}
.custom-checkbox .custom-control-input:checked ~ .custom-control-label::after {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e") !important;
}
.modal-header {
  align-items: center !important;
}
.hide {
  display: none;
}
.show {
  display: block;
}
</style>
