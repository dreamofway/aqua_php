<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="subject" content="<?=$ogp_title?>" /> 
        <meta name="keywords" content="<?=$meta_keywords?>" /> 
        <meta name="description" content="<?=$meta_description?>" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?=$ogp_title?>" />
        <meta property="og:stitle_name" content="<?=$ogp_stitle_name?>" />	 
        <meta property="og:url" content="<?=$ogp_url?>" />
        <meta property="og:image" content="<?=$ogp_image?>" />
        <meta property="og:description"  content="<?=$ogp_description?>" />
        <?php
            if( $favicon_path ){
        ?>
        <link rel="shortcut icon" type="image/x-icon"  href="<?=$favicon_path?>" />
        <?php
            }
        ?>
        <title><?=$meta_title?></title>
        
        <link rel="stylesheet" type="text/css" href="<?=$aqua_view_path;?>/public/css/index.css" />
        <link rel="stylesheet" type="text/css" href="<?=$aqua_view_path;?>/public/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="<?=$aqua_view_path;?>/public/css/font.css" />
        <link rel="stylesheet" type="text/css" href="<?=$aqua_view_path;?>/public/css/contents.css" />
        
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosanskr.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css">

        
        <script src="<?=$aqua_view_path;?>/public/js/jquery.min.js"></script>
    </head>
    <body >
            
        <div id="wrap" class="wrap">
        <!-- Top Bar Start -->
        <?php 
            if( $use_top == true ) {

                include_once( $this->getViewPhysicalPath( $top_path ) );
                
            }

            if( $use_left == true ) {
                include_once( $this->getViewPhysicalPath( $left_menu_path )  );
            }
            
        ?>
        <!-- Top Bar End -->

        <?php
            include_once( $contents_path );
        ?>

        <?php 
            if( $use_footer == true ) {

                include_once( $this->getViewPhysicalPath( $footer_path ) );
            
            }
        ?>

        </div>
        
        <script src="<?=$aqua_view_path;?>/public/js/jquery.form.js"></script>
        <script src="<?=$aqua_view_path;?>/public/js/lee.lib.js"></script>             
        <script src="<?=$aqua_view_path;?>/public/js/template/jquery.tmpl.min.js"></script>
        <script src="<?=$aqua_view_path;?>/public/js/template/jquery.tmplPlus.min.js"></script>
        <script src="<?=$aqua_view_path;?>/public/js/jquery.blockUI.js"></script>
        <script src="<?=$aqua_view_path;?>/public/js/index.js"></script>
        <script src="<?=$aqua_view_path;?>/public/js/parallax.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js"></script>    
        <script type="text/javascript">
            
        </script>
    </body>
</html>