function drawBarChart(canvas, labels, data, color) {
  if (!canvas || !canvas.getContext) return;
  const ctx = canvas.getContext('2d');
  const width = canvas.width = canvas.offsetWidth;
  const height = canvas.height = canvas.offsetHeight;
  ctx.clearRect(0, 0, width, height);

  const padding = 32;
  const chartW = width - padding * 2;
  const chartH = height - padding * 2;
  const max = Math.max(...data, 0);

  ctx.strokeStyle = '#e5e7eb';
  ctx.beginPath();
  ctx.moveTo(padding, padding);
  ctx.lineTo(padding, height - padding);
  ctx.lineTo(width - padding, height - padding);
  ctx.stroke();

  if (max === 0) {
    ctx.fillStyle = '#9ca3af';
    ctx.font = '14px Poppins, sans-serif';
    ctx.fillText('Aucune donnee', padding + 8, height / 2);
    return;
  }

  const barGap = 6;
  const barW = (chartW - barGap * (data.length - 1)) / data.length;

  data.forEach((value, i) => {
    const barH = (value / max) * (chartH - 10);
    const x = padding + i * (barW + barGap);
    const y = height - padding - barH;
    ctx.fillStyle = color;
    ctx.fillRect(x, y, barW, barH);
  });
}

function drawLineChart(canvas, labels, series, colors) {
  if (!canvas || !canvas.getContext) return;
  const ctx = canvas.getContext('2d');
  const width = canvas.width = canvas.offsetWidth;
  const height = canvas.height = canvas.offsetHeight;
  ctx.clearRect(0, 0, width, height);

  const padding = 32;
  const chartW = width - padding * 2;
  const chartH = height - padding * 2;
  const max = Math.max(...series.flat(), 0);

  ctx.strokeStyle = '#e5e7eb';
  ctx.beginPath();
  ctx.moveTo(padding, padding);
  ctx.lineTo(padding, height - padding);
  ctx.lineTo(width - padding, height - padding);
  ctx.stroke();

  if (max === 0) {
    ctx.fillStyle = '#9ca3af';
    ctx.font = '14px Poppins, sans-serif';
    ctx.fillText('Aucune donnee', padding + 8, height / 2);
    return;
  }

  series.forEach((data, sIndex) => {
    ctx.beginPath();
    ctx.strokeStyle = colors[sIndex];
    data.forEach((value, i) => {
      const x = padding + (i / (data.length - 1)) * chartW;
      const y = height - padding - (value / max) * (chartH - 10);
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.stroke();
  });
}

function renderStatsCharts(labels, ventesData, dettesData) {
  drawBarChart(document.getElementById('chartVentes'), labels, ventesData, '#f97316');
  drawBarChart(document.getElementById('chartDettes'), labels, dettesData, '#0b0b0b');
  drawLineChart(document.getElementById('chartComparaison'), labels, [ventesData, dettesData], ['#f97316', '#0b0b0b']);
}
