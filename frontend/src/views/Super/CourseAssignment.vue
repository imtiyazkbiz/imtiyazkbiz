<template>
  <div class="row">
    <div class="col-sm-12 mt-2">
      <div class="user-eltable">
        <el-table
          :data="tableData"
          row-key="id"
          header-row-class-name="thead-light custom-thead-light"
          class="coursesEmployeeGrid"
          role="table"
        >
          <el-table-column label="Course Name" property="" min-width="400px">
            <template slot-scope="props">
              <span>{{ props.row.course_name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Date Assigned" property="" min-width="200px">
            <template slot-scope="props">
              <span>{{ formattedDate(props.row.assigned_date) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Due Date" property="" min-width="150px">
            <template slot-scope="props">
              <!-- <span>{{formattedDate(props.row.due_date)}}</span>--><span
                v-if="
                  currentDate(props.row.due_date) &&
                    props.row.course_status != '1'
                "
                style="color:red;"
                ><b>{{ formattedDate(props.row.due_date) }}</b></span
              >
              <span v-else>{{ formattedDate(props.row.due_date) }}</span>
            </template>
          </el-table-column>
          <el-table-column min-width="150px" label="Lesson Status">
            <template slot-scope="props">
              <span
                type="danger"
                style="color:#f50636;"
                v-if="props.row.course_status === 0"
                ><b>Failed</b></span
              >
              <span
                type="warning"
                style="color: #ffd600;"
                v-if="props.row.course_status === 2"
                ><b>Open</b></span
              >
              <span
                type="success"
                style="color: #05bf70;"
                v-if="props.row.course_status === 1"
                ><b>Passed</b></span
              >
              <span
                type="danger"
                style="color:#f50636;"
                v-if="props.row.course_status === 3"
                ><b>Expired</b></span
              >
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
  </div>
</template>
<script>
import { Table, TableColumn, Select, Option } from "element-ui";
import moment from "moment";
export default {
  name: "course-assignment",
  props: {
    employee_id: String
  },
  components: {
    [Select.name]: Select,
    [Option.name]: Option,
    [Table.name]: Table,
    [TableColumn.name]: TableColumn
  },
  data() {
    return {
      tableData: []
    };
  },
  created() {
    this.refresh();
  },
  methods: {
    formattedDate(data) {
      return moment(data).format("MM-DD-YYYY");
    },

    refresh() {
      this.$http
        .post("employees/courses", {
          employee_id: this.employee_id
        })
        .then(resp => {
          this.tableData = [];
          let course_data = resp.data.courses;
          for (let data of course_data) {
            let obj = {
              id: data.course_id,
              course_name: data.name,
              due_date: data.employee_course_due_date,
              assigned_date: data.employee_course_date_assigned,
              course_status: data.employee_course_status
            };
            this.tableData.push(obj);
          }
        });
    },
    currentDate(duedate) {
      var currentDateWithFormat = new Date()
        .toJSON()
        .slice(0, 10)
        .replace(/-/g, "-");
      if (currentDateWithFormat > duedate) {
        return true;
      } else {
        return false;
      }
    }
  }
};
</script>
<style scoped>
@media only screen and (max-width: 760px),
  (min-device-width: 768px) and (max-device-width: 1024px) {
  .coursesEmployeeGrid >>> table.el-table__body td:nth-of-type(1):before {
    content: "Course Name";
  }
  .coursesEmployeeGrid >>> table.el-table__body td:nth-of-type(2):before {
    content: "Date Assigned";
  }
  .coursesEmployeeGrid >>> table.el-table__body td:nth-of-type(3):before {
    content: "Due Date";
  }
  .coursesEmployeeGrid >>> table.el-table__body td:nth-of-type(4):before {
    content: "Lesson Status";
  }
}
</style>
