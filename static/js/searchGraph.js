$(function () {
  
  $('#sourceBU')
  .multiselect({
               includeSelectAllOption: true,
               allSelectedText: 'All Orgs'
            });
  $("#sourceBU").multiselect('selectAll', false);
  $("#sourceBU").multiselect('updateButtonText');
  $('#sourceADS')
  .multiselect({
                });
  $('#targetBU')
  .multiselect({
               includeSelectAllOption: true,
               allSelectedText: 'All Orgs'
  });
  $("#targetBU").multiselect('selectAll', false);
  $("#targetBU").multiselect('updateButtonText');
  $('#targetADS')
  .multiselect({
  });
  function showSystem(name) {
      console.log("/system/" + encodeURIComponent(name));
      $.get("/system/" + encodeURIComponent(name),
            function(data){
            if (!data) return;
            var t = $("table2#results tbody").empty();
            var system = data[0].system;
            
            console.log("<li>" + system.name + " ID:" + system.SysID + " ADS:" + system.isADS + "</li>");
            $("#name").text(system.name);
            // var $list = $("#sysinfo").empty()
            $("<li>" + system.name + " ID:" + system.SysID + " ADS:" + system.isADS + "</li>").appendTo(list);
            }, "json");
      return false;
  }
  
  
  function search() {
      var query = getSearchParams();
      console.log(query);
      //var query=$("#search").find("input[name=search]").val();
      $.get("/search?"+query,
            function (data) {
            var t = $("table#results tbody").empty();
            if (!data || data.length == 0) {
            console.log("Empty result");
            return;
            }
            data.forEach(function (row) {
                         var system = row.system;
                         //   console.log(system);
                         $("<tr><td class='system'>" + system.name + " ID:" + system.SysID  + " ADS:" + system.isADS+ "</td></tr>").appendTo(t)
                         .click(function() { showSystem($(this).find("td.system").text());})
                         //    console.log(company);
                         });
            //  showSystem(data[0].system.name);
            }, "json");
      drawGraph(query);
      return false;
  }
  
  
  function getSearchParams(){
      var result='';
      var sourceAITs = $("#search").find("textarea[name=sourceAITs]").val();
      console.log("Source AITs:"+sourceAITs);
        var sourceBU = $('#sourceBU').val();
   
  
      console.log('SourceBU='+$('#sourceBU').val());
      var sourceADS =$('#sourceADS').val();
      console.log("SourceADS:"+sourceADS);
      var targetAITs = $("#search").find("textarea[name=targetAITs]").val();
      console.log("Target AITs:"+targetAITs);
      var targetBU=$('#targetBU').val();
      console.log("Target BU="+$('#targetBU').val());
      var targetADS =$('#targetADS').val();
      console.log("Target ADS:"+targetADS);
      
      result = 'sourceAITs='+encodeURIComponent(sourceAITs)+'&'
      +'sourceBU='+encodeURIComponent(sourceBU)+'&'
      +'sourceADS='+encodeURIComponent(sourceADS) +'&'
      +'targetAITs='+encodeURIComponent(targetAITs)+'&'
      +'targetBU='+encodeURIComponent(targetBU)+'&'
      +'targetADS='+encodeURIComponent(targetADS);
      console.log("Query String:"+result);
      return result;
  }
  
  $("#search").submit(search);
  search();
  })
