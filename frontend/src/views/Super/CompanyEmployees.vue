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
                            <div class="col-lg-6 col-5 text-left">
                                <h2 class="mb-0">Employees</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                <base-button
                                    name="Add User Screen"
                                    class="custom-btn"
                                    v-if="editor === 'company' || 'manager'"
                                    @click.prevent="createuser"
                                ><i class="fa fa-plus" aria-hidden="true"></i> Add
                                    User</base-button
                                >
                            </div>
                        </div>
                    </template>
                    <div class="">
                        <div
                            class="row justify-content-center justify-content-sm-between flex-wrap"
                        >
                            <div class="col-md-5">
                                <base-input
                                    v-debounce:2s.unlock="fetchData"
                                    name="Employee Search"
                                    label="Search:"
                                    v-model="searchQuery"
                                    prepend-icon="fas fa-search"
                                    placeholder="Search..."
                                >
                                </base-input>
                            </div>

                            <div class="col-md-4">
                                <label>Location:</label>
                                <el-select
                                    name="Employee Location Filter"
                                    class="select-primary w-100"
                                    v-model="filters.location_id"
                                    v-on:change="fetchData()"
                                    placeholder="Filter by Location"
                                >
                                    <el-option
                                        class="select-default"
                                        v-for="item in locations"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-md-3">
                                <label>Status:</label>
                                <el-select
                                    name="Employee Status"
                                    class="select-primary w-100"
                                    v-model="filters.employeStatus"
                                    v-on:change="changePage(1)"
                                    placeholder="Filter by Employee status"
                                >
                                    <el-option
                                        class="select-primary"
                                        v-for="item in status"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div
                            class="row justify-content-center justify-content-sm-between flex-wrap mb-4"
                        >
                            <div class="col-md-3">
                                <label>Bulk Action:</label>
                                <el-select
                                    name="Employee Bulk Action"
                                    class="select-primary w-100"
                                    v-on:change="bulkClicked()"
                                    v-model="bulkValue"
                                    placeholder="Bulk Action"
                                >
                                    <el-option
                                        class="select-primary"
                                        v-for="item in bulk_array"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    >
                                    </el-option>
                                </el-select>
                            </div>

                            <div class="col-md-2">
                                <label>Showing:</label>
                                <el-select
                                    class="select-primary pagination-select w-100"
                                    v-model="perPage"
                                    v-on:change="changePage(1)"
                                    placeholder="Per page"
                                >
                                    <el-option
                                        class="select-primary"
                                        v-for="item in perPageOptions"
                                        :key="item"
                                        :label="item"
                                        :value="item"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <el-popover placement="bottom-start" trigger="click">
                                    <el-checkbox-group
                                        v-model="filterValue"
                                        @change="columnFilter"
                                    >
                                        <el-checkbox
                                            v-for="(item, index) in filtersOptions"
                                            :label="item.value"
                                            v-bind:key="index"
                                        >{{ item.text }}</el-checkbox
                                        >
                                    </el-checkbox-group>
                                    <span slot="reference" class="arrangeDownArrow"
                                    ><h5>Filter User Type <i class="el-icon-arrow-down"></i
                                    ></h5></span>
                                </el-popover>
                            </div>
                        </div>

                        <div class="user-eltable">
                            <el-table
                                :data="tableData"
                                row-key="id"
                                header-row-class-name="thead-light custom-thead-light"
                                role="table"
                                class="usersGrid"
                                @selection-change="selectionChange"
                            >
                                <el-table-column
                                    v-for="column in tableColumns"
                                    :key="column.label"
                                    v-bind="column"
                                >
                                </el-table-column>
                                <el-table-column min-width="140px" prop="first_name">
                                    <template slot="header">
                    <span @click="sortByColumn(0)"
                    >First Name
                      <i
                          v-if="sortedColumn == 0 && order === 'asc'"
                          class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                            v-else
                            class="fas fa-arrow-down
                    text-blue linkColor"
                        />
                    </span>
                                    </template>
                                    <template slot-scope="props">
                                        <span>{{ props.row.first_name }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column min-width="140px" prop="last_name">
                                    <template slot="header">
                    <span @click="sortByColumn(1)"
                    >Last Name
                      <i
                          v-if="sortedColumn == 1 && order === 'asc'"
                          class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                            v-else
                            class="fas fa-arrow-down
                    text-blue linkColor"
                        />
                    </span>
                                    </template>
                                    <template slot-scope="props">
                                        <span>{{ props.row.last_name }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    min-width="170px"
                                    align="left"
                                    label=""
                                    prop="location"
                                >
                                    <template slot="header">
                    <span @click="sortByColumn(2)"
                    >Location
                      <i
                          v-if="sortedColumn == 2 && order === 'asc'"
                          class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                            v-else
                            class="fas fa-arrow-down
                    text-blue linkColor"
                        />
                    </span>
                                    </template>
                                    <template slot-scope="props">
                    <span v-if="props.row.company != ''">{{
                            props.row.company
                        }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="120px" align="left" prop="type">
                                    <template slot="header">
                    <span @click="sortByColumn(3)"
                    >User Type
                      <i
                          v-if="sortedColumn == 3 && order === 'asc'"
                          class="fas fa-arrow-up
                    text-blue linkColor"
                      /><i
                            v-else
                            class="fas fa-arrow-down
                    text-blue linkColor"
                        />
                    </span>
                                    </template>
                                    <template slot-scope="props">
                                        <span v-if="props.row.type == 'employee'">Employee</span>
                                        <span v-if="props.row.type == 'location_manager'"
                                        >Manager</span
                                        >
                                        <span v-if="props.row.type == 'admin'">Admin</span>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="120px" prop="">
                                    <template slot="header">
                                        <el-popover
                                            ref="fromPopOver"
                                            placement="top-start"
                                            min-width="250"
                                            trigger="hover"
                                        >
                      <span>
                        P = Passed. This user is compliant.<br /><br />O = Open.
                        This user has open courses,but is also compliant.<br /><br />F
                        = Failed. This user is non-compliant because they did
                        not pass the assigned course in the time permitted.
                      </span>
                                        </el-popover>
                                        <span @click="sortByColumn(4)"
                                        >P/O/F
                      <i
                          v-popover:fromPopOver
                          class="el-icon-question text-blue"
                      />&nbsp;
                      <i
                          v-if="sortedColumn == 4 && order === 'asc'"
                          class="fas fa-arrow-up
                    text-blue"
                      /><i
                                                v-else
                                                class="fas fa-arrow-down
                    text-blue"
                                            />
                    </span>
                                    </template>
                                    <template slot-scope="props">
                    <span v-on:click="openCourseDetails(props.row)">
                      <base-button
                          type=" pof-value pofcolorfail"
                          class="POF_btn"
                          v-if="props.row.fail != '0'"
                      >{{ props.row.passOpenFail }}</base-button
                      >
                      <base-button
                          type=" pof-value pofcoloropen"
                          class="POF_btn"
                          v-else-if="props.row.open != '0'"
                      >{{ props.row.passOpenFail }}</base-button
                      >
                      <base-button
                          type=" pof-value pofcolorpass"
                          class="POF_btn"
                          v-else-if="props.row.pass != '0'"
                      >{{ props.row.passOpenFail }}</base-button
                      >
                      <base-button type="default" class="POF_btn" v-else>{{
                              props.row.passOpenFail
                          }}</base-button>
                    </span>
                                    </template>
                                </el-table-column>

                                <el-table-column
                                    min-width="120px"
                                    label="Status"
                                    prop="status"
                                    align="left"
                                >
                                    <template slot-scope="props">
                                        <div
                                            class="d-flex"
                                            v-on:click="changeStatus(props.$index, props.row)"
                                        >
                                            <base-switch
                                                class="mr-1"
                                                v-if="props.row.status"
                                                type="success"
                                                v-model="props.row.status"
                                            ></base-switch>
                                            <base-switch
                                                class="mr-1"
                                                v-else
                                                type="danger"
                                                v-model="props.row.status"
                                            ></base-switch>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    min-width="110px"
                                    class-name="td-actions table-custom-size "
                                    label="Actions"
                                >
                                    <template slot-scope="props" class="d-flex custom-size">
                                        <el-tooltip content="Edit" placement="top">
                                            <base-button
                                                name="Edit Employee"
                                                size="sm"
                                                type=""
                                                icon
                                                @click="handleEdit(props.$index, props.row)"
                                                data-toggle="tooltip"
                                                data-original-title="Edit"
                                            >
                                                <i class="text-default fa fa-pencil-square-o"></i>
                                            </base-button>
                                        </el-tooltip>

                                        <el-tooltip
                                            content="Send Email"
                                            placement="top"
                                            v-if="props.row.email"
                                        >
                                            <base-button
                                                name="Send Email"
                                                type=""
                                                size="sm"
                                                icon
                                                @click="handleEnvelope(props.$index, props.row)"
                                                data-toggle="tooltip"
                                                data-original-title="Send Email"
                                            >
                                                <i class="text-warning ni ni-email-83"></i>
                                            </base-button>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                    <div slot="footer" class="d-flex justify-content-end ">
                        <nav v-if="pagination && tableData.length > 0">
                            <div class="row">
                                <div class="col-md-12">
                                    <ul
                                        class="pagination custompagination  justify-content-end align-items-center"
                                    >
                                        <p class="p-0 m-0 mr-2">
                                            Showing {{ tableData.length }} of {{ totalData }} entries
                                        </p>
                                        <li
                                            class="page-item"
                                            :class="{ disabled: currentPage === 1 }"
                                        >
                                            <a
                                                class="page-link"
                                                href="#"
                                                @click.prevent="changePage(currentPage - 1)"
                                            ><i class="fa fa-caret-left "></i>
                                            </a>
                                        </li>
                                        <li
                                            v-for="(page, index) in pagesNumber"
                                            class="page-item"
                                            :class="{ active: page == currentPage }"
                                            v-bind:key="index"
                                        >
                                            <a
                                                href="javascript:void(0)"
                                                @click.prevent="changePage(page)"
                                                class="page-link"
                                            >{{ page }}</a
                                            >
                                        </li>
                                        <li
                                            class="page-item"
                                            :class="{
                        disabled: currentPage === last_page
                      }"
                                        >
                                            <a
                                                class="page-link"
                                                href="#"
                                                @click.prevent="changePage(currentPage + 1)"
                                            ><i class="fa fa-caret-right "></i
                                            ></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                    </div>
                </card>
            </div>
        </div>
        <modal :show.sync="employeeDataModal" class="user-modal">
      <span slot="header" class="title title-up text-primary assign-coures" style=" width: 100%">

        {{ employeeCoursesData.first_name }}
        {{ employeeCoursesData.last_name }}'s Assigned Courses

        {{ employeeCoursesData.type=='admin' ? "(Company Administrator)"
          : employeeCoursesData.type=='employee' ? '(Employee)'
              : employeeCoursesData.type=='location_manager' ? "(Manager)"
                  : "" }}
     <span style="float:right;">
         <base-button
             class="custom-btn ml-5"
             type="default"
             @click.prevent="
            downloadBulkCertificate(course_employee_id, course_company_id)
          "
         >
          Download Certifcate
        </base-button>
        <base-button
            class="custom-btn"
            type="default"
            @click.prevent="
            showAssigncoursefolders(course_employee_id, course_company_id)
          "
        >
          Assign Course Folder
        </base-button>

        <base-button
            name="Assign Course Screen"
            class="custom-btn"
            type="default"
            @click.prevent="
            showAssigncourse(course_employee_id, course_company_id)
          "
        >
          Assign course
        </base-button>
     </span>
      </span>

            <form>
                <div class="user-eltable">
                    <el-table
                        :data="employeeCoursesData.courseData"
                        style="width: 100%"
                        role="table"
                        class="pofGrid formatTable"
                        header-row-class-name="thead-light"
                    >
                        <el-table-column min-width="100px" align="left" class="check">
                            <template slot="header">
                                <el-popover
                                    ref="fromPopOver"
                                    placement="top-start"
                                    width="250"
                                    trigger="hover"
                                >
                  <span style="display: flex; justify-content: center;">
                    Select the check box next to each course to download the
                    certificates.
                  </span>
                                </el-popover>
                                <span>
                  <i
                      v-popover:fromPopOver
                      class="el-icon-question
                    text-blue"
                  />
                </span>
                            </template>
                            <template slot-scope="props">
                <span>
                  <div class="" v-if="props.row.employee_course_status === 1">
                    <base-checkbox
                        class=""
                        v-model="props.row.course_checked"
                        v-on:input="courseCheckchange(props.row)"
                    ></base-checkbox>
                  </div>
                </span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Lesson name"
                            property=""
                            min-width="200px"
                            align="left"
                        >
                            <template slot-scope="props">
                                <span>{{ props.row.name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Lesson Status"
                            property=""
                            align="left"
                            min-width="150px"
                        >
                            <template slot-scope="props">
                                <span v-if="props.row.employee_course_status === 1">Pass</span>
                                <span v-if="props.row.employee_course_status === 2">Open</span>
                                <span v-if="props.row.employee_course_status === 0">Fail</span>
                                <span v-if="props.row.employee_course_status === 3"
                                >Expired</span
                                >
                            </template> </el-table-column
                        ><!--employee_course_due_date-->
                        <el-table-column
                            label="Date Completed"
                            property=""
                            min-width="100px"
                            align="left"
                        >
                            <template slot-scope="props">
                <span v-if="props.row.employee_course_date_completed === null"
                >Not Available</span
                >
                                <span v-else>{{
                                        formattedDate(props.row.employee_course_date_completed)
                                    }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Date Assigned"
                            property=""
                            align="left"
                            min-width="160px"
                        >
                            <template slot-scope="props">
                <span>{{
                        formattedDate(props.row.employee_course_date_assigned)
                    }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Due Date"
                            property=""
                            align="left"
                            min-width="120px"
                        >
                            <template slot-scope="props">
                <span>{{
                        formattedDate(props.row.employee_course_due_date)
                    }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Expiration Date"
                            property=""
                            align="left"
                            min-width="150px"
                        >
                            <template slot-scope="props">
                <span v-if="(props.row.employee_course_status === 1 && props.row.cerificate_expiration_date)">
                  {{
                        formattedDate(props.row.cerificate_expiration_date)
                    }}</span>
                                <span v-if="(props.row.employee_course_status === 1 && !props.row.cerificate_expiration_date)">
                  {{
                                        'N/A'
                                    }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Action"
                            property=""
                            align="left"
                            min-width="160px"
                            class-name="td-actions table-custom-size "
                        >
                            <template slot-scope="props" class="d-flex custom-size">

                                <el-tooltip
                                    content="Preview"
                                    placement="top"
                                    v-if="
                    props.row.employee_course_status === 1 &&
                      props.row.is_food_certifcate === 1
                  "
                                >
                                    <a
                                        class="linkColor"
                                        @click.prevent="
                      getProctoredExamCertificate(
                        props.row.food_safe_certificate_url
                      )
                    "
                                        data-original-title="Preview"
                                        data-toggle="tooltip"
                                    >
                    <span>
                      <i name="Preview Proctored Certificate" class="text-success fa fa-eye"></i>
                    </span>
                                    </a>
                                </el-tooltip>
                                <el-tooltip
                                    content="Preview"
                                    placement="top"
                                    v-else-if="
                   !props.row.is_coursefolder &&
                    props.row.employee_course_status === 1 &&
                      props.row.is_food_certifcate === 0
                  "
                                >
                                    <a
                                        :href="
                      baseUrl +
                        '/downloadCourseCertificate/preview/' +
                        props.row.id +
                        '/' +
                        course_employee_id +
                        '/' +
                        props.row.employee_certificate_id +
                        '/' +
                        props.row.is_coursefolder
                    "
                                        data-toggle="tooltip"
                                        data-original-title="Preview"
                                        target="_blank"
                                    >
                    <span> <i name="Preview Certificate" class="ml-1 text-success fas fa-eye"></i> </span
                    ></a>
                                </el-tooltip>


                                <el-tooltip
                                    content="Download"
                                    placement="top"
                                    v-if="
                   !props.row.is_coursefolder &&
                    props.row.employee_course_status === 1 &&
                      props.row.is_food_certifcate === 0
                  "
                                >
                                    <a
                                        :href="
                      baseUrl +
                        '/downloadCourseCertificate/download/' +
                        props.row.id +
                        '/' +
                        course_employee_id +
                        '/' +
                        props.row.employee_certificate_id +
                        '/' +
                        props.row.is_coursefolder
                    "
                                        data-toggle="tooltip"
                                        data-original-title="Download"
                                        download="true"
                                    >
                    <span> <i name="Download Certificate" class="ml-3 text-warning fa fa-download"></i> </span
                    ></a>
                                </el-tooltip>


                                <el-tooltip
                                    content="Delete"
                                    placement="top"
                                    v-if="props.row.employee_course_status != 1"
                                >
                                    <base-button
                                        type=""
                                        size="sm"
                                        @click="unAssignedCourse($index, props.row)"
                                        data-toggle="tooltip"
                                        data-original-title="Delete"
                                    >
                                        <i class="text-danger fas fa-trash"></i>
                                    </base-button>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="text-center my-4"></div>

                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="employeeEmailModal">
            <h3 slot="header" class="title title-up text-primary">
                Send Reminder Email to ({{ employeeEmailData.first_name }}
                {{ employeeEmailData.last_name }})
            </h3>
            <form>
                <div class="row">
                    <div class="col-md-4 text-center my-4">
                        <base-button
                            name="Coures Due Reminder Email"
                            type="submit"
                            class="btn btn-success btn-wd"
                            @click.prevent="sendReminderEmail"
                        >
                            Coures Due Reminder Email
                        </base-button>
                    </div>
                    <div class="col-md-4 text-center my-4">
                        <base-button
                            name="Resend Welcome Email"
                            type="submit"
                            class="btn btn-primary btn-wd"
                            @click.prevent="sendWelcomeEmail"
                        >
                            Resend Welcome Email
                        </base-button>
                    </div>
                    <div class="col-md-4 text-center my-4">
                        <base-button
                            name="Reset Password Email"
                            type="submit"
                            class="btn btn-warning btn-wd"
                            @click.prevent="sendPasswordResetEmail"
                        >
                            Reset Password Email
                        </base-button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="reassignLocationModel">
            <h6 slot="header" class="title title-up text-primary">Select Location</h6>
            <form>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <el-select
                            v-model="bulk_location_id"
                            placeholder="Filter by Location"
                        >
                            <el-option
                                class="select-default"
                                v-for="item in locations"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            >
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="text-center my-4">
                    <base-button type="primary" name="Assign Location" @click.prevent="assignBulkLocation">
                        Assign Location
                    </base-button>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>

        <modal :show.sync="courseAssigneeModal">
            <h3 slot="header" style="color: #444c57" class="title title-up">
                Assign Course
            </h3>
            <form>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <base-button name="Assign Course" @click.prevent="assignCourse">
                            {{ assigning ? "Processing" : "Assign Course" }}
                        </base-button>
                    </div>
                    <div class="col-md-12">
                        <base-input label="Select any Course to Assign:">
                            <el-select
                                multiple
                                v-model="assigned_course_id"
                                class="course_dropdown"
                                name="course"
                            >
                                <el-option
                                    v-for="(course, index) in courses"
                                    name="course"
                                    :key="index"
                                    :label="course.name"
                                    :value="course.id"
                                >
                                </el-option>
                            </el-select>
                        </base-input>
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="courseFolderAssigneeModal">
            <h3 slot="header" style="color: #444c57" class="title title-up">
                Assign Course Folder
            </h3>
            <form>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <base-button @click.prevent="assignCourseFolder">
                            {{ assigning ? "Processing" : "Assign Course Folder" }}
                        </base-button>
                    </div>
                    <div class="col-md-12">
                        <base-input label="Select Course Folder to Assign:">
                            <el-select
                                multiple
                                style="width:100%"
                                v-model="assigned_coursefolder_id"
                                class="course_dropdown"
                                name="course"
                            >
                                <el-option
                                    v-for="(course, index) in coursefolders"
                                    name="course"
                                    :key="index"
                                    :label="course.folder_name"
                                    :value="course.id"
                                >
                                </el-option>
                            </el-select>
                        </base-input>
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="download_certificate">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <h6 class="title title-up">
                        Download Certificates of {{ employeeCoursesData.first_name }}
                        {{ employeeCoursesData.last_name }}
                    </h6>
                    <a download :href="download_file_link" class="btn btn-default"
                    >Download</a
                    >
                </div>
                <div class="col-md-4"></div>
            </div>
        </modal>

        <modal :show.sync="courseUnassigneeModal">
            <h3 slot="header">Unassign Course</h3>
            <form>
                <div class="row">
                    <div class=" col-md-12 text-right">
                        <base-button  name="Unassign Course" @click.prevent="unassignCourse">
                            {{ unassigning ? "Processing" : "Unassign Course" }}
                        </base-button>
                    </div>

                    <div class="col-md-12">
                        <base-input label="Select Course to Unassign">
                            <el-select
                                multiple
                                v-model="unassigned_course_id"
                                class="course_dropdown"
                                name="course"
                            >
                                <el-option
                                    v-for="(course, index) in courses"
                                    name="course"
                                    :key="index"
                                    :label="course.name"
                                    :value="course.id"
                                >
                                </el-option>
                            </el-select>
                        </base-input>
                    </div>
                </div>

                <div class="clearfix"></div>
            </form>
        </modal>
        <modal :show.sync="courseFolderUnassigneeModal">
            <h4 slot="header">Unassign Course Folder</h4>
            <form>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-control-label"
                        >Select Course Folder to Unassign</label
                        >
                    </div>
                    <div class="col-md-12">
                        <el-select
                            multiple
                            style="width:100%"
                            v-model="unassigned_coursefolder_id"
                            class="course_dropdown"
                            name="course"
                        >
                            <el-option
                                v-for="(course, index) in coursefolders"
                                name="course"
                                :key="index"
                                :label="course.folder_name"
                                :value="course.id"
                            >
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="text-center my-4">
                    <base-button
                        type="danger"
                        @click.prevent="courseFolderUnassigneeModal = false"
                    >
                        Cancel
                    </base-button>
                    <base-button type="success" @click.prevent="unassignCourseFolder">
                        {{ unassigning ? "Processing" : "Unassign Course Folder" }}
                    </base-button>
                </div>

                <div class="clearfix"></div>
            </form>
        </modal>
    </div>
</template>
<script>
import {
    DatePicker,
    Table,
    TableColumn,
    Select,
    Option,
    Checkbox,
    CheckboxGroup,
    Button
} from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import moment from "moment";
import Vue from "vue";
import vueDebounce from "vue-debounce";
let timeout = null;
Vue.use(vueDebounce, {
    lock: true
});
export default {
    mixins: [serverSidePaginationMixin],
    components: {
        [Checkbox.name]: Checkbox,
        [CheckboxGroup.name]: CheckboxGroup,
        [Button.name]: Button,
        [Select.name]: Select,
        [Option.name]: Option,
        [Table.name]: Table,
        [TableColumn.name]: TableColumn,
        [DatePicker.name]: DatePicker
    },
    data() {
        return {
            baseUrl: this.$baseUrl,
            loading: false,
            check_all: false,
            check_all_course: false,
            isLoading: false,
            fullPage: true,
            course_employee_id: "",
            courses: [],
            bulk_location_id: "",
            course_due_date: "",
            reassignLocationModel: false,
            company_id: "",
            hot_user: "",
            hot_token: "",
            config: "",
            company_name: "",
            employeeEmailModal: false,
            courseAssigneeModal: false,
            courseUnassigneeModal: false,
            courseFolderUnassigneeModal:false,
            courseFolderAssigneeModal:false,
            checked_employee: [],
            checked_course: [],
            bulkValue: "",
            location: {
                locationName: "",
                locationStatus: true
            },
            employeeCoursesData: {
                first_name: "",
                last_name: "",
                location: "",
                type:"",
                courseData: []
            },
            employeeEmailData: {
                first_name: "",
                last_name: "",
                location: "",
                id: ""
            },
            download_certificate: false,
            download_file_link: "",
            bulk_array: [
                {
                    label: "Make User Active",
                    value: "make_employee_active"
                },
                {
                    label: "Make User Inactive",
                    value: "make_employee_in_active"
                },
                {
                    label: "Assign Course Folder",
                    value: "assign_course_folder"
                },
                {
                    label: "Unassign Course Folder",
                    value: "unassign_course_folder"
                },
                {
                    label: "Assign Course",
                    value: "assign_course"
                },
                {
                    label: "Unassign Course",
                    value: "unassign_course"
                },
                {
                    label: "Reassign Location",
                    value: "reassign_location"
                }
            ],
            status: [
                {
                    label: "Active",
                    value: "Active"
                },
                {
                    label: "Inactive",
                    value: "Inactive"
                },
                {
                    label: "Show All",
                    value: ""
                }
            ],
            filtersOptions: [
                { text: "All Admins", value: "2" },
                { text: "All Managers", value: "3" },
                { text: "All Employees", value: "4" }
            ],
            tableColumns: [
                {
                    type: "selection"
                }
            ],
            selectedRows: [],
            filters: {
                employeStatus: "Active",
                location_id: ""
            },

            checked_all: false,
            checked_all_courses: false,
            searchQuery: "",
            tbl_data: [],
            tableData: [],
            locations: [
                {
                    label: "All Locations",
                    value: ""
                }
            ],
            employeeDataModal: false,
            checked_courses: [],
            coursefolders: [],
            assigning: false,
            unassigning: false,
            assigned_course_id: "",
            unassigned_course_id: "",
            assigned_coursefolder_id: "",
            unassigned_coursefolder_id: "",
            editor: "",
            awaitingSearch: false,
            manager_id: "",
            admin_id: "",
            course_company_id: "",
            filterValue: [],
            show_loader: false
        };
    },
    created: function() {
        if (localStorage.getItem("hot-token")) {
            this.hot_user = localStorage.getItem("hot-user");
            this.hot_token = localStorage.getItem("hot-token");
            this.company_id = localStorage.getItem("hot-user-id");
            this.company_name = localStorage.getItem("hot-company-name");
            this.admin_id = localStorage.getItem("hot-admin-id");
        }
        if (localStorage.getItem("hot-user") === "employee") {
            this.editor = "employee";
        } else if (localStorage.getItem("hot-user") === "super-admin") {
            this.editor = "super-admin";
        }else if (localStorage.getItem("hot-user") === "sub-admin") {
            this.editor = "sub-admin";
        } else if (localStorage.getItem("hot-user") === "company-admin") {
            this.editor = "company";
        } else if (localStorage.getItem("hot-user") === "manager") {
            this.editor = "manager";
            this.manager_id = localStorage.getItem("hot-user-id");
        }
        if (this.$route.query.id) {
            this.filters.location_id = parseInt(this.$route.query.id);
        }
        this.fetchData();
    },
    // watch: {
    //   searchQuery: function() {
    //     if (this.searchQuery.length > 3 || this.searchQuery.length == 0) {
    //       clearTimeout(timeout);
    //       timeout = setTimeout(() => {
    //         this.fetchData();
    //       }, 300);
    //     }
    //   }
    // },
    methods: {
        createuser() {
            this.$router.push("/add_employee");
        },
        formattedDate(data) {
            return moment(data).format("MM-DD-YYYY");
        },
        resetFilters() {
            this.bulkValue = "";
            this.filters = {
                employeStatus: "",
                location_id: ""
            };
            this.searchQuery = "";
            this.filterValue = [];
            this.fetchData();
        },
        assignCourse() {
            this.assigning = true;
            if (this.assigned_course_id !== "") {
                let data = {
                    course_id: this.assigned_course_id,
                    company_id: this.course_company_id,
                    assign_to: [
                        {
                            employee_ids: [],
                            assign_to: "employee"
                        }
                    ]
                };

                if (this.selectedRows.length > 0) {
                    for (let id of this.selectedRows) {
                        let obj = {
                            id: id
                        };
                        data.assign_to[0].employee_ids.push(obj);
                    }
                } else {
                    let obj = {
                        id: this.course_employee_id
                    };
                    data.assign_to[0].employee_ids.push(obj);
                }

                this.$http
                    .post("course/assign", data, this.config)
                    .then(resp => {
                        this.assigning = false;
                        this.courseAssigneeModal = false;
                        this.bulkValue = "";
                        this.assigned_course_id = "";
                        this.selectedRows = [];
                        this.fetchData();

                        this.employeeDataModal = false;

                        if(resp.data.alreadyPassed==0 && resp.data.alreadyInProgress==0){
                            Swal.fire({
                                title: "Success!",
                                text: "Course(s) has been Assigned to Employee(s)",
                                icon: "success"
                            });
                        }else{
                            Swal.fire({
                                title: "Success!",
                                html: '<ul style="text-align: left;"><li>Course(s) Assigned: '+ resp.data.assigned +'</li><li>Course(s) In Progress: ' + resp.data.alreadyInProgress + '</li><li>Course(s) Already Passed: '+resp.data.alreadyPassed +'</li></ul>',
                                icon: "success"
                            });
                        }

                    })
                    .catch(function(error) {
                        Swal.fire({
                            title: "Error!",
                            html: error.response.data.message,
                            icon: "error"
                        });
                    });
            } else {
                this.assigning = false;
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        assignCourseFolder() {
            this.assigning = true;
            if (this.assigned_coursefolder_id !== "") {
                let data = {
                    folder_id: this.assigned_coursefolder_id,
                    company_id: this.course_company_id,
                    assign_to: [
                        {
                            employee_ids: [],
                            assign_to: "employee"
                        }
                    ]
                };

                if (this.selectedRows.length > 0) {
                    for (let id of this.selectedRows) {
                        let obj = {
                            id: id
                        };
                        data.assign_to[0].employee_ids.push(obj);
                    }
                } else {
                    let obj = {
                        id: this.course_employee_id
                    };
                    data.assign_to[0].employee_ids.push(obj);
                }

                this.$http
                    .post("course/assign", data, this.config)
                    .then(resp => {
                        this.assigning = false;
                        this.courseFolderAssigneeModal=false;
                        this.bulkValue = "";
                        this.assigned_coursefolder_id = "";
                        this.selectedRows = [];
                        this.fetchData();

                        this.employeeDataModal = false;
                        Swal.fire({
                            title: "Success!",
                            text: "Course has been Assigned to these Employees",
                            icon: "success"
                        });
                    })
                    .catch(function(error) {
                        Swal.fire({
                            title: "Error!",
                            html: error.response.data.message,
                            icon: "error"
                        });
                    });
            } else {
                this.assigning = false;
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        unassignCourse() {
            this.unassigning = true;
            if (this.unassigned_course_id !== "") {
                let data = {
                    course_id: this.unassigned_course_id,
                    company_id: this.company_id,
                    unassign_to: [
                        {
                            employee_ids: [],
                            unassign_to: "employee"
                        }
                    ]
                };
                for (let id of this.selectedRows) {
                    let obj = {
                        id: id
                    };
                    data.unassign_to[0].employee_ids.push(obj);
                }
                this.$http.post("course/unassign", data, this.config).then(resp => {
                    this.unassigning = false;
                    this.courseunassigneeModal = false;
                    this.bulkValue = "";
                    this.unassigned_course_id = "";
                    this.selectedRows = [];
                    this.fetchData();
                    Swal.fire({
                        title: "Success!",
                        text: "Course has been Unassigned to these Employees",
                        icon: "success"
                    });
                });
            } else {
                this.assigning = false;
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        unassignCourseFolder() {
            this.unassigning = true;
            if (this.unassigned_coursefolder_id !== "") {
                let data = {
                    folder_id: this.unassigned_coursefolder_id,
                    company_id: this.company_id,
                    unassign_to: [
                        {
                            employee_ids: [],
                            unassign_to: "employee"
                        }
                    ]
                };
                for (let id of this.selectedRows) {
                    let obj = {
                        id: id
                    };
                    data.unassign_to[0].employee_ids.push(obj);
                }
                this.$http.post("course/unassign", data, this.config).then(resp => {
                    this.unassigning = false;
                    this.coursefolderunassigneeModal = false;
                    this.bulkValue = "";
                    this.unassigned_coursefolder_id = "";
                    this.courseFolderUnassigneeModal=false;
                    this.selectedRows = [];
                    this.fetchData();
                    Swal.fire({
                        title: "Success!",
                        text: "Course has been Unassigned to these Employees",
                        icon: "success"
                    });
                });
            } else {
                this.assigning = false;
                Swal.fire({
                    title: "Error!",
                    text: "All fields are required!",
                    icon: "error"
                });
            }
        },
        downloadCertificates() {
            if (this.checked_courses.length > 0) {
                window.location =
                    this.$baseUrl +
                    "/downloadAllCourseCertificate/single/" +
                    this.checked_courses.join("_") +
                    "/" +
                    this.course_employee_id;
            } else {
                Swal.fire({
                    title: `Error`,
                    text: `Please select the certificate you want to download.`,
                    icon: "error"
                });
            }
        },
        columnFilter(e) {
            this.fetchData();
        },
        assignBulkLocation() {
            if (this.bulk_location_id !== "") {
                let ids = [];
                for (let id of this.selectedRows) {
                    let obj = {
                        id: id
                    };
                    ids.push(obj);
                }
                this.$http
                    .post(
                        "employees/reassign_location",
                        {
                            location_id: this.bulk_location_id,
                            employee_ids: ids
                        },
                        this.config
                    )
                    .then(resp => {
                        this.reassignLocationModel = false;
                        this.bulkValue = "";
                        this.bulk_location_id = "";
                        this.selectedRows = [];
                        this.fetchData();
                        Swal.fire({
                            title: "Success!",
                            text: "New Location has been Assigned to these Employees!",
                            icon: "success",
                            confirmButtonClass: "btn btn-success btn-fill",
                            buttonsStyling: true
                        });
                    });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Any Location!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkClicked() {
            this.check_all = false;
            if (this.selectedRows.length > 0) {
                if (this.bulkValue === "make_employee_active") {
                    this.bulkChangeStatusToActive();
                } else if (this.bulkValue === "make_employee_in_active") {
                    this.bulkChangeStatusToInactive();
                } else if (this.bulkValue === "assign_course") {
                    this.bulkAssignCourse();
                } else if (this.bulkValue === "unassign_course") {
                    this.bulkUnassignCourse();
                }  else if (this.bulkValue === "assign_course_folder") {
                    this.bulkAssignCourseFolder();
                } else if (this.bulkValue === "unassign_course_folder") {
                    this.bulkUnassignCourseFolder();}
                else if (this.bulkValue === "reassign_location") {
                    this.bulkReassignLocation();
                }
            } else {
                this.bulkValue = "";
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Employees First!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkChangeStatusToActive() {
            this.bulkValue = "";
            let self = this;
            let status = 1;
            for (let id in self.selectedRows) {
                self.$http
                    .put(
                        "/employees/update_status/" + self.selectedRows[id],
                        {
                            status: status
                        },
                        self.config
                    )
                    .then(resp => {
                        if (parseInt(id) === self.selectedRows.length - 1) {
                            self.selectedRows = [];
                            self.fetchData();
                            Swal.fire({
                                title: "Success!",
                                text: "Status has been Changed.",
                                icon: "success",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: false
                            });
                        }
                    });
            }
        },
        bulkChangeStatusToInactive() {
            this.bulkValue = "";
            let self = this;
            let status = 0;
            for (let id in self.selectedRows) {
                self.$http
                    .put(
                        "/employees/update_status/" + self.selectedRows[id],
                        {
                            status: status
                        },
                        self.config
                    )
                    .then(resp => {
                        if (parseInt(id) === self.selectedRows.length - 1) {
                            self.selectedRows = [];
                            self.fetchData();
                            Swal.fire({
                                title: "Success!",
                                text: "Status has been Changed.",
                                icon: "success",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: false
                            });
                        }
                    });
            }
        },
        bulkAssignCourse() {
            if (this.selectedRows.length > 0) {
                this.courseAssigneeModal = true;
            } else {
                this.bulkValue = "";
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Employees First!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkUnassignCourse() {
            if (this.selectedRows.length > 0) {
                this.courseUnassigneeModal = true;
            } else {
                this.bulkValue = "";
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Employees First!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkAssignCourseFolder() {
            if (this.selectedRows.length > 0) {
                this.courseFolderAssigneeModal = true;
            } else {
                this.bulkValue = "";
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Employees First!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkUnassignCourseFolder() {
            if (this.selectedRows.length > 0) {
                this.courseFolderUnassigneeModal = true;
            } else {
                this.bulkValue = "";
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Employees First!",
                    icon: "error",
                    confirmButtonClass: "btn btn-success btn-fill",
                    buttonsStyling: true
                });
            }
        },
        bulkReassignLocation() {
            this.reassignLocationModel = true;
        },
        sendReminderEmail() {
            let self = this;
            // let done = false;
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                text:
                    "You want to send Reminder Email to " +
                    self.employeeEmailData.first_name +
                    " " +
                    self.employeeEmailData.last_name +
                    "?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false
            })
                .then(result => {
                    if (result.value) {
                        let ids = [];
                        let obj = {
                            id: self.employeeEmailData.id
                        };
                        ids.push(obj);
                        self.$http
                            .post("employees/course_reminder_email", {
                                company_id: self.company_id,
                                ids: ids
                            })
                            .then(resp => {
                                self.employeeEmailModal = false;
                                if (resp.data.Status == 200) {
                                    Swal.fire({
                                        title: "Success!",
                                        text: resp.data.Email,
                                        icon: "success",
                                        confirmButtonClass: "btn btn-success btn-fill",
                                        buttonsStyling: false
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: resp.data.Email,
                                        icon: "error",
                                        confirmButtonClass: "btn btn-success btn-fill",
                                        buttonsStyling: false
                                    });
                                }
                            });
                    }
                })
                .catch(function() {});
        },
        showAssigncourse(row, course_company_id) {
            this.course_employee_id = row;
            this.course_company_id = course_company_id;
            this.courseAssigneeModal = true;
        },
        showAssigncoursefolders(row, course_company_id) {
            this.course_employee_id = row;
            this.course_company_id = course_company_id;
            this.courseFolderAssigneeModal = true;
        },
        downloadBulkCertificate(row){
            let user_id = row;
            window.location.href= this.baseUrl + '/downloadCourseFoldersCertificate/'+ user_id
        },
        sendWelcomeEmail() {
            let self = this;
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                text:
                    "You want to send Welcome Email to " +
                    self.employeeEmailData.first_name +
                    " " +
                    self.employeeEmailData.last_name +
                    "?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false
            })
                .then(result => {
                    if (result.value) {
                        let ids = [];
                        let obj = {
                            id: self.employeeEmailData.id
                        };
                        ids.push(obj);
                        self.$http
                            .post("employees/welcome_email", {
                                company_id: self.company_id,
                                ids: ids
                            })
                            .then(resp => {
                                self.employeeEmailModal = false;
                                Swal.fire({
                                    title: "Success!",
                                    text: "Email has been sent!",
                                    icon: "success",
                                    confirmButtonClass: "btn btn-success btn-fill",
                                    buttonsStyling: false
                                });
                            });
                    }
                })
                .catch(function() {});
        },
        sendPasswordResetEmail() {
            let self = this;
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                text: "You want to reset password ?",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false
            })
                .then(result => {
                    if (result.value) {
                        this.$http
                            .post("employees/password_reset", {
                                user_id: this.employeeEmailData.id
                            })
                            .then(resp => {
                                this.employeeEmailModal = false;
                                Swal.fire({
                                    title: "Success!",
                                    text:
                                        "New password has been sent to " +
                                        self.employeeEmailData.first_name +
                                        "'s Email!",
                                    icon: "success"
                                });
                            });
                    }
                })
                .catch(function() {});
        },
        handleEnvelope(index, row) {
            this.employeeEmailData.first_name = row.first_name;
            this.employeeEmailData.last_name = row.last_name;
            this.employeeEmailData.location = row.location;
            this.employeeEmailData.userName = row.location;
            this.employeeEmailData.id = row.id;
            this.employeeEmailModal = true;
        },
        handleEdit(index, row) {
            this.$router.push("/add_employee?id=" + row.id);
        },

        changeStatus(index, row) {
            let prev_val = row.status;
            let status = "";
            if (prev_val) {
                status = 0;
            } else {
                status = 1;
            }
            let self = this;
            Swal.fire({
                title: "Are you sure?",
                text: "You want to change status!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-success btn-fill",
                cancelButtonClass: "btn btn-danger btn-fill",
                confirmButtonText: "Yes!",
                cancelButtonText: "No",
                buttonsStyling: false
            })
                .then(result => {
                    if (result.value) {
                        self.$http
                            .put(
                                "/employees/update_status/" + row.id,
                                {
                                    status: status
                                },
                                self.config
                            )
                            .then(resp => {
                                self.fetchData();
                                Swal.fire({
                                    title: "Success!",
                                    text: "Status has been Changed.",
                                    icon: "success",
                                    confirmButtonClass: "btn btn-success btn-fill",
                                    buttonsStyling: false
                                });
                                self.tableData[index].status = !prev_val;
                            });
                    } else {
                        self.tableData[index].status = prev_val;
                    }
                })
                .catch(function() {
                    self.tableData[index].status = prev_val;
                });
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
                buttonsStyling: false
            }).then(result => {
                if (result.value) {
                    self.$http
                        .post(
                            "employees/unassign_course",
                            {
                                course_id: row.id,
                                employee_id: self.course_employee_id
                            },
                            self.config
                        )
                        .then(resp => {
                            self.employeeDataModal = false;
                            self.fetchData();
                            Swal.fire({
                                title: "Success!",
                                text: "Course has been Unassigned to this Employee!",
                                icon: "success",
                                confirmButtonClass: "btn btn-success btn-fill",
                                buttonsStyling: false
                            });
                        });
                }
            });
        },
        courseCheckchange(row) {
            console.clear();
            console.log(row);
            if (this.checked_courses.includes(row.employee_certificate_id)) {
                this.checked_courses.splice(
                    this.checked_courses.indexOf(row.employee_certificate_id),
                    1
                );
            } else {
                this.checked_courses.push(row.employee_certificate_id);
            }
        },

        openCourseDetails(row) {
            this.course_employee_id = row.id;
            this.employeeCoursesData.first_name = row.first_name;
            this.employeeCoursesData.last_name = row.last_name;
            this.employeeCoursesData.location = row.location;
            this.employeeCoursesData.type = row.type;
            this.employeeCoursesData.courseData = row.courses;
            this.course_company_id = row.company_id;
            this.employeeDataModal = true;
        },
        async fetchData() {
            if (this.editor == "company") {
                this.loading = true;
                this.$http
                    .post("employees/list", {
                        role: "admin",
                        filter_value: this.filterValue,
                        search: this.searchQuery,
                        company_id: this.filters.location_id,
                        employee_status: this.filters.employeStatus,
                        manager_id: this.admin_id,
                        page: this.currentPage,
                        column: this.sortedColumn,
                        order: this.order,
                        per_page: this.perPage
                    })
                    .then(resp => {
                        this.tableData = [];
                        let employee_data = resp.data.employee;
                        let total_data = resp.data.total;
                        this.totalData = total_data;
                        for (let data of employee_data) {
                            let obj = {
                                id: data.id,
                                checked: false,
                                first_name: data.first_name,
                                last_name: data.last_name,
                                email: data.email,
                                company: data.name,
                                company_id: data.company_id,
                                type: data.type,
                                location: "",
                                location_id: "",
                                courses: data.courses,
                                passOpenFail:
                                    data.course_pass_count +
                                    " | " +
                                    data.course_open_count +
                                    " | " +
                                    (data.course_fail_count + data.course_expired_count),
                                pass: data.course_pass_count,
                                open: data.course_open_count,
                                fail: data.course_fail_count + data.course_expired_count,
                                status: true
                            };

                            if (data.status) {
                                obj.status = true;
                            } else {
                                obj.status = false;
                            }
                            this.company_id = obj.company_id;
                            this.tableData.push(obj);
                        }
                    })
                    .finally(() => (this.loading = false));

                this.$http
                    .post("location/all", {
                        company_id: this.company_id
                    })
                    .then(resp => {
                        this.locations = [];
                        this.locations = [
                            {
                                label: "All",
                                value: ""
                            }
                        ];
                        for (let loc of resp.data) {
                            let obj = {
                                label: loc.name,
                                value: loc.id
                            };
                            this.locations.push(obj);
                        }
                    })
                    .finally(() => (this.loading = false));
                this.$http
                    .get("company/course_folders/" + this.company_id)
                    .then(resp => {
                        this.coursefolders = resp.data[0].coursefolders;
                    });
                this.$http.get("company/courses/" + this.company_id).then(resp => {
                    this.courses = resp.data[0].courses;
                });
            }
            if (this.editor == "manager") {
                this.loading = true;
                this.$http
                    .post("employees/list", {
                        role: "manager",
                        filter_value: this.filterValue,
                        search: this.searchQuery,
                        company_id: this.filters.location_id,
                        employee_status: this.filters.employeStatus,
                        manager_id: this.manager_id,
                        page: this.currentPage,
                        column: this.sortedColumn,
                        order: this.order,
                        per_page: this.perPage
                    })
                    .then(resp => {
                        this.tableData = [];
                        let employee_data = resp.data.employee;
                        let total_data = resp.data.total;
                        this.totalData = total_data;
                        for (let data of employee_data) {
                            let obj = {
                                id: data.id,
                                checked: false,
                                first_name: data.first_name,
                                last_name: data.last_name,
                                type: data.type,
                                location: "",
                                location_id: "",
                                email: data.email,
                                company: data.name,
                                company_id: data.company_id,
                                courses: data.courses,
                                passOpenFail:
                                    data.course_pass_count +
                                    " | " +
                                    data.course_open_count +
                                    " | " +
                                    (data.course_fail_count + data.course_expired_count),
                                pass: data.course_pass_count,
                                open: data.course_open_count,
                                fail: data.course_fail_count + data.course_expired_count,
                                status: true
                            };

                            if (data.status) {
                                obj.status = true;
                            } else {
                                obj.status = false;
                            }
                            this.tableData.push(obj);
                        }
                    })
                    .finally(() => (this.loading = false));

                this.$http
                    .post("location/all", {
                        company_id: this.company_id
                    })
                    .then(resp => {
                        this.locations = [];
                        this.locations = [
                            {
                                label: "All",
                                value: ""
                            }
                        ];
                        for (let loc of resp.data) {
                            let obj = {
                                label: loc.name,
                                value: loc.id
                            };
                            this.locations.push(obj);
                        }
                    })
                    .finally(() => (this.loading = false));
                this.$http
                    .get("company/coursefolders/" + this.company_id)
                    .then(resp => {
                        this.coursefolders = resp.data[0].coursefolders;
                    });
                this.$http.get("company/courses/" + this.company_id).then(resp => {
                    this.courses = resp.data[0].courses;
                });
            }
        },
        getProctoredExamCertificate: function(certificateURL) {
            this.$http
                .post("course/proctored-exam-certificate", {
                    certificateURL: certificateURL
                })
                .then(resp => {
                    window.open(resp.data.certificate_url, "_blank");
                });
        },
        deleteRow(row) {
            let indexToDelete = this.tableData.findIndex(
                tableRow => tableRow.id === row.id
            );
            if (indexToDelete >= 0) {
                this.tableData.splice(indexToDelete, 1);
            }
        },
        selectionChange(selectedRowss) {
            this.selectedRows = [];
            for (let selectedRow of selectedRowss) {
                this.selectedRows.push(selectedRow.id);
            }
        }
    }
};
</script>
<style scoped>
.no-border-card .card-footer {
    border-top: 0;
}

.custom-size .btn-sm {
    padding: 2px !important;
    font-size: 16px !important;
}
.ni-cloud-upload-96 {
    margin-top: 2px;
    font-size: 18px;
}

@media only screen and (max-width: 760px),
(min-device-width: 768px) and (max-device-width: 1024px) {
    .usersGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "Checkbox";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "First Name";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Last Name";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Location";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "User Type";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(6):before {
        content: "P/O/F";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(7):before {
        content: "Status";
    }
    .usersGrid >>> table.el-table__body td:nth-of-type(8):before {
        content: "Actions";
    }

    .pofGrid >>> table.el-table__body td:nth-of-type(1):before {
        content: "#";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(2):before {
        content: "Lesson Name";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(3):before {
        content: "Lesson Status";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(4):before {
        content: "Date Completed";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(5):before {
        content: "Date Assigned";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(6):before {
        content: "Due Date";
    }
    .pofGrid >>> table.el-table__body td:nth-of-type(7):before {
        content: "Actions";
    }
}
</style>
