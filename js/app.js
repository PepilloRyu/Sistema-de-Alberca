(function(){
 const timeout=Number(window.ALBERCAS_SESSION_TIMEOUT||900), logout=window.ALBERCAS_LOGOUT_URL||'index.php?page=logout', el=document.getElementById('idleCountdown'); let left=timeout;
 const reset=()=>{left=timeout}; ['click','keydown','mousemove','scroll','touchstart'].forEach(e=>window.addEventListener(e,reset,{passive:true}));
 setInterval(()=>{left--; if(el){let m=String(Math.max(0,Math.floor(left/60))).padStart(2,'0'),s=String(Math.max(0,left%60)).padStart(2,'0');el.textContent=`${m}:${s}`;} if(left<=0) location.href=logout;},1000);
 const c=document.getElementById('poolChart'); if(c&&window.Chart){try{const d=JSON.parse(c.dataset.pools||'[]');new Chart(c,{type:'bar',data:{labels:d.map(x=>x.n.replace('Alberca ','')),datasets:[{label:'Ocupación',data:d.map(x=>x.v),borderRadius:12}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}})}catch(e){}}
})();

(function(){
 const input=document.querySelector('[data-catalog-filter]');
 if(!input) return;
 const items=[...document.querySelectorAll('[data-catalog-item]')];
 input.addEventListener('input',()=>{
   const q=input.value.trim().toLowerCase();
   items.forEach(el=>{
     const hit=!q || el.textContent.toLowerCase().includes(q);
     el.classList.toggle('is-hidden',!hit);
   });
 });
})();
