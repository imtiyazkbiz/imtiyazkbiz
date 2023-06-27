<template>
    <div>
        <div class="graph-title">
            <div class="float-left">
                <h2>Pass / Open / Fail</h2>
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
import {Option, Select} from "element-ui";
import Highcharts from "highcharts";
import Highcharts3d from "highcharts/highcharts-3d";
import {Chart} from 'highcharts-vue';

Highcharts3d(Highcharts);
export default {
    name: "pof-pie-chart",
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
            colors: {
                pass: "#109618",
                fail: "#dc3912",
                open: "#fedf00"
            },
            highChartOptions: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45,
                        beta: 0
                    },
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '({point.y})<b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        depth: 45,
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.0f} %'
                        },
                        showInLegend: true,

                    }
                },
                series: [{
                    data: [{
                        name: 'PASS',
                        y: 0,
                        color: "lightgreen"
                    }, {
                        name: 'OPEN',
                        y: 0,
                        color: "yellow"
                    }, {
                        name: 'FAIL',
                        y: 0,
                        color: "red"
                    }]
                }]
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
            selectedDateRange: 1,
        }
    },
    mounted: function () {
        this.fetchData();
    },
    methods: {
        udpateChartData(data) {
            let series = [];
            for (const key in data) {
                series.push({name: key.toUpperCase(), y: data[key], color: this.colors[key]});
            }
            this.highChartOptions.series[0].data = series;
        },
        fetchData: function () {
            this.$http.post("analytics/pofe_courses", {
                company_id: this.company_id,
                dateRange: this.selectedDateRange,
            }).then(response => {
                this.udpateChartData(response.data);
            }).catch(error => {
                console.log('API failed for loading POF data');
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

}
</script>