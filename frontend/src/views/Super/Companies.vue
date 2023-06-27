<template>
  <card
    class="no-border-card"
    footer-classes="pb-2"
    v-loading.fullscreen.lock="loading"
  >
    <template slot="header">
      <div class="row align-items-center">
        <div class="col-lg-4 col-md-3 col-12">
          <h2 class="mb-0">Companies</h2>
        </div>

        <div class="col-lg-2 col-md-3 col-5 text-md-right" style="">
          <el-tooltip content="Delete Companies" placement="top">
            <base-button
              v-if="selectedRows.length > 0 && canDelete"
              type="danger"
              class="delete"
              data-toggle="tooltip"
              data-original-title="Delete Companies"
              @click.prevent="deleteBulkCompanies()"
              ><i class="fa fa-trash-o"></i> Delete</base-button
            >
          </el-tooltip>
        </div>
        <div class="col-lg-6 col-md-6 text-right">
          <base-button class="custom-btn" v-on:click="resetFilters()"
            ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
            Filters</base-button
          >

          <base-button
            v-if="canCreate"
            @click.prevent="openCreateCompany()"
            class="custom-btn"
            ><i class="fa fa-plus" aria-hidden="true"></i> Add
            Company</base-button
          >
        </div>
      </div>
    </template>
    <div>
      <div class="row flex-wrap">
        <div class="col-md-5">
          <base-input
            label="Search:"
            v-model="searchQuery"
            prepend-icon="fas fa-search"
            placeholder="Search..."
          >
          </base-input>
        </div>
        <div class="col-md-3">
          <base-input label="Companies:">
            <el-select
              class="select-primary"
              v-model="filters.companiesType"
              v-on:change="changePage(1)"
            >
              <el-option
                class="select-primary"
                v-for="item in company_type"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </base-input>
        </div>
        <div class="col-md-2 col-6">
          <base-input label="Status:">
            <el-select
              class="select-primary"
              v-model="filters.companyStatus"
              placeholder="Company Status"
              v-on:change="changePage(1)"
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
          </base-input>
        </div>

        <div class="col-md-2 form-group col-6">
          <base-input label="Showing:">
            <el-select
              class="select-primary pagination-select"
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
          </base-input>
        </div>
      </div>
      <div class="user-eltable">
        <el-table
          role="table"
          :data="tableData"
          stripe
          highlight-current-row
          row-key="id"
          id="tbl1"
          header-row-class-name="thead-light"
          class="compGrid"
          @selection-change="selectionChange"
        >
          <el-table-column
            v-for="column in tableColumns"
            :key="column.label"
            v-bind="column"
          >
          </el-table-column>
          <el-table-column min-width="180px" prop="company_name">
            <template slot="header">
              <span @click="sortByColumn(0)"
                >Company Name
                <i
                  v-if="sortedColumn == 0 && order === 'asc'"
                  class="fas fa-arrow-up text-blue linkColor"
                /><i v-else class="fas fa-arrow-down text-blue linkColor" />
              </span>
            </template>
            <template slot-scope="props">
              <router-link :to="'/edit_company?id=' + props.row.id">
                <span v-if="canEdit">
                  {{ props.row.company_name }}
                </span>
                <span v-else>
                  {{ props.row.company_name }}
                </span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column min-width="100px" prop="location">
            <template slot="header">
              <span @click="sortByColumn(1)"
                ># Location
                <i
                  v-if="sortedColumn == 1 && order === 'asc'"
                  class="fas fa-arrow-up text-center text-blue linkColor"
                /><i v-else class="fas fa-arrow-down text-blue linkColor" />
              </span>
            </template>
            <template slot-scope="props">
              <span
                class="linkColor comppaniescount"
                v-if="props.row.location"
                @click="routeLocations(props.$index, props.row)"
                >{{ props.row.location }}</span
              >
              <span class="disabled" v-else>{{ props.row.location }}</span>
            </template>
          </el-table-column>
          <el-table-column min-width="100px" prop="employees">
            <template slot="header">
              <span @click="sortByColumn(2)"
                ># Employee
                <i
                  v-if="sortedColumn == 2 && order === 'asc'"
                  class="fas fa-arrow-up text-blue linkColor"
                /><i v-else class="fas fa-arrow-down text-blue linkColor" />
              </span>
            </template>
            <template slot-scope="props">
              <span
                v-if="props.row.employees && filters.companiesType === 'parent'"
                class="linkColor comppaniescount"
                @click="
                  routeEmployee(props.$index, props.row, (onlyParent = true))
                "
                >{{ props.row.employees }}
              </span>

              <span
                v-else
                class="linkColor comppaniescount"
                @click="
                  routeEmployee(props.$index, props.row, (onlyParent = false))
                "
                >{{ props.row.employees }}</span
              >
              <span v-if="props.row.total_employees"> / </span>
              <span
                v-if="props.row.total_employees"
                class="linkColor comppaniescount"
                @click="
                  routeEmployee(props.$index, props.row, (onlyParent = false))
                "
              >
                {{ props.row.total_employees }}
              </span>
            </template>
          </el-table-column>
          <el-table-column min-width="100px" prop="assigned_courses">
            <template slot="header">
              <span @click="sortByColumn(3)"
                ># Courses
                <i
                  v-if="sortedColumn == 3 && order === 'asc'"
                  class="fas fa-arrow-up text-blue linkColor"
                /><i v-else class="fas fa-arrow-down text-blue linkColor" />
              </span>
            </template>
            <template slot-scope="props">
              <span
                v-if="props.row.assigned_courses"
                class="linkColor comppaniescount"
                @click="routeCourses(props.$index, props.row)"
                >{{ props.row.assigned_courses }}</span
              >
              <span v-else class="disabled">{{
                props.row.assigned_courses
              }}</span>
            </template>
          </el-table-column>
          <el-table-column min-width="120px" prop="">
            <template slot="header">
              <span
                ># Food Manager
              </span>
            </template>
            <template slot-scope="props">
              <span class="linkColor" v-if="props.row.total_fm_count" @click="showFoodManagerDetails(props.row)"><b>{{ props.row.food_manager_count }}</b><b>/{{(props.row.total_fm_count-props.row.used_fm_count)}}</b></span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column min-width="100px" label="Status" prop="status">
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
          <el-table-column min-width="150px" align="left" label="Actions">
            <div slot-scope="{ $index, row }" class="d-flex custom-size">
              <el-tooltip content="Edit" placement="top" v-if="canEdit">
                <router-link :to="'/edit_company?id=' + row.id">
                  <base-button
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
              <el-tooltip content="Download Course Report" placement="top">
                <base-button
                  @click="handleDownload($index, row)"
                  type=""
                  size="sm"
                  icon
                  data-toggle="tooltip"
                  data-original-title="Download Course Report"
                >
                  <!-- <i class="text-danger ni ni-single-copy-04"></i> -->
                  <i class="text-danger fa fa-cloud-download"></i>
                </base-button>
              </el-tooltip>
              <el-tooltip content="Download User Report" placement="top">
                <base-button
                  @click="downloadUserList($index, row)"
                  type=""
                  size="sm"
                  icon
                  data-toggle="tooltip"
                  data-original-title="Download User Report"
                >
                  <i class="text-primary fas fa-address-book"></i>
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
                  <i class="text-primary fa fa-users"></i>
                </base-button>
              </el-tooltip>
              <el-tooltip content="Import Child Companies" placement="top">
                <base-button
                  v-if="filters.companiesType == 'parent'"
                  @click="importChildCompanies($index, row)"
                  type=""
                  size="sm"
                  icon
                  data-toggle="tooltip"
                  data-original-title="Import Child Companies"
                >
                  <i class="text-warning fa fa-map-marker"></i>
                </base-button>
              </el-tooltip>
            </div>
          </el-table-column>
        </el-table>
      </div>
    </div>
    <div slot="footer" class="d-flex justify-content-end">
      <nav v-if="pagination && tableData.length > 0">
        <div class="row">
          <div class="col-md-12">
            <ul
              class="
                pagination
                custompagination
                justify-content-end
                align-items-center
              "
            >
              <p class="p-0 m-0 mr-2">
                Showing {{ tableData.length }} of {{ totalData }} entries
              </p>
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a
                  class="page-link"
                  href="#"
                  @click.prevent="changePage(currentPage - 1)"
                  ><i class="fa fa-caret-left"></i>
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
                  disabled: currentPage === last_page,
                }"
              >
                <a
                  class="page-link"
                  href="#"
                  @click.prevent="changePage(currentPage + 1)"
                  ><i class="fa fa-caret-right"></i
                ></a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
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
            type="warning"
            @click.prevent="downloadcourselist('open')"
            >Open Courses</base-button
          >
          <base-button
            type="danger"
            @click.prevent="downloadcourselist('non-complaint')"
          >
            Non Compliance</base-button
          >
          <base-button
            type="success"
            @click.prevent="downloadcourselist('complaint')"
            >Compliance</base-button
          >
        </div>
        <!-- <button type="button" class="download-btn" v-on:click="download">Download</button>-->
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="importEmployeeModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Import Bulk Employees
        <span class="highlight-title">{{ company_name }}</span>
      </h3>
      <form>
        <div style="text-align: left">
          <a
            href="/assets/userBulkUploadFormat.csv"
            download
            class="underline-class"
            style="padding-right: 5px; font-style: italic"
          >
            Click here
          </a>
          <span style="font-style: italic; font-size: 14px">
            to download sample CSV file.</span
          >
        </div>
        <div class="mt-2">
          <!-- <file-input v-on:change="getEmployeeFile($event)"></file-input> -->
          <input
            type="file"
            name="..."
            class="form-control"
            v-on:change="getEmployeeFile($event)"
          />
        </div>
        <div class="text-right mt-3">
          <base-button
            type="danger"
            size="md"
            @click.prevent="canceluploadEmployee"
          >
            Cancel
          </base-button>
          <base-button class="custom-btn" size="md" @click.prevent="uploadUser">
            Upload
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>

    <modal :show.sync="importLocationModel">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Import Bulk Locations for
        <span class="highlight-title">{{ company_name }}</span>
      </h3>
      <form>
        <div class="row" style="text-align: center">
          <a
            href="/assets/locationUploadFormat.csv"
            download
            class="underline-class"
            style="padding-right: 5px"
          >
            Click here
          </a>
          <span style="font-style: italic; font-size: 14px">
            to download sample CSV file.</span
          >
        </div>
        <div class="row mt-2">
          <!-- <file-input v-on:change="getEmployeeFile($event)"></file-input> -->
          <input
            type="file"
            name="..."
            class="form-control"
            v-on:change="getLocationFile($event)"
          />
        </div>
        <div class="text-center my-4">
          <base-button type="primary" size="md" @click.prevent="uploadLocation">
            Upload
          </base-button>
          <base-button
            type="danger"
            size="md"
            @click.prevent="canceluploadLocation"
          >
            Cancel
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="previewModal" class="user-modal">
      <h3 slot="header" class="title mb-0">Preview Employees Data</h3>
      <form>
        <div class="row">
          <div class="col-md-4">
            <base-input
              v-model="password"
              label="Set password for all users *"
            ></base-input>
          </div>
          <div class="col-md-4"></div>
          <div class="col-md-4">
            <base-input label="Courses">
              <el-select
                multiple
                filterable
                class="select-primary"
                name="Courses"
                v-model="selected_courses"
                placeholder="Select Course"
              >
                <el-option
                  class="select-primary"
                  v-for="item in companyCourses"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                >
                </el-option>
              </el-select>
            </base-input>
          </div>
        </div>
        <div>
          <div class="user-eltable uploademp-table">
            <el-table
              class="table-striped"
              header-row-class-name="thead-light custom-thead-light"
              :data="returnedData.employees"
              style="width: 100%"
            >
              <el-table-column min-width="40" align="left">
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
              <el-table-column min-width="180" align="left" label="Email">
                <template slot-scope="props">
                  <span>{{ props.row.employee_email }}</span>
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
              <el-table-column min-width="70" align="left" label="Actions">
                <template slot-scope="props">
                  <div class="d-flex custom-size">
                    <span class="mr-1">
                      <base-button
                        class="success"
                        type=""
                        size="sm"
                        icon
                        @click.prevent="edituploadeddata(props.$index + 1)"
                        ><i
                          class="text-default fa fa-pencil-square-o"
                        ></i></base-button
                    ></span>
                    <span
                      ><base-button
                        class="danger"
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
        </div>
        <div class="text-right mt-3">
          <base-button
            :disabled="!this.password"
            class="primary"
            @click.prevent="uploadEmployees"
          >
            {{ "Upload Employees" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="previewLocationModal" class="user-modal modal-overflow">
      <h3 slot="header" class="title title-up text-primary">
        Preview Locations Data
      </h3>
      <form>
        <div class="col-sm-12 mt-2">
          <el-table
            class="table-striped"
            header-row-class-name="thead-light custom-thead-light"
            :data="returnedData.locations"
            style="width: 100%"
          >
            <el-table-column min-width="50" align="left" label="">
              <template slot-scope="props">
                <span>{{ props.$index + 1 }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="120" align="left" label="Location type">
              <template slot-scope="props">
                <span>{{ props.row.location_type }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="120" align="left" label="Location Name">
              <template slot-scope="props">
                <span>{{ props.row.location_name }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="110" align="left" label="# of employee">
              <template slot-scope="props">
                <span>{{ props.row.location_employee_count }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="100" align="left" label="Phone">
              <template slot-scope="props">
                <span>{{ props.row.telephone_number }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="120" align="left" label="Address">
              <template slot-scope="props">
                <span>{{ props.row.address }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="90" align="left" label="City">
              <template slot-scope="props">
                <span>{{ props.row.city }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="90" align="left" label="State">
              <template slot-scope="props">
                <span>{{ props.row.state }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="90" align="left" label="ZipCode">
              <template slot-scope="props">
                <span>{{ props.row.zipcode }}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="50" align="left" label="">
              <template slot-scope="props">
                <div class="d-flex custom-size">
                  <span
                    ><base-button
                      class="success"
                      type=""
                      size="sm"
                      icon
                      @click.prevent="
                        editLocationUploadedData(props.$index + 1)
                      "
                      ><i
                        class="text-default fa fa-pencil-square-o"
                      ></i></base-button
                  ></span>
                </div>
              </template>
            </el-table-column>
            <el-table-column min-width="50" align="left" label="">
              <template slot-scope="props">
                <div class="d-flex custom-size">
                  <span
                    ><base-button
                      class="danger"
                      type=""
                      size="sm"
                      icon
                      @click.prevent="deleteLocationUploadedData(props.$index)"
                      ><i class="text-default fa fa-trash"></i></base-button
                  ></span>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="text-right my-4" sr>
          <base-button class="primary" @click.prevent="uploadLocations">
            {{ "Upload Locations" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="previewUpdateModal" v-if="editdata" class="user-modal">
      <h3 class="mb-0" slot="header">Edit Imported Data</h3>
      <div
        v-for="(employee, index) in returnedData.employees"
        :key="employee.id"
      >
        <div v-if="editIndex === index + 1">
          <div class="row">
            <div class="col-md-12">
              <h4 class="text-primary mb-3">Editing Record {{ index + 1 }}</h4>
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
                class="mr-3"
                style="width: 100%"
                placeholder="Select Job Title"
                v-model="employee.employee_job_title"
              >
                <el-option
                  v-for="(option, index) in job_titles"
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
              <label class="form-control-label">User Type</label>
              <el-select
                class="mr-3"
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
            <div class="col-md-1">
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
                type="text"
                label="Zipcode"
                :name="'Zipcode'"
                placeholder="Zipcode"
                v-model="employee.zipcode"
              >
              </base-input>
            </div>
            <div class="col-md-12" style="text-align: right">
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
    <modal
      :show.sync="previewLocationUpdateModal"
      v-if="editdata"
      class="user-modal"
    >
      <h3 class="mb-0" slot="header">Edit Imported Data</h3>
      <div
        class="container"
        v-for="(location, index) in returnedData.locations"
        :key="location.id"
      >
        <div v-if="editIndex === index + 1">
          <div class="row">
            <div class="col-md-12">
              <h4 class="text-primary">Location {{ index + 1 }}</h4>
            </div>
          </div>
          <div class="row">
            <div class="col-md-2">
              <label class="form-control-label">Location Type</label>
              <el-select
                class="mr-3"
                style="width: 100%"
                placeholder="Select Locaation Type"
                v-model="location.location_type"
              >
                <el-option
                  v-for="(option, index) in location_types"
                  class="select-primary"
                  :name="'Location type'"
                  rules="required"
                  :value="option.value"
                  :label="option.label"
                  :key="'location_type' + index"
                >
                </el-option>
              </el-select>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="Location Name"
                :name="'Location name_' + index"
                rules="required"
                placeholder="Location Name"
                v-model="location.location_name"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="number"
                label="# of employee"
                name="# of employee"
                placeholder="# of employee"
                v-model="location.location_employee_count"
              >
              </base-input>
            </div>

            <div class="col-md-2">
              <base-input
                type="text"
                label="Phone Number"
                placeholder="Phone number"
                v-model="location.telephone_number"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="Address"
                :name="'Address'"
                placeholder="Address"
                v-model="location.address"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="City"
                :name="'City'"
                placeholder="City"
                v-model="location.city"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="text"
                label="State"
                :name="'State'"
                placeholder="State"
                v-model="location.state"
              >
              </base-input>
            </div>
            <div class="col-md-2">
              <base-input
                type="number"
                label="Zipcode"
                :name="'Zipcode'"
                placeholder="Zipcode"
                v-model="location.zipcode"
              >
              </base-input>
            </div>
            <div class="col-md-12" style="text-align: center">
              <button
                class="btn btn-warning"
                @click.prevent="updateLocationUploadedData(index, location)"
              >
                Update
              </button>
            </div>
          </div>
        </div>
      </div>
    </modal>
    <modal size="lg" :show.sync="showFoodMangerDataModal">
        <h4 slot="header" class="mb-0"><span style="font-size:18px;">{{company_name}}</span> &nbsp; Total Count: {{total_fm_count}} &nbsp; Used Count: {{used_fm_count}}</h4>
        <food-manager-details v-if="showFoodMangerDataModal" v-on:fm_data_fetched="fm_data_fetched" :company_id="company_id"></food-manager-details>
    </modal>
  </card>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import serverSidePaginationMixin from "../Tables/PaginatedTables/serverSidePaginationMixin";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import XLSX from "xlsx";
import VuePhoneNumberInput from "vue-phone-number-input";
import "vue-phone-number-input/dist/vue-phone-number-input.css";
import FoodManagerDetails from "@/views/Super/FoodManagerDetails.vue";
let timeout = null;
export default {
  name: "companies",
  mixins: [serverSidePaginationMixin],
  components: {
    VuePhoneNumberInput,
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    FoodManagerDetails
  },
  data() {
    return {
      loading: false,
      downlaodModel: false,
      importEmployeeModel: false,
      importLocationModel: false,
      askPasswordModal: false,
      previewModal: false,
      previewLocationModal: false,
      previewLocationUpdateModal: false,
      returnedData: [],
      editdata: false,
      previewUpdateModal: false,
      companyCourses: [],
      password: "",
      company_name: "",
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
      company_type: [
        {
          label: "Parent",
          value: "parent",
        },
        {
          label: "Child",
          value: "child",
        },
        {
          label: "All",
          value: "all",
        },
      ],
      searchQuery: "",
      excel_data: {
        employee_first_name: "",
        employee_last_name: "",
        employee_email: "",
        user_name: "",
        employee_job_title: "",
        usertype: "",
        phonenum: "",
        //assignedlocation: "",
        file: "",
      },
      active_companies: "",
      excel_location_data: {
        location_type: "",
        location_name: "",
        location_employee_count: "",
        telephone_number: "",
        address: "",
        city: "",
        state: "",
        zipcode: "",
        file: "",
      },
      job_titles: [],
      user_types: [
        {
          label: "Admin",
          value: "admin",
        },
        {
          label: "Manager",
          value: "manager",
        },
        {
          label: "Employee",
          value: "employee",
        },
      ],
      filters: {
        companyStatus: "Active",
        companiesType: "parent",
        course_id: "All Courses",
      },
      tableColumns: [
        {
          type: "selection",
        },
      ],
      selected_courses: [],
      tableData: [],
      selectedRows: [],
      location_types: [],
      hot_user: "",
      hot_token: "",
      editor: "",
      orderType: "",
      canCreate: true,
      canEdit: true,
      canDelete: true,
      showFoodMangerDataModal: false,
      total_fm_count: 0,
      used_fm_count: 0
    };
  },
  watch: {
    searchQuery: function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.fetchData();
      }, 300);
    },
  },
  created: function () {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    } else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
      this.getRightsDetails();
    } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "company";
    } else if (localStorage.getItem("hot-user") === "manager") {
      this.editor = "manager";
    }
    this.setDefaultFilterData();
  },
  methods: {
    getRightsDetails() {
      let type = "Company";
      this.$http.get("subadmin/subadmin_rights/" + type).then((resp) => {
        this.canCreate =
          resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit =
          resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete =
          resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
      });
    },

    askPassword() {
      this.askPasswordModal = true;
    },
    cancelAssignCourse() {
      this.assignCourseModal = false;
    },
    edituploadeddata(e) {
      this.$http.get("employees/jobTitles").then((resp) => {
        let jobtitle = resp.data;
        for (let data of jobtitle) {
          let obj = {
            value: data.id,
            label: data.name,
          };
          this.job_titles.push(obj);
        }
      });

      this.previewUpdateModal = true;
      this.editIndex = e;
      this.editdata = true;
    },
    editLocationUploadedData(e) {
      this.$http.post("company/company_dropdown_data").then((resp) => {
        let locationtype = resp.data.companytype;
        for (let data of locationtype) {
          let obj = {
            value: data.type,
            label: data.type,
          };
          this.location_types.push(obj);
        }
      });

      this.previewLocationUpdateModal = true;
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
        assignedlocation: employees.assignedlocation,
      };
      this.returnedData.employees.splice(e, obj);
      this.previewUpdateModal = false;
      this.editdata = false;
      Swal.fire({
        title: "Success!",
        text: `Updated Successfully!`,
        icon: "success",
      });
    },
    canceluploadLocation() {
      this.importLocationModel = false;
    },
    canceluploadEmployee() {
      this.importEmployeeModel = false;
    },
    updateLocationUploadedData(e, locations) {
      let obj = {
        location_type: locations.location_type,
        location_name: locations.location_name,
        location_employee_count: locations.location_employee_count,
        telephone_number: locations.telephone_number,
        address: locations.address,
        city: locations.city,
        state: locations.state,
        zipcode: locations.zipcode,
      };
      this.returnedData.locations.splice(e, obj);
      this.previewLocationUpdateModal = false;
      this.editdata = false;
      Swal.fire({
        title: "Success!",
        text: `Updated Successfully!`,
        icon: "success",
      });
    },
    deleteUploadedData(e) {
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
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          this.returnedData.employees.splice(e, 1);
        }
      });
    },
    deleteLocationUploadedData(e) {
      Swal.fire({
        title: "Are you sure?",
        text: "You want to remove this Location",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        cancelButtonText: "No",
        confirmButtonText: "Yes!",
        cancelButtonText: "No",
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          this.returnedData.locations.splice(e, 1);
        }
      });
    },
    getEmployeeFile(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.excel_data.file = file[0];
    },

    getLocationFile(e) {
      let file = e.target.files || e.dataTransfer.files;
      this.excel_location_data.file = file[0];
    },
    uploadEmployees() {
      this.returnedData.password = this.password;
      this.returnedData.selected_courses = this.selected_courses;
      this.loading = true;
      this.$http
        .post("employees/bulk_user", this.returnedData)
        .then((resp) => {
          if (resp.status === 202 && resp.data.success_count == 0) {
            Swal.fire({
              title: "Success: " + resp.data.success_count,
              html:
                "Failed: " +
                resp.data.message.split("</br>").length +
                "</br>" +
                '<ul style="text-align: left;">' +
                resp.data.message +
                "</ul>",
              icon: "error",
            });
          } else {
            this.items = resp.data.data;
            const data = XLSX.utils.json_to_sheet(this.items);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, data, "data");
            XLSX.writeFile(wb, this.company_name + ".xlsx");

            if (resp.status === 202) {
              if (resp.data.success_count > 0) {
                Swal.fire({
                  title: "Success: " + resp.data.success_count,
                  html:
                    "Failed: " +
                    resp.data.message.split("</br>").length +
                    "</br>" +
                    '<ul style="text-align: left;">' +
                    resp.data.message +
                    "</ul>",
                  icon: "success",
                });
              }
            } else {
              Swal.fire({
                title: "Success!",
                text: "Employees Uploaded Successfully",
                icon: "success",
              });
            }

            if (this.editor === "super-admin" || this.editor === "sub-admin") {
              this.$router.push(
                "/all_users?id=" + this.company_id + "&parent=false"
              );
            }
            if (this.editor === "company") {
              this.$router.push("/company_employees");
            }
          }
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          } else if (error.response.status === 500) {
            Swal.fire({
              title: "Error!",
              text: error.response.data.message,
              icon: "error",
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: "Invalid File data!",
              icon: "error",
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    uploadLocations() {
      this.loading = true;
      this.$http
        .post("location/bulk_location", this.returnedData)
        .then((resp) => {
          Swal.fire({
            title: "Success!",
            text: "Locations Uploaded Successfully",
            icon: "success",
          });
          this.previewLocationModal = false;
          if (this.editor === "super-admin" || this.editor === "sub-admin") {
            this.$router.push("/all_companies");
          }
          if (this.editor === "company") {
            this.$router.push("/companies");
          }
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
              text: "Invalid File data!",
              icon: "error",
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    uploadUser() {
      this.loading = true;
      if (this.excel_data.file !== "") {
        let formData = new FormData();
        formData.append("quizFile", this.excel_data.file);
        formData.append("file", "employee");
        let employeeData = {
          password: "",
          selected_courses: [],
          employees: [],
          company_id: this.company_id,
        };
        this.employee_data = [];
        this.$http
          .post("course/read_file", formData, {})
          .then((resp) => {
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
                employee_job_title: employee[2],
                // assignedlocation: employee[2]
              };
              if (index !== 0) {
                employeeData.employees.push(obj);
              }
              index++;
            }
            this.returnedData = employeeData;
            this.$http
              .get("company/all_courses/" + this.company_id)
              .then((resp) => {
                this.companyCourses = [];
                let fobj = {
                  label: "All Courses",
                  value: "All Courses",
                };
                this.companyCourses.push(fobj);
                for (let data of resp.data[0].courses) {
                  let obj = {
                    label: data.name,
                    value: data.course_id,
                  };
                  this.companyCourses.push(obj);
                }
              });
            this.previewModal = true;
          })
          .catch(function (error) {
            Swal.fire({
              title: "Error!",
              text: "Invalid File data!",
              icon: "error",
            });
          })
          .finally(() => (this.loading = false));
      } else {
        this.loading = false;
        Swal.fire({
          title: "Error!",
          text: `Please Select any File!`,
          icon: "error",
        });
      }
    },
    uploadLocation() {
      this.loading = true;
      if (this.excel_location_data.file !== "") {
        let formData = new FormData();
        formData.append("quizFile", this.excel_location_data.file);
        formData.append("file", "location");
        let locationData = {
          locations: [],
          company_id: this.company_id,
        };
        this.location_data = [];
        this.$http
          .post("course/read_file", formData, {})
          .then((resp) => {
            let data = resp.data[0];
            let index = 0;
            for (let location of data) {
              let obj = {
                location_type: location[0],
                location_name: location[1],
                location_employee_count: location[2],
                telephone_number: location[3],
                address: location[4],
                city: location[5],
                state: location[6],
                zipcode: location[7],
              };
              if (index !== 0) {
                locationData.locations.push(obj);
              }
              index++;
            }
            this.returnedData = locationData;
            this.previewLocationModal = true;
          })
          .catch(function (error) {
            Swal.fire({
              title: "Error!",
              text: "Invalid File data!",
              icon: "error",
            });
          })
          .finally(() => (this.loading = false));
      } else {
        this.loading = false;
        Swal.fire({
          title: "Error!",
          text: `Please Select any File!`,
          icon: "error",
        });
      }
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
          course_id: this.filters.course_id,
        })
        .then((resp) => {
          this.items = resp.data;
          const data = XLSX.utils.json_to_sheet(this.items);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, data, "data");
          XLSX.writeFile(
            wb,
            this.company_name + "-" + this.report_type + ".xlsx"
          );
        })
        .catch(function (error) {
          if (error.response.status === 422) {
            let respmessage = error.response.data.message;
            Swal.fire({
              title: "Error!",
              text: respmessage,
              icon: "error",
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    openCreateCompany() {
      this.$router.push("/create_company");
    },

    routeEmployee(index, row, onlyParent) {
      this.$router.push("/all_users?id=" + row.id + "&parent=" + onlyParent);
    },
    routeLocations(index, row) {
      this.$router.push("/company_locations?id=" + row.id);
    },
    routeCourses(index, row) {
      this.$router.push("/courses?id=" + row.id);
    },
    downloadUserList(index, row) {
      this.company_id = row.id;
      this.company_name = row.company_name;
      this.report_type = "all_user";
      this.$http
        .post("company/users", {
          report_type: this.report_type,
          company_id: this.company_id,
        })
        .then((resp) => {
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
    importUsers(index, row) {
      this.company_id = row.id;
      this.company_name = row.company_name;
      this.importEmployeeModel = true;
    },
    importChildCompanies(index, row) {
      this.company_id = row.id;
      this.company_name = row.company_name;
      this.importLocationModel = true;
    },
    handleDownload(index, row) {
      this.company_id = row.id;
      this.company_name = row.company_name;
      this.$http.get("company/all_courses/" + this.company_id).then((resp) => {
        this.companyCourses = [];
        let fobj = {
          label: "All Courses",
          value: "All Courses",
        };
        this.companyCourses.push(fobj);
        for (let data of resp.data[0].courses) {
          let obj = {
            label: data.name,
            value: data.course_id,
          };
          this.companyCourses.push(obj);
        }
      });
      this.downlaodModel = true;
    },
    handleEdit(index, row) {
      this.$router.push("/edit_company?id=" + row.id);
    },
    deleteBulkCompanies() {
      Swal.fire({
        title: "Are you sure?",
        text: "You want to Delete?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
      }).then((result) => {
        if (result.value) {
          this.loading = true;
          this.$http
            .post("company/delete_companies", {
              companies: this.selectedRows,
            })
            .then((resp) => {
              Swal.fire({
                title: "Company",
                text: "Deleted Successfully.",
                icon: "success",
              }).then((result) => {
                if (result.value) {
                  this.fetchData();
                }
              });
            })
            .finally(() => (this.loading = false));
        }
      });
    },
    selectionChange(selectedRowss) {
      this.selectedRows = [];
      for (let selectedRow of selectedRowss) {
        if (this.selectedRows.includes(selectedRow.id)) {
          this.selectedRows.splice(
            this.selectedRows.indexOf(selectedRow.id),
            1
          );
        } else {
          this.selectedRows.push(selectedRow.id);
        }
      }
    },
    adminStates() {
      this.$http.get("company/admin_stats").then((resp) => {
        this.active_courses = resp.data.courses;
        this.active_companies = resp.data.companies;
        this.active_employees = resp.data.employees;
      });
    },
    fetchData() {
      this.loading = true;
      this.$http
        .post("company/all_companies", {
          search: this.searchQuery,
          company_status: this.filters.companyStatus,
          company_type: this.filters.companiesType,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
        })
        .then((resp) => {
          this.tableData = [];
          let comp_data = resp.data.companies;
          let total_data = resp.data.total;
          this.totalData = total_data;
          for (let data of comp_data) {
            let obj = {
              id: data.id,
              company_name: data.name,
              location: data.locations_count,
              employees: data.employees_count,
              total_employees: "",
              assigned_courses: data.courses_count,
              company_admin: data.admin,
              total_fm_count:data.fm_certificate_count,
              used_fm_count:(data.food_manager_pass_count+data.food_manager_open_count+data.food_manager_fail_count+data.food_manager_expired_count),
              food_manager_count:
                data.food_manager_pass_count +
                "/" +
                data.food_manager_open_count +
                "/" +
                data.food_manager_fail_count +
                "/" + 
                data.food_manager_expired_count,
              status: true,
            };
            if (data.total_employees_count) {
              obj.total_employees = data.total_employees_count;
            }
            if (data.status) {
              obj.status = true;
            } else {
              obj.status = false;
            }
            this.tableData.push(obj);
          }
        })
        .finally(() => (this.loading = false));
      this.saveSearchData();
    },
    resetFilters() {
      this.filters.company_id = "";
      this.filters.companyStatus = "Active";
      this.filters.companiesType = "parent";
      this.searchQuery = "";
      this.fetchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_company_search_data",
        JSON.stringify({
          role: "super-admin",
          search: this.searchQuery,
          company_status: this.filters.companyStatus,
          company_type: this.filters.companiesType,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage,
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_company_search_data")
      );
      if (previousStateData !== null) {
        this.searchQuery = previousStateData.search
          ? previousStateData.search
          : this.searchQuery;
        this.filters.companyStatus = previousStateData.company_status
          ? previousStateData.company_status
          : this.filters.companyStatus;
        this.filters.companiesType = previousStateData.company_type
          ? previousStateData.company_type
          : this.filters.companiesType;
        this.currentPage = previousStateData.page
          ? previousStateData.page
          : this.currentPage;
        this.sortedColumn = previousStateData.column
          ? previousStateData.column
          : this.sortedColumn;
        this.order = previousStateData.order
          ? previousStateData.order
          : this.order;
        this.perPage = previousStateData.per_page
          ? previousStateData.per_page
          : this.perPage;
      }
      this.fetchData();
    },
    changeStatus(index, row) {
      let prev_val = row.status;
      let status = "";
      if (prev_val) {
        status = 0;
      } else {
        status = 1;
      }
      Swal.fire({
        title: "Are you sure?",
        text: "You want to change status?",
        icon: "warning",
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
      })
        .then((result) => {
          if (result.value) {
            this.$http
              .put("/company/change_status/" + row.id, {
                status: status,
              })
              .then((resp) => {
                this.fetchData();
                this.adminStates();
                Swal.fire({
                  title: "Success!",
                  text: "Status has been Changed.",
                  type: "success",
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false,
                });
                this.tableData[index].status = !prev_val;
              });
          } else {
            this.tableData[index].status = prev_val;
          }
        })
        .catch(function () {
          this.tableData[index].status = prev_val;
        })
        .finally(() => (this.loading = false));
    },
      // food manager

    showFoodManagerDetails(row){
      this.company_name = row.company_name;
      this.total_fm_count = row.total_fm_count;
      this.used_fm_count = row.used_fm_count
      this.company_id=row.id;
      this.showFoodMangerDataModal=true;
    },

    fm_data_fetched(fm_used_count){
     this.used_fm_count=fm_used_count;
    }
  },
};
</script>
<style scoped>
.no-border-card .card-footer {
  border-top: 0;
}
.input-tel__input {
  caret-color: dodgerblue;
  display: block;
  width: 100%;
  height: calc(1.5em + 1.25rem + 2px);
  padding: 0.625rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.5;
  color: rgb(136, 152, 170);
  background-color: rgb(255, 255, 255);
  background-clip: padding-box;
  border-width: 1px;
  border-style: solid;
  border-image: initial;
  border-radius: 0.25rem;
  transition: all 0.15s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0s;
  padding-top: 0 !important;
}

@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .compGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Check";
  }
  .compGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Company Name" !important;
  }
  .compGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "# Location";
  }
  .compGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "# Employee";
  }
  .compGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "# Courses";
  }
  .compGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Status";
  }
  .compGrid >>> table.el-table__body td:nth-of-type(7):before {
    content: "Actions";
  }
}
</style>
