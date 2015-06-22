$(document).ready(function() { 
	$('[name="computingtype"]').click(function(){
		if( $(this).val() == 0 ){
			$("#dist_value").prop('disabled', true);
		}else{
			$("#dist_value").prop('disabled', false);
		}
	});
	
	
	$("#start").click(function(){
		$(this).prop('disabled', true);
		$("#start_label").html("computing...").show();
		// ajax for computing result
		var cat1 = $("select[name='cat1']").val();
		var cat2 = $("select[name='cat2']").val();
		var cat3 = $("select[name='cat3']").val();
		var dist = -1;
		if( !$("#dist_value").prop('disabled') ){
			dist =  $("input#dist_value").val();
		}
		try{
			var lat = getLat();
			var long = getLong();
		}catch(exception){
			alert("Network error");
		}
		
		$.post("ajax.php?type=computing",{
			cattop:cat1,
			catsec:cat2,
			cat:cat3,
			distant:dist,
			x:long,
			y:lat,
		},function(data,status){
			$("#start").prop('disabled', false);
			$("#start_label").hide();
			//alert(data);
			var json = eval(data); 
			if(json.length == 0 ){
				$("ul#rslt").html("no result");
			}else{
				$("ul#rslt").html("");
				var arr = new Array();
				deleteShaplayer();
				for( var i = 0 ; i < json.length ; i ++ ) { 
					var obj = json[i];
					var shape = new VEShape(VEShapeType.Pushpin,new VELatLong(obj.Y,obj.X, 0, VEAltitudeMode.Default));
					shape.SetTitle(obj.NAME);
					shape.SetDescription(obj.ADDR);
					shape.SetCustomIcon("<div class = \"pinbox\"><div class = \"rslt_id\" rid = \""+(i+1)+"\">"+ (i + 1) +"</div><img src='img/pin.png'/>");
					addShap(shape);
					$("<li RID = \"" + (i+1) + "\" PID = \"" + obj.ID + "\" lat = \"" + obj.Y + "\" long = \"" + obj.X + "\" shapeID = \"" + shape.GetID() + "\"></li>").appendTo("ul#rslt");
					$("<h4>" +  (i + 1) + ":" + obj.NAME + "</h4>" + "<div>" + obj.ADDR + "</div>").appendTo("ul#rslt>li:last");
					
				}
				showResult();
				$("ul#rslt>li").click(function(){
					$("ul#rslt>li").removeClass("selected");
					$(this).addClass("selected");
					setCenterTo(new VELatLong($(this).attr("lat"),$(this).attr("long"), 0, VEAltitudeMode.Default));
					$(".rslt_id").removeClass("selected");
					$(".rslt_id[rid='" + $(this).attr("rid") + "']").addClass("selected");
					});
				$(".pinbox").click(function(){
					var rid = $(this).find(".rslt_id").html();
					$("ul#rslt>li").removeClass("selected");
					$("ul#rslt>li[rid='"+rid+"']").addClass("selected");
					});
				
			}
			});
		
	});
	
	$("#start_label").hide();
	$(".cat").change(function(){
			if( $(this).attr("name") == "cat1" ){
				generateCatSec($(this).val());
			}else{
				if($(this).attr("name") == "cat2"){
					generateCat($(this).val());
				}
			}
		});
	$('input[name="locationSelect"]').click(function(){
		if( $(this).attr("locate") == "false"){
			$(this).val("Location set");
			$(this).attr("locate" , "true");
			$("#start").prop('disabled', true);
			$("#start_label").html("Please confirm your<br/> location on the map").show();
		}else{
			$(this).val("Select location on the map");
			$(this).attr("locate" , "false");
			$("#start").prop('disabled', false);
			$("#start_label").hide();
			resetCenter();
		}
	});
}); 


function generateCatSec(topId){
	$("select[name='cat2']").prop('disabled', true);
	$.post("ajax.php?type=combo",{
			cattop : topId
		},function(data,status){
	$("select[name='cat2']").html('<option value = "0">所有</option>');
	$(data).appendTo("select[name='cat2']");
	$("select[name='cat2']").prop('disabled', false);
	generateCat(0);
  });
	
}

function generateCat(SecId){
	if( SecId == 0 ){
		$("select[name='cat3']").html('<option value = "0">所有</option>');
	}else{
		$("select[name='cat3']").prop('disabled', true);
		$.post("ajax.php?type=combo",{
			cattop : 0,
			catsec : SecId
		},function(data,status){
		$("select[name='cat3']").html('<option value = "0">所有</option>');
		$(data).appendTo("select[name='cat3']");
		$("select[name='cat3']").prop('disabled', false);
	  });
	}
}
