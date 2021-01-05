
  <div class="container">
    <ul class="location">
  		<li><a href="/" class="location_list">홈 </a></li>
  		<li><a class="location_list">교회 소식</a></li>
  		<li><a class="location_list">금주의 말씀</a></li>
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
      <div class="content_box">
        <?=$loc->draw(); ?>
        <div class="content_box_right">


          <div class="txt_wrap">
            <ul class="sermon_desc">
              <li>
                <span class="highlight"><?=date('ymd')?> - 강남성서침례교회 금주의 말씀</span>
              </li>
              <li>
                [날짜]<span><?=date('y.m.d')?></span>
              </li>
            </ul>
            <div class="txt_script_wrap">
              <p class="txt_script">
                모든 육체는 풀과 같고 사람의 모든 영광은 풀의 꽃과 같으니라.
                풀은 마르고 그것의 꽃은 떨어지되 오직 주의 말씀은 영원토록 지속되나니
                <span class="source">베드로전서 1:24-25</span>
              </p>
            </div>
          </div>            
          <div class="table_wrap">
            <table class="wrsp_tbl">
              <caption class="blind">칼럼, 간증, 큐티</caption>
              <colgroup>
                <col style="width: 80px">
                <col style="width: auto;">
                <col style="width: 200px;">
              </colgroup>
              <thead>
                <tr>
                  <th scope="col">번호</th>
                  <th scope="col">제목</th>

                  <th scope="col">날짜</th>
                </tr>
              </thead>
              <tbody>
                <?php
                    if( $paging->total_rs > 0 ){ 
                        
                        foreach($list AS $key=>$value) {
                            
                ?>
                <tr>                
                  <td><?=$value["idx"]?></td>
                  <td>
                    <a href="./week_view?idx=<?=$value["idx"]?>&page=<?=$page.$params?>">
                        <p class="title ellipsis on"><?=$value["title"]?></p>
                    </a>
                  </td>
                  <td><?=DateType( $value["reg_date"], 8 ) ?></td>
                </tr>
                <?php
                        }
                    } else {
                ?>
                
                <tr>
                    <td colspan="3" style="text-align:center" >등록(검색)된 정보가 없습니다</td>
                </tr>
        
                <?php
                    }
                ?>	      
              </tbody>
            </table>
            <?=$paging->draw(); ?>
            <form name="search_frm" method="get" >
                <ul class="search">
                <li>
                    <select name="sch_type" >
                        <option value="title" <?=($sch_type === "title" ) ? 'selected' : '' ?> >제목</option>
                        <option value="contents" <?=($sch_type === "contents" ) ? 'selected' : '' ?> >본문</option>
                        <option value="pastor" <?=($sch_type === "pastor" ) ? 'selected' : '' ?> >설교자</option>
                    </select>
                </li>
                <li>
                    <input type="text" name="sch_keyword" required="required" label="검색어" value="<?=$sch_keyword?>" />
                    <button class="btn_search">검색</button>
                </li>
                </ul>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>

