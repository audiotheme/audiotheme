(function($) {
	// .clear-on-add will clear the value of a form element in a newly added row
	// .remove-on-add will remove an element from a newly added row
	
	var methods = {
		init : function( options ) {
			var settings = { };
			if (options) $.extend(settings, options);

			return this.each(function() {
				var repeater = $(this)
					firstItem = repeater.find('.meta-repeater-item:eq(0)');
				
				firstItem.parent().sortable({
					axis: 'y',
					forceHelperSize: true,
					forcePlaceholderSize: true,
					helper: function(e, ui) {
						var $helper = ui.clone();
						$helper.children().each(function(index) {
						  $(this).width(ui.children().eq(index).width())
						});
						
						return $helper;
					},
					start: function(e, ui) {
						var colCount = ui.helper.children().length;
						//ui.placeholder.css('visibility','visible').html('<td colspan="' + colCount + '">&nbsp;</td>');
					},
					update: function(e, ui) {
						repeater.metaRepeater('updateIndex');
					}
				});
				
				repeater.data('itemIndex', firstItem.siblings().length).data('itemTemplate', firstItem.clone());
				
				repeater.find('.meta-repeater-add-item').on('click', function(e) {
					e.preventDefault();
					$(this).closest('.meta-repeater').metaRepeater('addItem');
				});
				
				repeater.on('click', '.meta-repeater-remove-item', function(e) {
					e.preventDefault();
					$(this).closest('.meta-repeater-item').remove().closest('.meta-repeater').metaRepeater('updateIndex');
				});
				
				repeater.on('blur', 'input', function() {
					$(this).closest('.meta-repeater').find('.meta-repeater-item').removeClass('meta-repeater-active-item');
				}).on('focus', 'input', function() {
					$(this).closest('.meta-repeater-item').addClass('meta-repeater-active-item').siblings().removeClass('meta-repeater-active-item');
				});
			});
		},
		
		addItem : function() {
			var repeater = $(this),
				itemIndex = repeater.data('itemIndex'),
				itemTemplate = repeater.data('itemTemplate');
			
			repeater.find('.meta-repeater-items').append(itemTemplate.clone()).children(':last-child').find('.clear-on-add').val('').each(function(e) {
				var $this = $(this);
				$this.attr('name', $this.attr('name').replace('[0]', '[' + itemIndex + ']') );
			}).end().find('.remove-on-add').remove();
			
			repeater.data('itemIndex', itemIndex+1 ).metaRepeater('updateIndex');
		},
			
		updateIndex : function() {
			$('.meta-repeater-index', this).each(function(i) {
				$(this).text(i + 1 + '.');
			});
		}
	};	
	
	$.fn.metaRepeater = function(method) {
		if ( methods[method] ) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.metaRepeater');
		}    
	};
})(jQuery);






(function($) {
	// .clear-on-add will clear the value of a form element in a newly added row
	// .remove-on-add will remove an element from a newly added row
	
	var methods = {
		init : function( options ) {
			var settings = { };
			if (options) $.extend(settings, options);

			return this.each(function() {
				var metaList = $(this);
				
				$('tbody', this).sortable({
					axis: 'y',
					forceHelperSize: true,
					forcePlaceholderSize: true,
					helper: function(e, ui) {
						var $helper = ui.clone();
						$helper.children().each(function(index) {
						  $(this).width(ui.children().eq(index).width())
						});
						
						return $helper;
					},
					start: function(e, ui) {
						var colCount = ui.helper.children().length;
						ui.placeholder.css('visibility','visible').html('<td colspan="' + colCount + '">&nbsp;</td>');
					},
					update: function(e, ui) {
						metaList.audiothemeMetaList('updateIndex');
					}
				});
				
				metaList.data('itemIndex', $('tbody tr', this).length).data('itemRow', $('tbody tr:first-child', this).clone());
				
				$('.add-list-item', this).click(function(e) {
					e.preventDefault();
					$(this).closest('.meta-list').audiothemeMetaList('addRow');
				});
				
				metaList.on('click', '.remove-list-item', function(e) {
					e.preventDefault();
					$(this).closest('tr').remove().end().closest('.meta-list').audiothemeMetaList('updateIndex');
				});
				
				metaList.on('blur', 'input', function() {
					$(this).closest('tbody').find('tr').removeClass('active-row');
				}).on('focus', 'input', function() {
					$(this).closest('tr').addClass('active-row').siblings('tr').removeClass('active-row');
				});
			});
		},
		
		addRow : function() {
			var metaList = $(this),
				itemIndex = metaList.data('itemIndex'),
				itemRow = metaList.data('itemRow');
			
			metaList.find('tbody').append(itemRow.clone()).children('tr:last-child').find('.clear-on-add').val('').each(function(e) {
				var $this = $(this);
				$this.attr('name', $this.attr('name').replace('[0]', '[' + itemIndex + ']') );
			}).end().find('.remove-on-add').remove();
			
			metaList.data('itemIndex', itemIndex+1 ).audiothemeMetaList('updateIndex');
		},
			
		updateIndex : function() {
			$('.meta-list-index', this).each(function(i) {
				$(this).text(i + 1 + '.');
			});
		}
	};	
	
	$.fn.audiothemeMetaList = function(method) {
		if ( methods[method] ) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.audiothemeMetaList');
		}    
	};
})(jQuery);