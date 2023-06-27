
/*!

=========================================================
* Vue Argon Dashboard PRO - v1.1.0
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-dashboard
* Copyright 2019 Creative Tim (https://www.creative-tim.com)

* Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/
import Vue from 'vue';
import DashboardPlugin from './plugins/dashboard-plugin';
import App from './App.vue';
import axios from 'axios'
// router setup
import router from './routes/router';
import LoadScript from 'vue-plugin-load-script';
//walkthrough
import VueIntro from 'vue-introjs';
import 'intro.js/introjs.css';
Vue.use(LoadScript);
// plugin setup
Vue.use(DashboardPlugin);

// plugin setup
Vue.use(VueIntro);
Vue.prototype.$baseUrl= process.env.VUE_APP_API_URL || 'http://localhost:8008/';

let headers = {
  authorization: 'Bearer '+localStorage.getItem("hot-token"),
  'content-type': 'application/json'
}

Vue.prototype.$http = axios.create({
    baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8008/',
    headers
});

// before a request is made start the nprogress
Vue.prototype.$http.interceptors.request.use(config => {
  NProgress.start();
  return config
}, error => {
  // Do something with request error
  return Promise.reject(error);
});

// before a response is returned stop nprogress
Vue.prototype.$http.interceptors.response.use(response => {
  NProgress.done();
  return response
}, error => {
  NProgress.done();
  return Promise.reject(error);
});

/* eslint-disable no-new */
new Vue({
  el: '#app',
  render: h => h(App),
  router
});
