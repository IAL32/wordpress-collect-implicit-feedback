(function ($, window, document) {

    $(document).ready(function () {
        // set the dimensions and margins of the graph
        var vMargin = { mTop: 30, mRight: 30, mBottom: 30, mLeft: 50 },
            vWidth = 450 - vMargin.mLeft - vMargin.mRight,
            vHeight = 450 - vMargin.mTop - vMargin.mBottom;

        var vTimeEnd = moment();
        var vTimeStart = moment().subtract(1, "week");

        // append the svg object to the body of the page
        var svg = d3.select("#scroll-time-heatmap")
            .append("svg")
            .attr("width", vWidth + vMargin.mLeft + vMargin.mRight)
            .attr("height", vHeight + vMargin.mTop + vMargin.mBottom)
            .append("g")
            .attr("transform",
                "translate(" + vMargin.mLeft + "," + vMargin.mTop + ")");

        // Labels of row and columns
        let vWeekDays = moment.weekdays();
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

        // Build X scales and axis:
        var vYAxis = d3.scaleBand()
            .range([vHeight, 0])
            .domain(vHoursInDay)
            .padding(0.01);
        svg.append("g")
            .call(d3.axisLeft(vYAxis));

        // Build color scale
        var vColor = d3.scaleLinear()
            .range(["white", "#69b3a2"])
            .domain([1, 100])

        $.ajax({
            type: "GET",
            url: gCoimf.mSiteURL + "/wp-json/coimf/v1/admin/get-actions/",
            data: {
                // FIXME: possible SQL injection
                "filter": {
                    "action_type":  "= " + 2,
                    "time_start":   ">= '" + vTimeStart.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                    "time_end":     "<= '" + vTimeEnd.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                }
            },
            beforeSend: function(aXhr) {
                aXhr.setRequestHeader("X-WP-Nonce", cNonce);
            },
        }).then(function (aResponse) {
            let vData = aResponse.data;

            svg.selectAll()
                .data(vData, function (aItem) {
                    return aItem.group + ':' + aItem.variable;
                })
                .enter()
                .append("rect")
                .attr("x", function (aItem) {
                    let vItemTimeStart = moment(aItem.time_start).format("dddd");
                    return vXAxis(vItemTimeStart);
                })
                .attr("y", function (aItem) {
                    let vItemTimeEnd = moment(aItem.time_end).format("HH");
                    return vYAxis(vItemTimeEnd);
                })
                .attr("width", vXAxis.bandwidth())
                .attr("height", vYAxis.bandwidth())
                .style("fill", function (aItem) {
                    let vItemValueParsed = JSON.parse(aItem.value);
                    return vColor(vItemValueParsed.pageTime)
                });
        });
    });

})(jQuery, window, document, undefined);
