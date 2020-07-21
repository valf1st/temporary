<!--{layout_header}-->
<link rel="stylesheet" href="/html/css/pages/task-edit.css">
<style>
  .task-edit .task-wrap .box .right select {
    background: transparent;
    border: 2px solid rgba(255,255,255,.5);
    border-radius: 5px;
    /* padding: 12px; */
    width: 100%;
    color: rgba(255,255,255,.8);
    font-size: 14px;
  }
  .task-edit .task-wrap .box .right select option {
    color: initial;
  }
.btn-delete {
    background: rgba(255,255,255,.4);
}
</style>
<!--{/layout_header}-->
<!--{layout_footer}-->
<script src="/html/js/flatpickr.min.js"></script>
<script src="/html/js/pickr-ja.js"></script>
<script>
$(function() {
  
  // カレンダー設定
  $('.flatpickr-range').flatpickr({
    locale: "ja",
    mode: "range",
    allowInput: "true",
    enableTime: "true"
  });
  $('.flatpickr-date').flatpickr({
    locale: "ja",
    allowInput: "true"
  });
  $('.flatpickr-datetime').flatpickr({
    locale: "ja",
    allowInput: "true",
    enableTime: "true"
  });
  $('.flatpickr-times').flatpickr({
    locale: "ja",
    allowInput: "true",
    enableTime: "true",
    noCalendar: "true",
    dateFormat: "H:i",
  });

  var today = new Date();
  var month = today.getMonth() + 1;
  var day = today.getDate();
  var year = today.getFullYear();
  if(month < 10) month = '0' + month.toString();
  if(day < 10) day = '0' + day.toString();
  today = year + '-' + month + '-' + day;
  $('.flatpickr-date-modal').flatpickr({
    locale: "ja",
    allowInput: "true",
    maxDate: today
  });

  // 繰り返し日時設定  
  $('#select-repeat').on('change', function() {
    if ($(this).val() > 0){
      $('#js-input-repeat-enddate').prop('required', true).prop('disabled', false).parent('div').slideDown();
    } else {
      $('#js-input-repeat-enddate').prop('required', false).prop('disabled', true).parent('div').slideUp();
    }
  }).trigger('change');
  
  // 期限日チェック
  $('.btn-update[type=submit]').on("click", function() {
    const deadline = $('[name="task[deadline]"]').val();
    const do_date = $('[name="task[do_date]"]').val();
    if (deadline && deadline < do_date) {
      alert("期日は実行日より後に設定してください");
      return false;
    }
  });
  
});

// 完了モーダル
function openModalComplete(){
    MicroModal.show('modal-task-complete', {
      awaitCloseAnimation: true,
      disableScroll: true
    });
}
// 完了登録時のバリデーション
$("#complete_button").click(function() {
    // 1つも入力しない or 3つすべて入力でOK
    var date = $('input[name="do_date"]').val();
    var timeb = $('input[name="time_before"]').val();
    var timea = $('input[name="time_after"]').val();
    if(date && timeb && timea){
        return true;
    }else if(!date && !timeb && !timea){
        return true;
    }else{
        alert("日付、開始時間、終了時間はすべて入力もしくはすべて空にしてください。");
        return false;
    }
});

$('#filter_user').change(function(){
    var user = $(this).val();
    $('.milestone').show();
    $('.milestone').not('#u_'+ user).hide();
    $('#u_all').show();
})
function filterRoadmap(){
    var selected = document.querySelector('input[name="roadmap"]:checked').value;
    $('.roadmap').show();
    if(selected){
        $('.roadmap').not('#m_'+ selected).hide();
        $('#m_other').show();
    }
}

function deleteTask(task){
    MicroModal.show('modal-task-delete', {
      awaitCloseAnimation: true,
      disableScroll: true
    });
    /*var result = confirm("削除してよろしいですか?");
    if(result){
        window.location.href = "/mypage/work_tasks/" + task.value + "/delete";
    }*/
}
</script>
<!--{/layout_footer}-->

<article class="task-edit content-wrap color-white container-fluid">
  <section class="ttl-wrap alpha">
    <h1 class="page-title">タスク入力・編集</h1>
  </section>
  <section class="task-wrap bgc-black">
    <form id="work-task-form" method="post" action="/mypage/work_tasks/commit">
      <input type="hidden" name="task[id]" value="<?= $task["id"] ?>">
      <div class="box">
        <div class="left">
          <p class="name">タスクタイトル<span class="must">必須</span></p>
        </div>
        <div class="right">
          <input required type="text" name="task[title]" placeholder="タスクのタイトルを入力" value="<?= $task['title'] ?>">
        </div>
      </div>
      <div class="box align-items-top">
        <div class="left">
          <p class="name">タスクの詳細</p>
        </div>
        <div class="right">
          <textarea class="auto-resize" type="text" name="task[description]" placeholder="タスクの詳細を入力"><?= $task['description'] ?></textarea>
        </div>
      </div>

<? if ($task_users): ?>
      <div class="box">
        <div class="left">
          <p class="name">担当者<span class="must">必須</span></p>
        </div>
        <div class="right">
          <div style="margin-top: 10px;">
            <select required name="user_ids[]" class="form-control" id="filter_user">
  <? foreach ($task_users as $user): ?>
              <option value="<?= $user['user_id'] ?>"<?= $func->f2slct(in_array($user['user_id'], $user_ids)) ?>><?= $user['user_name'] ?></option>
  <? endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    <? if($create_user): ?>
      <div class="box">
        <div class="left">
          <p class="name">作成者</p>
        </div>
        <div class="right">
          <div style="margin-top: 10px;"><?= $create_user ?></div>
        </div>
      </div>
    <? endif; ?>
<? else: ?>
      <input type="hidden" name="user_ids[]" value="<?= $this->current_user->id ?>">
<? endif; ?>

      <div class="box place align-items-center">
        <div class="left">
          <p class="name">ロードマップ<span class="must">必須</span></p>
        </div>
        <div class="right">
<? if ($target_list): ?>
<?  foreach ($task_users as $task_user): ?>
    <?  foreach ($task_user['milestones'] as $m): ?>
          <div class="radio-group milestone" id="u_<?= $task_user['user_id'] ?>">
            <input required type="radio" id="roadmap-<?= $m['id'] ?>" name="roadmap" value="<?= $m['id'] ?>"<?= $func->f2chk($task['milestone_id'] == $m['id']) ?> onclick="filterRoadmap();">
            <label for="roadmap-<?= $m['id'] ?>"><?= $m['title'] ?></label>
          </div>
    <?  endforeach; ?>
<?  endforeach; ?>
          <div class="radio-group milestone" id="u_all">
            <input required type="radio" id="roadmap-other" name="roadmap" value="other" onclick="filterRoadmap();" <? if(!$task['milestone_id']): ?>checked="checked"<? endif; ?>>
            <label for="roadmap-other">その他</label>
          </div>
<? endif; ?>
        </div>
      </div>

      <div class="box place align-items-center">
        <div class="left">
          <p class="name">結果指標<span class="must">必須</span></p>
        </div>
        <div class="right">
<? if ($target_list): ?>
<?  foreach ($target_list as $target): ?>
    <? if($target['id'] == 0): ?>
          <div class="radio-group roadmap" id="m_other">
    <? else: ?>
          <div class="radio-group roadmap" id="m_<?= $target['milestone_id'] ?>">
    <? endif; ?>
            <input required type="radio" id="kpi-<?= $target['id'] ?>" name="task[target_id]" value="<?= $target['id'] ?>"<?= $func->f2chk($task['target_id'] == $target['id']) ?>>
            <label for="kpi-<?= $target['id'] ?>"><?= $target['title'] ?></label>
          </div>
<?  endforeach; ?>
<? endif; ?>
        </div>
      </div>
      <div class="box">
        <div class="left">
          <p class="name">実行日時<span class="must">必須</span></p>
        </div>
        <div class="right">
          <div style="display: flex; align-items: center;">
            <input required class="flatpickr-date form-control" type="text" name="task[do_date]" placeholder="日付" value="<?= $task["do_date"] ?>" autocomplete="off">
            <div style="padding: 0 10px;"></div>
            <input required class="flatpickr-times form-control" type="text" name="task[do_starttime]" placeholder="開始時刻" value="<?= $task["do_starttime"] ?>" autocomplete="off">
            <div style="padding: 0 10px;">〜</div>
            <input required class="flatpickr-times form-control" type="text" name="task[do_endtime]" placeholder="終了時刻" value="<?= $task["do_endtime"] ?>" autocomplete="off">
          </div>
          <div style="margin-top: 10px;">
            <select required id="select-repeat" name="task[repeat_type_id]" class="form-control">
<?  foreach ($repeat_types as $id => $name): ?>
              <option value="<?= $id ?>" <?= $func->f2slct($task["repeat_type_id"] == $id) ?>><?= $name ?></option>
<?  endforeach; ?>
            </select>
          </div>
          <div style="margin-top: 10px; display: none;">
            <input id="js-input-repeat-enddate" class="flatpickr-date form-control" type="text" name="task[repeat_enddate]" placeholder="繰り返し終了日" value="<?= $task["repeat_enddate"] ?>" autocomplete="off">
          </div>
        </div>
      </div>
      <div class="box">
        <div class="left">
          <p class="name">期限<span class="must">必須</span></p>
        </div>
        <div class="right">
          <input class="flatpickr-date max-w-150px" required type="text" name="task[deadline]" placeholder="日付" value="<?= $task['deadline'] ?>" autocomplete="off">
        </div>
      </div>
      <p class="text-center alpha mb-3">
        <button class="btn btn-update btn-size-normal btn-circle btn-size-normal ___open-modal-update" type="submit">保存する</button>
<? if($task['id'] && $task['progress'] < 100): ?>
        <a href="javascript:void(0)" onclick="openModalComplete(); return false;" class="btn btn-update btn-size-normal btn-circle">完了する</a>
<? endif; ?>
        <button class="btn btn-size-normal btn-circle btn-delete" onclick="deleteTask(this); return false;" value="<?= $task['id'] ?>">削除する</button>
      </p>
    </form>
  </section>
</article>

<!--{layout_modal}-->
<div class="micromodal-slide theme-black" id="modal-task-complete" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">実績入力</p>
      <form method="post" id="js-modal-c-href" class="form-basic" action="/mypage/work_tasks/complete/<?=$task['id']?>">
        <dl>
          <dt>タスク名</dt>
          <dd>
            <input class="form-control" type="text" name="title" value="<?=$task['title']?>" readonly />
          </dd>
        </dl>
        <dl>
          <dt>日付</dt>
          <dd><input class="form-control flatpickr-date-modal max-w-150px" type="date"  name="do_date" id="time_date" value="" autocomplete="off"></dd>
        </dl>
        <dl>
          <dt>時間</dt>
          <dd>
            <div class="form-inline">
              <input type="time" class="form-control" name="time_before" value="">
              <span class="ml-auto ml-sm-1 mr-auto mr-sm-1 mt-1 mb-1 rotate-90 rotate-sm-0 d-block text-center">～</span>
              <input type="time" class="form-control" name="time_after" value="">
            </div>
          </dd>
        </dl>
        <p class="text-center mt-4 alpha">
          <span class="btn-list">
            <button class="btn btn-update btn-circle btn-size-normal" type="submit" id="complete_button">完了する</button>
          </span>
        </p>
      </form>
    </div><!--/.modal-container-->
  </div><!--/.modal-overlay-->
</div><!--/.micromodal-slide-->

<!--{layout_modal}-->
<div class="micromodal-slide theme-black" id="modal-task-delete" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">タスク削除</p>
      <form method="post" id="js-modal-c-href" class="form-basic" action="/mypage/work_tasks/<?=$task['id']?>/delete">
        <p class="text-center mt-4 alpha">
          <span class="btn-list">
            <button class="btn btn-update btn-circle btn-size-normal" type="submit" id="complete_button" name="delete_type" value="this">このタスクのみ削除する</button>
            <button class="btn btn-update btn-circle btn-size-normal" type="submit" id="complete_button" name="delete_type" value="all">繰り返しタスクも削除する</button>
          </span>
        </p>
      </form>
    </div><!--/.modal-container-->
  </div><!--/.modal-overlay-->
</div><!--/.micromodal-slide-->
