/*! glFusion CMS v1.5.2 | https://www.glfusion.org | (c) 2015 glFusion | GNU GPL v2 License */
var glfusion;

glfusion = glfusion || {};

glfusion.admin = {
	configuration: {
		getTabLength: function (tabs) {
			return $(tabs).find('li').length;
		},

		addTab: function (tabs, url, text, index) {
			var newItem = $('<li><a href="' + url + '">' + text + '</a></li>');

			if (index <= this.getTabLength(tabs) - 1) {
				newItem.before($(tabs).find('li').eq(index));
			} else {
				newItem.insertAfter($(tabs).find('li').last());
			}

			tabs.tabs('refresh');
		},

		removeTab: function (tabs, index) {
			$(tabs).find('li').eq(index).remove();
			tabs.tabs('refresh');
		}
	}
};
// currently selected tab
var selectedTab;
$(function() {
	// start bootstrap
	var bootstrap = true;

	// dropdown menu when tabs overflow
	var dropDown = '';
	// init tabs
	var tabs = $("#tabs").tabs({
		beforeActivate: function(e, ui) {
			if (ui.newTab.children('a').attr('href') === '#tab-dropdown') {
				var container = ui.newTab.parent();

				if ($('#tabs-dropdown').length > 0) {
					$('#tabs-dropdown').toggle();
				} else {
					ui.newTab.append(dropDown);
					container.removeClass('ui-tabs-active ui-state-active');

					// show it and the positioning!
					$('#tabs-dropdown').show().position({
						of: ui.newTab,
						my: 'right top',
						at: 'right bottom',
						offset: '0 ' + container.height()
					});
				}

				return false;
			} else {
				$('#tabs-dropdown').hide().parent().removeClass('ui-tabs-active ui-state-active');
				$('.ui-tabs-panel').removeClass('ui-tabs-hide');
			}
			selectedTab = ui.newTab.children('a').attr('href');
		}
	});
	// tabs were getting overflow
	var hiddenTabs = {};
	var dropDownShown = false;
	var lastTabsWidth = 0;

	// click event handler
	$(document.body).click(function(e) {
		var target = $(e.target);
		var targetParent = target.parent();

		if ($('#tabs-dropdown').length > 0) {
			if ((target.attr('class') === 'ui-tabs-anchor') && (target.attr('href') !== '#tab-dropdown')) {
				var idx = tabs.tabs('option', 'active');
				var dummy = idx + ((idx == 0) ? 1 : -1); // dummy is any value not idx
				tabs.tabs("option", "active", dummy);
				tabs.tabs("option", "active", idx);

				$("#tabs-dropdown > li").each(function() {
					var href = $('a', this).attr('href');
					$(href).addClass('ui-tabs-hide');
				});
				return false;
			}

			if ( target.is('a') && (target.attr('href') === '#tab-dropdown')) {
				e.preventDefault();
				return false;
			}

			if ((target.attr('id') === 'tabs-dropdown') ||
			(targetParent.attr('id') === 'tabs-dropdown') ||
			(targetParent.parent().attr('id') === 'tabs-dropdown' )) {
				return dropDownHandler(e);
			}
		}

		$('#tabs-dropdown').hide();
		$('.config_name', tabs).removeClass('active-config');

		if ( target.is('input') || target.is('select') || target.is('textarea') ) {
			var tr = $(target, tabs).parent();

			// save changes
			if ( target.attr('id') == 'save_changes' || target.attr('id') == 'form_reset' ) {
				document.subgroup.action = frmGroupAction + '?' + selectedTab.substr(1);
			}

			// change class of currently active row
			if ( tr.hasClass('config_name') ) tr.addClass('active-config');
		}

		// select config from message box
		if ( target.hasClass('select_config') ) {
			for (key in autocomplete_data ) {
				if ( autocomplete_data[key].value == target.text() &&
				autocomplete_data[key].group == target.attr('group') &&
				autocomplete_data[key].subgroup == target.attr('subgroup'))
				{
					selectTab( '#tab-' + autocomplete_data[key].tab_id, target.attr('href') );
					if ( selectedTab === undefined ) {
						var idx = tabs.tabs('option', 'selected');
						selectedTab = $("#tabs > ul > li:eq(" + idx + ") a").attr('href');
					}
					break;
				}
			}
		}

		// unset action
		if ( target.hasClass('unset_param') ) {
			unset(target, target.attr('href').substr(1) );

			e.preventDefault();
			return false;
		}

		// restore action
		if ( target.hasClass('restore_param') ) {
			restore(target, target.attr('href').substr(1) );

			e.preventDefault();
			return false;
		}
	});

	// dropdown click
	$(document).on('click', '#tabs-dropdown', function(e) {
		dropDownHandler(e);
	});

	function dropDownHandler(e) {
		var target = $(e.target);

		if ( target.is('a') || target.is('li')  ) {
			selectTabInHiddenTabs( target.attr('href') );
		}

		return false;
	}

	/**
	* Select tab by href
	*/
	function selectTab(href, conf) {
		var foundInTabs = false;

		// first search in ordinary tabs
		$("#tabs > ul > li").each(function(idx) {
			var a = $('a', this);

			if (a.attr('href') == href) {
				tabs.tabs('option', 'active', idx);
				if ( conf ) {
					selectConf(conf);
				}
				selectedTab = href;
				foundInTabs = true;

				return true;
			}
		});

		// maybe in hiddenTabs
		if ( !foundInTabs ) {
			for (htab in hiddenTabs) {
				if ( htab == href ) {
					selectTabInHiddenTabs(htab);
					if ( conf ) {
						selectConf(conf);
					}
					foundInTabs = true;
				}
			}
		}

		return foundInTabs
	}

	/**
	* Select tab that reside in drop down by href
	*/
	function selectTabInHiddenTabs(href) {
		$('.ui-tabs-nav li.ui-state-default').each(function() {
			$(this).removeClass('ui-state-active ui-tabs-active');
		});
		$('.ui-tabs-panel', tabs).addClass('ui-tabs-hide');
		href = href.substring(href.lastIndexOf('#'));
		$( href ).removeClass('ui-tabs-hide');
		$(href).show();
		selectedTab = href;
		$('#tabs-dropdown').hide().parent().removeClass('ui-tabs-active ui-state-active');
	}

	function getSelectedConf() {
		var tab = '#' + window.location.search.substr(1);
		var conf = window.location.hash;

		selectTab(tab, conf);
		if ( selectedTab === undefined ) {
			var idx = tabs.tabs('option', 'active');
			selectedTab = $("#tabs > ul > li:eq(" + idx + ") a").attr('href');
		}
	}

	function selectConf(confName) {
		var conf = $("input[name='" + confName.substr(1) + "[nameholder]" + "']").parent();

		conf.addClass('active-config');
	}

	function resizeHandler() {
		var total = getTotalTabsWidth();

		reinitDropDownTab();
		lastTabsWidth = tabs.width();
		tabsOverflowHandler();
	}


	function tabsOverflowHandler() {
		var total = getTotalTabsWidth();

		if ( total.overflowAt !== null ) {
			createDropDownTab(total.overflowAt, total.width);
			lastTabsWidth = tabs.width();
		}
		// select the selected tab
		if ( selectedTab ) {
			selectTab( selectedTab, false );
		}
	}

	function getTotalTabsWidth() {
		var totalWidth = 10;
		var tabsWidth = tabs.width(); // width of the tab div
		var overflowAt = null;

		$("#tabs > ul > li").each(function(idx) {
			totalWidth += ($(this).width() + 5);
			if (totalWidth >= tabsWidth && overflowAt === null) {
				overflowAt = idx;
			}
		});

		return {'width': totalWidth, 'overflowAt': overflowAt};
	}

	function createDropDownTab(idxAfter, totalWidth) {
		var tabsLength = glfusion.admin.configuration.getTabLength(tabs);
		dropDown = '';
		if ( idxAfter > 0 ) {
			idxAfter -= 1;

			// remove tabs after the dropdown
			for ( var i = tabsLength-1; i >= idxAfter; i-- ) {
				var currenTab = $('li:eq('+i+') a', tabs);
				if ( currenTab.length ) {
					var currenTabHref = currenTab.attr('href');
					// when there's a dropdown
					if ( currenTabHref == '#tab-dropdown' ) {
						glfusion.admin.configuration.removeTab(tabs, 1);
					} else {
						var currenTabContent = $( currenTabHref );
						hiddenTabs[currenTabHref] = {
							'tab_title': currenTab.text(),
							'tab_content': currenTabContent.html()
						};
						glfusion.admin.configuration.removeTab(tabs, i);
					}
				}
			}

			if ( $('a[href=#tab-dropdown]', tabs).length ) {
				glfusion.admin.configuration.removeTab(tabs, glfusion.admin.configuration.getTabLength(tabs) - 1);
			}

			for ( tab in hiddenTabs ) {
				dropDown = '<li style="clear:both;"><a href="' + tab + '">' +
				hiddenTabs[tab]['tab_title'] + '</a></li>' + dropDown;

				var tabs_content = '<div id="' + tab.substr(1) + '" ' +
				'class="ui-tabs-panel ui-widget-content ' +
				'ui-corner-bottom ui-tabs-hide">' +
				hiddenTabs[tab]['tab_content'] +
				'</div>';

				// append the tab if not exists
				if ( !$(tab).length ) {
					tabs.append( tabs_content );
				}
			}

			if ( dropDown.length ) {
				dropDown = '<ul id="tabs-dropdown" class="ui-widget-content">' +
				dropDown + '</ul>';
			}
		}

		dropDownShown = true;
		glfusion.admin.configuration.addTab(tabs, '#tab-dropdown', 'More...', idxAfter);
		dropDownTabIdx  = idxAfter;
	}

	function reinitDropDownTab() {
		var tabsLength = glfusion.admin.configuration.getTabLength(tabs);
		var offsetIndex = tabsLength - 1;
		if (offsetIndex === 0 ) return;

		if ( dropDownShown ) {
			glfusion.admin.configuration.removeTab(tabs, tabsLength - 1);
			dropDownShown = false;
		}

		for ( tab in hiddenTabs ) {
			glfusion.admin.configuration.addTab(tabs, tab, hiddenTabs[tab]['tab_title'], offsetIndex /*tabsLength - 1*/);
			$( tab ).html( hiddenTabs[tab]['tab_content'] );
			offsetIndex++;
		}
		hiddenTabs = {}
	}

	// initialize selected tab
	selectedTab = $("#tabs > ul > li:eq(0) a").attr('href');

	// runs overflow handler once in bootstrap
	tabsOverflowHandler();

	// get selected tab and config if passed on url
	getSelectedConf();

	// end bootstrap
	bootstrap = false;
});

var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) {
			uniqueId = "Don't call this twice without a uniqueId";
		}
		if (timers[uniqueId]) {
			clearTimeout (timers[uniqueId]);
		}
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();
