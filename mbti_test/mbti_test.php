<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/Common.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/FrontCommon.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/StringUtil.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/DBUtil.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/FileUtil.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/Logger.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/log/marcketing_config.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/new/sms/smsProc.php");
$counselGbn = getParamDef("counselGbn", "25701");
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <?
  require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/meta.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/css.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/script.php");
  ?>
  <!-- 모바일에서 웹화면 들어올 때 모바일 페이지로 리다이렉트 -->
  <script>
    if ($(window).width() < 780) {
      $.getScript('js/nbw-parallax.js');
    }
    if (window.innerWidth < 780) {
      //Your Code
      window.location.href = 'https://www.isoohyun.co.kr/nm/html/lovetest/mbti_test.php?counselGbn=<?= $counselGbn ?>&mctkey=<?= $mctkey ?>';
    }
  </script>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <link rel="stylesheet" type="text/css" href="/new/css/sidemenu.css">
  <script src="https://developers.kakao.com/sdk/js/kakao.js"></script>
  <script>
    Kakao.init('16b3c92425889edb797d2dc78b3d1428'); // 발급받은 키 중 javascript키를 사용해준다.
    //카카오 정보 가져오기
    function kakaoGetData() {
      Kakao.Auth.login({
        success: function(response) {
          console.log(response);
          Kakao.API.request({
            url: '/v2/user/me',
            success: function(response) {
              var user_id = "k_" + response.id; // 아이디
              var birthyear = response.kakao_account.birthyear; // 생일
              var email = response.kakao_account.email; // 이메일
              var gender = response.kakao_account.gender; // 성별
              if (gender == 'male') { // DB에 맞는 성별처리
                gender = '1';
              } else {
                gender = '2';
              }
              var phone_number = response.kakao_account.phone_number; // 핸드폰번호
              var phone_number = phone_number.replace('+82 ', '0'); // 핸드폰 앞자리 치환
              var nickname = response.properties.nickname; // 카카오톡 닉네임

              $('#user_id').val(user_id);
              $('#birthday').val(birthyear);
              $('#email').val(email);
              $('#gender').val(gender);
              $('#phone').val(phone_number);
              $('#name').val(nickname);

            },
            fail: function(error) {
              console.log(error)
            },
          })
          Kakao.API.request({
            url: '/v1/user/shipping_address',
            success: function(response) { // 우선 첫번째 등록한 주소를 불러오도록...
              var base_address = response.shipping_addresses[0].base_address;
              var detail_address = response.shipping_addresses[0].detail_address;
              var zone_number = response.shipping_addresses[0].zone_number;
              $('#area').val(base_address);
              $('input[name="area_post_number"]').val(zone_number); // 우편번호
              //$('#detail_address').val(detail_address);
              //$('#zone_number').val(zone_number);
            },
            fail: function(error) {
              console.log(error)
            },
          })
          // 카카오 정보 및 form 정보 넘기는 부분
          setTimeout(function() {
            // var hope = '희망 성별 : ' + qgender + ', 희망 나이 :' + qage + ',희망 학력 : ' + qschool + ', 희망 급여 : ' + qpay + ', 희망 직업 : ' + qjob + ', 희망 외모 : ' + qlook;
            // $("#Idealtype_age").val(qage);
            // $("#Idealtype_school").val(qschool);
            // $("#Idealtype_income").val(qpay);
            // $("#Idealtype_job").val(qjob);
            // $("#Idealtype_looks").val(qlook);
            // $('input[name=content]').val(hope);
            $('#frm').validate({
              success: function() {
                this.target = "counselResult";
                this.action = "/new/common/mbti_test_proc.php";
                this.submit();
                var name = $('input[name=name]').val();
                var email = $('input[name=email1]').val() + '@' + $('input[name=email2]').val();
                $('#resultName').html($('input[name=name]').val());
                $('#resultEmail').html($('input[name=email]').val());
                $("#resultPhone").html($('input[name=phone]').val());
              }
            });
          }, 1000);
        },
        fail: function(error) {
          console.log(error)
        },
      })
    }

    // 이상형 전역변수
    // var qgender = '';
    // var qage = '';
    // var qschool = '';
    // var qpay = '';
    // var qjob = '';
    // var qlook = '';

    // mbti 
    var mbti01 = '';
    var mbti02 = '';
    var mbti03 = '';
    var mbti04 = '';
    var mbti = '';

    //페이지 열릴 때 show(0)으로 이동
    $(document).ready(function() {
      show(0);
      // $('.isfp').show();
    });

    // show()함수
    function show(idx, cmd, txt) {
      if (idx == 2) {
        if ($('#school').val() == "") {
          alert("학력을 선택해주세요");
          return false;
        } else if ($('select[name=new_birthday]').val() == "") {
          alert('출생년도를 선택해주세요.');
          $('select[name=new_birthday]').focus();
          return;
        }
      } else if (idx == 3) {
        mbti01 = txt;
        console.log(mbti01);
      } else if (idx == 5) {
        mbti02 = txt;
        console.log(mbti02);
      } else if (idx == 6) {
        mbti03 = txt;
        console.log(mbti03);
      } else if (idx == 7) {
        mbti04 = txt;
        console.log(mbti04);
      } else if (idx == 8) {
        mbti = (mbti01 + mbti04 + mbti02 + mbti03);
        console.log(mbti);
      }
      // mbti 결과에 따라서 페이지 section 처리 함
      if (this.mbti == "ISFP") {
        $('section').hide();
        $('.isfp').show();
      } else if (this.mbti == "ISFJ") {
        $('section').hide();
        $('.isfj').show();
      } else if (this.mbti == "ISTJ") {
        $('section').hide();
        $('.istj').show();
      } else if (this.mbti == "ISTP") {
        $('section').hide();
        $('.istp').show();
      } else if (this.mbti == "INFJ") {
        $('section').hide();
        $('.istp').show();
      } else if (this.mbti == "INTJ") {
        $('section').hide();
        $('.intj').show();
      } else if (this.mbti == "INFP") {
        $('section').hide();
        $('.infp').show();
      } else if (this.mbti == "INTP") {
        $('section').hide();
        $('.intp').show();
      } else if (this.mbti == "ESFJ") {
        $('section').hide();
        $('.esfj').show();
      } else if (this.mbti == "ESTJ") {
        $('section').hide();
        $('.estj').show();
      } else if (this.mbti == "ESFP") {
        $('section').hide();
        $('.esfp').show();
      } else if (this.mbti == "ESTP") {
        $('section').hide();
        $('.estp').show();
      } else if (this.mbti == "ENFJ") {
        $('section').hide();
        $('.enfj').show();
      } else if (this.mbti == "ENTJ") {
        $('section').hide();
        $('.entj').show();
      } else if (this.mbti == "ENTP") {
        $('section').hide();
        $('.entp').show();
      } else if (this.mbti == "ENFP") {
        $('section').hide();
        $('.enfp').show();
      } else {
        $('section').hide();
        $('section:eq(' + idx + ')').show();
      }

    }

    function success() {
      $('#frm').get(0).reset();
      show(8);
    }

    // select시 색 변경
    function changecolor1() {
      $(".new_birthday").css("background-color", "#7555c4");
      $(".new_birthday").css("border", "3px solid #7555c4");
      $(".new_birthday").css("color", "white");
      console.log("change1");
    }

    function changecolor2() {
      $(".school").css("background-color", "#7555c4");
      $(".school").css("border", "3px solid #7555c4");
      $(".school").css("color", "white");
      console.log("change2");
    }
  </script>
  <!-- p text -->
  <style>
    .p_text {
      text-align: center;
      font-size: 40px;
      padding-top: 40px;
      padding-bottom: 40px;
      color: #6645b8;
    }

    .list_box {
      width: 80%;
      border: 3px solid white;
      text-align: center;
      padding: 5% 4%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.5;
      margin-bottom: 30px;
    }

    .list_box:hover {
      width: 80%;
      border: 3px solid #6645b8;
      text-align: center;
      padding: 5% 4%;
      font-size: 20px;
      color: white;
      background-color: #7555c4;
      opacity: 0.5;
      margin-bottom: 30px;
    }

    .join-charge {
      background-color: #fae4f6;
    }

    input[id="gender1"]+label {
      width: 80%;
      border: 3px solid white;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.5;
      margin-bottom: 30px;
    }

    input[id="gender1"]:checked+label {
      width: 80%;
      border: 3px solid #6645b8;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: white;
      background-color: #7555c4;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="gender2"]+label {
      width: 80%;
      border: 3px solid white;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="gender2"]:checked+label {
      width: 80%;
      border: 3px solid #6645b8;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: white;
      background-color: #7555c4;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="marry1"]+label {
      width: 80%;
      border: 3px solid white;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="marry1"]:checked+label {
      width: 80%;
      border: 3px solid #6645b8;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: white;
      background-color: #7555c4;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="marry2"]+label {
      width: 80%;
      border: 3px solid white;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    input[id="marry2"]:checked+label {
      width: 80%;
      border: 3px solid #6645b8;
      text-align: center;
      padding: 2% 4%;
      font-size: 20px;
      color: white;
      background-color: #7555c4;
      opacity: 0.7;
      margin-bottom: 30px;
    }

    .new_birthday {
      width: 100%;
      border: 3px solid white;
      text-align: center;
      padding: 0.5% 7.8%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.5;
      margin-bottom: 10px;
    }


    .school {
      width: 100%;
      border: 3px solid white;
      text-align: center;
      padding: 0.5% 7.8%;
      font-size: 20px;
      color: black;
      background-color: whitesmoke;
      opacity: 0.5;
      margin-bottom: 30px;
    }
  </style>
</head>

<body>
  <?
  require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/header2.php");
  ?>
  <?
  require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/skyscraper3.php");
  ?>
  <!-- side menu start -->
  <div id="floating_open">
    <h2 class="lnb-tit">러브테스트</h2><br>
    <ul class="main_menu_list">
      <li><a href="/new/fate/fate03.php">이상형찾기</a></li>
      <li class="on"><a href="/new/fate/fate27.php?counselGbn=26822">MBTI 이상형 TEST</a></li>
      <li><a href="/new/fate/fate08.php">결혼시기 TEST</a></li>
      <li><a href="/new/lovetest/first_face_test.php">첫인상 TEST</a></li>
      <li><a href="/new/fate/fate21.php">재혼가능성 TEST</a></li>
      <li><a href="/new/fate/fate15.php">노블레스가입비 TEST</a></li>
      <li><a href="/new/fate/fate26.php">펜트하우스 TEST</a></li>
      <li><a href="/new/fate/fate18.php">내게 맞는 커플매니저</a></li>
      <li><a href="/new/fate/fate27.php?counselGbn=26822">이상형 추천 TEST</a></li>
    </ul>
  </div>
  <!-- side menu end -->

  <div class="content">
    <div class="bannerwrap">
      <div class="wrap">
        <!-- 시작부분 show(0) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/index_bg.png'); height:600px; background-repeat: no-repeat; background-position:center;">
              <a href="javascript:show(1);"><img style="text-align:center; margin-left:550px; margin-top:430px;" src="/new/image/mbti_test/index_btn.png" alt="" /></a>
            </div>
          </div>
        </section>

        <section id="lovetest">
          <form id="frm" name="frm" method="post">
            <input type="hidden" name="counselGbn" value="<?= getParam("counselGbn", "25701") ?>" />
            <input type="hidden" name="counselGbn2" value="mbti 테스트" />
            <input type="hidden" name="marriage" value="10501" />
            <input type="hidden" id="name" name="name">
            <input type="hidden" id="gender" name="gender">
            <input type="hidden" id="birthday" name="birthday">
            <input type="hidden" id="area" name="area">
            <input type="hidden" id="area_post_number" name="area_post_number">
            <input type="hidden" id="phone" name="phone">
            <input type="hidden" id="email" name="email">
            <input type="hidden" name="content" />
            <input type="hidden" name="user_id" id="user_id" />

            <!-- show(1) -->
            <div class="join-charge">
              <div style="background-image: url('/new/image/mbti_test/p_q02_bg.png'); height:600px;">
                <div class="input-box">
                  <div>
                    <center>
                      <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                    </center>
                  </div>
                  <p class="p_text">나의 정보 입력하고 시작하기</p>
                  <div style="text-align: center; margin-top:50px;">
                    <div>
                      <input id="gender1" type="radio" name="gender" value="1" style="display: none;" /> <label for="gender1">남성</label>&nbsp;&nbsp;
                      <input id="gender2" type="radio" name="gender" value="2" style="display: none;" /><label for="gender2">여성</label>
                    </div><br><br><br><br><br>
                    <div style="padding-top: 7px;">
                      <input id="marry1" type="radio" name="marriage" value="10501" style="display: none;" /><label for="marry1"> 초혼</label>&nbsp;&nbsp;
                      <input id="marry2" type="radio" name="marriage" value="10502" style="display: none;" /> <label for="marry2">재혼</label>
                    </div><br><br><br>
                    <div>
                      <select onchange="changecolor1();" id="new_birthday" name="new_birthday" class="new_birthday">
                        <option value="">출생년도</option>
                        <? for ($i = 1950; $i < date('Y'); $i++) { ?>
                          <option value="<?= $i ?>"><?= $i; ?>년</option>
                        <? } ?>
                      </select>
                    </div>
                    <div style="margin-top: 0px;">
                      <select onchange="changecolor2();" id="school" name="school" class="school" message="학력을 선택해주세요.">
                        <option value="">학력</option>
                        <option value="대학(2, 3년제) 재학">대학(2, 3년제) 재학</option>
                        <option value="대학(2, 3년제) 졸업">대학(2, 3년제) 졸업</option>
                        <option value="대학(4년제) 재학">대학(4년제) 재학</option>
                        <option value="대학(4년제) 졸업">대학(4년제) 졸업</option>
                        <option value="대학원(석사) 재학">대학원(석사) 재학</option>
                        <option value="대학원(석사) 졸업">대학원(석사) 졸업</option>
                        <option value="대학원(박사) 재학">대학원(박사) 재학</option>
                        <option value="대학원(박사) 졸업">대학원(박사) 졸업</option>
                        <option value="고등학교 졸업">고등학교 졸업</option>
                        <option value="기타">기타</option>
                      </select>
                    </div>
                  </div>
                </div>
                <!-- 이전페이지, 다음페이지 -->
                <div style="margin-left:400px; display:block; margin-top:-300px">
                  <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(0);return false;" />
                  <img style="margin-left: 800px;" src="/new/image/mbti_test/btn_next.png" alt="" onclick="show(2);return false;" />
                </div>
                <!-- 이전페이지, 다음페이지 -->
              </div>
            </div>
          </form>
          <iframe src="" id="counselResult" name="counselResult" width="0" height="0" style="display:none;" frameborder="0"></iframe>

        </section>

        <!-- show(2) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q01_bg01.png'); height:600px;">
              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>
              <p class="p_text">연인과 함께 더 즐거운 시간은?</p><br><br><br>
              <ul class="radio-box" style="width:24%; margin:auto;">
                <li class="list_box" onclick="show(3,'next','I');">여유롭게 보내는 집 데이트</li>
                <li class="list_box" onclick="show(3,'next','E');">이곳 저곳 돌아다니며 야외 데이트</li>
              </ul>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-144px;">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(1);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
        </section>

        <!-- show(3) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q01_bg01.png'); height:600px;">
              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>

              <p class="p_text">좋아하는 영화 장르는?</p><br><br><br>
              <ul class="radio-box" style="width:24%; margin:auto;">
                <li class="list_box" onclick="show(4,'next','로맨스');">로맨스</li>
                <li class="list_box" onclick="show(4,'next','액션');">액션</li>
              </ul>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-144px">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(2);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
        </section>

        <!-- show(4) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q01_bg01.png'); height:600px;">
              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>

              <p class="p_text">애인이 고민을 털어놨을 때</p><br><br><br>
              <ul class="radio-box" style="width:24%; margin:auto;">
                <li class="list_box" onclick="show(5,'next','F');">몰입해서 공감을 한다</li>
                <li class="list_box" onclick="show(5,'next','T');">해결책을 제안한다</li>
              </ul>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-144px">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(3);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
        </section>

        <!-- show(5) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q01_bg01.png'); height:600px;">
              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>

              <p class="p_text">남자/여자친구와 오랜만의 데이트!</p><br><br><br>
              <ul class="radio-box" style="width:24%; margin:auto;">
                <li class="list_box" onclick="show(6,'next','P');">전 날 미리 데이트 코스를 정해둔다</li>
                <li class="list_box" onclick="show(6,'next','J');">끌리는 대로 즉흥적으로 다닌다</li>
              </ul>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-144px">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(4);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
        </section>

        <!-- show(6) -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q01_bg01.png'); height:600px;">
              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>

              <p class="p_text">데이트 코스를 정할 때</p><br><br><br>
              <ul class="radio-box" style="width:24%; margin:auto;">
                <li class="list_box" onclick="show(7,'next','S');">SNS에서 인기 많은 곳을 찾는다</li>
                <li class="list_box" onclick="show(7,'next','N');">알려지지 않은 특별한 곳을 찾는다</li>
              </ul>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-144px">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(5);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
        </section>

        <!-- show(7) 카카오로 결과 확인하기 -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/p_q02_bg.png'); height:600px;">

              <div>
                <center>
                  <img style="margin-top: 30px;" src="/new/image/mbti_test/p_q_top_img.png" alt="" />
                </center>
              </div>
              <p class="p_text">나의 이상형 궁합은 무엇일까?</p><br>
              <div>
                <center>
                  <img style="margin-left: 50px;" src="/new/image/mbti_test/human.png" alt="" />
                </center>
              </div>
              <div>
                <center>
                  <img style="width:20%; height:80px; margin-top:-20px;" src="/new/image/mbti_test/btn_kakao.png" alt="" onclick="javascript:kakaoGetData();" />
                </center>
              </div>
              <!-- 이전페이지, 다음페이지 -->
              <div style="margin-left:400px; display:block; margin-top:-269px;">
                <img src="/new/image/mbti_test/btn_prev.png" alt="" onclick="show(6);return false;" />
              </div>
              <!-- 이전페이지, 다음페이지 -->
            </div>
          </div>
          <iframe src="" id="counselResult" name="counselResult" width="0" height="0" style="display:none;" frameborder="0"></iframe>
        </section>


        <!-- idx8 결과 창 -->
        <!-- <section id="lovetest">
      <div class="join-charge">
        <div style="padding-bottom: 50px;"></div>
        <p id="mbti" style="border: 5px solid #F00; width:40%; text-align:center; margin:auto; padding-top:10px; padding-bottom:10px; font-size:30px; font-weight:bold;">ISJF</p>

        <div class="btn-group type3 tc" style="position:absolute;top:93%;width:100%;">
          <div style="text-align:center;">
            <a href="#" onclick="location.reload();">처음으로</a>
          </div>
        </div>
      </div>
    </section> -->

        <!-- default -->
        <section id="lovetest">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/istj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- isfp 결과 -->
        <section id="lovetest" class="isfp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/isfp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- isfj 결과 -->
        <section id="lovetest" class="isfj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/isfj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- istj 결과 -->
        <section id="lovetest" class="istj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/istj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- istp 결과 -->
        <section id="lovetest" class="istp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/istp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- infj 결과 -->
        <section id="lovetest" class="infj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/infj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- intj 결과 -->
        <section id="lovetest" class="intj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/intj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- infp 결과 -->
        <section id="lovetest" class="infp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/infp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- intp 결과 -->
        <section id="lovetest" class="intp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/intp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- esfj 결과 -->
        <section id="lovetest" class="esfj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/esfj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- estj 결과 -->
        <section id="lovetest" class="estj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/estj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- esfp 결과 -->
        <section id="lovetest" class="esfp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/esfp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- estp 결과 -->
        <section id="lovetest" class="estp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/estp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- enfj 결과 -->
        <section id="lovetest" class="enfj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/enfj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- entj 결과 -->
        <section id="lovetest" class="entj">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/entj.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- enfp 결과 -->
        <section id="lovetest" class="enfp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/enfp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>

        <!-- entp 결과 -->
        <section id="lovetest" class="entp">
          <div class="join-charge">
            <div class="" style="background-image: url('/new/image/mbti_test/entp.jpg'); height:700px; background-repeat: no-repeat; background-size: contain;">
              <div style="display:block; margin-left:400px;"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <img src="/nm/static/images/lovetest/mbti_test/m_reset.png" alt="" onclick="location.reload();" />
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- footer start -->
  <div class="footer">
    <?
    require_once($_SERVER["DOCUMENT_ROOT"] . "/new/common/footer.php");
    ?>
    <div style="padding-bottom: 50px; background-color:#222222"></div>
  </div>
  <? include_once($_SERVER["DOCUMENT_ROOT"] . "/new/log/log_common.php"); ?>
  <!-- footer end -->

  <!-- 20220223 script -->
  <!-- ADPNUT SCRIPT -->
  <iframe src=”https://tag.adpnut.com/prd/view?shopid=ishish” width=”0” height="0" frameborder="0"></iframe>
</body>

</html>