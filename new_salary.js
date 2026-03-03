function addRow(tableId, type) {
  let table = document.getElementById(tableId);
  let r = table.rows.length;
  let row = table.insertRow();
  row.innerHTML = `
    <td>${r}</td>
    <td><input type="date" name="${type}_date[]" required></td>
    <td>
      <select name="${type}_day[]">
        <option>Mon</option><option>Tue</option><option>Wed</option>
        <option>Thu</option><option>Fri</option><option>Sat</option>
      </select>
    </td>
    <td><input type="time" name="${type}_from[]" required></td>
    <td><input type="time" name="${type}_to[]" required></td>
    <td><input class="${type}H" name="${type}_hours[]" value="0" oninput="calcHours(event)" required></td>
  `;
}

function calcHours(event) {
  const row = event.target.closest('tr');
  const times = row.querySelectorAll('input[type="time"]');
  if (times[0]?.value && times[1]?.value) {
    const diff = (new Date('2000-01-01T' + times[1].value) - new Date('2000-01-01T' + times[0].value)) / (1000 * 60 * 60);
    event.target.value = Math.max(0, Math.round(diff * 10) / 10);
  }
  calc();
}

function calc() {
  let th = [...document.querySelectorAll(".theoryH")].reduce((a,b)=>a+Number(b.value||0),0);
  let pr = [...document.querySelectorAll(".practicalH")].reduce((a,b)=>a+Number(b.value||0),0);
  document.getElementById("theoryTotal").value = th;
  document.getElementById("practicalTotal").value = pr;
  calcBill();
}

function calcBill() {
  let th = +document.getElementById("theoryTotal").value || 0;
  let pr = +document.getElementById("practicalTotal").value || 0;
  let tr = +document.getElementById("theoryRate").value || 0;
  let prr = +document.getElementById("practicalRate").value || 0;
  document.getElementById("totalBill").innerText = new Intl.NumberFormat('en-IN').format((th * tr) + (pr * prr));
}

// Signature
const canvas = document.getElementById("sign");
const ctx = canvas.getContext("2d");
let drawing = false;

canvas.addEventListener("mousedown", e => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
canvas.addEventListener("mousemove", e => { if (!drawing) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); ctx.lineWidth = 2; ctx.lineCap = "round"; });
canvas.addEventListener("mouseup", () => drawing = false);
canvas.addEventListener("touchstart", e => { e.preventDefault(); drawing = true; const rect = canvas.getBoundingClientRect(); ctx.beginPath(); ctx.moveTo(e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top); });
canvas.addEventListener("touchmove", e => { e.preventDefault(); if (!drawing) return; const rect = canvas.getBoundingClientRect(); ctx.lineTo(e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top); ctx.stroke(); });

document.getElementById("salaryForm").addEventListener("submit", function() {
  document.getElementById("signatureData").value = canvas.toDataURL();
});

function clearSignature() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}
