<header id="header" class="header">
  <nav class="section_header">
    <div class="gnb-user-wrap">
      <div class="gnb-user">
        <ul class="user-list">
          <li><a href="#">로그인</a></li>
          <li><a href="#">회원가입</a></li>
          <li><a href="#">아이디/패스워드 찾기</a></li>
        </ul>
      </div>
    </div>
    <div class="gnb-menu-wrap">

        <?php
                    
            if( gettype( $menu_info ) === 'array' ) {

                $top_menu_arr = $menu_info['top_menu'];
                $sub_menu_arr = $menu_info['sub_menu'];
                
        ?>
        
        <div class="gnb-menu">
            <a href="/" class="logo">
                <img src="<?=$img_path;?>/img_logo.png" />
            </a>
            
            <ul class="gnb-menu-list">

                <?php
                    for($top_loop_cnt = 0; $top_loop_cnt < count($top_menu_arr); $top_loop_cnt++) {
                ?>
                <li>
                    <a href="<?=$top_menu_arr[$top_loop_cnt]['link']?>"><?=$top_menu_arr[$top_loop_cnt]['title']?></a>
                </li>          
                <?php
                    }
                ?>
            </ul>
        </div>
        <div class="gnb-sub-wrap">
            <div class="gnb-sub-list">
                <div class="gnb-sub-menu">
                    <?php
                        foreach( $sub_menu_arr AS $memu_key=>$menu_data ){
                    ?>
                    <ul>
                        <?php
                            foreach( $menu_data AS $menu_info ) {
                        ?>
                        <li><a href="<?=$menu_info['link']?>"><?=$menu_info['title']?></a></li>                        
                        <?php
                            }
                        ?>
                    </ul>
                    <?php
                        }
                    ?>
                </div>

            </div>
        </div>

        <?php
            }
        ?>
    </div>

    <ul class="home_menu" style="display:none">
      <li><a href="#worship_time">예배 시간</a></li>
      <li><a href="#worship_order">예배 순서</a></li>
      <li><a href="#worship_doct">신조</a></li>
      <li><a href="#worship_support">후원하는 곳</a></li>
      <li><a href="#worship_location">오시는 길</a></li>
    </ul>
    
    <!-- <a href="#none" class="btn_menu"><img src="../img/btn_menu.png" /></a>
    <ul class="m_menu" style="display:none">
      <li><a href="#worship_time">예배 시간</a></li>
      <li><a href="#worship_order">예배 순서</a></li>
      <li><a href="#worship_doct">신조</a></li>
      <li><a href="#worship_support">후원하는 곳</a></li>
      <li><a href="#worship_location">오시는 길</a></li>
    </ul> -->
  </nav>
</header>
