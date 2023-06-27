<template>
    <card id="non-comp-section" class="no-border-card" footer-classes="pb-2">
        <template slot="header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="mb-0">Completions Within Past 7 Days</h3>
                </div>
            </div>
        </template>
        <div>
            <div class="row">
                <div class="col-md-6">
                    <label>Search:</label>
                    <base-input v-model="searchQuery" placeholder="Search..." prepend-icon="fas fa-search" v-on:keyup="fetchData()"></base-input>
                </div>
                <div class="col-md-6">
                    <base-input label="Showing:">
                        <el-select v-model="perPage" class="select-primary pagination-select" placeholder="Per page" v-on:change="changePage(1)">
                            <el-option v-for="item in perPageOptions" :key="item" :label="item" :value="item" class="select-primary"></el-option>
                        </el-select>
                    </base-input>
                </div>
            </div>
            <div class="user-eltable">
                <el-table :data="recent_completions_list" class="completionsGrid" header-row-class-name="thead-light custom-thead-light" role="table" row-key="id">
                    <el-table-column label="First Name" min-width="150px" prop="name">
                        <template slot-scope="props">
                            <span>{{ props.row.first_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Last Name" min-width="150px" prop="name">
                        <template slot-scope="props">
                            <span>{{ props.row.last_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Location" min-width="170px" prop="company">
                        <template slot-scope="props">
                            <span>{{ props.row.company_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Course" min-width="170px" prop="course">
                        <template slot-scope="props">
                            <span>{{ props.row.course_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Completion Date" min-width="170px" prop="completion">
                        <template slot-scope="props">
                            <span>{{ props.row.completed_date }}</span>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
        <div slot="footer" class="d-flex justify-content-end">
            <nav v-if="pagination && this.recent_completions_list.length > 0">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="pagination custompagination justify-content-end align-items-center">
                            <p class="p-0 m-0 mr-2">Showing {{ this.recent_completions_list.length }} of {{ totalData }} entries</p>
                            <li :class="{ disabled: currentPage === 1 }" class="page-item">
                                <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                                    <i class="fa fa-caret-left"></i>
                                </a>
                            </li>
                            <li v-for="(page, index) in pagesNumber" v-bind:key="index" :class="{ active: page == currentPage }" class="page-item">
                                <a class="page-link" href="javascript:void(0)" @click.prevent="changePage(page)">{{ page }}</a>
                            </li>
                            <li :class="{disabled: currentPage === last_page}" class="page-item">
                                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)"><i class="fa fa-caret-right"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </card>
</template>
<script>
import moment from "moment";
import { Option, Select, Table, TableColumn } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";

let timeout = null;
export default {
    name: "most-recent-completions",
    mixins: [serverSidePaginationMixin],
    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
    },
    props: {
        company_id: String
    },
    data() {
        return {
            loading: false,
            recent_completions_list: [],
            searchQuery: "",
        };
    },
    watch: {
        searchQuery: function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.fetchData();
            }, 300);
        },
        company_id: function () {
            this.fetchData();
        }
    },
    created() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        }
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.$http
                .post("analytics/recent_completions", {
                    search: this.searchQuery,
                    page: this.currentPage,
                    per_page: this.perPage,
                    company_id: this.company_id,
                })
                .then((resp) => {
                    let completions = resp.data.data;
                    this.totalData = resp.data.total;
                    this.recent_completions_list = [];
                    for (let data of completions) {
                        let obj = {
                            id: data.id,
                            course_name: data.course_name,
                            company_name: data.company_name,
                            completed_date: "",
                            first_name: data.first_name,
                            last_name: data.last_name,
                        };
                        if (data.employee_course_date_completed) {
                            obj.completed_date = moment(data.employee_course_date_completed).format("MM-DD-YYYY");
                        }

                        this.recent_completions_list.push(obj);
                    }
                });
        },
    },
};
</script>
<style scoped>
@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px) {
    .completionsGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "First Name";
    }

    .completionsGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Last name";
    }

    .completionsGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Location";
    }

    .completionsGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Course";
    }

    .completionsGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Completion Date";
    }
}
</style>
