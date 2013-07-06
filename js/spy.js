$(function(){
	
	var token = $('body').attr('data-token');
	
	
	$('body').layout({
		north__size:		50,
		north__resizable:	false,
		north__closable:	true,
		west__size:			265,
		west__resizable:	false
		});
	
	
	
	function update_view_h(data, view){
		$.each(data, function(key, value){
			
			switch(value['type']){
				case 'object':
					var new_view = $('<ul class="more-ex"></ul>');
					update_view_h(value['value'], new_view);
					
					var ele = $(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								value['key']+
							'</span>'+
							'<span class="value array" title="object">'+
								'<a href="#" class="more">[Object ...]</a>'+
								'<span class="class-info">'+ value['class'] +'</span>'+
							'</span>'+
						'</li>');
						
					$('.array', ele).append(new_view);
					view.append(ele);
					break;
					
				case 'array':
					var new_view = $('<ul class="more-ex"></ul>');
					update_view_h(value['value'], new_view);
					
					var ele = $(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								value['key']+
							'</span>'+
							'<span class="value array" title="array">'+
								'<a href="#" class="more">[Array ...]</a>'+
							'</span>'+
						'</li>');
						
					$('.array', ele).append(new_view);
					view.append(ele);
					break;
					
				default:
					view.append(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								value['key']+
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							'<span class="value '+ value['type'] +'" title="'+ value['type'] +'">'+
								value['value']+
							'</span>'+
						'</li>');
				}
			
			});
		}
	
	function update_view(data){
		$('#loading_data').hide();
		var view = $('.ui-layout-center');
		
		if(!data['success']){
			data['error'] = data['error'] ? data['error'] : "Unknown";
			$('#data', view).html(
								'<b><i>Could not fetch '+
								'session data. Error: '+data['error']+
								'</i></b>');
			return;
			}
		
		$('.header #cur_sess', view).html(data['session_id']);
		
		view = $('#data', view);
		
		view.empty();
		
		if(data['session'].length == 0){
			view.append('<span class="no-data">Session Empty</span>');
			}
		
		update_view_h(data['session'], view);
		
		};
	
	
	function get_data(sid){
		$('#loading_data').show();
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_get',
			data: {
				sec_token: token,
				action: 'get',
				sid: sid
				},
			success: update_view,
			error: function(xhr, desc){
				$('#loading_data').hide();
				
				}
			});
		}
	
	
	
	$('#data').on('click', '.more', function(event){
		event.preventDefault();
		$('.more-ex:first', $(this).parent()).slideToggle(200);
		});
	
	
	
	$('#list').on('click', '.item', function(event){
		
		$('#list .item.active').removeClass('active');
		
		var a = $(this);
		a.addClass('active');
		
		
		get_data(a.attr('data-sid'));
		});
	
	
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: 'ajax.php?_list',
		data: {
			sec_token: token,
			action: 'list'
			},
		success: function(data){
			var x = $('#list');
			$.each(data['sessions'], function(_,sess){
				x.append($('<li title="'+sess['size']+'b | Modified: '+(new Date(parseInt(sess['mod'])*1000)).toUTCString()+'" data-sid="'+sess['id']+'" class="item">'+sess['id']+'</li>'))
				});
			
			$('#list .item').first().trigger('click');
			}
		});
		
	
	
	$('#data').on('mouseover', 'li', function(event){
		$(this).addClass('hover');
		return false;
		});
		
	$('#data').on('mouseout', 'li', function(event){
		$(this).removeClass('hover');
		return false;
		});
	
	
	});