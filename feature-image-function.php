<?php
function validateImageDimensions($file_tmp) {
      list($width, $height) = getimagesize($file_tmp);
      // Size can't be more than 348*240
      $max_width = 1348;
      $max_height = 1240;
      if($width > $max_width || $height > $max_height) {
          return false;
      }
      return true;
  }