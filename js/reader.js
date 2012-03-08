var collapse = false;
var start = 0;
var limit = 30;
var scrollandloadmore = true;
var title = '';
var unread = 0;
var focused = true;
var noti = false;

/* Utilities */
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

/* Background updates */
var favicon = { // Thanks: http://softwareas.com/dynamic-favicons
	change: function(iconURL) {
	  if (arguments.length==2) {
	    document.title = optionalDocTitle;
	  }
	  this.addLink(iconURL, "icon");
	  this.addLink(iconURL, "shortcut icon");
	},
	 
	addLink: function(iconURL, relValue) {
	  var link = document.createElement("link");
	  link.type = "image/x-icon";
	  link.rel = relValue;
	  link.href = iconURL;
	  this.removeLinkIfExists(relValue);
	  this.docHead.appendChild(link);
	},
	 
	removeLinkIfExists: function(relValue) {
	  var links = this.docHead.getElementsByTagName("link");
	  for (var i=0; i<links .length; i++) {
	    var link = links[i];
	    if (link.type=="image/x-icon" && link.rel==relValue) {
	      this.docHead.removeChild(link);
	      return; // Assuming only one match at most.
	    }
	  }
	},
	 
	docHead:document.getElementsByTagName("head")[0]
}
function parseunreadcount(data){
	if(data.error == 'undefined'){
		$('#wrap').append('<strong>'+lang.errorplsreload+'</strong>');
	}else{
		if(window.webkitNotifications && data.unread.all > unread && !focused && readCookie('desknot') == 'true' && window.webkitNotifications.checkPermission() == 0) { // 0 is PERMISSION_ALLOWED
			if(noti) noti.cancel();
			noti = window.webkitNotifications.createNotification('images/gfr.png', lang.title, sprintf(lang.newmsgs, data.unread.all));
			noti.show();
			window.setTimeout(function(){if(noti)noti.cancel();}, 3000);
		}
		$(".unread").html("");
		$.each(data.unread,function(key, value){
			if(value > 0){
				$(".unreadcount_"+key).html("("+value+")");
				$(".unreadcount_zero_"+key).html(value);
			}else{
				$(".unreadcount_"+key).html("");
				$(".unreadcount_zero_"+key).html("0");
			}
		});
		$('title').html('('+data.unread.all+') '+title);
		if(data.unread.all > 0)
			favicon.change('somethingnew.ico');
		else
			favicon.change('favicon.ico');
		unread = data.unread.all;
		window.setTimeout('loadUnreadCount()', 30000);
	}
	if(collapse)
		collapsing();
}

function loadUnreadCount(){
	$.getJSON('/new_ajax.php', parseunreadcount);
}

/* Reading */

function markasread(){
	$('.unreadarticle').each(function(key,val){ 
			if( $("#right-col").scrollTop() > parseInt( $(val).position().top+$("#right-col").scrollTop() ) + parseInt( $(val).css('height') ) - 150
			||
			$("#right-col").scrollTop()+parseInt($("#right-col").innerHeight()) > parseInt($("#wrap").innerHeight())-20 
			||
			($("#right-col").scrollTop() == 0 && parseInt( $(val).position().top+$("#right-col").scrollTop() ) + parseInt( $(val).css('height') ) - 300 < parseInt($("#right-col").innerHeight() / 1.5) )){
				$.getJSON('markasread.php?article='+$(val).attr('id').substr(8,9), parseunreadcount);
				$(val).removeClass('unreadarticle').addClass('readarticle');
			}
		});
}

function loadmore(){
	if(location.pathname == '/all.php'){
		if(!scrollandloadmore) return false;
		var get = {};
		get = parseQueryString(location.query);
		
		if(get['show'] === undefined){
			var show = 'unread';
		}else if(get.show[1] === undefined){
			var show = escape(get.show);
		}else{
			var show = escape(get.show[1]);
		}
		
		if($("#right-col").scrollTop()+parseInt($("#right-col").innerHeight()) > parseInt($("#wrap").innerHeight())-160){
			scrollandloadmore = false;
			$("#right-col").append("<center class='load_more_content'><img	src='images/loading.gif' /></center>");
			$.get('all_ajax.php?show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
				if(status != 'success'){
					$('.load_more_content').remove();
					$('#right-col').append('<em style="color:red;">'+lang.error+'</em>');
					scrollandloadmore = true;
					return false;
				}else{
					$('.load_more_content').remove();
					if(data.search(/<!-- NOTHING MORE -->/) == -1){
						$('#wrap').append(data);
						register_focus_handler();
						scrollandloadmore = true;
					}else{
						scrollandloadmore = false;
					}
				}
			});
		}
	}else if(location.pathname == '/feeds.php'){
		if(!scrollandloadmore) return false;
		var get = {};
		get = parseQueryString(location.query);
		
		if(get['show'] === undefined){
			var show = 'unread';
		}else if(get.show[1] === undefined){
			var show = escape(get.show);
		}else{
			var show = escape(get.show[1]);
		}
		
		var feedid = parseInt(get.feedid[0]);
		
		if($("#right-col").scrollTop()+parseInt($("#right-col").innerHeight()) > parseInt($("#wrap").innerHeight())-160){
			scrollandloadmore = false;
			$("#right-col").append("<center class='load_more_content'><img	src='images/loading.gif' /></center>");
			$.get('feeds_ajax.php?feedid='+feedid+'&show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
				if(status != 'success'){
					$('.load_more_content').remove();
					$('#right-col').append('<em style="color:red;">'+lang.error+'!</em>');
					scrollandloadmore = true;
					return false;
				}else{
					$('.load_more_content').remove();
					if(data.search(/<!-- NOTHING MORE -->/) == -1){
						$('#wrap').append(data);
						register_focus_handler();
						scrollandloadmore = true;
					}else{
						scrollandloadmore = false;
					}
				}
			});
		}
	}
}

function sticky(id){
	$("#article_"+id).addClass("sticky");
	$("#article_"+id+" .stickylink").html(lang.removebookmark).attr("href", "javascript:unsticky("+id+");");
	$.getJSON('sticky_ajax.php?sticky='+id, parseunreadcount);
}
function unsticky(id){
	$("#article_"+id).removeClass("sticky");
	$("#article_"+id+" .stickylink").html(lang.bookmark).attr("href", "javascript:sticky("+id+");");
	$.getJSON('sticky_ajax.php?unsticky='+id, parseunreadcount);
}
function unstickyremove(id){
	$("#article_"+id).slideUp();
	$.getJSON('sticky_ajax.php?unsticky='+id, parseunreadcount);
}

var dashboardAddFeedActive = false;
function dashboardAddFeed(){
	if(dashboardAddFeedActive){
		("#inputfeedurl").focus();
		return false;
	}
	$(".dashboardbox.addfeed").html("<form action='settings.php' method='POST'><input type='text' name='feedurl' id='inputfeedurl' value='http://' /><input type='hidden' name='submit' value='true' /><input type='hidden' name='hash' value='"+$(this).attr("rel")+"' /></form>");
	$("#inputfeedurl").focus();
	dashboardAddFeedActive = true;
}
var articleselector = ".readarticle, .unreadarticle, .sticky";
var uglyfixommitnext;
function scroll_next(){
	var jump = $(".focused").next(articleselector);
	if(!jump) return false;
	$("#right-col").scrollTop($("#right-col").scrollTop() + jump.position().top - 5);
	
	$(".focused").removeClass("focused");
	jump.addClass("focused");
	uglyfixommitnext = true;
	
	$.getJSON('markasread.php?article='+parseInt($(".focused").attr('id').substr(8)), parseunreadcount);
	$(".focused").removeClass('unreadarticle').addClass('readarticle');
	
}
function scroll_prev(){
	var jump = $(".focused").prev(articleselector);
	if(!jump) return false;
	$("#right-col").scrollTop($("#right-col").scrollTop() + jump.position().top - 5);
	$(".focused").removeClass("focused");
	jump.addClass("focused");
	uglyfixommitnext = true;
}
function scroll_sticky(){
	var id = parseInt($(".focused").attr("id").substr(8));
	if($(".focused").hasClass("sticky")){
		if($("#wrap").hasClass("removeunstickied"))
			unstickyremove(id);
		else
			unsticky(id);
	}else{
		sticky(id);
	}
}


function register_scroll_hotkeys(){
	$(document).bind('keydown', 'n', scroll_next);
	$(document).bind('keydown', 'p', scroll_prev);
	$(document).bind('keydown', 'm', scroll_sticky);
}

function register_scroll_readhandler(){
	$('#right-col').scroll(function () { 
		markasread();
		loadmore();
	});
}
function register_focus_handler(){
	if($(".focused").length == 0)
		$(articleselector).first().addClass("focused");
	$(articleselector).not(".has_mouseenter_event").addClass("has_mouseenter_event").bind("mouseenter", function(){
		if(uglyfixommitnext){
			uglyfixommitnext = false;
			return;
		}
		
		//$.getJSON('markasread.php?article='+parseInt($(this).attr('id').substr(8)), parseunreadcount);
		//$(this).removeClass('unreadarticle').addClass('readarticle');
		
		$(articleselector).removeClass("focused");
		$(this).addClass("focused");
	});
}

/* Navigation */
function collapsing(){
	$(".uncollapse").show();
	$(".collapse").hide();
	$("#navi li").each(function(key, value){
		if(!$(value).hasClass("donthide")){
			span = $(value).children("a").children("span.text").children("span.unread");
			if(span.length == 1){
				if(span.html() == ""){
					$(value).hide();
				}else{
					$(value).show();
				}
			}
		}
	});
}

/* Startup */
$(document).ready(function(){
	
	$(window).bind('focus', function(){focused=true;if(noti)noti.cancel();});
	$(window).bind('blur', function(){focused=false;});
	
	$(".dashboardbox.addfeed").bind("click", dashboardAddFeed);
	
	if(readCookie("collapse") == "auto"){
		collapsing();
		collapse = true;
	}else{
		$(".collapse").show();
		$(".uncollapse").hide();
	}
	$(".collapse").bind("click", function(){
		collapse = true;
		createCookie("collapse", "auto", 365);
		collapsing();
		$(".uncollapse").show();
		$(".collapse").hide();
	});
	$(".uncollapse").bind("click", function(){
		collapse = false;
		eraseCookie("collapse");
		$("#navi li").show();
		$(".collapse").show();
		$(".uncollapse").hide();
	});
		
	title = $("title").html();
	loadUnreadCount();
	window.setTimeout('loadUnreadCount()', 60000);
	markasread();
});
if (top.location != self.location) {
	top.location = self.location;
}
