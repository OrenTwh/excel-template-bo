<?php echo view( 'admin/header', [ 'basic' => @$basic ] ); ?>
<?php echo view( $content, [ 'data' => @$data ] ); ?>
</html>