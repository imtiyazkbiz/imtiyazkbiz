<template>
    <div v-loading.fullscreen.lock="loading">
        <div class="header-section">
            <div class="toplogin-btn">
                <router-link to="/signup" class="login-text">Sign up</router-link>
            </div>
            <div class="container">
                <div class="header-body text-center">
                    <div class="row justify-content-center"></div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="login-section">
                <div class="white-shadow-box login-box positionUnset">
                    <h3 class="text-center mb-5 mt-4">Sign in with credentials</h3>
                    <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
                        <form role="form" @submit.prevent="handleSubmit(onSubmit)">
                            <base-input
                                alternative
                                class="mb-3 login-input"
                                name="Username"
                                :rules="{ required: true }"
                                prepend-icon="ni ni-email-83"
                                placeholder="Username"
                                v-model="form.username"
                            >
                            </base-input>
                            <base-input
                                alternative
                                class="mb-3 login-input"
                                name="Password"
                                :rules="{ required: true }"
                                prepend-icon="ni ni-lock-circle-open"
                                type="password"
                                placeholder="Password"
                                v-model="form.password"
                            >
                            </base-input>
                            <div class="text-right">
                                <base-button native-type="submit" class="custom-btn"
                                >Login
                                </base-button
                                >
                            </div>
                        </form>
                        <!-- <router-link to="/signup" class="text-light"
                            ><h3 class="text-center pb-4" style="color:#13b6e7;">
                              Not a customer?
                            </h3></router-link
                          > -->
                        <div class="row">
                            <div class="col-md-6 col-5">
                                <div class="text-left mt-3 mb-2">
                                    <p class="mb-0"></p>
                                    <router-link to="/forget_password" class="underline-class"
                                    >Forgot Password?
                                    </router-link
                                    >
                                </div>
                            </div>
                            <div class="col-md-6  col-7">
                                <div class="text-right mt-3 mb-2 singup-option">
                                    <p class="mb-0 pr-1">Not a customer? &nbsp;</p>
                                    <router-link to="/signup" class="underline-class"
                                    >Sign up
                                    </router-link
                                    >
                                </div>
                            </div>
                        </div>
                        <p class="error" v-if="errors.invalid">{{ errors.invalid }}</p>
                    </validation-observer>
                </div>
            </div>
        </div>
        <div class="push"></div>
    </div>
</template>
<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";

export default {
    data() {
        return {
            loading: false,
            form: {
                username: "",
                password: ""
            },
            errors: {
                invalid: ""
            }
        };
    },
    created: function () {
        if (this.$route.query.email && this.$route.query.password) {
            this.form.username = this.$route.query.email;
            this.form.password = this.$route.query.password;
            this.onSubmit();
        }
    },
    methods: {
        onSubmit() {
            this.loading = true;
            this.$http
                .post("user/login", {
                    email: this.form.username,
                    password: this.form.password
                })
                .then(resp => {
                    localStorage.setItem("hot-token", resp.data.token);
                    localStorage.setItem("hot-user", resp.data.role);
                    localStorage.setItem("hot-logged-user", resp.data.user_id);
                    localStorage.setItem("hot-user-full-name", resp.data.full_name);
                    let headers = {
                        authorization: "Bearer " + resp.data.token,
                        "content-type": "application/json"
                    };
                    this.$http.defaults.headers.authorization =
                        "Bearer " + resp.data.token;
                    let admin = "";
                    let level = "";

                    switch (resp.data.role) {
                        case "super-admin":
                            admin = "super_admin";
                            localStorage.setItem("hot-sidebar", admin);
                            this.$http
                                .post(
                                    "company/managerdata",
                                    {
                                        email: this.form.username
                                    },
                                    {headers}
                                )
                                .then(resp => {
                                    localStorage.setItem("hot-user-id", resp.data[0].id);
                                    this.$router.push("/dashboard");
                                });

                            break;
                        case "company-admin":
                            admin = "admin";
                            localStorage.setItem("hot-sidebar", admin);
                            this.$http
                                .post(
                                    "company/data",
                                    {
                                        email: this.form.username
                                    },
                                    {headers}
                                )
                                .then(resp => {
                                    if (resp.data.level) {
                                        localStorage.setItem("hot-company-level", "parent");
                                    } else {
                                        localStorage.setItem("hot-company-level", "child");
                                    }
                                    localStorage.setItem("hot-admin-id", resp.data.admin_id);
                                    localStorage.setItem("hot-user-id", resp.data[0].id);
                                    localStorage.setItem("hot-company-name", resp.data[0].name);
                                    this.$router.push("/dashboard");
                                });
                            break;
                        case "manager":
                            admin = "manager";
                            localStorage.setItem("hot-sidebar", admin);
                            this.$http
                                .post(
                                    "company/managerdata",
                                    {
                                        email: this.form.username
                                    },
                                    {headers}
                                )
                                .then(resp => {
                                    localStorage.setItem("hot-user-id", resp.data[0].id);
                                    localStorage.setItem("hot-user-name", resp.data[0].full_name);
                                    localStorage.setItem(
                                        "hot-user-number",
                                        resp.data[0].phone_num
                                    );
                                    localStorage.setItem(
                                        "hot-user-2fa",
                                        resp.data[0].is_2f_authenticated
                                    );
                                    this.$router.push("/dashboard");
                                });
                            break;
                        case "employee":
                            admin = "employee";
                            localStorage.setItem("hot-sidebar", admin);
                            this.$http
                                .post(
                                    "employees/user_data",
                                    {
                                        user_name: this.form.username
                                    },
                                    {headers}
                                )
                                .then(resp => {
                                    localStorage.setItem("hot-user-id", resp.data[0].id);
                                    localStorage.setItem("hot-user-name", resp.data[0].full_name);
                                    localStorage.setItem(
                                        "hot-user-number",
                                        resp.data[0].phone_num
                                    );
                                    localStorage.setItem(
                                        "hot-user-2fa",
                                        resp.data[0].is_2f_authenticated
                                    );
                                    if (resp.data[0].employee_status == 0) {
                                        this.errors.invalid = "Account is Deactivated by Admin..!!";
                                    } else {
                                        this.$router.push("/dashboard");
                                    }
                                });
                            break;
                        case "sub-admin":
                            admin = "sub_admin";
                            localStorage.setItem("hot-sidebar", admin);
                            this.$http
                                .post(
                                    "company/managerdata",
                                    {
                                        email: this.form.username
                                    },
                                    {headers}
                                )
                                .then(resp => {
                                    localStorage.setItem("hot-user-id", resp.data[0].id);
                                    this.$router.push("/dashboard");
                                });

                            break;
                        default:
                            //this.$router.push("/login");
                            this.errors.invalid = "Not Valid..!!";
                    }
                })
                .catch(function (error) {
                    let errorText = "Something went wrong! Please try again later.";
                    if (error.response && error.response.status === 422) {
                        errorText = error.response.data.message;
                    }
                    self.processing = false;
                    Swal.fire({
                        title: "Error!",
                        html: errorText,
                        icon: "error"
                    });
                })
                .finally(() => (this.loading = false));
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

.course-section {
    background-color: #ffffff !important;
    padding: 40px;
}

.error {
    color: red;
    text-align: center;
}

.py-5 {
    padding-bottom: 0px !important;
}

.mt--10 {
    margin-top: -10rem !important;
}

.user-icon {
    font-size: 10rem;
    padding-bottom: 1.3rem;
    color: #28c0e7;
}

.user-icon-company {
    font-size: 11.2rem;
    padding-bottom: 0;
    color: #28c0e7;
}

.singup-option {
    display: flex;
    justify-content: flex-end;
}

.btn.custom-btn {
    background-color: #80d610;
    border-color: #80d610;
}
</style>
