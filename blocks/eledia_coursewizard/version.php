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
 * @author Matthias Schwabe <support@eledia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package eledia_coursewizard
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2014033100;                   // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2013111800;                   // Requires this Moodle version. (2.6)
$plugin->release   = '0.1 (2014033100)';
$plugin->maturity  = MATURITY_STABLE;
$plugin->component = 'block_eledia_coursewizard';  // Full name of the plugin (used for diagnostics).
