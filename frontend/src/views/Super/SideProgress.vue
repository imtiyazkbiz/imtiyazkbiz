<template>
  <div>
    <h3>Progress:</h3>
    <ul v-for="(test, index) in totaltests" :key="index" class="list-group">
      <li v-if="test.status" class="list-group-item active ">
        <i class="fab fa-youtube" v-if="test.lesson_type == 'video'"></i>
        <i
          class="fab fa-youtube"
          v-else-if="test.lesson_type == 'youtube-video'"
        ></i>
        <i class="fa fa-file-pdf-o" v-else-if="test.lesson_type == 'pdf'"></i>
        <span
          class="linkColor"
          @click.prevent="redirectLesson(test.id, test.type)"
        >
          {{ test.name ? test.name : "Test" }}
        </span>
        <i class="fas fa-check-circle" style="float: right;"></i>
      </li>
      <li
        v-else-if="test.id === redirect_id"
        class="list-group-item current_active"
      >
        <i class="fab fa-youtube" v-if="test.lesson_type == 'video'"></i>
        <i
          class="fab fa-youtube"
          v-else-if="test.lesson_type == 'youtube-video'"
        ></i>
        <i class="fa fa-file-pdf-o" v-else-if="test.lesson_type == 'pdf'"></i>
        <span
          class="linkColor"
          @click.prevent="redirectLesson(test.id, test.type)"
          >{{ test.name ? test.name : "Test" }}</span
        >
      </li>
      <li v-else class="list-group-item">
        <i class="fab fa-youtube" v-if="test.lesson_type == 'video'"></i>
        <i
          class="fab fa-youtube"
          v-else-if="test.lesson_type == 'youtube-video'"
        ></i>
        <i class="fa fa-file-pdf-o" v-else-if="test.lesson_type == 'pdf'"></i>
        {{ test.name ? test.name : "Test" }}
      </li>
    </ul>
  </div>
</template>
<script>
export default {
  name: "side-progress",
  props: {
    course_id: String
  },
  data() {
    return {
      totaltests: [],
      redirect_id: ""
    };
  },

  created() {
    this.test();
  },
  methods: {
    test() {
      this.$http
        .post("course/totalpassedtest", {
          course_id: this.course_id
        })
        .then(resp => {
          let data = resp.data.data.tests;
          const testData = data.filter(obj => {
            return obj.pass_fail == 0;
          });

          if (testData.length > 0) {
            if (testData[0].course_lesson_id) {
              this.redirect_id = testData[0].course_lesson_id;
            } else if (testData[0].course_test_id) {
              this.redirect_id = testData[0].course_test_id;
            }
          }

          this.totaltests = [];
          for (let tests of data) {
            let test_obj = {
              id: "",
              name: "",
              lesson_type: "",
              type: tests.lesson_type,
              status: tests.pass_fail
            };
            if (tests.course_lesson_id) {
              test_obj.id = tests.course_lesson_id;
            } else if (tests.course_test_id) {
              test_obj.id = tests.course_test_id;
            }

            if (tests.course_lesson_name) {
              test_obj.name = tests.course_lesson_name;
            }
            if (tests.type) {
              test_obj.lesson_type = tests.type;
            }
            this.totaltests.push(test_obj);
          }
        });
    },
    redirectLesson(id, type) {
      this.$emit("lessonRedirection", id, type);
    }
  }
};
</script>
<style scoped>
.list-group-item.active {
  background-color: #9ece60 !important;
}

.list-group-item {
  padding: 8px 10px;
  font-size: 12px !important;
  border: 0px !important;
  color: #000 !important;
  background-color: #f7f7f7;
  border-radius: 0px !important;
}
.list-group-item.current_active {
  background-color: #4b535deb !important;
}
.list-group-item .linkColor {
  color: white;
}
</style>
