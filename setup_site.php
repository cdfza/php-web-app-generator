<?php

$page_builder = bin2hex(random_bytes(16));
$notifications = bin2hex(random_bytes(16));
$database = bin2hex(random_bytes(16));
$encryption = bin2hex(random_bytes(16));
$importer = bin2hex(random_bytes(16));
$enc1 = bin2hex(random_bytes(8));
$enc2 = bin2hex(random_bytes(8));
$enc3 = bin2hex(random_bytes(8));
$enc4 = bin2hex(random_bytes(8));

$select = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "SELECT"];
$insert = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "INSERT"];
$update = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "UPDATE, SELECT"];
$delete = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "SELECT, DELETE"];
$create = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "CREATE"];
$drop = [bin2hex(random_bytes(16)), bin2hex(random_bytes(32)), "DROP"];

$walk_t_arr = [];

array_push($walk_t_arr, $select);
array_push($walk_t_arr, $insert);
array_push($walk_t_arr, $update);
array_push($walk_t_arr, $delete);
array_push($walk_t_arr, $create);
array_push($walk_t_arr, $drop);


foreach($walk_t_arr as $r)
{
	$con = mysqli_connect('127.0.0.1','root','94040f37bc9af93dcc784427b3d05b16');
	mysqli_query($con, "CREATE USER '" . $r[0] . "'@'127.0.0.1' IDENTIFIED BY '" . $r[1] ."';");
	mysqli_query($con, "GRANT " . $r[2] . " ON *.* TO '" . $r[0] . "'@'127.0.0.1'");
}

$database_file = 
sprintf(
'<?php

	if($security["database"] == "%s")
	{

	class database
	{
		private static $db_users = array(
			"select" => array("%s", "%s", "127.0.0.1"),
			"insert" => array("%s", "%s", "127.0.0.1"),
			"update" => array("%s", "%s", "127.0.0.1"),
			"delete" => array("%s", "%s", "127.0.0.1"),
			"create" => array("%s", "%s", "127.0.0.1"),
			"drop" => array("%s", "%s", "127.0.0.1")
		);

		public function query($user, $query, $mode, $db)
		{

			if(isset($_SESSION["mduna"]))
			{
				if($_SESSION["mduna"] != "YES yes")
				{
					unset(self::$db_users["create"]);
					unset(self::$db_users["drop"]);
					unset(self::$db_users["delete"]);
				}
			}
			else
			{
					unset(self::$db_users["create"]);
					unset(self::$db_users["drop"]);
					unset(self::$db_users["delete"]);
			}

			//run connection
			$conn = new mysqli(self::$db_users[$user][2], self::$db_users[$user][0], self::$db_users[$user][1]);

			//test connection
			if ($conn->connect_error)
			{
				//connection error
				if($mode == "v")
				{
					return $conn->connect_error;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				//connection passed
				if($mode == "v")
				{
					$output = $conn->query($query);

					if($output == NULL)
					{
						return $conn->error;
					}
					else
					{
						return $output;
					}
				}
				else
				{
					if($conn->query($query) === TRUE)
					{
						return 1;
					}
					else
					{
						return 0;
					}
				}
			}

			//close connection
			$conn->close();
		}
	}
}
else
{
	die("FAILED ACCESS ATTEMPT!");
}', $database, $select[0], $select[1], $insert[0], $insert[1], $update[0], $update[1], $delete[0], $delete[1], $create[0], $create[1], $drop[0], $drop[1]);

$encryption_file = 
sprintf(
'<?php

if($security["encryption"] == "%s")
{
	class encryption
	{
		private static $global_password = "' . $enc1 . '";
		private static $global_key = "' . $enc2 . '";

		public function encrypt($data, $date = "", $random_string = "")
		{
			if($date == "" && $random_string == "")
			{
				$date = date("Y") . date("m") . date("d");
				$random_string = bin2hex(random_bytes(8));	
			}

			$iv = substr(openssl_encrypt(self::$global_key, "AES-128-CBC", substr(md5($random_string . "' . $enc4 . '"), 0, 16), OPENSSL_RAW_DATA, substr(md5($date . "' . $enc3 . '"), 0, 16)), 0, 16);
			$encrypted_data = openssl_encrypt($data, "AES-128-CBC", self::$global_password, OPENSSL_RAW_DATA, $iv);

			return [$encrypted_data, $date, $random_string];
		}

		public function decrypt($data, $date, $random_string)
		{
			$iv = substr(openssl_encrypt(self::$global_key, "AES-128-CBC", substr(md5($random_string . "' . $enc4 . '"), 0, 16), OPENSSL_RAW_DATA, substr(md5($date . "' . $enc3 . '"), 0, 16)), 0, 16);
			$encrypted_data = openssl_decrypt($data, "AES-128-CBC", self::$global_password, OPENSSL_RAW_DATA, $iv);

			return [$encrypted_data, $date, $random_string];
		}
	}
}
else
{
	die("FAILED ACCESS ATTEMPT!");
}

?>', $encryption);

$page_builder_file = 
'<?php

if($security["page_builder"] == "' . $page_builder . '")
{
	class page_builder
	{

		//Site name here
		public static $site_name = "' . $argv[1] . '";

		//function to create strating HTML
		public function basic_header_open($title = "")
		{

			//Check to see if a title has been defined
			if($title != "")
			{
				$html_title = self::$site_name . " - " . $title;
			}
			else
			{
				$html_title = self::$site_name;
			}

			//HTML Content
			$content = sprintf("<!doctype html>
								<html>
								<head>
								<title>%s</title>
								<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0,\" />
								<link href=\"/style/bootstrap/css/bootstrap.css\" rel=\"stylesheet\">
								<link href=\"/style/custom/general.css\" rel=\"stylesheet\">
								<link href=\"https://fonts.googleapis.com/css?family=Caveat+Brush|Schoolbell\" rel=\"stylesheet\"> ",
								$html_title);

			echo($content);
		}

		//Function to end HEAD html and start BODY
		public function basic_header_close()
		{
			//HTML Content
			$content = "</head><body id=\"bdy\">";

			echo($content);
		}

		//Function add JAVASCRIPT at the end of the body
		public function basic_footer_open()
		{
			//HTML Content
			$content = "
						<script 
							src=\"https://code.jquery.com/jquery-3.2.1.min.js\"
							integrity=\"sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=\"
				 			crossorigin=\"anonymous\">
				 		</script>
				 		<script src=\"/style/bootstrap/js/bootstrap.js\"></script>";

			echo($content);
		}

		//Function to end BODY
		public function basic_footer_close()
		{
			//HTML Content
			$content = "
				<footer class=\"footer\">
					<div class=\"container\">
						<span class=\"text-muted\">Place sticky footer content here.</span>
					</div>
				</footer>
			</body>";

			echo($content);
		}

		//Navbar
		public function basic_navbar()
		{
			//HTML content
			$content = sprintf("
			<nav class=\"navbar navbar-toggleable-sm navbar-inverse fixed-top bg-inverse\">

				<button class=\"navbar-toggler navbar-toggler-right\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbarNav\" aria-controls=\"navbarNav\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
			    	<span class=\"navbar-toggler-icon\"></span>
			  	</button>

	  			<a class=\"navbar-brand\" href=\"/\">%s</a>

				<div class=\"collapse navbar-collapse justify-content-end\" id=\"navbarNav\">
					<ul class=\"navbar-nav\">
						<li class=\"nav-item active\">
							<a class=\"nav-link\" href=\"#\">Home <span class=\"sr-only\">(current)</span></a>
						</li>
						<li class=\"nav-item\">
							<a class=\"nav-link\" href=\"#\">Features</a>
						</li>
						<li class=\"nav-item\">
							<a class=\"nav-link\" href=\"#\">Pricing</a>
						</li>
						<li class=\"nav-item\">
							<a class=\"nav-link disabled\" href=\"#\">Disabled</a>
						</li>
					</ul>
				</div>
			</nav>", self::$site_name);

			echo($content);
		}
	}
}
else
{
	die("FAILED ACCESS ATTEMPT!");
}

?>';

$notifications_file =
'<?php

if($security["notifications"] == "' . $notifications . '")
{
	class notification
	{
		public function create($type, $bold, $message, $refresh, $link = "", $layout = "full")
		{
			//here goes the message notifications code
			if($layout == "full")
			{
				$_SESSION["notification"] = sprintf(
					"<div class=\"container-fluid\">
						<div class=\"alert alert-%s alert-dismissible fade show\" role=\"alert\">
							<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
							<span aria-hidden=\"true\">&times;</span>
							</button>
							<strong>%s:</strong> %s
						</div>
					</div>", $type, $bold, $message
				);

				$_SESSION["notification_bool"] = true;
			}

			else if($layout == "boxed")
			{
				$_SESSION["notification"] = sprintf(
					"<div class=\"container\">
						<div class=\"alert alert-%s alert-dismissible fade show\" role=\"alert\">
							<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
							<span aria-hidden=\"true\">&times;</span>
							</button>
							<strong>%s:</strong> %s
						</div>
					</div>", $type, $bold, $message
				);

				$_SESSION["notification_bool"] = true;
			}

			if($refresh == true)
			{
				//check for reload
				echo(sprintf(
					"<script>window.location=\"%s\";</script>", $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . "/" . $link
				));
			}

		}

		public function show()
		{
			if(isset($_SESSION["notification_bool"]))
			{
				//print notification
				echo($_SESSION["notification"]);

				//clear settings
				$_SESSION["notification_bool"] = false;
				unset($_SESSION["notification"]);

			}
		}	
	}
}
else
{
	die("FAILED ACCESS ATTEMPT!");
}

?>';

$importer_file = 
sprintf(
'<?php

if($security["importer"] == "%s")
{
	function import_site_functions($arr_list)
	{
		if(in_array("page_builder", $arr_list))
		{
			$security["page_builder"] = "%s";
			include($_SERVER["DOCUMENT_ROOT"] . "/system/page_functions.php");
		}

		if(in_array("notifications", $arr_list))
		{
			$security["notifications"] = "%s";
			include($_SERVER["DOCUMENT_ROOT"] . "/system/notifications.php");
		}

		if(in_array("database", $arr_list))
		{
			$security["database"] = "%s";
			include($_SERVER["DOCUMENT_ROOT"] . "/system/database.php");
		}

		if(in_array("encryption", $arr_list))
		{
			$security["encryption"] = "%s";
			include($_SERVER["DOCUMENT_ROOT"] . "/system/encryption.php");
		}
	}
}
else
{
	die("FAILED ACCESS ATTEMPT!");
}

?>', $importer, $page_builder, $notifications, $database, $encryption);

$index = '<?php

//check for session if not start
if(session_status() !== PHP_SESSION_ACTIVE){
	session_start();
}

//get basic functions
$security["importer"] ="' . $importer . '";
include($_SERVER["DOCUMENT_ROOT"] . "/system/importer.php");
import_site_functions([\'page_builder\', \'encryption\', \'notifications\', \'database\']);

//Start building HTML
page_builder::basic_header_open(\'Homepage\');
page_builder::basic_header_close();
page_builder::basic_navbar();

notification::create("success", "Welcome!", "Hey there, welcome to this new site!", false, "");
notification::show()

?>

<div class="container-fluid">
	<div class="jumbotron">
		<h1>Hello, world!</h1>
		<p>From generated Web Application!</p>
	</div>
</div>

<?php

//build footer
page_builder::basic_footer_open();
page_builder::basic_footer_close();

?>
';

$index_fobidden = 
'
<?php

header("Location: https://' . $argv[1] . '/pages/forbidden");

?>
';

$fobidden = 
'
<h1>forbidden zone!</h1>
';

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/page_functions.php", "w") or die("Unable to open file!");
fwrite($myfile, $page_builder_file);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/importer.php", "w") or die("Unable to open file!");
fwrite($myfile, $importer_file);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/database.php", "w") or die("Unable to open file!");
fwrite($myfile, $database_file);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/notifications.php", "w") or die("Unable to open file!");
fwrite($myfile, $notifications_file);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/encryption.php", "w") or die("Unable to open file!");
fwrite($myfile, $encryption_file);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/system/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/media/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/style/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/style/custom/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/style/bootstrap/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/style/bootstrap/js/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/style/bootstrap/css/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/pages/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $index_fobidden);
fclose($myfile);

$myfile = fopen("/var/www/" . $argv[1] . "/public_html/pages/forbidden/index.php", "w") or die("Unable to open file!");
fwrite($myfile, $fobidden);
fclose($myfile);

?>
