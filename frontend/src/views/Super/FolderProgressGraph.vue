<template>
    <highcharts :options="highChartOptions"></highcharts>
</template>
<script>
import Highcharts from "highcharts";
import Highcharts3d from "highcharts/highcharts-3d";
import {Chart} from "highcharts-vue";

Highcharts3d(Highcharts);

export default {
    name: "pof-pie-chart",
    props: {
        folder_name: String,
        total_count: String,
        passed_count: String,
        failed_count: String
    },
    components: {
        highcharts: Chart
    },
    data() {
        return {
            colors: {
                pass: "#109618",
                fail: "#dc3912",
                pending: "#fedf00"
            },
            highChartOptions: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: "pie",
                    options3d: {
                        enabled: true,
                        alpha: 45,
                        beta: 0
                    }
                },
                title: {
                    style: {
                        fontSize: '16px'
                    },
                    text: ""
                },
                subtitle: {
                    style: {
                        fontSize: '12px'
                    },
                    text: ""
                },
                tooltip: {
                    pointFormat: "({point.y})<b>{point.y}</b>"
                },

                plotOptions: {
                    pie: {
                        size: 140,
                        depth: 45,
                        allowPointSelect: true,
                        cursor: "pointer",
                        dataLabels: {
                            enabled: true,
                            format: "<b>{point.name}</b>: {point.y}"
                        },
                        showInLegend: true
                    }
                },
                series: [
                    {
                        data: [
                            {
                                name: "PASS",
                                y: 0,
                                color: "#109618"
                            },
                            {
                                name: "FAIL",
                                y: 0,
                                color: "#dc3912"
                            },
                            {
                                name: "PENDING",
                                y: 0,
                                color: "#fedf00"
                            }
                        ]
                    }
                ]
            }
        };
    },
    mounted: function () {
        this.updateChartData();
    },
    methods: {
        updateChartData() {
            let series = [
                {name: "PASS", y: (this.passed_count), color: "#109618"},
                {name: "FAIL", y: (this.failed_count), color: "#dc3912"},
                {
                    name: "PENDING",
                    y: (this.total_count - this.passed_count - this.failed_count),
                    color: "#fedf00"
                }
            ];

            this.highChartOptions.series[0].data = [];
            this.highChartOptions.series[0].data = series;
            this.highChartOptions.title.text = this.folder_name;
            this.highChartOptions.subtitle.text = "Total Courses:" + this.total_count;
        }
    }
};
</script>
