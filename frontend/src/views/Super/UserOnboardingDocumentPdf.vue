<template>
  <div>
    <vue-html2pdf
      margin="[30, 30, 30, 30]"
      :show-layout="false"
      :float-layout="true"
      :enable-download="false"
      :preview-modal="true"
      :paginate-elements-by-height="1400"
      filename="Useronboarding"
      :pdf-quality="2"
      :manual-pagination="false"
      pdf-format="a4"
      pdf-orientation="landscape"
      pdf-content-width="1000px"
      ref="html2Pdf"
    >
      <!-- addedbysaksham -->
      <section slot="pdf-content">
        <div
          v-for="(document, index) in documents"
          :key="document.id"
          class="row col-12 ml-2 mr-2 mt-2"
        >
          <section class="pdf-item">
            <h2 class="row col-12 mt-2">Document {{ index + 1 }}</h2>
            <div class="pb-5" v-html="document.document"></div>
          </section>
         
        </div>
         <div class="html2pdf__page-break" />
        <section class="pdf-item">
          <div class="row col-12 ml-3 mr-2 mt-4">
            <h2>Basic Details</h2>
          </div>
          <div class="row col-12 ml-3 mr-2 mt-4">
            <div class="col-3">
              <label class="form-control-label">First Name </label>
            </div>
            <div class="col-7">
              <base-input
                rules="required"
                name="First Name"
                placeholder="First Name"
                disabled
                :value="userOnboardingDataRow.first_name"
              >
              </base-input>
            </div>
          </div>
          <div class="row col-12 ml-3 mr-2 mt-4">
            <div class="col-3">
              <label class="form-control-label">Last Name </label>
            </div>
            <div class="col-7">
              <base-input
                name="Last Name"
                placeholder="Last Name"
                :value="userOnboardingDataRow.last_name"
                disabled
              >
              </base-input>
            </div>
          </div>
          <div class="row col-12 ml-3 mr-2 mt-4">
            <div class="col-3">
              <label class="form-control-label">Location </label>
            </div>
            <div class="col-7">
              <base-input
                name="Location"
                placeholder="Location"
                disabled
                :value="userOnboardingDataRow.location"
              >
              </base-input>
            </div>
          </div>
          <div class="row col-12 ml-3 mr-2 mt-4">
            <div class="col-3">
              <label class="form-control-label">Date </label>
            </div>
            <div class="col-7">
              <base-input
                name="date"
                placeholder="Date"
                :value="userOnboardingDataRow.date"
                disabled
              >
              </base-input>
            </div>
          </div>
          <div class="row col-12 ml-3 mr-2 mt-4">
            <div class="col-3">
              <label class="form-control-label">IP </label>
            </div>
            <div class="col-7">
              <base-input
                name="IP"
                placeholder="IP"
                :value="userOnboardingDataRow.ip"
                disabled
              >
              </base-input>
            </div>
          </div>
        </section>
        <section class="pdf-item">
          <div class="row col-12 ml-3 mr-2 mt-4">
            <h2>Signature</h2>
          </div>
          <div class="col-12 pt-2 ml-2 mr-2">
            <img :src="userOnboardingDataRow.signature" class="signImg" />
          </div>
        </section>
      </section>
    </vue-html2pdf>
  </div>
</template>
<script>
import Vue from "vue";
import VueHtml2pdf from "vue-html2pdf";
Vue.use(VueHtml2pdf);
export default {
  name: "user-onboarding-document-pdf",
  props: {
    userOnboardingDataRow: {
      type: Object,
      required: true,
      default: {},
    },
  },

  data() {
    return {
      documents: [],
    };
  },
  async mounted() {
    // addedbysaksham
    await this.getDocuments();
    const html2PdfRef = this.$refs.html2Pdf;

    html2PdfRef.generatePdf();
  },
  created() {
  },

  methods: {
    async getDocuments() {
      console.log("Hi");
      // addedbysaksham
      try {
    
        const { data } = await this.$http.get(
          "company/user_onboarding_pdf_documents/" + this.userOnboardingDataRow.document_id
        );
        this.documents = data;
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