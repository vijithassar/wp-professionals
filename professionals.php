<?php

/*
Plugin Name: Professionals
Plugin URI: http://www.vijithassar.com
Description: Turn WordPress posts into professional listings
Version: 0.0.1
Author: Vijith Assar
Author URI: http://www.vijithassar.com
License: Apache2? LOL IDK W/E
*/

class Professionals {

  // run all functions
	public function __construct() {
    add_action( 'init', array( $this, 'create_professionals' ) );
    add_action('add_meta_boxes', array($this, 'add_profile_information') );
    add_filter( 'pre_get_posts', array( $this, 'add_professionals') );
		if ( ! is_admin() ):
			add_action( 'wp_enqueue_scripts', array($this, 'add_scripts') );
		endif;
  	add_filter( 'the_content', array($this, 'add_content') );
	}

  // sanitize strings for mechanical use
  public function string_to_key($string) {
    $key = strtolower( str_replace(' ', '_', $field) );
    return $key;
  }

  // create new custom post type
  public function create_professionals() {
    $options = [
      'labels' => [
        'name' => __( 'Professionals' ),
        'singular_name' => __( 'Professional' )
      ],
      'public' => true,
      'has_archive' => true,
      'rewrite' => [
				'slug'		 	=> 'writers',
				'with_front'	=> false
			],
      'taxonomies' => ['category'],
      'supports' => [
          'title',
          'editor',
          'excerpt',
					'custom_fields'
      ],
      'menu_icon' => 'dashicons-lightbulb'
    ];
    register_post_type( 'professionals', $options );
  }

  // render a text input field
  public function render_field($field_name, $field_value) {

    $field_key = $this->string_to_key($field_name);

    echo "<label for='$field_key'>$field_name</label> &nbsp; ";
    echo "<input type='text' name='$field_key' id='$field_key' val='$field_value'>";
  }

  // render the meta box
  public function profile_information_meta_box() {
    $fields = ['Email', 'Twitter handle'];
    echo '<form>';

    foreach ($fields as $field_name):
      echo "<div class='meta'>";

      $field_value = get_post_meta(get_the_id(), $field_name);

      foreach ($field_value as $index => $field_child):
        if (count($field_value) > 1):
          $field_name_iterator = $field_name . '_' . $index;
          $this->render_field($field_name_iterator, $field_child);
        else:
          $this->_field($field_name, $field_child);
        endif;
      endforeach;

      echo "</div>";
    endforeach;
    echo '</form>';
  }

  public function add_profile_information() {
      add_meta_box('profile-information', 'Profile Information', array( $this, 'profile_information_meta_box'), 'professionals', 'normal', 'high', null);
  }

  // add custom post type to main loop
  public function add_professionals( $query ) {
  	if ( is_home() || is_feed() || is_category() || is_search() ) {
			if ( $query->is_main_query() ):
  			$query->set( 'post_type', array( 'professionals') );
			else:
				$query->set( 'post_type', array( 'professionals', 'nav_menu_item') );
			endif;
    }
  	return $query;
  }

  // add custom javascript to page head
	public function add_scripts() {
	  wp_enqueue_script('twitterwidgets', 'http://platform.twitter.com/widgets.js', null, time(), true);
  }

	public function get_links() {
    $links = get_post_meta( get_the_id(), 'links');
    if ($links) {
      $links = $links[0];
      // add newline when there's no publication name
      $links = str_replace('] - [', "]\n - [", $links);
      // add newline when there is a publication name
      $links = str_replace(') - [', "]\n - [", $links);
      // force a space between adjacent parentheses, e.g. with publication name
      $links = str_replace(')(', ") (", $links);
      // force a space after markdown dash
      $links = str_replace('-[', "- [", $links);
      return $links;
    }
  }

  public function add_links() {
    $links = $this->get_links();
		$html = '';
		if ($links):
	    $html .= '<div class="links">';
			$html .= '<h5>Links</h5>';
	    if (function_exists(wpmarkdown_markdown_to_html)):
	      $links_html = wpmarkdown_markdown_to_html($links);
	      $html .= $links_html;
	    else:
	      $html .= $links;
	    endif;
	    $html .= '</div>';
		endif;
    return $html;
  }

  // add additional content to professional listings posts
	public function add_content($content) {
	  if( !is_page() ) {
  	  $new_content = "<div class='main'>$content</div>";
  	  $new_content .= $this->add_links();
  	  $new_content .= $this->add_subcategories('Topics');
  	  $new_content .= $this->add_subcategories('Locations');
			$new_content .= "<div class='etc'>";
      $new_content .= $this->add_twitter_link();
  	  $new_content .= $this->add_search_link();
      $new_content .= $this->add_email();
      $new_content .= "</div>";
  	  return $new_content;
	  } else {
	    return $content;
	  }
	}

	public function extract_ids($term) {
    return $term->term_id;
  }

  public function render_var($var) {
    echo '<pre>' . print_r($var, true) . '</pre>';
  }

  public function get_subcategories($parent) {
		$group_category_info = get_term_by('name', $parent, 'category');
		$group_category_id = $group_category_info->term_id;
		$group_categories = get_categories( array('child_of' => $group_category_id));
		$extract_ids = function($item) {
		  return $item->term_id;
		};
	  $group_categories_ids = array_map($extract_ids, $group_categories);

    $post_categories = get_the_category( get_the_id() );
    $post_categories_ids = array_map($extract_ids, $post_categories);

    $filtered_categories = [];
    $filtered_categories_ids = [];

    foreach($post_categories as $post_category):
      if(in_array($post_category->term_id, $group_categories_ids)):
        $hierarchical_string = get_category_parents($post_category->term_id, false);
        $hierarchical_array = explode('/', $hierarchical_string);
        foreach($hierarchical_array as $hierarchical_item):
          $hierarchical_category = get_term_by('name', $hierarchical_item, 'category');
          $already_added = in_array($hierarchical_category, $filtered_categories);
          $is_top_level = $hierarchical_category->parent === 0;
        if($hierarchical_category && !$already_added && !$is_top_level):
            array_push($filtered_categories, $hierarchical_category);
            array_push($filtered_categories_ids, $hierarchical_category->term_id);
          endif;
        endforeach;
      endif;
    endforeach;

    return $filtered_categories;

  }

	public function add_subcategories($name) {

    $subcategories = $this->get_subcategories($name);
		$html = '';

		if ($subcategories):
			$html .= '<div class="subcategories ' . $this->string_to_key($name) . '">';
	    $html .= "<h5>$name</h5>";
	    $html .= '<ul>';

	    foreach ($subcategories as $category):
  	      $category_url = get_category_link($category->term_id);
  	      $html .= "<li><a href='$category_url'>$category->name</a></li>";
	    endforeach;

	    $html .= '</ul>';
	    $html .= '</div>';
		endif;

    return $html;
	}

  // add search link
	public function add_search_link() {
	  $search_term = str_replace(' ', '+', get_the_title());
	  $search_link = '
      <div class="search">
        <a href="http://www.google.com/search?q=' . $search_term . '">search</a>
      </div>
    ';
	  return $search_link;
	}

  // retrieve twitter handle from custom post metadata
	public function get_twitter_handle() {
    $twitter_handle = get_post_meta( get_the_id(), 'twitter');
    if ($twitter_handle) {
      return $twitter_handle[0];
    }
	}

  // add twitter follow link after post content
	public function add_twitter_link() {
	  $twitter_handle = $this->get_twitter_handle();
	  if($twitter_handle) {
	    $first_character = substr($twitter_handle, 0, 1);
	    // remove first character
	    if($first_character === '@') {
	      $twitter_handle = substr($twitter_handle, 1);
	    }
	    $twitter_button = '
        <div class="twitter">
          <a class="twitter-follow-button" href="https://twitter.com/' .
	        $twitter_handle . '" data-show-follow-count="true">' .
	         'follow ' . $twitter_handle .
          '</a>
        </div>';
	    return $twitter_button;
	  }
	}

  // retrieve email address from custom post metadata
	public function get_email() {
    $email = get_post_meta( get_the_id(), 'email');
    if($email) {
      return $email[0];
    }
	}

  // add email link after post content
	public function add_email() {
	  $email = $this->get_email();
	  if($email) {
	    $email = $email;
	    $anchor = 'contact';
      $email_link = "
        <div class='contact'>
          <div class='email'>
            <a href='mailto:$email'>email</a>
          </div>
        </div>
      ";
      return $email_link;
	  }
	}

};

// instantiate and run all functions in constructor
new Professionals;

// add a widget for browsing topics
class Professionals_Filter extends WP_Widget {

	// initialize
	public function __construct() {

		parent::__construct(
					'professionals_filter', // Base ID
					__( 'Professionals Filter', 'text_domain' ), // Name
					array( 'description' => __( 'Drop down menu for browsing professional listings', 'text_domain' ), ) // Args
				);

		if ( ! is_admin() ):
			wp_enqueue_script( 'professionals', plugin_dir_url( __FILE__ ) . '/professionals.js', array('jquery'), time());
			$php_vars = [
				'wp_site_url' => get_site_url()
			];
			wp_localize_script( 'professionals', 'php', $php_vars);
		endif;

	}

	// collapse topics and locations into a single array
	public function compile_categories() {
		$arguments = func_get_args();
		$compiled = [];

		// extract only the relevant parameters with array_map
		$get_desired_parameters = function($term) {
			$result['id'] = $term->term_id;
			$result['name'] = $term->name;
			$result['count'] = $term->{'count'};
			return $result;
		};

		// loop through all input categories
		foreach($arguments as $compile):
			$results = array_map($get_desired_parameters, $compile);
			$compiled = array_merge($compiled, $results);
		endforeach;

		// sort results alphabetically
		$sort_by_key = function($a, $b) {
			$a_key = strtolower($a['name']);
			$b_key = strtolower($b['name']);
			return ($a_key > $b_key) ? 1 : 0;
		};
		uasort($compiled, $sort_by_key);

		return $compiled;

	}

	// debugger
	public function render_var($var) {
		echo '<pre>';
		echo print_r($var, true);
		echo '</pre>';
	}

	// render_widget
	public function widget($args, $instance) {

		// get locations
		$locations_info = get_term_by('name', 'Locations', 'category');
		$locations_id = $locations_info->term_id;
		$locations = get_categories( array('child_of' => $locations_id));

		// get topics
		$topics_info = get_term_by('name', 'Topics', 'category');
		$topics_id = $topics_info->term_id;
		$topics = get_categories( array('child_of' => $topics_id));

		// combine locations and topics
		$all_categories = $this->compile_categories($locations, $topics);

		// render menu
		$current_category = single_cat_title('', false);
		echo $args['before_widget'];
		echo $args['before_title'] . $args['widget_name'] . $args['after_title'];
		echo '<form>';
			echo '<div>';
				echo '<label for="professionals-filter">Filter</label>';
				echo '<select id="professionals-filter" name="professionals-filter">';
				echo '<option>Select</option>';
				foreach($all_categories as $category):
  					echo '<option ';
  					echo 'value="' . $category['id'] . '"';
  		      if ($category['name'] === $current_category):
  		        echo ' selected';
  		      endif;
  					echo '>';
  					echo strtolower($category['name']);
  					echo '</option>';
				endforeach;
				echo '</select>';
			echo '</div>';
		echo '</form>';
		echo $args['after_widget'];
	}

}

// instantiate
new Professionals_Filter;

// register
add_action( 'widgets_init', function() {
     register_widget( 'Professionals_Filter' );
});
