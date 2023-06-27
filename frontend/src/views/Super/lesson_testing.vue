<template
  ><div v-if="show_lesson && !pretestFlag">
    <div v-if="!showQuizFlag">
      <div class="row">
        <div class="col-md-8 text-left">
          <div
            v-if="
              open_lesson.video_url !== '' && open_lesson.video_url !== null
            "
          >
            <div
              v-if="open_lesson.mp4 && open_lesson.video_url.includes('vimeo')"
            >
              <vimeo-player
                ref="player"
                :video-id="open_lesson.video_url"
                @ready="onReady"
                @play="play"
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
            <div class="col-md-12 iframe_append" v-if="!open_lesson.mp4"></div>
          </div>
        </div>
        <div class="col-md-4">
          <ul
            v-for="(test, index) in totaltests"
            :key="index"
            class="list-group"
          >
            <li v-if="test.status" class="list-group-item active">
              <i class="fab fa-youtube" v-if="test.lesson_type == 'video'"></i>
              <i
                class="fa fa-file-pdf-o"
                v-else-if="test.lesson_type == 'pdf'"
              ></i>
              {{ test.name ? test.name : "Test" }}
              <i class="fas fa-check-circle" style="float: right;"></i>
            </li>
            <li v-else class="list-group-item">
              <i class="fab fa-youtube" v-if="test.lesson_type == 'video'"></i>
              <i
                class="fa fa-file-pdf-o"
                v-else-if="test.lesson_type == 'pdf'"
              ></i>
              {{ test.name ? test.name : "Test" }}
            </li>
          </ul>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="text-justify" v-html="open_lesson.lesson_content"></div>
        </div>
      </div>
    </div>

    <div v-if="showQuizFlag">
      <div>
        <div class="row">
          <div class="col-md-8 text-justify">
            <div v-html="open_lesson.quiz_instruction"></div>
          </div>
        </div>
        <div class="row" id="ques">
          <div
            class="col-md-12"
            v-for="(question, q_index) in open_lesson.questions"
            :key="question.id"
          >
            <div class="mb-3">
              <div class="col-md-12 form-inline">
                <span class=""
                  ><b>{{ q_index + 1 }}. {{ question.question_text }}</b></span
                >
                <div class=" ml-3"></div>
              </div>
            </div>
            <div v-for="(option, o_index) in question.options" :key="option.id">
              <div class="col-md-12">
                <div>
                  <div v-if="question.selected">
                    <input
                      type="radio"
                      v-model="question.selected"
                      :checked="true"
                      v-bind:value="'val_' + option.id"
                      v-on:input="
                        optionChecked(q_index, o_index, 'val_' + option.id)
                      "
                      :label="'val_' + option.id"
                      v-bind:id="o_index + '_' + q_index + '_' + option.id"
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
                        optionChecked(q_index, o_index, 'val_' + option.id)
                      "
                      :label="'val_' + option.id"
                      v-bind:id="o_index + '_' + q_index + '_' + option.id"
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
  <div v-if="show_test">
    <div class="row" id="test_top"></div>
    <div class="col-md-12">
      <!-- <p>{{ open_test.quiz_instruction }}</p> -->
      <div class="text-justify" v-html="open_test.quiz_instruction"></div>
    </div>
    <div class="row">
      <div
        class="col-md-12 mt-2"
        v-for="(question, q_index) in open_test.questions"
        :key="question.id"
      >
        <div class="mb-3">
          <div class="col-md-12 form-inline">
            <span class=""
              ><b>{{ q_index + 1 }}. {{ question.question_text }}</b></span
            >
          </div>
        </div>
        <div v-for="(option, o_index) in question.options" :key="option.id">
          <div class="col-md-12">
            <div v-if="option.selected_answers">
              <base-checkbox
                class=""
                :checked="true"
                v-model="option.selected_answers"
                v-bind:value="option.id"
                v-bind:id="o_index + '_' + q_index + '_' + option.id"
                v-on:input="optionTestChecked(q_index, o_index)"
                >{{ option.option_text }}</base-checkbox
              >
            </div>
            <div v-else>
              <base-checkbox
                class=""
                :checked="false"
                v-model="option.selected_answers"
                v-bind:value="option.id"
                v-bind:id="o_index + '_' + q_index + '_' + option.id"
                v-on:input="optionTestChecked(q_index, o_index)"
                >{{ option.option_text }}</base-checkbox
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row" v-if="open_test.result === 1">
      <div class="col-md-12 text-center">
        <small style="color: #444C57" class=" ">You've passed this Test!</small>
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
      Your certificate for the course is generated under Certificates. Please
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
</template>
