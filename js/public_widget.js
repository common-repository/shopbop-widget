var SWJquery = jQuery;
SWJquery(function($) {
   
	initShopbop();

	function initShopbop(){
		moveForcedLocation();
		checkParentWidth(); 
		reInitCarousel();
		loadExternal();
	}

	function moveForcedLocation() {
		$('.shopbop-core-widget--temp-location').each(function() {
			$widget = $(this);
			var target = $widget.data('forcelocation');
			if(target && target.indexOf(':') > 1) {
				target = target.split(':');
				var $target = $(target[0]);
				if($target && $target.length > 0) {
					if(target[1] == 'after') {
						$widget.detach().appendTo($target)
					}
					else {
						$widget.detach().prependTo($target)
					}
				}
				$widget.removeClass('shopbop-core-widget--temp-location');
			}
		});
	}

	function loadExternal()
	{
		var removeLoader = function()
		{
			SWJquery(".shopbop-widget-loading-bg").fadeOut(300, function() { $(this).remove(); });
		};

		SWJquery(".shopbop-widget-wrapper").css({visibility:"visible"});

		var lang = SWJquery(".shopbop-core-widget:eq(0)").data("lang");
		SWJquery.get(location.protocol+"//widgetcontent-shopbop.stickyhosting.co.uk/3.1/promotion-"+lang+".json?_=" + new Date().getTime(), function(data){
			if(data && data.text) {
				SWJquery(".shopbop-widget-feature").html(data.text);
			}
			SWJquery.get(location.protocol+"//widgetcontent-shopbop.stickyhosting.co.uk/3.1/marketing-"+lang+".json?_=" + new Date().getTime(), function(data){
				if(data && data.text) {
					SWJquery(".shopbop-widget-marketing-message").html(data.text);
				}
			}).always(removeLoader);
		}).always(removeLoader);
	}

	function reInitCarousel(){
		checkParentWidth();

		var widget_index = 1;

		SWJquery('.shopbop-core-widget .shopbop-widget-carousel').each(function(){
			var root = SWJquery(this);

			var totalLinks   = root.find('ul>li').length;
			var visibleLinks = root.find('ul').carouFredSel().triggerHandler("configuration", "items.visible");

			if (root.parent().parent().parent().parent().has('shopbop-mobile')) {
				var widgetWidth  = root.width();
			} else {
				var widgetWidth  = root.parent().width() - 300;
			}

			root.attr('id', 'shopbop-widget-' + widget_index);
			root.find('ul').carouFredSel({
			
				prev : '#shopbop-widget-' + widget_index + ' .shopbop-widget-carousel-prev',
				next : '#shopbop-widget-' + widget_index + ' .shopbop-widget-carousel-next',
				pagination : '#shopbop-widget-' + widget_index + ' .shopbop-widget-carousel-pager',
				
				circular : false,
				infinite: true,
				auto: false,
				height : 250,
				width: widgetWidth,
				align: "center",
				scroll:
				{
					easing: 'swing'
				},

				debug: false
			});

			widget_index++;
		});

		// add close button to content panes for mobile sized widgets
		SWJquery('.shopbop-core-widget').not('.shopbob-bp--small').each(function(i, v) {
			$panes = SWJquery(v).find('.shopbop-content-pane');
			if ($panes.find('.close-pane').length == 0) {
				$panes.each(function (index, value) {
					// create the close button
					var value = SWJquery(value);
					var closeButton = document.createElement("div");
					closeButton.setAttribute("class", "close-pane");
					value.append(closeButton);

					SWJquery(closeButton).on('click', function(evt) {
						SWJquery(v).find('.shopbop-widget-header__nav a').removeClass('selected');
						SWJquery(evt.srcElement).closest('.shopbop-content-pane').removeClass('shopbop-active-pane');
						value.closest('.shopbop-widget-body').removeClass('shopbob--showing-slider');
					});
				});
			}
		});

		// remove close button from content panes for non mobile sized widgets
		SWJquery('.shopbop-core-widget.shopbob-bp--small').each(function(i, v){
			$panes = SWJquery(v).find('.shopbop-content-pane .close-pane');
			if ($panes.length > 0) {
				$panes.remove();
			}

			// if no category is selected, select the first pane by default
			// this occurs when the pane is closed and then resized to
			// horizontal mode
			if (SWJquery(v).find('.shopbop-widget-header__nav a.selected').length == 0) {
				selectFirstPane(  SWJquery(v).closest('.shopbop-core-widget') );
			}
		});
	}

	//Element query
	function checkParentWidth(){
		SWJquery('.shopbop-core-widget').each(function(i, v) {
			var widget = SWJquery(v);
			var parentWidth = Math.floor(widget.outerWidth());
			var sizeClass = 'large';
			var size = [{'NAME':'shopbop-large', 'SIZE':99999},
						{'NAME':'shopbop-medium','SIZE':1024},
						{'NAME':'shopbop-small', 'SIZE':550},
						{'NAME':'shopbop-mobile','SIZE':500},
						{'NAME':'shopbop-mini','SIZE':201}
					];

			SWJquery.each(size, function( index, value ) {
				widget.removeClass(value.NAME);
				if(parentWidth < value.SIZE){
					sizeClass = value.NAME;
				}
			});

			var breakpoints = {
				'shopbob-bp--small': 600,
				'shopbob-bp--medium': 850,
				'shopbob-bp--large': 960,
			};
			$.each(breakpoints, function( index, value ) {
				widget.toggleClass(index, parentWidth >= value);
			});

			widget.addClass(sizeClass);

			if( sizeClass != 'shopbop-mobile' ) {
				if (!widget.hasClass('shopbop-auto')) {
					selectFirstPane(widget);
				}
			}
		});
	}

	SWJquery( window ).resize(function() {
		reInitCarousel();
	});

	//Hides the
	SWJquery('.shopbop-core-widget').mouseleave(function(){
		//SWJquery('.shopbop-core-widget .shopbop-widget-carousel-next').hide();
		//SWJquery('.shopbop-core-widget .shopbop-widget-carousel-prev').hide();
	});

	SWJquery('.shopbop-core-widget').mouseenter(function(){
		//SWJquery('.shopbop-core-widget .shopbop-widget-carousel-next').fadeIn("slow").show();
		//SWJquery('.shopbop-core-widget .shopbop-widget-carousel-prev').fadeIn("slow").show();
	});

	//Navigation button
	SWJquery('.shopbop-core-widget a.shopbop-pane-button').click(function(event){
		event.preventDefault();
		toggleWidgetPanels( this );
	});

	function toggleWidgetPanels( el ){
		var panel = SWJquery(el).data('shopbop-panel');
		var $widget = SWJquery(el).closest('.shopbop-core-widget');

		SWJquery(el).closest( '.shopbop-widget-header__nav' ).find('a').removeClass('selected');
		SWJquery(el).addClass('selected');

		$widget.find('.shopbop-content-pane').removeClass('shopbop-active-pane');
		$widget.find('.shopbop-widget-'+panel).addClass('shopbop-active-pane');
		$widget.find('.shopbop-widget-body').addClass('shopbob--showing-slider');

		reInitCarousel();
	}

	function selectFirstPane( el ){
	
		SWJquery(el).addClass('shopbop-auto');
		SWJquery('.shopbop-auto a.shopbop-pane-button1').addClass('selected');
		SWJquery('.shopbop-auto .shopbop-widget-panel1').addClass('shopbop-active-pane');
		SWJquery(el).closest('.shopbop-core-widget').find('.shopbop-widget-body').addClass('shopbob--showing-slider');
	}

	// SHOW DESCRIPTION FOR ITEMS
	SWJquery('.shopbop-core-widget .shopbop-widget-carousel li').mouseenter(function() {
		var self = $(this);

		self.closest('.shopbop-widget-carousel').find('li').removeClass('hovered').removeClass('not-hovered');
		self.closest('li').addClass('hovered').siblings().addClass('not-hovered');
	});

	var box_keep_open = '.shopbop-core-widget .shopbop-widget-carousel li, .shopbop-core-widget .shopbop-widget-carousel-info';
	SWJquery(box_keep_open).mouseleave(function(e) {
		if(!SWJquery(e.relatedTarget).is(box_keep_open) && SWJquery(e.relatedTarget).closest(box_keep_open).length == 0) {          
			$(this).closest('.shopbop-widget-carousel').find('.hovered').removeClass('hovered');
			$(this).closest('.shopbop-widget-carousel').find('.not-hovered').removeClass('not-hovered');
		}
	});

	SWJquery('.shopbop-widget-carousel-info').hide();
});
