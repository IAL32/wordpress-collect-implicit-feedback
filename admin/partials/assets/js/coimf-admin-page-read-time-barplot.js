(function ($, window, document) {
    "use strict";

    $(document).ready(function () {
        // set the dimensions and margins of the graph
        var vMargin = { mTop: 30, mRight: 30, mBottom: 50, mLeft: 50 },
            vWidth = 450 - vMargin.mLeft - vMargin.mRight,
            vHeight = 450 - vMargin.mTop - vMargin.mBottom;

        var vTimeEnd = moment();
        var vTimeStart = moment().subtract(1, "week");

        // append the svg object to the body of the page
        var svg = d3.select("#scroll-time-barplot")
            .append("svg")
            .attr("width", vWidth + vMargin.mLeft + vMargin.mRight)
            .attr("height", vHeight + vMargin.mTop + vMargin.mBottom)
            .append("g")
            .attr("transform",
                "translate(" + vMargin.mLeft + "," + vMargin.mTop + ")");

        // Labels of rows
        let vWeekDays = moment.weekdays();

        // Build X scales and axis:
        let vXAxis = d3.scaleBand()
            .range([0, vWidth])
            .domain(vWeekDays)
            .padding(0.01);
        svg.append("g")
            .attr("transform", "translate(0," + vHeight + ")")
            .call(d3.axisBottom(vXAxis))
            .selectAll("text")
            .attr("transform", "translate(-10,0)rotate(-45)")
            .style("text-anchor", "end");

        $.ajax({
            type: "GET",
            url: gCoimf.mSiteURL + "/wp-json/coimf/v1/admin/get-actions/",
            data: {
                // FIXME: possible SQL injection
                "select": [
                    "CAST(time_start AS DATE) as time_start_date",
                    "SUM(JSON_EXTRACT(value, \"$.pageTime\")) as page_time_sum",
                ],
                "filter": {
                    "action_type": "= " + 2,
                    "time_start": ">= '" + vTimeStart.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                    "time_end": "<= '" + vTimeEnd.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                },
                "groupby": [
                    "CAST(time_start AS DATE)"
                ]
            },
            beforeSend: function (aXhr) {
                aXhr.setRequestHeader("X-WP-Nonce", cNonce);
            },
        }).then(function (aResponse) {
            let vData = aResponse.data;

            var vDataMax = d3.max(vData, function(aItem) {
                return +aItem.page_time_sum;
            });

            // Build Y scales and axis:
            let vYAxis = d3.scaleLinear()
                .domain([0, vDataMax]) // FIXME: smarter domain range
                .range([vHeight, 0]);
            svg.append("g")
                .call(d3.axisLeft(vYAxis));

            // Bars
            svg.selectAll("mybar")
                .data(vData)
                .enter()
                .append("rect")
                .attr("x", function (aItem) {
                    let vItemTimeStart = moment(aItem.time_start_date).format("dddd");
                    return vXAxis(vItemTimeStart);
                })
                .attr("y", function (aItem) {
                    return vYAxis(aItem.page_time_sum);
                })
                .attr("width", vXAxis.bandwidth())
                .attr("height", function (aItem) {
                    return vHeight - vYAxis(aItem.page_time_sum);
                })
                .attr("fill", "#69b3a2")
        });
    });

})(jQuery, window, document, undefined);
