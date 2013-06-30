$(function(){

	$('body').layout({
		north__size:		50,
		north__resizable:	false,
		north__closable:	true,
		west__size:			265,
		west__resizable:	false
		});
	
	
	function update_view(data){
		var view = $('.ui-layout-center');
		$('.header', view).html(data['session_id']);
		
		view = $('#data', view);
		
		view.empty();
		$.each(data['session'], function(key, value){
			view.append('<li>'+key+': '+value+'</li>');
			});
		
		};
	
	
	function get_data(sid){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php',
			data: {
				action: 'get',
				sid: sid
				},
			success: update_view
			});
		}
	
	
	
	$('#list').on('click', '.item', function(e){
		
		$('#list .item.active').removeClass('active');
		
		var a = $(this);
		a.addClass('active');
		
		
		get_data(a.text());
		});
	
	
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: 'ajax.php',
		data: {
			action: 'list'
			},
		success: function(data){
			var x = $('#list');
			$.each(data['sessions'], function(_,id){
				x.append($('<li class="item">'+id+'</li>'))
				});
			
			$('#list .item').first().trigger('click');
			}
		});
	
	
	});