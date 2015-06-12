
var expanded_mores = [];
var data_view_scroll = 0;

$(function(){
	
	Array.prototype.clone = function() {
		return this.slice(0);
	};

	Array.prototype.cpush = function(item) {
		var copy = this.slice(0);
		copy.push(item);
		return copy;
	};

	function isNumeric(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}

	
	var token = $('body').attr('data-token');
	var user_role = $('body').attr('data-role');
	var my_ssid = $('body').attr('data-session-id');
	
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
	
	function nodata(){
		$('#data')
			.html('<b><i>Empty</i></b>')
			.attr('data-sid','');
		$('#cur_session').text('---');
		$('#refresh_data, #delete_session').hide();
		}
	
	function type_selector(t){
		var sel = ' selected="selected"';
		return '<select class="type" title="Change Type">'+
				'<option value="string"'+(t=='string'?sel:'')+'>String</option>'+
				'<option value="boolean"'+(t=='boolean'?sel:'')+'>Boolean</option>'+
				'<option value="integer"'+(t=='integer'?sel:'')+'>Integer</option>'+
				'<option value="double"'+(t=='double'?sel:'')+'>Double</option>'+
				'<option value="null"'+(t=='null'?sel:'')+'>NULL</option>'+
				'<option value="array"'+(t=='array'?sel:'')+'>Array (empty)</option>'+
			'</select>';
	}
	
	function val_input_ele(type, value){
		switch (type.toLowerCase()) {
			case 'string':
				return '<textarea class="value string" title="string" spellcheck="false">'+ value +'</textarea>';
			case 'integer':
				return '<input type="number" class="value integer" title="'+type+'" spellcheck="false" value="'+ parseInt(value) +'">';
			case 'double':
				return '<input type="number" class="value integer" title="'+type+'" spellcheck="false" value="'+ parseFloat(value) +'">';
			case 'boolean':
				if(isNumeric(value)){
					value = (value > 0);
				}else{
					value = ((''+value).toLowerCase()==='no' ? false : value);
					value = ((''+value).toLowerCase()==='false' ? false : value);
				}
				
				value = Boolean(value);
				
				return '<select class="value boolean"><option value="1"'+(value?' selected="selected"':'')+'>True</option><option value="0"'+(!value?' selected="selected"':'')+'>False</option></select>';
			case 'null':
				return '<span class="value null">NULL</span>';
			case 'array':
				return '<span class="value array">Empty Array</span>';
			default:
				return '<span class="value unknown">Unknown Type ('+ type +'):'+ value+'</span>';
		}
	}
	
	function update_view_h(data, view, stack){
		stack = stack || [];
		
		$.each(data, function(key, value){
			
			switch(value['type']){
				case 'object':
					var new_view = $('<ul class="more-ex"></ul>');
					update_view_h(value['value'], new_view, stack.cpush(value['key']));
					
					var a = '<li class="array_add"><a href="#add-var">+ Add Item</a></li>';
					
					if(new_view.is(':empty')){
						new_view.addClass('empty');
						new_view.append($('<i>Empty</i>'));
						$(a).prependTo(new_view);
					} else {
						$(a).prependTo(new_view);
						$(a).appendTo(new_view);
					}
					
					var ele = $(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							'<span class="value array object" title="object">'+
								'<a href="#" class="more">[Object ...]</a>'+
								'<span class="class-info">'+ value['class'] +'</span>'+
								'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
							'</span>'+
						'</li>');
					
					ele.data('var_path', stack.cpush(value['key']));
					
					$('.array', ele).after(new_view);
					view.append(ele);
					break;
					
				case 'array':
					var new_view = $('<ul class="more-ex"></ul>');
					update_view_h(value['value'], new_view, stack.cpush(value['key']));
					
					
					var a = '<li class="array_add"><a href="#add-var">+ Add Item</a></li>';
					
					if(new_view.is(':empty')){
						new_view.addClass('empty');
						new_view.append($('<i>Empty</i>'));
						$(a).prependTo(new_view);
					} else {
						$(a).prependTo(new_view);
						$(a).appendTo(new_view);
					}
					
					
					var ele = $(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							'<span class="value array" title="array">'+
								'<a href="#" class="more">[Array ...]</a>'+
								'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
							'</span>'+
						'</li>');
					
					ele.data('var_path', stack.cpush(value['key']));
					
					$('.array', ele).after(new_view);
					view.append(ele);
					break;
					
				case '-string':
				
					var ele = $(
						'<li title="string">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							/* '<textarea class="value string" title="string" spellcheck="false">'+
								value['value']+
							'</textarea>'+ */
							val_input_ele(value['type'], value['value']) +
							'<img class="save" src="images/save_16.png" alt="Save" title="Save" />'+
							'<img class="revert" src="images/revert_16.png" alt="Revert" title="Revert" />'+
							'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
							type_selector(value['type']) +
							'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
						'</li>');
					ele.data('var_path', stack.cpush(value['key']));
					ele.data('orig_val', value['value']);
					ele.data('orig_type', value['type']);
					
					view.append(ele);
					break;
					
				case '-double':
				case '-integer':
				
					var ele = $(
						'<li title="'+value['type']+'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							val_input_ele(value['type'], value['value']) +
							'<img class="save" src="images/save_16.png" alt="Save" title="Save" />'+
							'<img class="revert" src="images/revert_16.png" alt="Revert" title="Revert" />'+
							'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
							type_selector(value['type']) +
							'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
						'</li>');
					ele.data('var_path', stack.cpush(value['key']));
					ele.data('orig_val', value['value']);
					ele.data('orig_type', value['type']);
					
					view.append(ele);
					break;
					
				case '-boolean':
					
					var ele = $(
						'<li title="boolean">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							val_input_ele(value['type'], value['value']) +
							'<img class="save" src="images/save_16.png" alt="Save" title="Save" />'+
							'<img class="revert" src="images/revert_16.png" alt="Revert" title="Revert" />'+
							'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
							type_selector(value['type']) +
							'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
						'</li>');
					ele.data('var_path', stack.cpush(value['key']));
					ele.data('orig_val', value['value']);
					ele.data('orig_type', value['type']);
					
					view.append(ele);
					break;
					
				default:
					
					var ele = $(
						'<li title="'+ value['type'] +'">'+
							'<span class="key '+ typeof value['key'] +'" title="'+ typeof value['key'] +':'+ value['key'] +'">'+
								(value['key'] == '' 
									? '<i class="empty">[Empty]</i>' 
									: value['key']
									) +
							'</span>'+
							(value['flag']
								?'<span class="'+ value['flag'] +'" title="'+ value['flag'] +'"><img src="images/'+ value['flag'] +'.png" /></span>'
								:'')+
							val_input_ele(value['type'], value['value']) +
							'<img class="save" src="images/save_16.png" alt="Save" title="Save" />'+
							'<img class="revert" src="images/revert_16.png" alt="Revert" title="Revert" />'+
							'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
							type_selector(value['type']) +
							'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
						'</li>');
					ele.data('var_path', stack.cpush(value['key']));
					ele.data('orig_val', value['value']);
					ele.data('orig_type', value['type']);
					
					view.append(ele);
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
		
		restore_expanded();
		restore_scroll();
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
					
					if(!sid()){
						nodata();
						}
					
					return;
					}
				
				$.each(data['sessions'], function(_,sess){
					classes = [];
					
					if(sess['id'] == my_ssid){
						classes.push('mine');
						}
					if(sess['size'] == 0){
						classes.push('empty');
						}
						
					classes = classes.join(' ');
					
					x.append($('<li title="'+ sess['size'] +'b | Modified: '+ (new Date(parseInt(sess['mod'])*1000)).toUTCString() +'" data-sid="'+ sess['id'] +'" class="item '+ classes +'">'+ sess['id'] +'</li>'))
					});
				
				if(!sid()) $('#list .item:first').trigger('click');
				}
			});
		}
	
	
	
	function del_session(id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_del',
			data: {
				sec_token: token,
				sid: id,
				action: 'del'
				},
			success: function(data){
				
				if(data.length > 0){
					alert('Unknown error deleting session');
					return;
					}
				
				if(data['success'] == 0){
					alert(data['error']);
					return;
					}
				
				var e = $('#list .item[data-sid='+id+']');
				var n = e.next().add(e.prev());
				
				e.remove();
				
				if(n.length){
					n.first().trigger('click');
				}else{
					nodata();
					}
				
				// display success banner
				
				}
			});
		}
	
	
	
	
	
	
	
	function keep_expanded(){
		expanded_mores = [];
		$('#data .more-ex:visible').map(function(_, ele){
			expanded_mores.push($(this).parent().data('var_path').join());
		});
	}
	
	
	
	
	
	
	function restore_expanded(){
		
		if(!expanded_mores.length) return;
		
		$('#data .array').each(function(_, ele){
			ele = $(ele).parent();
			
			if($.inArray(ele.data('var_path').join(), expanded_mores) != -1){
				ele.find('.more-ex:first').show();
			}
			
		});
		
		expanded_mores = [];
	}
	
	
	
	function keep_scroll(){
		data_view_scroll = $('#data').scrollTop();
	}
	function restore_scroll(){
		if(data_view_scroll){
			$('#data').scrollTop(data_view_scroll);
			data_view_scroll = 0;
		}
		
	}
	
	
	function refresh_data(){
		// retain scroll position
		
		keep_expanded();
		keep_scroll();
		
		get_data(sid());
		}
	
	
	// fill session list
	list();
	
	// if the page was opened to a specific session, load it
	if(start_min){
		if(window.opener){
			expanded_mores = window.opener.expanded_mores;
			data_view_scroll = window.opener.data_view_scroll;
		}
		get_data(sid());
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
		
		keep_expanded();
		keep_scroll();
		
		window.open(
			'index.php?session_id='+sid(),
			'_blank',
			'height=500,width=800,menubar=0,location=0,toolbar=0');
		});
	
	
	
	
	
	$('#delete_session').click(function(event){
		event.preventDefault();
		
		if(!confirm('WARNING: \nAre you sure you want to delete this session?')){
			return;
			}
		
		del_session(sid());
		
		});
	
	
	
	
	
	$('#data, .header').on('keyup', '.array_add .key, .new-row .key', function(event){
		// change var_path
		$this = $(this);
		
		var path = $this.parent().data('var_path');
		path[path.length-1] = $this.val();
		$this.parent().data('var_path', path);
	});
	
	
	
	$('#data').on('click', '.array_add a', function(event){
		event.preventDefault();
		$this = $(this);
		var ele = $('<li class="input-row">'+
						'<input type="text" class="key" />'+
						'<textarea class="value string"></textarea>'+
						'<img class="save new" src="images/save_16.png" alt="Save" title="Save" />'+
						'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
						type_selector('string') +
						'<img class="delete" src="images/delete_16.png" alt="Delete" title="Delete" />'+
					'</li>');
		
		
		ele.data('var_path', $this.parent().parent().parent().data('var_path').cpush(''));
		
		if($this.index() == 0){
			ele.insertAfter($this);
		} else {
			ele.insertBefore($this);
		}
		ele.find('input:first').focus();
	});
	
	
	
	
	
	
	
	$('#add_var').click(function(event){
		event.preventDefault();
		
		$this = $(this);
		
		if($this.siblings('.new-row').length){
			$this.siblings('.new-row').find('.cancel').click();
			return;
		}
		
		var ele = $('<div class="new-row">'+
						'<input type="text" class="key" placeholder="Key" />'+
						'<textarea class="value string" placeholder="String Value - (use button on right to change type)"></textarea>'+
						'<img class="save new" src="images/save_16.png" alt="Save" title="Save" />'+
						'<img class="alter" src="images/edit_16.png" alt="Change Type" title="Change Type" />'+
						type_selector('string') +
						'<img class="cancel" src="images/x_16.png" alt="Cancel" title="Cancel" />'+
					'</div>').hide();
		
		
		ele.data('var_path', ['']);
		
		
		ele.insertAfter($this).slideDown(200);
		
		ele.find('input:first').focus();
	});
	
	
	
	
	
	// cancel new new from button
	$('.header').on('click', '.cancel', function _onclick_cancel(event){
		event.preventDefault();
		$(this).parent().slideUp(200, function(){
			$(this).remove();
		});
	});
	
	
	// array and object expansion
	$('#data').on('click', '.more', function _onclick_more(event){
		event.preventDefault();
		$('.more-ex:first', $(this).parent().parent()).slideToggle(200);
	});
	
	
	// show type changer
	$('#data, .header').on('click', '.alter', function _onclick_alter(event){
		event.preventDefault();
		$(this).hide();
		$(this).siblings('.type').show().focus();
	});
	
	
	// hide/cancel type changer
	$('#data, .header').on('blur', '.type', function _onblur_type(event){
		event.preventDefault();
		$(this).hide();
		$(this).siblings('.alter').css('display','');
	});
		
	// hide type changer
	$('#data, .header').on('change', '.type', function _onchange_type(event){
		event.preventDefault();
		$this = $(this);
		$this.hide();
		$this.siblings('.alter').css('display','');
		
		var sib = $this.siblings('.value');
		var val = sib.val();
		sib.replaceWith(val_input_ele($this.val(), val));
		$this.siblings('.save, .revert').show();
	});
	
	
	
	
	$('#data, .header').on('keyup', '.value.integer', function _onkeyup_integer(event){
		$this = $(this);
		
		if($this.val() == $this.parent().data('orig_val')){
			$this.parent().find('.save, .revert').hide();
		} else {
			$this.parent().find('.save, .revert').show();
		}
	
	});
	
	
	
	
	$('#data, .header').on('keypress', '.value.integer', function( event ) {
		var regex = new RegExp(/[a-zA-Z!@#$%^&*(){}\[\]\\|:;'"<>\/?\-_+=~`]/g);
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		
		if (regex.test(key)) {
			event.preventDefault();
			return false;
		}
		
	});
	
	
	$('#data, .header').on('keyup', '.value.string', function _onkeyup_string(event){
		$this = $(this);
		
		if($this.val() == $this.parent().data('orig_val')){
			$this.siblings('.save, .revert').hide();
		} else {
			$this.siblings('.save, .revert').show();
		}
		
		if(parseInt($this.css('height'), 10) > 96) return;
		
		rows = Math.min(Math.max(($this.val().match(/\n/g)||[]).length, 0), 5) + 1;
		//rows += (event.which==13) ?1 :0;
		$this.css({height: (rows * 1.2) +'em'});
	});
	
	
	
	$('#data').on('click', '.revert', function _onclick_revert(event){
		event.preventDefault();
		var $this = $(this);
		var $par = $this.parent();
		var var_type = $par.find('.type').val();
		var orig_type = $par.data('orig_type');
		if(orig_type != var_type) {
			$this.siblings('.value').replaceWith(val_input_ele(orig_type));
		}
		$par.find('.type').val(orig_type);
		$par.find('.value').val($par.data('orig_val'));
		
		$this.hide();
		$this.siblings('.save').hide();
		
	});
	
	
	
	
	
	
	$('#data, .header').on('click', '.save', function _onclick_save(event){
		event.preventDefault();
		var $this = $(this);
		var $par = $this.parent();
		var value = $par.find('.value').val();
		var var_path = $par.data('var_path');
		var var_type = $par.find('.type').val();
		var action = 'edit';
		
		if($this.hasClass('new')){
			action = 'add_var';
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_'+action,
			data: {
				sec_token: token,
				action: action,
				sid: sid(),
				var_path: var_path,
				var_type: var_type,
				value: value
			},
			success: function(data){
				
				if(data['success'] == 0){
					alert(data['error']);
					return;
					}
				
				// TODO: display success banner
				if($par.is('.new-row')){
					$par.find('.cancel').click();
				}
				
				if(action == 'add_var'){
					refresh_data();
					return;
				}else if(action == 'edit'){
					refresh_data();
				}
				
				$par.find('.save, .revert').hide();
				$par.data('orig_val', value);
				$par.data('orig_type', var_type);
			},
			error: function(xhr, desc){
				//$('#loading_data').hide();
				alert('Error while saving');
			}
		});
		
	});
	
	
	
	$('#data').on('click', '.input-row .delete', function(event){
		event.preventDefault();
		event.stopImmediatePropagation();
		
		$(this).parent().remove();
	});
	
	
	$('#data').on('click', '.delete', function _onclick_del(event){
		event.preventDefault();
		var $this = $(this);
		var $par = $this.parent();
		
		if($par.is('.array'))
			$par = $par.parent();
		
		var value = 'UNUSED';
		var var_path = $par.data('var_path');
		var var_type = 'DELETE';
		var action = 'del_var';
		
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'ajax.php?_'+action,
			data: {
				sec_token: token,
				action: action,
				sid: sid(),
				var_path: var_path,
				var_type: var_type,
				value: value
			},
			success: function(data){
				
				if(data['success'] == 0){
					alert(data['error']);
					return;
					}
				
				// TODO: display success banner
				
				$par.remove();
				
			},
			error: function(xhr, desc){
				//$('#loading_data').hide();
				alert('Error while deleting');
			}
		});
		
	});
	
	
	
	
	$('#data').on('change', '.value.string, .value.integer, .value.boolean', function _onchange_value(event){
		//event.preventDefault();
		$this = $(this);
		
		if($this.val() == $this.parent().data('orig_val')){
			$this.parent().find('.save, .revert').hide();
		} else {
			$this.parent().find('.save, .revert').show();
		}
		
	});
	
	
	
	/* $('#data').on('paste', '[contenteditable]' ,function _onpaste_all(e) {
		e.preventDefault();
		var text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
		
		document.execCommand('insertText', false, text);
		}); */
	
	
	
	
	
	
	// call up session for view
	$('#list').on('click', '.item', function _onclick_sess_list(event){
		
		$('#list .item.active').removeClass('active');
		
		var a = $(this);
		a.addClass('active');
		
		
		get_data(a.attr('data-sid'));
		});
	
	
	
	
	
	// search button
	$('#sid_search_button').click(function _onclick_search(event){
		list($('#sid_search').val());
		event.preventDefault();
		});
	
	
	
	
	
	// row highlighting for session data
	$('#data').on('mouseover', 'li', function _onhover_data_li(event){
		$(this).addClass('hover');
		return false;
		});
	
	// ...continued
	$('#data').on('mouseout', 'li', function _unhover_data_li(event){
		$(this).removeClass('hover');
		return false;
		});
	
	
	});