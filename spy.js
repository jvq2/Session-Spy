$(function(){

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
						'<li>'+
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
						'<li>'+
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
						'<li>'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								value['key']+
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'">'+ value['flag'] +'</span>'
								:'')+
							'<span class="value '+ value['type'] +'" title="'+ value['type'] +'">'+
								value['value']+
							'</span>'+
						'</li>');
				}
			
			});
		}
	
	function update_view(data){
		var view = $('.ui-layout-center');
		
		if(!data['success']){
			data['error'] = data['error'] ? data['error'] : "Unknown";
			$('#data', view).html(
								'<b><i>Could not fetch '+
								'session data. Error: '+data['error']+
								'</i></b>');
			return;
			}
		
		$('.header', view).html(data['session_id']);
		
		view = $('#data', view);
		
		view.empty();
		
		if(data['session'].length == 0){
			view.append('<span class="no-data">Session Empty</span>');
			}
		
		update_view_h(data['session'], view);
		
		};
	
	
	function get_data(sid){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_get',
			data: {
				action: 'get',
				sid: sid
				},
			success: update_view
			});
		}
	
	
	
	$('#data').on('click', '.more', function(event){
		event.preventDefault();
		$('.more-ex:first', $(this).parent()).slideToggle(200);
		});
	
	
	
	$('#list').on('click', '.item', function(e){
		
		$('#list .item.active').removeClass('active');
		
		var a = $(this);
		a.addClass('active');
		
		
		get_data(a.text());
		});
	
	
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: 'ajax.php?_list',
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