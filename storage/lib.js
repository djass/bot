$( document ).ready(function() {
	var auto = false;

	$("form").submit(function(){
			$("#welcome").hide();
		});

	relaunchquestion = function(){
		console.log("Relaunch question");
		auto = true;
		lastusersays = $( "div.usersays" ).last().text();
		$("input#say").val(lastusersays);
		$("form").submit();		
	}

	 
	showMessage = function(msg,from){
		
		if(auto){
			auto = false;
			 console.log("Mode auto: no message to show");
		}
		else{
			if(from =="usersays") {name = "Vous";delay = 0;}
	      	else {name = "Hermy"; delay = 1000;}
	      	var fullDate = new Date()

	      	time = fullDate.getHours()+":"+fullDate.getMinutes();
	      
		      	$('#container').append('<div class="part'+from+'"> <div class="'+from+'">'+msg+'</div><div class="clear"></div><div class="name'+from+'">'+time+' '+name+'</div></div>');
		      	$("#container .part"+from+":last").hide().delay(delay);
		      	$("#container .part"+from+":last").fadeToggle("slow", "linear");
	      	 

	      	$('#container').append('<div class="clear"></div>');
	      	$("html, body").animate({ scrollTop: $(document).height() }, 500);
			console.log("Mode normal: display message");

		}      
    }

    wait = function(){
      $('#container .partbotsays:last').append('<div class="clear"></div><img class="wait" src="images/searching1.gif"/>');
       console.log("Wait");
    }
    finish = function(){
      $('img.wait').hide();
       console.log("Finish!");
    }

	$("button#auto").click(function(){
		relaunchquestion();		 
	})
});