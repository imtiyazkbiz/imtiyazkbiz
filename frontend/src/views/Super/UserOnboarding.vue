<template>
  <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
    <form class="" @submit.prevent="handleSubmit(save)">
      <div class="row">
        <div
          class="col-md-12 mt-1"
          v-for="(doc, d_index) in onboarding"
          :key="d_index"
        >
          <span :id="'hidedoc_' + d_index" v-if="d_index > 0" class="hide">
            <div class="text-justify course-disc" v-html="doc.text"></div>
          </span>
          <span :id="'hidedoc_' + d_index" v-else>
            <div class="text-justify course-disc" v-html="doc.text"></div>
          </span>
        </div>
      </div>
      <div
        class="row"
        :id="'hidedoc_' + onboarding.length"
        v-if="showSignature"
      >
        <div class="col-6 mt-2">
          <label class="form-control-label"
            >First Name <span class="requireField">*</span></label
          >
          <base-input
            rules="required"
            name="First Name"
            placeholder="First Name"
            v-model="first_name"
          >
          </base-input>
        </div>
        <div class="col-6 mt-2">
          <label class="form-control-label"
            >Last Name <span class="requireField">*</span></label
          >
          <base-input
            rules="required"
            name="Last Name"
            placeholder="Last Name"
            v-model="last_name"
          >
          </base-input>
        </div>

        <div class="col-12 mt-1">
          <label class="form-control-label"
            >Signature <span class="requireField">*</span></label
          >
          <VueSignaturePad
            id="signature"
            width="100%"
            height="100px"
            ref="signaturePad"
            :options="options"
          />
        </div>
        <div class="col-12 mt-1">
          <label class="form-control-label"> Date:</label>
          <small> {{ currDate }}</small
          ><br />
          <input type="checkbox" v-model="agreepolicy" /> I have read and agree
          to terms and conditions.
        </div>

        <div class="col-3 mt-2">
          <base-button @click="goToPreviousDoc"> Previous </base-button>
        </div>
        <div class="col-9 text-right mt-2">
          <base-button @click="undo" class="btn-danger"> Undo </base-button>
          <base-button
            v-if="agreepolicy"
            class="custom-btn"
            native-type="submit"
          >
            Submit
          </base-button>
          <base-button v-else disabled class="custom-btn" native-type="submit">
            Submit
          </base-button>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 mt-3">
          <base-button
            v-if="currindex != '0' && currindex != onboarding.length"
            class="float-left"
            @click="goToPreviousDoc"
          >
            Previous
          </base-button>

          <base-button
            v-if="currindex != onboarding.length"
            class="float-right"
            @click="goToNextDoc"
          >
            Next
          </base-button>
        </div>
      </div>
    </form>
  </validation-observer>
</template>
<script>
import Vue from "vue";
import Swal from "sweetalert2";
import moment from "moment";
import VueSignaturePad from "vue-signature-pad";
Vue.use(VueSignaturePad);
export default {
  name: "user-onboarding",
  data() {
    return {
      options: {
        penColor: "#000",
      },
      onboarding: [],
      first_name: "",
      last_name: "",
      currindex: 0,
      showSignature: false,
      currDate: "",
      agreepolicy: false,
    };
  },
  mounted() {
    if (this.onboarding.length > 0) {
      for (var i = 1; i < this.onboarding.length; i++) {
        document.getElementById("hidedoc_" + i).classList.add("hide");
      }
    }
  },
  created() {
    this.currDate = moment().format("MM-DD-YYYY");
    this.$http.get("company/user_onboarding_documents/0").then((resp) => {
      for (let docu of resp.data) {
        let obj = {
          id:docu.id,
          text: docu.document,
        };
        this.onboarding.push(obj);
      }
    });
  },

  methods: {
    goToNextDoc() {
      document
        .getElementById("hidedoc_" + this.currindex)
        .classList.remove("show");

      document
        .getElementById("hidedoc_" + this.currindex)
        .classList.add("hide");

      this.currindex = this.currindex + 1;
      if (this.currindex >= this.onboarding.length) {
        this.showSignature = true;
        if (document.getElementById("hidedoc_" + this.currindex)) {
          document
            .getElementById("hidedoc_" + this.currindex)
            .classList.remove("hide");
        }
      } else {
        document
          .getElementById("hidedoc_" + this.currindex)
          .classList.remove("hide");

        document
          .getElementById("hidedoc_" + this.currindex)
          .classList.add("show");
        this.showSignature = false;
      }
    },
    goToPreviousDoc() {
      if (this.currindex == 0) {
        var id = "hidedoc_" + parseInt(this.currindex - 2);
        document.getElementById(id).classList.remove("hide");
        document.getElementById(id).classList.add("show");
        var curid = "hidedoc_" + parseInt(this.currindex - 1);
        document.getElementById(curid).classList.add("hide");
        this.currindex = this.currindex - 1;
      } else {
        var id = "hidedoc_" + parseInt(this.currindex - 1);
        document.getElementById(id).classList.remove("hide");
        document.getElementById(id).classList.add("show");
        var curid = "hidedoc_" + parseInt(this.currindex);
        document.getElementById(curid).classList.remove("show");
        document.getElementById(curid).classList.add("hide");
        this.currindex = this.currindex - 1;
      }
    },
    undo() {
      this.$refs.signaturePad.undoSignature();
    },
    save() {
      const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
      if (isEmpty) {
        Swal.fire({
          title: "Error!",
          text: "Please read and sign the document to proceed.",
          icon: "error",
        });
      } else {
        this.$http
          .post("company/user_onboarding_sign", {
            first_name: this.first_name,
            last_name: this.last_name,
            documents: this.onboarding,
            signature: data,
          })
          .then((resp) => {
            Swal.fire({
              title: "Success!",
              text: resp.data.message,
              icon: "success",
            }).then((result) => {
              if (result.value) {
                this.$router.go("/dashboard");
              }
            });
          });
      }
    },
  },
};
</script>
<style scoped>
#signature {
  border: double 2px transparent;
  border-radius: 5px;
  background-image: linear-gradient(white, white),
    radial-gradient(circle at top left, #4bc5e8, #4bc5e8);
  background-origin: border-box;
  background-clip: content-box, border-box;
}

.ql-align-center {
  text-align: center !important;
}
.ql-align-right {
  text-align: right !important;
}
.hide {
  display: none;
}
.show {
  display: block;
}
.course-disc {
  padding: 10px;
  overflow-y: auto !important;
  height: 300px !important;
}

.course-disc::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #f5f5f5;
  border-radius: 10px;
}

.course-disc::-webkit-scrollbar {
  width: 10px;
  background-color: #f5f5f5;
}

.course-disc::-webkit-scrollbar-thumb {
  background-color: #c0c4cc;
  border-radius: 10px;
}
.course-disc >>> p >>> img{
  max-width: 400px !important;
}
</style>