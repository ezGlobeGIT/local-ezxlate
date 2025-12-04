# local_ezxlate — Text Extraction & Update API for Moodle

`local_ezxlate` is a Moodle local plugin that offers a secure API for extracting and updating all Moodle 
textual content — courses, sections, activities, tags, questions, and more.
Created for EzGlobe and designed to support full multilingual translation, the plugin integrates smoothly 
with external translation pipelines as well as automated content-processing systems, 
ensuring efficient and accurate localization of Moodle content.


## Features

- Extract text content from Moodle in a structured JSON format
- Update existing text fields (course names, activity titles, descriptions…)
- Handle questions and tags
- Restrict access via:
  - API key
  - IP filtering
  - Moodle capability (`local/ezxlate:use`) and capabilities of the user dedicated to API
  - Optional per-entity restrictions (courses, question types, tags…), with user capabilities
- No personal data stored (privacy: null_provider)
- API-first design, no UI for end users

## License

This plugin is distributed under the terms of the GNU General Public License,
version 3 or later.

See the [LICENSES/GPL-3.0-or-later.txt](LICENSES/GPL-3.0-or-later.txt) file for details.

## Requirements

- Moodle 4.0 or later
- PHP 7.4+
- A dedicated Moodle user account to run API actions

## Installation

1. Copy the plugin into `local/ezxlate`
2. Visit **Site administration → Notifications**
3. Configure the plugin under  
   **Site administration → Plugins → Local plugins → ezxlate**

## Configuration

The plugin requires at least:

- **Enable API**
- **API key**
- **Execution user**  (among users with `local/ezxlate:use` capability)
- Optional restrictions:
  - Explicit list of authorized courses, or restriction of sensitive courses
  - Explicit list of IP adresses allowed to use the API
  - Allowed question context level (system, course categories, course questions banks)
  - Tags management allowed or restricted
- Optional features
  - Updating gradebook items names when updating activity name
  - Extending fields maximum size in the dabase schema if needed (for texts with multilingual tags)
  - Checking the previous value before changing a text
   
A complete configuration and usage guide is provided in the plungin wiki.

## API Endpoints

- `local/ezxlate/get.php`
- `local/ezxlate/set.php`
- `local/ezxlate/infos.php`

Full API documentation is provided in the plungin wiki.

## Permissions

- `local/ezxlate:use` and update capability on managed texts

## Privacy

Implements the null provider. No personal data stored.

## Changelog

See CHANGELOG.md for details.

## Credits

Maintainers:
- Christophe Blanchot, for EzGlobe

## Copyright

© 2025 EzGlobe, France
