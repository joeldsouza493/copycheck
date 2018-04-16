<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class plagiarism_setup_form extends moodleform {

/// Define the form
    function definition () {
        global $CFG;

        $mform =& $this->_form;
        $choices = array('No','Yes');
        $mform->addElement('html', get_string('copycheckexplain', 'plagiarism_copycheck'));
        $mform->addElement('checkbox', 'copycheck_use', get_string('usecopycheck', 'plagiarism_copycheck'));

        $mform->addElement('textarea', 'copycheck_student_disclosure', get_string('studentdisclosure','plagiarism_copycheck'),'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('copycheck_student_disclosure', 'studentdisclosure', 'plagiarism_copycheck');
        $mform->setDefault('copycheck_student_disclosure', get_string('studentdisclosuredefault','plagiarism_copycheck'));

        $this->add_action_buttons(true);
    }
}

