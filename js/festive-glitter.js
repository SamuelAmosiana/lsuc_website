(function(){
  try{
    // Always enable the festive glitter effect

    const MAX_PARTICLES = Math.min(140, Math.floor(window.innerWidth / 10));
    const COLORS = ['#ff4d4f','#ffa940','#fadb14','#52c41a','#1890ff','#722ed1','#eb2f96','#13c2c2'];

    const canvas = document.createElement('canvas');
    canvas.id = 'festiveGlitterCanvas';
    Object.assign(canvas.style, {
      position: 'fixed',
      top: '0',
      left: '0',
      width: '100vw',
      height: '100vh',
      pointerEvents: 'none',
      zIndex: '99999',
    });
    document.body.appendChild(canvas);
    const ctx = canvas.getContext('2d');

    let dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    function resize(){
      const w = window.innerWidth;
      const h = window.innerHeight;
      canvas.width = Math.floor(w * dpr);
      canvas.height = Math.floor(h * dpr);
      canvas.style.width = w + 'px';
      canvas.style.height = h + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    resize();
    window.addEventListener('resize', resize, { passive: true });

    function rand(min, max){ return Math.random() * (max - min) + min; }
    function pick(arr){ return arr[(Math.random() * arr.length) | 0]; }

    const particles = [];
    function spawnParticle(y = rand(-window.innerHeight, 0)){
      const size = rand(2, 5);
      particles.push({
        x: rand(0, window.innerWidth),
        y,
        size,
        color: pick(COLORS),
        vy: rand(40, 120) / 60, // px per frame
        vx: rand(-20, 20) / 60,
        rot: rand(0, Math.PI * 2),
        vr: rand(-0.05, 0.05),
        shape: Math.random() < 0.5 ? 'circle' : 'rect',
        alpha: rand(0.7, 1)
      });
    }

    for(let i=0;i<MAX_PARTICLES;i++) spawnParticle(rand(0, window.innerHeight));

    let lastTime = performance.now();
    function tick(ts){
      const dt = Math.min(33, ts - lastTime); // cap for tab switches
      lastTime = ts;

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const w = window.innerWidth;
      const h = window.innerHeight;

      for(let i=particles.length-1; i>=0; i--){
        const p = particles[i];
        p.x += p.vx * dt;
        p.y += p.vy * dt;
        p.rot += p.vr * dt;

        if(p.y - 10 > h){
          particles.splice(i,1);
          continue;
        }

        ctx.save();
        ctx.globalAlpha = p.alpha;
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.fillStyle = p.color;
        if(p.shape === 'circle'){
          ctx.beginPath();
          ctx.arc(0, 0, p.size, 0, Math.PI * 2);
          ctx.fill();
        } else {
          ctx.fillRect(-p.size, -p.size, p.size*2, p.size*2);
        }
        ctx.restore();
      }

      while(particles.length < MAX_PARTICLES){
        spawnParticle();
      }

      requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  } catch(e){
    // fail silently
  }
})();
