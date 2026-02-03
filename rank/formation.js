/************************************************************
 * 0. 合并开关 + 映射表
 ***********************************************************/
const mergeSwitch  = document.getElementById('mergeSwitch');   // 页面 checkbox
const MERGE_KEY    = 'heroMergeOn';                          // localStorage 键
let heroMergeMap   = {};                                     // 映射表

// 读取用户上次选择
mergeSwitch.checked = localStorage.getItem(MERGE_KEY) === 'true';

// 加载映射表（开关关闭时留空）
async function loadMergeMap() {
  if (!mergeSwitch.checked) { heroMergeMap = {}; return; }
  try {
    heroMergeMap = await (await fetch('/rank/hero_merge.json')).json();
  } catch { heroMergeMap = {}; }
}

// 统一转映射（开关关闭时原样返回）
const toMerge = id => mergeSwitch.checked ? (heroMergeMap[id] || id) : id;

// 开关变化时保存并重绘
mergeSwitch.addEventListener('change', () => {
  localStorage.setItem(MERGE_KEY, mergeSwitch.checked);
  loadMergeMap().then(() => loadData());
});

/************************************************************
 * 1. 工具函数
 ***********************************************************/
// 头像：jpg → png → 0000.jpg 链式 fallback
const heroIcon = id =>
  `<img src="/rank/img/${id}.jpg"
       onerror="if(this.dataset.f===1){this.src='/rank/img/0000.jpg';}else{this.dataset.f=1;this.src=this.src.replace('.jpg','.png');}"
       alt="${id}" width="40" height="40" style="border-radius:6px;border:1px solid var(--border)">`;

// 对象按值降序
const sortObj = obj =>
  Object.entries(obj).sort((a, b) => b[1] - a[1]);

/************************************************************
 * 2. 核心：根据原始 formation 数组做统计（含合并逻辑）
 ***********************************************************/
function analyse(list) {
  const formationCount = {};
  const firstOrderMap  = {};
  const heroCount      = {};
  let total = 0;

  list.forEach(raw => {
    const f = raw.trim();
    const parts = f.split('-');
    if (parts.length !== 6) return;

    const filtered = parts.filter(v => v !== '0000');
    if (!filtered.length) return;

    // ① 用于统计 key：先映射→再排序
    const forKey = filtered.map(toMerge).sort((a, b) => a - b);
    const key    = forKey.join('-');

    // ② 用于头像展示：只映射英雄号，保持原始先后顺序
    const forIcon = filtered.map(toMerge);

    formationCount[key] = (formationCount[key] || 0) + 1;
    if (!firstOrderMap[key]) firstOrderMap[key] = forIcon;

    forKey.forEach(h => { heroCount[h] = (heroCount[h] || 0) + 1; });
    total++;
  });

  return { formationCount, firstOrderMap, heroCount, total };
}

/************************************************************
 * 3. 渲染  –  阵容表只显示前 50
 ***********************************************************/
function render({ formationCount, firstOrderMap, heroCount, total }) {
  // 阵容表：前 50 条
  const fArr = sortObj(formationCount).slice(0, 50);
  const ftbody = document.querySelector('#formationTable tbody');
  ftbody.innerHTML = fArr.map(([key, cnt], idx) => {
    const heroes = firstOrderMap[key];
    const rate   = (cnt / total * 100).toFixed(2);
    return `
      <tr>
        <td>${idx + 1}</td>
        <td class="hero-line">${heroes.map(h => heroIcon(h)).join('')}</td>
        <td class="count">${cnt.toLocaleString()}</td>
        <td class="rate">${rate}%</td>
      </tr>`;
  }).join('');

  // 英雄网格：全部（想限制同样 .slice(0, N)）
  const hArr = sortObj(heroCount);
  const hgrid = document.getElementById('heroGrid');
  hgrid.innerHTML = hArr.map(([h, cnt], idx) => {
    const rate = (cnt / total * 100).toFixed(2);
    return `
      <div class="hero-card">
        <div class="seq">${idx + 1}</div>
        ${heroIcon(h)}
        <div class="num">${cnt.toLocaleString()}人</div>
        <div class="pct">${rate}%</div>
      </div>`;
  }).join('');
}

/************************************************************
 * 4. 拉数据 + 过滤事件
 ***********************************************************/
let globalRaw = [];

async function loadData() {
  document.getElementById('loading').style.display = 'flex';

  const zs = document.querySelector('input[name="zs"]').value || 1;
  const ze = document.querySelector('input[name="ze"]').value || 10;

  // 拼接 zone[] 参数（无空格）
  const zoneArr = Array.from(document.querySelectorAll('input[name="zone[]"]:checked'))
                       .map(ch => `zone[]=${ch.value}`)
                       .join('&');

  const qs = `zs=${zs}&ze=${ze}&${zoneArr}`;
  console.log('请求', qs);

  const res = await fetch(`/rank/formationapi.php?${qs}`);
  globalRaw = await res.json();
  console.log('返回条数', globalRaw.length);

  const data = analyse(globalRaw);
  render(data);
  document.getElementById('loading').style.display = 'none';
}

// 阻止表单默认提交 + 绑定查询按钮
document.getElementById('filterForm').addEventListener('submit', e => {
  e.preventDefault();
  loadData();
});

// 首次：先读合并表再加载数据
loadMergeMap().then(() => loadData());