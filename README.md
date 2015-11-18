Use cdnjs
===========================

Use cdnjs is a simple plugin used to rewrite links to common JavaScript and CSS files that are loaded from you server to those available on [cdnjs](https://cdnjs.com/).

For example, your site might request file located at `http://example.com/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.2.1` but this plugin will change that to `https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.2.1/jquery-migrate.min.js`.

This rewriting is done for several external open source libraries that WordPress use and that have identical copies on cdnjs. All libraries are manualy reviewed and tested.

Plugin also takes care to remove version query number for changed URLs for better caching (version is already in URL), to properly include minified or development version, and to put jQuery in noConflict mode. Files are always loaded from `https` which means you will benefit from `HTTP/2`.

There are no database calls or settings, it just works when is activated or vice versa.

There are also two filters that enable developers to add aditional libraries they might use in their plugins or themes. For example, you might include [Bootstrap](http://getbootstrap.com/) in your project and enqueue it in standard, proper way. Then, you just add filter to tell Use cdnjs to rewrite default URL to use cdnjs:

```
function md_bootstrap_use_cdnjs_style( $styles ) {
	$styles['bootstrap'] = array(
		'library'  => 'twitter-bootstrap',
		'file'     => 'css/bootstrap',
		'minified' => '.min',
	);

	return $styles;
}
add_filter( 'use_cdnjs_styles', 'md_bootstrap_use_cdnjs_style' );
```

What this means is that filter function accepts an array of items. Each item's key is name (handle) under which it was registered in WordPress (`bootstrap` in this case). Item is array that holds three settings:
 * `library` - name that cdnjs uses for that library (`twitter-bootstrap` in this case)
 * `file` - file name that cdnjs uses for that exact file (`css/bootstrap` in this case; it can be only single word)
 * `minified` - extension that cdnjs uses for minified version of file (`.min` in this case)

Please note that there are separate filters for JavaScript items and for CSS items. `use_cdnjs_styles` is for CSS, `use_cdnjs_scripts` is for JavaScript. Both are used the same way. Here is example for JavaScript:
 
```
function md_bootstrap_use_cdnjs_script( $styles ) {
	$styles['bootstrap'] = array(
		'library'  => 'twitter-bootstrap',
		'file'     => 'js/bootstrap',
		'minified' => '.min',
	);

	return $styles;
}
add_filter( 'use_cdnjs_scripts', 'md_bootstrap_use_cdnjs_script' );
```

Both filters in action can be seen if you look source code of [my blog](http://blog.milandinic.com/).

This plugin is inspired by [Use Google Libraries](https://github.com/jpenney/use-google-libraries) by [Jason Penney](http://jasonpenney.net/). It uses some techniques from it, but Use cdnjs is written from scratch and is aimed for modern WordPress versions while also applying difference from [Google Hosted Libraries](https://developers.google.com/speed/libraries/) and [cdnjs](https://cdnjs.com/).
