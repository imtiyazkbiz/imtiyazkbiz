<template>
    <div class="content">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <card>
                <!-- Card header -->
                <h2 slot="header" class="mb-0" v-if="folder_id">
                    Edit New Course Folder
                </h2>
                <h2 slot="header" class="mb-0" v-else>
                    Add New Course Folder
                </h2>
                <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
                    <form
                        class="needs-validation"
                        @submit.prevent="handleSubmit()"
                        enctype="multipart/form-data"
                    >
                        <div class="form-row">
                            <div class="col-md-6">
                                <base-input
                                    label="Folder Name"
                                    name="Folder Name"
                                    placeholder="Folder Name"
                                    rules="required"
                                    v-model="folder.folder_name"
                                >
                                </base-input>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-control-label">Folder Description</label>
                                <textarea
                                    placeholder="Folder Description"
                                    class="form-control"
                                    v-model="folder.folder_description"
                                ></textarea>
                            </div>

                            <div class="col-md-2">
                                <label class="form-control-label">Status</label>
                                <div class="d-flex">
                                    <base-switch
                                        class="mr-1"
                                        type="success"
                                        v-model="folder.status"
                                    ></base-switch>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-control-label">Certificate</label>
                                <el-select
                                    filterable
                                    placeholder="Select Certificate"
                                    v-model="certificate_id"
                                    style="width:100%"
                                >
                                    <el-option
                                        v-for="option in certificates"
                                        class="select-primary"
                                        :value="option.value"
                                        :label="option.label"
                                        :key="option.value"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-control-label"
                                >Certificate Valid For (Days)</label
                                >

                                <base-input min="1" name="Validity" type="number" v-model="folder.expiry"></base-input>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <base-button
                                class="custom-btn"
                                type="danger"
                                @click="$router.go(-1)"
                            >Cancel</base-button
                            >
                            <base-button class="custom-btn" @click.prevent="saveFolders">{{
                                    folder_id !== "" ? " Update" : "Submit"
                                }}</base-button>
                        </div>
                    </form>
                </validation-observer>
            </card>
        </div>
    </div>
</template>
<script>
import Vue from "vue";
import { Table, TableColumn, Select, Option } from "element-ui";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
Vue.component("v-select", vSelect);
export default {
    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn
    },
    data() {
        return {
            hot_user: "",
            hot_token: "",
            config: "",
            datePicker: "",
            folder_id: "",
            folder: {
                folder_name: "",
                folder_description: "",
                expiry: "",
                course: "",
                status: true
            },
            certificates: [],
            certificate_id: ""
        };
    },
    created() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        }

        this.config = {
            headers: {
                authorization: "Bearer " + localStorage.getItem("hot-token"),
                "content-type": "application/json"
            }
        };
        this.$http.get("course/unassignedCertificates").then(resp => {
            this.certificates = [];
            for (let certificate of resp.data) {
                let obj = {
                    value: certificate.id,
                    label: certificate.name
                };
                this.certificates.push(obj);
            }
        });

        if (this.$route.query.id) {
            this.folder_id = this.$route.query.id;
            this.$http
                .get("course/coursefolder/" + this.folder_id, this.config)
                .then(resp => {
                    let data = resp.data;
                    this.folder.folder_name = data.folder_name;
                    this.folder.folder_description = data.folder_description;
                    this.folder.expiry = data.expiry;
                    this.certificate_id = data.certificate_id;
                });
        }
    },
    methods: {
        saveFolders() {
            if (
                this.folder.folder_name !== "" &&
                this.folder.folder_description !== ""
            ) {
                if (this.folder_id !== "") {
                    this.$http
                        .put(
                            "course/coursefolder/" + this.folder_id,
                            {
                                folder_id: this.folder_id,
                                folder_name: this.folder.folder_name,
                                folder_description: this.folder.folder_description,
                                folder_status: this.folder.status,
                                expiry: this.folder.expiry,
                                certificate_id: this.certificate_id
                            },
                            this.config
                        )
                        .then(resp => {
                            Swal.fire({
                                title: "Success!",
                                text: `Folder Has been Updated!`,
                                icon: "success"
                            });
                            this.$router.push("/course_folder");
                        });
                } else {
                    this.$http
                        .post(
                            "course/coursefolder",
                            {
                                folder_name: this.folder.folder_name,
                                folder_description: this.folder.folder_description,
                                expiry: this.folder.expiry,
                                folder_status: this.folder.status,
                                certificate_id: this.certificate_id
                            },
                            this.config
                        )
                        .then(resp => {
                            Swal.fire({
                                title: "Success!",
                                text: `Folder Has been Added!`,
                                icon: "success"
                            });
                            this.$router.push("/course_folder");
                        });
                }
            } else {
                return Swal.fire({
                    title: "Error!",
                    text: `All fields are required!`,
                    icon: "error"
                });
            }
        }
    }
};
</script>
<style>
.stripe-card {
    border: 1px solid grey;
}
.stripe-card.complete {
    border-color: green;
}
.logo-size {
    width: 60%;
    height: auto;
}
</style>
