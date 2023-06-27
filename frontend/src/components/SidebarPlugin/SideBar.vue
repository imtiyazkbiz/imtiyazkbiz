<template>
  <div
    class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white"
    @mouseenter="$sidebar.onMouseEnter()"
    @mouseleave="$sidebar.onMouseLeave()"
  >
    <div class="scrollbar-inner sidebar_menu" ref="sidebarScrollArea">
      <div class="sidenav-header d-flex align-items-center">
        <div class="menu-logo">
          <img :src="'img/svg-icons/' + companyLogo" class="img-fluid" />
        </div>

        <!-- <router-link :to="'/dashboard'" class=" btn btn-link">
          <a class="navbar-brand" href="">
            <img :src="logo" class="navbar-brand-img" alt="Sidebar logo" />
          </a>
        </router-link> -->
        <div class="ml-auto  tgl-desk-hide togglemenuicon">
          <!-- Sidenav toggler -->
          <div
            class="sidenav-toggler d-none d-xl-block"
            :class="{ active: !$sidebar.isMinimized }"
            @click="minimizeSidebar"
          >
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </div>
        </div>
      </div>
      <slot></slot>
      <div class="navbar-inner">
        <ul class="navbar-nav">
          <slot name="links">
            <sidebar-item
              v-for="(link, index) in sidebarLinks"
              :key="link.name + index"
              :link="link"
            >
              <sidebar-item
                v-for="(subLink, index) in link.children"
                :key="subLink.name + index"
                :link="subLink"
              >
              </sidebar-item>
            </sidebar-item>
          </slot>
        </ul>
        <slot name="links-after">
          <router-link :to="'/dashboard'" class=" btn btn-link">
            <h6>
              {{ siteName }}
              <b>{{ commenthere }}</b>
            </h6>
            <img
              v-if="logo1"
              :src="logo1"
              class="navbar-brand-img"
              alt="Sidebar logo"
            />
          </router-link>
        </slot>
      </div>
    </div>
  </div>
</template>
<script>
import { Dynamic } from "../../wl";
export default {
  name: "sidebar",
  props: {
    title: {
      type: String,
      default: "",
      description: "Sidebar title"
    },
    shortTitle: {
      type: String,
      default: "CT",
      description: "Sidebar short title"
    },
    logo: {
      type: String,
      default: "Train_321.png",
      description: "Sidebar app logo"
    },
    sidebarLinks: {
      type: Array,
      default: () => [],
      description:
        "List of sidebar links as an array if you don't want to use components for these."
    },
    autoClose: {
      type: Boolean,
      default: true,
      description:
        "Whether sidebar should autoclose on mobile when clicking an item"
    }
  },
  provide() {
    return {
      autoClose: this.autoClose
    };
  },
  data() {
    return {
      logo1: "",
      commenthere: "",
      companyLogo: "",
      siteName: ""
    };
  },
  created() {
    this.sidebar_user =
      localStorage.getItem("hot-user") === "company-admin"
        ? "company"
        : localStorage.getItem("hot-user") === "super-admin"
        ? "super-admin"
        : localStorage.getItem("hot-user") === "sub-admin"
        ? "sub-admin"
        : localStorage.getItem("hot-user") === "manager"
        ? "manager"
        : "employee";
    switch (this.sidebar_user) {
      case "company":
        this.commenthere = "Logged in as Company Admin";
        this.getLogo();
        break;
      case "manager":
        this.commenthere = "Logged in as Manager";
        this.getLogo();
        break;
      case "employee":
        this.commenthere = "Logged in as Employee";
        this.getLogo();
        break;
      case "super-admin":
        this.commenthere = "Logged in as Super Admin";
        break;
      case "sub-admin":
        this.commenthere = "Logged in as Sub Admin";
        break;
      default:
        this.commenthere = "Logged in as Super Admin";
    }
  },
  methods: {
    getLogo() {
      this.$http.get("company/getlogo").then(resp => {
        let data = resp.data[0];

        console.log(data);
        if (data.logo) {
          this.logo1 = this.$baseUrl + "/images/" + data.logo;
        } else {
          this.logo1 = "";
        }
      });
    },
    minimizeSidebar() {
      if (this.$sidebar) {
        this.$sidebar.toggleMinimize();
      }
    }
  },
  mounted() {
    this.companyLogo = Dynamic.LOGO;
    this.$sidebar.isMinimized = this.$sidebar.breakpoint < window.innerWidth;
    this.minimizeSidebar();
  },
  beforeDestroy() {
    if (this.$sidebar.showSidebar) {
      this.$sidebar.showSidebar = false;
    }
  }
};
</script>
<style>
.navbar-vertical .navbar-brand-img,
.navbar-vertical .navbar-brand > img {
  max-width: 100% !important;
  max-height: 4rem !important;
}
.navbar-vertical.navbar-expand-xs .navbar-nav .nav-item .nav-link.active {
  padding-left: 1rem;
  padding-right: 1rem;
  border-radius: 0.375rem;
}

.navbar-vertical.navbar-expand-xs .navbar-nav .nav-item .nav-link.active {
  background: #ffffff00 !important;
  color: #00ccff !important;
  border-right: 4px solid #00ccff;
  border-radius: 0px !important;
}
</style>
