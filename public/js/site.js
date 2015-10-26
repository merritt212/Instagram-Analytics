$(function(){
	var SITEURL = "http://104.131.134.53/";
	var timezoneOffset = 7*60*60;
	var chartOverallFollowers = $('#chart-overall-followers');
	var accountId = $('#accountId').val();
	console.log($('#followerCount').val());	

	// Init global highcharts settings
	Highcharts.setOptions({
		lang: {
			thousandsSep: ','
		}
	});

	var runTimestamp = Math.floor(Date.now() / 1000);

	// Initialize the graph with the past 3 days as the default
	pullFollowerCounts(accountId, runTimestamp - 3*24*60*60, runTimestamp, function(data){ loadZoomChart(chartOverallFollowers, data, "Total Number of Followers"); });
	$('#overall-followers-daterange').daterangepicker();

	$('#overall-followers-daterange').on('apply.daterangepicker', function(ev, picker){
		$('button.overall-followers-range').removeClass("active");
		$('#overall-followers-daterange span').html("  " + picker.startDate.format("MMMM D YYYY") + " - " + picker.endDate.format("MMMM D YYYY"));
		pullFollowerCounts(accountId, picker.startDate.unix(), picker.endDate.unix(), function(data){ loadZoomChart(chartOverallFollowers, data, "Total Number of Followers"); });
	});

	$('button.overall-followers-range').on('click', function(){
		$('#overall-followers-daterange span').html("");
		$('button.overall-followers-range').removeClass("active");
		$(this).addClass("active");

		var now = Math.floor(Date.now() / 1000);
		if ($(this).attr("timeinterval-value") > 0){
			pullFollowerCounts(accountId, now - $(this).attr("timeinterval-value"), now, function(data){ loadZoomChart(chartOverallFollowers, data, "Total Number of Followers"); });
		} else{
			pullFollowerCounts(accountId, 0, now, function(data){ loadZoomChart(chartOverallFollowers, data); });
		}
	});

	$('button.show-post-analytics').on('click', function(){
		$('#post-analytics-modal .modal-body').html("");
		$('#post-analytics-modal').modal("toggle");

		var now = Math.floor(Date.now() / 1000);
		var start = now - 3*24*60*60;

		$.ajax({
			url: SITEURL + "ping/getPostCounts.php",
			method: "GET",
			data: {postId : $(this).attr("post-id"), start : start, end : now},
			success: function(response){
				data = jQuery.parseJSON(response);
				if (data.status == "success"){
					console.log(data);
					$('#post-analytics-modal .modal-body').html('<div id="post-analytics-chart"></div>');
					loadZoomChart($('#post-analytics-chart'), formatTimeseriesData(data.data), "", "# Likes");
				}
			}
		});
	});

	$('#live-overall-followers').highcharts({
        chart: {
            type: 'spline',
            animation: Highcharts.svg, // don't animate in old IE
            marginRight: 10,
            events: {
                load: function () {
                    // set up the updating of the chart each second
                    var series = this.series[0];
                    setInterval(function(){
                    	var now = Date.now();
                    	$.ajax({
							url: SITEURL + "ping/getCurrentFollowerCount.php",
							method: "GET",
							data: {accountId: accountId},
							success: function(response){
								console.log(response);
								var data = jQuery.parseJSON(response);
								if (data.status == "success"){
									series.addPoint([now, data.followerCount[1]], true, series.points.length > 24);
								}
							}
						});
                    }, 5000);
                }
            }
        },
        title: {
            text: 'Live Overall Followers'
        },
        xAxis: {
            type: 'datetime'
        },
        yAxis: {
        	allowDecimals: false,
            title: {
                text: '# Followers'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            formatter: function () {
                return '<b>' + Highcharts.numberFormat(this.y, 2) + ' Followers</b> at<br />' +
                    Highcharts.dateFormat('%H:%M:%S', this.x);
            }
        },
        legend: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        series: [{
            name: 'Live Overall Followers',
            data: [[Math.floor(Date.now() / 1000) * 1000, parseInt($('#followerCount').val())]]
        }]
    });

	function pullFollowerCounts(accountId, start, end, callback){
		$.ajax({
			url: SITEURL + "ping/getFollowerCounts.php",
			method: "GET",
			data: {accountId : accountId, start : start, end : end},
			success: function(response){
				var data = jQuery.parseJSON(response);
				if (data.status == "success"){
					var timeseries = formatTimeseriesData(data.data);
					callback(timeseries);
				}
			}
		});
	}

	function formatTimeseriesData(data){
		var timeseries = [];
		$.each(data, function(index, value){
			var time = Math.floor((value[0] - timezoneOffset) / 60) * 60 * 1000;
			timeseries.push([time, parseInt(value[1])]);
		});
		return timeseries;
	}

	function loadZoomChart(chart, data, title, yAxisTitle){
		title = title || "";
		yAxisTitle = yAxisTitle || "#Followers";

		chart.highcharts({
			chart: {
				zoomType: 'x'
			},
			title: {
				text: title
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				allowDecimals: false,
				title: {
					text: yAxisTitle
				}
			},
			plotOptions: {
				area: {
					fillColor: {
						linearGradient: {
							x1: 0,
							y1: 0,
							x2: 0,
							y2: 1
						},
						stops: [
							[0, Highcharts.getOptions().colors[0]],
							[1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
						]
					},
					marker: {
						radius: 2
					},
					lineWidth: 1,
					states: {
						hover: {
							lineWidth: 1
						}
					},
					threshold: null
				}
			},
			series: [{
				type: 'area',
				data: data,
				showInLegend: false
			}]
		});
	}
});