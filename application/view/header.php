<?php
	function xhtmlHeader($include = NULL){
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta charset="utf-8">
<script> var $HOME = '<?php echo SITEURI; ?>'; </script>
<script type="text/javascript" src="<?php echo SITEURI; ?>javascript/jquery/jquery.js"></script>
<!-- <script type="text/javascript" src="<?php echo SITEURI; ?>javascript/jquery/ui/jquery-ui.js"></script> -->
<!-- <script type="text/javascript" src="<?php echo SITEURI; ?>javascript/jquery/mobile_jquery.js"></script> -->
<script type="text/javascript" src="<?php echo SITEURI; ?>javascript/global.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo SITEURI; ?>css/reset.css" />
<link rel="stylesheet" type="text/css" href="<?php echo SITEURI; ?>css/global.css" />
<!-- <link rel="stylesheet" type="text/css" href="<?php echo SITEURI; ?>javascript/jquery/ui/themes/base/jquery-ui.css" /> -->
<!-- <link rel="stylesheet" type="text/css" href="<?php echo SITEURI; ?>javascript/jquery/mobile_jquery.css" /> -->
<?php
	if(isset($include) && is_array($include)){
		if(isset($include['title'])){
			echo "<title>{$include['title']}</title>\n";
		}else{
			echo "<!-- No page title was provided. -->\n";
		}

		if(isset($include['string']) && is_array($include['string'])){
			foreach($include['string'] as $string){
				echo $string."\n";
			}
		}

		if(isset($include['file']) && is_array($include['file'])){
			foreach($include['file'] as $inc_file){
				if(!is_array($inc_file) || !isset($inc_file['type']) || !isset($inc_file['url'])){
					echo "<!-- One or more required fields pertaining to listed files to be included were incorrect or missing. -->\n";
					continue;
				}

				if($inc_file['type'] == 'javascript'){
					echo "<script type=\"text/javascript\" src=\"{$inc_file['url']}\"></script>\n";
				}elseif($inc_file['type'] == 'css'){
					echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$inc_file['url']}\" />\n";
				}else{
					echo "<!-- A file which did not match a prespecified type was listed to be included in this page. -->\n";
				}

			}
		}else{
			echo "<!-- No additional files were listed to be included in this page. -->\n";
		}
	}
?>
</head>
<body>
<?php
	}
?>
