CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module contains a field type for nodes to update the moderation state of
some content types.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/scheduled_publish

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/scheduled_publish


REQUIREMENTS
------------

This module requires following module outside of Drupal core:

 * Content moderation - https://www.drupal.org/project/content_moderation


INSTALLATION
------------

 * Install the Scheduled Publish module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Configuration > Workflows > Workflow and
       enable a workflow for the content type.
    3. Navigate to Administration > Structure > Content types >
       [Content type to edit] and add a field of the type "Scheduled publish" to
       the node bundle.
    4. There will now be a "Scheduled Moderation" field set.

Notice: You should do the drupal cron every few minutes to guarantee that the
updates of the moderation state will be done at the correct time.


MAINTAINERS
-----------

 * Sascha Hannes (SaschaHannes) - https://www.drupal.org/u/saschahannes
 * Peter Majmesku - https://www.drupal.org/u/peter-majmesku
 * Sergei Semipiadniy (sergei_semipiadniy) -
   https://www.drupal.org/u/sergei_semipiadniy

Supporting organizations:

 * publicplan GmbH - https://www.drupal.org/publicplan-gmbh
