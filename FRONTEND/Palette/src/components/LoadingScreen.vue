<template>
  <div class="loading-screen">
    <div class="color-wheel"></div>
    <p class="loading-text">Loading...</p>
  </div>
</template>

<style scoped>
.loading-screen {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: #111827;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  transition: opacity 0.8s ease-in-out;
}

.hidden {
  opacity: 0;
  pointer-events: none;
}

.color-wheel {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: conic-gradient(
    red,
    orange,
    yellow,
    lime,
    cyan,
    blue,
    violet,
    red
  );
  animation: spin 1s linear infinite;
  position: relative;
}

.color-wheel::before {
  content: "";
  position: absolute;
  inset: 12px;
  background: #111827;
  border-radius: 50%;
}

.loading-text {
  margin-top: 20px;
  color: white;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>