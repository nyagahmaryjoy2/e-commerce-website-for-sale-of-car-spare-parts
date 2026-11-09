(() => {
   const script = document.currentScript;
   const loginUrl = script.dataset.loginUrl;
   const heartbeatUrl = script.dataset.heartbeatUrl;
   const timeoutMs = 5 * 60 * 1000;
   const heartbeatMs = 60 * 1000;
   let lastActivity = Date.now();
   let timeout;
   let redirecting = false;

   const expireSession = () => {
      if(redirecting) return;
      redirecting = true;
      fetch(`${heartbeatUrl}?expire=1`, {
         method: 'POST',
         credentials: 'same-origin',
         cache: 'no-store',
      }).finally(() => window.location.replace(`${loginUrl}?timeout=1`));
   };

   const resetTimeout = () => {
      lastActivity = Date.now();
      window.clearTimeout(timeout);
      timeout = window.setTimeout(expireSession, timeoutMs);
   };

   const recordActivity = () => {
      if(Date.now() - lastActivity >= 1000){
         resetTimeout();
      }
   };

   ['pointerdown', 'mousemove', 'keydown', 'input', 'scroll', 'touchstart'].forEach(eventName => {
      document.addEventListener(eventName, recordActivity, { passive: true });
   });

   window.setInterval(async () => {
      if(Date.now() - lastActivity >= heartbeatMs) return;
      try{
         const response = await fetch(heartbeatUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
         });
         if(response.status === 401){
            expireSession();
         }
      }catch(error){
         // The server-side idle check still expires the session if the heartbeat fails.
      }
   }, heartbeatMs);

   resetTimeout();
})();
