<template>
  <card>
    <!-- Card header -->
    <h3 slot="header" class="mb-0">Create New Course</h3>
    <div class="card-header pl-0">
      <div class="row" style="text-align:right;">
        <div class="col-lg-3 mt-3 form-inline">
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
        <div class="col-lg-3 mt-3 form-inline">
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
        <div class="col-lg-3 mt-3 form-inline">
          <b class="card-title1 mr-3 ">For Managers</b>
          <div class="d-flex justify-content-center">
            <base-switch
              class="mr-1"
              type="success"
              v-model="course.formanager"
            ></base-switch>
          </div>
        </div>
        <div class="col-lg-3 mt-3 form-inline">
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
    <br />
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
                  label="Course Length (mins) *"
                  name="Course Length"
                  placeholder="Course Length"
                  type="number"
                  rules="required"
                  v-model="course.course_length"
                >
                </base-input>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-4">
                <base-input
                  label="Attempts *"
                  name="Allowed Attempts"
                  type="number"
                  placeholder=""
                  v-model="course.allowed_attempts"
                >
                </base-input>
              </div>
              <div class="col-md-4">
                <label class="form-control-label">Course Cost *</label>
                <money
                  class="form-control"
                  v-model="course.course_cost"
                  v-bind="money"
                ></money>
              </div>
              <div class="col-md-4">
                <label class="form-control-label">Passing Rate</label>
                <br />
                <the-mask
                  class="form-control"
                  v-model="course.course_pass_rate"
                  mask="##%"
                  value=""
                  type="text"
                  placeholder=" Pass Rate"
                ></the-mask>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md-6">
                <base-input
                  type="number"
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
            <label class="form-control-label">Next Course</label>
            <v-select
              placeholder="None"
              class="select-primary form-group mr-3"
              v-model="course.nextcourse"
              :options="courses"
            ></v-select>
          </div>
          <div class="col-md-2" v-if="course.nextcourse">
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
            <label class="form-control-label">Company Specific</label>
            <v-select
              placeholder="Please Select"
              class="select-primary form-group mr-3"
              v-model="course.companyspecific"
              :options="company_specific"
            ></v-select>
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
          <div class="col-md-2 mt-2 form-inline" v-if="!course.companyspecific">
            <h4>Assigned Companies:</h4>
          </div>
          <div class="col-md-3 mt-4" v-if="!course.companyspecific">
            <el-select
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
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-12">
            <h3>Course Resources</h3>
          </div>
        </div>
        <div
          class="row"
          v-for="(res, index) in course.course_resources"
          :key="index"
        >
          <div class="col-md-4">
            <base-input
              label="Resource Name"
              name="'resource name_'+(index+1)"
              placeholder="Resource Name"
              v-model="res.resource_name"
            >
            </base-input>
          </div>
          <div class="col-md-4">
            <base-input
              label="Resource URL (url should contain http:// or https://)"
              v-on:blur="validUrl(res.resource_url)"
              placeholder="Resource URL"
              v-model="res.resource_url"
            >
            </base-input>
          </div>
          <div class="col-md-4">
            <label class="form-control-label">Upload Resource</label>
            <div>
              <span class="">
                <input
                  type="file"
                  name="..."
                  class="form-control "
                  v-on:change="getAllFiles($event, index)"
                />
              </span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <label class="cursor" v-on:click="addNewResource()">
              <i class="fas fa-plus-circle mr-1"></i
              ><b class="add_text">Add Another Resource</b>
            </label>
          </div>
        </div>
        <br />
        <div class="row">
          <div class="col-md-6">
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
                  ><b v-on:click="openThisLesson(index)">{{
                    lesson.lesson_name
                  }}</b></span
                >
                <base-button
                  class="btn btn-danger btn-neutral-custom btn-sm pull-right"
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
                <label
                  @click.native="lessonModal = true"
                  v-on:click="addLessonClicked()"
                >
                  <i class="fas fa-plus-circle mr-1"></i
                  ><b class="add_text">Add Lesson</b>
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
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
                  ><b v-on:click="openThisTest(index)">{{
                    "Test " + (index + 1)
                  }}</b></span
                >
                <base-button
                  class="btn btn-danger btn-neutral-custom btn-sm pull-right"
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
                <label
                  class="cursor"
                  @click.native="testModal = true"
                  v-on:click="addTestClicked()"
                >
                  <i class="fas fa-plus-circle mr-1"></i
                  ><b class="add_text">Add Test</b>
                </label>
              </div>
            </div>
          </div>
        </div>
        <br />
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

          <div class="col-md-6 form-inline" v-if="course.certificateavilable">
            <el-select
              placeholder="Select Certificate"
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
            <label
              class="cursor"
              @click.native="certificateModal = true"
              v-on:click="addCertificateClicked()"
            >
              <i class="fas fa-plus-circle mr-1"></i
              ><b class="add_text"> Add New Certificate</b></label
            >
          </div>
          <div class="col-md-3" v-if="course.certificateavilable">
            <base-input
              label="Certificate Valid For (Days)"
              name="Certificate Valid For (Days)"
              placeholder="Certificate Validity"
              v-model="course.certificate_validity"
            >
            </base-input>
          </div>
        </div>
        <br />

        <base-button class="custom-btn mr-3" @click="$router.go(-1)"
          >Back</base-button
        >
        <base-button class="custom-secondary-btn" @click.prevent="saveCourse">
          {{ "Submit" }}
        </base-button>
      </form>
    </validation-observer>
    <!--            lesson modal-->
    <modal :show.sync="lessonModal" class="user-model">
      <h4
        slot="header"
        class="modal-title"
        style="color: #272C33;"
        v-if="!lesson_preview"
      >
        Add New Lesson
      </h4>
      <h4
        slot="header"
        class="modal-title"
        style="color: #272C33;"
        v-if="lesson_preview"
      >
        <small>Course Name:</small> {{ course.course_name }}
      </h4>
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
                label="Allowed Attempts"
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
                <div class="d-flex justify-content-center">
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
    </modal>
    <!--            end of lesson modal-->
    <!--            test modal-->
    <modal :show.sync="testModal" class="user-model">
      <h4
        v-if="main_test && !test_preview && !test_question"
        style="color: #444C57;"
        slot="header"
        class="title  title-up"
      >
        Course Test
      </h4>
      <h4
        v-if="!main_test && !test_preview && test_question"
        style="color: #444C57;"
        slot="header"
        class="title  title-up"
      >
        Test Question
      </h4>
      <h4
        v-if="!main_test && test_preview && !test_question"
        style="color: #444C57;"
        slot="header"
        class="title  title-up"
      >
        Test Question Preview
      </h4>
      <form v-if="main_test && !test_preview && !test_question">
        <div class="row">
          <div class="col-md-12 text-right">
            <base-button
              type="primary"
              class="pull-right"
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
          <div class="col-md-3">
            <base-input
              type="number"
              label="Allowed Attempts"
              placeholder="Allowed Attempts"
              v-model="test.test_allowed_attempts"
            >
            </base-input>
            <span
              class="text-danger small"
              v-if="test_save_clicked && test.test_allowed_attempts === ''"
              >Allowed Attempts Field is Required!</span
            >
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Pass Message</label>
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
              <label>Fail Message</label>
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
          <div class="col-md-6">
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
        <!-- <div
          class="brdr mt-3"
          v-for="(question, q_index) in test.test_questions"
          :key="question.id"
        >
          <div class="row">
            <div class="col-md-6">
              <h6 style="color: #444C57;" class="card-subtitle mb-2 ">
                Question {{ q_index + 1 }}
              </h6>
            </div>
            <div class="col-md-3  ">
              <div
                class="d-flex justify-content-center"
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
                @click.prevent="removeTestQuestion(q_index)"
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
                      isRequired &&
                        test_save_clicked &&
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
                          isRequired &&
                            test_save_clicked &&
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
                            size="sm"
                            type="danger"
                            @click.prevent="removeTestOption(q_index, o_index)"
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
        </div> -->
        <div class="row">
          <div class="col-md-12">
            <label class="cursor" style="color: #444C57;"
              ><b>+</b
              ><a href="" @click.prevent="addQuestiontest()"
                >Add Questions</a
              ></label
            >
          </div>
        </div>
        <div class="text-center my-4">
          <base-button type="primary" size="md" @click.prevent="saveTest">
            Save Test
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
      <form v-if="!main_test && !test_preview && test_question">
        <div class="row">
          <div class="col-md-12 ">
            <h5
              style="color: #444C57;"
              class="card-subtitle title-up  text-center"
            >
              Select Excel File
            </h5>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3"></div>
          <div class="col-md-6">
            <label>Upload Test</label>
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
          </div>
        </div>
        <div class="text-center my-3">
          <base-button type="default" size="md" @click.prevent="backToMainTest">
            Cancel
          </base-button>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <base-button
            type="primary"
            size="md"
            @click.prevent="openTestPreview"
          >
            Upload Test
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
        <div class="row mt-3">
          <div class="col-md-3"></div>
          <div class="col-md-6 col-12">
            <label>Valid for how long?</label>
            <br />
            <el-select
              class=" mr-3"
              style="width: 100%"
              placeholder="Valid for how long?"
              v-model="certificate.certificate_valid_time"
            >
              <el-option
                v-for="(option, index) in selects.valid"
                class="select-primary"
                :value="option.value"
                :label="option.label"
                :key="option.value + '_' + index"
              >
              </el-option>
            </el-select>
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
    <modal :show.sync="quizModel" class="user-model">
      <h4 slot="header" style="color: #444C57" class="title title-up">
        Add New Question
      </h4>
      <div
        class="brdr"
        v-for="(question, q_index) in test.test_questions"
        :key="question.id"
      >
        <div class="row">
          <div class="col-md-6">
            <h3 style="color: #444C57;" class="card-subtitle mb-2 ">
              Question
            </h3>
          </div>
          <div class="col-md-3  "></div>
          <div class="col-md-3">
            <div class="d-flex justify-content-center">
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
            <!-- <base-button
              type="danger"
              style="float:right;"
              size="sm"
              @click.prevent="removeTestQuestion(q_index)"
            >
              <i class="fa fa-trash"></i>
            </base-button> -->
          </div>
        </div>
        <div class="row ">
          <div class="col-md-12">
            <div class="row">
              <div class="col-md-12 pt-4">
                <!-- <base-input
                  type="text"
                  label="Question"
                  placeholder="Question"
                  v-model="question.question_text"
                >
                </base-input> -->
                <textarea
                  rows="2"
                  cols="120"
                  class="form-control border-input"
                  v-model="question.question_text"
                  label="Question"
                  placeholder="Question here.."
                ></textarea>
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
              <div class="col-md-12 mt-4">
                <div class="row">
                  <div
                    class="col-md-6"
                    v-for="(option, o_index) in question.options"
                    :key="option.id"
                  >
                    <br />
                    <div class="row">
                      <div class="col-md-3  col-3 pt-3 mt-4">
                        <div class="d-flex">
                          <base-switch
                            class="mr-1"
                            v-if="option.correct"
                            type="success"
                            v-model="option.correct"
                          ></base-switch>
                          <base-switch
                            class="mr-1"
                            v-else
                            type="danger"
                            v-model="option.correct"
                          ></base-switch>
                        </div>
                      </div>
                      <div class="col-sm-7 col-6">
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

                      <div class="col-sm-2  col-2 mt-3">
                        <div class="row">
                          <div class="col-sm-2 col-2 pt-4">
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
      <div class="text-center my-4">
        <base-button type="primary" size="md" @click.prevent="addQuestiontest">
          Save Question
        </base-button>
        <base-button type="danger" size="md" @click.prevent="backToMainTest">
          Cancel
        </base-button>
      </div>
    </modal>
    <!--            end of certificate modal-->
  </card>
</template>
<script>
import Vue from "vue";
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import { TheMask } from "vue-the-mask";
import { Money } from "v-money";
import FileInput from "@/components/Inputs/FileInput";
import { Modal, BaseAlert } from "@/components";
import { VueEditor } from "vue2-editor";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
Vue.component("v-select", vSelect);
export default {
  components: {
    VueEditor,
    Modal,
    TheMask,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    FileInput,
    Money
  },
  data() {
    return {
      duplicateFlag: false,
      saving: false,
      hot_user: "",
      hot_token: "",
      config: "",
      //   courses_data: [],
      certificate_Data: [],
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
      modelValidations: {
        requiredText: {
          required: true
        },
        email: {
          required: true,
          email: true
        },
        number: {
          required: true,
          decimal: true
        },
        url: {
          required: true,
          url: true
        },
        idSource: {
          required: true
        },
        idDestination: {
          required: true,
          confirmed: "idSource"
        }
      },
      lesson_save_clicked: false,
      isRequired: true,
      test_save_clicked: false,
      courses: [],
      company_specific: [],
      course: {
        live: true,
        status: true,
        course_name: "",
        course_length: "",
        course_due: "",
        allowed_attempts: "",
        coursestatus: "",
        course_passmessage: "",
        course_failmessage: "",
        course_cost: "",
        course_description: "",
        course_resources: [
          {
            resource_name: "",
            resource_url: "",
            resource_file: ""
          }
        ],
        course_pass_rate: "",
        formanager: "1",
        foremployee: "1",
        certificateavilable: "1",
        nextcourse: "",
        companyspecific: "",
        employees_days_to_complete: "",
        manager_days_to_complete: "",
        certificate_validity: "",
        assignment_gap: "",
        course_lessons: [],
        course_test: [],
        course_certificate: "",
        assigned_companies_id: []
      },
      companies: [],
      lessonModal: false,
      certificateModal: false,
      testModal: false,
      lesson: {
        quizStatus: "0",
        lesson_type: "Select Type",
        lesson_name: "",
        allowed_attempts: "",
        passing_rate: "",
        no_of_questions: "",
        lesson_video_url: "",
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
      test: {
        test_allowed_attempts: "",
        test_instruction: "",
        test_pass_message: "",
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
      lessontype: [
        {
          label: "Video",
          value: "video"
        },
        {
          label: "Pdf",
          value: "pdf"
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
      quizModel: false,
      selects: {
        simple: "",
        valid: [
          {
            label: "1 Year",
            value: 1
          },
          {
            label: "2 Year",
            value: 2
          },
          {
            label: "3 Year",
            value: 3
          },
          {
            label: "4 Year",
            value: 4
          },
          {
            label: "5 Year",
            value: 5
          }
        ]
      },
      selected: {
        simple: "",
        countries: [],
        multiple: "ARS"
      },
      excel: {
        simple: "",
        columns: [
          {
            label: "Column A"
          },
          {
            label: "Column B"
          },
          {
            label: "Column C"
          },
          {
            label: "Column D"
          },
          {
            label: "Column E"
          },
          {
            label: "Column F"
          },
          {
            label: "Column G"
          },
          {
            label: "Column H"
          },
          {
            label: "Column I"
          },
          {
            label: "Column J"
          },
          {
            label: "Column K"
          },
          {
            label: "Column L"
          },
          {
            label: "Column M"
          },
          {
            label: "Column N"
          },
          {
            label: "Column O"
          },
          {
            label: "Column P"
          },
          {
            label: "Column Q"
          },
          {
            label: "Column R"
          },
          {
            label: "Column S"
          },
          {
            label: "Column T"
          },
          {
            label: "Column U"
          },
          {
            label: "Column V"
          },
          {
            label: "Column W"
          },
          {
            label: "Column X"
          },
          {
            label: "Column Y"
          },
          {
            label: "Column Z"
          }
        ],
        multiple: "ARS"
      },
      excel_data: {
        test_question: "",
        option_1: "",
        option_2: "",
        option_3: "",
        option_4: "",
        correct_answer: "",
        question_status: "",
        file: ""
      }
    };
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
    // this.$http
    //   .post(
    //     "course/all_courses",
    //     {
    //       course_status: "All"
    //     },
    //     this.config
    //   )
    //   .then(resp => {
    //     for (let course of resp.data) {
    //       let obj = {
    //         value: course.id,
    //         label: course.course_name
    //       };
    //       this.courses_data.push(obj);
    //     }
    //   });
    this.$http.get("course/unassignedCertificates", this.config).then(resp => {
      for (let certificate of resp.data) {
        let obj = {
          id: certificate.id,
          certificate_name: certificate.name,
          use_count: certificate.course_count,
          course_name: ""
        };
        if (certificate.course[0] !== undefined) {
          obj.course_name = certificate.course[0].name;
        }
        this.certificate_Data.push(obj);
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
        for (let comp of resp.data.companies) {
          let obj = {
            label: comp.name,
            value: comp.id
          };
          this.companies.push(obj);
        }
      });
    if (this.$route.query.id) {
      let duplicate_id = this.$route.query.id;
      this.$http.get("/course/edit/" + duplicate_id, this.config).then(resp => {
        this.duplicateFlag = true;
        let data = resp.data[0];
        let course_obj = {
          live: "",
          status: "",
          course_name: data.name,
          course_length: data.length,

          //course_due: data.due_days,
          allowed_attempts: data.allow_attempts,
          course_cost: data.cost,
          course_passmessage: data.pass_message,
          course_failmessage: data.fail_message,
          course_description: data.description,
          course_resources: [],
          course_lessons: [],
          course_test: [],
          course_certificate: "",
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
        let lesson_data = data.lessons;
        for (let lesson of lesson_data) {
          let lesson_obj = {
            id: lesson.id,
            allowed_attempts: lesson.allowed_attempts,
            course_passmessage: lesson.course_passmessage,
            lesson_name: lesson.course_lesson_name,
            lesson_video_url: lesson.course_lesson_video,
            lesson_content: lesson.course_lesson_content,
            lesson_quiz_instruction: lesson.course_lesson_quiz,
            lesson_questions: []
          };
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
        let resources_data = data.resources;
        for (let resource of resources_data) {
          let resource_obj = {
            id: resource.id,
            appear_status: 1,
            resource_name: resource.course_resource_name,
            resource_url: resource.course_resource_url,
            resource_file: resource.course_resource
          };
          course_obj.course_resources.push(resource_obj);
        }
        let tests_data = data.tests;
        for (let test of tests_data) {
          let test_obj = {
            id: test.id,
            test_allowed_attempts: test.course_test_allowed_attempt,
            test_instruction: test.course_test_instruction,
            test_pass_message: test.course_test_pass_msg,
            test_fail_message: test.course_test_fail_msg,
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
        let certificate_data = data.certificates;
        for (let certificate of certificate_data) {
          course_obj.course_certificate = certificate.id;
        }
        this.course = course_obj;
      });
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
            label: "This Course",
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
        for (let data of comp_data) {
          let obj = {
            label: data.name,
            value: data.id
          };

          this.company_specific.push(obj);
        }
      });
  },
  methods: {
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
        formData.append("quizFile", this.excel_data.file);
        this.$http
          .post("course/read_file", formData, this.config)
          .then(resp => {
            let data = resp.data;
            this.test.test_questions = [];
            for (let questoin_data of data[0]) {
              let test_obj = {
                question_text: questoin_data[0],
                question_status: true,
                allowed_attempts: this.test.test_allowed_attempts,
                options: [
                  {
                    option_text: questoin_data[1],
                    correct: false
                  },
                  {
                    option_text: questoin_data[2],
                    correct: false
                  },
                  {
                    option_text: questoin_data[3],
                    correct: false
                  },
                  {
                    option_text: questoin_data[4],
                    correct: false
                  }
                ]
              };
              this.test.test_questions.push(test_obj);
            }
            this.test_question = false;
            this.main_test = true;
            this.test_preview = false;
          });
      } else {
        return Swal.fire({
          title: "Error!",
          text: `Please Select any File!`,
          icon: "error"
        });
      }
    },
    backToMainTest() {
      this.test_question = false;
      this.main_test = true;
      this.test_preview = false;
      this.quizModel = false;
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
        this.test.test_allowed_attempts !== "" &&
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
                  return;
                  Swal.fire({
                    title: "Error!",
                    text: `All options's fields are required!`,
                    icon: "error"
                  });
                }
              }
              if (!correct_answer) {
                return;
                Swal.fire({
                  title: "Error!",
                  text: `Please select one or multiple correct answers for every question!`,
                  icon: "error"
                });
              }
            } else {
              return;
              Swal.fire({
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
        this.certificate.certificate_valid_time !== ""
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
            this.$http
              .get("course/unassignedCertificates", this.config)
              .then(resp => {
                this.certificate_Data = [];
                for (let certificate of resp.data) {
                  let obj = {
                    id: certificate.id,
                    certificate_name: certificate.name,
                    use_count: certificate.course_count,
                    course_name: ""
                  };
                  if (certificate.course[0] !== undefined) {
                    obj.course_name = certificate.course[0].course_name;
                  }
                  this.certificate_Data.push(obj);
                }
                this.certificateModal = false;
              });
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
        test_allowed_attempts: "",
        test_instruction: "",
        test_pass_message: "",
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
        formData.append("course_length", this.course.course_length);
        formData.append("course_pass_rate", this.course.course_pass_rate);
        formData.append("nextcourse", this.course.nextcourse.value);
        if (this.course.companyspecific.value) {
          formData.append("companyspecific", this.course.companyspecific.value);
        }
        formData.append("formanager", this.course.formanager);
        formData.append("foremployee", this.course.foremployee);
        formData.append(
          "employees_days_to_complete",
          this.course.employees_days_to_complete
        );
        formData.append(
          "manager_days_to_complete",
          this.course.manager_days_to_complete
        );
        formData.append(
          "certificate_validity",
          this.course.certificate_validity
        );
        formData.append("assignment_gap", this.course.assignment_gap);
        formData.append("certificateavilable", this.course.certificateavilable);
        formData.append("course_allow_attempts", this.course.allowed_attempts);
        formData.append("course_cost", this.course.course_cost);
        formData.append("pass_message", this.course.course_passmessage);
        formData.append("fail_message", this.course.course_failmessage);
        //formData.append('course_due', this.course.course_due);
        formData.append("course_description", this.course.course_description);
        let st = 0;
        if (this.course.status) {
          st = 1;
        } else {
          st = 0;
        }
        let liv = 0;
        if (this.course.live) {
          liv = 1;
        } else {
          liv = 0;
        }
        formData.append("course_status", st);
        formData.append("course_in_store", liv);
        let i = 0;
        for (let resource of this.course.course_resources) {
          formData.append("resources[" + i + "][appear_status]", 1);
          formData.append(
            "resources[" + i + "][course_resource_name]",
            resource.resource_name
          );
          formData.append(
            "resources[" + i + "][course_resource_url]",
            resource.resource_url
          );
          formData.append(
            "resources[" + i + "][course_resource]",
            resource.resource_file
          );
          i++;
        }
        if (this.course.course_lessons.length <= 0) {
          this.saving = false;
          return Swal.fire({
            title: "Error!",
            text: `At least One Lesson is Required!`,
            icon: "error"
          });
        } else {
          i = 0;
          for (let lesson of this.course.course_lessons) {
            formData.append(
              "lessons[" + i + "][course_lesson_name]",
              lesson.lesson_name
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
            if (lesson.lesson_video_url) {
              formData.append(
                "lessons[" + i + "][course_lesson_video]",
                "https://player.vimeo.com/video/" + lesson.lesson_video_url
              );
            }
            if (this.file) {
              formData.append(
                "lessons[" + i + "][course_lesson_pdf]",
                this.file
              );
            }
            formData.append(
              "lessons[" + i + "][course_lesson_content]",
              lesson.lesson_content
            );
            formData.append(
              "lessons[" + i + "][course_lesson_quiz]",
              lesson.lesson_quiz_instruction
            );
            formData.append(
              "lessons[" + i + "][course_lesson_passing_rate]",
              lesson.passing_rate
            );
            formData.append(
              "lessons[" + i + "][course_lesson_no_of_questions]",
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
        i = 0;
        for (let test of this.course.course_test) {
          formData.append(
            "tests[" + i + "][course_test_allowed_attempt]",
            test.test_allowed_attempts
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
          i++;
        }
        if (this.course.assigned_companies_id.length > 0) {
          i = 0;
          for (let company_id of this.course.assigned_companies_id) {
            formData.append("companies[" + i + "][id]", company_id);
            i++;
          }
        }
        if (this.course.course_certificate !== "") {
          i = 0;
          // for(let course_id of this.course.course_certificate){
          formData.append(
            "certificate_ids[" + i + "][id]",
            this.course.course_certificate
          );
          // i++;
          // }
        }
        this.$http.post("course", formData, this.config).then(resp => {
          this.$router.push("courses");
          Swal.fire({
            title: "Success!",
            text: `Course has been added!`,
            icon: "success"
          });
        });
      } else {
        this.saving = false;
        Swal.fire({
          title: "Error!",
          text: `Please fill all required feilds!`,
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
      this.testModal = false;
      this.quizModel = true;

      //   this.test.test_questions.push({
      //     question_text: "",
      //     question_status: true,
      //     allowed_attempts: "",
      //     options: [
      //       {
      //         option_text: "",
      //         correct: false
      //       }
      //     ]
      //   });
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
        resource_url: "",
        resource_file: ""
      });
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
</style>
