(function($) {
    'use strict';
    $(function() {
        $.post('../../ajax/table.php', function (response) {
            response = JSON.parse(response);

            // chart
            if ($('#cash-deposits-chart').length) {
                var cashDepositsCanvas = $("#cash-deposits-chart").get(0).getContext("2d");
                var data = {
                    //labels: [ "1", "2", "3", "4", "5", "6", "7"],
                    labels: response.chart1.weekdate,
                    datasets: [
                        {
                            label: '提问数',
                            data: response.chart1.qcount,
                            borderColor: [
                                '#ff4747'
                            ],
                            borderWidth: 2,
                            fill: false,
                            pointBackgroundColor: "#fff"
                        },
                        {
                            label: '回答数',
                            data: response.chart1.acount,
                            borderColor: [
                                '#4d83ff'
                            ],
                            borderWidth: 2,
                            fill: false,
                            pointBackgroundColor: "#fff"
                        },
                        {
                            label: '评论数',
                            data: response.chart1.ccount,
                            borderColor: [
                                '#ffc100'
                            ],
                            borderWidth: 2,
                            fill: false,
                            pointBackgroundColor: "#fff"
                        }
                    ]
                };
                var options = {
                    scales: {
                        yAxes: [{
                            display: true,
                            gridLines: {
                                drawBorder: false,
                                lineWidth: 1,
                                color: "#e9e9e9",
                                zeroLineColor: "#e9e9e9",
                            },
                            ticks: {
                                min: 0,
                                max: parseInt(response.chart1.maxcount),
                                fontColor: "#6c7383",
                                fontSize: 16,
                                fontStyle: 300,
                                padding: 15
                            }
                        }],
                        xAxes: [{
                            display: true,
                            gridLines: {
                                drawBorder: false,
                                lineWidth: 1,
                                color: "#e9e9e9",
                            },
                            ticks : {
                                fontColor: "#6c7383",
                                fontSize: 16,
                                fontStyle: 300,
                                padding: 15
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    legendCallback: function(chart) {
                        var text = [];
                        text.push('<ul class="dashboard-chart-legend">');
                        for(var i=0; i < chart.data.datasets.length; i++) {
                            text.push('<li><span style="background-color: ' + chart.data.datasets[i].borderColor[0] + ' "></span>');
                            if (chart.data.datasets[i].label) {
                                text.push(chart.data.datasets[i].label);
                            }
                        }
                        text.push('</ul>');
                        return text.join("");
                    },
                    elements: {
                        point: {
                            radius: 3
                        },
                        line :{
                            tension: 0
                        }
                    },
                    stepsize: 1,
                    layout : {
                        padding : {
                            top: 0,
                            bottom : -10,
                            left : 0,
                            right: 0
                        }
                    }
                };
                var cashDeposits = new Chart(cashDepositsCanvas, {
                    type: 'line',
                    data: data,
                    options: options
                });
                document.getElementById('cash-deposits-chart-legend').innerHTML = cashDeposits.generateLegend();
            }

            if ($('#total-sales-chart').length) {
                var totalSalesChartCanvas = $("#total-sales-chart").get(0).getContext("2d");

                var data = {
                    labels: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",'10', '11','12', '13', '14', '15','16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26','27','28','29', '30','31', '32', '33', '34', '35', '36', '37','38', '39', '40'],
                    datasets: [
                        {
                            label: '提问数',
                            //data: [42, 42, 30, 30, 18, 22, 16, 21, 22, 22, 22, 20, 24, 20, 18, 22, 30, 34 ,32, 33, 33, 24, 32, 34 , 30, 34, 19 ,34, 18, 10, 22, 24, 20, 22, 20, 21, 10, 10, 5, 9, 14 ],
                            data: response.chart2.qcount,
                            borderColor: [
                                'transparent'
                            ],
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: "rgba(47,91,191,0.77)"
                        },
                        {
                            label: '回答数',
                            data: response.chart2.acount,
                            borderColor: [
                                'transparent'
                            ],
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: "rgba(77,131,255,0.77)"
                        },
                        {
                            label: '评论数',
                            data: response.chart2.ccount,
                            borderColor: [
                                'transparent'
                            ],
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: "rgba(77,131,255,0.43)"
                        },
                        {
                            label: '总数',
                            data: response.chart2.total,
                            borderColor: [
                                'transparent'
                            ],
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: "rgba(98,143,246,0.43)"
                        }
                    ]
                };
                var options = {
                    scales: {
                        yAxes: [{
                            display: false,
                            gridLines: {
                                drawBorder: false,
                                lineWidth: 1,
                                color: "#e9e9e9",
                                zeroLineColor: "#e9e9e9",
                            },
                            ticks: {
                                display : true,
                                min: 0,
                                max: parseInt(response.chart2.maxcount),
                                stepSize: 10,
                                fontColor: "#6c7383",
                                fontSize: 16,
                                fontStyle: 300,
                                padding: 15
                            }
                        }],
                        xAxes: [{
                            display: false,
                            gridLines: {
                                drawBorder: false,
                                lineWidth: 1,
                                color: "#e9e9e9",
                            },
                            ticks : {
                                display: true,
                                fontColor: "#6c7383",
                                fontSize: 16,
                                fontStyle: 300,
                                padding: 15
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    legendCallback: function(chart) {
                        var text = [];
                        text.push('<ul class="dashboard-chart-legend mb-0 mt-4">');
                        for(var i=0; i < chart.data.datasets.length; i++) {
                            text.push('<li><span style="background-color: ' + chart.data.datasets[i].backgroundColor + ' "></span>');
                            if (chart.data.datasets[i].label) {
                                text.push(chart.data.datasets[i].label);
                            }
                        }
                        text.push('</ul>');
                        return text.join("");
                    },
                    elements: {
                        point: {
                            radius: 0
                        },
                        line :{
                            tension: 0
                        }
                    },
                    stepsize: 1,
                    layout : {
                        padding : {
                            top: 0,
                            bottom : 0,
                            left : 0,
                            right: 0
                        }
                    }
                };
                var totalSalesChart = new Chart(totalSalesChartCanvas, {
                    type: 'line',
                    data: data,
                    options: options
                });
                document.getElementById('total-sales-chart-legend').innerHTML = totalSalesChart.generateLegend();
            }

            // table
            const users = response.users;
            const tableBody = document.querySelector('#basic-user-data tbody');
            // 删除原有行
            for (let i = tableBody.rows.length - 1; i >= 0; i--) {
                tableBody.deleteRow(i);
            }

            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
          <td>${user.handle}</td>
          <td>${user.realname}</td>
          <td>${user.qposts}</td>
          <td>${user.aposts}</td>
          <td>${user.cposts}</td>
          <td>${user.totalvotes}</td>
          <td>${user.upvoteds}</td>
          <td>${user.aselecteds}</td>
          <td>${user.totalactiontime}</td>
          <td>${user.logindays}</td>
        `;
                tableBody.appendChild(row);
            });

            const tableBody2 = document.querySelector('#gaming-table-data tbody');
            //删除原有行
            for (let i = tableBody2.rows.length - 1; i >= 0; i--) {
                tableBody2.deleteRow(i);
            }
            var gamedata = response.gamedata;

            gamedata.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
          <td>${user.name}</td>
          <td>${user.realname}</td>
          <td>${user.task}</td>
          <td>${user.challenge}</td>
          <td>${user.badge}</td>
          <td><a href=${user.badgeUrl}><button class="btn btn-primary btn-lg">详情</button></a></td>
        `;
                tableBody2.appendChild(row);

            });

            const tableBody3 = document.querySelector('#task-table-data tbody');
            //删除原有行
            for (let i = tableBody3.rows.length - 1; i >= 0; i--) {
                tableBody3.deleteRow(i);
            }
            var taskdata = response.taskdata;

            taskdata.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
          <td>${user.id}</td>
          <td>${user.started}</td>
          <td>${user.ended}</td>
          <td>${user.description}</td>
          <td>${user.count}</td>
          <td>${user.reward}</td>
          <td>${user.finish}</td>
        `;
                tableBody3.appendChild(row);

            });


        })


    });
})(jQuery);