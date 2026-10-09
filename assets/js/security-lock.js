/**
 * Application Security Lock Engine
 * Anti-Screenshot & Anti-Copy Operations
 */

// 1. Turn screen black when OS screenshot utilities take window focus away
window.addEventListener('blur', function() {
  document.body.style.filter = 'blur(60px) brightness(0)';
});

window.addEventListener('focus', function() {
  document.body.style.filter = 'none';
});

// 2. Direct PrintScreen KeyUp Interceptor
// If the OS bypasses the focus event, this triggers the moment the physical key is released
window.addEventListener('keyup', function(e) {
  if (e.key === 'PrintScreen' || e.keyCode === 44) {
    // Instantly hide all visual layouts
    document.body.style.display = 'none';
    
    // Immediately clear out whatever the OS buffer managed to capture
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText('SECURITY RESTRICTION ACTIVE').catch(() => {});
    }
    
    alert('Screenshots are disabled on this platform.');
    
    // Bring the page layout back safely once they acknowledge the warning
    document.body.style.display = 'block';
  }
});

// 3. Disable Context Menu (Right Click)
document.addEventListener('contextmenu', event => event.preventDefault());

// 4. Restrict keyboard combinations and screenshot triggers
document.addEventListener('keydown', function(e) {
  if (
    e.key === 'PrintScreen' || e.keyCode === 44 ||
    (e.ctrlKey && e.key === 'p') || // Ctrl + P
    ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'v' || e.key === 's' || e.key === 'u')) || // Copy, Paste, Save, Source
    (e.keyCode >= 112 && e.keyCode <= 123) // DevTools F1 - F12
  ) {
    e.preventDefault();
    return false;
  }
});

// 5. Periodic Clipboard Eraser
setInterval(function() {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText('SECURITY RESTRICTION ACTIVE').catch(() => {});
  }
}, 500);