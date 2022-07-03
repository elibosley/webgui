<?PHP
/* Copyright 2005-2022, Lime Technology
 * Copyright 2012-2022, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$file = $_POST['file'];

function validpath($file) {
  global $docroot;
  return realpath(dirname("$docroot/$file")) == $docroot;
}

switch ($_POST['cmd']) {
case 'save':
  if (!validpath($file)) break;
  $source = $_POST['source'];
  $opts = $_POST['opts'] ?? 'qlj';
  if (in_array(pathinfo($source, PATHINFO_EXTENSION),['txt','conf','png'])) {
    exec("zip -$opts ".escapeshellarg("$docroot/$file")." ".escapeshellarg($source));
  } else {
    $tmp = "/var/tmp/".basename($source).".txt";
    copy($source, $tmp);
    exec("zip -$opts ".escapeshellarg("$docroot/$file")." ".escapeshellarg($tmp));
    @unlink($tmp);
  }
  echo "/$file";
  break;
case 'delete':
  if (validpath($file) && is_file("$docroot/$file")) unlink("$docroot/$file");
  break;
case 'diag':
  if (!validpath($file)) break;
  $anon = empty($_POST['anonymize']) ? '' : escapeshellarg($_POST['anonymize']);
  exec("echo $docroot/webGui/scripts/diagnostics $anon ".escapeshellarg("$docroot/$file")." | at -M now > /dev/null 2>&1");
  echo "/$file";
  break;
case 'unlink':
  $real = pathinfo("$docroot/$file");
  $backup = readlink("$docroot/$file");
  if ($backup && $real['dirname']==$docroot) exec("rm -f '$docroot/$file' '$backup'");
  break;
case 'backup':
  echo exec("$docroot/webGui/scripts/flash_backup");
  break;
}
?>
