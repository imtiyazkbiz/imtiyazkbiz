<template>
  <vimeo-player
    ref="player"
    :video-id="videoID"
    @ready="onReady"
    @play="play"
    @pause="pause"
    @ended="ended"
    :player-height="height"
  >
  </vimeo-player>
</template>
<script>
import Vue from "vue";
import vueVimeoPlayer from "vue-vimeo-player";
Vue.use(vueVimeoPlayer);
export default {
  data() {
    return {
      videoID: "141851770",
      height: 400,
      options: {},
      playerReady: false,
      playerEnded: false
    };
  },
  methods: {
    onReady() {
      this.playerReady = true;
    },
    play() {
      this.$refs.player.play();
    },
    pause() {
      this.$refs.player.pause();
    },
    ended() {
      this.playerEnded = true;
    }
  }
};
</script>
