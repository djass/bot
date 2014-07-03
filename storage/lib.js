$( document ).ready(function() {
	var auto = false;
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
			if(from =="usersays") name = "Vous";
	      	else name = "Hermy";
	      	var fullDate = new Date()

	      	time = fullDate.getHours()+":"+fullDate.getMinutes();
	      	$('#container').append('<div class="'+from+'">'+msg+'</div>');
	      	$('#container').append('<div class="clear"></div>');          
	      	$('#container').append('<div class="name'+from+'">'+time+' '+name+'</div>');
	      	$('#container').append('<div class="clear"></div>');
	      	$("html, body").animate({ scrollTop: $(document).height() }, 1000);
			console.log("Mode normal: display message");

		}      
    }

    wait = function(){
      $('#container').append('<div class="clear"></div><img class="wait" src="images/searching1.gif"/>');
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