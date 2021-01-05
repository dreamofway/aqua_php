
  <div class="container">
    <ul class="location">
  		<li><a href="/" class="location_list">홈 </a></li>
  		<li><a href="#" class="location_list">교회 소식</a></li>
  		<li><a href="#" class="location_list">금주의 말씀</a></li>
  	</ul>
    <div class="content">
      <div class="content_head">
  			<h2 class="page_title">금주의 말씀</h2>
        <p class="page_info">
            하나님의 사랑과 구원의 소식을 전파하는
          강남성서침례교회<br />
          GangNam Bible Baptist Church !
  			</p>
  		</div>
      <div class="content_box no_spot">
        <?=$loc->draw(); ?>
        <div class="content_box_right">
          <div class="txt_wrap">
            <ul class="sermon_desc">
              <li>
                <span class="highlight"><?=$title?></span>
              </li>
              <li>[작성자]<span><?=$reg_name?></span></li>
              <li>[날짜]<span><?=$reg_date?></span></li>
              <li>[조회]<span><?=$view_cnt?></span></li>
            </ul>
            <div class="txt_script_wrap">
              <p class="txt_script">
                <?=htmlspecialchars_decode($contents)?>
              </p>
            </div>
          </div>

          <div class="comment_box">
            <div class="cmt_head">

              <!-- TODO 01-->
              <!--
              로그아웃 시:
              -->
              <!-- <h3 class="cmt_heading">의견 쓰기</h3>
              <div>
                댓글을 작성하려면 로그인해주세요
                알랏으로 로그인 후 이용해 주시기 바랍니다 (확인)(취소) 확인 시 로그인ㄴ 화면으로
              </div> -->

              <!-- TODO 02-->
              <!--
              로그아웃 시:
              -->
              <h3 class="cmt_heading">의견 쓰기 <span class="cmt_cnt">1,000</span></h3>
              <div class="cmt_write">
                <span class="cmt_user">홍길동</span>
                <textarea id="cmtTextarea" class="cmt_textarea float_none" rows="3" cols="30"></textarea>
                <label for="cmtTextarea" class="cmt_textarea_guide" data-action="">
                주제와 무관한 댓글이나 악플은 경고 조치 없이 삭제될 수 있습니다
                </label>
                <button class="btn float_none">등록</button>
                <span class="limit_count_cnt">0/500</span>
              </div>
              <div class="cmt_list">
                <ul>
                  <li>
                    <span class="cmt_user">뱁티스트</span>
                    <p class="cmt_content">
                      하나님이 세상을 이처럼 사랑하사 독생자를 주셨으니 누구든지 예수믿으면 멸망치 않고 영생을 이룬다 요한복음 하나님이 세상을 이처럼 사랑하사 독생자를 주셨으니 누구든지 예수믿으면 멸망치 않고 영생을 이룬다 요한복음 하나님이 세상을 이처럼 사랑하사 독생자를 주셨으니 누구든지 예수믿으면 멸망치 않고 영생을 이룬다 요한복음 하나님이 세상을 이처럼 사랑하사 독생자를 주셨으니 누구든지 예수믿으면 멸망치 않고 영생을 이룬다 요한복음
                    </p>
                    <span class="date">2019-09-14 24:00</span><span class="date_ago float_none">1시간 전</span>
                  </li>
                  <li>
                    <span class="cmt_user">뱁티스트</span>
                    <p class="cmt_content">
                      하나님이 세상을 이처럼 사랑하사
                    </p>
                    <span class="date">2019-09-14 24:00</span><span class="date_ago float_none">1시간 전</span>
                  </li>
                  <li>
                    <span class="cmt_user">뱁티스트</span>
                    <p class="cmt_content">
                      하나님이 세상을 이처럼 사랑하사
                    </p>
                    <span class="date">2019-09-14 24:00</span><span class="date_ago float_none">1시간 전</span>
                  </li>
                  <li>
                    <span class="cmt_user">뱁티스트</span>
                    <p class="cmt_content">
                      하나님이 세상을 이처럼 사랑하사
                    </p>
                    <span class="date">2019-09-14 24:00</span><span class="date_ago float_none">1시간 전</span>
                  </li>
                </ul>

              </div>
              <div class="cmt_bottom">
                <a href="#none" class="btn float_none float_left">글쓰기</a>
                <a href="./week.php?page=<?=$page.$params?>" class="btn float_none float_right">목록</a>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>

  </div>
