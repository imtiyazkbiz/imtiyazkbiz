<template>
  <div v-loading.fullscreen.lock="loading">
    <div class="header-section">
      <div class="toplogin-btn">
        <router-link to="/signup" class="login-text"></router-link>
      </div>
      <div class="container">
        <div class="header-body text-center">
          <div class="row justify-content-center"></div>
        </div>
      </div>
    </div>
	    <div class="balloonimg">
      <div class="balloonimg-two">
        <img src="marketing/images/p-slidebg.png" class="img-fluid" alt="">
      </div>
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-8 offset-md-2 col-12">
            <h3 class="text-center mb-5 mt-4">{{ tour.title }}</h3>
              <iframe v-if="tour.videoUrl" :src="'https://player.vimeo.com/video/' + tour.videoUrl+'?autoplay=1&loop=1'" style="border: 0;" allow="autoplay;" width="100%" height="400"  webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="allowfullscreen" class="text-center">
              </iframe>
          </div>
        </div>   
      </div>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import vueVimeoPlayer from "vue-vimeo-player";
Vue.use(vueVimeoPlayer);

export default {
  data() {
    return {
      loading: false,
      tourId: "",
      tour: {
        title: "Tour",
        videoUrl: ""
      }
    };
  },
  created() {
    console.log('rajesh check video creat or not ');
    if (this.$route.query.id) {
      this.tourId = this.$route.query.id;
      this.getTourVimeoId();
    }
  },
  methods: {
    getTourVimeoId() {
      this.$http.get("get_tour_data/" + this.tourId).then(resp => {
        this.tour.title = resp.data.name;
        this.tour.videoUrl = resp.data.vimeo_video_id;
      });
    }
  }
};
</script>
<style scoped>
body,
html {
  height: 100%;
}
.form-section {
  background-color: #e4e8e8;
}
.login-section {
  background-color: #ececf9;
  padding: 0px;
}
</style>
