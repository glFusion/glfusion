/**
 * Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

// Theme specific styles to load into the styles combo.
CKEDITOR.stylesSet.add( 'nouveau', [
	{name: 'Paragraph',element: 'p' },
	{name: 'div clear:both',element: 'div',styles: {clear: 'both'}},
	{name: 'Alert',element: 'span',attributes: {'class':'alert'}},
	{name: 'Info',element: 'span',attributes: {'class':'info'}},
	{name: 'Download',element: 'span',attributes: {'class':'down'}},
	{name: 'Help',element: 'span',attributes: {'class':'help'}},
	{name: 'Idea',element: 'span',attributes: {'class':'idea'}},
	{name: 'Styled image (left)',element: 'img',attributes: { 'class': 'left' }},
	{name: 'Styled image (right)',element: 'img',attributes: { 'class': 'right' }},
    {name: 'Blue Bulleted List',element: 'ul',attributes: {'class' : 'bullet-blue'}},
    {name: 'Grey Bulleted List',element: 'ul',attributes: {'class' : 'bullet-grey'}},
    {name: 'Plus Bulleted List',element: 'ul',attributes: {'class' : 'bullet-plus'}},
    {name: 'RSS Bulleted List',element: 'ul',attributes: {'class' : 'bullet-rss'}},
    {name: 'Star Bulleted List',element: 'ul',attributes: {'class' : 'bullet-star'}},
	{name: 'Arrow Bulleted List',element: 'ul',attributes: {'class' : 'arrow'}},
	{name: 'Bug Bulleted List',element: 'ul',attributes: {'class' : 'bug'}},
	{name: 'Cart Bulleted List',element: 'ul',attributes: {'class' : 'cart'}},
	{name: 'Check Bulleted List',element: 'ul',attributes: {'class' : 'check'}},
	{name: 'Script Bulleted List',element: 'ul',attributes: {'class' : 'script'}},
    {name: 'Disc Bulleted List',element: 'ul',attributes: {'class' : 'disc'}},
    {name: 'Headphones Bulleted List',element: 'ul',attributes: {'class' : 'headphones'}},
    {name: 'Microphone Bulleted List',element: 'ul',attributes: {'class' : 'mic'}},
    {name: 'Speaker Bulleted List',element: 'ul',attributes: {'class' : 'speaker'}},
    {name: 'Video Bulleted List',element: 'ul',attributes: {'class' : 'video'}},
	{name: 'Borderless Table',element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } }
] );

