<template>
    <div>
        <div class="graph-title">
            <div class="float-left">
                <h2>Completions by Month</h2>
            </div>
            <div class="float-right">
                <el-select v-model="selectedDateRange" class="select-primary" name="Date Range" placeholder="Filter by Date Range">
                    <el-option v-for="(dateRange, index) in dateRanges" :key="index" :label="dateRange.label" :value="dateRange.value" class="select-primary"/>
                </el-select>
            </div>
            <div class="clearfix"></div>
        </div>
        <highcharts :options="highChartOptions"></highcharts>
    </div>
</template>
<script>
import Highcharts from "highcharts";
import Highcharts3d from "highcharts/highcharts-3d";
import {Chart} from "highcharts-vue";
import {Option, Select} from "element-ui";

Highcharts3d(Highcharts);
export default {
    name: "completions-graph",
    props: {
        company_id: String
    },
    components: {
        highcharts: Chart,
        [Select.name]: Select,
        [Option.name]: Option,
    },
    data() {
        return {
            highChartOptions: {
                chart: {
                    type: "column",
                    options3d: {
                        enabled: true,
                        alpha: 5,
                        beta: -5,
                        depth: 30,
                        viewDistance: 25
                    }
                },
                title: {
                    text: ""
                },
                xAxis: {
                    type: "datetime",
                    tickInterval: 1000 * 3600 * 24 * 30 // 1 month
                },
                yAxis: {
                    title: {
                        text: "Courses"
                    }
                },
                plotOptions: {
                    column: {
                        depth: 25
                    }
                },
                tooltip: {
                    formatter: function () {
                        return `<text x="8" data-z-index="1" y="20" style="color:#333333;cursor:default;font-size:12px;fill:#333333;">
                            <tspan style="fill:#1f78b4" x="8" dy="15">●</tspan><tspan dx="0"> Total Completions: </tspan>
                            <tspan style="font-weight:bold" dx="0"> ${this.y}</tspan></text>`;
                    }
                },
                series: [
                    {
                        name: "Total Completions",
                        type: "column",
                        data: [0, 0, 0, 0, 0, 0],
                        color: "#1f78b4" // sample data
                    }
                ]
            },
            dateRanges: [
                {
                    label: 'Last 30 Days',
                    value: 1,
                },
                {
                    label: 'Last 60 Days',
                    value: 2,
                },
                {
                    label: 'Last 90 Days',
                    value: 3,
                },
                {
                    label: '6 Month',
                    value: 6,
                },
                {
                    label: '12 Months',
                    value: 12,
                },
            ],
            selectedDateRange: 6,
        };
    },
    mounted: function () {
        this.fetchData();
    },
    methods: {
        udpateChartData(data) {
            let series = [];
            data.forEach(item => {
                series.push({
                    x: new Date(item.month + " 00:00:00").setDate(1),
                    y: item.count
                });
            });
            this.highChartOptions.series[0].data = series;
            console.log(this.highChartOptions.series[0].data);
        },
        fetchData: function () {
            this.$http
                .post("analytics/get_course_completions_byMonth", {
                    company_id: this.company_id,
                    dateRange: this.selectedDateRange,
                })
                .then(response => {
                    this.udpateChartData(response.data);
                })
                .catch(error => {
                    console.log("API failed for loading POF data");
                });
        }
    },
    watch: {
        company_id: function () {
            this.fetchData();
        },
        selectedDateRange: function () {
            this.fetchData();
        }
    }
};
</script>

