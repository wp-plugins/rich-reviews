<?php
/*
 * Created: 2014-07-15
 * Last Revised: 2014-07-15
 *
 * CHANGELOG:
 * 2014-07-15
 *      - Initial Class Creation
 */

class RROptions {

    var $options_name;

    var $defaults;

    /**
     *
     * @var MIXED STRING/BOOL
     */
    var $updated = FALSE;

    /**
     *
     * @var RichReviews
     */
    var $core;

    var $old_pt_slug;
    var $new_pt_slug;

    /**
     *
     * @param RichReviews $core
     */
    public function __construct($core) {
        $this->core = $core;
        if (isset($_POST['update'])) {
            $this->updated = $_POST['update'];
        }
        $this->options_name = $core->options_name;
        $this->defaults = array(
			'version' => '1.6.4',
      'star_color' => '#ffaf00',
			'snippet_stars' => FALSE,
			'reviews_order' => 'asc',
			'approve_authority' => 'manage_options',
			'require_approval' => 'checked',
			'show_form_post_title' => FALSE,
      'display_full_width' => FALSE,
			'credit_permission'=> FALSE,
      'show_date' => FALSE,
      'form-name-label' => 'Name',
      'form-name-display' => 'checked',
      'form-name-require' => 'checked',
      // 'form-reviewer-image-label' => 'Reviewer Image',
      // 'form-reviewer-image-display' => 'checked',
      // 'form-reviewer-image-require' => 'checked',
      'form-email-label' => 'Email',
      'form-email-display' => 'checked',
      'form-email-require' => FALSE,
      'form-title-label' => 'Review Title',
      'form-title-display' => 'checked',
      'form-title-require' => 'checked',
      // 'form-reviewed-image-label' => 'Review Image',
      // 'form-reviewed-image-display' => 'checked',
      // 'form-reviewed-image-require' => 'checked',
      'form-content-label' => 'Review Content',
      'form-content-display' => 'checked',
      'form-content-require' => 'checked',
      'form-submit-text' => 'Submit',
      'return-to-form' => FALSE,
      'send-email-notifications' => FALSE,
      'admin-email' => ''

          );
        if ($this->get_option() == FALSE) {
            $this->set_to_defaults();
        }
    }

    public function set_to_defaults() {
        delete_option($this->options_name);
        foreach ($this->defaults as $key=>$value) {
            $this->update_option($key, $value);
        }
    }

    public function update_options() {
        if (isset($_POST['update']) && $_POST['update'] === 'rr-update-options') {
             if (!isset($_POST['snippet_stars'])) { $_POST['snippet_stars'] = false; }
             if (!isset($_POST['show_date'])) { $_POST['show_date'] = false; }
             if (!isset($_POST['require_approval'])) { $_POST['require_approval'] = false; }
             if (!isset($_POST['show_form_post_title'])) { $_POST['show_form_post_title'] = false; }
             if (!isset($_POST['display_full_width'])) { $_POST['display_full_width'] = false; }
			       if (!isset($_POST['credit_permission'])) { $_POST['credit_permission'] = false; }
             if (!isset($_POST['form-name-label'])) { $_POST['form-name-label'] = false; }
             if (!isset($_POST['form-name-display'])) { $_POST['form-name-display'] = false; }
             if (!isset($_POST['form-name-require'])) { $_POST['form-name-require'] = false; }
             if (!isset($_POST['form-email-display'])) { $_POST['form-email-display'] = false; }
             if (!isset($_POST['form-email-require'])) { $_POST['form-email-require'] = false; }
             if (!isset($_POST['form-title-display'])) { $_POST['form-title-display'] = false; }
             if (!isset($_POST['form-title-require'])) { $_POST['form-title-require'] = false; }
             if (!isset($_POST['form-content-display'])) { $_POST['form-content-display'] = false; }
             if (!isset($_POST['form-content-require'])) { $_POST['form-content-require'] = false; }
             // if (!isset($_POST['form-reviewed-image-display'])) { $_POST['form-reviewed-image-display'] = false; }
             // if (!isset($_POST['form-reviewed-image-require'])) { $_POST['form-reviewed-image-require'] = false; }
             // if (!isset($_POST['form-reviewer-image-display'])) { $_POST['form-reviewer-image-display'] = false; }
             // if (!isset($_POST['form-reviewer-image-require'])) { $_POST['form-reviewer-image-require'] = false; }
             if (!isset($_POST['return-to-form'])) { $_POST['return-to-form'] = false; }
             if (!isset($_POST['send-email-notifications'])) { $_POST['send-email-notifications'] = false; }


            $current_settings = $this->get_option();
            $clean_current_settings = array();
            foreach ($current_settings as $k=>$val) {
                if ($k != NULL) {
                    $clean_current_settings[$k] = $val;
                }
            }

            $this->defaults = array_merge($this->defaults, $clean_current_settings);
            $update = array_merge($this->defaults, $_POST);
            $data = array();
            foreach ($update as $key=>$value) {
                if ($key != 'update' && $key != NULL) {
                    $data[$key] = $value;
                }
            }

            $this->update_option($data);
            $_POST['update'] = NULL;
            $this->updated = 'wpm-update-options';
        }
        else if (isset($_POST['update']) && ($_POST['update'] === 'rr-update-support' || $_POST['update'] === 'rr-update-support-prompt')) {
            $current_settings = $this->get_option();
            $this->defaults = array_merge($this->defaults, $current_settings);
            $update = array_merge($this->defaults, $_POST);
            $data = array();
            foreach ($update as $key=>$value) {
                if ($key != 'update' && $key != NULL) {
                    $data[$key] = $value;

                }
            }
            $this->update_option($data);
            $_POST['update'] = NULL;
            $this->updated = 'rr-update-support';
            //$this-set_to_defaults();
        }
    }



    // From metabox v1.0.6

    /**
    * Gets an option for an array'd wp_options,
    * accounting for if the wp_option itself does not exist,
    * or if the option within the option
    * (cue Inception's 'BWAAAAAAAH' here) exists.
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @return mixed (or FALSE on fail)
    */
    public function get_option($opt_name = '') {
       $options = get_option($this->options_name);

       // maybe return the whole options array?
       if ($opt_name == '') {
           return $options;
       }

       // are the options already set at all?
       if ($options == FALSE) {
           return $options;
       }

       // the options are set, let's see if the specific one exists
       if (! isset($options[$opt_name])) {
           return FALSE;
       }

       // the options are set, that specific option exists. return it
       return $options[$opt_name];
    }

    /**
    * Wrapper to update wp_options. allows for function overriding
    * (using an array instead of 'key, value') and allows for
    * multiple options to be stored in one name option array without
    * overriding previous options.
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @param  mixed $opt_val
    */
    public function update_option($opt_name, $opt_val = '') {
       // ----- allow a function override where we just use a key/val array
       if (is_array($opt_name) && $opt_val == '') {
           foreach ($opt_name as $real_opt_name => $real_opt_value) {
               $this->update_option($real_opt_name, $real_opt_value);
           }
       }
       else {
           $current_options = $this->get_option(); // get all the stored options

           // ----- make sure we at least start with blank options
           if ($current_options == FALSE) {
               $current_options = array();
           }

           // ----- now save using the wordpress function
           $new_option = array($opt_name => $opt_val);
           update_option($this->options_name, array_merge($current_options, $new_option));
       }
    }

    /**
    * Given an option that is an array, either update or add
    * a value (or data) to that option and save it
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @param  mixed $key_or_val
    * @param  mixed $value
    */
    public function append_to_option($opt_name, $key_or_val, $value = NULL, $merge_values = TRUE) {
       $key = '';
       $val = '';
       $results = $this->get_option($opt_name);

       // ----- always use at least an empty array!
       if (! $results) {
           $results = array();
       }

       // ----- allow function override, to use automatic array indexing
       if ($value === NULL) {
           $val = $key_or_val;

           // if value is not in array, then add it.
           if (! in_array($val, $results)) {
               $results[] = $val;
           }
       }
       else {
           $key = $key_or_val;
           $val = $value;

           // ----- should we append the array value to an existing array?
           if ($merge_values && isset($results[$key]) && is_array($results[$key]) && is_array($val)) {
                   $results[$key] = array_merge($results[$key], $val);
           }
           else {
                   // ----- don't care if key'd value exists. we override it anyway
                   $results[$key] = $val;
           }
       }

       // use our internal function to update the option data!
       $this->update_option($opt_name, $results);
    }

    public function update_messages() {
        if ($this->updated == 'rr-update-options') {
            echo '<div class="updated">The options have been successfully updated.</div>';
            $this->updated = FALSE;
        }
        else if ($this->updated == 'rr-update-support') {
             echo '<div class="updated">Thank you for supporting the development team! We really appreciate how awesome you are.</div>';
            $this->updated = FALSE;
        }
	}
}
