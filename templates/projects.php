<?php 
$uid		= $_['uid'];
$project_id = $_['project_id'];
$view		= $_['view'];
$item		= $_['item'];

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

$projects = OC_Projects_App::getProjects($uid);
?>

<header>
	<div class="top-menu">
		<ul id="projects_nav" class="breadcrumb">
			<li><a href="<?php p(OCP\Util::linkTo( 'projects', 'index.php' )); ?>">All Projects</a></li>
			<?php if ($project) { ?><li><span class="divider">/</span> <a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/$project[id]" ); ?>"><?php p( $project['name']); ?></a></li><?php } ?>
			<?php if ($view && $view != 'project') { ?><li><span class="divider">/</span> <a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/$project[id]/$view" ); ?>"><?php p( ucfirst($view) ); ?></a></li><?php } ?>
			<?php if ($item) { ?><li><span class="divider">/</span><a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/$project[id]/$view/$item" ); ?>"><?php p($item); ?></a></li><?php } ?>
		</ul>
	</div>
</header>

<section id="main">
	<?php switch ( $view ) {
	case 'history' : include_once('part.history.php'); break;
	case "details" : include_once("part.details.php"); break;
	case "tasks" : 
		#$item ? include_once("part.task.php") :	include_once("part.tasks.php"); 
		include_once("part.tasks.php"); break;
	case "notes" : include_once("part.notes.php"); break;
	case "files" : include_once("part.files.php"); break;
	case "meetings" : include_once("part.meetings.php"); break;
	case "contacts" : include_once("part.contacts.php"); break;
	case "emails" : include_once("part.emails.php"); break;
	case "events" : include_once("part.events.php"); break;
	case "issues" : include_once("part.issues.php"); break;
	case "project" : include_once("part.project.php"); break;
	case "people" : include_once("part.people.php"); break;
	default : include_once("part.listprojects.php"); break;
	} ?>
</section>