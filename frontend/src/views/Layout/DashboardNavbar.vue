<template>
  <base-nav
    container-classes="container-fluid"
    class="navbar-top  navbar-expand"
    :class="{ 'top-header navbar-dark': type === 'default' }"
  >
    <base-button
      size="sm"
      v-if="currentRouteName != 'Dashboard'"
      @click="$router.go(-1)"
      ><i class="fa fa-arrow-left"></i> back</base-button
    >
    <!-- <div class="header-search"> 
      <input class="search-input form-control" type="text" placeholder="Search Your Course">
      <span class="searchicon-top"><i class="fas fa-search "></i></span>
    </div> -->
    <div v-if="currentRouteName == 'Dashboard' && username === 'employee'" class="page-title mb-0">
        <h2>Dashboard</h2>
    </div>
    <!-- Navbar links -->
    <ul class="navbar-nav align-items-center ml-md-auto ml-auto">
      <li class="nav-item d-xl-none">
        <!-- Sidenav toggler -->
        <div
          class="pr-3 sidenav-toggler"
          :class="{
            active: $sidebar.showSidebar,
            'sidenav-toggler-dark': type === 'default',
            'sidenav-toggler-light': type === 'light'
          }"
          @click="toggleSidebar"
        >
          <div class="sidenav-toggler-inner">
            <i class="sidenav-toggler-line"></i>
            <i class="sidenav-toggler-line"></i>
            <i class="sidenav-toggler-line"></i>
          </div>
        </div>
      </li>
      <li class="nav-item d-sm-none">
        <a
          class="nav-link"
          href="#"
          data-action="search-show"
          data-target="#navbar-search-main"
        >
          <i class="ni ni-zoom-split-in"></i>
        </a>
      </li>
    </ul>
    <ul class="navbar-nav align-items-center ml-md-0 mobilemenuDev">
      <base-dropdown
        menu-on-right
        class="nav-item"
        tag="li"
        title-tag="a"
        title-classes="nav-link pr-0"
      >
        <a href="#" class="nav-link pr-0" @click.prevent slot="title-container">
          <div class="media align-items-center">
            <div class="media-body d-none d-lg-block mr-3 username-head">
              <span class="mb-0 text-sm font-weight-bold">{{ name }}</span>
            </div>
            <span class="avatar avatar-sm userprofile">
              <img alt="Image placeholder" src="img/theme/user.png" />
            </span>
          </div>
        </a>

        <template>
          <div class="dropdown-header noti-title">
            <div class="username-dropdown">
              <h6 class="text-overflow m-0">Welcome!</h6>
              <span>{{ name }}</span>
            </div>
          </div>

          <a v-on:click="account()" class="dropdown-item">
            <i class="ni ni-single-02"></i>
            <span name="My Profile">My profile</span>
          </a>
          <div class="dropdown-divider"></div>
          <a v-on:click="logout()" class="dropdown-item">
            <i class="ni ni-user-run"></i>
            <span name="Logout">Logout</span>
          </a>
        </template>
      </base-dropdown>
    </ul>
  </base-nav>
</template>
<script>
import { BaseNav } from "@/components";

export default {
  components: {
    BaseNav
  },
  props: {
    type: {
      type: String,
      default: "default", // default|light
      description:
        "Look of the dashboard navbar. Default (Green) or light (gray)"
    }
  },
  computed: {
    routeName() {
      const { name } = this.$route;
      return this.capitalizeFirstLetter(name);
    },
    currentRouteName() {
      return this.$route.name;
    }
  },
  data() {
    return {
      name: "Admin",
      link: "",
      activeNotifications: false,
      showMenu: false,
      searchModalVisible: false,
      searchQuery: "",
      username: ""
    };
  },
  created: function() {
    if (localStorage.getItem("hot-user") === "employee") {
      this.username = "employee";
    } else if (localStorage.getItem("hot-user") === "super-admin") {
      this.username = "Admin";
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.username = "Sub Admin";
    } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.username = "company";
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.username = "manager";
    }
  },
  mounted() {
    if (localStorage.getItem("hot-sidebar") === "admin") {
      this.name = localStorage.getItem("hot-user-full-name");
      this.link = "/account";
    } else if (localStorage.getItem("hot-user") === "employee") {
      this.name = localStorage.getItem("hot-user-name");
      this.link = "/add_employee";
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.name = localStorage.getItem("hot-user-name");
      this.link = "/account";
    }
  },

  methods: {
    account() {
      if (localStorage.getItem("hot-user") === "employee") {
        this.$router.push("/add_employee");
      } else if (localStorage.getItem("hot-user") === "super-admin") {
        this.$router.push("/account");
      } else if (localStorage.getItem("hot-user") === "sub-admin") {
        this.$router.push("/account");
       } else if (localStorage.getItem("hot-user") === "company-admin") {
        this.$router.push("/account");
      } else if (localStorage.getItem("hot-user") === "manager") {
        this.$router.push("/account");
      }
    },
    logout() {
         this.$http
        .get("user/logout_time")
        .then(resp => {
        });
       
      localStorage.removeItem("hot-token");
      localStorage.removeItem("hot_payment_responsible");
      localStorage.removeItem("hot-sidebar");
      localStorage.removeItem("hot-user-id");
      localStorage.removeItem("hot-company-id");
      localStorage.removeItem("hot-user");
      localStorage.removeItem("all_user_search_data");
      localStorage.removeItem("all_company_search_data");
      localStorage.removeItem("all_courses_search_data");
      localStorage.removeItem("all_certificate_search_data");
      localStorage.removeItem("all_tutorial_video_search_data");
      localStorage.removeItem("all_certificate_detail_search_data");

      delete this.$http.defaults.headers["authorization"];
    
      this.$router.push("/login");
    },
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    toggleNotificationDropDown() {
      this.activeNotifications = !this.activeNotifications;
    },
    closeDropDown() {
      this.activeNotifications = false;
    },
    toggleSidebar() {
      this.$sidebar.displaySidebar(!this.$sidebar.showSidebar);
    },
    hideSidebar() {
      this.$sidebar.displaySidebar(false);
    }
  }
};
</script>
