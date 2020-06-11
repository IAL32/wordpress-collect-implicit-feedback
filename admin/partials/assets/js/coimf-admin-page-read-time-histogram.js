(function ($, window, document) {
    "use strict";

    $(document).ready(function () {
        // set the dimensions and margins of the graph
        var vMargin = { mTop: 30, mRight: 30, mBottom: 70, mLeft: 50 },
            vWidth = 450 - vMargin.mLeft - vMargin.mRight,
            vHeight = 470 - vMargin.mTop - vMargin.mBottom;

        var vTimeStart = moment().subtract(7, "days");
        var vTimeEnd = moment();

        // append the svg object to the body of the page
        var svg = d3.select("#scroll-time-histogram")
            .append("svg")
            .attr("width", vWidth + vMargin.mLeft + vMargin.mRight)
            .attr("height", vHeight + vMargin.mTop + vMargin.mBottom)
            .append("g")
            .attr("transform",
                "translate(" + vMargin.mLeft + "," + vMargin.mTop + ")");
        svg.append("text")
            .attr("x", (vWidth / 2))             
            .attr("y", 0 - (vMargin.mTop / 2))
            .attr("text-anchor", "middle")
            .text("Distribution of read time");

        $.ajax({
            type: "GET",
            url: gCoimf.mSiteURL + "/wp-json/coimf/v1/admin/get-actions/",
            data: {
                // FIXME: possible SQL injection
                "select": [
                    "COUNT(*)",
                    "CAST(JSON_EXTRACT(value, \"$.pageTime\") AS int) as pageTime",
                ],
                "filter": {
                    // FIXME: customizable range with enum or struct
                    "action_type": "= 2",
                    "time_start": ">= '" + vTimeStart.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                    "time_end": "<= '" + vTimeEnd.format(gCoimf.cJsMYSQLDateTimeFormat) + "'",
                },
                "groupby": [
                    "pageTime"
                ],
                "orderby": "pageTime",
                "order": "DESC",
                "limit": -1,
                "offset": -1,
            },
            beforeSend: function (aXhr) {
                aXhr.setRequestHeader("X-WP-Nonce", cHistogramNonce);
            },
        }).then(function (aResponse) {
            let vData = aResponse.data;
            console.log(aResponse);

            var vDataMax = d3.max(vData, function(aItem) {
                return +aItem.pageTime;
            });

            let vXAxis = d3.scaleLinear()
                .domain([0, vDataMax])
                .range([0, vWidth]);
            svg.append("g")
                .attr("transform", "translate(0," + vHeight + ")")
                .call(d3.axisBottom(vXAxis))
                .selectAll("text")
                .attr("transform", "translate(-10,0)rotate(-45)")
                .style("text-anchor", "end");
            svg.append("text")             
                .attr("transform",
                        "translate(" + (vWidth / 2) + " ," + 
                                    (vHeight + vMargin.mTop + 20) + ")")
                .style("text-anchor", "middle")
                .text("Read Time");

            let vHistogram = d3.histogram()
                .value(function (aItem) { return aItem.pageTime })
                .domain(vXAxis.domain())
                // FIXME: customizable and dynamic number of bins
                .thresholds(vXAxis.ticks(10)); // number of bins

            let vBins = vHistogram(vData);

            let vYAxis = d3.scaleLinear()
                .range([vHeight, 0])
                .domain([0, d3.max(vBins, function(aItem) {
                    return aItem.length;
                })]);
            svg.append("g")
                .call(d3.axisLeft(vYAxis));
            svg.append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 0 - vMargin.mLeft)
                .attr("x",0 - (vHeight / 2))
                .attr("dy", "1em")
                .style("text-anchor", "middle")
                .text("Count");  

            // Bars
            svg.selectAll("rect")
                .data(vBins)
                .enter()
                .append("rect")
                    .attr("x", 1)
                    .attr("transform", function(aItem) {
                        return "translate(" + vXAxis(aItem.x0) + "," + vYAxis(aItem.length) + ")";
                    })
                    .attr("width", function(aItem) {
                        return vXAxis(aItem.x1) - vXAxis(aItem.x0) -1;
                    })
                    .attr("height", function(aItem) {
                        return vHeight - vYAxis(aItem.length);
                    })
                    // FIXME: make color customizable
                    .attr("fill", "#69b3a2")
        });
    });

})(jQuery, window, document, undefined);
