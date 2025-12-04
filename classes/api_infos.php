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
 * Class to manage api with "infos" command
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

use \DateTime;
use \stdClass;

/**
 * Class to manage API for entry point : infos.php
 *
 * This entry point is to get generic informations about the API
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_infos extends api {
        
    protected function do() {
        // Process the precise action : prepare the informations
        // Return a std object with code and other properties
        $this->answer->version = $this->version();
        $this->answer->previousVerification = (get_config("local_ezxlate", "previous") == 1 ? 1 : 0);
        $this->answer->fieldsExtension = (dbinfos::can_extend() ? 1 : 0);
        $this->answer->gradebook = (get_config("local_ezxlate", "gradebook") == 1 ? 1 : 0);
    }
}