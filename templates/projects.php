<?php 
	//print_r( $_['X-PJAX'] );
	//exit;

$project = NULL;
$view = isset($_['view']) ? $_['view'] : 'project' ;
if ( isset ($_['id']) && is_numeric($_['id'])) {
	$project = OC_Projects_App::getProject($_['id']);
} else {
	$projects = OC_Projects_App::getProjects();
	$view = NULL;
}
?>
<header>
	<h1 id="title">
		<a class="breadcrumb" href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ); ?>">Projects</a>
		<?php if ($project) { ?><a class='breadcrumb' href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id']; ?>"><?php p( $project['name']); ?></a><?php } ?>
		<?php if ($view && $view != 'project') { ?><a class='breadcrumb' href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/" . $view; ?>"><?php p( ucfirst($view) ); ?></a><?php } ?>
	</h1>
</header>

<section id="main">
	<?php switch ( $view ) {
	case 'history' :
		include_once('part.history.php');
		break;
	case "details" :
		include_once("part.details.php");
		break;
	case "tasks" :
		include_once("part.tasks.php");
		break;
	case "notes" :
		include_once("part.notes.php");
		break;
	case "files" :
		include_once("part.files.php");
		break;
	case "meetings" :
		include_once("part.meetings.php");
		break;
	case "contacts" :
		include_once("part.contacts.php");
		break;
	case "emails" :
		include_once("part.emails.php");
		break;
	case "events" :
		include_once("part.events.php");
		break;
	case "issues" :
		include_once("part.issues.php");
		break;
	case "project" :
		include_once("part.project.php");
		break;
	default :
		include_once("part.listprojects.php");
		break;
	} ?>
</section>