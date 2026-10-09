/**
 * Advanced PrintScreen Hotkey Blocker
 */

// 1. Intercept the keydown event inside the browser tab
document.addEventListener('keydown', function(e) {
  // Check for the "PrintScreen" key string or the legacy keyCode 44
  if (e.key === 'PrintScreen' || e.keyCode === 44) {
    e.preventDefault();
    
    // Optional: Alert the user or log the breach attempt
    alert('Screenshots are restricted on this application.');
    return false;
  }
});

// 2. Clear the System Clipboard (Crucial Fallback)
// If the operating system bypasses the event handler, this loop constantly 
// empties the clipboard so a captured snapshot cannot be pasted anywhere.
setInterval(function() {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText('Content Protected').catch(() => {});
  }
}, 400); // Clears the clipboard every 400 milliseconds