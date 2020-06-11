(function ($, window, document) {

    $(document).ready(function () {
        // set the dimensions and margins of the graph
        var vMargin = { mTop: 30, mRight: 100, mBottom: 50, mLeft: 50 },
            vWidth = 450 - vMargin.mLeft - vMargin.mRight,
            vHeight = 450 - vMargin.mTop - vMargin.mBottom;

        let vTimeStart = moment().subtract(7, "days");
        let vTimeEnd = moment();

        // append the svg object to the body of the page
        var svg = d3.select("#scroll-time-heatmap")
            .append("svg")
            .attr("width", vWidth + vMargin.mLeft + vMargin.mRight)
            .attr("height", vHeight + vMargin.mTop + vMargin.mBottom)
            .append("g")
            .attr("transform",
                "translate(" + vMargin.mLeft + "," + vMargin.mTop + ")");

        // Labels of row and columns
        let vWeekDays = []
        for (let vIdx = 1; vIdx <= 7; vIdx++) {
            vWeekDays.push(moment().subtract(7 - vIdx, "days").format("DD/MM"));
        }

        let vHoursInDay = [];
        for (let vIdx = 23; vIdx >= 0; vIdx--) {
            vHoursInDay.push(vIdx.pad() + ":00");
        }

        // Build X scales and axis:
        var vXAxis = d3.scaleBand()
            .range([0, vWidth])
            .domain(vWeekDays)
            .padding(0.01);
        svg.append("g")
            .attr("transform", "translate(0," + vHeight + ")")
            .call(d3.axisBottom(vXAxis))
            .selectAll("text")
            .attr("transform", "translate(-10,0)rotate(-45)")
            .style("text-anchor", "end")

        // Build X scales and axis:
        var vYAxis = d3.scaleBand()
            .range([vHeight, 0])
            .domain(vHoursInDay)
            .padding(0.01);
        svg.append("g")
            .call(d3.axisLeft(vYAxis));

        $.ajax({
            type: "GET",
            url: gCoimf.mSiteURL + "/wp-json/coimf/v1/admin/get-actions/",
            data: {
                // FIXME: possible SQL injection
                "select": [
                    "time_end",
                    "SUM(JSON_EXTRACT(value, \"$.pageTime\")) as page_time_sum",
                ],
                "filter": {
                    "action_type": "= " + 2,
                    "time_start": ">= '" + vTimeStart.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                    "time_end": "<= '" + vTimeEnd.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                },
                "groupby": [
                    "hour( time_end )",
                    "day( time_end )",
                ],
                "limit": -1,
                "offset": -1,
            },
            beforeSend: function (aXhr) {
                aXhr.setRequestHeader("X-WP-Nonce", cLineplotNonce);
            },
        }).then(function (aResponse) {
            let vData = aResponse.data;

            var vDataMax = d3.max(vData, function (aItem) {
                return +aItem.page_time_sum;
            });

            // Build color scale
            var vColor = d3.scaleLinear()
                .range(["white", "#69b3a2"])
                .domain([0, vDataMax])

            // Adding legend
            svg.append("g")
                .attr("class", "legendLinear")
                .attr("transform", "translate(" + (vWidth + 20) + ", " + vMargin.mTop + ")");

            var legendLinear = d3.legendColor()
                .labelFormat(d3.format(".2s"))
                .shapeWidth(30)
                .title("Seconds")
                .orient("vertical")
                .scale(vColor);

            svg.select(".legendLinear")
                .call(legendLinear);

            svg.selectAll()
                .data(vData)
                .enter()
                .append("rect")
                .attr("x", function (aItem) {
                    return vXAxis(moment(aItem.time_end).format("DD/MM"));
                })
                .attr("y", function (aItem) {
                    return vYAxis(moment(aItem.time_end).format("HH") + ":00");
                })
                .attr("width", vXAxis.bandwidth())
                .attr("height", vYAxis.bandwidth())
                .style("fill", function (aItem) {
                    return vColor(aItem.page_time_sum);
                });
        });
    });

})(jQuery, window, document, undefined);
