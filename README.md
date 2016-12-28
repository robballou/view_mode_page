# View mode page

## Introduction
This modules creates additional paths for a given entity type 
(+bundle(s) +language(s)) and choose which view mode should be used for 
rendering.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/view_mode_page
   
## Requirements
 * Drupal/System >= 8.1.0 (see https://www.drupal.org/node/2704821)
 * Drupal/Path
 * Ctools (https://www.drupal.org/project/ctools)
 * Token (https://www.drupal.org/project/token)

## Installation
 * Install as you would normally install a contributed Drupal module. See:
   https://www.drupal.org/node/1897420 for further information.

## Configuration
On the configuration page you can add as many path patterns as you desire, path 
patterns are based on the regular entity url's/aliases.

For instance, if you want to show the teaser at a specific URL. Go to your 
admin -> "Configuration" -> "Search and metadata" -> "View mode page". 
Here you can add a path pattern like /%/summary and select the view_mode teaser.
- If you have a content page which regular entity url/alias is "/my/great/page".
- You can now visit "/my/great/page/summary" which will render the teaser of
  that page.

## Maintainers
Current maintainers:
 * Davy Rolink (davy-r) - https://www.drupal.org/u/davy-r
 * Rob Ballou (rballou) - https://www.drupal.org/u/rballou
