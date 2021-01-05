
<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <div id="sidebar-menu">
            <ul>
                <li class="h2 bg0b1320"></li>
                <li class="h3"></li>
                <li class="h2 bg3b4a61"></li>
                <li class="h15 bg2b364a"></li>
                
                <?php
                    $left_menu_arr = $menu_info['left_menu'][$top_code];

                    for( $sub_loop_cnt = 0; $sub_loop_cnt < count( $left_menu_arr ); $sub_loop_cnt++ ) {
                ?>
                <li class="has_sub bg2b364a <?=($left_code == $left_menu_arr[$sub_loop_cnt]['code']) ? 'active' : '' ?>">
                    <a href="<?=$left_menu_arr[$sub_loop_cnt]['link'];?>" class="waves-effect <?=($left_code == $left_menu_arr[$sub_loop_cnt]['code']) ? 'active' : '' ?>">
                        <span><?=$left_menu_arr[$sub_loop_cnt]['title'];?></span>                            
                    </a>
                </li>
                <?php
                    }
                ?>
              
                <li class="h15 bg2b364a"></li> 
            
                <li class="h2 bg0b1320"></li>
                <li class="h3"></li> 
            </ul>
        </div>
        <!-- Sidebar -->
        <div class="clearfix"></div>

    </div>
</div>
<!-- Left Sidebar End -->
