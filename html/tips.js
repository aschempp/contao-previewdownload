window.addEvent('domready', function()
{
	new Tips($$('.pdftip'), {
		showDelay: 900,
		maxTitleChars: 50,
		maxOpacity: 0.9
	});
});