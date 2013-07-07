$(function(){
	
	var token = $('body').attr('data-token');
	var user_role = $('body').attr('data-role');
	
	var start_min = ($('body').attr('data-start-min') == 'true');
	
	var layout = $('body').layout({
		north__size:		50,
		north__resizable:	false,
		north__closable:	true,
		west__size:			265,
		//west__resizable:	false,
		west__initClosed:	start_min,
		north__initClosed:	start_min,
		east__initClosed:	true
		});
	
	
	
	// get/set current sid
	function sid(id){
		if(typeof id === "undefined"){
			return $('#data').attr('data-sid');
			}
		return $('#data').attr('data-sid', id);
		}
	
	
	
	
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
		
		// save the SID in the view container
		sid(data['session_id']);
		
		view.empty();
		
		if(data['session'].length == 0){
			view.append('<span class="no-data">Session Empty</span>');
			}
		
		update_view_h(data['session'], view);
		
		};
	
	
	
	function get_data(id){
		$('#loading_data').show();
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_get',
			data: {
				sec_token: token,
				action: 'get',
				sid: id
				},
			success: update_view,
			error: function(xhr, desc){
				$('#loading_data').hide();
				
				}
			});
		}
	
	
	
	function get_users(){
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_user_list',
			data: {
				sec_token: token,
				action: 'user-list'
				},
			success: function(data){
				var x = $('#users');
				
				x.html();
				
				// empty list nothing matched
				if(data.length < 1 || !data['success']){
					x.append($('<span class="no-data">Empty</span>'));
					return;
					}
				
				$.each(data['users'], function(_,user){
					x.append($('<li title="role: '+user['role']+'" data-uid="'+user['id']+'" class="item">'+user['name']+'<span class="role">'+user['role']+'</span></li>'))
					});
				
				}
			});
		}
	
	
	
	function list(search){
		search = (typeof search === "undefined") ? "" : search;
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_list',
			data: {
				sec_token: token,
				search: search,
				action: 'list'
				},
			success: function(data){
				var x = $('#list');
				
				x.empty();
				
				// empty list nothing matched
				if(data.length < 1 || !data['success']){
					x.append($('<span class="no-data">Empty</span>'));
					return;
					}
				
				$.each(data['sessions'], function(_,sess){
					x.append($('<li title="'+sess['size']+'b | Modified: '+(new Date(parseInt(sess['mod'])*1000)).toUTCString()+'" data-sid="'+sess['id']+'" class="item">'+sess['id']+'</li>'))
					});
				
				if(!sid()) $('#list .item').first().trigger('click');
				}
			});
		}
	
	
	
	function refresh_data(){
		get_data(sid());
		}
	
	
	// fill session list
	list();
	
	// if the page was opened to a specific session, load it
	if(start_min){
		refresh_data();
		}
	
	// fill user list
	if(user_role == 'admin'){
		get_users();
		}
	
	
	$('#add_user_dialog').dialog({autoOpen:false});
	
	
	
	// logout button
	$('#logout').click(function(event){
		event.preventDefault();
		window.location.href = "?logout&sec_token="+encodeURIComponent(token);
		});
	
	
	
	
	
	// users button
	$('#show_users').click(function(event){
		event.preventDefault();
		layout.toggle('east');
		});
	
	
	
	
	// refresh button
	$('#refresh_data').click(refresh_data);
	
	
	
	
	// fullscreen / toggle_panels button
	$('#toggle_panels').click(function(event){
		event.preventDefault();
		if(layout.state.west.isClosed && layout.state.east.isClosed && layout.state.north.isClosed){
			layout.open('west');
			layout.open('north');
			// dont open east (users)
		}else{
			layout.close('west');
			layout.close('north');
			layout.close('east');
			}
		});
	
	
	
	// open in new window button
	$('#data_new_window').click(function(event){
		event.preventDefault();
		
		window.open(
			'index.php?session_id='+sid(),
			'_blank',
			'height=500,width=800,menubar=0,location=0,toolbar=0');
		});
	
	
	
	
	
	// array and object expansion
	$('#data').on('click', '.more', function(event){
		event.preventDefault();
		$('.more-ex:first', $(this).parent()).slideToggle(200);
		});
	
	
	
	
	
	// call up session for view
	$('#list').on('click', '.item', function(event){
		
		$('#list .item.active').removeClass('active');
		
		var a = $(this);
		a.addClass('active');
		
		
		get_data(a.attr('data-sid'));
		});
	
	
	
	
	
	// search button
	$('#sid_search_button').click(function(event){
		list($('#sid_search').val());
		event.preventDefault();
		});
	
	
	
	
	
	// row highlighting for session data
	$('#data').on('mouseover', 'li', function(event){
		$(this).addClass('hover');
		return false;
		});
	
	// ...continued
	$('#data').on('mouseout', 'li', function(event){
		$(this).removeClass('hover');
		return false;
		});
	
	
	});