# wp-professionals

This is a WordPress plugin which turns posts into professional listings, wrapping the content with Twitter links as well as categories for topical specialties and hierarchical locations. It just reads custom post meta fields and applies them through a filter. You can see it in action at [Writers of Color](http://www.writersofcolor.org).

## Installation ##

Upload the contents of this repository to your WordPress plugins directory. Activate it from the Plugins page.

## Content ##

The plugin creates a new Custom Post Type called Professionals, so the professional listings will not interfere with your regular post stream. Depending on your theme strategy and your intended use of the regular WordPress posts, you may need to alter your loop queries in order to include the listings.

## Metadata ##

Each professional listing is populated by the usual WordPress fields like title (usually a name?) and description, and others may be displayed in your theme. The Professionals custom post type runs a filter before returning the results of the_content() which also appends additional post metadata pulled from custom fields, most notably email and twitter (the field name for both is lowercase, and the string value for the latter should not include the initial @ symbol).

There is currently no administration user interface for changing these fields. If this is posing a problem for you, please start complaining and I'll see what I can do.

## Taxonomies ##

Since this was originally built for listing writers, the taxonomies are currently hard-coded to "Topics" and "Locations," but this is easy enough to change by editing the plugin code (reach out if you need help) and I'll try to implement a user interface for it eventually.

Locations are hierarchical (e.g. with a city inside a state) and there's no limit to how deep you can go (e.g. you can add a neighborhood within the city). Topics may be flat or hierarchical.

Both these taxonomies are simply subcategories within the default WordPress category system, and then the specific topics and locations are further nested within those top-level categories. This system may eventually be changed to use a custom taxonomy at some point in the future, but for now using the regular categories provides maximum flexibility and portability across different themes.

## Widget ##

The plugin also creates an optional widget which effectively clones the built-in WordPress category browser, but restricts its contents to the Locations and Topics groups. When the plugin is enabled, this widget will be available on the widget administration page.
