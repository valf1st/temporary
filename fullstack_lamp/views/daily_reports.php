<!--{layout_header}-->
<link rel="stylesheet" href="/html/css/plugins/fullcalendar/core/main.min.css">
<link rel="stylesheet" href="/html/css/plugins/fullcalendar/daygrid/main.min.css">
<link rel="stylesheet" href="/html/css/plugins/fullcalendar/timegrid/main.min.css">
<link rel="stylesheet" href="/html/css/pages/day-diary.css">
<link rel="stylesheet" href="/html/css/plugins/zabuto_calendar.css">
<link rel="stylesheet" href="/html/css/plugins/zabuto_calendar-customize.css">
<!--{/layout_header}-->
<!--{layout_footer}-->
<script src="/html/js/zabuto_calendar.js"></script>
<script src="/html/js/fullcalendar/core/main.min.js"></script>
<script src="/html/js/fullcalendar/interaction/main.min.js"></script>
<script src="/html/js/fullcalendar/daygrid/main.min.js"></script>
<script src="/html/js/fullcalendar/timegrid/main.min.js"></script>
<script src="/html/js/fullcalendar/core/locales/ja.js"></script>
<script src="/html/js/flatpickr.min.js"></script>
<script src="/html/js/pickr-ja.js"></script>
<script>
// カレンダー選択
$(function(){
    $('.flatpickr-date').flatpickr({
        locale: "ja",
        allowInput: "true"
    });
});

// 予定・実績カレンダー初期化
$(function(){
    var schedule_plan = <?= $schedule_plan ? json_encode($schedule_plan) : "[]" ?>;
    var schedule_achievement = <?= $schedule_achievement ? json_encode($schedule_achievement) : "[]" ?>;
    fullcalendarPlan(schedule_plan);
    fullcalendarAchievement(schedule_achievement);
});

// 予定
function fullcalendarPlan(data) {
    fullcalendar('plan', data);
}

// 実績
function fullcalendarAchievement(data) {
    fullcalendar('achievement', data);
}

// 予定・実績カレンダー描画
function fullcalendar(type, data) {
    var eventDate = "<?= $date ?>";
    var eventColor = { plan: '#a60124', achievement: '#cf284c' };
    var events = (data && data.length > 0) ? data : {
        url: '/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/ajax-schedules/' + type,
        type: 'post'
    };
    var drag = { plan: false, achievement: true };

    // カレンダーオプション
    var options = {
        plugins: [ 'interaction', 'timeGrid' ] ,
        // 日本語化
        locale: 'ja',
        // ヘッダー非表示
        header: {
            left: '',
            center: '',
            right: ''
        },
        // デフォルト
        defaultDate: new Date(eventDate),
        // デフォルト表示
        defaultView: 'timeGridDay',
        // 終日表示の枠を表示
        allDaySlot: false,
        // イベント
        eventSources: [events],
        // イベントの背景色
        eventColor: eventColor[type],
        // イベント期間をドラッグしで変更
        eventDurationEditable: false,
        // イベントを重ねて表示
        slotEventOverlap: true,
        // 高さ
        height: 'auto',
        // イベントドラッグ
        droppable: false,
        // ドラッグで時間選択
        selectable: drag[type],
        selectMirror: drag[type],
        editable: true,
        eventDrop: function(eventDropInfo) {
            changeTime(eventDropInfo);
        },
    };

    var calendarEl = document.getElementById('fullcalendar-' + type);
    calendarEl.innerHTML = "";

    var calendar = new FullCalendar.Calendar(calendarEl, options);
<? if ($user_id == $this->current_user->id): ?>
    calendar.setOption('eventClick', function(info) {
        MicroModal.show('modal-reflexion-' + type, {
            awaitCloseAnimation: true,
            disableScroll: true
        });
        var schedule_id = info.event.id;
        $('#schedule_id_' + type).val(schedule_id);
        var work_task_id = info.event.extendedProps.wt_id;
        $('#work_task_' + type).val(work_task_id);
        var start = info.event.start.toISOString();
        start = start.split('T');
        var thisdate = start[0];
        $('#time_date_' + type).val(thisdate);
        start = start[1].split('.');
        $('#time_before_' + type).val(start[0]);
        var end = info.event.end.toISOString();
        end = end.split('T');
        end = end[1].split('.');
        $('#time_after_' + type).val(end[0]);
    });
<? endif; ?>
    calendar.setOption('select', function(info){
                       MicroModal.show('modal-reflexion-achievement', {
                           awaitCloseAnimation: true,
                           disableScroll: true
                       });
                       var start = info.startStr;
                       start = start.split('T');
                       var thisdate = start[0];
                       $('#time_date_achievement').val(thisdate);
                       start = start[1].split('+');
                       $('#time_before_achievement').val(start[0]);
                       var end = info.endStr;
                       end = end.split('T');
                       end = end[1].split('+');
                       $('#time_after_achievement').val(end[0]);
    });
    calendar.render();
}
function changeTime(info){
    var start = info.event.start.toISOString();
    var end = info.event.end.toISOString();
    var data = {"schedule_id": info.event.id, "start": start, "end": end};
    $.ajax({
        url: "/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/change_time_ajax",
        type: "post",
        dataType: "json",
        data: data,
        timeout: 5000,
    }).done(function(res) {
        if (res.state != 'success') {
            alert('変更に失敗しました。');
            return false;
        }
        alert('変更しました。');
    }).fail(function(res) {
        alert('変更に失敗しました。');
    });
}

// 他の日選択用のカレンダー
$(document).ready(function () {
    $("#calendar-link").zabuto_calendar({
        language: "jp",
        weekstartson: 0,
        data: <?= $this_month ?>,
        action: function() {
            return dateFunction(this.id, false);
        }
    });
});
function dateFunction(id) {
    var date = $("#" + id).data("date");
    var cdate = new Date(date);
    var today = new Date("<?= $today ?>");
    if(cdate <= today){
        window.location.href = "/mypage/daily-reports/<?= $user_id ?>/" + date;
    }
}

// レポート内容の更新
$(function() {
    $('.diary-box').on('keyup', function() {
        $(this).next().removeClass('disabled');
    });

    $('.update-btn').on('click', function() {
        var $this = $(this);
        var form = $this.parents('form');

        if (typeof(form) === "undefined") {
            return false;
        }

        var url = $(form).attr('action');
        var _data_set = $(form).serializeArray();

        var data = {};
        for (var _data of _data_set) {
            data[_data.name] = _data.value;
        }

        $.ajax({
            url: url,
            type: "post",
            dataType: "json",
            data: data,
            timeout: 5000,
        }).done(function(res) {
            if (res.state != 'success') {
                alert('保存に失敗しました。');
                return false;
            }
            $this.parent().addClass('disabled');
            alert('保存しました。');
        }).fail(function(res) {
            if(res.responseText.includes("ログイン")){
                window.location.href = "/login";
            }else{
                alert('保存に失敗しました。');
            }
        });
    });

    $('.comment-btn').each(function() {
        $(this).attr("type", "button");
    }).on('click', function() {
        var $this = $(this);
        var form = $(this).parents('form');

        if (typeof(form) === "undefined") {
            return false;
        }

        var url = $(form).attr('action');
        var _data_set = $(form).serializeArray();

        var data = {};
        for (var _data of _data_set) {
            data[_data.name] = _data.value;
        }

        if (!data.comment) {
            alert('コメントを入力してください。');
            return false;
        }

        $.ajax({
            url: url,
            type: "post",
            dataType: "json",
            data: data,
            timeout: 5000,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).done(function(res) {
            if(res.state == 'error'){
                alert('日報が提出されていないのでコメントできません。');
            }else if (res.state != 'success') {
                alert('送信に失敗しました。');
                return false;
            }
            window.location.reload();
        }).fail(function(res) {
            alert('送信に失敗しました。');
        });
    });
});

// 予定・実績編集用modal
$(function() {
    $('.open-modal-plan').on('click',function() {
        MicroModal.show('modal-reflexion-plan', {
            awaitCloseAnimation: true,
            disableScroll: true
        });
    });
    $('.open-modal-achievement').on('click',function() {
        MicroModal.show('modal-reflexion-achievement', {
            awaitCloseAnimation: true,
            disableScroll: true
        });
    });
});
//コメントリアクション
function sendReaction(reaction){
    var url = '/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/ajax-comment-reaction/';
    var data = {"comment_id": reaction.id, "reaction": reaction.value};
    $.ajax({
        url: url,
        type: "post",
        dataType: "json",
        data: data,
        timeout: 5000,
    }).done(function(res) {
        if (res.state != 'success') {
            alert('送信に失敗しました。');
            return false;
        }else if(res.type == 'update') {
            if(res.reaction == "bad"){
                $("#reaction_img"+res.id).attr("src","/html/img/reaction/thumbup.svg");
                $("#reaction_img"+res.id).attr("style","transform:rotate(180deg)");
            }else{
                $("#reaction_img"+res.id).attr("src","/html/img/reaction/"+res.reaction+".svg");
                $("#reaction_img"+res.id).removeAttr("style","transform:rotate(180deg)");
            }
        }else{
            $("#comment_reactions").css('display','block');
            if(res.reaction == "bad"){
                $("#comment_reactions").append('<img class="icon-opacity-8" id="reaction_img'+res.id+'" src="/html/img/reaction/thumbup.svg" alt="リアクション" style="transform:rotate(180deg)">');
            }else{
                $("#comment_reactions").append('<img class="icon-opacity-8" id="reaction_img'+res.id+'" src="/html/img/reaction/'+res.reaction+'.svg" alt="リアクション">');
            }
        }
    }).fail(function(res) {
        alert('送信に失敗しました。');
    });
}
// コメント編集
$(function(){
    $('.comment .reactions .do-edit').on('click', function(){
        MicroModal.show('modal-comment-edit', {
            awaitCloseAnimation: true,
            disableScroll: true
        });
        var id = $(this).attr('id');
        $('#edited_comment_id').val(id);
        var b_comment = $(this).parent().parent().find('.text').text();
        $('#edited_comment').val(b_comment);
    });
});
</script>
<!--{/layout_footer}-->
<article class="day content-wrap">

  <section class="ttl-wrap alpha">
<? if ($user_id == $this->current_user->id): ?>
    <h1 class="page-title"><?= $func->format_date($date, 'Y年m月d日') ?>の日報</h1>
<? else: ?>
    <h1 class="page-title"><?= $user_name ?>の日報 <?= $func->format_date($date, 'Y年m月d日') ?></h1>
<? endif; ?>
    <div class="btn-wrap d-flex align-items-center">
      <p class="have-btn active arrow">日報</p>
      <p class="move have-btn"><a href="/mypage/weekly-reports/<?= $user_id?>/<?= $date ?>" class="d-block btn-next arrow">週報</a></p>
    </div><!-- ./btn-wrap -->
  </section><!-- ./day-ttl -->

  <div class="wrapper">
    <div class="row">
      <div class="col-lg-6">
<? if ($milestones): ?>
  <? $latest = $milestones[0]; ?>
  <? $final = $milestones[count($milestones) -1]; ?>
        <section class="mark-wrap bg-box">
          <h2 class="heading ttl">ゴール目標</h2>
          <div class="circlebox align-items-center">
            <p class="remind text-center">あと<br><?= $func->diff_date(date('Y-m-d'), $final['date']) ?></p>
            <div class="text-box">
              <p class="goal-text"><?= $final['title'] ?></p>
              <div class="date d-flex align-items-center">
                <p class="checker"><img src="/html/img/common/checker-white.svg" alt="チェッカーフラッグ"></p>
                <p class="number"><?= $func->format_date($final['date'], 'Y/m/d') ?></p>
              </div><!-- ./date -->
            </div><!-- ./text-box -->
          </div><!-- ./circlebox -->
          <h2 class="heading ttl">直近の目標</h2>
          <div class="circlebox align-items-center">
            <p class="remind bgc-pink text-center">あと<br><?= $func->diff_date(date('Y-m-d'), $latest['date']) ?></p>
            <div class="text-box">
              <p class="goal-text"><?= $latest['title'] ?></p>
              <div class="date d-flex align-items-center">
                <p class="checker"><img src="/html/img/common/checker-white.svg" alt="チェッカーフラッグ"></p>
                <p class="number"><?= $func->format_date($latest['date'], 'Y/m/d') ?></p>
              </div><!-- ./date -->
            </div><!-- ./text-box -->
          </div><!-- ./circlebox -->
        </section>
<? else: ?>
        <section class="mark-wrap bg-box" style="position: relative;">
          <div style="filter: blur(5px);">
            <h2 class="heading ttl">ゴール目標</h2>
            <div class="circlebox align-items-center">
              <p class="remind text-center">あと<br>15日</p>
              <div class="text-box">
                <p class="goal-text">ゴール目標</p>
                <div class="date d-flex align-items-center">
                  <p class="checker"><img src="/html/img/common/checker-white.svg" alt="チェッカーフラッグ"></p>
                  <p class="number"><?php echo date("Y/m/d", strtotime("+15 day")); ?></p>
                </div><!-- ./date -->
              </div><!-- ./text-box -->
            </div><!-- ./circlebox -->
            <h2 class="heading ttl">直近の目標</h2>
            <div class="circlebox align-items-center">
              <p class="remind bgc-pink text-center">あと<br>5日</p>
              <div class="text-box">
                <p class="goal-text">直近の目標</p>
                <div class="date d-flex align-items-center">
                  <p class="checker"><img src="/html/img/common/checker-white.svg" alt="チェッカーフラッグ"></p>
                  <p class="number"><?php echo date("Y/m/d", strtotime("+5 day")); ?></p>
                </div><!-- ./date -->
              </div><!-- ./text-box -->
            </div><!-- ./circlebox -->
          </div>
          <a href="/mypage/roadmap" style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 25px;
            line-height: 35px;
            text-align: center;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            ">
            <p>目標を追加すると<br>ここに表示されます</p>
          </a>
        </section>
<? endif; ?>
      </div><!-- ./col -->
      <div class="col-lg-6 calendar">
        <section class="timeline bg-box">
          <div class="ttl-box heading line">
            <h2 class="heading">他の日の日報を見る</h2>
            <p class="alpha m-0">
              <button type="button" class="btn btn-add btn-circle" onclick="window.location.href='/mypage/daily-reports?user=self'">一覧を見る</button>
            </p>
          </div>
          <div class="calendar-wrap">
            <div id="calendar-link"></div>
          </div>
        </section>
      </div><!-- ./col -->
    </div><!-- ./row -->
  </div><!-- ./wrapper -->

  <div class="wrapper">
    <div class="row">
      <div class="col-lg-6">
        <section class="timeline bg-box">
<? if ($user_id == $this->current_user->id): ?>
          <div class="ttl-box heading line">
            <h2 class="heading">今日の振り返り</h2>
            <p class="alpha m-0">
              <button type="button" class="btn btn-add btn-circle open-modal-achievement">実績追加 <i class="fas fa-plus"></i></button>
            </p>
          </div>
<? else: ?>
          <h2 class="heading line">今日の振り返り</h2>
<? endif; ?>
          <div class="reflexion-box">
            <div class="row">
              <div class="col-sm-6">
                <h3 class="heading">予定</h3>
                <div id="fullcalendar-plan"></div>
              </div><!--/.col-->
              <div class="col-sm-6">
                <h3 class="heading">実績</h3>
                <div id="fullcalendar-achievement"></div>
              </div><!--/.col-->
            </div><!--/.row-->
          </div><!--/.reflexion-box-->
        </section>

      </div><!-- ./col -->
      <div class="col-lg-6">
        <section class="diary bg-box">
          <h2 class="heading line">今日の振り返り</h2>
          <div class="box">
            <dl class="box-ttl d-flex match-height-2 line">
              <dt><img class="icon-opacity-8" src="/html/img/common/like.svg" alt="うまくいったこと"></dt>
              <dd class="ttl font-weight-bold">うまくいったこと</dd>
            </dl>
<? if ($user_id == $this->current_user->id): ?>
            <form method="get" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/ajax-update">
              <textarea class="diary-box auto-resize" type="text" name="success" placeholder="ここに入力"><?= $p['success'] ?></textarea>
              <div class="d-flex align-items-center update disabled justify-content-center">
                <a href="javascript:void(0);" class="update-btn"><i class="fas fa-save"></i> 保存</a>
              </div>
            </form>
<? else: ?>
            <div class="diary-content"><?= nl2br($p['success']) ?></div>
<? endif; ?>
          </div><!-- ./box -->
          <div class="box">
            <dl class="box-ttl d-flex match-height-2 line">
              <dt><img class="icon-opacity-8" src="/html/img/dashboard/miss.svg" alt="うまくいかなかったこと"></dt>
              <dd class="ttl font-weight-bold">うまくいかなかったこと</dd>
            </dl>
<? if ($user_id == $this->current_user->id): ?>
            <form method="get" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/ajax-update">
              <textarea class="diary-box auto-resize" type="text" name="fail" placeholder="ここに入力"><?= $p['fail'] ?></textarea>
              <div class="d-flex align-items-center update disabled justify-content-center">
                <a href="javascript:void(0);" class="update-btn"><i class="fas fa-save"></i> 保存</a>
              </div>
            </form>
<? else: ?>
            <div class="diary-content"><?= nl2br($p['fail']) ?></div>
<? endif; ?>
          </div><!-- ./box -->
          <div class="box">
            <dl class="box-ttl d-flex match-height-2 line">
              <dt><img class="icon-opacity-8" src="/html/img/dashboard/retweet.svg" alt="改善案"></dt>
              <dd class="ttl font-weight-bold">改善案</dd>
            </dl>
<? if ($user_id == $this->current_user->id): ?>
            <form method="get" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/ajax-update">
              <textarea class="diary-box auto-resize" type="text" name="improvement" placeholder="ここに入力"><?= $p['improvement'] ?></textarea>
              <div class="d-flex align-items-center update disabled justify-content-center">
                <a href="javascript:void(0);" class="update-btn"><i class="fas fa-save"></i> 保存</a>
              </div>
            </form>
<? else: ?>
            <div class="diary-content"><?= nl2br($p['improvement']) ?></div>
<? endif; ?>
          </div><!-- ./box -->
        </section>
      </div><!-- ./col -->
    </div><!-- ./row -->
  </div><!-- ./wrapper -->

  <section class="comment bg-box">
    <div class="heading-wrap">
      <dl class="d-flex align-items-center">
        <dt><img class="icon-opacity-8" src="/html/img/diary/comment.svg" alt="コメント"></dt>
        <dd>コメント</dd>
      </dl>
      <a class="reload" href="javascript:window.location.reload();"><img class="icon-opacity-8" src="/html/img/diary/reload.svg" alt="リロード"></a>
    </div><!-- ./heading-wrap -->
<? if ($comments): ?>
  <? foreach ($comments as $rc): ?>
    <div class="box">
      <p class="icon-images">
    <? if ($rc['user_image']): ?>
        <img src="https://store-account.01cloud.jp/images/<?= $rc['user_image'] ?>">
    <? else: ?>
        <i class="fa fa-user-circle"></i>
    <? endif; ?>
      </p>
      <div class="content">
        <div class="wrap">
          <p class="name"><?= $rc['user_name'] ?></p>
          <p class="time"><?= $func->format_date($rc['created_at'], 'n/j H:i') ?></p>
        </div><!-- ./wrap -->
        <div class="text-wrap">
          <p class="text"><?= nl2br($rc['comment']) ?><p>
          <div class="reacted" id="comment_reactions" style="height:25px<? if(!$rc['reactions']){ ?>, display:none<? } ?>">
<? if($rc['reactions']): ?>
  <? foreach($rc['reactions'] as $reaction):?>
    <? if($reaction['icon'] == "bad"): ?>
            <img class="icon-opacity-8" id="reaction_img<?= $reaction['id'] ?>" src="/html/img/reaction/thumbup.svg" style="transform:rotate(180deg)" alt="リアクション">
    <? else: ?>
            <img class="icon-opacity-8" id="reaction_img<?= $reaction['id'] ?>" src="/html/img/reaction/<?= $reaction['icon'] ?>.svg" alt="リアクション">
    <? endif; ?>
  <? endforeach; ?>
<? endif; ?>
          </div>
          <div class="reactions">
            <p><img class="icon-opacity-8" src="/html/img/reaction/face2.svg" alt="リアクション"></p>
            <p class="do-reaction">リアクションをする</p>
    <? if ($rc['user_id'] == $this->current_user->id): ?>
            <p><img class="icon-opacity-8" src="/html/img/common/pen.svg" alt="編集"></p>
            <p class="do-edit" id="<?= $rc['id'] ?>">編集する</p>
    <? endif; ?>
          </div><!-- ./reactions -->
          <div class="reactions-item">
            <ul class="reactions-item-wrap">
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="1"><img class="img" src="/html/img/reaction/thumbup.svg" alt="いいね"></li>
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="2"><img class="img" src="/html/img/reaction/happy.svg" alt="嬉しい"></li>
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="3"><img class="img" src="/html/img/reaction/sad.svg" alt="悲しい"></li>
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="4"><img class="img" src="/html/img/reaction/heart.svg" alt="ハートマーク"></li>
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="5"><img class="img" src="/html/img/reaction/check.svg" alt="了解"></li>
              <li onclick="sendReaction(this)" id="<?= $rc['id'] ?>" value="6"><img class="img" src="/html/img/reaction/thumbup.svg" alt="BAD"></li>
            </ul>
          </div><!-- ./reactions-item -->
        </div>
      </div><!-- ./content -->
    </div><!-- ./box -->
  <? endforeach; ?>
<? endif; ?>
    <div class="box pb-3">
      <form method="post" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/comment" class="form-wrap d-flex align-items-start w-100">
        <textarea class="comment-area auto-resize" type="text" name="comment" placeholder="コメントを入力"></textarea>
        <button class="d-block comment-btn" type="submit">送信</button>
      </form>
    </div><!-- ./box -->
  </section>
</article>


<? /*
<div class="formBox">
  <div class="titleStyle01">
    <h1><i class="icon-calender"></i>日報<?= $p['id'] ? '編集' : '作成' ?></h1>
  </div>
  <form action="/mypage/daily_reports/commit" method="post" class="formInner" data-validation="/mypage/daily_reports/validation">
    <input type="hidden" name="p[id]" value="<?= $p["id"] ?>">
    <div>
      <dl class="formDlList pt_0">
        <dt>ロードマップで設定した日時</dt>
        <dd><input type="text" value="<?= date('Y-m-d') ?>" readonly></dd>
      </dl>
      <dl class="formDlList">
        <dt>GOAL日時</dt>
        <dd><input type="text" value="<?= date('Y-m-d') ?>" readonly></dd>
      </dl>
      <dl class="formDlList">
        <dt>日報日付</dt>
        <dd><input type="text" name="p[date]" value="<?= $p["date"] ?: date("Y-m-d") ?>" data-toggle="datepicker" id="js-reportDate"></dd>
      </dl>
      
<?  if ($this->current_user->level_id > 2): ?>
      <input type="hidden" name="p[id]" value="<?= $p["id"] ?>">
<?  else: ?>
      <dl class="formDlList">
        <dt>担当者</dt>
        <dd>
          <div class="selectboxStyle">
            <select name="p[administrator_id]" class="form-control">
<?  foreach ($administrators as $admin): ?>
              <option value="<?= $admin["id"] ?>" <?= $func->f2slct($admin["id"] == $p["administrator_id"]) ?>><?= $admin["name"] ?></option>
<?  endforeach; ?>
            </select>
          </div>
        </dd>
      </dl>
<?  endif; ?>
      <dl class="formDlList">
        <dt>今日の振り返り</dt>
        <dd>
          <table width="100%">
            <thead>
              <tr>
                <th></th>
                <th char="text-center">予定</th>
                <th>実績</th>
              </tr>
            </thead>
            <tbody id="js-tasks">
<?  foreach ($hours as $key => $hour): ?>
              <tr>
                <td><?= $hour["time"] ?><input type="hidden" name="r[<?= $key ?>][time]" value="<?= $hour["time"] ?>"></td>
                <td><input type="text" name="r[<?= $key ?>][task]" value="<?= $hour["task"] ?>"></td>
                <td><input type="text" name="r[<?= $key ?>][result]" value="<?= $hour["result"] ?>"></td>
              </tr>
<?  endforeach; ?>
            </tbody>
          </table>
        </dd>
      </dl>
      <dl class="formDlList">
        <dt>うまくいったこと</dt>
        <dd><textarea name="p[success]"><?= $p["success"] ?></textarea></dd>
      </dl>
      <dl class="formDlList">
        <dt>うまくいかなかったこと</dt>
        <dd><textarea name="p[fail]"><?= $p["fail"] ?></textarea></dd>
      </dl>
      <dl class="formDlList">
        <dt>改善点</dt>
        <dd><textarea name="p[improvement]"><?= $p["improvement"] ?></textarea></dd>
      </dl>
    </div>

    <div class="btnBox">
      <a href="/mypage/daily_reports" class="btnNormal btnSizeS">キャンセル</a>
      <button type="submit" class="btnBlue btnSizeM"><?= $p['id'] ? '変更' : '作成' ?>する</button>
    </div>
  </form>
</div>

<table class="hide">
  <tbody id="js-task_templete">
    <tr>
      <td>%%time%%<input type="hidden" name="r[%%key%%][time]" value="%%time%%"></td>
      <td><input type="text" name="r[%%key%%][task]"></td>
      <td><input type="text" name="r[%%key%%][result]"></td>
    </tr>
  </tbody>
</table>
*/ ?>
<!--{layout_modal}-->
<div class="micromodal-slide theme-black" id="modal-reflexion-plan" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">実績入力</p>
      <form method="post" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/add_achievement" class="form-basic">
        <dl>
          <dt>タイトル</dt>
          <dd>
            <select class="form-control" name="work_task_id" id="work_task_plan">
              <option></option>
<? if($work_tasks): ?>
    <? foreach($work_tasks as $task): ?>
              <option value="<?=$task['id']?>"><?=$task['title']?></option>
    <? endforeach; ?>
<? endif; ?>
            </select>
          </dd>
        </dl>
        <dl>
          <dt>日付</dt>
          <dd><input class="form-control flatpickr-date max-w-150px" type="date"  name="time_date" id="time_date_plan" value="" autocomplete="off" required></dd>
        </dl>
        <dl>
          <dt>時間</dt>
          <dd>
            <div class="form-inline">
              <input type="time" required class="form-control" name="time_before" id="time_before_plan" value="">
              <span class="ml-auto ml-sm-1 mr-auto mr-sm-1 mt-1 mb-1 rotate-90 rotate-sm-0 d-block text-center">～</span>
              <input type="time" required class="form-control" name="time_after" id="time_after_plan" value="">
            </div>
          </dd>
        </dl>
        <p class="text-center mt-4 alpha">
          <span class="btn-list">
            <button class="btn btn-update btn-circle btn-size-normal" type="submit">登録する</button>
          </span>
        </p>
      </form>
    </div><!--/.modal-container-->
  </div><!--/.modal-overlay-->
</div><!--/.micromodal-slide-->
<div class="micromodal-slide theme-black" id="modal-reflexion-achievement" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">実績変更</p>
      <form method="post" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/add_achievement" class="form-basic">
        <input type="hidden" name="schedule_id" id="schedule_id_achievement" />
        <dl>
          <dt>タイトル</dt>
          <dd>
            <select class="form-control" name="work_task_id" id="work_task_achievement">
              <option></option>
<? if($work_tasks): ?>
    <? foreach($work_tasks as $task): ?>
              <option value="<?=$task['id']?>"><?=$task['title']?></option>
    <? endforeach; ?>
<? endif; ?>
            </select>
          </dd>
        </dl>
        <dl>
          <dt>日付</dt>
          <dd><input class="form-control flatpickr-date max-w-150px" type="date" name="time_date" id="time_date_achievement" value="" autocomplete="off" required></dd>
        </dl>
        <dl>
          <dt>時間</dt>
          <dd>
            <div class="form-inline">
              <input type="time" required class="form-control" name="time_before" id="time_before_achievement" value="">
              <span class="ml-auto ml-sm-1 mr-auto mr-sm-1 mt-1 mb-1 rotate-90 rotate-sm-0 d-block text-center">～</span>
              <input type="time" required class="form-control" name="time_after" id="time_after_achievement" value="">
            </div>
          </dd>
        </dl>
        <p class="text-center mt-4 alpha">
          <button class="btn btn-update btn-circle btn-size-normal" type="submit">更新する</button>
        </p>
      </form>
    </div><!--/.modal-container-->
  </div><!--/.modal-overlay-->
</div><!--/.micromodal-slide-->

<div class="micromodal-slide theme-black" id="modal-comment-edit" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">コメント編集</p>
      <form method="post" action="/mypage/daily-reports/<?= $user_id ?>/<?= $date ?>/comment" class="form-basic">
        <input type="hidden" name="comment_id" id="edited_comment_id" />
        <dl>
          <dt>コメント</dt>
          <dd>
            <textarea class="form-control" name="comment" id="edited_comment"></textarea>
          </dd>
        </dl>
        <p class="text-center mt-4 alpha">
          <button class="btn btn-update btn-circle btn-size-normal comment-edit comment-btn" type="submit">更新する</button>
        </p>
      </form>
    </div><!--/.modal-container-->
  </div><!--/.modal-overlay-->
</div><!--/.micromodal-slide-->
<!--{/layout_modal}-->
