<?php


function flag_post(&$a) {

	$id = $_POST['id'];

	$reason = $_POST['reason'];
	$type = 0;
	if($reason === 'censor')
		$type = 1;
	elseif($reason === 'dead')
		$type = 2;

	if((! $id) || (! $type )) {
//		goaway($a->get_baseurl());
		print_r($_POST);
		return;
	}

	$r = q("SELECT * FROM `flag` WHERE `pid` = %d LIMIT 1",
		intval($id)
	);
	if(! count($r)) {
		$r = q("INSERT INTO `flag` ( `pid`, `reason`, `total` ) VALUES ( %d , %d, 1 ) ",
			intval($id),
			intval($type)
		);

		$msg = "An entry ($id) has just been flagged for $reason.";

		mail('info@friendika.com',"Directory Flag action",$msg);

	}
	else {
		q("UPDATE `flag` SET `total` = %d WHERE `id` = %d LIMIT 1",
			intval($r[0]['total']) + 1,
			intval($r[0]['id'])
		);
	}

	notice("Entry has been flagged.");

	goaway($a->get_baseurl());

}


function flag_content(&$a) {

	if($a->argc > 1)
		$id = intval($a->argv[1]);
	if(! $id) {
		goaway($a->get_baseurl());
	}


	$o = '<h3>Flag Directory Listing</h3>';

$o .= <<< EOT
<p>
You may flag profile listings for one of two reasons: inappropriate (adult) content, or if the link destination and therefore the profile entry is no longer valid. If you selected this form by mistake, please use your browser "Back" button to return to the Friendika directory.
</p>
<p>
Your request will be verified and if it is deemed to be valid, the entry will be flagged/removed. Please allow 24-36 hours for this action to take place.
</p>

<form action="flag" method="post" ><br /><br />

<input type="hidden" name="id" value="$id" >

<p>
Reason for flagging profile:
</p>

<input type="radio" name="reason" value="censor" >Adult content<br /><br />
<input type="radio" name="reason" value="dead" >Dead link<br /><br />

<input type="submit" name="submit" value="Submit" ><br />
</form>

EOT;

return $o;

}