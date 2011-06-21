<script type="text/javascript" src="<?php echo SC::jsPath("jquery-1.4.2.min"); ?>"></script>
<script type="text/javascript" src="<?php echo SC::jsPath("jquery.form"); ?>"></script>
<script type="text/javascript" src="<?php echo SC::jsPath("json2"); ?>"></script>
<script type="text/javascript" src="<?php echo SC::jsPath("sc"); ?>"></script>
<script type="text/javascript" src="<?php echo SC::jsPath("sc.board"); ?>"></script>
<script type="text/javascript" src="<?php echo SC::jsPath("sc.api"); ?>"></script>

<script type="text/javascript">

  SC.data.current_user = <?php echo $current_user ? $current_user->jsonify() : "null"; ?>;

</script>
