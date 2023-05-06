$(function() {
  /* ChartJS
   * -------
   * Data and config for chartjs
   */
  'use strict';
  $(function() {
    $.post('../../ajax/chart.php', function (response) {
      response = JSON.parse(response);

      var data = {
        labels: response.users.ids,
        datasets: [{
          label: '提问数',
          data: response.users.qcounts,
          backgroundColor:
              new Array(parseInt(response.users.idlength)).fill('rgba(45,142,164,0.2)'),
          //     ['rgba(255, 99, 132, 0.2)',
          //   'rgba(54, 162, 235, 0.2)',
          //   'rgba(255, 206, 86, 0.2)',
          //   'rgba(75, 192, 192, 0.2)',
          //   'rgba(153, 102, 255, 0.2)',
          //   'rgba(255, 159, 64, 0.2)'
          // ],
          borderColor:
              new Array(parseInt(response.users.idlength)).fill('rgb(115,132,217)'),
          //     [
          //   'rgba(255,99,132,1)',
          //   'rgba(54, 162, 235, 1)',
          //   'rgba(255, 206, 86, 1)',
          //   'rgba(75, 192, 192, 1)',
          //   'rgba(153, 102, 255, 1)',
          //   'rgba(255, 159, 64, 1)'
          // ],
          borderWidth: 1,
          fill: false
        },{
          label: '回答数',
          data: response.users.acounts,
          backgroundColor:
              new Array(parseInt(response.users.idlength)).fill('rgba(255, 159, 64, 0.2)'),
          //     [
          //   'rgba(255, 99, 132, 0.2)',
          //   'rgba(54, 162, 235, 0.2)',
          //   'rgba(255, 206, 86, 0.2)',
          //   'rgba(75, 192, 192, 0.2)',
          //   'rgba(153, 102, 255, 0.2)',
          //   'rgba(255, 159, 64, 0.2)'
          // ],
          borderColor:
              new Array(parseInt(response.users.idlength)).fill('rgba(255, 159, 64, 1)'),
          //     [
          //   'rgba(255,99,132,1)',
          //   'rgba(54, 162, 235, 1)',
          //   'rgba(255, 206, 86, 1)',
          //   'rgba(75, 192, 192, 1)',
          //   'rgba(153, 102, 255, 1)',
          //   'rgba(255, 159, 64, 1)'
          // ],
          borderWidth: 1,
          fill: false
        }]
      };
      var multiLineData = {
        //labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
        labels: response.baseChart.totaldate,
        datasets: [{
          label: '提问数',
          data: response.baseChart.qcount,
          borderColor: [
            '#587ce4'
          ],
          borderWidth: 2,
          fill: false
        },
          {
            label: '回答数',
            data: response.baseChart.acount,
            borderColor: [
              '#ede190'
            ],
            borderWidth: 2,
            fill: false
          },
          {
            label: '评论数',
            data: response.baseChart.ccount,
            borderColor: [
              '#f44252'
            ],
            borderWidth: 2,
            fill: false
          }
        ]
      };
      var options = {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
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

      // var timeLineData = {
      //   //labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
      //   labels: response.baseChart.totaldate,
      //   datasets: [{
      //     label: '提问数',
      //     data: response.timeChart.qcount,
      //     borderColor: [
      //       '#587ce4'
      //     ],
      //     borderWidth: 2,
      //     fill: false
      //   },
      //     {
      //       label: '回答数',
      //       data: response.timeChart.acount,
      //       borderColor: [
      //         '#ede190'
      //       ],
      //       borderWidth: 2,
      //       fill: false
      //     },
      //     {
      //       label: '评论数',
      //       data: response.timeChart.ccount,
      //       borderColor: [
      //         '#f44252'
      //       ],
      //       borderWidth: 2,
      //       fill: false
      //     }
      //   ]
      // };
      var timeLineOptions = {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
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

      var doughnutPieData = {
        datasets: [{
          data: [parseInt(response.pie4.question), parseInt(response.pie4.answer), parseInt(response.pie4.comment)],
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
          '问题数',
          '回答数',
          '评论数',
        ]
      };

      // 饼图
      var doughnutPie1Data = {
        datasets: [{
          data: [parseInt(response.pie1.answered), response.pie1.unanswered],
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
          '已回答问题',
          '未回答问题',
        ]
      };
      var doughnutPie2Data = {
        datasets: [{
          data: [parseInt(response.pie2.solved), response.pie2.unsolved],
          backgroundColor: [
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
          ],
          borderColor: [
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
          ],
        }],

        // These labels appear in the legend and in the tooltips when hovering different arcs
        labels: [
          '问题被解决数',
          '问题未解决数',
        ]
      };
      var doughnutPie3Data = {
        datasets: [{
          data: [parseInt(response.pie3.voted), response.pie3.unvoted],
          backgroundColor: [
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
          ],
          borderColor: [
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
          ],
        }],

        // These labels appear in the legend and in the tooltips when hovering different arcs
        labels: [
          '已投票答案',
          '未投票答案',
        ]
      };
      var doughnutPieOptions = {
        responsive: true,
        animation: {
          animateScale: true,
          animateRotate: true
        }
      };
      var areaData = {
        labels: ["2013", "2014", "2015", "2016", "2017"],
        datasets: [{
          label: '# of Votes',
          data: [12, 19, 3, 5, 2, 3],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1,
          fill: true, // 3: no fill
        }]
      };

      var areaOptions = {
        plugins: {
          filler: {
            propagate: true
          }
        }
      }

      var multiAreaData = {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: 'Facebook',
          data: [8, 11, 13, 15, 12, 13, 16, 15, 13, 19, 11, 14],
          borderColor: ['rgba(255, 99, 132, 0.5)'],
          backgroundColor: ['rgba(255, 99, 132, 0.5)'],
          borderWidth: 1,
          fill: true
        },
          {
            label: 'Twitter',
            data: [7, 17, 12, 16, 14, 18, 16, 12, 15, 11, 13, 9],
            borderColor: ['rgba(54, 162, 235, 0.5)'],
            backgroundColor: ['rgba(54, 162, 235, 0.5)'],
            borderWidth: 1,
            fill: true
          },
          {
            label: 'Linkedin',
            data: [6, 14, 16, 20, 12, 18, 15, 12, 17, 19, 15, 11],
            borderColor: ['rgba(255, 206, 86, 0.5)'],
            backgroundColor: ['rgba(255, 206, 86, 0.5)'],
            borderWidth: 1,
            fill: true
          }
        ]
      };

      var multiAreaOptions = {
        plugins: {
          filler: {
            propagate: true
          }
        },
        elements: {
          point: {
            radius: 0
          }
        },
        scales: {
          xAxes: [{
            gridLines: {
              display: false
            }
          }],
          yAxes: [{
            gridLines: {
              display: false
            }
          }]
        }
      }

      var scatterChartData = {
        datasets: [{
          label: 'First Dataset',
          data: [{
            x: -10,
            y: 0
          },
            {
              x: 0,
              y: 3
            },
            {
              x: -25,
              y: 5
            },
            {
              x: 40,
              y: 5
            }
          ],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)'
          ],
          borderColor: [
            'rgba(255,99,132,1)'
          ],
          borderWidth: 1
        },
          {
            label: 'Second Dataset',
            data: [{
              x: 10,
              y: 5
            },
              {
                x: 20,
                y: -30
              },
              {
                x: -25,
                y: 15
              },
              {
                x: -10,
                y: 5
              }
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.2)',
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
            ],
            borderWidth: 1
          }
        ]
      }

      var scatterChartOptions = {
        scales: {
          xAxes: [{
            type: 'linear',
            position: 'bottom'
          }]
        }
      }
      // Get context with jQuery - using jQuery's .get() method.
      if ($("#barChart").length) {
        var barChartCanvas = $("#barChart").get(0).getContext("2d");
        // This will get the first returned node in the jQuery collection.
        var barChart = new Chart(barChartCanvas, {
          type: 'bar',
          data: data,
          options: options
        });
      }

      if ($("#lineChart").length) {
        var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
        var lineChart = new Chart(lineChartCanvas, {
          type: 'line',
          data: data,
          options: options
        });
      }

      if ($("#linechart-multi").length) {
        var multiLineCanvas = $("#linechart-multi").get(0).getContext("2d");
        var lineChart = new Chart(multiLineCanvas, {
          type: 'line',
          data: multiLineData,
          options: options
        });
      }

      if ($("#areachart-multi").length) {
        var multiAreaCanvas = $("#areachart-multi").get(0).getContext("2d");
        var multiAreaChart = new Chart(multiAreaCanvas, {
          type: 'line',
          data: multiAreaData,
          options: multiAreaOptions
        });
      }

      if ($("#doughnutChart").length) {
        var doughnutChartCanvas = $("#doughnutChart").get(0).getContext("2d");
        var doughnutChart = new Chart(doughnutChartCanvas, {
          type: 'doughnut',
          data: doughnutPieData,
          options: doughnutPieOptions
        });
      }

      if ($("#pieChart").length) {
        var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas, {
          type: 'pie',
          data: doughnutPieData,
          options: doughnutPieOptions
        });
      }

      if ($("#areaChart").length) {
        var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
        var areaChart = new Chart(areaChartCanvas, {
          type: 'line',
          data: areaData,
          options: areaOptions
        });
      }

      if ($("#scatterChart").length) {
        var scatterChartCanvas = $("#scatterChart").get(0).getContext("2d");
        var scatterChart = new Chart(scatterChartCanvas, {
          type: 'scatter',
          data: scatterChartData,
          options: scatterChartOptions
        });
      }

      if ($("#browserTrafficChart").length) {
        var doughnutChartCanvas = $("#browserTrafficChart").get(0).getContext("2d");
        var doughnutChart = new Chart(doughnutChartCanvas, {
          type: 'doughnut',
          data: browserTrafficData,
          options: doughnutPieOptions
        });
      }

      if ($("#pieChart1").length) {
        var pieChartCanvas = $("#pieChart1").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas, {
          type: 'pie',
          data: doughnutPie1Data,
          options: doughnutPieOptions
        });
      }

      if ($("#pieChart2").length) {
        var pieChartCanvas = $("#pieChart2").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas, {
          type: 'pie',
          data: doughnutPie2Data,
          options: doughnutPieOptions
        });
      }

      if ($("#pieChart3").length) {
        var pieChartCanvas = $("#pieChart3").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas, {
          type: 'pie',
          data: doughnutPie3Data,
          options: doughnutPieOptions
        });
      }

      // 24小时 柱状图
      var stackedBarChartData = {
        labels: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24],
        datasets: [{
          label: '提问数',
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          borderColor: 'rgba(255, 99, 132, 0.8)',
          borderWidth: 1,
          stack: 'Stack 0',
          data: response.timeBar.question,
        }, {
          label: '回答数',
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 0.8)',
          borderWidth: 1,
          stack: 'Stack 0',
          data: response.timeBar.answer,
        }, {
          label: '评论数',
          backgroundColor: 'rgba(255, 206, 86, 0.5)',
          borderColor: 'rgba(255, 206, 86, 0.8)',
          borderWidth: 1,
          stack: 'Stack 0',
          data: response.timeBar.comment,
        }]
      };

      var timeLineChartOption = {
        responsive: true,
        title: {
          display: false,
          text: '堆叠柱状图'
        },
        scales: {
          xAxes: [{
            stacked: true
          }],
          yAxes: [{
            stacked: true
          }]
        }
      }

      if ($("#pieChart3").length) {
        var timeLineChart1 = $("#timeLineChart1").get(0).getContext("2d");
        var timeLineChart = new Chart(timeLineChart1, {
          type: 'bar',
          data: stackedBarChartData,
          options: timeLineChartOption
        });
      }

    })

  });

});

function updateDate() {
  let startDate = document.getElementById("chart-start-date").value;
  let endDate = document.getElementById("chart-end-date").value;
  var start;
  if (startDate == null || startDate.length == 0) {
    start = new Date(2023, 1, 19);
  } else {
    start = new Date(startDate + "T00:00:00");
  }
  var end;
  var now = new Date();
  if (endDate == null || endDate.length == 0) {
    end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  } else {
    end = new Date(endDate + "T00:00:00");
  }
  if (start < new Date(2023, 1, 19)) {
    window.alert("开始时间小于2023年2月19号，请输入合理时间");
  }
  if (start > now) {
    window.alert("开始时间大于现在时间，请输入合理日期");
    return;
  }
  if (start > end) {
    window.alert("开始时间大于结束时间，请输入合理日期");
    return;
  }
  if (end > now) {
    window.alert("结束时间大于现在时间，请输入合理日期");
    return;
  }
  var params = {};
  params.startDate = start.getFullYear() + "-" + (start.getMonth()+1) + "-" + start.getDate();
  params.endDate = end.getFullYear() + "-" + (end.getMonth()+1) + "-" + end.getDate();
  $.post("../../ajax/chartDate.php", params, function (response) {
    response = JSON.parse(response);
    console.log(response);
    var data = {
      labels: response.users.ids,
      datasets: [{
        label: '提问数',
        data: response.users.qcounts,
        backgroundColor:
            new Array(parseInt(response.users.idlength)).fill('rgba(45,142,164,0.2)'),
        //     ['rgba(255, 99, 132, 0.2)',
        //   'rgba(54, 162, 235, 0.2)',
        //   'rgba(255, 206, 86, 0.2)',
        //   'rgba(75, 192, 192, 0.2)',
        //   'rgba(153, 102, 255, 0.2)',
        //   'rgba(255, 159, 64, 0.2)'
        // ],
        borderColor:
            new Array(parseInt(response.users.idlength)).fill('rgb(115,132,217)'),
        //     [
        //   'rgba(255,99,132,1)',
        //   'rgba(54, 162, 235, 1)',
        //   'rgba(255, 206, 86, 1)',
        //   'rgba(75, 192, 192, 1)',
        //   'rgba(153, 102, 255, 1)',
        //   'rgba(255, 159, 64, 1)'
        // ],
        borderWidth: 1,
        fill: false
      },{
        label: '回答数',
        data: response.users.acounts,
        backgroundColor:
            new Array(parseInt(response.users.idlength)).fill('rgba(255, 159, 64, 0.2)'),
        //     [
        //   'rgba(255, 99, 132, 0.2)',
        //   'rgba(54, 162, 235, 0.2)',
        //   'rgba(255, 206, 86, 0.2)',
        //   'rgba(75, 192, 192, 0.2)',
        //   'rgba(153, 102, 255, 0.2)',
        //   'rgba(255, 159, 64, 0.2)'
        // ],
        borderColor:
            new Array(parseInt(response.users.idlength)).fill('rgba(255, 159, 64, 1)'),
        //     [
        //   'rgba(255,99,132,1)',
        //   'rgba(54, 162, 235, 1)',
        //   'rgba(255, 206, 86, 1)',
        //   'rgba(75, 192, 192, 1)',
        //   'rgba(153, 102, 255, 1)',
        //   'rgba(255, 159, 64, 1)'
        // ],
        borderWidth: 1,
        fill: false
      }]
    };
    var multiLineData = {
      //labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
      labels: response.baseChart.totaldate,
      datasets: [{
        label: '提问数',
        data: response.baseChart.qcount,
        borderColor: [
          '#587ce4'
        ],
        borderWidth: 2,
        fill: false
      },
        {
          label: '回答数',
          data: response.baseChart.acount,
          borderColor: [
            '#ede190'
          ],
          borderWidth: 2,
          fill: false
        },
        {
          label: '评论数',
          data: response.baseChart.ccount,
          borderColor: [
            '#f44252'
          ],
          borderWidth: 2,
          fill: false
        }
      ]
    };
    var options = {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
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

    // var timeLineData = {
    //   //labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
    //   labels: response.baseChart.totaldate,
    //   datasets: [{
    //     label: '提问数',
    //     data: response.timeChart.qcount,
    //     borderColor: [
    //       '#587ce4'
    //     ],
    //     borderWidth: 2,
    //     fill: false
    //   },
    //     {
    //       label: '回答数',
    //       data: response.timeChart.acount,
    //       borderColor: [
    //         '#ede190'
    //       ],
    //       borderWidth: 2,
    //       fill: false
    //     },
    //     {
    //       label: '评论数',
    //       data: response.timeChart.ccount,
    //       borderColor: [
    //         '#f44252'
    //       ],
    //       borderWidth: 2,
    //       fill: false
    //     }
    //   ]
    // };
    var timeLineOptions = {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
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

    var doughnutPieData = {
      datasets: [{
        data: [parseInt(response.pie4.question), parseInt(response.pie4.answer), parseInt(response.pie4.comment)],
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
        '问题数',
        '回答数',
        '评论数',
      ]
    };

    // 饼图
    var doughnutPie1Data = {
      datasets: [{
        data: [parseInt(response.pie1.answered), response.pie1.unanswered],
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
        '已回答问题',
        '未回答问题',
      ]
    };
    var doughnutPie2Data = {
      datasets: [{
        data: [parseInt(response.pie2.solved), response.pie2.unsolved],
        backgroundColor: [
          'rgba(255, 206, 86, 0.5)',
          'rgba(75, 192, 192, 0.5)',
          'rgba(153, 102, 255, 0.5)',
          'rgba(255, 159, 64, 0.5)',
          'rgba(255, 99, 132, 0.5)',
          'rgba(54, 162, 235, 0.5)',
        ],
        borderColor: [
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(255,99,132,1)',
          'rgba(54, 162, 235, 1)',
        ],
      }],

      // These labels appear in the legend and in the tooltips when hovering different arcs
      labels: [
        '问题被解决数',
        '问题未解决数',
      ]
    };
    var doughnutPie3Data = {
      datasets: [{
        data: [parseInt(response.pie3.voted), response.pie3.unvoted],
        backgroundColor: [
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(255,99,132,1)',
          'rgba(54, 162, 235, 1)',
        ],
        borderColor: [
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(255,99,132,1)',
          'rgba(54, 162, 235, 1)',
        ],
      }],

      // These labels appear in the legend and in the tooltips when hovering different arcs
      labels: [
        '已投票答案',
        '未投票答案',
      ]
    };
    var doughnutPieOptions = {
      responsive: true,
      animation: {
        animateScale: true,
        animateRotate: true
      }
    };
    var areaData = {
      labels: ["2013", "2014", "2015", "2016", "2017"],
      datasets: [{
        label: '# of Votes',
        data: [12, 19, 3, 5, 2, 3],
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(255, 206, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(255, 159, 64, 0.2)'
        ],
        borderColor: [
          'rgba(255,99,132,1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1,
        fill: true, // 3: no fill
      }]
    };

    var areaOptions = {
      plugins: {
        filler: {
          propagate: true
        }
      }
    }

    var multiAreaData = {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      datasets: [{
        label: 'Facebook',
        data: [8, 11, 13, 15, 12, 13, 16, 15, 13, 19, 11, 14],
        borderColor: ['rgba(255, 99, 132, 0.5)'],
        backgroundColor: ['rgba(255, 99, 132, 0.5)'],
        borderWidth: 1,
        fill: true
      },
        {
          label: 'Twitter',
          data: [7, 17, 12, 16, 14, 18, 16, 12, 15, 11, 13, 9],
          borderColor: ['rgba(54, 162, 235, 0.5)'],
          backgroundColor: ['rgba(54, 162, 235, 0.5)'],
          borderWidth: 1,
          fill: true
        },
        {
          label: 'Linkedin',
          data: [6, 14, 16, 20, 12, 18, 15, 12, 17, 19, 15, 11],
          borderColor: ['rgba(255, 206, 86, 0.5)'],
          backgroundColor: ['rgba(255, 206, 86, 0.5)'],
          borderWidth: 1,
          fill: true
        }
      ]
    };

    var multiAreaOptions = {
      plugins: {
        filler: {
          propagate: true
        }
      },
      elements: {
        point: {
          radius: 0
        }
      },
      scales: {
        xAxes: [{
          gridLines: {
            display: false
          }
        }],
        yAxes: [{
          gridLines: {
            display: false
          }
        }]
      }
    }

    var scatterChartData = {
      datasets: [{
        label: 'First Dataset',
        data: [{
          x: -10,
          y: 0
        },
          {
            x: 0,
            y: 3
          },
          {
            x: -25,
            y: 5
          },
          {
            x: 40,
            y: 5
          }
        ],
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)'
        ],
        borderColor: [
          'rgba(255,99,132,1)'
        ],
        borderWidth: 1
      },
        {
          label: 'Second Dataset',
          data: [{
            x: 10,
            y: 5
          },
            {
              x: 20,
              y: -30
            },
            {
              x: -25,
              y: 15
            },
            {
              x: -10,
              y: 5
            }
          ],
          backgroundColor: [
            'rgba(54, 162, 235, 0.2)',
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
          ],
          borderWidth: 1
        }
      ]
    }

    var scatterChartOptions = {
      scales: {
        xAxes: [{
          type: 'linear',
          position: 'bottom'
        }]
      }
    }
    // Get context with jQuery - using jQuery's .get() method.
    if ($("#barChart").length) {
      var barChartCanvas = $("#barChart").get(0).getContext("2d");
      // This will get the first returned node in the jQuery collection.
      var barChart = new Chart(barChartCanvas, {
        type: 'bar',
        data: data,
        options: options
      });
    }

    if ($("#lineChart").length) {
      var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
      var lineChart = new Chart(lineChartCanvas, {
        type: 'line',
        data: data,
        options: options
      });
    }

    if ($("#linechart-multi").length) {
      var multiLineCanvas = $("#linechart-multi").get(0).getContext("2d");
      var lineChart = new Chart(multiLineCanvas, {
        type: 'line',
        data: multiLineData,
        options: options
      });
    }

    if ($("#areachart-multi").length) {
      var multiAreaCanvas = $("#areachart-multi").get(0).getContext("2d");
      var multiAreaChart = new Chart(multiAreaCanvas, {
        type: 'line',
        data: multiAreaData,
        options: multiAreaOptions
      });
    }

    if ($("#doughnutChart").length) {
      var doughnutChartCanvas = $("#doughnutChart").get(0).getContext("2d");
      var doughnutChart = new Chart(doughnutChartCanvas, {
        type: 'doughnut',
        data: doughnutPieData,
        options: doughnutPieOptions
      });
    }

    if ($("#pieChart").length) {
      var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: doughnutPieData,
        options: doughnutPieOptions
      });
    }

    if ($("#areaChart").length) {
      var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
      var areaChart = new Chart(areaChartCanvas, {
        type: 'line',
        data: areaData,
        options: areaOptions
      });
    }

    if ($("#scatterChart").length) {
      var scatterChartCanvas = $("#scatterChart").get(0).getContext("2d");
      var scatterChart = new Chart(scatterChartCanvas, {
        type: 'scatter',
        data: scatterChartData,
        options: scatterChartOptions
      });
    }

    if ($("#browserTrafficChart").length) {
      var doughnutChartCanvas = $("#browserTrafficChart").get(0).getContext("2d");
      var doughnutChart = new Chart(doughnutChartCanvas, {
        type: 'doughnut',
        data: browserTrafficData,
        options: doughnutPieOptions
      });
    }

    if ($("#pieChart1").length) {
      var pieChartCanvas = $("#pieChart1").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: doughnutPie1Data,
        options: doughnutPieOptions
      });
    }

    if ($("#pieChart2").length) {
      var pieChartCanvas = $("#pieChart2").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: doughnutPie2Data,
        options: doughnutPieOptions
      });
    }

    if ($("#pieChart3").length) {
      var pieChartCanvas = $("#pieChart3").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: doughnutPie3Data,
        options: doughnutPieOptions
      });
    }

    // 24小时 柱状图
    var stackedBarChartData = {
      labels: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24],
      datasets: [{
        label: '提问数',
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
        borderColor: 'rgba(255, 99, 132, 0.8)',
        borderWidth: 1,
        stack: 'Stack 0',
        data: response.timeBar.question,
      }, {
        label: '回答数',
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 0.8)',
        borderWidth: 1,
        stack: 'Stack 0',
        data: response.timeBar.answer,
      }, {
        label: '评论数',
        backgroundColor: 'rgba(255, 206, 86, 0.5)',
        borderColor: 'rgba(255, 206, 86, 0.8)',
        borderWidth: 1,
        stack: 'Stack 0',
        data: response.timeBar.comment,
      }]
    };

    var timeLineChartOption = {
      responsive: true,
      title: {
        display: false,
        text: '堆叠柱状图'
      },
      scales: {
        xAxes: [{
          stacked: true
        }],
        yAxes: [{
          stacked: true
        }]
      }
    }

    if ($("#pieChart3").length) {
      var timeLineChart1 = $("#timeLineChart1").get(0).getContext("2d");
      var timeLineChart = new Chart(timeLineChart1, {
        type: 'bar',
        data: stackedBarChartData,
        options: timeLineChartOption
      });
    }
  });
}