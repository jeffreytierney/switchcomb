if(!SC) var SC = {};
if(!SC.Labs) SC.Labs = {};
SC.Labs.TextFade = {
	"params":{"q":"","token":0},
	"els":{},
	"dimensions":{},
	"cur":false,
	"items":[],
	"indent":0,
	"width":500,
	"fontSize":"2em",
	"field":"text",
	"start":function(q) {
		SC.Labs.TextFade.els["body"] = $(document.body);
		
		var height = $(window).height()/3;
		var width = Math.floor($(window).width()/1.1);
		var left = 10;
		var top = 10;
		
		SC.Labs.TextFade.dimensions.height = height;
		SC.Labs.TextFade.dimensions.width = width;
		SC.Labs.TextFade.dimensions.left = left;
		SC.Labs.TextFade.dimensions.top = top;
		
		var textfade = $(document.createElement("div")).attr("id", "textfade").addClass("textfade").css({"background-color":"transparent","position":"absolute","top":top+"px","left":left+"px","height":height+"px","width":width+"px"});
		var textfadecontainer = $(document.createElement("a")).attr("id", "textfadecontainer").addClass("textfadecontainer").css({"float":"left","font-size":SC.Labs.TextFade.fontSize,"width":SC.Labs.TextFade.width+"px"});
		var textfadesummary = $(document.createElement("div")).attr("id", "textfadesummary").addClass("textfadesummary").css({"float":"right","width":SC.Labs.TextFade.width+"px"});
		textfade.append(textfadecontainer);
		textfade.append(textfadesummary);
		
		SC.Labs.TextFade.els["body"].append(textfade);
		SC.Labs.TextFade.els["textfade"] = textfade;
		SC.Labs.TextFade.els["textfadecontainer"] = textfadecontainer;
		
		SC.Labs.TextFade.params.q = q; 
		//KGB.Streams.Home.get(SC.Labs.TextFade.params, SC.Labs.TextFade.handleAll);
		$.getJSON("http://www.switchcomb.com/ajax_dispatch.php?f=getmessages&threadid=6061&loadsince=0&callback=?", SC.Labs.TextFade.handleAll);
		SC.Labs.TextFade.params.token -= 25;
	},
	"handleAll":function(data) {
		//var textfade = SC.Labs.TextFade.els["textfade"];
		//alert("hi");
		$.each(data.messages, function(i, val) {
			SC.Labs.TextFade.queueOne(val);
			
		})
		
		if(SC.Labs.TextFade.params.token >= 0) {
			
			//KGB.Streams.Home.get(SC.Labs.TextFade.params, SC.Labs.TextFade.handleAll);
			SC.Labs.TextFade.params.token -= 25;
		}
		else {
			SC.Labs.TextFade.handleOne(SC.Labs.TextFade.items.shift());
		}
	},
	"handleOne":function(item) {
		if(item[SC.Labs.TextFade.field]) {
			var chars = {};
			var cur_chars = {};
			var to_drop = {};
			
			var tempentry = $(document.createElement("div")).attr("id", "textfadetempitem_"+item.id).addClass("textfadetempitem").css({"position":"absolute","top":"0px","left":"0px","font-size":SC.Labs.TextFade.fontSize,"z-index":"1","color":"transparent","width":SC.Labs.TextFade.width+"px","text-indent":(-1*SC.Labs.TextFade.indent)+"px"});
			
			for(var i=0; i<item[SC.Labs.TextFade.field].length; i++) {
				tempentry.append($(document.createElement("span")).html(item[SC.Labs.TextFade.field][i]));
			}
			SC.Labs.TextFade.els["textfade"].append(tempentry);
			
			$.each(tempentry.children(), function(i, val) {
				if(val && $(val).html()!= " ") {
					if(chars[$(val).html()]) {
						chars[$(val).html()].push($(val));
					}
					else {
						chars[$(val).html()] = [];
						chars[$(val).html()].push($(val));
					}
				}
			});
			var count = 0;
			$.each(chars, function(i, val) {
				count++;
			});
			
			
			if (SC.Labs.TextFade.cur) {
				var cur = SC.Labs.TextFade.els["textfadecontainer"];
				$.each(cur.children(), function(i, val) {
					if(val && $(val).html()!= "") {
						if(cur_chars[$(val).html()]) {
							cur_chars[$(val).html()].push($(val));
						}
						else {
							cur_chars[$(val).html()] = [];
							cur_chars[$(val).html()].push($(val));
						}
					}
				});
				
				var count = 0;
				$.each(cur_chars, function(i, val) {
					count++;
				});
			
				
				$.each(chars, function(i, val) {
					if(cur_chars[i]) {
						if(cur_chars[i].length > chars[i].length) {
							var len = chars[i].length;
							var len2 = cur_chars[i].length;
							for(var j=0; j<len; j++) {
								$(cur_chars[i][j]).animate({"left":($(chars[i][j]).offset().left+SC.Labs.TextFade.indent)+"px","top":($(chars[i][j]).offset().top-SC.Labs.TextFade.dimensions.top)+"px"});
								$(cur_chars[i][j]).data("used", true);
								$(chars[i][j]).data("used", true);
							}
							for(var j=len; j<len2; j++) {
								$(document.body).append($(cur_chars[i][j]).html);
								$(cur_chars[i][j]).data("used", false);
								$(cur_chars[i][j]).remove();
							}
						}
						else if(cur_chars[i].length < chars[i].length) {
							var len = cur_chars[i].length;
							var len2 = chars[i].length;
							for(var j=0; j<len; j++) {
								$(cur_chars[i][j]).animate({"left":($(chars[i][j]).offset().left+SC.Labs.TextFade.indent)+"px","top":($(chars[i][j]).offset().top-SC.Labs.TextFade.dimensions.top)+"px"});
								$(cur_chars[i][j]).data("used", true);
								$(chars[i][j]).data("used", true);
							}
							for(var j=len; j<len2; j++) {
								SC.Labs.TextFade.els["textfadecontainer"].append($(chars[i][j]).clone(true).css({"position":"absolute","top":$(chars[i][j]).offset().top-tempentry.offset().top+"px","left":($(chars[i][j]).offset().left+SC.Labs.TextFade.indent)+"px"}).hide().fadeIn(2000));
								$(chars[i][j]).data("used", true);
							}
						}
						else {
							var len = chars[i].length;
							for(var j=0; j<len; j++) {
								$(cur_chars[i][j]).animate({"left":($(chars[i][j]).offset().left+SC.Labs.TextFade.indent)+"px","top":($(chars[i][j]).offset().top-SC.Labs.TextFade.dimensions.top)+"px"});
								$(cur_chars[i][j]).data("used", true);
								$(chars[i][j]).data("used", true);
							}
						}	
					}
					else {
						$.each(val, function(j, val2) {
							SC.Labs.TextFade.els["textfadecontainer"].append($(chars[i][j]).clone(true).css({"position":"absolute","top":$(chars[i][j]).offset().top-tempentry.offset().top+"px","left":($(chars[i][j]).offset().left+SC.Labs.TextFade.indent)+"px"}).hide().fadeIn(2000));
							$(chars[i][j]).data("used", true);
						});
					}
				});
				$.each(cur_chars, function(i, val) {
					if(!chars[i]) {
						$.each(val, function(j, val2) {
							$(val2).remove();
						});
					}
					else {
						$.each(val, function(j, val2) {
							if(!$(val2).data("used")) {
								$(val2).remove();
							}
						});
					}
				});
				
			}
			
			else{
				for(var i=0; i<item[SC.Labs.TextFade.field].length; i++) {
					SC.Labs.TextFade.els["textfadecontainer"].append($(document.createElement("span")).html(item[SC.Labs.TextFade.field][i]).css());
				}
				$.each(SC.Labs.TextFade.els["textfadecontainer"].children(), function(i, val) {
					var left = $(val).offset().left+"px";
					var top = ($(val).offset().top-SC.Labs.TextFade.dimensions.top)+"px";
					$(val).css({"left":left,"top":top});
				});
				$.each(SC.Labs.TextFade.els["textfadecontainer"].children(), function(i, val) {
					$(val).css({"position":"absolute"});
				});
			}
			
			tempentry.remove();
			$("#textfadecontainer").attr("href", item.url);
			$("#textfadesummary").fadeOut(function() {$(this).html(item.authorname).fadeIn()});
			setTimeout(function() {
					if(SC.Labs.TextFade.items.length > 0) {
						SC.Labs.TextFade.handleOne(SC.Labs.TextFade.items.shift());
					}
				}, 4000);
			
			
			SC.Labs.TextFade.cur = item.id;
		}
		else {
			setTimeout(function() {
				if(SC.Labs.TextFade.items.length) {
					SC.Labs.TextFade.handleOne(SC.Labs.TextFade.items.shift());
				}
			}, 500);
		}
		
	},
	"queueOne":function(item) {
		SC.Labs.TextFade.items.push(item);

	},
}
