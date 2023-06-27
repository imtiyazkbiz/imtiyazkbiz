<template>
    <div v-loading.fullscreen.lock="loading" class="content">
        <base-header class="pb-6">
            <div class="row align-items-center py-2"></div>
        </base-header>
        <div class="container-fluid mt--6">
            <div>
                <card body-classes="" class="no-border-card" footer-classes="pb-2">
                    <template slot="header">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-left">
                                <h2 class="mb-0">Purchase Course</h2>
                            </div>
                        </div>
                    </template>
                    <div>
                        <div
                            class="
                col-12
                d-flex
                justify-content-center justify-content-sm-between
                flex-wrap
              "
                        ></div>
                        <div>

                            <h2>
                              Select Course(s) <span style="color: red">*</span>
                              <base-button
                                v-if="courseSelectionFocused"
                                type="success"
                                size="sm"
                                class="right"
                                plain
                                @click="doneClicked"
                                >Done</base-button
                              >
                            </h2>
                            <div class="row">
                              <div class="col-md-12">
                                <el-select
                                  class="select1"
                                  v-model="checked_courses"
                                  style="width: 100%"
                                  multiple
                                  filterable
                                  placeholder="Select Course(s)"
                                  @focus="showDone"
                                  @visible-change="doneClicked"
                                >
                                  <el-option-group label="Courses">
                                    <span v-for="option in tableData" :key="option.id">
                                      <el-option
                                        :label="option.name +' - $'+ option.cost"
                                        :value="option.id"
                                      >
                                      </el-option>
                                    </span>
                                  </el-option-group>
                                </el-select>
                              </div>
                            </div> 
                            <base-button
                                class="custom-btn mt-3"
                                data-original-title="Purchase Course"
                                data-toggle="tooltip"
                                size="sm"
                                type="success"
                                @click.native="purchaseCourse()"
                            >
                                Purchase
                            </base-button>
                        </div>
                    </div>
                </card>
            </div>
        </div>
        <modal :show.sync="purchaseCourseModel">
            <h4 slot="header" class="modal-title mb-0" style="color:#444C57">
                Payment for {{ course_name }}
            </h4>
            <pay-by-employee
                :amountPayable="amountPayable"
                :discount="discount"
                :orignalAmount="originalAmount"
                address=""
                city=""
                state=""
                type="purchaseCourse"
                zipcode=""
                v-on:payClicked="payClicked"
            />
        </modal>
    </div>
</template>
<script>
import Vue from "vue";
import {Option, OptionGroup, Select, Table, TableColumn} from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import PayByEmployee from "./PayByEmployee.vue";
import Swal from "sweetalert2";

export default {
    mixins: [clientPaginationMixin],
    components: {
        PayByEmployee,
        [Select.name]: Select,
        [Option.name]: Option,
        [OptionGroup.name]: OptionGroup,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
    },
    data() {
        return {
            loading: false,
            tableData: [],
            purchaseCourseModel: false,
            amountPayable: 0,
            originalAmount: 0,
            discount: 0,
            course_id: 0,
            courseSelectionFocused: false,
            checked_courses: [],
            courses_id: [],
            total_cost: "",
            course_name:""
        };
    },

    created() {
        this.fetchData();
        this.checked_courses = JSON.parse(localStorage.getItem("courses"));
    },
    methods: {

        fetchData() {
            this.loading = true;
            this.$http
                .get("employees/not_purchased_courses")
                .then((resp) => {
                    //this.tableData = [];
                    this.tableData = resp.data;
                })
                .finally(() => (this.loading = false));
        },

        purchaseCourse() {
            this.purchaseCourseModel = true;
            // this.amountPayable = row.cost;
            // this.course_id = row.id;
            // this.course_name = row.name;
            // console.log(this.checked_courses);

            this.loading = true;
            this.courses_id = this.checked_courses;

            if(this.checked_courses.length <= 0)
            {
                this.purchaseCourseModel = false;
                this.loading = false;
            }
            let data = {
                request_from: "employee_course_purchase",
                course_ids: this.checked_courses,

            };
            this.$http
                .post("user/course_data", data)
                .then(resp => {
                    this.amountPayable = resp.data.total;
                    this.course_name = resp.data.courses_name;
                    this.loading = false;
                })
                .catch(function (error) {
                    if (error.response.status === 422) {
                        return Swal.fire({
                            title: "Error!",
                            html: error.response.data.message,
                            icon: "error"
                        });
                    }
                });
        },
        payClicked(cardData, addressData) {
            this.loading = true;
            this.formData = {};
            let payment = {
                payment_type: "purchaseCourse",
                description: "Course Purchased by employee",
                cardholder_street_address: "",
                cardholder_zip: "",
                cardholder_city: "",
                cardholder_state: "",
                transaction_amount: "",
                token: cardData.token
            };
            if (addressData.address != "") {
                payment.cardholder_street_address = addressData.address;
                payment.cardholder_zip = addressData.zipcode;
                payment.cardholder_city = addressData.city;
                payment.cardholder_state = addressData.state;
            } else {
                payment.cardholder_street_address = this.address;
                payment.cardholder_zip = this.zipcode;
            }
            payment.transaction_amount = this.amountPayable.toFixed(2);
            this.formData.payment = payment;

            //this.formData.course_id = this.course_id;
            this.formData.courses_id = this.courses_id;


            this.$http
                .post("employees/purchase_course", this.formData)
                .then(resp => {
                    this.loading = false;
                    Swal.fire({
                        title: "Success!",
                        text: "Course Purchased Successfully.",
                        icon: "success"
                    }).then(result => {
                        this.purchaseCourseModel = false;
                        this.fetchData();
                    });
                })
                .catch(function (error) {
                    if (error.response.status === 422) {
                        this.loading = false;
                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error"
                        });
                        this.loading = false;
                    }
                }).finally(() => (this.loading = false));

        },
        showDone() {
            this.courseSelectionFocused = true;
        },
        doneClicked() {
            this.courseSelectionFocused = false;
        },
        showAgreement() {

            this.formData = {
                courses: this.checked_courses,
            };
        }

    },
};
</script>
<style scoped>
.no-border-card .card-footer {
    border-top: 0;
}

@media only screen and (max-width: 760px),
(min-device-width: 768px) and (max-device-width: 1024px) {
    .empcoursesGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Course Name";
    }

    .empcoursesGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Cost";
    }

    .empcoursesGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Action";
    }
}

.el-select-dropdown ul.el-select-group__wrap {
    display: block !important;
} 
</style>
