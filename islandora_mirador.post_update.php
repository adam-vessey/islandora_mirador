<?php

use Drupal\Core\Utility\UpdateException;

/**
 * @file
 * Drupal post-update hooks.
 */

/**
 * Update onto our new "installation_type" concept.
 */
function islandora_mirador_post_update_move_to_installation_types() {
  $config = \Drupal::configFactory()->getEditable('islandora_mirador.settings');
  $old_type = $config->get('mirador_library_use_remote');
  if ($old_type == 'local') {
    $config->set('installation_type', 'libraries');
  }
  elseif ($old_type == 'remote') {
    $remote_location = $config->get('mirador_library_location');
    if (strpos($remote_location, 'jsdelivr') !== FALSE) {
      $config->set('installation_type', 'remote.cdn.jsdelivr');
    }
    else {
      // Custom URL? Could be bad...
      $config->set('installation_type', 'remote.custom');
      # XXX: Really, this installation_config stuff seems like a candidate for
      # config entities, especially if the installation type concept was made
      # into a plugin.
      $config->set('installation_config.remote.custom', [
        'js' => [
          $remote_location => [
            'type' => 'external',
            'minified' => TRUE,
          ],
        ]
      ]);
    }
  }
  else {
    throw new UpdateException("Unknown 'mirador_library_use_remote' value of {$old_type} encountered.");
  }
  $config->clear('mirador_library_use_remote');
  $config->clear('mirador_library_location');
  $config->save();
}
