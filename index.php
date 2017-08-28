<?php

ini_set('max_execution_time', 300);
//error_reporting( E_ERROR ); 

/* ------------------------- */
/* Change $host, $dbname, $username, $password  */
/* ------------------------- */
$host = 'localhost';
$dbname = '0-testwordpress';
$username = 'root';
$password = '';
$table = 'wp_posts';
/* ------------------------- */

try {
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $username, $password);
$sql = "SELECT * FROM $table WHERE `post_status`='publish'";
$result = $pdo->query($sql);
$result->setFetchMode(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

/* -------------------------------------- CODE FOR: BUTTONS: [REMOVE] ---------------------------------- */

if(isset($_GET['id'])) {
	$id = $_GET['id'];
	$href = $_GET['href'];
	$name =  $_GET['name'];
	$attr = $_GET['attr'];

	header('Location: index.php');

	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $username, $password);
	$query = "UPDATE $table SET post_content = REPLACE(post_content, '<a href=\"".$href."\"".$attr.">".$name."</a>', '".$name."') WHERE id = '".$id."'";
	//REPLACE(post_content, '<a href=\"".$link_hrefs[$h]."\">".$link_names[$h]."</a>', '".$link_names[$h]."')
	
	$result = $pdo->query($query);
}

/* ----------------------------- CODE FOR: BUTTON [REMOVE ALL BROKEN LINKS] ------------------------ */
if(isset($_GET['remove_all'])) {
while ($row = $result->fetch()):

	//if(preg_match_all('/<a\s+href=["\']([^"\']+)["\'](.*)>([^"\']+)<\/a>/', $row['post_content'], $links, PREG_PATTERN_ORDER));
	if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']([\sA-Za-z0-9="_()]*)>([^"\']+)<\/a>/', $row['post_content'], $links, PREG_PATTERN_ORDER));
	$link_hrefs = array_unique($links[1]);
	$link_names = array_unique($links[3]);
	$link_attributes = array_unique($links[2]);

	if(isset($link_hrefs) && isset($link_names)) {

			for($h=0; $h < count($link_hrefs); $h++) {
				
				$check_url_status = check_url($link_hrefs[$h]);
				if($link_hrefs[$h] != '#'){
					if ($check_url_status == '404' || $check_url_status == '0') {

						$current_id = $row['ID'];

						if(!isset($link_attributes[$h]))
							$link_attributes[$h] = "";

						$pdoo = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $username, $password);
						$queryy = "UPDATE $table SET post_content = REPLACE(post_content, '<a href=\"".$link_hrefs[$h]."\"".$link_attributes[$h].">".$link_names[$h]."</a>', '".$link_names[$h]."') WHERE id = '".$row['ID']."'";
						
						$resultt = $pdoo->query($queryy);
						?>
							<script type="text/javascript">
								window.onload = function() {
									$("#success-msg").css("display", "block");
									$("#success-msg").delay(4000).fadeOut();
								}

					/* -------- REDIRECT TO HOMEPAGE -------- */
								var i = 110;
								function time(){
									i--;
									if (i < 0) 
										location.href = "index.php";
								}

								time();
								setInterval(time, 1000);

							</script>
						<?php
					}
				}
			}

	}

endwhile;
}

/* -------------------------------------------------------------------------------------------------------------------- */
function check_url($href) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $href);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	$headers = curl_getinfo($ch);
	curl_close($ch);

	return $headers['http_code'];
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Remove 404 links</title>
	
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<script type="text/javascript">
		$(window).on('load', function () {
	    	var $preloader = $('#page-preloader'),
	        $spinner   = $preloader.find('.spinner');    
		
		    $spinner.fadeOut();
		    $preloader.delay(350).fadeOut('slow');
		});
	</script>
</head>
<body>

<div id="page-preloader">
	<h1 style="padding:5% 0 0 27%;font-size:50px;font-weight:700;position:absolute;">PLEASE WAIT<br>SEARCHING BROKEN LINKS ...</h1>
	<span class="spinner"></span>
</div>

<?php
	$num = 50;
	for ($i=1;$i<=$num;$i++) {
		echo("<script>document.getElementById('page-preloader').value='".$i." из ".$num."';</script>");
	}
?>

	<div class="container">
		<a href="index.php"><h1>List of 404 links</h1></a>
		<hr>
		<p class="alert alert-info"><strong>Script Information!</strong> Script will remove all links where status code will equal 400 or 0. <br>
		<b>Example links before script operation:</b> &lt;a href="https://google.com/image-40123"&gt;Google&lt;/a&gt;<br>
		<b>After script operation: </b>Google<br></p>
		<p id="success-msg" class="alert alert-success" style="display: none;">Success! <br>All links with bad response(404 or 0) was removed!<br></p>

		<a href="?remove_all" class="btn btn-primary" style="width: 100%">Remove all broken links</a>
		<hr>

            <table width=200 class="table table-bordered table-striped">

                <thead>
                    <tr>
                    	<th>ID</th>
                        <th>POST TITLE</th>
                        <th>LINK NAME</th>
                        <th>ATTRIBUTES</th>
                        <th>ERROR LINK</th>
                        <th>HTTP CODE</th>
                        <th>POST ADDRESS</th>
                        <th>DELETE LINK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch()):

						if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']([\sA-Za-z0-9="_()]*)>([^"\']+)<\/a>/', $row['post_content'], $links, PREG_PATTERN_ORDER));

						$link_hrefs = array_unique($links[1]);
						$link_names = array_unique($links[3]);
						$link_attributes = array_unique($links[2]);

						if(isset($link_hrefs) && isset($link_names)) {

						for($h=0; $h < count($link_hrefs); $h++) {
						
						$check_url_status = check_url($link_hrefs[$h]);
						if($link_hrefs[$h] != '#'){
							if ($check_url_status == '404' || $check_url_status == '0') {
						
							$current_id = $row['ID'];
						
							if(!isset($link_attributes[$h]))
							$link_attributes[$h] = "";

							$link_attributes[$h] = htmlspecialchars($link_attributes[$h]);

							?><tr>
						        <td><?php echo htmlspecialchars($row['ID']) ?></td>
						        <td style="word-wrap: break-word;"><?php echo htmlspecialchars($row['post_title']); ?></td>
						        <td style="word-wrap: break-word;"><?php echo htmlspecialchars($link_names[$h]) ?></td>
						        <td><?php echo $link_attributes[$h]; ?></td>
						        <td style="word-wrap: break-word;"><?php echo htmlspecialchars($link_hrefs[$h]) ?></td>
						        <td style='text-align: center;'><?php echo '<span style=color:red>'.$check_url_status.'</span>' ?></td>
						        <td style="word-wrap: break-word;"><?php echo htmlspecialchars($row['guid']); ?></td>
						        <td style='text-align: center;'><a class="btn btn-danger msg-one" href="?id=<?php echo $current_id ?>&href=<?php echo $link_hrefs[$h] ?>&name=<?php echo $link_names[$h] ?>&attr=<?php echo $link_attributes[$h] ?>">remove</a></td>
					        </tr> 

						<?php
					}
				}
			}

	}
						endwhile; ?>
                </tbody>
            </table>
	</div>

</body>
</html>
