<template>
  <div class="content" v-loading.fullscreen.lock="loading">
    <base-header class="pb-6">
      <div class="row align-items-center py-2">
        <div class="col-lg-6 col-7"></div>
      </div>
    </base-header>
    <div class="container-fluid mt--6">
      <div>
        <card class="no-border-card" footer-classes="pb-2">
          <template slot="header">
            <div class="row align-items-center">
              <div class="col-lg-6 col-5 text-left">
                <h2 class="mb-0">Locations</h2>
              </div>
              <div
                class="col-lg-6 text-right"
                v-if="company_level == 'parent' && editor != 'manager'"
              >
                <base-button name="Create Location Screen" class="custom-btn" @click.prevent="createLocation"
                  ><i class="fa fa-plus" aria-hidden="true"></i> Add
                  Location</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div class="row justify-content-sm-between flex-wrap">
              <div class="col-md-5">
                <label>Search:</label>
                <base-input
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  v-on:keyup="fetchData()"
                  placeholder="Search..."
                >
                </base-input>
              </div>
              <div class="col-md-3">
                <label>Status:</label>
                <el-select
                  name="Location Status Filter"
                  class="select-primary w-100"
                  v-model="filters.locationStatus"
                  placeholder="Filter by Location Status"
                  v-on:change="fetchData()"
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
            <div class="row">
              <div
                class="col-sm-12 mt-2"
                v-if="
                  editor === 'admin' ||
                    (editor === 'company' && level === 'parent')
                "
              >
                <h3>Parent Location</h3>
                <div class="user-eltable">
                  <el-table
                    header-row-class-name="thead-light custom-thead-light"
                    class="parentGrid"
                    role="table"
                    :data="tbl2_data"
                  >
                    <el-table-column
                      min-width="150px"
                      align="left"
                      label="Company Name"
                    >
                      <template slot-scope="props">
                        <router-link :to="'/add_location?id=' + props.row.id">
                          <span>{{ props.row.location_name }}</span>
                        </router-link>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="First name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.first_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="Last name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.last_name }}</span>
                      </template>
                    </el-table-column>

                    <el-table-column min-width="200px" label="Contact Email">
                      <template slot-scope="props">
                        <span v-if="props.row.email.length < 25">
                          {{ props.row.email }}
                        </span>
                        <el-tooltip
                          :content="props.row.email"
                          placement="top"
                          v-else
                        >
                          <span>
                            {{ props.row.email.substring(0, 25) + "..." }}</span
                          >
                        </el-tooltip>
                      </template>
                    </el-table-column>

                    <el-table-column
                      min-width="100px"
                      align="left"
                      label="Active Users"
                    >
                      <template slot-scope="props" v-if="props.row.accessToParentCompany">
                        <span
                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          v-if="props.row.activeemp_count > props.row.emp_count"
                          style="color: red"
                          ><b name="Location employee count"
                            >{{ props.row.activeemp_count }}/{{
                              props.row.employee_count
                            }}</b
                          ></span
                        >
                        <span
                          v-else
                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          style="color: green"
                          ><b name="Location employee count"
                            >{{ props.row.activeemp_count }}/{{
                              props.row.employee_count
                            }}</b
                          ></span
                        >
                      </template>
                    </el-table-column>
                    <el-table-column
                      min-width="150px"
                      align="left"
                      label="Actions"
                    >
                        <div
                            slot-scope="{ $index, row }"
                            class="d-flex custom-size"
                        >
                          <span v-if="row.accessToParentCompany != false">
<el-tooltip v-if="canEdit" content="Edit" placement="top">
                          <base-button
                              name="Edit Location"
                              v-if="editor === 'company'"
                              @click.native="handleEdit($index, row)"
                              class="success"
                              type=""
                              size="sm"
                              icon
                              data-toggle="tooltip"
                              data-original-title="Edit"
                          >
                            <i class="text-default fa fa-pencil-square-o"></i>
                          </base-button>

                          <router-link
                              :to="'/edit_company?id=' + row.id"
                              v-else
                          >
                            <base-button
                                name="Edit Location"
                                class="success"
                                type=""
                                size="sm"
                                icon
                                data-toggle="tooltip"
                                data-original-title="Edit"
                            >
                              <i class="text-default fa fa-pencil-square-o"></i>
                            </base-button>
                          </router-link>
                        </el-tooltip>
                        <el-tooltip
                            content="Download Course Report"
                            placement="top"
                        >
                          <base-button
                              name="Download Course Report"
                              @click="handleDownload($index, row)"
                              type=""
                              size="sm"
                              icon
                              data-toggle="tooltip"
                              data-original-title="Download Course Report"
                          >
                            <!-- <i class="text-danger ni ni-single-copy-04"></i> -->
                            <i name="Download Course Report" class="text-danger fa fa-cloud-download"></i>
                          </base-button>
                        </el-tooltip>
                        <el-tooltip
                            content="Download User Report"
                            placement="top"
                        >
                          <base-button
                              @click="downloadUserList($index, row)"
                              type=""
                              size="sm"
                              icon
                              data-toggle="tooltip"
                              data-original-title="Download User Report"
                          >
                            <i name="Download User Report" class="text-primary fas fa-address-book"></i>
                          </base-button>
                        </el-tooltip>
                        <el-tooltip content="Import Users" placement="top">
                          <base-button
                              @click="importUsers($index, row)"
                              type=""
                              size="sm"
                              icon
                              data-toggle="tooltip"
                              data-original-title="Import Users"
                          >
                            <i name="Import Users" class="text-primary fa fa-users"></i>
                          </base-button>
                        </el-tooltip>
                          </span>

                        </div>
                    </el-table-column>
                  </el-table>
                </div>
              </div>
            </div>
            <div v-if="editor != 'admin'">
              <div
                class="mt-2"
                v-if="editor === 'manager' || level != 'parent'"
              >
                <h3>Assigned Locations</h3>
                <div class="user-eltable">
                  <el-table
                    :data="tableData"
                    row-key="id"
                    class="childGrid"
                    role="table"
                    header-row-class-name="thead-light  custom-thead-light"
                  >
                    <el-table-column
                      align="left"
                      min-width="150px"
                      label="Location Name"
                    >
                      <template slot-scope="props">
                        <!-- <router-link
                          :to="'/location_details?id=' + props.row.id"
                        > -->
                        <span>{{ props.row.location_name }}</span>
                        <!-- </router-link> -->
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="150px"
                      label="Address"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.address }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="First name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.first_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="Last name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.last_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="170px" label="Contact Email">
                      <template slot-scope="props">
                        <span v-if="props.row.email.length < 25">
                          {{ props.row.email }}
                        </span>
                        <el-tooltip
                          :content="props.row.email"
                          placement="top"
                          v-else
                        >
                          <span>
                            {{ props.row.email.substring(0, 25) + "..." }}</span
                          >
                        </el-tooltip>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="130px" label="Active Users">
                      <template slot-scope="props">
                        <span
                          v-if="props.row.activeemp_count > props.row.emp_count"
                          style="color: red"
                          ><b>{{ props.row.employee_count }}</b></span
                        >
                        <span v-else style="color: green"
                          ><b>{{ props.row.employee_count }}</b></span
                        >
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
              </div>
            </div>
            <div class="row">
              <div
                class="col-sm-12 mt-4"
                v-if="editor === 'company' && level === 'parent'"
              >
                <h3>Child Location</h3>
                <div class="user-eltable">
                  <el-table
                    :data="tableData"
                    row-key="id"
                    class="childGrid"
                    role="table"
                    header-row-class-name="thead-light  custom-thead-light"
                  >
                    <el-table-column
                      align="left"
                      min-width="150px"
                      label="Location Name"
                    >
                      <template slot-scope="props">
                        <router-link :to="'/add_location?id=' + props.row.id">
                          <span>{{ props.row.location_name }}</span>
                        </router-link>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="First name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.first_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="Last name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.last_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="200px" label="Contact Email">
                      <template slot-scope="props">
                        <span v-if="props.row.email.length < 24">
                          {{ props.row.email }}
                        </span>
                        <el-tooltip
                          :content="props.row.email"
                          placement="top"
                          v-else
                        >
                          <span>
                            {{ props.row.email.substring(0, 24) + "..." }}</span
                          >
                        </el-tooltip>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="100px" label="Active Users">
                      <template slot-scope="props">
                        <span
                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          v-if="props.row.activeemp_count > props.row.emp_count"
                          style="color: red"
                          ><b name="Employee Count">{{ props.row.activeemp_count }} }}</b></span
                        >
                        <span
                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          v-else
                          style="color: green"
                          ><b name="Employee Count">{{ props.row.activeemp_count }}</b></span
                        >
                      </template>
                    </el-table-column>
                    <el-table-column
                      min-width="100px"
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
                      min-width="130px"
                      align="left"
                      label="Actions"
                    >
                      <div
                        slot-scope="{ $index, row }"
                        class="d-flex custom-size"
                      >
                        <el-tooltip v-if="canEdit" content="Edit" placement="top">
                          <base-button
                            name="Edit Location"
                            @click.native="handleEdit($index, row)"
                            class="success"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Edit"
                          >
                            <i class="text-default fa fa-pencil-square-o"></i>
                          </base-button>
                        </el-tooltip>
                        <el-tooltip
                          content="Download Course Report"
                          placement="top"
                        >
                          <base-button

                            @click="handleDownload($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Download Course Report"
                          >
                            <!-- <i class="text-danger ni ni-single-copy-04"></i> -->
                            <i name="Download Course Report" class="text-danger fa fa-cloud-download"></i>
                          </base-button>
                        </el-tooltip>

                        <el-tooltip
                          content="Download User Report"
                          placement="top"
                        >
                          <base-button
                            @click="downloadUserList($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Download User Report"
                          >
                            <i name="Download User Report" class="text-primary fas fa-address-book"></i>
                          </base-button>
                        </el-tooltip>
                        <el-tooltip content="Import Users" placement="top">
                          <base-button
                            @click="importUsers($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Import Users"
                          >
                            <i name="Import Users" class="text-primary fa fa-users"></i>
                          </base-button>
                        </el-tooltip>
                      </div>
                    </el-table-column>
                  </el-table>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12 mt-2" v-if="editor === 'admin'">
                <h3>Child Location</h3>
                <div class="user-eltable">
                  <el-table
                    :data="tableData"
                    row-key="id"
                    class="childGrid"
                    role="table"
                    header-row-class-name="thead-light  custom-thead-light"
                  >
                    <el-table-column
                      align="left"
                      min-width="150px"
                      label="Location Name"
                    >
                      <template slot-scope="props">
                        <router-link :to="'/add_location?id=' + props.row.id">
                          <span>{{ props.row.location_name }}</span>
                        </router-link>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="First name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.first_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      align="left"
                      min-width="120px"
                      label="Last name"
                    >
                      <template slot-scope="props">
                        <span>{{ props.row.last_name }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="200px" label="Contact Email">
                      <template slot-scope="props">
                        <span v-if="props.row.email.length < 24">
                          {{ props.row.email }}
                        </span>
                        <el-tooltip
                          :content="props.row.email"
                          placement="top"
                          v-else
                        >
                          <span>
                            {{ props.row.email.substring(0, 24) + "..." }}</span
                          >
                        </el-tooltip>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="100px" label="Active Users">
                      <template slot-scope="props">
                        <span

                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          v-if="props.row.activeemp_count > props.row.emp_count"
                          style="color: red"
                          ><b name="Employee Count">{{ props.row.activeemp_count }}</b></span
                        >
                        <span
                          v-else

                          class="linkColor"
                          @click="routeEmployee(props.$index, props.row)"
                          style="color: green"
                          ><b name="Employee Count">{{ props.row.activeemp_count }}</b></span
                        >
                      </template>
                    </el-table-column>
                    <el-table-column
                      min-width="150px"
                      align="left"
                      label="Actions"
                    >
                      <div
                        slot-scope="{ $index, row }"
                        class="d-flex custom-size"
                      >
                        <el-tooltip v-if="canEdit" content="Edit" placement="top">
                          <router-link :to="'/edit_company?id=' + row.id">
                            <base-button
                              class="success"
                              type=""
                              size="sm"
                              icon
                              data-toggle="tooltip"
                              data-original-title="Edit"
                            >
                              <i name="Edit Location" class="text-default fa fa-pencil-square-o"></i>
                            </base-button>
                          </router-link>
                        </el-tooltip>
                        <el-tooltip
                          content="Download Course Report"
                          placement="top"
                        >
                          <base-button
                            @click="handleDownload($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Download Course Report"
                          >
                            <!-- <i class="text-danger ni ni-single-copy-04"></i> -->
                            <i name="Download Course Report" class="text-danger fa fa-cloud-download"></i>
                          </base-button>
                        </el-tooltip>

                        <el-tooltip
                          content="Download User Report"
                          placement="top"
                        >
                          <base-button
                            @click="downloadUserList($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Download User Report"
                          >
                            <i name="Download User Report" class="text-primary fas fa-address-book"></i>
                          </base-button>
                        </el-tooltip>
                        <el-tooltip content="Import Users" placement="top">
                          <base-button
                            @click="importUsers($index, row)"
                            type=""
                            size="sm"
                            icon
                            data-toggle="tooltip"
                            data-original-title="Import Users"
                          >
                            <i name="Import Users"  class="text-primary fa fa-users"></i>
                          </base-button>
                        </el-tooltip>
                      </div>
                    </el-table-column>
                  </el-table>
                </div>
              </div>
            </div>
          </div>
        </card>
      </div>
    </div>

    <modal :show.sync="reportEmployeeDataModal" class="user-modal">
      <h4 slot="header" class="title title-up text-primary">
        {{ location_name }}
      </h4>
      <form>
        <div class="card-body sqr_border">
          <div class="row brdr_bottom">
            <div class="col-md-3 text-center">
              <span class="text-primary"><b>Employee Name</b></span>
            </div>
            <div class="col-md-9">
              <div class="row">
                <div class="col-md-4 text-center">
                  <span class="text-primary"><b>Course Name</b></span>
                </div>
                <div class="col-md-4 text-center">
                  <span class="text-primary"><b>Course Status</b></span>
                </div>
                <div class="col-md-4 text-center">
                  <span class="text-primary"><b>Certificate</b></span>
                </div>
              </div>
            </div>
          </div>

          <div
            class="row brdr_bottom"
            v-for="employee in reportData"
            :key="employee.id"
          >
            <div class="col-md-3 text-center brdr_right">
              {{ employee.employee_full_name }}
            </div>
            <div class="col-md-9">
              <div
                class="row my-3"
                v-for="course of employee.courses"
                :key="course.id"
              >
                <div class="col-md-4 text-center">
                  <span>{{ course.course_name }}</span>
                </div>
                <div class="col-md-4 text-center">
                  <span v-if="course.employee_course_status === 1">Pass</span>
                  <span v-if="course.employee_course_status === 2">Open</span>
                  <span v-if="course.employee_course_status === 0">Fail</span>
                </div>
                <div class="col-md-4 text-center">
                  <a
                    :href="course.certificate_url"
                    target="_blank"
                    v-if="
                      course.certificate_url !== null &&
                        course.certificate_url !== undefined &&
                        course.certificate_url !== ''
                    "
                    ><span class="">View</span></a
                  >
                  <span
                    v-if="
                      course.certificate_url === null ||
                        course.certificate_url === undefined ||
                        course.certificate_url === ''
                    "
                  >
                    Not Available</span
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-9"></div>
          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="importEmployeeModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Import Bulk Employees
        <span class="highlight-title">{{ company_name }}</span>
      </h3>
      <form>
        <div class="row" style="text-align: center; ">
          <a
            href="/assets/userBulkUploadFormat.csv"
            download
            class="underline-class"
            style="padding-right:5px; font-style:italic;"
          >
            Click here
          </a>
          <span style="font-style:italic;font-size:14px;">
            to download sample CSV file.</span
          >
        </div>
        <div class="row mt-2">
          <!-- <file-input v-on:change="getEmployeeFile($event)"></file-input> -->
          <input
            type="file"
            name="..."
            class="form-control "
            v-on:change="getEmployeeFile($event)"
          />
        </div>
        <div class="text-center my-4">
          <base-button name="Upload Bulk Employee"  type="primary" size="md" @click.prevent="uploadUser">
            Upload
          </base-button>
          <base-button
            type="danger"
            size="md"
            @click.prevent="canceluploadEmployee"
          >
            Cancel
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="previewModal" class="user-modal">
      <h3 slot="header" class="title mb-0">
        Preview Employees Data
      </h3>
      <form>
        <div class="row">
          <div class="col-md-4">
            <base-input
              v-model="password"
              label="Set password for all users *"
            ></base-input>
          </div>
        </div>
        <div class="user-eltable">
          <el-table
            class="table-striped "
            header-row-class-name="thead-light custom-thead-light"
            :data="returnedData.employees"
            style="width: 100%"
          >
            <el-table-column min-width="50" align="left">
              <template slot-scope="props">
                <span>{{ props.$index + 1 }}</span>
              </template>
            </el-table-column>
            <el-table-column
              min-width="100"
              align="left"
              label="First Name"
              prop="first_name"
            >
              <template slot-scope="props">
                <span>{{ props.row.employee_first_name }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="100" align="left" label="last name">
              <template slot-scope="props">
                <span>{{ props.row.employee_last_name }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="90" align="left" label="Job title">
              <template slot-scope="props">
                <span>{{ props.row.employee_job_title }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="180" label="Contact Email">
              <template slot-scope="props">
                <span v-if="props.row.employee_email.length < 25">
                  {{ props.row.employee_email }}
                </span>
                <el-tooltip
                  :content="props.row.employee_email"
                  placement="top"
                  v-else
                >
                  <span>
                    {{
                      props.row.employee_email.substring(0, 25) + "..."
                    }}</span
                  >
                </el-tooltip>
              </template>
            </el-table-column>
             <el-table-column min-width="180" align="left" label="Username">
                <template slot-scope="props">
                  <span>{{ props.row.user_name }}</span>
                </template>
              </el-table-column>
            <el-table-column min-width="100" align="left" label="User Type">
              <template slot-scope="props">
                <span>{{ props.row.usertype }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="130" align="left" label="Phone No.">
              <template slot-scope="props">
                <span>{{ props.row.phonenum }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="100" align="left" label="Address">
              <template slot-scope="props">
                <span>{{ props.row.address }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="80" align="left" label="City">
              <template slot-scope="props">
                <span>{{ props.row.city }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="80" align="left" label="State">
              <template slot-scope="props">
                <span>{{ props.row.state }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="80" align="left" label="ZipCode">
              <template slot-scope="props">
                <span>{{ props.row.zipcode }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="100" align="left" label="Actions">
              <template slot-scope="props">
                <div class="d-flex custom-size">
                  <span class="mr-1">
                    <base-button
                      class="success"
                      type=""
                      size="sm"
                      icon
                      @click.prevent="edituploadeddata(props.$index + 1)"
                      ><i name="Edit Upload Data"
                        class="text-default fa fa-pencil-square-o"
                      ></i></base-button
                  ></span>
                  <span
                    ><base-button
                      class="danger"

                      name="Delete Upload Data"
                      type=""
                      size="sm"
                      icon
                      @click.prevent="deleteUploadedData(props.$index)"
                      ><i class="text-default fa fa-trash"></i></base-button
                  ></span>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="text-right mt-3">
          <base-button
            :disabled="!this.password"
            class="primary"
            name="Upload Bulk Employees"
            @click.prevent="uploadEmployees"
          >
            {{ "Upload Employees" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="previewUpdateModal" v-if="editdata" class="user-modal">
      <h3 class="mb-0" slot="header">Edit Imported Data</h3>
      <div
        class="container"
        v-for="(employee, index) in returnedData.employees"
        :key="employee.id"
      >
        <div v-if="editIndex === index + 1">
          <div class="row">
            <div class="col-md-12">
              <h4 class="text-primary">Editing Record {{ index + 1 }}</h4>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <base-input
                type="text"
                label="First Name"
                :name="'first name_' + index"
                rules="required"
                placeholder="First Name"
                v-model="employee.employee_first_name"
              >
              </base-input>
            </div>
            <div class="col-md-3">
              <base-input
                type="text"
                label="Last Name"
                :name="'last name_' + index"
                rules="required"
                placeholder="Last Name"
                v-model="employee.employee_last_name"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <label>Job Title</label>
              <el-select
                class=" mr-3"
                style="width: 100%"
                placeholder="Select Job Title"
                v-model="employee.employee_job_title"
              >
                <el-option
                  v-for="(option, index) in jobTitles"
                  class="select-primary"
                  :value="option.label"
                  :label="option.label"
                  :key="'job_title_' + index"
                >
                </el-option>
              </el-select>
            </div>
            <div class="col-md-2">
              <base-input
                type="email"
                label="Email"
                :name="'Email'"
                placeholder="Email"
                v-model="employee.employee_email"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="Username"
                :name="'Username'"
                placeholder="Username"
                v-model="employee.user_name"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <label>User Type</label>
              <el-select
                class=" mr-3"
                style="width: 100%"
                placeholder="Select User Type"
                v-model="employee.usertype"
              >
                <el-option
                  v-for="(option, index) in user_types"
                  class="select-primary"
                  :name="'User type'"
                  rules="required"
                  :value="option.value"
                  :label="option.label"
                  :key="'user_type_' + index"
                >
                </el-option>
              </el-select>
            </div>
            <div class="col-md-2">
              <!-- <base-input
                type="tel"
                label="Phone Number"
                placeholder="Phone number"
                v-model="employee.phonenum"
              >
              </base-input> -->
              <base-input label="Phone Number">
                <VuePhoneNumberInput
                  v-model="employee.phonenum"
                  :no-country-selector="true"
                  :no-example="true"
                  :translations="{ phoneNumberLabel: '' }"
                />
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="Address"
                :name="'Address'"
                placeholder="Address"
                v-model="employee.address"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="City"
                :name="'City'"
                placeholder="City"
                v-model="employee.city"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="State"
                :name="'State'"
                placeholder="State"
                v-model="employee.state"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="number"
                label="Zipcode"
                :name="'Zipcode'"
                placeholder="Zipcode"
                v-model="employee.zipcode"
              >
              </base-input>
            </div>
            <div class="col-md-12" style="text-align:right;">
              <button
                class="btn base-button primary btn-default"
                @click.prevent="updateuploadeddata(index, employee)"
              >
                Update
              </button>
            </div>
          </div>
        </div>
      </div>
    </modal>
    <modal :show.sync="downlaodModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Download Course Report for
        <span class="highlight-title">{{ company_name }}</span>
      </h3>
      <el-select v-model="filters.course_id" placeholder="All Courses">
        <el-option
          class="select-default"
          v-for="item in companyCourses"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        >
        </el-option>
      </el-select>
      <form>
        <br />
        <div class="row" style="text-align: center">
          <base-button
            name="Download Open Courses"
            type="warning"
            @click.prevent="downloadcourselist('open')"
            >Open Courses</base-button
          >
          <base-button
            name="Download Non Compliance Courses"
            type="danger"
            @click.prevent="downloadcourselist('non-complaint')"
          >
            Non Compliance</base-button
          >
          <base-button
            type="success"
            name="Download Compliance Courses"
            @click.prevent="downloadcourselist('complaint')"
            >Compliance</base-button
          >
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
  </div>
</template>
<script>
import {Option, Select, Table, TableColumn} from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import XLSX from "xlsx";
import VuePhoneNumberInput from "vue-phone-number-input";
import "vue-phone-number-input/dist/vue-phone-number-input.css";

let timeout = null;
export default {
  mixins: [serverSidePaginationMixin],
  components: {
    VuePhoneNumberInput,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      loading: false,
      returnedData: [],
      editdata: false,
      previewUpdateModal: false,
      previewModal: false,
      importEmployeeModel: false,
      reportEmployeeDataModal: false,
      company_id: "",
      hot_user: "",
      hot_token: "",
      config: "",
      password: "",
      company_name: "",
      excel_data: {
        employee_first_name: "",
        employee_last_name: "",
        employee_email: "",
        user_name:"",
        employee_job_title: "",
        usertype: "",
        phonenum: "",
        //assignedlocation: "",
        file: ""
      },
      user_types: [
        {
          label: "Admin",
          value: "admin"
        },
        {
          label: "Manager",
          value: "manager"
        },
        {
          label: "Employee",
          value: "employee"
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
          value: "All"
        }
      ],
      filters: {
        locationStatus: "Active",
        companyStatus: "Active",
        companiesType: "parent",
        course_id: "All Courses"
      },

      jobTitles: [],
      manager_id: "",
      admin_id: "",
      searchQuery: "",
      tbl_data: [],
      tbl2_data: [],
      json_data: [],
      reportData: [],
      location_name: "",
      tableData: [],
      selectedRows: [],
      editor: "",
      company_level: "",
      level: "",
      companyCourses: [],
      downlaodModel: false,
      canCreate:true,
      canEdit:true,
      canDelete:true,

    };
  },
  watch: {
    searchQuery: function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    }
  },
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");

      if (this.hot_user == "company-admin") {
        this.editor = "company";
        this.admin_id = localStorage.getItem("hot-admin-id");
        this.company_level = localStorage.getItem("hot-company-level");
        this.company_id = localStorage.getItem("hot-user-id");
        this.company_name = localStorage.getItem("hot-company-name");
      }
      console.log(this.company_level);
      if (this.hot_user == "super-admin"  || this.hot_user == "sub-admin") {
        this.editor = "admin";
      }
      if (this.hot_user == "sub-admin") {
       this.getRightsDetails();
      }

      if (this.hot_user == "manager") {
        this.editor = "manager";
        this.manager_id = localStorage.getItem("hot-user-id");
      }
    }
    this.fetchData();

  },
  methods: {
     getRightsDetails(){
       let type="Company";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
    },

    routeEmployee(index, row) {
      if (this.editor == "company") {
        this.$router.push("/company_employees?id=" + row.id);
      } else {
        this.$router.push("/all_users?id=" + row.id);
      }
    },
    downloadUserList(index, row) {
      this.company_id = row.id;
      this.company_name = row.location_name;
      this.report_type = "all_user";
      this.$http
        .post("company/users", {
          report_type: this.report_type,
          company_id: this.company_id
        })
        .then(resp => {
          this.items = resp.data;
          const data = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data, "data");
          XLSX.writeFile(
            wb,
            this.company_name + "-" + this.report_type + ".xlsx"
          );
        });
    },
    downloadcourselist(type) {
      this.loading = true;
      let report_type = "";
      if (type == "open") {
        this.report_type = "open_course";
      }
      if (type == "non-complaint") {
        this.report_type = "non_compliance";
      }
      if (type == "complaint") {
        this.report_type = "compliance";
      }
      this.$http
        .post("course/certificates/report", {
          report_type: this.report_type,
          company_id: this.company_id,
          course_id: this.filters.course_id
        })
        .then(resp => {
          this.items = resp.data;
          const data = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data, "data");
          XLSX.writeFile(
            wb,
            this.company_name + "-" + this.report_type + ".xlsx"
          );
        })
        .catch(function(error) {
          self.processing = false;
          if (error.response.status === 422) {
            let respmessage = error.response.data.message;
            Swal.fire({
              title: "Error!",
              text: respmessage,
              icon: "error"
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    importUsers(index, row) {
      this.company_id = row.id;
      this.company_name = row.location_name;
      this.importEmployeeModel = true;
    },
    getEmployeeFile(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.excel_data.file = file[0];
    },
    uploadEmployees() {
      this.returnedData.password = this.password;
      this.loading = true;
      this.processing = true;
      this.$http
        .post("employees/bulk_user", this.returnedData)
        .then(resp => {
           if(resp.status === 202 && resp.data.success_count == 0){

            Swal.fire({
              title: "Success: " + resp.data.success_count,
              html: "Failed: " + resp.data.message.split("</br>").length +"</br>"+
              '<ul style="text-align: left;">' + resp.data.message + '</ul>',
              icon: "error"
            });


           }else{
          this.items = resp.data.data;
          const data = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data, "data");
          XLSX.writeFile(wb, this.company_name + ".xlsx");
          if(resp.status === 202){
            if(resp.data.success_count > 0){
            Swal.fire({
              title: "Success: " + resp.data.success_count,
              html: "Failed: " + resp.data.message.split("</br>").length +"</br>"+
              '<ul style="text-align: left;">' + resp.data.message + '</ul>',
              icon: "success"
            });
            }

          }else{
            Swal.fire({
            title: "Success!",
            text: "Employees Uploaded Successfully",
            icon: "success"
          });
          }
          if (this.editor === "super-admin" || this.editor === "sub-admin") {
            this.$router.push("/all_users");
          }
          if (this.editor === "company") {
            this.$router.push("/company_employees");
          }

           }
        })
        .catch(function(error) {
          if (error.response.status === 422) {
            self.processing = false;
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error"
            });
          }else if(error.response.status === 500){
              Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error"
            });
          }else {
            self.processing = false;
            Swal.fire({
              title: "Error!",
              text: "Invalid File data!",
              icon: "error"
            });
          }
        })
        .finally(() => (this.loading = false));
    },

    uploadUser() {
      let self = this;
      this.processing = true;
      this.loading = true;
      if (this.excel_data.file !== "") {
        let formData = new FormData();
        formData.append("quizFile", this.excel_data.file);
        formData.append("file", "employee");
        let employeeData = {
          password: "",
          employees: [],
          company_id: this.company_id
        };
        this.employee_data = [];
        this.$http
          .post("course/read_file", formData, {})
          .then(resp => {
            let data = resp.data[0];
            let index = 0;
            for (let employee of data) {
              let obj = {
                employee_first_name: employee[0],
                employee_last_name: employee[1],
                employee_email: employee[3],
                user_name: employee[4],
                usertype: employee[5],
                phonenum: employee[6],
                address: employee[7],
                city: employee[8],
                state: employee[9],
                zipcode: employee[10],
                employee_job_title: employee[2]
                // assignedlocation: employee[2]
              };
              if (index !== 0) {
                employeeData.employees.push(obj);
              }
              index++;
            }
            this.returnedData = employeeData;
            this.previewModal = true;
            self.processing = false;
          })
          .catch(function(error) {
            self.processing = false;
            Swal.fire({
              title: "Error!",
              text: "Invalid File data!",
              icon: "error"
            });
          })
          .finally(() => (this.loading = false));
      } else {
        self.processing = false;
        this.loading = false;
        Swal.fire({
          title: "Error!",
          text: `Please Select any File!`,
          icon: "error"
        });
      }
    },
    acceptNumber(phonenum) {
      var x = phonenum.replace(/\D/g, "").match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      employee.phonenum = !x[2]
        ? x[1]
        : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    },
    edituploadeddata(e) {
      this.$http.get("employees/jobTitles").then(resp => {
        let jobtitle = resp.data;
        for (let data of jobtitle) {
          let obj = {
            value: data.id,
            label: data.name
          };
          this.jobTitles.push(obj);
        }
      });

      this.previewUpdateModal = true;
      this.editIndex = e;
      this.editdata = true;
    },
    updateuploadeddata(e, employees) {
      let obj = {
        employee_first_name: employees.first_name,
        employee_last_name: employees.last_name,
        employee_email: employees.email,
        user_name: employees.user_name,
        usertype: employees.usertype,
        phonenum: employees.phonenum,
        employee_job_title: employees.job_title,
        assignedlocation: employees.assignedlocation
      };
      this.returnedData.employees.splice(e, obj);
      this.previewUpdateModal = false;
      this.editdata = false;
      Swal.fire({
        title: "Success!",
        text: `Updated Successfully!`,
        icon: "success"
      });
    },
    canceluploadEmployee() {
      this.importEmployeeModel = false;
    },
    deleteUploadedData(e) {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: "You want to remove this employee",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        cancelButtonText: "No",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false
      }).then(result => {
        if (result.value) {
          self.returnedData.employees.splice(e, 1);
        }
      });
    },
    createLocation() {
      this.$router.push("/add_location");
    },

    handleDownload(index, row) {
      this.company_id = row.id;
      this.company_name = row.location_name;
      this.$http.get("company/all_courses/" + this.company_id).then(resp => {
        this.companyCourses = [];
        let fobj = {
          label: "All Courses",
          value: "All Courses"
        };
        this.companyCourses.push(fobj);
        for (let data of resp.data[0].courses) {
          let obj = {
            label: data.name,
            value: data.course_id
          };
          this.companyCourses.push(obj);
        }
      });
      this.downlaodModel = true;
    },
    handleEdit(index, row) {
      this.$router.push("/add_location?id=" + row.id);
    },

    handleReport(index, row) {
      this.location_name = row.location_name;
      this.$http
        .post("employees/report", {
          company_id: this.company_id,
          location_id: row.id
        })
        .then(resp => {
          this.reportData = resp.data;
          this.reportEmployeeDataModal = true;
        });
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
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        buttonsStyling: false
      })
        .then(result => {
          if (result.value) {
            self.$http
              .put(
                "/location/update_status/" + row.id,
                {
                  company_id: this.company_id,
                  status: status
                },
                self.config
              )
              .then(resp => {
                this.fetchData();
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
    fetchData() {
      this.loading = true;
      if (this.$route.query.id) {
        this.company_id = this.$route.query.id;
        this.$http
          .post("location/list", {
            search: this.searchQuery,
            company_id: this.company_id,
            location_status: this.filters.locationStatus,
            role: "super-admin"
          })
          .then(resp => {
            this.tableData = [];
            this.tbl2_data = [];
            if (resp.data.childdata) {
              this.level = "child";
              for (let data of resp.data.childdata) {
                let obj = {
                  id: data.id,
                  location_name: data.name,
                  address: data.address_1,
                  employee_count: data.employee_num,
                  activeemp_count: data.active_user_count,
                  first_name: data.admin ? data.admin.first_name : "",
                  last_name: data.admin ? data.admin.last_name : "",
                  email: data.admin ? data.admin.email : "",
                  status: true
                };
                if (data.status) {
                  obj.status = true;
                } else {
                  obj.status = false;
                }
                this.tableData.push(obj);
              }
            }
            if (resp.data.parentdata) {
              this.level = "parent";
              for (let data of resp.data.parentdata) {
                let obj = {
                  id: data.id,
                  location_name: data.name,
                  first_name: data.admin ? data.admin.first_name : "",
                  last_name: data.admin ? data.admin.last_name : "",
                  email: data.admin ? data.admin.email : "",
                  employee_count: data.employee_num,
                  activeemp_count: data.active_user_count
                };
                if (data.status) {
                  obj.status = true;
                } else {
                  obj.status = false;
                }
                this.tbl2_data.push(obj);
              }
            }
          })
          .finally(() => (this.loading = false));
      } else {
        if (this.editor == "manager") {
          this.$http
            .post("location/list", {
              search: this.searchQuery,
              location_status: this.filters.locationStatus,
              role: "manager"
            })
            .then(resp => {
              this.tableData = [];
              let location_data = resp.data;
              for (let data of location_data) {
                let obj = {
                  id: data.id,
                  location_name: data.name,
                  address: data.address_1,
                  employee_count: data.employee_num,
                  activeemp_count: data.active_user_count,
                  // location_manager: [],
                  first_name: data.admin ? data.admin.first_name : "",
                  last_name: data.admin ? data.admin.last_name : "",
                  email: data.admin ? data.admin.email : "",
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
        } else if (this.editor == "company") {
          this.$http
            .post("location/list", {
              search: this.searchQuery,
              location_status: this.filters.locationStatus,
              role: "admin"
            })
            .then(resp => {
              this.tableData = [];
              this.tbl2_data = [];
              let location_data = resp.data;
              if (resp.data.childdata) {
                this.level = "child";
                for (let data of resp.data.childdata) {
                  let obj = {
                    id: data.id,
                    location_name: data.name,
                    address: data.address_1,
                    employee_count: data.employee_num,
                    activeemp_count: data.active_user_count,
                    first_name: data.admin ? data.admin.first_name : "",
                    last_name: data.admin ? data.admin.last_name : "",
                    email: data.admin ? data.admin.email : "",
                    status: true
                  };
                  if (data.status) {
                    obj.status = true;
                  } else {
                    obj.status = false;
                  }
                  this.tableData.push(obj);
                }
              }
              if (resp.data.parentdata) {
                this.level = "parent";
                for (let data of resp.data.parentdata) {
                  let obj = {
                    id: data.id,
                    location_name: data.name,
                    first_name: data.admin ? data.admin.first_name : "",
                    last_name: data.admin ? data.admin.last_name : "",
                    email: data.admin ? data.admin.email : "",
                    employee_count: data.employee_num,
                    activeemp_count: data.active_user_count,
                    accessToParentCompany: data.accessToParentCompany,
                  };
                  if (data.status) {
                    obj.status = true;
                  } else {
                    obj.status = false;
                  }
                  this.tbl2_data.push(obj);
                }
              }
            })
            .finally(() => (this.loading = false));
        }
      }
    },
    selectionChange(selectedRows) {
      this.selectedRows = selectedRows;
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

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .parentGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Company Name";
  }
  .parentGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "First name";
  }
  .parentGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Last name";
  }
  .parentGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Contact Email";
  }
  .parentGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Active Users";
  }
  .parentGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Actions";
  }

  .childGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Loction Name";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "First Name";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Last Name";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Contact Email";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Empoyee Count";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Status";
  }
  .childGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Actions";
  }

  .adminchildGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Loction Name";
  }
  .adminchildGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Location Type";
  }
  .adminchildGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Location Address";
  }
  .adminchildGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Status";
  }
}
</style>
