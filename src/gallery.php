<?php
require_once 'users/init.php';
require_once US_DOC_ROOT.US_URL_ROOT.'includes/header.php';
require_once US_DOC_ROOT.US_URL_ROOT.'includes/navigation.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>

<div class="jumbotron">
	<h1>UserSpice 5 Secure Page</h1>
	<p class="text-center">
		<a class="btn btn-primary" href="https://brianracey.ca" role="button">Image Source »</a>
	</p>		
</div>
<?php
$images=glob('images/carousel/*.jpg');
?>

<div id="myCarousel" class="carousel slide" data-ride="carousel">
	<!-- Indicators -->
	<ol class="carousel-indicators">
	<?php
	$i=0;
	foreach ($images as $image){
	?>
		<li data-target="#myCarousel" data-slide-to="<?php echo $i ?>" <?php if($i==0) echo 'class="active"'?>></li>
	<?php
	$i++;}
	?>
	</ol>

	<!-- Wrapper for slides -->
	<div class="carousel-inner" role="listbox">
		<?php
		$i=0;
		foreach ($images as $image){
		?>
		<div class="item <?php if($i==0) echo 'active'?>">
			<img class="img-thumbnail img-responsive center-block" style="max-width: 100%; max-height: 600px" src="<?=$image?>">
		</div>
		<?php
		$i++;}
		?>
	</div>

	<!-- Left and right controls -->
	<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
</div>		

<?php
require_once US_DOC_ROOT.US_URL_ROOT.'includes/footer.php';
?>