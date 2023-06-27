<template>
  <div class="content">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <form id="lesson_test">
          <card class="no-border-card">
            <template slot="header">
              <div class="row align-items-center" v-if="pretestFlag">
                <div class="col-md-6">
                  <span class="form-inline">
                    <h2 class="mb-0">Pre Test</h2>
                    <br />
                  </span>
                </div>
                <div class="remain-attempts col-md-6 hideOnMobileView">
                  <base-button
                    name="Save Pretest"
                    @click.prevent="savePretest()"
                    class="custom-btn"
                    >Next <i class="fa fa-arrow-right"></i>
                  </base-button>
                </div>
              </div>
              <div
                class="row align-items-center"
                v-else-if="show_test || show_lesson"
              >
                <div class="col-md-3">
                  <span class="form-inline">
                    <h2 class="mb-0 attemp-course">
                      <span class="test-courst-name">{{ course_name }}</span>

                      <span class="test-q-name"
                        ><span class="test-q-name">{{
                          show_lesson ? "" + open_lesson.name : ""
                        }}</span
                        >{{
                          show_test && !open_test.practice_test
                            ? " Test "
                            : show_test && open_test.practice_test
                            ? "Practice Test"
                            : ""
                        }}{{
                          show_lesson && showQuizFlag
                            ? " &nbsp; &gt; &nbsp; Quiz"
                            : ""
                        }}</span
                      >
                    </h2>
                  </span>
                </div>

                <div class="remain-attempts col-md-3">
                  <p
                    class="attmp-time"
                    v-if="
                      (show_test && !open_test.practice_test) || showQuizFlag
                    "
                  >
                    <b>Remaining attempts: </b
                    >{{
                      show_test
                        ? open_test.remaining_attempts
                        : open_lesson.remaining_attempts
                    }}
                  </p>
                </div>

                <div class="col-md-2 text-right" v-if="!its_super_admin">
                  <span class="counter">
                    <label class="hours" :id="'hours_' + crrTimestamp"
                      >00</label
                    >
                    <label id="colon">:</label>
                    <label class="hours" :id="'minutes_' + crrTimestamp"
                      >00</label
                    >
                    <label id="colon">:</label>
                    <label class="hours" :id="'seconds_' + crrTimestamp"
                      >00</label
                    >
                  </span>
                  <input type="hidden" id="hiddenInput" />
                </div>
                <div
                  class="remain-attempts col-md-4 hideOnMobileView"
                  v-if="show_lesson && !showQuizFlag"
                >
                  <base-button
                    name="Back"
                    v-if="open_lesson_index != 0"
                    class="custom-btn"
                    @click.prevent="backQuiz()"
                    ><i class="fa fa-arrow-left"></i> Back</base-button
                  >
                  <base-button
                    v-if="open_lesson.timer_status && !nextButtonEnable"
                    name="Show Quiz"
                    disabled
                    class="custom-btn"
                    @click.prevent="showQuiz()"
                    >Next <i class="fa fa-arrow-right"></i> </base-button
                  >
                  <base-button
                    v-if="!open_lesson.timer_status && !nextButtonEnable"
                    name="Show Quiz"
                    class="custom-btn"
                    @click.prevent="showQuiz()"
                    >Next <i class="fa fa-arrow-right"></i> </base-button
                  >
                  <base-button
                    v-if="!open_lesson.timer_status && nextButtonEnable"
                    name="Show Quiz"
                    class="custom-btn"
                    @click.prevent="showQuiz()"
                    >Next <i class="fa fa-arrow-right"></i> </base-button
                  >
                  <base-button
                    v-if="open_lesson.timer_status && nextButtonEnable"
                    name="Show Quiz"
                    class="custom-btn"
                    @click.prevent="showQuiz()"
                    >Next <i class="fa fa-arrow-right"></i> </base-button
                  >
                </div>
                <div
                  class="remain-attempts col-md-4 hideOnMobileView"
                  v-else-if="show_lesson && showQuizFlag"
                >
                  <base-button
                    name="Submit Lesson"
                    @click.prevent="submitLesson()"
                    class="custom-btn"
                  >
                    {{
                      open_lesson.result === 1 || its_super_admin
                        ? "Next"
                        : "Next"
                    }}
                    <i class="fa fa-arrow-right"></i>
                  </base-button>
                </div>
                <div
                  class="remain-attempts col-md-4 hideOnMobileView"
                  v-else-if="show_test && !open_test.practice_test"
                >
                  <base-button
                    name="Back Test"
                    @click.prevent="backTest()"
                    class="custom-btn"
                  >
                    <i class="fa fa-arrow-left"></i> Back
                  </base-button>
                  <base-button
                    name="Submit Test"
                    @click.prevent="submitTest()"
                    class="custom-btn"
                  >
                    Next <i class="fa fa-arrow-right"></i>
                  </base-button>
                </div>
              </div>
              <div class="" v-if="show_passed_msg">
                <div class="row">
                  <div class="col-md-4">
                    <h2 class="mb-0">{{ course_name }}</h2>
                  </div>

                  <div class="col-md-8 text-right" v-if="!its_super_admin">
                    <span class="counter">
                      <label class="hours" :id="'hours_' + crrTimestamp"
                        >00</label
                      >
                      <label id="colon">:</label>
                      <label class="hours" :id="'minutes_' + crrTimestamp"
                        >00</label
                      >
                      <label id="colon">:</label>
                      <label class="hours" :id="'seconds_' + crrTimestamp"
                        >00</label
                      >
                    </span>
                    <input type="hidden" id="hiddenInput" />
                  </div>
                </div>
              </div>
            </template>
            <!-- Pretest Open --->
            <div class="row" v-if="pretestFlag">
              <div class="col-md-12">
                <h4>{{ pretest.name }}</h4>
              </div>
              <div class="col-md-12 mb-4">
                <div
                  class="text-justify course-disc"
                  v-html="pretest.instruction"
                ></div>
              </div>
              <div
                :key="question.id"
                class="col-md-12"
                v-for="(question, index) in pretest.questions"
              >
                <div class="mt-2">
                  <h6 class="questionname">
                    {{ index + 1 }}. {{ question.question_text }}
                  </h6>

                  <div v-if="question.question_type == 1">
                    <div class="col-md-6" v-if="question.validation === 1">
                      <base-input
                        @click.prevent="acceptNumber"
                        placeholder="(555)555-5555"
                        v-model="question.selected_answers"
                      ></base-input>
                    </div>
                    <div class="col-md-6" v-if="question.validation === 2">
                      <base-input
                        name="Email"
                        placeholder="Enter email"
                        type="email"
                        v-model="question.selected_answers"
                      ></base-input>
                    </div>
                    <div class="col-md-6" v-if="question.validation === 3">
                      <base-input
                        type="text"
                        v-model="question.selected_answers"
                      ></base-input>
                    </div>
                    <div class="col-md-6" v-if="question.validation === 4">
                      <el-date-picker
                        :picker-options="pickerOptions1"
                        format="MM/dd/yyyy"
                        value-format="yyyy-MM-dd"
                        placeholder="Pick a day"
                        style="width: 100%"
                        type="date"
                        v-model="question.selected_answers"
                      >
                      </el-date-picker>

<!--                        <el-date-picker-->
<!--                            class="hideOnMobileView"-->
<!--                            :picker-options="pickerOptions1"-->
<!--                            format="MM/dd/yyyy"-->
<!--                            placeholder="Pick a day"-->
<!--                            style="width: 100%"-->
<!--                            type="date"-->
<!--                            v-model="question.selected_answers"-->
<!--                        >-->
<!--                        </el-date-picker>-->

<!--                      <input-->
<!--                        id="dateField"-->
<!--                        type="text"-->
<!--                        class="form-control"-->
<!--                        v-on:keyup="setDate"-->
<!--                        placeholder="MM/DD/YYYY"-->
<!--                        v-model="question.selected_answers"-->
<!--                      />-->
                    </div>
                    <div class="col-md-6" v-if="question.validation === 5">
                      <base-input
                        placeholder="Enter SSN"
                        v-model="question.selected_answers"
                        @input="acceptSSNNumber( question)"
                      ></base-input>
                    </div>
                  </div>
                  <div v-else>
                    <div
                      :key="option.id"
                      class="qtn-checkbox"
                      v-for="option in question.options"
                    >
                      <input
                        :checked="true"
                        :name="'pretest' + question.id"
                        type="radio"
                        v-bind:value="true"
                        v-model="option.selected_answers"
                      />
                      {{ option.option_text }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12 text-right">
                <base-button
                  name="Submit Pretest"
                  @click.prevent="savePretest()"
                  size="custom-btn"
                  >Next <i class="fa fa-arrow-right"></i>
                </base-button>
              </div>
            </div>
            <!--Pre test close-->
            <div class="row" v-if="!pretestFlag">
              <div class="col-md-8">
                <div v-if="show_lesson && !pretestFlag">
                  <div class="row" v-if="!showQuizFlag">
                    <div
                      class="col-md-12 mb-2"
                      v-if="
                        open_lesson.video_url !== '' &&
                        open_lesson.video_url !== null
                      "
                    >
                      <div
                        v-if="
                          open_lesson.type == 'video' &&
                          open_lesson.video_url.includes('vimeo')
                        "
                      >
                        <!-- <vimeo-player
                          :player-height="height"
                          :video-id="open_lesson.video_url"
                          @ended="ended"
                          @play="play"
                          @ready="onReady"
                          ref="player"
                        ></vimeo-player> -->
                        <iframe
                          :src="open_lesson.video_url"
                          allow="autoplay; fullscreen; picture-in-picture"
                          allowfullscreen=""
                          frameborder="0"
                          height="400"
                          width="640"
                        ></iframe>
                      </div>

                      <div
                        v-else-if="
                          open_lesson.type == 'youtube-video' &&
                          open_lesson.video_url.includes('youtube')
                        "
                      >
                        <iframe
                          :src="open_lesson.video_url"
                          allow="autoplay; fullscreen; picture-in-picture"
                          allowfullscreen=""
                          frameborder="0"
                          height="400"
                          width="640"
                        ></iframe>
                      </div>

                      <div
                        v-else-if="
                          open_lesson.type == 'pdf' &&
                          open_lesson.video_url.includes('pdf')
                        "
                      >
                        <adobe-pdf
                          :key="open_lesson.video_url"
                          :url="open_lesson.video_url"
                          path="employee/documents"
                        ></adobe-pdf>
                      </div>
                    </div>
                    <div
                      class="col-md-12 mb-2"
                      v-if="open_lesson.type == 'gamification'"
                    >
                      <slider animation="fade" :autoplay="autoplay" v-if=" open_lesson.gamification.length > 1">
                        <slider-item
                          v-for="(i, index) in open_lesson.gamification"
                          :key="index"
                          style="
                            background-color: #fff;
                            width: 100%;
                            height: 100%;
                            padding: 40px;
                          "
                        >
                          <div
                            class="text-justify course-disc"
                            v-html="open_lesson.gamification[index].content"
                          ></div>
                        </slider-item>
                      </slider>
                       <div v-else
                           style="
                            width: 100%;
                            height: 100%;
                            padding: 40px;
                          "
                          class="slider text-justify course-disc"
                            v-html="open_lesson.gamification[0].content"
                        ></div>
                    </div>

                    <div class="col-md-12">
                      <div
                        class="text-justify course-disc"
                        v-html="open_lesson.lesson_content"
                      ></div>
                    </div>
                    <div class="col-md-3">
                      <base-button
                        v-if="open_lesson.timer_status && !nextButtonEnable"
                        disabled
                        class="custom-btn"
                        name="Show Quiz"
                        @click.prevent="showQuiz()"
                        >Next <i class="fa fa-arrow-right"></i> </base-button
                      >
                      <base-button
                        v-if="!open_lesson.timer_status && !nextButtonEnable"
                        class="custom-btn"
                        @click.prevent="showQuiz()"
                        >Next <i class="fa fa-arrow-right"></i> </base-button
                      >
                      <base-button
                        v-if="!open_lesson.timer_status && nextButtonEnable"
                        name="Show Quiz"
                        class="custom-btn"
                        @click.prevent="showQuiz()"
                        >Next <i class="fa fa-arrow-right"></i> </base-button
                      >
                      <base-button
                        name="Show Quiz"
                        v-if="open_lesson.timer_status && nextButtonEnable"
                        class="custom-btn"
                        @click.prevent="showQuiz()"
                        >Next <i class="fa fa-arrow-right"></i> </base-button
                      >
                    </div>
                    <div
                      v-if="open_lesson.timer_status && !nextButtonEnable"
                      class="col-md-3"
                      style="padding: 16px; background: #e4ffc1"
                    >
                      <b>Completes in:</b>
                    </div>
                    <div
                      v-if="open_lesson.timer_status && !nextButtonEnable"
                      class="col-md-4"
                      style="background: #e4ffc1; text-align: center"
                    >
                      <circular-count-down-timer
                        :key="open_lesson.id"
                        :initial-value="open_lesson.timer_value"
                        @finish="finished"
                        :stroke-width="3"
                        :seconds-stroke-color="'#9ACC59'"
                        :minutes-stroke-color="'#9ACC59'"
                        :hours-stroke-color="'#9ACC59'"
                        :seconds-fill-color="'#ffffff'"
                        :minutes-fill-color="'#ffffff'"
                        :hours-fill-color="'#ffffff'"
                        :padding="4"
                        :size="65"
                        :hour-label="''"
                        :minute-label="''"
                        :second-label="''"
                        :show-second="true"
                        :show-minute="true"
                        :show-hour="true"
                        :notify-every="'minute'"
                      ></circular-count-down-timer>
                    </div>
                  </div>
                  <div v-if="showQuizFlag && open_lesson.quiz_status">
                    <div>
                      <div class="text-justify course-disc">
                        <div v-html="open_lesson.quiz_instruction"></div>
                      </div>

                      <div class="row" id="ques">
                        <div
                          :key="question.id"
                          class="w-100 mt-2"
                          v-for="(question, q_index) in open_lesson.questions"
                        >
                          <div class="mb-1">
                            <div class="col-md-12 form-inline">
                              <span class="">
                                <h6 class="questionname">
                                  {{ q_index + 1 }}.
                                  {{ question.question_text }}
                                </h6>
                              </span>
                              <div class="ml-3"></div>
                            </div>
                          </div>
                          <div
                            :key="option.id"
                            v-for="(option, o_index) in question.options"
                          >
                            <div class="col-md-12 qtn-checkbox">
                              <div class="row left-margin">
                                <div class="col-md-0" v-if="question.selected">
                                  <input
                                    :checked="true"
                                    :name="'lesson' + question.id"
                                    :value="option.id"
                                    type="radio"
                                    v-model="question.selected"
                                    v-on:input="
                                      optionChecked(q_index, o_index, option.id)
                                    "
                                  />
                                </div>
                                <div class="col-md-0" v-else>
                                  <input
                                    :checked="false"
                                    :name="'lesson' + question.id"
                                    :value="option.id"
                                    type="radio"
                                    v-model="question.selected"
                                    v-on:input="
                                      optionChecked(q_index, o_index, option.id)
                                    "
                                  />
                                </div>
                                <div class="col-md-11 col-10 qtn-checkbox">
                                  {{ option.option_text }}
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="submit text-left mt-3">
                        <div>
                          <base-button
                            name="Submit Lesson"
                            @click.prevent="submitLesson()"
                            class="custom-btn"
                            >Next <i class="fa fa-arrow-right"></i>
                          </base-button>
                        </div>
                      </div>
                      <div v-if="showQuizFlag && open_lesson.result === 1">
                        <div class="col-md-12 text-center">
                          <small class="">You've passed this lesson!</small>
                        </div>
                      </div>
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <div class="clearfix"></div>
                    </div>
                  </div>
                  <div v-if="showQuizFlag && !open_lesson.quiz_status">
                    <p>
                      Quiz is not available with this lesson. Click next button
                      to continue.
                    </p>
                  </div>
                </div>

                <div v-if="show_test">
                  <div>
                    <div class="passing-gread">
                      {{
                        show_test && !open_test.practice_test
                          ? "Passing Grade " + this.passing_percent + "%"
                          : ""
                      }}
                    </div>
                    <div
                      class="text-justify course-disc"
                      v-html="open_test.quiz_instruction"
                    ></div>
                  </div>

                  <div
                    :key="question.id"
                    class="mt-2"
                    v-for="(question, q_index) in open_test.questions"
                  >
                    <div class="row">
                      <div class="col-md-12 form-inline">
                        <h6 class="questionname">
                          <b>
                            <i
                              :id="'correct_' + question.id"
                              class="fa fa-check"
                              style="display: none; color: green"
                            ></i>
                            <i
                              :id="'incorrect_' + question.id"
                              class="fa fa-times"
                              style="display: none; color: red"
                            ></i>
                            {{ question.sr_no }}. {{ question.question_text }}
                          </b>
                        </h6>
                      </div>
                    </div>
                    <div
                      :key="option.id"
                      v-for="(option, o_index) in question.options"
                    >
                      <div class="col-md-12 qtn-checkbox">
                        <div class="row left-margin">
                          <div class="col-md-0" v-if="question.selected">
                            <input
                              :checked="true"
                              :name="'test' + question.id"
                              :value="option.id"
                              type="radio"
                              v-model="question.selected"
                              v-on:input="
                                optionTestChecked(q_index, o_index, option.id)
                              "
                            />
                          </div>
                          <div class="col-md-0" v-else>
                            <input
                              :checked="false"
                              :name="'test' + question.id"
                              :value="option.id"
                              type="radio"
                              v-model="question.selected"
                              v-on:input="
                                optionTestChecked(q_index, o_index, option.id)
                              "
                            />
                          </div>
                          <div
                            :id="question.id + '_' + option.id"
                            class="col-md-11 col-10 qtn-checkbox"
                          >
                            <span>{{ option.option_text }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="text-left mt-3" v-if="!open_test.practice_test">
                    <base-button
                      name="Submit Test"
                      @click.prevent="submitTest()"
                      class="custom-btn"
                    >
                      Next  <i class="fa fa-arrow-right"></i>
                    </base-button>
                  </div>

                  <div class="text-left mt-3" v-else>
                    <span v-if="!finishedPracticeTest">
                      <base-button
                        name="Submit Pretest"
                        @click.prevent="submitPracticeTest()"
                        class="custom-btn"
                        v-if="!practiceSubmitDisable"
                      >
                        Submit
                      </base-button>
                      <base-button
                        name="Submit Lesson"
                        @click.prevent="refershPracticeTest()"
                        class="custom-btn"
                        v-else
                      >
                        Next <i class="fa fa-angle-double-right"></i>
                      </base-button>

                      <span class="styleResult" v-if="practiceSubmitDisable"
                        >Result:
                        <b id="styleResultId"
                          >{{
                            Math.round(
                              (correctQuestions / open_test.questions.length) *
                                100
                            )
                          }}%
                        </b>
                      </span>
                    </span>
                    <span v-else>
                      <base-button
                        name="Test Finshed"
                        @click.prevent="showPassedMsg()"
                        class="custom-btn"
                      >
                        Finish
                      </base-button>
                    </span>
                  </div>
                  <div class="clearfix"></div>
                </div>

                <!-- Surveytest Open --->
                <div class="row" v-if="surveytestFlag">
                  <div class="col-md-12">
                    <h4>{{ surveytest.name }}</h4>
                  </div>
                  <div class="col-md-12">
                    <div
                      class="text-justify course-disc"
                      v-html="surveytest.instruction"
                    ></div>
                  </div>
                  <div
                    :key="question.id"
                    class="col-md-12"
                    v-for="(question, index) in surveytest.questions"
                  >
                    <div>
                      <h6 class="questionname">
                        {{ index + 1 }}. {{ question.question_text }}
                      </h6>

                      <div v-if="question.question_type == 1">
                        <div class="mt-2" v-if="question.validation === 1">
                          <base-input
                            @click.prevent="acceptNumber"
                            placeholder="(555) 555-5555"
                            v-model="question.selected_answers"
                          ></base-input>
                        </div>
                        <div class="mt-2" v-if="question.validation === 2">
                          <base-input
                            name="Email"
                            placeholder="Enter email"
                            type="email"
                            v-model="question.selected_answers"
                          ></base-input>
                        </div>
                        <div class="mt-2" v-if="question.validation === 3">
                          <base-input
                            type="text"
                            v-model="question.selected_answers"
                          ></base-input>
                        </div>
                        <div class="mt-2" v-if="question.validation === 4">
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
                        <div
                          :key="option.id"
                          v-for="option in question.options"
                        >
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
                      >Next <i class="fa fa-arrow-right"></i>
                    </base-button>
                  </div>
                </div>
              </div>

              <div
                class="col-md-4 scroll-timeline lessonulLi"
                v-if="!show_passed_msg"
              >
                <side-progress
                v-if="!its_super_admin"
                  :course_id="course_id"
                  v-on:lessonRedirection="lessonRedirection"
                ></side-progress>
              </div>

              <div
                :class="this.food_safe_online_proctored_exam.classes"
                v-if="
                  this.food_safe_online_proctored_exam.enable &&
                  this.food_safe_online_proctored_exam.completed != 1
                "
              >
                <a
                  :disabled="food_safe_online_proctored_exam.loader"
                  :href="food_safe_online_proctored_exam.url"
                  class="btn base-button custom-btn btn-default"
                  target="_blank"
                  >Schedule Proctored Exam
                  <i
                    name="Schedule Proctored Exam"
                    class="fas fa-spin fa-spinner"
                    v-if="food_safe_online_proctored_exam.loader"
                  ></i
                ></a>
                <span
                  class="service-unavailable"
                  v-if="this.food_safe_online_proctored_exam.errors.length >= 1"
                  >Service Unavailable</span
                >
              </div>

              <div class="col-md-12" id="msg_top" v-if="show_passed_msg">
                <congratulations-section
                  :certificate_availbility="certificate_availability"
                  :hot_user="hot_user"
                  :its_super_admin="its_super_admin"
                  :next_course_message="next_course_message"
                  :pass_message="pass_message"
                  :practice_test="open_test.practice_test"
                ></congratulations-section>
              </div>
            </div>
          </card>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import {DatePicker, Option, Select, Table, TableColumn} from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import Swal from "sweetalert2";
import vueVimeoPlayer from "vue-vimeo-player";
import AdobePdf from "./AdobePdf.vue";
import SideProgress from "./SideProgress.vue";
import CongratulationsSection from "./CongratulationsSection.vue";
import CircularCountDownTimer from "vue-circular-count-down-timer";
import EasySlider from "vue-easy-slider";

Vue.use(EasySlider);
Vue.use(vueVimeoPlayer);
Vue.use(CircularCountDownTimer);
export default {
  mixins: [clientPaginationMixin],
  components: {
    AdobePdf,
    SideProgress,
    CongratulationsSection,
    [DatePicker.name]: DatePicker,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      baseUrl: this.$baseUrl,
      practiceSubmitDisable: false,
      nextButtonEnable: false,
      height: 400,
      playerReady: false,
      playerEnded: false,
      show_lesson: false,
      show_test: false,
      testType: "",
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
      next_course: "",
      assignment_gap: "",
      pass_message: "",
      next_course_message: "",
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
      pre_test: [],
      pretest: {},
      surveytest: [],
      formattedPretest: {},
      its_super_admin: false,
      showQuizFlag: false,
      passed_msg: "",
      pretestFlag: false,
      pretest_status: false,
      surveytestFlag: false,
      surveytest_status: false,
      certificate_availability: 0,
      practiceTestResult: {},
      splicedQuestonArray: {},
      remainingQuestionPraticeTest: [],
      finishedPracticeTest: false,
      originalArray: [],
      sliceStart: 0,
      start: 1,
      end: 1,
      examResult: 0,
      correctQuestions: 0,
      incorrectQuestions: 0,
      correctAnswer: "",
      is_last_lesson: "",
      food_safe_online_proctored_exam: {
        enable: 0,
        loader: true,
        url: "",
        completed: 0,
        errors: [],
        classes: "exam-button-wrapper",
      },
      pickerOptions1: {},
      totalSeconds: "",
      hoursLabel: "",
      minutesLabel: "",
      secondsLabel: "",
      totalMinutes: 0,
      crrTimestamp: Math.floor(Date.now()),
      autoplay: false,
    };
  },
  beforeRouteLeave(to, from, next) {
    if(!this.its_super_admin){
    this.updateTimerValue();
    }
    next();
  },
  created() {
    this.isLoading = true;
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (this.hot_user === "company-admin") {
      this.user_id = localStorage.getItem("hot-admin-id");
    } else {
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

  mounted() {
    var test = window.addEventListener("beforeunload", this.updateTimerValue);
    setInterval(this.setTime, 1000);
  },
  methods: {
    backQuiz() {
      try {
        if (this.open_lesson_index - 1 >= 0) {
          this.open_lesson = this.lessons[this.open_lesson_index - 1];
          if (this.open_lesson.quiz_status && !this.open_lesson.result) {
            this.showQuizFlag = true;
          } else if (
            (this.open_lesson.quiz_status || !this.open_lesson.quiz_status) &&
            this.open_lesson.result
          ) {
            if (this.lessons.length > this.open_lesson_index - 1) {
              this.open_lesson_index--;
            } else {
              if (this.open_test_index >= 0) {
                this.show_test = true;
                this.show_lesson = false;
              }
              this.open_lesson_index = this.lessons.length + 1;
            }
            const open_lesson_data1 =
              this.lessons[Math.abs(this.open_lesson_index)];
            this.open_lesson = open_lesson_data1;
          } else {
            this.formattedLessontest = {
              is_last_lesson: this.is_last_lesson,
              course_id: this.course_id,
              test_id: this.open_lesson.id,
              test_type: "lesson",
              questions: [],
            };
            this.testType = "lesson";
            this.submitAnswers(this.formattedLessontest, this.testType);
          }
        }
      } catch (error) {
        console.log("error", error);
      }
    },
    backTest() {
      try {
        this.open_lesson = this.lessons[this.lessons.length - 1];
        this.show_lesson = true;
        this.show_test = false;
        this.open_lesson_index = this.lessons.length - 1;
      } catch (error) {
        console.log("error", error);
      }
    },
    updateTimerValue() {
      let data = {
        hour_time: document.getElementById("hours_" + this.crrTimestamp)
          .innerHTML,
        min_time: document.getElementById("minutes_" + this.crrTimestamp)
          .innerHTML,
        sec_time: document.getElementById("seconds_" + this.crrTimestamp)
          .innerHTML,
        course_id: this.course_id,
        employee_id: this.user_id,
      };
      this.$http.post("course/updateTimerValue", data).then((resp) => {
        console.log("done");
      });
    },
    setTime() {
      this.hoursLabel = document.getElementById("hours_" + this.crrTimestamp);
      this.minutesLabel = document.getElementById(
        "minutes_" + this.crrTimestamp
      );
      this.secondsLabel = document.getElementById(
        "seconds_" + this.crrTimestamp
      );
      ++this.totalSeconds;

      if (this.secondsLabel) {
        this.secondsLabel.innerHTML = this.pad(this.totalSeconds % 60);
        document.getElementById("hiddenInput").value =
          this.secondsLabel.innerHTML;
      }
      this.totalMinutes = this.totalSeconds / 60;
      if (this.minutesLabel) {
        this.minutesLabel.innerHTML = this.pad(
          parseInt(this.totalMinutes % 60)
        );
      }
      if (this.hoursLabel) {
        this.hoursLabel.innerHTML = this.pad(
          parseInt(this.totalSeconds / 3600)
        );
      }
    },
    pad(val) {
      var valString = val + "";
      if (valString.length < 2) {
        return "0" + valString;
      } else {
        return valString;
      }
    },
    setDate(evt) {
      var el = document.getElementById("dateField");
      if (
        (evt.keyCode >= 48 && evt.keyCode <= 57) ||
        (evt.keyCode >= 96 && evt.keyCode <= 105)
      ) {
        evt = evt || window.event;

        var size = document.getElementById("dateField").value.length;

        if (
          (size == 2 && document.getElementById("dateField").value > 12) ||
          (size == 5 &&
            Number(document.getElementById("dateField").value.split("/")[1]) >
              31) ||
          (size == 10 &&
            Number(document.getElementById("dateField").value.split("/")[2]) >
              new Date().getFullYear())
        ) {
          alert("Invalid Date");
          document.getElementById("dateField").value = "";
          return;
        }

        if (
          (size == 2 && document.getElementById("dateField").value < 13) ||
          (size == 5 &&
            Number(document.getElementById("dateField").value.split("/")[1]) <
              32)
        ) {
          document.getElementById("dateField").value += "/";
        }
      }
    },
    finished() {
      this.nextButtonEnable = true;
    },
    showLesson() {
      this.pretestFlag = false;
    },
    showQuiz() {
      try {
        if (this.open_lesson.quiz_status && !this.open_lesson.result) {
          this.showQuizFlag = true;
        } else if (
          (this.open_lesson.quiz_status || !this.open_lesson.quiz_status) &&
          this.open_lesson.result
        ) {
          if (this.lessons.length > this.open_lesson_index + 1) {
            this.open_lesson_index++;
          } else {
            if (this.open_test_index >= 0) {
              this.show_test = true;
              this.show_lesson = false;
            }
            this.open_lesson_index = this.lessons.length - 1;
          }
          const open_lesson_data1 =
            this.lessons[Math.abs(this.open_lesson_index)];
          this.open_lesson = open_lesson_data1;
        } else {
          this.formattedLessontest = {
            is_last_lesson: this.is_last_lesson,
            course_id: this.course_id,
            test_id: this.open_lesson.id,
            test_type: "lesson",
            questions: [],
          };
          this.testType = "lesson";
          this.submitAnswers(this.formattedLessontest, this.testType);
        }
      } catch (error) {
        console.log("error", error);
      }
    },
    acceptNumber() {},
    savePretest() {
      this.formattedPretest = {
        course_id: this.course_id,
        test_id: this.pretest.id,
        test_type: "pre_test",
        questions: [],
      };
      for (let quest of this.pretest.questions) {
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
        this.formattedPretest.questions.push(question_obj);
      }
      this.$http
        .post("course/employeeAnswer", this.formattedPretest)
        .then((resp) => {
          this.pretestFlag = false;
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
    saveSurveytest() {
      this.formattedSurveytest = {
        course_id: this.course_id,
        test_id: this.surveytest.id,
        test_type: "survey",
        questions: [],
      };
      for (let quest of this.surveytest.questions) {
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
        .post("course/employeeAnswer", this.formattedSurveytest)
        .then((resp) => {
          this.surveytestFlag = false;
          Swal.fire({
            icon: "success",
            html: resp.data.message,
            confirmButtonClass: "btn btn-success btn-fill",
            confirmButtonText: "OK",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              this.showPassedMsg();
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

    getData() {
      this.$http
        .post(
          "course/full_data",
          {
            course_id: this.course_id,
            user_id: this.user_id,
          },
          this.config
        )
        .then((resp) => {
          this.pretest_status = resp.data[0].pretest_status;
          this.course_name = resp.data[0].name;
          this.next_course = resp.data[0].next_course;
          this.assignment_gap = resp.data[0].assignment_gap;
          this.pass_message = resp.data[0].pass_message;
          this.certificate_availability = resp.data[0].certificate_available;
          this.remaining_attempts = resp.data[0].allow_attempts;
          this.passing_percent = resp.data[0].passing_percent;
          let lessons = resp.data[0].lessons;
          this.all_tests = resp.data[0].tests;
          let pretest = resp.data[0].pretest;
          let surveytest = resp.data[0].survey;
          this.totalSeconds = resp.data[0].timer_value;
          if (resp.data[0].pretest != "" && !this.pretest_status) {
            this.pretestFlag = true;
            let obj = {
              id: pretest.id,
              name: pretest.name,
              test_type: "pre_test",
              instruction: pretest.instruction,
              questions: [],
            };
            let questions = pretest.questions;
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
            this.pretest = obj;
          }

          for (let test of surveytest) {
            this.surveytestFlag = false;
            if (test != null || test != "") {
              this.surveytest_status = true;
            }
            let obj = {
              id: test.id,
              name: test.name,
              test_type: "survey",
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
            this.surveytest = obj;
          }
          for (let lesson of lessons) {
            let obj = {
              id: lesson.id,
              name: lesson.course_lesson_name,
              allowed_attempts: lesson.allowed_attempts,
              remaining_attempts: lesson.remaining_attempts,
              type: lesson.type,
              video_url: lesson.course_lesson_video,
              is_last_lesson: lesson.is_last_lesson,
              timer_status: lesson.timer_status,
              timer_value: lesson.timer_value_insec,
              lesson_content: lesson.course_lesson_content,
              quiz_instruction: lesson.course_lesson_quiz,
              quiz_status: lesson.quiz_status,
              result: lesson.result,
              gamification: "",
              questions: [],
            };
            if (obj.type == "gamification") {
              this.$http
                .post(
                  "course/gamification_data",
                  {
                    course_id: this.course_id,
                    lesson_id: obj.id,
                  },
                  this.config
                )
                .then((resp) => {
                  obj.gamification = resp.data;
                });
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
                correct_answers: "",
                selected_answers: [],
                options: [],
              };
              if (quest.status) {
                question_obj.status = true;
              } else {
                question_obj.status = false;
              }
              let options = quest.answers;
              for (let opt of options) {
                if (opt.course_quiz_correct_answer) {
                  question_obj.correct_answers = opt.id;
                }
                let opt_obj = {
                  id: opt.id,
                  selected_answers: false,
                  option_text: opt.course_quiz_question_option,
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

          if (this.$route.query.redirection === "yes") {
            this.lessonRedirection(
              parseInt(this.$route.query.lesson_id),
              this.$route.query.type
            );
          }
          if (this.open_lesson_index >= 0) {
            this.show_lesson = true;
            this.open_lesson = this.lessons[this.open_lesson_index];
            this.is_last_lesson = this.open_lesson.is_last_lesson;
          } else {
            this.getTest();
          }
          this.food_safe_online_proctored_exam.enable =
            resp.data[0].food_safe_online_proctored_exam;
          if (this.food_safe_online_proctored_exam.enable) {
            this.$http
              .post("course/proctored-exam", {
                course_id: this.course_id,
              })
              .then((resp) => {
                if (resp.data.url) {
                  this.food_safe_online_proctored_exam.url = resp.data.url;
                  this.food_safe_online_proctored_exam.completed =
                    resp.data.completed;
                  this.food_safe_online_proctored_exam.loader = false;
                } else {
                  this.food_safe_online_proctored_exam.errors =
                    resp.data.errors;
                  this.food_safe_online_proctored_exam.classes =
                    "exam-button-wrapper has-errors";
                }
              });
          }
        });
    },
    lessonRedirection(lesson_id, type) {
      if (type == "lesson") {
        const open_lesson_data = this.lessons.filter((obj) => {
          return obj.id == lesson_id;
        });

        this.show_test = false;
        this.show_lesson = true;
        this.open_lesson = open_lesson_data[0];

        this.open_lesson_index = this.lessons.findIndex(
          (x) => x.id === lesson_id
        );

        this.showQuizFlag = false;
      } else if (type == "test") {
        const open_test_data = this.tests.filter((obj) => {
          return obj.id == lesson_id;
        });
        this.show_test = true;
        this.show_lesson = false;
        this.open_test = open_test_data[0];
        this.open_test_index = this.tests.findIndex((x) => x.id === lesson_id);
        this.showQuizFlag = false;
      }
    },
    getTest() {
      if (this.all_tests.length <= 0) {
        if (this.surveytest_status) {
          this.surveytestFlag = true;
        } else {
          this.showPassedMsg();
          return;
        }
      }
      for (let test of this.all_tests) {
        let obj = {
          id: test.id,
          practice_test: test.practice_test,
          enable_submit: test.enable_submit_button,
          is_last_lesson: test.is_last_lesson,
          passed_msg: test.course_test_pass_msg,
          allowed_attempts: test.allowed_attempts,
          remaining_attempts: test.remaining_attempts,
          quiz_instruction: test.course_test_instruction,
          result: test.result,
          questions: [],
        };
        let questions = test.questions;
        var srl = 1;
        for (let quest of questions) {
          let question_obj = {
            sr_no: srl,
            id: quest.id,
            pass: false,
            question_text: quest.question,
            attempts: quest.allowed_attempts,
            allowed_attempts: quest.allowed_attempts,
            status: false,
            correct_answers: "",
            selected_answers: [],
            options: [],
          };
          if (quest.status) {
            question_obj.status = true;
          } else {
            question_obj.status = false;
          }
          let options = quest.answers;
          for (let opt of options) {
            if (opt.course_quiz_correct_answer) {
              question_obj.correct_answers = opt.id;
            }
            let opt_obj = {
              id: opt.id,
              selected_answers: false,
              option_text: opt.course_quiz_question_option,
            };
            question_obj.options.push(opt_obj);
          }
          obj.questions.push(question_obj);
          srl++;
        }
        this.tests.push(obj);
        if (!obj.result && this.open_test_index < 0) {
          this.open_test_index = this.all_tests.indexOf(test);
        }
      }
      if (this.open_test_index >= 0) {
        this.show_test = true;
        this.show_lesson = false;
        this.pretestFlag = false;
        this.show_passed_msg = false;
        this.submitted = false;
        this.open_test = this.tests[this.open_test_index];

        if (this.open_test.practice_test) {
          this.originalArray = this.open_test.questions;
          this.splicedQuestonArray = this.open_test.questions.slice(
            this.sliceStart,
            this.open_test.enable_submit
          );

          this.open_test.questions = this.splicedQuestonArray;
        }
        this.is_last_lesson = this.open_test.is_last_lesson;
      } else {
        if (this.surveytest_status) {
          this.surveytestFlag = true;
        } else {
          this.showPassedMsg();
        }
      }
    },
    refershPracticeTest() {
      this.practiceSubmitDisable = false;
      if (this.originalArray.length !== this.end) {
        if (this.sliceStart > 0) {
          this.start = this.start + this.open_test.enable_submit;
          this.end = this.end + this.open_test.enable_submit;
        } else {
          this.start =
            parseInt(this.sliceStart) + parseInt(this.open_test.enable_submit);
          this.end =
            parseInt(this.open_test.enable_submit) +
            parseInt(this.open_test.enable_submit);

          this.sliceStart++;
        }
        if (this.end > this.originalArray.length) {
          this.end = this.originalArray.length;
        }
        this.open_test.questions = this.originalArray.slice(
          this.start,
          this.end
        );
      } else {
        this.finishedPracticeTest = true;
      }
    },
    showPassedMsg() {
      this.$http
        .post("course/assignnextcourse", {
          course_id: this.course_id,
          user_id: this.user_id,
        })
        .then((resp) => {
          this.show_lesson = false;
          this.show_test = false;
          this.show_passed_msg = true;
          if (resp.data.status == "Success") {
            this.next_course_message = resp.data.message;
          }
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              html: error.response.data.message,
              icon: "error",
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: "Somthing went wrong!",
              icon: "error",
            });
          }
        });
    },
    submitLesson() {
      this.formattedLessontest = {
        is_last_lesson: this.is_last_lesson,
        course_id: this.course_id,
        test_id: this.open_lesson.id,
        test_type: "lesson",
        questions: [],
      };
      for (let quest of this.open_lesson.questions) {
        let question_obj = {
          question_id: quest.id,
          question: quest.question_text,
          answer: "",
          currect_answer_id: quest.correct_answers,
          answer_id: 0,
          selected: quest.selected,
        };
        for (let option of quest.options) {
          if (option.id == question_obj.selected) {
            question_obj.answer = option.option_text;
            question_obj.answer_id = option.id;
          }
        }
        this.formattedLessontest.questions.push(question_obj);
      }
      this.testType = "lesson";
      this.submitAnswers(this.formattedLessontest, this.testType);
    },
    optionChecked(q_index, o_index, val) {
      this.open_lesson.questions[q_index].selected_answers = [];
      this.open_lesson.questions[q_index].selected_answers.push(val);
    },
    optionTestChecked(q_index, o_index, val) {
      this.open_test.questions[q_index].selected_answers = [];
      this.open_test.questions[q_index].selected_answers.push(val);
    },
    mergeArrayObjects(arr1, arr2) {
      return arr1.map((item, i) => {
        if (item.question_id === arr2[i].question_id) {
          return Object.assign({}, item, arr2[i]);
        }
      });
    },
    submitPracticeTest() {
      this.correctQuestions = 0;
      this.incorrectQuestions = 0;
      for (let selectquest of this.open_test.questions) {
        if (!selectquest.selected) {
          return Swal.fire({
            title: "Error!",
            text: "Please select option(s) to continue.",
            icon: "error",
          });
        }
      }
      this.practiceSubmitDisable = true;
      this.selectedanswers = [];
      this.formattedtest = {
        course_id: this.course_id,
        test_id: this.open_test.id,
        test_type: "practice test",
        questions: [],
      };
      for (let quest of this.open_test.questions) {
        let question_obj = {
          question_id: quest.id,
          //selected: quest.selected
        };
        let question_obj1 = {
          question_id: quest.id,
          selected: quest.selected,
        };
        this.formattedtest.questions.push(question_obj);
        this.selectedanswers.push(question_obj1);
      }

      this.selectedanswers.sort(function (a, b) {
        return parseInt(a.question_id) - parseInt(b.question_id);
      });

      this.loading = true;
      this.$http
        .post("course/practiceTestAnswers", this.formattedtest, this.config)
        .then((resp) => {
          resp.data.sort(function (a, b) {
            return parseInt(a.question_id) - parseInt(b.question_id);
          });

          this.practiceTestResult = this.mergeArrayObjects(
            this.selectedanswers,
            resp.data
          );

          this.practiceTestResult.forEach((item) => {
            let cAnswer = item.question_id + "_" + item.correct_answer;
            let sAnswer = item.question_id + "_" + item.selected;

            if (cAnswer === sAnswer) {
              this.correctQuestions++;
              document.getElementById(cAnswer).classList.add("correct");
              document.getElementById(
                "correct_" + item.question_id
              ).style.display = "inline-block";
            } else {
              this.incorrectQuestions++;
              document.getElementById(cAnswer).classList.add("correct");
              document.getElementById(sAnswer).classList.add("wrong");
              document.getElementById(
                "incorrect_" + item.question_id
              ).style.display = "inline-block";
            }
          });
          if (this.correctQuestions >= this.incorrectQuestions) {
            document.getElementById("styleResultId").style.color = "green";
          } else {
            document.getElementById("styleResultId").style.color = "red";
          }
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    submitTest() {
      this.formattedtest = {
        is_last_lesson: this.is_last_lesson,
        course_id: this.course_id,
        test_id: this.open_test.id,
        test_type: "test",
        questions: [],
      };
      var srl = 1;
      for (let quest of this.open_test.questions) {
        let question_obj = {
          sr_no: srl,
          question_id: quest.id,
          question: quest.question_text,
          answer: "",
          currect_answer_id: quest.correct_answers,
          answer_id: 0,
          selected: quest.selected,
        };
        for (let option of quest.options) {
          if (option.id == question_obj.selected) {
            question_obj.answer = option.option_text;
            question_obj.answer_id = option.id;
          }
        }
        this.formattedtest.questions.push(question_obj);
        srl++;
      }
      this.testType = "test";
      this.submitAnswers(this.formattedtest, this.testType);
    },

    submitAnswers(formattedtest, test_type) {
      this.$http
        .post("course/employeeAnswer", formattedtest)
        .then((resp) => {
          this.pretestFlag = false;
          if (test_type == "test") {
            let totalAttempts =
              resp.data.data.total_attempts - resp.data.data.attempts;
            if (isNaN(totalAttempts)) {
              this.open_test.remaining_attempts = 0;
            } else {
              this.open_test.remaining_attempts = totalAttempts;
            }
          }
          if (test_type == "lesson") {
            this.open_lesson.remaining_attempts =
              resp.data.data.total_attempts - resp.data.data.attempts;
          }

          Swal.fire({
            icon: resp.data.status ? "success" : "error",
            html: resp.data.message,
            confirmButtonClass: "btn btn-success btn-fill",
            confirmButtonText: "OK",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              if (resp.data.retake == 2) {
                this.$router.push("/course_instructions?id=" + this.course_id);
              }
              if (resp.data.data.pass_fail == 1) {
                window.location.reload(true);
              }

              document.getElementById("lesson_test").reset();
              if (test_type == "lesson") {
                for (let question of this.open_lesson.questions) {
                  question.selected = "";
                  question.selected_answers = [];
                }
                this.refreshLessonQuesions();
              }
              if (test_type == "test") {
                for (let question of this.open_test.questions) {
                  question.selected = "";
                  question.selected_answers = [];
                }
                this.refreshTestQuesions();
              }
              e.preventDefault();
            }
          });
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              html: error.response.data.message,
              icon: "error",
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: "Somthing went wrong!",
              icon: "error",
            });
          }
        });
    },
    refreshLessonQuesions() {
      this.$http
        .post(
          "course/full_data",
          {
            course_id: this.course_id,
            user_id: this.user_id,
          },
          this.config
        )
        .then((resp) => {
          this.open_lesson.questions = [];
          let lessons = resp.data[0].lessons;
          for (let lesson of lessons) {
            if (lesson.id == this.open_lesson.id) {
              let obj = {
                questions: [],
              };
              let questions = lesson.questions;

              for (let quest of questions) {
                let question_obj = {
                  id: quest.id,
                  pass: false,
                  question_text: quest.question,
                  attempts: quest.allowed_attempts,
                  allowed_attempts: quest.allowed_attempts,
                  status: false,
                  correct_answers: "",
                  selected_answers: [],
                  options: [],
                };
                if (quest.status) {
                  question_obj.status = true;
                } else {
                  question_obj.status = false;
                }
                let options = quest.answers;
                for (let opt of options) {
                  if (opt.course_quiz_correct_answer) {
                    question_obj.correct_answers = opt.id;
                  }
                  let opt_obj = {
                    id: opt.id,
                    selected_answers: false,
                    option_text: opt.course_quiz_question_option,
                  };
                  question_obj.options.push(opt_obj);
                }
                obj.questions.push(question_obj);
              }
              for (let test of obj.questions) {
                this.open_lesson.questions.push(test);
              }
            }
          }
        });
    },
    refreshTestQuesions() {
      this.$http
        .post(
          "course/full_data",
          {
            course_id: this.course_id,
            user_id: this.user_id,
          },
          this.config
        )
        .then((resp) => {
          this.open_test.questions = [];
          this.all_tests = resp.data[0].tests;
          for (let test of this.all_tests) {
            if (test.id == this.open_test.id) {
              let obj = {
                questions: [],
              };
              let questions = test.questions;
              var srl = 1;
              for (let quest of questions) {
                let question_obj = {
                  sr_no: srl,
                  id: quest.id,
                  pass: false,
                  question_text: quest.question,
                  attempts: quest.allowed_attempts,
                  allowed_attempts: quest.allowed_attempts,
                  status: false,
                  correct_answers: "",
                  selected_answers: [],
                  options: [],
                };
                if (quest.status) {
                  question_obj.status = true;
                } else {
                  question_obj.status = false;
                }
                let options = quest.answers;
                for (let opt of options) {
                  if (opt.course_quiz_correct_answer) {
                    question_obj.correct_answers = opt.id;
                  }
                  let opt_obj = {
                    id: opt.id,
                    selected_answers: false,
                    option_text: opt.course_quiz_question_option,
                  };
                  question_obj.options.push(opt_obj);
                }
                obj.questions.push(question_obj);
                srl++;
              }
              for (let test of obj.questions) {
                this.open_test.questions.push(test);
              }
              //this.tests.push(obj);
            }
          }
        });
    },
      acceptSSNNumber(question) {
          var x = question.selected_answers
              .replace(/\D/g, "")
              .match(/(\d{0,3})(\d{0,2})(\d{0,4})/);
          question.selected_answers = !x[2]
              ? x[1]
              : x[1] + "-" + x[2] + (x[3] ? "-" + x[3] : "");

          var y = question.selected_answers
              .replace(/\D/g, "")
              .match(/(\d{0,3})(\d{0,2})(\d{0,4})/);
          question.selected_answers = !y[2]
              ? y[1]
              : y[1] + "-" + y[2] + (y[3] ? "-" + y[3] : "");
      },
  },
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
  margin-bottom: 0;
}

.disabled:hover {
  cursor: not-allowed;
}

.scroll-timeline {
  overflow-y: auto !important;
  min-height: 200px;
  max-height: 200px;
}

#lesson_test p {
  font-size: 14px;
}

#lesson_test input[type="radio"] {
  margin-bottom: 10px;
}

#lesson_test b {
  font-size: 15px;
}

.remain-attempts p {
  text-align: center;
}

.lessonulLi .fa-check-circle {
  position: relative;
  top: 4px;
}

.lessonulLi i {
  margin-right: 5px;
}

.left-margin {
  margin-left: 0px;
}

.wrong {
  background-color: #f32c2c;
  color: white;
  padding: 0px 6px 0px 6px;
  margin: 1px 0px 3px 10px;
  border: 1px solid #af1834;
  transition: background-color 1s linear;
  -webkit-transition: background-color 1s linear;
}

.correct {
  color: white;
  background-color: #28be28;
  padding: 0px 6px 0px 6px;
  margin: 1px 0px 3px 10px;
  border: 1px solid #538839;
  transition: background-color 1s linear;
  -webkit-transition: background-color 1s linear;
  animation: blinkingBackground 1s infinite;
  -webkit-animation: blinkingBackground 1s infinite; /* Safari 4+ */
  -moz-animation: blinkingBackground 1s infinite; /* Fx 5+ */
  -o-animation: blinkingBackground 1s infinite; /* Opera 12+ */
}

@keyframes blinkingBackground {
  0% {
    background-color: #2eaf2e;
  }
  25% {
    background-color: #14c614;
  }
  50% {
    background-color: #2eaf2e;
  }
  75% {
    background-color: #14c614;
  }
  100% {
    background-color: #2eaf2e;
  }
}

.styleResult {
  padding: 20px;
  color: black;
  font-weight: bold;
}

.exam-button-wrapper {
  position: absolute;
  right: 20px;
  bottom: 23px;
}

.exam-button-wrapper a[disabled="disabled"] {
  pointer-events: none;
}

.exam-button-wrapper.has-errors a {
  background-color: #ff0000;
  border-color: #60aeff;
}

.exam-button-wrapper.has-errors a .fas {
  display: none;
}

.exam-button-wrapper span.service-unavailable {
  display: block;
  padding: 5px;
  text-align: center;
  font-weight: 600;
}
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  iframe {
    width: 100% !important;
    height: auto !important;
  }
}
.counter {
  color: #1bc6f1;
}
.hours,
.minutes,
.seconds,
#colon {
  font-size: 30px !important;
}
.slider {
  height: 400px !important;
  border: 1px solid #ebeef5;
  overflow-y: auto !important;
}

.slider-indicators {
  visibility: hidden !important;
}
.slider-btn-left {
  background: none !important;
}
.slider-btn-right {
  right: 6px !important;
  background: none !important;
}
.slider-btn {
  position: absolute;
  top: 50% !important;
  z-index: 999;
  height: 20px !important;
  width: 20px !important;
  border: none;
  background: #fff;
  color: #0b427b;
  outline: 0;
  transition: none !important;
  cursor: pointer;
}
.slider-icon {
  display: inline-block;
  width: 15px;
  height: 15px;
  border-left: 2px solid rgb(10 206 255) !important;
  border-bottom: 2px solid rgb(10 206 255) !important;
  transition: border 0.2s;
}
.slider-indicator-icon {
  background-color: #0b427b !important;
}
.slider-indicator-active {
  background-color: rgb(10 206 255) !important;
}
.slider::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #f5f5f5;
  border-radius: 10px;
}

.slider::-webkit-scrollbar {
  width: 10px;
  background-color: #f5f5f5;
}

.slider::-webkit-scrollbar-thumb {
  background-color: #c0c4cc;
  border-radius: 10px;
}
.ql-align-center {
  text-align: center !important;
}
.ql-align-right {
  text-align: right !important;
}
.course-disc p img{
  max-width: 100% !important;
  height: 310px !important;
}
</style>
