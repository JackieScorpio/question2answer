$(function() {
    /* ChartJS
     * -------
     * Data and config for chartjs
     */
    'use strict';
    $(function() {
        $.post('../../ajax/chart2.php', function (response) {
            response = JSON.parse(response);

            // 用户徽章数获取
            var badgeCountChartData = {
                labels: response.badgecount.ids,
                datasets: [{
                    label: '一级徽章',
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 0.8)',
                    borderWidth: 1,
                    stack: 'Stack 0',
                    data: response.badgecount.badge1,
                }, {
                    label: '二级徽章',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 0.8)',
                    borderWidth: 1,
                    stack: 'Stack 0',
                    data: response.badgecount.badge2,
                }, {
                    label: '三级徽章',
                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderColor: 'rgba(255, 206, 86, 0.8)',
                    borderWidth: 1,
                    stack: 'Stack 0',
                    data: response.badgecount.badge3,
                }]
            };

            var badgeCountChartOption = {
                responsive: true,
                title: {
                    display: false,
                    text: '堆叠柱状图'
                },
                scales: {
                    xAxes: [{
                        stacked: true,
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }],
                    yAxes: [{
                        stacked: true,
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }]
                }
            }

            if ($("#badgeCountChart").length) {
                var badgeCountChart = $("#badgeCountChart").get(0).getContext("2d");
                var myBadgeCountChart = new Chart(badgeCountChart, {
                    type: 'bar',
                    data: badgeCountChartData,
                    options: badgeCountChartOption
                });
            }

            // 各级徽章数饼图
            // 饼图
            var badgePie1Data = {
                datasets: [{
                    data: response.badgeCat.cat1count,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(208,59,243,0.5)',
                        'rgba(79,141,67,0.5)',
                        'rgba(35,71,138,0.5)',
                        'rgba(238,61,135,0.5)'
                    ],
                    borderColor: [
                        // 'rgba(255, 99, 132, 0.5)',
                        // 'rgba(54, 162, 235, 0.5)',
                        // 'rgba(255, 206, 86, 0.5)',
                        // 'rgba(75, 192, 192, 0.5)',
                        // 'rgba(153, 102, 255, 0.5)',
                        // 'rgba(255, 159, 64, 0.5)',
                        // 'rgba(208,59,243,0.5)',
                        // 'rgba(79,141,67,0.5)',
                        // 'rgba(35,71,138,0.5)',
                        // 'rgba(238,61,135,0.5)'
                    ],
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: response.badgeCat.cat1
            };
            var badgePie2Data = {
                datasets: [{
                    data: response.badgeCat.cat2count,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(208,59,243,0.5)',
                        'rgba(79,141,67,0.5)',
                        'rgba(35,71,138,0.5)',
                        'rgba(238,61,135,0.5)'
                    ],
                    borderColor: [
                        // 'rgba(255, 99, 132, 0.5)',
                        // 'rgba(54, 162, 235, 0.5)',
                        // 'rgba(255, 206, 86, 0.5)',
                        // 'rgba(75, 192, 192, 0.5)',
                        // 'rgba(153, 102, 255, 0.5)',
                        // 'rgba(255, 159, 64, 0.5)',
                        // 'rgba(208,59,243,0.5)',
                        // 'rgba(79,141,67,0.5)',
                        // 'rgba(35,71,138,0.5)',
                        // 'rgba(238,61,135,0.5)'
                    ],
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: response.badgeCat.cat2
            };
            var badgePie3Data = {
                datasets: [{
                    data: response.badgeCat.cat3count,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(208,59,243,0.5)',
                        'rgba(79,141,67,0.5)',
                        'rgba(35,71,138,0.5)',
                        'rgba(238,61,135,0.5)'
                    ],
                    borderColor: [
                        // 'rgba(255, 99, 132, 0.5)',
                        // 'rgba(54, 162, 235, 0.5)',
                        // 'rgba(255, 206, 86, 0.5)',
                        // 'rgba(75, 192, 192, 0.5)',
                        // 'rgba(153, 102, 255, 0.5)',
                        // 'rgba(255, 159, 64, 0.5)',
                        // 'rgba(208,59,243,0.5)',
                        // 'rgba(79,141,67,0.5)',
                        // 'rgba(35,71,138,0.5)',
                        // 'rgba(238,61,135,0.5)'
                    ],
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: response.badgeCat.cat3
            };
            var badgePie4Data = {
                datasets: [{
                    data: response.badgeCat.catDcount,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(208,59,243,0.5)',
                        'rgba(79,141,67,0.5)',
                        'rgba(35,71,138,0.5)',
                        'rgba(238,61,135,0.5)'
                    ],
                    borderColor: [
                        // 'rgba(255, 99, 132, 0.5)',
                        // 'rgba(54, 162, 235, 0.5)',
                        // 'rgba(255, 206, 86, 0.5)',
                        // 'rgba(75, 192, 192, 0.5)',
                        // 'rgba(153, 102, 255, 0.5)',
                        // 'rgba(255, 159, 64, 0.5)',
                        // 'rgba(208,59,243,0.5)',
                        // 'rgba(79,141,67,0.5)',
                        // 'rgba(35,71,138,0.5)',
                        // 'rgba(238,61,135,0.5)'
                    ],
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: response.badgeCat.catD
            };
            var badgePieOptions = {
                responsive: true,
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            };
            if ($("#pieBadge1").length) {
                var pieBadge1 = $("#pieBadge1").get(0).getContext("2d");
                var pieBadge1c = new Chart(pieBadge1, {
                    type: 'pie',
                    data: badgePie1Data,
                    options: badgePieOptions
                });
            }
            if ($("#pieBadge2").length) {
                var pieBadge2 = $("#pieBadge2").get(0).getContext("2d");
                var pieBadge2c = new Chart(pieBadge2, {
                    type: 'pie',
                    data: badgePie2Data,
                    options: badgePieOptions
                });
            }
            if ($("#pieBadge3").length) {
                var pieBadge3 = $("#pieBadge3").get(0).getContext("2d");
                var pieBadge3c = new Chart(pieBadge3, {
                    type: 'pie',
                    data: badgePie3Data,
                    options: badgePieOptions
                });
            }
            if ($("#pieBadge4").length) {
                var pieBadge4 = $("#pieBadge4").get(0).getContext("2d");
                var pieBadge4c = new Chart(pieBadge4, {
                    type: 'pie',
                    data: badgePie4Data,
                    options: badgePieOptions
                });
            }

            // 用户任务完成数量
            var taskFinishData = {
                labels: response.users.ids,
                datasets: [{
                    label: '任务完成数',
                    data: response.task.userFinishCount,
                    backgroundColor:
                        new Array(parseInt(response.users.idlength)).fill('rgba(225,205,160,0.2)'),
                    borderColor:
                        new Array(parseInt(response.users.idlength)).fill('rgb(154,151,94)'),
                    borderWidth: 1,
                    fill: false
                }]
            };

            var taskFinishOptions = {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                        },
                        gridLines: {
                            drawOnChartArea: false
                        },
                        minInterval: 1
                    }],
                    xAxes: [{
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }],
                },
                legend: {
                    display: true
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }

            };

            if ($("#taskFinishCountChart").length) {
                var taskFinishCountChart = $("#taskFinishCountChart").get(0).getContext("2d");
                var taskFinishCountChartc = new Chart(taskFinishCountChart, {
                    type: 'bar',
                    data: taskFinishData,
                    options: taskFinishOptions
                });
            }

            // 任务完成人数
            var taskFinishUserData = {
                labels: response.task.taskFinishids,
                datasets: [{
                    label: '任务完成人数',
                    data: response.task.taskFinishCount,
                    backgroundColor:
                        new Array(parseInt(response.users.idlength)).fill('rgba(224,151,118,0.2)'),
                    borderColor:
                        new Array(parseInt(response.users.idlength)).fill('rgb(77,39,24)'),
                    borderWidth: 1,
                    fill: false
                }]
            };

            var taskFinishUserOptions = {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                        },
                        gridLines: {
                            drawOnChartArea: false
                        },
                        minInterval: 1
                    }],
                    xAxes: [{
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }],
                },
                legend: {
                    display: true
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }

            };
            if ($("#taskFinishUserCountChart").length) {
                var taskFinishUserCountChart = $("#taskFinishUserCountChart").get(0).getContext("2d");
                var taskFinishCountUserChartc = new Chart(taskFinishUserCountChart, {
                    type: 'bar',
                    data: taskFinishUserData,
                    options: taskFinishUserOptions
                });
            }

            // 问答挑战
            var challengePieData = {
                datasets: [{
                    data: [response.challenge.question, response.challenge.answer],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: [
                    '问答挑战发布量',
                    '问答挑战参与量',
                ]
            };

            var challengePieOptions = {
                responsive: true,
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            };
            if ($("#challengePieChart").length) {
                var challengePieChart = $("#challengePieChart").get(0).getContext("2d");
                var tchallengePieChartc = new Chart(challengePieChart, {
                    type: 'doughnut',
                    data: challengePieData,
                    options: challengePieOptions
                });
            }


        })

    });

});