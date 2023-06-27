<template>
  <div>
    <vue-html2pdf
      :show-layout="false"
      :float-layout="true"
      :enable-download="false"
      :preview-modal="true"
      :paginate-elements-by-height="1100"
      filename="document"
      :pdf-quality="2"
      :manual-pagination="false"
      pdf-format="a4"
      pdf-orientation="portrait"
      pdf-content-width="800px"
      ref="html2Pdf"
    >
       
      <section slot="pdf-content">
        <div
          v-for="(document, index) in survey"
          :key="document.id"
          class="row col-12 ml-2 mr-2 mt-2"
        >
          <!-- <section class="pdf-item"> -->
            <h2 class="row col-12 mt-2">{{index+1 + "." }}  {{document.question}}</h2>
            <div class="row col-12 pb-5" v-html="document.answer"></div>
          <!-- </section> -->
         
        </div>
        <div class="html2pdf__page-break" />
      </section>
    </vue-html2pdf>
  </div>
</template>
<script>
import Vue from "vue";
import VueHtml2pdf from "vue-html2pdf";
Vue.use(VueHtml2pdf);
export default {
  name: "post-login-survey-pdf",
  props: {
    survey_id: Number,
    employee_id: Number
  },

  data() {
    return {
      survey: [],
    };
  },
  async mounted() {
    await this.getSurvey();
    const html2PdfRef = this.$refs.html2Pdf;

    html2PdfRef.generatePdf();
  },
  created() {
  },

  methods: {
    async getSurvey() {
      try {
    
        const { data } = await this.$http.get(
          "getPostLoginSurveySubmissions/" + this.survey_id + "/" + this.employee_id
        );
        this.survey = data;
      } catch (Err) {
        console.error(Err);
      }
    },
  },
  computed: {},
};
</script>
<style scoped>
</style>