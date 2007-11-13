<?php
//
// Optional course format configuration file
//
// This file contains any specific configuration settings for the 
// format.
//
// The default blocks layout for this course format (contains blocks IDs as given in a table mdl_block)
//     - there must be one colon (divide left and right column) and boxes are divided by comma:
    global $USER;
    
    if ($USER->su_isadvanced) {
        // layout for advanced users
        $format['defaultblocks'] = 'samouk_course,samouk_users,samouk_data,activity_modules,course_list:'.
                                   'search,news_items,calendar_upcoming,recent_activity';
    } else {
        // layout for beginners
        $format['defaultblocks'] = 'samouk_course,samouk_users,samouk_data:'.
                                   'news_items,calendar_upcoming,recent_activity';
    }
//
?>