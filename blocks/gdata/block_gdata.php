<?php // $Id$
/**
 * Copyright (C) 2009  Moodlerooms Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 * 
 * @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
 * @author Mark Nielsen
 */


/**
 * Google Data block class definition
 *
 * Development plans:
 *
 * Right now, this block is focused
 * primarily on the Google Apps Provisioning
 * API and syncing Moodle user accounts
 * to Google.  If more integrations
 * with Google Services are added, then
 * this block will need to be re-organized
 * to be more of a plugin system that houses
 * all of the integrated Google Services.
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/
class block_gdata extends block_list {

    /**
     * Current hook being handled
     *
     * @var string
     **/
    var $hook = 'status';

    /**
     * Title and version
     *
     * @return void
     **/
    function init() {
        $this->title   = get_string('blockname', 'block_gdata');
        $this->version = 2008072901;
    }

    function has_config() {
        return true;
    }
    
    /**
     * Block content contains a list
     * of links to the various admin
     * screens of the block
     *
     * @return object
     **/
    function get_content() {
        global $CFG, $USER, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance) or !self::has_capability()) {
            return $this->content;
        }

        $title = get_string('settings', 'block_gdata');
        $this->content->items[] = "<a title=\"$title\" href=\"$CFG->wwwroot/$CFG->admin/settings.php?section=blocksettinggdata\">$title</a>";
        $this->content->icons[] = "<img src=\"$CFG->pixpath/i/settings.gif\" alt=\"$title\" />";

        $title = get_string('status', 'block_gdata');
        $this->content->items[] = "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/gdata/index.php?hook=status\">$title</a>";
        $this->content->icons[] = "<img src=\"$CFG->pixpath/i/tick_green_small.gif\" alt=\"$title\" />";

        $title = get_string('userssynced', 'block_gdata');
        $this->content->items[] = "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/gdata/index.php?hook=users\">$title</a>";
        $this->content->icons[] = "<img src=\"$CFG->pixpath/i/users.gif\" alt=\"$title\" />";

        $title = get_string('addusers', 'block_gdata');
        $this->content->items[] = "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/gdata/index.php?hook=addusers\">$title</a>";
        $this->content->icons[] = "<img src=\"$CFG->pixpath/i/users.gif\" alt=\"$title\" />";

        return $this->content;
    }

    /**
     * Only allow block to be added to
     * site and admin pages
     *
     * @return array
     **/
    function applicable_formats() {
        return array('site' => true, 'admin' => true);
    }

    /**
     * Can the user add the block to the page?
     *
     * @return boolean
     **/
    function user_can_addto(&$page) {
        return self::has_capability();
    }

    /**
     * Any cleanup necessary when the block is deleted
     *
     * @return boolean
     **/
    function before_delete() {
        return delete_records('config_plugins', 'plugin', 'blocks/gdata');
    }

    /**
     * Block cron hook
     *
     * @return boolean
     **/
    function cron() {
        // Don't update the last run
        // value for the block's cron
        return false;
    }

    /**
     * Actual cron method
     *
     * @return boolean
     **/
    function cron_alt() {
        global $CFG;

        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');

        // Make sure this is not set
        unset_config('authorization', 'blocks/gdata');

        // The following code prevents the cron method
        // from being ran multiple times when the first
        // is still being executed
        $expire  = get_config('blocks/gdata', 'cronexpire');
        $started = get_config('blocks/gdata', 'cronstarted');

        if (empty($expire) or !is_numeric($expire)) {
            // Not set properly - go to default
            $expire = HOURSECS * 24;
        } else {
            $expire = HOURSECS * $expire;
        }
        if (!empty($started)) {
            $timetocheck = time() - $expire;

            if ($started > $timetocheck) {
                mtrace('Gdata cron haulted: cron is either still running or has not yet expired.  The cron will expire at '.userdate($started + $expire));
                return true; // Still return true to prevent us from hitting this message every 5 minutes or so
            }
        }

        // Be user to use the same time...
        $now = time();

        // Set the time we started
        set_config('cronstarted', $now, 'blocks/gdata');

        try {
            $gapps = new blocks_gdata_gapps();
            $gapps->sync_moodle_to_gapps($now + $expire);
        } catch (blocks_gdata_exception $e) {
            mtrace('Synchronization haulted: '.$e->getMessage());
        } catch (Zend_Exception $e) {
            mtrace('Synchronization haulted: '.$e->getMessage());
        }

        // Zero out our start time to free up the cron
        set_config('cronstarted', 0, 'blocks/gdata');

        // Always remove
        unset_config('authorization', 'blocks/gdata');

        // Always return true
        return true;
    }

    /**
     * Called Statically
     *
     * Does the current user have
     * the capability to use this
     * block and its features?
     *
     * May change, so using this method
     *
     * @param boolean $required Require the capability (throws error if is user does not have)
     * @return boolean
     **/
    function has_capability($required = false) {
        if ($required) {
            require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
        }
        return has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
    }

/**
 * Display Methods and Hooks
 *
 **/

    /**
     * Serves up the different display and
     * process methods provided by this block.
     *
     * Looks for the param "hook" and then
     * calls hook_process then hook_display
     *
     * If hook_display returns anything, a
     * header and footer is printed around
     * the return value.  Any errors should
     * be thrown as blocks_gdata_exception
     * and will be caught by this method.
     *
     * @return void
     **/
    function view() {
        global $CFG;

        require_once($CFG->libdir.'/blocklib.php');
        require_once($CFG->dirroot.'/blocks/gdata/exception.php');

        require_login(SITEID, false);

        self::has_capability(true);

        $hook  = optional_param('hook', 'status', PARAM_ALPHAEXT);
        $block = block_instance('gdata');

        // Register the hook with the block
        $block->hook = $hook;

        // Hook methods to execute
        $process = "{$hook}_process";
        $display = "{$hook}_display";

        // At least one of the hooks has to be callable
        if (!is_callable(array($block, $process)) and !is_callable(array($block, $display))) {
            error("Unable to handle request for $hook");
        }

        try {
            // Process first if available
            if (is_callable(array($block, $process))) {
                $block->$process();
            }
            // Run display if available
            if (is_callable(array($block, $display))) {
                $return = $block->$display();

                if ($return) {
                    $block->print_header();
                    echo $return;
                    $block->print_footer();
                }
            }
        } catch (blocks_gdata_exception $e) {
            // Catch and display any errors from processing or display
            error($e->getMessage());
        }
    }

    /**
     * Prints the header - very simple right now
     *
     * @return void
     **/
    function print_header() {
        global $CFG;

        $title = get_string('blockname', 'block_gdata');

        print_header_simple($title, $title, build_navigation($title));
        print_heading_with_help($title, 'gapps', 'block_gdata');

        // Only print tabs if current hook has a tab
        if (in_array($this->hook, array('status', 'users', 'addusers'))) {
            $tabs = $row = $inactive = array();

            $row[]  = new tabobject('status', $CFG->wwwroot.'/blocks/gdata/index.php?hook=status', get_string('status', 'block_gdata'));
            $row[]  = new tabobject('users', $CFG->wwwroot.'/blocks/gdata/index.php?hook=users', get_string('userssynced', 'block_gdata'));
            $row[]  = new tabobject('addusers', $CFG->wwwroot.'/blocks/gdata/index.php?hook=addusers', get_string('addusers', 'block_gdata'));
            $tabs[] = $row;

            print_tabs($tabs, $this->hook, $inactive);
        }
    }

    /**
     * Prints the footer - very simple right now
     *
     * @return void
     **/
    function print_footer() {
        global $COURSE;

        print_footer($COURSE);
    }

    /**
     * Assists with calling functions that do no return output
     *
     * @param string $callback First param is a callback
     * @param mixed $argX Keep passing arguments to pass to the callback
     * @return string
     **/
    function buffer() {
        $arguments = func_get_args();
        $callback  = array_shift($arguments);

        ob_start();
        call_user_func_array($callback, $arguments);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Status hook - display connection
     * status to Google Apps
     *
     * @return string
     **/
    function status_display() {
        global $CFG;

        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');

        try {
            $gapps = new blocks_gdata_gapps();

            $output = notify(get_string('connectionsuccess', 'block_gdata'), 'notifysuccess', 'center', true);
        } catch (blocks_gdata_exception $e) {
            $output = notify($e->getMessage(), 'notifyproblem', 'center', true);
        }

        return $output;
    }

    /**
     * Users hook - process submits from
     * users_display()
     *
     * @return void
     **/
    function users_process() {
        global $CFG;

        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');

        if ($userids = optional_param('userids', 0, PARAM_INT) or optional_param('allusers', '', PARAM_RAW)) {
            if (!confirm_sesskey()) {
                throw new blocks_gdata_exception('confirmsesskeybad', 'error');
            }
            $gapps = new blocks_gdata_gapps(false);

            if (optional_param('allusers', '', PARAM_RAW)) {
                list($select, $from, $where) = $this->get_sql('users');

                // Bulk processing
                if ($rs = get_recordset_sql("$select $from $where")) {
                    while ($user = rs_fetch_next_record($rs)) {
                        $gapps->moodle_remove_user($user->id);
                    }
                    rs_close($rs);
                } else {
                    throw new blocks_gdata_exception('invalidparameter');
                }
            } else {
                // Handle ID submit
                foreach ($userids as $userid) {
                    $gapps->moodle_remove_user($userid);
                }
            }
            redirect($CFG->wwwroot.'/blocks/gdata/index.php?hook=users');
        }
    }

    /**
     * Users hook - display the users
     * who are currently being synced
     * to Google Apps
     *
     * @return string
     **/
    function users_display() {
        return $this->display_user_table('users');
    }

    /**
     * Addusers hook - processes the
     * submit from addusers_display()
     *
     * @return void
     **/
    function addusers_process() {
        global $CFG;

        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');

        if ($userids = optional_param('userids', 0, PARAM_INT) or optional_param('allusers', '', PARAM_RAW)) {
            if (!confirm_sesskey()) {
                throw new blocks_gdata_exception('confirmsesskeybad', 'error');
            }
            $gapps = new blocks_gdata_gapps(false);

            if (optional_param('allusers', '', PARAM_RAW)) {
                list($select, $from, $where) = $this->get_sql('addusers');

                // Bulk processing
                if ($rs = get_recordset_sql("$select $from $where")) {
                    while ($user = rs_fetch_next_record($rs)) {
                        $gapps->moodle_create_user($user);
                    }
                    rs_close($rs);
                } else {
                    throw new blocks_gdata_exception('invalidparameter');
                }
            } else {
                // Process user IDs
                foreach ($userids as $userid) {
                    if ($user = get_record('user', 'id', $userid, '', '', '', '', 'id, username, password')) {
                        $gapps->moodle_create_user($user);
                    } else {
                        throw new blocks_gdata_exception('invalidparameter');
                    }
                }
            }
            redirect($CFG->wwwroot.'/blocks/gdata/index.php?hook=addusers');
        }
    }

    /**
     * Addusers hook - displays users
     * that can be added to the sync to Google Apps
     *
     * @return string
     **/
    function addusers_display() {
        return $this->display_user_table('addusers');
    }

    /**
     * Helper method, displays a table
     * of users with checkboxes next to them.
     * Also includes a submit button to take
     * action on those users.
     *
     * @param string $hook The calling hook
     * @return string
     * @todo Not in love with this method, but it works
     **/
    function display_user_table($hook) {
        global $CFG;

        require_once($CFG->libdir.'/tablelib.php');

        $pagesize = optional_param('pagesize', 50, PARAM_INT);

        $table  = new flexible_table("blocks-gdata-$hook");
        $filter = $this->create_filter($hook, $pagesize);

        // Define columns based on hook
        switch($hook) {
            case 'users':
                $table->define_columns(array('username', 'fullname', 'email', 'lastsync', 'status'));
                $table->define_headers(array(get_string('username'), get_string('fullname'), get_string('email'), get_string('lastsync', 'block_gdata'), get_string('status')));
                break;

            case 'addusers':
                $table->define_columns(array('username', 'fullname', 'email'));
                $table->define_headers(array(get_string('username'), get_string('fullname'), get_string('email')));
                break;
        }

        $table->define_baseurl("$CFG->wwwroot/blocks/gdata/index.php?hook=$hook&amp;pagesize=$pagesize");
        $table->pageable(true);
        $table->sortable(true, 'username', SORT_DESC);
        $table->set_attribute('width', '100%');
        $table->set_attribute('class', 'flexible generaltable generalbox');
        $table->column_style('action', 'text-align', 'center');
        $table->setup();

        list($select, $from, $where) = $this->get_sql($hook, $filter);

        $total = count_records_sql("SELECT COUNT(*) $from $where");

        $table->pagesize($pagesize, $total);

        if ($users = get_records_sql("$select $from $where ORDER BY ".$table->get_sql_sort(), $table->get_page_start(), $table->get_page_size())) {
            foreach ($users as $user) {
                $username = print_checkbox("userids[]", $user->id, false, s($user->username), s($user->username), '', true);

                // Define table contents based on hook
                switch ($hook) {
                    case 'users':
                        if ($user->lastsync > 0) {
                            $lastsync = userdate($user->lastsync);
                        } else {
                            $lastsync = get_string('never');
                        }

                        $table->add_data(array($username, fullname($user), $user->email, $lastsync, get_string("status$user->status", 'block_gdata')));
                        break;

                    case 'addusers':
                        $table->add_data(array($username, fullname($user), $user->email));
                        break;
                }
            }
        }

        $output  = print_box_start('boxaligncenter boxwidthwide', '', true);
        $output .= $this->buffer(array($filter, 'display_add'));
        $output .= $this->buffer(array($filter, 'display_active'));

        if (empty($table->data)) {
            // Avoid printing the form on empty tables
            $output .= $this->buffer(array($table, 'print_html'));
        } else {
            $allstr       = get_string('selectall', 'block_gdata');
            $nonestr      = get_string('selectnone', 'block_gdata');
            $submitstr    = get_string("submitbutton$hook", 'block_gdata');
            $submitallstr = get_string("submitbuttonall$hook", 'block_gdata', $total);
            $confirmstr   = get_string("confirm$hook", 'block_gdata', $total);
            $confirmstr   = addslashes_js($confirmstr);
            $options      = array(50 => 50, 100 => 100, 250 => 250, 500 => 500, 1000 => 1000);

            $output .= "<form class=\"userform\" id=\"userformid\" action=\"$CFG->wwwroot/blocks/gdata/index.php\" method=\"post\">";
            $output .= '<input type="hidden" name="hook" value="'.$hook.'" />';
            $output .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
            $output .= $this->buffer(array($table, 'print_html'));
            $output .= "<p><a href=\"#\" title=\"$allstr\" onclick=\"select_all_in('FORM', 'userform', 'userformid'); return false;\">$allstr</a> / ";
            $output .= "<a href=\"#\" title=\"$nonestr\" onclick=\"deselect_all_in('FORM', 'userform', 'userformid'); return false;\">$nonestr</a></p>";
            $output .= "<input type=\"submit\" name=\"users\" value=\"$submitstr\" />&nbsp;&nbsp;";
            $output .= "<input type=\"submit\" name=\"allusers\" value=\"$submitallstr\" onclick=\"return confirm('$confirmstr');\" />";
            $output .= '</form><br />';
            $output .= popup_form("$CFG->wwwroot/blocks/gdata/index.php?hook=$hook&amp;pagesize=", $options, 'changepagesize',
                                  $pagesize, '', '', '', true, 'self', get_string('pagesize', 'block_gdata'));
        }
        $output .= print_box_end(true);

        return $output;
    }

    /**
     * Create the user filter
     *
     * @param string $hook The calling hook
     * @param int $pagesize Page size
     * @return user_filtering
     * @warning new user_filtering can clear $_POST
     **/
    function create_filter($hook, $pagesize = 50) {
        global $CFG;

        require_once($CFG->dirroot.'/user/filters/lib.php');

        return new user_filtering(NULL, $CFG->wwwroot.'/blocks/gdata/index.php', array('hook' => $hook, 'pagesize' => $pagesize));
    }

    /**
     * Generate SQL for querying user view
     * data
     *
     * All queries must return id, username and
     * password fields from the user table.
     *
     * @param string $hook The calling hook
     * @param user_filtering $filter User filter form
     * @return array
     **/
    function get_sql($hook, $filter = NULL) {
        global $CFG;

        $select = $from = $where = '';

        if ($filter === NULL) {
            $filter = $this->create_filter($hook);
        }

        switch($hook) {
            case 'users':
                // Get all users that are not in our sync table (block_gdata_gapps) that are not scheduled to be deleted
                $select = "SELECT u.id, u.username, u.password, u.firstname, u.lastname, u.email, g.lastsync, g.status";
                $from   = "FROM {$CFG->prefix}user u, {$CFG->prefix}block_gdata_gapps g";
                $where  = "WHERE u.id = g.userid AND g.remove = 0 AND u.deleted = 0";

                // SQL gets a little weird here because the filtersql doesn't do field aliases
                if ($filtersql = $filter->get_sql_filter()) {
                    $where .= " AND u.id IN (SELECT id FROM {$CFG->prefix}user WHERE $filtersql)";
                }
                break;

            case 'addusers':
                // Get all users that are not in our sync table (block_gdata_gapps) or
                // users that are in our sync table but are scheduled to be deleted
                $select = "SELECT id, username, password, firstname, lastname, email";
                $from   = "FROM {$CFG->prefix}user";
                $where  = "WHERE id NOT IN (SELECT userid FROM {$CFG->prefix}block_gdata_gapps WHERE remove = 0) AND deleted = 0 AND username != 'guest'";

                if ($filtersql = $filter->get_sql_filter()) {
                    $where .= " AND $filtersql";
                }
                break;
        }

        return array($select, $from, $where);
    }
}

?>