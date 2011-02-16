<?php 

// This file keeps track of upgrades to 
// the search block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_gmail_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if ($result && $oldversion < 2009072000) {

        // TODO 'we dont' use the gmail table in the future we might and then we'll need to update it 

        $table = new XMLDBTable('gmail_oauth_consumer_token'); 

        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,XMLDB_NOTNULL,XMLDB_SEQUENCE);
            $table->addFieldInfo('user_id', XMLDB_TYPE_CHAR, '11', null, XMLDB_NOTNULL);
            $table->addFieldInfo('token', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL); 
            $table->addFieldInfo('token_secret', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL); 
            $table->addFieldInfo('token_type', XMLDB_TYPE_CHAR, '64', null); 
            $table->addFieldInfo('timestamp',XMLDB_TYPE_INTEGER, '12',XMLDB_UNSIGNED,XMLDB_NOTNULL);
            
            // Add Key Field
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            
            $result = $result && create_table($table);
        }
    }


    if ($result && $oldversion < 2010041304) {
        $table = new XMLDBTable('gmail_oauth_consumer_token');
        $result = $result && rename_table($table, 'block_gmail_oauth_consumer_token');
    }

    if ($result && $oldversion < 2010041305) {
        $table = new XMLDBTable('gmail');
        $result = $result && rename_table($table, 'block_gmail');
    }

    return $result;
}

?>
