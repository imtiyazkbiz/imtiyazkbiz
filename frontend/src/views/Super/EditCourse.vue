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
        <h2 slot="header" class="mb-0">Edit Course</h2>

        <div class="row" style="text-align:right;">
          <div class="col-lg-3 form-inline">
            <b class="card-title mr-3 mb-0">Add to Store</b>
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
            <b class="card-title mr-3  mb-0">Status</b>
            <div class="d-flex justify-content-center">
              <base-switch
                class="mr-1"
                type="success"
                id="status-switch"
                v-model="course.status"
              ></base-switch>
            </div>
          </div>
          <div class="col-lg-3 form-inline">
            <b class="card-title1 mr-3 ">For Managers</b>
            <div class="d-flex justify-content-center">
              <base-switch
                class="mr-1"
                type="success"
                v-model="course.formanager"
              ></base-switch>
            </div>
          </div>
          <div class="col-lg-3 form-inline">
            <b class="card-title1 mr-3 ">For Employees</b>
            <div class="d-flex justify-content-center">
              <base-switch
                @input="switchChange"
                class="mr-1"
                type="success"
                v-model="course.foremployee"
              ></base-switch>
            </div>
          </div>
        </div>
        <hr />
        <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
          <form
            class="needs-validation"
            @submit.prevent="handleSubmit()"
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
                      placeholder="Course Name As Per Certificate"
                      :maxlength="max"
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
                    <label class="form-control-label">Course Cost *</label>
                    <money
                      class="form-control"
                      v-model="course.course_cost"
                      v-bind="money"
                    ></money>
                  </div>
                  <div class="col-md-6">
                    <label class="form-control-label">Passing Rate</label>
                    <br />
                    <base-input
                      v-model="course.course_pass_rate"
                      type="number"
                      name="Passing Rate"
                      min="0"
                      max="100"
                      placeholder=" Pass Rate"
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
                <vue-editor :editorOptions="editorSettings" v-model="course.course_description"></vue-editor>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-4">
                <!-- <v-select
              placeholder="None"
              class="select-primary form-group mr-3"
              v-model="course.nextcourse"
              :options="courses"
            ></v-select> -->
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
              <div class="col-md-4" v-if="course.nextcourse">
                <base-input
                  type="number"
                  label="Assignment Gap (Days)"
                  name="Assignment Gap (Days)"
                  placeholder="Assignment Gap"
                  v-model="course.assignment_gap"
                >
                </base-input>
              </div>
              <div class="col-md-4">
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
              <div class="col-md-3" v-if="course.companyspecific">
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
                <base-checkbox v-model="showCompanies"
                  >Show Assigned Companies</base-checkbox
                >
                <base-input label="Assigned Companies: " v-if="showCompanies">
                  <el-select
                    multiple
                    filterable
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

              <div class="col-md-2" v-if="course_id == 123 || course_id == 268">
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
              <div class="col-md-3">
                 <base-input label="Course Category">
                   <el-select
                      placeholder="Select Course Category"
                      v-model="course.courseCategory"
                    >
                      <el-option
                        v-for="option in courseCategory"
                        class="select-primary"
                        :value="option.id"
                        :label="option.name"
                        :key="option.id"
                      ></el-option>
                    </el-select>
                 </base-input>
              </div>
            </div>
            <hr />
            <div class="row mt-4">
              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0">
                      Course Resources
                    </h5>
                  </div>
                  <base-input label="">
                    <el-select
                      multiple
                      filterable
                      placeholder="Select"
                      v-model="course.courseResources"
                    >
                      <el-option
                        v-for="option in courseResources"
                        class="select-primary"
                        :value="option.id"
                        :label="option.name"
                        :key="option.id"
                      ></el-option>
                    </el-select>
                  </base-input>
                </div>
              </div>
              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0">
                      Course Lesson
                    </h5>
                    <label
                      style="color: #444C57"
                      class=" cursor"
                      v-on:click="addLessonClicked()"
                      ><i class="fas fa-plus-circle mr-1"></i
                      ><b class="add_text">Add Lesson</b></label
                    >
                  </div>
                  <ul class="list-unstyled test-div">
                    <li
                      class=" p-1"
                      v-for="(lesson, index) in course.course_lessons"
                      :key="lesson.id"
                    >
                      <div class="row">
                        <div class="col-md-9">
                          <span style="color: #444C57" class=" cursor">
                            <b v-on:click="openThisLesson(index)"
                              ><a class="href">{{ lesson.lesson_name }}</a></b
                            ></span
                          >
                        </div>
                        <div class="col-md-2" style="cursor:pointer">
                          <el-tooltip content="Drag" placement="top">
                            <i
                              class="fas fa-grip-lines"
                              data-toggle="tooltip"
                              data-original-title="Edit"
                            ></i>
                          </el-tooltip>
                        </div>
                        <div class="col-md-1">
                          <span
                            class="btn btn-danger btn-sm pull-right"
                            v-on:click="removeLesson(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </span>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <br />
            <div class="row">
              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0">
                      Course Test
                    </h5>

                    <div v-if="course.course_test == 0">
                      <label
                        style="color: #444C57"
                        class=" cursor"
                        v-on:click="addTestClicked()"
                      >
                        <i class="fas fa-plus-circle mr-1"></i
                        ><b class="add_text"> Add Test</b></label
                      >
                    </div>
                  </div>
                  <ul class="list-unstyled">
                    <li
                      class=" p-1"
                      v-for="(test, index) in course.course_test"
                      :key="test.id"
                    >
                      <div class="row">
                        <div class="col-md-12">
                          <span
                            class="btn btn-danger  btn-sm pull-right "
                            @click.prevent="removeTest(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </span>
                          <span style="color: #444C57" class=" cursor"
                            ><i class="nc-icon  nc-single-copy-04 mr-2"></i
                            ><b v-on:click="openThisTest(index)"
                              ><a class="href">{{
                                "Test " + (index + 1)
                              }}</a></b
                            ></span
                          >
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
              <!-- {{ course.course_pretest }} -->
              <!-- //pretest -->
              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0 ">
                      Course Pre Test
                    </h5>
                    <div v-if="course.course_pretest == 0">
                      <label
                        style="color: #444C57"
                        class=" cursor"
                        v-on:click="addPreTestClicked()"
                      >
                        <i class="fas fa-plus-circle mr-1"></i
                        ><b class="add_text"> Add Pre Test</b></label
                      >
                    </div>
                  </div>
                  <ul class="list-unstyled">
                    <li
                      class=" p-1"
                      v-for="(pretest, index) in course.course_pretest"
                      :key="pretest.id"
                    >
                      <div class="row">
                        <div class="col-md-12">
                          <span
                            class="btn btn-danger  btn-sm pull-right "
                            @click.prevent="removePreTest(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </span>
                          <span style="color: #444C57" class=" cursor"
                            ><i class="nc-icon  nc-single-copy-04 mr-2"></i
                            ><b v-on:click="openThisPreTest(index)"
                              ><a class="href">{{ pretest.name }}</a></b
                            ></span
                          >
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
              <!-- pretest end -->
            </div>
            <br />
            <div class="row">
              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0 ">
                      Course Survey Test
                    </h5>
                  </div>
                  <ul class="list-unstyled">
                    <li
                      class=" p-1"
                      v-for="(surveytest, index) in course.course_surveytest"
                      :key="surveytest.id"
                    >
                      <div class="row ">
                        <div class="col-md-12">
                          <span
                            class="btn btn-danger  btn-sm pull-right "
                            @click.prevent="removeSurveyTest(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </span>
                          <span style="color: #444C57" class=" cursor"
                            ><i class="nc-icon  nc-single-copy-04 mr-2"></i
                            ><b v-on:click="openThisSurveyTest(index)"
                              ><a class="href">{{ surveytest.name }}</a></b
                            ></span
                          >
                        </div>
                      </div>
                    </li>
                  </ul>
                  <div
                    class="row align-items-center"
                    v-if="course.course_surveytest == 0"
                  >
                    <div class="col-md-6">
                      <label
                        style="color: #444C57"
                        class=" cursor"
                        v-on:click="addSurveyTestClicked()"
                      >
                        <i class="fas fa-plus-circle mr-1"></i
                        ><b class="add_text"> Add Survey Test</b></label
                      >
                    </div>

                    <div class="col-md-6">
                      <el-select
                        placeholder="Select Survey"
                        class="ml-2"
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
              </div>

              <div class="col-md-6">
                <div class="course-broder">
                  <div class="addCour-title">
                    <h5 class="mb-0 ">
                      Course Certificate
                    </h5>
                  </div>
                  <ul class="list-unstyled">
                    <li
                      class="text-info p-1"
                      v-for="(certificates, index) in course.course_certificate"
                      :key="certificates.id"
                    >
                      <div class="row">
                        <div class="col-md-12">
                          <span
                            class="btn btn-danger  btn-sm pull-right"
                            @click.prevent="removeCertificate(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </span>
                          <span style="color: #444C57" class=" cursor"
                            ><b v-on:click="openThisCertificate(index)"
                              ><a class="href">{{
                                certificates.certificate_name
                              }}</a></b
                            ></span
                          >
                        </div>
                      </div>
                    </li>
                  </ul>
                  <el-select
                    filterable
                    placeholder="Select Certificate"
                    class="ml-2 mr-2"
                    v-model="course.course_certificates"
                    v-if="course.course_certificate.length < 1"
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
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-3  form-inline">
                <label class="form-control-label mr-3 ">Allow Certificate</label
                ><br />
                <div class="d-flex">
                  <base-switch
                    class="mr-1"
                    type="success"
                    v-model="course.certificateavilable"
                  ></base-switch>
                </div>
              </div>
              <div class="col-md-3 ">
                <label class="form-control-label"
                  >Certificate Valid For (Days)</label
                >
                <base-input
                  name="Certificate Valid For (Days)"
                  placeholder="Certificate Validity"
                  v-model="course.certificate_validity"
                >
                </base-input>
              </div>
            </div>

            <div class="text-md-right mt-3 asigncrs-btn">
              <router-link to="/courses"
                ><base-button type="danger" class="custom-btn mr-2">
                  Cancel
                </base-button></router-link
              >
              <base-button
                type="danger"
                class="custom-btn"
                @click.prevent="deleteCourse"
              >
                Delete Course
              </base-button>

              <base-button class="custom-btn" @click.prevent="saveCourse">
                Save Course
              </base-button>
              <router-link
                :to="'/lesson_form?id=' + course_id + '&super_admin=true'"
                ><base-button class="custom-btn">
                  Preview
                </base-button></router-link
              >
            </div>
          </form>
        </validation-observer>

        <!--            test modal-->
        <modal :show.sync="testModal" class="user-modal">
          <h4
            v-if="main_test && !test_preview && !test_question"
            style="color:#444C57;"
            slot="header"
            class="modal-title mb-0"
          >
            Course Test
          </h4>
          <h4
            v-if="!main_test && !test_preview && test_question"
            style="color:#444C57;"
            slot="header"
            class="modal-title mb-0"
          >
            Test Question
          </h4>
          <h4
            v-if="!main_test && test_preview && !test_question"
            style="color:#444C57;"
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
              <div class="col-md-2">
                <base-checkbox
                  label="Practice test"
                  v-model="test.practice_test"
                  >Practice test</base-checkbox
                >
              </div>
              <div class="col-md-3" v-if="test.practice_test">
                <base-input
                  label="After # of questions enable submit button"
                  type="number"
                  min="1"
                  v-model="test.enable_submit_button"
                >
                </base-input>
              </div>
              <div class="col-md-2">
                <base-input
                  type="number"
                  label="Number of questions"
                  placeholder="Number of questions"
                  v-model="test.test_no_of_questions"
                >
                </base-input>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-control-label">Pass Message</label>
                  <textarea
                    class="form-control border-input"
                    placeholder="Pass Message..."
                    v-model="test.test_pass_message"
                  >
                  </textarea>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-control-label">Fail Message</label>
                  <textarea
                    class="form-control border-input"
                    placeholder="Fail Message..."
                    v-model="test.test_fail_message"
                  >
                  </textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-control-label">Test Instruction</label>
                  <!-- <textarea
                rows="2"
                class="form-control border-input"
                placeholder="Content Here..."
                v-model="test.test_instruction"
              >
              </textarea> -->
                  <vue-editor v-model="test.test_instruction"></vue-editor>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-9"></div>
              <div class="col-md-3">
                <label class="form-control-label">Type: </label>&nbsp;
                <el-select
                  class="select-primary w-100"
                  v-model="questionFilter"
                  v-on:change="fetchTestFilterData()"
                  placeholder="Filter"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in filterType"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>
            </div>
            <div
              class="brdr question_box mt-3 "
              v-for="(question, q_index) in test.test_questions"
              :key="question.id"
            >
              <div class="row d-flex align-items-center">
                <div class="col-md-8">
                  <h4 class="card-subtitle " style="color:#444C57;">
                    Question {{ q_index + 1 }}
                  </h4>
                </div>
                <div class="col-md-2 col-6   ">
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
              <div class="row">
                <div class="col-md-12">
                  <div class="row">
                    <div class="col-md-12 pt-4">
                      <base-input
                        type="text"
                        label="Question"
                        placeholder="Question"
                        v-model="question.question_text"
                      >
                      </base-input>
                    </div>
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-sm-12 col-12">
                          <div class="currest-ans-label">
                            <label class="form-control-label"
                              >Correct Answer</label
                            >
                          </div>
                        </div>
                      </div>
                      <div
                        class="row align-items-center"
                        v-for="(option, o_index) in question.options"
                        :key="option.id"
                      >
                        <div class="col-md-9 col-6">
                          <base-input
                            type="text"
                            :label="'Answer Option ' + (o_index + 1)"
                            placeholder="Option"
                            v-model="option.option_text"
                          >
                          </base-input>
                        </div>
                        <div class="col-md-3 col-6">
                          <div class="row">
                            <div class="col-sm-8 col-8">
                              <base-checkbox
                                class="pull-right"
                                v-model="option.correct"
                              ></base-checkbox>
                            </div>
                            <div class="col-sm-4 col-4">
                              <base-button
                                type="danger"
                                style="float:right;"
                                size="sm"
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
                            style="color:#444C57;"
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
                  style="color:#444C57;"
                  v-on:click="addQuestiontest()"
                  ><b>+</b>Add Another Question</label
                >
              </div>
            </div>
            <div class="text-right mt-2">
              <base-button class="custom-btn" @click.prevent="saveTest">
                Save Test
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
          <form v-if="!main_test && !test_preview && test_question">
            <div class="row">
              <div class="col-md-12 ">
                <h5 class="card-subtitle title-up text-center">
                  Select Excel File
                </h5>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3"></div>
              <div class="col-md-5">
                <label>Upload Test</label>
                <div>
                  <span class=""
                    ><!--accept=".csv,  application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"-->
                    <input
                      type="file"
                      class="form-control "
                      v-on:change="getTestFile($event)"
                    />
                  </span>
                </div>
              </div>
              <div class="col-md-4">
                <h5>
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
            </div>
            <div class="text-center my-3">
              <base-button
                type="primary"
                size="md"
                @click.prevent="openTestPreview"
              >
                Upload Test
              </base-button>
              <base-button
                type="danger"
                size="md"
                @click.prevent="backToMainTest"
              >
                Cancel
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
          <form v-if="!main_test && test_preview && !test_question">
            <div class="text-center my-3">
              <base-button
                type="default"
                size="sm"
                @click.prevent="backToTestQuestion"
              >
                Back
              </base-button>
              <base-button type="primary" size="sm" @click.prevent="saveTest">
                Save Test
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
        </modal>
        <!--            end of test modal-->

        <!-- Pretest modal -->
        <!--            test modal-->
        <modal :show.sync="pretestModal" class="user-modal">
          <h4 slot="header" class="modal-title mb-0">
            Add Pre Test
          </h4>
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
            class="brdr question_box mt-4"
            v-for="(question, q_index) in pretest.pretest_questions"
            :key="question.id"
          >
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
                  v-if="question.checked_validations === 1"
                  placeholder="(555) 555-5555"
                  @input="acceptNumber"
                ></base-input>
                <base-input
                  type="email"
                  name="Email"
                  v-model="previewEmail"
                  v-if="question.checked_validations === 2"
                  placeholder="Enter valid email"
                ></base-input>
                <base-input
                  v-model="previewText"
                  v-if="question.checked_validations === 3"
                  placeholder="Enter Text"
                ></base-input>
                <el-date-picker
                  v-model="previewDate"
                  v-if="question.checked_validations === 4"
                  style="width: 100%"
                  type="date"
                  placeholder="Pick a day"
                  format="MM/dd/yyyy"
                  :picker-options="pickerOptions1"
                >
                </el-date-picker>
                <base-input
                  v-model="previewSsn"
                  v-if="question.checked_validations === 5"
                  placeholder="Enter SSN"
                ></base-input>
              </div>
            </div>

            <div class="row " v-if="question.answer_type == 2">
              <div class="col-md-12">
                <div class="row">
                  <div class="col-md-8">
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
                      <div class="col-sm-5  col-5 mt-5">
                        <div class="row">
                          <div class="">
                            <base-button
                              size="sm"
                              type="danger"
                              @click.prevent="
                                removePreTestOption(q_index, o_index)
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
          <div class="text-right mt-2">
            <base-button class="custom-btn" @click.prevent="savePreTest">
              Save Pre Test
            </base-button>
          </div>
          <div class="clearfix"></div>
        </modal>
        <!-- end Pretest modal -->

        <!--            test modal-->
        <modal :show.sync="surveytestModal" class="user-modal">
          <h2 slot="header" style="color: #444C57" class="modal-title mb-0 ">
            Add Survey Test
          </h2>
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
          <hr />
          <div
            class="brdr mt-3  question_box"
            v-for="(question, q_index) in surveytest.surveytest_questions"
            :key="question.id"
          >
            <div class="row">
              <div class="col-md-8">
                <h4 style="color: #444C57;">Question {{ q_index + 1 }}</h4>
              </div>
              <div class="col-md-3  col-6">
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
                  @click.prevent="removeSurveyTestQuestion(q_index)"
                >
                  <i class="fa fa-trash"></i>
                </base-button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-11 pt-2 pb-2">
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
                  v-if="question.checked_validations === 1"
                  placeholder="(555) 555-5555"
                  @input="acceptNumber"
                ></base-input>
                <base-input
                  type="email"
                  name="Email"
                  v-model="previewEmail"
                  v-if="question.checked_validations === 2"
                  placeholder="Enter valid email"
                ></base-input>
                <base-input
                  v-model="previewText"
                  v-if="question.checked_validations === 3"
                  placeholder="Enter Text"
                ></base-input>
                <el-date-picker
                  v-model="previewDate"
                  v-if="question.checked_validations === 4"
                  style="width: 100%"
                  type="date"
                  placeholder="Pick a day"
                  format="MM/dd/yyyy"
                  :picker-options="pickerOptions1"
                >
                </el-date-picker>
                <base-input
                  v-model="previewSsn"
                  v-if="question.checked_validations === 5"
                  placeholder="Enter SSN"
                ></base-input>
              </div>
            </div>

            <div class="row " v-if="question.answer_type == 2">
              <div class="col-md-12">
                <div class="row">
                  <div class="col-md-8">
                    <div
                      class="row"
                      v-for="(option, o_index) in question.answers"
                      :key="option.id"
                    >
                      <div class="col-sm-6 mt-2">
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
                      <div class="col-sm-5  col-5 mt-5">
                        <div class="row">
                          <div class="">
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
          <div class="text-right mt-2">
            <base-button class="custom-btn" @click.prevent="saveSurveyTest">
              Save Survey Test
            </base-button>
          </div>
          <div class="clearfix"></div>
        </modal>
        <!-- end Survey modal -->

        <!--             lesson modal-->
        <modal
          :show.sync="lessonModal"
          class="user-modal modal-overflow-lesson"
          v-on:close="onLessonModelClose"
        >
          <h4
            slot="header"
            style="color: #444C57"
            class="modal-title mb-0"
            v-if="!lesson_preview"
          >
            Add New Lesson
          </h4>
          <h4
            slot="header"
            style="color: #444C57"
            class="modal-title"
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
              <div class="col-md-5">
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
              <div class="col-md-2">
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
                <div v-if="lesson.lesson_video_url">
                  <a
                    :href="
                      'https://player.vimeo.com/video/' +
                        lesson.lesson_video_url
                    "
                    target="_blank"
                    >Click here to see video</a
                  >
                </div>
              </div>
                 <div
                class="col-md-3 mt-5"
                v-if="lesson.lesson_type == 'gamification'"
              >
               <span class="linkColor" @click.prevent="openGamificationModel">Click to see gamification content</span>
               </div>
              <div
                class="col-md-3"
                v-if="lesson.lesson_type == 'youtube-video'"
              >
                <base-input
                  label="YouTube Id"
                  placeholder="YouTube Id"
                  type="text"
                  v-model="lesson.youtube_lesson_video_url"
                >
                </base-input>
                <div v-if="lesson.youtube_lesson_video_url">
                  <a
                    :href="
                      'https://www.youtube.com/embed/' +
                        lesson.youtube_lesson_video_url
                    "
                    target="_blank"
                    >Click here to see video</a
                  >
                </div>
              </div>
              <div class="col-md-3" v-if="lesson.lesson_type == 'pdf'">
                <div class="" v-if="!lesson.lesson_pdf_url">
                  <label>Upload Pdf</label>
                  <file-input v-on:change="onImageChange"></file-input>
                </div>
                <div v-else>
                  <label class="form-control-label">Change Pdf</label>
                  <file-input v-on:change="onImageChange"></file-input>
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
                  manual-input
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
                  <span
                    class="text-danger small"
                    v-if="lesson_save_clicked && lesson.lesson_content === ''"
                    >Lesson Content Field is Required!</span
                  >
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
                    <label class="form-control-label">
                      Quiz Instruction:
                    </label>
                    <vue-editor
                      placeholder="Quiz Instructions here..."
                      v-model="lesson.lesson_quiz_instruction"
                    ></vue-editor>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-9"></div>
                <div class="col-md-3">
                  <label class="form-control-label">Type: </label>&nbsp;
                  <el-select
                    class="select-primary w-100"
                    v-model="questionFilter"
                    v-on:change="fetchFilterData()"
                    placeholder="Filter"
                  >
                    <el-option
                      class="select-primary"
                      v-for="item in filterType"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div
                class="brdr question_box mt-3"
                v-for="(question, q_index) in lesson.lesson_questions"
                :key="question.id"
              >
                <div class="row d-flex align-items-center">
                  <div class="col-md-8">
                    <h4 style="color: #444C57" class=" ">
                      Question {{ q_index + 1 }}
                    </h4>
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
                      <div class="col-md-12 pt-1">
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
                      <div class="col-md-12">
                        <div class="row">
                          <div class="col-sm-12 col-12 ">
                            <div class="currest-ans-label">
                              <label class="form-control-label"
                                >Correct Answer</label
                              >
                            </div>
                          </div>
                        </div>
                        <div
                          class="row align-items-center"
                          v-for="(option, o_index) in question.options"
                          :key="option.id"
                        >
                          <div class="col-sm-9">
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
                          <div class="col-sm-3 text-right">
                            <div class="row align-items-center">
                              <div class="col-sm-9">
                                <base-checkbox
                                  class="pull-right"
                                  v-model="option.correct"
                                ></base-checkbox>
                              </div>
                              <div class="col-sm-3">
                                <base-button
                                  type="danger"
                                  size="sm"
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
                              style="color: #444C57"
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
                    style="color: #444C57"
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
              <base-button class="custom-btn" @click.prevent="previewLesson">
                Preview
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
          <div v-if="lesson_preview">
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
            <div class="text-right mt-2">
              <base-button class="custom-btn" @click.prevent="backToLesson">
                Back
              </base-button>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <base-button class="custom-btn" @click.prevent="saveLesson">
                Save
              </base-button>
            </div>
          </div>
        </modal>
        <!--            end of lesson modal-->
        <!--                asign company modal-->
        <modal :show.sync="assignCompanyModal">
          <h4 slot="header" class="title title-up">Assign New Company</h4>
          <form>
            <div class="row">
              <div class="col-md-12 text-center">
                <el-select
                  class=" mr-3"
                  placeholder="Single Select"
                  v-model="assigned_companies_id"
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
              </div>
            </div>
            <div class="text-center my-3">
              <base-button
                :disabled="processing"
                class="custom-btn"
                @click.prevent="assignCompany"
              >
                {{ processing ? "Processing" : "Assign" }}
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
        </modal>
        <!--                certificate modal-->
        <modal :show.sync="certificateModal">
          <h4 slot="header" style="color:#444C57;" class="modal-title mb-0">
            Add New Certificate
          </h4>
          <form>
            <div class="row">
              <div class="col-md-12">
                <h3 style="color:#444C57;" class=" ">
                  Step 1: Enter Global Information
                </h3>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-12 col-12">
                <base-input
                  type="text"
                  label="Certificate Name *"
                  placeholder="Certificate Name"
                  v-model="certificate.certificate_name"
                >
                </base-input>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 col-12">
                <base-input
                  type="text"
                  label="Signature Title 1"
                  placeholder="Signature Title 1"
                  v-model="certificate.certificate_signature_title_1"
                >
                </base-input>
              </div>
              <div class="col-md-6 col-12">
                <base-input
                  type="text"
                  label="Signature Title 2"
                  placeholder="Signature Title 2"
                  v-model="certificate.certificate_signature_title_2"
                >
                </base-input>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-md-12">
                <h3 style="color:#444C57;" class=" ">Step 2: Customization</h3>
              </div>
            </div>
            <div class="row mt-3">
              <!--                                <div class="col-md-3"></div>-->
              <div class="col-md-12 col-12">
                <div class="form-group">
                  <label class="form-control-label">Custom Text</label>
                  <textarea
                    rows="2"
                    class="form-control border-input"
                    placeholder="Custom Text Here..."
                    v-model="certificate.certificate_custom_text"
                  >
                  </textarea>
                </div>
              </div>
            </div>
            <div class="text-right">
              <base-button class="custom-btn" @click.prevent="saveCertificates">
                {{
                  certificate_id !== ""
                    ? " Update Certificate"
                    : "Add Certificate"
                }}
              </base-button>
            </div>
            <div class="clearfix"></div>
          </form>
        </modal>
        <!--            end of certificate modal-->
        <!--                Gamification modal-->
        <modal :show.sync="gamifiedModel" v-on:close="closegamification">
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
                  <vue-editor v-model="content.text"></vue-editor>
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
import Sortable from "sortablejs";
import { Money } from "v-money";
import { Modal } from "@/components";
import FileInput from "@/components/Inputs/FileInput";
import { VueEditor,Quill } from "vue2-editor";
import ImageResize from "quill-image-resize-vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import VueTimepicker from "vue2-timepicker";
import "vue2-timepicker/dist/VueTimepicker.css";
Quill.register("modules/imageResize", ImageResize);
Vue.component("v-select", vSelect);
export default {
  components: {
    Sortable,
    VueTimepicker,
    [DatePicker.name]: DatePicker,
    VueEditor,
    Modal,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    FileInput,
    [TableColumn.name]: TableColumn,
    Money
  },
  data() {
    return {
      loading:false,
       editorSettings: {
        modules: {
          imageResize: {}
        }
      },
      hideGamificationBlock: true,
      showCompanies: false,
      baseUrl: this.$baseUrl,
      max: 38,
      pretest_save_clicked: false,
      lesson_save_clicked: false,
      allowedAttempt: true,
      isLoading: false,
      fullPage: true,
      processing: false,
      hot_user: "",
      hot_token: "",
      config: "",
      courses_data: [],
      isRequired: true,
      currindex: 0,
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
      course_id: "",
      certificate_id: "",
      lesson_id: "",
      test_id: "",
      assigned_companies_id: "",
      companies: [],
      assigned_certificate_id: "",
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
      surveyData: [],
      previewPhone: "",
      previewEmail: "",
      previewText: "",
      previewSsn: "",
      previewDate: "",
      courses: [],
      company_specific: [],
      tableData: [],
      lesson_preview: false,
      assignCertificate: false,
      test_question: false,
      main_test: true,
      test_preview: false,
      updating: false,
      percentage: {
        decimal: ".",
        thousands: ",",
        prefix: "",
        suffix: "%",
        precision: 2,
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
      lessontype: [
        {
          label: "Vimeo Video",
          value: "video"
        },
        {
          label: "YouTube video",
          value: "youtube-video"
        },
        {
          label: "Pdf",
          value: "pdf"
        },
        {
          label: "Gamification",
          value: "gamification",
        },
      ],

      course: {
        course_type: true,
        course_2fa: false,
        discounted_course: false,
        discounted_course_comment: "",
        weekly_report: false,
        food_safe_online_proctored_exam: false,
        course_certificates: "",
        course_survey: "",
        certificateavilable: true,
        formanager: true,
        foremployee: true,
        live: true,
        status: true,
        course_name: "",
        secondary_course_name:"",
        course_name_certificate: "",
        course_length: "",
        course_due: "",
        allowed_attempts: "",
        course_passmessage: "",
        course_failmessage: "",
        course_cost: "",
        course_description: "",
        nextcourse: "",
        companyspecific: "",
        employees_days_to_complete: "",
        manager_days_to_complete: "",
        reassignment_expiry: true,
        expiry_attempts: "",
        certificate_validity: "",
        assignment_gap: "",
        course_pass_rate: "",
        course_resources: [],
        courseCategory:"",
        course_lessons: [],
        course_test: [],
        course_pretest: [],
        course_surveytest: [],
        course_certificate: [],
        assigned_companies_id: [],
        courseResources: []
      },
      lessonModal: false,
      certificateModal: false,
      assignCompanyModal: false,
      testModal: false,
      pretestModal: false,
      surveytestModal: false,
      gamifiedModel: false,
      lesson: {
        nextButtonTimerStatus: false,
        timerValue: "",
        no_of_questions: "",
        passing_rate: "",
        lesson_name: "",
        lesson_type: "video",
        lesson_video_url: "",
        youtube_lesson_video_url: "",
         gamified_content: [
          {
            text: "",
          },
        ],
        lesson_pdf_url: "",
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
      },
      appear_status: false,
      resource_name: "",
      resource_type: "",
      resource_url: "",
      resource_file: "",
      resource_id: "",
      file_name: "",
      test: {
        practice_test: 0,
        enable_submit_button: "",
        test_instruction: "",
        test_pass_message: "",
        test_no_of_questions: "",
        test_fail_message: "",
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
      },
      courseResources: [],
      courseCategory:[],
      surveytest: {
        name: "",
        instructions: "",
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
          label: "Address1",
          value: 3
        },
        {
          label: "Address2",
          value: 8
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
          label: "Zipcode",
          value: 9
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
      questionFilter: 1,
      lessonarrayquestion: [],
      lessonarrayquestions: [],
      lessonIndex: "",
      testarrayquestion: [],
      filterType: [
        {
          label: "Active",
          value: 1
        },
        {
          label: "Inactive",
          value: 0
        },
        {
          label: "All",
          value: 2
        }
      ],

      certificate_Data: [],
      certificate: {
        certificate_name: "",
        course_id: "",
        certificate_date: "",
        certificate_valid_time: "",
        certificate_custom_text: "",
        certificate_signature_title_1: "",
        certificate_signature_title_2: ""
      },
      selects: {
        simple: "",
        valid: [
          {
            label: "1 Year",
            value: 1
          },
          { label: "2 Year", value: 2 },
          { label: "3 Year", value: 3 },
          { label: "4 Year", value: 4 },
          { label: "5 Year", value: 5 }
        ]
      }
    };
  },
  mounted() {
    const tbody = document.querySelector(".test-div");
    const self = this;
    Sortable.create(tbody, {
      onEnd({ newIndex, oldIndex }) {
        const targetRow = self.course.course_lessons.splice(oldIndex, 1)[0];
        self.course.course_lessons.splice(newIndex, 0, targetRow);
      }
    });
  },
  created() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
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
            formanager: "",
            foremployee: "",
            course_name: data.name,
            secondary_course_name: data.secondary_course_name,
            course_name_certificate: data.course_name_certificate,
            course_length: data.length,
            allowed_attempts: data.allow_attempts,
            course_due: data.due_days,
            course_cost: data.cost,
            employees_days_to_complete: data.employees_days_to_complete,
            manager_days_to_complete: data.managers_days_to_complete,
            reassignment_expiry: data.reassignment_expiry,
            expiry_attempts: data.expiry_attempts,
            course_pass_rate: data.passing_percent,
            course_passmessage: data.pass_message,
            course_failmessage: data.fail_message,
            course_description: data.description,
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
            course_employees: data.course_employees,
            course_resources: [],
            course_lessons: [],
            course_test: [],
            course_pretest: [],
            course_surveytest: [],
            course_certificate: [],
            assigned_companies_id: [],
            courseResources: [],
            courseCategory: data.category,
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
          let lesson_data = data.lessons;
          for (let lesson of lesson_data) {
            let lesson_obj = {
              id: lesson.id,
              lesson_name: lesson.course_lesson_name,
              allowed_attempts: lesson.allowed_attempts,
              lesson_content: lesson.course_lesson_content,
              no_of_questions: lesson.no_of_questions,
              lesson_type: lesson.type,
              passing_rate: lesson.passing_rate,
              nextButtonTimerStatus: lesson.timer_status,
              timerValue: lesson.timer_value,
              quizStatus: "",
              lesson_quiz_instruction: lesson.course_lesson_quiz,
              lesson_questions: [],
              gamified_content: [],
            };
            if (lesson.quiz_status === 1) {
              lesson_obj.quizStatus = true;
            } else if (lesson.quiz_status === 0) {
              lesson_obj.quizStatus = false;
            } else {
              lesson_obj.quizStatus = lesson.quiz_status;
            }
            if (lesson.type == "video") {
              lesson_obj.lesson_video_url = lesson.course_lesson_video ? lesson.course_lesson_video.substr(
                31
              ) : lesson.course_lesson_video;
            } else if (lesson.type == "youtube-video") {
              lesson_obj.youtube_lesson_video_url = lesson.course_lesson_video ? lesson.course_lesson_video.substr(
                30
              ) : lesson.course_lesson_video;
            } else if (lesson.type == "pdf") {
              lesson_obj.lesson_pdf_url = lesson.course_lesson_video;
            } else if (lesson.type == "gamification") {
              for (let gamification of lesson.gamification_data) {
                let gamification_obj = {
                  text: gamification.content,
                };
                lesson_obj.gamified_content.push(gamification_obj);
              }
             
            }
            for (let question of lesson.questions) {
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
            let certificate_obj = {
              id: certificate.id,
              certificate_name: certificate.name,
              certificate_custom_text: certificate.custom_text,
              certificate_date: certificate.date,
              course_id: certificate.course_id,
              certificate_valid_time: certificate.valid,
              certificate_signature_title_1: certificate.signature_title_1,
              certificate_signature_title_2: certificate.signature_title_2
            };
            course_obj.course_certificate.push(certificate_obj);
          }

          this.course = course_obj;

          this.lessonarrayquestions = this.course.course_lessons;
        });
    }
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
            value: 0
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
       this.$http.get("course/category").then((response) => {
      this.courseCategory = response.data;
    });
    this.$http
      .post("course/companies", {
        course_id: this.course_id
      })
      .then(resp => {
        let companies_data = resp.data.companies[0].companies;
        for (let data of companies_data) {
          let obj = {
            id: data.id,
            company_name: data.name,
            company_admin: data.email
          };
          this.tableData.push(obj);
        }
      });
  },
  watch: {
    "lesson.lesson_type"(val) {
      console.log("val",val);
      if (val === "gamification") {
       this.openGamificationModel();
      }
    },
  },
  methods: {
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
      this.gamifiedModel = false;
    },
    switchChange() {
      if (this.course.foremployee == 0 && this.course.course_employees > 0) {
        Swal.fire({
          text:
            this.course.course_employees +
            " employee(s) are already assigned to this course.",
          icon: "warning",
          confirmButtonClass: "btn btn-success btn-fill",
          buttonsStyling: true
        });
      }
    },
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
    refresh() {
      this.$http
        .post("course/companies", {
          course_id: this.course_id
        })
        .then(resp => {
          this.tableData = [];
          let companies_data = resp.data.companies[0].companies;
          for (let data of companies_data) {
            let obj = {
              id: data.id,
              company_name: data.name,
              company_admin: data.email
            };
            this.tableData.push(obj);
          }
        });
    },
    changeLocationStatus() {
      let prev_val = this.course.status;
      let status = "";
      if (prev_val) {
        status = 0;
      } else {
        status = 1;
      }
      let self = this;
      Swal.fire({
        title: "Are you sure?",
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
                "/course/update_status/" + self.course_id,
                {
                  status: status
                },
                self.config
              )
              .then(resp => {
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
                self.course.status = !prev_val;
              });
          }
        })
        .catch(function() {
          self.course.status = prev_val;
        });
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
    removePreTestQuestion(index) {
      this.pretest.pretest_questions.splice(index, 1);
    },
    removePreTestOption(q_index, opt_index) {
      this.pretest.pretest_questions[q_index].answers.splice(opt_index, 1);
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
              } else if (question.status === 0) {
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
    assignCompany() {
      this.processing = true;
      this.$http
        .post(
          "company/course",
          {
            company_id: this.assigned_companies_id,
            course_id: this.course_id
          },
          this.config
        )
        .then(resp => {
          this.processing = false;
          this.refresh();
          Swal.fire({
            title: "Success!",
            text: "Assigned Successfully!",
            icon: "success"
          });
          this.assignCompanyModal = false;
        })
        .catch(resp => {
          this.processing = false;
          Swal.fire({
            title: "Error!",
            text: "Course can't assign to this Company...",
            icon: "error"
          });
        });
    },
    fetchFilterData() {
      if (this.questionFilter === 1) {
        var result1 = this.lessonarrayquestion.filter(obj => {
          return obj.question_status === true;
        });
      } else if (this.questionFilter === 0) {
        var result1 = this.lessonarrayquestion.filter(obj => {
          return obj.question_status === false;
        });
      } else {
        var result1 = this.lessonarrayquestion;
      }

      this.lesson.lesson_questions = result1;
    },
    fetchTestFilterData() {
      if (this.questionFilter === 1) {
        var result1 = this.testarrayquestion.filter(obj => {
          return obj.question_status === true;
        });
      } else if (this.questionFilter === 0) {
        var result1 = this.testarrayquestion.filter(obj => {
          return obj.question_status === false;
        });
      } else {
        var result1 = this.testarrayquestion;
      }

      this.test.test_questions = result1;
    },

    assignCompanyClicked() {
      this.updating = false;
      this.assignCompanyModal = true;
    },

    getAllFiles: function(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.resource_file = file[0];
    },
    openThisTest(test_index) {
      this.updating = true;
      this.test_id = this.course.course_test[test_index].id;
      this.testModal = true;
      this.test = this.course.course_test[test_index];
      this.testarrayquestion = this.test.test_questions;
    },
    openThisPreTest(test_index) {
      this.updating = true;
      this.test_id = this.course.course_pretest[test_index].id;
      this.pretestModal = true;
      this.pretest = this.course.course_pretest[test_index];
    },
    openThisSurveyTest(test_index) {
      this.updating = true;
      this.test_id = this.course.course_surveytest[test_index].id;
      this.surveytestModal = true;
      this.surveytest = this.course.course_surveytest[test_index];
    },

    openThisCertificate(index) {
      this.updating = true;
      this.certificate_id = this.course.course_certificate[index].id;
      this.certificateModal = true;
      this.certificate = this.course.course_certificate[index];
    },
    removeTest(test_index) {
      let id = this.course.course_test[test_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/test/" + id, self.config)
                .then(resp => {
                  self.course.course_test.splice(test_index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Test has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.course.course_test.splice(test_index, 1);
      }
    },
    removePreTest(test_index) {
      let id = this.course.course_pretest[test_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/pretest/" + id, self.config)
                .then(resp => {
                  self.course.course_pretest.splice(test_index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Pre Test has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.course.course_pretest.splice(test_index, 1);
      }
    },
    onLessonModelClose() {
      this.lessonarrayquestions[
        this.lessonIndex
      ].lesson_questions = this.lessonarrayquestion;
    },
    removeSurveyTest(test_index) {
      let id = this.course.course_surveytest[test_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete(
                  "/course/surveytest/" + id + "/" + this.course_id,
                  self.config
                )
                .then(resp => {
                  self.course.course_surveytest.splice(test_index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Survey unassigned from this course.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.course.course_surveytest.splice(test_index, 1);
      }
    },
    addPreTestClicked() {
      this.pretestModal = true;
    },
    addSurveyTestClicked() {
      this.surveytestModal = true;
    },
    removeCertificate(index) {
      let id = this.course.course_certificate[index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete(
                  "/course/unassigncertificate/" + id + "/" + this.course_id,
                  self.config
                )
                .then(resp => {
                  self.course.course_certificate.splice(index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Certificate has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.course.course_certificate.splice(index, 1);
      }
    },
    removeLesson(lesson_index) {
      let id = this.course.course_lessons[lesson_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/lesson/" + id, self.config)
                .then(resp => {
                  self.course.course_lessons.splice(lesson_index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Lesson has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.course.course_lessons.splice(lesson_index, 1);
      }
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
    openThisLesson(lesson_index) {
      this.lessonIndex = lesson_index;
      this.updating = true;
      this.lesson_preview = false;
      this.lesson_id = this.course.course_lessons[lesson_index].id;
      this.lessonModal = true;
      this.lesson = this.course.course_lessons[lesson_index];
      this.lessonarrayquestion = this.lessonarrayquestions[
        lesson_index
      ].lesson_questions;
      this.fetchFilterData();
    },
    backToLesson() {
      this.lesson_preview = false;
    },
    previewLesson() {
      this.lesson_preview = true;
    },
    saveLesson() {
      this.loading=true;
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
                  this.loading=false;
                   Swal.fire({
                    title: "Error!",
                    text: `All options's fields are required!`,
                    icom: "error"
                  });
                }
              }
              if (!correct_answer) {
                this.loading=false;
                 Swal.fire({
                  title: "Error!",
                  text: `Please select one or multiple correct answers for every question!`,
                  icon: "error"
                });
              }
            } else {
              this.loading=false;
               Swal.fire({
                title: "Error!",
                text: `All Questions's fields are required!`,
                icon: "error"
              });
            }
          }
        }
      } else {
        this.loading=false;
         Swal.fire({
          title: "Error!",
          text: `All Lesson's Fields are required!`,
          icon: "error"
        });
      }
      if (!this.updating) {
        let formData = new FormData();
        formData.append("course_id", this.course_id);
        let i = 0;
        formData.append(
          "lessons[" + i + "][course_lesson_name]",
          this.lesson.lesson_name
        );
        let nbtm = 0;
        nbtm = this.lesson.nextButtonTimerStatus ? 1 : 0;
        formData.append(
          "lessons[" + i + "][course_next_button_timer_status]",
          nbtm
        );
        formData.append(
          "lessons[" + i + "][course_timer_value]",
          this.lesson.timerValue
        );
        formData.append("lessons[" + i + "][type]", this.lesson.lesson_type);
        if (this.lesson.lesson_type == "video") {
          formData.append(
            "lessons[" + i + "][course_lesson_video]",
            "https://player.vimeo.com/video/" + this.lesson.lesson_video_url
          );
        }
        if (this.lesson.lesson_type == "youtube-video") {
          formData.append(
            "lessons[" + i + "][course_lesson_video]",
            "https://www.youtube.com/embed/" +
              this.lesson.youtube_lesson_video_url
          );
        }
        if (this.lesson.lesson_type == "pdf") {
          formData.append("lessons[" + i + "][course_lesson_pdf]", this.file);
        }
         if (this.lesson.lesson_type == "gamification") {
          let l = 0;
          for (let content of this.lesson.gamified_content) {
            formData.append(
              "lessons[" + i + "][course_lesson_gamification][" + l + "]",
              content.text
            );
            l++;
          }
        }
        formData.append(
          "lessons[" + i + "][allowed_attempts]",
          this.lesson.allowed_attempts
        );
        formData.append(
          "lessons[" + i + "][course_lesson_content]",
          this.lesson.lesson_content
        );
        formData.append(
          "lessons[" + i + "][course_lesson_quiz]",
          this.lesson.lesson_quiz_instruction
        );
        let qs = 0;
        qs = this.lesson.quizStatus ? 1 : 0;

        formData.append("lessons[" + i + "][quizStatus]", qs);
        formData.append(
          "lessons[" + i + "][no_of_questions]",
          this.lesson.no_of_questions
        );
        formData.append(
          "lessons[" + i + "][passing_rate]",
          this.lesson.passing_rate
        );
        let qs1 = 0;
        let j = 0;
        for (let question of this.lesson.lesson_questions) {
          qs1 = question.question_status ? 1 : 0;

          formData.append(
            "lessons[" + i + "][questions][" + j + "][question]",
            question.question_text
          );
          formData.append(
            "lessons[" + i + "][questions][" + j + "][status]",
            qs1
          );
          // formData.append('lessons['+i+'][questions]['+j+'][allowed_attempts]',question.allowed_attempts);
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

        this.$http.post("course/lesson", formData, this.config).then(resp => {
          this.lessonModal = false;
          this.lesson_preview = false;
          this.course.course_lessons.push(this.lesson);
          this.loading=false;
          Swal.fire({
            title: "Success!",
            text: `Lesson has been added for this Course!`,
            icon: "success"
          }).then(function() {
            window.location.reload(true);
          });
        });
      } else {
        this.loading=true;
        let qs = 0;
        qs = this.lesson.quizStatus ? 1 : 0;

        let formData = new FormData();
        formData.append("lesson_id", this.lesson_id);
        formData.append("course_id", this.course_id);
        let i = 0;
        formData.append("course_lesson_name", this.lesson.lesson_name);
        let nbt = 0;
        nbt = this.lesson.nextButtonTimerStatus ? 1 : 0;
        formData.append("course_next_button_timer_status", nbt);
        formData.append("course_timer_value", this.lesson.timerValue);
        formData.append("type", this.lesson.lesson_type);
        if (this.lesson.lesson_type == "video") {
          formData.append(
            "course_lesson_video",
            "https://player.vimeo.com/video/" + this.lesson.lesson_video_url
          );
        }
        if (this.lesson.lesson_type == "youtube-video") {
          formData.append(
            "course_lesson_video",
            "https://www.youtube.com/embed/" +
              this.lesson.youtube_lesson_video_url
          );
        }
        if (this.lesson.lesson_type == "pdf" && this.file != undefined) {
          formData.append("course_lesson_pdf", this.file);
        }
         if (this.lesson.lesson_type == "gamification") {
          let l = 0;
          for (let content of this.lesson.gamified_content) {
            if (content.text) {
              formData.append(
                "course_lesson_gamification[" + l + "]",
                content.text
              );
            }
            l++;
          }
        }
        formData.append("allowed_attempts", this.lesson.allowed_attempts);
        formData.append("course_lesson_content", this.lesson.lesson_content);
        formData.append(
          "course_lesson_quiz",
          this.lesson.lesson_quiz_instruction
        );
        let qs12 = 0;
        qs12 = this.lesson.quizStatus ? 1 : 0;

        formData.append("quizStatus", qs12);
        formData.append("no_of_questions", this.lesson.no_of_questions);
        formData.append("passing_rate", this.lesson.passing_rate);

        let qs1 = 0;
        let j = 0;
        for (let question of this.lesson.lesson_questions) {
          qs1 = question.question_status ? 1 : 0;
          if (question.id) {
            formData.append("questions[" + j + "][id]", question.id);
          }
          formData.append(
            "questions[" + j + "][question]",
            question.question_text
          );
          formData.append("questions[" + j + "][status]", qs1);
          // formData.append('lessons['+i+'][questions]['+j+'][allowed_attempts]',question.allowed_attempts);
          let k = 0;
          for (let answer of question.options) {
            if (answer.id) {
              formData.append(
                "questions[" + j + "][answers][" + k + "][id]",
                answer.id
              );
            }
            formData.append(
              "questions[" +
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
              "questions[" +
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

        this.$http
          .post("course/updatelesson", formData, this.config)
          .then(resp => {
            this.lessonModal = false;
            this.lesson_preview = false;
            this.loading=false;
            Swal.fire({
              title: "Success!",
              text: `Lesson has been Updated!`,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      }
    },
    addSurveyOptionTest(question_index) {
      this.surveytest.surveytest_questions[question_index].answers.push({
        answer: "",
        correct_answer: false
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
    },

    savePreTest() {
      this.pretest.course_id = parseInt(this.course_id);
      if (this.pretest.id != undefined) {
        this.$http
          .put(
            "course/preTestUpdate/" + this.pretest.id,
            this.pretest,
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      } else {
        this.$http
          .post("course/preTest", this.pretest, this.config)
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      }
    },
    saveSurveyTest() {
      this.surveytest.course_id = parseInt(this.course_id);
      if (this.surveytest.id != undefined) {
        this.$http
          .put(
            "course/editSurvey/" + this.surveytest.id,
            this.surveytest,
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      } else {
        this.$http
          .post("course/createSurvey", this.surveytest, this.config)
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      }
    },
    saveTest() {
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
      if (!this.updating) {
        let formData = new FormData();
        formData.append("course_id", this.course_id);

        let i = 0;
        formData.append(
          "tests[" + i + "][practice_test]",
          this.test.practice_test
        );
        formData.append(
          "tests[" + i + "][enable_submit_button]",
          this.test.enable_submit_button
        );
        formData.append(
          "tests[" + i + "][course_test_instruction]",
          this.test.test_instruction
        );
        formData.append(
          "tests[" + i + "][course_test_pass_msg]",
          this.test.test_pass_message
        );
        formData.append(
          "tests[" + i + "][course_test_fail_msg]",
          this.test.test_fail_message
        );
        formData.append(
          "tests[" + i + "][course_no_of_questions]",
          this.test.test_no_of_questions
        );
        let j = 0;
        for (let question of this.test.test_questions) {
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
            if (answer.correct) {
              ans = 1;
            } else {
              ans = 0;
            }
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
        this.$http.post("course/test", formData, this.config).then(resp => {
          this.test_question = false;
          this.main_test = true;
          this.test_preview = false;
          this.testModal = false;
          this.course.course_test.push(this.test);
          Swal.fire({
            title: "Success!",
            text: `Test has been added for this Course!`,
            icon: "success"
          }).then(function() {
            window.location.reload(true);
          });
        });
      } else {
        let data = {
          course_id: this.course_id,
          practice_test: this.test.practice_test,
          enable_submit_button: this.test.enable_submit_button,
          course_test_instruction: this.test.test_instruction,
          course_test_pass_msg: this.test.test_pass_message,
          course_test_fail_msg: this.test.test_fail_message,
          course_no_of_questions: this.test.test_no_of_questions,
          questions: []
        };

        let j = 0;
        for (let question of this.test.test_questions) {
          let st = 0;
          st = question.question_status ? 1 : 0;

          let q_obj = {
            id: question.id,
            question: question.question_text,
            status: st,
            answers: []
          };
          let k = 0;
          for (let answer of question.options) {
            let ans = 0;
            ans = answer.correct ? 1 : 0;

            let o_obj = {
              id: answer.id,
              course_quiz_question_option: answer.option_text,
              course_quiz_correct_answer: ans
            };
            q_obj.answers.push(o_obj);
            k++;
          }
          data.questions.push(q_obj);
          j++;
        }
        this.$http
          .put("course/test/" + this.test_id, data, this.config)
          .then(resp => {
            this.updating = false;
            this.test_question = false;
            this.main_test = true;
            this.test_preview = false;
            this.testModal = false;
            Swal.fire({
              title: "Success!",
              text: `Test has been updated!`,
              icon: "success"
            }).then(function() {
              window.location.reload(true);
            });
          });
      }
    },
    saveCertificates() {
      if (
        this.certificate.certificate_name !== "" &&
        this.certificate.certificate_signature_title_1 !== ""
      ) {
        if (this.updating) {
          this.$http
            .put(
              "course/certificate/" + this.certificate_id,
              {
                course_certificate_name: this.certificate.certificate_name,
                course_certificate_valid: this.certificate
                  .certificate_valid_time,
                course_certificate_custom_text: this.certificate
                  .certificate_custom_text,
                signature_title_1: this.certificate
                  .certificate_signature_title_1,
                signature_title_2: this.certificate
                  .certificate_signature_title_2
              },
              this.config
            )
            .then(resp => {
              this.updating = false;
              this.certificateModal = false;
              Swal.fire({
                title: "Success!",
                text: `Certificate has been updated!`,
                icon: "success"
              }).then(function() {
                window.location.reload(true);
              });
            });
        } else {
          this.$http
            .post(
              "course/certificate",
              {
                course_id: this.course_id,
                course_certificate_name: this.certificate.certificate_name,
                course_certificate_valid: this.certificate
                  .certificate_valid_time,
                course_certificate_custom_text: this.certificate
                  .certificate_custom_text,
                signature_title_1: this.certificate
                  .certificate_signature_title_1,
                signature_title_2: this.certificate
                  .certificate_signature_title_2
              },
              this.config
            )
            .then(resp => {
              this.certificateModal = false;
              let data = resp.data;
              this.course.course_certificate.push({
                id: data.id,
                certificate_name: data.name,
                certificate_custom_text: data.custom_text,
                certificate_valid_time: data.valid
              });
              Swal.fire({
                title: "Success!",
                text: `Certificate has been added!`,
                icon: "success"
              }).then(function() {
                window.location.reload(true);
              });
            });
        }
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
        test_allowed_attempts: "",
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
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(result => {
          if (result.value) {
            self.$http
              .delete("/course/delete/" + self.course_id, self.config)
              .then(resp => {
                Swal.fire({
                  title: "Deleted!",
                  text: "Course has been deleted.",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                }).then(function() {});
                self.$router.push({ path: "/courses" });
              });
          }
        })
        .catch(function() {});
    },
    saveCourse() {
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
        let ct = 0;
        let c2 = 0;
        let dc = 0;
        let wr = 0;
        ct = this.course.course_type ? 1 : 0;
        c2 = this.course.course_2fa ? 1 : 0;
        dc = this.course.discounted_course ? 1 : 0;
        wr = this.course.weekly_report ? 1 : 0;

        let formData = new FormData();
        formData.append("course_name", this.course.course_name);
        formData.append("secondary_course_name", this.course.secondary_course_name);
        formData.append(
          "course_name_certificate",
          this.course.course_name_certificate
        );
        formData.append("course_length", this.course.course_length);
        formData.append("course_due", this.course.course_due);
        formData.append("course_allow_attempts", this.course.allowed_attempts);
        formData.append("course_pass_message", this.course.course_passmessage);
        formData.append("course_fail_message", this.course.course_failmessage);
        formData.append("course_cost", this.course.course_cost);
        formData.append("course_description", this.course.course_description);
        formData.append("course_type", this.course.course_type);
        formData.append("course_2fa", this.course.course_2fa);
        formData.append("course_category", this.course.courseCategory);
        formData.append("discounted_course", dc);
        formData.append(
          "discounted_course_comment",
          this.course.discounted_course_comment
        );
        formData.append("weekly_report", wr);
        let st = 0;
        let liv = 0;
        let fm = 0;
        let fe = 0;
        let ca = 0;
        let re = 0;
        st = this.course.status ? 1 : 0;
        liv = this.course.live ? 1 : 0;
        fm = this.course.formanager ? 1 : 0;
        fe = this.course.foremployee ? 1 : 0;
        ca = this.course.certificateavilable ? 1 : 0;
        dc = this.course.discounted_course ? 1 : 0;
        re = this.course.reassignment_expiry ? 1 : 0;
        formData.append("course_status", st);
        formData.append("course_in_store", liv);
        this.$http
          .put(
            "course/update/" + this.course_id,
            {
              course_name: this.course.course_name,
              secondary_course_name: this.course.secondary_course_name,
              course_name_certificate: this.course.course_name_certificate,
              course_length: this.course.course_length,
              course_due: this.course.course_due,
              course_allow_attempts: this.course.allowed_attempts,
              course_cost: this.course.course_cost,
              course_description: this.course.course_description,
              course_pass_message: this.course.course_passmessage,
              course_fail_message: this.course.course_failmessage,
              next_course: this.course.nextcourse,
              company_specific: this.course.companyspecific,
              course_type: this.course.course_type,
              course_2fa: this.course.course_2fa,
              discounted_course: dc,
              discounted_course_comment: this.course.discounted_course_comment,
              weekly_report: wr,
              employees_days_to_complete: this.course
                .employees_days_to_complete,
              manager_days_to_complete: this.course.manager_days_to_complete,
              reassignment_expiry: re,
              expiry_attempts: this.course.expiry_attempts,
              certificateavilable: ca,
              foremployee: fe,
              formanager: fm,
              assignment_gap: this.course.assignment_gap,
              course_pass_rate: this.course.course_pass_rate,
              certificate_validity: this.course.certificate_validity,
              course_status: st,
              course_in_store: liv,
              assigned_companies: this.course.assigned_companies_id,
              course_certificate: this.course.course_certificates,
              course_survey: this.course.course_survey,
              food_safe_online_proctored_exam: this.course
                .food_safe_online_proctored_exam
                ? 1
                : 0,
              courseResources: this.course.courseResources,
              lessonOrder: this.course.course_lessons,
               course_category: this.course.courseCategory,
            },
            this.config
          )
          .then(resp => {
            Swal.fire({
              title: "Success!",
              text: `Course has been Updated!`,
              icon: "success"
            });
            this.$router.push("/courses");
          })
          .catch(function(error) {
            if (error.response.status === 422) {
              Swal.fire({
                title: "Error!",
                text: error.response.data.message,
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
    addFile(index) {},
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
    removeTestQuestion(index) {
      let id = this.test.test_questions[index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/question/" + id, self.config)
                .then(resp => {
                  self.test.test_questions.splice(index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Question has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.test.test_questions.splice(index, 1);
      }
    },
    removeTestOption(q_index, opt_index) {
      let id = this.test.test_questions[q_index].options[opt_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/question_answer/" + id, self.config)
                .then(resp => {
                  self.test.test_questions[q_index].options.splice(
                    opt_index,
                    1
                  );
                  Swal.fire({
                    title: "Deleted!",
                    text: "Option has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.test.test_questions[q_index].options.splice(opt_index, 1);
      }
    },
    removeLessonOption(q_index, opt_index) {
      let id = this.lesson.lesson_questions[q_index].options[opt_index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
          type: "warning",
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
                .delete("/course/question_answer/" + id, self.config)
                .then(resp => {
                  self.lesson.lesson_questions[q_index].options.splice(
                    opt_index,
                    1
                  );
                  Swal.fire({
                    title: "Deleted!",
                    text: "Option has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.lesson.lesson_questions[q_index].options.splice(opt_index, 1);
      }
    },
    removeLessonQuestion(index) {
      let id = this.lesson.lesson_questions[index].id;
      if (id !== undefined) {
        let self = this;
        Swal.fire({
          title: "Are you sure?",
          text: `You won't be able to revert this!`,
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
                .delete("/course/question/" + id, self.config)
                .then(resp => {
                  self.lesson.lesson_questions.splice(index, 1);
                  Swal.fire({
                    title: "Deleted!",
                    text: "Question has been deleted.",
                    icon: "success",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: false
                  }).then(function() {});
                });
            }
          })
          .catch(function() {});
      } else {
        this.lesson.lesson_questions.splice(index, 1);
      }
    },
    addOptionlesson(question_index) {
      this.lesson.lesson_questions[question_index].options.push({
        option_text: "",
        correct: false
      });
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

    addLessonClicked() {
      this.lesson_preview = false;
      this.updating = false;
      this.lessonModal = true;
      this.lesson = {
        lesson_name: "",
        nextButtonTimerStatus: false,
        timerValue: "",
        lesson_video_url: "",
         gamified_content: [
          {
            text: "",
          },
        ],
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
    }
  }
};
</script>
<style scoped>
.currest-ans-label {
  position: absolute;
  right: 5%;
  left: 86%;
  width: 100px;
}
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
.btn-neutral-custom {
  color: red;
  background-color: #fff;
  border-color: #fff;
  box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
}
.href {
  cursor: pointer;
  color: darkcyan;
}
.ql-editor {
  border: none;
  border-top: 1px solid #cccccc;
}
hr {
  border-top: 1px solid #f2f2f2 !important;
  margin-top: 12px;
  margin-bottom: 12px;
}
.hide {
  display: none;
}
.show {
  display: block;
}
</style>
