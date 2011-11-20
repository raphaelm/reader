var start = 0;
var limit = 30;
var scrollandloadmore = true;
var title = '';
var unread = 0;
var focused = true;
var noti = false;
	
	
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
			if(value > 0)
				$("#unreadcount_"+key).html("("+value+")");
			else
				$("#unreadcount_"+key).html("");
		});
		$('title').html('('+data.unread.all+') '+title);
		unread = data.unread.all;
		window.setTimeout('loadUnreadCount()', 30000);
	}
}

function loadUnreadCount(){
	$.getJSON('/new_ajax.php', parseunreadcount);
}

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
		
		if($("#right-col").scrollTop()+parseInt($("#right-col").innerHeight()) > parseInt($("#wrap").innerHeight())-80){
			scrollandloadmore = false;
			$("#wrap").append("<center><img	src='images/loading.gif' class='load_more_content' /></center>");
			$.get('all_ajax.php?show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
				if(status != 'success'){
					$('.load_more_content').remove();
					$('#wrap').append('<em style="color:red;">'+lang.error+'</em>');
					scrollandloadmore = true;
					return false;
				}else{
					$('.load_more_content').remove();
					if(data.search(/<!-- NOTHING MORE -->/) == -1){
						$('#wrap').append(data);
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
		
		if($("#right-col").scrollTop()+parseInt($("#right-col").innerHeight()) > parseInt($("#wrap").innerHeight())-80){
			scrollandloadmore = false;
			$("#wrap").append("<center><img	src='images/loading.gif' class='load_more_content' /></center>");
			$.get('feeds_ajax.php?feedid='+feedid+'&show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
				if(status != 'success'){
					$('.load_more_content').remove();
					$('#wrap').append('<em style="color:red;">'+lang.error+'!</em>');
					scrollandloadmore = true;
					return false;
				}else{
					$('.load_more_content').remove();
					if(data.search(/<!-- NOTHING MORE -->/) == -1){
						$('#wrap').append(data);
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

$(document).ready(function(){
			
	$('#right-col').scroll(function () { 
		markasread();
		loadmore();
	});
	
	$(window).bind('focus', function(){focused=true;if(noti)noti.cancel();});
	$(window).bind('blur', function(){focused=false;});
	
	title = $("title").html();
	loadUnreadCount();
	window.setTimeout('loadUnreadCount()', 60000);
	markasread();
});
