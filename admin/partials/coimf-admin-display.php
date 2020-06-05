<?php
$vDB = Coimf_DB::getInstance();

$vActions = Coimf_Action::getAllActions();

?>

<table>
<?php
foreach ( $vActions as $vAction ) {
    echo "<tr>";
    $vParsedAction = Coimf_Action::fromAction( $vAction );
    echo ( sprintf(
            "<td>%1\$s</td><td>%2\$s</td><td>%3\$s</td>",
            $vParsedAction->id, $vParsedAction->user_id, $vParsedAction->session_id
        ));
    echo "</tr>";
}
?>
</table>

<input type="text" id="urlToCapture" />
<button id="captureButton">Capture</button>

<div id="captureOutput"></div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
