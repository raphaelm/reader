var start = 0;
var limit = 30;
var scrollandloadmore = true;
var title = '';

function loadmore(){
	if(location.pathname == '/m_all.php'){
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
	
		scrollandloadmore = false;
		$('.loadmore').remove();
		$("#wrap").append("<center><img	src='images/loading.gif' class='load_more_content' /></center>");
		$.get('all_ajax.php?mobile=true&show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
			if(status != 'success'){
				$('.load_more_content, .loadmore').remove();
				$('#wrap').append('<em style="color:red;">'+lang.error+'</em>');
				scrollandloadmore = true;
				$('#wrap').append("<a href='javascript:loadmore();' class='loadmore'>"+lang.loadmore+"</a>");
				return false;
			}else{
				$('.load_more_content, .loadmore').remove();
				if(data.search(/<!-- NOTHING MORE -->/) == -1){
					$('#wrap').append(data);
					$('#wrap').append("<a href='javascript:loadmore();' class='loadmore'>"+lang.loadmore+"</a>");
					scrollandloadmore = true;
				}else{
					scrollandloadmore = false;
				}
			}
		});
	}else if(location.pathname == '/m_feeds.php'){
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
		
		scrollandloadmore = false;
		$('.loadmore').remove();
		$("#wrap").append("<center><img	src='images/loading.gif' class='load_more_content' /></center>");
		$.get('feeds_ajax.php?mobile=true&feedid='+feedid+'&show='+show+'&lasttimestamp='+lasttimestamp, function(data, status){
			if(status != 'success'){
				$('.load_more_content, .loadmore').remove();
				$('#wrap').append('<em style="color:red;">'+lang.error+'</em>');
				scrollandloadmore = true;
				$('#wrap').append("<a href='javascript:loadmore();' class='loadmore'>"+lang.loadmore+"</a>");
				return false;
			}else{
				$('.load_more_content, .loadmore').remove();
				if(data.search(/<!-- NOTHING MORE -->/) == -1){
					$('#wrap').append(data);
					scrollandloadmore = true;
					$('#wrap').append("<a href='javascript:loadmore();' class='loadmore'>"+lang.loadmore+"</a>");
				}else{
					scrollandloadmore = false;
				}
			}
		});
	}
}
function togglearticle(aid){
	$("#article_"+aid+" .sum").toggle();
	$.get('markasread.php?article='+aid);
	$("#article_"+aid).removeClass('unreadarticle').addClass('readarticle');
}
function sticky(id){
	$("#article_"+id).addClass("sticky");
	$("#article_"+id+" .stickylink").html(lang.removebookmark).attr("href", "javascript:unsticky("+id+");");
	$.get('sticky_ajax.php?sticky='+id);
}
function unsticky(id){
	$("#article_"+id).removeClass("sticky");
	$("#article_"+id+" .stickylink").html(lang.bookmark).attr("href", "javascript:sticky("+id+");");
	$.get('sticky_ajax.php?unsticky='+id);
}
function unstickyremove(id){
	$("#article_"+id).slideUp();
	$.get('sticky_ajax.php?unsticky='+id);
}
$(document).ready(function(){	
	title = $("title").html();
	$(".sum").hide()
	$(".select strong").bind("click", function(){
		$(this).parent().children("ul").toggle();		
	});
});
