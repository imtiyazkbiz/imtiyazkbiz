<template>
    <div class="content" v-loading.fullscreen.lock="loading">
        <base-header class="pb-6">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
        </base-header>
        <div class="container-fluid mt--6">
            <div>
                <card class="no-border-card" footer-classes="pt-1">
                    <template slot="header">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-lg-6">
                                <h2 class="mb-0">Documents Report</h2>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12 text-right">
                                <base-button name="Clear Filters" class="custom-btn" v-on:click="resetFilters()">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Clear Filters
                                </base-button>
                                <base-button class="custom-btn" name="Export Excel" v-on:click="exportExcel()">Excel Download</base-button>
                            </div>
                        </div>
                    </template>
                    <div>
                        <div class="row d-flex justify-content-center justify-content-sm-between flex-wrap">
                            <div class="col-md-6">
                                <base-input label="Search:" name="Search" v-model="searchQuery" prepend-icon="fas fa-search" placeholder="Search Keyword"></base-input>
                            </div>
                            <div class="col-md-4">
                                <base-input label="Company:">
                                    <el-select filterable name="Company Filter" class="select-primary" v-on:change="fetchData()" v-model="filters.location_id" placeholder="Filter by Location">
                                        <el-option class="select-primary" v-for="item in locations" :key="item.value" :label="item.label" :value="item.value"></el-option>
                                    </el-select>
                                </base-input>
                            </div>
                            <div class="col-md-2 form-group">
                                <base-input label="Showing:">
                                    <el-select class="select-primary pagination-select" v-model="perPage" v-on:change="changePage(1)" placeholder="Per page">
                                        <el-option class="select-primary" v-for="item in perPageOptions" :key="item" :label="item" :value="item"></el-option>
                                    </el-select>
                                </base-input>
                            </div>
                        </div>
                        <div class="user-eltable">
                            <el-table role="table" :data="tableData" stripe highlight-current-row lazy row-key="id" id="tableOne" header-row-class-name="thead-light" class="loginReportGrid table-striped">
                                <el-table-column min-width="100px" prop="first_name">
                                    <template slot="header">
                                        <span @click="sortByColumn('first_name')">First Name
                                            <i v-if="sortedColumn == 'first_name' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        {{ props.row.first_name }}
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="100px" prop="last_name">
                                    <template slot="header">
                                        <span @click="sortByColumn('last_name')">Last Name
                                            <i v-if="sortedColumn == 'last_name' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        {{ props.row.last_name }}
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="100px" prop="company_name">
                                    <template slot="header">
                                        <span>Location</span>
                                        <template slot-scope="props">
                                            {{ props.row.company_name }}
                                        </template>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="100px" prop="user_name">
                                    <template slot="header">
                                        <span @click="sortByColumn('title')">Document Name
                                            <i v-if="sortedColumn == 'title' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        {{ props.row.name }}
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="100px" prop="user_name">
                                    <template slot="header">
                                        <span @click="sortByColumn('type')">Type
                                            <i v-if="sortedColumn == 'type' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        {{ props.row.type }}
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="100px" prop="user_name">
                                    <template slot="header">
                                        <span @click="sortByColumn('created_at')">Date
                                            <i v-if="sortedColumn == 'created_at' && order === 'asc'" class="fas fa-arrow-up text-blue linkColor"/>
                                            <i v-else class="fas fa-arrow-down text-blue linkColor"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        {{ props.row.created_at }}
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                    <div slot="footer" class="d-flex justify-content-end">
                        <nav v-if="pagination && tableData.length > 0">
                            <div class="row">
                                <div class="col-md-12">
                                    <ul
                                        class="pagination custompagination justify-content-end align-items-center">
                                        <p class="p-0 m-0 mr-2">
                                            Showing {{ tableData.length }} of {{ totalData }} entries
                                        </p>
                                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                            <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                                                <i class="fa fa-caret-left"></i>
                                            </a>
                                        </li>
                                        <li v-for="(page, index) in pagesNumber" class="page-item" :class="{ active: page == currentPage }" v-bind:key="index">
                                            <a href="javascript:void(0)" @click.prevent="changePage(page)" class="page-link">{{ page }}</a>
                                        </li>
                                        <li class="page-item" :class="{ disabled: currentPage === last_page}">
                                            <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                                                <i class="fa fa-caret-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                    </div>
                </card>
            </div>
        </div>
    </div>
</template>
<script>
import {Option, Select, Table, TableColumn} from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import XLSX from "xlsx";

let timeout = null;
export default {
    mixins: [serverSidePaginationMixin],
    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
    },
    data() {
        return {
            loading: false,
            searchQuery: '',
            filters: {
                location_id: '',
            },
            locations: [
                {
                    label: 'All',
                    value: '',
                },
            ],
            tableData: [],
        }
    },
    created: function () {
        this.$http.post("location/all_company_location", {
            role: this.editor,
        }).then((resp) => {
            for (let loc of resp.data) {
                let obj = {
                    label: loc.name,
                    value: loc.id,
                };
                this.locations.push(obj);
            }
        });
        this.order = '';
        this.fetchData();
    },
    methods: {
        resetFilters: function () {
            this.searchQuery = '';
            this.filters.location_id = '';
            this.fetchData();
        },
        fetchData: function () {
            this.$http.get('resources/documents-report', {
                params: {
                    search: this.searchQuery,
                    page: this.currentPage,
                    column: this.sortedColumn ? this.sortedColumn : 'created_at',
                    per_page: this.perPage,
                    order: this.order ? this.order : 'desc',
                    company_id: this.filters.location_id,
                }
            }).then((resp) => {
                this.tableData = resp.data.data;
                this.totalData = resp.data.total;
            }).finally(() => (this.loading = false));
        },
        exportExcel() {
            this.$http.get('resources/download-documents-report', {
                params: {
                    search: this.searchQuery,
                    page: this.currentPage,
                    column: this.sortedColumn,
                    per_page: this.perPage,
                    order: this.order,
                    company_id: this.filters.location_id,
                }
            }).then((resp) => {
                const data = XLSX.utils.json_to_sheet(resp.data.download);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, data, "data");
                XLSX.writeFile(wb, "DocumentReports.xlsx");
            }).finally(() => (this.loading = false));
        },
    },
    watch: {
        searchQuery: function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.fetchData();
            }, 300);
        },
    }
};
</script>
