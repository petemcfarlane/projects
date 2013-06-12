<?php 
$uid		= $_['uid'];
$project_id = $_['project_id'];
$view		= $_['view'];

// if single project selected
if ($project_id) {
	$project = OC_Projects_App::getProject($project_id, $uid);
	if (!$project) {
		print "Error: You do not have permissions to view or edit this project, please refresh this page.";
		exit;
	}
} else {
	$project = NULL;
}
?>

<header>
	<h1 id="title">
		<a class="breadcrumb" href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ); ?>">Projects</a>
		<?php if ($project) { ?><a class='breadcrumb' href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id']; ?>"><?php p( $project['name']); ?></a><?php } ?>
		<?php if ($view && $view != 'project') { ?><a class='breadcrumb' href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/" . $view; ?>"><?php p( ucfirst($view) ); ?></a><?php } ?>
	</h1>
</header>



<?php 
/*$project_id = 1;
$uid = 'Steve';
$action = 'edited';
$target_type = 'project';
$target_id = 1;
$excerpt = 'Project Name - Title';
$atime = date('Y-m-d H:i:s');
//print_r( OC_Projects_App::addAction( $project_id, $action, $target_type, $target_id, $excerpt, $uid, $atime ) ); 
*/?>


<section id="main">
	<?php switch ( $view ) {
	case 'history' : include_once('part.history.php'); break;
	case "details" : include_once("part.details.php"); break;
	case "tasks" : include_once("part.tasks.php"); break;
	case "notes" : include_once("part.notes.php"); break;
	case "files" : include_once("part.files.php"); break;
	case "meetings" : include_once("part.meetings.php"); break;
	case "contacts" : include_once("part.contacts.php"); break;
	case "emails" : include_once("part.emails.php"); break;
	case "events" : include_once("part.events.php"); break;
	case "issues" : include_once("part.issues.php"); break;
	case "project" : include_once("part.project.php"); break;
	default : include_once("part.listprojects.php"); break;
	} ?>
</section>