<!--{layout_header}-->
<link rel="stylesheet" href="/html/css/pages/task-view.css">
<style>
  .task-view .task-detail > p:not(:nth-last-child(-n+3)) {
    border-right: 1px solid rgba(255,255,255,.8);
  }
  section.views {
    margin-top: 20px!important;
  }
  .sort-pulldowns {
    margin-top: 20px;
    margin-left: auto;
    width: 300px;
    display: flex;
  }
  .sorts .sort-pulldowns select {
    width: 100%;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 5px;
    padding: 10px 13px;
    font-size: 14px;
    font-weight: bold;
    color: rgba(255,255,255,.8);
    background: transparent;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin-left: 10px;
  }

  ol {
    margin: 0;
    padding: 0;
    padding-left: 30px;
  }

  ol:not(:first-child) {
    margin: 0 0 0 50px;
    padding: 0;
    list-style-type: none;
  }
  .sortable li {
    margin: 7px 0 0 0;
    padding: 0;
  }
  .sortable li div  {
    margin: 0;
    cursor: move;
  }
  .sortable li.placeholder {
    background-color: #cfcfcf;
  }

  .sortingBox ol li {
      background: #fff;
      border-bottom: 0px;
  }
  .sortingBox ol li .pickArea {
      background: #F0F3F4;
      background: #D3EDFB;
      align-items: center;
      border-right: 1px solid #E3E9EB;
  }
  .sortingBox ol li .pickArea i::before {
      transform: rotate(90deg);
      color: #C1C9CD;
  }
  .icon-dot-menu:before {
      content: '\e828';
  }

  .view-task-wrap .parent .wrap {
    margin-left: 0;
  }
  .radio-group {
    margin-right: 15px;
  }
</style>
<!--{/layout_header}-->
<!--{layout_footer}-->
<script src="/html/js/flatpickr.min.js"></script>
<script src="/html/js/pickr-ja.js"></script>
<script src="/plugins/jQueryUI/jquery-ui.min.js" type="text/javascript"></script>
<script src="/plugins/nestedSortable/jquery.mjs.nestedSortable.js" type="text/javascript"></script>
<script>
// 絞り込みボックス折りたたみ
$('h2').click(function(event){
    var id = event.target.id;
    if($("#refine-" + id).is(":hidden")){
        $("#refine-" + id).slideDown();
        $("#triangle").text('▲');
    }else{
        $("#refine-" + id).slideUp();
        $("#triangle").text('▼');
    }
});
// 絞り込みボックスクリア
function clearFilter(){
    $('.js-filter-user_id').prop("checked", false);
    $('.js-filter-date-start').val("");
    $('.js-filter-date-end').val("");
    $('.js-filter-target_id').val("");
    $('.js-filter-roadmap').val("");
    $('#js-filter-finished').prop("checked", false);
    $('#js-filter-unfinished').prop("checked", false);
    filterWorkTasks();
    filterRoadmap();
}
// カレンダー選択
$(function() {
  $('.flatpickr-date').flatpickr({
    locale: "ja",
    allowInput: "true"
  });
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

// タスク開閉
$(function() {
  $(".view-task-wrap .arrow-clickable").on("click", function() {
    $(this).closest(".wrap").children(".wrap").slideToggle();
    $(this).children('.arrow').toggleClass('open');
  });
});

// 表示順プルダウン選択時
$(function() {
  $(".sort-pulldowns select").on("change", function() {
    const sort = $('select[name="sort"]').val();
    const order = $('select[name="order"]').val();
    location.href = window.location.pathname + "?sort=" + sort + "&order=" + order;
  });
});

// modal
/*$(function() {
  $('.open-modal').on('click',function() {
    const task_id = $(this).attr('data-task-id');
    const task = $('#task-item-' + task_id);
    const modalItems = [
      'title',
      'do_date',
      'deadline',
      'status_name',
      'description',
    ];
    $.each(modalItems, function(key, val) {
      const item = $(task).find('.js-task-item-' + val).text();
      $('#js-modal-' + val).text(item);
    });
    $('#js-modal-href').attr('href', '/mypage/work_tasks/' + task_id + '/modify');
    $('#js-modal-users').empty();
    $(task).find('.js-task-item-user').appendTo('#js-modal-users');
    MicroModal.show('modal-task-detail', {
      awaitCloseAnimation: true,
      disableScroll: true
    });
  });
});*/
$(function() {
  $('.open-modal-complete').on('click',function() {
    const task_id = $(this).attr('data-task-id');
    const task = $('#task-item-' + task_id);
    const modalItems = [
      'title',
      'do_date'
    ];
    $.each(modalItems, function(key, val) {
      const item = $(task).find('.js-task-item-' + val).text();
      $('#js-modal-c-' + val).val(item);
    });
    $('#js-modal-c-href').attr('action', '/mypage/work_tasks/complete/' + task_id );
    $('#js-modal-users').empty();
    $(task).find('.js-task-item-user').appendTo('#js-modal-users');
    MicroModal.show('modal-task-complete', {
      awaitCloseAnimation: true,
      disableScroll: true
    });
  });
});

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

// フィルターメソッドのバインド
$(function() {
  $('.js-filter-date-start, .js-filter-date-end, .js-filter-target_id').on("change", function() {
    filterWorkTasks();
  });
  $('.js-filter-user_id').on("click", function() {
    filterWorkTasks();
  });
});

// ラジオボタン処理（チェックボックスにcss当てるのが面倒なためラジオボタンのまま）
$(function() {
  $('#js-filter-finished, #js-filter-unfinished').on("click", function() {
    if ($(this).hasClass("checked")) {
      $(this).prop("checked", false).removeClass("checked");
    } else {
      $(this).prop("checked", true).addClass("checked");
    }
    filterWorkTasks();
  }).trigger('click').trigger('click');
});
$(function() {
    var $progressRadio = $('input:radio[id=js-filter-unfinished]');
    var defaultFilterProgress = "<?= $filter['progress']?>";
    if($progressRadio.is(':checked') === false && defaultFilterProgress != "all") {
        $progressRadio.prop("checked", true).addClass("checked");
    }
  filterRoadmap();
});

function filterRoadmap(){
    var selected = document.getElementById("roadmap").value;
    $('.roadmap').show();
    if(selected){
        $('.roadmap').not('#m_'+ selected).hide();
    }
    filterWorkTasks();
}
// 検索条件でフィルタリングするメソッド
function filterWorkTasks() {

  // 定数/変数設定
  const initBit = 0b0000000;
  const hitBits = {
    'user'   : 0b0000001,
    'start'  : 0b0000010,
    'end'    : 0b0000100,
    'target' : 0b0001000,
    'progress':0b0010000,
  };
  let hitBitTotal = initBit;
  let taskBits = {};
  $('.task-body').each(function() {
    taskBits[$(this).attr('id')] = initBit;
  });
  
  // 担当者処理
  const checkedUsers = $('.js-filter-user_id:checked');
  if (checkedUsers.length > 0) {
    hitBitTotal = (hitBitTotal | hitBits['user']);
    $(checkedUsers).each(function() {
      const hitTasks = $('.js-task-user_id[value=' + $(this).val() + ']').parents('.task-body');
      if (hitTasks) {
        $(hitTasks).each(function() {
          const hitTaskId = $(this).attr('id');
          taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['user']);
        });
      }
    });
  }  

  // 検索開始日付処理
  const dateStart = $('.js-filter-date-start').val();
  if (dateStart !== "") {
    hitBitTotal = (hitBitTotal | hitBits['start']);
    $('.js-task-deadline').each(function() {
      const targetDate = $(this).val();
      if (targetDate !== "" && targetDate >= dateStart) {
        const hitTask = $(this).parents('.task-body');
        const hitTaskId = $(hitTask).attr('id');
        taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['start']);
      }
    });
  }
  
  // 検索終了日付処理
  const dateEnd = $('.js-filter-date-end').val();
  if (dateEnd !== "") {
    hitBitTotal = (hitBitTotal | hitBits['end']);
    $('.js-task-deadline').each(function() {
      const targetDate = $(this).val();
      if (targetDate !== "" && targetDate <= dateEnd) {
        const hitTask = $(this).parents('.task-body');
        const hitTaskId = $(hitTask).attr('id');
        taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['end']);
      }
    });
  }

  // 完了・未完了
  const progressFinished = $('#js-filter-finished:checked');
  const progressUnfinished = $('#js-filter-unfinished:checked');
  if ((progressFinished.length > 0 && progressUnfinished.length == 0) || (progressFinished.length == 0 && progressUnfinished.length > 0)) {
    hitBitTotal = (hitBitTotal | hitBits['progress']);
    $('.js-task-progress').each(function() {
      const hitTasks = (progressFinished.length)? $('.js-task-progress[value=100]').parents('.task-body') : $('.js-task-progress[value!=100]').parents('.task-body');
      if (hitTasks) {
        $(hitTasks).each(function() {
          const hitTaskId = $(this).attr('id');
          taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['progress']);
        });
      }
    });
  }

  // 結果指標処理
  const filterTargetId = $('.js-filter-target_id').val();
  var selected = document.getElementById("roadmap").value;
  if (filterTargetId !== "") {
    hitBitTotal = (hitBitTotal | hitBits['target']);
    $('.js-task-target_id').each(function() {
      const taskTargetId = $(this).val();
      if (filterTargetId == taskTargetId) {
        const hitTask = $(this).parents('.task-body');
        const hitTaskId = $(hitTask).attr('id');
        taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['target']);
      }
    });
  }else if(selected > 0){
      // ロードマップ絞り込み時
      hitBitTotal = (hitBitTotal | hitBits['target']);
      $('.js-task-milestone_id').each(function() {
        const taskTargetId = $(this).val();
        if (selected == taskTargetId) {
          const hitTask = $(this).parents('.task-body');
          const hitTaskId = $(hitTask).attr('id');
          taskBits[hitTaskId] = (taskBits[hitTaskId] | hitBits['target']);
        }
      });
  }

  // 表示処理
  let showTaskCount = 0;
  if (hitBitTotal == initBit) {
    $('.task-body').show();
    showTaskCount = 99999;
  } else {
    $('.task-body').hide();
    $.each(taskBits, function(task_body_id, resultBit) {
      if ((hitBitTotal & resultBit) == hitBitTotal) {
        $('#' + task_body_id).show();
        showTaskCount++;
      }
    });
  }
  if (showTaskCount == 0) {
    $('.js-no-tasks').show();
  } else {
    $('.js-no-tasks').hide();
  }
    targetColor();
}

// 結果指標の丸表示
function targetColor() {
    var i;
    var visible;

    // 結果指標ごとに実行
    for (i = 0; i < 10; i++) {
        targetColorChange(i);
    }

    // その他
    targetColorChange("other");
}

function targetColorChange(target) {
    var visible = false;

    // 一旦対象のターゲットを非表示
    $("#target-" + target).parent().hide();

    // 一つでもタスク一覧に対象のターゲットに紐付くタスクが表示されていればvisibleをtrueにセット
    $("[id=target-n-" + target + "]").each(function() {
        if ($(this).is(":visible")) {
            visible = true;
            return false;
        }
    });

    // visibleがtrueだったら対象のターゲットを表示
    if (visible) {
        $("#target-" + target).parent().show();
    }
}

// タスク並び替え
$(function() {
  $('ol.sortable').nestedSortable({
    disableNesting: 'no-nest',
    forcePlaceholderSize: true,
    handle: 'div',
    helper:'clone',
    items: 'li',
    listType: 'ol',
    maxLevels: 3,
    opacity: .6,
    placeholder: 'placeholder',
    revert: 250,
    tabSize: 25,
    tolerance: 'pointer',
    toleranceElement: '> div',
    stop: function(e, ui) {
      var el_parent = $(ui.item).closest("li").closest("ol").closest("li");
      var parent_task_id = el_parent.data('task_id');
      var move_task_id = $(ui.item).data('task_id');
      var move_items = {'parent_task_id':parent_task_id, 'move_task_id':move_task_id};
      var order_ids = createOrderIds();
      $.ajax({
        url:'/mypage/work_tasks/relate_ajax',
          type:'POST',
          responseType:"json",
          data:{'move_items': move_items, 'order_ids': order_ids}
        })
        // Ajaxリクエストが成功した時発動
        .done((json) => {
          console.log("success", json);
        })
        // Ajaxリクエストが失敗した時発動
        .fail((data) => {
          console.log("fail", data.responseText);
        })
        // Ajaxリクエストが成功・失敗どちらでも発動
        .always((data) => {
        });

    }
  });
});

function createOrderIds() {
  let order_ids = [];
  $('li.movable').each(function() {
      order_ids.push($(this).attr('data-task_id'));
  });
  return order_ids;
}

// ターゲット色クラス
$("#target-1").addClass('bgc-yellow');
$("#target-2").addClass('bgc-green');
$("#target-3").addClass('bgc-blue');
$("#target-4").addClass('bgc-dark-blue');
$("#target-5").addClass('bgc-purple');
$("#target-6").addClass('bgc-pink');
$("#target-7").addClass('bgc-light-pink');
$("#target-8").addClass('bgc-orange');
$("#target-9").addClass('bgc-gray');
$("#target-other").addClass('bgc-gray');

$("[id=target-n-1]").addClass('bgc-yellow');
$("[id=target-n-2]").addClass('bgc-green');
$("[id=target-n-3]").addClass('bgc-blue');
$("[id=target-n-4]").addClass('bgc-dark-blue');
$("[id=target-n-5]").addClass('bgc-purple');
$("[id=target-n-6]").addClass('bgc-pink');
$("[id=target-n-7]").addClass('bgc-light-pink');
$("[id=target-n-8]").addClass('bgc-orange');
$("[id=target-n-9]").addClass('bgc-gray');
$("[id=target-n-other]").addClass('bgc-gray'); // ターゲットに紐づかないタスクが0番目のデフォルトカラーと同じになってしまうため暫定
</script>
<!--{/layout_footer}-->

<article class="task-view content-wrap color-white container-fluid">
  <section class="ttl-wrap alpha">
    <h1 class="page-title"><?= $func->f2str($user["id"] != $this->current_user->id, $user["name"] . "の") ?>タスク一覧</h1>
    <div>
      <a href="/mypage/work_tasks/create" class="btn btn-action btn-circle btn-size-normal">タスク追加 <i class="fas fa-plus"></i></a>
    </div>
  </section>
  <section class="task-wrap bg-box font-weight-bold container-fluid">
<? if (count($tabs) > 1): ?>
    <ul class="nav nav-tabs">
<? foreach ($tabs as $key => $name): ?>
      <li class="nav-item">
        <a href="#tab<?= $key ?>" data-toggle="tab" class="d-inline-block nav-link position-relative tab <?= $func->f2str($key == 0, "active") ?>" data-tab_name="tab<?= $key ?>">
          <span class="arrow"><?= $name ?><br class="d-block d-sm-none"></span>
        </a>
      </li>
<? endforeach; ?>
    </ul>
<? endif; ?>
    <div class="tab-content">
      <div id="tab0" class="tab-pane frame active">
        <section class="filter-wrap">
          <form action="/mypage/work_tasks" method="POST">
            <div class="refine-wrap">
              <h2 class="refine" id="box-<?= $alias ?>">タスクを絞り込む<div id="triangle" style="display:inline; float:right;">▼</div></h2>
              <div class="box" id="refine-box-<?= $alias ?>" style="display:none">
                <div class="row">
<? if (count($task_users) > 1): ?>
                  <div class="col-sm-5 col-lg-4">
                    <div class="person-choice">
                      <p class="ttl">担当者</p>
                      <div class="person-box">
  <? foreach ($task_users as $rc): ?>
                        <div class="person-line position-relative">
                          <input type="checkbox" class="js-filter-user_id" value="<?= $rc["user_id"] ?>" id="user-<?= $rc["user_id"] ?>">
                          <label for="user-<?= $rc["user_id"] ?>">
                            <dl class="d-flex align-items-center">
                              <dt>
    <? if ($rc['image']): ?>
                                <img src="https://store-account.01cloud.jp/images/<?= $rc['image'] ?>">
    <? else: ?>
                                <i class="fa fa-user-circle"></i>
    <? endif; ?>
                              </dt>
                              <dd><?= $rc['user_name'] ?></dd>
                            </dl>
                          </label>
                        </div>
  <? endforeach; ?>
                      </div>
                    </div>
                  </div>
<? endif; ?>
                  <div class="col-sm-<?= count($task_users) > 1 ? "7" : "12" ?> col-lg-8">
                    <div class="date-and-kpi">
                      <p class="ttl">期限・結果指標</p>
                      <div class="date">
                        <dl class="d-lg-flex align-items-center">
                          <dt>期限</dt>
                          <dd class="d-sm-flex align-items-center justify-content-between">
                            <div class="inputbox">
                              <input class="flatpickr-date js-filter-date-start" type="date" value="<?= $filter['date_start']?>" autocomplete="off">
                            </div>
                            <div class="inputbox">
                              <input class="flatpickr-date js-filter-date-end" type="date" value="<?= $filter['date_end']?>" autocomplete="off">
                            </div>
                          </dd>
                        </dl>
                        <div class="kpi">
                          <dl class="d-lg-flex align-items-center">
                            <dt>ロードマップ</dt>
                              <dd class="d-flex position-relative align-items-center justify-content-between w-100">
                               <select name="roadmap" id="roadmap" class="js-filter-roadmap" onchange="filterRoadmap();">
                                <option value="" selected>ロードマップを選択</option>
    <?  foreach ($milestones as $rc): ?>
        <? if($rc['id'] == $n_milestone['id'] && $filter['progress'] != "all"): ?>
                                  <option value="<?= $rc['id'] ?>" selected="selected"><?= $rc['title'] ?></option>
        <? else: ?>
                                  <option value="<?= $rc['id'] ?>"><?= $rc['title'] ?></option>
        <? endif; ?>
    <?  endforeach; ?>
                              </select>
                            </dd>
                          </dl>
                        </div>
                        <div class="kpi">
                          <dl class="d-lg-flex align-items-center">
                            <dt>結果指標</dt>
                            <dd class="d-flex position-relative align-items-center justify-content-between w-100">
                              <select name="kpi" class="js-filter-target_id" value>
                                <option value="" selected>結果指標を選択</option>
  <?  foreach ($target_list as $rc): ?>
                                <option class="roadmap" value="<?= $rc['id'] ?>" id="m_<?= $rc['milestone_id'] ?>"><?= $rc['title'] ?></option>
  <?  endforeach; ?>
                              </select>
                            </dd>
                          </dl>
                        </div>
                        <dl class="d-lg-flex align-items-center">
                          <dt>状態</dt>
                          <dd class="d-sm-flex align-items-center">
                            <div class="radio-group">
                              <input type="radio" id="js-filter-finished">
                              <label for="js-filter-finished">完了</label>
                            </div>
                            <div class="radio-group">
                              <input type="radio" id="js-filter-unfinished">
                              <label for="js-filter-unfinished">未完了</label>
                            </div>
                          </dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>
  
                <div class="align-items-center text-center">
                    <button onclick="clearFilter(); return false;" class="btn btn-action btn-circle btn-size-small">条件をクリア</button>
                </div>
  
              </div>
            </div>
          </form>
        </section>

        <section class="views">
          <div class="wrap">
            <div class="kpi-label row align-items-center">
<? foreach($targets as $key => $target): ?>
                <div class="col-xl-3 col-lg-6">
                    <p id="target-<?=$key ?>" class="taskname circle mb-10px"><?= $target['title'] ?></p>
                </div>
<? endforeach; ?>
            </div>
          </div>
        </section>
        
        <section class="sorts">
          <div class="sort-pulldowns">
  <?  foreach ($pulldowns as $name => $pulldown): ?>
            <select name="<?= $name ?>">
  <?    foreach ($pulldown as $key => $val): ?>
              <option value="<?= $key ?>" <?= $func->f2slct($_GET[$name] == $key) ?>><?= $val ?></option>
  <?    endforeach; ?>
            </select>
  <?  endforeach; ?>
          </div>
        </section>
        
        <section class="views">
  <!--        
            <p class="view-now">該当するタスク<span class="number">30</span>件中、<span class="number">1~20</span>件を表示</p>      
  -->
          <div class="view-task-wrap">            
  <?  foreach ($tasks as $task): ?>
            <div id="task_<?= $task["id"] ?>" class="parent wrap task-body">
              <? include VIEWPATH . 'mypage/work_tasks/partial/filter_items.php'; ?>
              <? include VIEWPATH . 'mypage/work_tasks/partial/detail_items.php'; ?>
              <div class="task-frame d-lg-flex align-items-center justify-content-between">
                <div class="taskname-box position-relative">
                  <p id="target-n-<?=$task['target_no'] ?>" class="taskname" data-task-id="<?= $task["id"] ?>"><?= $task['title'] ?></p>
                </div>
                <div class="task-detail d-none d-sm-flex align-items-center">
                  <p class="text-center">
                    <span class="font-11px d-inline-block padding-bottom-5px">実行日時</span>
                    <br class="d-none d-lg-block"><span class="js-task-do_date"><?= $task['do_date'] ? $func->format_date($task['do_date'], 'm月d日') : '未定' ?></span>
                  </p>
                  <p class="text-center">
                    <span class="font-11px d-inline-block padding-bottom-5px">期限</span>
                    <br class="d-none d-lg-block"><span class="js-task-deadline"><?= $task['deadline'] ? $func->format_date($task['deadline'], 'm月d日') : '未定' ?></span>
                  </p>
                  <p class="text-center">
                    <span class="font-11px d-inline-block padding-bottom-5px">状態</span>
                    <br class="d-none d-lg-block"><span class="js-task-status_name"><?= $progress_list[$task['progress']] ?></span>
                  </p>
                  <p class="edit-btn">
  <?    if ($task["progress"] == 0): ?>
                    <a href="javascript:void(0)" class="d-block open-modal-complete" data-task-id="<?= $task["id"] ?>">完了する</a>
  <?    endif; ?>
                  </p>
                  <p class="edit-btn">
                    <a href="/mypage/work_tasks/<?= $task['id'] ?>/modify" class="d-block"><img src="/html/img/task/pen.svg" alt="編集アイコン">編集</a>
                  </p>
                </div>
              </div>
            </div>
    <? endforeach; ?>
            <div class="parent wrap js-no-tasks" style="display: none;">
              <div class="task-frame d-lg-flex align-items-center justify-content-between">
                <div class="taskname-box position-relative">
                  <p class="text-center">表示できるタスクはありません。</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      <div id="tab1" class="tab-pane frame">
        <section class="views">
          <div id="sortarea" class="view-task-wrap">
<?  if ($sort_tasks): ?>
            <ol class="sortable">
<?    foreach ($sort_tasks as $task): ?>
              <? include VIEWPATH . "mypage/work_tasks/partial/sort_item.php"; ?>
<?    endforeach; ?>
            </ol>
<?  else: ?>
            <div class="parent wrap">
              <div class="task-frame d-lg-flex align-items-center justify-content-between">
                <div class="taskname-box position-relative">
                  <p class="text-center">表示できるタスクはありません。</p>
                </div>
              </div>
            </div>
<?  endif; ?>
          </div>
        </section>
      </div>
    </div>
  </section>
</article>

<!--{layout_modal}-->
<div class="micromodal-slide theme-black theme-long" id="modal-task-detail" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <div class="title-wrap">
        <h3 id="js-modal-title" class="taskname bgc-orange <!--done-->">広告の効果が出ていない広告の効果が出ていない</h3>
      </div>
      <div class="detail-wrap">
        <div class="items <!--done-->">
<!--
          <dl>
            <dt>所要時間</dt>
            <dd id="js-modal-required-time">3時間</dd>
          </dl>
-->
          <dl>
            <dt>実行日時</dt>
            <dd id="js-modal-do_date">9月10日 10:00 ～ 9月30日 12:00</dd>
          </dl>
          <dl>
            <dt>期限</dt>
            <dd id="js-modal-deadline">9月30日</dd>
          </dl>
          <dl>
            <dt>状態</dt>
            <dd id="js-modal-status_name">未着手</dd>
          </dl>
        </div>
        <p class="edit">
          <a href="task-edit.php" id="js-modal-href" class="btn btn-edit btn-circle"><img class="icon" src="/html/img/task/pen.svg" alt="編集"> 編集</a>
        </p>
      </div>
      <div class="tab-container">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#detail">詳細</a></li>
<!--
          <li class="nav-item">
            <a class="nav-link have-notice" data-toggle="tab" href="#comment">
              <span class="text">コメント</span>
              <span class="notice">10</span>
            </a>
          </li>
-->
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane active" id="detail">
          <div class="detail-box">
            <p id="js-modal-description" class="detail-text">詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク詳細タスク</p>
          </div>
          <div class="party">
            <p class="ttl">担当者</p>
            <div id="js-modal-users" class="person-wrap">
              <div class="person">
                <img src="/html/img/diary/people1.png">
                <p class="name">石原 さとみ</p>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane comment" id="comment">
          <div class="ttl-wrap d-flex align-items-center justify-content-between">
            <dl class="ttl-box d-flex align-items-center">
              <dt><img class="icon-opacity-8" src="/html/img/diary/comment.svg" alt="コメント"></dt>
              <dd>コメント</dd>
            </dl>
            <a class="reload"><img class="icon-opacity-8" src="/html/img/diary/reload.svg" alt="リロード"></a>
          </div>
          <div class="box">
            <p class="icon-images"><img src="/html/img/diary/people1.png" alt="○○さんのアイコン"></p>
            <div class="content">
              <div class="wrap">
                <p class="name">石原 さとみ</p>
                <p class="time">8/27 18:20</p>
              </div>
              <div class="text-wrap">
                <p class="text">カレー飲ませて！
                  <br>はやく～はやく～はやく～はやく～はやく～はやく～はやく～はやく～はやく～
                  <br>はやく～はやく～はやく～はやく～はやく～はやく～はやく～はやく～はやく～
                </p>
                <div class="reactions">
                  <p><img class="icon-opacity-8" src="/html/img/reaction/face2.svg" alt="リアクション"></p>
                  <p class="do-reaction">リアクションをする</p>
                </div>
                <div class="reactions-item">
                  <ul class="reactions-item-wrap">
                    <li><img class="img" src="/html/img/reaction/thumbup.svg" alt="いいね"></li>
                    <li><img class="img" src="/html/img/reaction/happy.svg" alt="嬉しい"></li>
                    <li><img class="img" src="/html/img/reaction/sad.svg" alt="悲しい"></li>
                    <li><img class="img" src="/html/img/reaction/heart.svg" alt="ハートマーク"></li>
                    <li><img class="img" src="/html/img/reaction/check.svg" alt="了解"></li>
                    <li><img class="img" src="/html/img/reaction/thumbup.svg" alt="BAD"></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="box">
            <p class="icon-images"><img src="/html/img/diary/people2.png" alt="○○さんのアイコン"></p>
            <div class="content">
              <div class="wrap">
                <p class="name">竹野内 豊</p>
                <p class="time">8/27 18:00</p>
              </div>
              <div class="text-wrap">
                <p class="text">今日の晩御飯はカレーです。</p>
                <div class="reactions">
                  <p><img class="icon-opacity-8" src="/html/img/reaction/face2.svg"></p>
                  <p>リアクションをする</p>
                </div>
                <div class="reactions-item">
                  <ul class="reactions-item-wrap">
                    <li><img class="img" src="/html/img/reaction/thumbup.svg" alt="いいね"></li>
                    <li><img class="img" src="/html/img/reaction/happy.svg" alt="嬉しい"></li>
                    <li><img class="img" src="/html/img/reaction/sad.svg" alt="悲しい"></li>
                    <li><img class="img" src="/html/img/reaction/heart.svg" alt="ハートマーク"></li>
                    <li><img class="img" src="/html/img/reaction/check.svg" alt="了解"></li>
                    <li><img class="img" src="/html/img/reaction/thumbup.svg" alt="BAD"></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="box pb-3">
            <form method="post" action="#" class="form-wrap w-100 d-flex align-items-start">
              <textarea class="comment-area auto-resize" type="text" name="コメント" placeholder="コメントを入力"></textarea>
              <button class="d-block" type="submit">送信</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--{/layout_modal}-->

<!--{layout_modal}-->
<div class="micromodal-slide theme-black" id="modal-task-complete" aria-hidden="true">
  <div class="modal-overlay" tabindex="-1" data-micromodal-close>
    <div class="modal-container" role="dialog" aria-modal="true">
      <p class="modal-close-area">
        <button class="modal-close" tabindex="-1" aria-label="閉じる" data-micromodal-close></button>
      </p>
      <p class="modal-title">実績入力</p>
      <form method="post" id="js-modal-c-href" class="form-basic">
        <dl>
          <dt>タスク名</dt>
          <dd>
            <input class="form-control" type="text" name="title" id="js-modal-c-title" readonly />
          </dd>
        </dl>
        <dl>
          <dt>日付</dt>
          <dd><input class="form-control flatpickr-date-modal max-w-150px" type="date"  name="do_date" value="" autocomplete="off"></dd>
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
