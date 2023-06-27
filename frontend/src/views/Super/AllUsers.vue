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
              <div class="col-md-6 text-left">
                <h2 class="mb-0">Users</h2>
              </div>

              <div class="col-md-6 text-right">
                <base-button class="custom-btn" v-on:click="resetFilters()"
                  ><i class="fa fa-refresh" aria-hidden="true"></i> Clear
                  Filters</base-button
                >

                <base-button class="custom-btn" v-if="canCreate" @click.prevent="createuser"
                  ><i class="fa fa-plus" aria-hidden="true"></i> Add
                  User</base-button
                >
              </div>
            </div>
          </template>
          <div>
            <div
              class="row d-flex justify-content-center justify-content-sm-between flex-wrap"
            >
              <div class="col-md-5">
                <base-input
                  v-debounce:2s.unlock="fetchData"
                  label="Search:"
                  v-model="searchQuery"
                  prepend-icon="fas fa-search"
                  placeholder="Search..."
                >
                </base-input>
              </div>

              <div class="col-md-2 col-6">
                <label class="form-control-label">Type: </label>&nbsp;
                <el-select
                  class="select-primary w-100"
                  v-model="filters.userfilterType"
                  v-on:change="changePage(1)"
                  placeholder="Filter"
                >
                  <el-option
                    class="select-primary"
                    v-for="item in filterType"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>

              <div
                class="col-md-3 col-6 form-group "
                style="margin-left:0px !important;"
              >
                <el-popover
                  ref="fromPopOver"
                  placement="top-start"
                  min-width="250"
                  trigger="hover"
                >
                  <span>
                    <ul>
                      <li>Filter company</li>
                      <li>
                        Choose one or more employees to assign/unassign multiple
                        courses.
                      </li>
                    </ul>
                  </span>
                </el-popover>

                <label class="form-control-label"
                  >Company:
                  <i
                    v-popover:fromPopOver
                    class="fa fa-info-circle text-blue"
                  />
                </label>
                <el-select
                  class="select-primary"
                  v-model="filters.company_id"
                  v-on:change="changePage(1)"
                  placeholder="Select Company"
                  filterable
                >
                  <el-option
                    class="select-primary"
                    v-for="item in companies"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                    :parent_id="item.parent_id"
                  >
                  </el-option>
                </el-select>
              </div>

              <div
                class="col-md-2  form-group "
                style="margin-left:0px !important;"
              >
                <label class="form-control-label">Status:</label>
                <el-select
                  class="select-primary"
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
              class="row justify-content-md-center justify-content-md-between flex-wrap"
            >
              <div
                class="col-md-2 col-6 form-group"
                style="margin-left:0px !important;"
                v-if="selectedRows.length > 0"
              >
                <base-input label="Bulk Action: ">
                  <el-select
                    class="select-primary"
                    v-model="bulkValue"
                    v-on:change="bulkClicked()"
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
                    <span
                      v-if="
                        filters.company_id ||
                          filters.userfilterType == 'individual'
                      "
                    >
                      <el-option
                        class="select-primary"
                        v-for="item in bulk_assign_array"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      >
                      </el-option>
                    </span>
                  </el-select>
                </base-input>
              </div>

              <div class="col-md-2 col-6 form-group">
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
            <div class="row">
              <div class="col-md-4 mb-2">
                <el-popover placement="bottom-start" trigger="click">
                  <el-checkbox-group
                    v-model="filterValue"
                    @change="changePage(1)"
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
              <!-- test  -->
              <el-table
                ref="filterTable"
                role="table"
                :data="tableData"
                stripe
                highlight-current-row
                lazy
                row-key="id"
                header-row-class-name="thead-light"
                @selection-change="selectionChange"
                class="usersGrid table-striped"
                id="tableOne"
              >
                <!-- :filters="filtersOptions"
                  :filter-method="filterHandler" -->
                <el-table-column type="selection"> </el-table-column>

                <el-table-column min-width="120px" prop="first_name">
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
                    {{ props.row.first_name }}
                  </template>
                </el-table-column>
                <el-table-column min-width="120px" prop="last_name">
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
                    {{ props.row.last_name }}
                  </template>
                </el-table-column>
                <el-table-column min-width="200px" prop="company">
                  <template slot="header">
                    <span @click="sortByColumn(2)"
                      >Company
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
                    <a
                      style="cursor:pointer; color:#00b8ff;"
                      v-on:click="setCompany(props.row.company_id)"
                      >{{ props.row.company }}</a
                    >
                  </template>
                </el-table-column>
                <el-table-column min-width="100px" prop="user_type">
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
                    <span v-if="props.row.type == ('employee' || 'Employee')">
                      <a
                        style="cursor:pointer; color:#00b8ff;"
                        v-on:click="filterUserType(4)"
                        >Employee</a
                      ></span
                    >
                    <span
                      v-if="props.row.type == ('location_manager' || 'Manager')"
                    >
                      <a
                        style="cursor:pointer; color:#00b8ff;"
                        v-on:click="filterUserType(3)"
                        >Manager</a
                      ></span
                    >
                    <span v-if="props.row.type == ('admin' || 'Admin')">
                      <a
                        style="cursor:pointer; color:#00b8ff;"
                        v-on:click="filterUserType(2)"
                        >Admin</a
                      ></span
                    >
                    <span
                      v-if="props.row.type == ('individual' || 'Individual')"
                    >
                      <a
                        style="cursor:pointer; color:#00b8ff;"
                        v-on:click="filterUserType(4)"
                        >Individual</a
                      ></span
                    >
                     <span
                      v-if="props.row.type == ('sub-admin')"
                    >
                      <a
                        style="cursor:pointer; color:#00b8ff;"
                        v-on:click="filterUserType(5)"
                        >Sub-admin</a
                      ></span
                    >
                  </template>
                </el-table-column>
                <el-table-column min-width="110px" prop="">
                  <template slot="header">
                    <el-popover
                      ref="fromPopOver"
                      placement="top-start"
                      width="250"
                      trigger="hover"
                    >
                      <span style="display: flex; justify-content: center;">
                        P = Passed. This user is compliant.<br /><br />O = Open.
                        This user has open <br />courses, but is also
                        compliant.<br /><br />F = Failed. This user is
                        non-compliant because they did not pass the assigned
                        course in the time permitted.
                      </span>
                    </el-popover>
                    <span @click="sortByColumn(4)"
                      >P/O/F
                      <i
                        v-popover:fromPopOver
                        class="el-icon-question
                    text-blue"
                      />&nbsp;
                      <i
                        v-if="sortedColumn == 4 && order === 'asc'"
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
                  min-width="110px"
                  label="Status"
                  prop="status"
                  align=""
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
                <el-table-column min-width="150px" label="Actions">
                  <div slot-scope="{ $index, row }" class="d-flex custom-size">
                    <el-tooltip content="Edit" placement="top">
                      <base-button
                        v-if="canEdit"
                        @click.native="handleEdit($index, row)"
                        class="edit"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Edit"
                      >
                        <i class="text-default fas fa-pencil-square-o"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip content="Upload" placement="top">
                      <base-button
                        @click.native="handleUpload($index, row)"
                        class="upload"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Upload"
                      >
                        <i class="text-primary ni ni-cloud-upload-96"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip
                      content="Email"
                      placement="top"
                      v-if="row.email"
                    >
                      <base-button
                        @click.native="handleEnvelope($index, row)"
                        class="email"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Email"
                      >
                        <i class="text-warning ni ni-email-83"></i>
                      </base-button>
                    </el-tooltip>
                    <el-tooltip content="Delete" placement="top">
                      <base-button
                        v-if="canDelete"
                        @click.native="handleDelete($index, row)"
                        class="delete"
                        type=""
                        size="sm"
                        icon
                        data-toggle="tooltip"
                        data-original-title="Delete"
                      >
                        <i class="text-danger fas fa-trash"></i>
                      </base-button>
                    </el-tooltip>
                  </div>
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
      <span slot="header" class="title title-up text-primary assign-coures">
      
        {{ employeeCoursesData.first_name }}
        {{ employeeCoursesData.last_name }}'s Assigned Courses

        {{ employeeCoursesData.type=='admin' ? "(Company Administrator)" 
        : employeeCoursesData.type=='employee' ? '(Employee)' 
        : employeeCoursesData.type=='individual' ? "(Individual)" 
        : employeeCoursesData.type=='location_manager' ? "(Manager)"
        : " " }}
       <span >
         <base-button
          class="ml-5"
        
          @click.prevent="
            downloadBulkCertificate(course_employee_id)
          "
        >
          Download Certifcate
        </base-button>
        
        <base-button
          v-if="employeeCoursesData.type != 'individual'"
         
          @click.prevent="showAssigncourse(course_employee_id, ($interface = 'course'))"
        >
          Assign course
        </base-button>

        <base-button
          v-if="employeeCoursesData.type != 'individual'"
          @click.prevent="
            showAssigncourse(course_employee_id, ($interface = 'folder'))
          "
        >
          Assign Course Folder
        </base-button>
       </span>
      </span>

      <form>
        <div class="table-responsive table-full-width user-eltable">
          <el-table
            :data="employeeCoursesData.courseData"
            header-row-class-name="thead-light"
            role="table"
            class="pofGrid formatTable"
          >
            <el-table-column
              label="Lesson name"
              property=""
              min-width="160px"
              align="left"
            >
              <template slot-scope="props">
                <span>{{ props.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="Lesson Status"
              property=""
              min-width="160px"
            >
              <template slot-scope="props">
                <span v-if="props.row.employee_course_status === 3"
                  >Expired</span
                >
                <span v-if="props.row.employee_course_status === 2">Open</span>
                <span v-if="props.row.employee_course_status === 1">Pass</span>
                <span v-if="props.row.employee_course_status === 0">Fail</span>
              </template>
            </el-table-column>
            <el-table-column
              label="Date Completed"
              property=""
              min-width="160px"
              align="left"
            >
              <template slot-scope="props">
                <span
                  v-if="
                    props.row.employee_course_date_completed === '' ||
                      props.row.employee_course_date_completed === null
                  "
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
              min-width="160px"
            >
              <template slot-scope="props">
                <span>{{
                  formattedDate(props.row.employee_course_due_date)
                }}</span>
              </template>
            </el-table-column>
             <el-table-column
              label="Expiration Date"
              prop="expiration_date"
              align="left"
              min-width="160px"
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
            >
              <div slot-scope="{ $index, row }" class="d-flex custom-size">
                <el-tooltip v-if="canEdit" content="Edit" placement="top">
                  <base-button
                    type=""
                    size="sm"
                    @click="
                      editCourseStatus(
                        $index,
                        row,
                        course_employee_id,
                        employeeCoursesData.type
                      )
                    "
                    data-toggle="tooltip"
                    data-original-title="Edit"
                  >
                    <i class="text-success fa fa-pencil-square-o"></i>
                  </base-button>
                </el-tooltip>
                <el-tooltip
                  content="Delete"
                  placement="top"
                  v-if="row.employee_course_status != 1"
                >
                  <base-button
                    type=""
                    size="sm"
                    @click="unAssignedCourse($index, row)"
                    data-toggle="tooltip"
                    data-original-title="Delete"
                  >
                    <i class="text-danger fas fa-trash"></i>
                  </base-button>
                </el-tooltip>
                <el-tooltip
                  content="Preview"
                  placement="top"
                  v-if="

                    row.employee_course_status === 1 &&
                      row.is_food_certifcate === 1
                  "
                >
                  <a
                    class="linkColor"
                    @click.prevent="
                      getProctoredExamCertificate(row.food_safe_certificate_url)
                    "
                    data-original-title="Preview"
                    data-toggle="tooltip"
                  >
                    <span>
                      <i class="text-success fa fa-eye"></i>
                    </span>
                  </a>
                </el-tooltip>
                <el-tooltip
                  content="Preview"
                  placement="top"
                  v-else-if="
                   !row.is_coursefolder &&
                    row.employee_course_status === 1 &&
                      row.is_food_certifcate === 0
                  "
                >
                  <a
                    :href="
                      baseUrl +
                        '/downloadCourseCertificate/preview/' +
                        row.id +
                        '/' +
                        course_employee_id +
                        '/' +
                        row.employee_certificate_id + 
                        '/' + row.is_coursefolder
                    "
                    data-toggle="tooltip"
                    data-original-title="Preview"
                    target="_blank"
                  >
                    <span> <i class="ml-1 text-success fas fa-eye"></i> </span
                  ></a>
                </el-tooltip>
              </div>
            </el-table-column>
          </el-table>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="showUploadModel">
      <h3 slot="header" class="mb-0">
        {{ employeeCoursesData.first_name }}
        {{ employeeCoursesData.last_name }}'s Document Upload
      </h3>
      <form @submit="uploadDocument" enctype="multipart/form-data">
        <br />
        <div class="form-row">
          <div class="col-md-6">
            <base-input
              label="Document Title *"
              name="Document Title"
              required
              placeholder="Title"
              v-model="document_title"
            >
            </base-input>
          </div>
          <div class="col-md-6">
            <base-input
              label="Document Description"
              placeholder="Document Description"
              v-model="document_description"
            >
            </base-input>
          </div>
          <div class="col-md-12">
            <base-input label="Document Type *">
              <el-select
                required
                class="select-primary"
                name="Document Type"
                v-model="document_type"
                placeholder="Select type"
              >
                <el-option
                  class="select-primary"
                  v-for="item in documentType"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                >
                </el-option>
              </el-select>
            </base-input>
          </div>
          <div class="col-md-12" v-if="document_type == 'link'">
            <base-input
              label="Document Url (url should contain http:// or https://) *"
              name="Document Link"
              v-on:blur="validUrl(document_url)"
              placeholder="Document Url"
              v-model="document_url"
            >
            </base-input>
          </div>
          <div class="col-md-12" v-if="document_type == 'file'">
            <label>Document *</label>
            <form>
              <file-input v-on:change="onImageChange"></file-input>
            </form>
          </div>
          <div class="col-md-12" style="margin-top:10px;">
            <base-button
              @click.native="uploadDocument"
              class="uplaod"
              type="success"
              size="md"
              icon
            >
              Upload
            </base-button>
          </div>
        </div>
      </form>

      <br />
      <div class="form-row">
        <div class="col-md-12">
          <h3 slot="header" class="mb-0">Uploaded Documents</h3>
          <br />
        </div>
        <div class="col-md-12">
          <div class="user-eltable">
            <el-table
              row-key="id"
              role="table"
              class="table-responsive align-items-center table-flush table-flush table-striped documentGrid"
              header-row-class-name="thead-light custom-thead-light"
              :data="tableData2"
            >
              <el-table-column label="Document Title" prop="" min-width="140px">
                <template slot-scope="props">
                  {{ props.row.document_name }}
                </template>
              </el-table-column>
              <el-table-column min-width="200px" label="Actions">
                <div slot-scope="{ row }" class="d-flex">
                  <a
                    v-if="row.document_type == 'file'"
                    :href="baseUrl + '/employee/documents/' + row.document"
                    target="_blank"
                  >
                    <base-button type="" size="sm" icon>
                      <i class="text-primary fa fa-eye"></i>
                    </base-button>
                  </a>

                  <a
                    v-if="row.document_type == 'link'"
                    :href="row.url"
                    target="_blank"
                  >
                    <base-button type="" size="sm" icon>
                      <i class="text-primary fa fa-eye"></i>
                    </base-button>
                  </a>
                  <base-button
                    @click.native="deleteDocument(row.id)"
                    type=""
                    size="sm"
                    icon
                  >
                    <i class="text-danger fa fa-trash"></i>
                  </base-button>
                </div>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
    </modal>
    <modal :show.sync="employeeEmailModal">
      <h3 slot="header" class="title title-up text-primary text-center">
        Send Reminder Email to {{ employeeEmailData.first_name }}
        {{ employeeEmailData.last_name }}
      </h3>
      <form>
        <div class="text-center my-4">
          <div class="row">
            <div class="col-sm-6">
              <base-button
                @click.native="sendReminderEmail"
                class="edit"
                size="sm"
                icon
              >
                Course Due Reminder Email
              </base-button>
            </div>
            <div class="col-sm-6">
              <base-button
                @click.native="sendExpiredReminderEmail"
                class="edit"
                type="danger"
                size="sm"
                icon
              >
                Course Expired Reminder Email
              </base-button>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-sm-6">
              <base-button
                @click.native="sendWelcomeEmail"
                class="edit"
                type="primary"
                size="sm"
                icon
              >
                Resend Welcome Email
              </base-button>
            </div>
            <div class="col-sm-6">
              <base-button
                @click.native="sendPasswordResetEmail"
                class="edit"
                type="success"
                size="sm"
                icon
              >
                Reset Password Email
              </base-button>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal
      :show.sync="askUpdateOption"
      v-on:close="onAskUpdateOptionClose"
      class="user-modal"
    >
      <h3 slot="header" class="title title-up text-primary">
        Choose Option
      </h3>
      <div class="row">
        <div class="col-md-6">
          <input
            type="radio"
            name="updateoption"
            v-model="updateOption"
            v-on:change="updateOptionChange"
            value="date_assigned"
          />
          Assigned / Due Date <br />
        </div>
        <div class="col-md-6">
          <input
            type="radio"
            name="updateoption"
            v-model="updateOption"
            v-on:change="updateOptionChange"
            value="manually"
          />
          Manually Pass / Fail
          <br />
        </div>
      </div>

      <span v-if="showAssigndate">
        <div class="row mt-4">
          <div class="col-md-3">
            <base-input label="Assign Date: ">
              <el-date-picker
                v-on:input="changeDueDate()"
                v-model="assign_date"
                placeholder="Pick a day"
                style="width: 100%"
                format="MM/dd/yyyy"
                :picker-options="pickerOptions"
              >
              </el-date-picker>
            </base-input>
          </div>
          <div class="col-md-3">
            <base-input label="Due Date: ">
              <el-date-picker
                v-on:input="changeAssignDate()"
                v-model="due_date"
                placeholder="Pick a day"
                style="width: 100%"
                format="MM/dd/yyyy"
                :picker-options="pickerOptions"
              >
              </el-date-picker>
            </base-input>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button type="primary" @click.prevent="updateCourse">
            Update
          </base-button>
        </div>
      </span>
    </modal>
    <modal
      :show.sync="openCousreEditModel"
      v-on:close="onOpenCousreEditModelClose"
    >
      <h3 slot="header" class="title title-up text-primary">
        Update Course Status for <u>{{ course_name }}</u>
      </h3>
      <form>
        <div class="row">
          <div class="col-md-6">
            <b>Status : </b>
            <br />
            <el-select v-model="changed_status" rules="required">
              <el-option
                v-for="item in changestatus"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="row mt-4" v-if="changed_status == 1">
          <div class="col-md-12 mt-2" v-if="certificate_availablity">
            <input type="checkbox" v-model="generateCertificate" /> Generate
            Certificate Automatically
          </div>
          <div class="col-md-12 mt-2" v-else>
            <h5>
              This course does not have certificate available. Upload
              certificate manually.
            </h5>
          </div>
          <br />
          <div class="col-md-6">
            <b>Completed Date :</b>
            <el-date-picker
              v-model="completed_date"
              placeholder="Pick a day"
              style="width: 100%"
              format="MM/dd/yyyy"
              :picker-options="pickerOptions"
            >
            </el-date-picker>
          </div>
          <div class="col-md-6" v-if="!generateCertificate">
            <b>Expiration Date:</b>
            <el-date-picker
              v-model="expiration_date"
              placeholder="Pick a day"
              style="width: 100%"
              format="MM/dd/yyyy"
              :picker-options="pickerOptions1"
            >
            </el-date-picker>
          </div>
          <div class="col-md-6" v-if="!generateCertificate">
            <b>Upload Certificate :</b>
            <file-input v-on:change="onImageChange"></file-input>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button
            type="primary"
            :disabled="changed_status == 2"
            @click.prevent="updateCourse"
          >
            Update
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="employeeAssignCourseModel">
      <h3 slot="header" class="title title-up text-primary">Assign Course</h3>
      <form>
        <div class="row">
          <div class="col-md-12 text-right">
            <base-button
              :disabled="!filters.company_id || !filters.course_id.length > 0"
              @click.prevent="assignCourse">
              Assign Course(s)
            </base-button>
          </div>
          <div class="col-md-12">
            <base-input label="Choose Company:">
            <el-select
              class="company_dropdown2"
              style="width: 100%;"
              v-model="filters.company_id"
              v-on:input="getCompanyCourses($event)"
              placeholder="Select Company"
            >
              <el-option
                class="select-default"
                v-for="item in company"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
            </base-input>
          </div>
          <div class="col-md-12">
            <base-input label="Choose Courses:">
            <el-select
              style="width: 100%;"
              v-model="filters.course_id"
              v-on:change="fetchData()"
              placeholder="Select Courses"
              multiple
              filterable
            >
              <el-option
                class="select-default"
                v-for="item in courses"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
            </base-input>
          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
      <modal :show.sync="employeeAssignCourseFolderModel">
      <h3 slot="header" class="title title-up text-primary">
        Assign Course Folder
      </h3>
      <form>
        <div class="row">
            <div class="col-md-12 text-right">
          <base-button
            :disabled="!filters.company_id || !filters.folder_id.length > 0"
            @click.prevent="assignCourseFolder"
          >
            Assign Course Folder(s)
          </base-button>
        </div>
          <div class="col-md-12">
             <base-input label="Choose Company:">
            <el-select
              class="company_dropdown2"
              style="width: 100%;"
              v-model="filters.company_id"
              v-on:input="getCompanyCourseFolders($event)"
              placeholder="Select Company"
            >
              <el-option
                class="select-default"
                v-for="item in company"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
             </base-input>
          </div>
          <div class="col-md-12">
             <base-input label="Choose Course Folders:">
            <el-select
              style="width: 100%;"
              v-model="filters.folder_id"
              v-on:change="fetchData()"
              placeholder="Select Course Folders"
              multiple
              filterable
            >
              <el-option
                class="select-default"
                v-for="item in coursefolders"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
             </base-input>
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
              class="select-default"
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
          <button
            type="submit"
            class="btn btn-warning btn-wd"
            @click.prevent="assignBulkLocation"
          >
            Assign Location
          </button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
       <modal :show.sync="courseManuallyPassModal" v-on:close="closeMassUpdateCourse">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Manually Update Course
      </h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <label class="form-control-label">Select any Course: </label>
          </div>
          <div class="col-md-12">
            <el-select
              fiterable
              v-model="filters.course_id"
              class="course_dropdown"
              name="course"
              style="width:100%"
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
          </div>

          <div class="col-md-6">
            <label class="form-control-label">Status : </label>
            <br />
            <el-select v-model="changed_status" rules="required">
              <el-option
                v-for="item in changestatus"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="row mt-4" v-if="changed_status==1">
          <div class="col-md-12 mt-2" >
            <input type="checkbox" v-model="generateCertificate" /> Generate
            Certificate Automatically
          </div>
          
          <br />
          <div class="col-md-6">
            <label class="form-control-label">Completed Date :</label>
            <el-date-picker
              v-model="completed_date"
              placeholder="Pick a day"
              style="width: 100%"
              format="MM/dd/yyyy"
              :picker-options="pickerOptions"
            >
            </el-date-picker>
          </div>
          <div class="col-md-6" v-if="!generateCertificate">
            <label class="form-control-label">Expiration Date:</label>
            <el-date-picker
              v-model="expiration_date"
              placeholder="Pick a day"
              style="width: 100%"
              format="MM/dd/yyyy"
              :picker-options="pickerOptions1"
            >
            </el-date-picker>
          </div>
          <div class="col-md-6" v-if="!generateCertificate">
            <label class="form-control-label">Upload Certificate :</label>
            <file-input v-on:change="onImageChange"></file-input>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button
            type="danger"
            @click.prevent="closeMassUpdateCourse"
          >
            Cancel
          </base-button>
          <base-button type="success" @click.prevent="manuallyUpdateCourse">
            {{ "Update Course" }}
          </base-button>
        </div>
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="courseAssigneeModal" v-on:close="closeAssignModal">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Assign Course
      </h3>
      <form>
        <div class="row">
          <div class="col-md-12 text-right">
            <base-button @click.prevent="assignCourse">
              {{ "Assign Course" }}
            </base-button>
          </div>
          <div class="col-md-12">
            <label>Select any Course to Assign: </label>
          </div>
           <div class="col-md-12">
            <el-select
              multiple
              fiterable
              style="width:100%"
              v-model="filters.course_id"
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
          </div>
        </div>
       
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="courseUnassigneeModal" v-on:close="closeAssignModal">
      <h3 slot="header">Unassign Course</h3>
      <form>
        <div class="row">
            <div class="col-md-12 text-right">
          <base-button
            type="danger"
            @click.prevent="courseUnassigneeModal = false"
          >
            Cancel
          </base-button>
          <base-button  @click.prevent="unassignCourse">
            {{ unassigning ? "Processing" : "Unassign Course" }}
          </base-button>
        </div>
          <div class="col-md-12">
            <base-input label="Select Course Unassign: ">
            <el-select
              multiple
              filterable
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
      </form>
    </modal>
    <modal :show.sync="courseFolderAssigneeModal" v-on:close="closeAssignModal">
      <h3 slot="header" style="color: #444c57" class="title title-up">
        Assign Course Folder
      </h3>
      <form>
        <div class="row">
          <div class="col-md-12 text-right">
            <base-button @click.prevent="assignCourseFolder">
              {{ "Assign Course Folder" }}
            </base-button>
          </div>
          <div class="col-md-12">
            <label>Select any Course Folder to Assign: </label>
          </div>
           <div class="col-md-12">
            <el-select
              multiple
              fiterable
              style="width:100%"
              v-model="filters.folder_id"
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
       
        <div class="clearfix"></div>
      </form>
    </modal>
    <modal :show.sync="courseFolderUnassigneeModal" v-on:close="closeAssignModal">
      <h3 slot="header">Unassign Course Folder</h3>
      <form>
        <div class="row">
           <div class="col-md-12 text-right">
          <base-button
            type="danger"
            @click.prevent="courseUnassigneeModal = false"
          >
            Cancel
          </base-button>
          <base-button  @click.prevent="unassignCourseFolder">
            {{ unassigning ? "Processing" : "Unassign Course Folder" }}
          </base-button>
        </div>
          <div class="col-md-12">
            <base-input label="Select Course Folder to Unassign: ">
            <el-select
              multiple
              filterable
              v-model="unassigned_folder_id"
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
    <modal :show.sync="courseSendEmailModal" v-on:close="closeSendEmailModel">
      <h3 slot="header">Send Email</h3>
      <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
      <form   @submit.prevent="handleSubmit(sendBulkEmails)">
        <div class="row">
          <div class="col-md-12">
            <label class="form-control-label">Select: </label>&nbsp;
            <el-select
              v-model="bulk_email"
              class="course_dropdown"
              name="course"
              style="width:100%"
            >
              <el-option
                v-for="(emails, index) in bulkEmails"
                name="location"
                :key="index"
                :label="emails.label"
                :value="emails.value"
              >
              </el-option>
            </el-select>
          </div>
          <div class="col-md-12 mt-2" v-if="bulk_email=='send_tutorial_link'">
            <label class="form-control-label">Tutorial Link (url should contain http:// or https://) *:</label>&nbsp;
            <base-input type="text"
              name="Tutorial Link"
              v-on:blur="validUrl(tutorial_link)"
              rules="required"
              placeholder="Tutorial Link"
              v-model="tutorial_link"
              ></base-input>
          </div>

        </div>
        <div class="my-4 text-center">
          <base-button
            type="danger"
            @click.prevent="courseSendEmailModal = false"
          >
            Cancel
          </base-button>
          <base-button class="custom-btn" native-type="submit">
            {{ unassigning ? "Processing" : "Send" }}
          </base-button>
        </div>

        <div class="clearfix"></div>
      </form>
        </validation-observer>
    </modal>
    <modal :show.sync="locationReassignModal" v-on:close="closeAssignModal">
      <h3 slot="header">Reassign Location</h3>
      <form>
        <div class="row">
          <div class="col-md-12">
            <label>Select Location to Reassign: </label>&nbsp;
            <el-select
              v-if="isEmployee"
              multiple
              filterable
              v-model="reassigned_location_id"
              class="course_dropdown"
              name="location"
            >
              <el-option
                :disabled="reassigned_location_id.length >= 1"
                v-for="(location, index) in locations"
                name="location"
                :key="index"
                :label="location.label"
                :value="location.value"
              >
              </el-option>
            </el-select>
            <el-select
              v-else
              multiple
              filterable
              v-model="reassigned_location_id"
              class="course_dropdown"
              name="location"
            >
              <el-option
                v-for="(location, index) in locations"
                name="location"
                :key="index"
                :label="location.label"
                :value="location.value"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="text-center my-4">
          <base-button
            type="danger"
            v-on:click="closeAssignModal"
            @click.prevent="locationReassignModal = false"
          >
            Cancel
          </base-button>
          <base-button type="success" @click.prevent="reassignLocation">
            {{ "Reassign Location" }}
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
import FileInput from "@/components/Inputs/FileInput";
import axios from "axios";
import moment from "moment";
import Vue from "vue";
import vueDebounce from "vue-debounce";
import BaseInput from '../../components/Inputs/BaseInput.vue';
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
    FileInput,
    BaseInput,
    [DatePicker.name]: DatePicker
  },
  data() {
    return {
      sendTutorialLinkModal: false,
      courseAssigneeModal: false,
      employeeAssignCourseFolderModel: false,
      courseFolderAssigneeModal:false,
      courseFolderUnassigneeModal:false,
      loading: false,
      certificate_availablity: false,
      baseUrl: this.$baseUrl,
      generateCertificate: false,
      showAssigndate: false,
      employeeEmailModal: false,
      askUpdateOption: false,
      completed_date: "",
      expiration_date: "",
      certificate_name: "",
      changed_status: 2,
      course_name: "",
      openCousreEditModel: false,
      reassignLocationModel: false,
      fileSingle: [],
      bulkValue: "",
      bulk_location_id: "",
      selectedCompany: "",
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

      location: {
        locationName: "",
        locationStatus: true
      },
      locations: [],
      tableData: [],
      tableData2: [],
      selectedRows: [],
      hot_user: "",
      hot_token: "",
      config: "",
      company_id: "",

      companies: [
        {
          label: "All",
          value: "",
          parent_id: 0
        }
      ],
      course_employee_id: "",
      employeeDataModal: false,
      employeeCoursesData: {
        first_name: "",
        last_name: "",
        location: "",
        type: "",
        courseData: []
      },
      document_title: "",
      document_type: "",
      document_description: "",
      document_url: "",
      changestatus: [
        {
          label: "Select",
          value: 2
        },
        {
          label: "Pass",
          value: 1
        },
        {
          label: "Fail",
          value: 0
        }
      ],
      filterType: [
        {
          label: "Sub Admins",
          value: "sub-admin"
        },
        {
          label: "Individual Users",
          value: "individual"
        },
        {
          label: "Company Users",
          value: "company_user"
        },
        {
          label: "All Users",
          value: ""
        }
      ],

      company: [],
      filters: {
        userfilterType: "",
        employeStatus: "Active",
        company_id: "",
        onlyParent: false,
        course_id: [],
        folder_id: [],
        user_type: ""
      },
      filterVal: [],
      bulk_array: [
        {
          label: "Make User Active",
          value: "make_employee_active"
        },
        {
          label: "Make User Inactive",
          value: "make_employee_in_active"
        }
      ],
      bulk_assign_array: [
        {
          label: "Manually Update Course",
          value: "manually_pass_course"
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
          label: "Assign Course Folder",
          value: "assign_course_folder"
        },
        {
          label: "Unassign Course Folder",
          value: "unassign_course_folder"
        },
        {
          label: "Reassign Location",
          value: "reassign_location"
        },
        {
          label: "Send Email",
          value: "send_email"
        },
       
      ],

      bulkEmails: [
        {
          label: "Course Due Reminder Email",
          value: "course_due_email"
        },
        {
          label: "Course Expired Reminder Email",
          value: "course_expire_email"
        },
        {
          label: "Resend Welcome Email",
          value: "resend_welcome_email"
        },
        {
          label: "Reset Password Email",
          value: "reset_password_email"
        }, 
        {
          label: "Send Tutorial Link",
          value: "send_tutorial_link"
        }
      ],
      bulk_email: "",
      courses: [],
      coursefolders: [],
      searchQuery: "",
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
          label: "All",
          value: ""
        }
      ],
      employeeEmailData: {
        first_name: "",
        last_name: "",
        location: "",
        id: ""
      },
      unassigned_course_id: "",
      unassigned_folder_id: "",
      reassigned_location_id: "",
      isEmployee: false,
      unassigning: false,
      courseUnassigneeModal: false,
      courseSendEmailModal: false,
      locationReassignModal: false,
      checked_employee: [],
      showUploadModel: false,
      employeeAssignCourseModel: false,
      updateOption: "date_assigned",
      assign_date: "",
      due_date: "",
      days_to_complete: "",
      documentType: [
        {
          label: "Link",
          value: "link"
        },
        {
          label: "File",
          value: "file"
        }
      ],
      pickerOptions1: {
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              picker.$emit("pick", new Date());
            }
          },
          {
            text: "Yesterday",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit("pick", date);
            }
          },
          {
            text: "A week ago",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", date);
            }
          }
        ],
        disabledDate: this.disabledDueDate
      },
      pickerOptions: {
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              picker.$emit("pick", new Date());
            }
          },
          {
            text: "Yesterday",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit("pick", date);
            }
          },
          {
            text: "A week ago",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", date);
            }
          }
        ]
      },
      filterValue: [],
      tutorial_link:"",
      canCreate:true,
      canEdit:true,
      canDelete:true,
      courseManuallyPassModal:false
    };
  },
  watch: {
    searchQuery: function() {
      if (this.searchQuery.length > 3 || this.searchQuery.length == 0) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
          this.fetchData();
        }, 300);
      }
    },
      'filters.company_id': function(newVal , oldVal){
        this.filters.onlyParent = true ;
      }
  },
  created: function() {
    if (localStorage.getItem("hot-token")) {
      this.hot_user = localStorage.getItem("hot-user");
      this.hot_token = localStorage.getItem("hot-token");
    }
    if (localStorage.getItem("hot-user") === "employee") {
      this.editor = "employee";
    } else if (localStorage.getItem("hot-user") === "super-admin") {
      this.editor = "super-admin";
    }else if (localStorage.getItem("hot-user") === "sub-admin") {
      this.editor = "sub-admin";
       this.getRightsDetails();
     } else if (localStorage.getItem("hot-user") === "company-admin") {
      this.editor = "company";
    }

    if (this.$route.query.id) {
      this.filters.company_id = parseInt(this.$route.query.id);
    }
    if (this.$route.query.parent == true) {
      this.filters.onlyParent = this.$route.query.parent;
    }

    this.setDefaultFilterData();
    this.updateOptionChange();

    this.$http.get("company/all").then(resp => {
      for (let comp of resp.data) {
        let obj = {
          label: comp.name,
          value: comp.id,
          parent_id: comp.parent_id
        };
        this.companies.push(obj);
      }
    });
  },

  methods: {
     getRightsDetails(){
       let type="User";
       this.$http.get("subadmin/subadmin_rights/" + type).then(resp => {
        this.canCreate=resp.data[0].permissions.indexOf("c") !== -1 ? true : false;
        this.canEdit=resp.data[0].permissions.indexOf("e") !== -1 ? true : false;
        this.canDelete=resp.data[0].permissions.indexOf("d") !== -1 ? true : false;
       });
    },
    disabledDueDate(time) {
      return time.getTime() < this.completed_date;
    },
    validUrl(url) {
      var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      if (pattern.test(url)) {
        return true;
      }
      alert("Url is not valid!");
      return false;
    },
    onAskUpdateOptionClose() {
      this.updateOption = "date_assigned";
      this.assign_date = "";
      this.due_date = "";
      this.updateOptionChange();
    },
    onOpenCousreEditModelClose() {
      this.updateOption = "date_assigned";
      this.assign_date = "";
      this.due_date = "";
      this.updateOptionChange();
      this.changed_status = 2;
      this.generateCertificate = false;
      this.completed_date = "";
      this.expiration_date = "";
    },
    updateOptionChange() {
      if (this.updateOption == "manually") {
        this.openCousreEditModel = true;
        this.showAssigndate = false;
      }
      if (this.updateOption == "date_assigned") {
        this.showAssigndate = true;
      }
    },
    resetFilters() {
      this.filters.company_id = "";
      this.filters.employeStatus = "Active";
      this.filters.user_type = "";
      this.searchQuery = "";
      this.bulkValue = "";
      this.filters.userfilterType = "";
      this.filterValue = [];
      this.fetchData();
    },
    setCompany(row) {
      this.filters.company_id = row;
      this.fetchData();
    },
    columnFilter(e) {
      this.fetchData();
    },
    filterUserType(id) {
      this.filters.user_type = id;
      this.fetchData();
    },
    createuser() {
      this.$router.push("/create_user");
    },
     getCompanyCourseFolders(event) {
      this.$http.get("company/course_folders/" + event).then(resp => {
        this.coursefolders = [];
        for (let folder of resp.data) {
          for (let cours of folder.coursefolders) {
            let obj = {
              label: cours.folder_name,
              value: cours.id
            };
            this.coursefolders.push(obj);
          }
        }
      });
    },
    getCompanyCourses(event) {
      this.$http.get("company/courses/" + event).then(resp => {
        this.courses = [];
        for (let cour of resp.data) {
          for (let cours of cour.courses) {
            let obj = {
              label: cours.name,
              value: cours.id
            };
            this.courses.push(obj);
          }
        }
      });
    },
    reassignLocation() {
      if (this.reassigned_location_id.length === 0) {
        Swal.fire({
          title: "Error!",
          text: "Please Select Any Location!",
          icon: "error",
          confirmButtonClass: "btn btn-success btn-fill",
          buttonsStyling: true
        });
        return;
      }
      this.$http
        .post(
          "employees/reassign_multiple_location",
          {
            location_ids: this.reassigned_location_id,
            employee_ids: this.selectedRows
          },
          this.config
        )
        .then(resp => {
          this.locationReassignModal = false;
          this.reassigned_location_id = "";
          this.bulkValue = "";
          this.fetchData();
          Swal.fire({
            title: "Success!",
            text: "New Location has been Assigned to these Employees!",
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: true
          });
        });
    },
    assignBulkLocation() {
      if (this.bulk_location_id !== "") {
        let ids = [];
        for (let id of this.checked_employee) {
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
    closeAssignModal() {
      this.bulkValue = "";
      this.reassigned_location_id = "";
    },
     closeMassUpdateCourse(){
      this.bulkValue = "";
      this.filters.course_id = "";
      this.completed_date="";
      this.expiration_date="";
      this.courseManuallyPassModal=false;
    },
    closeSendEmailModel(){
        this.bulkValue="";
        this.bulk_email="";
        this.tutorial_link="";
    },
    changeDueDate() {
      if (this.assign_date) {
        let date = "";
        this.date = new Date(this.assign_date);
        this.due_date = new Date(
          this.date.setDate(this.date.getDate() + this.days_to_complete)
        );
      } else {
        this.due_date = "";
      }
    },
    changeAssignDate() {
      if (this.due_date) {
        let date = "";
        this.date = new Date(this.due_date);
        this.assign_date = new Date(
          this.date.setDate(this.date.getDate() - this.days_to_complete)
        );
      } else {
        this.assign_date = "";
      }
    },
    unAssignedCourse(index, row) {
      let self = this;
      Swal.fire({
        title: "You are about to delete this course.",
        text: "Would you like to continue?",
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
                  buttonsStyling: true
                });
              });
          }
        })
        .catch(function() {});
    },
    editCourseStatus(index, row, employee_id, type) {
      this.employeeid = employee_id;
      this.courseid = row.id;
      this.course_name = row.name;
      this.days_to_complete = "";
      this.onAskUpdateOptionClose();
      if (type == "employee") {
        this.days_to_complete = row.employees_days_to_complete;
      } else {
        this.days_to_complete = row.managers_days_to_complete;
      }

      if (row.certificate_available) {
        this.certificate_availablity = true;
        this.generateCertificate = true;
      } else {
        this.certificate_availablity = false;
        this.generateCertificate = false;
      }

      this.askUpdateOption = true;
      //this.openCousreEditModel = true;
    },
    formattedDate(data) {
      return moment(data).format("MM-DD-YYYY");
    },
    onImageChange(e) {
      let files = e;
      this.file = files[0];
    },
    updateCourse() {
      if (this.updateOption == "date_assigned") {
        if (this.assign_date && this.due_date) {
          this.submitUpdateCourse();
        } else {
          Swal.fire({
            title: "Error!",
            text: "Assign and Due date is required!",
            icon: "error",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: false
          });
        }
      }
      if (this.updateOption == "manually") {
        this.submitUpdateCourse();
      }
    },
    submitUpdateCourse() {
      const config = {
        headers: {
          "content-type": "multipart/form-data"
        }
      };
      let formData = new FormData();
      if (this.file) {
        formData.append("file", this.file);
      } else {
        formData.append("file", "");
      }
      formData.append("type", this.updateOption);
      formData.append("course_id", this.courseid);
      formData.append("employee_id", this.employeeid);
      formData.append("status", this.changed_status);
      formData.append("generateCertificate", this.generateCertificate);
      
      if (this.completed_date) {
        formData.append(
          "completed_date",
          moment(this.completed_date).format("YYYY-MM-DD")
        );
      } else {
        formData.append("completed_date", "");
      }
      if (this.assign_date) {
        formData.append(
          "date_assigned",
          moment(this.assign_date).format("YYYY-MM-DD")
        );
      } else {
        formData.append("date_assigned", "");
      }
      if (this.due_date) {
        formData.append("due_date", moment(this.due_date).format("YYYY-MM-DD"));
      } else {
        formData.append("due_date", "");
      }
      if (this.expiration_date) {
        formData.append(
          "expiration_date",
          moment(this.expiration_date).format("YYYY-MM-DD")
        );
      } else {
        formData.append("expiration_date", "");
      }

      this.$http
        .post("employees/update_course", formData, config)
        .then(resp => {
          this.askUpdateOption = false;
          this.openCousreEditModel = false;
          this.employeeDataModal = false;
          formData.append("file", "");
          formData.append("course_id", this.courseid);
          formData.append("employee_id", this.employeeid);
          formData.append("certificate_name", "");
          formData.append("completed_date", "");
          formData.append("expiration_date", "");
          if (this.changed_status == 1) {
            this.$http
              .post("course/assignnextcourse", {
                course_id: this.courseid,
                user_id: this.employeeid
              })
              .then(resp => {
                Swal.fire({
                  title: "Success!",
                  text: `Status Updated Successfully!`,
                  icon: "success"
                });
                this.fetchData();
                this.onOpenCousreEditModelClose();
              });
          } else {
            Swal.fire({
              title: "Success!",
              text: `Status Updated Successfully!`,
              icon: "success"
            });
            this.fetchData();
            this.onOpenCousreEditModelClose();
          }
        });
    },
    manuallyUpdateCourse() {
      const config = {
        headers: {
          "content-type": "multipart/form-data"
        }
      };
      let formData = new FormData();
      if (this.file) {
        formData.append("file", this.file);
      } else {
        formData.append("file", "");
      }
      formData.append("course_id", this.filters.course_id);
      
      formData.append("employee_ids", this.selectedRows);
      formData.append("status", this.changed_status);
      if (this.completed_date) {
        formData.append(
          "completed_date",
          moment(this.completed_date).format("YYYY-MM-DD")
        );
      } else {
        formData.append("completed_date", "");
      }
      if (this.expiration_date) {
        formData.append(
          "expiration_date",
          moment(this.expiration_date).format("YYYY-MM-DD")
        );
      } else {
        formData.append("expiration_date", "");
      }

      this.$http
        .post("employees/updateCourseManually", formData, config)
        .then(resp => {
          formData.append("file", "");
          formData.append("course_id", this.courseid);
          formData.append("employee_id", this.employeeid);
          formData.append("certificate_name", "");
          formData.append("completed_date", "");
          formData.append("expiration_date", "");

            Swal.fire({
              title: "Success!",
              text: `Status Updated Successfully!`,
              icon: "success"
            });
            this.closeMassUpdateCourse();
            this.fetchData();
        });
    },
    uploadDocument(e) {
      e.preventDefault();
      let currentObj = this;
      if (this.document_title !== "" && this.document_type !== "") {
        let formData = new FormData();
        formData.append("file", this.file);
        formData.append("user_id", this.course_employee_id);
        formData.append("title", this.document_title);
        formData.append("type", this.document_type);
        formData.append("description", this.document_description);
        formData.append("url", this.document_url);
        this.$http
          .post(this.$baseUrl + "/employees/upload_document", formData)
          .then(function(response) {
            currentObj.empdoc();

            Swal.fire({
              title: "Success!",
              text: "Document uploaded successfully",
              icon: "success",
              confirmButtonClass: "btn btn-success btn-fill",
              buttonsStyling: false
            }).then(result => {
              if (result.value) {
                currentObj.document_title = "";
                currentObj.document_description = "";
                currentObj.document_url = "";
                currentObj.document_type = "";
                currentObj.file = "";
              }
            });
          })
          .catch(function(error) {
            currentObj.output = error;
          });
      } else {
        return Swal.fire({
          title: "Error!",
          text: `Please Fill all the mandatory fields!`,
          icon: "error"
        });
      }
    },
    empdoc() {
      this.$http
        .post("employees/documents", {
          employee_id: this.course_employee_id
        })
        .then(resp => {
          this.tableData2 = [];
          let document_data = resp.data;
          for (let data of document_data) {
            let obj = {
              id: data.id,
              document: data.document,
              url: data.url,
              document_name: data.title,
              document_type: data.type
            };
            this.tableData2.push(obj);
          }
        });
    },
    handleUpload(index, row) {
      this.course_employee_id = row.id;
      this.employeeCoursesData.first_name = row.first_name;
      this.employeeCoursesData.last_name = row.last_name;
      this.empdoc();
      this.showUploadModel = true;
    },
    sendBulkEmails() {
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      let data = {
        company_id: this.selectedCompany,
        ids: []
      };
      if (this.selectedRows.length > 0) {
        for (let id of this.selectedRows) {
          let obj = {
            id: id
          };
          data.ids.push(obj);
        }
      } else {
        let obj = {
          id: this.course_employee_id
        };
        data.ids.push(obj);
      }
      if(this.bulk_email == "send_tutorial_link"){
           data.tutorial_link= this.tutorial_link;
      }
      if (this.bulk_email == "course_due_email") {
        this.$http.post("employees/course_reminder_email", data).then(resp => {
          this.courseSendEmailModal = false;
           this.bulkValue="";
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
      } else if (this.bulk_email == "course_expire_email") {
        this.$http
          .post("employees/course_expire_reminder_email", data)
          .then(resp => {
            this.courseSendEmailModal = false;
             this.bulkValue="";
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
      } else if (this.bulk_email == "resend_welcome_email") {
        this.$http.post("employees/welcome_email", data).then(resp => {
          this.courseSendEmailModal = false;
           this.bulkValue="";
          Swal.fire({
            title: "Success!",
            text: "Email has been sent!",
            icon: "success",
            confirmButtonClass: "btn btn-success btn-fill",
            buttonsStyling: false
          });
        });
      } else if (this.bulk_email == "reset_password_email") {
        this.$http.post("employees/password_reset", data).then(resp => {
          this.courseSendEmailModal = false;
           this.bulkValue="";
          Swal.fire({
            title: "Success!",
            text: "Reset Password email has been sent.",
            confirmButtonClass: "btn btn-success btn-fill",
            icon: "success"
          });
        });
      }else if(this.bulk_email == "send_tutorial_link"){
          this.$http
              .post("employees/send_tutorial_link", data)
              .then(resp => {
                this.courseSendEmailModal = false;
                this.bulkValue="";
                 this.bulk_email="";
                this.tutorial_link="";
               Swal.fire({
                title: "Success!",
                text: "Tutorial link has been sent.",
                confirmButtonClass: "btn btn-success btn-fill",
                icon: "success"
              });
              });
      }
    },

    sendReminderEmail() {
      let self = this;
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
        confirmButtonText: "Yes",
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
    sendExpiredReminderEmail() {
      let self = this;
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
        confirmButtonText: "Yes",
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
              .post("employees/course_expire_reminder_email", {
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
    sendWelcomeEmail() {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        type: "warning",
        text:
          "You want to send Welcome Email to " +
          self.employeeEmailData.first_name +
          " " +
          self.employeeEmailData.last_name +
          "?",
        showCancelButton: true,
        confirmButtonClass: "btn btn-success btn-fill",
        cancelButtonClass: "btn btn-danger btn-fill",
        confirmButtonText: "Yes",
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
        type: "warning",
        text:
          "You want to reset password to " +
          self.employeeEmailData.first_name +
          " " +
          self.employeeEmailData.last_name +
          "?",
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
              .post("employees/password_reset", {
                user_id: self.employeeEmailData.id
              })
              .then(resp => {
                self.employeeEmailModal = false;
                Swal.fire({
                  title: "Success!",
                  text:
                    "Reset password link has been sent to " +
                    self.employeeEmailData.first_name +
                    "'s email!",
                  icon: "success"
                });
              });
          }
        })
        .catch(function() {});
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
    openCourseDetails(row) {
      this.course_employee_id = row.id;
      this.employeeCoursesData.first_name = row.first_name;
      this.employeeCoursesData.last_name = row.last_name;
      this.employeeCoursesData.type = row.type;
      this.employeeCoursesData.location = row.location;
      this.employeeCoursesData.courseData = row.courses;

      this.employeeDataModal = true;
    },
    handleEnvelope(index, row) {
      this.employeeEmailData.first_name = row.first_name;
      this.employeeEmailData.last_name = row.last_name;
      this.employeeEmailData.location = row.location;
      this.employeeEmailData.id = row.id;
      this.employeeEmailModal = true;
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
        text: "You want to change status?",
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
                  icon: "success"
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
    deleteDocument(document_id) {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: "You want to delete!",
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
              .post(
                "employees/delete_document",
                {
                  employee_id: self.course_employee_id,
                  document_id: document_id
                },
                self.config
              )
              .then(resp => {
                self.empdoc();
                Swal.fire({
                  title: "Success!",
                  text: "Document has been deleted!",
                  icon: "success",
                  buttonsStyling: true
                });
              });
          }
        })
        .catch(function() {});
    },
    bulkClicked() {
      if (this.selectedRows.length > 0) {
        if (this.bulkValue === "make_employee_active") {
          this.bulkChangeStatusToActive();
        } else if (this.bulkValue === "make_employee_in_active") {
          this.bulkChangeStatusToInactive();
        } else if (this.bulkValue === "assign_delete_course") {
          this.bulkAssignDeleteCourse();
        } else if (this.bulkValue === "reassign_location") {
          this.bulkReassignLocation();
        } else if (this.bulkValue == "assign_course") {
          this.bulkAssignCourse();
        } else if (this.bulkValue == "manually_pass_course") {
          this.bulkManualPassCourse();
        } else if (this.bulkValue == "unassign_course") {
          this.bulkUnassignCourse();
        }else if (this.bulkValue == "assign_course_folder") {
          this.bulkAssignCourseFolder();
        } else if (this.bulkValue == "unassign_course_folder") {
          this.bulkUnassignCourseFolder();
        } else if (this.bulkValue == "send_email") {
          this.bulkSendEmail();
        }
      } else {
        this.bulkValue = "";
        Swal.fire({
          text: "Please Select Employees First!",
          icon: "error",
          confirmButtonClass: "btn btn-danger btn-fill",
          buttonsStyling: true
        });
      }
    },
     bulkManualPassCourse(){
     if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      this.$http.get("company/courses/" + this.selectedCompany).then(resp => {
        this.courses = resp.data[0].courses;
      });
      if (this.selectedRows.length > 0) {
        this.courseManuallyPassModal = true;
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
    bulkAssignCourse() {
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      this.$http
        .get("company/courses/" + this.selectedCompany)
        .then(resp => {
          this.courses = resp.data[0].courses;
        });
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
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      this.$http
        .get("company/courses/" + this.selectedCompany)
        .then(resp => {
          this.courses = resp.data[0].courses;
        });
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
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      this.$http
        .get("company/course_folders/" + this.selectedCompany)
        .then(resp => {
          this.coursefolders = resp.data[0].coursefolders;
        });
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
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      this.$http
        .get("company/course_folders/" + this.selectedCompany)
        .then(resp => {
          this.coursefolders = resp.data[0].coursefolders;
        });
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
    bulkSendEmail() {
      if (this.selectedRows.length > 0) {
        this.courseSendEmailModal = true;
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
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        let companyKeys = Object.keys(this.companies).find(
          key => this.companies[key]["value"] === this.filters.company_id
        );
        let companyParentId = this.companies[companyKeys].parent_id;
        this.selectedCompany =
          companyParentId == 0 ? this.filters.company_id : companyParentId;
      }

      this.$http
        .post("location/all_company_location", {
          company_id: this.selectedCompany
        })
        .then(resp => {
          this.locations = [];
          for (let loc of resp.data) {
            let obj = {
              label: loc.name,
              value: loc.id
            };
            this.locations.push(obj);
          }
        });
      if (this.selectedRows.length > 0) {
        this.checkIsEmployeeSelected();
        this.locationReassignModal = true;
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
    checkIsEmployeeSelected() {
      var employeesKeys = undefined;
      var thisObj = this;
      this.selectedRows.forEach(function(item, index) {
        employeesKeys = Object.keys(thisObj.tableData).find(
          key => thisObj.tableData[key]["id"] === item
        );
        if (
          employeesKeys !== undefined &&
          thisObj.tableData[employeesKeys].type === "employee"
        ) {
          thisObj.isEmployee = true;
          return;
        } else {
          thisObj.isEmployee = false;
          return;
        }
      });
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
    downloadBulkCertificate(row){
       let user_id = row;
       window.location.href= this.baseUrl + '/downloadCourseFoldersCertificate/'+ user_id
    },
  showAssigncourse(employee_id, $interface) {
      this.filters.course_id = "";
      this.filters.company_id = "";
      this.$http.get("company/company_employee/" + employee_id).then(resp => {
        this.company = [];
        for (let comp of resp.data[0].company) {
          let obj = {
            label: comp.name,
            value: comp.id
          };
          this.company.push(obj);
        }
      });
      if ($interface == "course") {
        this.employeeAssignCourseModel = true;
        this.employeeAssignCourseFolderModel = false;
      }
      if ($interface == "folder") {
        this.employeeAssignCourseModel = false;
        this.employeeAssignCourseFolderModel = true;
      }
    },

    assignCourseFolder() {
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      if (this.filters.folder_id !== "" || this.filters.company_id !== "") {
        this.loading = true;
        let data = {
          folder_id: this.filters.folder_id,
          company_id: this.selectedCompany,
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
            this.employeeAssignCourseFolderModel=false;
            this.courseFolderAssigneeModal=false;
            this.filters.folder_id=[];
            this.assigned_course_id = "";
            this.employeeAssignCourseModel = false;
            this.employeeDataModal = false;
            this.bulkValue = "";
            this.fetchData();
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
          })
          .finally(() => (this.loading = false));
      } else {
        Swal.fire({
          title: "Error!",
          text: "All fields are required!",
          icon: "error"
        });
      }
    },
    assignCourse() {
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      if (this.filters.course_id !== "" || this.filters.company_id !== "") {
        this.loading = true;
        let data = {
          course_id: this.filters.course_id,
          company_id: this.selectedCompany,
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
            this.employeeAssignCourseFolderModel=false;
            this.filters.course_id=[];
            this.assigned_course_id = "";
            this.employeeAssignCourseModel = false;
            this.employeeDataModal = false;
            this.bulkValue = "";
            this.fetchData();
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
          })
          .finally(() => (this.loading = false));
      } else {
        Swal.fire({
          title: "Error!",
          text: "All fields are required!",
          icon: "error"
        });
      }
    },
    unassignCourse() {
      this.unassigning = true;
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      if (this.unassigned_course_id !== "") {
        let data = {
          course_id: this.unassigned_course_id,
          company_id: this.selectedCompany,
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
          this.courseUnassigneeModal = false;
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
      if (!this.filters.company_id) {
        this.selectedCompany = 0;
      } else {
        this.selectedCompany = this.filters.company_id;
      }
      if (this.unassigned_folder_id !== "") {
        let data = {
          folder_id: this.unassigned_folder_id,
          company_id: this.selectedCompany,
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
          this.courseFolderUnassigneeModal = false;
          this.bulkValue = "";
          this.unassigned_folder_id = "";
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
    fetchData() {
        if(!this.company_id && !this.$route.query.parent) {
            this.filters.onlyParent = true;
        }
      this.loading = true;
      this.loading = true;
      this.$http
        .post("employees/list", {
          role: "super-admin",
          filter_value: this.filterValue,
          search: this.searchQuery,
          company_id: this.filters.company_id,
          user_type: this.filters.user_type,
          employee_status: this.filters.employeStatus,
          filter_type: this.filters.userfilterType,
          only_parent: this.filters.onlyParent,
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
              location: "",
              location_id: "",
              company: data.name,
              company_id: data.company_id,
              courses: data.courses,
              type: data.type,
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
      this.saveSearchData();
    },
    saveSearchData() {
      localStorage.setItem(
        "all_user_search_data",
        JSON.stringify({
          role: "super-admin",
          search: this.searchQuery,
          company_id: this.filters.company_id,
          user_type: this.filters.user_type,
          employee_status: this.filters.employeStatus,
          filter_type: this.filters.userfilterType,
          only_parent: this.filters.onlyParent,
          page: this.currentPage,
          column: this.sortedColumn,
          order: this.order,
          per_page: this.perPage
        })
      );
    },
    setDefaultFilterData() {
      let previousStateData = JSON.parse(
        localStorage.getItem("all_user_search_data")
      );

      if (previousStateData !== null) {
        this.searchQuery = previousStateData.search
          ? previousStateData.search
          : this.searchQuery;
        this.filters.company_id = parseInt(this.$route.query.id)
          ? parseInt(this.$route.query.id)
          : previousStateData.company_id
          ? previousStateData.company_id
          : this.filters.company_id;
        this.filters.user_type = previousStateData.user_type
          ? previousStateData.user_type
          : this.filters.user_type;
        this.filters.employeStatus = previousStateData.employee_status
          ? previousStateData.employee_status
          : this.filters.employeStatus;
        this.filters.userfilterType = previousStateData.filter_type
          ? previousStateData.filter_type
          : this.filters.userfilterType;
        this.filters.onlyParent = this.$route.query.parent
          ? this.$route.query.parent
          : previousStateData.only_parent
          ? previousStateData.only_parent
          : this.filters.onlyParent;
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
    handleEdit(index, row) {
      if(row.type=="sub-admin"){
      this.$router.push("/create_subadmin?id=" + row.id);
      }else{
      this.$router.push("/add_employee?id=" + row.id);
       }
    },
    handleDelete(index, row) {
      let self = this;
      Swal.fire({
        title: "Are you sure?",
        text: `You won't be able to revert this!`,
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
              .post(
                "employees/delete_employee",
                {
                  employee_id: row.id
                },
                self.config
              )
              .then(resp => {
                self.fetchData();
                Swal.fire({
                  title: "Deleted!",
                  text: `Employee Deleted successfully`,
                  icon: "success",
                  confirmButtonClass: "btn btn-success btn-fill",
                  buttonsStyling: false
                });
              });
          }
        })
        .catch(function() {});
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
        if (this.selectedRows.includes(selectedRow.id)) {
          this.selectedRows.splice(
            this.selectedRows.indexOf(selectedRow.id),
            1
          );
        } else {
          this.selectedRows.push(selectedRow.id);
        }
      }
    }
  }
  // filters: {
  //   columnHead(value) {
  //     return value
  //       .split("_")
  //       .join(" ")
  //       .toUpperCase();
  //   }
  // }
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
.modal-header .title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  font-size: 18px;
  font-weight: 600;
}
.custom-size >>> .btn-sm {
  display: flex;
}

.POF_btn {
  padding: 2px 10px;
}
.el-table__column-filter-trigger i {
  color: #ffffff !important;
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
    content: "Company";
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
    content: "Lesson Name";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Lesson Status";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Date Completed";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Date Assigned";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(5):before {
    content: "Due Date";
  }
  .pofGrid >>> table.el-table__body td:nth-of-type(6):before {
    content: "Actions";
  }
  .documentGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Document Name";
  }
  .documentGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Actions";
  }
}
</style>
