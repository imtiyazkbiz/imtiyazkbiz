
<template>
    <div class="content">
        <base-header class="pb-1">
            <div class="row align-items-center py-2">
                <div class="col-lg-6 col-7"></div>
            </div>
            <div v-if="editor === 'super-admin' || editor === 'sub-admin'" class="row">
                <div class="col-xl-12">
                    <div class="page-title">
                        <h2>Dashboard</h2>
                    </div>
                </div>
                <div v-if="canViewCompany" class="col-xl-4 col-md-6 dashbaordbox">
                    <router-link class="nav-item" to="/all_companies">
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3>Active Companies</h3>
                                    <h2>{{ active_companies }}</h2>
                                </div>
                                <div class="card-icon icons-1">
                                    <img src="img/svg-icons/XMLID_-1.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div v-else class="col-xl-4 col-md-6 dashbaordbox">
                    <div class="dash-card-box">
                        <div class="card-value-box">
                            <div class="card-left">
                                <h3>Active Companies</h3>
                                <h2>{{ active_companies }}</h2>
                            </div>
                            <div class="card-icon icons-1">
                                <img src="img/svg-icons/XMLID_-1.svg"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="canViewUser" class="col-xl-4 col-md-6 dashbaordbox">
                    <router-link class="nav-item" to="/all_users">
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Active Users">Active Users</h3>
                                    <h2 name="Active Users">{{ active_employees }}</h2>
                                </div>
                                <div class="card-icon icons-2">
                                    <img src="img/svg-icons/asset-1.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div v-else class="col-xl-4 col-md-6 dashbaordbox">
                    <div class="dash-card-box">
                        <div class="card-value-box">
                            <div class="card-left">
                                <h3 name="Active Users">Active Users</h3>
                                <h2 name="Active Users">{{ active_employees }}</h2>
                            </div>
                            <div class="card-icon icons-2">
                                <img src="img/svg-icons/asset-1.svg"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="canViewCourse" class="col-xl-4 col-md-6 dashbaordbox">
                    <router-link class="nav-item" to="/courses">
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Active Courses">Active Courses</h3>
                                    <h2 name="Active Courses">{{ active_courses }}</h2>
                                </div>
                                <div class="card-icon icons-3">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div v-else class="col-xl-4 col-md-6 dashbaordbox">
                    <div class="dash-card-box">
                        <div class="card-value-box">
                            <div class="card-left">
                                <h3 name="Active Courses">Active Courses</h3>
                                <h2 name="Active Courses">{{ active_courses }}</h2>
                            </div>
                            <div class="card-icon icons-3">
                                <img src="img/svg-icons/asset-2.svg"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="editor === 'company' || editor === 'manager'" class="row dashbaord-row-card">
                <div class="col-xl-12">
                    <div class="page-title">
                        <h2>Dashboard</h2>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/company_employees">
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Active Users">Users</h3>
                                    <h2 name="Active Users">{{ active_employees }}</h2>
                                </div>
                                <div class="card-icon icons-1">
                                    <img src="img/svg-icons/asset-1.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/company_courses">
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Active Courses">Courses</h3>
                                    <h2 name="Active Courses">{{ active_courses }}</h2>
                                </div>
                                <div class="card-icon icons-2">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="col-xl-3 col-md-6">
<!--                    <router-link class="nav-item" to="/company_locations">-->
                        <div class="dash-card-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Active Locations">Locations</h3>

                                    <div class="location_FlexBox">
                                        <h2 name="Active Locations">{{ active_location }}</h2>
                                        <div class="select_new company-location-dropdown">
                                            <el-select name="Employee Location Filter" class="select-primary w-100 customInput_box" v-model="adminCompanyId" placeholder="Filter by Location">
                                                <el-option class="select-default" v-for="item in companyAdminlocations" :key="item.value" :label="item.label" :value="item.value"/>
                                            </el-select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-icon icons-3">
                                    <img src="img/svg-icons/XMLID_-1.svg"/>
                                </div>
                            </div>
                        </div>
<!--                    </router-link>-->
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="dash-card-box">
                        <div class="card-value-box">
                            <div class="card-left">
                                <h3>Compliance</h3>
                                <h2>{{ compliance }}</h2>
                            </div>
                            <div class="card-icon icons-4">
                                <img src="img/svg-icons/001-clipboard.svg"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="editor === 'employee'" class="row">
                <!-- <div class="col-xl-12">
                    <div class="page-title">
                        <h2>Dashboard</h2>
                    </div>
                </div> -->
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/employee_courses?course=open">
                        <div v-if="opened != '0'" class="dash-card-box emp-card-box yellow-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Open Courses">Open Courses</h3>
                                    <h2 name="Open Courses">{{ opened }}</h2>
                                </div>
                                <div class="card-icon emp-coures-1">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                        <div v-else class="dash-card-box emp-card-box yellow-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3>Open Courses</h3>
                                    <h2>0</h2>
                                </div>
                                <div class="card-icon emp-coures-1">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/employee_courses?course=failed">
                        <div v-if="failed != '0'" class="dash-card-box emp-card-box red-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Failed Courses">Failed Courses
                                        <el-popover ref="fromPopOver" placement="top-start" trigger="hover" width="250">
                                            <span> These courses can still be taken at any time.</span>
                                        </el-popover>
                                        <span>
                                            <i v-popover:fromPopOver class="el-icon-question" style="color:#dc3912" />
                                        </span>
                                    </h3>
                                    <h2 name="Failed Courses">{{ failed }}</h2>
                                </div>
                                <div class="card-icon emp-coures-3">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                        <div v-else class="dash-card-box emp-card-box red-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3>
                                        Failed Courses
                                        <el-popover ref="fromPopOver" placement="top-start" trigger="hover" width="250">
                                            <span> These courses can still be taken at any time.</span>
                                        </el-popover>
                                        <span>
                                            <i v-popover:fromPopOver class="el-icon-question" style="color:#dc3912" />
                                        </span>
                                    </h3>
                                    <h2>0</h2>
                                </div>
                                <div class="card-icon emp-coures-3">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/employee_courses?course=expired">
                        <div v-if="expired != '0'" class="dash-card-box emp-card-box red-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Expired Courses">Expired Courses
                                        <el-popover ref="expiredCourses" placement="top-start" trigger="hover" width="250">
                                            <span> These courses can still be taken at any time.</span>
                                        </el-popover>
                                        <span>
                                            <i v-popover:expiredCourses class="el-icon-question" style="color:#dc3912" />
                                        </span>
                                    </h3>
                                    <h2 name="Expired Courses">{{ expired }}</h2>
                                </div>
                                <div class="card-icon emp-coures-3">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                        <div v-else class="dash-card-box emp-card-box red-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3>Expired Courses
                                        <el-popover ref="expiredCourses" placement="top-start" trigger="hover" width="250">
                                            <span> These courses can still be taken at any time.</span>
                                        </el-popover>
                                        <span>
                                            <i v-popover:expiredCourses class="el-icon-question" style="color:#dc3912" />
                                        </span>
                                    </h3>
                                    <h2>0</h2>
                                </div>
                                <div class="card-icon emp-coures-3">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
                <div class="col-xl-3 col-md-6">
                    <router-link class="nav-item" to="/employee_courses?course=passed">
                        <div v-if="passed != '0'" class="dash-card-box emp-card-box green-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3 name="Passed Courses">Passed Courses</h3>
                                    <h2 name="Passed Courses">{{ passed }}</h2>
                                </div>
                                <div class="card-icon emp-coures-2">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                        <div v-else class="dash-card-box emp-card-box green-box">
                            <div class="card-value-box">
                                <div class="card-left">
                                    <h3>Passed Courses</h3>
                                    <h2>0</h2>
                                </div>
                                <div class="card-icon emp-coures-2">
                                    <img src="img/svg-icons/asset-2.svg"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
            </div>
        </base-header>
        <div class="container-fluid mt-4">
            <div v-if="editor === 'super-admin' || editor === 'sub-admin'" id="client-section">
                <companies v-if="canViewCompany"></companies>
            </div>
            <div v-if="editor === 'company' || editor === 'manager'" id="non-complaint">
                <div class="row dash_list" style="margin-top: 20px">
                    <div class="col-xl-6">
                        <template>
                            <div class="card shadow dashbarod-table-box">
                                <pof-pie-chart :company_id="adminCompanyId"></pof-pie-chart>
                            </div>
                        </template>
                    </div>

                    <div class="col-xl-6">
                        <template>
                            <div class="card shadow dashbarod-table-box">
                                <completions-graph :company_id="adminCompanyId"></completions-graph>
                            </div>
                        </template>
                    </div>
                </div>
                <card id="non-comp-section" body-classes="" class="no-border-card" footer-classes="pb-2">
                    <template slot="header">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h3 v-intro="'Here you will find a list of all employees who are not compliant with training.'" v-intro-step="2" class="mb-0">Non-Compliant Employees</h3>
                            </div>
                            <div class="col-md-8 text-right">
                                <base-button v-intro="'Click this button to send a mass email to all non-compliant users.'" v-intro-step="3" class="custom-btn" name="Remind Entire List" v-on:click="sendReminderAllEmployees('non_compliant')">Remind Entire List</base-button>
                            </div>
                        </div>
                    </template>
                    <div>
                        <div class="row justify-content-sm-between">
                            <div class="col-md-6">
                                <label>Search:</label>
                                <base-input v-model="searchQuery" placeholder="Search..." prepend-icon="fas fa-search" v-on:keyup="refreshNon()"></base-input>
                            </div>
                            <!--                            <div class="col-md-3 form-group" style="margin-left:0px;!important">
                                                            <label>Showing:</label>
                                                            <el-select v-model="pagination.perPage" class="select-primary pagination-select" placeholder="Per page">
                                                                <el-option v-for="item in pagination.perPageOptions" :key="item" :label="item" :value="item" class="select-primary"></el-option>
                                                            </el-select>
                                                        </div>-->
                        </div>
                        <div class="user-eltable">
                            <el-table :data="queriedData" class="nonGrid" header-row-class-name="thead-light custom-thead-light" role="table" row-key="id" @sort-change="sortChange" @selection-change="selectionChange">
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
                                        <span>{{ props.row.company }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="180px" prop="">
                                    <template slot="header">
                                        <el-popover ref="fromPopOver" placement="top-start" trigger="hover" width="250">
                                            <span> P = Passed. This user is compliant.<br/><br/>O = Open. This user has open courses,but is also compliant.<br/><br/>F = Failed. This user is non-compliant because they did not pass the assigned course in the time permitted. </span>
                                        </el-popover>
                                        <span
                                        >P/O/F
                                            <i v-popover:fromPopOver class="el-icon-question text-blue"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        <span v-on:click="openCourseDetailsDue(props.row)">
                                            <base-button v-if="props.row.fail != '0'" class="POF_btn" type=" pof-value pofcolorfail">{{ props.row.pof }}</base-button>
                                            <base-button v-else-if="props.row.open != '0'" class="POF_btn" type=" pof-value pofcoloropen">{{ props.row.pof }}</base-button>
                                            <base-button v-else-if="props.row.pass != '0'" class="POF_btn" type=" pof-value pofcolorpass">{{ props.row.pof }}</base-button>
                                            <base-button v-else class="POF_btn" type="default">{{ props.row.pof }}</base-button>
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column align="left" label="Action" min-width="120px">
                                    <div slot-scope="{ $index, row }" class="d-flex">
                                        <base-button class="edit" icon name="Send Reminder" size="sm" type="success" @click="sendReminder($index, row)"> Send Reminder</base-button>
                                    </div>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                </card>
                <card body-classes="" class="no-border-card" footer-classes="pb-2">
                    <template slot="header">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0">Employee Courses Due Within 7 Days</h3>
                            </div>
                        </div>
                    </template>
                    <div>
                        <div class="user-eltable">
                            <el-table :data="tbl_data" class="dueGrid" header-row-class-name="thead-light custom-thead-light" role="table">
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
                                        <span>{{ props.row.company }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="180px" prop="">
                                    <template slot="header">
                                        <el-popover ref="fromPopOver" placement="top-start" trigger="hover" width="250">
                                            <span> P = Passed. This user is compliant.<br/><br/>O = Open. This user has open courses,but is also compliant.<br/><br/>F = Failed. This user is non-compliant because they did not pass the assigned course in the time permitted. </span>
                                        </el-popover>
                                        <span
                                        >P/O/F
                                            <i v-popover:fromPopOver class="el-icon-question text-blue"/>
                                        </span>
                                    </template>
                                    <template slot-scope="props">
                                        <span v-on:click="openCourseDetailsDue(props.row)">
                                            <base-button v-if="props.row.fail != '0'" class="POF_btn" type=" pof-value pofcolorfail">{{ props.row.pof }}</base-button>
                                            <base-button v-else-if="props.row.open != '0'" class="POF_btn" type=" pof-value pofcoloropen">{{ props.row.pof }}</base-button>
                                            <base-button v-else-if="props.row.pass != '0'" class="POF_btn" type=" pof-value pofcolorpass">{{ props.row.pof }}</base-button>
                                            <base-button v-else class="POF_btn" type="default">{{ props.row.pof }}</base-button>
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column align="left" label="Action" min-width="120px">
                                    <div slot-scope="{ $index, row }" class="d-flex">
                                        <base-button class="edit" icon name="Send Reminder" size="sm" type="success" @click="sendExpiringReminder($index, row)"> Send Reminder</base-button>
                                    </div>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                </card>
                <most-recent-completions :company_id="adminCompanyId"></most-recent-completions>
            </div>
            <div v-if="editor === 'employee'">
                <card class="no-border-card courses" footer-classes="pb-2" style="margin-top: 20px">
                    <template slot="header">
                        <div class="row" style="padding-bottom: 0px !important">
                            <div class="col-md-3">
                                <h3 v-intro="'These are the courses you are required to take.  If any courses are Expired, you should complete them first, then complete the Open courses.  If there are no courses listed, you are up to date with your training.'" v-intro-step="1">Required Courses</h3>
                            </div>
                            <div class="col-md-9"></div>
                        </div>
                    </template>
                    <div>
                        <div class="col-12 d-flex justify-content-center justify-content-sm-between flex-wrap"></div>
                        <div class="user-eltable">
                            <el-table :data="queriedData" class="courseGrid mobileGrid" header-row-class-name="thead-light" role="table" row-key="id" @sort-change="sortChange" @selection-change="selectionChange">
                                <el-table-column label="Course Name" min-width="230px" prop="name">
                                    <template slot-scope="props">
                                        <span>{{ props.row.course_name }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Due Date" min-width="200px" prop="">
                                    <template slot-scope="props">
                                        <span v-if="currentDate(props.row.due_date) && props.row.course_status != '1'" style="color: red"
                                        ><b>{{ formattedDate(props.row.due_date) }}</b></span
                                        >
                                        <span v-else>{{ formattedDate(props.row.due_date) }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Lesson Status" min-width="200px" prop="">
                                    <template slot-scope="props">
                                        <span v-if="props.row.course_status === 0" style="color: #f50636" type="danger"><b>Failed</b></span>
                                        <span v-if="props.row.course_status === 2" style="color: #ffd600" type="warning"><b>Open</b></span>
                                        <span v-if="props.row.course_status === 1" style="color: #05bf70" type="success"><b>Passed</b></span>
                                        <span v-if="props.row.course_status === 3" style="color: #f50636" type="danger"><b>Expired</b></span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Estimated Time (in min)" min-width="200px" prop="name">
                                    <template slot-scope="props">
                                        <span>{{ props.row.course_length }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Action" min-width="150px">
                                    <template slot-scope="props">
                                        <base-button v-if="is_payByEmployeeOn && !props.row.is_employee_paid" name="Pay Course" type="info" @click.prevent="PayCourse(props.row)">
                                            {{ "Pay" }}
                                        </base-button>
                                        <base-button v-else name="Take Course" type="success" @click.prevent="takingCourse(props.row.id, props.row.due_date, props.row.is_2fa_required)">
                                            {{ props.row.course_attempts !== undefined && props.row.course_attempts.length > 0 ? "Retake Course " : "Take Course" }}
                                        </base-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                </card>
                <div v-if="folderProgress.length > 0" class="row">
                    <div class="col-xl-6">
                        <div class="card shadow dashbarod-table-box">
                            <div class="graph-title">
                                <h3 v-intro="'This section tells you how many courses you have in your program.  The chart shows your progress completing those courses.'" v-intro-step="2">Course Folder Progress</h3>
                            </div>
                            <div class="row">
                                <div v-for="(data, index) in folderProgress" v-bind:key="index" :class="folderProgress.length == 3 ? 'col-md-4' : folderProgress.length == 2 ? 'col-md-6' : folderProgress.length == 1 ? 'col-md-12' : ''">
                                    <folder-progress-graph :failed_count="data.failed_course_count" :folder_name="data.folder_name" :passed_count="data.passed_course_count" :total_count="data.course_count"></folder-progress-graph>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <modal :show.sync="employeeDataModal" class="user-modal">
            <h3 slot="header" class="title title-up" style="color: #444c57">
                {{ employeeCoursesData.first_name }}
                {{ employeeCoursesData.last_name }}'s Assigned Courses
            </h3>
            <div class="col-md-12"></div>
            <form>
                <div class="user-eltable table-full-width">
                    <el-table :data="employeeCoursesData.courseData" class="assignedGrid" header-row-class-name="thead-light custom-thead-light">
                        <el-table-column align="left" label="Lesson name" min-width="180px" property="">
                            <template slot-scope="props">
                                <span>{{ props.row.name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="left" label="Lesson Status" min-width="150px" property="">
                            <template slot-scope="props">
                                <span v-if="props.row.employee_course_status === 1" style="color: #05bf70">Passed</span>
                                <span v-if="props.row.employee_course_status === 2" style="color: #ffd600">Open</span>
                                <span v-if="props.row.employee_course_status === 0" style="color: #f50636">Failed</span>
                                <span v-if="props.row.employee_course_status === 3" style="color: #f50636">Expired</span>
                            </template>
                        </el-table-column>
                        <!--employee_course_due_date-->
                        <el-table-column align="left" label="Date Completed" min-width="150px" property="">
                            <template slot-scope="props">
                                <span v-if="props.row.employee_course_date_completed === null">Not Available</span>
                                <span v-else>{{ formattedDate(props.row.employee_course_date_completed) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="left" label="Date Assigned" min-width="150px" property="">
                            <template slot-scope="props">
                                <span>{{ formattedDate(props.row.employee_course_date_assigned) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="left" label="Due Date" min-width="150px" property="">
                            <template slot-scope="props">
                                <span>{{ formattedDate(props.row.employee_course_due_date) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="left" label="" min-width="150px" property="">
                            <template slot-scope="props">
                                <base-button class="" size="sm" style="cursor: pointer" type="danger" @click="unAssignedCourse(props.$index, props.row)"><i class="fa fa-trash"></i></base-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="text-center my-4"></div>
                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="show2faModal">
            <h3 slot="header" class="title title-up text-primary text-center">Need <u>Two Factor Authentication</u> to continue</h3>
            <form>
                <div class="row">
                    <div class="col-md-12">
                        <h5>This course requires 2 Factor authentication. We will be sending you a verification code on your registered phone number.</h5>
                    </div>
                    <div class="col-md-12">
                        <label><b>Registered Phone Number:</b> {{ phonenumber }} (<a class="linkColor" @click.prevent="changePhone">Add</a>)</label>
                    </div>

                    <div class="col-md-7">
                        <base-input v-if="changePhoneNum" v-model="newphonenumber" placeholder="Enter new phone number" type="tel"></base-input>
                    </div>
                    <div v-if="changePhoneNum" class="col-md-5" style="padding: 0px !important">
                        <label> (<a class="linkColor" @click.prevent="cancelPhone">Cancel</a>)</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>Select verification channel:</label>
                    </div>
                    <div class="col-md-3">
                        <input v-model="verificationType" name="verificationtype" type="radio" value="sms"/>
                        Text
                    </div>
                    <div class="col-md-3">
                        <input v-model="verificationType" name="verificationtype" type="radio" value="call"/>
                        Call
                    </div>
                    <div class="col-md-12 mt-4">
                        <base-button size="sm" type="success" @click.prevent="sendVerification">Start Verification</base-button>
                    </div>
                </div>
            </form>
        </modal>
        <modal :show.sync="askOtpModal">
            <h3 slot="header" class="title title-up text-primary text-center">Enter Code</h3>
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <base-input v-model="otp" placeholder="Enter Code" type="number"></base-input>
                    </div>
                    <div class="col-md-6">
                        <base-button size="sm" type="success" @click.prevent="verifyOtp">Verify</base-button>
                    </div>
                </div>
            </form>
        </modal>
        <modal v-if="payCourseModel" :show.sync="payCourseModel">
            <h4 slot="header" class="modal-title mb-0" style="color: #444c57">Payment</h4>
            <pay-by-employee :address="this.address" :amountPayable="discountedCost" :city="this.city" :discount="is_payByEmployeeDiscount" :orignalAmount="orignalCost" :state="this.state" :zipcode="this.zipcode" type="payByEmployee" v-on:payClicked="payClicked"/>
        </modal>
    </div>
</template>
<script>
import moment from "moment";
// Components
import {Dropdown, DropdownItem, DropdownMenu, Option, Select, Table, TableColumn} from "element-ui";
import clientPaginationMixin from "../Tables/PaginatedTables/clientPaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import Companies from "../Super/Companies.vue";
import PofPieChart from "./PofPieChart.vue";
import CompletionsGraph from "./CompletionsGraph.vue";
import MostRecentCompletions from "./MostRecentCompletions.vue";
import PayByEmployee from "@/views/Super/PayByEmployee.vue";
import FolderProgressGraph from "@/views/Super/FolderProgressGraph.vue";

// Vue.component('apexchart', VueApexCharts)
function randomScalingFactor() {
    return Math.round(Math.random() * 100);
}
export default {
    mixins: [clientPaginationMixin],
    components: {
        Companies,
        PayByEmployee,
        [Dropdown.name]: Dropdown,
        [DropdownItem.name]: DropdownItem,
        [DropdownMenu.name]: DropdownMenu,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
        PofPieChart,
        CompletionsGraph,
        MostRecentCompletions,
        FolderProgressGraph,
    },
    data() {
        return {
            progressButtonContent: false,
            baseUrl: this.$baseUrl,
            show2faModal: false,
            is_authenticated: 0,
            phonenumber: "",
            newphonenumber: "",
            verificationType: "sms",
            changePhoneNum: false,
            askOtpModal: false,
            otp: "",
            employee_data: [],
            employeeDataModal: false,
            courses: [],
            projects: [],
            tableData: [],
            tbl_data: [],
            selectedRows: [],
            locations: [],
            items: [],
            status: [
                {
                    label: "Active",
                    value: "Active",
                },
                {
                    label: "Inactive",
                    value: "Inactive",
                },
                {
                    label: "Show All",
                    value: "",
                },
            ],
            month_count: [],
            report_type: "",
            hot_user: "",
            hot_token: "",
            editor: "",
            active_non_complaint: "",
            active_companies: "",
            active_employees: "",
            active_courses: "",
            active_location: "1",
            employeeEmailData: {
                first_name: "",
                last_name: "",
                location: "",
                id: "",
            },
            byMonth: [],
            compliance: "",
            employeeCoursesData: {
                first_name: "",
                last_name: "",
                location: "",
                courseData: [],
            },
            course_employee_id: "",
            user_id: "",
            passed: "",
            opened: "",
            failed: "",
            expired: "",
            is_payByEmployeeOn: 0,
            payCourseModel: false,
            is_payByEmployeeDiscount: "0",
            discountedCost: 0,
            orignalCost: 0,
            address: "",
            city: "",
            state: "",
            zipcode: "",
            canViewCompany: true,
            canViewUser: true,
            canViewCourse: true,
            folderProgress: "",
            adminCompanyId: '',
            companyAdminlocations: [],
        };
    },
    created: function () {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
        }
        if (localStorage.getItem("hot-user") === "employee") {
            this.editor = "employee";
        } else if (localStorage.getItem("hot-user") === "super-admin") {
            this.editor = "super-admin";
        } else if (localStorage.getItem("hot-user") === "sub-admin") {
            this.editor = "sub-admin";
            this.getRightsDetails();
        } else if (localStorage.getItem("hot-user") === "company-admin") {
            this.editor = "company";
        } else if (localStorage.getItem("hot-sidebar") === "manager") {
            this.editor = "manager";
        }
        if (this.editor === "super-admin" || this.editor === "sub-admin") {
            this.$http.get("company/admin_stats").then((resp) => {
                this.active_courses = resp.data.courses.toString();
                this.active_companies = resp.data.companies.toString();
                this.active_employees = resp.data.employees.toString();
            });
        }
        if (this.editor === "company" || this.editor === "manager") {
            this.company_id = localStorage.getItem("hot-user-id");
            // this.manager_id=localStorage.getItem("hot-admin-id");
            // this.adminCompanyId = this.company_id;
            this.$http.post("location/all", {
                company_id: this.company_id
            }).then(resp => {
                this.companyAdminlocations = [];
                if (resp.data) {
                    for (let loc of resp.data) {
                        let obj = {
                            label: loc.name,
                            value: loc.id
                        };
                        this.companyAdminlocations.push(obj);
                    }
                }
                this.adminCompanyId = parseInt(resp.data[0].id);
            }).finally(() => {
                this.loading = false;
            });

            this.getCompanyStats();
            this.refreshNon();
            this.refreshDue();
            this.getCompanyCourses();
            this.getComplianceCount();
        }
        if (this.editor === "employee") {
            this.user_id = localStorage.getItem("hot-user-id");
            this.$http.get("employees/stats/" + this.user_id).then((resp) => {
                this.passed = resp.data.pass.toString();
                this.opened = resp.data.open.toString();
                this.failed = resp.data.fail.toString();
                this.expired = resp.data.expired.toString();
            });
            this.employeeCourses();
            this.folderProgressData();
        }
    },
    updated: function () {
        window.dispatchEvent(new Event('resize'));
    },
    methods: {
        getRightsDetails() {
            let type = "All";
            this.$http.get("subadmin/subadmin_rights/" + type).then((resp) => {
                this.canViewCompany = resp.data[0].permissions.indexOf("v") !== -1 ? true : false;
                this.canViewUser = resp.data[1].permissions.indexOf("v") !== -1 ? true : false;
                this.canViewCourse = resp.data[2].permissions.indexOf("v") !== -1 ? true : false;
            });
        },
        changePhone() {
            this.changePhoneNum = true;
        },
        cancelPhone() {
            this.newphonenumber = "";
            this.changePhoneNum = false;
        },
        //employee
        folderProgressData() {
            this.$http.get("employees/employeeCourseFolderProgress").then((response) => {
                this.folderProgress = response.data;
            }).catch((error) => {
                console.log("API failed for loading progress data");
            });
        },
        employeeCourses() {
            this.$http
                .post("employees/courses", {
                    search: this.searchQuery,
                    employee_id: this.user_id,
                    section: "dashboard",
                })
                .then((resp) => {
                    this.tableData = [];
                    let course_data = resp.data.courses;
                    let employee_data = resp.data.employee;
                    this.address = employee_data.address;
                    this.state = employee_data.state;
                    this.city = employee_data.city;
                    this.zipcode = employee_data.zipcode;
                    this.is_authenticated = employee_data.is_2f_authenticated;
                    this.phonenumber = employee_data.phone_num;
                    let payby_employee = resp.data.company_pay_by_employee;
                    if (payby_employee != null) {
                        this.company_id = payby_employee.company_id;
                        this.is_payByEmployeeOn = payby_employee.pay_employee_status;
                        this.is_payByEmployeeDiscount = payby_employee.pay_employee_discount;
                    }
                    for (let data of course_data) {
                        let obj = {
                            id: data.course_id,
                            course_name: data.name,
                            course_length: data.length,
                            course_cost: data.cost,
                            discountedCost: "",
                            due_date: data.employee_course_due_date,
                            course_status: data.employee_course_status,
                            is_2fa_required: "",
                        };
                        if (data.course_paid_status === 0) {
                            obj.discountedCost = obj.course_cost - obj.course_cost * (this.is_payByEmployeeDiscount / 100);
                            obj.is_employee_paid = false;
                        } else {
                            obj.is_employee_paid = true;
                        }
                        if (data.is_2fa_required === 1) {
                            obj.is_2fa_required = true;
                        } else {
                            obj.is_2fa_required = false;
                        }
                        this.tableData.push(obj);
                    }
                });
        },

        takingCourse(course_id, due_date, is_2fa_required) {
            if (is_2fa_required && this.is_authenticated == 0) {
                this.show2faModal = true;
            } else {
                this.$router.push("/course_instructions?id=" + course_id + "&due_date=" + due_date);
            }
        },
        sendVerification() {
            let data = {
                phone_no: "",
                chanel_type: this.verificationType,
            };
            if (this.newphonenumber) {
                data.phone_no = this.newphonenumber;
            } else {
                data.phone_no = this.phonenumber;
            }
            this.$http
                .post("twilio/sendOTP", data)
                .then((resp) => {
                    this.askOtpModal = true;
                    Swal.fire({
                        title: "Success!",
                        text: "Otp sent to your phone number.",
                        icon: "success",
                    });
                })
                .catch(function (error) {
                    Swal.fire({
                        title: "Error!",
                        text: "Something went wrong!",
                        icon: "error",
                    });
                });
        },
        verifyOtp() {
            let data = {
                phone_no: "",
                otp: this.otp,
            };
            if (this.newphonenumber) {
                data.phone_no = this.newphonenumber;
            } else {
                data.phone_no = this.phonenumber;
            }
            this.$http
                .post("twilio/VerifysendOTP", data)
                .then((resp) => {
                    this.show2faModal = false;
                    this.askOtpModal = false;
                    Swal.fire({
                        title: "Success!",
                        text: "Verified successfully.",
                        icon: "success",
                    });
                    this.employeeCourses();
                })
                .catch(function (error) {
                    if (error.response.status === 422) {
                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error",
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Something went wrong.",
                            icon: "error",
                        });
                    }
                });
        },
        payClicked(cardData, addressData) {
            let payment = {
                payment_type: "payByEmployee",
                cardholder_street_address: "",
                cardholder_zip: "",
                cardholder_city: "",
                cardholder_state: "",
                transaction_amount: "",
                card_number: cardData.cardNumber,
                card_exp_date: cardData.expire,
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
            payment.transaction_amount = this.discountedCost.toFixed(2);

            this.formData.payment = payment;

            this.$http
                .post("course/pay_by_employee_submission", this.formData)
                .then((resp) => {
                    Swal.fire({
                        title: "Success!",
                        html: `Amount Paid Sucessfully.`,
                        icon: "success",
                        confirmButton: "btn btn-success",
                        confirmButtonText: "Ok",
                    }).then((result) => {
                        this.employeeCourses();
                    });
                })
                .catch(function (error) {
                    if (error.response.status === 422) {
                        return Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error",
                        });
                    }
                })
                .finally(() => (this.payCourseModel = false));
        },
        PayCourse(row) {
            this.discountedCost = row.discountedCost;
            this.orignalCost = row.course_cost;
            this.formData = {
                employee_id: this.user_id,
                company_id: this.company_id,
                course_id: row.id,
                orignal_amount: row.course_cost,
                pay_by_employee_dicount: this.is_payByEmployeeDiscount,
                actual_amount: this.discountedCost,
            };
            this.payCourseModel = true;
        },
        selectionChange(selectedRows) {
            this.selectedRows = selectedRows;
        },
        currentDate(duedate) {
            var currentDateWithFormat = new Date().toJSON().slice(0, 10).replace(/-/g, "-");
            if (currentDateWithFormat > duedate) {
                return true;
            } else {
                return false;
            }
        },
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        sendReminderAllEmployees(val) {
            if (val === "non_compliant") {
                this.$http
                    .post("employees/non_compliant_reminder_email_list", {
                        type: "non_compliant",
                        company_id: this.company_id,
                    })
                    .then((resp) => {
                        Swal.fire({
                            title: "Success!",
                            text: "Email has been sent to entire list!",
                            icon: "success",
                            confirmButtonClass: "btn btn-success btn-fill",
                            buttonsStyling: false,
                        });
                    });
            } else {
                this.$http
                    .post("employees/non_compliant_reminder_email_list", {
                        type: "due",
                        company_id: this.company_id,
                    })
                    .then((resp) => {
                        Swal.fire({
                            title: "Success!",
                            text: "Email has been sent to entire list!",
                            icon: "success",
                            confirmButtonClass: "btn btn-success btn-fill",
                            buttonsStyling: false,
                        });
                    });
            }
        },
        unAssignedCourse(index, row) {
            let self = this;
            Swal.fire({
                title: "Are you sure?",
                text: "You want to delete this course",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false,
            }).then((result) => {
                if (result.value) {
                    self.$http
                        .post("employees/unassign_course", {
                            course_id: row.id,
                            employee_id: self.course_employee_id,
                        })
                        .then((resp) => {
                            self.employeeDataModal = false;
                            //  self.refreshDue();
                            Swal.fire({
                                title: "Success!",
                                text: "Course has been Unassigned to this Employee!",
                                icon: "success",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: true,
                            }).then((result) => {
                                if (result.value) {
                                    self.refreshDue();
                                    self.refreshNon();
                                }
                            });
                        });
                }
            });
        },
        sendReminder(index, row) {
            let self = this;
            this.employeeEmailData.first_name = row.first_name;
            this.employeeEmailData.last_name = row.last_name;
            this.employeeEmailData.location = row.location;
            this.employeeEmailData.id = row.id;
            // let done = false;
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                text: "You want to send Reminder Email to " + self.employeeEmailData.first_name + " " + self.employeeEmailData.last_name + "?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false,
            })
                .then((result) => {
                    if (result.value) {
                        let ids = [];
                        let obj = {
                            id: self.employeeEmailData.id,
                        };
                        ids.push(obj);
                        self.$http
                            .post("employees/course_expire_reminder_email", {
                                company_id: self.company_id,
                                ids: ids,
                            })
                            .then((resp) => {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Email has been sent!",
                                    icon: "success",
                                    confirmButtonClass: "btn btn-success btn-fill",
                                    buttonsStyling: false,
                                });
                            });
                    }
                })
                .catch(function () {
                });
        },
        sendExpiringReminder(index, row) {
            let self = this;
            this.employeeEmailData.first_name = row.first_name;
            this.employeeEmailData.last_name = row.last_name;
            this.employeeEmailData.location = row.location;
            this.employeeEmailData.id = row.id;
            // let done = false;
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                text: "You want to send Reminder Email to " + self.employeeEmailData.first_name + " " + self.employeeEmailData.last_name + "?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false,
            })
                .then((result) => {
                    if (result.value) {
                        let ids = [];
                        let obj = {
                            id: self.employeeEmailData.id,
                        };
                        ids.push(obj);
                        self.$http.post("employees/course_expiring_reminder_email", {
                            company_id: self.company_id,
                            ids: ids,
                        }).then((resp) => {
                            Swal.fire({
                                title: "Success!",
                                text: "Email has been sent!",
                                icon: "success",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: false,
                            });
                        }).catch((error) => {
                            console.log(error.response.data.Email);
                            Swal.fire({
                                title: "Error!",
                                text: error.response.data.Email,
                                icon: "error",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: false,
                            });
                        });
                    }
                })
        },
        handleEdit(index, row) {
            this.$router.push("/add_location?id=" + row.id);
        },
        handleDelete(index, row) {
            let indexToDelete = this.tableData.findIndex((tableRow) => tableRow.id === row.id);
            if (indexToDelete >= 0) {
                this.tableData.splice(indexToDelete, 1);
            }
        },
        openCourseDetailsDue(row) {
            this.course_employee_id = row.id;
            this.employeeCoursesData.first_name = row.first_name;
            this.employeeCoursesData.last_name = row.last_name;
            this.employeeCoursesData.location = row.location;
            this.employeeCoursesData.courseData = row.courses;
            this.employeeDataModal = true;
        },
        refreshNon() {
            this.$http
                .post("employees/non_compliance", {
                    search: this.searchQuery,
                    company_id: this.adminCompanyId,
                })
                .then((resp) => {
                    this.tableData = [];
                    let employee_data = resp.data.employee;
                    for (let data of employee_data) {
                        let obj = {
                            id: data.id,
                            first_name: data.first_name,
                            last_name: data.last_name,
                            company: data.company_name,
                            pof: data.course_pass_count + " | " + data.course_open_count + " | " + (data.course_fail_count + data.course_expired_count),
                            pass: data.course_pass_count,
                            fail: data.course_fail_count + data.course_expired_count,
                            open: data.course_open_count,
                            assigned_lessons: parseInt(data.course_pass_count) + parseInt(data.course_open_count) + parseInt(data.course_fail_count + data.course_expired_count),
                            lesson_passed: data.course_pass_count,
                            lesson_outstanding: data.course_fail_count + data.course_expired_count,
                            courses: data.courses,
                        };
                        this.tableData.push(obj);
                    }
                });
        },

        refreshDue() {
            this.$http
                .post("employees/course_due", {
                    search: this.searchQuery,
                    company_id: this.adminCompanyId,
                })
                .then((resp) => {
                    let employee_data = resp.data.employee;
                    this.tbl_data = [];
                    for (let data of employee_data) {
                        let obj = {
                            id: data.id,
                            first_name: data.first_name,
                            last_name: data.last_name,
                            company: data.company_name,
                            pof: data.course_pass_count + " | " + data.course_open_count + " | " + (data.course_fail_count + data.course_expired_count),
                            pass: data.course_pass_count,
                            fail: data.course_fail_count + data.course_expired_count,
                            open: data.course_open_count,
                            assigned_lessons: parseInt(data.course_pass_count) + parseInt(data.course_open_count) + parseInt(data.course_fail_count + data.course_expired_count),
                            lesson_passed: data.course_pass_count,
                            lesson_outstanding: data.course_fail_count,
                            courses: data.courses_due,
                        };
                        this.tbl_data.push(obj);
                    }
                });
        },
        getCompanyStats: function () {
            this.$http.get("company/stats/" + this.adminCompanyId).then((resp) => {
                console.log(resp.data);
                this.active_courses = resp.data.courses.toString();
                if (resp.data.location != "0") {
                    this.active_location = resp.data.location.toString();
                }
                this.active_employees = resp.data.employee.toString();
            });
        },
        getCompanyCourses: function () {
            this.$http.get("company/courses/" + this.adminCompanyId).then((resp) => {
                this.courses = resp.data[0].courses;
            });
        },
        getComplianceCount: function () {
            this.$http.get("analytics/compliance_count").then((response) => {
                this.compliance = response.data.length;
            }).catch((error) => {
                console.log("API failed for loading Compliance data");
            });
        }
    },
    watch: {
        adminCompanyId: function () {
            this.refreshDue();
            this.refreshNon();
            this.getCompanyStats();
            this.getCompanyCourses();
        }
    }
};
</script>
<style>
.company-location-dropdown .el-select .el-input .el-input__inner {
    height: 30px !important;
}

.company-location-dropdown .el-select .el-input span {
    display: flex;
    align-items: center;
}

.company-location-dropdown .el-select .el-input span:hover {
    text-decoration: none;
}

.customInput_box .el-input.el-input--suffix input.el-input__inner {
    height: 30px;
}
</style>
<style scoped>
@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px) {
    .nonGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "First Name";
    }

    .nonGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Last Name";
    }

    .nonGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Company";
    }

    .nonGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "P/O/F";
    }

    .nonGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Action";
    }

    .dueGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "First Name";
    }

    .dueGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Last Name";
    }

    .dueGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Company";
    }

    .dueGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "P/O/F";
    }

    .dueGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Action";
    }

    .courseGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Course Name" !important;
    }

    .courseGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Due Date";
    }

    .courseGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Lesson status";
    }

    .courseGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Estimated Time (in min)";
    }

    .courseGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Action";
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Lesson Name" !important;
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Lesson Status";
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Completed Date";
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Assigned Date";
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Due Date";
    }

    .assignedGrid >>> table.el-table__body td:nth-of-type(6):before {
        content: "Action";
    }
}

.fade-leave-active {
    transition: all 0.9s ease;
    overflow: hidden;
    visibility: visible;
    position: absolute;
    width: 100%;
    opacity: 1;
}

.fade-enter,
.fade-leave-to {
    visibility: hidden;
    width: 100%;
    opacity: 0;
}

iframe {
    height: 500px;
    width: 100%;
}

#app iframe {
    width: 100%;
    height: 500px;
}

.courses .card-header {
    padding-bottom: 0px !important;
}

.click-text {
    padding-right: 5px;
    color: #00ccff;
}

.click-text:hover {
    text-decoration: underline;
    color: #0b427b;
}

.dashbaordbox a {
    /* box-shadow: 0px 0px 11px #05b8e4; */
    display: block;
}

.dashbaordbox .icon {
    width: 4rem;
    height: 4rem;
}

.dashbarod-card-title {
    font-size: 15px;
    padding: 5px 0;
}

.dashbarod-table-box .el-table .el-table__header-wrapper thead th .cell {
    min-height: 22px;
    font-weight: 700;
}

.dashbarod-table-box .el-table__body-wrapper table tbody td {
    padding: 3px;
}

.select_new {
    position: absolute;
    bottom: 24px;
    max-width: 200px;
    left: 50px;
}


.el-input.el-input--suffix INPUT {
    HEIGHT: 30PX !IMPORTANT;
}

@media only screen and (max-width: 992px) {
    .select_new {
        position: absolute;
        bottom: 34px;
        max-width: 50%;
        left: 50px;
    }
}

@media only screen and (max-width: 767px) {
    .select_new {
        position: absolute;
        bottom: 34px;
        max-width: 100%;
        left: 50px;
    }
}

.location_FlexBox {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    z-index: 999;
    position: relative;
}

.company-location-dropdown {
    position: static;
    max-width: 100%;
    padding-right: 10px;
}

.dash-card-box {
    margin-bottom: 20px;
}

</style>
