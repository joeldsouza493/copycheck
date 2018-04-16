<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   plagiarism_copycheck
 * @author    Joel D'Souza <joel.dsouza493@gmail.com>
 * @license   Apache 2.0
 */

    require_once(dirname(dirname(__FILE__)) . '/../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/plagiarismlib.php');
    require_once($CFG->dirroot.'/plagiarism/new/lib.php');
    require_once($CFG->dirroot.'/plagiarism/new/plagiarism_form.php');

    require_login();
    admin_externalpage_setup('plagiarismcopycheck');

    $context = get_context_instance(CONTEXT_SYSTEM);

    require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

    require_once('plagiarism_form.php');
    $mform = new plagiarism_setup_form();
    $plagiarismplugin = new plagiarism_plugin_copycheck();

    if ($mform->is_cancelled()) {
        redirect('');
    }

    echo $OUTPUT->header();

    if (($data = $mform->get_data()) && confirm_sesskey()) {
        if (!isset($data->copycheck_use)) {
            $data->copycheck_use = 0;
        }
        foreach ($data as $field=>$value) {
            if (strpos($field, 'copycheck')===0) {
                if ($tiiconfigfield = $DB->get_record('config_plugins', array('name'=>$field, 'plugin'=>'plagiarism'))) {
                    $tiiconfigfield->value = $value;
                    if (! $DB->update_record('config_plugins', $tiiconfigfield)) {
                        error("errorupdating");
                    }
                } else {
                    $tiiconfigfield = new stdClass();
                    $tiiconfigfield->value = $value;
                    $tiiconfigfield->plugin = 'plagiarism';
                    $tiiconfigfield->name = $field;
                    if (! $DB->insert_record('config_plugins', $tiiconfigfield)) {
                        error("errorinserting");
                    }
                }
            }
        }
        notify(get_string('savedconfigsuccess', 'plagiarism_copycheck'), 'notifysuccess');
    }
    $plagiarismsettings = (array)get_config('plagiarism');
    $mform->set_data($plagiarismsettings);

    echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
    $mform->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
