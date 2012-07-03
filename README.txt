INTRODUCTION
------------

Create separate pages for different view modes for a given content type.

On the manage display page for a given content type, you can assign patterns
for view modes so that they will become a page at the given path. For
instance, if you want to show the teaser at a specific URL, you could do
node/%/teaser. If you also use Display Suite or other modules that allow you
to create new view modes, you can use these modes to display one or more fields
from a node at a separate URL.

Restrictions: currently, the node path must be contained in the URL. This means
view mode pages must follow the form [node path/node alias]/[view mode url].
These pages are added via hook_menu. Multiple wildcard characters can be used.

HOW TO ADD A VIEW MODE PAGE
---------------------------

1. Create a content type
2. Configure a view mode. If you are using Display Suite, or another means of adding view modes, you may need to add a new one.
3. Go to the "manage display" tab for your content type.
4. In the "View mode pages" tab, enter your URL pattern for the view mode page.
5. Save the content type. You may also need to clear the Drupal cache, but the page should be available!