<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>幻想排行榜 - 数据查询</title>
    <script src="jquery-3.6.4.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link rel="stylesheet" href="index.css">

    <script>
        $(document).ready(function() {
            var serverSelect = $('#server');
            var selectedZone = $('#zone').val();
            var currentPage = 1;

            // 显示加载状态
            function showLoading() {
                $('#result').html('<div class="loading"><i class="fas fa-spinner"></i><p>正在加载数据...</p></div>');
            }

            // 初始化时获取初始服务器列表
            function loadServers(zone) {
                var queryType = $('#query_type').val();
                serverSelect.html('<option value="">正在加载...</option>');
                
                $.ajax({
                    type: 'GET',
                    url: 'get_servers.php',
                    data: { zone: zone, query_type: queryType },
                    dataType: 'json',
                    success: function(data) {
                        serverSelect.empty();
                        serverSelect.append($('<option>', {
                            value: '',
                            text: '🌐 全部服务器'
                        }));
                        data.sort(function(a, b) {
                           return parseInt(a.substring(1)) - parseInt(b.substring(1));
                        }).forEach(function(server) {
                            serverSelect.append($('<option>', {
                                value: server,
                                text: server
                            }));
                        });
                        
                        // If Select2 is already initialized, destroy it first
                        if (serverSelect.data('select2')) {
                            serverSelect.select2('destroy');
                        }
                        
                        // Initialize Select2
                        serverSelect.select2({
                            placeholder: "🔍 点击或输入关键字搜索",
                            allowClear: true
                        });
                        
                    },
                    error: function(error) {
                        console.log("Error:", error);
                        serverSelect.html('<option value="">加载失败</option>');
                    }
                });
            }

            // AJAX查询排行榜数据
            function queryRanking(page = 1) {
                showLoading();
                
                currentPage = page;
                var formData = $('#ranking-form').serialize() + '&page=' + currentPage;
                
                
                $.ajax({
                    type: 'GET',
                    url: 'query_ranking.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // console.log("排行榜请求结果:", response); // 新增：输出请求结果到控制台
                        if (response.success) {
                            $('#result').html(response.html);
                            // ===== 区号范围兜底过滤 =====
                            const raw  = $('#zone_range').val().trim();
                            const set  = raw ? parseRange(raw) : null;
                            if (set && set.size) {
                                $('#result table tbody tr').each(function () {
                                    // 从行里取区服文字  S14、S7 ...
                                    const serverTxt = $(this).find('.server-name').text();   // 你页面已有这个节点
                                    const num       = parseInt(serverTxt.replace(/\D/g,''),10);
                                    $(this).toggle(set.has(num));   // 不在范围的行直接隐藏
                                });
                            }
                            renderPagination(response.currentPage, response.totalPages);
                            bindSortEvents();
                            // 新增：解析阵容属性
                            heroesAttrList = [];
                            $('.hero-icon').each(function(){
                                var attrs = $(this).data('attrs');
                                if (attrs) {
                                    heroesAttrList.push(attrs);
                                }
                            });
                        } else {
                            $('#result').html('<div class="no-data"><i class="fas fa-exclamation-triangle"></i><p>' + response.error + '</p></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#result').html('<div class="no-data"><i class="fas fa-exclamation-triangle"></i><p>查询失败，请稍后重试</p></div>');
                        console.log('AJAX Error:', error);
                    }
                });
            }

            // 渲染分页控件
            function renderPagination(currentPage, totalPages) {
                if (totalPages <= 1) {
                    $('#pagination').empty();
                    return;
                }

                var paginationHtml = '';
                
                // 上一页
                paginationHtml += `<a href="#" class="page-link ${currentPage == 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">&laquo; 上一页</a>`;

                // 页码
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    paginationHtml += '<a href="#" class="page-link" data-page="1">1</a>';
                    if (startPage > 2) {
                        paginationHtml += '<span class="disabled">...</span>';
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    if (i == currentPage) {
                        paginationHtml += `<span class="current">${i}</span>`;
                    } else {
                        paginationHtml += `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
                    }
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHtml += '<span class="disabled">...</span>';
                    }
                    paginationHtml += `<a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a>`;
                }
                
                // 下一页
                paginationHtml += `<a href="#" class="page-link ${currentPage == totalPages ? 'disabled' : ''}" data-page="${parseInt(currentPage) + 1}">下一页 &raquo;</a>`;

                $('#pagination').html(paginationHtml);
            }

            // 绑定排序事件
            function bindSortEvents() {
                $('.sortable').off('click').on('click', function() {
                    var column = $(this).data('column');
                    var currentOrder = $(this).data('order') || 'asc';
                    var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                    
                    // 更新排序图标
                    $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
                    $(this).find('i').removeClass('fa-sort').addClass(newOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    $(this).data('order', newOrder);
                    
                    // 这里可以添加AJAX排序逻辑
                });
            }

            // 初始化
            loadServers(selectedZone);
            
            // 页面加载时执行一次查询
            queryRanking(1);

            // 监听 zone 变化
            $('#zone').on('change', function() {
                var selectedZone = $(this).val();
                loadServers(selectedZone);
                // 区域变化时自动查询
                setTimeout(function() {
                    queryRanking(1);
                }, 500);
            });

            // 监听服务器变化
            $('#server').on('change', function() {
                queryRanking(1);
            });

            // 监听复选框变化
            $('input[type="checkbox"]').on('change', function() {
                queryRanking(1);
            });

            // 监听排序选项变化
            $('#sort_by, #sort_order').on('change', function() {
                queryRanking(1);
            });

            // 监听查询类型变化
            $('#query_type').on('change', function() {
                var queryType = $(this).val();
                var selectedZone = $('#zone').val();
                toggleQueryOptions(queryType);
                loadServers(selectedZone);
                setTimeout(function() {
                    queryRanking(1);
                }, 500);
            });

            // 添加对搜索框的监听
            var searchTimeout;
            $('#search_player').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    queryRanking(1);
                }, 500); // 500毫秒延迟，避免频繁查询
            });

            $('#search_team').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    queryRanking(1);
                }, 500); // 500毫秒延迟，避免频繁查询
            });

            // 分页点击事件
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled') || $(this).hasClass('current')) {
                    return;
                }
                var page = $(this).data('page');
                queryRanking(page);
            });

            // 切换查询选项显示
            function toggleQueryOptions(queryType) {
                if (queryType === 'team') {
                    // 显示军团选项，隐藏玩家选项
                    $('.player-option').hide();
                    $('.team-option').show();
                    $('.player-sort').hide();
                    $('.team-sort').show();
                    
                    // 设置军团查询的默认排序
                    $('#sort_by').val('popularity');
                    
                    // 取消选中玩家专有的显示选项
                    $('#show_uid, #show_level, #show_fame, #show_updata_time').prop('checked', false);
                } else {
                    // 显示玩家选项，隐藏军团选项
                    $('.player-option').show();
                    $('.team-option').hide();
                    $('.player-sort').show();
                    $('.team-sort').hide();
                    
                    // 设置玩家查询的默认排序
                    $('#sort_by').val('power');
                    
                    // 取消选中军团专有的显示选项
                    $('#show_tid, #show_team_level, #show_nofpeople, #show_percapita').prop('checked', false);
                }
            }

            // 阻止表单默认提交，改为AJAX查询
            $('#ranking-form').on('submit', function(e) {
                e.preventDefault();
                queryRanking();
            });
            
            
            /* ================= 左右切换 start ================= */
$(document).on('click', '#hero-prev, #hero-next', function () {
    const $cur = $('#hero-attr-modal').data('triggerIcon');
    if (!$cur || !$cur.length) return;

    const dir = this.id === 'hero-prev' ? -1 : 1;
    let pos = +$cur.data('pos') + dir;
    if (pos < 1) pos = 6; if (pos > 6) pos = 1;

    const $nextIcon = $cur.closest('tr').find(`.hero-icon[data-pos="${pos}"]`);
    if (!$nextIcon.length) return;

    // 更新“当前头像”记录
    $('#hero-attr-modal').data('triggerIcon', $nextIcon);

    const uid   = $nextIcon.data('uid');
    const fid   = $nextIcon.attr('alt').replace('英雄', '');
    const hName = heroNameMap[fid] || (pos + '号英雄');

    // 直接请求 + 填充，不 hide 也不 trigger
    $.getJSON('get_hero_attr.php', { uid, pos }, res => {
        fillHeroPanel(pos, hName, res.success ? res.data : null, $nextIcon.attr('src'));
    }).fail(() => {
        fillHeroPanel(pos, hName, null, $nextIcon.attr('src'));
    });
});
/* ===== 区号范围实时过滤 ===== */
var $zoneRange = $('#zone_range');
var zoneRangeTimer;
$zoneRange.on('input', function () {
    clearTimeout(zoneRangeTimer);
    zoneRangeTimer = setTimeout(function () {
        console.log('zone_range input 触发');   // 控制台先看事件进没进来
        filterServerByRange();   // 过滤下拉框
        queryRanking(1);         // 立即重新查表
    }, 300);
});

function filterServerByRange () {
    var raw = $zoneRange.val().trim();
    var set = parseRange(raw);
    $('#server option').each(function () {
        var txt = $(this).text();          // S14、S7 ...
        var num = parseInt(txt.substring(1), 10);
        if (!raw || set.size === 0 || $(this).val() === '') {
            $(this).show();
        } else {
            $(this).toggle(set.has(num));
        }
    });
    // 当前选中项被隐藏时自动切回“全部服务器”
    if ($('#server').val() && $('#server option:selected').is(':hidden')) {
        $('#server').val('').trigger('change');
    }
}

function parseRange (str) {
    var set = new Set();
    if (!str) return set;
    str.split(',').forEach(p => {
        p = p.trim();
        if (/^\d+$/.test(p)) {
            set.add(parseInt(p, 10));
        } else if (/^(\d+)-(\d+)$/.test(p)) {
            var [, a, b] = p.match(/^(\d+)-(\d+)$/);
            var start = parseInt(a, 10), end = parseInt(b, 10);
            for (var i = Math.min(start, end); i <= Math.max(start, end); i++) set.add(i);
        }
    });
    return set;
}
/* ================= 左右切换 start ================= */
/* ================= 左右切换 end ================= */
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-trophy"></i> 幻想排行榜</h1>
            <p>玩家数据查询</p>
        </div>

        <div class="search-panel">
            <form id="ranking-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="query_type"><i class="fas fa-list"></i> 查询类型</label>
                        <select id="query_type" name="query_type">
                            <option value="player" selected>👤 玩家排行榜</option>
                            <option value="team">🛡️ 军团排行榜</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="zone"><i class="fas fa-server"></i> 服务器大区</label>
                        <select id="zone" name="zone">
                            <option value="1" selected>🇨🇳 国内服</option>
                            <option value="2">🌍 国际-中文服</option>
                            <option value="3">🌎 国际-英文服</option>
                            <option value="all">🌐 所有服</option>
                            <!--<option value="0">🧪 测试服</option>-->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="server"><i class="fas fa-map-marker-alt"></i> 所在区服</label>
                        <select id="server" name="server">
                            <option value="">正在加载...</option>
                        </select>
                    </div>
                                        <!-- 新增：区号范围 -->
                    <div class="form-group player-option team-option">
                        <label for="zone_range"><i class="fas fa-filter"></i> 区号范围</label>
                        <input type="text"
                               id="zone_range"
                               name="zone_range"
                               placeholder="例：1-20 或 5,7,9-12">
                    </div>

                    <div class="form-group player-option">
                        <label for="search_player"><i class="fas fa-search"></i> 搜索玩家</label>
                        <input type="text" id="search_player" name="search_player" placeholder="输入玩家昵称或UID">
                    </div>

                    <div class="form-group team-option" style="display: none;">
                        <label for="search_team"><i class="fas fa-search"></i> 搜索军团</label>
                        <input type="text" id="search_team" name="search_team" placeholder="输入军团名称或TID">
                    </div>
                </div>

                <div class="options-section">
                    <div class="options-title"><i class="fas fa-eye"></i> 显示选项</div>
                    <div class="checkbox-grid" id="display-options">
                        <!-- 玩家排行榜显示选项 -->
                        <div class="checkbox-item player-option">
                            <input type="checkbox" id="show_formation" name="show_formation" value="1" checked>
                            <label for="show_formation"><i class="fas fa-th"></i> 玩家阵容</label>
                        </div>
                        <div class="checkbox-item player-option">
                            <input type="checkbox" id="show_uid" name="show_uid" value="1">
                            <label for="show_uid"><i class="fas fa-id-card"></i> UID</label>
                        </div>
                        <div class="checkbox-item player-option">
                            <input type="checkbox" id="show_level" name="show_level" value="1">
                            <label for="show_level"><i class="fas fa-level-up-alt"></i> 等级</label>
                        </div>
                        <div class="checkbox-item player-option">
                            <input type="checkbox" id="show_fame" name="show_fame" value="1">
                            <label for="show_fame"><i class="fas fa-star"></i> 声望</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="show_server" name="show_server" value="1" checked>
                            <label for="show_server"><i class="fas fa-server"></i> 区服</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="abbreviate" name="abbreviate" value="1">
                            <label for="abbreviate"><i class="fas fa-compress-alt"></i> 区号简写</label>
                        </div>
                        <div class="checkbox-item player-option">
                            <input type="checkbox" id="show_updata_time" name="show_updata_time" value="1">
                            <label for="show_updata_time"><i class="fas fa-clock"></i> 更新时间</label>
                        </div>
                        
                        <!-- 军团排行榜显示选项 -->
                        <div class="checkbox-item team-option" style="display: none;">
                            <input type="checkbox" id="show_tid" name="show_tid" value="1">
                            <label for="show_tid"><i class="fas fa-id-badge"></i> 军团ID</label>
                        </div>
                        <div class="checkbox-item team-option" style="display: none;">
                            <input type="checkbox" id="show_team_level" name="show_team_level" value="1">
                            <label for="show_team_level"><i class="fas fa-level-up-alt"></i> 军团等级</label>
                        </div>
                        <div class="checkbox-item team-option" style="display: none;">
                            <input type="checkbox" id="show_nofpeople" name="show_nofpeople" value="1">
                            <label for="show_nofpeople"><i class="fas fa-users"></i> 人数</label>
                        </div>
                        <div class="checkbox-item team-option" style="display: none;">
                            <input type="checkbox" id="show_percapita" name="show_percapita" value="1">
                            <label for="show_percapita"><i class="fas fa-chart-line"></i> 人均繁荣</label>
                        </div>

                    </div>
                </div>

                <div class="options-section">
                    <!--<div class="options-title"><i class="fas fa-sort"></i> 排序选项</div>-->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="sort_by"><i class="fas fa-sort-amount-down"></i> 排序</label>
                            <select id="sort_by" name="sort_by">
                                <!-- 玩家排行榜排序选项 -->
                                <option value="power" selected class="player-sort">⚡ 战力排序</option>
                                <option value="fame" class="player-sort">⭐ 声望排序</option>
                                <option value="level" class="player-sort">📈 等级排序</option>
                                <option value="server" class="player-sort">🔢 区号排序</option>
                                <option value="name" class="player-sort">📝 名称排序</option>
                                <option value="uid" class="player-sort">🆔 UID排序</option>
                                
                                <!-- 军团排行榜排序选项 -->
                                <option value="popularity" class="team-sort" style="display: none;">🏆 繁荣度排序</option>
                                <option value="level" class="team-sort" style="display: none;">📈 等级排序</option>
                                <option value="nofpeople" class="team-sort" style="display: none;">👥 人数排序</option>
                                <option value="server" class="team-sort" style="display: none;">🔢 服务器排序</option>
                                <option value="percapita" class="team-sort" style="display: none;">📊 人均繁荣排序</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sort_order"><i class="fas fa-sort"></i> 排序方式</label>
                            <select id="sort_order" name="sort_order">
                                <option value="desc" selected>📉 降序 (高到低)</option>
                                <option value="asc">📈 升序 (低到高)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <h1 class="search-btn" style="text-align: center;">下拉查看结果</h1>
                <!--<button type="submit" class="search-btn">-->
                <!--    <i class="fas fa-search"></i> 下拉查看结果-->
                <!--</button>-->
            </form>
        </div>

        <!-- 查询结果显示区域 -->
        <div class="results-container">
            <div id="result">
                <div class="no-data">
                    <i class="fas fa-search"></i>
                    <p>请选择查询条件并点击查询按钮</p>
                    <small>支持实时筛选和无刷新查询</small>
                </div>
            </div>
            <div id="pagination" class="pagination"></div>
        </div>
    </div>
</body>
</html>

<!-- 英雄属性悬浮窗结构和样式（复用 hero-stats-card） -->
<div class="hero-stats-card" id="hero-attr-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;max-width:600px;background:#fff;border-radius:15px;padding:25px;box-shadow:0 8px 25px rgba(0,0,0,.1);">
    <h3><i class="fas fa-user"></i> <span id="hero-title">英雄属性</span></h3>
    <div class="info-item"><span class="info-label"><i class="fas fa-heart"></i> 生命:</span><span class="info-value highlight" id="h-hp">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-paw"></i> 攻击:</span><span class="info-value highlight" id="h-attack">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-shield-alt"></i> 防御:</span><span class="info-value highlight" id="h-defense">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-jedi"></i> 必杀:</span><span class="info-value highlight" id="h-skill">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-plus"></i> 追加攻击:</span><span class="info-value highlight" id="h-addattack">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-plus"></i> 追加防御:</span><span class="info-value highlight" id="h-adddefense">?</span></div>
    <div class="info-item"><span class="info-label"><i class="fas fa-fist-raised"></i> 战斗力:</span><span class="info-value highlight" id="h-power">?</span></div>

    <div style="margin-top:20px;display:flex;justify-content:space-between;gap:15px;">
        <div style="flex:1;text-align:center;">
            <div style="font-weight:600;color:#555;margin-bottom:8px;">装备</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;grid-auto-flow:row;" id="equip-grid"></div>
        </div>
        <div style="flex:1;text-align:center;">
            <div style="font-weight:600;color:#555;margin-bottom:8px;">首饰</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;grid-auto-flow:row;" id="jewelry-grid"></div>
        </div>
        <div style="flex:1;text-align:center;">
            <div style="font-weight:600;color:#555;margin-bottom:8px;">符石</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;grid-auto-flow:row;" id="runes-grid"></div>
        </div>
    </div>

    <div style="margin-top:20px;display:flex;align-items:center;justify-content:space-between;">
        <!-- 上一个英雄 -->
        <button id="hero-prev" class="hero-nav-inner" title="上一个英雄">
            <i class="fas fa-chevron-left"></i>
        </button>
    
        <!-- 关闭（居中） -->
        <button id="close-hero-modal" class="hero-close">关闭</button>
    
        <!-- 下一个英雄 -->
        <button id="hero-next"  class="hero-nav-inner" title="下一个英雄">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
/* 英雄编号→名字对照表（根目录） */
let heroNameMap = {};
fetch('hero_name.json')
  .then(res => { if(!res.ok) throw new Error('404'); return res.json(); })
  .then(json => { heroNameMap = json; })
  .catch(err => { console.warn('英雄名字对照表加载失败', err); heroNameMap = {}; });

let heroesAttrList = [];
$(document).on('mousedown', function(e){
    var modal = $('#hero-attr-modal');
    if(modal.is(':visible')){
        if(!$(e.target).closest('#hero-attr-modal').length && !$(e.target).hasClass('hero-icon')){
            modal.hide();
        }
    }
});

$(document).on('click', '.hero-icon', function(){
    // ★ 记下是哪一个头像触发的，供左右按钮使用
    $('#hero-attr-modal').data('triggerIcon', $(this));
    var $img  = $(this);
    var uid   = $(this).data('uid');
    var pos   = $(this).data('pos');
    var fid   = $(this).attr('alt').replace('英雄','');
    var hName = heroNameMap[fid] || (pos + '号英雄');
    
        // 取出头像地址
    var heroIconSrc = $img.attr('src');
    // 把原来的 <i class="fas fa-user"> 换成 <img>
    // 1. 先清空标题行里所有图标（i 和 img）
    $('#hero-title').siblings('i, img.hero-head-icon').remove();
    // 2. 再插当前英雄头像
    $('#hero-title').before(
        `<img class="hero-head-icon" src="${heroIconSrc}" style="width:30px;height:30px;border-radius:30%;vertical-align:middle;margin-right:6px;">`
    );
    
    $.ajax({
        url: 'get_hero_attr.php',
        type: 'GET',
        data: { uid: uid, pos: pos },
        dataType: 'json',
        success: function(res) {
            if(res.success && res.data){
                showHeroAttrs(pos, hName, res.data);
                renderEquipSlots(res.data.equips   || '0000-0000-0000-0000-0000-0000');
                renderJewelrySlots(res.data.jewelrys || '0000-0000-0000-0000-0000-0000');
                renderRunesSlots(res.data.runes    || '0000-0000-0000-0000-0000-0000');
            }else{
                showHeroAttrs(pos, hName, null);
                renderEquipSlots('0000-0000-0000-0000-0000-0000');
                renderJewelrySlots('0000-0000-0000-0000-0000-0000');
                renderRunesSlots('0000-0000-0000-0000-0000-0000');
            }
        },
        error: function(){
            showHeroAttrs(pos, hName, null);
            renderEquipSlots('0000-0000-0000-0000-0000-0000');
            renderJewelrySlots('0000-0000-0000-0000-0000-0000');
            renderRunesSlots('0000-0000-0000-0000-0000-0000');
        }
    });
});

$('#close-hero-modal').on('click', function(){
    $('#hero-attr-modal').hide();
});

function renderEquipSlots(equipStr){
    const ids = equipStr.split('-');
    const grid = $('#equip-grid');
    grid.empty();
    const order = [0, 3, 1, 4, 2, 5];
    for(let i of order){
        const id = ids[i] || '0000';
        const imgSrc = `img/equip/${id}.jpg`;
        const img = $(`<img src="${imgSrc}" style="width:36px;height:36px;border-radius:4px;object-fit:cover;" onerror="this.src='img/equip/0000.jpg'">`);
        grid.append(img);
    }
}
function renderJewelrySlots(jewelryStr){
    const ids = jewelryStr.split('-');
    const grid = $('#jewelry-grid');
    grid.empty();
    const order = [0, 3, 1, 4, 2, 5];
    for(let i of order){
        const id = ids[i] || '0000';
        const imgSrc = `img/jewelry/${id}.jpg`;
        const img = $(`<img src="${imgSrc}" style="width:36px;height:36px;border-radius:4px;object-fit:cover;" onerror="this.src='img/jewelry/0000.jpg'">`);
        grid.append(img);
    }
}
function renderRunesSlots(runesStr){
    const ids = runesStr.split('-');
    const grid = $('#runes-grid');
    grid.empty();
    const order = [0, 3, 1, 4, 2, 5];
    for(let i of order){
        const id = ids[i] || '0000';
        const imgSrc = `img/runes/${id}.jpg`;
        const img = $(`<img src="${imgSrc}" style="width:36px;height:36px;border-radius:4px;object-fit:cover;" onerror="this.src='img/runes/0000.jpg'">`);
        grid.append(img);
    }
}

function showHeroAttrs(pos, hName, attrs) {
    const $img = $('#hero-attr-modal').data('triggerIcon');
    const iconSrc = $img.attr('src');
    fillHeroPanel(pos, hName, attrs, iconSrc);
    // 如果面板没打开再打开；已打开就什么都不做
    if (!$('#hero-attr-modal').is(':visible')) {
        $('#hero-attr-modal').show();
    }
}

function fillHeroPanel(pos, hName, attrs, heroIconSrc) {
    $('#hero-attr-modal').show();

    /* 1. 头像 */
    $('#hero-title').siblings('i, img.hero-head-icon').remove();
    $('#hero-title').before(
        `<img class="hero-head-icon" src="${heroIconSrc}" style="width:30px;height:30px;border-radius:30%;vertical-align:middle;margin-right:6px;">`
    );

    /* 2. 名称 + [n](所属用户名字)   —— 改造这里即可 */
    // 先从触发悬浮窗的那枚头像里拿到所属玩家名
    const $triggerIcon = $('#hero-attr-modal').data('triggerIcon');   // 就是 .hero-icon
    const userName = $triggerIcon.closest('tr')
                             .find('.player-name')
                             .contents()      // 所有子节点（文本节点 + 元素节点）
                             .filter(function () {
                                 return this.nodeType === 3; // 只保留文本节点
                             })
                             .text()
                             .trim() || '未知玩家';

    $('#hero-title').html(
      `<span class="hero-name-block">${hName} [${pos}]</span>(${userName})`
    );
    // $('#hero-title').html(
    //   `<span class="no-wrap-all">${hName} [${pos}](${userName})</span>`
    // );

    // 属性
    const fmt = v => (v == null || v === '' || isNaN(v)) ? '?' :
        (v = Number(v), v >= 1e8 ? (v / 1e8).toFixed(2) + '亿' :
            v >= 1e4 ? (v / 1e4).toFixed(2) + '万' : String(v));
    $('#h-hp').text(attrs ? fmt(attrs.hp) : '?');
    $('#h-attack').text(attrs ? fmt(attrs.attack) : '?');
    $('#h-defense').text(attrs ? fmt(attrs.defense) : '?');
    $('#h-skill').text(attrs ? fmt(attrs.skill) : '?');
    $('#h-addattack').text(attrs ? fmt(attrs.addattack) : '?');
    $('#h-adddefense').text(attrs ? fmt(attrs.adddefense) : '?');
    $('#h-power').text(attrs ? fmt(attrs.power) : '?');

    // 装备
    renderEquipSlots(attrs?.equips   || '0000-0000-0000-0000-0000-0000');
    renderJewelrySlots(attrs?.jewelrys || '0000-0000-0000-0000-0000-0000');
    renderRunesSlots(attrs?.runes    || '0000-0000-0000-0000-0000-0000');
}
</script>