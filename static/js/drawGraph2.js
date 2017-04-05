
//Query and draw D3 Graph
function drawGraph(){
    console.log("Called drawGraph");
    "use strict"
    $("#graph").empty();
    
    var width,height
    var chartWidth, chartHeight
    var margin
    var svg = d3.select("#graph").append("svg")
    var chartLayer = svg.append("g").classed("chartLayer", true)
    //initColors();
    var color = d3.scaleOrdinal(d3.schemeCategory10);
    main();

    function main() {
        var query=$("#search").find("input[name=search]").val();
        console.log("/graph?q=" + encodeURIComponent(query));
        d3.json("/graph?q=" + encodeURIComponent(query), function(error, graph2) {
         if (error) throw error;
         if(!data)
        {
                console.log("result is empty!");
               // return;
        }
         var data = {
                    nodes:graph2.nodes,
                    links:graph2.links
                };
 
                setSize(data);
                drawChart(data);
            });
    }
    
        function setSize(data) {
         width = document.querySelector("#graph").clientWidth
         height = document.querySelector("#graph").clientHeight
         
         margin = {top:0, left:0, bottom:0, right:0 }
         
         
         chartWidth = width - (margin.left+margin.right)
         chartHeight = height - (margin.top+margin.bottom)
         
         svg.attr("width", width).attr("height", height)   .attr("pointer-events", "all");
         
         
         chartLayer
         .attr("width", chartWidth)
         .attr("height", chartHeight)
         .attr("transform", "translate("+[margin.left, margin.top]+")")
         
         
     }
    
  
    
    
    function drawChart(data) {
        // define arrow markers for graph links
        svg.append('svg:defs').append('svg:marker')
        .attr('id', 'end-arrow')
        .attr('viewBox', '0 -5 10 10')
        .attr('refX', 6)
        .attr('markerWidth', 3)
        .attr('markerHeight', 3)
        .attr('orient', 'auto')
        .append('svg:path')
        .attr('d', 'M0,-5L10,0L0,5')
        .attr('fill', '#000');
        
        svg.append('svg:defs').append('svg:marker')
        .attr('id', 'start-arrow')
        .attr('viewBox', '0 -5 10 10')
        .attr('refX', 4)
        .attr('markerWidth', 3)
        .attr('markerHeight', 3)
        .attr('orient', 'auto')
        .append('svg:path')
        .attr('d', 'M10,-5L0,0L10,5')
        .attr('fill', '#000');
        
         var simulation = d3.forceSimulation()
         .force("link", d3.forceLink().id(function(d) { return d.index }))
         .force("collide",d3.forceCollide( function(d){return 18 }).iterations(16) )
         .force("charge", d3.forceManyBody())
         .force("center", d3.forceCenter(chartWidth / 2, chartWidth / 2))
         .force("y", d3.forceY(0))
        .force("x", d3.forceX(0));
     
         var link = svg.append("g")
         .attr("class", "links")
         .selectAll("line")
         .data(data.links)
         .enter()
         .append("line")
         .attr("stroke", "black")
        .style('marker-start', function(d) {
               return d.left ? 'url(#start-arrow)' : '';
               })
        .style('marker-end', function(d) {
               return d.right ? 'url(#end-arrow)' : '';
               });
        
         var node = svg.append("g")
         .attr("class", "nodes")
         .selectAll("circle")
         .data(data.nodes)
         .enter().append("circle")
         .attr("r", 10)
       .style("fill", function(d) {
                      if (d.label == "Company") {return color(2)}
                    else 	{ return color(4) }
                  ;})
        .on("mouseover", function(){d3.select(this).transition().attr("r",20);})
        .on("mouseout", function(){d3.select(this).transition().attr("r",10)})
         .call(d3.drag()
               .on("start", dragstarted)
               .on("drag", dragged)
               .on("end", dragended));
               
        // html title attribute
        node.append("title")
               .text(function (d) { return d.title; })
     
        
        
       var ticked = function() {
           link
           .attr("x1", function(d) { return d.source.x; })
           .attr("y1", function(d) { return d.source.y; })
           .attr("x2", function(d) { return d.target.x; })
           .attr("y2", function(d) { return d.target.y; });
           
           node
           .attr("cx", function(d) { return d.x; })
           .attr("cy", function(d) { return d.y; });
       }
       
       simulation
       .nodes(data.nodes)
       .on("tick", ticked);
       
       simulation.force("link")
       .links(data.links);
       
       
       
       function dragstarted(d) {
           if (!d3.event.active) simulation.alphaTarget(0.3).restart();
           d.fx = d.x;
           d.fy = d.y;
       }
       
       function dragged(d) {
           d.fx = d3.event.x;
           d.fy = d3.event.y;
       }
       
       function dragended(d) {
           if (!d3.event.active) simulation.alphaTarget(0);
           d.fx = null;
           d.fy = null;
       }
       
    }
};

//   .style("fill", function(d) {
//       if (d.label == "Company") {return "red"}
//      else 	{ return "black" }
//    ;})
//        .on("click", function(){d3.select(this).transition().style("fill", function()
//{return source[Math.floor(Math.random()*source.length)];});})



